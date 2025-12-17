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
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * View to edit
 *
 * @since  1.6
 */
class TjvendorsViewPayout extends HtmlView
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
		$this->state = $this->get('State');
		$this->item  = $this->get('Item');
		$this->form  = $this->get('Form');
		$this->input = Factory::getApplication()->getInput();
		$com_params = ComponentHelper::getParams('com_tjvendors');
		$this->bulkPayoutStatus = $com_params->get('bulk_payout');

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

		$input = Factory::getApplication()->getInput();

		// Let's get the extension name
		$client = $input->get('client', '', 'STRING');

		$viewTitle = Text::_('COM_TJVENDOR_EDIT_PAYOUT');

		if (isset($this->item->checked_out))
		{
			$checkedOut = !($this->item->checked_out == 0 || $this->item->checked_out == $user->get('id'));
		}
		else
		{
			$checkedOut = false;
		}

		$clientTitle = TjvendorFrontHelper::getClientName($client);
		ToolbarHelper::title($clientTitle . '  ' . $viewTitle, 'pencil.png');

		if (empty($this->item->id))
		{
			ToolbarHelper::cancel('payout.cancel', 'JTOOLBAR_CANCEL');
		}
		else
		{
			ToolbarHelper::cancel('payout.cancel', 'JTOOLBAR_CLOSE');
		}
	}
}
