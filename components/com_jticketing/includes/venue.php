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
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Registry\Registry;
use Joomla\String\StringHelper;

/**
 * JTicketing venue class.
 *
 * @since  2.4.0
 */
class JTicketingVenue extends CMSObject
{
	/**
	 * The auto incremental primary key of the venue
	 *
	 * @var    integer
	 * @since  2.4.0
	 */
	public $id = 0;

	/**
	 * Vendor id of the venue
	 *
	 * @var    integer
	 * @since  2.4.0
	 */
	public $vendor_id = 0;

	/**
	 * Asset id
	 *
	 * @var    integer
	 * @since  2.4.0
	 */
	public $asset_id = 0;

	/**
	 * Name of the venue
	 *
	 * @var    string
	 * @since  2.4.0
	 */
	public $name = '';

	/**
	 * unique string identifier of the venue
	 *
	 * @var    string
	 * @since  2.4.0
	 */
	public $alias = '';

	/**
	 * Category id (derived from joomla)
	 *
	 * @var    integer
	 * @since  2.4.0
	 */
	public $venue_category = 0;

	/**
	 *
	 *
	 * @var    integer
	 * @since  2.4.0
	 */
	public $online = 0;

	/**
	 * The provider for the online event
	 *
	 * @var    string
	 * @since  2.4.0
	 */
	public $online_provider = '';

	/**
	 * Venue country
	 *
	 * @var    integer
	 * @since  2.4.0
	 */
	public $country = 0;

	/**
	 * Venue state
	 *
	 * @var    string
	 * @since  2.4.0
	 */
	public $state_id = 0;

	/**
	 * Venue city
	 *
	 * @var    string
	 * @since  2.4.0
	 */
	public $city = '';

	/**
	 * Venue zip code
	 *
	 * @var    string
	 * @since  2.4.0
	 */
	public $zipcode = '';

	/**
	 * Venue address
	 *
	 * @var    string
	 * @since  2.4.0
	 */
	public $address = '';

	/**
	 * Venue latitude
	 *
	 * @var    float
	 * @since  2.4.0
	 */
	public $latitude = 0.00;

	/**
	 * Venue longitude
	 *
	 * @var    float
	 * @since  2.4.0
	 */
	public $longitude = 0.00;

	/**
	 *
	 * @var    integer
	 * @since  2.4.0
	 */
	public $privacy = 0;

	/**
	 * The Joomla user id of the creator
	 *
	 * @var    integer
	 * @since  2.4.0
	 */
	public $created_by = 0;

	/**
	 * The Joomla user id of the modifier
	 *
	 * @var    string
	 * @since  2.4.0
	 */
	public $modified_by = 0;

	/**
	 * The state of the venue
	 *
	 * @var    integer
	 * @since  2.4.0
	 */
	public $state = 0;

	/**
	 * Extra information regarding the venue
	 *
	 * @var    string
	 * @since  2.4.0
	 */
	public $params = '';

	/**
	 * Ordering of the event
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
	 * The venue checked out date and time
	 *
	 * @var    string
	 * @since  2.4.0
	 */
	public $checked_out_time = '';

	/**
	 * holds the already loaded instances of the venue
	 *
	 * @var    array
	 * @since  2.4.0
	 */
	protected static $venueObj = array();

	/**
	 * Constructor activating the default information of the venue
	 *
	 * @param   int  $id  The unique venue key to load.
	 *
	 * @since   2.4.0
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
			$this->checked_out_time = $nulldate;
		}
	}

	/**
	 * Returns the global venue object
	 *
	 * @param   integer  $id  The primary key of the venue to load (optional).
	 *
	 * @return  JTicketingVenue  The venue object.
	 *
	 * @since   2.4.0
	 */
	public static function getInstance($id = 0)
	{
		if (!$id)
		{
			return new JTicketingVenue;
		}

		if (empty(self::$venueObj[$id]))
		{
			self::$venueObj[$id] = new JTicketingVenue($id);
		}

		return self::$venueObj[$id];
	}

	/**
	 * Method to load a venue properties
	 *
	 * @param   int  $id  The venue id
	 *
	 * @return  boolean  True on success
	 *
	 * @since   2.4.0
	 */
	public function load($id)
	{
		$table = JT::table("venue");

		if ($table->load($id))
		{
			$this->setProperties($table->getProperties());

			$this->id = (int) $table->get('id');
			$this->vendor_id = (int) $table->get('vendor_id');
			$this->asset_id = (int) $table->get('asset_id');
			$this->ordering = (int) $table->get('ordering');
			$this->checked_out = (int) $table->get('checked_out');
			$this->created_by = (int) $table->get('created_by');
			$this->modified_by = (int) $table->get('modified_by');
			$this->venue_category = (int) $table->get('venue_category');
			$this->online = (int) $table->get('online');
			$this->online_provider = $table->get('online_provider');
			$this->country = (int) $table->get('country');
			$this->state_id = (int) $table->get('state_id');
			$this->longitude = (float) $table->get('longitude');
			$this->latitude = (float) $table->get('latitude');
			$this->privacy = (int) $table->get('privacy');

			return true;
		}

		return false;
	}

	/**
	 * Method to save venue
	 *
	 * @return  boolean  True on success
	 *
	 * @since   2.4.0
	 */
	public function save()
	{
		$table = JT::table("venue");
		$table->bind($this->getProperties());

		if (!$table->check())
		{
			$this->setError($table->getError());

			return false;
		}

		$result = $table->store();

		if ($result)
		{
			$this->id = $table->get('id');

			return true;
		}

		$this->setError($table->getError());

		return false;
	}

	/**
	 * This method will return the params
	 *
	 * @return  String  params
	 *
	 * @since   2.5.0
	 */
	public function getParams()
	{
		return $this->params;
	}

	/**
	 * This method return all the events associated with the venue.
	 *
	 * @return  array  array of the events
	 *
	 * @since   3.0.0
	 */
	public function getOnlineEvents()
	{
		$event = JT::onlineEvent($this);

		return $event->list();
	}

	/**
	 * Get the online event service provider
	 *
	 * @return  string  The name of the online provider
	 *
	 * @since   3.0.0
	 */
	public function getOnlineProvider()
	{
		$param = new Registry($this->params);

		// @TODO this need to be fixed on the plugin level
		if ($this->online_provider == 'plug_tjevents_adobeconnect')
		{
			return "AdobeConnect" . $param->get('event_type');
		}

		return $this->online_provider . $param->get('event_type');
	}
}
