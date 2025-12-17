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

use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

/**
 * Methods supporting a list of Jticketing records.
 *
 * @since  1.6
 */
class JticketingModelPDFTemplates extends ListModel
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see        JController
	 * @since      1.6
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
			'id', 'a.id',
			'ordering', 'a.ordering',
			'state', 'a.state',
			'vendor_id', 'a.vendor_id',
			'title', 'a.title',
			);
		}

		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   Elements order
	 * @param   string  $direction  Order direction
	 *
	 * @return void
	 *
	 * @throws Exception
	 *
	 * @since    1.6
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// Initialise variables.
		$app    = Factory::getApplication();
		$userId = Factory::getUser()->id;

		// List state information
		$limit = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->get('list_limit'));
		$this->setState('list.limit', $limit);

		$limitStart = $app->getUserStateFromRequest('limitstart', 'limitstart', 0);
		$this->setState('list.start', $limitStart);

		if ($list = $app->getUserStateFromRequest($this->context . '.list', 'list', array(), 'array'))
		{
			foreach ($list as $name => $value)
			{
				// Extra validations
				switch ($name)
				{
					case 'fullordering':
						$orderingParts = explode(' ', $value);

						if (count($orderingParts) >= 2)
						{
							// Latest part will be considered the direction
							$fullDirection = end($orderingParts);

							if (in_array(strtoupper($fullDirection), array('ASC', 'DESC', '')))
							{
								$this->setState('list.direction', $fullDirection);
							}

							unset($orderingParts[count($orderingParts) - 1]);

							// The rest will be the ordering
							$fullOrdering = implode(' ', $orderingParts);

							if (in_array($fullOrdering, $this->filter_fields))
							{
								$this->setState('list.ordering', $fullOrdering);
							}
						}
						else
						{
							$this->setState('list.ordering', $ordering);
							$this->setState('list.direction', $direction);
						}
						break;

					case 'ordering':
						if (!in_array($value, $this->filter_fields))
						{
							$value = $ordering;
						}
						break;

					case 'direction':
						if (!in_array(strtoupper($value), array('ASC', 'DESC', '')))
						{
							$value = $direction;
						}
						break;

					case 'limit':
						$limit = $value;
						break;

					// Just to keep the default case
					default:
						$value = $value;
						break;
				}

				$this->setState('list.' . $name, $value);
			}
		}

		// Receive & set filters
		if ($filters = $app->getUserStateFromRequest($this->context . '.filter', 'filter', array(), 'array'))
		{
			foreach ($filters as $name => $value)
			{
				$this->setState('filter.' . $name, $value);
			}
		}

		$ordering = $app->getInput()->get('filter_order');

		if (!empty($ordering))
		{
			$list             = $app->getUserState($this->context . '.list');

			if (!in_array($ordering, $this->filter_fields))
			{
				$ordering = 'ordering';
			}

			$list['ordering'] = $ordering;
			$app->setUserState($this->context . '.list', $list);
		}

		$orderingDirection = $app->getInput()->get('filter_order_Dir');

		if (!empty($orderingDirection))
		{
			$list              = $app->getUserState($this->context . '.list');

			if (!in_array(strtoupper($orderingDirection), array('ASC', 'DESC')))
			{
				$orderingDirection = 'asc';
			}

			$list['direction'] = $orderingDirection;
			$app->setUserState($this->context . '.list', $list);
		}

		$list = $app->getUserState($this->context . '.list');

		if (empty($list['ordering']))
		{
			$list['ordering'] = 'ordering';
		}

		if (empty($list['direction']))
		{
			$list['direction'] = 'asc';
		}

		if (isset($list['ordering']))
		{
			$this->setState('list.ordering', $list['ordering']);
		}

		if (isset($list['direction']))
		{
			$this->setState('list.direction', $list['direction']);
		}

		// Load the filter search.
		$search = $app->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');

		// Filter the search key value
		$search = htmlspecialchars($search ? $search : '', ENT_COMPAT, 'UTF-8');

		$this->setState('filter.search', $search);

		if ($app->isClient('administrator'))
		{
			// Filtering cat_id
			$this->setState('filter.cat', $app->getUserStateFromRequest($this->context . '.filter.categoryfilter', 'filter_categoryfilter', '', 'string'));

			// Filtering type
			$this->setState('filter.type', $app->getUserStateFromRequest($this->context . '.filter.type', 'filter_typefilter', '', 'string'));

			// Filtering Privacy
			$this->setState('filter.privacy', $app->getUserStateFromRequest($this->context . '.filter.privacyfilter', 'filter_privacyfilter', '', 'string'));
		}
		else
		{
			// Filtering type
			$this->setState('venue_type', $app->getUserStateFromRequest($this->context . '.venue_type', 'venue_type', '', 'string'));

			// Filtering Privacy
			$this->setState('venue_privacy', $app->getUserStateFromRequest($this->context . '.venue_privacy', 'venue_privacy', '', 'string'));

			// Filtering login user
			$this->setState('jtUserId', $userId);
		}
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return   JDatabaseQuery
	 *
	 * @since    1.6
	 */
	protected function getListQuery()
	{
		$app = Factory::getApplication();
		$integration = JT::getIntegration(true);

		// Create a new query object.
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query
			->select(
				$this->getState(
					'list.select', 'DISTINCT a.*'
				)
			);

		$query->from('`#__jticketing_pdf_templates` AS a');

		$query->join('LEFT', $db->qn('#__jticketing_integration_xref', 'i') . 'ON (' . $db->qn('i.id') . ' = ' . $db->qn('a.event_id') . ')');

		if ($integration == 1)
		{
			$query->select('comm.title');
			$query->join('LEFT', $db->qn('#__community_events', 'comm') . 'ON (' . $db->qn('comm.id') . ' = ' . $db->qn('i.eventid') . ')');
			$query->where($db->qn('i.source') . ' = ' . $db->quote("com_community"));
		}
		elseif ($integration == 2)
		{
			$query->select('event.title');
			$query->join('LEFT', $db->qn('#__jticketing_events', 'event') . 'ON (' . $db->qn('event.id') . ' = ' . $db->qn('i.eventid') . ')');
			$query->where($db->qn('i.source') . ' = ' . $db->quote("com_jticketing"));
		}
		elseif ($integration == 3)
		{
			$query->select('je.summary AS title');
			$query->join('LEFT', $db->qn('#__jevents_repetition', 'rep') . 'ON (' . $db->qn('i.eventid') . ' = ' . $db->qn('rep.rp_id') . ')');
			$query->join('LEFT', $db->qn('#__jevents_vevent', 'jv') . 'ON (' . $db->qn('jv.ev_id') . ' = ' . $db->qn('rep.eventid') . ')');
			$query->join('LEFT', $db->qn('#__jevents_vevdetail', 'je') . 'ON (' . $db->qn('je.evdet_id') . ' = ' . $db->qn('rep.eventdetail_id') . ')');
			$query->where($db->qn('i.source') . ' = ' . $db->quote("com_jevents"));
		}
		elseif ($integration == 4)
		{
			$query->select('es.title');
			$query->join('LEFT', $db->qn('#__social_clusters', 'es') . 'ON (' . $db->qn('es.id') . ' = ' . $db->qn('i.eventid') . ')');
			$query->where($db->qn('i.source') . ' = ' . $db->quote("com_easysocial"));
		}

		if (!Factory::getUser()->authorise('core.edit', 'com_jticketing'))
		{
			$query->where('a.state = 1');
		}

		// Filter by search in title
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('a.id = ' . (int) substr($search, 3));
			}
			else
			{
				$search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
				$query->where('( title LIKE ' . $search . ' )');
			}
		}

		if ($app->isClient('administrator'))
		{
			$vendorID = $this->getState('filter.vendor_id');

			// Filter by state
			if (!empty($vendorID))
			{
				$query->where($db->qn('i.vendor_id') . ' = ' . (int) $vendorID);
			}
		}
		else
		{
			$user = Factory::getUser();

			$superUser = $user->authorise('core.admin');

			if ($user && $user->id)
			{
				if (!$superUser)
				{
					$tjvendorFrontHelper = new tjvendorFrontHelper;
					$getVendorId = $tjvendorFrontHelper->checkVendor($user->id, 'com_jticketing');

					if ($getVendorId)
					{
						$query->where($db->qn('i.vendor_id') . ' = ' . (int) $getVendorId);
					}
					else 
					{
						$query->where($db->qn('i.vendor_id') . ' = ' . (int) 0);
					}
				}
			}
			else 
			{
				$query->where($db->qn('i.vendor_id') . ' = ' . (int) 0);
			}
		}

		$stateFilter = $this->getState('filter.state');

		// Filter by state
		if (is_numeric($stateFilter))
		{
			$query->where('a.state = ' . (int) $stateFilter);
		}
		elseif ($stateFilter === '')
		{
			$query->where('(a.state IN (0, 1))');
		}
		elseif ($stateFilter == '')
		{
			$query->where('(a.state IN (0,1))');
		}
		// Add the list ordering clause.
		$orderCol  = $this->state->get('list.ordering');
		$orderDirn = $this->state->get('list.direction');

		if ($orderCol && $orderDirn)
		{
			$query->order($db->escape($orderCol . ' ' . $orderDirn));
		}

		return $query;
	}

	/**
	 * Overrides the default function to check Date fields format, identified by
	 * "_dateformat" suffix, and erases the field if it's not correct.
	 *
	 * @return void
	 */
	protected function loadFormData()
	{
		$app              = Factory::getApplication();
		$filters          = $app->getUserState($this->context . '.filter', array());
		$errorDateFormat = false;

		foreach ($filters as $key => $value)
		{
			if (strpos($key, '_dateformat') && !empty($value) && $this->isValidDate($value) == null)
			{
				$filters[$key]    = '';
				$errorDateFormat = true;
			}
		}

		if ($errorDateFormat)
		{
			$app->enqueueMessage(Text::_("COM_JTICKETING_SEARCH_FILTER_DATE_FORMAT"), "warning");
			$app->setUserState($this->context . '.filter', $filters);
		}

		return parent::loadFormData();
	}

	/**
	 * Checks if a given date is valid and in a specified format (YYYY-MM-DD)
	 *
	 * @param   string  $date  Date to be checked
	 *
	 * @return bool
	 */
	private function isValidDate($date)
	{
		$date = str_replace('/', '-', $date);

		return (date_create($date)) ? Factory::getDate($date)->format("Y-m-d") : null;
	}

	/**
	 * Function to get the venue filter options
	 *
	 * @param   INT  $created_by  Fetch creators venue
	 *
	 * @return  array
	 *
	 * @since 3.2.0
	 */
	public function getVenueFilterOptions($created_by = 0)
	{
		$db = Factory::getDbo();

		$query = $db->getQuery(true);
		$query->select($db->quoteName('e.venue', 'venue_id'));
		$query->select($db->quoteName('v.name', 'title'));
		$query->from($db->quoteName('#__jticketing_events', 'e'));
		$query->join('INNER', $db->quoteName('#__jticketing_venues', 'v') . ' ON (' . $db->quoteName('e.venue') . ' = ' . $db->quoteName('v.id') . ')');

		if ($created_by)
		{
			$query->where('created_by = ' . (int) $created_by);
		}

		$db->setQuery($query);
		$venues = $db->loadObjectList();

		$venueFilter = array();
		$venueFilter[] = HTMLHelper::_('select.option', '', Text::_('COM_JTICKETING_FILTER_SELECT_VENUE'));

		if (!empty($venues))
		{
			foreach ($venues as $eachVenue)
			{
				$venueFilter[] = HTMLHelper::_('select.option', $eachVenue->venue_id, $eachVenue->title);
			}
		}

		return $venueFilter;
	}
}
