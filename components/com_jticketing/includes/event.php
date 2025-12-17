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
use Joomla\CMS\Access\Access;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Object\CMSObject;
use Joomla\String\StringHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\HTML\HTMLHelper;

JLoader::discover("JTicketingEvent", JPATH_SITE . '/components/com_jticketing/includes/event');

/**
 * JTicketing event class.
 *
 * @since  2.5.0
 */
class JTicketingEvent extends CMSObject
{
	/**
	 * Primary key of the integration table
	 *
	 * @var    integer
	 * @since  2.5.0
	 */
	public $integrationId = 0;

	/**
	 * Vendor id of the event
	 *
	 * @var    integer
	 * @since  2.5.0
	 */
	public $vendor_id = 0;

	/**
	 * Primary key of the event the base table of this event id may vary based on the integration
	 *
	 * @var    integer
	 * @since  2.5.0
	 */
	public $eventid = 0;

	/**
	 *
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	public $source = '';

	/**
	 *
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	public $paypal_email = 0;

	/**
	 *
	 *
	 * @var    integer
	 * @since  2.5.0
	 */
	public $checkin = 0;

	/**
	 *
	 *
	 * @var    integer
	 * @since  2.5.0
	 */
	public $userid = 0;

	/**
	 *
	 *
	 * @var    integer
	 * @since  2.5.0
	 */
	public $cron_status = '';

	/**
	 *
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	public $cron_date = '';

	/**
	 * holds the already loaded ticketTypes
	 *
	 * @var    array
	 * @since  2.5.0
	 */
	private  $ticketTypes = array();

	/**
	 * holds the already loaded ticketTypes
	 *
	 * @var    array
	 * @since  2.5.0
	 */
	private  $allTicketTypes = array();

	/**
	 * hold the value of users purchase history
	 *
	 * This variable is set true if user bought the event at least once
	 *
	 * @var    integer
	 * @since  2.8.0
	 */
	private  $isBought = null;

	/**
	 *  Whether the type of event is paid/free
	 *
	 * @var    bool
	 * @since  2.8.0
	 */
	private  $isPaid = null;

	/**
	 * Is any pending current user enrolment present against the event
	 *
	 * @var    bool
	 * @since  2.8.0
	 */
	private  $isPending = null;

	/**
	 * Is any cancelled current user enrolment present against the event
	 *
	 * @var    bool
	 * @since  2.8.0
	 */
	private  $isCancelled = null;

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
	 * @param   int     $id               The unique event key to load.
	 * @param   string  $integrationType  the integration allowed with the ticketing system
	 *
	 * @since   2.5.0
	 */
	public function __construct($id = 0, $integrationType = 'com_jticketing')
	{
		if (!empty($id))
		{
			$this->loadEvent($id, $integrationType);
		}

		if (!$this->eventid)
		{
			$this->cron_date = Factory::getDbo()->getNullDate();
		}
	}

	/**
	 * Returns the global event object
	 *
	 * @param   integer  $id               The primary key of the event to load (optional).
	 * @param   string   $integrationType  the integration allowed with the ticketing system
	 *
	 * @return  JTicketingEvent  The event object.
	 *
	 * @since   2.5.0
	 */
	public static function getInstance($id = 0, $integrationType = null)
	{
		if (!$id)
		{
			// Always return the native event object
			return new JTicketingEventJticketing($id);
		}

		if (empty(self::$eventObj[$id]))
		{
			if (is_null($integrationType))
			{
				$integrationType = JT::getIntegration();
			}

			$integrationType = StringHelper::substr($integrationType, StringHelper::strpos($integrationType, "_") + 1);

			$eventClass = 'JTicketingEvent' . StringHelper::ucfirst($integrationType);

			if (method_exists($eventClass, 'loadInstance'))
			{
				self::$eventObj[$id] = call_user_func($eventClass . '::loadInstance', $id);
			}
			else
			{
				self::$eventObj[$id] = new $eventClass($id);
			}
		}

		return self::$eventObj[$id];
	}

