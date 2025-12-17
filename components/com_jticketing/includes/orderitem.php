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
use Joomla\CMS\Object\CMSObject;

/**
 * JTicketing event class.
 *
 * @since  2.5.0
 */
class JTicketingOrderItem extends CMSObject
{
	/**
	 * The auto incremental primary key of the order item
	 *
	 * @var    integer
	 * @since  2.5.0
	 */
	public $id = 0;

	/**
	 * Order table primary key - Foreign key of the order item
	 *
	 * @var    integer
	 * @since  2.5.0
	 */
	public $order_id = 0;

	/**
	 * Ticket type ID
	 *
	 * @var    integer
	 * @since  2.5.0
	 */
	public $type_id = 0;

	/**
	 * attendee Id against this order item
	 *
	 * @var    integer
	 * @since  2.5.0
	 */
	public $attendee_id = 0;

	/**
	 * Ticket count
	 *
	 * @var    integer
	 * @since  2.5.0
	 */
	public $ticketcount = 1;

	/**
	 * Actual ticket price against the ticket type
	 *
	 * @var    integer
	 * @since  2.5.0
	 */
	public $ticket_price = 0;

	/**
	 * Amount which buyer has paid while purchasing a ticket
	 *
	 * @var    integer
	 * @since  2.5.0
	 */
	public $amount_paid = 0;

	/**
	 * Total fees paid by buyer against the order item
	 *
	 * @var    integer
	 * @since  2.5.0
	 */
	public $fee_amt = 0;

	/**
	 * Fees details
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	public $fee_params = '';

	/**
	 * Not used
	 *
	 * @var    integer
	 * @since  2.5.0
	 */
	public $attribute_amount = 0;

	/**
	 * Coupon discount against the order item - Not use
	 *
	 * @var    integer
	 * @since  2.5.0
	 */
	public $coupon_discount = 0;

	/**
	 * Payment status against the order item - Not use
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	public $payment_status = '';

	/**
	 * Ticket buyer name- Not use
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	public $name = '';

	/**
	 * Ticket buyer email - Not use
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	public $email = '';

	/**
	 * Comment added against the order item - Not use
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	public $comment = '';

	/**
	 * holds the already loaded instances of the Order Items
	 *
	 * @var    array
	 * @since  2.5.0
	 */
	protected static $orderItemObj = array();

	/**
	 * holds the Zero-decimal currencies
	 *
	 * @var    array
	 * @since  5.0.4
	 */
	private $zeroDecimalCurrencies = ['BIF', 'CLP', 'DJF', 'GNF', 'JPY', 'KMF', 'KRW', 'MGA', 'PYG', 'RWF', 'UGX', 'VND', 'VUV', 'XAF', 'XOF', 'XPF'];

	/**
	 * Constructor activating the default information of the order item
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
	}

	/**
	 * Returns the global order item object
	 *
	 * @param   integer  $id  The primary key of the event to load (optional).
	 *
	 * @return  JTicketingOrderItem  The event object.
	 *
	 * @since   2.5.0
	 */
	public static function getInstance($id = 0)
	{
		if (!$id)
		{
			return new JTicketingOrderItem;
		}

		if (empty(self::$orderItemObj[$id]))
		{
			self::$orderItemObj[$id] = new JTicketingOrderItem($id);
		}

		return self::$orderItemObj[$id];
	}

	/**
	 * Method to load a order item properties
	 *
	 * @param   int  $id  The order id
	 *
	 * @return  boolean  True on success
	 *
	 * @since   2.5.0
	 */
	public function load($id)
	{
		$table = JT::table("orderitem");

		if ($table->load($id))
		{
			$this->setProperties($table->getProperties());

			$this->id = (int) $table->get('id');
			$this->order_id = (int) $table->get('order_id');
			$this->type_id = (int) $table->get('type_id');
			$this->attendee_id = (int) $table->get('attendee_id');
			$this->ticketcount = (int) $table->get('ticketcount');
			$this->ticket_price = (float) $table->get('ticket_price');
			$this->amount_paid = (float) $table->get('amount_paid');
			$this->fee_amt = (float) $table->get('fee_amt');
			$this->attribute_amount = (float) $table->get('ticketscount');
			$this->coupon_discount = (int) $table->get('coupon_discount');

			return true;
		}

		return false;
	}

