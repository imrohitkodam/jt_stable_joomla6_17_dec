<?php
/**
 * @package     TJVendors
 * @subpackage  com_tjvendors
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

$fronthelperPath = JPATH_SITE . '/components/com_tjvendors/helpers/fronthelper.php';
if (file_exists($fronthelperPath)) {
	require_once $fronthelperPath;
}

/**
 * View class for a list of Tjvendors.
 *
 * @since  1.6
 */
class TjvendorsViewVendors extends HtmlView
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
		$this->params = ComponentHelper::getParams('com_tjvendors');
		Text::script('COM_TJVENDOR_VENDOR_APPROVAL');
		Text::script('COM_TJVENDOR_VENDOR_DENIAL');

		$this->vendorApproval = $this->params->get('vendor_approval');
		$client = $this->input->get('client', '', 'STRING');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors));
		}

		TjvendorsHelper::addSubmenu('vendors');

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
		$input = Factory::getApplication()->getInput();
		$this->client = $input->get('client', '', 'STRING');

		$state = $this->get('State');
		$canDo = TjvendorsHelper::getActions();

		$toolbar = Toolbar::getInstance('toolbar');
		$toolbar->appendButton(
		'Custom', '<a id="tjHouseKeepingFixDatabasebutton" class="btn btn-default hidden"><span class="icon-refresh"></span>'
		. Text::_('COM_TJVENDORS_FIX_DATABASE') . '</a>');
		ToolbarHelper::addNew('vendor.add');
		
		if (JVERSION >= '4.0.0')
		{
			$dropdown = $toolbar->dropdownButton('status-group')
				->text('JTOOLBAR_CHANGE_STATUS')
				->toggleSplit(false)
				->icon('icon-ellipsis-h')
				->buttonClass('btn btn-action')
				->listCheck(true);

			$childBar = $dropdown->getChildToolbar();
		}

		$tjvendorFrontHelper = new TjvendorFrontHelper;
		$clientTitle         = $tjvendorFrontHelper->getClientName($this->client);
		$title               = !empty($this->client) ? $clientTitle . ' : ' : '';

		ToolbarHelper::title($title . Text::_('COM_TJVENDORS_TITLE_VENDORS'), 'list.png');

		if ($canDo->get('core.edit.state'))
		{
			if (JVERSION < '4.0.0')
			{
				if (isset($this->items[0]->state))
				{
					ToolbarHelper::divider();
					ToolbarHelper::custom('vendors.publish', 'publish.png', 'publish_f2.png', 'JTOOLBAR_PUBLISH', true);
					ToolbarHelper::custom('vendors.unpublish', 'unpublish.png', 'unpublish_f2.png', 'JTOOLBAR_UNPUBLISH', true);
				}

				if (isset($this->items[0]))
				{
					ToolbarHelper::deleteList('', 'vendors.delete', 'JTOOLBAR_DELETE');
				}
			}
			else
			{
				if (isset($this->items[0]->state))
				{
					$childBar->publish('vendors.publish')->listCheck(true);
					$childBar->unpublish('vendors.unpublish')->listCheck(true);
				}

				if (isset($this->items[0]))
				{
					$childBar->delete('vendors.delete')->listCheck(true);
				}
			}
		}

		HTMLHelper::_('bootstrap.modal', 'collapseModal');

		if ($canDo->get('core.admin'))
		{
			ToolbarHelper::preferences('com_tjvendors');
		}

		$this->extra_sidebar = '';
	}
}
