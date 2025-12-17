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

if (file_exists(JPATH_SITE . '/components/com_jticketing/helpers/main.php')) { require_once JPATH_SITE . '/components/com_jticketing/helpers/main.php'; }

/**
 * Methods supporting a list of Jticketing records.
 *
 * @since  2.1
 */
class JticketingModelMytickets extends ListModel
{
	public $jticketingmainhelper;

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
				'ticket_id', 'attendee.id',
				'status', 'attendee.status',
				'title', 'events.title',
				'tickets', 'show_ticket'
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
		$app = Factory::getApplication();

		// Load the filter state.
		$search = $app->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');

		$this->setState('filter.search', $search);

		$ticketId = $app->getUserStateFromRequest($this->context . '.filter.tickets', 'filter_tickets');
		$this->setState('filter.tickets', $ticketId);

		$showTicket = $app->getUserStateFromRequest($this->context . '.filter.show_ticket', 'filter_show_ticket');
		$this->setState('filter.show_ticket', $showTicket);

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
		$db          = $this->getDbo();
		$query       = $db->getQuery(true);
		$logedInUser = Factory::getUser();
		$integration = JT::getIntegration(true);

		$columns = array('attendee.id', 'attendee.event_id', 'attendee.status', 'attendee.owner_id',
			'chck.checkin', 'user.firstname', 'user.lastname',
			'order.coupon_code', 'order.amount', 'order.order_id','order.cdate', 'oitem.ticketcount',
			'order.email', 'oitem.ticketcount', 'order.customer_note'
			);

		$query->select($db->quoteName($columns));
		$query->select($db->quoteName('type.title', 'ticket_type_title'));
		$query->select($db->quoteName('order.status', 'order_status'));
		$query->select($db->quoteName('type.price', 'amount'));
		$query->select($db->quoteName('attendee.enrollment_id', 'ticket_id'));
		$query->select('(type.price * oitem.ticketcount) AS totalamount');

		$query->from($db->quoteName('#__jticketing_attendees', 'attendee'));

		$query->join('LEFT', $db->qn('#__jticketing_checkindetails', 'chck') . 'ON (' . $db->qn('chck.attendee_id')
			. ' = ' . $db->qn('attendee.id') . ')');

		$query->join('LEFT', $db->qn('#__jticketing_order_items', 'oitem') . 'ON (' . $db->qn('oitem.attendee_id')
			. ' = ' . $db->qn('attendee.id') . ')');

		$query->join('LEFT', $db->qn('#__jticketing_order', 'order') . 'ON (' . $db->qn('order.id')
			. ' = ' . $db->qn('oitem.order_id') . ')');

		$query->join('LEFT', $db->qn('#__jticketing_users', 'user') . 'ON (' . $db->qn('user.order_id') . ' = ' . $db->qn('order.id') . ')');

		$query->join('LEFT', $db->qn('#__jticketing_types', 'type') . 'ON (' . $db->qn('type.id') . ' = ' . $db->qn('attendee.ticket_type_id') . ')');

		$query->join('LEFT', $db->quoteName('#__jticketing_integration_xref', 'intxref')
		. ' ON (' . $db->quoteName('intxref.id') . ' = ' . $db->quoteName('order.event_details_id') . ')');

		$query->where($db->qn('attendee.owner_id') . ' = ' . $db->quote($logedInUser->id));
		$query->where($db->quoteName('attendee.status') . ' = ' . $db->quote('A'));

