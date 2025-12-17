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
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * View class for rating types
 *
 * @since  3.0.0
 */
class JLikeViewRatingtypes extends HtmlView
{
	protected $state;

	protected $items;

	protected $form;

	protected $sidebar;

	protected $user;

	protected $pagination;

	public $filterForm;

	public $activeFilters;

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
		$this->state         = $this->get('State');
		$this->items         = $this->get('Items');
		$this->pagination    = $this->get('Pagination');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');

		$JlikeHelper = new JLikeHelper;
		$JlikeHelper->addSubmenu('ratingtypes');

		$this->sidebar = '';
		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors));
		}

		// Include the component HTML helpers.
		HTMLHelper::addIncludePath(JPATH_COMPONENT . '/helpers/html');

		$this->addToolbar();

		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return void
	 *
	 * @since  3.0.0
	 */
	protected function addToolbar()
	{
		$state = $this->get('State');
		$title = Text::_('COM_JLIKE_TITLE_RATING_TYPES');

		ToolbarHelper::title($title, 'list');
		ToolbarHelper::addNew('ratingtype.add');
		ToolbarHelper::editList('ratingtype.edit');
		ToolbarHelper::publish('ratingtypes.publish', 'JTOOLBAR_PUBLISH', true);
		ToolbarHelper::unpublish('ratingtypes.unpublish', 'JTOOLBAR_UNPUBLISH', true);
		ToolbarHelper::deleteList(
			Text::_('COM_JLIKE_VIEW_DELETE_MESSAGE'), 'ratingtypes.delete', Text::_('COM_JLIKE_VIEW_DELETE')
		);
	}
}
