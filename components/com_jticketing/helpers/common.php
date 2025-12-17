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
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\Filesystem\File;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

// Joomla 6: JLoader removed - use require_once
$routeHelperPath = JPATH_SITE . '/components/com_jticketing/helpers/route.php';
if (file_exists($routeHelperPath))
{
	require_once $routeHelperPath;
}

$orderPath = JPATH_SITE . '/components/com_jticketing/events/order.php';
if (file_exists($orderPath))
{
	require_once $orderPath;
}

$tickettypePath = JPATH_SITE . '/components/com_jticketing/models/tickettype.php';
if (file_exists($tickettypePath))
{
	require_once $tickettypePath;
}

$mainHelperPath = JPATH_SITE . '/components/com_jticketing/helpers/main.php';
if (file_exists($mainHelperPath))
{
	require_once $mainHelperPath;
}

$enrollmentPath = JPATH_SITE . '/components/com_jticketing/models/enrollment.php';
if (file_exists($enrollmentPath))
{
	require_once $enrollmentPath;
}

/**
 * common helper class
 *
 * @package     JTicketing
 * @subpackage  component
 * @since       1.0
 */
class JticketingCommonHelper
{
	public $error, $integration, $jticketingmainhelper, $JTRouteHelper, $jtTriggerOrder;

	/**
	 * Constructor
	 *
	 * @since   1.0
	 */
	public function __construct()
	{
		$com_params  = ComponentHelper::getParams('com_jticketing');
		$this->integration = $com_params->get('integration');
		$this->jticketingmainhelper = new jticketingmainhelper;
		$this->JTRouteHelper = new JTRouteHelper;
		$this->jtTriggerOrder = new JticketingTriggerOrder;
	}

	/**
	 * Get Event xref id
	 *
	 * @param   integer  $eventId  eventId
	 *
	 * @return  mixed
	 *
	 * @since   1.0
	 *
	 * @deprecated 3.2.0 The method is deprecated and will be removed in the next version.
	 * Use JT::event($eventId, $integration)->integrationId; instead.
	 */
	public function getEventIntegXrefId($eventId)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		if (!empty($eventId))
		{
			$query->select($db->quoteName(array('id')));
			$query->from($db->quoteName('#__jticketing_integration_xref'));
			$query->where($db->quoteName('eventid') . ' = ' . $db->quote($eventId));

			if ($this->integration == 1)
			{
				$query->where($db->quoteName('source') . ' = ' . $db->quote('com_community'));
			}
			elseif ($this->integration == 2)
			{
				$query->where($db->quoteName('source') . ' = ' . $db->quote('com_jticketing'));
			}
			elseif ($this->integration == 3)
			{
				$query->where($db->quoteName('source') . ' = ' . $db->quote('com_jevents'));
			}
			elseif ($this->integration == 4)
			{
				$query->where($db->quoteName('source') . ' = ' . $db->quote('com_easysocial'));
			}

			$db->setQuery($query);

			return $eventXrefId = $db->loadResult();
		}

