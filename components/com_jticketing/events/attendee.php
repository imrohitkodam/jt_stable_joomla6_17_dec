<?php
/**
 * @package     JTicketing
 * @subpackage  com_jticketing
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

if (file_exists(JPATH_SITE . '/components/com_jticketing/helpers/main.php')) { require_once JPATH_SITE . '/components/com_jticketing/helpers/main.php'; }
if (file_exists(JPATH_SITE . '/components/com_jticketing/models/enrollment.php')) { require_once JPATH_SITE . '/components/com_jticketing/models/enrollment.php'; }
if (file_exists(JPATH_SITE . '/components/com_jticketing/helpers/frontendhelper.php')) { require_once JPATH_SITE . '/components/com_jticketing/helpers/frontendhelper.php'; }
if (file_exists(JPATH_SITE . '/components/com_jticketing/helpers/common.php')) { require_once JPATH_SITE . '/components/com_jticketing/helpers/common.php'; }

/**
 * JTicketing triggers class for attendee and enrollment
 * This trigger is used in both attendee and enrollment scenarios
 *
 * @since  2.1
 */
class JticketingTriggerAttendee
{
	public $jticketingParams;

	public $jtFrontendHelper;

	public $eventFormModel;

	public $error;

	/**
	 * Method acts as a constructor
	 *
	 * @since   2.1
	 */
	public function __construct()
	{
		$this->jticketingParams = ComponentHelper::getParams('com_jticketing');
		$this->jtFrontendHelper = new Jticketingfrontendhelper;
		$this->eventFormModel   = JT::model('EventForm');
	}

