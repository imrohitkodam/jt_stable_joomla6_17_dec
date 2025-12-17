<?php
/**
 * @package     JTicketing
 * @subpackage  com_jticketing
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

if (file_exists(JPATH_SITE . '/components/com_jticketing/events/event.php')) { require_once JPATH_SITE . '/components/com_jticketing/events/event.php'; }

use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\Filesystem\Folder;

/**
 * Methods supporting a jticketing checkin.
 *
 * @since  2.0.0
 */
class JticketingModelCheckin extends AdminModel
{
	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param   type    $type    The table type to instantiate
	 * @param   string  $prefix  A prefix for the table class name. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return	JTable	A database object
	 *
	 * @since	2.0
	 */
	public function getTable($type = 'Checkin', $prefix = 'JticketingTable', $config = array())
	{
		$app = Factory::getApplication();

		if ($app->isClient("administrator"))
		{
			return Table::getInstance($type, $prefix, $config);
		}
		else
		{
			$this->addTablePath(JPATH_ADMINISTRATOR . '/components/com_jticketing/tables');

			return Table::getInstance($type, $prefix, $config);
		}
	}

	/**
	 * Method to get the record form.
	 *
	 * @param   array    $data      An optional array of data for the form to interogate.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return    JForm    A JForm object on success, false on failure
	 *
	 * @since    2.0
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Initialise variables.
		$app = Factory::getApplication();

		// Get the form.
		$form = $this->loadForm('com_jticketing.checkin', 'checkin', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  mixed  The user id on success, false on failure.
	 *
	 * @since  2.0
	 */
	public function save($data)
	{
		$app                  = Factory::getApplication();
		$jticketingMainHelper = new jticketingmainhelper;
		$currentDateTime      = '';

		if (empty($data['attendee_id']))
		{
			return false;
		}

		$attendeeId = $data['attendee_id'];
		$state      = isset($data['state']) ? $data['state'] : 0;
		$event      = isset($data['event_obj']) ? $data['event_obj'] : '';
		$userid     = isset($data['user_id']) ? $data['user_id'] : Factory::getUser()->id;
		$isCron     = isset($data['isCron']) ? $data['isCron'] : false;
		$rId        = isset($data['r_id']) ? $data['r_id'] : 0; // Check for r_id
		$db = Factory::getDbo();
		$currentDate = Factory::getDate()->format('Y-m-d'); // Get today's date in Y-m-d format

		if (file_exists(JPATH_SITE . '/components/com_jticketing/models/attendeeform.php')) { require_once JPATH_SITE . '/components/com_jticketing/models/attendeeform.php'; }
		$model = BaseDatabaseModel::getInstance('AttendeeForm', 'JticketingModel');

		$attendeeData  = $model->getItem($attendeeId);
		$integration   = JT::getIntegration();

		if (empty($event) && (isset($data['eventid']) || $attendeeData->event_id))
		{
			$eventid  = !empty($data['eventid']) ? $data['eventid'] : $attendeeData->event_id;
			$eventObj = JT::event()->loadByIntegration($eventid);

			$event_id = $eventObj->id;

			if ($integration == "com_jticketing")
			{
				if (file_exists(JPATH_SITE . '/components/com_jticketing/models/eventform.php')) { require_once JPATH_SITE . '/components/com_jticketing/models/eventform.php'; }
				$eventModel = BaseDatabaseModel::getInstance('EventForm', 'JticketingModel');

				$event = $eventModel->getItem($event_id);

				if ($isCron == false)
				{
					if (!empty($event->ideal_time) && $event->online_events == 0)
					{
						$seconds = $event->ideal_time * 60;

						$event->spendTime = $seconds;
					}
				}
			}
		}

		$eventid      = isset($eventid) ? $eventid : $event->eventId;
		$tempEvent    = JT::event()->loadByIntegration($eventid);
		$event_id     = $tempEvent->getId();
		$eventCreator = $tempEvent->getCreator();
		
		if ($integration == "com_jticketing") {
			// Fetch r_id while checkin the main event to add the r_id of this main event inside checkindetails table
			if ($rId == 0)
			{
				$eventStartDate = substr($event->startdate, 0, 10);

				$query = $db->getQuery(true)
					->select($db->quoteName('r_id'))
					->from($db->quoteName('#__jticketing_recurring_events'))
					->where($db->quoteName('event_id') . ' = ' . (int) $event_id)
					->where($db->quoteName('start_date') . ' = ' . $db->quote($eventStartDate));

				$db->setQuery($query);
				$rId = $db->loadResult();
			}

			if (is_array($rId) && isset($rId[0]))
			{
				$rId = (int) $rId[0];
			}
			else
			{
				$rId = (int) $rId;
			}
			// Validate r_id and start_date only if state is 1
			if ($state == 1) {
				if ($rId > 0) {
					// Fetch event data from recurring events table
					$query = $db->getQuery(true)
						->select($db->quoteName(['start_date', 'start_time', 'end_time']))
						->from($db->quoteName('#__jticketing_recurring_events'))
						->where($db->quoteName('r_id') . ' = ' . (int) $rId);
					$db->setQuery($query);
					$eventData = $db->loadObject();
					if (!$eventData)
					{
						$this->setError(Text::_('COM_JTICKETING_NO_RECURRING_EVENTS_FOUND'));
						return false;
					}
					// Extract event details
					$startDate = $eventData->start_date;
					// Get Joomla's configured timezone
					$config = Factory::getConfig();
					$timezone = $config->get('offset', 'UTC');

					// Get the current date and time in Joomla's timezone
					$currentDateTime = new DateTime('now', new DateTimeZone($timezone));
					$currentDate = $currentDateTime->format('Y-m-d');
					$currentTime = $currentDateTime->format('H:i:s');

					// Validate if the start date matches the current date
					if ($startDate !== $currentDate) {
						$this->setError(Text::sprintf('COM_JTICKETING_EVENT_NOT_FOUND_ON_DATE'));
						return false;
					}
				}
				else
				{
					// Handle non-recurring events
					$query = $db->getQuery(true)
						->select($db->quoteName(['startdate', 'enddate', 'recurring_type']))
						->from($db->quoteName('#__jticketing_events'))
						->where($db->quoteName('id') . ' = ' . (int) $event_id);
					$db->setQuery($query);
					$event = $db->loadObject();

					if ($event->recurring_type === 'No_repeat') {
						$startDate = (new DateTime($event->startdate))->format('Y-m-d');
						$endDate = (new DateTime($event->enddate))->format('Y-m-d');

						// Validate if the current date is within the event date range
						if ($currentDate < $startDate || $currentDate > $endDate) {
							$this->setError(Text::sprintf('COM_JTICKETING_EVENT_NOT_FOUND_ON_DATE', $currentDate));
							return false;
						}
					}
				}
			}
		}
		if ($app->isClient("site") && $userid != $eventCreator && $isCron == false)
		{
			$this->setError(Text::_('COM_JTICKETING_NOT_AUTHORISED_FOR_ACTION'));

			return false;
		}

		$date = Factory::getDate();

		if (!empty($state))
		{
			$currentDateTime = $date->toSql(true);
		}

		$tableCheckin = $this->getTable("Checkin", "JTicketingTable");
		$tableCheckin->load(array('attendee_id' => (int) $attendeeData->id));

		$checkinData = array();

		// Load existing record based on attendee_id and r_id combination
		$result = $this->getTable();
		$result->load(array('attendee_id' => (int) $attendeeData->id, 'r_id' => $rId));

		// Update checkinData
		$checkinData['ticketid']       = $attendeeData->ticket_type_id;
		$checkinData['checkintime']    = (isset($event->checkintime) && !empty($event->checkintime)) ? $event->checkintime : $currentDateTime;
		$checkinData['checkouttime']   = (isset($event->checkouttime) && !empty($event->checkouttime)) ? $event->checkouttime : $event->enddate;

		$EventStartDate = strtotime($event->startdate);
		$EventEndDate = strtotime($event->enddate);
		$idealEventTime = abs($EventEndDate - $EventStartDate);
		$idealEventTime = gmdate('H:i:s', $idealEventTime);

		$checkinData['spent_time']     = (isset($event->spendTime) && !empty($event->spendTime)) ? $event->spendTime : $idealEventTime;
		$checkinData['checkin']        = $state;
		$checkinData['eventid']        = isset($eventid) ? $eventid : $event->eventId;
		$checkinData['attendee_id']    = $attendeeData->id;
		$checkinData['attendee_email'] = !empty($attendeeData->owner_email) ? $attendeeData->owner_email
		: Factory::getUser($attendeeData->owner_id)->email;
		$checkinData['attendee_name']  = Factory::getUser($attendeeData->owner_id)->name;
		$checkinData['owner_id']       = $attendeeData->owner_id;
		$checkinData['notify']         = isset($data['notify']) ? $data['notify'] : 0;
		if ($integration == "com_jticketing") {
			$checkinData['r_id'] = $rId;
		} else {
			$checkinData['r_id'] = 0;
		}

		// If record exists, update it; otherwise, create a new one
		$checkinData['id'] = $result->id;

		$saveResult = parent::save($checkinData);

		if ($saveResult)
		{
			// Now UPDATE INTEGRATIONXREF TABLE FOR CHECKIN COUNT
			if ((empty($result->id) && $state == 1) || (!empty($result->id) && $result->checkin == 0 && $state == 1)  || $state == 0)
			{
				$this->checkinIntegration($state, $attendeeData);
			}

			// TODO Updation, Mark TODO complete
			// Issue certificate
			$jtTriggerEvent = new JticketingTriggerEvent;

			$checkinDetails               = array();
			$checkinDetails['attendeeId'] = $attendeeData->id;
			$checkinDetails['checkin']    = $checkinData['checkin'];
			$eventData                    = array();
			$eventData['eventId']         = $checkinData['eventid'];

			if ($checkinData['checkin'])
			{
				$eventData['status'] = 'C';
			}
			else
			{
				$eventData['status'] = 'I';
			}

			$eventData['assigned_to'] = $attendeeData->owner_id;
			$returnArray = $jtTriggerEvent->onAfterJtEventCheckin($checkinDetails, $eventData);

			// Add certificate URl
			if (isset($returnArray['certificateUrl']) && $returnArray['certificateUrl'])
			{
				$checkinData['certificateUrl'] = $returnArray['certificateUrl'];
				$checkinData['certificateRawUrl'] = $returnArray['certificateRawUrl'];
			}

			/*if ($tempEvent->isOnline() == 0)
			{
				// Plugin trigger of shika to add attendance
				PluginHelper::importPlugin('tjevent');
				Factory::getApplication()->triggerEvent('onAttendanceEvent', array($checkinData, $state));
			}*/

			PluginHelper::importPlugin('tjevents');

			if (Folder::exists(JPATH_SITE . '/components/com_tjlms'))
			{
				PluginHelper::importPlugin('tjevent');
			}

			if (Folder::exists(JPATH_SITE . '/components/com_tjlms'))
			{
				$eventObject = JT::event()->loadByIntegration($checkinData['eventid']);

				$addLesson['last_accessed_on'] = $checkinData['checkouttime'];
				$addLesson['timestart']        = $checkinData['checkintime'];
				list($h, $m, $s) = explode(':', $checkinData['spent_time']);
				$addLesson['spent_time']       = ($h * 3600) + ($m * 60) + $s;
				$addLesson['event_id']         = $eventObject->getId();
				$addLesson['completed']        = 1;
				$addLesson['state']            = 1;
				$resultTjlms = Factory::getApplication()->triggerEvent('updateLessonTrack', array($checkinData['owner_id'], $addLesson));
			}

			// Old trigger - Added plugin trigger tobe executed after check in done
			PluginHelper::importPlugin('system');
			Factory::getApplication()->triggerEvent('onAfterJtEventCheckin', array($checkinData));

			// New trigger
			PluginHelper::importPlugin('jticketing');
			Factory::getApplication()->triggerEvent('onAfterJtAttendeeCheckin', array($checkinData));
			return true;
		}

		return false;
	}

