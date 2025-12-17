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

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Response\JsonResponse;
use Joomla\Utilities\ArrayHelper;

/**
 * VAnnotation controller class.
 *
 * @package     Jlike
 * @subpackage  Jlike
 * @since       2.2
 */
class JlikeControllerAnnotations extends AdminController
{
	/**
	 * Proxy for getModel.
	 *
	 * @param   string  $name    name.
	 * @param   string  $prefix  prefix.
	 *
	 * @return object|boolean Object on success, false on failure.
	 *
	 * @since	1.6
	 */
	public function getModel($name = 'annotation', $prefix = 'JlikeModel')
	{
		return parent::getModel($name, $prefix, array('ignore_request' => true));
	}

	/**
	 * Method to save the submitted ordering values for records via AJAX.
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function saveOrderAjax()
	{
		// Get the input
		$input = Factory::getApplication()->getInput();
		$pks   = $input->post->get('cid', array(), 'array');
		$order = $input->post->get('order', array(), 'array');

		// Sanitize the input
		ArrayHelper::toInteger($pks);
		ArrayHelper::toInteger($order);

		// Get the model
		$model = $this->getModel();

		// Save the ordering
		$return = $model->saveorder($pks, $order);

		if ($return)
		{
			echo "1";
		}

		// Close the application
		Factory::getApplication()->close();
	}

	/**
	 * Method to get submitted comment records via AJAX.
	 *
	 * @return object
	 */
	public function getData()
	{
		$app     = Factory::getApplication();
		$context = "com_jlike.annotations";

		$commentRes = array();
		$input      = Factory::getApplication()->getInput();

		$data = array();

		$data['content_id'] = $input->getInt('content_id', '');
		$data['type']       = $input->getString('subtype', '');
		$data['client']     = $input->getString('client', '');

		$result = new stdClass;

		if (empty($data['content_id']) || (empty($data['type']) && empty($data['client'])))
		{
			$result->success   = false;
			$result->result    = Text::_("COM_JLIKE_INVALID_REQUEST");

			echo new JsonResponse($result);

			Factory::getApplication()->close();
		}

		// Load AnnotationForm Model
		$model = BaseDatabaseModel::getInstance('Annotations', 'JlikeModel');
		$model->setState("content_id", $data['content_id']);

		// Get context
		$data['context'] = $input->getString('context', '');
		$model->setState("context", $data['context']);

		$data['parent_id']  = $input->getInt('parent_id', '0');
		$model->setState("parent_id", $data['parent_id']);

		$model->setState("type", $data['type']);

		list($plg_type, $plg_name) = explode(".", $data['client']);

		$data['plg_type']   = $plg_type;
		$data['plg_name']   = $plg_name;

		$data['limitstart'] = $input->getInt('limitstart', '0');
		$model->setState("list.start", $data['limitstart']);

		$data['limit']      = $input->getInt('limit', '10');
		$model->setState("list.limit", $data['limit']);

		$data['ordering'] = $input->getString('ordering', '');
		$model->setState("list.ordering", $data['ordering']);

		$data['direction']      = $input->getString('direction', 'DESC');
		$model->setState("list.direction", $data['direction']);

		$data['note']       = $input->getInt('note', '0');
		$model->setState("note", $data['note']);

		$annotations = $model->getAnnotations($data);

		$result->success = true;
		$result->result  = $annotations;

		$AnnotationFormModel = BaseDatabaseModel::getInstance('AnnotationForm', 'JlikeModel');
		$result->total       = $AnnotationFormModel->getTotal($data['content_id'], $data['type']);

		echo new JsonResponse($result);

		Factory::getApplication()->close();
	}
}
