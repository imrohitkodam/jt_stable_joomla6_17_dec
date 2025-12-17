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
 * View class for a list of Jlike.
 *
 * @since  1.6
 */
class JlikeViewReminders extends HtmlView
{
	protected $items;

	protected $pagination;

	protected $state;

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
		$this->state         = $this->get('State');
		$this->items         = $this->get('Items');
		$this->pagination    = $this->get('Pagination');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');

		$app  = Factory::getApplication();
		$this->displayExtension = $app->getInput()->getCmd('extension', '', 'string');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors));
		}

		JlikeHelper::addSubmenu('reminders');

		$this->addToolbar();

		$this->sidebar = '';		parent::display($tpl);
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
		$state = $this->get('State');
		$canDo = JlikeHelper::getActions();

		ToolbarHelper::title(Text::_('COM_JLIKE_TITLE_REMINDERS'), 'reminders.png');

		// Check if the form exists before showing the add/edit buttons
		$formPath = JPATH_COMPONENT_ADMINISTRATOR . '/views/reminder';

		if (file_exists($formPath))
		{
			if ($canDo->get('core.create'))
			{
				ToolbarHelper::addNew('reminder.add', 'JTOOLBAR_NEW');
				ToolbarHelper::custom('reminders.duplicate', 'copy.png', 'copy_f2.png', 'JTOOLBAR_DUPLICATE', true);
			}

			if ($canDo->get('core.edit') && isset($this->items[0]))
			{
				ToolbarHelper::editList('reminder.edit', 'JTOOLBAR_EDIT');
			}
		}

		if ($canDo->get('core.edit.state'))
		{
			if (isset($this->items[0]->state))
			{
				ToolbarHelper::divider();
				ToolbarHelper::custom('reminders.publish', 'publish.png', 'publish_f2.png', 'JTOOLBAR_PUBLISH', true);
				ToolbarHelper::custom('reminders.unpublish', 'unpublish.png', 'unpublish_f2.png', 'JTOOLBAR_UNPUBLISH', true);
			}
			elseif (isset($this->items[0]))
			{
				// If this component does not use state then show a direct delete button as we can not trash
				ToolbarHelper::deleteList('', 'reminders.delete', 'JTOOLBAR_DELETE');
			}

			if (isset($this->items[0]->checked_out))
			{
				ToolbarHelper::custom('reminders.checkin', 'checkin.png', 'checkin_f2.png', 'JTOOLBAR_CHECKIN', true);
			}
		}

		// Show trash and delete for components that uses the state field
		if (isset($this->items[0]->state))
		{
			if ($state->get('filter.state') == -2 && $canDo->get('core.delete'))
			{
				ToolbarHelper::deleteList('', 'reminders.delete', 'JTOOLBAR_EMPTY_TRASH');
				ToolbarHelper::divider();
			}
			elseif ($canDo->get('core.edit.state'))
			{
				ToolbarHelper::trash('reminders.trash', 'JTOOLBAR_TRASH');
				ToolbarHelper::divider();
			}
		}

		if ($canDo->get('core.admin'))
		{
			$input     = Factory::getApplication()->getInput();
			$extension = $input->get('extension', 'com_jlike');

			ToolbarHelper::preferences($extension);
		}

		// Set sidebar action - New in 3.0
		// JHtmlSidebar::setAction('index.php?option=com_jlike&view=reminders');

		$this->extra_sidebar = '';
	}
}
