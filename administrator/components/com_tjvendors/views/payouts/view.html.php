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
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
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
class TjvendorsViewPayouts extends HtmlView
{
	protected $items;

	protected $pagination;

	protected $state;

	protected $vendor_details;

	protected $uniqueClients;

	protected $bulkPayoutStatus;

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
		$this->model = $this->getModel('payouts');
		$this->input = Factory::getApplication()->getInput();

		// Getting vendor id from url
		$vendor_id = $this->input->get('vendor_id', '', 'INT');
		BaseDatabaseModel::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_tjvendors/models', 'vendors');
		$tjvendorsModelVendors = BaseDatabaseModel::getInstance('Vendors', 'TjvendorsModel');
		$vendorsDetail = $tjvendorsModelVendors->getItems();
		$this->vendor_details = $vendorsDetail;
		$this->uniqueClients = TjvendorsHelper::getUniqueClients();
		$com_params = ComponentHelper::getParams('com_tjvendors');
		$this->bulkPayoutStatus = $com_params->get('bulk_payout');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors));
		}

		TjvendorsHelper::addSubmenu('payouts');

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
		$client = $input->get('client', '', 'STRING');

		$state = $this->get('State');
		$canDo = TjvendorsHelper::getActions();
		ToolbarHelper::custom('back', 'chevron-left.png', '', 'COM_TJVENDORS_BACK', false);

		$tjvendorFrontHelper = new TjvendorFrontHelper;
		$clientTitle = $tjvendorFrontHelper->getClientName($client);

		$title = !empty($client) ? $clientTitle . ' : ' : '';

		ToolbarHelper::title($title . Text::_('COM_TJVENDORS_TITLE_PAYOUTS'), 'list.png');

		if ($canDo->get('core.admin'))
		{
			ToolbarHelper::preferences('com_tjvendors');
		}

		$this->extra_sidebar = '';
	}
}
