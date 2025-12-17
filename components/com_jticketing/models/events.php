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
use Joomla\CMS\Table\Table;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Access\Access;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\String\StringHelper;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

if (file_exists(JPATH_LIBRARIES . "/techjoomla/media/storage/local.php")) { require_once JPATH_LIBRARIES . "/techjoomla/media/storage/local.php"; }
if (file_exists(JPATH_LIBRARIES . "/techjoomla/media/xref.php")) { require_once JPATH_LIBRARIES . "/techjoomla/media/xref.php"; }

/**
 * Model for getting event list
 *
 * @package     JTicketing
 * @subpackage  component
 * @since       1.0
 */
class JticketingModelEvents extends ListModel
{
	public $filterBlacklist;
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @since    1.6
	 */
	public function __construct($config = array())
	{
		require_once JPATH_SITE . "/components/com_jticketing/helpers/frontendhelper.php";
		$this->objFrontendhelper = new Jticketingfrontendhelper;

		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'title', 'events.title',
				'state', 'events.state',
				'created', 'created_on',
				'startdate', 'startdate',
				'enddate', 'enddate',
				'location', 'location',
				'category', 'catid',
				'booking_start_date','booking_start_date',
				'booking_end_date','booking_end_date'
			);
		}

		parent::__construct($config);
	}

	/**
	 * Method to auto populate state
	 *
	 * @param   object  $ordering   ordering of list
	 * @param   object  $direction  direction of list
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// Initialise variables.
		$app = Factory::getApplication();

		// List state information
		$limit = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->get('list_limit'));
		$this->setState('list.limit', $limit);
		$limitstart = Factory::getApplication()->getInput()->getInt('limitstart', 0);
		$this->setState('list.start', $limitstart);

		// Load the parameters. Merge Global and Menu Item params into new object
		$params     = ComponentHelper::getParams('com_jticketing');
		$menuParams = new Registry;

		if ($menu = $app->getMenu()->getActive())
		{
			$menuParams->loadString($menu->getParams());
		}

		$mergedParams = clone $menuParams;
		$mergedParams->merge($params);
		$ordering     = $app->getUserStateFromRequest($this->context . '.filter_order', 'filter_order');
		$direction    = $app->getUserStateFromRequest($this->context . '.filter_order_Dir', 'filter_order_Dir');

		if (empty($ordering))
		{
			$ordering = $mergedParams->get('default_sort_by_option') ? $mergedParams->get('default_sort_by_option'):'startdate';
		}

		if (empty($direction))
		{
			$direction = $mergedParams->get('filter_order_Dir') ? $mergedParams->get('filter_order_Dir'):'desc';
		}

		if ($ordering && !in_array($ordering, $this->filter_fields))
		{
			$ordering = 'startdate';
		}

		if (!in_array(strtoupper($direction), array('ASC', 'DESC', '')))
		{
			$direction = 'desc';
		}

		$layout = $app->getUserStateFromRequest($this->context . '.layout', 'layout', 'default', 'string');

		if ($layout === 'default')
		{
			$this->setState('list.ordering', $ordering);
			$this->setState('list.direction', $direction);
			$this->setState('filter.search', $app->getUserStateFromRequest('com_jticketing' . 'search', 'search', '', 'string'));

			$this->setState('filter_day', $app->getUserStateFromRequest($this->context . '.filter_day', 'filter_day', '', 'string'));
			$this->setState('filter_quicksearchfields',
							$app->getUserStateFromRequest($this->context . '.filter_quicksearchfields', 'filter_quicksearchfields', '', 'string')
							);
			$this->setState('filter.tags', $this->getUserStateFromRequest($this->context . '.filter.tags', 'filter_tags', '', 'string'));
			$this->setState('filter.price', $this->getUserStateFromRequest($this->context . '.filter.price', 'filter_price', '', 'string'));
			$this->setState('filter_creator', $app->getUserStateFromRequest($this->context . '.filter_creator', 'filter_creator', 0));
			$this->setState('filter_location', $app->getUserStateFromRequest($this->context . '.filter_location', 'filter_location', '', 'string'));
			$this->setState('filter_start_date', $app->getUserStateFromRequest($this->context . '.filter_start_date', 'filter_start_date', '', 'string'));
			$this->setState('filter_end_date', $app->getUserStateFromRequest($this->context . '.filter_end_date', 'filter_end_date', '', 'string'));
			$this->setState('online_events', $app->getUserStateFromRequest($this->context . '.online_events', 'online_events', '', 'string'));
			$this->setState('filter_events_cat', $app->getUserStateFromRequest($this->context . '.filter_events_cat', 'filter_events_cat', '', 'string'));
			$this->setState('filter_booking_start_date', $app->getUserStateFromRequest(
			$this->context . '.filter_booking_start_date', 'filter_booking_start_date', '', 'string'
			)
			);
			$this->setState('filter_booking_end_date', $app->getUserStateFromRequest(
			$this->context . '.filter_booking_end_date', 'filter_booking_end_date', '', 'string'
			)
			);
		}
		else
		{
			// Blacklist the tags filter so it will be ignored by the parent class.
			$this->filterBlacklist[] = 'tags';
			$this->filterBlacklist[] = 'price';
		}

		$this->setState('layout', $app->getUserStateFromRequest($this->context . '.layout', 'layout', 'default', 'string'));

		if (empty($this->filterBlacklist))
		{
			$this->filterBlacklist = array();
		}

		// List state information.
		parent::populateState($ordering, $direction);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return	JDatabaseQuery
	 *
	 * @since	1.6
	 */
	public function getListQuery()
	{
		// Create a new query object.
		$db     = $this->getDbo();
		$app        = Factory::getApplication();
		$menuParams = new Registry;

		if ($menu = $app->getMenu()->getActive())
		{
			$menuParams->loadString($menu->getParams());
		}

		$query  = $db->getQuery(true);
		$userid = $app->getInput()->get('jt_user_id', '');

		if (!empty($userid))
		{
			$user = Factory::getUser($userid);
		}
		else
		{
			$user = Factory::getUser();
		}

		$allowedViewLevels = Access::getAuthorisedViewLevels($user->id);
		$implodedViewLevels = implode('","', $allowedViewLevels);

		$integration = JT::getIntegration(true);

		$events_to_show = $this->getState('filter_quicksearchfields');

		if (empty($events_to_show))
		{
			if ($menuParams->get('featured_event', '0'))
			{
				$events_to_show = 'featured';
			}
			else 
			{
				$events_to_show = $menuParams->get('events_to_show', 'show_all');
			}

			$this->setState('filter_quicksearchfields', $events_to_show);
		}

		$layout            = $this->getState('layout');
		$ordering          = $this->getState('list.ordering');
		$direction         = $this->getState('list.direction');
		$creator           = $this->getState('filter_creator');
		$location          = $this->getState('filter_location');
		$filter_start_date = $this->getState('filter_start_date');
		$filter_day        = $this->getState('filter_day');
		$filter_tags       = $this->getState('filter.tags');
		$filter_price      = $this->getState('filter.price');
		$filter_end_date   = $this->getState('filter_end_date');
		$events_to_show    = $this->getState('filter_quicksearchfields');
		$online_events     = $this->getState('online_events');
		$catid             = $this->getState('filter_events_cat');

		$filter_booking_start_date = $this->getState('filter_booking_start_date');
		$filter_booking_end_date = $this->getState('filter_booking_end_date');

		// Filter by search in title
		$search = $this->getState('filter.search');
		$search = (!empty($search)) ? $search : $app->getInput()->get('search', '', 'STRING');

		// Filter by venue
		$venue = $this->getState('filter_venue');
		$venue = (!empty($venue)) ? $venue : $app->getInput()->get('venue', '', 'INT');

		if ($integration == 2)
		{
			// Select the required fields from the table.
			$query->select($this->getState('list.select', 'events.*'));
			$query->from('`#__jticketing_events` AS events');

			$query->select('v.name, v.online_provider, v.address, v.country, v.state_id, v.city, v.zipcode, v.params');
			$query->join('LEFT', '#__jticketing_venues AS v ON v.id=events.venue');

			// Join over the venue table
			$query->select('con.country AS coutryName');
			$query->join('LEFT', '#__tj_country AS con ON con.id=v.country');

			// Join over the venue table
			$query->select('r.region');
			$query->join('LEFT', '#__tj_region AS r ON r.id=v.state_id');

			// Join over the category 'catid'.
			$query->select(' c.title AS category');
			$query->join('LEFT', '#__categories AS c ON c.id = events.catid');

			// Join over the created by field 'created_by',
			$query->select(' u.name AS created_by_name');
			$query->join('LEFT', '#__users AS u ON u.id = events.created_by');

			$query->select(' xref.id AS xref_id');
			$query->select('xref.checkin AS attended_count');
			$query->select('xref.id AS integrationId');

			$query->join(
					'LEFT', $db->quoteName('#__jticketing_integration_xref', 'xref')
					. ' ON ' . $db->quoteName('xref.eventid') . ' = ' . $db->quoteName('events.id')
					. ' AND ' . $db->quoteName('xref.source') . ' = ' . $db->quote('com_jticketing')
				);

			if (!empty($search))
			{
				if (stripos($search, 'id:') === 0)
				{
					$query->where('events.id = ' . (int) substr($search, 3));
				}
				else
				{
					$search = $db->Quote('%' . $db->escape($search, true) . '%');
					$query->where('( events.title LIKE ' . $search . ' OR events.long_description LIKE ' . $search . ' OR events.meta_data LIKE ' . $search . ' )');
				}
			}

			if ($layout == 'my')
			{
				$query->where("events.state<>-2");
				$query->where($db->quoteName('events.created_by') . ' = ' . (int) $user->id);
			}
			else
			{
				$filterState = $this->getState('filter.state');
				if (isset($filterState) && is_array($filterState) && count($filterState))
				{
					$states = ArrayHelper::toInteger($filterState);
					$query->whereIn($db->quoteName('events.state'), $states);
				}
				else 
				{
					$query->where("events.state=1");
				}
			}

			// Filter by event creator
			if ($creator)
			{
				$query->where($db->quoteName('events.created_by') . ' = ' . (int) $creator);
			}

			// Filtering catid
			if ($catid)
			{
				$query->where($db->quoteName('events.catid') . ' = ' . $db->quote($catid));
			}

			// Filtering venue
			if ($venue)
			{
				$query->where($db->quoteName('events.venue') . ' = ' . $db->quote($venue));
			}

			// For location filter
			if (!empty($location))
			{
				$query->andWhere(
					array($db->quoteName('events.location') . ' LIKE ' . $db->quote($location),
					$db->quoteName('v.address') . ' LIKE ' . $db->quote($location)
					)
				);
			}

			// For event type filter
			if ($online_events == '1' || $online_events == '0')
			{
				$query->where($db->quoteName('events.online_events') . ' = ' . $db->quote($online_events));
			}

			switch ($events_to_show)
			{
				case 'featured':
					$query->where("events.featured = 1");
					break;
				case 'ongoing':
					$query->where("events.enddate >= UTC_TIMESTAMP()");
					break;
				case 'past':
					$query->where("events.enddate <= UTC_TIMESTAMP()");
					break;
				case 'upcoming':
					$date             = date("Y-m-d H:i:s");
					$query->where($db->qn('events.startdate') . ' >= ' . $db->quote($date));
					break;
				case 'today':
					$today = date("Y-m-d");
					$query->where(('DATE(events.startdate)') . ' = ' . $db->quote($today));
					break;
				default:
					break;
			}

			$dateField_start = "startdate";
			$dateField_end = "enddate";

			if (!empty($filter_start_date) and !empty($filter_end_date))
			{
				$query->where($dateField_start . 'BETWEEN' . $db->quote($filter_start_date) . ' AND ' . $db->quote($filter_end_date));
			}
			elseif (!empty($filter_start_date))
			{
				$query->where($dateField_start . ' >= ' . $db->quote($filter_start_date));
			}
			elseif (!empty($filter_end_date))
			{
				$query->where($dateField_end . ' <= ' . $db->quote($filter_end_date));
			}

			$bookingdateField_start = "booking_start_date";
			$bookingdateField_end = "booking_end_date";

			if (!empty($filter_booking_start_date) and !empty($filter_booking_end_date))
			{
				$query->where($bookingdateField_start . 'BETWEEN' . $db->quote($filter_booking_start_date) . ' AND ' . $db->quote($filter_booking_end_date));
			}
			elseif (!empty($filter_booking_start_date))
			{
				$query->where($bookingdateField_start . ' >= ' . $db->quote($filter_booking_start_date));
			}
			elseif (!empty($filter_booking_end_date))
			{
				$query->where($bookingdateField_end . ' <= ' . $db->quote($filter_booking_end_date));
			}

			// Get events with repect to access level
			if ($layout != 'my')
			{
				$query->where('events.access IN ("' . $implodedViewLevels . '")');
			}

			if (!empty($filter_day))
			{
				switch ($filter_day)
				{
					case 'today':
						$date = Factory::getDate('now');
						$query->where($db->qn("events.startdate") . ' like' . $db->q($date->format("Y-m-d") . '%'));

						break;

					case 'tomorrow':
						$date     = Factory::getDate('now');
						$tomorrow = $date->modify('+1 day');
						$tomorrow = $tomorrow->format('Y-m-d');

						$query->where($db->qn("events.startdate") . ' like' . $db->q($tomorrow . '%'));
						break;

					case 'weekend':
						$staticSundayfinish = '';

						if (Factory::getDate('now')->format('D') != 'Sat')
						{
							$staticfinish       = Factory::getDate('now')->modify('next Saturday')->format('Y-m-d');
							$staticSundayfinish = Factory::getDate('now')->modify('next Sunday')->format('Y-m-d');
						}
						else
						{
							$staticfinish = Factory::getDate('now')->format("Y-m-d");
						}

						// Create array with conditions.
						$staticFinishCondition = array($db->qn("events.startdate") . ' like' . $db->q($staticfinish . '%'));

						if ($staticSundayfinish)
						{
							$staticFinishCondition[] = $db->qn("events.startdate") . ' like' . $db->q($staticSundayfinish . '%');
						}

						$query->andWhere($staticFinishCondition);

						break;

					case 'thisweek':
						$thisWeekFirstDay = Factory::getDate('now')->modify('this week')->format('Y-m-d');
						$thisWeekLastDay  = Factory::getDate('now')->modify('next Sunday')->format('Y-m-d');

						$query->where(
							$db->qn("events.startdate") . ' BETWEEN' . $db->q($thisWeekFirstDay . '%') . ' AND ' . $db->q($thisWeekLastDay . '%')

						);

						break;

					case 'nextweek':
						$nextWeekFirstDay = Factory::getDate('now')->modify("next week Monday")->format('Y-m-d');
						$nextWeekLastDay  = Factory::getDate('now')->modify("next week Sunday")->format('Y-m-d');

						$query->where(
							$db->qn("events.startdate") . ' BETWEEN' . $db->q($nextWeekFirstDay . '%') . ' AND ' . $db->q($nextWeekLastDay . '%')
						);
						break;

					case 'thismonth':
						$thisMonthFirstDay = Factory::getDate('now')->format('Y-m-01');
						$thisMonthLastDay  = Factory::getDate('now')->format('Y-m-t');

						$query->where(
							$db->qn("events.startdate") . ' BETWEEN' . $db->q($thisMonthFirstDay . '%') . ' AND ' . $db->q($thisMonthLastDay . '%')

						);

						break;

					case 'nextmonth':
						$nextMonthFirstDay = Factory::getDate('now')->modify('+1 month')->format('Y-m-01');
						$nextMonthLastDay  = Factory::getDate('now')->modify('+1 month')->format('Y-m-t');

						$query->where(
							$db->quoteName("events.startdate") . ' BETWEEN' . $db->quote($nextMonthFirstDay . '%') . ' AND ' . $db->quote($nextMonthLastDay . '%')

						);
						break;

					default:
						if (!empty($filter_day) && $filter_day != 'custom_date')
						{
							$dateArray       = explode("-", $filter_day);
							$filterStartDate = Factory::getDate($dateArray[0])->format('Y-m-d');
							$filterEndDate   = Factory::getDate($dateArray[1])->format('Y-m-d');
							$query->where($db->qn("events.startdate") . 'BETWEEN' . $db->q($filterStartDate . '%') . ' AND ' . $db->q($filterEndDate . '%'));
						}
						break;
				}
			}

			if (is_numeric($filter_tags))
			{
				$query->where($db->quoteName('tagmap.tag_id') . ' = ' . (int) $filter_tags)
				->join(
					'LEFT', $db->quoteName('#__contentitem_tag_map', 'tagmap')
					. ' ON ' . $db->quoteName('tagmap.content_item_id') . ' = ' . $db->quoteName('events.id')
					. ' AND ' . $db->quoteName('tagmap.type_alias') . ' = ' . $db->quote('com_jticketing.event')
				);
			}

			if (!empty($filter_price))
			{
				if ($filter_price == 'free')
				{
					$query->having('MAX(' . $db->quoteName('type.price') . ') = ' . 0);
				}

				if ($filter_price == 'paid')
				{
					$query->having('MAX(' . $db->quoteName('type.price') . ') > ' . 0);
				}

				$query->join(
					'LEFT', $db->quoteName('#__jticketing_types', 'type')
					. ' ON ' . $db->quoteName('type.eventid') . ' = ' . $db->quoteName('xref.id')
				);

				$query->group($db->quoteName('type.eventid'));
			}

			// Call TjfieldsHelper for the search filter module.
			$TjfieldsHelperPath = JPATH_SITE . '/components/com_tjfields/helpers/tjfields.php';

			if (!class_exists('TjfieldsHelper'))
			{
				JLoader::register('TjfieldsHelper', $TjfieldsHelperPath);
				JLoader::load('TjfieldsHelper');
			}

			$TjfieldsHelper = new TjfieldsHelper;
			$tjfieldItem_ids = $TjfieldsHelper->getFilterResults();
			$jinput = $app->input;
			$client = $jinput->get('client', '', 'string');

			if (!empty($client))
			{
				if ($tjfieldItem_ids != '-2')
				{
					$query->where(" events.id IN (" . $tjfieldItem_ids . ") ");
				}
			}

			if ($ordering && $direction)
			{
				$query->order($db->escape($ordering . ' ' . $direction));
			}
		}
		elseif ($integration == 4)
		{
			// Filter by search in title
			$search = $this->getState('filter.search');
			$search = (!empty($search)) ? $search : $app->getInput()->get('search', '', 'STRING');

			// Select the required fields from the table.
			$query->select($this->getState('list.select', 'events.*,events.address AS location,events.description	AS short_description'));
			$query->from('`#__social_clusters` AS events');

			// Join over the cluster id
			$query->select(' eventdet.start AS startdate,eventdet.end AS enddate');
			$query->join('INNER', '#__social_events_meta AS eventdet ON eventdet.cluster_id = events.id');

			// Join over the category 'catid'.
			$query->select(' c.title AS category');
			$query->join('INNER', '#__social_clusters_categories AS c ON c.id = events.category_id');

			// Join over the created by field 'creator_uid',
			$query->select(' u.name AS creator_uid_name');
			$query->join('INNER', '#__users AS u ON u.id = events.creator_uid');

			// Join over the cluster id
			$query->select(' event_images.square AS image');
			$query->join('LEFT', '#__social_avatars AS event_images ON event_images.uid = events.id');

			$query->select('xref.vendor_id, xref.id as xref_id');
				$query->join(
					'LEFT', $db->quoteName('#__jticketing_integration_xref', 'xref')
					. ' ON ' . $db->quoteName('xref.eventid') . ' = ' . $db->quoteName('events.id')
					. ' AND ' . $db->quoteName('xref.source') . ' = ' . $db->quote('com_easysocial')
				);

			if (!empty($search))
			{
				if (stripos($search, 'id:') === 0)
				{
					$query->where('events.id = ' . (int) substr($search, 3));
				}
				else
				{
					$search = $db->Quote('%' . $db->escape($search, true) . '%');
					$query->where('( events.title LIKE ' . $search . ' )');
				}
			}

			if ($layout == 'my')
			{
				$query->where("events.state<>-2");
				$query->where("events.creator_uid=" . $user->id . "");
			}
			else
			{
				$query->where("events.state=1");
			}

				if ($creator)
				{
					$query->where("events.creator_uid = '" . $creator . "'");
				}

			// For location filter
			if ($location != '')
			{
				$query->where("( events.address LIKE '%{$location}%' )");
			}

			// For event type filter
			if ($online_events == '1' || $online_events == '0')
			{
				$query->where('events.online_events = ' . "'" . $online_events . "'");
			}

			// Filtering catid
			if ($catid)
			{
				$query->where("events.category_id = '" . $catid . "'");
			}

			switch ($events_to_show)
			{
				case 'featured':
					$query->where("events.featured = 1");
					break;
				case 'ongoing':
					$query->where("eventdet.end >= UTC_TIMESTAMP()");
					break;
				case 'past':
					$query->where("eventdet.end <= UTC_TIMESTAMP()");
					break;
				case 'upcoming':
					$date             = date("Y-m-d H:i:s");
					$query->where($db->qn('eventdet.start') . ' >= ' . $db->quote($date));
					break;
				case 'today':
					$today = date("Y-m-d");
					$query->where(('DATE(eventdet.start)') . ' = ' . $db->quote($today));
					break;
				default:
					break;
			}

			// For ordering filter
			$query->group('events.id');

			if ($ordering && $direction)
			{
				$query->order($db->escape($ordering . ' ' . $direction));
			}
		}
		elseif ($integration == 1)
		{
			// Filter by search in title
			$search = $this->getState('filter.search');
			$search = (!empty($search)) ? $search : $app->getInput()->get('search', '', 'STRING');

			$select = 'events.*, events.location AS location,events.description AS short_description, events.startdate AS startdate, events.enddate AS enddate,
			events.cover AS image';

			// Select the required fields from the table.
			$query->select($this->getState('list.select', $select));
			$query->from('`#__community_events` AS events');

			// Join over the category 'catid'.
			$query->select(' c.name AS category');
			$query->join('INNER', '#__community_events_category AS c ON c.id = events.catid');

			// Join over the created by field 'creator_uid',
			$query->select(' u.name AS creator_uid_name');
			$query->join('INNER', '#__users AS u ON u.id = events.creator');

			$query->select('xref.vendor_id, xref.id as xref_id');
				$query->join(
					'LEFT', $db->quoteName('#__jticketing_integration_xref', 'xref')
					. ' ON ' . $db->quoteName('xref.eventid') . ' = ' . $db->quoteName('events.id')
					. ' AND ' . $db->quoteName('xref.source') . ' = ' . $db->quote('com_community')
				);

			if (!empty($search))
			{
				if (stripos($search, 'id:') === 0)
				{
					$query->where('events.id = ' . (int) substr($search, 3));
				}
				else
				{
					$search = $db->Quote('%' . $db->escape($search, true) . '%');
					$query->where('( events.title LIKE ' . $search . ' )');
				}
			}

			if ($layout == 'my')
			{
				$query->where("events.published<>-2");
				$query->where("events.creator=" . $user->id . "");
			}
			elseif ($creator)
			{
				if ($creator)
				{
					$query->where("events.creator = '" . $creator . "'");
				}
			}
			else
			{
				$query->where("events.published=1");
			}

			// For location filter
			if ($location != '')
			{
				$query->where("( events.location LIKE '%{$location}%' )");
			}

			// Filtering catid
			if ($catid)
			{
				$query->where("events.catid = '" . $catid . "'");
			}

			switch ($events_to_show)
			{
				case 'featured':
					$query->join('INNER', '#__community_featured AS f ON f.cid = events.id');
					$query->where("events.id = f.cid");
					break;
				case 'ongoing':
					$query->where("events.enddate >= UTC_TIMESTAMP()");
					break;
				case 'past':
					$query->where("events.enddate <= UTC_TIMESTAMP()");
					break;
				case 'upcoming':
					$date             = date("Y-m-d H:i:s");
					$query->where($db->qn('events.startdate') . ' >= ' . $db->quote($date));
					break;
				case 'today':
					$today = date("Y-m-d");
					$query->where(('DATE(events.startdate)') . ' = ' . $db->quote($today));
					break;
				default:
					break;
			}

			// For ordering filter
			$query->group('events.id');

			if ($ordering && $direction)
			{
				$query->order($db->escape($ordering . ' ' . $direction));
			}
		} 
		elseif ($integration == 3)
		{
			// Filter by search in title
			$search = $this->getState('filter.search');
			$search = (!empty($search)) ? $search : $app->getInput()->get('search', '', 'STRING');

			// Select the required fields from the table.
			$query->select('events.*');
			$query->select($db->quoteName('je.dtend', 'enddate'));
			$query->select($db->quoteName('je.dtstart', 'startdate'));
			$query->select($db->quoteName('je.description', 'short_description'));
			$query->select($db->quoteName('je.summary', 'title'));
			$query->select($db->quoteName('je.loc_id', 'location'));
			$query->select($db->quoteName('rep.rp_id', 'id'));
			$query->from($db->quoteName('#__jevents_vevent', 'events'));

			// Join over the category 'catid'.
			$query->select($db->quoteName('c.title', 'category'));
			$query->join('INNER', $db->quoteName('#__categories', 'c')
				. ' ON (' . $db->quoteName('c.id') . ' = ' . $db->quoteName('events.catid') . ')');

			// Join over the created by field 'creator_uid',
			$query->select($db->quoteName('u.name', 'creator_uid_name'));
			$query->join('INNER', $db->quoteName('#__users', 'u')
				. ' ON (' . $db->quoteName('u.id') . ' = ' . $db->quoteName('events.created_by') . ')');

			$query->join('LEFT', $db->qn('#__jevents_vevdetail', 'je') . 'ON (' . $db->qn('je.evdet_id') . ' = ' . $db->qn('events.detail_id') . ')');
			$query->join('LEFT', $db->qn('#__jevents_repetition', 'rep') . 'ON (' . $db->qn('rep.eventid') . ' = ' . $db->qn('events.ev_id') . ')');

			$query->select($db->quoteName('xref.vendor_id'));
			$query->select($db->quoteName('xref.id', 'xref_id'));
				$query->join(
					'LEFT', $db->quoteName('#__jticketing_integration_xref', 'xref')
					. ' ON ' . $db->quoteName('xref.eventid') . ' = ' . $db->quoteName('rep.rp_id')
					. ' AND ' . $db->quoteName('xref.source') . ' = ' . $db->quote('com_jevents')
				);

			$query->where($db->quoteName('xref.source') . ' = ' . $db->quote('com_jevents'));

			if (!empty($search))
			{
				if (stripos($search, 'id:') === 0)
				{
					$query->where($db->quoteName('events.ev_id') . ' = ' . (int) substr($search, 3));
				}
				else
				{
					$search = $db->Quote('%' . $db->escape($search, true) . '%');
					$query->where('( ' . $db->quoteName('je.summary') . ' LIKE ' . $search . ' )');
				}
			}

			if ($layout == 'my')
			{
				$query->where($db->quoteName('events.state') . '<>-2');
				$query->where($db->quoteName('events.created_by') . ' = ' . $user->id);
			}
			elseif ($creator)
			{
				if ($creator)
				{
					$query->where($db->quoteName('events.created_by') . ' = ' . $creator);
				}
			}

			$query->where($db->quoteName('events.state') . ' = 1');

			// For location filter
			if ($location != '')
			{
				$location = $db->Quote('%' . $db->escape($location, true) . '%');
				$query->where('(' . $db->quoteName('events.location') . ' LIKE ' . $location . ')');
			}

			// Filtering catid
			if ($catid)
			{
				$query->where($db->quoteName('events.catid') . ' = ' . $db->Quote($catid));
			}

			switch ($events_to_show)
			{
				case 'featured':
					$query->join('INNER', $db->quoteName('#__community_featured', 'f')
						. ' ON (' . $db->quoteName('f.cid') . ' = ' . $db->quoteName('events.ev_id') . ')');
					$query->where($db->quoteName('events.ev_id') . ' = ' . $db->quoteName('f.id'));
					break;
				case 'ongoing':
					$query->where("je.dtend >= UTC_TIMESTAMP()");
					break;
				case 'past':
					$query->where("je.dtend <= UTC_TIMESTAMP()");
					break;
				case 'upcoming':
					$date             = date("Y-m-d H:i:s");
					$query->where($db->qn('je.dtstart') . ' >= ' . $db->quote($date));
					break;
				case 'today':
					$today = date("Y-m-d");
					$query->where(('DATE(je.dtstart)') . ' = ' . $db->quote($today));
					break;
				default:
					break;
			}

			// For ordering filter
			$query->group('events.ev_id');

			if ($ordering && $direction)
			{
				$query->order($db->escape($ordering . ' ' . $direction));
			}
		}

		return $query;
	}

	/**
	 * This is used to get venue name
	 *
	 * @param   int  $id  order id
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function getVenueparams($id)
	{
		$db = Factory::getDbo();
		$sql = "SELECT params FROM #__jticketing_venues WHERE #__jticketing_venues.id =" . $id;
		$db->setQuery($sql);
		$venueName = $db->loadResult();

		return $venueName;
	}

	/**
	 * Get ordering option
	 *
	 * @return  array  options array
	 *
	 * @since   1.0
	 */
	public function getOrderingOptions()
	{
		$mainframe = Factory::getApplication();
		$default_sort_options = $mainframe->getParams()->get('default_sort_by_option');
		$integration = JT::getIntegration(true);

		if ($mainframe->isClient("administrator"))
		{
			$filter_order = $mainframe->getUserStateFromRequest('com_jticketing.filter_order', 'filter_order', 'created', 'string');
		}
		else
		{
			$filter_order = $mainframe->getUserStateFromRequest('com_jticketing.filter_order', 'filter_order',  $default_sort_options, 'string');
		}

		$this->setState('filter_order', $filter_order);
		$options   = array();
		$options[] = HTMLHelper::_('select.option', '', Text::_('COM_JTK_FILTER_SELECT_OREDERING'));
		$options[] = HTMLHelper::_('select.option', 'title', Text::_('COM_JTK_TITLE'));
		$options[] = HTMLHelper::_('select.option', 'created', Text::_('COM_JTK_CREATED'));
		$options[] = HTMLHelper::_('select.option', 'startdate', Text::_('COM_JTK_START_DATE'));
		$options[] = HTMLHelper::_('select.option', 'enddate', Text::_('COM_JTK_END_DATE'));

		// If Native integration then only below options
		if ($integration == '2')
		{
			$options[] = HTMLHelper::_('select.option', 'modified', Text::_('COM_JTK_MODIFIED'));
			$options[] = HTMLHelper::_('select.option', 'booking_start_date', Text::_('COM_JTK_BOOK_SDATE'));
			$options[] = HTMLHelper::_('select.option', 'booking_end_date', Text::_('COM_JTK_BOOK_EDATE'));
		}

		return $options;
	}

	/**
	 * Get days option
	 *
	 * @return  array  options array
	 *
	 * @since   1.0
	 */
	public function getDayOptions()
	{
		$options = array();

		$options[] = HTMLHelper::_('select.option', '', Text::_('COM_JTICKETING_FILTER_SELECT_DAYS'));
		$options[] = HTMLHelper::_('select.option', 'today', Text::_('COM_JTICKETING_TODAY'));
		$options[] = HTMLHelper::_('select.option', 'tomorrow', Text::_('COM_JTICKETING_TOMORROW'));
		$options[] = HTMLHelper::_('select.option', 'weekend', Text::_('COM_JTICKETING_WEEKEND'));
		$options[] = HTMLHelper::_('select.option', 'thisweek', Text::_('COM_JTICKETING_THIS_WEEK'));
		$options[] = HTMLHelper::_('select.option', 'nextweek', Text::_('COM_JTICKETING_NEXT_WEEK'));
		$options[] = HTMLHelper::_('select.option', 'thismonth', Text::_('COM_JTICKETING_THIS_MONTH'));
		$options[] = HTMLHelper::_('select.option', 'nextmonth', Text::_('COM_JTICKETING_NEXT_MONTH'));
		$options[] = HTMLHelper::_('select.option', 'custom_date', Text::_('COM_JTICKETING_START_DATE_END_DATE'));

		return $options;
	}

	/**
	 * Get direction option
	 *
	 * @return  array  options array
	 *
	 * @since   1.0
	 */
	public function getOrderingDirectionOptions()
	{
		$mainframe = Factory::getApplication();
		$filter_order_Dir = $mainframe->getParams()->get('filter_order_Dir');

		if ($mainframe->isClient('administrator'))
		{
			$filter_order_Dir = $mainframe->getUserStateFromRequest('com_jticketing.filter_order_Dir', 'filter_order_Dir', 'desc', 'string');
		}
		else
		{
			$filter_order_Dir = $mainframe->getUserStateFromRequest('com_jticketing.filter_order_Dir', 'filter_order_Dir', $filter_order_Dir, 'string');
		}

		$this->setState('filter_order_Dir', $filter_order_Dir);
		$options   = array();
		$options[] = HTMLHelper::_('select.option', '', Text::_('COM_JTK_FILTER_SELECT_OREDERING_DIRECTION'));
		$options[] = HTMLHelper::_('select.option', 'asc', Text::_('COM_JTK_ASCENDING'));
		$options[] = HTMLHelper::_('select.option', 'desc', Text::_('COM_JTK_DESCENDING'));

		return $options;
	}

	/**
	 * Get creator option
	 *
	 * @return  array  options array
	 *
	 * @since   1.0
	 */
	public function getCreator()
	{
		$db          = $this->getDbo();
		$query       = $db->getQuery(true);
		$integration = JT::getIntegration(true);

		if ($integration == 2)
		{
			$query->select('DISTINCT(u.id) AS creator_id, u.name');
			$query->from($db->quoteName('#__jticketing_events', 'events'));
			$query->join('INNER', $db->quoteName('#__users', 'u') . 'ON (' . $db->quoteName('u.id') . '=' . $db->quoteName('events.created_by') . ')');
		}

		if ($integration == 4)
		{
			$$query->select('DISTINCT(u.id) AS creator_id, u.name');
			$query->from($db->quoteName('#__social_clusters', 'events'));
			$query->join('INNER', $db->quoteName('#__users', 'u') . 'ON (' . $db->quoteName('u.id') . '=' . $db->quoteName('events.creator_uid') . ')');
		}

		$query->where($db->quoteName('events.state') . '=' . $db->quote('1'));
		$db->setQuery($query);
		$creators = $db->loadObjectList();

		$options   = array();
		$options[] = HTMLHelper::_('select.option', '', Text::_('COM_JTK_FILTER_CREATOR'));

		foreach ($creators as $creator)
		{
			$options[] = HTMLHelper::_('select.option', $creator->creator_id, $creator->name);
		}

		return $options;
	}

	/**
	 * Get venue location option
	 *
	 * @return  array  options array
	 *
	 * @since   2.0
	 */
	public function getVenueLocations()
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true);
		$query->select('DISTINCT(address) AS address, id as id');
		$query->from('`#__jticketing_venues` AS venues');
		$query->where($db->quoteName('venues.state') . ' = 1');
		$query->where($db->quoteName('venues.online') . ' = 0');

		$db->setQuery($query);

		return $db->loadAssocList();
	}

	/**
	 * Get location option
	 *
	 * @return  array  options array
	 *
	 * @since   1.0
	 */
	public function getLocation()
	{
		$db          = $this->getDbo();
		$query       = $db->getQuery(true);
		$integration = JT::getIntegration(true);

		if ($integration == 2)
		{
			$query->select('DISTINCT(location)');
			$query->from($db->quoteName('#__jticketing_events'));
		}

		if ($integration == 4)
		{
			$query->select($db->quoteName('DISTINCT(address)'));
			$query->from($db->quoteName('#__social_clusters'));
		}

		$query->where($db->quoteName('state') . ' = ' . $db->quote('1'));
		$query->where($db->quoteName('venue') . ' = ' . $db->quote('0'));
		$db->setQuery($query);

		$location = $db->loadColumn();
		$venueLocations = $this->getVenueLocations();
		$options  = array();
		$options[] = HTMLHelper::_('select.option', '', Text::_('COM_JTK_FILTER_LOCATION'));

		foreach ($location as $val)
		{
			if ($val)
			{
				$options[] = HTMLHelper::_('select.option', $val, $val);
			}
		}

		foreach ($venueLocations as $val)
		{
			$options[] = HTMLHelper::_('select.option', $val['address'], $val['address']);
		}

		return $options;
	}

	/**
	 * Method to get a list of courses.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 *
	 * @since   1.0.0
	 */
	public function getItems()
	{
		$items = parent::getItems();
		$filter = InputFilter::getInstance();

		if (!empty($items))
		{
			foreach ($items as $item)
			{
				$eventDetails = $this->getTJEventDetails($item->id);

				if (array_key_exists('buy_button', $eventDetails))
				{
					$item->buy_link = $eventDetails['buy_button_link'];
					$item->buy_button = $eventDetails['buy_button'];
				}

				if (array_key_exists('enrol_button', $eventDetails))
				{
					$item->enrol_link = $eventDetails['enrol_link'];
					$item->enrol_button = $eventDetails['enrol_button'];
				}

				if (array_key_exists('waitinglist_button', $eventDetails))
				{
					$item->waitinglist_button_link = $eventDetails['waitinglist_button_link'];
					$item->waitinglist_button = $eventDetails['waitinglist_button'];
				}

				$item->isboughtEvent = $eventDetails['isboughtEvent'];

				/* Get Event Ticket type price here*/

				$eventObj             = JT::event($item->id);
				$getTicketTypes       = $eventObj->getTicketTypes();

				for ($i = 0; $i < count($getTicketTypes); $i++)
				{
					$item->availableSeats = $getTicketTypes[$i]->available;
					$item->availableCount = $getTicketTypes[$i]->count;
					$item->unlimitedSeats = $getTicketTypes[$i]->unlimited_seats;
				}

				if (count($getTicketTypes) == 1)
				{
					foreach ($getTicketTypes as $ticketInfo)
					{
						$item->eventPriceMaxValue = $ticketInfo->price;
						$item->eventPriceMinValue = $ticketInfo->price;
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

					$item->eventPriceMaxValue = $maxTicketPrice;
					$item->eventPriceMinValue = $minTicketPrice;
				}
				else
				{
					$item->eventPriceMaxValue = 1;
					$item->eventPriceMinValue = -1;
				}

				if (!empty($item->booking_end_date) && !empty($item->booking_end_date))
				{
					$eventBookingStartdate = Factory::getDate(
						($item->booking_start_date != '0000-00-00 00:00:00') ?
						$item->booking_start_date :
						$item->created
					)->Format(Text::_('Y-m-d H:i:s'));

					$eventBookingEndDate = Factory::getDate(
						($item->booking_end_date) != '0000-00-00 00:00:00' ?
						$item->booking_end_date :
						$item->enddate
					)->Format(Text::_('Y-m-d H:i:s'));
				}

				$curr_date = Factory::getDate()->Format(Text::_('Y-m-d H:i:s'));

				if (!empty($eventBookingEndDatedate) && !empty($eventBookingStartdate))
				{
					if ($eventBookingEndDate < $curr_date)
					{
						// Booking date is closed
						$item->bookingStatus = -1;
					}
					elseif ($eventBookingStartdate > $curr_date)
					{
						// Booking not started
						$item->bookingStatus = 1;
					}
					else
					{
						// Booking is started
						$item->bookingStatus = 0;
					}
				}

				$com_params     = ComponentHelper::getParams('com_jticketing');
				$integration    = $com_params->get('integration');

				if ($integration == '2')
				{
					if (empty($item->location) && $item->venue != '0')
					{
						$venueDetails = JT::model('venueform')->getItem($item->venue);

						if (!empty($venueDetails->online))
						{
							if ($item->online_provider == "plug_tjevents_adobeconnect")
							{
								$item->location = 'Adobe - ' . $venueDetails->name;
							}
							else
							{
								$item->location = StringHelper::ucfirst($venueDetails->online_provider) . ' - ' . $venueDetails->name;
							}
						}
						else
						{
							$address           = $filter->clean($item->address, 'string');
							$eventVenueDetails = $filter->clean($venueDetails->name, 'string');
							$item->location    = $eventVenueDetails . ' - ' . $address;
						}
					}
				}

				$modelMedia = BaseDatabaseModel::getInstance('Media', 'JticketingModel');
				$eventImagePath = $com_params->get('jticketing_media_upload_path', 'media/com_jticketing/events');

				$mediaXrefLib = TJMediaXref::getInstance();
				$data = array('clientId' => $item->id, 'client' => 'com_jticketing.event','isGallery' => 1);
				$mediaGallery = $mediaXrefLib->retrive($data);
				if (file_exists(JPATH_LIBRARIES . "/techjoomla/media/tables/files.php")) { require_once JPATH_LIBRARIES . "/techjoomla/media/tables/files.php"; }

				if ($mediaGallery)
				{
					$galleryFiles = array();
					$config = array();

					foreach ($mediaGallery as $mediaXref)
					{
						$config['id'] = $mediaXref->media_id;

						$filetable = Table::getInstance('Files', 'TJMediaTable');

						// Load the object based on the id or throw a warning.
						$filetable->load($mediaXref->media_id);

						$mediaType = explode(".", $filetable->type);

						$eventImgPath = $eventImagePath . '/' . $mediaType[0] . 's';

						$config['uploadPath'] = $eventImgPath;

						$galleryFiles[] = TJMediaStorageLocal::getInstance($config);
					}

					$item->gallery = $galleryFiles;
				}

				$eventMainImage = $modelMedia->getEventMedia($item->id, 'com_jticketing.event', 0);

				foreach ($eventMainImage as $image)
				{
					if (isset($image->media_id))
					{
						$mediaConfig = array();
						$mediaConfig['id'] = $image->media_id;

						$filetable = Table::getInstance('Files', 'TJMediaTable');

						// Load the object based on the id or throw a warning.
						$filetable->load($image->media_id);

						$mediaType = explode(".", $filetable->type);

						$singleImgPath = $eventImagePath . '/' . $mediaType[0] . 's';

						$mediaConfig['uploadPath'] = $singleImgPath;

						$mediaImage = TJMediaStorageLocal::getInstance($mediaConfig);
						$imgparams = json_decode($mediaImage->params);

						if (empty($imgparams->detail))
						{
							$item->image = $mediaImage;
						}
						else
						{
							$item->coverImage = $mediaImage;
						}

						if (empty($item->image->media))
						{
							$item->image = new Stdclass;
							$item->image->media = Uri::root() . 'media/com_jticketing/images/default-event-image.png';
							$item->image->media_s = Uri::root() . 'media/com_jticketing/images/default-event-image.png';
							$item->image->media_l = Uri::root() . 'media/com_jticketing/images/default-event-image.png';
							$item->image->media_m = Uri::root() . 'media/com_jticketing/images/default-event-image.png';
						}
					}
				}
			}
		}

		return $items;
	}

	/**
	 * Method to check buy button display or not.
	 *
	 * @param   integer  $eventID         event id.
	 * @param   string   $redirectionUrl  redirection url.
	 *
	 * @return  mixed  An array of events details, false on failure.
	 *
	 * @since   1.0.0
	 */
	public function getTJEventDetails($eventID, $redirectionUrl = '')
	{
		require_once JPATH_SITE . "/components/com_jticketing/helpers/main.php";
		$jticketingmainhelper = new jticketingmainhelper;
		$userID = Factory::getUser()->id;
		$eventdata = JT::event($eventID);

		$returnData = $this->objFrontendhelper->renderBookingHTML($eventID, $userID, $eventdata, $redirectionUrl);

		return $returnData;
	}

	/**
	 * Method to all events integration redirection link.
	 *
	 * @param   String  $integration  integration set at backend.
	 *
	 * @return  mixed  A String of events link, false on empty integration.
	 *
	 * @since   2.5.0
	 */
	public function getAllEventsLink($integration)
	{
		if (!empty($integration))
		{
			$link 	= $itemId = '';
			$app	= Factory::getApplication();
			$menu	= $app->getMenu();

			switch ($integration)
			{
				case COM_JTICKETING_CONSTANT_INTEGRATION_JOMSOCIAL:
						$link = 'index.php?option=com_community&view=events';

						return CRoute::_($link, false);
					break;

				case COM_JTICKETING_CONSTANT_INTEGRATION_JEVENTS:
						$link 		= 'index.php?option=com_jevents&view=list&layout=events';
						$menuItem 	= $menu->getItems('link', $link, true);
						$itemId 	= !empty($menuItem->id) ? $menuItem->id : 0;
					break;

				case COM_JTICKETING_CONSTANT_INTEGRATION_EASYSOCIAL:
						$link 	= 'index.php?option=com_easysocial&view=events';
						$itemId = FRoute::getItemId('events');
					break;

				default:
					return false;
					break;
			}

			return Route::_($link . '&Itemid=' . $itemId, false);
		}

		return false;
	}

	/**
	 * Function to get the category filter options
	 *
	 * @param   Boolean  $default  Add option of 'Select default category'
	 *
	 * @return  array
	 *
	 * @since 3.2.0
	 */
	public function getCatFilterOptions($default = true)
	{
		$categories = JHtmlCategory::categories('com_jticketing');

		// Remove add to Root from category list
		array_pop($categories);

		if ($default)
		{
			$obj          = new stdClass;
			$obj->text    = Text::_('COM_JTICKETING_FILTER_SELECT_EVENT_CATEGORY');
			$obj->value   = '';
			$obj->disable = '';
			array_unshift($categories, $obj);
		}

		return $categories;
	}

	/**
	 * Function to get the event filter
	 *
	 * @param   INT  $created_by  Fetch creators events
	 *
	 * @return  array
	 *
	 * @since 3.2.0
	 */
	public function getEventFilterOptions($created_by = 0)
	{
		$eventsModel = JT::model('events', array('ignore_request' => true));

		if ($created_by)
		{
			$eventsModel->setState('filter_creator', $created_by);
		}

		$events = $eventsModel->getItems();
		$eventFilter   = array();
		$eventFilter[] = HTMLHelper::_('select.option', '', Text::_('COM_JTICKETING_FILTER_SELECT_EVENT'));

		if (!empty($events))
		{
			foreach ($events as $event)
			{
				$eventId       = htmlspecialchars($event->id);
				$eventName     = htmlspecialchars($event->title);
				$eventFilter[] = HTMLHelper::_('select.option', $event->id, $event->title);
			}
		}

		return $eventFilter;
	}

	/**
	 * Method to Returns array of events .
	 *
	 * @param   array  $options  array of creator id and event status.
	 *
	 * @return  Array  array of the items.
	 *
	 * @since  2.8.0
	 *
	 */
	public function getEvents($options = array())
	{
		$model = JT::model('events', array('ignore_request' => true));

		if (isset($options['creatorId']))
		{
			$model->setState('filter_creator', $options['creatorId']);
		}

		if (isset($options['eventStatus']))
		{
			$model->setState('filter_quicksearchfields', $options['eventStatus']);
		}

		return $model->getItems();
	}

	/**
	 * Get Event Categories description
	 *
	 * @return  array
	 *
	 * @since  3.2.0
	 */
	public function getEventCategories()
	{
		$integration = JT::getIntegration();
		$catOptions = array();
		$catOptions[] = HTMLHelper::_('select.option', "", Text::_('COM_JTICKETING_FILTER_SELECT_ALL_CATEGORY'));

		switch ($integration)
		{
			case 'com_easysocial':

				$model = ES::model('EventCategories');
				$categories = $model->getCategories(array('state' => SOCIAL_STATE_PUBLISHED, 'ordering' => 'ordering', 'excludeContainer' => true));

				if (!empty($categories))
				{
					foreach ($categories as $category)
					{
						$catOptions[] = HTMLHelper::_('select.option', $category->id, $category->title);
					}
				}

				break;

			case 'com_community':

				if (file_exists(JPATH_ADMINISTRATOR . '/components/com_community/models/eventcategories.php')) { require_once JPATH_ADMINISTRATOR . '/components/com_community/models/eventcategories.php'; }
				$jomsocialCategoryModel = new CommunityModelEventCategories;
				$categories = $jomsocialCategoryModel->getCategories();

				if (!empty($categories))
				{
					foreach ($categories as $category)
					{
						$catOptions[] = HTMLHelper::_('select.option', $category->id, $category->name);
					}
				}

				break;

			case 'com_jticketing' || 'com_jevents':

				$categories  = HTMLHelper::_('category.options', $integration, array('filter.published' => array(1)));

				if (!empty($categories))
				{
					foreach ($categories as $category)
					{
						if (!empty($category))
						{
							$catOptions[] = HTMLHelper::_('select.option', $category->value, $category->text);
						}
					}
				}

				break;
		}

		if (empty($catOptions))
		{
			array_push($catOptions, HTMLHelper::_('select.option', '', Text::_('NO_CATEGORIES_FOUND')));
		}

		return $catOptions;
	}

	/**
	 * Method to fetch vendor specific events
	 *
	 * @param   Integer  $vendorId  vendor id
	 *
	 * @return  array
	 *
	 * @since   4.1.3
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
				if (isset($event->xref_id) && $event->xref_id)
				{
					$vendorEvents[$key]['id'] = $event->xref_id;
					$vendorEvents[$key]['title'] = $event->title;

					if ($comParams->get('enable_eventstartdateinname'))
					{
						$startDate   = $utilities->getFormatedDate($event->startdate);
						$vendorEvents[$key]['title']   = $vendorEvents[$key]['title'] . '(' . $startDate . ')';
					}
				}
			}
		}

		$vendorEventData = array();
		$vendorEventData['events'] = $vendorEvents;
		$vendorEventData['userData']['userId'] = $tjvendorsTablevendor->user_id;

		return $vendorEventData;
	}
	
	/**
	 * Method getTable.
	 *
	 * @param   String  $type    Type
	 * @param   String  $prefix  Prefix
	 * @param   Array   $config  Config
	 *
	 * @return Id
	 *
	 * @since    1.8.1
	 */
	public function getTable($type = 'event', $prefix = 'JticketingTable', $config = array())
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
	 * Method publish.
	 *
	 * @param   Integer  $id     Id
	 * @param   String   $state  State
	 *
	 * @return void
	 *
	 * @since    1.8.1
	 */
	public function publish($id, $state)
	{
		$table = $this->getTable();
		$table->load($id);
		$table->state = $state;

		return $table->store();
	}
}
