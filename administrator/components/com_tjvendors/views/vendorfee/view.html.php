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

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * View to edit
 *
 * @since  1.6
 */
class TjvendorsViewVendorFee extends HtmlView
{
	protected $state;

	protected $item;

	protected $form;

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
		$input = Factory::getApplication()->getInput();
		$this->vendor_id = $input->get('vendor_id', '', 'INT');
		$this->id = $input->get('id', '', 'INT');
		$this->state = $this->get('State');
		$this->item  = $this->get('Item');
		$this->form  = $this->get('Form');
		$this->input = Factory::getApplication()->getInput();
		Text::script('COM_TJVENDORS_FEES_NEGATIVE_NUMBER_ERROR');
		Text::script('COM_TJVENDORS_FEES_PERCENT_ERROR');

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
	 * @throws Exception
	 */
	protected function addToolbar()
	{
		Factory::getApplication()->getInput()->set('hidemainmenu', true);

		$user  = Factory::getUser();
		$isNew = ($this->item->vendor_id == 0);

		$input = Factory::getApplication()->getInput();
		$client = $input->get('client', '', 'STRING');

		if ($isNew)
		{
			$viewTitle = Text::_('COM_TJVENDOR_NEW_USER_SPECIFIC_COMMISSION');
		}
		else
		{
			$viewTitle = Text::_('COM_TJVENDOR_EDIT_USER_SPECIFIC_COMMISSION');
		}

		$clientTitle = TjvendorFrontHelper::getClientName($client);
		ToolbarHelper::title($clientTitle . '  ' . $viewTitle, 'pencil.png');

		ToolbarHelper::apply('vendorfee.apply', 'JTOOLBAR_APPLY');
		ToolbarHelper::save('vendorfee.save', 'JTOOLBAR_SAVE');

		if (empty($this->item->id))
		{
			ToolbarHelper::cancel('vendorfee.cancel', 'JTOOLBAR_CANCEL');
		}
		else
		{
			ToolbarHelper::cancel('vendorfee.cancel', 'JTOOLBAR_CLOSE');
		}
	}
}
