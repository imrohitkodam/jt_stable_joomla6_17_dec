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
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

if (file_exists(JPATH_SITE . '/components/com_jticketing/helpers/main.php')) { require_once JPATH_SITE . '/components/com_jticketing/helpers/main.php'; }

/**
 * Methods supporting a list of Jticketing records.
 *
 * @since  2.2
 */
class JTicketingModelWaitinglist extends ListModel
{
	public $jticketingmainhelper;

	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @since   2.2
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
			'username', 'waitlist.username',
			'name', 'waitlist.name',
			'behaviour', 'waitlist.behaviour',
			'status', 'waitlist.status',
			'title', 'events.title',
			'waitinglist_status','events'
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

		$waitinglistStatus = $app->getUserStateFromRequest($this->context . '.filter.waitinglist_status', 'filter_waitinglist_status');
		$this->setState('filter.waitinglist_status', $waitinglistStatus);

		$behaviourStatus = $app->getUserStateFromRequest($this->context . '.filter.behaviour', 'filter_behaviour');
		$this->setState('filter.behaviour', $behaviourStatus);

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
		parent::populateState('waitlist.id', 'desc');
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 *
	 * @since   2.2
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db          = $this->getDbo();
		$query       = $db->getQuery(true);
		$logedInUser = Factory::getUser();
		$integration = JT::getIntegration(true);

		$columns = array('waitlist.id', 'waitlist.status', 'waitlist.user_id',
			'waitlist.behaviour', 'users.username', 'users.name'
			);

		$query->select($db->quoteName($columns));
		$query->select($db->quoteName('waitlist.event_id', 'waitlist_event_id'));
		$query->from($db->quoteName('#__jticketing_waiting_list', 'waitlist'));
		$query->join('LEFT', $db->quoteName('#__jticketing_integration_xref', 'intxref')
		. ' ON (' . $db->quoteName('intxref.id') . ' = ' . $db->quoteName('waitlist.event_id') . ')');

		$query->join('LEFT', $db->quoteName('#__users', 'users') . 'ON ( ' . $db->quoteName('users.id') . '=' . $db->quoteName('waitlist.user_id') . ')');

		// Filter by search in title
		$search         = $this->getState('filter.search');
		$events         = $this->getState('filter.events');
		$behaviour      = $this->getState('filter.behaviour');
		$waitlistStatus = $this->getState('filter.waitinglist_status');

		if (!empty($search))
		{
			$search = $db->Quote('%' . $db->escape($search, true) . '%');

			if ($integration == 3)
			{
				$query->where(
				$db->quoteName('events.summary') . ' LIKE ' . $search . ' OR ' . $db->quoteName('users.username') . ' LIKE '
				. $search . ' OR ' . $db->quoteName('users.name') . ' LIKE ' . $search
				);
			}
			else
			{
				$query->where(
				$db->quoteName('events.title') . ' LIKE ' . $search . ' OR ' . $db->quoteName('users.username') . ' LIKE '
				. $search . ' OR ' . $db->quoteName('users.name') . ' LIKE ' . $search
				);
			}
		}

		if (!empty($behaviour))
		{
			if ($behaviour == 1)
			{
				$query->where($db->quoteName('waitlist.behaviour') . '=' . $db->quote('E-commerce'));
			}
			elseif ($behaviour == 2)
			{
				$query->where($db->quoteName('waitlist.behaviour') . '=' . $db->quote('Classroom_training'));
			}
		}

		$status = array('WL', 'C', 'CA');

		if (!empty($waitlistStatus) && in_array($waitlistStatus, $status))
		{
			$query->where($db->quoteName('waitlist.status') . '=' . $db->quote($waitlistStatus));
		}

		if (!empty($events))
		{
			$query->where($db->quoteName('waitlist.event_id') . '=' . (int) $events);
		}

		// Add the list ordering clause.
		$orderCol  = $this->state->get('list.ordering');
		$orderDirn = $this->state->get('list.direction');

