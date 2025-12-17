<?php
/**
 * @package     TJVendors
 * @subpackage  com_tjvendors
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\ListModel;

/**
 * Methods supporting a list of Tjvendors records.
 *
 * @since  1.6
 */
class TjvendorsModelReports extends ListModel
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
				'credit', 'pass.`credit`',
				'debit', 'pass.`debit`',
				'reference_order_id', 'pass.`reference_order_id`',
				'currency', 'pass.`currency`',
				'credit', 'pass.`credit`',
				'debit', 'pass.`debit`',
				'reference_order_id', 'pass.`reference_order_id`',
				'vendor_title', 'vendors.`vendor_title`',
				'transaction_id', 'pass.`transaction_id`',
				'transaction_time', 'pass.`transaction_time`',
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

		if (!in_array($orderCol, $this->filter_fields))
		{
			$orderCol = 'vendors.vendor_id';
		}

		$this->setState('list.ordering', $orderCol);

		$vendorId = $app->getUserStateFromRequest($this->context . '.filter.vendor_id', 'vendor_id', '0', 'string');
		$this->setState('filter.vendor_id', $vendorId);

		$currency = $app->getUserStateFromRequest($this->context . '.filter.currency', 'currency', '0', 'string');
		$this->setState('filter.currency', $currency);

		$fromDate = $app->getUserStateFromRequest($this->context . '.filter.fromDate', 'fromDates', '0', 'string');
		$this->setState('filter.fromDate', $fromDate);

		$toDate = $app->getUserStateFromRequest($this->context . '.filter.toDate', 'toDates', '0', 'string');
		$this->setState('filter.toDate', $toDate);

		$client = $app->getUserStateFromRequest($this->context . '.filter.vendor_client', 'vendor_client', '0', 'string');
		$this->setState('filter.vendor_client', $client);

		$paidUnpaid = $app->getUserStateFromRequest($this->context . '.filter.paidUnpaid', 'paidUnpaid', '0', 'string');
		$this->setState('filter.paidUnpaid', $paidUnpaid);

		$transactionType = $app->getUserStateFromRequest($this->context . '.filter.transactionType', 'transactionType', '0', 'string');
		$this->setState('filter.transactionType', $transactionType);

		// Load the filter state.
		$search = $app->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		// Load the parameters.
		$params = ComponentHelper::getParams('com_tjvendors');
		$this->setState('params', $params);

		// List state information.
		parent::populateState('vendors.vendor_id', 'desc');
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return   JDatabaseQuery
	 *
	 * @since    1.6
	 */

	public function getListQuery()
	{
		$transactionType = $this->getState('filter.transactionType', '');
		$client = $this->getState('filter.vendor_client', '');
		$currency = $this->getState('filter.currency', '');
		$vendor_id = $this->getState('filter.vendor_id', '');

		$db = Factory::getDbo();

		$query = $db->getQuery(true);
		$query->select(array('vendors.vendor_id', 'vendors.vendor_title', 'pass.*'));
		$query->from($db->quoteName('#__tjvendors_vendors', 'vendors'));
		$query->join('LEFT', $db->quoteName('#__tjvendors_passbook', 'pass') .
			' ON (' . $db->quoteName('vendors.vendor_id') . ' = ' . $db->quoteName('pass.vendor_id') . ')');
		$query->where($db->quoteName('pass.id') . ' is not null');

		if (!empty($transactionType))
		{
			if ($transactionType == "debit")
			{
				$query->where($db->quoteName('debit') . " >0 ");
			}
			else
			{
				$query->where($db->quoteName('credit') . " >0 ");
			}
		}

		if (!empty($client))
		{
				$query->where($db->quoteName('client') . ' = ' . $db->quote($client));
		}

		if (!empty($vendor_id))
		{
				$query->where($db->quoteName('pass.vendor_id') . ' = ' . $db->quote($vendor_id));
		}

		if (!empty($currency))
		{
				$query->where($db->quoteName('pass.currency') . ' = ' . $db->quote($currency));
		}

		$fromDate = $this->getState('filter.fromDate', '');
		$toDate = $this->getState('filter.toDate', '');

		if (empty($toDate) && !empty($fromDate))
		{
			$query->where('Date(' . $db ->quoteName('transaction_time') .')' . " >= " . $db->quote($fromDate));
		}
		elseif (empty($fromDate) && !empty($toDate))
		{
			$query->where('Date('.$db->quoteName('transaction_time').')' . " <= " . $db->quote($toDate));
		}
		elseif (!empty($fromDate) && !empty($toDate))
		{
			$query->where('Date('.$db ->quoteName('transaction_time') .')' . 'BETWEEN' . "'$fromDate'" . 'AND' . "'$toDate'");
		}

		// Filter by search in title
		$search = $db->escape($this->getState('filter.search'));

		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where($db->quoteName('vendors.vendor_id') . ' = ' . (int) substr($search, 3));
			}
			else
			{
				$search = $db->Quote('%' . $db->escape($search, true) . '%');
				$query->where('(' . $db->quoteName('vendors.vendor_id') . ' LIKE ' . $search . 'OR' .
				$db->quoteName('pass.currency') . 'OR' . $db->quoteName('vendors.vendor_title') . ' LIKE ' . $search .
				'OR' . $db->quoteName('transaction_id') . ' LIKE ' . $search . ')');
			}
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

		if (!in_array(strtoupper($orderDirn), array('ASC', 'DESC')))
		{
			$orderDirn = 'DESC';
		}

		return $query;
	}

	/**
	 * get the list items
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
