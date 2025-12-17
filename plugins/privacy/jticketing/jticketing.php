<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2018 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access.
defined('_JEXEC') or die();

use Joomla\CMS\Table\Table;
use Joomla\CMS\Language\Text;
use Joomla\CMS\User\User;
use Joomla\CMS\Table\User as UserTable;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_jticketing/models/');
Table::addIncludePath(JPATH_ROOT . '/administrator/components/com_jticketing/tables');

JLoader::register('PrivacyPlugin', JPATH_ADMINISTRATOR . '/components/com_privacy/helpers/plugin.php');
JLoader::register('PrivacyRemovalStatus', JPATH_ADMINISTRATOR . '/components/com_privacy/helpers/removal/status.php');

/**
 * Privacy plugin managing JTicketing user data
 *
 * @since  2.3.4
 */
class PlgPrivacyJTicketing extends PrivacyPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 *
	 * @since  2.3.4
	 */
	protected $autoloadLanguage = true;

	/**
	 * Database object
	 *
	 * @var    JDatabaseDriver
	 * @since  2.3.4
	 */
	protected $db;

	/**
	 * Reports the privacy related capabilities for this plugin to site administrators.
	 *
	 * @return  array
	 *
	 * @since  2.3.4
	 */
	public function onPrivacyCollectAdminCapabilities()
	{
		$this->loadLanguage();

		return array(
			Text::_('PLG_PRIVACY_JTICKETING') => array(
				Text::_('PLG_PRIVACY_JTICKETING_PRIVACY_CAPABILITY_USER_DETAIL'),
				Text::_('PLG_PRIVACY_JTICKETING_PRIVACY_VENDOR_CAPABILITY_USER_DETAIL'),
				Text::_('PLG_PRIVACY_JTICKETING_PRIVACY_REPORTS_CAPABILITY_USER__DETAIL'),
				Text::_('PLG_PRIVACY_JTICKETING_PRIVACY_JLIKE_CAPABILITY_USER__DETAIL'),
				Text::_('PLG_PRIVACY_JTICKETING_PRIVACY_ACTIVITY_STREAM_CAPABILITY_USER_DETAIL'),
				Text::_('PLG_PRIVACY_JTICKETING_PRIVACY_TJNOTIFICATION_CAPABILITY_USER_DETAIL'),
				Text::_('PLG_PRIVACY_JTICKETING_PRIVACY_HIERARCHY_CAPABILITY_USER_DETAIL')
			)
		);
	}

	/**
	 * Processes an export request for JTicketing user data
	 *
	 * This event will collect data for the following tables:
	 *
	 * - #__jticketing_users
	 *
	 * - #__jticketing_events
	 * - #__jticketing_media_files
	 * - #__jticketing_integration_xref
	 *
	 * - #__jticketing_order
	 * - #__jticketing_order_items
	 *
	 * - #__jticketing_attendees
	 *
	 * - #__jticketing_venues
	 *
	 * - #__jticketing_waiting_list
	 *
	 * - #__jticketing_coupon
	 *
	 * @param   PrivacyTableRequest  $request  The request record being processed
	 * @param   JUser                $user     The user account associated with this request if available
	 *
	 * @return  PrivacyExportDomain[]
	 *
	 * @since  2.3.4
	 */
	public function onPrivacyExportRequest(PrivacyTableRequest $request, User $user = null)
	{
		if (!$user)
		{
			return array();
		}

		/** @var JTableUser $userTable */
		// $userTable = User::getTable();
		// $userTable->load($user->id);
		$userTable = Factory::getUser($user->id);
		$domains = array();

		// Create the domain for getting JTicketing user data
		$domains[] = $this->onCreateJTicketingUserDomain($userTable);

		// Create the domain for the JTicketing Venue data
		// Venue related data stored in #__jticketing_venues table
		$domains[] = $this->onCreateJTicketingVenueDomain($userTable);

		// Create the domain for the JTicketing event data
		// Event related data stored in #__jticketing_events, #__jticketing_media_files, #__jticketing_integration_xref tables
		$domains[] = $this->onCreateJTicketingEventDomain($userTable);

		// Create the domain for the JTicketing order data
		// Order related data stored in #__jticketing_order, #__jticketing_order_items tables
		$domains[] = $this->onCreateJTicketingOrderDomain($userTable);

		// Create the domain for the JTicketing Attendee data
		// Attendee related data stored in #__jticketing_attendees table
		$domains[] = $this->onCreateJTicketingAttendeesDomain($userTable);

		// Create the domain for the JTicketing Waiting List data
		// Venue related data stored in #__jticketing_waiting_list table
		$domains[] = $this->onCreateJTicketingWaitListDomain($userTable);

		// Create the domain for the JTicketing coupon list
		// Coupon related data stored in #__jticketing_coupon table
		$domains[] = $this->onCreateJTicketingCouponsDomain($userTable);

		return $domains;
	}

	/**
	 * Create the domain for the JTicketing user data
	 *
	 * @param   JTableUser  $user  The JTableUser object to process
	 *
	 * @return  PrivacyExportDomain
	 *
	 * @since  2.3.4
	 */
	private function onCreateJTicketingUserDomain(User $user)
	{
		$domain = $this->createDomain('JTicketing billing Info', 'JTicketing user billing data');

		$query = $this->db->getQuery(true)
			->select('id, user_id, order_id, user_email, address_type, firstname, lastname, vat_number, tax_exempt,
				country_code, address, city, state_code, zipcode, phone, approved, country_mobile_code')
			->from($this->db->quoteName('#__jticketing_users'))
			->where($this->db->quoteName('user_id') . ' = ' . (int) $user->id);

		$jticketinguserData = $this->db->setQuery($query)->loadAssocList();

		if (!empty($jticketinguserData))
		{
			foreach ($jticketinguserData as $userData)
			{
				$domain->addItem($this->createItemFromArray($userData, $userData['id']));
			}
		}

		return $domain;
	}

	/**
	 * Create the domain for the JTicketing Venue data
	 *
	 * @param   JTableUser  $user  The JTableUser object to process
	 *
	 * @return  PrivacyExportDomain
	 *
	 * @since  2.3.4
	 */
	public function onCreateJTicketingVenueDomain(User $user)
	{
		$domain = $this->createDomain('JTicketing venue', 'JTicketing user created venue data');

		$query = $this->db->getQuery(true)
			->select(
				'id, vendor_id, asset_id, ordering, state, checked_out, checked_out_time, created_by, modified_by,
				name, alias, venue_category, online, online_provider, country, state_id, city, zipcode, address,
				longitude, latitude, privacy, params'
			)
			->from($this->db->quoteName('#__jticketing_venues'))
			->where($this->db->quoteName('created_by') . ' = ' . (int) $user->id);

		$venueData = $this->db->setQuery($query)->loadAssocList();

		if (!empty($venueData))
		{
			foreach ($venueData as $venue)
			{
				$domain->addItem($this->createItemFromArray($venue, $venue['id']));
			}
		}

		return $domain;
	}

	/**
	 * Create the domain for the JTicketing event data
	 *
	 * @param   JTableUser  $user  The JTableUser object to process
	 *
	 * @return  PrivacyExportDomain
	 *
	 * @since  2.3.4
	 */
	public function onCreateJTicketingEventDomain(User $user)
	{
		$domain = $this->createDomain('JTicketing event ', 'JTicketing user created event data');

		// Event table data
		$query = $this->db->getQuery(true)
			->select(
				'id, created_by, title, alias, catid, ideal_time, venue, short_description, long_description, startdate, enddate,
				booking_start_date, booking_end_date, location, latitude, longitude, permission, image, created, modified, state,
				allow_view_attendee, access, featured, online_events, ordering, checked_out, checked_out_time, jt_params, meta_data,
				meta_desc'
			)
			->from($this->db->quoteName('#__jticketing_events'))
			->where($this->db->quoteName('created_by') . ' = ' . (int) $user->id);

		$eventData = $this->db->setQuery($query)->loadAssocList();

		if (!empty($eventData))
		{
			foreach ($eventData as $event)
			{
				$domain->addItem($this->createItemFromArray($event, $event['id']));
			}
		}

		// JTicketing Media Files Data
		$query = $this->db->getQuery(true)
			->select('id, title, type, path, state, source, original_filename, size, storage, created_by, access, created_date, params')
			->from($this->db->quoteName('#__tj_media_files'))
			->where($this->db->quoteName('created_by') . ' = ' . (int) $user->id);

		$eventMediaData = $this->db->setQuery($query)->loadAssocList();

		if (!empty($eventMediaData))
		{
			foreach ($eventMediaData as $eventMedia)
			{
				$domain->addItem($this->createItemFromArray($eventMedia, $eventMedia['id']));
			}
		}

		// JTicketing_integration_xref Data
		$query = $this->db->getQuery(true)
			->select('id, vendor_id, eventid, source, paypal_email, checkin, userid, cron_status, cron_date')
			->from($this->db->quoteName('#__jticketing_integration_xref'))
			->where($this->db->quoteName('userid') . ' = ' . (int) $user->id);

		$eventIntegrationXrefData = $this->db->setQuery($query)->loadAssocList();

		if (!empty($eventIntegrationXrefData))
		{
			foreach ($eventIntegrationXrefData as $eventIntegrationData)
			{
				$domain->addItem($this->createItemFromArray($eventIntegrationData, $eventIntegrationData['id']));
			}
		}

		return $domain;
	}

	/**
	 * Create the domain for the JTicketing order data
	 *
	 * @param   JTableUser  $user  The JTableUser object to process
	 *
	 * @return  PrivacyExportDomain
	 *
	 * @since  2.3.4
	 */
	public function onCreateJTicketingOrderDomain(User $user)
	{
		$domain = $this->createDomain('JTicketing order ', 'JTicketing order data');

		// JTicketing Order Data
		$query = $this->db->getQuery(true)
			->select(
				'id, order_id, parent_order_id, event_details_id, name, email, user_id, cdate, mdate,
				transaction_id, payee_id, order_amount, original_amount, amount, coupon_code, fee, status,
				processor, ip_address, ticketscount, extra, order_tax, order_tax_details, coupon_discount,
				coupon_discount_details, ticket_email_sent, customer_note'
			)
			->from($this->db->quoteName('#__jticketing_order'))
			->where($this->db->quoteName('user_id') . ' = ' . (int) $user->id);

		$ordersData = $this->db->setQuery($query)->loadAssocList();

		if (!empty($ordersData))
		{
			foreach ($ordersData as $orderData)
			{
				$domain->addItem($this->createItemFromArray($orderData, $orderData['id']));
			}
		}

		if (!empty($ordersData))
		{
			foreach ($ordersData as $orderData)
			{
				// JTicketing Order Item Data
				$query = $this->db->getQuery(true)
					->select(
						'id, order_id, type_id, attendee_id, ticketcount, ticket_price, amount_paid, fee_amt, fee_params,
						attribute_amount, coupon_discount, payment_status, name, email, comment'
					)
					->from($this->db->quoteName('#__jticketing_order_items'))
					->where($this->db->quoteName('order_id') . ' = ' . (int) $orderData['id']);
				$orderItemsData = $this->db->setQuery($query)->loadAssocList();

				if (!empty($orderItemsData))
				{
					foreach ($orderItemsData as $orderItem)
					{
						$domain->addItem($this->createItemFromArray($orderItem, $orderItem['id']));
					}
				}
			}
		}

		return $domain;
	}

	/**
	 * Create the domain for the JTicketing Attendee data
	 *
	 * @param   JTableUser  $user  The JTableUser object to process
	 *
	 * @return  PrivacyExportDomain
	 *
	 * @since  2.3.4
	 */
	public function onCreateJTicketingAttendeesDomain(User $user)
	{
		$domain = $this->createDomain('JTicketing attendees ', 'JTicketing user attendee data');

		// JTicketing User Attendee Data
		$query = $this->db->getQuery(true)
			->select('id, enrollment_id, owner_id, owner_email, status, event_id, ticket_type_id')
			->from($this->db->quoteName('#__jticketing_attendees'))
			->where($this->db->quoteName('owner_id') . ' = ' . (int) $user->id);

		$attendeeData = $this->db->setQuery($query)->loadAssocList();

		if (!empty($attendeeData))
		{
			foreach ($attendeeData as $attendee)
			{
				$domain->addItem($this->createItemFromArray($attendee, $attendee['id']));
			}
		}

		return $domain;
	}

	/**
	 * Create the domain for the JTicketing WaitList data
	 *
	 * @param   JTableUser  $user  The JTableUser object to process
	 *
	 * @return  PrivacyExportDomain
	 *
	 * @since  2.3.4
	 */
	public function onCreateJTicketingWaitListDomain(User $user)
	{
		$domain = $this->createDomain('JTicketing wait list ', 'JTicketing wait list data');

		$query = $this->db->getQuery(true)
			->select('id, user_id, event_id, behaviour, status, created_date')
			->from($this->db->quoteName('#__jticketing_waiting_list'))
			->where($this->db->quoteName('user_id') . ' = ' . (int) $user->id);

		$waitingListData = $this->db->setQuery($query)->loadAssocList();

		if (!empty($waitingListData))
		{
			foreach ($waitingListData as $waitListItem)
			{
				$domain->addItem($this->createItemFromArray($waitListItem, $waitListItem['id']));
			}
		}

		return $domain;
	}

	/**
	 * Create the domain for the JTicketing coupon data
	 *
	 * @param   JTableUser  $user  The JTableUser object to process
	 *
	 * @return  PrivacyExportDomain
	 *
	 * @since  2.3.4
	 */
	public function onCreateJTicketingCouponsDomain(User $user)
	{
		$domain = $this->createDomain('JTicketing coupon ', 'JTicketing user created coupon data');
		$query = $this->db->getQuery(true)
			->select(
				'id, state, ordering, checked_out, checked_out_time, name, code,
				value, val_type, max_per_user, description, params,
				valid_from, valid_to, created_by'
			)
			->select($this->db->quoteName('limit', 'maximum_uses'))
			->from($this->db->quoteName('#__jticketing_coupon'))
			->where($this->db->quoteName('created_by') . ' = ' . (int) $user->id);

		$couponsData = $this->db->setQuery($query)->loadAssocList();

		if (!empty($couponsData))
		{
			foreach ($couponsData as $coupon)
			{
				$domain->addItem($this->createItemFromArray($coupon, $coupon['id']));
			}
		}

		return $domain;
	}

	/**
	 * Performs validation to determine if the data associated with a remove information request can be processed
	 *
	 * This event will not allow a super user account to be removed
	 *
	 * @param   PrivacyTableRequest  $request  The request record being processed
	 * @param   JUser                $user     The user account associated with this request if available
	 *
	 * @return  Array|Boolean  PrivacyRemovalStatus
	 *
	 * @since  2.3.4
	 */
	public function onPrivacyCanRemoveData(PrivacyTableRequest $request, User $user = null)
	{
		$status = new PrivacyRemovalStatus;

		if (!$user)
		{
			return $status;
		}

		$db = Factory::getDbo();

		// Check requested user has billing information in #__jticketing_users table.
		$query = $db->getQuery(true);
		$query->select($db->quoteName('o.order_id'));
		$query->from($db->quoteName('#__jticketing_order', 'o'));
		$query->join('LEFT', $db->quoteName('#__jticketing_users', 'u') . ' ON (' . $db->quoteName('u.order_id') . ' = ' . $db->quoteName('o.id') . ')');
		$query->where($db->quoteName('u.user_id') . '=' . $user->id);
		$db->setQuery($query);
		$userBillingInfo = $db->loadColumn();

		if (!empty($userBillingInfo))
		{
			$status->canRemove = false;
			$ordersList        = 'ID: ' . implode(', ', $userBillingInfo);
			$status->reason    = Text::sprintf('PLG_PRIVACY_JTICKETING_ERROR_CANNOT_REMOVE_USER_DATA', $ordersList);

			return $status;
		}

		// Check requested user is in wailting list for event ticket
		if ($this->onJtCheckUserIsInWaitList($user) === false)
		{
			$status->canRemove = false;
			$status->reason    = Text::_('PLG_PRIVACY_JTICKETING_ERROR_WAITLIST_CANNOT_REMOVE_USER_DATA');

			return $status;
		}

		// Check requested user is in attendees table
		if ($this->onJtCheckUserIsInAttendees($user) === false)
		{
			$status->canRemove = false;
			$status->reason    = Text::_('PLG_PRIVACY_JTICKETING_ERROR_ATTENDEES_CANNOT_REMOVE_USER_DATA');

			return $status;
		}

		// Check requested user has created any venue. And the created venue has used for a event
		$jticketingModelVenues = BaseDatabaseModel::getInstance('venues', 'JticketingModel');

		// Get requested user created all venues record
		$jticketingModelVenues->setState('jtUserId', $user->id);
		$userCreatedVenues = $jticketingModelVenues->getItems();
		$venueIdArray      = array();

		foreach ($userCreatedVenues as $venue)
		{
			$venueIdArray[] = $venue->id;
		}

		$jticketingModelVenueForm = BaseDatabaseModel::getInstance('venueForm', 'JticketingModel');

		// Check here requested user created venues are used for event. if used it will return venue id if not used will return false.
		if (!empty($venueIdArray))
		{
			$venueDetails = $jticketingModelVenueForm->usedVenues($venueIdArray);
		}

		if (!empty($venueDetails))
		{
			$venueIds = implode(', ', $venueDetails);

			$query = $db->getQuery(true);
			$query->select($db->quoteName('name'));
			$query->from($db->quoteName('#__jticketing_venues'));
			$query->where($db->quoteName('id') . ' IN (' . $venueIds . ' )');
			$db->setQuery($query);
			$venueName = $db->loadColumn();

			if (!empty($venueName))
			{
				$status->canRemove = false;
				$venueList         = 'Venues: ' . implode(', ', $venueName);
				$status->reason    = Text::sprintf('PLG_PRIVACY_JTICKETING_ERROR_VENUE_CANNOT_REMOVE_USER_DATA', $venueList);

				return $status;
			}
		}

		// Check here requested user has created any event. And have any order against that event.
		BaseDatabaseModel::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jticketing/models/');
		$jticketingModelEvents = BaseDatabaseModel::getInstance('events', 'JticketingModel');

		// Checking here requested user has created event
		$jticketingModelEvents->setState('created_by', $user->id, 'STRING');
		$userCreatedEvents = $jticketingModelEvents->getItems();

		// Requested user has not created event
		if (empty($userCreatedEvents))
		{
			return $status;
		}
		else
		{
			$eventsOrderData = array();
			$eventName = array();
			$jticketingModelorders = BaseDatabaseModel::getInstance('orders', 'JticketingModel');

			foreach ($userCreatedEvents as $key => $event)
			{
				$jticketingModelorders->setState('user_created_events', $event->id);
				$eventsOrderData = $jticketingModelorders->getItems();

				if ($eventsOrderData)
				{
					$eventName[] = $event->title;
				}
			}

			if (!empty($eventName))
			{
				$status->canRemove = false;
				$eventList         = 'Events: ' . implode(', ', $eventName);
				$status->reason    = Text::sprintf('PLG_PRIVACY_JTICKETING_ERROR_EVENT_ORDERS_CANNOT_REMOVE_USER_DATA', $eventList);

				return $status;
			}
			else
			{
				$db = $this->db;
				$query = $db->getQuery(true)
					->select($db->quoteName('title'))
					->from($db->quoteName('#__jticketing_events'))
					->where($db->quoteName('created_by') . ' = ' . (int) $user->id);
				$db->setQuery($query);
				$eventsArr = $db->loadAssoc();

				if (!empty($eventsArr))
				{
					$status->canRemove = false;
					$eventList         = 'Events: ' . implode(', ', $eventsArr);
					$status->reason    = Text::sprintf('PLG_PRIVACY_JTICKETING_ERROR_EVENT_CANNOT_REMOVE_USER_DATA', $eventList);

					return $status;
				}
			}
		}

		return $status;
	}

	/**
	 * Check requested user is in waiting list
	 *
	 * @param   Object  $user  The request user data
	 *
	 * @return  boolean
	 *
	 * @since  2.3.4
	 */
	private function onJtCheckUserIsInWaitList($user)
	{
		$jticketingTableWaitinglist = Table::getInstance('waitinglist', 'JTicketingTable', array());
		$jticketingTableWaitinglist->load(array('user_id' => $user->id));

		if ($jticketingTableWaitinglist->id)
		{
			return false;
		}

		return true;
	}

	/**
	 * Check requested user is in attendees list
	 *
	 * @param   Object  $user  The request user data
	 *
	 * @return  boolean
	 *
	 * @since  2.3.4
	 */
	private function onJtCheckUserIsInAttendees($user)
	{
		$jticketingTableAttendees = Table::getInstance('attendees', 'JTicketingTable', array());
		$jticketingTableAttendees->load(array('owner_id' => $user->id));

		if ($jticketingTableAttendees->id)
		{
			return false;
		}

		return true;
	}

	/**
	 * Removes the data associated with a remove information request
	 *
	 * This event will pseudoanonymise the user account
	 *
	 * @param   PrivacyTableRequest  $request  The request record being processed
	 * @param   JUser                $user     The user account associated with this request if available
	 *
	 * @return  void
	 *
	 * @since  2.3.4
	 */
	public function onPrivacyRemoveData(PrivacyTableRequest $request, User $user = null)
	{
		// This plugin only processes data for registered user accounts
		if (!$user)
		{
			return;
		}

		// If there was an error loading the user do nothing here
		if ($user->guest)
		{
			return;
		}

		$db = $this->db;

		// 1. Delete JTicketing venue data :
		$query1 = $db->getQuery(true)
			->delete($db->quoteName('#__jticketing_venues'))
			->where('created_by = ' . $user->id);
		$db->setQuery($query1);
		$db->execute();

		// 2. Delete JTicketing coupon data :
		$query3 = $db->getQuery(true)
			->delete($db->quoteName('#__jticketing_coupon'))
			->where('created_by = ' . (int) $user->id);
		$db->setQuery($query3);
		$db->execute();
	}
}