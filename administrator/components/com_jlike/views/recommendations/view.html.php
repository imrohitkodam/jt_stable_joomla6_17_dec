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

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * View class for a list of Jlike.
 *
 * @since  1.6
 */
class JlikeViewRecommendations extends HtmlView
{
	protected $items;

	protected $pagination;

	protected $state;

	/**
	 * Display the view
	 *
	 * @param   mixed  $tpl  default null
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		$this->state      = $this->get('State');
		$this->items      = $this->get('Items');
		$this->pagination = $this->get('Pagination');

		// Check for errors.

		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors));
		}

		JlikeHelper::addSubmenu('recommendations');

		$this->addToolbar();

		$this->sidebar = '';		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since	1.6
	 */
	protected function addToolbar()
	{
		require_once JPATH_COMPONENT . '/helpers/jlike.php';

		$state = $this->get('State');
		$canDo = JlikeHelper::getActions($state->get('filter.category_id'));

		ToolbarHelper::title(Text::_('COM_JLIKE_TITLE_RECOMMENDATIONS'), 'recommendations.png');

		// Check if the form exists before showing the add/edit buttons
		$formPath = JPATH_COMPONENT_ADMINISTRATOR . '/views/todos';

		if (file_exists($formPath))
		{
			if ($canDo->get('core.create'))
			{
				ToolbarHelper::addNew('todos.add', 'JTOOLBAR_NEW');
			}

			if ($canDo->get('core.edit') && isset($this->items[0]))
			{
				ToolbarHelper::editList('todos.edit', 'JTOOLBAR_EDIT');
			}
		}

		if ($canDo->get('core.edit.state'))
		{
			if (isset($this->items[0]->state))
			{
				ToolbarHelper::divider();
				ToolbarHelper::custom('recommendations.publish', 'publish.png', 'publish_f2.png', 'JTOOLBAR_PUBLISH', true);
				ToolbarHelper::custom('recommendations.unpublish', 'unpublish.png', 'unpublish_f2.png', 'JTOOLBAR_UNPUBLISH', true);
			}
			elseif (isset($this->items[0]))
			{
				// If this component does not use state then show a direct delete button as we can not trash
				ToolbarHelper::deleteList('', 'recommendations.delete', 'JTOOLBAR_DELETE');
			}

			if (isset($this->items[0]->state))
			{
				ToolbarHelper::divider();
				ToolbarHelper::archiveList('recommendations.archive', 'JTOOLBAR_ARCHIVE');
			}

			if ($canDo->get('core.admin'))
			{
				ToolbarHelper::preferences('com_jlike');
			}

			// Show trash and delete for components that uses the state field
			if (isset($this->items[0]->state))
			{
				if ($state->get('filter.state') == -2 && $canDo->get('core.delete'))
				{
					ToolbarHelper::deleteList('', 'recommendations.delete', 'JTOOLBAR_EMPTY_TRASH');
					ToolbarHelper::divider();
				}
				elseif ($canDo->get('core.edit.state'))
				{
					ToolbarHelper::trash('recommendations.trash', 'JTOOLBAR_TRASH');
					ToolbarHelper::divider();
				}
			}

			if ($canDo->get('core.admin'))
			{
				ToolbarHelper::preferences('com_jlike');
			}

			// Note: JHtmlSidebar was removed in Joomla 4+

			$this->extra_sidebar = '';
		}
	}

	/**
	 * Sort on column.
	 *
	 * @return  void
	 *
	 * @since	1.6
	 */
	protected function getSortFields()
	{
		return array(
			'a.id'           => Text::_('JGRID_HEADING_ID'),
			'a.ordering'     => Text::_('JGRID_HEADING_ORDERING'),
			'a.state'        => Text::_('JSTATUS'),
			'a.assigned_by'  => Text::_('COM_JLIKE_RECOMMENDATIONS_ASSIGNED_BY'),
			'a.assigned_to'  => Text::_('COM_JLIKE_RECOMMENDATIONS_ASSIGNED_TO'),
			'a.created_date' => Text::_('COM_JLIKE_RECOMMENDATIONS_CREATED'),
			'a.status'       => Text::_('COM_JLIKE_RECOMMENDATIONS_STATUS'),
			'a.title'        => Text::_('COM_JLIKE_RECOMMENDATIONS_TITLE'),
		);
	}
}
