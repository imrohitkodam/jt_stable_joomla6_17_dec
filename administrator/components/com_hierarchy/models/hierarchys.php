<?php
/**
 * @version    SVN: <svn_id>
 * @package    Com_Hierarchy
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2017 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;

/**
 * Methods supporting a list of Hierarchy records.
 *
 * @since  1.6
 */
class HierarchyModelHierarchys extends ListModel
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
				'name', 'a.name',
				'hierarchy_users', 'a.hierarchy_users',
				'context', 'a.context',
			);
		}

		BaseDatabaseModel::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_hierarchy/models');
		$this->hierarchyModel = BaseDatabaseModel::getInstance('Hierarchy', 'HierarchyModel');

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

		$userNames = $app->getUserStateFromRequest($this->context . '.filter.hierarchy_users', 'filter_hierarchy_users', '', 'string');
		$this->setState('filter.hierarchy_users', $userNames);

		$contextName = $app->getUserStateFromRequest($this->context . '.filter.context', 'filter_context', '', 'string');
		$this->setState('filter.context', $contextName);

		// Load the parameters.
		$params = ComponentHelper::getParams('com_hierarchy');
		$this->setState('params', $params);

		// List state information.
		parent::populateState('a.id', 'asc');
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 *
	 * @since   1.6
	 */
	/* protected function getListQuery()
	{
		$query = $this->_db->getQuery();

		// Select the required fields from the table.
		$query->select(
				$this->getState('list.select',
				'DISTINCT' . $this->_db->quoteName('a.id', 'subuserId') . ',' . $this->_db->quoteName('a.name') .
				',' . $this->_db->quoteName('a.username') . ',' . $this->_db->quoteName('a.email', 'user_email')
				)
				);
		$query->from($this->_db->quoteName('#__users', 'a'));

		// Join over the user field 'user_id'
		$query->select(
				$this->_db->quoteName(
					array('hu.id', 'hu.user_id', 'hu.reports_to', 'hu.context','hu.context_id',
					'hu.created_by', 'hu.modified_by', 'hu.created_date', 'hu.modified_date', 'hu.state', 'hu.note')
							)
				);
		$query->join('LEFT', $this->_db->quoteName('#__hierarchy_users', 'hu') . '
		ON (' . $this->_db->quoteName('hu.user_id') . ' = ' . $this->_db->quoteName('a.id') . ')');

		// Filter by search in title
		$search = $this->getState('filter.search');
		$userNames = $this->getState('filter.hierarchy_users');
		$contextName = $this->getState('filter.context');

		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('a.id = ' . (int) substr($search, 3));
			}
			else
			{
				$search = $this->_db->Quote('%' . $this->_db->escape($search, true) . '%');
				$query->where('( a.name LIKE ' . $search . ' )');
			}
		}

		// Filter by user name
		if (!empty($userNames))
		{
			$userNames = $this->_db->Quote('%' . $this->_db->escape($userNames, true) . '%');
			$query->where('( a.id LIKE ' . $userNames . ' )');
		}

		// Filter by context
		if (!empty($contextName))
		{
			$contextName = $this->_db->Quote('%' . $this->_db->escape($contextName, true) . '%');
			$query->where('( hu.context LIKE ' . $contextName . ' )');
		}

		$query->where('a.block=0');

		// Add the list ordering clause.
		$orderCol = $this->state->get('list.ordering');
		$orderDirn = $this->state->get('list.direction');

		if ($orderCol && $orderDirn)
		{
			$query->order($this->_db->escape($orderCol . ' ' . $orderDirn));
		}

		return $query;
	} */

		protected function getListQuery()
	{
		$query = $this->_db->getQuery(true);

		// Select the required fields from the table.
		$query->select(
				$this->getState('list.select',
				'DISTINCT' . $this->_db->quoteName('a.id', 'subuserId') . ',' . $this->_db->quoteName('a.name') .
				',' . $this->_db->quoteName('a.username') . ',' . $this->_db->quoteName('a.email', 'user_email')
				)
				);
		$query->from($this->_db->quoteName('#__users', 'a'));

		// Join over the user field 'user_id'
		$query->select(
				$this->_db->quoteName(
					array('hu.id', 'hu.user_id', 'hu.reports_to', 'hu.context','hu.context_id',
					'hu.created_by', 'hu.modified_by', 'hu.created_date', 'hu.modified_date', 'hu.state', 'hu.note')
							)
				);
		$query->join('LEFT', $this->_db->quoteName('#__hierarchy_users', 'hu') . '
		ON (' . $this->_db->quoteName('hu.user_id') . ' = ' . $this->_db->quoteName('a.id') . ')');

		// Filter by search in title
		$search = $this->getState('filter.search');
		$userNames = $this->getState('filter.hierarchy_users');
		$contextName = $this->getState('filter.context');

		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('a.id = ' . (int) substr($search, 3));
			}
			else
			{
				$search = $this->_db->Quote('%' . $this->_db->escape($search, true) . '%');
				$query->where('( a.name LIKE ' . $search . ' )');
			}
		}

		// Filter by user name
		if (!empty($userNames))
		{
			$userNames = $this->_db->Quote('%' . $this->_db->escape($userNames, true) . '%');
			$query->where('( a.id LIKE ' . $userNames . ' )');
		}

		// Filter by context
		if (!empty($contextName))
		{
			$contextName = $this->_db->Quote('%' . $this->_db->escape($contextName, true) . '%');
			$query->where('( hu.context LIKE ' . $contextName . ' )');
		}

		$query->where('a.block=0');

		// Add the list ordering clause.
		$orderCol = $this->state->get('list.ordering');
		$orderDirn = $this->state->get('list.direction');

		if ($orderCol && $orderDirn)
		{
			$query->order($this->_db->escape($orderCol . ' ' . $orderDirn));
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

		if (!empty($items))
		{
			foreach ($items as $item)
			{
				$reportsToUserName = array();

				if ($item->subuserId)
				{
					$results = $this->hierarchyModel->getReportsTo($item->user_id);

					foreach ($results as $res)
					{
						if (!empty($res->name))
						{
							$reportsToUserName[] = $res->name;
						}
					}
				}

				$item->ReportsToUserNameStr = implode(", ", $reportsToUserName);
				$item->ReportsToUserStr     = implode(", ", array_column($results, 'reports_to'));
			}
		}

		return $items;
	}
}
