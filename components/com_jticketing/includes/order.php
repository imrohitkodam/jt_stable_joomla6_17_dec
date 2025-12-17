<?php
/**
 * @package     JTicketing
 * @subpackage  com_jticketing
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die();
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Registry\Registry;

/**
 * JTicketing order class.
 *
 * @since  2.5.0
 */
class JTicketingOrder extends CMSObject
{
	/**
	 * The auto incremental primary key of the order
	 *
	 * @var    integer
	 * @since  2.5.0
	 */
	public $id = 0;

	/**
	 * Order ID with prefix - combination of order primary key and config
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	public $order_id = 0;

	/**
	 * Parent order ID - Not used anywhere yet.
	 *
	 * @var    integer
	 * @since  2.5.0
	 */
	public $parent_order_id = 0;

	/**
	 * event_details_id - #_jticketing_integration_xref table primary key
	 *
	 * @var    integer
	 * @since  2.5.0
	 */
	public $event_details_id = 0;

	/**
	 * Ticket buyer name
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	public $name = '';

	/**
	 * Ticket buyer email
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	public $email = '';

	/**
	 * Joomla user ID - In case of guest checkout it will be 0
	 *
	 * @var    integer
	 * @since  2.5.0
	 */
	public $user_id = 0;

	/**
	 * Order creation date
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	public $cdate = '';

	/**
	 * Order modification date
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	public $mdate = '';

	/**
	 * Order transaction ID sent by Payment gateway
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	public $transaction_id = '';

	/**
	 * Email ID entered on payment gateway
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	public $payee_id = '';

	/**
	 * Order amount - Not in use
	 *
	 * @deprecated 2.5.0
	 *
	 * @var    float
	 * @since  2.5.0
	 */
	public $order_amount = 0;

	/**
	 * Original amount with no fee applied
	 *
	 * @var    float
	 * @since  2.5.0
	 */
	private $original_amount = 0;

	/**
	 * Amount after applying fee
	 *
	 * @var    float
	 * @since  2.5.0
	 */
	private $amount = 0;

	/**
	 * Coupon code applied against the order
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	private $coupon_code = '';

	/**
	 * Site admin commission(processing fee)
	 *
	 * @var    float
	 * @since  2.5.0
	 */
	private $fee = 0;

	/**
	 * Order status C - completed, P - Pending, RF - Reversed, etc
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	private $status = COM_JTICKETING_CONSTANT_ORDER_STATUS_INCOMPLETE;

	/**
	 * Payment gateway used to place order
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	public $processor = '';

	/**
	 * Ticket buyer IP address
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	public $ip_address = '';

	/**
	 * Number of ticket purchased against the order
	 *
	 * @var    integer
	 * @since  2.5.0
	 */
	public $ticketscount = 0;

	/**
	 * JSON response sent by payment gateway
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	public $extra = '';

	/**
	 * tax applied against the order
	 *
	 * @var    float
	 * @since  2.5.0
	 */
	private $order_tax = 0;

	/**
	 * JSON for Tax splits or tax information sent by tax plugin
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	public $order_tax_details = '';

	/**
	 * Discount amount by applying coupon
	 *
	 * @var    float
	 * @since  2.5.0
	 */
	private $coupon_discount = 0.0;

	/**
	 * JSON for coupon detail information
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	private $coupon_discount_details = '';

	/**
	 * Ticket email is sent this order or not
	 *
	 * @var    integer
	 * @since  2.5.0
	 */
	public $ticket_email_sent = 0;

	/**
	 * Customer added note, while placing an order
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	public $customer_note = '';

	/**
	 * holds the already loaded instances of the Order
	 *
	 * @var    array
	 * @since  2.5.0
	 */
	protected static $orderObj = array();

	/**
	 * holds the items in order
	 *
	 * @var    array
	 * @since  2.5.0
	 */
	private $items = array();

	/**
	 * holds the Zero-decimal currencies
	 *
	 * @var    array
	 * @since  5.0.4
	 */
	private $zeroDecimalCurrencies = ['BIF', 'CLP', 'DJF', 'GNF', 'JPY', 'KMF', 'KRW', 'MGA', 'PYG', 'RWF', 'UGX', 'VND', 'VUV', 'XAF', 'XOF', 'XPF'];

	/**
	 * Constructor activating the default information of the order
	 *
	 * @param   int  $id  The unique event key to load.
	 *
	 * @since   2.5.0
	 */
	public function __construct($id = 0)
	{
		if (!empty($id))
		{
			$this->load($id);
		}

		if (! $this->id)
		{
			$nulldate = Factory::getDbo()->getNullDate();

			// Initialise the default variables
			// $this->cdate = $nulldate;
			$this->mdate = $nulldate;
		}
	}

	/**
	 * Returns the global order object
	 *
	 * @param   integer  $id  The primary key of the event to load (optional).
	 *
	 * @return  JTicketingOrder  The event object.
	 *
	 * @since   2.5.0
	 */
	public static function getInstance($id = 0)
	{
		if (!$id)
		{
			return new JTicketingOrder;
		}

		if (empty(self::$orderObj[$id]))
		{
			self::$orderObj[$id] = new JTicketingOrder($id);
		}

		return self::$orderObj[$id];
	}