	/**
	 * update jticketing checkin integration
	 *
	 * @param   INT     $state         publish/unpublish
	 * @param   OBJECT  $attendeeData  attendeeData
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function checkinIntegration($state, $attendeeData)
	{
		$db = Factory::getDbo();
		$user = Factory::getUser();
		$jticketingmainhelper = new jticketingmainhelper;

		if ($attendeeData)
		{
			$query = $db->getQuery(true);

			if ($state == 1)
			{
				$fields = array($db->quoteName('checkin') . ' = checkin+1');
				$conditions = array($db->quoteName('id') . ' = ' . (int) $attendeeData->event_id);
			}
			else
			{
				$fields = array($db->quoteName('checkin') . ' = checkin-1');
				$conditions = array($db->quoteName('checkin') . '> 0',
					$db->quoteName('id') . ' = ' . (int) $attendeeData->event_id);
			}

			$query->update($db->quoteName('#__jticketing_integration_xref'))->set($fields)->where($conditions);
			$db->setQuery($query);
			$db->execute();
		}

		if ($state == 1)
		{
			$com_params          = JT::config();
			$socialintegration   = $com_params->get('integrate_with', 'none');
			$streamCheckinTicket = $com_params->get('streamCheckinTicket', 0);

			$actor_id = !empty($attendeeData->owner_id) ? $attendeeData->owner_id : $user->id;

			if ($socialintegration != 'none')
			{
				// Add in activity.
				if ($streamCheckinTicket == 1)
				{
					$orderModel  = JT::model('order');
					$libclass    = $orderModel->getJticketSocialLibObj();
					$action      = 'streamCheckinTicket';
					$eventObj    = JT::event()->loadByIntegration($attendeeData->event_id);
					$linkMode    = Factory::getConfig()->get('force_ssl', 0) == 2 ? Route::TLS_FORCE : Route::TLS_IGNORE;
					$link = Route::link('site', $eventObj->getUrl(false), false, $linkMode, true);
					$eventLink   = '<a class="" href="' . $link . '">' . $eventObj->getTitle() . '</a>';

					if ($attendeeData->id)
					{
						$buyername = '';

						// Get Attendee Details
						$attendeeFields = JT::attendeefieldvalues()->loadByAttendeeId($attendeeData->id);

						foreach ($attendeeFields as $attendeeField)
						{
							$attendee_details[$attendeeField->name] = $attendeeField->field_value;
						}

						if (!empty($attendee_details['first_name']))
						{
							$buyername = $attendee_details['first_name'] . (!empty($attendee_details['last_name']) ? ' ' . $attendee_details['last_name'] : '');
						}
						else
						{
							// If collect attendee info is set  to no in backend then take first and last name from billing info.
							$attendeeObj = JT::attendee($attendeeData->id);
							$buyername = $attendeeObj->getFirstName() . ' ' . $attendeeObj->getLastName();
						}

						$originalMsg = Text::sprintf('COM_JTICKETING_CHECKIN_SUCCESS_ACT_NAME', $buyername, $eventLink);
					}
					else
					{
						$originalMsg = Text::sprintf('COM_JTICKETING_CHECKIN_SUCCESS_ACT', $eventLink);
					}

					$libclass->pushActivity($actor_id, $act_type = '', $act_subtype = '', $originalMsg, $act_link = '', $title = '', $act_access = 0);
				}
			}
		}

		return 1;
	}

	/**
	 * Method to get a single record.
	 *
	 * @param   integer  $pk  The id of the primary key.
	 *
	 * @return	mixed	Object on success, false on failure.
	 *
	 * @since	2.0
	 */
	public function getItem($pk = null)
	{
		if ($pk !== null)
		{
			return $item = parent::getItem($pk);
		}

		return false;
	}

