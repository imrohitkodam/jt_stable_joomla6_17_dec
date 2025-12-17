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
 * View class for list view of products.
 *
 * @package     Jlike
 * @subpackage  Jlike
 * @since       2.2
 */
class JlikeViewAnnotations extends HtmlView
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
		$this->state      = $this->get('State');
		$this->items      = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->filterForm    = $this->get('FilterForm');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors));
		}

		JlikeHelper::addSubmenu('annotations');
		$this->addToolbar();

		if (JVERSION >= 3.0)
		{
			$this->sidebar = '';		}
		else
		{
			// $this->sidebar = JSubMenuHelper::render();
		}

		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function addToolbar()
	{
		require_once JPATH_COMPONENT . '/helpers/jlike.php';

		$state = $this->get('State');
		$canDo = JlikeHelper::getActions($state->get('filter.category_id'));

		ToolbarHelper::title(Text::_('COM_JLIKE_TITLE_ANNOTATIONS'), 'annotations.png');

		// Check if the form exists before showing the add/edit buttons
		$formPath = JPATH_COMPONENT_ADMINISTRATOR . '/views/annotation';

		if (file_exists($formPath))
		{
			if ($canDo->get('core.edit') && isset($this->items[0]))
			{
				ToolbarHelper::editList('annotation.edit', 'JTOOLBAR_EDIT');
			}
		}

		if ($canDo->get('core.edit.state'))
		{
			if (isset($this->items[0]->state))
			{
				ToolbarHelper::divider();
				ToolbarHelper::custom('annotations.publish', 'publish.png', 'publish_f2.png', 'JTOOLBAR_PUBLISH', true);
				ToolbarHelper::custom('annotations.unpublish', 'unpublish.png', 'unpublish_f2.png', 'JTOOLBAR_UNPUBLISH', true);
			}
			elseif (isset($this->items[0]))
			{
				// If this component does not use state then show a direct delete button as we can not trash
				ToolbarHelper::deleteList('', 'annotations.delete', 'JTOOLBAR_DELETE');
			}

			if (isset($this->items[0]->state))
			{
				ToolbarHelper::divider();
				ToolbarHelper::archiveList('annotations.archive', 'JTOOLBAR_ARCHIVE');
			}

			if (isset($this->items[0]->checked_out))
			{
				ToolbarHelper::custom('annotations.checkin', 'checkin.png', 'checkin_f2.png', 'JTOOLBAR_CHECKIN', true);
			}
		}

		// Show trash and delete for components that uses the state field
		if (isset($this->items[0]->state))
		{
			if ($state->get('filter.state') == -2 && $canDo->get('core.delete'))
			{
				ToolbarHelper::deleteList('', 'annotations.delete', 'JTOOLBAR_EMPTY_TRASH');
				ToolbarHelper::divider();
			}
			elseif ($canDo->get('core.edit.state'))
			{
				ToolbarHelper::trash('annotations.trash', 'JTOOLBAR_TRASH');
				ToolbarHelper::divider();
			}
		}

		if ($canDo->get('core.admin'))
		{
			ToolbarHelper::preferences('com_jlike');
		}
	}

	/**
	 * Returns an array of fields the table can be sorted by
	 *
	 * @return  array  Array containing the field name to sort by as the key and display text as value
	 *
	 * @since   3.0
	 */
	protected function getSortFields()
	{
		return array(
			'a.id'              => Text::_('JGRID_HEADING_ID'),
			'a.ordering'        => Text::_('JGRID_HEADING_ORDERING'),
			'a.state'           => Text::_('JSTATUS'),
			'a.user_id'         => Text::_('COM_JLIKE_ANNOTATIONS_USER_ID'),
			'a.annotation'      => Text::_('COM_JLIKE_ANNOTATIONS_ANNOTATION'),
			'a.annotation_date' => Text::_('COM_JLIKE_ANNOTATIONS_ANNOTATION_DATE'),
		);
	}
}
