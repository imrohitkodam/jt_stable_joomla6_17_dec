<?php
/**
 * @version    SVN: <svn_id>
 * @package    JLike
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2016 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
require_once JPATH_SITE . '/components/com_jlike/models/annotationform.php';
require_once JPATH_SITE . '/components/com_jlike/models/annotations.php';

/**
 * Class for checkin to tickets for mobile APP
 *
 * @package     JLike
 * @subpackage  component
 * @since       1.0
 */
class JlikeApiResourceAnnotations extends ApiResource
{
	/**
	 * Get Comments
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function get()
	{
		$app = Factory::getApplication();
		$context = "com_jlike.annotations";

		$commentRes = array();
		$input = Factory::getApplication()->getInput();

		$data = array();

		$data['content_id'] = $input->getInt('content_id', '');
		$data['type'] = $input->getString('subtype', '');
		$data['client']  = $input->getString('client', '');

		$result = new stdClass;

		if (empty($data['content_id']) || (empty($data['type']) && empty($data['client'])))
		{
			$result->success = false;
			$result->result    = Text::_("COM_API_INVALID_REQUEST");

			$this->plugin->setResponse($result);

			return;
		}

		// Load AnnotationForm Model
		$model       = BaseDatabaseModel::getInstance('Annotations', 'JlikeModel');
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
		$model->setState("list.start",  $data['limitstart']);

		$data['limit']      = $input->getInt('limit', '10');
		$model->setState("list.limit", $data['limit']);

		$data['ordering'] = $input->getString('ordering', '');
		$model->setState("list.ordering",  $data['ordering']);

		$data['direction']      = $input->getString('direction', 'DESC');
		$model->setState("list.direction", $data['direction']);

		$data['note']       = $input->getInt('note', '0');
		$model->setState("note", $data['note']);

		$data["user"]       = $this->plugin->get('user');
		$data["user_id"]    = $this->plugin->get('user')->id;
		
		$annotations = $model->getAnnotations($data);

		$result->success       = true;
		$result->result = $annotations;

		$AnnotationFormModel = BaseDatabaseModel::getInstance('AnnotationForm', 'JlikeModel');
		$result->total   = $AnnotationFormModel->getTotal($data['content_id'], $data['type']);

		$this->plugin->setResponse($result);
	}

	/**
	 * Save Annotations
	 *
	 * @return  json comment id
	 *
	 * @since   1.0
	 */
	public function post()
	{
		$input = Factory::getApplication()->getInput();
		$post  = $input->post;

		$data = array();

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

		$data["user"]       = $this->plugin->get('user');
		$data["user_id"]    = $this->plugin->get('user')->id;

		if (empty($data['content_id']) || empty($data['annotation']) || (empty($data['type']) && empty($data['client'])))
		{
			$result->success = false;
			$result->result    = Text::_("COM_API_INVALID_REQUEST");

			$this->plugin->setResponse($result);

			return;
		}

		// Load AnnotationForm Model
		$model = BaseDatabaseModel::getInstance('AnnotationForm', 'JlikeModel');

		$result = new stdClass;

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

		$this->plugin->setResponse($result);
	}

	/**
	 * Delete method
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function delete()
	{
		$input = Factory::getApplication()->getInput();

		$data = array();
		$data['id'] = $input->getInt('id', '');

		$result = new stdClass;

		if (empty($data['id']))
		{
			$result->success = false;
			$result->message = Text::_("COM_JLIKE_DELETE_ERROR_NO_ID");
			$this->plugin->setResponse($result);

			return;
		}

		$model = BaseDatabaseModel::getInstance('AnnotationForm', 'JlikeModel');

		$result = new stdClass;
		$result->success = new stdclass;

		if ($model->delete($data))
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
