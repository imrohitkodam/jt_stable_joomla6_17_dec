<?php
/**
 * @package     JTicketing
 * @subpackage  com_jticketing
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Router\Route;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Component\ComponentHelper;
if (file_exists(JPATH_SITE . '/components/com_jticketing/helpers/common.php')) { require_once JPATH_SITE . '/components/com_jticketing/helpers/common.php'; }
if (file_exists(JPATH_SITE . '/components/com_jticketing/events/order.php')) { require_once JPATH_SITE . '/components/com_jticketing/events/order.php'; }

/**
 * controller for order
 *
 * @package     JTicketing
 * @subpackage  component
 * @since       1.0
 */
class JticketingControllerorders extends BaseController
{
	public $jTOrderHelper;

	/**
	 * Constructor
	 *
	 * @since   1.0
	 */
	public function __construct()
	{
		parent::__construct();
		if (file_exists(JPATH_SITE . '/components/com_jticketing/helpers/route.php')) { require_once JPATH_SITE . '/components/com_jticketing/helpers/route.php'; }
		$this->JTRouteHelper = new JTRouteHelper;
		$this->jticketingmainhelper = new jticketingmainhelper;
		$this->jtTriggerOrder = new JticketingTriggerOrder;

		if (file_exists(JPATH_SITE . '/components/com_jticketing/helpers/order.php')) { require_once JPATH_SITE . '/components/com_jticketing/helpers/order.php'; }
		$this->jTOrderHelper = new JticketingOrdersHelper;
	}

	/**
	 * Changes order status for example pending to completed
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function save()
	{
		$mainframe		= Factory::getApplication();
		$linkForOrders	= 'index.php?option=com_jticketing&view=orders';

		if (!Session::checkToken())
		{
			$mainframe->enqueueMessage(Text::_('JINVALID_TOKEN'), 'error');

			$mainframe->redirect($linkForOrders);
		}

		$input			= $mainframe->input;
		$post			= $input->post;

		/* var $orderModel JTicketingModelOrder */
		$orderModel		= JT::model('order');
		$statusArray 	= $orderModel->getOrderStatues(2);
		$orderId     	= $post->get('order_id');

		/** @var $order JticketingOrder */
		$order        	= JT::order($orderId);
		$event		 	= JT::event()->loadByIntegration($order->event_details_id);
		$user         	= Factory::getUser();
		$redirectview	= $post->get('redirectview', '', 'STRING');
		$status 		= $post->get('payment_status', '', 'STRING');