		return;
	}

	/**
	 * Method to get event vendor
	 *
	 * @param   int  $eventId  event id from all event related tables for example for jomsocial pass jomsocial's event id
	 *
	 * @return  Object  vendor id
	 *
	 * @deprecated 3.2.0 The method is deprecated and will be removed in the next version.
	 */
	public function getEventVendor($eventId)
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true);

		if (empty($eventId))
		{
			return;
		}

		if ($this->integration == 1)
		{
			$source = 'com_community';
		}
		elseif ($this->integration == 2)
		{
			$source = 'com_jticketing';
		}
		elseif ($this->integration == 3)
		{
			$source = 'com_jevents';
		}
		elseif ($this->integration == 4)
		{
			$source = 'com_easysocial';
		}

		$query->select($db->quoteName(array('vendor_id')));
		$query->from($db->quoteName('#__jticketing_integration_xref'));
		$query->where($db->quoteName('source') . ' = ' . $db->quote($source));
		$query->where($db->quoteName('eventid') . ' = ' . $db->quote($eventId));
		$db->setQuery($query);
		$eventVendor = $db->loadObject();

		return $eventVendor;
	}

	/**
	 * Get layout html
	 *
	 * @param   string  $viewName       name of view
	 * @param   string  $layout         layout of view
	 * @param   string  $searchTmpPath  site/admin template
	 * @param   string  $useViewpath    site/admin view
	 *
	 * @return  [type]                  description
	 *
	 * @deprecated 3.2.0 Use JT::utilities()->getViewpath($viewName, $layout, $searchTmpPath, $useViewpath); instead.
	 */
	public function getViewPath($viewName, $layout = "", $searchTmpPath = 'SITE', $useViewpath = 'SITE')
	{
		$searchTmpPath = ($searchTmpPath == 'SITE') ? JPATH_SITE : JPATH_ADMINISTRATOR;
		$useViewpath   = ($useViewpath == 'SITE') ? JPATH_SITE : JPATH_ADMINISTRATOR;
		$app           = Factory::getApplication();

		if (!empty($layout))
		{
			$layoutName = $layout . '.php';
		}
		else
		{
			$layoutName = "default.php";
		}

		// Get templates from override folder

		if ($searchTmpPath == JPATH_SITE)
		{
			$defTemplate = JT::utilities()->getSiteDefaultTemplate(0);
		}
		else
		{
			$defTemplate = JT::utilities()->getSiteDefaultTemplate(0);
		}

		$override = $searchTmpPath . '/templates/' . $defTemplate . '/html/com_jticketing/' . $viewName . '/' . $layoutName;

		if (File::exists($override))
		{
			return $view = $override;
		}
		else
		{
			return $view = $useViewpath . '/components/com_jticketing/views/' . $viewName . '/tmpl/' . $layoutName;
		}
	}

	/**
	 * Get sites/administrator default template
	 *
	 * @param   mixed  $client  0 for site and 1 for admin template
	 *
	 * @return  json
	 *
	 * @since   1.5
	 *
	 * @deprecated 3.2.0 Use JT::utilities()->getSiteDefaultTemplate($client); instead.
	 */
	public function getSiteDefaultTemplate($client = 0)
	{
		try
		{
			$db = Factory::getDbo();

			// Get current status for Unset previous template from being default
			// For front end => client_id=0
			$query = $db->getQuery(true)->select('template')->from($db->quoteName('#__template_styles'))->where('client_id=' . $client)->where('home=1');
			$db->setQuery($query);

			return $db->loadResult();
		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());

			return '';
		}
	}

	/**
	 * Function to create free ticket
	 *
	 * @param   integer  $userID   userID
	 * @param   integer  $orderID  orderID
	 * @param   integer  $flag     While using CreateOrder API, we set flag to 1
	 *
	 * @return  string
	 *
	 * @since   1.0
	 *
	 * @deprecated 3.2.0 The method is deprecated and will be removed in the next version.
	 */
	public function createFreeTicket($userID, $orderID, $flag = 0)
	{
		$com_params  = ComponentHelper::getParams('com_jticketing');
		$order = $this->jticketingmainhelper->getorderinfo($orderID);

		JLoader::register('JticketingMailHelper', JPATH_SITE . '/components/com_jticketing/helpers/mail.php');

		// If free ticket then confirm automatically and redirect to Invoice View.
		if ($order['order_info']['0']->amount == 0 || $flag == 1)
		{
			$input = Factory::getApplication()->getInput();
			$confirmOrder = array();
			$confirmOrder['buyer_email']    = '';
			$confirmOrder['status']         = 'C';
			$confirmOrder['processor']      = "Free_ticket";
			$confirmOrder['transaction_id'] = "";
			$confirmOrder['raw_data']       = "";
			$paymentHelper = JPATH_ROOT . '/components/com_jticketing/models/payment.php';

			if (!class_exists('jticketingModelpayment'))
			{
				JLoader::register('jticketingModelpayment', $paymentHelper);
				JLoader::load('jticketingModelpayment');
			}

			$jticketingModelpayment = new jticketingModelpayment;
			$jticketingModelpayment->updatesales($confirmOrder, $orderID);

			// Match JomSocial attendees when user buy a ticket
			$member_id = $jticketingModelpayment->getEventMemberid($orderID, 'C');
			$jticketingModelpayment->eventupdate($orderID, $member_id);

			if (!empty($userID))
			{
				// TODO insertion
				$eventData                     = array();
				$eventData['eventId']          = $order['eventinfo']->getId();
				$eventData['eventTitle']       = $order['eventinfo']->getTitle();
				$eventData['startDate']        = $order['eventinfo']->getStartdate();
				$eventData['endDate']          = $order['eventinfo']->getEndDate();
				$eventData['action']           = '';
				$eventData['assigned_to']      = $userID;
				$eventData['notify']           = false;

				$order['order_info']['0']->status = 'C';
				$this->jtTriggerOrder->onOrderStatusChange($order, $eventData);
			}

			$guestEmail = '';

			// Update attendee status to Approve when order get completed
			JT::order($orderID)->updateAttendeeStatus();

			// For Guest user attach email
			if (!$order['order_info']['0']->user_id)
			{
				isset($order['order_info']['0']->user_email) ? $order['order_info']['0']->user_email : $order['order_info']['0']->user_email = '';
				$guestEmail = "&email=" . md5($order['order_info']['0']->user_email);
			}

			$Itemid = JT::utilities()->getItemId('index.php?option=com_jticketing&view=orders');
			$orderIdWithPrefix = $order['order_info']['0']->orderid_with_prefix;
			$redUrl   = "index.php?option=com_jticketing&view=orders&sendmail=1&layout=order&orderid=";
			$redUrl .= $orderIdWithPrefix . "&processor=Free_ticket&Itemid=" . $Itemid . $guestEmail;
			$invoiceUrl = $this->JTRouteHelper->JTRoute($redUrl);

			$eventDetails      = $this->jticketingmainhelper->getticketDetails($order['eventinfo']->id, $order['items']['0']->attendee_id);
			$socialIntegration = $com_params->get('integrate_with', 'none');
			$streamBuyTicket   = $com_params->get('streamBuyTicket', 0);
			$order_id          = $order['order_info']['0']->id;

			// Send Ticket Email.
			if ($this->integration == 2)
			{
				if (!$eventDetails->online_events)
				{
					JticketingMailHelper::sendmailnotify($order_id, 'afterordermail');
				}
			}
			else
			{
				JticketingMailHelper::sendmailnotify($order_id, 'afterordermail');
			}

			if ($socialIntegration != 'none')
			{
				// Add in activity.
				if ($streamBuyTicket == 1 and !empty($userID))
				{
					$jteventHelper = new jteventHelper;
					$libClass    = $jteventHelper->getJticketSocialLibObj();
					$action      = 'streamBuyTicket';
					$eventLink   = '<a class="" href="' . $order['eventinfo']->event_url . '">' . $order['eventinfo']->getTitle() . '</a>';
					$originalMsg = Text::sprintf('COM_JTICKETING_PURCHASED_TICKET', $eventLink);
					$libClass->pushActivity($userID, $actType = '', $actSubtype = '', $originalMsg, $actLink = '', $title = '', $actAccess = 0);
				}
			}

			if ($this->integration == 2)
			{
				$activityData = array();
				$activityData['status'] = 'C';
				$orderId = $order['order_info']['0']->orderid_with_prefix;

				// Trigger After Process Payment
				PluginHelper::importPlugin('system');

				// Old Trigger
				Factory::getApplication()->triggerEvent('onJtAfterProcessPayment', array($activityData, $orderId, $pg_plugin = ''));

				// New Trigger
				$result = Factory::getApplication()->triggerEvent('onAfterJtProcessPayment', array($activityData, $orderId, $pg_plugin = ''));

				// If online event create user on adobe site and register for this event
				if ($eventDetails->online_events == 1)
				{
					if (file_exists(JPATH_SITE . '/components/com_jticketing/models/venueform.php')) { require_once JPATH_SITE . '/components/com_jticketing/models/venueform.php'; }
					$venueModel = BaseDatabaseModel::getInstance('VenueForm', 'JticketingModel');
					$venueDetail = $venueModel->getItem($eventDetails->venue);
					$eventParams = json_decode($eventDetails->params, true);
					$venueParams = $venueDetail->params;
					$jtParams = new stdClass;
					$utilities = JT::utilities();

					$enrollUser = Factory::getUser($userID);
					$jtParams->user_id  = $userID;
					$jtParams->name     = $enrollUser->name;
					$jtParams->email    = $enrollUser->email;
					$jtParams->password = $utilities->generateRandomString(8);
					$jtParams->meeting_url = $eventParams['event_url'];
					$jtParams->api_username = $venueParams['api_username'];
					$jtParams->api_password = $venueParams['api_password'];
					$jtParams->host_url = $venueParams['host_url'];
					$jtParams->sco_id = $eventParams['event_sco_id'];

					$event    = JT::event($order['eventinfo']->id);
					$attendee = JT::attendee($order['items']['0']->attendee_id);

					if (!$event->addAttendee($attendee))
					{
						$this->setError($event->getError());

						return false;
					}

					JticketingMailHelper::onlineEventNotify($order_id, $jtParams, $order['eventinfo']);
				}
			}

			return $invoiceUrl;
		}
	}

	/**
	 * Get all Text for javascript
	 *
	 * @return   void
	 *
	 * @since   1.0
	 */
	public static function getLanguageConstant()
	{
		$params = ComponentHelper::getParams('com_jticketing');
		$mediaSize = $params->get('jticketing_media_size', '15');
		$purchaseLimit = $params->get('max_noticket_peruserperpurchase', '8');

		// For venue valiation
		Text::script('COM_JTICKETING_INVALID_FIELD');
		Text::script('COM_JTICKETING_ONLINE_EVENTS_PROVIDER');
		Text::script('COM_JTICKETING_FORM_LBL_VENUE_ADDRESS');
		Text::script('COM_JTICKETING_VENUE_FORM_ADDRESS_FILED');
		Text::script('COM_JTICKETING_VENUE_FORM_ONLINE_PROVIDER');
		Text::script('JT_TICKET_BOOKING_ID_VALIDATION');

		// Event
		Text::script('JT_EVENT_COUNTER_STARTS_IN_DAYS');
		Text::script('JT_EVENT_COUNTER_STARTS_IN_TIME');
		Text::script('JT_EVENT_COUNTER_ENDS_IN_DAYS');
		Text::script('JT_EVENT_COUNTER_ENDS_IN_TIME');
		Text::script('JT_EVENT_COUNTER_EXPIRE');
		Text::script('COM_JTICKETING_NO_RECORDING_FOUND');
		Text::script('COM_JTICKETING_RECORDING_NAME');
		Text::script('COM_JTICKETING_FILTERS_MORE_LBL');
		Text::script('COM_JTICKETING_EVENT_RELATED_AJAX_FAIL_ERROR_MESSAGE');
		Text::script('COM_JTICKETING_TICKET_ACCESS_EXCLUDE');
		Text::script('COM_JTICKETING_TICKET_ACCESS_INCLUDE');
		Text::script('COM_JTICKETING_VENUE_VIDEOS');
		Text::script('COM_JTICKETING_TICKET_CAPACITY_ALERT');
		Text::script('COM_JTICKETING_VENUE_CAPACITY_ERROR');
		Text::script('COM_JTICKETING_FILE_TYPE_NOT_ALLOWED');


		// Billing Form validation
		Text::script('COM_JTICKETING_CHECK_SPECIAL_CHARS');
		Text::script('COM_JTICKETING_ENTER_NO_OF_TICKETS');
		Text::script('COM_JTICKETING_FILL_ALL_REQUIRED_FIELDS');
		Text::script('COM_JTICKETING_ACCEPT_TERMS_AND_CONDITIONS');
		Text::script('COM_JTICKETING_PREV');
		Text::script('COM_JTICKETING_BILLIN_SELECT_STATE');
		Text::script('COM_JTICKETING_ENTER_VALID_SEAT_COUNT_FOR_TICKET');

		// Gallary Upload File validation
		Text::sprintf('COM_TJMEDIA_VALIDATE_MEDIA_SIZE', $mediaSize, 'MB', array('script' => true));

		// Move from main.php helper
		Text::script('COM_JTICKETING_SAVE_AND_CLOSE');
		Text::script('COM_JTICKETING_ADDRESS_NOT_FOUND');
		Text::script('COM_JTICKETING_LONG_LAT_VAL');
		Text::script('COM_JTICKETING_CONFIRM_TO_DELETE');
		Text::script('COM_JTICKETING_NUMBER_OF_TICKETS');
		Text::script('ENTER_COP_COD');
		Text::script('COP_EXISTS');
		Text::script('ENTER_LESS_COUNT_ERROR');
		Text::script('COM_JTICKETING_ENTER_NUMERICS');
		Text::script('COM_JTICKETING_ENTER_AMOUNT_GR_ZERO');
		Text::script('COM_JTICKETING_ENTER_AMOUNT_INT');
		Text::script('COM_JTICKETING_MEETING_BUTTON');
		Text::script('COM_JT_MEETING_ACCESS');
		Text::script('COM_JTICKETING_EVENT_FINISHED');
		Text::script('JGLOBAL_CONFIRM_DELETE');
		Text::script('COM_TJMEDIA_VALIDATE_YOUTUBE_URL');
		Text::script('COM_JTICKETING_EVENT_GALLERY_VIDEOS');
		Text::script('COM_JTICKETING_EVENT_GALLERY_IMAGES');
		Text::script('COM_JTICKETING_EMPTY_DESCRIPTION_ERROR');
		Text::script('COM_JTICKETING_INVALID_FIELD');
		Text::script('COM_JTICKETING_FORM_LBL_EVENT_DATE_ERROR');
		Text::script('COM_JTICKETING_FORM_LBL_EVENT_BOOKING_DATE_ERROR');
		Text::script('COM_JTICKETING_FORM_LBL_EVENT_BOOKING_EVENT_END_ERROR');
		Text::script('COM_JTICKETING_CUSTOM_LOCATION');
		Text::script('COM_JTICKETING_NO_VENUE_ERROR_MSG');
		Text::script('COM_JTICKETING_EVENTS_ENTER_MEETING_SITE_POPUPS');
		Text::script('COM_JTICKETING_NO_ONLINE_VENUE_ERROR');
		Text::script('UNLIM_SEATS');
		Text::script('COM_JTICKETING_VALIDATE_CAPTCHA');
		Text::script('COM_JTICKETING_VALIDATE_ROUNDED_PRICE');
		Text::script('COM_JTICKETING_FORM_SELECT_EXISTING_EVENT_OPTION');
		Text::script('COM_JTICKETING_FORM_EVENT_DEFAULT_VENUE_OPTION');

		// Event Detail page Gallery
		Text::script('COM_JTICKETING_GALLERY_VIDEO_TEXT');
		Text::script('COM_JTICKETING_GALLERY_IMAGE_TEXT');
		Text::script('COM_JTICKETING_EVENT_VIDEOS');
		Text::script('COM_JTICKETING_DESCRIPTION_READ_MORE');
		Text::script('COM_JTICKETING_DESCRIPTION_READ_LESS');
		Text::script('COM_JTICKETING_SOMETHING_WENT_WRONG');
		Text::script('COM_JTICKETING_YOUR_TIME');
		Text::script('COM_JTICKETING_FORM_LBL_VENUE_TITLE');

		// Enrollment
		Text::script('COM_JTICKETING_SELECT_USER_TO_ENROLL');
		Text::script('COM_JTICKETING_SELECT_EVENT_TO_ENROLL');
		Text::script('COM_JTICKETING_ARE_YOU_SURE_YOU_TO_DELETE_THE_ATTENDEE');

		// Privacy setting
		Text::script('COM_JTICKETING_PRIVACY_TERMS_AND_CONDITIONS_ERROR');

		// Integration validations
		Text::script('COM_JTICKETING_JOMSOCIAL_EVENT_TICKET_TYPES_SAVE_ERROR');
		Text::script('COM_JTICKETING_EASYSOCIAL_EVENT_TICKET_TYPES_SAVE_ERROR');
		Text::script('COM_JTICKETING_TICKET_TITLE_EMPTY');
		Text::script('COM_JTICKETING_TICKET_SEAT_COUNT_ERROR');
		Text::script('COM_JTICKETING_ENTER_VALID_TICKET_AMOUNT');

		// Order status messages
		Text::script('COM_JTICKETING_ORDER_STATUS_MESSAGE1');
		Text::script('COM_JTICKETING_ORDER_STATUS_REFUND');
		Text::script('COM_JTICKETING_ORDER_STATUS_FAILED');
		Text::script('COM_JTICKETING_ORDER_STATUS_DECLINE');
		Text::script('COM_JTICKETING_ORDER_STATUS_CANCEL_REVERSED');
		Text::script('COM_JTICKETING_ORDER_STATUS_REVERSED');
		Text::script('COM_JTICKETING_ORDER_STATUS_MESSAGE2');
		Text::script('COM_JTICKETING_ORDER_STATUS_CHANGED');
		Text::script('COM_JTICKETING_EMAIL_ALREADY_EXIST');
		Text::script('COM_JTICKETING_ORDER_LOADING');
		Text::script('JT_PERUSER_PER_PURCHASE_LIMIT_ERROR');

		// Ticket start date
		Text::script('COM_JTICKETING_BOOKING_START_DATE_WITH_EVENT_DATE_ERROR');

		// Ticket end date
		Text::script('COM_JTICKETING_TICKET_END_DATE_GREATER_BOOKING_END_DATE_ERROR');
		Text::script('COM_JTICKETING_TICKET_END_DATE_LESS_BOOKING_START_DATE_ERROR');
		Text::script('COM_JTICKETING_TICKET_END_DATE_GREATER_EVENT_END_DATE_ERROR');
		Text::script('COM_JTICKETING_TICKET_END_DATE_LESS_EVENT_MODIFICATION_DATE_ERROR');
		Text::script('COM_JTICKETING_SAVE_THE_EVENT_CHANGED_DATES');
		Text::script('COM_JTICKETING_ENDDATE_GREATER_STARTDATE_VALIDATION');
		Text::script('COM_JTICKETING_STARTDATE_LESS_ENDDATE_VALIDATION');
		Text::script('COM_JTICKETING_STARTDATE_VALIDATION');
		Text::script('COM_JTICKETING_ENDDATE_VALIDATION');

		// Ticket seat count
		Text::script('COM_JTICKETING_INVALID_SEAT_COUNT_ERROR');
		Text::script('COM_JTICKETING_ERROR');

		// Coupon Form validation
		Text::script('COM_JTICKETING_MIN_AMT_SHOULD_GREATER_MSG');
		Text::script('COM_JTICKETING_ARE_YOU_SURE_YOU_TO_DELETE_THE_COUPON');
		Text::script('COM_JTICKETING_ORDER_COUPON_INVALID');
		Text::script('COM_JTICKETING_ORDER_RELATED_AJAX_FAIL_ERROR_MESSAGE');
		Text::script('COM_JTICKETING_COUPON_PERCENTAGE_ERROR');

		// Attendee Form Validation
		Text::script('COM_JTICKETING_INVALID_ATTENDEE_FIRST_NAME');
		Text::script('COM_JTICKETING_INVALID_ATTENDEE_LAST_NAME');
		Text::script('COM_JTICKETING_INVALID_ATTENDEE_MOB');
		Text::script('COM_JTICKETING_INVALID_ATTENDEE_EMAIL');

		// Billing Form Validation
		Text::script('COM_JTICKETING_INVALID_BILLING_MOB');
		Text::script('COM_JTICKETING_INVALID_BILLING_LNAME');
		Text::script('COM_JTICKETING_INVALID_BILLING_FNAME');
		Text::script('COM_JTICKETING_ERROR_REPEAT_UNTIL_GREATER_THAN_STARTDATE');
		Text::script('COM_JTICKETING_FORM_LBL_REPEAT_COUNT_INVALID');
		Text::script('COM_JTICKETING_FORM_LBL_REPEAT_INTERVAL_REQUIRED');
		Text::script('COM_JTICKETING_FORM_LBL_REPEAT_INTERVAL_INVALID');
		Text::script('COM_JTICKETING_FORM_LBL_REPEAT_UNTIL_REQUIRED');
		Text::script('COM_JTICKETING_FORM_LBL_REPEAT_COUNT_REQUIRED');

		// Event Validation
		Text::script('COM_JTICKETING_START_NUMBER_FOR_EVENT_LEVEL_SEQUENCE');
		Text::script('COM_JTICKETING_START_NUMBER_FOR_SEQUENCE');
	}

	/**
	 * Check if the logged in user is a vendor
	 *
	 * @return   mixed
	 *
	 * @since   2.0
	 *
	 * @deprecated 3.2.0 Use loadByUserId() and getId(); of vendor classs instead
	 */
	public static function checkVendor()
	{
		$user_id = jFactory::getuser()->id;
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName('vendor_id'));
		$query->from($db->quoteName('#__tjvendors_vendors'));
		$query->where($db->quoteName('user_id') . ' = ' . $user_id);
		$db->setQuery($query);
		$vendor = $db->loadResult();

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
	 * Validates User Login
	 *
	 * @deprecated  3.0.0 Check the user id directly
	 *
	 * @return  boolean
	 *
	 * @deprecated  3.2.0 Use $user = Factory::getUser(); and verify if user is persent or not
	 */
	public function validateUserLogin()
	{
		$user = Factory::getUser();
		$uid = $user->id;

		if (!$uid)
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	/**
	 * Function to Decrement Ticket Count
	 *
	 * @param   Array  $enrollmentData  A prefix for the store id.
	 *
	 * @return  Boolean
	 *
	 * @since   2.1
	 *
	 * @deprecated 3.2.0 Use JT::tickettype($ticket_type_id)->decreaseAvilableSeats(); instead
	 */
	public function decrementTickets($enrollmentData)
	{
		$jtTicketTypeModel   = new JTicketingModelTickettype;

		// Check for avaible tickets of passed ticket id, enrollmentData['ticket_id'] is ticket type id
		$ticketCountDetails = $jtTicketTypeModel->getItem($enrollmentData['ticket_type_id']);

		if (!($ticketCountDetails->unlimited_seats || $ticketCountDetails->count >= 1))
		{
			$this->setError(Text::_('COM_JTICKETING_ERROR_NOT_ENOUGH_TICKETS'));

			return false;
		}

		// Decrement ticket count when user get enrollment approval
		if ($ticketCountDetails->unlimited_seats != 1)
		{
			$ticketCountData = array();
			$ticketCountData['id'] = $enrollmentData['ticket_type_id'];
			$ticketCountData['count'] = --$ticketCountDetails->count;

			$countDecremented = $jtTicketTypeModel->save($ticketCountData);

			if (!$countDecremented)
			{
				$this->setError(Text::_('COM_JTICKETING_ERROR_SOMETHING_IS_WRONG_TICKET'));

				return false;
			}
		}

		return true;
	}

	/**
	 * Function to Increment Ticket Count
	 *
	 * @param   Array  $data  Enrollment/Order data
	 *
	 * @return  Boolean
	 *
	 * @since   2.1
	 *
	 * @deprecated 3.2.0 Use JT::tickettype($ticket_type_id)->increaseAvailableSeats(); instead
	 */
	public function incrementTickets($data)
	{
		$jtTicketTypeModel = new JTicketingModelTickettype;

		// Check for available tickets of passed ticket id, data['ticket_id'] is ticket type id
		$ticketCountDetails = $jtTicketTypeModel->getItem($data['ticket_type_id']);

		if ((int) $ticketCountDetails->available === (int) $ticketCountDetails->count && $ticketCountDetails->unlimited_seats != 1)
		{
			$this->setError(Text::_('COM_JTICKETING_ERROR_SOMETHING_IS_WRONG_TICKET'));

			return false;
		}

		// Decrement ticket count when user get enrollment approval
		if ($ticketCountDetails->unlimited_seats != 1)
		{
			$ticketCountData = array();
			$ticketCountData['id'] = $data['ticket_type_id'];
			$ticketCountData['count'] = ++$ticketCountDetails->count;

			// Save incremented ticket count
			$countDecremented = $jtTicketTypeModel->save($ticketCountData);

			if (!$countDecremented)
			{
				$this->setError(Text::_('COM_JTICKETING_ERROR_SOMETHING_IS_WRONG_TICKET'));

				return false;
			}
		}

		return true;
	}

	/**
	 * Sets error message.
	 *
	 * @param   string  $error  error message
	 *
	 * @return  Boolean
	 *
	 * @deprecated 3.2.0 The method is deprecated and will be removed in the next version.
	 */
	public function setError($error)
	{
		$this->error = $error;
	}
}