	/**
	 * Method to load a event properties
	 *
	 * @param   int     $id               The event id
	 * @param   string  $integrationType  the integration allowed with the ticketing system
	 *
	 * @return  boolean  True on success
	 *
	 * @since   2.5.0
	 */
	public function loadEvent($id, $integrationType)
	{
		$table = JT::table("integrationxref");

		if ($table->load(['eventid' => $id, 'source' => $integrationType]))
		{
			$this->integrationId = (int) $table->get('id');
			$this->vendor_id = (int) $table->get('vendor_id');
			$this->eventid = (int) $table->get('eventid');
			$this->source = $table->get('source');
			$this->paypal_email = $table->get('paypal_email');
			$this->checkin = (int) $table->get('checkin');
			$this->userid = (int) $table->get('userid');
			$this->enable_ticket = (int) $table->get('enable_ticket');
			$this->cron_status = $table->get('cron_status');
			$this->cron_date = $table->get('cron_date');

			return true;
		}

		return false;
	}

	/**
	 * Method to load a integration xref properties
	 *
	 * @param   int  $id  The integration id
	 *
	 * @return  JTicketingEvent  Object on success
	 *
	 * @since   2.5.0
	 */
	public function loadByIntegration($id)
	{
		$table = JT::table("integrationxref");

		if ($table->load(array('id' => $id)))
		{
			return self::getInstance($table->eventid, $table->source);
		}

		return self::getInstance();
	}

	/**
	 * Return the formatted price of the event based on the ticket max and min price
	 *
	 * @return  string
	 *
	 * @since   2.5.0
	 */
	public function getPriceText()
	{
		$eventMaxPrice = $this->getMaximumPrice();
		$eventMinPrice = $this->getMinimumPrice();
		$utilities = JT::utilities();

		if (($eventMaxPrice == $eventMinPrice) && (($eventMaxPrice == 0) && ($eventMinPrice == 0)))
		{
			return StringHelper::strtoupper(Text::_('COM_JTICKETING_ONLY_FREE_TICKET_TYPE'));
		}
		elseif (($eventMaxPrice == $eventMinPrice) && (($eventMaxPrice != 0) && ($eventMinPrice != 0)))
		{
			return $utilities->getFormattedPrice(($eventMaxPrice));
		}
		elseif (($eventMaxPrice) && ($eventMinPrice == - 1))
		{
			return StringHelper::strtoupper(Text::_('COM_JTICKETING_HOUSEFULL_TICKET_TYPE'));
		}
		else
		{
			// @TODO this must come from the language constant
			return $utilities->getFormattedPrice(($eventMinPrice)) . ' - ' . $utilities->getFormattedPrice(($eventMaxPrice));
		}
	}

	/**
	 * Return the maximum price of the event based on the ticket price
	 *
	 * @return  string
	 *
	 * @since   _DEPLOY_VERSION_
	 */
	public function getMaximumPrice()
	{
		$maximumTicketPrice = 0;
		$tickets = $this->getTicketTypes();

		if (count($tickets) == 1)
		{
			$maximumTicketPrice = $tickets[0]->price;
		}
		else if(count($tickets) != 0)
		{
			usort(
				$tickets,
				function($ticket1, $ticket2)
				{
					return $ticket1->price > $ticket2->price;
				}
			);

			$maximumTicketPrice  = end($tickets)->price;
		}

		return $maximumTicketPrice;
	}

	/**
	 * Return the minimum price of the event based on the ticket price
	 *
	 * @return  string
	 *
	 * @since   _DEPLOY_VERSION_
	 */
	public function getMinimumPrice()
	{
		$minimumTicketPrice = 0;
		$tickets = $this->getTicketTypes();

		if (count($tickets) == 1)
		{
			$minimumTicketPrice = $tickets[0]->price;
		}
		else if(count($tickets) != 0)
		{
			usort(
				$tickets,
				function($ticket1, $ticket2)
				{
					return $ticket1->price > $ticket2->price;
				}
			);

			$minimumTicketPrice  = reset($tickets)->price;
		}

		return $minimumTicketPrice;
	}

