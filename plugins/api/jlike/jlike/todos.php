<?php
/**
 * @version    SVN: <svn_id>
 * @package    Jlike
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2016 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

require_once JPATH_SITE . '/components/com_jlike/models/recommendationform.php';
require_once JPATH_SITE . '/components/com_jlike/models/recommendations.php';

/**
 * Class for checkin to tickets for mobile APP
 *
 * @package     Jlike
 * @subpackage  component
 * @since       1.0
 */

class JlikeApiResourceTodos extends ApiResource
{
	/**
	 * Create new todo
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function post()
	{
		$input = Factory::getApplication()->getInput();
		$post  = $input->post;

		$data = array();

		$data['id']          = $input->get('id', '', 'INT');
		$data['content_id']  = $input->get('content_id', '', 'INT');
		$data['type']        = $post->get('type', 'todos', 'string');

		$data['context']     = $post->get('subtype', '', 'string');
		$data['client']      = $post->get('client', '', 'string');

		list($plg_type, $plg_name) = explode(".", $data['client']);

		$data['plg_type']    = $plg_type;
		$data['plg_name']    = $plg_name;
		$data['title']       = $post->get('title', '', 'string');
		$data['sender_msg']  = $post->get('sender_msg', '', 'string');

		$assigned_by = $post->get('assigned_by', '', 'INT');

		if (!empty($assigned_by) && $assigned_by != 0)
		{
			$data['assigned_by'] = $assigned_by;
		}

		$assigned_to = $post->get('assigned_to', '', 'INT');

		if (!empty($assigned_to) && $assigned_to != 0)
		{
			$data['assigned_to'] = $assigned_to;
		}

		$data['start_date']  = $post->get('start_date', '', 'DATETIME');
		$data['due_date']    = $post->get('due_date', '', 'DATETIME');
		$data['parent_id']   = $post->get('parent_id', '', 'INT');
		$data['status']      = $post->get('status', 'I', 'string');
		$data['state']       = $post->get('state', '1', 'INT');

		$result = new stdClass;

		if (empty($data['content_id']) || $data['content_id'] == 0 || empty($data['sender_msg']))
		{
			$result->success = false;
			$result->message = Text::_("COM_JLIKE_ERROR_FETCHING_TODOS");

			$this->plugin->setResponse($result);

			return false;
		}

		// Load RecommendationForm Model
		$model       = BaseDatabaseModel::getInstance('RecommendationForm', 'JlikeModel');

		// Save data in the database and return the repsonse.
		if ($todos_id = $model->save($data))
		{
			$result->success = true;
			$data = $model->getData($todos_id, $data);

			$assigned_by         = new stdClass;
			$assigned_by->id     = $data->assigned_by;
			$assigned_by->name   = Factory::getUser($data->assigned_by)->name;
			$assigned_by->avatar = '';

			$assigned_to = new stdClass;
			$assigned_to->id     = $data->assigned_to;
			$assigned_to->name   = Factory::getUser($data->assigned_to)->name;
			$assigned_to->avatar = '';

			$result->id          = $data->id;
			$result->title       = $data->title;
			$result->sender_msg  = $data->sender_msg;
			$result->start_date  = $data->start_date;
			$result->due_date    = $data->due_date;
			$result->parent_id   = $data->parent_id;
			$result->assigned_by = $assigned_by;
			$result->assigned_to = $assigned_to;
		}
		else
		{
			$result->success = false;
			$result->message = Text::_("COM_JLIKE_ERROR_ADDING_TODOS");
		}

		$this->plugin->setResponse($result);
	}

	/**
	 * Get todos
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function get()
	{
		$model       = BaseDatabaseModel::getInstance('Recommendations', 'JlikeModel');

		$input = Factory::getApplication()->getInput();
		$data['content_id']  = $input->get('content_id', '', 'INT');

		$result = new stdClass;

		if (empty($data['content_id']))
		{
			$result->success = false;
			$result->data    = Text::_("COM_API_INVALID_REQUEST");

			$this->plugin->setResponse($result);

			return;
		}

		$model->setState("content_id", $data['content_id']);

		$data['type']  = $input->get('type', 'todos', 'STRING');
		$model->setState("type", $data['type']);

		$data['context']  = $input->get('subtype', '', 'STRING');
		$model->setState("context", $data['context']);

		$data['client']  = $input->get('client', '', 'STRING');

		list($plg_type, $plg_name) = explode(".", $data['client']);

		$data['plg_type'] = $plg_type;
		$data['plg_name'] = $plg_name;

		$data['supress_data'] = $input->get('supress_data', '', 'BOOL');

		$data['filters']  = $input->get('filters', '', 'INT');

		$data['limitstart'] = $input->get('limitstart', '0', 'INT');
		$model->setState("list.start",  $data['limitstart']);

		$data['limit']      = $input->get('limit', '10', 'INT');
		$model->setState("list.limit", $data['limit']);

		$data['status']     = $input->get('status', '', 'STRING');

		$model->setState("status", $data['status']);

		$data['state']     = $input->get('state', '', 'INT');
		$model->setState("state", $data['state']);

		// Save data in the database and return the repsonse.
		$result = new stdClass;
		$result->data = new stdClass;

		if ($todos = $model->getTodos($data))
		{
			$result->success = true;
			$result->data->result = $todos;
			$result->data->total = $model->getTotalRecommendation($data['content_id']);
		}
		else
		{
			$result->success = false;
			$result->message = Text::_("COM_JLIKE_ERROR_FETCHING_TODOS");
		}

		$this->plugin->setResponse($result);
	}

	/**
	 * Delete todo
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function delete()
	{
		$input = Factory::getApplication()->getInput();

		$data = array();
		$data['id'] = $input->get('id', '', 'INT');

		$result = new stdClass;

		if (empty($data['id']))
		{
			$result->success = false;
			$result->message = Text::_("COM_JLIKE_DELETE_ERROR_NO_ID");
			$this->plugin->setResponse($result);

			return;
		}

		$model = BaseDatabaseModel::getInstance('RecommendationForm', 'JlikeModel');

		$result = new stdClass;
		$result->success = new stdclass;

		if ($todoid = $model->delete($data))
		{
			$result->success = true;
		}
		else
		{
			$result->success = false;
			$result->message = Text::_("COM_JLIKE_ERROR_IN_DELETING_ITEM");
		}

		$this->plugin->setResponse($result);
	}
}
