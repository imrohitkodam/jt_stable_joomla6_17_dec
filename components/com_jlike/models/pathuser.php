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
use Joomla\CMS\Table\Table;
use Joomla\CMS\Form\Form;
use Joomla\CMS\MVC\Model\FormModel;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Language\Text;


$pathsModelPath = JPATH_SITE . '/components/com_jlike/models/paths.php';
if (file_exists($pathsModelPath)) {
	require_once $pathsModelPath;
}

$todoModelPath = JPATH_SITE . '/components/com_jlike/models/todo.php';
if (file_exists($todoModelPath)) {
	require_once $todoModelPath;
}

$pathModelPath = JPATH_SITE . '/components/com_jlike/models/path.php';
if (file_exists($pathModelPath)) {
	require_once $pathModelPath;
}

$cpjpathstatusModelPath = JPATH_SITE . '/components/com_cpjactionboard/models/cpjpathstatus.php';
if (file_exists($cpjpathstatusModelPath)) {
	require_once $cpjpathstatusModelPath;
}
use Joomla\Utilities\ArrayHelper;

/**
 * JLike model Pathuser.
 *
 * @since  1.6
 */
class JLikeModelPathUser extends FormModel
{
	protected $user;

	protected $db;

	protected $pathsModel;

	protected $dispatcher;

	protected $path_model;

	protected $obj_todo;

	/**
	 * Constructor
	 *
	 * @throws  Exception
	 */
	public function __construct()
	{
		$this->_params = ComponentHelper::getParams('com_jlike');
		$this->user = Factory::getUser();
		$this->path_model = BaseDatabaseModel::getInstance('Path', 'JlikeModel');
		$this->obj_todo   = BaseDatabaseModel::getInstance('Todo', 'JlikeModel');
		$this->pathsModel = BaseDatabaseModel::getInstance('Paths', 'JlikeModel');
		$this->db = Factory::getDbo();
		parent::__construct();
	}

	/**
	 * Method to get a table object, load it if necessary.
	 *
	 * @param   string  $type    The table name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  Table  A Table object
	 *
	 * @since   12.2
	 * @throws  Exception
	 */
	public function getTable($type = 'PathUser', $prefix = 'JlikeTable', $config = array())
	{
		$this->addTablePath(JPATH_ADMINISTRATOR . '/components/com_jlike/tables');

		return Table::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to get the record form.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  mixed  A Form object on success, false on failure
	 *
	 * @since   1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_jlike.cpjpathstatus', 'pathuser', array('control' => 'jform', 'load_data' => $loadData));

		return $form = empty($form) ? false : $form;
	}

	/**
	 * Method to check if path is allowed to access (Either to Subscribe or to Complete).
	 *
	 * @param   array  $data  The form data
	 *
	 * @return  boolean
	 *
	 * @since  1.0.0
	 *
	 */
	private function canAccessPath($data)
	{
		$canCompletePath = $this->user->authorise('core.path.complete', 'com_jlike');

		// Get path params
		$this->pathsModel->setState('filter.path_id', $data['path_id']);
		$this->pathsModel->setState('filter.state', '1');
		$pathDetails = $this->path_model->getData($data['path_id']);

		// Check if path subscription dates are open for subscription - start
		$isPathOpenForSubscription = $this->path_model->isPathOpenForSubscription();
		$allowedToSubscribe = $isPathOpenForSubscription->allowedToSubscribe;

		// Check if path subscription dates are open for subscription - end

		$pathParams = json_decode($pathDetails->params);

		// Check access level.
		$levels = $this->user->getAuthorisedViewLevels();
		$allowedToAccess = in_array($pathDetails->access, $levels);

		// To subscribe
		if (empty($data['status']))
		{
			$data['status'] = 'I';
		}

		$canAccessPath = false;

		switch ($data['status'])
		{
			// Path subscription
			case 'I':
				$canAccessPath = ($allowedToSubscribe && $allowedToAccess) ? true : false;
				break;

			// Path completion
			case 'C':
				if ((($pathParams->core->approval === 'self' || $pathParams->core->approval === 'auto') && $allowedToAccess)
					|| ($pathParams->core->approval === 'admin' && $canCompletePath))
				{
					$canAccessPath = true;
				}
				break;
		}

		return $canAccessPath;
	}

