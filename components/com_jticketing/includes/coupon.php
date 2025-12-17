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
use Joomla\CMS\Table\Table;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Object\CMSObject;

/**
 * JTicketing coupon class.
 *
 * @since  2.4.0
 */
class JTicketingCoupon extends CMSObject
{
	/**
	 * The auto incremental primary key of the coupon
	 *
	 * @var    integer
	 * @since  2.4.0
	 */
	public $id = 0;

	/**
	 * The state of the coupon
	 *
	 * @var    integer
	 * @since  2.4.0
	 */
	public $state = 0;

	/**
	 * Ordering of the coupon
	 *
	 * @var    integer
	 * @since  2.4.0
	 */
	public $ordering = 0;

	/**
	 * The Joomla User id who checked out the record
	 *
	 * @var    integer
	 * @since  2.4.0
	 */
	public $checked_out = 0;

	/**
	 * The coupon checked out date and time
	 *
	 * @var    string
	 * @since  2.4.0
	 */
	public $checked_out_time = '';

	/**
	 * Coupon name
	 *
	 * @var    string
	 * @since  2.4.0
	 */
	public $name = '';

	/**
	 * Coupon code
	 *
	 * @var    string
	 * @since  2.4.0
	 */
	public $code = '';

	/**
	 * Coupon discount amount
	 *
	 * @var    float
	 * @since  2.4.0
	 */
	public $value = 0;

	/**
	 * Coupon discount type(in percentage or flat. default flat)
	 *
	 * @var    integer
	 * @since  2.4.0
	 */
	public $val_type = 0;

	/**
	 * Coupon limit
	 *
	 * @var    integer
	 * @since  2.4.0
	 */
	public $limit = 0;

	/**
	 * Maximum per user coupon uses count
	 *
	 * @var    integer
	 * @since  2.4.0
	 */
	public $max_per_user = 0;

	/**
	 * Coupon valid from
	 *
	 * @var    string
	 * @since  2.4.0
	 */
	public $valid_from = '';

	/**
	 * Coupon valid to
	 *
	 * @var    string
	 * @since  2.4.0
	 */
	public $valid_to = '';

	/**
	 * joomla user id of the creator
	 *
	 * @var    integer
	 * @since  2.4.0
	 */
	public $created_by = 0;

	/**
	 * Events id
	 *
	 * @var    string
	 * @since  2.4.0
	 */
	public $event_ids = '';

	/**
	 * Used coupon count
	 *
	 * @var    integer
	 * @since  2.4.0
	 */
	public $used = 0;

	/**
	 * Coupon owner vendor id
	 *
	 * @var    integer
	 * @since  2.4.0
	 */
	public $vendor_id = 0;

	/**
	 * holds the already loaded instances of the coupon
	 *
	 * @var    array
	 * @since  2.4.0
	 */
	protected static $couponObj = array();

	/**
	 * Constructor activating the default information of the coupon
	 *
	 * @param   int  $id  The unique coupon key to load.
	 *
	 * @since   2.4.0
	 */
	public function __construct($id = 0)
	{
		if (!empty($id))
		{
			$this->load($id);
		}
	}

	/**
	 * Returns the global coupon object
	 *
	 * @param   integer  $id  The primary key of the coupon to load (optional).
	 *
	 * @return  JTicketingCoupon  The coupon object.
	 *
	 * @since   2.4.0
	 */
	public static function getInstance($id = 0)
	{
		if (!$id)
		{
			return new JTicketingCoupon;
		}

		// Check if the coupon id is already cached.
		if (empty(self::$couponObj[$id]))
		{
			self::$couponObj[$id] = new JTicketingCoupon($id);
		}

		return self::$couponObj[$id];
	}

	/**
	 * Method to load a coupon properties
	 *
	 * @param   int  $id  The coupon id
	 *
	 * @return  boolean  True on success
	 *
	 * @since   2.4.0
	 */
	public function load($id)
	{
		$table = JT::table("coupon");

		if ($table->load($id))
		{
			$this->setProperties($table->getProperties());
			$this->id               = (int) $table->get('id');
			$this->state            = (int) $table->get('state');
			$this->ordering         = (int) $table->get('ordering');
			$this->checked_out      = (int) $table->get('checked_out');
			$this->name             = $table->get('name');
			$this->code             = $table->get('code');
			$this->value            = (float) $table->get('value');
			$this->val_type         = (int) $table->get('val_type');
			$this->limit            = (int) $table->get('limit');
			$this->max_per_user     = (int) $table->get('max_per_user');
			$this->created_by       = (int) $table->get('created_by');
			$this->used             = (int) $table->get('used');
			$this->event_ids        = $table->get('event_ids');
			$this->vendor_id        = (int) $table->get('vendor_id');

			return true;
		}

		return false;
	}

