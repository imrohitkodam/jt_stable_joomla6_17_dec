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
use Joomla\CMS\Log\Log;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Access\Access;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Component\ComponentHelper;

if (file_exists(JPATH_SITE . '/components/com_jticketing/models/tickettype.php')) { require_once JPATH_SITE . '/components/com_jticketing/models/tickettype.php'; }
if (file_exists(JPATH_SITE . '/components/com_jticketing/models/attendeeform.php')) { require_once JPATH_SITE . '/components/com_jticketing/models/attendeeform.php'; }
if (file_exists(JPATH_SITE . '/components/com_jticketing/models/eventform.php')) { require_once JPATH_SITE . '/components/com_jticketing/models/eventform.php'; }
if (file_exists(JPATH_SITE . '/components/com_jticketing/helpers/main.php')) { require_once JPATH_SITE . '/components/com_jticketing/helpers/main.php'; }
if (file_exists(JPATH_SITE . '/components/com_jticketing/helpers/common.php')) { require_once JPATH_SITE . '/components/com_jticketing/helpers/common.php'; }
if (file_exists(JPATH_SITE . '/components/com_jticketing/helpers/frontendhelper.php')) { require_once JPATH_SITE . '/components/com_jticketing/helpers/frontendhelper.php'; }
if (file_exists(JPATH_SITE . '/components/com_jticketing/helpers/mail.php')) { require_once JPATH_SITE . '/components/com_jticketing/helpers/mail.php'; }
if (file_exists(JPATH_SITE . '/components/com_jticketing/events/attendee.php')) { require_once JPATH_SITE . '/components/com_jticketing/events/attendee.php'; }
if (file_exists(JPATH_SITE . '/components/com_jticketing/helpers/event.php')) { require_once JPATH_SITE . '/components/com_jticketing/helpers/event.php'; }

/**
 * Methods supporting a list of Jticketing records.
 *
 * @since  2.1
 */
class JticketingModelEnrollment extends ListModel
{
	public $jtModelTickettype;

	public $jtTicketTypeModel;

	public $jtCommonHelper;

	public $jtEventHelper;

	public $jtAttendeesModel;

