<?php
/**
 * @package     JTicketing
 * @subpackage  com_jticketing
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

// Joomla 6: JVERSION check removed
		if (false) // Legacy < '4.0.0')
{
	require_once JPATH_ADMINISTRATOR . '/components/com_installer/models/database.php';
}

/**
 * JTicketing Migration Model
 *
 * @since  1.6
 */
class JticketingModelMigration extends BaseDatabaseModel
{
	/**
	 * Method to add activity for old event prior to v 2.0
	 *
	 * @return  array
	 *
	 * @since   1.0
	 */
	public function migrateData()
	{
		$migration = array();
		$migration['event'] = $this->addOldEventVendor();
		$migration['venue'] = $this->addOldVenueVendor();
		$migration['payout'] = $this->fixPayoutsTable();
		$migration['activity'] = $this->addActivity();
		$migration['media'] = $this->imageMigration();
		$migration['attendee'] = $this->attendeeMigration();
		$migration['jlikecontent'] = $this->deletDuplicateJlikeData();
		$migration['activity'] = $this->fixActivityActorid();
		$migration['coupon'] = $this->addUsedCouponCount();
		$migration['imageType'] = $this->imageTypeMigration();

		return $migration;
	}

	/**
	 * Method to add activity for event order
	 *
	 * @return  boolean
	 *
	 * @since   2.0
	 */
	public function pushOrderActivity()
	{
		BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_activitystream/models');
		$activityStreamModelActivity = BaseDatabaseModel::getInstance('Activity', 'ActivityStreamModel');

		require_once JPATH_SITE . '/plugins/system/jticketingactivities/helper.php';
		$plgSystemJticketingActivities = new PlgSystemJticketingActivitiesHelper;

		// Actitivty for donations - start
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('id');
		$query->from($db->quoteName('#__jticketing_order'));
		$query->where($db->quoteName('status') . "=" . "'C'");
		$query->order('order_id ASC');
		$db->setQuery($query);
		$completedOrders = $db->loadColumn();

		foreach ($completedOrders as $completedOrder)
		{
			$orderDetails = JT::order($completedOrder);
			$orderDetails->eventData = new stdClass;

			// Append event details to order details
			$orderDetails->eventData = JT::event()->loadByIntegration($orderDetails->event_details_id);

			$user = Factory::getUser($orderDetails->user_id);
			$activityData = array();
			$activityData['id'] = '';
			$actorData = $plgSystemJticketingActivities->getActorData($user->get('id'));
			$activityData['actor'] = json_encode($actorData);
			$user = Factory::getUser();
			$activityData['actor_id'] = $user->get('id');
			$activityData['created_date'] = $orderDetails->cdate;

			$eventType = $orderDetails->eventData->isOnline() ? 'Online' : 'Offline';

			$objectData = array();
			$objectData['type'] = $eventType;
			$objectData['amount'] = $orderDetails->getAmount(true);
			$activityData['object'] = json_encode($objectData);
			$activityData['object_id'] = 'order';

			// Get event-target data
			$targetData = array();
			$targetData['id'] = $orderDetails->event_details_id;
			$targetData['type'] = 'event';
			$targetData['url'] = Uri::root() . 'index.php?option=com_jticketing&view=event&id=' . $orderDetails->event_details_id;
			$targetData['name'] = $orderDetails->eventData->getTitle();
			$activityData['target'] = json_encode($targetData);
			$activityData['target_id'] = $orderDetails->event_details_id;
			$activityData['type'] = 'jticketing.order';

			$activityData['template'] = $orderDetails->eventData->isOnline() ? 'onlineEventOrder.mustache' : 'offlineEventOrder.mustache';

			$activityStreamModelActivity->save($activityData);
		}

		return true;
	}

