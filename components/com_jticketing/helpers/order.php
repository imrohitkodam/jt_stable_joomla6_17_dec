<?php
/**
 * @package     JTicketing
 * @subpackage  com_jticketing
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Component\ComponentHelper;

/**
 * Class for showing toolbar in backend jticketing toolbar
 *
 * @package     JTicketing
 * @subpackage  component
 * @since       1.0
 *
 * @deprecated  2.5.0 Will be removed without replacement in the next major release
 */
class JticketingOrdersHelper
{
	/**
	 * function for chec order payout
	 *
	 * @param   integer  $order_id  integer
	 *
	 * @return  array|$orderDetails
	 *
	 * @since   2.0
	 */
	public function checkOrderPayout($order_id)
	{
		$db = Factory::getDbo();

		// Create a new query object.
		$query = $db->getQuery(true);
		$query->select($db->quoteName('reference_order_id'))
			->from($db->quoteName('#__tjvendors_passbook'))
			->where($db->quoteName('debit') . ' > 0 ');
		$db->setQuery($query);
		$result = $db->loadResult();

		return $result;
	}

	/**
	 * function for order check
	 *
	 * @param   integer  $order_id  integer
	 *
	 * @deprecated  2.5.0 use the alternative methods from the libraries
	 *
	 * @return  array|$orderDetails
	 *
	 * @since   2.0
	 */
	public function orderCheck($order_id)
	{
		$orderDetails = $this->getOrderDetails($order_id);
		$db = Factory::getDbo();

		// Create a new query object.
		$query = $db->getQuery(true);
		$query->select($db->quoteName('total'))
			->from($db->quoteName('#__tjvendors_passbook'))
			->where($db->quoteName('reference_order_id') . ' = ' . $db->quote($orderDetails['order_id']));
		$db->setQuery($query);
		$orderDetails = $db->loadResult();

		return $orderDetails;
	}

	/**
	 * function for geting order details
	 *
	 * @param   integer  $order_id  integer
	 *
	 * @deprecated  2.5.0 use the alternative methods from the libraries
	 *
	 * @return  array|$orderDetails
	 *
	 * @since   2.0
	 */
	public function getOrderDetails($order_id)
	{
		$db = Factory::getDbo();
		BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_jticketing/models', 'order');
		$JticketingModelOrders = BaseDatabaseModel::getInstance('Order ', 'JticketingModel');
		$orderDetails = $JticketingModelOrders->getItem($order_id);

		return $orderDetails;
	}

	/**
	 * function for geting order details
	 *
	 * @deprecated  2.5.0 use the alternative methods from the libraries/Models
	 *
	 * @return  true
	 *
	 * @since   2.0
	 */
	public function checkPayoutPermit()
	{
		$com_params = ComponentHelper::getParams('com_tjvendors');
		$payout_day_limit = $com_params->get('payout_limit_days', '0', 'INT');
		$date = Factory::getDate();
		$presentDate = $date->modify("-" . $payout_day_limit . " day");
		$payout_date_limit = $presentDate->format('Y-m-d');

		if ($date >= $payout_date_limit)
		{
			return true;
		}
	}

	/**
	 * function for geting order details
	 *
	 * @param   integer  $userId  integer
	 *
	 * @return  boolean
	 *
	 * @since   2.0
	 *
	 * @deprecated  2.5.0  The method is deprecated use
	 * Tjvendors::vendor()->loadByUserId($userId, 'com_jticketing')->getPaymentConfig() method instead.
	 */
	public function checkGatewayDetails($userId)
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
		$db->setQuery($query);
		$result = $db->loadAssoc();
		$params = new stdclass;

		if (isset(json_decode($result['params'])->payment_gateway))
		{
			$params = json_decode($result['params'])->payment_gateway;
		}

		if (empty($params->payment_gateway0->payment_gateways))
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * function for update attendee status
	 *
	 * @param   integer  $orderId  order id
	 *
	 * @param   string   $status   order status
	 *
	 * @return  boolean
	 *
	 * @since   2.0
	 *
	 * @deprecated  2.5.0  The method is deprecated use JT::order($orderId)->updateAttendeeStatus(); instead.
	 */
	public function updateAttendeeStatus($orderId, $status)
	{
		// Get Order items
		$order 			= JT::order($orderId);
		$orderItemData = $order->getItems();

		if (!empty($orderItemData))
		{
			foreach ($orderItemData as $orderData)
			{
				$attendee 					= JT::Attendee($orderData->attendee_id);
				$attendee->status 			= $status;
				$attendee->ticket_type_id	= $orderData->type_id;

				return $attendee->save();
			}
		}

		return false;
	}
}
