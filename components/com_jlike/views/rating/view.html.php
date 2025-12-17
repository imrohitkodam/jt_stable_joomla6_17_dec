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
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Table\Table;

JLoader::import('components.com_jlike.models.rating', JPATH_ADMINISTRATOR);
JLoader::import('components.com_jlike.models.ratingtypes', JPATH_ADMINISTRATOR);
JLoader::import('components.com_jlike.helper', JPATH_SITE);

/**
 * Rating view
 *
 * @since  3.0.0
 */
class JlikeViewRating extends HtmlView
{
	protected $state;

	protected $item;

	protected $form;

	protected $params;

	protected $app;

	protected $user;

	protected $model;

	public $ratingType;

	protected $content_id;

	protected $ucmType;

	protected $rating;

	protected $rating_type_id;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  Template name
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	public function display($tpl = null)
	{
		$app            = Factory::getApplication();
		$jinput         = $app->input;
		$ratingTypeCode = $jinput->get('ratingTypeCode', '', 'STRING');
		$contentId      = $jinput->get('contentId', 0, 'INT');
		Factory::getLanguage()->load('com_jlike');

		$this->user = Factory::getUser();

		if ((!$this->user->id) || (!$contentId))
		{
			$app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'error');

			// $app->redirect(JUri::root());
			return;
		}

		$this->isRatingSubmitted = $this->checkRatingIsSubmitted($contentId);

		if ($this->isRatingSubmitted)
		{
			JLoader::import('components.com_jlike.models.ratings', JPATH_ADMINISTRATOR);
			$ratingsModel = BaseDatabaseModel::getInstance('ratings', 'JLikeModel', array('ignore_request' => true));
			$ratingsModel->setState('filter.content_id', $contentId);
			$ratingsModel->setState('filter.submitted_by', Factory::getUser()->id);
			$ratings = $ratingsModel->getItems();
			$this->rating = $ratings[0];

			$tpl = 'detail';
		}

		$this->model = $this->getModel('rating');

		$ratingTypesModel = BaseDatabaseModel::getInstance('ratingtypes', 'JLikeModel', array('ignore_request' => true));
		$ratingTypesModel->setState('filter.code', $ratingTypeCode);
		$ratingType = $ratingTypesModel->getItems();

		$this->ratingType = clone $ratingType[0];
		$this->content_id = $contentId;

		// Get rating type
		$this->rating_type_id = $this->ratingType->id;

		if ($this->ratingType->tjucm_type_id)
		{
			Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_tjucm/tables');
			$ucmTypeTable = Table::getInstance('type', 'TjucmTable');
			$ucmTypeTable->load(array('id' => $this->ratingType->tjucm_type_id));
			$this->ucmType = $ucmTypeTable->unique_identifier;

			// UcmType is unique identifier from ucm type table
			$this->model->setState('filter.ucmType', $this->ucmType);
		}

		$this->model->setState('filter.ratingTypeId', $this->ratingType->id);

		$this->form = $this->model->getForm();

		$helperPath = JPATH_SITE . '/components/com_jlike/helpers/main.php';

		if (!class_exists('ComjlikeMainHelper'))
		{
			// Require_once $helperPath;
			JLoader::register('ComjlikeMainHelper', $helperPath);
			JLoader::load('ComjlikeMainHelper');
		}

		$userInfo = new stdClass;
		$ComjlikeMainHelper = new ComjlikeMainHelper;
		$sLibObj  = $ComjlikeMainHelper->getSocialLibraryObject('', array("plg_type" => '', "plg_name" => ''));
		$this->user->avtar   = $sLibObj->getAvatar($this->user, 50);

		parent::display($tpl);
	}

	/**
	 * check is the rating already submitted
	 *
	 * @param   int  $contentId  content id
	 *
	 * @return boolean
	 *
	 * @throws Exception
	 */
	public function checkRatingIsSubmitted($contentId)
	{
		if ($contentId)
		{
			JLoader::import('components.com_jlike.models.ratings', JPATH_ADMINISTRATOR);
			$ratingsModel = BaseDatabaseModel::getInstance('ratings', 'JLikeModel', array('ignore_request' => true));
			$ratingsModel->setState('filter.content_id', $contentId);
			$ratingsModel->setState('filter.submitted_by', Factory::getUser()->id);
			$ratings = $ratingsModel->getItems();
		}

		$result = !empty($ratings[0]->id) ? true : false;

		return $result;
	}
}