	/**
	 * Determine if the user have any completed order against the event
	 *
	 * @param   integer  $userId  The joomla user id
	 *
	 * @return  integer|boolean
	 *
	 * @since   2.5.0
	 */
	public function isBought($userId = null)
	{
		if (is_null($userId))
		{
			$userId = Factory::getUser()->id;
		}

		if (!$userId)
		{
			return false;
		}

		if ($this->isBought == null && $this->integrationId)
		{
			/** @var $attendeeModel JticketingModelAttendees */
			$attendeeModel = JT::model('attendees', array("ignore_request" => true));
			$this->isBought = count(
							$attendeeModel->getAttendees(
							array('event_id' => $this->integrationId,
								'owner_id' => $userId,
								'status' => COM_JTICKETING_CONSTANT_ATTENDEE_STATUS_APPROVED
							)
						)
					);
		}

		return $this->isBought;
	}

	/**
	 * Method to load the available ticket types of the event
	 *
	 * @return  array  The list of the ticket types
	 *
	 * @since   2.5.0
	 */
	public function getTicketTypes($allTicketTypes = false)
	{
		if (empty($this->ticketTypes) && $this->integrationId)
		{
			$access = (implode("','", array_unique(Access::getAuthorisedViewLevels(Factory::getUser()->id))));

			/** @var $ticketTypesModel JticketingModelTickettypes */
			$ticketTypesModel = JT::model('tickettypes', array("ignore_request" => true));
			$ticketTypesModel->setState('filter.eventid', $this->integrationId);
			$ticketTypesModel->setState('filter.access', $access);

			if (!$allTicketTypes)
			{
				$ticketTypesModel->setState('filter.state', '1');
				$ticketTypesModel->setState('filter.available', '1');
				$ticketTypesModel->setState('filter.ticket_enddate', Factory::getDate('now', 'UTC'));
				$ticketTypesModel->setState('filter.ticket_startdate', Factory::getDate('now', 'UTC'));
			}
			else 
			{
				$ticketTypesModel->setState('filter.state', '');
				$ticketTypesModel->setState('filter.available', '');
				$ticketTypesModel->setState('filter.ticket_enddate', '');
				$ticketTypesModel->setState('filter.ticket_startdate', '');
			}
			$this->ticketTypes = $ticketTypesModel->getItems();
		}

		return $this->ticketTypes;
	}

	/**
	 * Determine if the user is allowed to buy this event
	 *
	 * @param   integer  $userId  The joomla user id
	 *
	 * @return  boolean
	 *
	 * @since   2.5.0
	 */
	public function isAllowedToBuy($userId = null)
	{
		// Check if the event is end
		if ($this->isOver() || empty($this->getTicketTypes()) || $this->isBookingEnd() || ! $this->isBookingStarted())
		{
			return false;
		}

		if (is_null($userId))
		{
			$userId = Factory::getUser()->id;
		}

		// Check if the per ticket limit is applicable
		return !$this->isBuyingLimitExceed($userId);
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
		$eventEndDate = Factory::getDate($this->getEndDate(), 'UTC')->toUnix();

		return $eventEndDate < $currentDate;
	}

	/**
	 * Determine if the buying limit exceed by the user
	 *
	 * @param   integer  $userId  The joomla user id
	 *
	 * @return  boolean return true if the buying limit exceed
	 *
	 * @since   2.5.0
	 */
	public function isBuyingLimitExceed($userId = null)
	{
		if (is_null($userId))
		{
			$userId = Factory::getUser()->id;
		}

		// Check the config if single ticket is enabled
		$singleTicketEnabled = JT::config()->get('single_ticket_per_user', 0);

		if ($singleTicketEnabled || $this->isOnline())
		{
			// Check if event is already bought
			return $this->isBought($userId) >= 1;
		}

		return false;
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
		// For integrated events we need to check the booking end time based on the ticket end date
		$ticketTypes = $this->getTicketTypes();
		$currentDate = Factory::getDate()->toUnix();

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
		// There is no parameter available to check booking is started or not for integration
		return true;
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
		return false;
	}

