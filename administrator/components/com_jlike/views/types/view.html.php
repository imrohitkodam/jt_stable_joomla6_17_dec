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

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * View to edit
 *
 * @since  1.6
 */
class JLikeViewTypes extends HtmlView
{
	protected $state;

	protected $items;

	protected $form;

	protected $params;

	protected $canSave;

	protected $userid;

	protected $pagination;

	protected $sortColumn;

	protected $sortDirection;

	protected $searchterms;

	protected $sidebar;

	public $filterForm;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  Template name
	 *
	 * @throws Exception
	 * @return void
	 */
	public function display($tpl = null)
	{
		$app  = Factory::getApplication();
		$user = Factory::getUser();

		$this->items      = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->state      = $this->get('State');
		$this->filterForm = $this->get('FilterForm');

		// Following variables used more than once
		$this->sortColumn 	   = $this->state->get('list.ordering');
		$this->sortDirection	 = $this->state->get('list.direction');
		$this->searchterms	   = $this->state->get('filter.search');

		$JlikeHelper = new JLikeHelper;
		$JlikeHelper->addSubmenu('types');

		$this->sidebar = '';
		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors));
		}

		$this->addToolbar();

		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return void
	 *
	 * @since    1.6
	 */
	protected function addToolbar()
	{
		require_once JPATH_COMPONENT . '/helpers/jlike.php';
		$state = $this->get('State');
		$title = Text::_('COM_JLIKE_TYPES_LIST');

		ToolbarHelper::title($title, 'type');
		ToolbarHelper::addNew('type.add');
		ToolbarHelper::editList('type.edit');
		ToolbarHelper::deleteList(
		Text::_('COM_JLIKE_VIEW_DELETE_MESSAGE'), 'types.delete', Text::_('COM_JLIKE_VIEW_DELETE')
		);
	}
}
