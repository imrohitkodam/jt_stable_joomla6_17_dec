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
use Joomla\Registry\Registry;

/**
 * JTicketing attendee class.
 *
 * @since  2.5.0
 */
class JTicketingAttendee extends CMSObject
{
	/**
	 * The auto incremental primary key of the attendee
	 *
	 * @var    integer
	 * @since  2.5.0
	 */
	public $id = 0;

	/**
	 * Combination of attendee ID and enrolment prefix
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	public $enrollment_id = '';

	/**
	 * Joomla user ID
	 *
	 * @var    integer
	 * @since  2.5.0
	 */
	public $owner_id = 0;

	/**
	 * Attendee email for guest checkout
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	public $owner_email = '';

	/**
	 * Attendee/enrolment approval status
	 * A = Approved, R = Rejected and P = pending
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	public $status = COM_JTICKETING_CONSTANT_ATTENDEE_STATUS_PENDING;

	/**
	 * Integration xref ID primary key
	 *
	 * @var    integer
	 *
	 * @since  2.5.0
	 */
	public $event_id = 0;

	/**
	 * ticket type table primary key
	 *
	 * @var    integer
	 * @since  2.5.0
	 */
	public $ticket_type_id = 0;

	/**
	 * Extra params stored against attendee
	 *
	 * @var    string
	 * @since  3.0.0
	 */
	private $params = '';

	/**
	 * Attendee first name
	 *
	 * @var    string
	 * @since  3.0.0
	 */
	private $first_name = null;

	/**
	 * Attendee last name
	 *
	 * @var    string
	 * @since  3.0.0
	 */
	private $last_name = null;

	/**
	 * Attendee Phone Number
	 *
	 * @var    string
	 * @since  3.1.0
	 */
	private $phoneNumber = null;

	/**
	 * holds the already loaded instances of the attendee
	 *
	 * @var    array
	 * @since  2.5.0
	 */
	protected static $attendeeObj = array();

	/**
	 * Client for which certificate is issued
	 *
	 * @var    string
	 * @since  2.7.0
	 */
	protected static $certificateClient = 'com_jticketing.event';

	/**
	 * Constructor activating the default information of the attendee
	 *
	 * @param   int  $id  The unique attendee key to load.
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
	 * Returns the global attendee object
	 *
	 * @param   integer  $id  The primary key of the attendee to load (optional).
	 *
	 * @return  JTicketingAttendee  The attendee object.
	 *
	 * @since   2.5.0
	 */
	public static function getInstance($id = 0)
	{
		if (!$id)
		{
			return new JTicketingAttendee;
		}

		if (empty(self::$attendeeObj[$id]))
		{
			self::$attendeeObj[$id] = new JTicketingAttendee($id);
		}

		return self::$attendeeObj[$id];
	}

	/**
	 * Method to load a attendee properties
	 *
	 * @param   int  $id  The order id
	 *
	 * @return  boolean  True on success
	 *
	 * @since   2.5.0
	 */
	public function load($id)
	{
		$table = JT::table("attendees");

		if ($table->load($id))
		{
			$this->id             = (int) $table->get('id');
			$this->enrollment_id  = $table->get('enrollment_id');
			$this->owner_id       = (int) $table->get('owner_id');
			$this->owner_email    = $table->get('owner_email');
			$this->status         = $table->get('status');
			$this->event_id       = (int) $table->get('event_id');
			$this->ticket_type_id = (int) $table->get('ticket_type_id');
			$this->params         = $table->get('params');

			return true;
		}

		return false;
	}

	/**
	 * Method to save the attendee object to the database
	 *
	 * @return  boolean  True on success
	 *
	 * @since   2.5.0
	 */
	public function save()
	{
		$isNew = $this->isNew();
		$table = JT::table('attendees');

		// Allow an exception to be thrown.
		try
		{
			$result = $table->save(get_object_vars($this));

			// Check and store the object.
			if (!$result)
			{
				$this->setError($table->getError());

				return false;
			}

			// Set the id for the order item object in case we created a new order item.
			if ($isNew)
			{
				$this->load($table->get('id'));

				// Generate enrolment ID
				$attendeeModel = JT::model('attendeeform');
				$this->enrollment_id = $attendeeModel->generateEnrollmentId($this->id);

				return $this->save();
			}

			return $this->load($this->id);
		}
		catch (\Exception $e)
		{
			$this->setError($e->getMessage());

			return false;
		}
	}

	/**
	 * Method to check is attendee new or not
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
	 * Method to delete the attendee
	 *
	 * @return  boolean  True on success
	 *
	 * @since   2.5.0
	 */
	public function delete()
	{
		// Delete the values from value table
		/** @var $valueModel JticketingModelAttendeefieldvalues */
		$valueModel = JT::model('Attendeefieldvalues');
		$valueModel->deleteAttendeeInfo($this);

		// Delete the check in data if available
		/** @var $checkinModel JticketingModelCheckin */
		$checkinModel = JT::model('checkin');
		$checkinModel->deleteCheckinInfo($this);

		// @TODO Remove the todos against the attendee

		$event = JT::event()->loadByIntegration($this->event_id);

		if ($event->isOnline())
		{
			// Remove attendee from online event for ex. zoom
			if (!$event->deleteAttendee($this))
			{
				$this->setError($event->getError());

				return false;
			}
		}

		$table = JT::table('attendees');

		if (!$table->delete($this->id))
		{
			$this->setError($table->getError());

			return false;
		}

		return true;
	}

