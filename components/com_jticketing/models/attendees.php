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

if (file_exists(JPATH_SITE . '/components/com_jticketing/helpers/main.php')) { require_once JPATH_SITE . '/components/com_jticketing/helpers/main.php'; }
if (file_exists(JPATH_ADMINISTRATOR . '/components/com_users/models/user.php')) { require_once JPATH_ADMINISTRATOR . '/components/com_users/models/user.php'; }

use Joomla\CMS\Log\Log;
use Joomla\CMS\Factory;
use Joomla\CMS\User\User;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
/**
 * Methods supporting a list of Jticketing records.
 *
 * @since  2.1
 */
class JticketingModelAttendees extends ListModel
{
	public $jticketingmainhelper;

	protected $defaultUserGroup = 0;

	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @since   2.1
	 * @see     JController
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'owner_id', 'attendee.owner_id',
				'owner_email', 'attendee.owner_email',
				'entry_number', 'oitem.entry_number',
				'attendee_email', 'afv3.field_value',
				'notify', 'attendee.notify',
				'attended_status', 'chck.checkin',
				'status', 'ateendee.status',
				'title', 'events.title',
				'checkintime','chck.checkintime',
				'events', 'ticketId',
				'order.order_id'
			);
		}

		$this->jticketingmainhelper = new jticketingmainhelper;
		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 *
	 * @since   2.1
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// Initialise variables.
		$app = Factory::getApplication('administrator');

		// Load the filter state.
		$search = $app->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');

		$this->setState('filter.search', $search);

		$events = $app->getUserStateFromRequest($this->context . '.filter.events', 'filter_events');
		$this->setState('filter.events', $events);

		$attendedStatus = $app->getUserStateFromRequest($this->context . '.filter.attended_status', 'filter_attended_status');
		$this->setState('filter.attended_status', $attendedStatus);

		$status = $app->getUserStateFromRequest($this->context . 'filter.status', 'filter_status');
		$this->setState('filter.status', $status);

		$ownerID = $app->getUserStateFromRequest($this->context . '.filter.owner_id', 'filter_owner_id');
		$this->setState('filter.owner_id', $ownerID);

		// Load the parameters.
		$params = ComponentHelper::getParams('com_jticketing');
		$this->setState('params', $params);

		$listStart = $app->getUserStateFromRequest('limitstart', '', 'INT');

		$this->setState('list.start', $listStart);

		$listlimit = $app->getUserStateFromRequest('limit', '', 'INT');

		// Default pagination for first page load
		if ($listlimit == '' && $listlimit != '0')
		{
			$listlimit = 20;
		}

		$this->setState('list.limit', $listlimit);

		// List state information.
		parent::populateState('attendee.id', 'desc');
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 *
	 * @since   2.1
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db           = $this->getDbo();
		$query        = $db->getQuery(true);
		$logedInUser  = Factory::getUser();
		$canEnrollAll = $logedInUser->authorise('core.enrollall', 'com_jticketing');
		$integration  = JT::getIntegration(true);

		$columns = array('attendee.id', 'attendee.event_id', 'attendee.status', 'attendee.owner_id',
			'attendee.owner_email','attendee.enrollment_id', 'chck.checkin','chck.checkintime', 'user.firstname',
			'user.lastname', 'users.username', 'order.coupon_code', 'order.amount',
			'order.order_id','order.cdate', 'order.email', 'oitem.ticketcount', 'order.customer_note', 'oitem.entry_number'
		);

		$query->select($db->quoteName($columns));
		$query->select($db->quoteName('type.title', 'ticket_type_title'));
		$query->select($db->quoteName('order.status', 'order_status'));
		$query->select($db->quoteName('type.price', 'amount'));
		$query->select($db->quoteName('users.name', 'buyer_name'));
		$query->select($db->quoteName('users.email', 'buyer_email'));
		$query->select($db->quoteName('user.phone', 'buyer_phone'));
		$query->select('(type.price * oitem.ticketcount) AS totalamount');

		$query->from($db->quoteName('#__jticketing_attendees', 'attendee'));

		$query->join('LEFT', $db->quoteName('#__jticketing_integration_xref', 'intxref')
		. ' ON (' . $db->quoteName('intxref.id') . ' = ' . $db->quoteName('attendee.event_id') . ')');

		$query->join('LEFT', $db->qn('#__jticketing_checkindetails', 'chck') . 'ON (' . $db->qn('chck.attendee_id')
			. ' = ' . $db->qn('attendee.id') . ')');

		$query->join('LEFT', $db->qn('#__jticketing_order_items', 'oitem') . 'ON (' . $db->qn('oitem.attendee_id')
			. ' = ' . $db->qn('attendee.id') . ')');

		$query->join('LEFT', $db->qn('#__jticketing_order', 'order') . 'ON (' . $db->qn('order.id')
			. ' = ' . $db->qn('oitem.order_id') . ')');

		$query->join('LEFT', $db->qn('#__jticketing_users', 'user') . 'ON (' . $db->qn('user.order_id') . ' = ' . $db->qn('order.id') . ')');

		$query->join('LEFT', $db->qn('#__jticketing_types', 'type') . 'ON (' . $db->qn('type.id') . ' = ' . $db->qn('attendee.ticket_type_id') . ')');

		// Only add this query for the case attendee colletion is on
		$comParams          = ComponentHelper::getParams('com_jticketing');
		$attendeeCollection = $comParams->get('collect_attendee_info_checkout', false);

		if ($attendeeCollection)
		{
			$query->select($db->qn('afv1.field_value', 'fname'));
			$query->select($db->qn('afv2.field_value', 'lname'));
			$query->select($db->qn('afv3.field_value', 'attendee_email'));

			$query->join('LEFT', $db->qn('#__jticketing_attendee_field_values', 'afv1') .
				'ON (' . $db->qn('attendee.id') . ' = ' . $db->qn('afv1.attendee_id') .
				' AND ' . $db->qn('afv1.field_id') . ' = ' . $db->escape('1') .
				' AND ' . $db->qn('afv1.field_source') . ' = ' . $db->q('com_jticketing') .
				')');

			$query->join('LEFT', $db->qn('#__jticketing_attendee_field_values', 'afv2') .
				'ON (' . $db->qn('attendee.id') . ' = ' . $db->qn('afv2.attendee_id') .
				' AND ' . $db->qn('afv2.field_id') . ' = ' . $db->escape('2') .
				' AND ' . $db->qn('afv2.field_source') . ' = ' . $db->q('com_jticketing') .
				')');

			$query->join('LEFT', $db->qn('#__jticketing_attendee_field_values', 'afv3') .
				'ON (' . $db->qn('attendee.id') . ' = ' . $db->qn('afv3.attendee_id') .
				' AND ' . $db->qn('afv3.field_id') . ' = ' . $db->escape('4') .
				' AND ' . $db->qn('afv3.field_source') . ' = ' . $db->q('com_jticketing') .
				')');

			$query->join('LEFT', $db->qn('#__jticketing_attendee_fields', 'af') .
				'ON (' . $db->qn('afv1.field_id') . ' = ' . $db->qn('af.id') .
				' AND ' . $db->qn('afv2.field_id') . ' = ' . $db->qn('af.id') .
				' AND ' . $db->qn('afv3.field_id') . ' = ' . $db->qn('af.id') . ')');
		}

		// Integration selecting events
		if ($integration == 1)
		{
			// Jomsocial
			$query->select($db->quoteName('events.id', 'event_id'));
			$query->select($db->quoteName('events.title', 'title'));
			$query->select($db->quoteName('events.startdate', 'eventStartDate'));

			$query->join('INNER', $db->quoteName('#__community_events', 'events')
		. ' ON (' . $db->quoteName('events.id') . ' = ' . $db->quoteName('intxref.eventid') . ')');
		}
		elseif ($integration == 2)
		{
			// Native
			$query->select($db->quoteName('events.id', 'event_id'));
			$query->select($db->quoteName('events.title', 'title'));
			$query->select($db->quoteName('events.startdate', 'eventStartDate'));

			$query->join('LEFT', $db->quoteName('#__jticketing_events', 'events')
			. ' ON (' . $db->quoteName('events.id') . ' = ' . $db->quoteName('intxref.eventid') . ')');
			$query->select($db->quoteName('reccevent.r_id'));
			$query->join('LEFT', $db->quoteName('#__jticketing_recurring_events', 'reccevent')
				. ' ON (' . $db->quoteName('events.id') . ' = ' . $db->quoteName('reccevent.event_id') . ')');		
		}
		elseif ($integration == 3)
		{
			// Jevent
			$query->select($db->quoteName('jev.eventid', 'event_id'));
			$query->select($db->quoteName('events.summary', 'title'));

			$query->join('INNER', $db->quoteName('#__jevents_repetition', 'jev')
		. ' ON (' . $db->quoteName('jev.rp_id') . ' = ' . $db->quoteName('intxref.eventid') . ')');

			$query->join('INNER', $db->quoteName('#__jevents_vevdetail', 'events') . ' ON (' . $db->quoteName('events.evdet_id')
				. ' = ' . $db->quoteName('jev.eventdetail_id') . ')');
		}
		elseif ($integration == 4)
		{
			// Easy social
			$query->select($db->quoteName('events.id', 'event_id'));
			$query->select($db->quoteName('events.title', 'title'));

			$query->join('INNER', $db->quoteName('#__social_clusters', 'events')
		. ' ON (' . $db->quoteName('events.id') . ' = ' . $db->quoteName('intxref.eventid') . ')');
		}
		$query->select('IF(attendee.owner_id=0,order.name,users.name) as name');
		$query->join('LEFT', $db->quoteName('#__users', 'users') . 'ON ( ' . $db->quoteName('users.id') . '=' . $db->quoteName('attendee.owner_id') . ')');

		// Filter by search in title
		$search         = $this->getState('filter.search');
		$events         = $this->getState('filter.events');
		$attendedStatus = $this->getState('filter.attended_status');
		$status         = $this->getState('filter.status');
		$ownerID        = $this->getState('filter.owner_id');
		$ticketId       = $this->getState('filter_ticketId');

		if (!empty($search))
		{
			$search = $db->Quote('%' . $db->escape($search, true) . '%');

			if ($integration == 3)
			{
				$query->where('('.
					$db->quoteName('events.summary') . ' LIKE ' . $search . ' OR ' . $db->quoteName('order.name') . ' LIKE '
					. $search . ' OR ' . $db->quoteName('users.name') . ' LIKE ' . $search . ' OR '
					. $db->quoteName('attendee.owner_email') . ' LIKE ' . $search. ')'
				);
			}
			else
			{
				$conditions = $db->quoteName('events.title') . ' LIKE ' . $search . ' OR ' .
								$db->quoteName('order.name') . ' LIKE ' . $search . ' OR ' .
								$db->quoteName('order.order_id') . ' LIKE ' . $search . ' OR ' .
								$db->quoteName('users.name') . ' LIKE ' . $search . ' OR ' .
								$db->quoteName('attendee.enrollment_id') . ' LIKE ' . $search . ' OR ' .
								$db->quoteName('attendee.owner_email') . ' LIKE ' . $search;

				if ($attendeeCollection)
				{
					$conditions .= ' OR ' . $db->quoteName('afv1.field_value') . ' LIKE ' . $search .
									' OR CONCAT(' . $db->quoteName('afv1.field_value') . '," ",' . $db->quoteName('afv2.field_value') . ') LIKE ' . $search.
									' OR ' . $db->quoteName('afv2.field_value') . ' LIKE ' . $search;
				}

				$query->where('(' . $conditions . ')');
			}
		}

		if (!empty($attendedStatus))
		{
			if ($attendedStatus == 1)
			{
				$query->where($db->quoteName('chck.checkin') . '=' . $db->quote($attendedStatus));
			}
			elseif ($attendedStatus == 2)
			{
				$query->where('(' . $db->quoteName('chck.checkin') . '=' . $db->quote('0') . " OR " . $db->quoteName('chck.attendee_id') . ' is NULL)');
			}
		}

		if (!empty($status))
		{
			$query->where($db->quoteName('attendee.status') . '=' . $db->quote($status));
		}

		if (!empty($ownerID))
		{
			$query->where($db->quoteName('attendee.owner_id') . '=' . $db->quote($ownerID));
		}

		if (!empty($ticketId))
		{
			$query->where($db->quoteName('type.id') . '=' . $db->quote($ticketId));
		}

		// Add the list ordering clause.
		$orderCol  = $this->state->get('list.ordering');
		$orderDirn = $this->state->get('list.direction');

		// Integration searching and filtering
		if ($integration == 1)
		{
			// Jom social
			$query->where($db->quoteName('intxref.source') . ' = ' . $db->quote('com_community'));

			// If loged In user is admin show all events/enrollments
			if (!($logedInUser->authorise('core.admin') || $canEnrollAll))
			{
				$query->where($db->quoteName('events.creator') . ' = ' . (int) $logedInUser->id);
			}
		}
		elseif ($integration == 2)
		{
			// Native
			$query->where($db->quoteName('intxref.source') . '=' . $db->quote('com_jticketing'));

			// If loged In user is admin show all events/enrollments
			if (!($logedInUser->authorise('core.admin') || $canEnrollAll))
			{
				$query->where($db->quoteName('events.created_by') . ' = ' . (int) $logedInUser->id);
			}
		}
		elseif ($integration == 3)
		{
			// Jevent
			$query->where($db->quoteName('intxref.source') . ' = ' . $db->quote('com_jevents'));

			if ($orderCol == 'events.title')
			{
				$orderCol = 'events.summary';
			}

			// If loged In user is admin show all events/enrollments
			if (!($logedInUser->authorise('core.admin') || $canEnrollAll))
			{
				$query->join('LEFT', $db->qn('#__jevents_vevent', 'vevent') . 'ON (' . $db->qn('events.evdet_id') . ' = ' . $db->qn('vevent.detail_id') . ')');
				$query->where($db->quoteName('vevent.created_by') . ' = ' . (int) $logedInUser->id);
			}
		}
		elseif ($integration == 4)
		{
			// Easy social
			$query->where($db->quoteName('intxref.source') . '=' . $db->quote('com_easysocial'));

			// If loged In user is admin show all events/enrollments

			if (!($logedInUser->authorise('core.admin') || $canEnrollAll))
			{
				$query->where($db->quoteName('events.creator_uid') . ' = ' . (int) $logedInUser->id);
			}
		}

		if (!empty($events))
		{
			$callFromApi = $this->state->get('callFromApi', '');

			// If integratin is jomsocial then in api we send event id as community event id 
			if ($callFromApi)
			{
				$query->where($db->quoteName('events.id') . ' = ' . (int) $events);
			}
			else 
			{
				$query->where($db->quoteName('intxref.id') . ' = ' . (int) $events);
			}
		}

		if ($orderCol && $orderDirn)
		{
			if ($orderCol == 'oitem.entry_number')
			{
				if (!empty($events))
				{
					$query->order([
						$db->quoteName('oitem.type_id') . ' '. $orderDirn,
						$db->escape('CAST(' . $db->quoteName($orderCol) . ' AS UNSIGNED)') . ' '. $orderDirn
					]);
				}
				else
				{
					$query->order($db->escape('CAST(' . $orderCol . ' AS UNSIGNED) ' . $orderDirn));
				}
			}
			else
			{
				$query->order($db->escape($orderCol . ' ' . $orderDirn));		
			}
		}

		$query->group($db->quoteName('attendee.id'));
		
		return $query;
	}

	/**
	 * Email to selected attendees or Adds antries to jticketing queue which will be used later
	 *
	 * @param   ARRAY  $attendee_ids  attendee_ids
	 *
	 * @return  array
	 *
	 * @since   1.0
	 */
	public function getAttendeeEmail($attendee_ids)
	{
		if (file_exists(JPATH_SITE . '/components/com_jticketing/models/attendeeform.php')) { require_once JPATH_SITE . '/components/com_jticketing/models/attendeeform.php'; }
		$model = BaseDatabaseModel::getInstance('AttendeeForm', 'JticketingModel');
		$email_array = array();

		foreach ($attendee_ids AS $attendee_id)
		{
			$attendeeData  = $model->getItem($attendee_id);

			if (isset($attendeeData->owner_email))
			{
				$email_array[] = $attendeeData->owner_email;
			}
		}

		return array_unique($email_array);
	}

	/**
	 * Email to selected attendees or Adds antries to jticketing queue which will be used later
	 *
	 * @param   ARRAY   $cid             array of emails
	 * @param   string  $subject         subject of email
	 * @param   string  $message         message of email
	 * @param   string  $attachmentPath  Attachment path
	 *
	 * @return  boolean  true/false
	 *
	 * @since   1.0
	 */
	public function emailtoSelected($cid, $subject, $message, $attachmentPath = '')
	{
		JLoader::register('JticketingMailHelper', JPATH_SITE . '/components/com_jticketing/helpers/mail.php');
		$com_params   = JT::config();
		$replytoemail = $com_params->get('reply_to');

		$app      = Factory::getApplication();
		$mailfrom = $app->get('mailfrom', '', 'string');
		$fromname = $app->get('fromname', '', 'string');

		if (isset($replytoemail))
		{
			$replytoemail = explode(",", $replytoemail);
		}

		foreach ($cid AS $email)
		{
			// If order is deleted dont send reminder
			if (!$email)
			{
				continue;
			}

			$result = JticketingMailHelper::sendMail($mailfrom, $fromname, $email, $subject, $message, $html = 1, '', '', '', $replytoemail);
		}

		return $result;
	}

	/**
	 * Method to get an array of data items
	 *
	 * @return  mixed An array of data on success, false on failure.
	 */
	public function getItems()
	{
		if (file_exists(JPATH_SITE . '/components/com_jticketing/helpers/time.php')) { require_once JPATH_SITE . '/components/com_jticketing/helpers/time.php'; }
		$comParams                   = JT::config();
		$collectAttendeeInfoCheckout = $comParams->get('collect_attendee_info_checkout');
		$items                       = parent::getItems();
		$jticketingTimehelper        = new JticketingTimeHelper;
		$utilities    = JT::utilities();

		if (!empty($items) && $collectAttendeeInfoCheckout)
		{
			$eventIDs = array();

			foreach ($items as $item)
			{
				// Take attendee fields information for event specific fields
				if (!in_array($item->event_id, $eventIDs))
				{
					$eventIDs[] = $item->event_id;
					$extraFieldslabel[$item->event_id] = JT::model('attendeefields')->extraFieldslabel($item->event_id);
				}

				// Add extra fields label as column head.
				if (!empty($extraFieldslabel[$item->event_id]))
				{
					foreach ($extraFieldslabel[$item->event_id] as $extrafield)
					{
						foreach ($extrafield->attendee_value as $key => $value)
						{
							// Set field label for current ateendee field
							if ($item->id == $key)
							{
								$label = Text::_($extrafield->label);

								$item->$label = $value->field_value;
								break;
							}
						}
					}
				}
			}
		}

		foreach ($items as &$item)
		{
			if (empty($item->checkintime) || $item->checkintime == '0000-00-00 00:00:00')
			{
				$item->checkintime = ' - ';
			}
			else
			{
				$item->checkintime = $utilities->getFormatedDate($item->checkintime);
			}

			if (empty($item->cdate) || $item->cdate == '0000-00-00 00:00:00')
			{
				$item->cdate = ' - ';
			}
			else
			{
				$item->cdate = $utilities->getFormatedDate($item->cdate);
			}

			if ($comParams->get('enable_eventstartdateinname') && (property_exists($item, 'eventStartDate')))
			{
				$startDate   = $utilities->getFormatedDate($item->eventStartDate);
				$item->title = $item->title . '(' . $startDate . ')';
			}
		}

		return $items;
	}

	/**
	 * Function to get attendees
	 *
	 * @param   array  $options  filter array
	 *
	 * @return  Mixed|void object
	 *
	 * @since   2.1
	 */
	public function getAttendees($options = array())
	{
		$db    = Factory::getDbo();

		if (!is_array($options))
		{
			return;
		}

		$query = $db->getQuery(true);
		$query->select('*');
		$query->from($db->quoteName('#__jticketing_attendees'));

		if (isset($options['owner_id']))
		{
			$query->where($db->quoteName('owner_id') . ' = ' . $db->quote($options['owner_id']));
		}

		if (isset($options['event_id']))
		{
			$query->where($db->quoteName('event_id') . ' = ' . $db->quote($options['event_id']));
		}

		if (isset($options['status']))
		{
			$query->where($db->quoteName('status') . ' = ' . $db->quote($options['status']));
		}

		if (isset($options['limit']))
		{
			$query->setLimit((int) $options['limit']);
		}

		$db->setQuery($query);

		return $db->loadObjectList();
	}

	/**
	 * Method to return the array of all the attendee enrollment statuses
	 *
	 * @return  array array of all the statuses as key and their full forms as value
	 *
	 * @since  2.8.0
	 *
	 */
	public function getAttendeeActions()
	{
		$statues = array(COM_JTICKETING_CONSTANT_ATTENDEE_STATUS_PENDING,
				COM_JTICKETING_CONSTANT_ATTENDEE_STATUS_REJECTED,
				COM_JTICKETING_CONSTANT_ATTENDEE_STATUS_APPROVED,
				COM_JTICKETING_CONSTANT_ATTENDEE_STATUS_MOVE);

		$fullForms = array(Text::_('COM_JTICKETING_ENROLMENT_STATUS_PENDING'),
				Text::_('COM_JTICKETING_ENROLMENT_STATUS_REJECTED'),
				Text::_('COM_JTICKETING_ENROLMENT_STATUS_APPROVED'),
				Text::_('COM_JTICKETING_ENROLLMENT_STATUS_MOVE'));

		return array_combine($statues, $fullForms);
	}

	/**
	 * Generate valid enrollment statuses depending on the current status
	 *
	 * @param   string  $status       Enrollments status
	 * @param   array   $allStatuses  All Status array for options
	 *
	 * @return  array
	 *
	 * @since  2.8.0
	 *
	 */
	public function getValidAttendeeActions($status, $allStatuses)
	{
		// Get the move attendee config.
		$config             = JT::config();
		$moveAttendeeConfig = $config->get('enable_attendee_move');

		$unsetOrderStatus = array(
				"P"   => array (0 => "M"),
				"A"   => array (0 => "P"),
				"R"   => array (0 => "P"),
		);

		// In case the config is off then remove the move option in select list
		if (!$moveAttendeeConfig)
		{
			$unsetOrderStatus = array(
					"P"   => array (0 => "M"),
					"A"   => array (0 => "P", 1 => "M"),
					"R"   => array (0 => "P", 1 => "M"),
			);
		}

		// Unset the enrollment statuses which we do not want in the enrollment status array.
		foreach ($unsetOrderStatus as $key => $orderStatuses)
		{
			if ($key === $status)
			{
				foreach ($orderStatuses as $orderStatus)
				{
					// Unset the indexes
					unset($allStatuses[$orderStatus]);
				}
			}
		}

		return $allStatuses;
	}

	/**
	 * Create or update joomla user.
	 *
	 * @param   array  $userData  A record object formfield.
	 *
	 * @return  mixed  Return user id.
	 *
	 * @since   3.1.0
	 */

	public function createUpdateUser($userData)
	{
		$addGroups = empty($userData["addgroups"]) ? '' : $userData["addgroups"];
		$user           = new User($userData['id']);
		$userData['id'] = $user->id;

		$newUser = 0;

		if (empty($userData['id']))
		{
			$newUser = 1;

			$userData['groups'] = (array) $this->defaultUserGroup;

			if (!empty($addGroups))
			{
				$userData['groups'] = explode("|", $addGroups);
			}
		}

		if (!$newUser && !empty($userData['password']))
		{
			$userData['password2'] = $userData['password'];
		}

		$name     = $userData['firstname'] . ' ' . $userData['lastname'];
		$username = '';

		if ($newUser)
		{
			$username = empty($userData['username']) ? trim($userData['email']) : $userData['username'];
		}

		$userData['name']     = empty(trim($name)) ? $user->name : $name;
		$userData['username'] = empty($username) ? $user->username : $username;
		$userData['email']    = empty(trim($userData['email'])) ? $user->email : trim($userData['email']);

		if (!$newUser)
		{
			$userData['registerDate'] = $user->registerDate;
			$userData['lastvisitDate'] = $user->lastvisitDate;
		}

		$user = new User($userData['id']);

		if ($user->bind($userData))
		{
			if ($user->save())
			{
				if ($newUser)
				{
					Log::add(Text::sprintf("COM_JTICKETING_MANAGEENROLLMENTS_LOG_NEW_USER", $user->id, json_encode($user->groups)), Log::INFO, 'com_jticketing');
				}
				else
				{
					Log::add(Text::sprintf("COM_JTICKETING_MANAGEENROLLMENTS_LOG_UPDATED_USER", $user->id), Log::INFO, 'com_jticketing');
				}

				if (!$newUser)
				{
					$userId = $user->id;

					if (!empty($addGroups))
					{
						$groupIds = explode("|", $addGroups);

						foreach ($groupIds as $groupId)
						{
							try
							{
								UserHelper::addUserToGroup($userId, $groupId);
							}
							catch (Exception $e)
							{
								Log::add(
								Text::sprintf("COM_JTICKETING_MANAGEENROLLMENTS_LOG_USERGROUP_ADD_FAIL", $userData['email'], $groupId, $e->getMessage()
								), Log::ERROR, 'com_jticketing'
								);
							}
						}
					}

					if (!empty($userData["removegroups"]))
					{
						$groupIds = explode("|", $userData["removegroups"]);

						foreach ($groupIds as $groupId)
						{
							try
							{
								UserHelper::removeUserFromGroup($userId, $groupId);
							}
							catch (Exception $e)
							{
								Log::add(
								Text::sprintf("COM_JTICKETING_MANAGEENROLLMENTS_LOG_USERGROUP_REMOVE_FAIL",
								$userData['email'], $groupId, $e->getMessage()
								), Log::ERROR, 'com_jticketing'
								);
							}
						}
					}
				}

				return $user->id;
			}
		}

		Log::add(Text::sprintf("COM_JTICKETING_MANAGEENROLLMENTS_LOG_ERROR_NEW_USER", $user->getError(), $userData['email']), Log::ERROR, 'com_jticketing');

		return false;
	}

	/**
	 * Delete attendee
	 *
	 * @param   Array  &$pks  id of jticketing_attendee table to delete
	 *
	 * @return  boolean
	 *
	 * @since   1.0.0
	 */
	public function delete(&$pks)
	{
		foreach ($pks as $i => $id)
		{
			$attendee = JT::attendee($id);

			if (!$attendee->delete())
			{
				// Prune items that you can't change.
				unset($pks[$i]);

				return false;
			}
		}

		return true;
	}
}
