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
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;

JLoader::import('components.com_jlike.models.ratings', JPATH_ADMINISTRATOR);

/**
 * View to display rating list
 *
 * @since  3.0.0
 */
class JLikeViewRatings extends HtmlView
{
	protected $state;

	protected $items;

	protected $user;

	protected $pagination;

	protected $activeFilters;

	public $app;

	protected $contentId;

	protected $model;

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
		$this->app 			= Factory::getApplication();
		$jinput				= $this->getApplication()->input;
		$this->contentId	= $jinput->get('contentId', 0, 'INT');
		$this->user			= Factory::getUser();

		Factory::getLanguage()->load('com_jlike');

		// Validate user login
		/**
		 *
		if (!$this->user->id)
		{
			$current = Uri::getInstance()->toString();
			$url     = base64_encode($current);
			$this->getApplication()->redirect(Route::_('index.php?option=com_users&view=login&return=' . $url, false));
		}*/

		$this->model = $this->getModel('ratings');

		$this->state = $this->model->getState();
		$this->model->setState('filter.content_id', $this->contentId);
		$this->items = $this->model->getItems();
		$this->pagination = $this->get('Pagination');

		$this->activeFilters = $this->get('ActiveFilters');
		$helperPath = JPATH_SITE . '/components/com_jlike/helpers/main.php';

		if (!class_exists('ComjlikeMainHelper'))
		{
			// Require_once $path;
			JLoader::register('ComjlikeMainHelper', $helperPath);
			JLoader::load('ComjlikeMainHelper');
		}

		$userInfo = new stdClass;
		$ComjlikeMainHelper = new ComjlikeMainHelper;
		$sLibObj  = $ComjlikeMainHelper->getSocialLibraryObject('', array("plg_type" => '', "plg_name" => ''));

		// Get avatar for logged-in user
		if ($this->user->id)
		{
			$this->user->avtar = $sLibObj->getAvatar($this->user, 50);
		}

		parent::display($tpl);
	}

	/**
	 * Method to order fields
	 *
	 * @return array
	 */
	protected function getSortFields()
	{
		return array(
			'created_date' => Text::_('COM_JLIKE_RATINGS_LATEST_REVIEWS'),
			'rating' => Text::_('COM_JLIKE_RATINGS_TOP_REVIEWS'),
		);
	}
}