	/**
	 * Method to load a order properties
	 *
	 * @param   int  $id  The order id
	 *
	 * @return  boolean  True on success
	 *
	 * @since   2.5.0
	 */
	public function load($id)
	{
		$table = JT::table("order");

		if ($table->load($id))
		{
			$this->id = (int) $table->get('id');
			$this->order_id = $table->get('order_id');
			$this->parent_order_id = (int) $table->get('parent_order_id');
			$this->event_details_id = (int) $table->get('event_details_id');
			$this->name = $table->get('name');
			$this->email = $table->get('email');
			$this->user_id = (int) $table->get('user_id');
			$this->cdate = $table->get('cdate');
			$this->mdate = $table->get('mdate');
			$this->transaction_id = $table->get('transaction_id');
			$this->payee_id = $table->get('payee_id');
			$this->order_amount = (float) $table->get('order_amount');
			$this->original_amount = (float) $table->get('original_amount');
			$this->amount = (float) $table->get('amount');
			$this->coupon_code = $table->get('coupon_code');
			$this->fee = (float) $table->get('fee');
			$this->status = $table->get('status');
			$this->processor = $table->get('processor');
			$this->ip_address = $table->get('ip_address');
			$this->ticketscount = (int) $table->get('ticketscount');
			$this->extra = $table->get('extra');
			$this->order_tax_details = $table->get('order_tax_details');
			$this->order_tax = (float) $table->get('order_tax');
			$this->coupon_discount = (float) $table->get('coupon_discount');
			$this->coupon_discount_details = $table->get('coupon_discount_details');
			$this->ticket_email_sent = (int) $table->get('ticket_email_sent');
			$this->customer_note = $table->get('customer_note');

			return true;
		}

		return false;
	}

	/**
	 * Method to load a order by Order by order ID
	 *
	 * @param   string  $orderId  The order Id
	 *
	 * @return  JTicketingOrder  Object on success
	 *
	 * @since   2.5.0
	 */
	public function loadByOrderId($orderId)
	{
		$table = JT::table("order");

		if ($table->load(array('order_id' => $orderId)))
		{
			return self::getInstance($table->id);
		}

		return self::getInstance();
	}

	/**
	 * Method to bind an associative array of data to a user object
	 *
	 * @param   array  $array  The associative array to bind to the object
	 *
	 * @return  boolean  True on success
	 *
	 * @since   2.5.0
	 */
	public function bind($array)
	{
		if (isset($array['event_details_id'])
			&& (!empty($this->event_details_id) && ($this->event_details_id == $array['event_details_id']))
			||  (empty($this->event_details_id)))
		{
			$this->event_details_id = $array['event_details_id'];
		}

		if (isset($array['parent_order_id']))
		{
			$this->parent_order_id = 0;
		}

		if (isset( $array['user_id']))
		{
			$this->user_id = $array['user_id'];

			$user = Factory::getUser($array['user_id']);
			$this->email = $user->email;
			$this->name = $user->name;
		}

		if (isset($array['status'])
			&& ($array['status'] == COM_JTICKETING_CONSTANT_ORDER_STATUS_INCOMPLETE
			|| $array['status'] == COM_JTICKETING_CONSTANT_ORDER_STATUS_FAILED
			|| $array['status'] == COM_JTICKETING_CONSTANT_ORDER_STATUS_PENDING))
		{
			$this->status = $array['status'];
		}

		if (isset($array['transaction_id']))
		{
			$this->transaction_id = $array['transaction_id'];
		}

		if (isset($array['customer_note']))
		{
			$this->customer_note = $array['customer_note'];
		}

		if (isset($array['ticket_email_sent']))
		{
			$this->ticket_email_sent = $array['ticket_email_sent'];
		}

		if (isset($array['extra']))
		{
			$this->extra = $array['extra'];
		}

		return true;
	}

	/**
	 * Method to create/load the Order
	 * This method will check the pending order against the user and event if found will return the same ow creates a new
	 *
	 * @return  boolean  True on success
	 *
	 * @since   2.5.0
	 */
	public function loadOrCreate()
	{
		$orderId = Factory::getSession()->get('JT_orderId');

		if ($this->isNew() && !empty($this->user_id))
		{
			// Check whther it is owner and its allowed to buy owner

			$event = JT::event()->loadByIntegration($this->event_details_id);

			if ($event->isBookingEnd())
			{
				$this->setError(Text::_('COM_JTICKETING_EVENT_IS_CLOSED'));

				return false;
			}

			if (($this->user_id == $event->getCreator()) && !JT::config()->get('eventowner_buy'))
			{
				$this->setError(Text::_('COM_JTICKETING_EVENT_OWNER_CANT_BUY'));

				return false;
			}

			$orders = JT::model('orders');
			$orderData = $orders->getOrders(
					array(
							'event_details_id' => $this->event_details_id,
							'user_id' => $this->user_id,
							'status' => COM_JTICKETING_CONSTANT_ORDER_STATUS_INCOMPLETE)
					);

			if (!empty($orderData[0]->id))
			{
				$this->load($orderData[0]->id);

				// Revalidate the order
				if (!$this->reValidateAmount())
				{
					$this->removeOrderItems();
				}

				return true;
			}

			return $this->save();
		}
		elseif ($this->isNew() && JT::config()->get('allow_buy_guest'))
		{
			if (empty($orderId))
			{
				// Create new order
				return $this->save();
			}

			// Avoid the completed orders in the sessions
			$sessionOrder = self::getInstance($orderId);

			if ($sessionOrder->status != COM_JTICKETING_CONSTANT_ORDER_STATUS_INCOMPLETE || $this->event_details_id != $sessionOrder->event_details_id)
			{
				Factory::getSession()->clear('JT_orderId');
			}
			else
			{
				$this->load($orderId);

				// Revalidate the order
				if (!$this->reValidateAmount())
				{
					$this->removeOrderItems();
				}

				return true;
			}

			return $this->save();
		}

		// No order is pending or not in the session and the order is already created or it's not allowed to create it
		return false;
	}

