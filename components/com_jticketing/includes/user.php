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
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Language\Text;

/**
 * JTicketing user class.
 *
 * @since  2.5.0
 */
class JTicketingUser extends CMSObject
{
	/**
	 * The auto incremental primary key of the JTicketing user table
	 *
	 * @var    integer
	 * @since  2.5.0
	 */
	public $id = 0;

	/**
	 * Joomla user ID
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	public $user_id = 0;

	/**
	 * Order table primary key
	 *
	 * @var    integer
	 * @since  2.5.0
	 */
	public $order_id = 0;

	/**
	 * buyer email ID
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	public $user_email = '';

	/**
	 * Address type
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	public $address_type = 'BT';

	/**
	 * Ticket buyer first name
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	public $firstname = '';

	/**
	 * Ticket buyer last name
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	public $lastname = '';

	/**
	 * Ticket buyer registration type
	 *
	 * @var    integer
	 * @since  DEPLOY_VERSION
	 */
	public $registration_type = 0;

	/**
	 * Ticket buyer business name
	 *
	 * @var    string
	 * @since  DEPLOY_VERSION
	 */
	public $business_name = '';

	/**
	 * Ticket buyer VAT number
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	public $vat_number = '';

	/**
	 * Ticket buyer tax state
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	public $tax_exempt = 0;

	/**
	 * Country code of TJField country table
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	public $country_code = '';

	/**
	 * Ticket buyer address
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	public $address = '';

	/**
	 * Ticket buyer city
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	public $city = '';

	/**
	 * Ticket buyer state code
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	public $state_code = '';

	/**
	 * Ticket buyer zip code
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	public $zipcode = '';

	/**
	 * Ticket buyer phone number
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	public $phone = '';

	/**
	 * Billing address status
	 *
	 * @var    integer
	 * @since  2.5.0
	 */
	public $approved = 1;

	/**
	 * Country mobile code
	 *
	 * @var    integer
	 * @since  2.5.0
	 */
	public $country_mobile_code = 0;

	/**
	 * comment
	 *
	 * @var    varchar
	 * @since  2.5.0
	 */
	public $comment = '';

	/**
	 * holds the already loaded instances of the Order
	 *
	 * @var    array
	 * @since  2.5.0
	 */
	protected static $userObj = array();

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
			return new JTicketingUser;
		}

		if (empty(self::$userObj[$id]))
		{
			self::$userObj[$id] = new JTicketingUser($id);
		}

		return self::$userObj[$id];
	}

	/**
	 * Method to load a user properties
	 *
	 * @param   int  $id  The user id
	 *
	 * @return  boolean  True on success
	 *
	 * @since   2.5.0
	 */
	public function load($id)
	{
		$table = JT::table("user");

		if ($table->load($id))
		{
			$this->setProperties($table->getProperties());

			$this->id = (int) $table->get('id');
			$this->user_id = (int) $table->get('user_id');
			$this->order_id = (int) $table->get('order_id');
			$this->tax_exempt = (int) $table->get('tax_exempt');
			$this->country_mobile_code = (int) $table->get('country_mobile_code');
			$this->comment = (int) $table->get('comment');

			return true;
		}

		return false;
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
		if (isset($array['registration_type']) && trim($array['business_name']) == "" && $array['registration_type'] == "1")
		{
			$this->setError(Text::_("COM_JTICKETING_BILLING_DATA_INVALID_BUSINESS_NAME"));

			return false;
		}

		if (isset($array['order_id']))
		{
			$this->order_id = $array['order_id'];
		}

		if (isset($array['user_id']))
		{
			$this->user_id = $array['user_id'];
		}

		if ($array['user_email'])
		{
			$this->user_email = $array['user_email'];
		}

		if ($array['firstname'])
		{
			$this->firstname = $array['firstname'];
		}

		if (isset($array['lastname']))
		{
			$this->lastname = $array['lastname'];
		}

		if (isset($array['registration_type']) && $array['registration_type'])
		{
			$this->registration_type = $array['registration_type'];
		}

		if (isset($array['business_name']) && $array['business_name'])
		{
			$this->business_name = $array['business_name'];
		}

		if (isset($array['vat_number']) && $array['vat_number'])
		{
			$this->vat_number = $array['vat_number'];
		}

		if (isset($array['country_code']))
		{
			$this->country_code = $array['country_code'];
		}

		if (isset($array['address']))
		{
			$this->address = $array['address'];
		}

		if (isset($array['city']))
		{
			$this->city = $array['city'];
		}

		if (isset($array['state_code']))
		{
			$this->state_code = $array['state_code'];
		}

		if (isset($array['zipcode']))
		{
			$this->zipcode = $array['zipcode'];
		}

		if (isset($array['phone']))
		{
			$this->phone = $array['phone'];
		}

		if (isset($array['country_mobile_code']))
		{
			$this->country_mobile_code = $array['country_mobile_code'];
		}

		if (isset($array['comment']))
		{
			$this->comment = $array['comment'];
		}

		return true;
	}

	/**
	 * Method to save the User object to the database
	 *
	 * @return  boolean  True on success
	 *
	 * @since   2.5.0
	 * @throws  \RuntimeException
	 */
	public function save()
	{
		$isNew = $this->isNew();
		$table = JT::table('user');

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

			// Store the billing info in the database
			$result = $table->store();

			// Set the id for the billing info object in case we created a new order item.
			if ($result && $isNew)
			{
				$this->id = $table->get('id');
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
	 * Method to load a billing details by order id
	 *
	 * @param   int  $id  The order id
	 *
	 * @return  Array|Boolean  false on fail and Array on success
	 *
	 * @since   2.7.0
	 */
	public function loadByOrderId($id)
	{
		$table = JT::table("user");

		if ($table->load(array('order_id' => $id)))
		{
			return self::getInstance($table->id);
		}

		return self::getInstance();
	}
}