	/**
	 * transfer credit and debit entries in passbook table of vendors
	 *
	 * @return  boolean
	 *
	 * @since   2.0
	 */
	public function fixPayoutsTable()
	{
		$check = $this->checkTableExists('jticketing_ticket_payouts');

		if ($check)
		{
			$oldCreditData = $this->getOldData();
			$oldPayoutData = $this->getOldPayoutData();

			if (empty($oldCreditData))
			{
				$db = Factory::getDbo();
				$db->dropTable('#__jticketing_ticket_payouts', true);
			}
			else
			{
				if (!empty($oldPayoutData))
				{
					$result = $this->formatPayoutData($oldCreditData, $oldPayoutData);

					if ($result)
					{
						$db = Factory::getDbo();
						$db->dropTable('#__jticketing_ticket_payouts', true);
					}
				}
			}
		}

		return true;
	}

	/**
	 * Method to add activity for old event prior to v 2.0
	 *
	 * @return  boolean
	 *
	 * @since   1.0
	 */
	public function addActivity()
	{
		require_once JPATH_SITE . '/plugins/system/jticketingactivities/helper.php';
		$plgSystemJticketingActivities = new PlgSystemJticketingActivitiesHelper;

		BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_jticketing/models');
		BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_activitystream/models');
		$activityStreamModelActivity = BaseDatabaseModel::getInstance('Activity', 'ActivityStreamModel');

		$db    = Factory::getDbo();

		// Getting all event id. Create the base select statement.
		$query = $db->getQuery(true);
		$query->select('eventid');
		$query->from($db->quoteName('#__jticketing_integration_xref', 'i'));
		$query->where($db->quoteName('i.source') . ' = ' . $db->quote("com_jticketing"));
		$db->setQuery($query);
		$eventIds = $db->loadColumn();

		$type = ("'event.addvideo', 'event.addimage','jticketing.addevent', 'jticketing.textpost',
		'event.extended', 'eventBooking.extended', 'jticketing.order'");
		$query = $db->getQuery(true);
		$query->select('DISTINCT target_id');
		$query->from($db->quoteName('#__tj_activities'));
		$query->where($db->quoteName('type') . ' IN (' . $type . ')');
		$db->setQuery($query);
		$targetIds = $db->loadColumn();

		if (!empty($eventIds))
		{
			foreach ($eventIds as $eventId)
			{
				// Add campaign create activity
				if (!in_array($eventId, $targetIds))
				{
					// Fetching event data by id
					$jtickeitngModelEventFrom = BaseDatabaseModel::getInstance('eventform', 'JticketingModel');
					$eventData = $jtickeitngModelEventFrom->getItem($eventId);

					$user = Factory::getUser($eventData->created_by);
					$activityData = array();
					$activityData['id'] = '';
					$activityData['created_date'] = $eventData->created;

					$actorData = $plgSystemJticketingActivities->getActorData($user->get('id'));
					$activityData['actor'] = json_encode($actorData);
					$activityData['actor_id'] = $user->get('id');

					$objectData = array();
					$objectData['type'] = 'event';
					$objectData['name'] = $eventData->title;
					$objectData['id'] = $eventData->id;
					$objectData['url'] = Uri::root() . 'index.php?option=com_jticketing&view=event&id=' . $eventData->id;
					$activityData['object'] = json_encode($objectData);
					$activityData['object_id'] = $eventData->id;

					$targetData = array();
					$targetData['type'] = 'event';
					$targetData['name'] = $eventData->title;
					$targetData['id'] = $eventData->id;
					$targetData['url'] = Uri::root() . 'index.php?option=com_jticketing&view=event&id=' . $eventData->id;
					$activityData['target'] = json_encode($targetData);
					$activityData['target_id'] = $eventData->id;

					$activityData['type'] = 'jticketing.addevent';
					$activityData['template'] = 'addevent.mustache';

					$activityStreamModelActivity->save($activityData);
				}
			}
		}

		if (empty($targetIds))
		{
			$this->pushOrderActivity();
		}

		return true;
	}

	/**
	 * add credit entries in the passbook table
	 *
	 * @param   integer  $id  The xref id
	 *
	 * @return void
	 *
	 * @since 2.0
	 */
	public function getIntegrationData($id)
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from($db->quoteName('#__jticketing_integration_xref'));
		$query->where($db->quoteName('id') . ' = ' . $db->quote($id));
		$db->setQuery($query);

		return $db->loadObject();
	}