	/**
	 * Method to bind an associative array of data to a order Item object
	 *
	 * @param   array  $array  The associative array to bind to the object
	 *
	 * @return  boolean  True on success
	 *
	 * @since   2.5.0
	 */
	public function bind($array)
	{
		$this->order_id = $array['order_id'];
		$this->type_id = $array['type_id'];
		$this->ticket_price = $array['ticket_price'];

		return true;
	}

	/**
	 * Method to save the Order Item object to the database
	 *
	 * @return  boolean  True on success
	 *
	 * @since   2.5.0
	 */
	public function save()
	{
		$isNew = $this->isNew();
		$table = JT::table('orderitem');

		// Allow an exception to be thrown.
		try
		{
			$table->bind($this->getProperties());

			// Check and store the object.
			if (!$table->check())
			{
				$this->setError($table->getError());

				return false;
			}

			// Store the order item in the database
			$result = $table->store();

			// Set the id for the order item object in case we created a new order item.
			if ($result && $isNew)
			{
				$this->load($table->get('id'));

				$params = JT::config();
				$collectAttendeeInfo = $params->get('collect_attendee_info_checkout');

				if ($collectAttendeeInfo == 0)
				{
					// Add attendee entry on successful save of order Item
					$attendee               = JT::attendee();
					$user                   = Factory::getUser();
					$attendee->ticket_type_id = $this->type_id;
					$ticketType             = JT::tickettype($this->type_id);
					$attendee->event_id     = $ticketType->eventid;
					$attendee->owner_id     = $user->id;
					$attendee->owner_email  = $user->email;
					$attendee->save();

					$this->attendee_id      = $attendee->id;

					return $this->save();
				}
			}
			elseif ($result && !$isNew)
			{
				$this->load($this->id);

				return true;
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
	 * Method to delete the order Item object from the database
	 *
	 * @return  boolean  True on success
	 *
	 * @since   2.5.0
	 */
	public function delete()
	{
		// Create the user table object
		$table = JT::table('orderitem');

		if (!$table->delete($this->id))
		{
			$this->setError($table->getError());

			return false;
		}

		// Remove the attendee details

		if (!empty($this->attendee_id))
		{
			$attendee = JT::attendee($this->attendee_id);
			$attendee->delete();
		}

		return true;
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
		$config        = JT::config();
		$currencyCode  = $config->get('currency');

		$this->type_id = $ticketData->id;

		// Zero-decimal currencies. (It does not have decimal values, so all figures should be handled as integers.)
		if (in_array($currencyCode, $this->zeroDecimalCurrencies))
		{
			$this->ticket_price = round($ticketData->price);
			$this->amount_paid  = round($ticketData->price);
		}
		else
		{
			$this->ticket_price = $ticketData->price;
			$this->amount_paid  = $ticketData->price;
		}
		
		$this->calculateFee($ticketData);

		return $this->save();
	}

	/**
	 * Method to calculate order Item level fee
	 *
	 * @param   Object  $ticketData  Ticket data
	 *
	 * @return  boolean  True on success
	 *
	 * @since   2.5.0
	 */
	private function calculateFee($ticketData)
	{
		$params = JT::config();
		$adminFeeLevel = $params->get('admin_fee_level');
		$handleTransaction = $params->get('handle_transactions');
		$currencyCode  = $params->get('currency');
		$siteAdminFeeCap = $params->get('siteadmin_comm_cap');

		// If handle transaction is set then remove fee from item.
		if ($handleTransaction)
		{
			$this->fee_amt = 0;

			return true;
		}

		if ($adminFeeLevel == 'order')
		{
			return true;
		}

		// 1  : inclusive 0 : exclusive
		$isInclusive  = $params->get('admin_fee_mode');

		/** @var $orderModel JticketingModelOrder */
		$orderModel = JT::model('order');
		$vendorSpecificFee = $orderModel->getUserSpecificCommision($ticketData->eventid);
		$siteAdminFeePer = isset($vendorSpecificFee->percent_commission) && ($vendorSpecificFee->percent_commission != 0) ?$vendorSpecificFee->percent_commission:$params->get('siteadmin_comm_per');
		$siteAdminFlatFee = isset($vendorSpecificFee->flat_commission) && ($vendorSpecificFee->percent_commission != 0) ? $vendorSpecificFee->flat_commission :$params->get('siteadmin_comm_flat');
		$siteAdminFlatFee = ($ticketData->price == 0) ? 0 : $siteAdminFlatFee;

		if (($siteAdminFeePer == 0 && $siteAdminFlatFee == 0) || ($this->amount_paid == 0))
		{
			$this->fee_amt = 0;
			$this->fee_params = '';

			return true;
		}

		if ($siteAdminFeeCap && $siteAdminFeeCap > 0)
		{
			$this->amount_paid = $siteAdminFeeCap;
		}

		$perAmt = $this->amount_paid * $siteAdminFeePer / 100;

		// Zero-decimal currencies. (It does not have decimal values, so all figures should be handled as integers.)
		if (in_array($currencyCode, $this->zeroDecimalCurrencies))
		{
			$this->fee_amt = round($perAmt + $siteAdminFlatFee);
		}
		else
		{
			$this->fee_amt = ($perAmt + $siteAdminFlatFee);
		}

		// Fee params
		$orderItemFeeParam = new stdClass;
		$orderItemFeeParam->admin_fee_mode  = $isInclusive;
		$orderItemFeeParam->admin_fee_level = $adminFeeLevel;
		$this->fee_params = json_encode($orderItemFeeParam);

		// If exclusive fee (on top of ticket price)
		if (!$isInclusive)
		{
			// Zero-decimal currencies. (It does not have decimal values, so all figures should be handled as integers.)
			if (in_array($currencyCode, $this->zeroDecimalCurrencies))
			{
				$this->amount_paid = round($this->amount_paid + $this->fee_amt);
			}
			else
			{
				$this->amount_paid = ($this->amount_paid + $this->fee_amt);
			}
		}

		return true;
	}

	/**
	 * Method to check is order item new or not
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
	 * Method to load a order items by order id
	 *
	 * @param   int  $id  The order id
	 *
	 * @return  Array|Boolean  false on fail and Array on success
	 *
	 * @since   2.5.0
	 */
	public function loadByOrderId($id)
	{
		$orderItemsModel = JT::model('OrderItems');

		return $orderItemsModel->getOrderItems(array('order_id' => $id));
	}

	/**
	 * Validate the order item against the given event id
	 *
	 * @param   int  $eventId  The event integration id stored in the order table
	 *
	 * @return  boolean  true on success
	 *
	 * @since   2.5.0
	 */
	public function reValid($eventId)
	{
		// Validate ticket type
		$ticketType = JT::ticketType($this->type_id);

		if (($ticketType->price != $this->ticket_price) || !$ticketType->isValidToBuy($eventId))
		{
			return false;
		}

		$originalAmountPaid = $this->amount_paid;
		$this->amount_paid = $this->ticket_price;
		$this->calculateFee($ticketType);

		// If the calculated fee is having more than 2 decimal value then convert it to two decimal before comparing.
		if ($originalAmountPaid != round($this->amount_paid, 2))
		{
			self::$orderItemObj[$this->id] = null;

			return false;
		}

		self::$orderItemObj[$this->id] = null;

		return true;
	}

	/**
	 * Method to order item by attendee Id
	 *
	 * @param   string  $attendeeId  The attendee Id
	 *
	 * @return  JTicketingOrderItem  Object on success
	 *
	 * @since   2.5.0
	 */
	public function loadByAttendeeId($attendeeId)
	{
			$table = JT::table("orderitem");

		if ($table->load(array('attendee_id' => $attendeeId)))
		{
			return self::getInstance($table->id);
		}

		return self::getInstance();
	}
}