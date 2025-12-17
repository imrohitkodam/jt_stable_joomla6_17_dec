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
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Helper\TagsHelper;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\String\StringHelper;

/**
 * JTicketing event class.
 *
 * @since  2.5.0
 */
class JTicketingEventJticketing extends JTicketingEvent
{
	/**
	 * The auto incremental primary key of the event
	 *
	 * @var    integer
	 * @since  2.5.0
	 */
	public $id = 0;

	/**
	 * joomla user id of the creator
	 *
	 * @var    integer
	 * @since  2.5.0
	 */
	public $created_by = 0;

	/**
	 * Event title
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	public $title = '';

	/**
	 * unique string identifier of the event
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	public $alias = '';

	/**
	 * Category id (derived from joomla)
	 *
	 * @var    integer
	 * @since  2.5.0
	 */
	public $catid = 0;

	/**
	 *
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	public $ideal_time = '';

	/**
	 * The venue of the event xref to venue table
	 *
	 * @var    integer
	 * @since  2.5.0
	 */
	public $venue = 0;

	/**
	 * Event short description
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	public $short_description = '';

	/**
	 * Event long description
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	public $long_description = '';

	/**
	 * Event start date
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	public $startdate = '';

	/**
	 * Event end date
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	public $enddate = '';

	/**
	 * Event booking start date
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	public $booking_start_date = '';

	/**
	 * Event booking end date
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	public $booking_end_date = '';

	/**
	 * Event custom location
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	public $location = '';

	/**
	 * Location lattitude
	 *
	 * @var    float
	 * @since  2.5.0
	 */
	public $latitude = 0.00;

	/**
	 * Location longitude
	 *
	 * @var    float
	 * @since  2.5.0
	 */
	public $longitude = 0.00;

	/**
	 * Permission for attendance
	 * 0 - Open (Anyone can mark attendance);
	 * 1 - Private (Only invited can mark attendance)'
	 *
	 * @var    integer
	 * @since  2.5.0
	 */
	public $permission = 0;

	/**
	 * Event image name
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	public $image = '';

	/**
	 * The system date of event creation
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	public $created = '';

	/**
	 * The modification date of the event
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	public $modified = '';

	/**
	 * The state of the event
	 *
	 * @var    integer
	 * @since  2.5.0
	 */
	public $state = 0;

	/**
	 * Whether the Event creator want to show event attendee publically or not
	 *
	 * @var    integer
	 * @since  2.5.0
	 */
	public $allow_view_attendee = 0;

	/**
	 * Which Joomla access level have permission to access this event (Manager/Publisher)
	 *
	 * @var    integer
	 * @since  2.5.0
	 */
	public $access = 0;

	/**
	 * Used to show the featured event
	 *
	 * @var    integer
	 * @since  2.5.0
	 */
	public $featured = 0;

	/**
	 * Used to identify the online events
	 *
	 * @var    integer
	 * @since  2.5.0
	 */
	public $online_events = 0;

	/**
	 * Ordering of the event
	 *
	 * @var    integer
	 * @since  2.5.0
	 */
	public $ordering = 0;

	/**
	 * The Joomla User id who checked out the record
	 *
	 * @var    integer
	 * @since  2.5.0
	 */
	public $checked_out = 0;

	/**
	 * The event checked out date and time
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	public $checked_out_time = '';

	/**
	 * Extra information regarding the event
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	public $params = '';

	/**
	 * Meta data of the event
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	public $meta_data = '';

	/**
	 * Meta description of the event
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	public $meta_desc = '';

	/**
	 * holds the already loaded instances of the event
	 *
	 * @var    array
	 * @since  2.5.0
	 */
	protected static $eventObj = array();