	/**
	 * format the payout data according to date
	 *
	 * @param   object  $oldCreditData  The credit data of tickets.
	 *
	 * @param   object  $oldPayoutData  The debit data of payouts.
	 *
	 * @return boolean
	 *
	 * @since 2.0
	 */
	public function formatPayoutData($oldCreditData, $oldPayoutData)
	{
		require_once JPATH_SITE . '/components/com_jticketing/helpers/common.php';
		require_once JPATH_SITE . '/components/com_jticketing/helpers/main.php';
		require_once JPATH_SITE . '/components/com_jticketing/helpers/order.php';
		require_once JPATH_ADMINISTRATOR . '/components/com_tjvendors/helpers/tjvendors.php';

		$dataSize = sizeof($oldCreditData);
		$count = 0;

		foreach ($oldCreditData as $data)
		{
			$count++;

			foreach ($oldPayoutData as $payoutData)
			{
				$date = new Date($payoutData->date . ' +23 hour +59 minutes');

				if ($date <= $data->cdate)
				{
					$this->addPayoutData($payoutData);
				}
			}

			$this->addCreditData($data);

			if ($dataSize == $count)
			{
				foreach ($oldPayoutData as $payoutData)
				{
					$date = new Date($payoutData->date . ' +23 hour +59 minutes');

					if ($date > $data->cdate)
					{
						$this->addPayoutData($payoutData);
					}
				}
			}
		}

		return true;
	}

	/**
	 * add credit entries in the passbook table
	 *
	 * @param   object  $data  The credit data of tickets.
	 *
	 * @return void
	 *
	 * @since 2.0
	 */
	public function addCreditData($data)
	{
		$data->client = "com_jticketing";
		$tjvendorFrontHelper = new TjvendorFrontHelper;
		$xrefId = $data->event_details_id;
		$integrationDetails = $this->getIntegrationData($xrefId);

		if (!empty($integrationDetails))
		{
			$vendorCheck = $tjvendorFrontHelper->checkVendor($integrationDetails->userid, 'com_jticketing');

			if (!$vendorCheck)
			{
				$vendor_id = $this->addOldVendor($integrationDetails->userid);
			}
			else
			{
				$vendor_id = $vendorCheck;
			}

			BaseDatabaseModel::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_tjvendors/models', 'vendor');
			BaseDatabaseModel::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_tjvendors/helpers', 'tjvendors');
			Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_tjvendors/tables', 'vendor');
			$com_params = ComponentHelper::getParams($data->client);
			$currency = $com_params->get('currency');
			$entry_data['vendor_id'] = $vendor_id;
			$totalAmount = TjvendorsHelper::getTotalAmount($entry_data['vendor_id'], $currency, 'com_jticketing');
			$entry_data['reference_order_id'] = $data->order_id;
			$transactionClient = Text::_('COM_JTICKETING');
			$entry_data = array();
			$entry_data['transaction_id'] = $transactionClient . '-' . $currency . '-' . $entry_data['vendor_id'] . '-';
			$entry_data['transaction_time'] = $data->cdate;
			$entry_data['credit'] = $data->amount - $data->fee;
			$entry_data['total'] = $totalAmount['total'] + $entry_data['credit'];
			$entry_data['debit'] = 0;
			$entry_status = "credit_for_ticket_buy";
			$params = array("customer_note" => $data->customer_note,"entry_status" => $entry_status);
			$entry_data['params'] = json_encode($params);
			$entry_data['currency'] = $currency;
			$entry_data['client'] = $data->client;
			BaseDatabaseModel::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_tjvendors/models', 'payout');
			$TjvendorsModelPayout = BaseDatabaseModel::getInstance('Payout', 'TjvendorsModel');
			$TjvendorsModelPayout->addCreditEntry($entry_data);
		}
	}

