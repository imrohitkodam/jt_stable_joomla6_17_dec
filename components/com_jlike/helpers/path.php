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
defined('_JEXEC') or die();
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Language\Text;

$language = Factory::getLanguage();
$language->load('com_jlike');

// Import library dependencies

/**
 * Path Helper
 *
 * @since  1.6
 */
class ComjlikePathHelper
{
	protected $db;

	protected $app;

	protected $userid;

	/**
	 * Method acts as a consturctor
	 *
	 * @since   1.0.0
	 */
	public function __construct()
	{
		$this->db  = Factory::getDbo();
		$this->app = Factory::getApplication();
		$this->userid = Factory::getUser()->id;
	}

	/**
	 * Method to update todo status
	 *
	 * @param   INT     $contentInfo  content info
	 * @param   INT     $userId       user id
	 * @param   STRING  $status       todo status
	 *
	 * @return void
	 *
	 * @since   1.0.0
	 */
	public function updateTodoStatus($contentInfo, $userId, $status)
	{
		if (!empty($contentInfo) && !empty($userId) && !empty($status))
		{
			try
			{
				JLoader::import('components.com_jlike.helpers.main', JPATH_SITE);
				$ComjlikeMainHelper = new ComjlikeMainHelper;
				$contentId = $ComjlikeMainHelper->findContentId($contentInfo);

				JLoader::import('components.com_jlike.models.todo', JPATH_SITE);
				JLoader::import('components.com_jlike.models.todos', JPATH_SITE);

				$todosModel = BaseDatabaseModel::getInstance('Todos', 'JLikeModel');
				$todoModel  = BaseDatabaseModel::getInstance('Todo', 'JLikeModel');

				$todosModel->setState('filter.content_id', $contentId);
				$todosModel->setState('filter.assigned_to', $userId);

				if (!empty($contentInfo['path_id']))
				{
					$todosModel->setState('filter.path_id', $contentInfo['path_id']);
				}

				$todo = $todosModel->getItems();

				if (count($todo))
				{
					if ($todo[0]->status != $status)
					{
						$data['id'] = $todo[0]->todo_id;
						$data['status'] = $status;
						$data['assigned_to'] = $userId;

						$data['modified_date'] = Factory::getDate()->toSql();

						if ($status == 'C')
						{
							$data['completion_date'] = Factory::getDate()->toSql();
						}

						$result = $todoModel->save($data);

						return $result;
					}
				}
			}
			catch (Exception $e)
			{
				echo $e->getMessage();
			}
		}
	}

	/**
	 * Function to get todo status
	 *
	 * @param   STRING  $client  User Id
	 * @param   INT     $uid     User group id
	 *
	 * @return  result
	 *
	 * @since  1.0.0
	 */
	public function getTodoStatus($client, $uid)
	{
		if ($client && $uid)
		{
			try
			{
				$query = $this->db->getQuery(true);

				// Find the content id on Todo ID & Use content ID to find the in which path this Todo is
				$query->select(array('t.content_id, t.status'));
				$query->from($this->db->qn('#__jlike_content', 'content'));
				$query->join('INNER', $this->db->qn('#__jlike_todos', 't') . ' ON (' . $this->db->qn('t.content_id') . ' = ' . $this->db->qn('content.id') . ')');
				$query->where($this->db->qn('content.element') . ' = ' . $this->db->quote($client));
				$query->where($this->db->qn('t.assigned_to') . ' = ' . (int) $uid);
				$this->db->setQuery($query);

				return $result = $this->db->loadObject();
			}
			catch (Exception $e)
			{
				echo $e->getMessage();
			}
		}
	}

