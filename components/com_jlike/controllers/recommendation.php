<?php
/**
 * @package     JLike
 * @subpackage  com_jlike
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access
defined('_JEXEC') or die;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Response\JsonResponse;

use Joomla\Utilities\ArrayHelper;

/**
 * Recommendation controller class.
 *
 * @since  1.6
 */
class JlikeControllerRecommendation extends FormController
{
	/**
	 * Constructor
	 *
	 * @throws Exception
	 */
	public function __construct()
	{
		$this->view_list = 'recommendations';
		parent::__construct();
	}

	/**
	 * Method to get a model object, loading it if required.
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  object  The model.
	 *
	 * @since   3.0.0
	 */
	public function &getModel($name = 'Recommendation', $prefix = 'JlikeModel', $config = array())
	{
		return parent::getModel($name, $prefix, array('ignore_request' => true));
	}

	/**
	 * Method to save.
	 *
	 * @return  boolean  True on success
	 *
	 * @since   3.0.0
	 */
	public function save()
	{
		$app = Factory::getApplication();
		$input = $app->input;
		$post  = $input->post;

		// Get the input
		$userIds = $post->get('uid', array(), 'array');
		$user = Factory::getUser();
		$data = array();

		if (!is_array($userIds) || count($userIds) < 1)
		{
			$app->enqueueMessage(Text::_('COM_JLIKE_NO_ITEM_SELECTED'), 'error');

			return false;
		}

		// Sanitize the input
		$userIds = ArrayHelper::toInteger($userIds);

		$redirectUrl = $post->get('redirect_url', '', 'STR');

		$notify  = $post->get('notify', '', 'INT');
		$data['element'] = $post->get('client', '', 'STR');
		$data['url'] = $post->get('url', '', 'STR');
		$data['element_id'] = $post->get('element_id', '', 'INT');
		$data['title'] = $post->get('title', '', 'STR');
		$data['img'] = $post->get('img', '', 'STR');
		$data['assigned_by'] = $user->id;
		$data['type'] = $post->get('type', 'reco', 'STR');
		$data['start_date'] = $post->get('start_date', '', 'DATETIME');
		$data['due_date'] = $post->get('due_date', '', 'DATETIME');
		$data['status'] = $post->get('status', 'I', 'STR');
		$data['state'] = $post->get('state', '1', 'INT');
		$data['created_by'] = $post->get('created_by', '', 'INT');
		$data['sender_msg'] = $post->get('sender_msg', '', 'STR');
		$data['context'] = $post->get('context', '', 'STR');

		$error = true;
		$msg = Text::_('COM_JLIKE_TASK_ERROR');

		foreach ($userIds as $userId)
		{
			$data['assigned_to'] = $userId;

			// Get the model
			$model = $this->getModel();

			// Save the items.
			$result = $model->setTodo($data, $notify);

			if ($result == true)
			{
				$error = false;
				$msg = Text::_('COM_JLIKE_TASK_SUCCESS');
				$this->setMessage(Text::sprintf('COM_JLIKE_SAVE_SUCCESS', count($userIds)));
			}
			else
			{
				$this->setMessage(Text::_('COM_JLIKE_SAVE_FAILED'), 'error');
			}
		}

		if ($redirectUrl)
		{
			$this->setRedirect($redirectUrl);
		}
		else
		{
			echo new JsonResponse($data, $msg, $error);
			$app->close();
		}
	}
}
