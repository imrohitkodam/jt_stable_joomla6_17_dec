<?php
/**
 * @package     JLike
 * @subpackage  com_jlike
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;
use Joomla\Data\DataObject;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;


/**
 * Methods supporting a list of Jlike records.
 * 
 * @since  1.6
 */
class JlikeModelRecommendations extends ListModel
{
/**
 * Constructor.
 *
 * @param   array  $config  An optional associative array of configuration settings.
 * 
 * @see        JController
 * @since    1.6
 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
							'id', 'a.id',
			'ordering', 'a.ordering',
			'state', 'a.state',
			'created_by', 'a.created_by',
			'content_id', 'a.content_id',
			'assigned_by', 'a.assigned_by',
			'assigned_to', 'a.assigned_to',
			'created_date', 'a.created_date',
			'start_date', 'a.start_date',
			'due_date', 'a.due_date',
			'status', 'a.status',
			'title', 'a.title',
			'type', 'a.type',
			'system_generated', 'a.system_generated',
			'parent_id', 'a.parent_id',
			'list_id', 'a.list_id',
			'modified_date', 'a.modified_date',
			'modified_by', 'a.modified_by',
			'can_override', 'a.can_override',
			'overriden', 'a.overriden',
			'params', 'a.params',
			'todo_list_id', 'a.todo_list_id',
			'ideal_time', 'a.ideal_time',
			'content_title','c.title',
			);
		}

		parent::__construct($config);
	}

	/**
  * Method to auto-populate the model state.
  * 
  * @param   mixed  $ordering   ordering
  * 
  * @param   mixed  $direction  direction
  * 
  * @return  mixed
  * Note. Calling getState in this method will result in recursion.
  */
	protected function populateState($ordering = null, $direction = null)
	{
		// Initialise variables.
		$app = Factory::getApplication('administrator');

		// Load the filter state.
		$search = $app->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$published = $app->getUserStateFromRequest($this->context . '.filter.state', 'filter_published', '', 'string');
		$this->setState('filter.state', $published);

		// Load the parameters.
		$params = ComponentHelper::getParams('com_jlike');
		$this->setState('params', $params);

		// List state information.
		parent::populateState('a.id', 'asc');
	}

	/**
  * Method to get a store id based on model configuration state.
  *
  * This is necessary because the model is used by the component and
  * different modules that might need different sets of data or different
  * ordering requirements.
  *
  * @param   string	  $id  A prefix for the store id.
  * 
  * @return   string	 A  store id.
  * 
  * @since	1.6
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
  * @return	DataObjectbaseQuery
  * 
  * @since	1.6
  */
	protected function getListQuery()
	{
		// Create a new query object.
		$db = $this->getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select(
			$this->getState(
			'list.select', 'DISTINCT a.*'
			)
		);
		$query->from('`#__jlike_todos` AS a');

		// Join over the content for content title & url
		$query->select('c.title AS content_title');
		$query->select('c.url AS content_url');
		$query->join('LEFT', '#__jlike_content AS c ON c.id=a.content_id');

		// Join over the users for the checked out user
		$query->select("uc.name AS editor");
		$query->join("LEFT", "#__users AS uc ON uc.id=a.checked_out");

		// Join over the user field 'created_by'
		$query->select('created_by.name AS created_by');
		$query->join('LEFT', '#__users AS created_by ON created_by.id = a.created_by');

		// Filter by published state
		$published = $this->getState('filter.state');

		if (is_numeric($published))
		{
			$query->where('a.state = ' . (int) $published);
		}
		elseif ($published === '')
		{
			$query->where('(a.state IN (0, 1))');
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
				$search = $db->Quote('%' . $db->escape($search, true) . '%');
			}
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
}
