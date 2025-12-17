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
require_once JPATH_SITE . '/components/com_jlike/models/likes.php';
require_once JPATH_SITE . '/components/com_jlike/models/like.php';
require_once JPATH_SITE . '/components/com_jlike/models/likeform.php';

/**
 * Class for checkin to tickets for mobile APP
 *
 * @package     JLike
 * @subpackage  component
 * @since       1.0
 */
class JlikeApiResourceLikes extends ApiResource
{
	/**
	 * Get likes
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function get()
	{
		$app = Factory::getApplication();
		$context = "com_jlike.annotations";

		$user  = $this->plugin->get('user');

		$commentRes = array();
		$input = Factory::getApplication()->getInput();

		$data = array();

		$data['content_id']    = $input->get('content_id', '', 'INT');

		$result = new stdClass;

		if (empty($data['content_id']))
		{
			$result->success = false;
			$result->data    = Text::_("COM_API_INVALID_REQUEST");

			$this->plugin->setResponse($result);

			return;
		}

		$user  = $this->plugin->get('user');

		// Load Likes Model
		$model       = BaseDatabaseModel::getInstance('Likes', 'JlikeModel');
		$model->setState("content_id", $data['content_id']);

		$data['userid']         = $this->plugin->get('user')->id;
		$model->setState("userid", $data['userid']);

		$data['annotation_id'] = $input->get('annotation_id', '', 'INT');
		$model->setState("annotation_id", $data['annotation_id']);

		$data['client']     = $input->get('client', '', 'STRING');
		$model->setState("client", $data['client']);

		$data['subtype']    = $input->get('subtype', '', 'STRING');
		$model->setState("subtype", $data['subtype']);

		$data['plg_type']   = $input->get('plg_type', '', 'STRING');
		$data['plg_name']   = $input->get('plg_name', '', 'STRING');

		$data['limitstart'] = $input->get('limitstart', '0', 'INT');
		$model->setState("list.start",  $data['limitstart']);

		$data['limit']      = $input->get('limit', '10', 'INT');
		$model->setState("list.limit", $data['limit']);

		$likes = $model->getLikes($data);

		$result->success       = true;
		$result->data          = new stdclass;
		$result->data->results = $likes;

		$likeFormModel = BaseDatabaseModel::getInstance('Likeform', 'JlikeModel');
		$likeFormModel->setState("content_id", $data['content_id']);
		$likeFormModel->setState("annotation_id", $data['annotation_id']);
		$likeFormModel->setState("userid", $data['userid']);

		$result->data->total_likes      = $likeFormModel->getTotalsLike();
		$result->data->total_dislikes   = $likeFormModel->getTotalsDisLike();
		$result->data->is_liked         = $likeFormModel->isLiked();
		$result->data->is_disliked      = $likeFormModel->isDisLiked();
		$result->data->id               = $likeFormModel->getId();

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
		$user  = $this->plugin->get('user');

		$data = array();
		$data['id']            = $post->get('id', '', 'INT');
		$data['content_id']    = $post->get('content_id', '', 'INT');

		$data['subtype']       = $post->get('subtype', '', 'STRING');
		$data['client']        = $post->get('client', '', 'STRING');

		list($plg_type, $plg_name) = explode(".", $data['client']);

		$data['plg_type']   = $plg_type;
		$data['plg_name']   = $plg_name;

		$data['annotation_id'] = $post->get('annotation_id', '0', 'INT');
		$data['dislike']       = $post->get('dislike', '', 'string');
		$data["user"]          = $this->plugin->get('user');
		$data["userid"]        = $this->plugin->get('user')->id;

		if ($data['dislike'] == 'true')
		{
			$data['dislike'] = 1;
			$data['like'] = 0;
		}

		if ($data['dislike'] == 'false')
		{
			$data['dislike'] = 0;
			$data['like'] = 1;
		}

		if (!isset($data['content_id']))
		{
			$obj->data = new stdClass;
			$this->plugin->setResponse($obj);

			return;
		}

		// Load LikeForm Model
		$model = BaseDatabaseModel::getInstance('LikeForm', 'JlikeModel');
		$model->setState("content_id", $data['content_id']);
		$model->setState("userid", $data['userid']);

		$result = new stdClass;

		if ($likeId = $model->save($data))
		{
			// Get total likes of content
			$getTotalLikes = $model->getTotalsLike();

			// Get total dislikes of content
			$getTotalsDisLike = $model->getTotalsDisLike();

			$result->success        = true;
			$result->id             = $likeId;
			$result->total_likes    = $getTotalLikes;
			$result->total_dislikes = $getTotalsDisLike;
		}
		else
		{
			$result->success = false;
			$result->message = Text::_("COM_JLIKE_ERROR_ADDING_COMMENT");
			$this->plugin->setResponse($data);
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
		$id = $input->get('id', '', 'INT');

		$result = new stdClass;

		if (empty($id))
		{
			$result->success = false;
			$result->message = Text::_("COM_JLIKE_DELETE_ERROR_NO_ID");
			$this->plugin->setResponse($result);

			return;
		}

		$model = BaseDatabaseModel::getInstance('like', 'JlikeModel');

		if ($model->delete($id))
		{
			$result->success = true;
		}

		$this->plugin->setResponse($result);
	}
}