	/**
	 * Check if Certificate already issued
	 *
	 * @return  object Tj Certificate's Certificate object
	 *
	 * @since  2.7.0
	 */
	public function checkCertificateIssued()
	{
		if (file_exists(JPATH_ADMINISTRATOR . '/components/com_tjcertificate/includes/tjcertificate.php')) { require_once JPATH_ADMINISTRATOR . '/components/com_tjcertificate/includes/tjcertificate.php'; }
		$tjCert = TJCERT::Certificate();

		return $tjCert::getIssued(self::$certificateClient, $this->event_id, 0, false, $this->id);
	}

	/**
	 * Check attendee is chacked In
	 *
	 * @return  Boolean true if checkIn and False if not checkin
	 *
	 * @since  3.0.0
	 */
	public function isCheckedIn()
	{
		$checkinModel = JT::model('checkin');
		$event = JT::event()->loadByIntegration($this->event_id);
		$user  = Factory::getUser();

		if ($checkinModel->getCheckinStatus($this->id) || $user->id == $event->userid)
		{
			return true;
		}

		$this->setError(Text::_('COM_JTICKETING_ATTENDEE_NOT_CHECKIN'));

		return false;
	}

	/**
	 * Get the params of the attendee
	 *
	 * @return  Registry Registry object of params
	 *
	 * @since  3.0.0
	 */
	public function getParams()
	{
		return new Registry($this->params);
	}

	/**
	 * Set the params of the attendee
	 *
	 * @param   Registry  $params  The registry object having params values
	 *
	 * @return  boolean Tru on success
	 *
	 * @since  3.0.0
	 */
	public function setParams(Registry $params)
	{
		$this->params = $params->toString();

		return true;
	}

	/**
	 * Get the Email id of the attendee
	 *
	 * @return  string  Email address
	 *
	 * @since  3.0.0
	 */
	public function getEmail()
	{
		$attendeeCollection = (bool) JT::config()->get('collect_attendee_info_checkout', false);

		if ($attendeeCollection && !empty($this->id))
		{
			$attendeeFields = JT::AttendeeFieldValues()->loadByAttendeeId($this->id);

			foreach ($attendeeFields as $field)
			{
				if ($field->name == "email")
				{
					return $field->field_value;
				}
			}
		}

		// We already storing custom field email id as owner email send it
		return $this->owner_email;
	}

	/**
	 * Get the first name of attendee
	 * In case of custom field it will retun name from custom field ow return Joomla full name
	 *
	 * @return  string  Attendee name
	 *
	 * @since  3.0.0
	 */
	public function getFirstName()
	{
		if (!is_null($this->first_name))
		{
			return $this->first_name;
		}

		$attendeeCollection = (bool) JT::config()->get('collect_attendee_info_checkout', false);

		if ($attendeeCollection && !empty($this->id))
		{
			$attendeeFields = JT::AttendeeFieldValues()->loadByAttendeeId($this->id);

			foreach ($attendeeFields as $field)
			{
				if ($field->name == "first_name")
				{
					$this->first_name = $field->field_value;
					break;
				}
			}
		}
		else if(!empty($this->id))
		{
			// Get the order billing details if attendee information is not present

			$orderItem = JT::orderItem()->loadByAttendeeId($this->id);

			$billingData = JT::order($orderItem->order_id)->getbillingdata();

			if (!empty($billingData->firstname))
			{
				$this->first_name = $billingData->firstname;
			}
		}

		if ($this->owner_id && empty($this->first_name))
		{
			$user = Factory::getUser($this->owner_id);

			if ($user->id)
			{
				$this->first_name = $user->name;
			}
		}

		return (string) $this->first_name;
	}

	/**
	 * Get the last name of attendee
	 * In case of custom field it will retun name from custom field ow return empty value
	 *
	 * @return  string  Attendee last name
	 *
	 * @since  3.0.0
	 */
	public function getLastName()
	{
		if (!is_null($this->last_name))
		{
			return $this->last_name;
		}

		$attendeeCollection = (bool) JT::config()->get('collect_attendee_info_checkout', false);

		if ($attendeeCollection && !empty($this->id))
		{
			$attendeeFields = JT::AttendeeFieldValues()->loadByAttendeeId($this->id);

			foreach ($attendeeFields as $field)
			{
				if ($field->name == "last_name")
				{
					$this->last_name = $field->field_value;
					break;
				}
			}
		}
		else if(!empty($this->id))
		{
			// Get the order billing details if attendee information is not present

			$orderItem = JT::orderItem()->loadByAttendeeId($this->id);

			$billingData = JT::order($orderItem->order_id)->getbillingdata();

			if (!empty($billingData->lastname))
			{
				$this->last_name = $billingData->lastname;
			}
		}

		// If still the Last name is empty then assign the first name to fulfill the required validation on this field by online providers(zoom, jitsi, etc.)
		if (empty($this->last_name))
		{
			$this->last_name = $this->first_name;
		}

		return (string) $this->last_name;
	}

	/**
	 * Get the Phone Number of attendee
	 * In case of custom field it will return Phone Number from custom field or return empty value
	 *
	 * @return  string  Attendee Phone Number with country code
	 *
	 * @since  3.1.0
	 */
	public function getPhoneNumber()
	{
		if (!is_null($this->phoneNumber))
		{
			return $this->phoneNumber;
		}

		$attendeeCollection = (bool) JT::config()->get('collect_attendee_info_checkout', false);

		if ($attendeeCollection)
		{
			$attendeeFields = JT::AttendeeFieldValues()->loadByAttendeeId($this->id);

			foreach ($attendeeFields as $field)
			{
				if ($field->name == "phone")
				{
					$this->phoneNumber = $field->field_value;

					break;
				}
			}
		}

		return (string) $this->phoneNumber;
	}
}