	/**
	 * Trigger for after event save
	 *
	 * @param   Array  $enrollmentDetails  Campaign Details
	 *
	 * @return  Boolean
	 */
	public function onAfterEnrollmentStatusChange($enrollmentDetails)
	{
		$user                = Factory::getUser();
		$userAssingedTo      = Factory::getUser($enrollmentDetails['owner_id']);
		$integration         = JT::getIntegration(true);
		JLoader::register('JticketingMailHelper', JPATH_SITE . '/components/com_jticketing/helpers/mail.php');

		switch (strtoupper($enrollmentDetails['status']))
		{
			case 'A':
					// Decrement ticket count if not unlimited
					$tickettype = JT::tickettype($enrollmentDetails['ticket_type_id']);
					$tickettype->decreaseAvilableSeats();

					if (!empty($tickettype->getError()))
					{
						$this->setError($tickettype->getError());

						return false;
					}

					// Insert or Update todo entries
					$eventDetails = JT::event($enrollmentDetails['eventId']);
					$eventDetails->event_url = $eventDetails->getUrl();

					if (empty($eventDetails->getId()))
					{
						$this->setError(Text::_('COM_JTICKETING_ERROR_SOMETHING_IS_WRONG_WITH_EVENT_DETAILS'));

						return false;
					}

					// Insert TODO entries for current enrollment
					$eventData = array();
					$eventData['eventId']     = $eventDetails->getId();
					$eventData['eventTitle']  = $eventDetails->getTitle();
					$eventData['startDate']   = $eventDetails->getStartDate();
					$eventData['endDate']     = $eventDetails->getEndDate();
					$eventData['assigned_by'] = $user->id;
					$eventData['assigned_to'] = $userAssingedTo->id;
					$eventData['user_id']     = $userAssingedTo->id;
					$eventData['notify']      = $enrollmentDetails['notify'];

					// Insert todo or update todo
					$this->eventFormModel->saveTodo($eventData);

					// If online event Invite user and send mail
					if ($integration == 2)
					{
						if ($eventDetails->isOnline())
						{
							$meeting_url              = json_decode($eventDetails->params);
							$venueDetails 			  = JT::model('venueform')->getItem($eventDetails->venue);
							$venueParams              = (object) $venueDetails->params;
							$venueParams->user_id     = $userAssingedTo->id;
							$venueParams->name        = $userAssingedTo->name;
							$venueParams->email       = $userAssingedTo->email;

							$utilities = JT::utilities();
							$venueParams->password    = $utilities->generateRandomString(8);

							$venueParams->meeting_url = $meeting_url->event_url;
							$venueParams->sco_id      = $meeting_url->event_sco_id;

							if (!empty($venueParams))
							{
								$attendee = JT::Attendee($enrollmentDetails['attendee_id']);
								$event    = JT::event($enrollmentDetails['eventId']);

								if (!$event->addAttendee($attendee))
								{
									$this->setError($event->getError());

									return false;
								}

								if ($enrollmentDetails['notify'] == true)
								{
									JticketingMailHelper::onlineEventNotify(
										JT::order(),
										JT::attendee($enrollmentDetails['attendee_id']),
										JT::event($enrollmentDetails['eventId'])
									);
								}
							}
						}

						$activityData = array();
						$activityData['status'] = 'A';

						// Trigger After enrollment
						PluginHelper::importPlugin('system');

						// Old system plugin trigger
						Factory::getApplication()->triggerEvent('onJtAfterEnrollment', array($activityData, $enrollmentDetails['attendee_id']));

						// Trigger after enrollment
						PluginHelper::importPlugin('jticketing');

						// JTicketing plugin trigger
						Factory::getApplication()->triggerEvent('onAfterJtEnrollment', array($activityData, $enrollmentDetails['attendee_id']));
					}

					if ((!$eventDetails->isOnline() && $integration == 2) || $integration != 2)
					{
						BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_jticketing/models');
						$attendeeFormModel = BaseDatabaseModel::getInstance('AttendeeForm', 'JticketingModel');
						$attendeeDetails   = $attendeeFormModel->getItem($enrollmentDetails['attendee_id']);

						if ($enrollmentDetails['notify'] == true)
						{
							JticketingMailHelper::enrollmentTicketMail($eventDetails, $attendeeDetails);
						}
					}

				break;

			case 'R':
					// Change ticket count when enrollment status changes from Approval to anything
					if (isset($enrollmentDetails['old_status']))
					{
						$eventDetails    = JT::event($enrollmentDetails['eventId']);
						$attendeeDetails = JT::attendee($enrollmentDetails['attendee_id']);

						// Send mail in case of reject case only.
						if (strtoupper($enrollmentDetails['status']) == 'R' && $enrollmentDetails['notify'] == true)
						{
							JticketingMailHelper::rejectEnrollmentMail($eventDetails, $attendeeDetails);
						}
					}

			case 'P':
					// Change ticket count when enrollment status changes from Approval to anything
					if (isset($enrollmentDetails['old_status']) && strtoupper($enrollmentDetails['old_status']) === "A")
					{
						// Increment ticket count if not unlimited
						$tickettype = JT::tickettype($enrollmentDetails['ticket_type_id']);
						$tickettype->increaseAvailableSeats();

						if (!empty($tickettype->getError()))
						{
							$this->setError($tickettype->getError());

							return false;
						}

						// Delete todo if exist
						$params                 = array();
						$params['isEnrollment'] = 1;
						$params['eventId']      = $enrollmentDetails['eventId'];
						$params['assigned_to']  = $enrollmentDetails['owner_id'];

						$isDeleted = $this->eventFormModel->deleteTodo($params);

						if (!$isDeleted)
						{
							$this->setError(Text::_('COM_JTICKETING_ERROR_SOMETHING_IS_WRONG_WITH_TODO'));

							return false;
						}

						$config          = JT::config();
						$integration     = $config->get('integration');
						$eventDetails    = JT::event($enrollmentDetails['eventId']);
						$attendeeDetails = JT::attendee($enrollmentDetails['attendee_id']);

						// Send mail in case of reject case only.
						if (strtoupper($enrollmentDetails['status']) == 'R'
							&& $enrollmentDetails['notify'] == true
							&& (!$eventDetails->isOnline() && $integration == COM_JTICKETING_CONSTANT_INTEGRATION_NATIVE)
							|| $integration != COM_JTICKETING_CONSTANT_INTEGRATION_NATIVE)
						{
							JticketingMailHelper::rejectEnrollmentMail($eventDetails, $attendeeDetails);
						}
					}
			break;
		}

		$affectIntegrationAttendeeSeats = $this->jticketingParams->get('affect_js_native_seats');

		if ($affectIntegrationAttendeeSeats == 1)
		{
			$integration = JT::getIntegration(true);

			if ($integration == 4)
			{
				// Update easysocial attendee count
				$path = JPATH_ADMINISTRATOR . '/components/com_easysocial/includes/easysocial.php';
				JLoader::register('ES', $path, true);

				$eventId = $enrollmentDetails['eventId'];
				$userId  = $userAssingedTo->id;

				if (($enrollmentDetails['status']) == 'A')
				{
					$task = 'going';
				}
				elseif($enrollmentDetails['status'] == 'P')
				{
					$task = 'maybe';
				}
				else
				{
					$task = 'notgoing';
				}

				$event   = ES::event($eventId);
				$event->rsvp($task, $userId);
			}
		}
	}

	/**
	 * Sets error message.
	 *
	 * @param   string  $error  error message
	 *
	 * @return  Boolean
	 */
	public function setError($error)
	{
		$this->error = $error;
	}
}