	/**
	 * Constructor activating the default information of the event
	 *
	 * @param   int  $eventId  The unique event key to load.
	 *
	 * @since   2.5.0
	 */
	public function __construct($eventId)
	{
		if (!empty($eventId))
		{
			$this->load($eventId);
		}

		if (! $this->id)
		{
			$nulldate = Factory::getDbo()->getNullDate();

			// Initialise the default variables
			$this->startdate = $nulldate;
			$this->enddate = $nulldate;
			$this->booking_start_date = $nulldate;
			$this->booking_end_date = $nulldate;
			$this->created = $nulldate;
			$this->modified = $nulldate;
			$this->checked_out_time = $nulldate;
		}

		parent::__construct($eventId, 'com_jticketing');
	}

	/**
	 * Returns the global event object
	 *
	 * @param   integer  $id  The primary key of the event to load (optional).
	 *
	 * @return  JTicketingEventJticketing  The event object.
	 *
	 * @since   3.0.0
	 */
	public static function loadInstance($id = 0)
	{
		if (!$id)
		{
			// Always return the native event object
			return new JTicketingEventJticketing($id);
		}

		if (empty(self::$eventObj[$id]))
		{
			$event = new JTicketingEventJticketing($id);

			if ($event->isOnline())
			{
				PluginHelper::importPlugin('tjevents');

				$provider = $event->getOnlineProvider();

				$eventClass = 'JTicketingEvent' . StringHelper::ucfirst($provider);
				$implementedInterface = class_implements($eventClass);

				if (class_exists($eventClass) && (in_array('JTicketingEventOnline', $implementedInterface)))
				{
					$event = new $eventClass($event);
				}
			}

			self::$eventObj[$id] = $event;
		}

		return self::$eventObj[$id];
	}

	/**
	 * Method to load a event properties
	 *
	 * @param   int  $id  The event id
	 *
	 * @return  boolean  True on success
	 *
	 * @since   2.5.0
	 */
	public function load($id)
	{
		/** @var $table JticketingTableEvent */
		$table = JT::table("event");

		if ($table->load($id))
		{
			$this->setProperties($table->getProperties());

			$this->id = (int) $table->get('id');
			$this->created_by = (int) $table->get('created_by');
			$this->catid = (int) $table->get('catid');
			$this->ideal_time = (int) $table->get('ideal_time');
			$this->venue = (int) $table->get('venue');
			$this->latitude = (float) $table->get('latitude');
			$this->longitude = (float) $table->get('longitude');
			$this->permission = (int) $table->get('permission');
			$this->state = (int) $table->get('state');
			$this->allow_view_attendee = (int) $table->get('allow_view_attendee');
			$this->access = (int) $table->get('access');
			$this->featured = (int) $table->get('featured');
			$this->online_events = (int) $table->get('online_events');
			$this->ordering = (int) $table->get('ordering');
			$this->checked_out = (int) $table->get('checked_out');

			return true;
		}

		return false;
	}

	/**
	 * This method will return the venue details
	 *
	 * @return  JTicketingVenue  The venue object
	 *
	 * @since   2.5.0
	 */
	public function getVenueDetails()
	{
		if ($this->venue != 0)
		{
			$venue = JT::venue($this->venue);

			if ($this->online_events)
			{
				return $venue->name;
			}

			return $venue->name . ', ' . $venue->address;
		}

		return $this->location;
	}

	/**
	 * This method will return the event title
	 *
	 * @return  string  Event title
	 *
	 * @since   2.5.0
	 */
	public function getTitle()
	{
		return $this->title;
	}

	/**
	 * This method will return the event start date
	 *
	 * @return  string  Event startDate
	 *
	 * @since   2.5.0
	 */
	public function getStartDate()
	{
		return $this->startdate;
	}

	/**
	 * This method will return the event end date
	 *
	 * @return  string  Event enddate
	 *
	 * @since   2.5.0
	 */
	public function getEndDate()
	{
		return $this->enddate;
	}

	/**
	 * This method will return the event Id
	 *
	 * @return  integer  Event Id
	 *
	 * @since   2.5.0
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * This method will return the event creator Joomla user id
	 *
	 * @return  integer
	 *
	 * @since   2.5.0
	 */
	public function getCreator()
	{
		return $this->created_by;
	}