	/**
	 * Method to do activities before Path Subscription.
	 *
	 * @param   array  $data  The form data
	 *
	 * @return  boolean
	 *
	 * @since  1.0.0
	 *
	 */
	private function beforePathSubscribe($data)
	{
		// This trigger calls for Subscription
		PluginHelper::importPlugin("tjpath");
		$this->dispatcher->trigger('onBeforePathSubscribe', array($data));

		$isPath  = $this->path_model->isPathOfPaths($data['path_id']);

		if ($isPath)
		{
			$this->subscribeNextPath($data);
		}
		else
		{
			$this->addTodo($data);
		}
	}

	/**
	 * Method to do activities after Path Subscription.
	 *
	 * @param   array  $data  The form data
	 *
	 * @return  boolean
	 *
	 * @since  1.0.0
	 *
	 */
	private function afterPathSubscribe($data)
	{
		PluginHelper::importPlugin('tjpath');
		$this->dispatcher->trigger('onAfterPathSubscribe', array($data));
	}

	/**
	 * Method to do activities before Path Completion.
	 *
	 * @param   array  $data  The form data
	 *
	 * @return  boolean
	 *
	 * @since  1.0.0
	 *
	 */
	private function beforePathComplete($data)
	{
		PluginHelper::importPlugin('tjpath');
		$this->dispatcher->trigger('onBeforePathCompletion', array($data));
	}