	/**
	 * Method to load a coupon properties
	 *
	 * @param   string  $code  The coupon code
	 *
	 * @return  boolean|integer  id on success false on error
	 *
	 * @since   2.4.0
	 */
	public function loadByCode($code)
	{
		$table = JT::table("coupon");

		if ($table->load(array('code' => $code)))
		{
			if ($table->get('code') === $code)
			{
				$this->setProperties($table->getProperties());
				$this->id           = (int) $table->get('id');
				$this->state        = $table->get('state');
				$this->ordering     = (int) $table->get('ordering');
				$this->checked_out  = (int) $table->get('checked_out');
				$this->name         = $table->get('name');
				$this->code         = $table->get('code');
				$this->value        = (float) $table->get('value');
				$this->val_type     = (int) $table->get('val_type');
				$this->limit        = (int) $table->get('limit');
				$this->max_per_user = (int) $table->get('max_per_user');
				$this->created_by   = (int) $table->get('created_by');
				$this->used         = (int) $table->get('used');
				$this->event_ids    = $table->get('event_ids');
				$this->vendor_id    = (int) $table->get('vendor_id');

				return true;
			}
		}

		return false;
	}

	/**
	 * Method to validate coupon
	 *
	 * @param   integer  $eventId  An array containing related coupon code and event Id
	 *
	 * @return  boolean|array  True on success
	 *
	 * @since   2.4.0
	 */
	public function isValid($eventId)
	{
		$couponValidFrom = Factory::getDate($this->valid_from ? $this->valid_from : '', 'UTC')->toUnix();
		$couponValidTo   = Factory::getDate($this->valid_to ? $this->valid_to : '', 'UTC')->toUnix();
		$currentDate     = Factory::getDate()->toUnix();

		// Check coupon is valid to apply date wise
		if (($couponValidFrom > 0 || $couponValidTo > 0) && ($currentDate > $couponValidTo || $currentDate < $couponValidFrom))
		{
				$this->setError(Text::_('COM_JTICKETING_ORDER_COUPON_VALID_DATE'));

				return false;
		}

		// Check coupon is published state
		if ($this->state != '1')
		{
			$this->setError(Text::_('COP_EXISTS'));

			return false;
		}

		// Check coupon uses limit.
		if ((int) $this->limit > 0 && ((int) $this->limit <= (int) $this->used))
		{
			$this->setError(Text::_('COM_JTICKETING_ORDER_COUPON_MAX_USES'));

			return false;
		}

		if ($this->validateMaxUsesPerUser() === false)
		{
			$this->setError(Text::_('COM_JTICKETING_ORDER_COUPON_MAX_PER_USES'));

			return false;
		}

		// Check vendor validation.
		if ($this->validateVendorSpecificCoupon($eventId) === false)
		{
			$this->setError(Text::_('COM_JTICKETING_ORDER_COUPON_EVENT'));

			return false;
		}

		if ($this->validateEventSpecificCoupon($eventId) === false)
		{
			$this->setError(Text::_('COM_JTICKETING_ORDER_COUPON_EVENT'));

			return false;
		}

		return true;
	}

	/**
	 * Check if this coupon is assigned to user
	 *
	 * @return bool
	 */
	private function validateMaxUsesPerUser()
	{
		$user = Factory::getUser();

		if ($user->id && $this->max_per_user > 0)
		{
			$couponCodeArr = array('state' => $this->state, 'code' => $this->code, 'userId' => $user->id);

			$model = JT::model('coupons');
			$orderCount = $model->getUsedCouponCount($couponCodeArr);

			if ($this->max_per_user <= $orderCount)
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Perform the vendor validations
	 *
	 * @param   Integer  $eventId  Event Id
	 *
	 * @return bool
	 */
	private function validateVendorSpecificCoupon($eventId)
	{
		if (empty($this->vendor_id))
		{
			return true;
		}

		$event    = JT::event()->loadByIntegration($eventId);

		return $this->vendor_id == $event->vendor_id;
	}

	/**
	 * This will perform the event base validations
	 *
	 * @param   Integer  $eventId  Event Id
	 *
	 * @return bool
	 */
	private function validateEventSpecificCoupon($eventId)
	{
		if (!empty($this->event_ids))
		{
			$eventList = explode(",", $this->event_ids);

			if (!in_array($eventId, $eventList))
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Method to save the Coupon object to the database
	 *
	 * @return  boolean  True on success
	 *
	 * @since   2.5.0
	 */
	public function save()
	{
		$table = JT::table('coupon');

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

			// Store the coupon item in the database
			$result = $table->store();

			// Set the id for the coupon object in case we created a new coupon.
			if ($result)
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
	 * Check whether the user is creator or not
	 *
	 * @param   integer  $userId  The joomla user id
	 *
	 * @return  boolean
	 *
	 * @since   2.5.0
	 */
	public function isCreator($userId = null)
	{
		if (is_null($userId))
		{
			$userId = Factory::getUser()->id;
		}

		return $this->created_by === (int) $userId;
	}

	/**
	 * Check whether the user is a vendor for this coupon
	 *
	 * @param   integer  $userId  The Joomla user id
	 *
	 * @return  boolean
	 *
	 * @since   2.5.0
	 */
	public function isVendor($userId = null)
	{
		if (is_null($userId))
		{
			$userId = Factory::getUser()->id;
		}

		// Get the user id from the vendor id. We are retrieving the user id because we may have a multiple vendors for the same userid
		BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_tjvendors/models');

		// @TODO remove the below line when the issue is fixed https://github.com/techjoomla/com_tjvendors/issues/36
		Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_tjvendors/tables');

		/** @var $model TjvendorsModelVendor */
		$model = BaseDatabaseModel::getInstance("vendor", 'TjvendorsModel', array("ignore_request" => true));
		$vendor = $model->getItem($this->vendor_id);

		return $vendor->user_id == $userId;
	}
}