	/**
	 * This method will check whether the user is creator or not
	 *
	 * @param   integer  $userId  The Joomla user id
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

		return (int)$this->getCreator() === (int) $userId;
	}

	/**
	 * Determine if the event is repetitive
	 *
	 * @return  boolean return true if the event is repetitive
	 *
	 * @since   2.5.0
	 */
	public function isrepeat()
	{
		return false;
	}

	/**
	 * Check if there is tickets presents against the event
	 * If the tickets are present against the event (including free) then the event is paid
	 * If the tickets are not present against the event(mostly integrations) then it indicates that the event is not paid
	 *
	 * @return  boolean return true if the event is paid
	 *
	 * @since   2.5.0
	 */
	public function isPaid()
	{
		if ($this->isPaid == null && $this->integrationId)
		{
			/** @var $ticketTypesModel JticketingModelTickettypes */
			$ticketTypesModel = JT::model('tickettypes', array("ignore_request" => true));
			$ticketTypesModel->setState('filter.eventid', $this->integrationId);
			$ticketTypesModel->setState('filter.state', '1');
			$ticketTypesModel->setState('list.limit', '1');
			$this->isPaid = !empty($ticketTypesModel->getItems());
		}

		return $this->isPaid;
	}

	/**
	 * Fetch vendor details against event
	 *
	 * @return  object  Vendor details
	 *
	 * @since   2.5.0
	 */
	public function getVendorDetails()
	{
		BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_tjvendors/models');
		$tjvendorsModelVendor = BaseDatabaseModel::getInstance('vendor', 'TjvendorsModel');

		return $tjvendorsModelVendor->getDetailsByVendorId($this->vendor_id);
	}

	/**
	 * Determine if the user have pending enrolment againt him
	 *
	 * @param   integer  $userId  The joomla user id
	 *
	 * @return  bool
	 *
	 * @since   2.5.0
	 */
	public function isEnrollmentPending($userId = null)
	{
		if ($this->isPending == null && $this->integrationId)
		{
			if (is_null($userId))
			{
				$userId = Factory::getUser()->id;
			}

			/** @var $attendeeModel JticketingModelAttendees */

			$attendeeModel = JT::model('attendees', array("ignore_request" => true));
			$this->isPending = !empty($attendeeModel->getAttendees(
				array('event_id' => $this->integrationId,
					'owner_id' => $userId,
					'status' => COM_JTICKETING_CONSTANT_ATTENDEE_STATUS_PENDING,
					'limit' => '1'
					)
				));
		}

		return $this->isPending;
	}