		// Integration selecting events
		if ($integration == 1)
		{
			// Jomsocial
			$query->select($db->quoteName('events.id', 'event_id'));
			$query->select($db->quoteName('events.title', 'title'));
			$query->select($db->quoteName('events.startdate', 'eventStartDate'));
			$query->join('INNER', $db->quoteName('#__community_events', 'events')
		. ' ON (' . $db->quoteName('events.id') . ' = ' . $db->quoteName('intxref.eventid') . ')');

			$query->where($db->quoteName('intxref.source') . ' = ' . $db->quote('com_community'));

			// If loged In user is admin show all events/enrollments
			if (!($logedInUser->authorise('core.admin')))
			{
				$query->where($db->quoteName('events.creator') . ' = ' . $logedInUser->id);
			}
		}
		elseif ($integration == 2)
		{
			// Native
			$query->select($db->quoteName('events.id', 'event_id'));
			$query->select($db->quoteName('events.title', 'title'));
			$query->select($db->quoteName('events.startdate', 'eventStartDate'));
			$query->join('LEFT', $db->quoteName('#__jticketing_events', 'events')
		. ' ON (' . $db->quoteName('events.id') . ' = ' . $db->quoteName('intxref.eventid') . ')');
			$query->where($db->quoteName('intxref.source') . '=' . $db->quote('com_jticketing'));

			// If loged In user is admin show all events/enrollments
			if (!($logedInUser->authorise('core.admin')))
			{
				$query->where($db->quoteName('events.created_by') . ' = ' . $logedInUser->id);
			}
		}
		elseif ($integration == 3)
		{
			// Jevent
			$query->select($db->quoteName('events.evdet_id', 'event_id'));
			$query->select($db->quoteName('events.summary', 'title'));

			$query->join('INNER', $db->quoteName('#__jevents_vevdetail', 'events')
		. ' ON (' . $db->quoteName('events.evdet_id') . ' = ' . $db->quoteName('intxref.eventid') . ')');

			$query->join('INNER', $db->quoteName('#__jevents_vevent', 'jev') . ' ON (' . $db->quoteName('jev.detail_id')
				. ' = ' . $db->quoteName('events.evdet_id') . ')');
			$query->where($db->quoteName('intxref.source') . ' = ' . $db->quote('com_jevents'));

			if ($orderCol == 'events.title')
			{
				$orderCol = 'events.summary';
			}

			// If loged In user is admin show all events/enrollments
			if (!($logedInUser->authorise('core.admin')))
			{
				$query->where($db->quoteName('jev.created_by') . ' = ' . $logedInUser->id);
			}
		}
		elseif ($integration == 4)
		{
			// Easy social
			$query->select($db->quoteName('events.id', 'event_id'));
			$query->select($db->quoteName('events.title', 'title'));

			$query->join('INNER', $db->quoteName('#__social_clusters', 'events')
		. ' ON (' . $db->quoteName('events.id') . ' = ' . $db->quoteName('intxref.eventid') . ')');
			$query->where($db->quoteName('intxref.source') . '=' . $db->quote('com_easysocial'));

			// If loged In user is admin show all events/enrollments
			if (!($logedInUser->authorise('core.admin')))
			{
				$query->where($db->quoteName('events.creator_uid') . ' = ' . $logedInUser->id);
			}
		}

		if ($orderCol && $orderDirn)
		{
			$query->order($db->escape($orderCol . ' ' . $orderDirn));
		}

		return $query;
	}

	/**
	 * Email to selected waitlist or Adds antries to jticketing queue which will be used later
	 *
	 * @param   ARRAY  $waitlistIds  waitlist id
	 *
	 * @return  array
	 *
	 * @since   2.2
	 */
	public function getWaitlistUserEmails($waitlistIds)
	{
		if (file_exists(JPATH_SITE . '/components/com_jticketing/models/waitlistform.php')) { require_once JPATH_SITE . '/components/com_jticketing/models/waitlistform.php'; }
		$model = BaseDatabaseModel::getInstance('WaitlistForm', 'JTicketingModel');
		$emailArray = array();

		foreach ($waitlistIds AS $waitlistId)
		{
			$waitlistData  = $model->getItem($waitlistId);

			if (isset($waitlistData->user_id))
			{
				$emailArray[] = Factory::getUser($waitlistData->user_id)->email;
			}
		}

		return array_unique($emailArray);
	}

	/**
	 * Email to selected attendees or Adds antries to jticketing queue which will be used later
	 *
	 * @param   ARRAY   $cid      array of emails
	 * @param   string  $subject  subject of email
	 * @param   string  $message  message of email
	 *
	 * @return  boolean  true/false
	 *
	 * @since   2.2
	 */
	public function notifyUsersByEmail($cid, $subject, $message)
	{
		JLoader::register('JticketingMailHelper', JPATH_SITE . '/components/com_jticketing/helpers/mail.php');

		$comParams    = ComponentHelper::getParams('com_jticketing');
		$replytoemail = $comParams->get('reply_to');

		$app      = Factory::getApplication();
		$mailfrom = $app->get('mailfrom');
		$fromname = $app->get('fromname');
		$result   = 0;

		if (isset($replytoemail))
		{
			$replytoemail = explode(",", $replytoemail);
		}

		foreach ($cid as $email)
		{
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
		$comParams = JT::config();
		$items     = parent::getItems();
		$utilities = JT::utilities();

		foreach ($items as &$item)
		{
			if ($comParams->get('enable_eventstartdateinname') && (property_exists($item, 'eventStartDate')))
			{
				$startDate   = $utilities->getFormatedDate($item->eventStartDate);
				$item->title = $item->title . '(' . $startDate . ')';
			}
		}

		return $items;
	}
}
