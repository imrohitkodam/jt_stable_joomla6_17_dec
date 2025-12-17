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
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Response\JsonResponse;


/**
 * VAnnotation controller class.
 *
 * @package     Jlike
 * @subpackage  Jlike
 * @since       3.0.0
 */

class JlikeControllerAnnotationForm extends AdminController
{
	/**
	 * Proxy for getModel.
	 *
	 * @param   string  $name    name.
	 * @param   string  $prefix  prefix.
	 *
	 * @return Object|boolean Object on success, false on failure.
	 *
	 * @since   3.0.0
	 */
	public function getModel($name = 'AnnotationForm', $prefix = 'JlikeModel')
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));

		return $model;
	}

	/**
	 * Method to get required parameters to perform actions on comment view.
	 *
	 * @return Object
	 */
	public function getInitData()
	{
		$app = Factory::getApplication();
		$post  = $app->input;
		$data = array();

		// Item Url
		$data['url'] = $post->getString('url', '');

		// E.g comment
		$data['type'] = $post->getString('type', '');

		// E.g collaborator
		$data['subtype'] = $post->getString('subtype', '');

		// E.g com_xyz
		$data['element'] = $post->getString('client', '');

		// Item Id
		$data['element_id'] = $post->getInt('cont_id', '');

		// Item title
		$data['title'] = $post->getString('title', '');

		$client = $post->getString('client', '');

		list($data['plg_type'], $data['plg_name']) = explode(".", $client);

		// Load AnnotationForm Model
		$model = BaseDatabaseModel::getInstance('contentform', 'JlikeModel');

		$result = new stdclass;

		if ($content_id = $model->getContentID($data))
		{
			$result->success = true;
			$result->content_id = $content_id;

			// Get the users to mention
			if ($data['type'] == "annotations")
			{
				// Get the model
				$model = $this->getModel();
				$result->userslist = $model->getUsersList($data);
				$result->usersInfo = $model->getUserInfo($data);
			}
		}
		else
		{
			$result->success = false;
			$result->message = Text::_("COM_JLIKE_ERROR_IN_INIT");
		}

		echo new JsonResponse($result);

		Factory::getApplication()->close();
	}

	/**
	 * Save Annotation
	 *
	 * @return  json comment id
	 *
	 * @since   3.0.0
	 */
	public function save()
	{
		$input = Factory::getApplication()->getInput();
		$post  = $input->post;

		$data = array();
		$result = new stdClass;

		$data['id']         = $post->getInt('annotation_id', '');
		$data['content_id'] = $post->getInt('content_id', '');
		$data['client']     = $post->getString('client', '');
		$data['type']       = $post->getString('subtype', '');
		$data['context']    = $post->getString('context', '');

		list($plg_type, $plg_name) = explode(".", $data['client']);

		$data['plg_type']   = $plg_type;
		$data['plg_name']   = $plg_name;
		$data['annotation'] = $post->getString('annotation', '');
		$data['parent_id']  = $post->getInt('parent_id', '0');
		$data['state']      = $post->getInt('state', '1');
		$data['note']       = $post->getInt('note', '0');
		$data['supress_data'] = $post->getInt('supress_data', '0');

		$user  = Factory::getUser();

		$data["user"]       = $user;
		$data["user_id"]    = $user->id;

		if (empty($data['content_id']) || empty($data['annotation']) || (empty($data['type']) && empty($data['client'])))
		{
			$result->success = false;
			$result->result    = Text::_("COM_JLIKE_INVALID_REQUEST");
			echo new JsonResponse($result);
			Factory::getApplication()->close();
		}

		// Get the model
		$model = $this->getModel();

		$id    = (!empty($data['id'])) ? $data['id'] : (int) $model->getState('annotation.id');

		if ($id)
		{
			// Check the user can edit this item
			$authorised = ($user->authorise('core.edit', 'com_jlike') || $user->authorise('core.edit.own', 'com_jlike')) ? true : false;
		}
		else
		{
			// Check the user can create new items in this section
			$authorised = $user->authorise('core.create', 'com_jlike');
		}

		if ($authorised !== true)
		{
			$result->success = false;
			$result->result  = Text::_('JERROR_ALERTNOAUTHOR');
			echo new JsonResponse($result);
			Factory::getApplication()->close();
		}

		if ($annotation_id = $model->save($data))
		{
			$result->success = true;

			if ($data['supress_data'] != '1')
			{
				$result->result = $model->getData($annotation_id, $data);
			}

			$result->total = $model->getTotal($data['content_id'], $data['type']);
		}
		else
		{
			$result->success = false;
			$result->message = Text::_("COM_JLIKE_ERROR_ADDING_COMMENT");
		}

		echo new JsonResponse($result);
		Factory::getApplication()->close();
	}

	/**
	 * Delete Annotation
	 *
	 * @return  json comment id
	 *
	 * @since   3.0.0
	 */
	public function delete()
	{
		$input = Factory::getApplication()->getInput();

		$data = array();
		$data['id'] = $input->getInt('id', '');

		$result = new stdClass;

		// Get the model
		$model = $this->getModel();

		if (empty($data['id']))
		{
			$result->success = false;
			$result->message = Text::_("COM_JLIKE_DELETE_ERROR_NO_ID");
			echo new JsonResponse($result);
			Factory::getApplication()->close();
		}

		$id = (!empty($data['id'])) ? $data['id'] : (int) $model->getState('annotation.id');

		if (Factory::getUser()->authorise('core.delete', 'com_jlike') !== true)
		{
			$result->success = false;
			$result->message = Text::_("JERROR_ALERTNOAUTHOR");
			echo new JsonResponse($result);
			Factory::getApplication()->close();
		}

		if ($model->delete($data))
		{
			$result->success = true;
		}
		else
		{
			$result->success = false;
			$result->message = Text::_("COM_JLIKE_ERROR_IN_DELETING_ITEM");
		}

		echo new JsonResponse($result);
		Factory::getApplication()->close();
	}
}
