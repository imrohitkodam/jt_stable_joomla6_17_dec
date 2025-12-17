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
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\CMS\MVC\Controller\AdminController;

if (file_exists(JPATH_SITE . '/components/com_jticketing/helpers/main.php')) { require_once JPATH_SITE . '/components/com_jticketing/helpers/main.php'; }
if (file_exists(JPATH_SITE . '/components/com_jticketing/helpers/event.php')) { require_once JPATH_SITE . '/components/com_jticketing/helpers/event.php'; }

/**
 * Jtickeing list enrollment controller.
 *
 * @since  2.1
 */
class JticketingControllerEnrollment extends AdminController
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     JControllerLegacy
	 * @since   2.1
	 */
	public function __construct($config = array())
	{
		$lang = Factory::getLanguage();
		$extension = 'com_jticketing';
		$base_dir = JPATH_ADMINISTRATOR;
		$language_tag = '';
		$reload = true;
		$lang->load($extension, $base_dir, $language_tag, $reload);

		parent::__construct($config);
	}

	/**
	 * common function to drive enrollment
	 *
	 * @return  void
	 *
	 * @since   2.1
	 */
	public function save()
	{
		Session::checkToken() or Session::checkToken('get') or jexit('Invalid Token');

		$app                  = Factory::getApplication();
		$exceptions           = array();
		$enrollModelObj       = new stdClass;
		$successCount         = 0;
		$failureCount         = 0;
		$alreadyEnrolledCount = 0;
		$config               = JT::config();

		$userIds     = $app->getInput()->get('cid', '0', 'ARRAY');
		$eventIds    = $app->getInput()->get('selected_events', '0', 'ARRAY');
		$redirectUrl = $app->getInput()->get('redirectUrl', '', 'STRING');
		$notify      = $app->getInput()->get('notify_user_enroll', '', 'INT');

		// Enroll each user to every event
		foreach ($userIds as $userId)
		{
			foreach ($eventIds as $eventId)
			{
				// Enrollment Params
				$data = array();

				// Mandatory params
				$data['userId'] = $userId;
				$data['eventId'] = $eventId;

				// Not mandatory params
				$data['notify'] = $notify;

				if (!empty($data))
				{
					// If we create object outside of for loop it assign same error message to different events.
					$enrollModelObj = $this->getModel('enrollment');
					$result = $enrollModelObj->save($data);

					if ($result)
					{
						if ((int) $result === 2)
						{
							++$alreadyEnrolledCount;
						}
						else
						{
							++$successCount;
						}
					}
					else
					{
						++$failureCount;
					}

					$name  = Factory::getUser($userId)->name;
					$title = JT::event($eventId)->getTitle();
					$error = $enrollModelObj->getError();

					if (!empty($error))
					{
						// Unsuccessful enrollments
						$exceptions[] = Text::sprintf('COM_JTICKETING_ENROLLMENT_ERROR_MESSAGES', $name, $title, $error);
					}
					else
					{
						// Successful enrollments
						$error = Text::_("COM_JTICKETING_ENROLLMENT_SUCCESS_MESSAGE_SINGULAR");
						$exceptions[] = Text::sprintf('COM_JTICKETING_ENROLLMENT_ERROR_MESSAGES', $name, $title, $error);
					}
				}
			}
		}

		$failureLog = '';

		if (count($exceptions))
		{
			$failureLog = Text::_("COM_JTICKETING_FAILURE_LOG");
			$enrollmentTitleMessage = Text::sprintf('COM_JTICKETING_ENROLLMENT_MESSAGE', $successCount, $alreadyEnrolledCount, $failureCount, '');

			// Error handling & log writing
			$enrollModelObj->writeEnrollmentLog($exceptions, $enrollmentTitleMessage);
		}

		$taskLink = 'index.php?option=com_jticketing&view=enrollment&task=enrollment.jtEnrollmentDownloadLog';
		$file = '<b><a target="_Blank" href="' . $taskLink . '" >' . $failureLog . '</a></b>';
		$enrollmentErrorLogMsg = Text::sprintf('COM_JTICKETING_ENROLLMENT_MESSAGE', $successCount, $alreadyEnrolledCount, $failureCount, $file);

		if ($redirectUrl && $successCount == 1)
		{
			$redirectUrl = base64_decode($redirectUrl);
			$app->redirect($redirectUrl);
		}
		elseif (empty($redirectUrl) && !empty($enrollmentErrorLogMsg))
		{
			$app->enqueueMessage($enrollmentErrorLogMsg, 'Notice');
			$app->redirect(Route::_('index.php?option=com_jticketing&view=enrollment&tmpl=component', false));
		}
		else
		{
			// In case redirect url is not set, redirect user to homepage
			$app->redirect(Uri::root());
		}

		if (!empty($enrollmentErrorLogMsg) && $redirectUrl)
		{
			$redirectUrl = base64_decode($redirectUrl);
			$app->enqueueMessage($enrollmentErrorLogMsg, 'Notice');
			$app->redirect($redirectUrl);
		}
		else
		{
			$app->enqueueMessage($enrollmentErrorLogMsg, 'Notice');
			$app->redirect(Route::_('index.php?option=com_jticketing&view=enrollment&tmpl=component', false));
		}
	}

	/**
	 * Common function to download failure log for enrollments.
	 *
	 * @return  void
	 *
	 * @since   2.1
	 */
	public function jtEnrollmentDownloadLog()
	{
		$session  = Factory::getSession();
		$config   = Factory::getConfig();
		$user     = Factory::getUser();
		$filename = $session->get('filename' . $user->id);

		if (empty($filename))
		{
			return;
		}

		$file     = $config->get('tmp_path') . '/' . $filename . '.txt';

		if (file_exists($file))
		{
			header('Content-Description: File Transfer');
			header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename="' . basename($file) . '"');
			header('Expires: 0');
			header('Cache-Control: must-revalidate');
			header('Pragma: public');
			header('Content-Length: ' . filesize($file));
			readfile($file);
			jexit();
		}
	}

	/**
	 * Method to move the attendee enrollment from one event to another.
	 *
	 * @return  void
	 *
	 * @since  2.8.0
	 */
	public function moveAttendee()
	{
		Session::checkToken() or Session::checkToken('get') or jexit('Invalid Token');

		$app           = Factory::getApplication();
		$config        = JT::config();
		$jinput        = $app->input;
		$user          = Factory::getUser();
		$attendeeId    = $jinput->getInt('attendeeId', 0);
		$eventId       = $jinput->getInt('eventId', 0);
		$userId        = $jinput->getInt('userId', 0);
		$selectedEvent = $jinput->getInt('selected_event', 0);
		$url           = Route::_('index.php?option=com_jticketing&view=attendees', false);
		$return        = false;

		// Check permissions here
		$attendeeObj        = JT::attendee($attendeeId);

		if (empty($selectedEvent))
		{
			$app->enqueueMessage(Text::_('COM_JTICKETING_ATTENDEES_VIEW_EMPTY_EVENT_ERROR'), 'Error');
			$app->redirect($url);
		}

		// If user is not authorized then show error.
		if (!$config->get('enable_attendee_move', 0)
			|| $attendeeObj->status === COM_JTICKETING_CONSTANT_ATTENDEE_STATUS_PENDING)
		{
			$app->enqueueMessage(Text::_('COM_JTICKETING_NOT_AUTHORIED_TO_PERFORM_THIS_ACTION'), 'Error');
			$app->redirect($url);
		}

		// Check if seat is available in the new event.
		$moveEvent       = JT::event($selectedEvent);
		$endDateTime = new DateTime($moveEvent->getEndDate());
		$currentDateTime = new DateTime();

		// check move attendee to not expired event.
		if($currentDateTime > $endDateTime)
		{
			$app->enqueueMessage(Text::_('COM_JTICKETING_ENROLMENT_ATTENDEE_MOVE_EXPIRED_EVENT'), 'Error');
			$app->redirect($url);
		}

		$enrollmentModel = JT::model('Enrollment', array('ignore_request' => true));
		$isSeatAvailable = $enrollmentModel->getTicketTypeId($moveEvent->integrationId);

		if (!$isSeatAvailable)
		{
			$app->enqueueMessage(Text::_('COM_JTICKETING_ENROLLMENT_VIEW_EVENT_EXCEEDS_TICKET_LIMIT'), 'Error');
			$app->redirect($url);
		}
		else
		{
			// Genrate required data for enrolling new attendee.
			$newEnrollData                  = array();
			$newEnrollData['userId']        = $userId;
			$newEnrollData['eventId']       = $selectedEvent;
			$newEnrollData['status']        = 'A';
			$newEnrollData['notify']        = true;
			$newEnrollData['oldAttendeeId'] = $attendeeId;

			// Adding flag to skip the duplication check.
			$newEnrollData['move'] = true;

			$app->setUserState('attendee.oldEventId', $eventId);

			// Enroll for new event.
			/** @var $enrollmentModel JticketingModelEnrollment */
			$return = $enrollmentModel->save($newEnrollData);

			if ($return)
			{
				// Return to the attendee page.
				$app->enqueueMessage(Text::_("COM_JTICKETING_ENROLLMENT_VIEW_ATTENDEE_MOVE_SUCCESSFULL"), 'success');
				$app->redirect(Route::_('index.php?option=com_jticketing&view=attendees', false));
			}

			if ($enrollmentModel->getError())
			{
				$app->enqueueMessage($enrollmentModel->getError(), 'Error');
			}
			else
			{
				$app->enqueueMessage(Text::_('COM_JTICKETING_ENROLLMENT_SOMETHING_IS_WRONG'), 'Error');
			}

			$app->redirect($url);
		}
	}
}
