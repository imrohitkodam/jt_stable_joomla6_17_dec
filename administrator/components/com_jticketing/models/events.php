<?php
declare(strict_types=1);

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
use Joomla\CMS\Table\Table;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\Database\ParameterType;

/**
 * Methods supporting a list of Jticketing records.
 *
 * @since  1.6
 */
class JticketingModelEvents extends ListModel
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @since   1.6
	 * @see     JController
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'ordering', 'a.ordering',
				'state', 'a.state',
				'title', 'a.title',
				'catid', 'a.catid',
				'created_by', 'a.created_by',
				'startdate', 'a.startdate',
				'enddate', 'a.enddate',
				'featured', 'a.featured',
				'created_by', 'a.created_by',
				'id', 'a.id',
				'online_offline'
			);
		}

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
	 * @since   1.6
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// Initialise variables.
		$app = Factory::getApplication('administrator');

		// Load the filter state.
		$search = $app->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$online_offline = $app->getUserStateFromRequest($this->context . '.filter.online_offline', 'filter_online_offline');
		$this->setState('filter.search', $online_offline);

		$published = $app->getUserStateFromRequest($this->context . '.filter.state', 'filter_published', '', 'string');
		$this->setState('filter.state', $published);

		// Filtering catid
		$this->setState('filter.catid', $app->getUserStateFromRequest($this->context . 'filter.catid', 'filter_catid', '', 'string'));

		// Filtering created_by
		$this->setState('filter.created_by', $app->getUserStateFromRequest($this->context . '.filter.created_by', 'filter_created_by', '', 'string'));

		// Load the parameters.
		$params = ComponentHelper::getParams('com_jticketing');
		$this->setState('params', $params);

		// List state information.
		parent::populateState('a.id', 'desc');
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param   string  $id  A prefix for the store id.
	 *
	 * @return  string  A store id.
	 *
	 * @since   1.6
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.state');

		return parent::getStoreId($id);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 *
	 * @since   1.6
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db = $this->getDatabase();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select', 'a.*'
			)
		);
		$query->from('`#__jticketing_events` AS a');

		// Join over the users for the checked out user.
		$query->select('uc.name AS editor');
		$query->join('LEFT', '#__users AS uc ON uc.id=a.checked_out');

		// Join over the venue table
		$query->select('v.name, v.online_provider, v.address, v.country, v.state_id, v.city, v.zipcode, v.params');
		$query->join('LEFT', '#__jticketing_venues AS v ON v.id=a.venue');

		// Join over the venue table
		$query->select('con.country AS coutryName');
		$query->join('LEFT', '#__tj_country AS con ON con.id=v.country');

		// Join over the venue table
		$query->select('r.region');
		$query->join('LEFT', '#__tj_region AS r ON r.id=v.state_id');

		// Join over the category 'catid'
		$query->select('catid.id AS categoryID,catid.title AS catid');
		$query->join('LEFT', '#__categories AS catid ON catid.id = a.catid');

		// Join over the user field 'creator'
		$query->select('creator.name AS creator');
		$query->join('LEFT', '#__users AS creator ON creator.id = a.created_by');

		// Join over the user field 'created_by'
		$query->select('created_by.name AS created_by');
		$query->join('LEFT', '#__users AS created_by ON created_by.id = a.created_by');

		// Join over the xref field 'checkin'
		$query->select('jt_xref.checkin AS attended_count');
		$query->select('jt_xref.id AS integrationId');
		$query->join('LEFT', '#__jticketing_integration_xref AS jt_xref ON jt_xref.eventid = a.id');
		$source = 'com_jticketing';
		$query->where($db->quoteName('jt_xref.source') . ' = :source')
			->bind(':source', $source, ParameterType::STRING);

		$online_offline = $this->getState('filter.online_offline');

		if (!empty($online_offline))
		{
			if ($online_offline == 1)
			{
				$query->where($db->quoteName('a.online_events') . ' = :online_events')
					->bind(':online_events', $online_offline, ParameterType::INTEGER);
			}
			else
			{
				$query->where($db->quoteName('a.online_events') . ' = :online_events_off')
					->bind(':online_events_off', 0, ParameterType::INTEGER);
			}
		}

		// Filter by published state
		$published = $this->getState('filter.state');

		// Code change by KOMAL line no 155 orignal code is elseif($published === '')
		if (is_numeric($published))
		{
			$query->where('a.state = :state')
				->bind(':state', $published, ParameterType::INTEGER);
		}
		elseif ($published === '')
		{
			$query->where('(a.state IN (0, 1))');
		}
		elseif ($published == '')
		{
			$query->where('(a.state IN (0,1))');
		}

		// Filter by search in title
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$id = (int) substr($search, 3);
				$query->where('a.id = :search_id')
					->bind(':search_id', $id, ParameterType::INTEGER);
			}
			else
			{
				$searchTerm = '%' . $search . '%';
				$query->where('( a.title LIKE :search_title )')
					->bind(':search_title', $searchTerm, ParameterType::STRING);
			}
		}

		// Filtering catid
		$filter_catid = $this->state->get("filter.catid");

		if ($filter_catid)
		{
			$query->where($db->quoteName('a.catid') . ' = :catid')
				->bind(':catid', $filter_catid, ParameterType::INTEGER);
		}

		// Filtering featured event
		$featured = $this->state->get("filter.featured");

		if ($featured)
		{
			$query->where($db->quoteName('a.featured') . ' = :featured')
				->bind(':featured', $featured, ParameterType::INTEGER);
		}

		$now = date("Y-m-d H:i:s");

		// Filtering past event
		$enddate = $this->state->get("filter.enddate");

		if ($enddate)
		{
			$query->where($db->quoteName('a.enddate') . ' < :now')
				->bind(':now', $now, ParameterType::STRING);
		}

		// Filtering upcoming event
		$startdate = $this->state->get("filter.startdate");

		if ($startdate)
		{
			$query->where($db->quoteName('a.startdate') . ' > :now_start')
				->bind(':now_start', $now, ParameterType::STRING);
		}

		// Filtering created_by
		$filter_created_by = $this->state->get("filter.created_by") ? $this->state->get("filter.created_by") : $this->getState('created_by');

		if ($filter_created_by)
		{
			$query->where($db->quoteName('a.created_by') . ' = :created_by')
				->bind(':created_by', $filter_created_by, ParameterType::INTEGER);
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
	 * Method to get a list of users.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 *
	 * @since   1.6.1
	 */
	public function getItems()
	{
		$items = parent::getItems();

		foreach ($items AS $item)
		{
			$item->ticket_types = $this->getTicketTypes($item->id);
		}

		return $items;
	}

	/**
	 * Method to get a list of users.
	 *
	 * @param   integer  $eventid  An event id
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 *
	 * @since   1.6.1
	 */
	public function getTicketTypes($eventid = '', $allTicketTypes = false)
	{
		$eventid = Factory::getApplication()->getInput()->get('id');

		if (!empty($eventid))
		{
			return JT::event($eventid, JT::getIntegration())->getTicketTypes($allTicketTypes);
		}

		return false;
	}

	/**
	 * Method to ____.
	 *
	 * @param   array    $items     An array.
	 * @param   integer  $featured  set featured value.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function setItemFeatured($items, $featured)
	{
		$db    = $this->getDatabase();
		$count = 0;

		if (is_array($items))
		{
			foreach ($items as $id)
			{
				$query = $db->getQuery(true)
					->update($db->quoteName('#__jticketing_events'))
					->set($db->quoteName('featured') . ' = :featured')
					->where($db->quoteName('id') . ' = :id')
					->bind(':featured', $featured, ParameterType::INTEGER)
					->bind(':id', $id, ParameterType::INTEGER);
				$db->setQuery($query);

				if (!$db->execute())
				{
					$this->setError($db->getErrorMsg());

					return 0;
				}

				$count++;
			}
		}

		return $count;
	}

	/**
	 * Method toget category name
	 *
	 * @param   integer  $id  category id
	 *
	 * @return  string
	 *
	 * @since   1.6
	 */
	public function getCategoryName($id)
	{
		$db = $this->getDatabase();
		$query = $db->getQuery(true)
			->select($db->quoteName('title'))
			->from($db->quoteName('#__categories'))
			->where($db->quoteName('id') . ' = :id')
			->bind(':id', $id, ParameterType::INTEGER);
		$db->setQuery($query);
		$category = $db->loadResult();

		return $category;
	}

	/**
	 * Method to fetch vendor specific events
	 *
	 * @param   Integer  $vendorId  vendor id
	 *
	 * @return  array
	 *
	 * @since   2.4.0
	 */
	public function getVendorSpecificEvents($vendorId)
	{
		$comParams    = JT::config();
		$utilities    = JT::utilities();
		$vendorEvents = array();

		Table::addIncludePath(JPATH_ROOT . '/administrator/components/com_tjvendors/tables');
		$tjvendorsTablevendor = Table::getInstance('vendor', 'TjvendorsTable', array());
		$tjvendorsTablevendor->load(array('vendor_id' => (int) $vendorId));

		$eventsModel = JT::model('events', array('ignore_request' => true));
		$eventsModel->setState('filter_creator', (int) $tjvendorsTablevendor->user_id);
		$eventList = $eventsModel->getItems();

		if (!empty($eventList))
		{
			foreach ($eventList as $key => $event)
			{
				$vendorEvents[$key]['id'] = !empty($event->xrefId) ? $event->xrefId : $event->integrationId;
				$vendorEvents[$key]['title'] = $event->title;

				if ($comParams->get('enable_eventstartdateinname'))
				{
					$startDate   = $utilities->getFormatedDate($event->startdate);
					$vendorEvents[$key]['title']   = $vendorEvents[$key]['title'] . '(' . $startDate . ')';
				}
			}
		}

		$vendorEventData = array();
		$vendorEventData['events'] = $vendorEvents;
		$vendorEventData['userData']['userId'] = $tjvendorsTablevendor->user_id;

		return $vendorEventData;
	}
}