	/**
	 * Method to do activities after Path Completion.
	 *
	 * @param   array  $data  The form data
	 *
	 * @return  boolean
	 *
	 * @since  1.0.0
	 *
	 */
	private function afterPathComplete($data)
	{
		$this->checkParentPathCompletion($data);
		PluginHelper::importPlugin('tjpath');
		$this->dispatcher->trigger('onAfterPathCompletion', array($data));
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data
	 *
	 * @return bool
	 *
	 * @throws Exception
	 * @since 1.6
	 */
	public function save($data)
	{
		if (!$this->user->id)
		{
			$this->setError(Text::_('COM_JLIKE_INVALID_USER'));

			return false;
		}

		$canAccessPath = $this->canAccessPath($data);

		if ($canAccessPath)
		{
			$table = $this->getTable();

			if ($data['status'] == 'C')
			{
				$data['completed_date'] = Factory::getDate()->toSql();

				$this->beforePathComplete($data);
			}
			else
			{
				$data['subscribed_date'] = Factory::getDate()->toSql();

				$this->beforePathSubscribe($data);
			}

			$getPathDetails = $this->getPathUserDetails($data['path_id'], $data['user_id']);

			if (!$getPathDetails || $getPathDetails->status != $data['status'])
			{
				// Load Todo Model
				if ($table->save($data))
				{
					if ($data['status'] == 'C' && $data['path_user_id'])
					{
						// This trigger calls for path completion.
						$this->afterPathComplete($data);
					}
					else
					{
						// This trigger calls for Subscription
						$this->afterPathSubscribe($data);
					}

					return true;
				}
			}

			$this->setError(Text::_('COM_JLIKE_PATHUSER_ERROR_MSG_SAVE'));

			return false;
		}
		else
		{
			$this->setError(Text::_('COM_JLIKE_PATHUSER_ERROR_MSG_SAVE'));

			return false;
		}
	}

	/**
	 * Method to get user subscribed path.
	 *
	 * @param   INT  $pathId  path id
	 * @param   INT  $userId  logged user id
	 *
	 * @return Object|boolean Object on success, false on failure.
	 *
	 * @throws Exception
	 */
	public function getPathUserDetails($pathId = '', $userId = '')
	{
		$user = Factory::getuser($userId);

		if (!$user->id)
		{
			$this->setError(Text::_('COM_JLIKE_INVALID_USER'));

			return false;
		}

		if (!$pathId)
		{
			$this->setError(Text::_('COM_JLIKE_INVALID_PATH_ID'));

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

		if ($userId)
		{
			$query->where($this->db->quoteName('pu.user_id') . ' = ' . (int) $userId);
		}

		if ($pathId)
		{
			$query->where($this->db->quoteName('pu.path_id') . ' = ' . (int) $pathId);
		}

		$this->db->setQuery($query);

		return $this->db->loadobject();
	}

	/**
	 * Method to get make the status of path.
	 *
	 * @param   INT  $pathId  path id
	 * @param   INT  $userId  logged user id
	 *
	 * @return Object|boolean Object on success, false on failure.
	 *
	 * @throws Exception
	 */
	public function MakeStatus($pathId, $userId)
	{
		$user = Factory::getuser($userId);

		if (!$user->id)
		{
			$this->setError(Text::_('COM_JLIKE_INVALID_USER'));

			return false;
		}

		if (!$pathId)
		{
			$this->setError(Text::_('COM_JLIKE_INVALID_PATH_ID'));

			return false;
		}

		$pathModel = BaseDatabaseModel::getInstance('Paths', 'JLikeModel');
		$pathModel->setState('filter.path_id', $pathId);
		$pathDetails = $pathModel->getItems();

		$status = '';

		if (!empty($pathDetails[0]))
		{
			$pathParams = json_decode($pathDetails[0]->params);

			switch ($pathParams->core->approval)
			{
				case 'self' :
					$status = 'I';

				break;
				case 'admin':
					$status = 'I';
				break;
				case 'auto':
					$status = 'C';
				break;
			}
		}

		return $status;
	}

	/**
	 * Method to subscribe to next path.
	 *
	 * @param   Array  $data  Array of path id & user Id
	 *
	 * @return void.
	 *
	 * @throws Exception
	 */
	public function subscribeNextPath($data)
	{
		if (!$data['path_id'])
		{
			$this->setError(Text::_('COM_JLIKE_INVALID_PATH_ID'));

			return false;
		}

		$query = $this->db->getQuery(true);

		// This query should have function need refactor here. Added by Sudhir
		// Select the required fields from the table.
		$query->select('p.path_id, p.path_title, p.path_description,png.node,node.path_title as node_title');
		$query->from($this->db->quoteName('#__jlike_paths', 'p'));
		$query->join('LEFT', $this->db->quoteName('#__jlike_pathnode_graph', 'png') .
		' ON (' . $this->db->quoteName('png.path_id') . ' = ' . $this->db->quoteName('p.path_id') . ')');
		$query->join('INNER', $this->db->quoteName('#__jlike_paths', 'node') .
		' ON (' . $this->db->quoteName('node.path_id') . ' = ' . $this->db->quoteName('png.node') . ')');
		$query->where($this->db->quoteName('p.state') . ' = ' . $this->db->quote(1));
		$query->where($this->db->quoteName('png.lft') . ' = ' . $this->db->quote(0));
		$query->where($this->db->quoteName('png.isPath') . ' = ' . $this->db->quote(1));
		$query->where($this->db->quoteName('p.path_id') . ' = ' . (int) $data['path_id']);
		$this->db->setQuery($query);
		$result = $this->db->loadObjectList();

		// Need to Look for optional paths
		if (count($result) == 1)
		{
			$userId = $data['user_id'];

			$data = array();
			$data['path_id'] = $result[0]->node;
			$data['user_id'] = $userId;

			$data['subscribed_date'] = Factory::getDate()->toSql();

			$this->save($data);
		}
	}

	/**
	 * Method to assign todos to user from current path.
	 *
	 * @param   Array  $data  path id & user Id
	 *
	 * @return void.
	 *
	 * @throws Exception
	 */
	public function addTodo($data)
	{
		if (!$data['path_id'])
		{
			$this->setError(Text::_('COM_JLIKE_INVALID_PATH_ID'));

			return false;
		}

		// Create a new query object.
		$query = $this->db->getQuery(true);

		// Select all records from the todo rules table where rule_set_id is exactly equal to path_id .
		$query->select('npr.*,c.*,c.id as content_id');
		$query->from($this->db->quoteName('#__jlike_pathnode_graph', 'npr'));
		$query->join('INNER', $this->db->quoteName('#__jlike_content', 'c') .
		' ON (' . $this->db->quoteName('c.id') . ' = ' . $this->db->quoteName('npr.node') . ')');
		$query->join('INNER', $this->db->quoteName('#__jlike_paths', 'p') .
		' ON (' . $this->db->quoteName('p.path_id') . ' = ' . $this->db->quoteName('npr.path_id') . ')');
		$query->where($this->db->quoteName('p.path_id') . ' = ' . (int) $data['path_id']);

		// Reset the query using our newly populated query object.
		$this->db->setQuery($query);
		$result = $this->db->loadObjectList();

		if (!empty($result))
		{
			foreach ($result as $todo)
			{
					$Todos = array();
					$Todos['ordering']      = $todo->order;
					$Todos['content_id']    = $todo->content_id;
					$Todos['created_by']    = (int) $data['user_id'];
					$Todos['assigned_to']   = (int) $data['user_id'];
					$Todos['assigned_by']   = (int) $data['user_id'];

					$Todos['created_date']  = Factory::getDate()->toSql();
					$Todos['modified_date'] = Factory::getDate()->toSql();

					$Todos['status']        = 'I';
					$Todos['title']         = $todo->title;
					$Todos['type']          = 'self';

					// Insert the object into the  table.
					$this->obj_todo->save($Todos);
			}
		}
	}

	/**
	 * Method to check onafter path completion.
	 *
	 * @param   array  $data  path id & user Id
	 *
	 * @return void.
	 *
	 * @throws Exception
	 */
	public function checkParentPathCompletion($data)
	{
		$user = Factory::getuser($data['user_id']);

		if (!$user->id)
		{
			$this->setError(Text::_('COM_JLIKE_INVALID_USER'));

			return false;
		}

		if (!$data['path_id'])
		{
			$this->setError(Text::_('COM_JLIKE_INVALID_PATH_ID'));

			return false;
		}

		$pathUserModel = BaseDatabaseModel::getInstance('PathUser', 'JLikeModel');
		$pathsNodeModel = BaseDatabaseModel::getInstance('Pathnodegraphs', 'JlikeModel');

		$pathsNodeModel->setState('filter.node_content', $data['path_id']);
		$pathsNodeModel->setState('filter.state', '1');
		$pathsNodeModel->setState('filter.isPath', '1');
		$parentPathsList = $pathsNodeModel->getItems();

		foreach ($parentPathsList as $parent)
		{
			if ($parent->rgt)
			{
				$nextPathData['path_id']  = $parent->rgt;
				$nextPathData['user_id']  = $data['user_id'];

				if ($this->save($nextPathData) === true)
				{
				}
			}

			$modelPathsNodeGraphs = BaseDatabaseModel::getInstance('Pathnodegraphs', 'JlikeModel');
			$modelPathsNodeGraphs->setState('filter.path_id', $parent->path_id);
			$modelPathsNodeGraphs->setState('filter.state', '1');
			$modelPathsNodeGraphs->setState('filter.isPath', '1');
			$modelPathsNodeGraphs->setState('filter.this_compulsory', '1');
			$childPaths = $modelPathsNodeGraphs->getItems();

			$statusCount = 0;

			foreach ($childPaths as $childPath)
			{
				$pathUser = $pathUserModel->getPathUserDetails($childPath->node, $data['user_id']);

				if ($pathUser->status == 'C')
				{
					$statusCount++;
				}
			}

			if ($statusCount == count($childPaths))
			{
				// Find the ID of path user table on path id and user id for Update the Path status for Given User
				$pathUserDetails = $pathUserModel->getPathUserDetails($parent->path_id, $data['user_id']);
				$pathStatus = $pathUserModel->MakeStatus($parent->path_id, $data['user_id']);

				$newData = array();
				$newData['path_user_id'] = $pathUserDetails->path_user_id;
				$newData['status']       = $pathStatus;
				$newData['path_id']      = $parent->path_id;
				$newData['user_id']      = $data['user_id'];

				// Insert the object into the  table.
				if ($pathUserModel->save($newData))
				{
				}
			}
		}
	}
}
