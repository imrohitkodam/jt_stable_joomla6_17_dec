<?php
/**
 * @version    SVN: <svn_id>
 * @package    Jlike
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2016 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

require_once JPATH_SITE . '/components/com_jlike/models/recommendationform.php';
require_once JPATH_SITE . '/components/com_jlike/models/recommendations.php';
require_once JPATH_SITE . '/components/com_jlike/models/annotationform.php';
require_once JPATH_SITE . '/components/com_jlike/models/annotations.php';
require_once JPATH_SITE . '/components/com_jlike/models/contentform.php';
require_once JPATH_SITE . '/components/com_jlike/models/annotationform.php';

/**
 * Class for checkin to tickets for mobile APP
 *
 * @package     Jlike
 * @subpackage  component
 * @since       1.0
 */

class JlikeApiResourceHybridTodos extends ApiResource
{
	protected $recommendationFormModel;

	protected $recommendationsModel;

	protected $annotationFormModel;

	protected $annotationsModel;

	protected $contentModel;

	/**
	 * Constructor
	 *
	 * @param   array  $config  An array
	 *
	 * @since   12.2
	 * @throws  Exception
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		$this->recommendationFormModel = BaseDatabaseModel::getInstance('RecommendationForm', 'JlikeModel');
		$this->recommendationsModel = BaseDatabaseModel::getInstance('Recommendations', 'JlikeModel');
		$this->annotationFormModel  = BaseDatabaseModel::getInstance('AnnotationForm', 'JlikeModel');
		$this->annotationsModel     = BaseDatabaseModel::getInstance('Annotations', 'JlikeModel');
		$this->contentModel         = BaseDatabaseModel::getInstance('contentform', 'JlikeModel');
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

		$post = Factory::getApplication()->getInput();

		$getContentId               = array();
		$getContentId['url']        = $post->get('url', '', 'url');
		$getContentId['type']       = $post->get('type', '', 'string');
		$getContentId['subtype']    = $post->get('subtype', '', 'string');

		// Set recommendationsModel state
		$this->recommendationsModel->setState("context", $getContentId['subtype']);
		$getContentId['element']    = $post->get('client', '', 'string');
		$getContentId['element_id'] = $post->get('cont_id', '', 'string');
		$getContentId['title']      = $post->get('title', '', 'string');
		$client                     = $post->get('client', '', 'string');

		list($getContentId['plg_type'], $getContentId['plg_name']) = explode(".", $client);

		$result = new stdclass;

		if (empty($getContentId['url']) || empty($getContentId['element_id']) || empty($getContentId['element']))
		{
			$result->success = false;
			$result->data    = Text::_("COM_API_INVALID_REQUEST");

			$this->plugin->setResponse($result);

			return;
		}

		$data  = array();
		$input = Factory::getApplication()->getInput();

		// Get and set limitstart
		$data['limitstart'] = $input->get('limitstart', '0', 'INT');
		$this->recommendationsModel->setState("list.start",  $data['limitstart']);

		// Get and set limit
		$data['limit'] = $input->get('limit', '2', 'INT');
		$this->recommendationsModel->setState("list.limit", $data['limit']);

		// Get and set ordering
		$data['ordering'] = $input->get('ordering', '', 'string');
		$this->recommendationsModel->setState("list.ordering", $data['ordering']);

		// Get and set direction
		$data['direction'] = $input->get('direction', '', 'string');
		$this->recommendationsModel->setState("list.direction", $data['direction']);

		// Get and status
		$data['status'] = $input->get('status', '', 'string');
		$this->recommendationsModel->setState("status", $data['status']);

		// Get content id
		if ($contentId = $this->contentModel->getContentID($getContentId))
		{
			// Set state
			$this->recommendationsModel->setState("content_id", $contentId);

			// Get todos based on content id
			if ($todos = $this->recommendationsModel->getTodos($getContentId))
			{
				$this->annotationsModel->setState("content_id", $contentId);
				$this->annotationsModel->setState("list.limit", $data['limit']);

				$result->success = true;

				// Get todo comments
				foreach ($todos as $key => $val)
				{
					$getContentId['context'] = '';
					$getContentId['context'] = 'reviewer#todo#' . $val->id;

					$this->annotationsModel->setState("context", $getContentId['context']);
					$todos[$key]->comments      = $this->annotationsModel->getHybridAnnotations($getContentId);
					$todos[$key]->totalComments = $this->recommendationsModel->getTotalRecommendation($contentId);
				}

				$userInfo = $this->annotationFormModel->getUserInfo($getContentId);

				$result->data  = new stdclass;
				$result->data->result   = $todos;
				$result->data->userinfo = $userInfo;
				$result->data->total    = $this->recommendationsModel->getTotalRecommendation($contentId);
			}
			else
			{
				$result->success = false;
				$result->message = Text::_("COM_JLIKE_ERROR_FETCHING_TODOS");
			}
		}
		else
		{
			$result->success = false;
			$result->message = Text::_("COM_JLIKE_ERROR_IN_INIT");
		}

		$this->plugin->setResponse($result);
	}

	/**
	 * Get todos
	 *
	 * @return  void|boolean
	 *
	 * @since   1.0
	 */
	public function post()
	{

		$input = Factory::getApplication()->getInput();
		$post  = Factory::getApplication()->getInput();

		// Get Content id
		$getContentId               = array();
		$getContentId['url']        = $post->get('url', '', 'url');
		$getContentId['type']       = $post->get('type', '', 'string');
		$getContentId['subtype']    = $post->get('subtype', '', 'string');
		$getContentId['element']    = $post->get('client', '', 'string');
		$getContentId['element_id'] = $post->get('cont_id', '', 'string');
		$getContentId['title']      = $post->get('title', '', 'string');
		$getContentId['client']     = $post->get('client', '', 'string');

		list($data['plg_type'], $data['plg_name']) = explode(".", $getContentId['client']);

		// Load AnnotationForm Model
		$result = new stdclass;

		if (empty($getContentId['url']) || empty($getContentId['element_id']) || empty($getContentId['element']))
		{
			$result->success = false;
			$result->data    = Text::_("COM_API_INVALID_REQUEST");

			$this->plugin->setResponse($result);

			return;
		}

		// Save todo
		$contentId = '';

		if ($contentId = $this->contentModel->getContentID($getContentId))
		{
			$this->annotationsModel->setState("content_id", $contentId);

			$saveTodo = array();

			// Get posted data
			$saveTodo['id']          = $input->get('id', '', 'INT');
			$saveTodo['content_id']  = $contentId;
			$saveTodo['type']        = $post->get('type', 'todos', 'string');

			// Context to diff page level and content level todos
			$saveTodo['context']     = $post->get('subtype', '', 'string');
			$saveTodo['client']      = $post->get('client', '', 'string');

			// Required to trigger events
			list($plg_type, $plg_name) = explode(".", $saveTodo['client']);
			$saveTodo['plg_type']    = $plg_type;
			$saveTodo['plg_name']    = $plg_name;

			// Get todo title and entered comment
			$saveTodo['title']       = $post->get('title', '', 'string');
			$saveTodo['sender_msg']  = $post->get('sender_msg', '', 'string');
			$saveTodo['start_date']  = $post->get('start_date', '', 'DATETIME');
			$saveTodo['due_date']    = $post->get('due_date', '', 'DATETIME');
			$saveTodo['parent_id']   = $post->get('parent_id', '', 'INT');
			$saveTodo['status']      = $post->get('status', 'I', 'string');
			$saveTodo['state']       = $post->get('state', '1', 'INT');

			// Assigned by name
			$assigned_by = $post->get('assigned_by', '', 'INT');

			if (!empty($assigned_by) && $assigned_by != 0)
			{
				$saveTodo['assigned_by'] = $assigned_by;
			}

			// Assigned to name
			$assigned_to = $post->get('assigned_to', '', 'INT');

			if (!empty($assigned_to) && $assigned_to != 0)
			{
				$saveTodo['assigned_to'] = $assigned_to;
			}

			// Check content id
			if (empty($contentId) || $contentId == 0 || empty($saveTodo['sender_msg']))
			{
				$result->success = false;
				$result->message = Text::_("COM_JLIKE_ERROR_FETCHING_TODOS");

				$this->plugin->setResponse($result);

				return false;
			}

			// Save todo data
			if ($todosId = $this->recommendationFormModel->save($saveTodo))
			{
				// Get todo data
				$returnData   = array();
				$returnData[] = $this->recommendationFormModel->getData($todosId, $saveTodo);

				// Get user avatars
				$userProfileInfo = $this->recommendationFormModel->getUserAvatar($saveTodo);

				// Prepare user name, avatar, profile link
				$assigned_by         = new stdClass;
				$assigned_by->id     = $returnData[0]->assigned_by;
				$assigned_by->name   = Factory::getUser($returnData[0]->assigned_by)->name;
				$assigned_by->avatar = $userProfileInfo['assigned_by']->avatar;
				$assigned_by->profile_link = $userProfileInfo['assigned_by']->profile_link;

				// Prepare user name, avatar, profile link
				$assigned_to         = new stdClass;
				$assigned_to->id     = $returnData[0]->assigned_to;
				$assigned_to->name   = Factory::getUser($returnData[0]->assigned_to)->name;
				$assigned_to->avatar = $userProfileInfo['assigned_to']->avatar;
				$assigned_to->profile_link = $userProfileInfo['assigned_to']->profile_link;

				$returnData[0]->assigned_by = $assigned_by;
				$returnData[0]->assigned_to = $assigned_to;

				$result->success = true;
				$result->data    = new stdclass;

				// Initially comments array should be blank
				$returnData[0]->comments = array();
				$result->data->result    = $returnData;
				$result->data->userinfo  = $assigned_by;
			}
			else
			{
				$result->success = false;
				$result->message = Text::_("COM_JLIKE_ERROR_ADDING_TODOS");
			}
		}
		else
		{
			$result->success = false;
			$result->message = Text::_("COM_JLIKE_ERROR_IN_INIT");
		}

		$this->plugin->setResponse($result);
	}
}
