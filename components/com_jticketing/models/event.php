<?php
/**
 * @package     JTicketing
 * @subpackage  com_jticketing
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\User\User;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;
use Joomla\CMS\MVC\Model\FormModel;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

if (file_exists(JPATH_SITE . '/components/com_jticketing/events/event.php')) { require_once JPATH_SITE . '/components/com_jticketing/events/event.php'; }


/**
 * Model for getting event list
 *
 * @package     JTicketing
 * @subpackage  component
 * @since       1.0
 */
class JticketingModelEvent extends FormModel
{
	private $item = '';
	/**
	 * Method to populate state
	 *
	 * @return	void
	 *
	 * @since	1.6
	 */
	protected function populateState()
	{
		$app = Factory::getApplication('com_jticketing');

		// Load state from the request userState on edit or from the passed variable on default
		if (Factory::getApplication()->getInput()->get('layout') == 'edit')
		{
			$id = Factory::getApplication()->getUserState('com_jticketing.edit.event.id');
		}
		else
		{
			$id = Factory::getApplication()->getInput()->get('id');
			Factory::getApplication()->setUserState('com_jticketing.edit.event.id', $id);
		}

		$this->setState('event.id', $id);

		// Load the parameters.
		$params       = $app->getParams();
		$params_array = $params->toArray();

		if (isset($params_array['item_id']))
		{
			$this->setState('event.id', $params_array['item_id']);
		}

		$this->setState('params', $params);
	}

