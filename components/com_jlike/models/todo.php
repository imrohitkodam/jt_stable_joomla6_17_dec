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
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Form\Form;
use Joomla\CMS\MVC\Model\FormModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Language\Text;

JLoader::import('components.com_jlike.models.todos', JPATH_SITE);
JLoader::import('components.com_jlike.helpers.path', JPATH_SITE);

use Joomla\Utilities\ArrayHelper;

/**
 * Jlike model.
 *
 * @since  1.6
 */
class JlikeModelTodo extends FormModel
{
	private $item = null;

	/**
	 * Method to get the table
	 *
	 * @param   string  $type    Name of the Table class
	 * @param   string  $prefix  Optional prefix for the table class name
	 * @param   array   $config  Optional configuration array for Table object
	 *
	 * @return  Table|boolean Table if found, boolean false on failure
	 */
	public function getTable($type = 'Todos', $prefix = 'JlikeTable', $config = array())
	{
		$this->addTablePath(JPATH_ADMINISTRATOR . '/components/com_jlike/tables');

		return Table::getInstance($type, $prefix, $config);
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
		$id    = (!empty($data['id'])) ? $data['id'] : (int) $this->getState('todos.id');
		$state = (!empty($data['state'])) ? 1 : 0;
		$user  = Factory::getUser();
		$db = Factory::getDbo();

		$table = $this->getTable();

		/* This is the way to find already todo exist
		 * $todos_model = BaseDatabaseModel::getInstance('Todos', 'JLikeModel');
		$todos_model->setState('filter.content_id', $data['content_id']);
		$todos_model->setState('filter.assigned_to', $data['assigned_to']);
		$already_existed_todo  = $todos_model->getItems();*/
		$already_existed_todo = array();

		if (!empty($data['content_id']) && !empty($data['assigned_to']))
		{
			/* This query is temprary solution*/
			// Create a new query object.
			$query = $db->getQuery(true);

			// Select all records from the todo rules table where rule_set_id is exactly equal to path_id .
			$query->select('*');
			$query->from('`#__jlike_todos` AS t');
			$query->where($db->quoteName('t.content_id') . ' = ' . $db->quote($data['content_id']));
			$query->where($db->quoteName('t.assigned_to') . ' = ' . $db->quote($data['assigned_to']));

			// Reset the query using our newly populated query object.
			$db->setQuery($query);
			$already_existed_todo = $db->loadObjectList();
		}

		if (count($already_existed_todo) == 0)
		{
			if ($table->save($data) === true)
			{

				if ($id !== 0)
				{
					// @TODO:On After TODO update
					PluginHelper::importPlugin('tjpath');
					Factory::getApplication()->triggerEvent('onAfterTodoUpdate', array($table->id));
				}

				if ($data['status'] == 'C')
				{
					$app = Factory::getApplication();
					$app->setUserState("com_jlike.todoSave", "1");
					$app->setUserState("com_jlike.todoSaveMsg", Text::_('COM_JLIKE_TODO_REDIRECTION_SUCCESS_MSG'));
					$app->setUserState("com_jlike.todoSaveRedirectTimestamp", time());

					$pathHelper	= new ComjlikePathHelper;
					$pathid 	= $pathHelper->getSubPathId($id);

					// Set only if present
					if (!empty($pathid))
					{
						$app->setUserState("com_jlike.pathId", $pathid);
					}

					// @TODO:On after todo completion
					PluginHelper::importPlugin('tjpath');
					Factory::getApplication()->triggerEvent('onAfterTodoCompletion', array($table->id));
				}

				return $table->id;
			}
			else
			{
				return false;
			}
		}
		else
		{
			return false;
		}
	}

	/**
	 * Abstract method for getting the form from the model.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  Form|boolean  A Form object on success, false on failure
	 *
	 * @since   12.2
	 */
	public function getForm($data = array(), $loadData = true)
	{
	}

	/**
	 * Method for getting the content from todo id.
	 *
	 * @param   INT  $todo_id  Todo id.
	 *
	 * @return  Object  A object on success, false on failure
	 *
	 * @since   12.2
	 */
	public function getContent($todo_id)
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true);

		// Find the content id on Todo ID & Use content ID to find the in which path this Todo is
		$query->select(array('t.content_id,t.assigned_by,t.assigned_to,t.created_date,t.start_date,t.due_date,t.status,t.title as todo_title'));
		$query->select(array('content.element_id,content.url,content.element,content.title as content_title,content.img'));
		$query->from($db->qn('#__jlike_todos', 't'));
		$query->join('INNER',
					$db->qn('#__jlike_content', 'content') . ' ON (' . $db->qn('t.content_id') . ' = ' . $db->qn('content.id') . ')');
		$query->where($db->qn('t.id') . ' = ' . $todo_id);

		// Reset the query using our newly populated query object.
		$db->setQuery($query);

		return $content = $db->loadObject();
	}
}
