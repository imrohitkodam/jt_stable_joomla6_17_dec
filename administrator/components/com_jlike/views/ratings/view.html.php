<?php
/**
 * @package     JLike
 * @subpackage  com_jlike
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access to this file
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

if (file_exists(JPATH_ADMINISTRATOR . '/components/com_jlike/models/ratingtypes.php')) {
	require_once JPATH_ADMINISTRATOR . '/components/com_jlike/models/ratingtypes.php';
}

/**
 * View class for view ratings
 *
 * @since  3.0.0
 */
class JLikeViewRatings extends HtmlView
{
	protected $state;

	protected $items;

	protected $form;

	protected $canSave;

	protected $sidebar;

	protected $user;

	protected $pagination;

	public $filterForm;

	public $activeFilters;

	public $ratingTypes;

	/**
	 * Display the rating types
	 *
	 * @param   string  $tpl  The name of the layout file to parse.
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		$this->user          = Factory::getUser();
		$this->items         = $this->get('Items');
		$this->pagination    = $this->get('Pagination');
		$this->state         = $this->get('State');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');

		$JlikeHelper = new JLikeHelper;
		$JlikeHelper->addSubmenu('ratings');

		$this->sidebar = '';
		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors));
		}

		$this->addToolbar();

		$ratingTypesModel  = BaseDatabaseModel::getInstance('ratingtypes', 'JLikeModel', array('ignore_request' => true));
		$this->ratingTypes = $ratingTypesModel->getRatingTypesById();

		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return void
	 *
	 * @since    3.0.0
	 */
	protected function addToolbar()
	{
		$state = $this->get('State');
		$title = Text::_('COM_JLIKE_TITLE_RATING_TYPES');

		ToolbarHelper::title($title, 'list');
		ToolbarHelper::publish('ratings.publish', 'JTOOLBAR_PUBLISH', true);
		ToolbarHelper::unpublish('ratings.unpublish', 'JTOOLBAR_UNPUBLISH', true);
		ToolbarHelper::deleteList(
			Text::_('COM_JLIKE_VIEW_DELETE_MESSAGE'), 'ratings.delete', Text::_('COM_JLIKE_VIEW_DELETE')
		);
	}
}
