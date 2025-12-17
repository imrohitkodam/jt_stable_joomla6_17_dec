<?php
/**
 * @package     TJVendors
 * @subpackage  com_tjvendors
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access.
defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Model\ListModel;

/**
 * Tjvendors model.
 *
 * @since  1.6
 */
class TjvendorsModelVendors extends ListModel
{
/**
	* Constructor.
	*
	* @param   array  $config  An optional associative array of configuration settings.
	*
	* @see        JController
	* @since      1.6
	*/
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'pass.`id`',
				'total', 'pass.`total`',
				'currency', 'pass.`currency`',
				'credit', 'pass.`credit`',
				'debit', 'pass.`debit`',
				'transaction_id', 'pass.`transaction_id`',
				'transaction_time', 'pass.`transaction_time`',
				'reference_order_id', 'pass.`reference_order_id`',
				'vendor_client', 'vendors.`vendor_client`',

			);
		}

		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   Elements order
	 * @param   string  $direction  Order direction
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// Initialise variables.
		$app = Factory::getApplication('administrator');

		// Set ordering.
		$orderCol = $app->getUserStateFromRequest($this->context . '.filter_order', 'filter_order');

		$this->setState('list.ordering', $orderCol);

		$transactionType = $app->getUserStateFromRequest($this->context . '.filter.transactionType', 'transactionType', '0', 'string');
		$this->setState('filter.transactionType', $transactionType);

		$currency = $app->getUserStateFromRequest($this->context . '.filter.currency', 'currency', '0', 'string');
		$this->setState('filter.currency', $currency);

		$fromDate = $app->getUserStateFromRequest($this->context . '.filter.fromDate', 'fromDates', '0', 'string');
		$this->setState('filter.fromDate', $fromDate);

		$toDate = $app->getUserStateFromRequest($this->context . '.filter.toDate', 'toDates', '0', 'string');
		$this->setState('filter.toDate', $toDate);

		$urlClient = $app->getInput()->get('client', '', 'STRING');
		$client = $app->getUserStateFromRequest($this->context . '.filter.vendor_client', 'vendor_client', '0', 'string');

		if (empty($client))
		{
			$client = $urlClient;
		}

		$this->setState('filter.vendor_client', $client);

		// Load the filter state.
		$search = $app->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		// Load the parameters.
		$params = ComponentHelper::getParams('com_tjvendors');
		$this->setState('params', $params);

		parent::populateState();
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return   JDatabaseQuery
	 *
	 * @since    1.6
	 */

	protected function getListQuery()
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true);

		$filterClient = $this->getState('filter.vendor_client', '');

		if ($filterClient)
		{
			$client = $filterClient;
		}
		else
		{
			$client = '';
		}

		$currency = $this->getState('filter.currency', '');
		$TjvendorFrontHelper = new TjvendorFrontHelper;
		$vendor_id = $TjvendorFrontHelper->getVendor();
		$columns = array('vendors.vendor_id');
		$query->select($db->quoteName($columns));
		$query->select('pass.*');
		$query->from($db->quoteName('#__tjvendors_vendors', 'vendors'));
		$query->join('LEFT', $db->quoteName('#__tjvendors_passbook', 'pass') .
			' ON (' . $db->quoteName('vendors.vendor_id') . ' = ' . $db->quoteName('pass.vendor_id') . ')');
		$query->where($db->quoteName('pass.id') . ' is not null');

		if (!empty($vendor_id))
		{
			$query->where($db->quoteName('vendors.vendor_id') . ' = ' . $db->quote($vendor_id));
		}

		if ($client != 'all' && $client != '')
		{
			$query->where($db->quoteName('pass.client') . ' = ' . $db->quote($client));
		}

		if (!empty($currency))
		{
			$query->where($db->quoteName('pass.currency') . ' = ' . $db->quote($currency));
		}

		$db->setQuery($query);

		// Filter by search in title
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('pass.id = ' . (int) substr($search, 3));
			}
			else
			{
				$search = $db->Quote('%' . $db->escape($search, true) . '%');
				$query->where('(' . $db->quoteName('vendors.vendor_title') . ' LIKE ' . $search .
							'OR ' . $db->quoteName('pass.currency') . ' LIKE' . $search .
							'OR ' . $db->quoteName('pass.client') . ' LIKE' . $search .
							'OR ' . $db->quoteName('pass.vendor_id') . ' LIKE' . $search . ')');
			}
		}

		$transactionType = $this->getState('filter.transactionType', '');

		if (!empty($transactionType))
		{
			if ($transactionType == "Debit")
			{
				$query->where($db->quoteName('debit') . " >0 ");
			}
			else
			{
				$query->where($db->quoteName('credit') . " >0 ");
			}
		}

		$fromDate = $this->getState('filter.fromDate', '');
		$toDate = $this->getState('filter.toDate', '');

		if (empty($toDate) && !empty($fromDate))
		{
			$query->where($db ->quoteName('transaction_time') . " >= " . $db->quote($fromDate));
		}
		elseif (empty($fromDate) && !empty($toDate))
		{
			$query->where($db ->quoteName('transaction_time') . " <= " . $db->quote($toDate));
		}
		elseif (!empty($fromDate) && !empty($toDate))
		{
			$query->where($db ->quoteName('transaction_time') . 'BETWEEN' . "'$fromDate'" . 'AND' . "'$toDate'");
		}

		// Add the list ordering clause.
		$orderCol  = $this->state->get('list.ordering');
		$orderDirn = $this->state->get('list.direction');

		if (!in_array(strtoupper($orderDirn), array('ASC', 'DESC')))
		{
			$orderDirn = 'DESC';
		}

		if ($orderCol && $orderDirn)
		{
			$query->order($db->escape($orderCol . ' ' . $orderDirn));
		}

		return $query;
	}

	/**
	 * Get items data.
	 *
	 * @return   items
	 *
	 * @since    1.6
	 */
	public function getItems()
	{
		$items = parent::getItems();

		foreach ($items as $i => $item)
		{
			$entry_status = json_decode($item->params);

			if ($entry_status->entry_status == "credit_remaining_payout")
			{
				unset($items[$i]);
			}
		}

		return $items;
	}
}