	/**
	 * Determine if the event is end
	 *
	 * @return  boolean return true if the event is over
	 *
	 * @since   2.5.0
	 */
	public function isOver()
	{
		$currentDate = Factory::getDate()->toUnix();
		$eventEndDate = Factory::getDate($this->enddate, 'UTC')->toUnix();

		return $eventEndDate < $currentDate;
	}

	/**
	 * Determine if the event is yet to start
	 *
	 * @return  boolean return true if the event is yet to start
	 *
	 * @since   2.5.0
	 */
	public function isUpcoming()
	{
		$currentDate = Factory::getDate()->toUnix();
		$eventStartDate = Factory::getDate($this->startdate, 'UTC')->toUnix();

		return $eventStartDate > $currentDate;
	}

	/**
	 * Determine if the event booking is end
	 *
	 * @return  boolean return true if the booking is over
	 *
	 * @since   2.5.0
	 */
	public function isBookingEnd()
	{
		$user = Factory::getUser();
		$currentDate = Factory::getDate()->toUnix();

		if ($this->booking_end_date == '0000-00-00 00:00:00')
		{
			$bookingEndDate = Factory::getDate($this->enddate, 'UTC')->toUnix();
		}
		else
		{
			$bookingEndDate = Factory::getDate($this->booking_end_date, 'UTC')->toUnix();
		}

		if ($bookingEndDate < $currentDate)
		{
			return true;
		}

		// For integrated events we need to check the booking end time based on the ticket end date
		$ticketTypes = $this->getTicketTypes();

		// If tickets are not available for booking return false
		foreach ($ticketTypes as $ticketType)
		{

			// If ticket is available for booking return false
			if (empty($ticketType->ticket_enddate) ||
				(Factory::getDate($ticketType->ticket_enddate, 'UTC')->toUnix() > $currentDate))
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Determine if the event booking is started
	 *
	 * @return  boolean return true if the booking is already started
	 *
	 * @since   2.5.0
	 */
	public function isBookingStarted()
	{
		$currentDate = Factory::getDate()->toUnix();

		if ($this->booking_end_date == '0000-00-00 00:00:00')
		{
			$bookingStartDate = Factory::getDate($this->created, 'UTC')->toUnix();
		}
		else
		{
			$bookingStartDate = Factory::getDate($this->booking_start_date, 'UTC')->toUnix();
		}

		return $bookingStartDate < $currentDate;
	}

	/**
	 * This method return the path of the event image
	 *
	 * @param   string  $size  The image size
	 *
	 * @return  string  the event image path
	 *
	 * @since   2.5.0
	 */
	public function getAvatar($size = 'media_l')
	{
		$modelMediaXref = JT::model('MediaXref');
		$modelMedia = JT::model('Media');
		$eventMainImage = $modelMediaXref->getEventMedia($this->id, 'com_jticketing.event', 0);

		if (!empty($eventMainImage))
		{
			$media = $modelMedia->getItem($eventMainImage[0]->media_id);

			return $media->{$size};
		}

		return Route::_(Uri::root() . 'media/com_jticketing/images/default-event-image.png');
	}

	/**
	 * Method to load a event organizer Avatar
	 *
	 * @return  string  Image path of the organizer
	 *
	 * @since   2.5.0
	 */
	public function getOrganizerAvatar()
	{
		$gravatar = JT::config()->get('gravatar');

		if ($gravatar)
		{
			$user = Factory::getUser($this->getCreator());

			// Refer https://en.gravatar.com/site/implement/images/php/
			$hash = md5(strtolower(trim($user->email)));

			return 'https://www.gravatar.com/avatar/' . $hash . '?s=32';
		}
		else
		{
			return Uri::root() . 'media/com_jticketing/images/default_avatar.png';
		}
	}

	/**
	 * Method to load a event organizer profile link
	 *
	 * @return  string  profile path of the organizer
	 *
	 * @since   2.5.0
	 */
	public function getOrganizerProfileLink()
	{
		// Implement the Joomla profile link here if applicable

		return '';
	}

	/**
	 * Method to load the attendee of an event
	 *
	 * @param   int  $limitstart  Pagination limit start
	 * @param   int  $limit       Pagination limit
	 *
	 * @return  array The list of the attendees
	 *
	 * @since   2.5.0
	 */
	public function getAtttendees($limitstart = 0, $limit = 5)
	{
		$attendeesModel = JT::model('attendees', array("ignore_request" => true));
		$attendeesModel->setState('list.start', $limitstart);
		$attendeesModel->setState('list.limit', $limit);

		return $attendeesModel->getItems();
	}

	/**
	 * Method to load a event tags if the tagging is eabled
	 *
	 * @return  array|boolean  Array of of tag objects or boolean if the tags are not enabled
	 *
	 * @since   2.5.0
	 */
	public function getTags()
	{
		if (!JT::config()->get('show_tags', '0') || !$this->id)
		{
			return false;
		}

		$tagHelper = new TagsHelper;

		return $tagHelper->getItemTags('com_jticketing.event', $this->id);
	}

	/**
	 * This method will return the event url
	 *
	 * @param   boolean  $sef  flag to get sef or non sef URL
	 *
	 * @return  string
	 *
	 * @since   2.5.0
	 */
	public function getUrl($sef = true)
	{
		$link = "index.php?option=com_jticketing&view=event&id=" . $this->id;

		if (!$sef)
		{
			return $link;
		}

		$itemId = JT::utilities()->getItemId($link, 0, $this->catid);

		return Route::_($link . '&Itemid=' . $itemId, false);
	}

	/**
	 * This method will return the category Id
	 *
	 * @return  integer  Category Id
	 *
	 * @since   2.5.0
	 */
	public function getCategory()
	{
		return $this->catid;
	}

	/**
	 * Determine if the event is online
	 *
	 * @return  boolean return true if the event is online
	 *
	 * @since   2.5.0
	 */
	public function isOnline()
	{
		return !empty($this->online_events);
	}

	/**
	 * Determine if the event is online
	 *
	 * @return  string  The name of the online provider
	 *
	 * @since   3.0.0
	 */
	public function getOnlineProvider()
	{
		// There is no direct way to find out the online provider so need to get this from venue
		$venueObj = JT::venue($this->venue);

		return $venueObj->getOnlineProvider();
	}

	/**
	 * This method will return the params of the event
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
	 * Check certification enabled
	 *
	 * @return  Boolean
	 *
	 * @since   2.7.0
	 */
	public function isCertificationEnabled()
	{
		$config                 = JT::config();
		$integration            = $config->get('integration');
		$certification          = $config->get('enable_certification');
		$isTJCertificateEnabled = ComponentHelper::isEnabled('com_tjcertificate');

		if ($integration == COM_JTICKETING_CONSTANT_INTEGRATION_NATIVE && $isTJCertificateEnabled && $certification)
		{
			return true;
		}

		return false;
	}

	/**
	 * Get certificate template
	 *
	 * @return  Integer|Boolean
	 *
	 * @since   2.7.0
	 */
	public function getCertificateTemplate()
	{
		if (JT::utilities()->isJSON($this->params))
		{
			$jtParams = json_decode($this->params, true);

			return $jtParams['certificate_template'];
		}

		return false;
	}

	/**
	 * Get certificate expiry
	 *
	 * @return  Integer|Boolean
	 *
	 * @since   2.7.0
	 */
	public function getCertificateExpiry()
	{
		if (JT::utilities()->isJSON($this->params))
		{
			$jtParams = json_decode($this->params, true);

			return $jtParams['certificate_expiry'];
		}

		return false;
	}

	/**
	 * Generate and return replacement tag's object.
	 *
	 * @return  object
	 *
	 * @since  2.7.0
	 */
	public function getReplacementTags()
	{
		$replacement = new stdClass;

		$config     = JT::config();
		$dateFormat = $config->get('date_format_show');
		if ($dateFormat == "custom")
		{
			$dateFormat = $config->get('custom_format');
		}

		// Event details
		$replacement->event            = new stdClass;
		$replacement->event            = $this;
		$replacement->event->title     = $this->getTitle();
		$replacement->event->startdate = HTMLHelper::date($this->startdate, $dateFormat, true);
		$replacement->event->enddate   = HTMLHelper::date($this->enddate, $dateFormat, true);

		// Event vendor details
		$vendorDetails                        = $this->getVendorDetails();
		$replacement->event->organizer        = $vendorDetails->vendor_title;
		$replacement->event->organizer_detail = $vendorDetails->vendor_description;

		// Event venue location
		$replacement->event->location = $this->getVenueDetails();

		$jtParams = $this->params;

		if (JT::utilities()->isJSON($jtParams))
		{
			$jtParams = json_decode($this->params, true);
		}

		$certificateExpiryDate = !empty($jtParams['certificate_expiry']) ?
		$jtParams['certificate_expiry'] : 0;

		if (!empty($certificateExpiryDate) && $certificateExpiryDate > 0)
		{
			$certificateExpiryDate = Factory::getDate($this->startdate, 'UTC');
			$certificateExpiryDate->modify("+" . $jtParams['certificate_expiry'] . " days");
			$certificateExpiryDate = $certificateExpiryDate->toSql();
		}

		// Event certificate details
		$replacement->certificate = new stdClass;

		if ($certificateExpiryDate)
		{
			$replacement->certificate->expiry = HTMLHelper::date($certificateExpiryDate, $dateFormat, true);
		}
		else
		{
			$replacement->certificate->expiry = "";
		}

		return $replacement;
	}

	/**
	 * Method to save the Event object to the database
	 *
	 * @param   array  $data  The event data to be bind with the object
	 *
	 * @return  boolean  True on success
	 *
	 * @since   3.0.0
	 */
	public function save($data)
	{
		/** @var $table JticketingTableEvent */
		$table = JT::table("event");

		try
		{
			$result = $table->save(get_object_vars($this));

			if (!$result)
			{
				$this->setError($table->getError());

				return false;
			}

			return $this->load($table->get('id'));
		}
		catch (\Exception $e)
		{
			$this->setError($e->getMessage());

			return false;
		}
	}

	/**
	 * Method to check if event is multiple day
	 *
	 * @return  integer
	 *
	 * @since   3.2.0
	 */
	public function isMultiDay()
	{
		$startdate 	= new DateTime($this->getStartDate());
		$enddate 	= new DateTime($this->getEndDate());

		if ($startdate->format('Y-m-d') < $enddate->format('Y-m-d'))
		{
			return 1;
		}

		return 0;
	}

	/**
	 * Get state of the event
	 *
	 * @return  Integer
	 *
	 * @since   3.2.0
	 */
	public function getState()
	{
		return $this->state;
	}


	/**
	 * Retrieve recurring events for a given event
	 *
	 * Fetches recurring events from `#__jticketing_recurring_events` based on the event ID.
	 * Returns an empty array if the event has no recurrence or no valid ID.
	 *
	 * @param   object  $eventDetails  Event details with ID and recurring type.
	 *
	 * @return  array    List of recurring event objects (`r_id`, `start_date`, `start_time`).
	 *
	 * @since   3.2.0
	 */

	public function getRecurringEventsByEventDetails($eventDetails)
	{
		$db = Factory::getDbo();
		if (empty($eventDetails->id)) {
			return [];
		}
		if (isset($eventDetails->recurring_type) && $eventDetails->recurring_type === 'No_repeat') {
			return [];
		}
		$recurring_query = $db->getQuery(true);
		$recurring_query->select('r.r_id, r.start_date, r.start_time')
			->from($db->quoteName('#__jticketing_recurring_events') . ' AS r')
			->where('r.event_id = ' . $db->quote($eventDetails->id));
		$db->setQuery($recurring_query);
		$recurring_events = $db->loadObjectList();
		return $recurring_events;
	}

}
