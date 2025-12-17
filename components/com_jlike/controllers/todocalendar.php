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
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;

/**
 * Class for Jlike Todo Calendar List Controller
 *
 * @package  Jlike
 * @since    2.0
 */
class JlikeControllerTodoCalendar extends JLikeController
{
	/**
	 * Method to Get todos
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function getTodo()
	{
		require_once JPATH_SITE . '/components/com_jlike/models/recommendations.php';

		$model  = BaseDatabaseModel::getInstance('Recommendations', 'JlikeModel');
		$result = new stdClass;
		$input  = Factory::getApplication()->getInput();
		$data['type']  = $input->get('type', 'todos', 'STRING');
		$model->setState("type", $data['type']);

		$data['context']  = $input->get('subtype', '', 'STRING');
		$model->setState("context", $data['context']);

		$data['assigned_to']  = $input->get('assigned_to', '', 'INT');
		$model->setState("assigned_to", $data['assigned_to']);

		$data['client']  = $input->get('client', '', 'STRING');
		$model->setState("client", $data['client']);

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
			// @TODO add a better logic to make URL SEF. Refer - Task #121864
			foreach ($todos as $todo => $value)
			{
				$link = Route::_($todos[$todo]->content_url, false);
				$todos[$todo]->content_url = $link;
			}

			$result->success = true;
			$result->data->result = $todos;
		}
		else
		{
			$result->success = false;
			$result->message = Text::_("COM_JLIKE_ERROR_FETCHING_TODOS");
		}

		echo json_encode($result);
		Factory::getApplication()->close();
	}
}
