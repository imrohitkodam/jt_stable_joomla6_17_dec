<?php
/**
 * @package     JTicketing
 * @subpackage  com_jticketing
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * View class for a list of Jticketing.
 *
 * @since  1.6
 */
class JticketingViewAttendeeCoreFields extends HtmlView
{
	protected $items;

	protected $pagination;

	protected $state;

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
		$this->state = $this->get('State');
		$this->items = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->input = Factory::getApplication()->getInput();
		$this->user      = Factory::getUser();
		$this->listOrder = $this->state->get('list.ordering', '');
		$this->listDirn  = $this->state->get('list.direction', '');
		$this->canOrder  = $this->user->authorise('core.edit.state', 'com_jticketing');
		$this->saveOrder = $this->listOrder == 'a.`ordering`';

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors));
		}

		JticketingHelper::addSubmenu('attendeecorefields');

		$this->addToolbar();

		$this->sidebar = ""; // Joomla 6: HTMLHelperSidebar::render() removed
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
		$input = Factory::getApplication()->getInput();
		$state = $this->get('State');
		$canDo = JticketingHelper::getActions();

		ToolbarHelper::title(Text::_('COM_JTICKETING_TITLE_ATTENDEE_CORE_FIELDS'), 'book');

		if ($canDo->{'core.edit.state'})
		{
			if (isset($this->items[0]->state))
			{
				ToolbarHelper::divider();
				ToolbarHelper::custom('attendeeCorefields.publish', 'publish.png', 'publish_f2.png', 'JTOOLBAR_PUBLISH', true);
				ToolbarHelper::custom('attendeeCorefields.unpublish', 'unpublish.png', 'unpublish_f2.png', 'JTOOLBAR_UNPUBLISH', true);
			}
		}

		if ($canDo->{'core.admin'})
		{
			ToolbarHelper::preferences('com_jticketing');
		}

		// Joomla 6: HTMLHelperSidebar::setAction() removed - sidebar functionality removed

		$this->extra_sidebar = '';
	}
}
