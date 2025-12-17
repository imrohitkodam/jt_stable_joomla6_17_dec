<?php
declare(strict_types=1);

/**
 * @package     JTicketing
 * @subpackage  com_jticketing
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Log\Log;
Use Joomla\String\StringHelper;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Database\ParameterType;

/**
 * Model for order for creating order and process order
 *
 * @package     JTicketing
 * @subpackage  component
 * @since       1.0
 */
class JticketingModelorders extends ListModel
{
	public $result;

	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     JController
	 * @since   1.6
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
					'id', 'o.`id`',
					'order_id', 'o.`order_id`',
					'event_details_id', 'o.`event_details_id`',
					'status', 'o.`status`',
					'events',
					'datefilter',
					'processor', 'o.processor',
			);
		}

		$this->jticketingmainhelper = new jticketingmainhelper;

		$jTOrderHelper = JPATH_ROOT . '/components/com_jticketing/helpers/order.php';

		if (!class_exists('JticketingOrdersHelper'))
		{
			JLoader::register('JticketingOrdersHelper', $jTOrderHelper);
			JLoader::load('JticketingOrdersHelper');
		}

		$this->jTOrderHelper = new JticketingOrdersHelper;
		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   Elements order
	 * @param   string  $direction  Order direction
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// Initialise variables.
		$app = Factory::getApplication();

		// Load the filter state.
		$search = $app->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		// Get pagination request variables
		$limit      = (int) $app->getUserStateFromRequest('global.list.limit', 'limit', $app->get('list_limit'));
		$limitstart = (int) $app->getUserStateFromRequest('limitstart', 'limitstart', 0);

		// In case limit has been changed, adjust it
		$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);
		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);

		$userId = $app->getUserStateFromRequest($this->context . '.filter.user', 'filter_user', '', 'string');
		$this->setState('filter.user', (int) $userId);

		$orderPaymentStatus = array('I', 'P', 'C', 'E', 'UR', 'RF' ,'CRV', 'RV', 'D' );

		if ($app->isClient('administrator'))
		{
			$eventId = $app->getUserStateFromRequest($this->context . '.filter.events', 'filter_event', '', 'string');
			$this->setState('filter.events', (int) $eventId);

			$searchPaymentStatus = $app->getUserStateFromRequest($this->context . '.filter.status', 'filter_status', '', 'string');

			if (in_array($searchPaymentStatus, $orderPaymentStatus))
			{
				$this->setState('filter.status', (string) $searchPaymentStatus);
			}
		}
		else
		{
			$eventId = $app->getUserStateFromRequest($this->context . '.search_event', 'search_event', '', 'string');
			$this->setState('search_event', (int) $eventId);

			$searchPaymentStatus = $app->getUserStateFromRequest($this->context . '.search_paymentStatus', 'search_paymentStatus', '', 'string');

			if (in_array($searchPaymentStatus, $orderPaymentStatus))
			{
				$this->setState('search_paymentStatus', (string) $searchPaymentStatus);
			}
		}

		$orderDir = array('asc', 'desc');

		$filterOrder     = $app->getUserStateFromRequest($this->context . '.filter_order', 'filter_order', 'id', 'cmd');
		$this->setState('filter_order', $filterOrder);

		$filterOrderDir = $app->getUserStateFromRequest($this->context . '.filter_order_Dir', 'filter_order_Dir', 'desc', 'word');

		if (in_array($filterOrderDir, $orderDir))
		{
			$this->setState('filter_order_Dir', $filterOrderDir);
		}

		// List state information.
		parent::populateState('id', 'desc');
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return   JDatabaseQuery
	 *
	 * @since    1.6
	 */
	protected function getListQuery()
	{
		$app         = Factory::getApplication();
		$layout      = $app->getInput()->get('layout', '', 'STRING');
		$user        = Factory::getUser();
		$integration = JT::getIntegration(true);

		// Create a new query object.
		$db = $this->getDatabase();
		$query = $db->getQuery(true);
		$query->select(
				array(
						'o.transaction_id as transaction_id,o.order_tax,o.coupon_discount,
			o.order_id as order_id,i.eventid AS eventid, o.id,o.name,o.processor,o.cdate,o.amount,
			o.fee,o.status,o.ticketscount,o.original_amount,o.event_details_id,i.eventid
			as evid,o.amount as paid_amount,o.coupon_code,user.firstname,user.lastname'
				)
				);
		$query->from($db->qn('#__jticketing_order', 'o'));
		$query->join('LEFT', $db->qn('#__jticketing_integration_xref', 'i') . 'ON (' . $db->qn('o.event_details_id') . ' = ' . $db->qn('i.id') . ')');
		$query->join('LEFT', $db->qn('#__jticketing_users', 'user') . 'ON (' . $db->qn('o.id') . ' = ' . $db->qn('user.order_id') . ')');

		if ($integration == 1)
		{
			$query->select('comm.title');
			$query->join('LEFT', $db->qn('#__community_events', 'comm') . 'ON (' . $db->qn('comm.id') . ' = ' . $db->qn('i.eventid') . ')');
			$query->where($db->qn('i.source') . ' = ' . $db->quote("com_community"));
		}
		elseif ($integration == 2)
		{
			$query->select('event.title');
			$query->join('LEFT', $db->qn('#__jticketing_events', 'event') . 'ON (' . $db->qn('event.id') . ' = ' . $db->qn('i.eventid') . ')');
			$query->where($db->qn('i.source') . ' = ' . $db->quote("com_jticketing"));
		}
		elseif ($integration == 3)
		{
			$query->select('je.summary AS title');
			$query->join('LEFT', $db->qn('#__jevents_repetition', 'rep') . 'ON (' . $db->qn('i.eventid') . ' = ' . $db->qn('rep.rp_id') . ')');
			$query->join('LEFT', $db->qn('#__jevents_vevent', 'jv') . 'ON (' . $db->qn('jv.ev_id') . ' = ' . $db->qn('rep.eventid') . ')');
			$query->join('LEFT', $db->qn('#__jevents_vevdetail', 'je') . 'ON (' . $db->qn('je.evdet_id') . ' = ' . $db->qn('rep.eventdetail_id') . ')');
			$query->where($db->qn('i.source') . ' = ' . $db->quote("com_jevents"));
		}
		elseif ($integration == 4)
		{
			$query->select('es.title');
			$query->join('LEFT', $db->qn('#__social_clusters', 'es') . 'ON (' . $db->qn('es.id') . ' = ' . $db->qn('i.eventid') . ')');
			$query->where($db->qn('i.source') . ' = ' . $db->quote("com_easysocial"));
		}

		// Filter by search in title
		$search = $this->getState('filter.search');

		if ($app->isClient('administrator'))
		{
			$eventId = $this->getState('filter.events')? $this->getState('filter.events') : $this->getState('user_created_events');
			$searchPaymentStatus = $this->getState('filter.status');
		}
		else
		{
			$eventId = $this->getState('search_event');
			$searchPaymentStatus = $this->getState('search_paymentStatus');
		}

		$date = $this->getState('filter.datefilter');
		$filterOrder 	= $this->getState('list.ordering', 'id');
		$filterOrderDir = $this->getState('list.direction', 'desc');

		if (!empty($search))
		{
			if (stripos($search, 'o.id:') === 0)
			{
				$query->where('o.order_id = ' . (int) substr($search, 4));
			}
			else
			{
				$search = $db->Quote('%' . $db->escape($search, true) . '%');
				$query->where('( o.order_id LIKE ' . $search . ' )');
			}
		}

		$userFilter = $this->getState('filter.user');

		if ($userFilter)
		{
			$user = Factory::getUser($userFilter);
		}

		if (($user and $layout == 'my') || $userFilter)
		{
			$query->where('(user.user_id=' . $user->id . ')');
		}

		// If all orders view show only events that are created by that user
		if ($user and $layout == 'default')
		{
			$query->where('(i.userid=' . (int) $user->id . ')');
		}

		$source = JT::getIntegration();

		if ($source)
		{
			$query->where('(i.source=' . $db->quote($source) . ')');
		}

		if (!empty($date))
		{
			$query->where('DATE(`cdate`)=' . $db->quote($date));
		}

		if (!empty($eventId))
		{
			$query->where('(o.event_details_id=' . (int) $eventId . ')');
		}
		else
		{
			// If layout = my find events for which user has made orders
			if ($layout == 'default')
			{
				// If layout = default find all events which are created by that user
				$eventsModel = JT::model('events', array("ignore_request" => true));
				$eventsModel->setState('filter.created_by', $user->id);
				$eventlist = $eventsModel->getItems();

				if (!empty($eventList))
				{
					$eventIntegId = array();

					foreach ($eventList as $key => $event)
					{
						$eventIntegId[] = JT::event($event->id)->integrationId;
					}

					if (!empty($eventIntegId))
					{
						$query->where($db->quoteName('o.event_details_id') . ' IN ("' . implode('","', $eventIntegId) . '")');
					}
				}
			}
		}

		if (!empty($searchPaymentStatus))
		{
			$statusArray = array('P', 'C', 'D', 'E', 'UR', 'RF', 'CRV', 'RV', 'I');
			$paymentStatus = StringHelper::strtoupper($searchPaymentStatus);

			if (!in_array($paymentStatus, $statusArray))
			{
				// If any invalid status then set C as default search value
				$paymentStatus = 'C';
			}

			$query->where($db->quoteName('o.status') . ' LIKE ' . $db->quote($paymentStatus));
		}

		if (!empty($filterOrder))
		{
			$db = $this->getDatabase();
			$columnInfo = $db->getTableColumns('#__jticketing_order');

			foreach ($columnInfo as $key => $value)
			{
				$allowedFields[] = $key;
			}

			if (in_array($filterOrder, $allowedFields))
			{
				$query->order($db->escape($filterOrder) . ' ' . $db->escape($filterOrderDir));
			}
		}

		return $query;
	}

	/**
	 * Method to get an array of data items
	 *
	 * @return  mixed An array of data on success, false on failure.
	 */
	public function getItems()
	{
		$items = parent::getItems();

		return $items;
	}

	/**
	 * Get data for a order
	 *
	 * @return  object  $this->result  payout data
	 *
	 * @since   1.0
	 */
	public function getData()
	{
		if (empty($orderData))
		{
			$query = $this->getListQuery();
			$orderData = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
		}

		return $orderData;
	}

	/**
	 * get total count
	 *
	 * @return  int  $this->_total  total count
	 *
	 * @since   1.0
	 */
	public function getTotal()
	{
		// Lets load the content if it doesn’t already exist
		if (empty($this->_total))
		{
			$query        = $this->getListQuery();
			$this->_total = $this->_getListCount($query);
		}

		return $this->_total;
	}

	/**
	 * get eventname
	 *
	 * @return  String   event title
	 *
	 * @since   1.0
	 */
	public function getEventName()
	{
		$input     = Factory::getApplication()->getInput();
		$eventid   = $input->get('event', '', 'INT');
		$query     = $this->jticketingmainhelper->getEventName($eventid);
		$this->_db->setQuery($query);
		$this->_data = $this->_db->loadResult();

		return JT::event($eventId, $integration)->getTitle();
	}

	/**
	 * Get Event details
	 *
	 * @return  object  $this->result  event data
	 *
	 * @since   1.0
	 */
	public function Eventdetails()
	{
		$input     = Factory::getApplication()->getInput();
		$mainframe = Factory::getApplication();
		$eventid   = $input->get('event', '', 'INT');
		$db = $this->getDatabase();
		$query = $db->getQuery(true);
		$query->select($db->quoteName('title'));
		$query->from($db->quoteName('#__community_events'));
		$query->where($db->quoteName('id') . ' = :eventid')
			->bind(':eventid', $eventid, ParameterType::INTEGER);
		$db->setQuery($query);
		$this->result = $db->loadResult();

		return $this->result;
	}

	/**
	 * Store order
	 *
	 * @param   integer  $post  post data for order
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function store($post)
	{
		$integration         = JT::getIntegration(true);
		$db                  = $this->getDatabase();
		$res                 = new stdClass;
		$res->id             = $post->get('order_id');
		$res->mdate          = date("Y-m-d H:i:s");
		$res->transaction_id = md5($post->get('order_id'));
		$res->payee_id       = $post->get('buyer_email');
		$res->status         = trim($post->get('pstatus'));
		$res->processor      = $post->get('processor');

		if (!$db->updateObject('#__jticketing_order', $res, 'id'))
		{
			return false;
		}

		if ($post->get('pstatus') == 'C')
		{
			if ($integration == 1)
			{
				$query = "SELECT i.id, i.eventid
				FROM #__jticketing_integration_xref AS i
				LEFT JOIN  #__jticketing_order AS o ON o.event_details_id = i.id
				WHERE o.id =" . $post->get('order_id');
				$db->setQuery($query);
				$eventid = $db->loadObjectlist();
			}

			if ($integration == 1)
			{
				$query = "SELECT type_id,count(ticketcount) as ticketcounts
					FROM #__jticketing_order_items where order_id=" . $post->get('order_id') . " GROUP BY type_id";
			}
			elseif ($integration = 2)
			{
				$query = "SELECT type_id,count(ticketcount) as ticketcounts
					FROM #__jticketing_order_items where order_id=" . $post->get('order_id') . " GROUP BY type_id";
			}

			$db->setQuery($query);
			$orderDetails = $db->loadObjectlist();

			foreach ($orderDetails as $orderDetail)
			{
				$typeData = '';
				$resType  = new stdClass;

				if ($integration == 1)
				{
					$query = "SELECT count
					FROM #__jticketing_types where id=" . $orderDetail->type_id;
				}
				elseif ($integration == 2)
				{
					$query = "SELECT count
					FROM #__jticketing_types where id=" . $orderDetail->type_id;
				}

				$db->setQuery($query);
				$typeData       = $db->loadResult();
				$resType->id    = $orderDetail->type_id;
				$resType->count = $typeData - $orderDetail->ticketcounts;

				if ($integration == 1)
				{
					$db->updateObject('#__community_events', $resType, 'id');
				}
				elseif ($integration == 2)
				{
					$db->updateObject('#__jticketing_types', $resType, 'id');
				}
			}
		}
	}

	/**
	 * Delete order
	 *
	 * @param   Array  &$pks  id of jticketing_order table to delete
	 *
	 * @return  boolean
	 *
	 * @since   1.0.0
	 */
	public function delete(&$pks)
	{
		foreach ($pks as $i => $id)
		{
			$order = JT::order($id);

			if (!$order->delete())
			{
				// Prune items that you can't change.
				unset($pks[$i]);
			}
		}

		return true;
	}

	/**
	 * Decrease ticket available seats
	 *
	 * @param   integer  $order_id  id of jticketing_order table
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function eventsTypesCountDecrease($order_id)
	{
		$db   = $this->getDatabase();
		$data = $this->jticketingmainhelper->getOrder_ticketcount($order_id, 0);
		$db->setQuery($data);
		$result  = $db->loadobjectlist();

		foreach ($result as $tempResult)
		{
			// Update the Type ticeket count
			$db = $this->getDatabase();
			$query = $db->getQuery(true);
			$query->update($db->quoteName('#__jticketing_types'))
				->set($db->quoteName('count') . ' = ' . $db->quoteName('count') . ' - :cnt')
				->where($db->quoteName('id') . ' = :type_id')
				->bind(':cnt', $tempResult->cnt, ParameterType::INTEGER)
				->bind(':type_id', $tempResult->type_id, ParameterType::INTEGER);
			$db->setQuery($query);
			$db->execute();
		}
	}

	/**
	 * Increase ticket available seats
	 *
	 * @param   integer  $order_id  id of jticketing_order table
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function eventsTypesCountIncrease($order_id)
	{
		$order       = JT::order($order_id);
		$integration = JT::getIntegration(true);

		if (!empty($order))
		{
			// Get the order items.
			$orderDetails 	= $order->getItems();

			foreach ($orderDetails as $orderdetail)
			{
				$ticketType 		= JT::Tickettype($orderdetail->type_id);

				if (!$ticketType->unlimited_seats)
				{
					$ticketType->count 	= max(0, $ticketType->count + $orderdetail->ticketcount);

					$ticketType->save();
				}
			}

			if ($integration == 1)
			{
				$this->unJoinMembers($order_id);
			}
		}
	}

	/**
	 * Get order status based on order id
	 *
	 * @param   integer  $order_id  id of jticketing_order table
	 *
	 * @return  string order status like C,P
	 *
	 * @since   1.0
	 */
	public function getOrderStatus($order_id)
	{
		$db = $this->getDatabase();
		$query = $db->getQuery(true);
		$query->select($db->quoteName('status'));
		$query->from($db->quoteName('#__jticketing_order'));
		$query->where($db->quoteName('id') . ' = :order_id')
			->bind(':order_id', $order_id, ParameterType::INTEGER);
		$db->setQuery($query);

		return $db->loadResult();
	}

	/**
	 * Update order status based on order id
	 *
	 * @param   integer  $orderId    id of jticketing_order table
	 *
	 * @param   string   $status     status to change
	 *
	 * @param   string   $sendEmail  sendEmail on status change
	 *
	 * @return  boolean
	 *
	 * @since   1.0
	 */
	public function updateOrderStatus($orderId, $status, $sendEmail =null)
	{
		JLoader::register('JticketingMailHelper', JPATH_SITE . '/components/com_jticketing/helpers/mail.php');
		$order = JT::order($orderId);

		$validStatuses        = array();

		// Get all order statuses
		$statusArray = $this->getOrderStatusArray();

		// Get the valid order statuses
		$validOrderStatuses = $this->getValidOrderStatus($order->getStatus(), $statusArray);

		// Loop through the array to get the order statuses
		if (!empty($validOrderStatuses))
		{
			foreach ($validOrderStatuses as $key => $validOrderStatus)
			{
				$validStatuses[] = $key;
			}
		}

		// Check if the current order status is present in the valid order statuses
		if (!in_array($status, $validStatuses))
		{
			return false;
		}

		$db    = $this->getDatabase();
		$query = $db->getQuery(true);
		$query->update($db->quoteName('#__jticketing_order'));
		$query->set($db->quoteName('status') . ' = :status')
			->where($db->quoteName('id') . ' = :order_id')
			->bind(':status', $status, ParameterType::STRING)
			->bind(':order_id', $orderId, ParameterType::INTEGER);
		$db->setQuery($query);
		$db->execute();

		$input   = Factory::getApplication()->getInput();
		$post    = $input->post;
		$comment = $post->get('comment', '', 'STRING');
		$orderItemsId = $post->get('order_items_id');

		if (isset($orderItemsId) && isset($comment))
		{
			$query = $db->getQuery(true);
			$query->update($db->quoteName('#__jticketing_order_items'));
			$query->set($db->quoteName('comment') . ' = ' . $db->quote($comment));
			$query->where($db->quoteName('id') . ' = ' . (int) $orderItemsId);
			$db->setQuery($query);
			$db->execute();
		}

		$tjvendorFrontHelper            = new TjvendorFrontHelper;
		$order                          = JT::order($orderId);
		$JticketingModelIntegrationxref = JT::model('Integrationxref');
		$integrationDetails             = $JticketingModelIntegrationxref->getItem($order->event_details_id);

		$orderDetails                     = array();
		$orderDetails['vendor_id']        = $integrationDetails->vendor_id;
		$orderDetails['status']           = $status;
		$orderDetails['client']           = "com_jticketing";
		$orderDetails['client_name']      = Text::_('COM_JTICKETING');
		$orderDetails['order_id']         = $order->order_id;
		$orderDetails['amount']           = $order->getAmount(true);
		$orderDetails['customer_note']    = $order->customer_note;
		$orderDetails['fee_amount']       = $order->getFee(true);
		$orderDetails['transaction_time'] = $order->cdate;

		// Update attendee status to Approve when order get completed
		JT::order($orderId)->updateAttendeeStatus();

		// Change sent column ub backend queue
		if ($status != 'C')
		{
			$query = $db->getQuery(true);
			$query->update($db->quoteName('#__jticketing_queue'));
			$query->set($db->quoteName('sent') . ' = 4');
			$query->where($db->quoteName('order_id') . ' = ' . (int) $orderId);
			$query->where($db->quoteName('sent') . "IN ('0','3')");
			$db->setQuery($query);
			$db->execute();

			$query = $db->getQuery(true);
			$query->select($db->quoteName(array('id')));
			$query->from($db->quoteName('#__jticketing_order_items'));
			$query->where($db->quoteName('order_id') . ' = ' . (int) $orderId);
			$db->setQuery($query);
			$orderItems    = $db->loadObjectlist();

			if ($orderItems)
			{
				foreach ($orderItems AS $oitems)
				{
					// Delete From Checkin Details Table
					$query = $db->getQuery(true);
					$query->delete($db->quoteName('#__jticketing_checkindetails'));
					$query->where($db->quoteName('ticketid') . ' = ' . (int) $oitems->id);
					$db->setQuery($query);
					$db->execute();
				}
			}
		}
		else
		{
			// Update coupon count
			if ($order->getCouponCode())
			{
				$table = JT::table('coupon');

				if ($table->load(array('code' => $order->getCouponCode())))
				{
					$couponform       = JT::Model('couponform');
					$couponData       = $couponform->getItem($table->id);
					$couponData->used = $couponData->used + 1;
					$couponform->save((array) $couponData);
				}
			}

			if ($status == ' C')
			{
				$tjvendorFrontHelper->addEntry($orderDetails);
			}

			$query = $db->getQuery(true);
			$query->update($db->quoteName('#__jticketing_queue'));
			$query->set($db->quoteName('sent') . ' = 0');
			$query->where($db->quoteName('order_id') . ' = ' . (int) $orderId);
			$query->where($db->quoteName('sent') . '= 4');
			$db->setQuery($query);
			$db->execute();
		}

		$comParams = ComponentHelper::getParams('com_jticketing');

		// Send order status change

		JticketingMailHelper::sendOrderStatusEmail($orderId, $status);

		// Trigger After Process Payment
		PluginHelper::importPlugin('system');

		// Old Trigger
		Factory::getApplication()->triggerEvent('onJtAfterProcessPayment', array($post, $orderId, $pg_plugin = ''));

		return true;
	}

	/**
	 * Send remiders to client
	 *
	 * @param   int  $plug_call  this is 1 if function called from jticketing system plugin
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function sendReminder($plug_call = 0)
	{
		$JticketingMainHelper = new Jticketingmainhelper;
		$comParams           = ComponentHelper::getParams('com_jticketing');
		$db                  = $this->getDatabase();
		$pkeyForReminder   = $comParams->get("pkey_for_reminder");
		$sendAutoReminders = $comParams->get("send_auto_reminders");

		if ($sendAutoReminders != 1)
		{
			return false;
		}

		$input            = Factory::getApplication()->getInput();
		$privateKeyInUrl = $input->get('pkey', '', 'STRING');
		$returnMsg       = array();

		if ($pkeyForReminder != $privateKeyInUrl)
		{
			echo "You are Not authorized To send mails";

			return;
		}
		else
		{
			if ($plug_call == 0)
			{
				echo "*****************************<br />";
				echo "Sending Reminders <br />";
				echo "----------------------------- <br />";
			}

			$batchSizeReminders = $comParams->get("batch_size_reminders");
			$enbBatch            = $comParams->get("enb_batch");

			// Send  manual emails(which are added to queue from backend attendee list view)
			$returnMsg[] = $this->sendManualEmail($enbBatch, $batchSizeReminders);

			// Send normal reminder emails so no need to pass flag
			$returnMsg[] = $this->sendEmailReminder($enbBatch, $batchSizeReminders);
			$returnMsg[] = $this->sendSMSReminder($enbBatch, $batchSizeReminders);

			// Add entries to log files
			$tableMsg = '';
			$tableMsg .= "<table>";

			if (empty($returnMsg['0']) and empty($returnMsg['1']))
			{
				if ($plug_call == 0)
				{
					echo "===No records found==";
				}

				return;
			}

			foreach ($returnMsg as $msgs)
			{
				foreach ($msgs AS $msg)
				{
					// $tableMsg .= "<tr><td align=\"center\"></td>";
					$tableMsg .= "<td>" . $msg["msg"] . "</td>";
					Log::addLogger(array('text_file' => 'com_jticketing.reminder.php'), Log::ALL, $msg["msg"]);
					$tableMsg .= "</tr>";
				}
			}

			if ($plug_call == 0)
			{
				$tableMsg .= "</table>";
				echo $tableMsg;
			}
		}
	}

	/**
	 * Send SMS Reminders
	 *
	 * @param   string   $enbBatch            enable batch or not
	 * @param   integer  $batchSizeReminders  status to change
	 * @param   integer  $sent_delayed        if message is delayed give it preference
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function sendManualEmail($enbBatch, $batchSizeReminders, $sent_delayed = 0)
	{
		JLoader::register('JticketingMailHelper', JPATH_SITE . '/components/com_jticketing/helpers/mail.php');
		$JticketingMainHelper = new Jticketingmainhelper;
		$comParams           = ComponentHelper::getParams('com_jticketing');
		$db                   = $this->getDatabase();
		$returnMsg           = array();
		$query                = "select *
		from #__jticketing_queue l
		WHERE sent=" . $sent_delayed . "  AND reminder_type='manual_email' AND  DATE(NOW()) = DATE(`date_to_sent`)
		order by date_to_sent desc";

		if ($enbBatch == '1')
		{
			$query .= " LIMIT {$batchSizeReminders}";
		}

		$db->setQuery($query);
		$reminderIds = $db->loadObjectList();

		if (empty($reminderIds))
		{
			return array();
		}

		$i = 0;

		foreach ($reminderIds AS $reminder)
		{
			if (!empty($reminder->content) and !empty($reminder->email))
			{
				$query = "";

				// Find event start date
				$query = "SELECT event_details_id from #__jticketing_order where  status='C' AND id=" . $reminder->order_id;
				$db->setQuery($query);
				$eventIntegrationId = $db->loadResult();

				// If order is deleted dont send reminder
				if (!$eventIntegrationId)
				{
					continue;
				}

				$query = "";
				$query = "SELECT eventid from #__jticketing_integration_xref where id=$eventIntegrationId";
				$db->setQuery($query);
				$eventid = $db->loadResult();

				// If order is deleted dont send reminder
				if (!$eventid)
				{
					continue;
				}

				$eventDetails = JT::event($eventid);

				// If event is deleted dont send reminder
				if (!$eventDetails)
				{
					continue;
				}

				// If event unpublished dont send reminder
				if ($eventDetails->getState() != 1)
				{
					continue;
				}

				$todayDate = Factory::getDate();
				$email      = JticketingMailHelper::sendMail($mailfrom, $fromname, $reminder->email, $reminder->subject, $reminder->content, $mode = 1);

				if ($email == 1)
				{
					$returnMsg['success'] = 1;
					$obj                   = new StdClass;
					$obj->id               = $reminder->id;
					$obj->sent             = 1;
					$obj->sent_date        = date("Y-m-d H:i:s");

					if (!$db->updateObject('#__jticketing_queue', $obj, 'id'))
					{
						$returnMsg[$i]['success'] = 0;
						$returnMsg[$i]['msg']     = "Database error";

						return $returnMsg;
					}

					$returnMsg[$i]['success'] = 1;
					$returnMsg[$i]['msg']     = "Successfully Sent to " . $reminder->email;
					$i++;
				}
				else
				{
					// If email not sent set it as delayed
					$obj            = new StdClass;
					$obj->id        = $reminder->id;
					$obj->sent      = 0;
					$obj->sent_date = date("Y-m-d H:i:s");

					if (!$db->updateObject('#__jticketing_queue', $obj, 'id'))
					{
						$returnMsg[$i]['success'] = 0;
						$returnMsg[$i]['msg']     = "Database error";

						return $returnMsg;
					}

					$returnMsg[$i]['success'] = 0;
					$returnMsg[$i]['msg']     = "Failed to sent" . $reminder->email;
					$i++;
				}
			}
		}
	}

	/**
	 * Send Email Reminders
	 *
	 * @param   string   $enbBatch            enable batch or not
	 * @param   integer  $batchSizeReminders  size of batch
	 * @param   integer  $sent_delayed        if message is delayed give it preference
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function sendEmailReminder($enbBatch, $batchSizeReminders, $sent_delayed = 0)
	{
		$db                   = $this->getDatabase();
		$JticketingMainHelper = new Jticketingmainhelper;
		$jtEventHelper        = new jteventHelper;
		$returnMsg           = array();
		$app                  = Factory::getApplication();
		$mailer               = Factory::getMailer();
		$mailfrom             = $app->get('mailfrom');
		$fromname             = $app->get('fromname');
		$sitename             = $app->get('sitename');
		JLoader::register('JticketingMailHelper', JPATH_SITE . '/components/com_jticketing/helpers/mail.php');

		// Delete all entries in queue which are not sent
		$query              = "";
		$input              = Factory::getApplication()->getInput();
		$doNotAddPending = $input->get('do_not_add_pending');
		$debug              = $input->get('jt_debug');

		if (empty($doNotAddPending))
		{
			$this->addPendingEntriesToQueue();
		}

		$query = "";
		$query = "select queue.*,remtypes.replytoemail
		from #__jticketing_queue AS queue ,#__jticketing_reminder_types AS remtypes
		WHERE queue.reminder_type_id=remtypes.id
		AND  remtypes.state=1 AND queue.sent=" . $sent_delayed . "
		AND queue.reminder_type='email' AND  DATE(NOW()) = DATE(`date_to_sent`)
		order by date_to_sent desc";

		if ($enbBatch == '1')
		{
			$query .= " LIMIT {$batchSizeReminders}";
		}

		$db->setQuery($query);
		$reminderIds = $db->loadObjectList();

		if ($debug)
		{
			print_r($reminderIds);
		}

		if (empty($reminderIds))
		{
			return array();
		}

		$i = 0;

		foreach ($reminderIds AS $reminder)
		{
			// Find all reminder data
			$query                   = "";
			$reminder->reminder_type = trim($reminder->reminder_type);
			$reminder->content       = trim($reminder->content);

			if ($reminder->reminder_type == "email" and !empty($reminder->content) and !empty($reminder->email))
			{
				$query = "";

				// Find event start date
				$db    = $this->getDatabase();
				$query = "SELECT event_details_id from #__jticketing_order where  status='C' AND id=" . $reminder->order_id;
				$db->setQuery($query);
				$eventIntegrationId = $db->loadResult($query);

				// If order is deleted dont send reminder
				if (!$eventIntegrationId)
				{
					continue;
				}

				$query = "";
				$query = "SELECT eventid from #__jticketing_integration_xref where id=$eventIntegrationId";
				$db->setQuery($query);
				$eventid = $db->loadResult();

				// If order is deleted dont send reminder
				if (!$eventid)
				{
					continue;
				}

				$eventDetails = JT::event($eventid);

				// If event is deleted dont send reminder
				if (!$eventDetails)
				{
					continue;
				}

				// If event unpublished dont send reminder
				if ($eventDetails->getState() != 1)
				{
					continue;
				}

				$todayDate = date('Y-m-d');

				// Check if date has not passed
				if (strtotime($eventDetails->getStartDate()) < strtotime($todayDate))
				{
					$obj            = new StdClass;
					$obj->id        = $reminder->id;
					$obj->sent      = 2;
					$obj->sent_date = date("Y-m-d H:i:s");

					if (!$db->updateObject('#__jticketing_queue', $obj, 'id'))
					{
						$returnMsg[$i] = array();
						$returnMsg[$i]['success'] = 0;
						$returnMsg[$i]['msg']     = "Database error";

						return $returnMsg;
					}

					continue;
				}

				$toEmailBcc = '';
				PluginHelper::importPlugin('system');
				$resp = Factory::getApplication()->triggerEvent('onJtBeforeReminderEmail', array($reminder->email));

				if (!empty($resp['0']))
				{
					$toEmailBcc = $resp['0'];
				}

				// $email = $JticketingMainHelper->jt_sendmail($reminder->email, $reminder->subject, );
				// $email  = $mailer->sendMail($mailFrom, $fromName, $reminder->email, $reminder->subject, $reminder->content, $mode = 1, $toemail_bcc);

				if ($toEmailBcc)
				{
					$bccStr = explode(",", $toEmailBcc);
				}
				else
				{
					$bccStr = '';
				}

				$sub   = $reminder->subject;

				$email = JticketingMailHelper::sendMail(
						$mailfrom, $fromname, $reminder->email, $sub, $reminder->content,
						$mode = 1, $bcc_str, '', '', $reminder->replytoemail,
						$reminder->replytoemail, ''
						);

				if ($email == 1)
				{
					$returnMsg['success'] = 1;
					$obj                   = new StdClass;
					$obj->id               = $reminder->id;
					$obj->sent             = 1;
					$obj->sent_date        = date("Y-m-d H:i:s");

					if (!$db->updateObject('#__jticketing_queue', $obj, 'id'))
					{
						$returnMsg[$i]['success'] = 0;
						$returnMsg[$i]['msg']     = "Database error";

						return $returnMsg;
					}

					$returnMsg[$i]['success'] = 1;
					$returnMsg[$i]['msg']     = "Successfully Sent to " . $reminder->email;

					// Set flag as 1 if date_to_sent is less than above reminder of same order ID
					$query = "";
					$query = "SELECT id from #__jticketing_queue where sent=0
							AND order_id=" . $reminder->order_id . " AND reminder_type='email'
							AND date_to_sent<='" . $reminder->date_to_sent . "'";
					$db->setQuery($query);
					$queueIDS = $db->loadobjectlist();

					if (isset($queueIDS))
					{
						foreach ($queueIDS AS $queueID)
						{
							$obj            = new StdClass;
							$obj->id        = $queueID->id;
							$obj->sent      = 2;
							$obj->sent_date = date("Y-m-d H:i:s");

							if (!$db->updateObject('#__jticketing_queue', $obj, 'id'))
							{
								$returnMsg[$i]['success'] = 0;
								$returnMsg[$i]['msg']     = "Database error";
							}
						}
					}

					$i++;
				}
				else
				{
					// If email not sent set it as delayed
					$obj            = new StdClass;
					$obj->id        = $reminder->id;
					$obj->sent      = 3;
					$obj->sent_date = date("Y-m-d H:i:s");

					if (!$db->updateObject('#__jticketing_queue', $obj, 'id'))
					{
						$returnMsg[$i]['success'] = 0;
						$returnMsg[$i]['msg']     = "Database error";

						return $returnMsg;
					}

					$returnMsg[$i]['success'] = 0;
					$returnMsg[$i]['msg']     = "Failed to sent" . $reminder->email;
					$i++;
				}
			}
		}

		return $returnMsg;
	}

	/**
	 * Send SMS Reminders
	 *
	 * @param   string   $enbBatch            enable batch or not
	 * @param   integer  $batchSizeReminders  status to change
	 * @param   integer  $sent_delayed        if message is delayed give it preference
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function sendSMSReminder($enbBatch, $batchSizeReminders, $sent_delayed = 0)
	{
		$db                   = $this->getDatabase();
		$JticketingMainHelper = new Jticketingmainhelper;
		$jtEventHelper        = new jteventHelper;
		$returnMsg           = array();

		/* Find only latest reminders of sms type for that event suppose 3 reminders of week,day and month
		 * and if email reminder not sent previously or error occured for monthly,weekly then only send one day reminder
		 */

		$input              = Factory::getApplication()->getInput();
		$doNotAddPending = $input->get('do_not_add_pending');

		if (empty($doNotAddPending))
		{
			// $jtEventHelper->addPendingEntriestoQueue();
		}

		$query = "";
		$query = "select queue.*,remtypes.replytoemail
		from #__jticketing_queue AS queue ,#__jticketing_reminder_types AS remtypes
		WHERE queue.reminder_type_id=remtypes.id
		AND  remtypes.state=1 AND queue.sent=" . $sent_delayed . "
		AND queue.reminder_type='sms' AND  DATE(NOW()) = DATE(`date_to_sent`)
		order by date_to_sent desc";

		if ($enbBatch == '1')
		{
			$query .= " LIMIT {$batchSizeReminders}";
		}

		$db->setQuery($query);
		$reminderIds = $db->loadObjectList();

		if (empty($reminderIds))
		{
			return array();
		}

		$i = 0;

		foreach ($reminderIds AS $reminder)
		{
			/*Find all reminder data
			 $query = "";
			 $query = "SELECT * from #__jticketing_queue where  id=" . $reminder_id->id;
			 $db->setQuery($query);
			 $reminders = $db->loadObjectList();*/

			// Foreach ($reminders AS $reminder)
			{
				$reminder->content = trim($reminder->content);

				if ($reminder->reminder_type == 'sms' and !empty($reminder->content) and !empty($reminder->mobile_no))
				{
					// Find event start date
					$query = "";
					$query = "SELECT event_details_id from #__jticketing_order where
				status='C' AND id=" . $reminder->order_id;
					$db->setQuery($query);
					$eventIntegrationId = $db->loadResult($query);

					// If order is deleted dont send reminder
					if (!$eventIntegrationId)
					{
						continue;
					}

					$query = "";
					$query = "SELECT eventid from #__jticketing_integration_xref where id=" . $eventIntegrationId;
					$db->setQuery($query);
					$eventid = $db->loadResult($query);

					// If order is deleted dont send reminder
					if (!$eventid)
					{
						continue;
					}

					$eventDetails = JT::event($eventid);

					// If event is deleted dont send reminder
					if (!$eventDetails)
					{
						continue;
					}

					// If event is unpublished do not send reminder
					if ($eventDetails->getState() != 1)
					{
						continue;
					}

					$today_date = Factory::getDate();

					// Check if date has not passed
					if (strtotime($eventDetails->getStartDate()) < strtotime($today_date))
					{
						$obj            = new StdClass;
						$obj->id        = $reminder->id;
						$obj->sent      = 2;
						$obj->sent_date = date("Y-m-d H:i:s");

						if (!$db->updateObject('#__jticketing_queue', $obj, 'id'))
						{
							$returnMsg[$i]['success'] = 0;
							$returnMsg[$i]['msg']     = "Database error";

							return $returnMsg;
						}

						continue;
					}

					$vars            = new StdClass;
					$vars->mobile_no = trim($reminder->mobile_no);
					$params          = ComponentHelper::getParams('com_jticketing');

					$smsGateways = $params->get('smsgateways');
					PluginHelper::importPlugin('tjsms', $smsGateways);
					$res = Factory::getApplication()->triggerEvent($smsGateways . 'send_message', array($reminder->content,$vars));

					if (!empty($res[0]))
					{
						$response = $res[0];
					}

					if (!empty($response))
					{
						$obj            = new StdClass;
						$obj->id        = $reminder->id;
						$obj->sent      = 1;
						$obj->sent_date = date("Y-m-d H:i:s");

						if (!$db->updateObject('#__jticketing_queue', $obj, 'id'))
						{
							$returnMsg[$i]['success'] = 0;
							$returnMsg[$i]['msg']     = "Database error";

							return $returnMsg;
						}

						// Set flag as 1 if date_to_sent is less than above reminder of same order ID
						$query = "";
						$query = "SELECT id from #__jticketing_queue where sent=0
						AND order_id=" . $reminder->order_id . "
						AND reminder_type='email' AND date_to_sent<='" . $reminder->date_to_sent . "'";
						$db->setQuery($query);
						$queueIDS = $db->loadobjectlist();

						if (isset($queueIDS))
						{
							foreach ($queueIDS AS $queueID)
							{
								$obj            = new StdClass;
								$obj->id        = $queueID->id;
								$obj->sent      = 2;
								$obj->sent_date = date("Y-m-d H:i:s");

								if (!$db->updateObject('#__jticketing_queue', $obj, 'id'))
								{
									$returnMsg[$i]['success'] = 0;
									$returnMsg[$i]['msg']     = "Database error";
								}
							}
						}

						$returnMsg[$i]['success'] = 1;
						$returnMsg[$i]['msg']     = "Successfully Sent to " . $vars->mobile_no;
						$i++;
					}
					else
					{
						// If sms not sent set it as delayed
						$obj            = new StdClass;
						$obj->id        = $reminder->id;
						$obj->sent      = 0;
						$obj->sent_date = date("Y-m-d H:i:s");

						if (!$db->updateObject('#__jticketing_queue', $obj, 'id'))
						{
							$returnMsg[$i]['success'] = 0;
							$returnMsg[$i]['msg']     = "Database error";

							return $returnMsg;
						}
					}
				}
			}
		}

		return $returnMsg;
	}

	/**
	 * function to add data to queue
	 *
	 * @param   object  $reminder  data of reminder
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function addtoQueue($reminder)
	{
		$db                 = $this->getDatabase();
		$obj                = new StdClass;
		$obj->sent          = $reminder->sent;
		$obj->reminder_type = $reminder->reminder_type;
		$obj->date_to_sent  = $reminder->date_to_sent;
		$obj->subject       = $reminder->subject;
		$obj->content       = $reminder->content;
		$obj->email         = $reminder->email;
		$obj->order_id      = $reminder->order_id;

		if (!empty($reminder->id))
		{
			$obj->id = $reminder->id;

			if (!$db->updateObject('#__jticketing_queue', $obj, 'id'))
			{
			}
		}
		else
		{
			if (!$db->insertObject('#__jticketing_queue', $obj, 'id'))
			{
			}
		}
	}

	/**
	 * Generate valid order status
	 *
	 * @param   string  $status       Order's status
	 *
	 * @param   array   $allStatuses  All Status
	 *
	 * @return  array
	 */
	public function getValidOrderStatus($status, $allStatuses)
	{
		$unsetOrderStatus = array(
				"P"   => array (0 => "RF", 1 => "CRV", 2 => "RV", 3 => "I"),
				"C"   => array (0 => "P",  1 => "D",   2 => "E", 3 => "UR",  4 => "CRV", 5 => "I"),
				"D"   => array (0 => "P",  1 => "C",   2 => "E", 3 => "UR",  4 => "RF", 5 => "CRV", 6 => "RV", 7 => "I"),
				"E"   => array (0 => "P",  1 => "C",   2 => "D", 3 => "UR",  4 => "RF", 5 => "CRV", 6 => "RV", 7 => "I"),
				"UR"  => array (0 => "P",  1 => "D",   2 => "RF",3 => "CRV", 4 => "RV", 5 => "I"),
				"RF"  => array (0 => "P",  1 => "C",   2 => "D", 3 => "E",   4 => "UR", 5 => "CRV", 6 => "RV", 7 => "I"),
				"CRV" => array (0 => "P",  1 => "C",   2 => "D", 3 => "E",   4 => "UR", 5 => "RF",  6 => "RV", 7 => "I"),
				"I"   => array (0 => "P",  1 => "C",   2 => "D", 3 => "E",   4 => "UR", 5 => "RF",  6 => "RV"),
				"RV"  => array (0 => "P",  1 => "C",   2 => "D", 3 => "E",   4 => "UR", 5 => "RF", 6 => "I"),
		);

		foreach ($unsetOrderStatus as $key => $orderStatuses)
		{
			if ($key === $status)
			{
				foreach ($orderStatuses as $orderStatus)
				{
					// Unset the indexes
					unset($allStatuses[$orderStatus]);
				}
			}
		}

		return $allStatuses;
	}

	/**
	 * Get all order statuses
	 *
	 * @deprecated 2.5.0 use getOrderStatues from order model
	 * @return  array
	 *
	 * @since   2.2
	 */
	public function getOrderStatusArray()
	{
		$orderStatusArray = array(
				'P' => Text::_('JT_PSTATUS_PENDING'),
				'C' => Text::_('JT_PSTATUS_COMPLETED'),
				'D' => Text::_('JT_PSTATUS_DECLINED'),
				'E' => Text::_('JT_PSTATUS_FAILED'),
				'UR' => Text::_('JT_PSTATUS_UNDERREVIW'),
				'RF' => Text::_('JT_PSTATUS_REFUNDED'),
				'CRV' => Text::_('JT_PSTATUS_CANCEL_REVERSED'),
				'RV' => Text::_('JT_PSTATUS_REVERSED'),
		);

		return $orderStatusArray;
	}

	/**
	 * Returns array of order object for frontend listing.
	 *
	 * @param   array  $options  Filters
	 *
	 * @since  2.5.0
	 *
	 * @return array
	 */
	public function getOrders($options = array())
	{
		$db    = $this->getDatabase();
		$query = $db->getQuery(true);
		$query->select('DISTINCT *');
		$query->from($db->quoteName('#__jticketing_order'));

		if (isset($options['event_details_id']))
		{
			$query->where($db->quoteName('event_details_id') . ' = ' . $db->quote($options['event_details_id']));
		}

		if (isset($options['user_id']))
		{
			$query->where($db->quoteName('user_id') . ' = ' . $db->quote($options['user_id']));
		}

		if (isset($options['status']))
		{
			$query->where($db->quoteName('status') . ' = ' . $db->quote($options['status']));
		}

		$db->setQuery($query);

		return $db->loadObjectList();
	}

	/**
	 * Method to change the status of the order.
	 *
	 * @param   JTicketingOrder  $order   Object of the JTicketingOrder who's status is to be changed
	 * @param   String           $status  status to be changed for an Order
	 *
	 * @since  2.5.0
	 *
	 * @return Boolean
	 */
	public function changeOrderStatus($order, $status)
	{
		$event = JT::event()->loadByIntegration($order->event_details_id);
		$orderDetails                = array();
		$orderDetails['vendor_id']   = $event->vendor_id;
		$orderDetails['status']      = $status;
		$orderDetails['client']      = "com_jticketing";
		$orderDetails['client_name'] = Text::_('COM_JTICKETING');
		$orderDetails['order_id']    = $order->id;
		$orderDetails['amount']      = $order->get('amount');
		$orderDetails['customer_note']    = "";
		$orderDetails['fee_amount']       = $order->get('fee');
		$orderDetails['transaction_time'] = $order->cdate;
		$tjvendorFrontHelper              = new TjvendorFrontHelper;

		if (!empty($order) && !empty($status))
		{
			$jtTriggerOrder			= new JticketingTriggerOrder;

			if ($status === COM_JTICKETING_CONSTANT_ORDER_STATUS_COMPLETED)
			{
				if (!$order->complete())
				{
					$this->setError($order->getError());

					return false;
				}

				$tjvendorFrontHelper->addEntry($orderDetails);

				// Trigger to put user in easysocial groups
				PluginHelper::importPlugin('jticketing');
				Factory::getApplication()->triggerEvent('onAfterJtOrderComplete', array($order, false));
			}
			else
			{
				// If its an action after order complete then add the ticket type specific count.
				if ($order->getStatus() === 'C')
				{
					$this->eventsTypesCountIncrease($order->id);
				}

				if (!$this->updateOrderStatus($order->id, $status))
				{
					$this->setError($this->getError());

					return false;
				}

				if ($status == 'RF')
				{
					$order->refund();

					$tjvendorFrontHelper->addEntry($orderDetails);
				}
				// status status from completed to reverse and set attendee status as rejected
				if ($status == 'RV' && $order->getStatus() === 'C')
				{
					$order->reverse();
				}				

				// JlikeTodo deletion will go here
				$eventData                     = array();
				$eventData['orderId']          = $order->id;
				$eventData['action']           = 'delete';
				$eventData['assigned_to']      = $order->user_id;

				// Delete jliketodo related to that order
				$jtTriggerOrder->onOrderStatusChange($order, $eventData);
			}

			return true;
		}
	}

	/**
	 * Get all buyer events
	 *
	 * @param   integer  $userId  Event Creator
	 *
	 * @return  Object on success 
	 *
	 * @since   DEPLOY_VERSION
	 */
	public function getBuyerEvents($userId)
	{
		$db = $this->getDatabase();
		$query = $db->getQuery(true);

		$query->select('events.id, events.title, events.startdate, intxref.vendor_id, intxref.id as xref_id');
		$query->from($db->quoteName('#__jticketing_events', 'events'));
		$query->join('INNER', $db->quoteName('#__jticketing_integration_xref', 'intxref')
		. ' ON (' . $db->quoteName('intxref.eventid') . ' = ' . $db->quoteName('events.id') . ')');
		$query->join('INNER', $db->quoteName('#__jticketing_order', 'order')
		. ' ON (' . $db->quoteName('order.event_details_id') . ' = ' . $db->quoteName('intxref.id') . ')');

		$query->where($db->quoteName('intxref.source') . ' = ' . $db->quote('com_jticketing'));

		if ($userId)
		{
			$query->where($db->quoteName('order.user_id') . ' = ' . (int) $userId);
		}
		else
		{
			return false;
		}

		$query->where($db->quoteName('events.state') . ' = 1');
		$query->group($db->quoteName('events.id'));
		
		$db->setQuery($query);

		if ($eventData = $db->loadObjectList())
		{
			return $eventData;
		}

		return array();
	}

	/**
	 * Get all buyer JEvents
	 *
	 * @param   integer  $userId  Event Creator
	 *
	 * @return  Object on success 
	 *
	 * @since   DEPLOY_VERSION
	 */
	public function getBuyerJEvents($userId)
	{
		$db = $this->getDatabase();
		$query = $db->getQuery(true);

		$query
			->select([
				$db->qn('jev.ev_id', 'id'),
				$db->qn('je.summary', 'title'),
				$db->qn('rep.startrepeat', 'repstartdate'),
				$db->qn('je.dtstart', 'startdate'),
				$db->qn('intxref.vendor_id'),
				$db->qn('intxref.id', 'xref_id')
			])
			->from($db->qn('#__jticketing_integration_xref', 'intxref'))
			->join('INNER', $db->qn('#__jticketing_order', 'o') . ' ON ' . $db->qn('o.event_details_id') . ' = ' . $db->qn('intxref.id'))
			->join('LEFT', $db->qn('#__jevents_repetition', 'rep') . ' ON ' . $db->qn('intxref.eventid') . ' = ' . $db->qn('rep.rp_id'))
			->join('LEFT', $db->qn('#__jevents_vevent', 'jev') . ' ON ' . $db->qn('jev.ev_id') . ' = ' . $db->qn('rep.eventid'))
			->join('LEFT', $db->qn('#__jevents_vevdetail', 'je') . ' ON ' . $db->qn('je.evdet_id') . ' = ' . $db->qn('rep.eventdetail_id'))
			->where($db->qn('intxref.source') . ' = ' . $db->quote('com_jevents'))
			->where($db->qn('o.user_id') . ' = ' . (int) $userId)
			->group($db->qn('jev.ev_id'));
		

		$db->setQuery($query);

		if ($eventData = $db->loadObjectList())
		{
			return $eventData;
		}

		return array();
	}

	/**
	 * This will add pending entries to reminder queue
	 *
	 * @return  void
	 *
	 * @since   3.2.0
	 */
	public function addPendingEntriesToQueue()
	{
		$jtTriggerOrder	= new JticketingTriggerOrder;
		$this->setState('filter.status', 'C');
		$orders = $this->getItems();

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
			$jtTriggerOrder->onOrderStatusChange($order, $eventData);
		}
	}

	/**
	 * Method to decrease jomsocial seats if order status changed from confirmed topending
	 *
	 * @param   String  $order_id  order id ofjticketing_order table
	 *
	 * @return Boolean
	 *
	 * @since  3.2.0
	 */
	public function unJoinMembers($order_id)
	{
		$com_params          = JT::config();
		$affectJsNativeSeats = $com_params->get('affect_js_native_seats');

		if ($affectJsNativeSeats != '1')
		{
			return;
		}

		$orderData = JT::order($order_id);

		if (empty($orderData))
		{
			return false;
		}

		$db                  = $this->getDatabase();
		$eventData           = JT::event()->loadByIntegration($orderData->event_details_id);
		$arr                 = array();
		$arr['id']           = $eventData->getId();
		$arr['confirmedcount'] = $eventData->event->confirmedcount - $orderData->ticketscount;
		$eventData->save($arr);

		$db = $this->getDatabase();
		$query = $db->getQuery(true);
		$conditions = array(
			$db->quoteName('memberid') . ' = ' . $db->quote($orderData->user_id),
			$db->quoteName('eventid') . ' = ' . $db->quote($eventData->getId()),
		);

		$query->delete($db->quoteName('#__community_events_members'));
		$query->where($conditions);
		$query->setLimit($orderData->ticketscount);
		$db->setQuery($query);

		if (!$db->execute())
		{
			$this->setError($db->getErrorMsg());

			return false;
		}
	}
}
