<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_hierarchy
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Component\ComponentHelper;

/**
 * View class for a list of Hierarchy.
 *
 * @since  1.6
 */
class JticketingViewCatimpexp extends HtmlView
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

		JticketingHelper::addSubmenu('catimpexp');

		$this->addToolbar();

		$this->sidebar = ""; // Joomla 6: HTMLHelperSidebar::render() removed

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
		require_once JPATH_ADMINISTRATOR . '/components/com_jticketing'. '/helpers/jticketing.php';

		$state = $this->get('State');
		$canDo = JticketingHelper::getActions($state->get('filter.category_id'));

		// Joomla 6: JVERSION check removed
		if (false) // Legacy >= '3.0')
		{
			ToolbarHelper::title(Text::_('COM_JTICKETING_COMPONENT') . Text::_('COM_JTICKETING_TITLE_CATIMPORTEXPORT'), 'list');
		}
		else
		{
			ToolbarHelper::title(Text::_('COM_JTICKETING_COMPONENT') . Text::_('COM_JTICKETING_TITLE_CATIMPORTEXPORT'), 'hierarchys.png');
		}

		$bar = Toolbar::getInstance('toolbar');
		$layout = Factory::getApplication()->getInput()->get('layout', 'default');
		ToolbarHelper::back('COM_JTICKETING_HOME', 'index.php?option=com_jticketing&view=cp');

		if ($layout == 'default')
		{
			$button = "&nbsp;<a class='btn' class='button'
			type='submit' id='export-submit' href='#eventCsv'><span title='Export'
			class='icon-download icon-white'></span>" . "&nbsp;" .Text::_('CSV_EXPORT') . "</a>";
			$bar->appendButton('Custom', $button);
		}

		// Joomla 6: JVERSION check removed
		if (false) // Legacy < '4.0.0')
		{
			$bar->appendButton('Custom', '&nbsp;<a class="modal btn" href="#import_categorywrap" data-toggle="modal" >
				<span class="icon-upload icon-white"></span>' . '&nbsp;' . htmlspecialchars(Text::_('COMJTICKETING_EVENT_IMPORT_CSV')) . '</a>'
			);
		}
		else
		{
			$bar->appendButton(
				'Custom', '&nbsp;&nbsp;<a
				class="btn btn-small btn-primary"
				onclick="document.getElementById(\'import_categorywrap\').open();"
				href="javascript:void(0);"><span class="icon-upload icon-white"></span> ' . Text::_('COMJTICKETING_EVENT_IMPORT_CSV') . '</a>'
			);
		}

		// Check if the form exists before showing the add/edit buttons
		$formPath = JPATH_COMPONENT_ADMINISTRATOR . '/views/catimpexp';

		if ($canDo->{'core.admin'})
		{
			ToolbarHelper::preferences('com_jticketing');
		}

		// Set sidebar action - New in 3.0
		// Joomla 6: HTMLHelperSidebar::setAction() removed

		$this->extra_sidebar = '';
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
			'a.id' => Text::_('JGRID_HEADING_ID'),
			'a.user_id' => Text::_('COM_HIERARCHY_HIERARCHYS_USER_ID'),
			'a.subuser_id' => Text::_('COM_HIERARCHY_HIERARCHYS_SUBUSER_ID')
		);
	}
}