	/**
	 * add debit entries in the passbook table
	 *
	 * @param   object  $payoutData  The payout old data.
	 *
	 * @return void
	 *
	 * @since 2.0
	 */
	public function addPayoutData($payoutData)
	{
		$tjvendorFrontHelper = new TjvendorFrontHelper;
		$com_params = ComponentHelper::getParams('com_jticketing');
		$currency = $com_params->get('currency');
		$vendorCheck = $tjvendorFrontHelper->checkVendor($payoutData->user_id, 'com_jticketing');

		if (!$vendorCheck)
		{
			$vendor_id = $this->addOldVendor($payoutData->user_id);
		}
		else
		{
			$vendor_id = $vendorCheck;
		}

		$newPayoutData = new stdClass;
		$newPayoutData->debit = $payoutData->amount;
		$payableAmount = TjvendorsHelper::getTotalAmount($vendor_id, $currency, 'com_jticketing');
		$newPayoutData->total = $payableAmount['total'] - $newPayoutData->debit;
		$newPayoutData->transaction_time = $payoutData->date;
		$newPayoutData->client = 'com_jticketing';
		$newPayoutData->currency = $currency;
		$transactionClient = Text::_('COM_JTICKETING');
		$newPayoutData->transaction_id = $transactionClient . '-' . $currency . '-' . $vendor_id . '-';
		$newPayoutData->id = '';
		$newPayoutData->vendor_id = $vendor_id;
		$newPayoutData->status = $payoutData->status;
		$newPayoutData->credit = '0.00';
		$params = array("customer_note" => "", "entry_status" => "debit_payout");
		$newPayoutData->params = json_encode($params);

		// Insert the object into the user passbook table.
		$result = Factory::getDbo()->insertObject('#__tjvendors_passbook', $newPayoutData);

		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('max(' . $db->quotename('id') . ')');
		$query->from($db->quoteName('#__tjvendors_passbook'));
		$db->setQuery($query);

		$payout_id = $db->loadResult();

		$payout_update = new stdClass;

		// Must be a valid primary key value.
		$payout_update->id = $payout_id;
		$payout_update->transaction_id = $newPayoutData->transaction_id . $payout_update->id;

		// Update their details in the passbook table using id as the primary key.
		Factory::getDbo()->updateObject('#__tjvendors_passbook', $payout_update, 'id');
	}

	/**
	 * add a user as a vendor
	 *
	 * @param   int  $user_id  The user id.
	 *
	 * @return void
	 *
	 * @since 2.0
	 */
	public function addOldVendor($user_id)
	{
		$tjvendorFrontHelper = new TjvendorFrontHelper;
		$vendorData                  = array();
			$vendorData['vendor_client'] = "com_jticketing";
		$vendorData['user_id']       = $user_id;
		$vendorData['vendor_title']  = $vendorData['userName'];
		$vendorData['state']         = "1";

		BaseDatabaseModel::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_tjvendors/models', 'vendor');
		$TjvendorsModelVendors = BaseDatabaseModel::getInstance('Vendor', 'TjvendorsModel');
		Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_tjvendors/tables', 'vendor');

		$TjvendorsModelVendors->save($vendorData);
		$vendor_id = $tjvendorFrontHelper->checkVendor($user_id, 'com_jticketing');

		return $vendor_id;
	}