		// Allow to change status to event creator or Admin access user.
		if ($user->authorise('core.admin') === false && $user->id != $event->getCreator())
		{
			$mainframe->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'Error');
			$mainframe->redirect(!empty($redirectview) ? $redirectview : $linkForOrders);
		}

		// Check if status is the status from the status array.
		if (!in_array($status, $statusArray))
		{
			$this->setRedirect($linkForOrders);
		}

		/** @var $ordersModel JticketingModelorders */
		$ordersModel 	= JT::model('orders');
		$result 		= $ordersModel->changeOrderStatus($order, $status);

		if ($result)
		{
			$mainframe->enqueueMessage(Text::_('COM_JTICKETING_ORDER_STATUS_CHANGED'), 'success');
		}
		else
		{
			$mainframe->enqueueMessage($this->getError(), 'Error');
		}

		$mainframe->redirect(!empty($redirectview)? $redirectview : Route::_($linkForOrders, false));
	}

	/**
	 * Retry payment gateway on confirm payment view frontend.
	 *
	 * @return  json.
	 *
	 * @since   1.6
	 */
	public function retryPayment()
	{
		$input = Factory::getApplication()->getInput();
		$getdata = $input->get;
		$pg_plugin = $getdata->get('gateway_name', '', 'STRING');
		$order = $getdata->get('order', '', 'STRING');
		$orders = (explode("-", $order));
		$order_id = end($orders);
		$order_id = (int) $order_id;
		$modelObj = $this->getModel('payment');
		$payment_getway_form = $modelObj->getHTMLS($pg_plugin, $order_id, $order);

		echo json_encode($payment_getway_form);
		jexit();
	}

	/**
	 * Get Ticket types data
	 *
	 * @param   integer  $eventid  eventid
	 *
	 * @return  array  ticket types
	 *
	 * @since   1.0
	 *
	 * @deprecated 3.2.0 The method is deprecated and will be removed in the next version.
	 */
	public function gettickettypesdata($eventid)
	{
		if (empty($eventid))
		{
			$eventid = $input->get('eventid');
		}

		if (empty($client))
		{
			echo "Please select integration in backend option";
		}

		$jticketingmainhelper     = new jticketingmainhelper;
		$jticketingfrontendhelper = new jticketingfrontendhelper;
		$integration              = $jticketingmainhelper->getIntegration();
		$client                   = $jticketingfrontendhelper->getClientName($integration);

		if (empty($client))
		{
			echo "Please select integration in backend option";
		}

		$query = "SELECT id FROM #__jticketing_types WHERE state=1 AND eventid = " . $integration;
		$db->setQuery($query);

		return $ticket_types = $db->loadAssocList();
	}

	/**
	 * Book tickets based on data
	 *
	 * @param   integer  $userid        eventid
	 * @param   string   $profile_type  profile_type
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function getUserMobile($userid, $profile_type = 'joomla')
	{
		PluginHelper::importPlugin('system');
		$data1 = Factory::getApplication()->triggerEvent('onJtBeforeMobileforReminder', array($userid, $profile_type));

		if (!empty($data1['0']))
		{
			return $mobile_no = $data1['0'];
		}

		/* @TODO for bajaj add this to plugin
		 if($userid)
		 {
		 $query = "SELECT mobile FROM #__tjlms_user_xref WHERE `user_id`=".$userid;
		 $db->setQuery($query);

		 return $mobile_no = $db->loadResult();
		 }*/

		$db    = Factory::getDbo();
		$query = "SELECT profile_value FROM #__user_profiles WHERE `profile_key` like 'profile.phone'";
		$db->setQuery($query);

		return $mobile_no = $db->loadResult();
	}

	/**
	 * Generate random no
	 *
	 * @param   integer  $length  length for field
	 * @param   string   $chars   Allowed characters
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function rand_str($length = 32, $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890')
	{
		// Length of character list
		$chars_length = (strlen($chars) - 1);

		// Start our string
		$string = $chars[rand(0, $chars_length)];

		// Generate random string
		for ($i = 1; $i < $length; $i = strlen($string))
		{
			// Grab a random character from our list
			$r = $chars[rand(0, $chars_length)];

			// Make sure the same two characters don't appear next to each other
			if ($r != $string[$i - 1])
			{
				$string .= $r;
			}
		}

		// Return the string
		return $string;
	}

	/**
	 * Send Reminders to client
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function sendReminder()
	{
		$model    = $this->getModel('orders');
		$response = $model->sendReminder();
	}

	/**
	 * This function fixes available seats for ticket types(if only one ticket type present)
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function fixSeats()
	{
		$db = Factory::getDbo();

		$query = "SELECT SUM( orderd.ticketscount ) AS seats, xref.eventid,xref.id
		FROM  #__jticketing_order AS orderd,  #__jticketing_integration_xref AS xref
		WHERE STATUS =  'C'
		AND orderd.event_details_id = xref.id
		GROUP BY orderd.event_details_id";
		$db->setQuery($query);
		$eventlists = $db->loadObjectList();

		if (!empty($eventlists))
		{
			foreach ($eventlists AS $events)
			{
				$obj          = new StdClass;
				$obj->eventid = $events->id;
				$query        = "SELECT count(`id`) FROM #__jticketing_types WHERE eventid=" . $obj->eventid;
				$db->setQuery($query);
				$records = 0;
				$records = $db->loadResult();

				if ($records == 1)
				{
					$query        = "SELECT available FROM #__jticketing_types WHERE eventid=" . $obj->eventid;
					$db->setQuery($query);
					$available  = $db->loadResult();
					echo "count==" . $obj->count = $available - $events->seats;

					if (!$db->updateObject('#__jticketing_types', $obj, 'eventid'))
					{
					}
				}
			}
		}
	}

	/**
	 * Send Pending ticket Emails to purchaser
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function sendPendingTicketEmails()
	{
		$db = Factory::getDbo();

		$input                  = Factory::getApplication()->getInput();
		$Jticketingmainhelper   = new Jticketingmainhelper;
		$com_params             = ComponentHelper::getParams('com_jticketing');
		$integration            = $com_params->get('integration');
		$pkey_for_pending_email = $com_params->get("pkey_for_pending_email");
		$input                  = Factory::getApplication()->getInput();
		$private_keyinurl       = $input->get('pkey', '', 'STRING');
		$passed_start           = $input->get('start_date', '', 'STRING');
		$passed_end             = $input->get('end_date', '', 'STRING');
		$accessible_groups_str  = $input->get('accessible_groups', '', 'STRING');
		$accessible_groups      = explode(",", $accessible_groups_str);
		$event_ids              = $input->get('event_id', '', 'STRING');
		$today_date             = date('Y-m-d H:m:s');
		$skipuser = '';

		if ($pkey_for_pending_email != $private_keyinurl)
		{
			echo "You are Not authorized To send Pending mails";

			return;
		}

		$pending_email_batch_size = $com_params->get("pending_email_batch_size");
		$enb_batch                = $com_params->get("pending_email_enb_batch");
		$path                     = JPATH_ROOT . '/components/com_jticketing/helpers/main.php';
		$jticketingfrontendhelper = JPATH_ROOT . '/components/com_jticketing/helpers/frontendhelper.php';
		JLoader::register('JticketingMailHelper', JPATH_SITE . '/components/com_jticketing/helpers/mail.php');

		if (!class_exists('jticketingfrontendhelper'))
		{
			JLoader::register('jticketingfrontendhelper', $jticketingfrontendhelper);
			JLoader::load('jticketingfrontendhelper');
		}

		if (!class_exists('jticketingmainhelper'))
		{
			JLoader::register('jticketingmainhelper', $path);
			JLoader::load('jticketingmainhelper');
		}

		$Jticketingfrontendhelper = new Jticketingfrontendhelper;
		$Jticketingfrontendhelper->loadHelperClasses();
		$jticketingmainhelper = new jticketingmainhelper;
		$clientnm             = JT::getIntegration();

		if ($integration == 2)
		{
			$query = "SELECT orderd.*,xref.eventid AS eventid
			FROM  #__jticketing_order AS orderd,#__jticketing_events AS events,#__jticketing_integration_xref AS xref
			WHERE orderd.STATUS =  'C' AND orderd.ticket_email_sent=0
			AND orderd.event_details_id = xref.id AND xref.eventid=events.id AND  DATE(NOW()) <= DATE(`startdate`)";

			if ($passed_start)
			{
				$query .= " AND DATE(`startdate`)>='$passed_start' ";
			}

			if ($passed_end)
			{
				$query .= " AND DATE(`startdate`)<='$passed_end'";
			}

			if ($event_ids)
			{
				$event_id_arr = explode(",", $event_ids);
				$event_id_str = implode("','", $event_id_arr);
				$query .= " AND events.id IN ('$event_id_str')";
			}
		}
		else
		{
			$db = Factory::getDbo();
			$query = $db->getQuery(true);

			// Select the required fields from the table.
			$query->select('DISTINCT orderd.*');
			$query->select('xref.eventid');
			$query->from('`#__jticketing_order` AS orderd');
			$query->join('LEFT', $db->quoteName('#__jticketing_integration_xref', 'xref') . 'ON (' .
							$db->quoteName('orderd.event_details_id') . '=' . $db->quoteName('xref.id') . ')');
			$query->where($db->quoteName('xref.source') . '= ' . $db->quote($clientnm));
			$query->where($db->quoteName('orderd.status') . '= ' . $db->quote('C'));
			$query->where($db->quoteName('orderd.ticket_email_sent') . '= 0');
		}

		if ($enb_batch == '1')
		{
			$query .= " LIMIT {$pending_email_batch_size}";
		}

		$db->setQuery($query);
		$orders = $db->loadObjectList();
		$result = array();
		$i      = 0;

		foreach ($orders AS $orderdata)
		{
			$allow_email = $email = 0;
			

			if ($integration == 3)
			{
				if (JT::event($orderdata->eventid)->getEndDate() && JT::event($orderdata->eventid)->getEndDate() < time())
				{
					continue;
				}
			}
			else if ($integration != 2 && (date(JT::event($orderdata->eventid)->getStartDate()) < $today_date))
			{
				continue;
			}

			if ($accessible_groups_str)
			{
				$uid = $orderdata->user_id;
				$query = $db->getQuery(true);
				$query
				->select('title')->from('#__usergroups')
				->where('id IN (' . implode(',', array_values(Factory::getUser($uid)->groups)) . ')');
				$db->setQuery($query);
				$groups = $db->loadColumn();
				$allow_email = count(array_intersect($groups, $accessible_groups));

				if ($allow_email)
				{
					$email = JticketingMailHelper::sendmailnotify($orderdata->id, 'afterordermail');
				}
				else
				{
					$skipuser = 1;
				}
			}
			else
			{
				$email = JticketingMailHelper::sendmailnotify($orderdata->id, 'afterordermail');
			}

			if ($email['success'])
			{
				$obj                    = new StdClass;
				$obj->id                = $orderdata->id;
				$obj->ticket_email_sent = 1;

				if ($db->updateObject('#__jticketing_order', $obj, 'id'))
				{
				}

				echo "==Mailsent Successfully===";
				echo "<br/>";
				echo "<br/>";
				echo "To Email===" . $orderdata->email;
				echo "<br/>";
				echo "<br/>";
			}

			if ($skipuser)
			{
				echo "===Skipping since group is==" . implode(",", $groups);
			}

			$i++;
		}
	}

	/**
	 * This will add pending entries to reminder queue
	 *
	 * @return  void
	 *
	 * @since   1.0
	 *
	 * @deprecated 3.2.0 Use JT::model('orders')->addPendingEntriesToQueue(); instead
	 */
	public function addPendingEntriesToQueue()
	{
		$db    = Factory::getDbo();
		$input = Factory::getApplication()->getInput();

		$query = "SELECT orderd.*,xref.eventid AS eventid
		FROM  #__jticketing_order AS orderd,  #__jticketing_integration_xref AS xref
		WHERE STATUS =  'C'
		AND orderd.event_details_id = xref.id";
		$db->setQuery($query);
		$orders = $db->loadObjectList();

		foreach ($orders AS $orderdata)
		{
			$order = JT::order($orderdata->id);
			$event = JT::event()->loadByIntegration($order->event_details_id);

			// TODO insertion
			$eventData               = array();
			$eventData['eventId']    = $event->getId();
			$eventData['eventTitle'] = $event->getTitle();
			$eventData['startDate']  = $event->getStartDate();
			$eventData['endDate']    = $event->getEndDate();

			// Insert todo or update todo
			$eventData['assigned_to'] = $order->user_id;

			// Delete todo related to that order
			$this->jtTriggerOrder->onOrderStatusChange($order, $eventData);
		}
	}
}
