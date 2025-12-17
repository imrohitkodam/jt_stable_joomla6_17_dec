<?php
/**
 * @package     JLike
 * @subpackage  com_jlike
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Component\ComponentHelper;

/**
 * jlike view
 *
 * @since  1.0
 */
class JlikeViewjlike extends HtmlView
{
	protected $params;

	protected $urldata;

	protected $jlikehelperObj;

	protected $jlikemainhelperObj;

	protected $userdetails;

	protected $data;

	protected $userlables;

	protected $content_id;

	protected $goaldetails;

	protected $userNote;

	protected $buttonset;

	protected $comments;

	protected $ordering;

	protected $comments_count;

	protected $statusMgt;

	protected $Allstatuses;

	protected $likeContId;

	protected $userStatusId;

	protected $reviews;

	protected $reviews_count;

	protected $allowRating;

	protected $reviews_count_loginuser;

	protected $oluser;

	protected $userslist;

	/**
	 * jlike view
	 *
	 * @param   OBJECT  $tpl  boolean
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function display($tpl = null)
	{
		$this->params        = ComponentHelper::getParams('com_jlike');
		$input    = Factory::getApplication()->getInput();
		$post     = $input->post;
		$ordering = $post->get('sorting', 'DESC', 'STRING');

		Factory::getLanguage()->load('com_jlike');
		$setdata = Factory::getApplication()->getInput();
		$this->urldata = json_decode($setdata->get('data', '', 'RAW'));

		if ((!isset($this->urldata->plg_type)) || (isset($this->urldata->plg_type) && empty($this->urldata->plg_type)))
		{
			$this->urldata->plg_type = '';
		}

		$type = empty($this->urldata->type) ? '' : $this->urldata->type;

		$extraParams = array("plg_name" => $this->urldata->plg_name, "plg_type" => $this->urldata->plg_type, 'type' => $type);

		$array = array('show_like_buttons', 'show_comments', 'show_note', 'show_list',
		'toolbar_buttons', 'showrecommendbtn', 'showsetgoalbtn', 'showassignbtn', 'plg_type', 'jlike_allow_rating', 'show_reviews');

		// Remove warnings for not set variables
		foreach ($array as $key => $value)
		{
			if (!isset($this->urldata->$value))
			{
				$this->urldata->$value = null;
			}
		}

		$oluser               = Factory::getUser();
		$this->jlikehelperObj = new comjlikeHelper;
		$helperPath = JPATH_SITE . '/components/com_jlike/helpers/main.php';

		if (!class_exists('ComjlikeMainHelper'))
		{
			// Require_once $path;
			JLoader::register('ComjlikeMainHelper', $helperPath);
			JLoader::load('ComjlikeMainHelper');
		}

		$this->jlikemainhelperObj = new ComjlikeMainHelper;

		// Get Params each component
		$this->params = $this->jlikemainhelperObj->getjLikeParams();

		$model             = $this->getModel('jlike_likes');
		$this->userdetails = $this->jlikehelperObj->getUserDetails($oluser, $extraParams);
		$this->data        = $model->getData($this->urldata->cont_id, $this->urldata->element, $this->params->get('show_users'), $extraParams);
		$this->userlables = '';

		$this->content_id = (isset($this->data['content_id'])) ? $this->data['content_id'] : '';

		// Get goal details
		$this->goaldetails = $this->jlikemainhelperObj->getGoalDetails($oluser->id, $this->content_id);

		$this->userlables = $model->getUserlabels($this->content_id);

		$this->userNote = $model->geUserNote($this->urldata->element, $this->urldata->cont_id, $oluser->id);
		$this->buttonset   = $this->jlikehelperObj->getbttonset();

		// Get Comments Data
		$this->comments = $model->getCommentsData($this->urldata->cont_id, $this->urldata->element, 0, 0, $ordering, '', '', '', $extraParams);
		$this->ordering = $ordering;

		// Get Comments count
		$this->comments_count = $model->getCommentsCount($this->urldata->cont_id, $this->urldata->element, $note_type = '', $extraParams);

		// Default count 0
		if (empty($this->comments_count))
		{
			$this->comments_count = 0;
		}

		$this->statusMgt = $this->params->get('statusMgt', 0);

		if ($this->statusMgt)
		{
			// Get status List
			$this->Allstatuses = $this->jlikehelperObj->getAllStatus();
			$this->likeContId = $this->jlikehelperObj->getContentId($this->urldata->cont_id, $this->urldata->element);

			// Get users content status
			$this->userStatusId = $this->jlikehelperObj->getUsersContStatus($this->likeContId);
		}

		// Rating & Reviews
		// Get Reviews Data
		$this->reviews = $model->getRatingReviewData($this->urldata->cont_id, $this->urldata->element, 0, 0, $ordering, '', '', 2);

		// Get Reviews count
		$this->reviews_count = $model->getReviewsCount($this->urldata->cont_id, $this->urldata->element, 2, '');
		$this->allowRating = isset($this->urldata->jlike_allow_rating) ? $this->urldata->jlike_allow_rating : 0;

		// Get Rating avarage
		// $this->getRatingAvg = $model->getRatingAvg($this->urldata->cont_id);

		$this->reviews_count_loginuser = $model->getReviewsCount($this->urldata->cont_id, $this->urldata->element, 2, 'loginuser');

		$this->oluser = $oluser;

		// Get the users to @mention
		$urldataArr = (array) $this->urldata;
		$this->userslist = $model->getUsersList($urldataArr, $extraParams);

		parent::display($tpl);
	}
}
