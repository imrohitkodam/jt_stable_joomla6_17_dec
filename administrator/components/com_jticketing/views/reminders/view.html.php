<?php
/**
 * @version    SVN: <svn_id>
 * @package    Com_Jticketing
 * @copyright  Copyright (C) 2005 - 2014. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * Shika is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */
// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Component\ComponentHelper;

/**
 * View class for a list of Reminders.
 *
 * @since  1.0.0
 */
class JticketingViewReminders extends HtmlView
{
	protected $items;

	protected $pagination;

	protected $state;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		$params = ComponentHelper::getParams('com_jticketing');
		$integration = $params->get('integration');

		// Native Event Manager.
		if($integration<1)
		{
			$this->sidebar = ""; // Joomla 6: HTMLHelperSidebar::render() removed
			ToolbarHelper::preferences('com_jticketing');
		?>
			<div class="alert alert-info alert-help-inline">
		<?php echo Text::_('COMJTICKETING_INTEGRATION_NOTICE');
		?>
			</div>
		<?php
			return false;
		}

		$this->state = $this->get('State');
		$this->items = $this->get('Items');
		$this->pagination = $this->get('Pagination');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors));
		}

		JticketingHelper::addSubmenu('reminders');

		$this->addToolbar();

		$this->sidebar = ""; // Joomla 6: HTMLHelperSidebar::render() removed
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since   1.0
	 *
	 * @return  void
	 */
	protected function addToolbar()
	{
		require_once JPATH_ADMINISTRATOR . '/components/com_jticketing'. '/helpers/jticketing.php';

		$state = $this->get('State');
		$canDo = JticketingHelper::getActions($state->get('filter.category_id'));
		ToolbarHelper::back('COM_JTICKETING_HOME', 'index.php?option=com_jticketing&view=cp');


		// Joomla 6: JVERSION check removed
		if (false) // Legacy >= '3.0')
		{
			ToolbarHelper::title(Text::_('COM_JTICKETING_COMPONENT') . Text::_('COM_JTICKETING_TITLE_REMINDERS'), 'folder');
		}
		else
		{
			ToolbarHelper::title(Text::_('COM_JTICKETING_COMPONENT') . Text::_('COM_JTICKETING_TITLE_REMINDERS'), 'reminders.png');
		}

		// Check if the form exists before showing the add/edit buttons
		$formPath = JPATH_COMPONENT_ADMINISTRATOR . '/views/reminder';

		if (file_exists($formPath))
		{
			if ($canDo->{'core.create'})
			{
				ToolbarHelper::addNew('reminder.add', 'JTOOLBAR_NEW');
			}

			if ($canDo->{'core.edit'} && isset($this->items[0]))
			{
				ToolbarHelper::editList('reminder.edit', 'JTOOLBAR_EDIT');
			}
		}

		if ($canDo->{'core.edit.state'})
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

			if (isset($this->items[0]->state))
			{
				ToolbarHelper::divider();
				ToolbarHelper::archiveList('reminders.archive', 'JTOOLBAR_ARCHIVE');
			}

			if (isset($this->items[0]->checked_out))
			{
				ToolbarHelper::custom('reminders.checkin', 'checkin.png', 'checkin_f2.png', 'JTOOLBAR_CHECKIN', true);
			}
		}

		// Show trash and delete for components that uses the state field
		if (isset($this->items[0]->state))
		{
			if ($state->get('filter.state') == -2 && $canDo->{'core.delete'})
			{
				ToolbarHelper::deleteList('', 'reminders.delete', 'JTOOLBAR_EMPTY_TRASH');
				ToolbarHelper::divider();
			}
			elseif ($canDo->{'core.edit.state'})
			{
				ToolbarHelper::trash('reminders.trash', 'JTOOLBAR_TRASH');
				ToolbarHelper::divider();
			}
		}

		if ($canDo->{'core.admin'})
		{
			ToolbarHelper::preferences('com_jticketing');
		}

		// Set sidebar action - New in 3.0
		// Joomla 6: HTMLHelperSidebar::setAction() removed

		$this->extra_sidebar = '';

			HTMLHelperSidebar::addFilter(

			Text::_('JOPTION_SELECT_PUBLISHED'),
				'filter_published',
				HTMLHelper::_('select.options', HTMLHelper::_('jgrid.publishedOptions'), "value", "text", $this->state->get('filter.state'), true)
			);
	}

	/**
	 * Sort the Fields.
	 *
	 * @since   1.0
	 *
	 * @return  void
	 */
	protected function getSortFields()
	{
		return array(
		'a.id' => Text::_('JGRID_HEADING_ID'),
		'a.ordering' => Text::_('JGRID_HEADING_ORDERING'),
		'a.state' => Text::_('JSTATUS'),
		'a.title' => Text::_('COM_JTICKETING_REMINDERS_TITLE'),
		'a.description' => Text::_('COM_JTICKETING_REMINDERS_DESCRIPTION'),
		'a.days' => Text::_('COM_JTICKETING_REMINDERS_DAYS'),
		);
	}
}
