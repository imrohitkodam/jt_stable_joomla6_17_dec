<?php
/**
 * @package     TJVendors
 * @subpackage  com_tjvendors
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access to this file
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\MVC\View\HtmlView;

/**
 * List view : Pending Payouts
 *
 * @since  1.6
 */
class TjvendorsViewVendors extends HtmlView
{
	protected $user_id;

	protected $input;

	protected $items;

	protected $pagination;

	protected $filterForm;

	protected $activeFilters;

	protected $currencies;

	protected $vendor_id;

	protected $uniqueClients;

	protected $totalDetails;

	protected $vendorClient;

	/**
	 * Display passbook transaction list
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		$app = Factory::getApplication();
		$this->user_id = Factory::getUser()->id;
		$this->input = $app->getInput();

		// Get data from the model
		$items_model = BaseDatabaseModel::getInstance('vendors', 'TjvendorsModel');
		$this->items = $items_model->getItems();
		$this->pagination	= $items_model->getPagination();
		$this->state		= $items_model->getState();
		$this->filterForm		= $items_model->getFilterForm();
		$this->activeFilters	= $items_model->getActiveFilters();
		$tjvendorFrontHelper = new TjvendorFrontHelper;
		$this->currencies = $tjvendorFrontHelper->getCurrencies();
		$this->vendor_id = $tjvendorFrontHelper->getvendor();
		$this->uniqueClients = $tjvendorFrontHelper->getUniqueClients($this->user_id);
		$client = $this->state->get('filter.vendor_client', '');
		$currency = $this->state->get('filter.currency', '');
		$this->totalDetails = $tjvendorFrontHelper->getTotalDetails($this->vendor_id, $client, $currency);
		$this->vendorClient = $app->getUserStateFromRequest('client', 'client', '');

		$fronthelperPath = JPATH_SITE . '/components/com_tjvendors/helpers/fronthelper.php';
		if (file_exists($fronthelperPath)) {
			require_once $fronthelperPath;
		}
		$this->tjvendorFrontHelper = new TjvendorFrontHelper;
		$this->vendorItemID = $this->tjvendorFrontHelper->getItemId(
			'index.php?option=com_tjvendors&view=vendors'
			);

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new \Exception(implode('<br />', $errors), 500);

			return false;
		}
		// Display the template
		parent::display($tpl);
	}
}