	/**
	 * Determine if the user enrolment is Cancelled
	 *
	 * @param   integer  $userId  The joomla user id
	 *
	 * @return  bool
	 *
	 * @since   2.5.0
	 */
	public function isEnrollmentCanceled($userId = null)
	{
		if ($this->isCancelled == null && $this->integrationId)
		{
			if (is_null($userId))
			{
				$userId = Factory::getUser()->id;
			}

			/** @var $attendeeModel JticketingModelAttendees */

			$attendeeModel = JT::model('attendees', array("ignore_request" => true));

			$this->isCancelled = !empty($attendeeModel->getAttendees(
				array('event_id' => $this->integrationId,
					'owner_id' => $userId,
					'status' => COM_JTICKETING_CONSTANT_ATTENDEE_STATUS_REJECTED,
					'limit' => '1'
				)
			));
		}

		return $this->isCancelled;
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
	 * Return the online provider event id
	 *
	 * @return  string
	 *
	 * @since   3.0.0
	 */
	public function getOnlineEventId()
	{
		return '';
	}

	/**
	 * Return the integration id of the event
	 *
	 * @return  int
	 *
	 * @since   3.0.0
	 */
	public function getIntegrationId()
	{
		return $this->integrationId;
	}

	/**
	 * Get sold tickets count against event
	 *
	 * @return  integer  sold ticket count
	 *
	 * @since   3.2.0
	 */
	public function soldTicketCount()
	{
		$attendeeModel = JT::model('attendees', array("ignore_request" => true));
		$attendeeModel->setState('filter.events', $this->integrationId);
		$attendeeModel->setState('filter.status', 'A');

		return $attendeeModel->getTotal();
	}

	/**
	 * Method to get Remaining seat count.
	 *
	 * @return  mixed
	 *
	 * @since   3.2.0
	 */
	public function getTicketCount()
	{
		$tickets     = $this->getTicketTypes();
		$ticketCount = 0;

		foreach ($tickets as $ticket)
		{
			$ticketObj = JT::ticketType($ticket->id);

			if ($ticketObj->unlimited_seats)
			{
				return Text::_('COM_JTICKETING_UNLIMITED_SEATS');
			}

			$ticketCount += (int) $ticketObj->available;
		}

		return $ticketCount;
	}

	/**
	 * This is used get custom field types
	 *
	 * @param   int  $fieldType  be it attendee fields or ticket types
	 *
	 * @return  customFields
	 *
	 * @since   3.2.0
	 */
	public function getCustomFieldTypes($fieldType)
	{
		$source = empty($this->integrationId) ? JT::getIntegration() : $this->source;

		if ($fieldType == "attendeeFields")
		{
			$customFields = JT::integration()->generateCustomFieldHtml('attendeefields', $this->eventid, $source);
		}
		else
		{
			$customFields = JT::integration()->generateCustomFieldHtml('ticket_types', $this->eventid, $source);
		}

		return $customFields;
	}

	/**
	 * This is used get book ticket button html for easysocial overrride
	 *
	 * @return  string
	 *
	 * @since   3.3.3
	 */
	public function getESBuyButtonHTML()
	{
		$redirectionUrl         = $this->getUrl();
		$eventId                = $this->getId();
		$showbook               = $this->isAllowedToBuy();
		$config        		    = JT::config();
		$enableWaitingList 	    = $config->get('enable_waiting_list');
		$integration       	    = $config->get('integration');
		$user                   = Factory::getUser();
		$userId                 = $user->id;
		$isboughtEvent          = $this->isBought($userId);
		$enableSelfEnrollment   = $config->get('enable_self_enrollment', '0', 'INT');

		if ((empty($this->getTicketTypes()) && $enableWaitingList == 'none' && empty($this->isOver())) || empty($this->isTicketingEnabled()))
		{
			return '<a href="#" class="btn btn-es-primary-o btn-smt">' . Text::_('COM_JTICKETING_EVENTS_UNAUTHORISED') . '</a>';
		}

		if ($enableSelfEnrollment && $user->authorise('core.enroll', 'com_jticketing.event.' . $eventId) == '1' && $userId)
		{
			if ($this->isEnrollmentCanceled($userId))
			{
				// return Enrollment cancel button
				return "<a href='#'  class='btn btn-info disabled w-100 booking-btn'>" . Text::_('COM_JTICKETING_EVENTS_ENROLL_CANCEL_BTN') . "</a>";
			}

			if ($this->isEnrollmentPending($userId))
			{
				// return enrollment pending button
				return "<a href='#' class='btn btn-info disabled w-100 booking-btn'>"
					. Text::_('COM_JTICKETING_EVENTS_ENROLL_PENDING_BUTTON') . "</a>";
			}

			if ($isboughtEvent || $this->getCreator() == $userId)
			{
				return "<a href='#' class='btn btn-info disabled w-100 booking-btn'>" . Text::_('COM_JTICKETING_EVENTS_ENROLLED_BTN') . "</a>";
			}

			if ($showbook)
			{
				$itemId = Factory::getApplication()->getInput()->get('Itemid');
				$redirect = '';

				if (!empty($redirectionUrl))
				{
					$redirectionUrl = base64_encode($redirectionUrl);
					$redirect = '&redirectUrl=' . $redirectionUrl;
				}

				$enrollTicketLink = Route::_('index.php?option=com_jticketing&task=enrollment.save&selected_events=' . $eventId .
							'&cid=' . $userId .
							'&Itemid=' . $itemId . '&notify_user_enroll=1' . $redirect, false
					);

				$enrollTicketLink .= '&' . Session::getFormToken() . '=1';

				return "<a href='" . $enrollTicketLink . "' class='btn
							btn-default btn-success com_jt_book com_jticketing_button w-100 booking-btn'>" . Text::_('COM_JTICKETING_ENROLL_BUTTON') . "</a>";
			}
		}

		if ($showbook)
		{
			// return buy button link
			$bookTicketLink = Route::_('index.php?option=com_jticketing&task=order.addOrder&eventId=' . $eventId, false);

			$bookTicketLink .= '&' . Session::getFormToken() . '=1';

			$buyButton = "<a href='" . $bookTicketLink . "' class='btn btn-info enable w-100 booking-btn'
			data-loading-text='<i class=fa fa-spinner fa-spin></i>Loading...''>" . Text::_('COM_JTICKETING_BUY_BUTTON') . "</a>";

			if (!empty($isboughtEvent))
			{
				$classvisibleXS = (JTICKETING_LOAD_BOOTSTRAP_VERSION == 'bs3') ? 'visible-xs' : 'd-block d-sm-none';

				return '<div class = "' . $classvisibleXS . '">
							<div class="info">
								<p>' . Text::_("COM_JTICKETING_ONLINE_EVENT_ALREADY_BOUGHT") . '</p>
					</div> ' . $buyButton . '
				</div>
				<div class="hidden-xs">
					<span class="tool-tip" data-toggle="tooltip" data-placement="top" title="' . Text::_('COM_JTICKETING_ONLINE_EVENT_ALREADY_BOUGHT') . '">' . $buyButton . '
					</span>
				</div>';
			}
			else
			{
				return $buyButton;
			}
		}

		// Single ticket is purchased Display view ticket button
		if ($this->isBuyingLimitExceed($userId) && !empty($userId) && empty($this->isOver()))
		{
			$attendeesModel = JT::model('attendees');
			$attendees = $attendeesModel->getAttendees(
					array('event_id' => $this->integrationId,
					'owner_id' => $userId,
					'status' => COM_JTICKETING_CONSTANT_ATTENDEE_STATUS_APPROVED)
				);

			$viewTicketLink = Route::_('index.php?option=com_jticketing&view=mytickets&tmpl=component&layout=ticketprint&attendee_id='
			. $attendees['0']->id, false
			);

			$modalConfig = array('width' => '800px', 'height' => '300px', 'modalWidth' => 80, 'bodyHeight' => 70);
			$modalConfig['url'] = $viewTicketLink;
			$modalConfig['title'] = Text::_('COM_JTICKETING_VIEW_TICKET_BUTTON');
			$jtViewTicketBtnHTML =  HTMLHelper::_('bootstrap.renderModal', 'jtViewTicketBtn'. $attendees['0']->id, $modalConfig);

			// Joomla 6: JVERSION check removed
		if (false) // Legacy < '4.0.0')
			{
				return $jtViewTicketBtnHTML . "<a data-target='#jtViewTicketBtn" . $attendees['0']->id . "' data-toggle='modal' class='af-relative af-d-block btn btn-default btn-info' title='"
				. Text::_('COM_JTICKETING_VIEW_TICKET_BUTTON_TOOLTIP') . "'>"
				. Text::_('COM_JTICKETING_VIEW_TICKET_BUTTON') . "</a>";
			}
			else
			{
				return $jtViewTicketBtnHTML . "<a data-bs-target='#jtViewTicketBtn" . $attendees['0']->id . "' data-bs-toggle='modal' class='af-relative af-d-block btn btn-default btn-info' title='"
				. Text::_('COM_JTICKETING_VIEW_TICKET_BUTTON_TOOLTIP') . "'>"
				. Text::_('COM_JTICKETING_VIEW_TICKET_BUTTON') . "</a>";
			}
		}

		// If booking date is not closed and waiting list is there
		if ($this->isBookingStarted() && empty($this->isOver()) && $enableWaitingList != 'none')
		{
			$waitlistFormModel = JT::model('waitlistform');
			$isAdded = $waitlistFormModel->isAlreadyAddedToWaitlist($eventId, $userId);

			if (!empty($isAdded))
			{
				// return waiting list button
				return "<a href='#' class='btn btn-info disabled w-100 booking-btn'>" . Text::_('COM_JTICKETING_EVENTS_WAITLISTED_BTN') . "</a>";				
			}

			$redirect = '';

			if (!empty($redirectionUrl))
			{
				$redirectionUrl = base64_encode($redirectionUrl);
				$redirect = '&redirectUrl=' . $redirectionUrl;
			}

			$waitinglistLink = Route::_('index.php?option=com_jticketing&task=waitlistform.save&eventid=' . $eventId . '&userid=' . $userId . $redirect, false);
			$waitinglistLink .= '&id=0&' . Session::getFormToken() . '=1';

			// return waiting list button
			return "<a title='" . Text::_('COM_JTICKETING_WAITINGLIST_BUTTON_DESC') . "' href=" . $waitinglistLink . "
			class='btn  btn-default btn-info w-100 booking-btn'>"
			. Text::_('COM_JTICKETING_WAITINGLIST_BUTTON') . "</a>";

			
		}

		// If event is end
		if ($this->isOver())
		{
			return '<a href="#" class="btn disabled btn-danger w-100 booking-btn">' . Text::_("COM_JTICKETING_EVENTS_BOOKING_BTN_CLOSED") . '</a>';
		}

		// Event booking is not yet started
		if (empty($this->isBookingStarted()))
		{
			// return booking not started yet
			return '<a href="#" class="btn disabled btn-danger w-100 booking-btn">' . Text::_('COM_JTICKETING_EVENTS_BOOKING_BTN_NOT_STARTED') . '
								</a>';
		}

		// If event booking end
		if ($this->isBookingEnd())
		{
			return '<a href="#" class="btn disabled btn-danger w-100 booking-btn">' . Text::_("COM_JTICKETING_EVENTS_BOOKING_BTN_CLOSED") . '</a>';
		}
	}

	/**
	 * This is used check if ticketing is enabled for the event to purchase
	 *
	 * @return  int
	 *
	 * @since   3.3.4
	 */
	public function isTicketingEnabled()
	{
		return !empty($this->enable_ticket) ? $this->enable_ticket : 0;
	}

	/**
	 * This is used check if user respective vendor create permission available
	 *
	 * @return  int
	 *
	 * @since   3.3.4
	 */
	public function checkVendorPermission($userId = null)
	{
		$user     = Factory::getUser($userId);

		if (!$user->authorise('core.admin'))
		{
			if (!$user->authorise('core.create', 'com_tjvendors')) 
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Method to load the available ticket types of the event
	 *
	 * @return  array  The list of the ticket types
	 *
	 * @since   2.5.0
	 */
	public function getAllTicketTypes()
	{
		if (empty($this->allTicketTypes) && $this->integrationId)
		{
			$access = (implode("','", array_unique(Access::getAuthorisedViewLevels(Factory::getUser()->id))));

			/** @var $ticketTypesModel JticketingModelTickettypes */
			$ticketTypesModel = JT::model('tickettypes', array("ignore_request" => true));
			$ticketTypesModel->setState('filter.eventid', $this->integrationId);
			$ticketTypesModel->setState('filter.access', $access);

			$ticketTypesModel->setState('filter.state', '');
			$ticketTypesModel->setState('filter.available', '');
			$ticketTypesModel->setState('filter.ticket_enddate', '');
			$ticketTypesModel->setState('filter.ticket_startdate', '');
			$this->allTicketTypes = $ticketTypesModel->getItems();
		}

		return $this->allTicketTypes;
	}
}