		// Integration selecting events
		if ($integration == 1)
		{
			// Jomsocial
			$query->select($db->quoteName('events.id', 'event_id'));
			$query->select($db->quoteName('events.title', 'title'));

			$query->join('INNER', $db->quoteName('#__community_events', 'events')
		. ' ON (' . $db->quoteName('events.id') . ' = ' . $db->quoteName('intxref.eventid') . ')');
		}
		elseif ($integration == 2)
		{
			// Native
			$query->select($db->quoteName('events.id', 'event_id'));
			$query->select($db->quoteName('events.title', 'title'));

			$query->join('LEFT', $db->quoteName('#__jticketing_events', 'events')
		. ' ON (' . $db->quoteName('events.id') . ' = ' . $db->quoteName('intxref.eventid') . ')');
		}
		elseif ($integration == 3)
		{
			// Jevent
			$query->select($db->quoteName('rep.rp_id', 'event_id'));
			$query->select($db->quoteName('jev.summary', 'title'));

			$query->join('LEFT', $db->qn('#__jevents_repetition', 'rep') . 'ON (' . $db->qn('intxref.eventid') . ' = ' . $db->qn('rep.rp_id') . ')');
			$query->join('LEFT', $db->qn('#__jevents_vevent', 'events') . 'ON (' . $db->qn('events.ev_id') . ' = ' . $db->qn('rep.eventid') . ')');
			$query->join('LEFT', $db->qn('#__jevents_vevdetail', 'jev') . 'ON (' . $db->qn('jev.evdet_id') . ' = ' . $db->qn('rep.eventdetail_id') . ')');
		}
		elseif ($integration == 4)
		{
			// Easy social
			$query->select($db->quoteName('events.id', 'event_id'));
			$query->select($db->quoteName('events.title', 'title'));

			$query->join('INNER', $db->quoteName('#__social_clusters', 'events')
		. ' ON (' . $db->quoteName('events.id') . ' = ' . $db->quoteName('intxref.eventid') . ')');
		}

		// Filter by search in title
		$search   = $this->getState('filter.search');
		$ticketId = $this->getState('filter.tickets');
		$showTicket = $this->getState('filter.show_ticket');

		if (!empty($search))
		{
			$search = $db->Quote('%' . $db->escape($search, true) . '%');

			if ($integration == 3)
			{
				$query->where(
				$db->quoteName('jev.summary') . ' LIKE ' . $search
				);
			}
			else
			{
				$query->where(
				$db->quoteName('events.title') . ' LIKE ' . $search
				);
			}
		}

		if (!empty($ticketId))
		{
			$query->where($db->quoteName('attendee.id') . '=' . $db->quote($ticketId));
		}

		if (!empty($showTicket))
		{
			switch ($showTicket)
			{
				case 'upcoming':
					if ($integration == 3)
					{
						$query->where($db->quoteName('rep.endrepeat') . ">= UTC_TIMESTAMP()");
					}
					else 
					{
						$query->where($db->quoteName('events.enddate')  . ">= UTC_TIMESTAMP()");
					}
				break;
				case 'past':
					if ($integration == 3)
					{
						$query->where($db->quoteName('rep.endrepeat') . "<= UTC_TIMESTAMP()");
					}
					else 
					{
						$query->where($db->quoteName('events.enddate') . "<= UTC_TIMESTAMP()");
					}
				break;
				default:
					break;
			}
		}

		// Add the list ordering clause.
		$orderCol  = $this->state->get('list.ordering');
		$orderDirn = $this->state->get('list.direction');

		// Integration searching and filtering
		if ($integration == 1)
		{
			// Jom social
			$query->where($db->quoteName('intxref.source') . ' = ' . $db->quote('com_community'));
		}
		elseif ($integration == 2)
		{
			// Native
			$query->where($db->quoteName('intxref.source') . '=' . $db->quote('com_jticketing'));
		}
		elseif ($integration == 3)
		{
			// Jevent
			$query->where($db->quoteName('intxref.source') . ' = ' . $db->quote('com_jevents'));

			if ($orderCol == 'events.title')
			{
				$orderCol = 'events.summary';
			}
		}
		elseif ($integration == 4)
		{
			// Easy social
			$query->where($db->quoteName('intxref.source') . '=' . $db->quote('com_easysocial'));
		}

		if ($orderCol && $orderDirn)
		{
			$query->order($db->escape($orderCol . ' ' . $orderDirn));
		}

		return $query;
	}

	/**
	 * Method to get an array of data items
	 *
	 * @return  mixed An array of data on success, false on failure.
	 */
	public function getItems()
	{
		$items = parent::getItems();

		return $items;
	}
}
