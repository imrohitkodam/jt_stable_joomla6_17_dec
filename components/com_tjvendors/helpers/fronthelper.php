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
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Table\Table;

$payoutPath = JPATH_ADMINISTRATOR . '/components/com_tjvendors/tables/payout.php';
if (file_exists($payoutPath)) {
	require_once $payoutPath;
}

$tjvendorsPath = JPATH_ADMINISTRATOR . '/components/com_tjvendors/helpers/tjvendors.php';
if (file_exists($tjvendorsPath)) {
	require_once $tjvendorsPath;
}

/**
 * Class TjvendorsFrontendHelper
 *
 * @since  1.6
 */
class TjvendorFrontHelper
{
	/**
	 * Get an instance of the named model
	 *
	 * @param   string  $name  Model name
	 *
	 * @return null|object
	 */
	public static function getModel($name)
	{
		$model = null;

		// If the file exists, let's
		if (file_exists(JPATH_SITE . '/components/com_tjvendors/models/' . strtolower($name) . '.php'))
		{
			require_once JPATH_SITE . '/components/com_tjvendors/models/' . strtolower($name) . '.php';
			$model = BaseDatabaseModel::getInstance($name, 'TjvendorsModel');
		}

		return $model;
	}

	/**
	 * Get array of unique Clients
	 *
	 * @param   string  $user_id  To give user specific clients for the filter
	 *
	 * @return boolean|array
	 */
	public static function getUniqueClients($user_id)
	{
		$vendor_id = self::getvendor();
		$db        = Factory::getDbo();
		$query     = $db->getQuery(true);
		$query->select('DISTINCT' . $db->quoteName('client'));
		$query->from($db->quoteName('#__tjvendors_passbook', 'vendors'));

		if (!empty($vendor_id))
		{
			$query->where($db->quoteName('vendors.vendor_id') . ' = ' . $vendor_id);
		}

		$db->setQuery($query);
		$clients = array();
		$clients['all'] = Text::_('JFILTER_PAYOUT_CHOOSE_CLIENTS');

		try
		{
			$result = $db->loadAssocList();
		}
		catch (Exception $e)
		{
			Factory::getApplication()->enqueueMessage(Text::_('COM_TJVENDORS_DB_EXCEPTION_WARNING_MESSAGE'), 'error');
		}

		if (empty($result))
		{
			return false;
		}

		foreach ($result as $i)
		{
			$tjvendorFrontHelper = new TjvendorFrontHelper;
			$client = $tjvendorFrontHelper->getClientName($i['client']);
			$client = Text::_(strtoupper($i['client']));
			$clients[] = array("clientType" => $i['client'], "clientValue" => $client);
		}

		return $clients;
	}

	/**
	 * Get paid amount
	 *
	 * @param   string  $vendor_id  integer
	 *
	 * @param   string  $client     client
	 *
	 * @param   string  $currency   integer
	 *
	 * @return amount
	 */
	public static function getTotalDetails($vendor_id, $client, $currency)
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('sum(' . $db->quoteName('credit') . ') As credit');
		$query->select('sum(' . $db->quoteName('debit') . ') As debit');

		$query->from($db->quoteName('#__tjvendors_passbook'));

		if (!empty($vendor_id))
		{
			$query->where($db->quoteName('vendor_id') . '=' . $vendor_id);
		}

		if (!empty($client))
		{
		$query->where($db->quoteName('client') . " = " . $db->quote($client));
		}

		if (!empty($currency))
		{
		$query->where($db->quoteName('currency') . " = " . $db->quote($currency));
		}

		$db->setQuery($query);