	/**
	 * Method to get an ojbect.
	 *
	 * @param   integer  $id  The id of the object to get.
	 *
	 * @return  mixed	Object on success, false on failure.
	 */
	public function &getData($id = null)
	{
		if (empty($this->item))
		{
			$this->item = false;

			if (empty($id))
			{
				$id = $this->getState('event.id');
			}

			if (!is_numeric($id) || $id <= 0)
			{
				return false;
			}

			BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_jticketing/models', 'eventform');
			$jtickeitngModelEventFrom = BaseDatabaseModel::getInstance('eventform', 'JticketingModel');
			$eventData = $jtickeitngModelEventFrom->getItem($id);

			$eventComFieldsData = FieldsHelper::getFields('com_jticketing.event', $eventData, true);
			$eventComFieldsCustomFields = array();

			if (!empty($eventComFieldsData))
			{
				foreach ($eventComFieldsData as $field)
				{
					$eventComFieldsCustomFields[$field->title] = $field;
				}

				$eventData->customField = $eventComFieldsCustomFields;
			}
		}

		if (!empty($eventData))
		{
			if ($eventData->venue == "0")
			{
				$eventData->event_address = $eventData->location;
			}
			else
			{
				$eventData->event_address = JT::model('venueform')->getItem($eventData->venue)->address;
			}
		}

		$url            = new Uri;
		$pageURL        = $url->toString();
		$redirectionUrl = base64_encode($pageURL);

		/*Event Book button condition checked here*/
		$jticketingEventsModel = BaseDatabaseModel::getInstance('Events', 'JticketingModel');
		$eventBookButtonDetails = $jticketingEventsModel->getTJEventDetails($eventData->id, $redirectionUrl);

		if (array_key_exists('buy_button', $eventBookButtonDetails))
		{
			$eventData->buy_link = $eventBookButtonDetails['buy_button_link'];
			$eventData->buy_button = $eventBookButtonDetails['buy_button'];
		}

		if (array_key_exists('enrol_button', $eventBookButtonDetails))
		{
			$eventData->enrol_link = $eventBookButtonDetails['enrol_link'];
			$eventData->enrol_button = $eventBookButtonDetails['enrol_button'];
		}

		if (array_key_exists('enrolled_button', $eventBookButtonDetails))
		{
			$eventData->enrolled_button = $eventBookButtonDetails['enrolled_button'];
		}

		if (array_key_exists('waitinglist_button', $eventBookButtonDetails))
		{
			$eventData->waitinglist_button_link = $eventBookButtonDetails['waitinglist_button_link'];
			$eventData->waitinglist_button = $eventBookButtonDetails['waitinglist_button'];
		}

		if (array_key_exists('waitlisted_button', $eventBookButtonDetails))
		{
			$eventData->waitlisted_button = $eventBookButtonDetails['waitlisted_button'];
		}

		if (array_key_exists('enroll_pending_button', $eventBookButtonDetails))
		{
			$eventData->enroll_pending_button = $eventBookButtonDetails['enroll_pending_button'];
		}

		if (array_key_exists('enroll_cancel_button', $eventBookButtonDetails))
		{
			$eventData->enroll_cancel_button = $eventBookButtonDetails['enroll_cancel_button'];
		}

		if (array_key_exists('viewTicket_button', $eventBookButtonDetails))
		{
			$eventData->viewTicket_button = $eventBookButtonDetails['viewTicket_button'];
		}

		$eventData->isboughtEvent = $eventBookButtonDetails['isboughtEvent'];

		// Get event organizer information
		if (file_exists(JPATH_SITE . '/components/com_jticketing/helpers/integrations.php')) { require_once JPATH_SITE . '/components/com_jticketing/helpers/integrations.php'; }
		$jTicketingIntegrationsHelper  = new JTicketingIntegrationsHelper;
		$eventData->organizerAvatar = JT::integration()->getUserAvatar($eventData->created_by);
		$eventData->organizerProfileUrl = JT::utilities()->getUserProfileUrl($eventData->created_by);

		$eventBookingStartdate = Factory::getDate(
			($eventData->booking_start_date != '0000-00-00 00:00:00') ?
			$eventData->booking_start_date :
			$eventData->created
		)->Format(Text::_('COM_JTICKETING_DATE_FORMAT_SHOW_BOOK_BTN'));

		$eventBookingEndDate = Factory::getDate(
			($eventData->booking_end_date != '0000-00-00 00:00:00') ?
			$eventData->booking_end_date :
			$eventData->enddate
		)->Format(Text::_('COM_JTICKETING_DATE_FORMAT_SHOW_BOOK_BTN'));

		$eventStartdate = Factory::getDate($eventData->startdate)->Format(Text::_('COM_JTICKETING_DATE_FORMAT_SHOW_BOOK_BTN'));
		$eventEndDate = Factory::getDate($eventData->enddate)->Format(Text::_('COM_JTICKETING_DATE_FORMAT_SHOW_BOOK_BTN'));
		$curr_date = Factory::getDate()->Format(Text::_('COM_JTICKETING_DATE_FORMAT_SHOW_BOOK_BTN'));

		/* Get Event Ticket type price here*/
		$getTicketTypes = JT::event($id, JT::getIntegration())->getTicketTypes();

		if (count($getTicketTypes) == 1)
		{
			foreach ($getTicketTypes as $ticketInfo)
			{
				$eventData->eventPriceMaxValue = $ticketInfo->price;
				$eventData->eventPriceMinValue = $ticketInfo->price;
			}
		}
		elseif(!empty($getTicketTypes))
		{
			$maxTicketPrice = -9999999;
			$minTicketPrice = 9999999;

			foreach ($getTicketTypes as $ticketInfo)
			{
				if ($ticketInfo->price > $maxTicketPrice)
				{
					$maxTicketPrice = $ticketInfo->price;
				}

				if ($ticketInfo->price < $minTicketPrice)
				{
					$minTicketPrice = $ticketInfo->price;
				}
			}

			$eventData->eventPriceMaxValue = $maxTicketPrice;
			$eventData->eventPriceMinValue = $minTicketPrice;
		}
		else
		{
			$eventData->eventPriceMaxValue = 1;
			$eventData->eventPriceMinValue = -1;
		}

		if ($eventBookingEndDate < $curr_date)
		{
			// Booking date is closed
			$eventData->eventBookStatus = -1;
		}
		elseif ($eventBookingStartdate > $curr_date)
		{
			// Booking not started
			$eventData->eventBookStatus = 1;
		}
		else
		{
			// Booking is started
			$eventData->eventBookStatus = 0;
		}

		if ($eventEndDate < $curr_date)
		{
			// Event end date is closed
			$eventData->eventStatus = -1;
		}
		elseif ($eventStartdate > $curr_date)
		{
			// Event not started
			$eventData->eventStatus = 1;
		}
		else
		{
			// Event is started
			$eventData->eventStatus = 0;
		}

		if ($eventData->id)
		{
			$attendeesModel = JT::model('attendees', array("ignore_request" => true));
			$attendeesModel->setState('filter.events', $eventData->id);
			$eventAttendeeInfo             = $attendeesModel->getItems();
			$eventData->eventAttendeeCount = count($eventAttendeeInfo);

			$eventAttendeeInfo = array_slice($eventAttendeeInfo, 0, 4);

			$table = User::getTable();

			foreach ($eventAttendeeInfo as $value)
			{
				if (!empty($value->owner_id) && $table->load($value->owner_id))
				{
					$value->avatar = JT::integration()->getUserAvatar($value->owner_id);
				}
			}

			$eventData->eventAttendeeInfo = $eventAttendeeInfo;
		}

		return $eventData;
	}

	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param   string  $type    The table type to instantiate
	 * @param   string  $prefix  A prefix for the table class name. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function getTable($type = 'Event', $prefix = 'JticketingTable', $config = array())
	{
		$this->addTablePath(JPATH_COMPONENT_ADMINISTRATOR . '/tables');

		return Table::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to check in an item.
	 *
	 * @param   integer  $id  The id of the row to check out.
	 *
	 * @return	boolean  True on success, false on failure.
	 *
	 * @since	1.6
	 */
	public function checkin($id = null)
	{
		// Get the id.
		$id = (!empty($id)) ? $id : (int) $this->getState('event.id');

		if ($id)
		{
			// Initialise the table
			$table = $this->getTable();

			// Attempt to check the row in.
			if (method_exists($table, 'checkin'))
			{
				if (!$table->checkin($id))
				{
					$this->setError($table->getError());

					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Method to check out an item.
	 *
	 * @param   integer  $id  The id of the row to check out.
	 *
	 * @return	boolean  True on success, false on failure.
	 *
	 * @since	1.6
	 */
	public function checkout($id = null)
	{
		// Get the user id.
		$id = (!empty($id)) ? $id : (int) $this->getState('event.id');

		if ($id)
		{
			// Initialise the table
			$table = $this->getTable();

			// Get the current user object.
			$user = Factory::getUser();

			// Attempt to check the row out.
			if (method_exists($table, 'checkout'))
			{
				if (!$table->checkout($user->get('id'), $id))
				{
					$this->setError($table->getError());

					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Method to get the record form.
	 *
	 * @param   string  $data      An optional array of data for the form to interogate.
	 * @param   string  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return	JForm	A JForm object on success, false on failure
	 *
	 * @since   1.0
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_jticketing.event', 'event', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return	mixed	The data for the form.
	 *
	 * @since	1.6
	 */
	protected function loadFormData()
	{
		$data = $this->getData();

		return $data;
	}

	/**
	 * Method to save form
	 *
	 * @param   string  $data  An optional array of data for the form to interogate.
	 *
	 * @return	mixed	A integer id on success, false on failure
	 *
	 * @since   1.0
	 */
	public function save($data)
	{
		$jteventHelper = new jteventHelper;
		PluginHelper::importPlugin('system');
		$result = Factory::getApplication()->triggerEvent('onBeforeJtEventCreate', array($data));
		$id     = (!empty($data['id'])) ? $data['id'] : (int) $this->getState('event.id');
		$state  = (!empty($data['state'])) ? 1 : 0;
		$user   = Factory::getUser();

		if ($id)
		{
			// Check the user can edit this item
			$authorised = $user->authorise('core.edit', 'com_jticketing') || $authorised = $user->authorise('core.edit.own', 'com_jticketing');

			// The user cannot edit the state of the item.
			if ($user->authorise('core.edit.state', 'com_jticketing') !== true && $state == 1)
			{
				$data['state'] = 0;
			}
		}
		else
		{
			// Check the user can create new items in this section
			$authorised = $user->authorise('core.create', 'com_jticketing');

			// The user cannot edit the state of the item.
			if ($user->authorise('core.edit.state', 'com_jticketing') !== true && $state == 1)
			{
				$data['state'] = 0;
			}
		}

		if ($authorised !== true)
		{
			$app = Factory::getApplication();
			$app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'error');
			
			return false;
		}

		$table = $this->getTable();

		if ($table->save($data) === true)
		{
			return $id;
		}
		else
		{
			return false;
		}

		$socialintegration = $com_params->get('integrate_with', 'none');
		$streamAddEvent    = $com_params->get('streamAddEvent', 0);

		if ($socialintegration != 'none')
		{
			$user       = Factory::getUser();
			$orderModel = JT::model('order');
			$libclass   = $orderModel->getJticketSocialLibObj();

			// Add in activity.
			if ($streamAddEvent)
			{
				$action      = 'addevent';
				$link = Uri::root() . substr(Route::_('index.php?option=com_jticketing&view=event&id=' . $id), strlen(Uri::base(true)) + 1);
				$eventLink   = '<a class="" href="' . $link . '">' . $data['title'] . '</a>';
				$originalMsg = Text::sprintf('COM_JTICKETING_ACTIVITY_ADD_EVENT', $eventLink);
				$libclass->pushActivity($user->id, $act_type = '', $act_subtype = '', $originalMsg, $act_link = '', $title = '', $act_access = 0);
			}
		}

		// TRIGGER After create event
		PluginHelper::importPlugin('system');
		$result = Factory::getApplication()->triggerEvent('onAfterJtEventCreate', array($data));

		// TRIGGER After create event
		$jtTriggerEvent = new JticketingTriggerEvent;
		$jtTriggerEvent->onAfterEventSave($data, true);
	}

	/**
	 * Method to delete event
	 *
	 * @param   string  $data  post data
	 *
	 * @return	JForm	A JForm object on success, false on failure
	 *
	 * @since   1.0
	 */
	public function delete($data)
	{
		$id = (!empty($data['id'])) ? $data['id'] : (int) $this->getState('event.id');

		if (Factory::getUser()->authorise('core.delete', 'com_jticketing') !== true)
		{
			$app = Factory::getApplication();
			$app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'error');
			
			return false;
		}

		$table = $this->getTable();

		if ($table->delete($data['id']) === true)
		{
			return $id;
		}
		else
		{
			return false;
		}

		// TRIGGER After create event
		PluginHelper::importPlugin('jticketing');

		// Old trigger
		$result = Factory::getApplication()->triggerEvent('onAfterJtDeleteEvent', array($data, $id));

		// Added new trigger
		Factory::getApplication()->triggerEvent('onAfterJtEventDelete', array($data));

		return true;
	}

	/**
	 * Method to get category name
	 *
	 * @param   string  $id  id of category
	 *
	 * @return	string  category name
	 *
	 * @since   1.0
	 */
	public function getCategoryName($id)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		if (isset($id))
		{
			$query->select('title')->from('#__categories')->where('id = ' . $id);
			$db->setQuery($query);
		}

		return $db->loadObject();
	}

	/**
	 * Method to get the form for extra fields.This form file will be created by field manager.
	 *
	 * @param   array  $id  An optional array of data for the form to interogate.
	 *
	 * @return	mixed  Array or false
	 *
	 * @since	1.6
	 */
	public function getDataExtra($id = null)
	{
		if (empty($id))
		{
			$id = (int) $this->getState('event.id');
		}

		if (empty($id))
		{
			return false;
		}

		$TjfieldsHelperPath = JPATH_SITE . '/components/com_tjfields/helpers/tjfields.php';

		if (!class_exists('TjfieldsHelper'))
		{
			JLoader::register('TjfieldsHelper', $TjfieldsHelperPath);
			JLoader::load('TjfieldsHelper');
		}

		$tjFieldsHelper = new TjfieldsHelper;
		$data               = array();
		$data['client']     = 'com_jticketing.event';
		$data['content_id'] = $id;
		$extra_fields_data = $tjFieldsHelper->FetchDatavalue($data);

		return $extra_fields_data;
	}

	/**
	 * Method to get ticket types
	 *
	 * @param   array  $id  An optional array of data for the form to interogate.
	 *
	 * @return	boolean|array
	 *
	 * @since	1.6
	 */
	public function GetTicketTypes($id = null, $allTicketTypes = false)
	{
		if (empty($id))
		{
			$id = $this->getState('event.id');
		}

		if (empty($id))
		{
			return false;
		}

		return JT::event($id, JT::getIntegration())->getTicketTypes($allTicketTypes);
	}

	/**
	 * Render booking HTML
	 *
	 * @param   int  $eventid  id of event
	 * @param   int  $userid   userid
	 *
	 * @return  array HTML
	 *
	 * @since   1.0
	 */
	public function renderBookingHTML($eventid,$userid='')
	{
		$path = JPATH_ROOT . '/components/com_jticketing/helpers/frontendhelper.php';

		if (!class_exists('Jticketingfrontendhelper'))
		{
			JLoader::register('Jticketingfrontendhelper', $path);
			JLoader::load('Jticketingfrontendhelper');
		}

		$path = JPATH_ROOT . '/components/com_jticketing/helpers/main.php';

		if (!class_exists('Jticketingmainhelper'))
		{
			JLoader::register('Jticketingmainhelper', $path);
			JLoader::load('Jticketingmainhelper');
		}

		$Jticketingfrontendhelper = new Jticketingfrontendhelper;
		$Jticketingmainhelper = new Jticketingmainhelper;
		$eventdata = JT::event($eventid);
		$HTML = $Jticketingfrontendhelper->renderBookingHTML($eventid, $userid, $eventdata);

		$HTML['online_html'] = '';

		if ($eventdata->isOnline())
		{
			if ($HTML['isboughtEvent'] || $eventdata->created_by == $userid)
			{
				$params = json_decode($eventdata->params, "true");

				// TRIGGER After create event
				PluginHelper::importPlugin('tjevents');
				$result = Factory::getApplication()->triggerEvent('onGenerateMeetingHTML', array($params,$eventdata));

				if (!empty($result['0']))
				{
					$HTML['online_html'] = $result['0'];
				}
			}
		}

		return $HTML;
	}

	/**
	 * Get video data - Added by Nidhi
	 *
	 * @param   integer  $vid   Video id
	 * @param   string   $type  Video provider e.g youtube, vimeo, upload
	 *
	 * @return   Object   video data
	 *
	 * since 1.7
	 */
	public function getVideoData($vid, $type)
	{
		if (!empty($vid) && !empty($type))
		{
			$db = Factory::getDbo();
			$query = $db->getQuery(true);
			$query->select("*");
			$query->from($db->quoteName("#__jticketing_media_files"));
			$query->where($db->quoteName('id') . ' = ' . $vid);
			$db->setQuery($query);
			$results = $db->loadObject();

			return $results;
		}
	}

	/**
	 * This is add google event
	 *
	 * @param   int  $eventDetails  event details
	 *
	 * @return  $url
	 *
	 * @since   1.0
	 */
	public function addGoogleEvent($eventDetails)
	{
		$eventName = $eventDetails->title;
		$formattedEventName = str_replace(' ', '+', $eventName);
		$startDateTime = new DateTime($eventDetails->startdate);
		$googleStartDate = $startDateTime->format(DateTime::ISO8601);
		$removeExtraStart = substr($googleStartDate, 0, -4);
		$formattedStartDate = preg_replace("/[^ \w]+/", "", $removeExtraStart) . "Z";
		$endDateTime = new DateTime($eventDetails->enddate);
		$googleEndDate = $endDateTime->format(DateTime::ISO8601);
		$removeExtraEnd = substr($googleEndDate, 0, -4);
		$formattedEndDate = preg_replace("/[^ \w]+/", "", $removeExtraEnd) . "Z";
		$description = strip_tags($eventDetails->long_description);
		// Check if the event is an online event
		if ($eventDetails->online_events)
		{
			// Get the join URL
			$location = $eventDetails->params['join_url'];
			$description .= " <br><br> Join with Link - ". $location;
		}
		else
		{
			// Get the offline event location
			$location = $eventDetails->location;
		}
		$url = "https://calendar.google.com/calendar/render?action=TEMPLATE&text=" .
		$formattedEventName . "&dates=" . $formattedStartDate . "/" . $formattedEndDate . "&details=" .
		$description . "&location=" . $location . "&pli=1&sf=true&output=xml#eventpage_6";

		return $url;
	}

	/**
	 * Method to get Graph Data
	 *
	 * @param   Integer  $durationVal  This show the duration for graph data
	 * @param   Integer  $eventId      This show the event id or user id
	 *
	 * @return  Json data
	 *
	 * @since  2.0
	 */
	public function getEventGarphData($durationVal, $eventId)
	{
		if ($durationVal == 0)
		{
			$graphDuration = 7;
		}
		elseif ($durationVal == 1)
		{
			$graphDuration = 30;
		}

		$todate = Factory::getDate(date('Y-m-d'))->Format(Text::_('Y-m-d'));

		$db = Factory::getDbo();
		$user = Factory::getUser();
		$query = $db->getQuery(true);

		if ($durationVal == 0 || $durationVal == 1)
		{
			$backdate = date('Y-m-d', strtotime(date('Y-m-d') . ' - ' . $graphDuration . ' days'));

			$query->select('SUM(o.amount) AS order_amount');
			$query->select('DATE(o.cdate) AS cdate');
			$query->select('COUNT(o.id) AS orders_count');
			$query->from($db->qn('#__jticketing_order', 'o'));
			$query->join('LEFT', $db->qn('#__jticketing_integration_xref', 'e') . ' ON (' . $db->qn('e.id') . ' = ' . $db->qn('o.event_details_id') . ')'
				);
			$query->where(
				$db->qn('e.eventid') . ' = ' . $db->quote($eventId) . ' AND ' .
				$db->qn('e.source') . ' = ' . $db->quote("com_jticketing") . ' AND ' .
				$db->qn('o.status') . ' = ' . $db->quote('C')
			);
			$query->where('DATE(' . $db->qn('o.cdate') . ')' . ' >= ' . $db->quote($backdate) . ' AND ' . 'DATE(' .
			$db->qn('o.cdate') . ')' . ' <= ' . $db->quote($todate)
			);
			$query->group('DATE(' . $db->qn('o.cdate') . ')');
			$query->order($db->qn('o.cdate') . 'DESC');

			$db->setQuery($query);
			$results = $db->loadObjectList();
		}
		elseif ($durationVal == 2)
		{
			$curdate    = date('Y-m-d');
			$back_year  = date('Y') - 1;
			$back_month = date('m') + 1;
			$backdate   = $back_year . '-' . $back_month . '-' . '01';

			$curdate    = date('Y-m-d');
			$back_year  = date('Y') - 1;
			$back_month = date('m') + 1;
			$backdate   = $back_year . '-' . $back_month . '-' . '01';

			$query->select('SUM(o.amount) AS order_amount');
			$query->select('MONTH(o.cdate) AS month_name');
			$query->select('YEAR(o.cdate) AS year_name');
			$query->select('COUNT(o.id) AS orders_count');
			$query->from($db->qn('#__jticketing_order', 'o'));
			$query->join('LEFT', $db->qn('#__jticketing_integration_xref', 'e') . ' ON (' . $db->qn('e.id') . ' = ' .
			$db->qn('o.event_details_id') . ')');

			$query->where(
				$db->qn('e.eventid') . ' = ' . $db->quote($eventId) . ' AND ' .
				$db->qn('e.source') . ' = ' . $db->quote("com_jticketing") . ' AND ' .
				$db->qn('o.status') . ' = ' . $db->quote('C')
				);

			$query->where('DATE(' . $db->qn('o.cdate') . ')' . ' >= ' . $db->quote($backdate) . ' AND ' . 'DATE(' .
			$db->qn('o.cdate') . ')' . ' <= ' . $db->quote($todate)
			);

			$query->group($db->quote('year_name'));
			$query->group('month_name');
			$query->order($db->quote('YEAR( o.cdate )') . 'DESC');
			$query->order($db->quote('MONTH( o.cdate )') . 'DESC');

			$db->setQuery($query);
			$results = $db->loadObjectList();
		}

		return $results;
	}

	/**
	 * Methode viewMoreAttendee
	 *
	 * @param   integer  $eventId           event id
	 * @param   integer  $jticketing_index  jticketing_index
	 *
	 * @return   Object  Donor data
	 *
	 * since 1.7
	 */
	public function viewMoreAttendee($eventId, $jticketing_index)
	{
		$jTicketingIntegrationsHelper  = new JTicketingIntegrationsHelper;
		$attendeesModel = JT::model('attendees', array("ignore_request" => true));
		$attendeesModel->setState('filter.events', $eventId);
		$attendeesModel->setState('list.start', $jticketing_index - 1);
		$attendeesModel->setState('list.limit', 10);
		$eventAttendeeInfo = $attendeesModel->getItems();
		$html = "";
		$eventAttendee_html_view = JT::utilities()->getViewPath('event', 'default_attendeelist');

		foreach ($eventAttendeeInfo as $this->eventAttendeeInfo)
		{
			$this->eventAttendeeInfo->avatar = JT::integration()->getUserAvatar($this->eventAttendeeInfo->owner_id);
			ob_start();
			include $eventAttendee_html_view;
			$html .= ob_get_contents();
			ob_end_clean();

			$jticketing_index ++;
		}

		$result                     = array();
		$result['jticketing_index'] = $jticketing_index;
		$result['records']          = $html;

		return $result;
	}

	/**
	 * Get field id and type
	 *
	 * @param   array  $msg           msg to send
	 * @param   int    $message_body  msg to send
	 *
	 * @return  void
	 */
	public function replaceCustomFields($msg, $message_body)
	{
		if (!empty($msg['customfields_ticket']))
		{
			foreach ($msg['customfields_ticket'] as $label_ticket => $value_ticket)
			{
				$message_body = str_replace("[" . $label_ticket . "]", $value_ticket, $message_body);
			}
		}

		if (!empty($msg['customfields_event']))
		{
			foreach ($msg['customfields_event'] as $label_event => $value_event)
			{
				$message_body = str_replace("[" . $label_event . "]", $value_event, $message_body);
			}
		}

		return $message_body;
	}
}