	/**
	 * check id the table exists
	 *
	 * @param   string  $table  The table name.
	 *
	 * @return boolean
	 *
	 * @since 2.0
	 */
	public function checkTableExists($table)
	{
		$db = Factory::getDbo();
		$config = Factory::getConfig();

		$dbname = $config->get('db');
		$dbprefix = $config->get('dbprefix');

		$query = $db->getQuery(true);
		$query->select($db->quoteName('table_name'));
		$query->from($db->quoteName('information_schema.tables'));
		$query->where($db->quoteName('table_schema') . ' = ' . $db->quote($dbname));
		$query->where($db->quoteName('table_name') . ' = ' . $db->quote($dbprefix . $table));
		$db->setQuery($query);
		$check = $db->loadResult();

		if ($check)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * get old orders data
	 *
	 * @return mixed
	 *
	 * @since 2.0
	 */
	public function getOldData()
	{
		$com_params = ComponentHelper::getParams('com_jticketing');
		$handle_transactions = $com_params->get('handle_transactions', 0);

		if ($handle_transactions == 0)
		{
			$db = Factory::getDbo();
			$query = $db->getQuery(true);
			$query->select('*');
			$query->from($db->quoteName('#__jticketing_order'));
			$query->where($db->quoteName('status') . ' = ' . $db->quote('C'));
			$db->setQuery($query);

			return $db->loadObjectList();
		}
		else
		{
			return false;
		}
	}

	/**
	 * get old payouts data
	 *
	 * @return mixed
	 *
	 * @since 2.0
	 */
	public function getOldPayoutData()
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from($db->quoteName('#__jticketing_ticket_payouts'));
		$db->setQuery($query);

		return $db->loadObjectList();
	}

	/**
	 * get old event xref data
	 *
	 * @return mixed
	 *
	 * @since 2.0
	 */
	public function getOldEventXrefData()
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from($db->quoteName('#__jticketing_integration_xref'));
		$db->setQuery($query);