		try
		{
			$rows = $db->loadAssoc();
			$totalDebitAmount   = $rows['debit'];
			$totalCreditAmount  = $rows['credit'];
			$totalpendingAmount = $totalCreditAmount - $totalDebitAmount;
			$totalDetails       = array("debitAmount" => $totalDebitAmount, "creditAmount" => $totalCreditAmount, "pendingAmount" => $totalpendingAmount);

			return $totalDetails;
		}
		catch (Exception $e)
		{
			Factory::getApplication()->enqueueMessage(Text::_('COM_TJVENDORS_DB_EXCEPTION_WARNING_MESSAGE'), 'error');
		}
	}

	/**
	 * Get clients for vendors
	 *
	 * @param   integer  $vendor_id  required to give vendor specific result
	 *
	 * @return boolean|array
	 */
	public static function getClientsForVendor($vendor_id)
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from($db->quoteName('#__vendor_client_xref'));

		if (!empty($vendor_id))
		{
			$query->where($db->quoteName('vendor_id') . ' = ' . $vendor_id);
		}

		$db->setQuery($query);

		try
		{
			$result = $db->loadAssocList();
		}
		catch (Exception $e)
		{
			Factory::getApplication()->enqueueMessage(Text::_('COM_TJVENDORS_DB_EXCEPTION_WARNING_MESSAGE'), 'error');
		}

		if (empty($result))
		{
			return false;
		}

		if (!empty($result))
		{
			foreach ($result as $client)
			{
				$clientsForVendor[] = $client['client'];
			}

			return $clientsForVendor;
		}
	}

	/**
	 * Get vendor for that user
	 *
	 * @return integer
	 */
	public static function getvendor()
	{
		$user_id = Factory::getuser()->id;
		$vendorDetails = Table::getInstance('vendor', 'TjvendorsTable', array());
		$vendorDetails->load(array('user_id' => $user_id));

		return $vendorDetails->vendor_id;
	}

	/**
	 * Get vendor for that user
	 *
	 * @return array|boolean
	 */
	public static function getCurrencies()
	{
		$vendor_id = self::getvendor();
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('DISTINCT' . $db->quoteName('currency'));
		$query->from($db->quoteName('#__tjvendors_passbook'));

		if (!empty($vendor_id))
		{
			$query->where($db->quoteName('vendor_id') . ' = ' . $vendor_id);
		}

		$db->setQuery($query);
		$currencies[] = Text::_('JFILTER_PAYOUT_CHOOSE_CURRENCY');

		try
		{
			$result = $db->loadAssocList();
		}
		catch (Exception $e)
		{
			Factory::getApplication()->enqueueMessage(Text::_('COM_TJVENDORS_DB_EXCEPTION_WARNING_MESSAGE'), 'error');
		}

		if (empty($result))
		{
			return false;
		}

		foreach ($result as $i)
		{
			$currencies[] = $i;
		}

		return $currencies;
	}

	/**
	 * Get get paymentDetails
	 *
	 * @param   string  $vendor_id  integer
	 *
	 * @param   string  $client     integer
	 *
	 * @deprecated use getPaymentGatewayConfig instead
	 *
	 * @return res|object  vendor detail
	 */
	public static function getPaymentDetails($vendor_id, $client)
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from($db->quoteName('#__vendor_client_xref'));

		if (!empty($vendor_id))
		{
			$query->where($db->quoteName('vendor_id') . ' = ' . $db->quote($vendor_id));
		}

		if (!empty($client))
		{
			$query->where($db->quoteName('client') . ' = ' . $db->quote($client));
		}

		$db->setQuery($query);

		try
		{
			$res = $db->loadObject();
		}
		catch (Exception $e)
		{
			Factory::getApplication()->enqueueMessage(Text::_('COM_TJVENDORS_DB_EXCEPTION_WARNING_MESSAGE'), 'error');
		}

		return $res;
	}

	/**
	 * Check if the logged in user is a vendor depending on the client
	 *
	 * @param   integer  $user_id  user id
	 *
	 * @param   string   $client   client
	 *
	 * @return   mixed
	 *
	 * @since   1.1
	 */
	public static function checkVendor($user_id, $client)
	{
		if (empty($user_id))
		{
			$user_id = Factory::getuser()->id;
		}

		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName('v.vendor_id'));
		$query->from($db->quoteName('#__tjvendors_vendors', 'v'));
		$query->join('LEFT', $db->quoteName('#__vendor_client_xref', 'vx') .
		' ON (' . $db->quoteName('v.vendor_id') . ' = ' . $db->quoteName('vx.vendor_id') . ')');
		$query->where($db->quoteName('v.user_id') . ' = ' . $db->quote($user_id));
		$query->where($db->quoteName('vx.client') . ' = ' . $db->quote($client));
		$db->setQuery($query);

		try
		{
			$vendor = $db->loadColumn()[0] ?? null;
		}
		catch (Exception $e)
		{
			Factory::getApplication()->enqueueMessage(Text::_('COM_TJVENDORS_DB_EXCEPTION_WARNING_MESSAGE'), 'error');
		}

		if (!$vendor)
		{
			return false;
		}
		else
		{
			return $vendor;
		}
	}

	/**
	 * function for geting paymentgateway details
	 *
	 * @param   integer  $userId  user id
	 *
	 * @param   string   $client  client
	 *
	 * @return  boolean
	 *
	 * @since   1.1
	 */
	public function checkGatewayDetails($userId, $client)
	{
		$db = Factory::getDbo();

		// Create a new query object.
		$query = $db->getQuery(true);
		$arrayColumns = array('vc.params');
		$query->select($db->quoteName($arrayColumns));
		$query->from($db->quoteName('#__tjvendors_vendors', 'v'));
		$query->join('LEFT', $db->quoteName('#__vendor_client_xref', 'vc') .
		' ON (' . $db->quoteName('v.vendor_id') . ' = ' . $db->quoteName('vc.vendor_id') . ')');
		$query->where($db->quoteName('v.user_id') . ' = ' . $db->quote($userId));
		$query->where($db->quoteName('vc.client') . ' = ' . $db->quote($client));
		$db->setQuery($query);

		try
		{
			$result = $db->loadAssoc();
		}
		catch (Exception $e)
		{
			Factory::getApplication()->enqueueMessage(Text::_('COM_TJVENDORS_DB_EXCEPTION_WARNING_MESSAGE'), 'error');
		}

		if (isset($result['params']) && !empty($result['params']))
		{
			$params = json_decode($result['params']);
		}

		if (empty($params->payment_gateway))
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * function for adding credit entry
	 *
	 * @param   integer  $order_data  integer
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function addEntry($order_data)
	{
		$com_params = ComponentHelper::getParams($order_data['client']);
		$vendorParams = ComponentHelper::getParams('com_tjvendors');
		$payout_day_limit = $vendorParams->get('payout_limit_days', '0', 'INT');
		$date = Factory::getDate();
		$payout_date_limit = $date->modify("-" . $payout_day_limit . " day");
		$currency = !empty($order_data['currency']) ? $order_data['currency'] : $com_params->get('currency');

		$payoutTable = Table::getInstance('payout', 'TjvendorsTable', array());
		$payoutTable->load(array('reference_order_id' => $order_data['order_id']));

		if ($payoutTable->debit > 0)
		{
			$checkOrderPayout = $payoutTable->order_id;
		}
		else
		{
			$checkOrderPayout = false;
		}

		$entry_data['vendor_id'] = $order_data['vendor_id'];
		$totalAmount = TjvendorsHelper::getTotalAmount($entry_data['vendor_id'], $currency, $order_data['client']);
		$entry_data['reference_order_id'] = $order_data['order_id'];
		$entry_data['transaction_id'] = $order_data['client_name'] . '-' . $currency . '-' . $entry_data['vendor_id'] . '-';
		$entry_data['transaction_time'] = Factory::getDate()->toSql();

		if ($order_data['status'] != "C")
		{
			if ($order_data['status'] == "RF")
			{
				$entry_status = "debit_refund";
			}
			elseif ($order_data['status'] == "P")
			{
				$entry_status = "debit_pending";
			}

			$entry_data['debit'] = $order_data['amount'] - $order_data['fee_amount'];
			$entry_data['credit'] = 0;
			$entry_data['total'] = $totalAmount['total'] - $entry_data['debit'];
		}
		elseif ($order_data['status'] == "C")
		{
			$entry_data['credit'] = $order_data['amount'] - $order_data['fee_amount'];
			$entry_data['debit'] = 0;
			$entry_data['total'] = $totalAmount['total'] + $entry_data['credit'];
			$entry_status = "credit_for_ticket_buy";
		}

		$params = array("customer_note" => $order_data['customer_note'], "entry_status" => $entry_status);
		$entry_data['params'] = json_encode($params);
		$entry_data['currency'] = $currency;
		$entry_data['client'] = $order_data['client'];

		BaseDatabaseModel::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_tjvendors/models', 'payout');
		$tjvendorsModelPayout = BaseDatabaseModel::getInstance('Payout', 'TjvendorsModel');
		$tjvendorsModelPayout->addCreditEntry($entry_data);
	}

	/**
	 * Get paid amount
	 *
	 * @param   string  $vendor_id     integer
	 *
	 * @param   string  $currency      integer
	 *
	 * @param   string  $filterClient  client from filter
	 *
	 * @return  int
	 */
	public static function getPaidAmount($vendor_id, $currency, $filterClient)
	{
		$input = Factory::getApplication()->getInput();
		$urlClient = $input->get('client', '', 'STRING');
		$com_params = ComponentHelper::getParams('com_tjvendors');
		$bulkPayoutStatus = $com_params->get('bulk_payout');
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from($db->quoteName('#__tjvendors_passbook'));

		if (!empty($filterClient))
		{
			$client = $filterClient;
		}
		else
		{
			$client = $urlClient;
		}

		if (!empty($vendor_id))
		{
			$query->where($db->quoteName('vendor_id') . ' = ' . $db->quote($vendor_id));
		}

		if (!empty($currency))
		{
			$query->where($db->quoteName('currency') . ' = ' . $db->quote($currency));
		}

		if ($bulkPayoutStatus == 0)
		{
			$query->where($db->quoteName('client') . ' = ' . $db->quote($client));
		}

		$db->setQuery($query);

		try
		{
			$paidDetails = $db->loadAssocList();
			$amount = 0;

			foreach ($paidDetails as $detail)
			{
				$entryStatus = json_decode($detail['params']);
				$entryStatus->entry_status;

				if ($entryStatus->entry_status == "debit_payout" && $detail['status'] == 1)
				{
					$amount = $amount + $detail['debit'];
				}
			}

			return $amount;
		}
		catch (Exception $e)
		{
			Factory::getApplication()->enqueueMessage(Text::_('COM_TJVENDORS_DB_EXCEPTION_WARNING_MESSAGE'), 'error');
		}
	}

	/**
	 * Get client name
	 *
	 * @param   string  $client  client
	 *
	 * @return  $clientName
	 */
	public static function getClientName($client)
	{
		$clientName = strtoupper($client);

		// Need to load the menu language file as mod_menu hasn't been loaded yet.
		$lang = Factory::getLanguage();
		$lang->load($client, JPATH_ADMINISTRATOR, null, false, true);

		return Text::_($clientName);
	}

	/**
	 * Function to get Item id
	 *
	 * @param   STRING  $link  URL
	 *
	 * @return  INT  Item id
	 *
	 * @since  1.0.0
	 */
	public function getItemId($link)
	{
		$app = Factory::getApplication();

		if ($app->isClient('site'))
		{
  	 		$menu     = $app->getMenu();
			$menuItem = $menu->getItems('link', $link, true);

			if ($menuItem)
			{
				return $itemid = $menuItem->id;
			}
		}

		return false;
	}

	/**
	 * Method to retriew vendor info based on the client (OPtional if not provided global will be return)
	 * This method will check for the global entry(clientless) of the vendor based on the client value
	 *
	 * @param   integer  $vendorId  VendorId
	 *
	 * @param   string   $client    client
	 *
	 * @param   boolean  $global    Whether to load global config of vendor
	 *
	 * @return  Object
	 *
	 * @since   1.2
	 */
	public function getPaymentGatewayConfig($vendorId, $client = "", $global = true)
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$arrayColumns = array('vc.params');
		$query->select($db->quoteName($arrayColumns));
		$query->from($db->quoteName('#__tjvendors_vendors', 'v'));
		$query->join('LEFT', $db->quoteName('#__vendor_client_xref', 'vc') .
				' ON (' . $db->quoteName('v.vendor_id') . ' = ' . $db->quoteName('vc.vendor_id') . ')');

		$query->where($db->quoteName('v.vendor_id') . ' = ' . $db->quote($vendorId));

		$queryCustom = clone $query;

		if (!empty($client))
		{
			$queryCustom->where($db->quoteName('vc.client') . ' = ' . $db->quote($client));
		}
		else
		{
			$queryCustom->where($db->quoteName('vc.client') . ' = ""');
		}

		$db->setQuery($queryCustom);

		try
		{
			$result = $db->loadAssoc();

			if ((empty($result) && $global))
			{
				$queryCustom = clone $query;
				$queryCustom->where($db->quoteName('vc.client') . ' = ""');
			}

			$db->setQuery($queryCustom);
			$result = $db->loadColumn()[0] ?? null;

			return json_decode($result);
		}
		catch (Exception $e)
		{
			Factory::getApplication()->enqueueMessage(Text::_('COM_TJVENDORS_DB_EXCEPTION_WARNING_MESSAGE'), 'error');
		}
	}

	/**
	 * Get client name
	 *
	 * @param   string   $client    client
	 * @param   integer  $vendorId  Venodor ID
	 *
	 * @return  Boolean Client exist or not
	 *
	 * @since  1.3.0
	 */
	public function isClientExist($client, $vendorId)
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('client');
		$query->from($db->quoteName('#__vendor_client_xref'));
		$query->where($db->quoteName('client') . ' = ' . $db->quote($client));
		$query->where($db->quoteName('vendor_id') . ' = ' . (int) $vendorId);
		$db->setQuery($query);
		$return = $db->loadColumn()[0] ?? null;

		if ($return)
		{
			return true;
		}
		else
		{
			return false;
		}
	}
}
