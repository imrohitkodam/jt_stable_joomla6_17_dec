<?php
/**
 * @package     JLike
 * @subpackage  com_jlike
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access.
defined('_JEXEC') or die;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\Data\DataObject;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Factory;


/**
 * JLike Model Todos
 *
 * @since  1.6
 */
class JLikeModelTodos extends ListModel
{
	private $item = null;

	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     BaseDatabaseModel
	 * @since   12.2
	 */
	public function __construct($config = array())
	{
		BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_jlike/models');

		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'a.id',
				'title','a.title',
			);
		}

		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * This method should only be called once per instantiation and is designed
	 * to be called on the first call to the getState() method unless the model
	 * configuration flag to ignore the request is set.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 *
	 * @since   12.2
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// Initialise variables.
		$app = Factory::getApplication();

		// List state information
		$limit = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->get('list_limit'));
		$this->setState('list.limit', $limit);

		$limitstart = $app->getUserStateFromRequest('limitstart', 'limitstart', 0);
		$this->setState('list.start', $limitstart);

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

		$begin = $app->getUserStateFromRequest($this->context . '.filter.begin', 'filter_begin', '', 'string');
		$this->setState('filter.begin', $begin);

		$end = $app->getUserStateFromRequest($this->context . '.filter.end', 'filter_end', '', 'string');
		$this->setState('filter.end', $end);

		$ordering = $app->getInput()->get('filter_order');

		if (!empty($ordering))
		{
			$list             = $app->getUserState($this->context . '.list');
			$list['ordering'] = $app->getInput()->get('filter_order');
			$app->setUserState($this->context . '.list', $list);
		}

		$orderingDirection = $app->getInput()->get('filter_order_Dir');

		if (!empty($orderingDirection))
		{
			$list              = $app->getUserState($this->context . '.list');
			$list['direction'] = $app->getInput()->get('filter_order_Dir');
			$app->setUserState($this->context . '.list', $list);
		}

		$list = $app->getUserState($this->context . '.list');

		if (empty($list['ordering']))
		{
			$list['ordering'] = 'modified_date';
		}

		if (empty($list['direction']))
		{
			$list['direction'] = 'DESC';
		}

		if (isset($list['ordering']))
		{
			$this->setState('list.ordering', $list['ordering']);
		}

		if (isset($list['direction']))
		{
			$this->setState('list.direction', $list['direction']);
		}
	}

	/**
	 * Method to get a DataObjectbaseQuery object for retrieving the data set from a database.
	 *
	 * @return  DataObjectbaseQuery  A DataObjectbaseQuery object to retrieve the data set.
	 *
	 * @since   12.2
	 */
	protected function getListQuery()
	{
		// Get a db connection.
		$db      = Factory::getDbo();
		$app     = Factory::getApplication();

		//  Should be in Controller and then set state
		$path_id = $this->getState('filter.path_id');

		if (empty($path_id))
		{
			$path_id = $app->getInput()->get('path_id');
		}

		$user_id = $this->getState('filter.assigned_to');

		if (empty($user_id))
		{
			$user_id = Factory::getUser()->id;
		}

		// Create a new query object.
		$query = $db->getQuery(true);
		$query->select(
						array('t.id as todo_id,t.content_id,t.assigned_by,t.assigned_to,t.created_date, t.start_date,t.due_date, t.status,content.title as todo_title')
						);
		$query->select(array('content.url, content.element'));
		$query->select(array('graph.pathnode_graph_id, graph.path_id, graph.lft, graph.node, graph.rgt, graph.isPath, graph.this_compulsory '));
		$query->from($db->quoteName('#__jlike_todos', 't'));
		$query->join('INNER',
						$db->quoteName('#__jlike_content', 'content') . ' ON (' . $db->quoteName('t.content_id') . ' = ' . $db->quoteName('content.id') . ')');
		$query->join('INNER',
						$db->quoteName('#__jlike_pathnode_graph', 'graph') . ' ON (' . $db->quoteName('content.id') . ' = ' . $db->quoteName('graph.node') . ')');
		$query->where($db->quoteName('graph.isPath') . '= 0');
		$query->where($db->quoteName('t.state') . '= 0');

		$todoId = $this->getState('filter.id');

		// Filter by assigned to
		if ($todoId)
		{
			$query->where($db->quoteName('t.id') . '=' . (int) $todoId);
		}

		// Filter by assigned to
		if ($user_id)
		{
			$query->where($db->quoteName('t.assigned_to') . '=' . $db->quote($user_id));
		}

		// Filter by content id
		$content_id = $this->getState('filter.content_id');

		if ($content_id)
		{
			$query->where($db->quoteName('graph.node') . '=' . $db->quote($content_id));
		}

		// Filter by todo status
		$todo_status = $this->getState('filter.todo_status');

		if ($todo_status)
		{
			$query->where($db->quoteName('t.status') . '=' . $db->quote($todo_status));
		}

		// Filter by todo this_compulsory
		$thisCompulsory = $this->getState('filter.this_compulsory');

		if (!empty($thisCompulsory))
		{
			$query->where($db->quoteName('graph.this_compulsory') . '=' . (int) $thisCompulsory);
		}

		// Filter by path id
		if (!empty($path_id))
		{
			$query->where($db->quoteName('graph.path_id') . '=' . $db->quote($path_id));
		}

		$query->order($db->quoteName('t.ordering') . ' ASC');

		return $query;
	}

	/**
	 * Method to get last incomplite todo
	 *
	 * @param   INT  $userId  user id
	 * @param   INT  $pathId  path id
	 *
	 * @return  string|boolean
	 */
	public function getLastIncompleteToDo($userId, $pathId = 0)
	{
		if (!empty($userId))
		{
			$db = Factory::getDbo();
			$query = $db->getQuery(true);
			$query->select(array($db->quoteName('pg.node') , $db->quoteName('pg.ispath'), $db->quoteName('td.status', 'todo_status')));
			$query->select(array($db->quoteName('pu.status', 'path_status')));
			$query->from($db->quoteName('#__jlike_paths', 'p'));
			$query->join('INNER', $db->quoteName('#__jlike_path_user', 'pu') . ' ON ' . $db->quoteName('p.path_id') . '=' . $db->quoteName('pu.path_id'));
			$query->join('INNER', $db->quoteName('#__jlike_pathnode_graph', 'pg') . ' ON ' . $db->quoteName('p.path_id') . '=' . $db->quoteName('pg.path_id'));
			$query->join('LEFT', $db->quoteName('#__jlike_todos', 'td') . ' ON ' . $db->quoteName('td.content_id') . '=' . $db->quoteName('pg.node'));

			$query->where($db->quoteName('pu.user_id') . " = " . $userId);
			$query->where($db->quoteName('p.depth') . " <> 0 ");
			$query->where($db->quoteName('td.status') . " <> " . $db->quote('C'));
			$query->where($db->quoteName('td.assigned_to') . " = " . $db->quote($userId));

			if (!empty($pathId) && $pathId != 0)
			{
				$query->where($db->quoteName('pu.path_id') . " = " . $db->quote($pathId));
			}

			$query->order($db->quoteName('pg.order') . "ASC");

			// Get path which are not complited
			$query->where($db->quoteName('pu.status') . " <> " . $db->quote('C'));
			$db->setQuery($query);
			$result = $db->loadObjectList();

			$url = $this->getContentUrl($result, $userId, $pathId);

			return $url;
		}
		else
		{
			return false;
		}
	}

	/**
	 * get content ID
	 *
	 * @param   ARRAY  $nodesData  node data
	 * @param   INT    $userId     user id
	 * @param   INT    $pathId     path id
	 *
	 * @return  string
	 */
	public function getContentUrl($nodesData, $userId, $pathId)
	{
		$db = Factory::getDbo();

		foreach ($nodesData as $nodeData)
		{
			if ($nodeData->todo_status == "I" || $nodeData->path_status == "I")
			{
				if ($nodeData->ispath)
				{
					$this->getLastIncompleteToDo($userId, $nodeData->node);
				}
				else
				{
					$query = $db->getQuery(true);
					$query->select($db->quoteName('url'));
					$query->from($db->quoteName('#__jlike_content'));
					$query->where($db->quoteName('id') . " = " . $db->quote($nodeData->node));
					$db->setQuery($query);
					$result = $db->loadAssoc();

					$link = $result['url'];

					$app = Factory::getApplication();
					$menu   = $app->getMenu();
					$menuItem = $menu->getItems('link', $link, true);

					$link = $link . "&Itemid=" . $menuItem->id;

					return $link;
				}
			}
		}
	}
}