	/**
	 * Method to save the Order object to the database
	 *
	 * @return  boolean  True on success
	 *
	 * @since   2.5.0
	 */
	public function save()
	{
		$isNew = $this->isNew();

		// Create the order table object
		$table = JT::table('order');

		// Allow an exception to be thrown.
		try
		{
			$table->bind(get_object_vars($this));

			// Check and store the object.
			if (!$table->check())
			{
				$this->setError($table->getError());

				return false;
			}

			// Store the order data in the database
			$result = $table->store();

			// Set the id for the order object in case we created a new order.
			if ($result && $isNew)
			{
				$this->load($table->get('id'));
				$order = JT::model('order');
				$this->order_id = $order->generateOrderID($this->id);

				return $this->save();
			}
			elseif ($result && !$isNew)
			{
				return $this->load($this->id);
			}
		}
		catch (\Exception $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		return $result;
	}

	/**
	 * Method to check is order new or not
	 *
	 * @return  boolean  True on success
	 *
	 * @since   2.5.0
	 */
	private function isNew()
	{
		return $this->id < 1;
	}

	/**
	 * Method to add order Item
	 *
	 * @param   Object  $ticketData  Ticket data
	 *
	 * @return  boolean  True on success
	 *
	 * @since   2.5.0
	 */
	public function addItem(JTicketingTickettype $ticketData)
	{
		$orderItem              = JT::orderItem();
		$orderItem->order_id    = $this->id;
		$config                 = JT::config();
		$inclusiveFee                 = $config->get("admin_fee_mode");
		$singleTicket           = $config->get('single_ticket_per_user', 0);
		$orderLimit             = $config->get('max_noticket_peruserperpurchase', 0);
		$event                  = JT::event()->loadByIntegration($this->event_details_id);
		$currencyCode           = $config->get('currency');

		// In case of the single user per ticket case delete all the previous items before adding new one.
		if ($singleTicket || (int) $orderLimit === 1 || $event->isOnline())
		{
			$this->removeOrderItems();
		}

		// Validate user and order.
		if (!$this->check($ticketData, 'addItem'))
		{
			return false;
		}

		if (!$orderItem->addItem($ticketData))
		{
			$this->setError($orderItem->getError());

			return false;
		}

		$this->resetTaxValues();

		if ($this->coupon_code)
		{
			$this->resetCouponValues();
		}

		// Zero-decimal currencies. (It does not have decimal values, so all figures should be handled as integers.)
		if (in_array($currencyCode, $this->zeroDecimalCurrencies))
		{
			$this->order_amount     = round($this->order_amount + $orderItem->ticket_price);
			$this->original_amount  = round($this->original_amount + $orderItem->ticket_price);
			$this->ticketscount     = round($this->ticketscount + 1);
			$this->amount           = round($this->original_amount);
		}
		else
		{
			$this->order_amount     = $this->order_amount + $orderItem->ticket_price;
			$this->original_amount  = $this->original_amount + $orderItem->ticket_price;
			$this->ticketscount     = $this->ticketscount + 1;
			$this->amount           = $this->original_amount;
		}

		if ($orderItem->fee_amt > 0)
		{
			// Zero-decimal currencies. (It does not have decimal values, so all figures should be handled as integers.)
			if (in_array($currencyCode, $this->zeroDecimalCurrencies))
			{
				$this->fee = round($this->fee + $orderItem->fee_amt);
			}
			else
			{
				$this->fee = $this->fee + $orderItem->fee_amt;
			}
		}

		if ($this->coupon_code)
		{
			$this->applyCoupon($this->coupon_code, true);
		}

		$this->calculateFee();

		if (!$inclusiveFee)
		{
			// Zero-decimal currencies. (It does not have decimal values, so all figures should be handled as integers.)
			if (in_array($currencyCode, $this->zeroDecimalCurrencies))
			{
				$this->amount = round($this->amount + $this->fee);
			}
			else
			{
				$this->amount += $this->fee;
			}
			
		}

		if ($this->coupon_code)
		{
			$this->applyCoupon($this->coupon_code, true);
		}

		$this->applyTax();

		if ($this->save())
		{
			// Add for cache
			if (!empty($this->items))
			{
				$this->items[$orderItem->id] = $orderItem;
			}

			return true;
		}

		return false;
	}

	/**
	 * Method to remove order Item
	 *
	 * @param   Object  $ticketData  ticket type data
	 *
	 * @return  boolean  True on success
	 *
	 * @since   2.5.0
	 */
	public function removeItem(JTicketingTickettype $ticketData)
	{
		$config                 = JT::config();
		$singleTicket           = $config->get('single_ticket_per_user', 0);

		// Validate user and order.
		if (!$this->check($ticketData, 'removeItem'))
		{
			return false;
		}

		// In case of the single user per ticket case delete all the previous items before adding new one.
		if ($singleTicket)
		{
			$this->removeOrderItems();
		}

		/** @var $orderItemModels JTicketingModelOrderItems */
		$orderItemModels = JT::model('orderitems');
		$orderItemData = $orderItemModels->getOrderItems(
				array(
						'order_id' => $this->id,
						'type_id' => $ticketData->id)
				);

		if (empty($orderItemData))
		{
			return true;
		}

		// Always remove the last entry
		krsort($orderItemData);
		$orderItem = JT::orderItem();
		$orderItem->load(key($orderItemData));

		if (!$orderItem->delete())
		{
			$this->setError($orderItem->getError());

			return false;
		}

		$this->resetTaxValues();

		if ($this->coupon_code)
		{
			$this->resetCouponValues();
		}

		$this->ticketscount     = max(0, ($this->ticketscount - 1));

		if ($this->ticketscount == 0)
		{
			$this->amount           = 0;
			$this->order_amount     = 0;
			$this->original_amount  = 0;
			$this->fee = 0;

			if ($this->coupon_code)
			{
				$this->removeCoupon($this->coupon_code);
			}
		}
		else
		{
			// Remove from the cache if available
			if (!empty($this->items))
			{
				unset($this->items[$orderItem->id]);
			}

			$this->validateFee();

			if (!$config->get("admin_fee_mode"))
			{
				$this->amount += $this->fee;
			}

			if ($this->coupon_code)
			{
				$this->applyCoupon($this->coupon_code, true);
			}

			// Well...! Now reverse engineering
			$this->applyTax();
		}

		return $this->save();
	}

	/**
	 * Method to calculate order level fee
	 *
	 * @return  boolean  True on success
	 *
	 * @since   2.5.0
	 */
	private function validateFee()
	{
		$this->order_amount = $this->original_amount = $this->ticketscount = $this->amount = $this->fee = 0;

		$orderItems = $this->getItems();

		if (empty($orderItems))
		{
			return true;
		}

		foreach ($orderItems as $item)
		{
			$this->order_amount     = $this->order_amount + $item->ticket_price;
			$this->original_amount  = $this->original_amount + $item->ticket_price;
			$this->ticketscount     = $this->ticketscount + 1;
			$this->amount           = max(0, ($this->amount + $item->ticket_price));
			$this->fee              += $item->fee_amt;
		}

		return $this->calculateFee();
	}

	/**
	 * Method to calculate order level fee
	 *
	 * @return  boolean  True on success
	 *
	 * @since   2.5.0
	 */
	private function calculateFee()
	{
		$params = JT::config();
		$adminFeeLevel = $params->get('admin_fee_level');
		$siteAdminFeeCap = $params->get('siteadmin_comm_cap');
		$handleTransaction = $params->get('handle_transactions');

		if ($handleTransaction)
		{
			$this->fee = 0;

			return true;
		}

		$feeAmount = $this->original_amount;

		if ($adminFeeLevel == 'orderitem')
		{
			// If Fee amount is greater than fee cap then take fee cap instead of fees
			if ($siteAdminFeeCap > 0 && $this->fee > $siteAdminFeeCap)
			{
				$this->fee = $siteAdminFeeCap;
			}

			return true;
		}
		else
		{
			if ($siteAdminFeeCap && $siteAdminFeeCap > 0)
			{
				$feeAmount = $siteAdminFeeCap;
			}
		}

		$orderModel = JT::model('order');
		$vendorSpecificFee = $orderModel->getUserSpecificCommision($this->event_details_id);
		$siteAdminFeePer = isset($vendorSpecificFee->percent_commission) && ($vendorSpecificFee->percent_commission != 0) ?$vendorSpecificFee->percent_commission:$params->get('siteadmin_comm_per');
		$siteAdminFlatFee = isset($vendorSpecificFee->flat_commission) && ($vendorSpecificFee->percent_commission != 0) ? $vendorSpecificFee->flat_commission :$params->get('siteadmin_comm_flat');

		if (($siteAdminFeePer == 0 && $siteAdminFlatFee == 0) || ($this->amount == 0))
		{
			$this->fee = 0;

			return true;
		}

		$perAmt = $feeAmount * $siteAdminFeePer / 100;
		$this->fee = $perAmt + $siteAdminFlatFee;

		// If Fee amount is greater than fee cap then take fee cap instead of fees
		if ($siteAdminFeeCap > 0 && $this->fee > $siteAdminFeeCap)
		{
			$this->fee = $siteAdminFeeCap;
		}

		return true;
	}

	/**
	 * Format the price of order object
	 *
	 * @param   boolean  $formated  Default true
	 *
	 * @return  integer|string    formatted like 3$ or 3
	 *
	 * @since   2.5.0
	 */
	public function getAmount($formated = true)
	{
		$params = JT::config();
		$currencyCode = $params->get('currency');

		if ($formated)
		{
			$utilities = JT::utilities();

			return $utilities->getFormattedPrice($this->amount);
		}

		// Zero-decimal currencies. (It does not have decimal values, so all figures should be handled as integers.)
		if (in_array($currencyCode, $this->zeroDecimalCurrencies))
		{
			return round($this->amount);
		}
		else
		{
			return $this->amount;
		}
	}

	/**
	 * Format the fee of order object
	 *
	 * @param   boolean  $formated  Default true
	 *
	 * @return  integer|string    formatted like 3$ or 3
	 *
	 * @since   2.5.0
	 */
	public function getFee($formated = true)
	{
		if ($formated)
		{
			$utilities = JT::utilities();

			return $utilities->getFormattedPrice($this->fee);
		}

		return $this->fee;
	}

	/**
	 * Format the fee of order object
	 *
	 * @return  object  User object
	 *
	 * @since   2.5.0
	 */
	public function getbillingdata()
	{
		/* var $userModel JticketingModelUser */
		$userModel      = JT::model('user');
		$orderItemData  = $userModel->getUser(
				array(
				'order_id' => $this->id)
				);

		// Avoid php warning for zero index of array
		if (!isset($orderItemData[0]))
		{
			$orderItemData[0] = '';
		}

		return $orderItemData[0];
	}

	/**
	 * Save billing information
	 *
	 * @param   array  $data  Billing address data
	 *
	 * @return  boolean  on success true on failure false
	 *
	 * @since   2.7.0
	 */
	public function addBillingData($data)
	{
		$user = JT::user()->loadByOrderId($this->id);
		$data['order_id'] = $this->id;

		if (!$user->bind($data))
		{
			$this->setError($user->getError());

			return false;
		}

		if (!$user->save())
		{
			$this->setError($user->getError());

			return false;
		}

		return true;
	}

	/**
	 * Method to get Order items
	 *
	 * @param   Boolean  $loadNew  True to get latest order items false for current objects items.
	 *
	 * @return  array  Items array
	 *
	 * @since   2.5.0
	 */
	public function getItems($loadNew = false)
	{
		if (empty($this->items) || $loadNew)
		{
			/** @var $orderItemModel JTicketingModelOrderItem */
			$orderItemModel = JT::model('orderitems', array('ignore_request' => true));
			$this->items = $orderItemModel->getOrderItems(array('order_id' => $this->id));
		}

		return $this->items;
	}

	/**
	 * get Order items by type
	 *
	 * @param   integer  $typeId  Ticket type ID
	 *
	 * @return  array  Items array
	 *
	 * @since   2.5.0
	 */
	public function getItemsByType($typeId)
	{
		$config = JT::config();
		$items = new stdClass;
		$feeLevel = $config->get('admin_fee_level');
		$feeMode = $config->get('admin_fee_mode');
		$orderItemModel = JT::model('orderitems');
		$itemsByType = $orderItemModel->getOrderItems(
			array('order_id' => $this->id,
					'type_id' => $typeId)
		);

		$items->totalPrize = 0;
		$items->totalFee = 0;
		$items->count = 0;
		$items->price = 0;

		if ($itemsByType)
		{
			foreach ($itemsByType as $item)
			{
				if ($feeLevel == 'orderitem' && $feeMode == 0)
				{
					$items->totalPrize = $items->totalPrize + $item->ticket_price;
				}
				else
				{
					$items->totalPrize = $items->totalPrize + $item->amount_paid;
				}

				$items->totalFee   = $items->totalFee + $item->fee_amt;
				$items->count = $items->count + 1;
				$items->price = $item->ticket_price;
			}
		}

		return $items;
	}

	/**
	 * Validate coupon code
	 *
	 * @param   string   $couponCode  coupon code
	 * @param   boolean  $updateOnly  Flag to determine whether want to store the coupon discount or just update it
	 *
	 * @return  boolean  true on success and false on failed case.
	 *
	 * @since   2.5.0
	 */
	public function applyCoupon($couponCode, $updateOnly = false)
	{
		if (!$this->isOwner())
		{
			return false;
		}

		if ($this->status == COM_JTICKETING_CONSTANT_ORDER_STATUS_COMPLETED)
		{
			$this->setError(Text::_('COM_JTICKETING_NOT_ALLOWED_TO_MODIFY_COMPLETED_ORDER'));

			return false;
		}

		$coupon        = JT::coupon();
		$coupon->loadByCode($couponCode);
		$isValid = $coupon->isValid($this->event_details_id);

		if ($isValid !== true)
		{
			if ($this->coupon_code == $couponCode)
			{
				$this->removeCoupon($couponCode);
			}

			$this->setError($coupon->getError());

			return false;
		}

		if ($this->original_amount <= 0 && !empty($this->coupon_code))
		{
			if ($this->coupon_code == $couponCode)
			{
				$this->removeCoupon($couponCode);
			}

			return false;
		}

		// Reset the values if already applied the coupon
		if (!empty($this->coupon_discount))
		{
			$this->resetCouponValues();
		}

		$val = (float) $coupon->value;

		// Need to remove tax before applying the coupon
		$this->resetTaxValues();

		if ($coupon->val_type == 1 && $val)
		{
			$val = (float) ($val / 100) * $this->amount;
		}

		$this->amount = max(0, ($this->amount - $val));
		$this->coupon_discount = $val;
		$this->coupon_code = $coupon->code;
		$couponDetails = array("value" => $coupon->value, "val_type" => $coupon->val_type, "coupon_code" => $coupon->code);
		$this->coupon_discount_details = json_encode($couponDetails);
		$this->applyTax();
		$this->calculateFee();

		if ($updateOnly)
		{
			return true;
		}

		return $this->save();
	}

	/**
	 * Remove coupon code of specific order
	 *
	 * @param   string  $couponCode  coupon code
	 *
	 * @return  Boolean True on success and false on remove failed case.
	 *
	 * @since   2.5.0
	 */
	public function removeCoupon($couponCode)
	{
		if (!$this->isOwner())
		{
			return false;
		}

		$coupon         = JT::coupon();
		$coupon->loadByCode($couponCode);

		if ($this->status == COM_JTICKETING_CONSTANT_ORDER_STATUS_COMPLETED)
		{
			$this->setError(Text::_('COM_JTICKETING_NOT_ALLOWED_TO_MODIFY_COMPLETED_ORDER'));

			return false;
		}

		if ($this->coupon_code != $couponCode)
		{
			return false;
		}

		$this->resetCouponValues();
		$this->coupon_code = '';
		$this->resetTaxValues();
		$this->applyTax();

		return $this->save();
	}

	/**
	 * Mark the order status to complete and perform post operations
	 *
	 * @return  Boolean True on successful status change and false for failed case.
	 *
	 * @since   2.5.0
	 */
	public function complete()
	{
		// If status is not complete then only change the status.
		if ($this->status == COM_JTICKETING_CONSTANT_ORDER_STATUS_COMPLETED)
		{
			return true;
		}

		$this->status       = COM_JTICKETING_CONSTANT_ORDER_STATUS_COMPLETED;
		$this->processor = ($this->amount === 0.0) ? 'Free_ticket' : $this->processor;

		if (!$this->save())
		{
			return false;
		}

		/** @var $orderModel JticketingModelOrder */
		$orderModel         = JT::model("order", array('ignore_request' => true));
		
		// Add entry number to the ticket
		$orderModel->onAfterOrderConfirmedAddEntryNumber($this);

		// Perform on after order complete operations.
		if (!$orderModel->onAfterOrderComplete($this))
		{
			$this->setError($orderModel->getError());
			$orderModel->getError();

			return false;
		}

		return true;
	}

	/**
	 * function for update attendee status
	 *
	 * @return  boolean
	 *
	 * @since  2.5.0
	 */
	public function updateAttendeeStatus()
	{
		$attendeeStatuses = array(
			COM_JTICKETING_CONSTANT_ORDER_STATUS_COMPLETED => COM_JTICKETING_CONSTANT_ATTENDEE_STATUS_APPROVED,
			COM_JTICKETING_CONSTANT_ORDER_STATUS_PENDING => COM_JTICKETING_CONSTANT_ATTENDEE_STATUS_PENDING,
			COM_JTICKETING_CONSTANT_ORDER_STATUS_REVERSED => COM_JTICKETING_CONSTANT_ATTENDEE_STATUS_REJECTED,
			COM_JTICKETING_CONSTANT_ORDER_STATUS_REFUND => COM_JTICKETING_CONSTANT_ATTENDEE_STATUS_REJECTED,
			COM_JTICKETING_CONSTANT_ORDER_STATUS_FAILED => COM_JTICKETING_CONSTANT_ATTENDEE_STATUS_REJECTED,
			COM_JTICKETING_CONSTANT_ORDER_STATUS_DECLINE => COM_JTICKETING_CONSTANT_ATTENDEE_STATUS_REJECTED,
			COM_JTICKETING_CONSTANT_ORDER_STATUS_CANCEL_REVERSED => COM_JTICKETING_CONSTANT_ATTENDEE_STATUS_REJECTED,
			COM_JTICKETING_CONSTANT_ORDER_STATUS_UNDER_REVIEW => COM_JTICKETING_CONSTANT_ATTENDEE_STATUS_REJECTED,
		);

		$status = $attendeeStatuses[$this->getStatus()];

		// Get Order items
		$orderItemData = $this->getItems(true);

		if (!empty($orderItemData))
		{
			foreach ($orderItemData as $orderData)
			{
				$attendee                 = JT::Attendee($orderData->attendee_id);
				$attendee->status         = $status;
				$attendee->ticket_type_id = $orderData->type_id;

				if (!$attendee->save())
				{
					$this->setError($attendee->getError());

					return false;
				}
			}

			return true;
		}

		return false;
	}

	/**
	 * Get Order item types depending on the type id from order items
	 * This functions created due to architecture bug of storing the different order item for same type id.
	 *
	 * @return  Array | Boolean  Array of TicketType objects on success and boolean on false
	 *
	 * @since   2.5.0
	 */
	public function getItemTypes()
	{
		// Get order items.
		$this->items    = $this->getItems();

		if (empty($this->items))
		{
			$this->setError(Text::_("COM_JTICKETING_ORDER_ITEMS_ARE_EMPTY"));

			return false;
		}

		// Get the type id from the order items of the order.
		$result = array();

		foreach ($this->items as $item)
		{
			$result[] = $item->type_id;
		}

		// Get the unique type id from the array of items.
		$types = array_unique($result);

		// Return the type objects from the unique type address from order items.
		return array_map(
			function($each){
				return JT::Tickettype($each);
			}, $types
			);
	}

	/**
	 * Reset the coupon values based on the current state of the order variables
	 *
	 * @return  boolean  True on success
	 *
	 * @since   2.5.0
	 */
	private function resetCouponValues()
	{
		if (!empty($this->coupon_discount))
		{
			$isInclusive = JT::config()->get("admin_fee_mode");

			if ($this->amount < $this->coupon_discount)
			{
				$this->amount = $isInclusive ? $this->original_amount : $this->original_amount + $this->fee;
			}
			else
			{
				$this->amount = $this->amount + $this->coupon_discount;
			}

			$this->coupon_discount  = 0.0;
			$this->coupon_discount_details = '';
		}

		return true;
	}

	/**
	 * Apply tax based on the steps
	 *
	 * @return  boolean  True on success
	 *
	 * @since   2.5.0
	 */
	private function applyTax()
	{
		$config        = JT::config();

		if ($config->get('allow_taxation'))
		{
			$this->resetTaxValues();
			PluginHelper::importPlugin('jticketingtax');

			$taxResults     = Factory::getApplication()->triggerEvent('onJtCalculateTax', array($this));

			$tax = new Registry($taxResults);

			$this->order_tax_details = $tax->toString();

			// We may have multiple plugins enables for this
			foreach ($taxResults as $tax)
			{
				if (isset($tax->total))
				{
					// Typecasting to avoid A non-numeric value encountered warning
					$this->amount  += (float) $tax->total;
					$this->order_tax  += (float) $tax->total;
				}
			}
		}

		return true;
	}

	/**
	 * Reset the tax variables
	 *
	 * @return  boolean  True on success
	 *
	 * @since   2.5.0
	 */
	private function resetTaxValues()
	{
		if (!empty($this->order_tax))
		{
			// First calculate the tax amount
			$taxAmount = max(0, $this->amount - $this->order_tax);

			$this->amount    = $taxAmount;
			$this->order_tax = 0;
			$this->order_tax_details = '';
		}

		return true;
	}

	/**
	 * Validate user with order object and check for access level.
	 *
	 * @param   JTicketingTickettype  $ticketData  The current ticket type data
	 * @param   string                $action      The action calling the check method
	 *
	 * @return  boolean  True on success
	 *
	 * @since   2.5.0
	 */
	private function check($ticketData, $action)
	{
		$config             = JT::config();
		$orderLimit         = $config->get('max_noticket_peruserperpurchase', 0);

		// Check if user is the owner of this order.
		if (!$this->isOwner())
		{
			$this->setError(Text::_('JERROR_ALERTNOAUTHOR'));

			return false;
		}

		// Check if order is already completed.
		if ($this->status === COM_JTICKETING_CONSTANT_ORDER_STATUS_COMPLETED)
		{
			$this->setError(Text::_('COM_JTICKETING_ORDER_IS_ALREADY_COMPLETED'));

			return false;
		}

		if (!$ticketData->isAvailable())
		{
			$this->setError(Text::sprintf('COM_JTICKETING_TICKET_TYPE_PURCHASE_LIMIT_EXCEEDS', $ticketData->title));

			return false;
		}

		if (!$ticketData->isValidToBuy($this->event_details_id))
		{
			$this->setError(Text::_('COM_JTICKETING_ADD_ITEM_ITEMTYPE_IS_NOT_PRESENT'));

			return false;
		}

		// Check per transaction ticket buying limit.
		if ($this->ticketscount >= (int) $orderLimit && $action == 'addItem' && $orderLimit !== '0')
		{
			$this->setError(Text::sprintf('COM_JTICKETING_TRANCTION_TICKET_PURCHASE_LIMIT_EXCEEDS', $orderLimit));

			return false;
		}

		// Check that ticket type limit is exceeded or not.
		$typeDeatails = $this->getItemsByType($ticketData->id);

		if (isset($ticketData->max_ticket_per_order) && $ticketData->max_ticket_per_order && ($typeDeatails->count + 1) > $ticketData->max_ticket_per_order && $action == 'addItem')
		{
			$this->setError(Text::sprintf('COM_JTICKETING_TRANCTION_MAX_TICKET_TICKET_PURCHASE_LIMIT_EXCEEDS', $ticketData->max_ticket_per_order));

			return false;
		}

		if (!$ticketData->unlimited_seats && $typeDeatails->count >= $ticketData->count && $action == 'addItem')
		{
			$this->setError(Text::sprintf('COM_JTICKETING_TICKET_TYPE_PURCHASE_LIMIT_EXCEEDS', $ticketData->title));

			return false;
		}

		return true;
	}

	/**
	 * Method to remove the order Items
	 * Once all the order is removed the amount and other stat will reset to it's default values
	 *
	 * @return  boolean  True on success
	 *
	 * @since   2.5.0
	 */
	public function removeOrderItems()
	{
		$orderItems = $this->getItems();

		foreach ($orderItems as $orderItemData)
		{
			$orderItemObj = JT::orderItem();
			$orderItemObj->load($orderItemData->id);

			if (!$orderItemObj->delete())
			{
				$this->setError($orderItemObj->getError());

				return false;
			}
		}

		// Remove from the cache if available
		$this->items = null;

		// We have removed all order item so just reset the statistics to default.

		$this->coupon_code = $this->order_tax_details = $this->coupon_discount_details = "";
		$this->order_amount = $this->original_amount = $this->ticketscount = 0;
		$this->amount = $this->fee = $this->order_tax = $this->coupon_discount = 0;

		return $this->save();
	}

	/**
	 * Method to get the order tax details
	 *
	 * @return  String  order tax details.
	 *
	 * @since   2.5.0
	 */
	public function getTaxDetails()
	{
		return $this->order_tax_details;
	}

	/**
	 * Method to get the order original amount
	 *
	 * @return  float  order original amount without fee and without tax and discount.
	 *
	 * @since   2.5.0
	 */
	public function getOriginalAmount()
	{
		return $this->original_amount;
	}

	/**
	 * Method to get the current state of object
	 *
	 * @return  string  return the current state of order
	 *
	 * @since   2.5.0
	 */
	public function getStatus()
	{
		return $this->status;
	}

	/**
	 * Method to get the net amount
	 * The amount return from here not includes the discount and taxes
	 * The fee is added in the net amount based on the configuration
	 *
	 * @return  float  return net amount depending on the inclusive config of fee
	 *
	 * @since   2.5.0
	 */
	public function getNetAmount()
	{
		$isInclusive = JT::config()->get("admin_fee_mode");

		return $isInclusive ? $this->original_amount : $this->original_amount + $this->fee;
	}

	/**
	 * This method will check whether the user is owner of this order or not
	 *
	 * @param   integer  $userId  The joomla user id
	 *
	 * @return  boolean
	 *
	 * @since   2.5.0
	 */
	public function isOwner($userId = null)
	{
		if (is_null($userId))
		{
			$userId = Factory::getUser()->id;
		}

		return $this->user_id == (int) $userId;
	}

	/**
	 * Method to revalidate the order amount
	 *
	 * @return  boolean  true on success
	 *
	 * @since   2.5.0
	 */
	public function reValidateAmount()
	{
		// Store the original amount for later
		$oldAmount = $this->amount;

		// Get the order Items available in the order
		$OrderItems = $this->getItems(true);

		if (empty($OrderItems))
		{
			return false;
		}

		$ticketStat = array();

		foreach ($OrderItems as $OrderItem)
		{
			$odItem = JT::orderItem($OrderItem->id);

			if (!$odItem->reValid($this->event_details_id))
			{
				return false;
			}

			// Avoid php warning Undefined index for incremental values
			if (!isset($ticketStat[$odItem->type_id]['count']))
			{
				$ticketStat[$odItem->type_id]['count'] = 0;
			}

			$ticketStat[$odItem->type_id]['count'] += 1;
		}

		foreach ($ticketStat as $ticketId => $stat)
		{
			$ticket = JT::tickettype($ticketId);

			$available = $ticket->getAvailable();

			if ($available != COM_JTICKETING_CONSTANT_TICKET_TYPE_UNLIMITED && $available < $stat['count'])
			{
				return false;
			}
		}

		// Now validate the order level config's
		$orderLimit = JT::config()->get('max_noticket_peruserperpurchase', 0);

		// Check per transaction ticket buying limit.
		if ($this->ticketscount > (int) $orderLimit && $orderLimit != 0)
		{
			return false;
		}

		$this->items = null;

		$this->resetTaxValues();

		if (!empty($this->coupon_code))
		{
			$this->resetCouponValues();
		}

		// At this stage all the Order items are validated Now validate the fee
		$this->validateFee();

		if (!JT::config()->get('admin_fee_mode'))
		{
			$this->amount += $this->fee;
		}

		if (!empty($this->coupon_code))
		{
			$this->applyCoupon($this->coupon_code, true);
		}

		$this->applyTax();

		/**
		 * Remove the decimal values
		 * Comparing with integer due to the below complications
		 *
		 * https://www.geeksforgeeks.org/comparing-float-value-in-php/
		 * https://stackoverflow.com/questions/9079158/php-dropping-decimals-without-rounding-up/9079182
		 */
		if ((floor($this->amount)) != (floor($oldAmount)))
		{
			return false;
		}

		return true;
	}

	/**
	 * Returns a property of the object or the default value if the property is not set.
	 *
	 * @param   string  $property  The name of the property.
	 * @param   mixed   $default   The default value.
	 *
	 * @return  mixed    The value of the property.
	 *
	 * @since   2.5.0
	 */
	public function get($property, $default = null)
	{
		if (isset($this->$property))
		{
			return $this->$property;
		}

		return $default;
	}

	/**
	 * Method to get the applied Order tax
	 *
	 * @return  Float  return the applied order tax.
	 *
	 * @since   2.5.0
	 */
	public function getOrderTax()
	{
		return $this->order_tax;
	}

	/**
	 * Method to get the ticket count of the order
	 *
	 * @return  integer  return the no of tickets of the order.
	 *
	 * @since   2.5.0
	 */
	public function getTicketsCount()
	{
		return $this->ticketscount;
	}

	/**
	 * Method to get the coupon discount.
	 *
	 * @param   boolean  $formated  Default true
	 *
	 * @return  float|string    formatted like 3$ or 3.0
	 *
	 * @since   2.5.0
	 */
	public function getCouponDiscount($formated = true)
	{
		if ($formated)
		{
			$utilities = JT::utilities();

			return $utilities->getFormattedPrice($this->coupon_discount);
		}

		return $this->coupon_discount;
	}

	/**
	 * Method to get the coupon code applied to order.
	 *
	 * @return  String  returns the coupon code applied to order
	 *
	 * @since   2.5.0
	 */
	public function getCouponCode()
	{
		return $this->coupon_code;
	}

	/**
	 * Method to delete the order
	 *
	 * @return  boolean  True on success
	 *
	 * @since   2.5.0
	 */
	public function delete()
	{
		$oldOrder = clone $this;

		// Get the statuses where we need to update the ticket count
		/** @var $ordersModel JticketingModelorders*/
		$ordersModel = JT::model('orders');

		/** @var $orderModel JticketingModelorder*/
		$orderModel = JT::model('order');
		$allStatuses = $orderModel->getOrderStatues();
		$statuses = $ordersModel->getValidOrderStatus(COM_JTICKETING_CONSTANT_ORDER_STATUS_COMPLETED, $allStatuses);

		// Check whether the  order is already completed
		if (!empty($statuses[$this->status]))
		{
			// We have to reverse the ticket count and coupon count if applied
			$itemTypes = $this->getItemTypes();

			foreach ($itemTypes as $item)
			{
				// Update only limited tickets
				if (empty($item->unlimited_seats))
				{
					$itemStat = $this->getItemsByType($item->id);
					$item->count = (int) max(0, $item->count + $itemStat->count);
					$item->save();
				}
			}

			// Check for coupon and update the count
			if (!empty($this->coupon_code))
			{
				$coupon = JT::coupon();
				$coupon->loadByCode($this->coupon_code);
				$coupon->used = max(0, $coupon->used - 1);
				$coupon->save();
			}

			// Remove the user as member for integration
			/**
			 * @TODO need to check whether other order are completed against the same user if not then only remove as member
			 * $event = JT::event();
			 * $event = $event->loadByIntegration($this->event_details_id);
			 * $event->deleteMember($this->user_id);
			 */
		}

		// Remove the billing info
		$orderModel->deleteBillingInfo($this);

		// @TODO remove the activities against the order

		// Remove the order items
		$this->removeOrderItems();

		// Create the user table object
		$table = JT::table('order');

		if (!$table->delete($this->id))
		{
			$this->setError($table->getError());

			return false;
		}

		// Trigger the event
		PluginHelper::importPlugin('jticketing');
		Factory::getApplication()->triggerEvent('onAfterJtOrderDelete', array($oldOrder));

		return true;
	}

	/**
	 * Method to refund order
	 *
	 * @return  void
	 *
	 * @since   3.3.0
	 */
	public function refund()
	{
		// Check for coupon and update the count
		if (!empty($this->coupon_code))
		{
			$coupon = JT::coupon();
			$coupon->loadByCode($this->coupon_code);
			$coupon->used = max(0, $coupon->used - 1);
			$coupon->save();
		}

		// Remove the order items
		if (!$this->removeOrderItems())
		{
			$this->setError($this->getError());

			return false;
		}

		$this->status = COM_JTICKETING_CONSTANT_ORDER_STATUS_REFUND;

		if (!$this->save())
		{
			$this->setError($this->getError());

			return false;
		}
	}
	/**
	 * Method to reverse order sets attendee status to rejected
	 *
	 * @return  void
	 *
	 * @since   3.3.0
	 */
	public function reverse()
	{
		$db = Factory::getDbo();
	
		// Update attendees for this order's user & event
		$query = $db->getQuery(true)
			->update($db->quoteName('#__jticketing_attendees'))
			->set($db->quoteName('status') . ' = ' . $db->quote('R'))
			->where($db->quoteName('owner_id') . ' = ' . (int) $this->user_id)
			->where($db->quoteName('event_id') . ' = ' . (int) $this->event_details_id);
		$db->setQuery($query);
		$db->execute();
	
		return true;
	}
}