<?php
/**
 * @package     JLike
 * @subpackage  com_jlike
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
use Joomla\Data\DataObject;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Factory;

/**
 * Methods supporting a list of records.
 *
 * @since  1.0.0
 */
class JlikeModelPathNodeGraphs extends ListModel
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see        JController
	 * @since      1.0.0
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'a.pathnode_graph_id',
				'a.path_id',
				'a.lft',
				'a.node',
				'a.rgt',
				'a.order',
				'a.isPath',
				'a.this_compulsory',
				'a.delay',
				'a.duration',
				'a.visibility',
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
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// Initialise variables.
		$app = Factory::getApplication();

		// Load the filter state.
		$search = $app->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');

		// Omit double (white-)spaces and set state
		$this->setState('filter.search', preg_replace('/\s+/', ' ', $search));

		if (!(in_array($orderCol, $this->filter_fields)))
		{
			$orderCol = 'a.pathnode_graph_id';
		}

		$this->setState('list.ordering', $orderCol);
		$listOrder = $app->getInput()->get('filter_order_Dir', 'ASC');

		// Get Path Id
		$pathId = $app->getUserStateFromRequest($this->context . '.filter.path_id', 'path_id');

		if (!empty($pathId))
		{
			$this->setState('filter.path_id', $pathId);
		}

		if (!in_array(strtoupper($listOrder), array('ASC', 'DESC', '')))
		{
			$listOrder = 'ASC';
		}

		$this->setState('list.direction', $listOrder);

		parent::populateState('a.pathnode_graph_id', 'asc');
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return   DataObjectbaseQuery
	 *
	 * @since    1.6
	 */
	protected function getListQuery()
	{
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

		$query->from($db->quoteName('#__jlike_pathnode_graph', 'a'));

		// Filter by search in title.
		$search = $this->getState('filter.search');

		// Filter by Path
		$pathId = $this->getState('filter.path_id');

		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where($db->quoteName('a.pathnode_graph_id') . ' = ' . (int) substr($search, 3));
			}
			else
			{
				$search = $db->quote('%' . $db->escape($search) . '%');
				$query->where($db->quoteName('a.path_id') . 'LIKE ' . $search . 'OR' . $db->quoteName('a.path_id') . 'LIKE ' . $search);
			}
		}

		if (!empty($pathId))
		{
			$query->where($db->quoteName('a.path_id') . ' = ' . (int) $pathId);
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
}