	public $jtFrontendHelper;

	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @since   2.1
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'uc.id',
				'name', 'uc.name',
				'status', 'uc.block',
				'username', 'uc.username',
				'groupfilter', 'uum.group_id',
				'subuserfilter'
			);
		}

		$this->jtModelTickettype = new JTicketingModelTickettype;
		$this->jtTicketTypeModel = new JTicketingModelTickettype;
		$this->jtCommonHelper    = new JticketingCommonHelper;
		$this->jtEventHelper     = new JteventHelper;
		$this->jtAttendeesModel  = new JticketingModelAttendeeForm;
		$this->jtFrontendHelper  = new Jticketingfrontendhelper;

		$lang         = Factory::getLanguage();
		$extension    = 'com_jticketing';
		$base_dir     = JPATH_ADMINISTRATOR;
		$language_tag = '';
		$reload       = true;
		$lang->load($extension, $base_dir, $language_tag, $reload);

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
		// List state information.
		parent::populateState('uc.username', 'asc');

		$app = Factory::getApplication();

		// Load the filter state.
		$search = $app->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$subUsers = $app->getUserStateFromRequest($this->context . '.filter.subuserfilter', 'filter_subuserfilter');

		$this->setState('filter.subuserfilter', $subUsers);

		// Filter for selected event
		$selectedEvents = $app->getUserStateFromRequest($this->context . '.filter.selected_events', 'selected_events', '', 'ARRAY');

		$this->setState('filter.selected_events', $selectedEvents);

		// Load the parameters.
		$params = ComponentHelper::getParams('com_jticketing');
		$this->setState('params', $params);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return	JDatabaseQuery
	 *
	 * @since	2.1
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db           = $this->getDbo();
		$query        = $db->getQuery(true);
		$user         = Factory::getUser();
		$integration  = JT::getIntegration(true);
		$canEnrollAll = $user->authorise('core.enrollall', 'com_jticketing');
		$canEnrollOwn = $user->authorise('core.enrollown', 'com_jticketing');

		if ($user->authorise('core.admin') || $canEnrollAll)
		{
			// Select the required fields from the table.
			$query->select(
					$this->getState(
							'list.select', 'distinct(uc.id), uc.name, uc.username, uc.block'
					)
			);

			$query->from('`#__users` AS uc');
		}
		elseif ($canEnrollOwn)
		{
			$query->select(
					$this->getState(
							'list.select', 'uc.id, uc.name, uc.username'
					)
			);

			// As per integration
			if ((int) $integration === 1)
			{
				$query->from('`#__community_events` AS events');
				$query->join('INNER', $db->quoteName('#__jticketing_integration_xref', 'xref')
					. ' ON (' . $db->quoteName('xref.eventid') . ' = ' . $db->quoteName('events.id') . ')');

				$query->where($db->quoteName('xref.source') . ' = ' . $db->quote('com_jticketing'));
				$query->where($db->quoteName('events.creator') . ' = ' . $db->quote($user->id));
			}
			elseif ((int) $integration === 2)
			{
				$query->from('`#__jticketing_events` AS events');

				$query->join('INNER', $db->quoteName('#__jticketing_integration_xref', 'xref')
					. ' ON (' . $db->quoteName('xref.eventid') . ' = ' . $db->quoteName('events.id') . ')');

				$query->where($db->quoteName('xref.source') . ' = ' . $db->quote('com_jticketing'));
				$query->where($db->quoteName('events.created_by') . ' = ' . $db->quote($user->id));
			}
			elseif((int) $integration === 3)
			{
				$query->from($db->quoteName('#__jevents_vevdetail', 'events'));
				$query->join('INNER', $db->quoteName('#__jevents_vevent', 'jev') . ' ON (' . $db->quoteName('jev.detail_id')
				. ' = ' . $db->quoteName('events.evdet_id') . ')');
				$query->where($db->quoteName('events.state') . ' = 1');

				$query->join('INNER', $db->quoteName('#__jticketing_integration_xref', 'xref')
					. ' ON (' . $db->quoteName('xref.eventid') . ' = ' . $db->quoteName('events.evdet_id') . ')');

				$query->where($db->quoteName('xref.source') . ' = ' . $db->quote('com_jevents'));
				$query->where($db->quoteName('jev.created_by') . ' = ' . $db->quote($user->id));
			}
			elseif((int) $integration === 4)
			{
				$query->from('`#__social_clusters` AS events');

				$query->join('INNER', $db->quoteName('#__jticketing_integration_xref', 'xref')
					. ' ON (' . $db->quoteName('xref.eventid') . ' = ' . $db->quoteName('events.id') . ')');

				$query->where($db->quoteName('xref.source') . ' = ' . $db->quote('com_jticketing'));
				$query->where($db->quoteName('events.creator_uid') . ' = ' . $db->quote($user->id));
			}

			$query->join('INNER', $db->quoteName('#__jticketing_attendees', 'attendees')
					. ' ON (' . $db->quoteName('attendees.event_id') . ' = ' . $db->quoteName('xref.id') . ')');

			$query->join('INNER', $db->quoteName('#__users', 'uc')
					. ' ON (' . $db->quoteName('uc.id') . ' = ' . $db->quoteName('attendees.owner_id') . ')');

			$query->where($db->quoteName('uc.block') . ' = 0');
			$query->group($db->quoteName('uc.id'));
		}

		// Filter by search in title
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('uc.id = ' . (int) substr($search, 3));
			}
			else
			{
				$search = $db->Quote('%' . $db->escape($search, true) . '%');
				$query->where('(( uc.name LIKE ' . $search . ' ) OR ( uc.username LIKE ' . $search . ' ) OR ( uc.id LIKE ' . $search . ' ))');
			}
		}

		$subUsers = $this->getState('filter.subuserfilter');

		if ($subUsers == 1)
		{
			if (file_exists(JPATH_ADMINISTRATOR . '/components/com_jticketing/helpers/jticketing.php')) { require_once JPATH_ADMINISTRATOR . '/components/com_jticketing/helpers/jticketing.php'; }
			$hasUsers = JticketingHelper::getSubusers();

			if (!$hasUsers)
			{
				$hasUsers = array(0);
			}

			$query->where($db->qn('uc.id') . 'IN(' . implode(',', $db->q($hasUsers)) . ')');
		}

		// Add the list ordering clause.
		$orderCol = $this->state->get('list.ordering');
		$orderDirn = $this->state->get('list.direction');

		if ($orderCol && $orderDirn)
		{
			$query->order($db->escape($orderCol . ' ' . $orderDirn));
		}

		return $query;
	}

	/**
	 * To get the records
	 *
	 * @return  Object
	 *
	 * @since  2.1
	 */
	public function getItems()
	{
		$items = parent::getItems();

		// Get a db connection.
		$db	= Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName(array('title', 'id')));
		$query->from($db->quoteName('#__usergroups'));
		$db->setQuery($query);
		$groups = $db->loadAssocList('id', 'title');

		foreach ($items as $k => $obj)
		{
			$userGroups = Access::getGroupsByUser($obj->id, false);
			$userGroups = array_flip($userGroups);
			$group 		= array_intersect_key($groups, $userGroups);
			$items[$k]->groups = implode('<br />', $group);
		}

		return $items;
	}

	/**
	 * Function to drive enrollment
	 *
	 * @param   Array  $data  enrollment data.
	 *
	 * @return  mixed
	 *
	 * @since   2.1
	 */
	public function save($data)
	{
		$user = Factory::getUser();

		// Get Jticketing config/params
		$jtParams = ComponentHelper::getParams('com_jticketing');

		/*
			Extract has following params :
			$id, $eventId, $userId, $emailId, $ticketId, $notify, $approve, $oldAttendeeId, $move.
		*/

		extract($data);

		// Permission check for self enrollment & manage enrollment cases
		$permissionCheck = $this->permissionCheck($user, $userId, $eventId);

		if (!$permissionCheck)
		{
			$this->setError(Text::_('COM_JTICKETING_NOT_AUTHORIED'));

			return false;
		}

		if (empty($eventId) || empty($userId))
		{
			$this->setError(Text::_('COM_JTICKETING_ERROR_REQUIRED_VARIABLE_EMPTY'));

			return false;
		}

		// Check is all need config are set
		$config = $this->checkEnrollmentConfigs();

		if (!$config && !isset($data['move']))
		{
			$this->setError(Text::_('COM_JTICKETING_ERROR_ENABLE_CLASSROOM_TRAINING_SETTING'));

			return false;
		}

		// Check is user already enrolled
		if (isset($id) && (int) $id)
		{
			// When we update the enrollment
			$isEnrolled = 0;
		}
		else
		{
			// When we insert the enrollment
			$isEnrolled = $this->isAlreadyEnrolled($eventId, $userId);
		}

		$move = isset($data['move']) ? $data['move'] : false;
		if ($isEnrolled && !$move)
		{
			$this->setError(Text::_('COM_JTICKETING_ERROR_ALREADY_ENROLLED'));

			// Returning 2 for already enrolled users.
			return 2;
		}

		// Get event xref id to store in enrollment / attendee table
		$integration = JT::getIntegration();
		$xrefId      = JT::event($eventId, $integration)->integrationId;

		if (empty($xrefId))
		{
			$this->setError(Text::_('COM_JTICKETING_ERROR_SOMETHING_IS_WRONG_WITH_INTEGRATION'));

			return false;
		}

		// Check for ticket avalability & If user passed ticket id ignore this step other wise provide first ticket
		if (!isset($ticketId) || !is_numeric($ticketId) || empty($ticketId))
		{
			// Xref id as paramter to get ticket type id
			$ticketId = $this->getTicketTypeId($xrefId);

			if (empty($ticketId))
			{
				return false;
			}
		}
		else
		{
			// Check for avaible tickets of passed ticket id
			$ticketCountDetails = $this->jtTicketTypeModel->getItem($ticketId);

			if (!($ticketCountDetails->unlimited_seats || $ticketCountDetails->count >= 1))
			{
				$this->setError(Text::_('COM_JTICKETING_ERROR_NOT_ENOUGH_TICKETS'));

				return false;
			}
		}

		// Setting up data to store
		$enrollmentData = array();

		if (isset($id) && (int) $id)
		{
			$enrollmentData['id'] = $id;
		}

		// This is xref event id
		$enrollmentData['event_id'] = $xrefId;

		// This is actual event id
		$enrollmentData['eventId']  = $eventId;

		$enrollmentData['owner_id'] = $userId;

		if (isset($ticketId) && (int) $ticketId )
		{
			$enrollmentData['ticket_type_id'] = $ticketId;
		}

		$email = Factory::getUser($userId)->email;

		$enrollmentData['owner_email'] = $email;

		if (isset($userEmail))
		{
			$enrollmentData['owner_email'] = $userEmail;
		}

		if (!empty($notify))
		{
			$enrollmentData['notify'] = true;
		}
		else
		{
			$enrollmentData['notify'] = false;
		}

		// If enrollment status is passed then use this otherwise user according to the permission/ Config
		if (isset($status))
		{
			$enrollmentData['status'] = $status;

			if ($status === "" || $status === 0)
			{
				$enrollmentData['status'] = 'P';
			}
		}
		else
		{
			$approval  = (int) $jtParams->get('enable_enrollment_approval') === 1;
			$loginUser = ((int) $user->id === (int) $userId);
			$enrollAll = $user->authorise('core.enrollall', 'com_jticketing');

			// If 'mass enrollment', approval  status will be 'A' always
			if ($approval && $loginUser && !$enrollAll)
			{
				$enrollmentData['status'] = 'P';
			}
			else
			{
				$enrollmentData['status'] = 'A';
			}
		}

		// Save enrollment
		$response   = $this->jtAttendeesModel->save($enrollmentData);
		$oldEventId = Factory::getApplication()->getUserState('attendee.oldEventId');

		// If old event id is set then its move attendee request so migrate the old attnedee data to new attendee id.
		if (!empty($oldEventId) && $response !== false && !empty($oldAttendeeId))
		{
			// Get all the attendee fields data.
			$oldAttendeeData       = JT::AttendeeFieldValues()->loadByAttendeeId($oldAttendeeId, '');
			$oldAttendeeFieldsData = JT::AttendeeFieldValues()->loadByAttendeeId($oldAttendeeId, '', 'com_tjfields.com_jticketing.ticket');

			// Merge the tjfield and jticketing data.
			if (!empty($oldAttendeeFieldsData))
			{
				$oldAttendeeData = array_merge($oldAttendeeData, $oldAttendeeFieldsData);
			}

			// Update all attendee fields values to new attendee id.
			foreach ($oldAttendeeData as $edata)
			{
				$edata->attendee_id = $response;
				$edata->save();
			}

			// Delete the old attendee record.
			$oldAttendeeObj = JT::attendee($oldAttendeeId);
			$ticketData     = JT::tickettype($oldAttendeeObj->ticket_type_id);

			if (!$ticketData->unlimited_seats)
			{
				$ticketData->count++;
				$ticketData->save();
			}

			$oldAttendeeObj->delete();
		}

		if ($response)
		{
			$enrollmentData['attendee_id'] = $response;

			// Get event details as per integration.
			$eventDetails = JT::event()->loadByIntegration($enrollmentData['event_id']);

			if ($eventDetails->online_events == 0)
			{
				// Check notify is true or false to send email notification
				if ($enrollmentData['notify'] == true)
				{
					JticketingMailHelper::enrollmentNotificationMail($enrollmentData['event_id'], $enrollmentData['attendee_id']);
				}
			}

			if ($enrollmentData['status'] === "A" || $enrollmentData['status'] === "a")
			{
				// Attendee entity is used in attendee and enrollment context.
				$jtEnrollmentTrigger = new JticketingTriggerAttendee;
				$jtEnrollmentTrigger->onAfterEnrollmentStatusChange($enrollmentData);

				if (!empty($jtEnrollmentTrigger->error))
				{
					$this->setError($jtEnrollmentTrigger->error);

					return false;
				}
			}

			return true;
		}
	}

	/**
	 * Function to Check is user already enrolled
	 *
	 * @param   INT  $eventId  event id.
	 *
	 * @param   INT  $userId   user id.
	 *
	 * @return  Boolean
	 *
	 * @since   2.1
	 */
	public function isAlreadyEnrolled($eventId, $userId)
	{
		$db          = Factory::getDbo();
		$integration = JT::getIntegration();
		$xrefId      = JT::event($eventId, $integration)->integrationId;

		$query = $db->getQuery(true);
		$query->select('*');
		$query->from($db->quoteName('#__jticketing_attendees'));
		$query->where($db->quoteName('owner_id') . ' = ' . $db->quote($userId));
		$query->where($db->quoteName('event_id') . ' = ' . $db->quote($xrefId));
		$db->setQuery($query);
		$enrolledUser = $db->loadObject();

		if (!empty($enrolledUser))
		{
			if ($enrolledUser->status == 'A')
			{
				return 1;
			}
			elseif ($enrolledUser->status == 'P')
			{
				return 2;
			}
			elseif($enrolledUser->status == 'R')
			{
				return 3;
			}
		}

		return false;
	}

	/**
	 * Function to Check componet configurations & event level configs
	 *
	 * @return  Boolean
	 *
	 * @since   2.1
	 */
	public function checkEnrollmentConfigs()
	{
		$jtParams = ComponentHelper::getParams('com_jticketing');

		if ((int) $jtParams->get('enable_self_enrollment') !== 1)
		{
			return false;
		}

		return true;
	}

	/**
	 * Function to check Permission for enrollments
	 *
	 * @param   OBJECT  $user     Object of assigned by
	 * @param   INT     $userId   ID of assinged to
	 * @param   INT     $eventId  Event id
	 *
	 * @return  Boolean
	 *
	 * @since   2.1
	 */
	public function permissionCheck($user, $userId, $eventId)
	{
		$app          = Factory::getApplication();
		$assignee     = Factory::getUser($userId);
		$canEnrollAll = $user->authorise('core.enrollall', 'com_jticketing');
		$canEnrollOwn = $user->authorise('core.enrollown', 'com_jticketing');
		$selfEnroll   = $assignee->authorise('core.enroll', 'com_jticketing');
		$event        = JT::event($eventId);

		// Check for component level permission
		if ($app->isClient('site'))
		{
			if ((!empty($selfEnroll) && ($assignee->id == $user->id)) || !empty($canEnrollAll) || (!empty($canEnrollOwn) && ($event->created_by == $user->id)))
			{
				return true;
			}
		}
		else
		{
			if (!empty($selfEnroll))
			{
				return true;
			}
		}

		// Get current Integrations
		$comParams = ComponentHelper::getParams('com_jticketing');
		$integration = $comParams->get('integration');

		// If integration is native check event level permission
		if ($integration == 2)
		{
			$enroll = $assignee->authorise('core.enroll', 'com_jticketing.event.' . $eventId);

			// Check for Event level permission for enroll
			if ($app->isClient('site'))
			{
				if (!empty($enroll) && ($assignee->id == $user->id))
				{
					return true;
				}
			}
			else
			{
				if (!empty($selfEnroll))
				{
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Function to get ticket type id
	 *
	 * @param   INT  $xrefId  xref id
	 *
	 * @return  Mixed
	 *
	 * @since   2.1
	 */
	public function getTicketTypeId($xrefId)
	{
		$tickets = JT::event()->loadByIntegration($xrefId)->getTicketTypes();

		if (!empty($tickets))
		{
			$ticketTypeId = '';

			foreach ($tickets as $ticket)
			{
				$ticketCountDetails = $this->jtTicketTypeModel->getItem($ticket->id);

				// Check for avaible tickets
				if ($ticketCountDetails->unlimited_seats || $ticketCountDetails->count >= 1)
				{
					// If we get any tickets avaiable then use that

					$ticketTypeId = $ticket->id;
					break;
				}
			}

			if (empty($ticketTypeId))
			{
				$this->setError(Text::_('COM_JTICKETING_ERROR_NOT_ENOUGH_TICKETS'));

				return false;
			}

			return $ticketTypeId;
		}
		else
		{
			$this->setError(Text::_('COM_JTICKETING_ERROR_TICKET_NOT_AVAILABLE'));

			return false;
		}
	}

	/**
	 * Function to update enrollment approval
	 *
	 * @param   ARRAY  $data  enrollment data
	 *
	 * @return  void
	 *
	 * @since   2.1
	 */
	public function update($data)
	{
		$app                  = Factory::getApplication();
		$user                 = Factory::getUser();
		$jtEnrollmentTrigger  = new JticketingTriggerAttendee;

		$db           = $this->getDbo();
		$query        = $db->getQuery(true);

		$query->select($db->quoteName('order.order_id'));
		$query->select($db->quoteName('order.status', 'order_status'));
		$query->from($db->quoteName('#__jticketing_attendees', 'attendee'));

		$query->join('LEFT', $db->qn('#__jticketing_order_items', 'oitem') . 'ON (' . $db->qn('oitem.attendee_id')
			. ' = ' . $db->qn('attendee.id') . ')');

		$query->join('LEFT', $db->qn('#__jticketing_order', 'order') . 'ON (' . $db->qn('order.id')
			. ' = ' . $db->qn('oitem.order_id') . ')');
		$query->where($db->quoteName('attendee.id') . ' = ' . (int) $data['id']);
		$db->setQuery($query);

		$attndeeOrderDetails = $db->loadObject();

		$isOrderPresent = $attndeeOrderDetails->order_id ? true : false;
		if ($isOrderPresent && $attndeeOrderDetails->order_status == COM_JTICKETING_CONSTANT_ORDER_STATUS_INCOMPLETE)
		{
			$app->enqueueMessage(Text::_('COM_JTICKETING_ENROLLMENT_SOMETHING_IS_WRONG'), 'error');

			return false;
		}

		$beforeEnrollmentDetails = $this->jtAttendeesModel->getItem((int) $data['id']);

		// Call attendee's model to update enrollment entries
		$response = $this->jtAttendeesModel->save($data);

		if ($response)
		{
			// Attendee entity is used in attendee and enrollment context.
			$enrollmentDetails                = $this->jtAttendeesModel->getItem($data['id']);

			// Get actual event id from xref
			$eventId = JT::event()->loadByIntegration($enrollmentDetails->event_id)->id;

			$enrollmentData                   = array();
			$enrollmentData['ticket_type_id'] = $enrollmentDetails->ticket_type_id;

			// EventId is actual event id
			$enrollmentData['eventId']        = $eventId;

			// Event_id is xref id
			$enrollmentData['event_id']    = $enrollmentDetails->event_id;
			$enrollmentData['owner_id']    = $enrollmentDetails->owner_id;
			$enrollmentData['status']      = $enrollmentDetails->status;
			$enrollmentData['notify']      = $data['notify'];
			$enrollmentData['attendee_id'] = $data['id'];

			// Set old status varible to identify status change.
			if (!empty($beforeEnrollmentDetails))
			{
				$enrollmentData['old_status'] = $beforeEnrollmentDetails->status;
			}

			// Triggers
			$jtEnrollmentTrigger->onAfterEnrollmentStatusChange($enrollmentData);

			if ($jtEnrollmentTrigger->error)
			{
				$app->enqueueMessage($jtEnrollmentTrigger->error, 'Error');

				return  false;
			}
			else
			{
				if (strtoupper($data['status']) == 'A')
				{
					$app->enqueueMessage(Text::_('COM_JTICKETING_ENROLLMENT_APPROVAL_SUCCESS'), 'success');
				}
				elseif(strtoupper($data['status']) == 'P')
				{
					$app->enqueueMessage(Text::_('COM_JTICKETING_ENROLLMENT_APPROVAL_PENDING'), 'success');
				}
				elseif (strtoupper($data['status']) == 'R')
				{
					$app->enqueueMessage(Text::_('COM_JTICKETING_ENROLLMENT_APPROVAL_REJECTED'), 'success');
				}

				return true;
			}
		}
		else
		{
			$app->enqueueMessage(Text::_('COM_JTICKETING_ENROLLMENT_SOMETHING_IS_WRONG'), 'error');

			return false;
		}
	}

	/**
	 * Function to write failure log for enrollments.
	 *
	 * @param   Array   $exceptions              exceptions
	 *
	 * @param   string  $enrollmentTitleMessage  First line of log
	 *
	 * @return  void
	 *
	 * @since   2.1
	 */
	public function writeEnrollmentLog($exceptions, $enrollmentTitleMessage)
	{
		$user        = Factory::getUser();
		$config      = Factory::getConfig();
		$session     = Factory::getSession();
		$logFileName = Date::getInstance() . '-' . $user->id . '-' . mt_rand();

		// Set log file name to session
		$session->set('filename' . $user->id, $logFileName);

		Log::addLogger(
						array('text_file' => $logFileName . '.txt', 'text_file_path' => $config->get('tmp_path')
							, 'text_entry_format' => '{DATETIME} {MESSAGE}' ), Log::ALL, array('com_jticketing')
		);

		// Write starting of log file
		Log::add($enrollmentTitleMessage, Log::ERROR, 'com_jticketing');

		if (count($exceptions))
		{
			foreach ($exceptions as $exception)
			{
				// Write this to file.
				Log::add(Text::sprintf($exception, $user->id), Log::ERROR, 'com_jticketing');
			}
		}
	}

	/**
	 * Get recommend user for the course.
	 *
	 * @param   int  $courseId  id of course
	 * @param   int  $userId    id of user
	 *
	 * @return  ARRAY $record
	 *
	 * @since   3.2.0
	 */
	public function getuserRecommendedUsers($courseId, $userId)
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('lr.assigned_to');
		$query->from('`#__jlike_todos` as lr');
		$query->join('INNER', '`#__jlike_content` as lc ON lc.id=lr.content_id');
		$query->where('lr.assigned_by=' . (int) $userId);
		$query->where('lr.type="reco"');
		$query->where('lc.element_id=' . (int) $courseId . ' LIMIT 0,5');

		// Set the query for execution.
		$db->setQuery($query);

		$recommendedusers = $db->loadColumn();
		$orderModel       = JT::model('order');

		foreach ($recommendedusers as $index => $recommend_userid)
		{
			$this->sociallibraryobj   = $orderModel->getJticketSocialLibObj();
			$recommendedusers[$index] = new stdClass;
			$student = Factory::getUser($recommend_userid);
			$recommendedusers[$index]->username = Factory::getUser($recommend_userid)->username;
			$recommendedusers[$index]->name = Factory::getUser($recommend_userid)->name;
			$recommendedusers[$index]->avatar = $this->sociallibraryobj->getAvatar($student, 50);

			$link = '';

			if ($this->sociallibraryobj->getProfileUrl($student))
			{
				$link = Uri::root() . substr(Route::_($this->sociallibraryobj->getProfileUrl($student)), strlen(Uri::base(true)) + 1);
			}

			$recommendedusers[$index]->profileurl = $link;
		}

		return $recommendedusers;
	}
}