	/**
	 * Method to delete media record
	 *
	 * @param   string  &$mediaId  post data
	 *
	 * @return	JForm	A JForm object on success, false on failure
	 *
	 * @since   2.0
	 */
	public function delete(&$mediaId)
	{
		if (parent::delete($mediaId))
		{
			return true;
		}

		return false;
	}

	/**
	 * Method to get checkin details from order items id
	 *
	 * @param   int  $attendee_id  attendee_id jticketin_order_items table
	 * @param   int  $eventid      eventid        eventid
	 *
	 * @return  void
	 */
	public function getCheckinStatus($attendee_id, $eventid = '')
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('checkin');
		$query->from('#__jticketing_checkindetails');
		$query->where($db->quoteName('attendee_id') . ' = ' . $db->quote($attendee_id));
		$db->setQuery($query);
		$eventOnDate = $db->loadResult();

		if (!empty($eventOnDate))
		{
			return 1;
		}

		return 0;
	}

	/**
	 * Method to get attendee ID from enrollment ID
	 *
	 * @param   string  $enrollmentID  attendee_id attendees table
	 *
	 * @return  integrer
	 */
	public function getAttendeeID($enrollmentID)
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('id');
		$query->from('#__jticketing_attendees');
		$query->where($db->quoteName('enrollment_id') . ' = ' . $db->quote($enrollmentID));
		$db->setQuery($query);
		$atendeeID = $db->loadResult();

		return $atendeeID;
	}

	/**
	 * Method to delete the checkin information agianst the attendee
	 *
	 * @param   JTicketingAttendee  $attendee  The attendee object
	 *
	 * @return  mixed    false on failure
	 *
	 * @since   2.5.0
	 */
	public function deleteCheckinInfo(JTicketingAttendee $attendee)
	{
		if (!$attendee->id)
		{
			return false;
		}

		/**
		 * Performing a simple delete query because we don't have primary/unique key on attendee_id
		 */
		$query = $this->_db->getQuery(true);
		$query->delete($this->_db->qn('#__jticketing_checkindetails'));
		$query->where($this->_db->qn('attendee_id') . '=' . (int) $attendee->id);
		$this->_db->setQuery($query);

		return $this->_db->execute();
	}

	/**
	 * Generate and return replacement tag's object.
	 *
	 * @param   JTicketingAttendee  $attendee  The attendee object
	 *
	 * @return  object|boolean
	 *
	 * @since  2.7.0
	 */
	public function getReplacementTags(JTicketingAttendee $attendee)
	{
		if (!$attendee->id)
		{
			return false;
		}

		$replacement     = new stdClass;
		$replacement->checkin = new stdClass;
		$utilities       = JT::utilities();
		$event           = JT::event()->loadByIntegration($attendee->event_id);
		$currentDateTime = Factory::getDate()->toSql();
		$spentTime = isset($event->spendTime) ? $event->spendTime : date('H:i:s', strtotime($event->enddate) - strtotime($event->startdate));

		// To get attendee information
		$attendeeFieldVal = JT::attendeefieldvalues();

		$fieldsValues = $attendeeFieldVal->loadByAttendeeId($attendee->id);

		if (!empty($fieldsValues))
		{
			// The default email index value in $fieldsValues array is 0
			// In case event creator added extra attenddee fields other than first name, last name, phone and email then the email index value will change
			$fieldsValuesKeys                     = array_column($fieldsValues, 'name');
			$emailFieldIndex                      = array_search('email', $fieldsValuesKeys);
			$firstNameFieldIndex                  = array_search('first_name', $fieldsValuesKeys);
			$lastNameFieldIndex                   = array_search('last_name ', $fieldsValuesKeys);
			$toEmail['attendee_email']            = $fieldsValues[$emailFieldIndex]->field_value;
			$replacement->checkin->attendee_name = $fieldsValues[$firstNameFieldIndex]->field_value . ' ' . $fieldsValues[$lastNameFieldIndex]->field_value;
		}
		elseif ($attendee->owner_id != 0)
		{
			$user = Factory::getUser($attendee->owner_id);
			$replacement->checkin->attendee_name = $user->name;
			$replacement->checkin->attendee_email = $user->email;
		}
		else
		{
			$orderItem = JT::orderitem()->loadByAttendeeId($attendee->id);
			$order     = JT::order($orderItem->order_id);

			$replacement->checkin->attendee_name  = $order->name;
			$replacement->checkin->attendee_email = $order->email;
		}

		// Checkin details
		$replacement->checkin->attendee_name  = $replacement->checkin->attendee_name;
		$replacement->checkin->attendee_email = $toEmail['attendee_email'];
		$replacement->checkin->checkintime    = $utilities->getFormatedDate(isset($event->checkintime) ? $event->checkintime : $currentDateTime);
		$replacement->checkin->checkouttime   = $utilities->getFormatedDate(isset($event->checkouttime) ? $event->checkouttime : $event->enddate);
		$replacement->checkin->spent_time     = $spentTime;

		return $replacement;
	}

	/**
	 * Method to get checkin details from ticketId
	 *
	 * @param   int  $ticketId  ticketId
	 *
	 * @return  int
	 *
	 * @since  3.2.0
	 */
	public function getCheckinStatusByTicketId($ticketId)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('checkin');
		$query->from('#__jticketing_checkindetails');
		$query->where($db->quoteName('ticketid') . ' = ' . $db->quote($ticketId));
		$db->setQuery($query);

		return (!empty($db->loadResult())) ? 1 : 0;
	}
}