	/**
	 * Function to get todo status
	 *
	 * @param   STRING  $client  User Id
	 *
	 * @return  result
	 *
	 * @since  1.0.0
	 */
	public function getPathId($client)
	{
		if (!empty($client))
		{
			$query = $this->db->getQuery(true);

			// Find path id
			$query->select('png.path_id, content.title');
			$query->from($this->db->qn('#__jlike_pathnode_graph', 'png'));
			$query->join('INNER', $this->db->qn('#__jlike_content', 'content') .
			' ON (' . $this->db->qn('content.id') . ' = ' . $this->db->qn('png.node') . ')');
			$query->where($this->db->qn('content.element') . ' = ' . $this->db->quote($client));
			$query->where($this->db->qn('png.isPath') . ' = ' . $this->db->quote(0));
			$this->db->setQuery($query);

			return $result = $this->db->loadObject();
		}
	}

	/**
	 * Function to check subscribed path
	 *
	 * @param   INT  $userId  User Id
	 *
	 * @return  Object|boolean Object on success, false on failure
	 *
	 * @since  1.0.0
	 */
	public function isSubscribedPath($userId)
	{
		$user = Factory::getuser($userId);

		if (!$user->id)
		{
			$this->setError(Text::_('COM_JLIKE_INVALID_USER'));

			return false;
		}

		$query = $this->db->getQuery(true);

		// Select the required fields from the table.
		$query->select('pu.*, p.*');
		$query->from($this->db->quoteName('#__jlike_path_user', 'pu'));
		$query->join('INNER', $this->db->quoteName('#__jlike_paths', 'p') .
		' ON (' . $this->db->quoteName('p.path_id') . ' = ' . $this->db->quoteName('pu.path_id') . ')');

		// Join over the created by field 'created_by'
		$query->join('INNER', $this->db->quoteName('#__users', 'u') .
		' ON (' . $this->db->quoteName('u.id') . ' = ' . $this->db->quoteName('pu.user_id') . ')');

		$query->where($this->db->quoteName('pu.user_id') . ' = ' . (int) $userId);
		$query->where($this->db->quoteName('p.depth') . ' = ' . $this->db->quote(0));

		$this->db->setQuery($query);

		return $this->db->loadobject();
	}

	/**
	 * Function used to do action on path params
	 *
	 * @param   Integger  $pathId  path id
	 * @param   String    $action  action
	 *
	 * @return  array
	 *
	 * @since  1.0.0
	 */
	public function getPathParams($pathId, $action)
	{
		$this->paths_model     = BaseDatabaseModel::getInstance('Paths', 'JlikeModel');
		$this->paths_model->setState('filter.path_id', $pathId);
		$pathDetails = $this->paths_model->getItems();

		if (!empty($pathDetails[0]))
		{
			$pathParams = json_decode($pathDetails[0]->params);

			foreach ($pathParams as $key => $param)
			{
				if ($key == $action)
				{
					return $param;
				}
			}
		}
	}

	/**
	 * Function used to get subpath id from todo id.
	 *
	 * @param   Integer|String  $todoId  todo id
	 *
	 * @return  Int pathId of the todoId
	 *
	 * @since  1.0.0
	 */
	public function getSubPathId($todoId)
	{
		// Get todo details.
		JLoader::import('components.com_jlike.models.todos', JPATH_SITE);
		$todosModel = BaseDatabaseModel::getInstance('Todos', 'JLikeModel', array('ignore_request' => true));
		$todosModel->setState('filter.id', (int) $todoId);
		$todoDetails	= $todosModel->getItems();

		// Get master path details.
		JLoader::import('components.com_jlike.models.pathnodegraphs', JPATH_SITE);
		$pathNodeGraphModel = BaseDatabaseModel::getInstance('Pathnodegraphs', 'JlikeModel', array('ignore_request' => true));
		$pathNodeGraphModel->setState('filter.node_content', (int) $todoDetails[0]->content_id);
		$pathNodeGraphModel->setState('filter.state', '1');
		$pathNodeGraphModel->setState('filter.isPath', '0');
		$pathInfo 	= $pathNodeGraphModel->getItems();

		if (!empty($pathInfo[0]->path_id))
		{
			return $pathInfo[0]->path_id;
		}
	}
}