		return $db->loadObjectList();
	}

	/**
	 * get venue data
	 *
	 * @return mixed
	 *
	 * @since 2.0
	 */
	public function getOldVenueData()
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from($db->quoteName('#__jticketing_venues'));
		$db->setQuery($query);

		return $db->loadObjectList();
	}

	/**
	 * Add vendor id in xref table against an event
	 *
	 * @return boolean
	 *
	 * @since 2.0
	 */
	public function addOldEventVendor()
	{
		$oldEventXrefData = $this->getOldEventXrefData();
		$tjvendorFrontHelper = new TjvendorFrontHelper;

		foreach ($oldEventXrefData as $eventXrefData)
		{
			$vendorCheck = $tjvendorFrontHelper->checkVendor($eventXrefData->userid, 'com_jticketing');

			if (empty($vendorCheck))
			{
				if (!empty($eventXrefData->paypal_email))
				{
					$vendor_id = $this->addOldVendor($eventXrefData->userid);
					$params = ComponentHelper::getParams('com_jticketing');
					$handle_transactions = $params->get('handle_transactions');

					if ($handle_transactions == 1)
					{
						$payment_gateway = "paypal";
					}
					else
					{
						$payment_gateway = "adaptive_paypal";
					}

					$param1 = new stdClass;
					$param1->payment_gateway = $payment_gateway;
					$gatewayDetails = array("payment_gateway" => $payment_gateway, "payment_email_id" => $eventXrefData->paypal_email);

					$params = (object) array_merge((array) $param1, $gatewayDetails);

					$paymentArray = array();
					$paymentArray['payment_gateway0'] = $params;
					$paymentArrayList['payment_gateway'] = $paymentArray;

					$vendorParams = json_encode($paymentArrayList);

					$vendorData = new stdClass;
					$vendorData->vendor_id = $vendor_id;
					$vendorData->params    = $vendorParams;

					Factory::getDbo()->updateObject('#__vendor_client_xref', $vendorData, 'vendor_id');
				}
				else
				{
					$vendor_id = $this->addOldVendor($eventXrefData->userid);
				}

				$newEventData = new stdClass;
				$newEventData->vendor_id = $vendor_id;
				$newEventData->id = $eventXrefData->id;

				// Insert the object into the user integration table.
				Factory::getDbo()->updateObject('#__jticketing_integration_xref', $newEventData, 'id');
			}
			else
			{
				$vendor_id = $vendorCheck;

				if (!empty($eventXrefData->paypal_email))
				{
					$params = ComponentHelper::getParams('com_jticketing');
					$handle_transactions = $params->get('handle_transactions');

					if ($handle_transactions == 1)
					{
						$payment_gateway = "paypal";
					}
					else
					{
						$payment_gateway = "adaptive_paypal";
					}

					$param1 = new stdClass;
					$param1->payment_gateway = $payment_gateway;
					$gatewayDetails = array("payment_gateway" => $payment_gateway, "payment_email_id" => $eventXrefData->paypal_email);

					$params = (object) array_merge((array) $param1, $gatewayDetails);

					$paymentArray = array();
					$paymentArray['payment_gateway0'] = $params;
					$paymentArrayList['payment_gateway'] = $paymentArray;

					$vendorParams = json_encode($paymentArrayList);

					$vendorData = new stdClass;
					$vendorData->vendor_id = $vendor_id;
					$vendorData->params    = $vendorParams;

					Factory::getDbo()->updateObject('#__vendor_client_xref', $vendorData, 'vendor_id');
				}

				$newEventData = new stdClass;
				$newEventData->vendor_id = $vendor_id;
				$newEventData->id = $eventXrefData->id;

				// Insert the object into the user integration table.
				Factory::getDbo()->updateObject('#__jticketing_integration_xref', $newEventData, 'id');
			}
		}

		return true;
	}

	/**
	 * Add vendor id to venue.
	 *
	 * @return boolean
	 *
	 * @since 2.0
	 */
	public function addOldVenueVendor()
	{
		$oldVenueData        = $this->getOldVenueData();
		$tjvendorFrontHelper = new TjvendorFrontHelper;

		foreach ($oldVenueData as $venueData)
		{
			$vendorCheck = $tjvendorFrontHelper->checkVendor($venueData->created_by, 'com_jticketing');

			if (empty($vendorCheck))
			{
				$vendor_id = $this->addOldVendor($venueData->created_by);
			}
			else
			{
				$vendor_id = $vendorCheck;
			}

			$newVenueData = new stdClass;
			$newVenueData->vendor_id = $vendor_id;
			$newVenueData->id = $venueData->id;

			// Insert the object into the user profile table.
			Factory::getDbo()->updateObject('#__jticketing_venues', $newVenueData, 'id');
		}

		return true;
	}

	/**
	 * Image Migration
	 *
	 * @return void
	 *
	 * @since 2.0
	 */
	public function imageMigration()
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName(array('id', 'image')));
		$query->from($db->quoteName('#__jticketing_events'));
		$db->setQuery($query);
		$eventImages = $db->loadAssocList();

		foreach ($eventImages as $image)
		{
			if ($image['image'] != 'default-event-image.png' && $image['image'] != '')
			{
				$mediaData = array();
				$mediaData['name'] = $image['image'];
				$mediaData['type'] = "image";
				$mediaData['size'] = 0;
				$mediaData['tmp_name'] = JPATH_ROOT . '/media/com_jticketing/images/' . $mediaData['name'];
				$mediaData['upload_type'] = "move";

				BaseDatabaseModel::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jticketing/models', 'media');
				$jtMediaModel = BaseDatabaseModel::getInstance('Media', 'JticketingModel');

				if ($returnData = $jtMediaModel->save($mediaData))
				{
					BaseDatabaseModel::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jticketing/models', 'mediaxref');
					$jtMediaxrefModel = BaseDatabaseModel::getInstance('MediaXref', 'JticketingModel');
					$mediaXref = array();
					$mediaXref['media_id'] = $returnData['id'];
					$mediaXref['client_id'] = $image['id'];
					$mediaXref['client'] = 'com_jticketing.event';

					if ($jtMediaxrefModel->save($mediaXref))
					{
						$image_update = new stdClass;

						// Must be a valid primary key value.
						$image_update->id = $image['id'];
						$image_update->image = '';

						// Update their details in the events table using id as the primary key.
						Factory::getDbo()->updateObject('#__jticketing_events', $image_update, 'id');
					}
				}
			}
		}

		return true;
	}

	/**
	 * Attendee Migration
	 *
	 * @return Boolean
	 *
	 * @since 2.1
	 */
	public function attendeeMigration()
	{
		$jtParams = ComponentHelper::getParams('com_jticketing');
		$enrollmentPrefix = $jtParams->get('order_prefix');

		$db    = $this->getDbo();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from($db->quoteName('#__jticketing_order'));
		$db->setQuery($query);
		$orders = $db->loadObjectList();

		foreach ($orders as $order)
		{
			$orderQuery = $db->getQuery(true);
			$orderQuery->select('*');
			$orderQuery->from($db->quoteName('#__jticketing_order_items'));
			$orderQuery->where($db->quoteName('order_id') . ' = ' . $db->quote($order->id));
			$db->setQuery($orderQuery);
			$orderItems = $db->loadObjectList();

			foreach ($orderItems as $orderItem)
			{
				// Update attendee data if attendee information is already collected
				if ($orderItem->attendee_id != 0)
				{
					// Must be a valid primary key value.
					$attendeeUpdateData = new stdClass;
					$attendeeUpdateData->id = $orderItem->attendee_id;
					$attendeeUpdateData->owner_email = $order->email;

					// Change enrollment status according to orders
					if ($order->status === 'C' || $order->status === 'c')
					{
						$attendeeUpdateData->status = 'A';
					}
					elseif ($order->status === 'P' || $order->status === 'p')
					{
						$attendeeUpdateData->status = 'P';
					}
					else
					{
						$attendeeUpdateData->status = 'R';
					}

					$attendeeUpdateData->event_id = $order->event_details_id;
					$attendeeUpdateData->ticket_type_id = $orderItem->type_id;

					// Enrollment id with prefix
					$attendeeUpdateData->enrollment_id = JT::model('attendeeform')->generateEnrollmentId($orderItem->attendee_id);

					// Update the details in the attendees table using id as the primary key.
					$db->updateObject('#__jticketing_attendees', $attendeeUpdateData, 'id');
				}
				else
				{
					// Must be a valid primary key value.
					$attendeeData = new stdClass;
					$attendeeData->owner_id = $order->user_id;
					$attendeeData->owner_email = $order->email;
					$attendeeData->event_id = $order->event_details_id;
					$attendeeData->ticket_type_id = $orderItem->type_id;

					// Change enrollment status according to orders
					if ($order->status === 'C' || $order->status === 'c')
					{
						$attendeeData->status = 'A';
					}
					elseif ($order->status === 'P' || $order->status === 'p')
					{
						$attendeeData->status = 'P';
					}
					else
					{
						$attendeeData->status = 'R';
					}

					// Update the details in the attendees table using id as the primary key.
					$db->insertObject('#__jticketing_attendees', $attendeeData);

					// Update Enrollment id with prefix
					$lastAttendeeId = $db->insertid();
					$enrollmentUpdateObj = new stdClass;
					$enrollmentUpdateObj->id = $lastAttendeeId;
					$enrollmentUpdateObj->enrollment_id = $enrollmentPrefix . $lastAttendeeId;

					// Update their details in the attendee table using id as the primary key.
					$db->updateObject('#__jticketing_attendees', $enrollmentUpdateObj, 'id');

					// Update order item table with attendee id
					$orderItemUpdateData = new stdClass;
					$orderItemUpdateData->id = $orderItem->id;
					$orderItemUpdateData->attendee_id = $lastAttendeeId;
					$db->updateObject('#__jticketing_order_items', $orderItemUpdateData, 'id');
				}
			}
		}

		return true;
	}

	/**
	 * JLike content Migration
	 *
	 * @return Boolean
	 *
	 * @since 2.1
	 */
	public function deletDuplicateJlikeData()
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true);

		// Delete all the JTicketing records which have wrong URL.
		$conditions = array(
			$db->quoteName('url') . ' LIKE \'http%\'',
			$db->quoteName('element') . ' = ' . $db->quote('com_jticketing.event')
		);

		$query->delete($db->quoteName('#__jlike_content'));
		$query->where($conditions);
		$db->setQuery($query);

		$db->execute();

		return true;
	}

	/**
	 * Activity Migration
	 *
	 * @return Boolean
	 *
	 * @since 2.3.4
	 */
	public function fixActivityActorid()
	{
		if (ComponentHelper::isEnabled('com_activitystream', true))
		{
			// Load activity component models
			BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_activitystream/models');
		}

		$activityStreamModelActivity = BaseDatabaseModel::getInstance('Activity', 'ActivityStreamModel');

		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName(array('id', 'actor')));
		$query->from($db->quoteName('#__tj_activities'));
		$query->where($db->quoteName('type') . '=' . $db->quote('jticketing.order'));
		$db->setQuery($query);
		$activities = $db->loadAssocList();

		foreach ($activities as $activity)
		{
			$activitiesArray = json_decode($activity['actor']);

			if (isset($activitiesArray->id))
			{
				$activityData['id'] = $activity['id'];
				$activityData['actor_id'] = $activitiesArray->id;
				$activityStreamModelActivity->save($activityData);
			}
		}

		return true;
	}

	/**
	 * Update used coupon count in `used` column of coupon table
	 *
	 * @return boolean
	 *
	 * @since 2.4.0
	 */
	public function addUsedCouponCount()
	{
		$db    = Factory::getDbo();

		// Get total applied coupon count group by coupon count
		$query = $db->getQuery(true);
		$query->select('COUNT(coupon_code) as couponCount');
		$query->select($db->quoteName('coupon_code'));
		$query->from($db->quoteName('#__jticketing_order'));
		$query->where($db->quoteName('status') . '=' . $db->quote('C'));
		$query->group($db->quoteName('coupon_code'));
		$db->setQuery($query);
		$usedCouponCountArr = $db->loadObjectList();

		if (!empty($usedCouponCountArr))
		{
			foreach ($usedCouponCountArr as $coupon)
			{
				$query = $db->getQuery(true);
				$query->update($db->quoteName('#__jticketing_coupon'))
				->set($db->quoteName('used') . ' = ' . (int) $coupon->couponCount)
				->where($db->quoteName('code') . ' = ' . $db->quote($coupon->coupon_code));
				$db->setQuery($query);
				$db->execute();
			}
		}

		return true;
	}

	/**
	 * Update image `type` column of media table
	 *
	 * @return boolean
	 *
	 * @since 2.4.0
	 */
	public function imageTypeMigration()
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName(array('id', 'type', 'path')));
		$query->from($db->quoteName('#__tj_media_files'));
		$db->setQuery($query);
		$eventImages = $db->loadAssocList();

		foreach ($eventImages as $image)
		{
			if ($image['type'] == 'image.jpg' || $image['type'] == 'image.png'
				|| $image['type'] == 'jpeg' || $image['type'] == 'image/jpg'
				|| $image['type'] == 'image/png' || $image['type'] == 'image/jpeg')
			{
				$image_update = new stdClass;
				$image_update->id = $image['id'];
				$image_update->type = 'image';

				// Update their type in the media table using id as the primary key.
				Factory::getDbo()->updateObject('#__tj_media_files', $image_update, 'id');
			}
		}

		return true;
	}
}
