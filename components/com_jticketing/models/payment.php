<?php
/**
 * @package     JTicketing
 * @subpackage  com_jticketing
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die();
use Joomla\Filesystem\File;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Response\JsonResponse;
use Joomla\Registry\Registry;

if (file_exists(JPATH_LIBRARIES . '/techjoomla/tjnotifications/tjnotifications.php')) { require_once JPATH_LIBRARIES . '/techjoomla/tjnotifications/tjnotifications.php'; }
require_once JPATH_ADMINISTRATOR . '/components/com_tjvendors/helpers/tjvendors.php';
if (file_exists(JPATH_SITE . '/components/com_jticketing/events/order.php')) { require_once JPATH_SITE . '/components/com_jticketing/events/order.php'; }

/**
 * Model for post processing payment
 *
 * @package     JTicketing
 * @subpackage  component
 * @since       1.0
 */

class JticketingModelpayment extends BaseDatabaseModel
{
	public $utilities;

	/**
	 * Constructor
	 *
	 * @since   1.0
	 */
	public function __construct()
	{
		parent::__construct();

		$TjGeoHelper = JPATH_ROOT . '/components/com_tjfields/helpers/geo.php';

		if (!class_exists('TjGeoHelper'))
		{
			JLoader::register('TjGeoHelper', $TjGeoHelper);
			JLoader::load('TjGeoHelper');
		}

		$jTOrderHelper = JPATH_ROOT . '/components/com_jticketing/helpers/order.php';

		if (!class_exists('JticketingOrdersHelper'))
		{
			JLoader::register('JticketingOrdersHelper', $jTOrderHelper);
			JLoader::load('JticketingOrdersHelper');
		}

		$this->TjGeoHelper = new TjGeoHelper;
		$this->jTOrderHelper = new JticketingOrdersHelper;
		$this->jtTriggerOrder = new JticketingTriggerOrder;
		$this->_db         = Factory::getDbo();
		$this->utilities = JT::utilities();

		// Load jlike main helper to call api function for assigndetails and other
		$path = JPATH_SITE . '/components/com_jlike/helpers/main.php';
		$this->ComjlikeMainHelper = "";

		if (File::exists($path))
		{
			if (!class_exists('ComjlikeMainHelper'))
			{
				JLoader::register('ComjlikeMainHelper', $path);
				JLoader::load('ComjlikeMainHelper');
			}

			$this->ComjlikeMainHelper = new ComjlikeMainHelper;
		}

		// Load jlike model to call api function for assigndetails and other
		$path = JPATH_SITE . '/components/com_jlike/models/recommendations.php';
		$this->JlikeModelRecommendations = "";

		if (File::exists($path))
		{
			if (!class_exists('JlikeModelRecommendations'))
			{
				JLoader::register('JlikeModelRecommendations', $path);
				JLoader::load('JlikeModelRecommendations');
			}

			$this->JlikeModelRecommendations = new JlikeModelRecommendations;
		}
	}

	/**
	 * Gives payment html from plugin
	 *
	 * @param   string   $pg_plugin  name of plugin like paypal
	 * @param   integer  $oid        id of jticketing_order
	 *
	 * @return  html  payment html
	 *
	 * @since   1.0
	 */
	public function confirmpayment($pg_plugin, $oid)
	{
		$post = Factory::getApplication()->getInput()->get('post');
		$vars = $this->getPaymentVars($pg_plugin, $oid);

		if (!empty($post) && !empty($vars))
		{
			if (!empty($result))
			{
				$vars = $result[0];
			}

			PluginHelper::importPlugin('payment', $pg_plugin);

			if (isset($vars->is_recurring) and $vars->is_recurring == 1)
			{
				$result = Factory::getApplication()->triggerEvent('onTP_ProcessSubmitRecurring', array($post, $vars));
			}
			else
			{
				$result = Factory::getApplication()->triggerEvent('onTP_ProcessSubmit', array($post, $vars));
			}
		}
		else
		{
			Factory::getApplication()->enqueueMessage(Text::_('SOME_ERROR_OCCURRED'), 'error');
		}
	}

	/**
	 * Gives vars to be used in plugin by parsing them in plugin structure
	 *
	 * @param   string   $pg_plugin  name of plugin like paypal
	 * @param   integer  $orderid    id of jticketing_order
	 *
	 * @return  OBJECT | false  payment related data on success and false on fail case.
	 *
	 * @since   1.0
	 */
	public function getPaymentVars($pg_plugin, $orderid)
	{
		if (!empty($orderid) && is_int($orderid))
		{
			$order                = JT::order($orderid);
			$user                 = $order->getbillingdata();
			$jticketingmainhelper = new jticketingmainhelper;
			$params               = JT::config();
			$handle_transactions  = $params->get('handle_transactions');
			$session              = Factory::getSession();
			$orderItemid          = $this->utilities->getItemId('index.php?option=com_jticketing&view=orders&layout=order');
			$chkoutItemid         = $this->utilities->getItemId('index.php?option=com_jticketing&view=order');

			// Append prefix and order_id
			$vars           		= new stdClass;
			$vars->order_id 		= $order->order_id;
			$vars->user_id  		= $order->user_id;

			if (isset($user->firstname))
			{
				$vars->user_firstname = $user->firstname;
			}

			if (isset($user->lastname))
			{
				$vars->user_lastname = $user->lastname;
			}

			if (isset($user->address))
			{
				$vars->user_address = $user->address;
			}

			if (isset($user->user_email))
			{
				$vars->user_email = $user->user_email;
			}

			if (isset($user->city))
			{
				$vars->user_city = $user->city;
			}

			if (isset($user->zipcode))
			{
				$vars->user_zip = $user->zipcode;
			}

			if (isset($user->phone))
			{
				$vars->phone = $user->phone;
			}

			if (isset($user->country_code))
			{
				$vars->country_code = $user->country_code;
			}

			if (isset($user->state_code))
			{
				$vars->state_code = $user->state_code;
			}

			$guest_email = '';

			if (!$order->user_id && $params->get('allow_buy_guest'))
			{
				$guest_email = "&email=" . md5($order->email);
			}

			$vars->item_name     = $order->order_id;
			$ecTrackId           = base64_encode($order->order_id);
			$return_url          = "index.php?option=com_jticketing&view=orders&layout=order";
			$return_url .= $guest_email . "&orderid=" . $order->order_id . "&processor={$pg_plugin}&Itemid=" . $orderItemid . "&ecTrackId=" . $ecTrackId;
			$vars->return        = Uri::root() . substr(Route::_($return_url, false), strlen(Uri::base(true)) + 1);
			$cancel_return       = "index.php?option=com_jticketing&view=order&layout=cancel&processor={$pg_plugin}&Itemid=" . $chkoutItemid;
			$vars->cancel_return = Uri::root() . substr(Route::_($cancel_return, false), strlen(Uri::base(true)) + 1);
			$url                 = Uri::root() . "index.php?option=com_jticketing&task=payment.processpayment" . $guest_email;
			$url .= "&order_id=" . $order->order_id . "&processor=" . $pg_plugin;
			$vars->notify_url = $url;
			$vars->url           = Route::_($url, false);
			$vars->currency_code = $params->get('currency');
			$vars->comment       = $order->customer_note;

			$vars->amount        = $order->getAmount(false);
			$vars->tax = 0;
			if ($params->get('allow_taxation') && $order->get('order_tax') && $pg_plugin == 'paypal')
			{
				$vars->amount = $order->getNetAmount();
				$tax = 0;
				$taxDetails = new Registry($order->order_tax_details);
				foreach ($taxDetails as $taxDetail)
				{
					foreach ($taxDetail->breakup as $breakup)
					{
						$tax += $breakup->value;
					}
				}
				$vars->tax = $tax;
			}			

			$vars->userInfo      = (array) $user;
			$cancel_return             = "index.php?option=com_jticketing&view=order&layout=cancel&processor={$pg_plugin}&Itemid=" . $chkoutItemid;
			$vars->cancel_return       = Uri::root() . substr(Route::_($cancel_return, false), strlen(Uri::base(true)) + 1);
			$return_url                = "index.php?option=com_jticketing&view=orders&layout=order" . $guest_email . "&orderid=" . $order->order_id;
			$return_url               .= "&processor={$pg_plugin}&Itemid=" . $orderItemid . "&ecTrackId=" . $ecTrackId;
			$vars->return              = Uri::root() . substr(Route::_($return_url, false), strlen(Uri::base(true)) + 1);
			$submiturl                 = "index.php?option=com_jticketing&task=payment.confirmpayment&orderid=" .
			($order->id) . "&processor ={$pg_plugin}";
			$vars->submiturl           = Route::_($submiturl, false);

			if ($order->processor == 'paypal' && $handle_transactions == 1)
			{
				$vars->business = $this->getEventownerEmail($orderid);
			}

			// For Adpative payment
			$vars->adaptiveReceiverList = $this->getReceiverList($vars, $pg_plugin, $orderid);

			// Get event owner: For stripe
			$event                  = JT::event()->loadByIntegration($order->event_details_id);
			$vars->owner            = $event->getCreator();
			$vars->bootstrapVersion = $params->get("bootstrap_version");

			// Get commision amount
			$vars->commision = $order->getFee(false);
			$vars->client    = "jticketing";

			// Get order desc
			$vars->order_desc = $event->getTitle();

			$orderItems = $order->getItems();

			if (!empty($orderItems))
			{
				$cartItemsData = array();
				$cartItemsData['final_amount'] = $order->getAmount(false);
				$cartItemsData['amount']       = $order->getOriginalAmount();
				$cartItemsData['discount']     = $order->getCouponDiscount(false);
				$cartItemsData['total_items']  = count($orderItems);
				$cartItemsData['cart_items']   = array();

				foreach ($orderItems as $orderItem)
				{
					$ticketType = JT::Tickettype($orderItem->type_id);

					$cartItem = array();
					$cartItem['item_id']       = $orderItem->type_id;
					$cartItem['order_item_id'] = $orderItem->id;
					$cartItem['item_title']    = $ticketType->title;
					$cartItem['item_desc']     = $ticketType->desc;
					$cartItem['quantity']      = $orderItem->ticketcount;
					$cartItem['price']         = $orderItem->ticket_price;
					$cartItem['tax']           = '';
					$cartItem['shipping']      = '';
					$cartItem['other_charges'] = '';

					$cartItemsData['cart_items'][] = $cartItem;
				}

				$vars->cart_info = $cartItemsData;
			}

			// Get billing details
			$billingData              = array();
			$billingData['name']      = $user->firstname . ' ' . $user->lastname;
			$billingData['address_1'] = $user->address;
			$billingData['address_2'] = '';
			$billingData['city']      = $user->city;
			$billingData['state']     = $user->state_code;
			$billingData['country']   = $user->country_code;
			$billingData['zip']       = $user->zipcode;
			$billingData['phone']     = $user->phone;
			$vars->billing_address    = $billingData;

			// Get user info
			$userData               = array();
			$userData['user_id']    = $user->user_id;
			$userData['user_email'] = $user->user_email;
			$userData['firstname']  = $user->firstname;
			$userData['lastname']   = $user->lastname;
			$vars->user_info        = $userData;

			return $vars;
		}
	}

	/**
	 * Changes gateway html when clicked on checkout
	 *
	 * @return  array  $billDetails  biling infor of the payee
	 *
	 * @since   1.0
	 */
	public function changegateway()
	{
		if (file_exists(JPATH_SITE . '/components/com_jticketing/models/payment.php')) { require_once JPATH_SITE . '/components/com_jticketing/models/payment.php'; }

		$jinput          = Factory::getApplication()->getInput();
		$model           = new jticketingModelpayment;
		$selectedGateway = $jinput->get('gateways', '');
		$order_id        = $jinput->getInt('order_id');

		// Checking whether payment gateway and order id are not empty and order id should be
		// integer and order id from post data should be same as session order id.
		if (!empty($selectedGateway) && !empty($order_id) && is_int($order_id))
		{
			$model->updateOrderGateway($selectedGateway, $order_id);
			$payhtml = $model->getHTML($order_id, $selectedGateway);

			return $payhtml[0];
		}
	}

	/**
	 * Get payment gateway html from plugin
	 *
	 * @param   integer  $order_id  id of jticketing_order table
	 *
	 * @return  Array|Boolean  on success return array
	 *
	 * @since   1.0
	 */
	public function getHTML($order_id, $gateway = '')
	{
		if (!empty($order_id) && is_int($order_id))
		{
			$order              = JT::order($order_id);

			if ($order->getStatus() == COM_JTICKETING_CONSTANT_ORDER_STATUS_INCOMPLETE)
			{
				$bindData           = array();
				$bindData['status'] = COM_JTICKETING_CONSTANT_ATTENDEE_STATUS_PENDING;

				if ($gateway)
				{
					$bindData['processor'] = $gateway;
				}

				if (!$order->bind($bindData))
				{
					return false;
				}

				if (!$order->save())
				{
					return false;
				}
			}

			if ($gateway)
			{
				$order->processor = $gateway;
			}
			else
			{
				$order = $this->getdetails($order_id);
			}
			
			$vars      = $this->getPaymentVars($order->processor, $order_id);
			PluginHelper::importPlugin('payment', $order->processor);

			$html = Factory::getApplication()->triggerEvent('onTP_GetHTML', array($vars));

			return $html;
		}
	}

	/**
	 * Get payment gateway html from plugin
	 *
	 * @param   integer  $pg_plugin  id of jticketing_order table
	 *
	 * @param   integer  $order_id   id of jticketing_order table
	 *
	 * @param   integer  $order      id of jticketing_order table
	 *
	 * @return  html
	 *
	 * @since   1.0
	 */
	public function getHTMLS($pg_plugin, $order_id, $order)
	{
		if (!empty($order_id) && is_int($order_id))
		{
			$db = Factory::getDbo();
			$query = $db->getQuery(true);
			$query->update("#__jticketing_order AS JTO");
			$query->set("JTO.processor = '" . $pg_plugin . "', JTO.status = 'P'");
			$query->where("JTO.order_id= '" . $order . "'");
			$db->setQuery($query);
			$result = $db->execute();
			$order = $this->getdetails($order_id);
			$vars      = $this->getPaymentVars($order->processor, $order_id);
			PluginHelper::importPlugin('payment', $pg_plugin);
			$html       = Factory::getApplication()->triggerEvent('onTP_GetHTML', array($vars));

			return $html;
		}
	}

	/**
	 * Get all order details based on id
	 *
	 * @param   integer  $tid  id of jticketing_order table
	 *
	 * @return  array
	 *
	 * @since   1.0
	 */
	public function getdetails($tid)
	{
		if (!empty($tid) && is_int($tid))
		{
			$params = ComponentHelper::getParams('com_jticketing');
			$db = Factory::getDbo();
			$query = $db->getQuery(true);
			$query->select($db->quoteName(array('firstname','user_email','phone','user_id')));
			$query->from($db->quoteName('#__jticketing_users'));
			$query->where($db->quoteName('order_id') . " = " . $db->quote($tid));
			$query->where($db->quoteName('address_type') . " = 'BT'");
			$db->setQuery($query);
			$orderdetails = $db->loadObjectlist();

			$query1 = $db->getQuery(true);
			$query1->select($db->quoteName(array('fee','amount','customer_note','processor','order_id')));
			$query1->from($db->quoteName('#__jticketing_order'));
			$query1->where($db->quoteName('id') . " = " . $db->quote($tid));
			$db->setQuery($query1);
			$orderamt = $db->loadObjectlist();
			$orderdetails['0']->order_id = $orderamt[0]->order_id;

			$query2 = $db->getQuery(true);
			$query2->select($db->quoteName(array('i.type_id', 't.title', 't.price'), array(null, 'order_item_name', null)));
			$query2->select('sum(' . $db->quoteName('i.ticketcount') . ') as ticketcount');
			$query2->from($db->quoteName('#__jticketing_order_items', 'i'));
			$query2->join('LEFT', $db->quoteName('#__jticketing_types', 't') . ' ON (' . $db->quoteName('t.id') . ' = ' . $db->quoteName('i.type_id') . ')');
			$query2->where($db->quoteName('i.order_id') . " = " . $db->quote($tid));
			$query2->group($db->quoteName('i.type_id'));

			$db->setQuery($query2);
			$orderlist['items'] = $db->loadObjectlist();
			$itemarr            = array();

			foreach ($orderlist['items'] as $item)
			{
				$itemarr[] = $item->order_item_name;
			}

			if ($itemarr[0])
			{
				$itemstring = implode('\n', $itemarr);
			}

			$orderdetails['0']->order_item_name = $itemstring;
			$orderdetails['0']->processor       = $orderamt[0]->processor;
			$orderdetails['0']->order_amt       = $orderamt[0]->amount;
			$orderdetails['0']->fee             = $orderamt[0]->fee;
			$orderdetails['0']->currency        = $params->get('currency');
			$orderdetails['0']->customer_note   = preg_replace('/\<br(\s*)?\/?\>/i', " ", $orderamt[0]->customer_note);

			return $orderdetails['0'];
		}
	}

	/**
	 * Post Processing for order
	 *
	 * @param   string  $post       post data
	 * @param   string  $pg_plugin  payment gateway name
	 * @param   int     $order_id   id of jticketing_order table
	 *
	 * @return  array
	 *
	 * @since   1.0
	 */
	public function processpayment($post, $pg_plugin, $order_id)
	{
		// Trigger Before process Paymemt
		PluginHelper::importPlugin('system');
		$order   		= JT::order()->loadByOrderId($order_id);
		Factory::getApplication()->triggerEvent('onJtBeforeProcessPayment', array($post, $order->id, $pg_plugin));

		$session     = Factory::getSession();
		$id          = $order->id;
		$return_resp = array();

		// Authorise Post Data
		if (!empty($post['plugin_payment_method']) && $post['plugin_payment_method'] == 'onsite')
		{
			$plugin_payment_method = $post['plugin_payment_method'];
		}

		$post['client'] = 'jticketing';

		PluginHelper::importPlugin('payment', $pg_plugin);
		$vars = $this->getPaymentVars($pg_plugin, $order->id);

		try
		{
			$data = Factory::getApplication()->triggerEvent('onTP_Processpayment', array($post, $vars));
		}
		catch (Exception $e)
		{
			echo new JsonResponse($e);
		}

		$data = $data[0];

		// Validate the order Amount before checkout
		if ($order->getAmount(false) != $data['total_paid_amt'])
		{
			$event = JT::event()->loadByIntegration($order->event_details_id);
			$app = Factory::getApplication();
			$app->enqueueMessage(Text::_('COM_JTICKETING_INVALID_ORDER_AMOUNT_VALIDATION'), 'message');
			$app->redirect($event->getUrl());
		}

		$this->storelog($pg_plugin, $data);

		if ($data)
		{
			try
			{
				$orderItemid = $this->utilities->getItemId('index.php?option=com_jticketing&view=orders&layout=order');

				$user_detail 		= $order->getbillingdata();

				$params      = ComponentHelper::getParams('com_jticketing');
				$guest_email = "";

				if (!$user_detail->user_id && $params->get('allow_buy_guest'))
				{
					$guest_email = "&email=" . md5($user_detail->user_email);
				}

				$data['processor'] 		= $pg_plugin;
				$data['status']    		= trim($data['status']);
				$return_resp['status'] 	= '0';

				if ($order->getAmount(false) == 0)
				{
					$data['order_id']       = $id;
					$data['total_paid_amt'] = 0;
					$data['processor']      = $pg_plugin;
					$data['status']         = 'C';
				}

				if (($data['status'] == 'C' && $order->getAmount(false) == $data['total_paid_amt']) or ($data['status'] == 'C' && $order->getAmount(false) == 0))
				{
					$data['status']        = 'C';
					$return_resp['status'] = '1';
				}
				elseif ($order->getAmount(false) != $data['total_paid_amt'] && $data['processor'] != 'adaptive_paypal')
				{
					$data['status']        = 'E';
					$return_resp['status'] = '0';
				}
				elseif (empty($data['status']))
				{
					$data['status']        = 'P';
					$return_resp['status'] = '0';
				}

				if ($data['status'] != 'C' && !empty($data['error']))
				{
					$return_resp['msg'] = $data['error']['code'] . " " . $data['error']['desc'];
				}

				$this->updateOrder($id, $user_detail->user_id, $data, $return_resp);

				// Clear order session
				$session->set('JT_orderid', '');
				$session->set('JT_fee', '');
				$ecTrackId = base64_encode($order->order_id);
				$return = "index.php?option=com_jticketing&view=orders&layout=order";
				$return .= $guest_email . "&orderid=" . $order->order_id . "&processor={$pg_plugin}&Itemid=" . $orderItemid . "&ecTrackId=" . $ecTrackId;
				$return_resp['return'] = Uri::root() . substr(Route::_($return, false), strlen(Uri::base(true)) + 1);

				// Trigger After Process Payment
				PluginHelper::importPlugin('system');

				// Old Trigger
				Factory::getApplication()->triggerEvent('onJtAfterProcessPayment', array($data, $order->id, $pg_plugin));

				// New Trigger
				Factory::getApplication()->triggerEvent('onAfterJtProcessPayment', array($data, $order->id, $pg_plugin));
			}
			catch (Exception $e)
			{
				throw new Exception($e->getMessage());
			}
		}
		else
		{
			$return_resp['msg'] = Text::_('COM_JTICKETING_ORDER_ERROR');
		}

		return $return_resp;
	}

	/**
	 * Check if order is already processed
	 *
	 * @param   string  $transaction_id  transaction_id for order
	 * @param   string  $order_id        id of jticketing_order
	 *
	 * @return  int  1 or 0
	 *
	 * @since   1.0
	 */
	public function Dataprocessed($transaction_id, $order_id)
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->qn('event_details_id'));
		$query->from($db->qn('#__jticketing_order'));
		$query->where($db->qn('id') . " = " . $db->quote($order_id));
		$query->where($db->qn('transaction_id') . " = " . $db->quote($transaction_id));
		$query->where($db->qn('status') . " = " . $db->quote('C'));
		$db->setQuery($query);
		$eventdata = $db->loadResult();

		if (!empty($eventdata))
		{
			return 1;
		}
		else
		{
			return 0;
		}
	}

	/**
	 * Update order its status,seats and other data
	 *
	 * @param   string  $id           id for jticketing_order
	 * @param   string  $userid       userid of payee
	 * @param   array   $data         data of jticketing_order
	 * @param   string  $return_resp  return_resp
	 *
	 * @return  int  1 or 0
	 *
	 * @since   1.0
	 */
	public function updateOrder($id, $userid, $data, $return_resp)
	{
		JLoader::register('JticketingMailHelper', JPATH_SITE . '/components/com_jticketing/helpers/mail.php');
		$processed 	= 0;
		$order 		= JT::order($id);

		// Imp Check if Data is processed and Status is already Completed
		$processed = $this->Dataprocessed($data['transaction_id'], $id);

		if ($order->event_details_id && strtolower($order->getStatus()) == 'c')
		{
			$return_resp['status'] = '1';

			return $return_resp;
		}

		if ($data['status'] == 'C' && $processed != 1)
		{
			$bindData 					= array();
			$bindData['transaction_id'] = $data['transaction_id'];
			$bindData['extra'] 			= json_encode($data['raw_data']);

			if ($order->bind($bindData))
			{
				if ($order->complete())
				{
					JticketingMailHelper::sendOrderStatusEmail($id, $data['status']);
					$return_resp['status'] = '1';
				}
			}
			$event                            = JT::event()->loadByIntegration(
				$order->event_details_id
			);
			$orderDetails                     = array();
			$orderDetails['vendor_id']        = $event->vendor_id;
			$orderDetails['status']           = $order->getStatus();
			$orderDetails['client']           = "com_jticketing";
			$orderDetails['client_name']      = Text::_('COM_JTICKETING');
			$orderDetails['order_id']         = $order->id;
			$orderDetails['amount']           = $order->get('amount');
			$orderDetails['customer_note']    = "";
			$orderDetails['fee_amount']       = $order->get('fee');
			$orderDetails['transaction_time'] = $order->cdate;
			$tjvendorFrontHelper              = new TjvendorFrontHelper;
			$tjvendorFrontHelper->addEntry($orderDetails);
		}
		elseif (!empty($data['status']))
		{
			$this->updateStatus($data);
		}

		return $return_resp;
	}

	/**
	 * Update order and send invoice and add payout entry
	 *
	 * @param   int     $id         id for jticketing_order
	 * @param   string  $data       userid of payee
	 * @param   string  $member_id  payee id
	 *
	 * @return  Boolean  true on success and false on failed case.
	 *
	 * @since   1.0
	 *
	 * @deprecated 3.0.0 This method is deprecated and will be removed in next version
	 */
	public function updateOrderEvent($id, $data, $member_id)
	{
		$comParams           				= JT::config();
		$socialintegration   				= $comParams->get('integrate_with', 'none');
		$streamBuyTicket     				= $comParams->get('streamBuyTicket', 0);

		$user                 				= Factory::getUser();

		JLoader::register('JticketingMailHelper', JPATH_SITE . '/components/com_jticketing/helpers/mail.php');
		$jteventHelper        				= new jteventHelper;
		$orderinfo   						= JT::order($id);
		$eventInfo 							= JT::event()->loadByIntegration($orderinfo->event_details_id);

		// Add Payout Entry
		$tjvendorFrontHelper = new TjvendorFrontHelper;
		$orderModel          = JT::model('order');

		$orderDetails                     	= array();
		$orderDetails['vendor_id']       	= !empty($eventInfo->vendor_id) ? $eventInfo->vendor_id : 0;
		$orderDetails['status']          	= $data['status'];
		$orderDetails['client']           	= "com_jticketing";
		$orderDetails['client_name']      	= Text::_('COM_JTICKETING');
		$orderDetails['order_id']         	= $orderinfo->order_id;
		$orderDetails['amount']           	= $orderinfo->getAmount(false);
		$orderDetails['customer_note']    	= $orderinfo->customer_note;
		$orderDetails['fee_amount']       	= $orderinfo->getFee(false);
		$orderDetails['transaction_time'] 	= $orderinfo->cdate;

		$tjvendorFrontHelper->addEntry($orderDetails);

		if ($socialintegration != 'none' && $streamBuyTicket == 1 && !empty($user->id))
		{
			// Add in activity.
			$libclass						= $orderModel->getJticketSocialLibObj();
			$title 							= !empty($eventInfo->getTitle()) ? $eventInfo->getTitle() : '';
			$eventLink   					= '<a class="" href="' . $eventInfo->getUrl() . '">' . $eventInfo->getTitle() . '</a>';
			$originalMsg 					= Text::sprintf('COM_JTICKETING_PURCHASED_TICKET', $eventLink);
			$libclass->pushActivity($user->id, '', '', $originalMsg, '', '', 0);
		}

		// Update attendee status to Approve when order get completed
		JT::order($id)->updateAttendeeStatus();

		$this->updatesales($data, $id);

		$orderModel->eventupdate($orderinfo, $orderinfo->user_id);

		// Send Ticket Email.
		if (!$eventInfo->isOnline())
		{
			JticketingMailHelper::sendmailnotify($id, 'afterordermail');
		}

		// Update coupon count
		if ($orderinfo->getCouponCode())
		{
			$table = JT::table('coupon');

			if ($table->load(array('code' => $orderinfo->getCouponCode())))
			{
				$couponform       = JT::Model('couponform');
				$couponData       = $couponform->getItem($table->id);
				$couponData->used = $couponData->used + 1;
				$couponform->save((array) $couponData);
			}
		}

		// Send Invoice Email.
		JticketingMailHelper::sendInvoiceEmail($id);

		// JlikeTODO insertion or updation
		if ($orderinfo->user_id)
		{
			$eventData                = array();
			$eventData['eventId']     = $eventInfo->id;
			$eventData['eventTitle']  = $eventInfo->title;
			$eventData['startDate']   = $eventInfo->getStartDate();
			$eventData['endDate']     = $eventInfo->getEndDate();

			// Insert JlikeTodo or update todo
			$eventData['assigned_to'] = $orderinfo->user_id;
			$orderinfo->status 		  = $data['status'];

			$this->jtTriggerOrder->onOrderStatusChange($orderinfo, $eventData);
		}

		// Add entries to JLikeTODO table to send reminder for Event
		$integration 	= $comParams->get('integration');
		$eventType 		= $eventInfo->online_events;

		if ($integration == 2 && $eventType == 1)
		{
			$meeting_url			= json_decode($eventInfo->getParams());
			$venueDetails          	= $eventInfo->getVenueDetails();
			$randomPassword        	= $this->utilities->generateRandomString(8);
			$venueParams           	= json_decode($venueDetails->getParams());
			$venueParams->user_id  	= $orderinfo->user_id;
			$venueParams->name     	= $orderinfo->name;
			$venueParams->email    	= $orderinfo->email;
			$venueParams->password 	= $randomPassword;
			$venueParams->meeting_url = json_decode($meeting_url->event_url);

			if ($eventType == '1')
			{
				// TRIGGER After create event
				PluginHelper::importPlugin('tjevents');
				$result = Factory::getApplication()->triggerEvent('onTjinviteUsers', array($venueParams));

				JticketingMailHelper::onlineEventNotify($id, $venueParams, $eventInfo);
			}
		}

		// Add payout entry
		$this->addPayoutEntry($id, $data['transaction_id'], $data['status'], $data['processor']);

		return true;
	}

	/**
	 * Get payout ID and status from payout table
	 *
	 * @param   string  $transactionID  id for jticketing_order
	 * @param   string  $userid         userid of payee
	 *
	 * @return  array  payout array
	 *
	 * @since   1.0
	 */
	public function getPayoutId($transactionID, $userid)
	{
		$db    = Factory::getDbo();
		$query = "SELECT `id`,`status`
		FROM `#__jticketing_ticket_payouts`
		WHERE `transction_id`='" . $transactionID . "' AND `user_id`=" . $userid;
		$db->setQuery($query);

		return $db->loadAssoc();
	}

	/**
	 * Update payout entry
	 *
	 * @param   string  $order_id   id for jticketing_order
	 * @param   string  $txnid      txnid
	 * @param   array   $status     status
	 * @param   string  $pg_plugin  name of plugin
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function addPayoutEntry($order_id, $txnid, $status, $pg_plugin)
	{
		$plugin           = PluginHelper::getPlugin('payment', $pg_plugin);
		$params              = ComponentHelper::getParams('com_jticketing');
		$handle_transactions = $params->get('handle_transactions');

		if ($pg_plugin == 'adaptive_paypal' || ($pg_plugin == 'paypal' && $handle_transactions == 1))
		{
			// Lets set the paypal email if admin is not handling transactions
			$adaptiveDetails  = array();
			$adaptiveDetails = JT::model('payment')->getSplitPaymentDetails($order_id);

			foreach ($adaptiveDetails as $userReport)
			{
				$com_params = ComponentHelper::getParams('com_jticketing');
				$currency   = $com_params->get('currency');
				$vendor_id  = $this->vendorCheck($userReport['owner']);

				$tjvendorsHelper = new TjvendorsHelper;
				$payableAmount   = $tjvendorsHelper->getTotalAmount($vendor_id, $currency, 'com_jticketing');

				$newPayoutData                     = array();
				$newPayoutData['debit']            = $userReport['commissonCutPrice'];
				$newPayoutData['total']            = $payableAmount['total'] - $newPayoutData['debit'];
				$newPayoutData['transaction_time'] = Factory::getDate()->toSql();
				$newPayoutData['client']           = 'com_jticketing';
				$newPayoutData['currency']         = $currency;
				$transactionClient                 = Text::_('COM_JTICKETING');
				$newPayoutData['transaction_id']   = $transactionClient . '-' . $currency . '-' . $vendor_id . '-';
				$newPayoutData['id']               = '';
				$newPayoutData['vendor_id']        = $vendor_id;
				$newPayoutData['status']           = 1;
				$newPayoutData['credit']           = '0.00';
				$newPayoutData['adaptive_payout']  = 1;

				if ($pg_plugin == 'paypal')
				{
					$customerNote = Text::_("COM_JTICKETING_DIRECT_PAYMENT_VENDOR_PAYPAL");
				}
				else
				{
					$customerNote = Text::_("COM_JTICKETING_DIRECT_PAYMENT_VENDOR_ADAPTIVE_PAYPAL");
				}

				$params = array("customer_note" => $customerNote, "entry_status" => "debit_payout");
				$newPayoutData->params = json_encode($params);
				BaseDatabaseModel::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_tjvendors/models', 'payout');
				$tjvendorsModelPayout = BaseDatabaseModel::getInstance('Payout', 'TjvendorsModel');
				$tjvendorsModelPayout->save($newPayoutData);
			}
		}
	}

	/**
	 * check if user is a vendor
	 *
	 * @param   integer  $user_id  order's id
	 *
	 * @return  mixed
	 *
	 * @since   2.0
	 */
	public function vendorCheck($user_id)
	{
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
	 * Get userid of payee from order
	 *
	 * @param   string  $order_id      order_id in jticketing_order
	 * @param   string  $order_status  order_status like C and P
	 *
	 * @return  integer
	 *
	 * @since   1.0
	 */
	public function getEventMemberid($order_id, $order_status)
	{
		$db = Factory::getDbo();

		if ($order_status == 'P' OR $order_status == 'RF')
		{
			$query = $db->getQuery(true);
			$query->select($db->quoteName('user_id'));
			$query->from($db->quoteName('#__jticketing_order'));
			$query->where($db->quoteName('id') . ' = ' . $db->quote($order_id));
			$query->where($db->quoteName('status') . ' != ' . $db->quote('C'));
		}
		else
		{
			$query = $db->getQuery(true);
			$query->select($db->quoteName('user_id'));
			$query->from($db->quoteName('#__jticketing_order'));
			$query->where($db->quoteName('id') . ' = ' . $db->quote($order_id));
			$query->where($db->quoteName('status') . ' = ' . $db->quote('C'));
		}

		$db->setQuery($query);

		return $user_id = $db->loadResult();
	}

	/**
	 * Store login data
	 *
	 * @param   string  $name  name of plugin
	 * @param   string  $data  data
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function storelog($name, $data)
	{
		$data1              = array();
		$data1['raw_data']  = $data['raw_data'];
		$data1['JT_CLIENT'] = "com_jticketing";
		PluginHelper::importPlugin('payment', $name);
		$data = Factory::getApplication()->triggerEvent('onTP_Storelog', array($data1));
	}

	/**
	 * Update status of  the order if it is not confirm
	 *
	 * @param   Array  $data  data
	 *
	 * @return  Boolean true on successfull execution and false on unsuccessfull execution
	 *
	 * @since   1.0
	 */
	public function updateStatus($data)
	{
		/** @var $order JTicketingOrder */
		$order						= JT::order()->loadByOrderId($data['order_id']);

		$bindData 					= array();
		$bindData['transaction_id'] = $data['transaction_id'];
		$bindData['extra'] 			= json_encode($data['raw_data']);

		if (isset($data['status']))
		{
			$bindData['status'] = $data['status'];
		}
		else
		{
			$bindData['status'] 		= COM_JTICKETING_CONSTANT_ATTENDEE_STATUS_PENDING;
		}

		if (!$order->bind($bindData))
		{
			return false;
		}

		if (!$order->save())
		{
			return false;
		}

		if ($data['status'] != 'C')
		{
			JticketingMailHelper::sendInvoiceEmail($order->id);
		}

		// Update attendee status to Approve when order get completed
		JT::order($order->id)->updateAttendeeStatus();

		return true;
	}

	/**
	 * Update sales data
	 *
	 * @param   array  $data  data
	 * @param   int    $id    order id
	 *
	 * @return  false
	 *
	 * @since   1.0
	 *
	 * @deprecated  2.5.0  updatesales method will be replaced with decrementTicketCount method in order's model.
	 */
	public function updatesales($data, $id)
	{
		$order = JT::order($id);
		$order->mdate		= Factory::getDate()->toSql();
		$order->transaction_id = $data['transaction_id'];
		$order->payee_id       = $data['buyer_email'];
		$order->status         = $data['status'];
		$order->processor      = $data['processor'];
		$order->extra          = json_encode($data['raw_data']);

		if (!$order->save())
		{
			return false;
		}

		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName('type_id'));
		$query->select('count(ticketcount) AS ticketcounts');
		$query->from($db->quoteName('#__jticketing_order_items'));
		$query->where($db->quoteName('order_id') . " = " . $db->quote($id));
		$query->group($db->quoteName('type_id'));
		$db->setQuery($query);
		$orderdetails = $db->loadObjectlist();

		foreach ($orderdetails as $orderdetail)
		{
			$typedata = '';
			$restype  = new stdClass;
			$query    = "SELECT count
				FROM #__jticketing_types where id=" . $orderdetail->type_id;
			$db->setQuery($query);
			$typedata       = $db->loadResult();
			$restype->id    = $orderdetail->type_id;
			$restype->count = $typedata - $orderdetail->ticketcounts;
			$db->updateObject('#__jticketing_types', $restype, 'id');
		}
	}

	/**
	 * Update event data for jomsocial and other integration for event members
	 *
	 * @param   array  $order_id  id of jticketing_order table
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function getEventownerEmail($order_id)
	{
		$db = Factory::getDbo();

		// Retrieve XrefID
		$query = $db->getQuery(true);
		$query->select($db->quoteName(array('event_details_id')));
		$query->from($db->quoteName('#__jticketing_order'));
		$query->where($db->quoteName('id') . " = " . $db->quote($order_id));
		$db->setQuery($query);
		$eventid = $db->loadResult();

		$query1 = $db->getQuery(true);
		$query1->select($db->quoteName(array('vendor_id')));
		$query1->from($db->quoteName('#__jticketing_integration_xref'));
		$query1->where($db->quoteName('id') . " = " . $db->quote($eventid));
		$db->setQuery($query1);
		$vendor_id = $db->loadResult();

		$query2 = $db->getQuery(true);
		$query2->select($db->quoteName(array('params')));
		$query2->from($db->quoteName('#__vendor_client_xref'));
		$query2->where($db->quoteName('vendor_id') . " = " . $db->quote($vendor_id));
		$db->setQuery($query2);
		$result = $db->loadResult();

		if ($result)
		{
			$paymentDetails = json_decode($result)->payment_gateway;

			foreach ($paymentDetails as $paymentDetail)
			{
				if ($paymentDetail->payment_gateways == 'paypal')
				{
					return $paymentDetail->payment_email_id;
				}
			}
		}

		return '';
	}

	/**
	 * Get all event data for jomsocial based on creator
	 *
	 * @param   int  $creator  creator of event
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function getEventData($creator)
	{
		$db    = Factory::getDbo();
		$query = "SELECT events.id,events.creator, sum(ticket.amount) AS nprice, sum(ticket.fee) AS nfee
							  FROM #__community_events AS events
					          LEFT JOIN #__jticketing_events_xref AS eventdetails
							  ON events.id = eventdetails.eventid
							  LEFT JOIN #__jticketing_order AS ticket
							  ON eventdetails.eventid = ticket.event_details_id
							  WHERE ticket.status = 'C'
							  AND events.creator ='" . $creator . "' GROUP BY events.creator";
		$db->setQuery($query);
		$rows = $db->loadObject();

		return $rows;
	}

	/**
	 * Update selected gateway in database
	 *
	 * @param   string  $selectedGateway  gateway that is selected
	 * @param   int     $order_id         order_id of the order which is selected
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function updateOrderGateway($selectedGateway, $order_id)
	{
		$row            = new stdClass;
		$row->id        = $order_id;
		$row->processor = $selectedGateway;

		if (!$this->_db->updateObject('#__jticketing_order', $row, 'id'))
		{
			echo $this->_db->stderr();

			return 0;
		}

		return 1;
	}

	/**
	 * Get data for adaptive payment
	 *
	 * @param   string  $vars       vars for adaptive payment
	 * @param   int     $pg_plugin  payment gateway name
	 * @param   int     $orderid    orderid of the order which is selected
	 *
	 * @return  array
	 *
	 * @since   1.0
	 */
	public function getReceiverList($vars, $pg_plugin, $orderid)
	{
		// GET BUSINESS EMAIL
		$plugin           = PluginHelper::getPlugin('payment', $pg_plugin);
		$pluginParams     = json_decode($plugin->params);
		$businessPayEmial = "";

		if (property_exists($pluginParams, 'business'))
		{
			$businessPayEmial = trim($pluginParams->business);
		}
		else
		{
			return array();
		}

		$params              = ComponentHelper::getParams('com_jticketing');
		$handle_transactions = $params->get('handle_transactions', 0);

		// Lets set the paypal email if admin is not handling transactions
		$adaptiveDetails  = array();
		$adaptiveDetails = JT::model('payment')->getSplitPaymentDetails($orderid);

		if ($pg_plugin == 'adaptive_paypal' && !empty($orderid) && is_int($orderid))
		{
			$receiverList                = array();
			$receiverList[0]             = array();
			$tamount                     = 0;
			$receiverList[0]['receiver'] = $businessPayEmial;
			$receiverList[0]['amount']   = $adaptiveDetails['0']['commission'];
			$receiverList[0]['primary']  = false;

			if (!empty($adaptiveDetails[$businessPayEmial]))
			{
				// Primary account
				unset($adaptiveDetails[$businessPayEmial]);
			}
			else
			{
				// $tamount = $tamount + $receiverList[0]['amount'];
			}

			// Add other receivers
			$index = 1;

			foreach ($adaptiveDetails as $detail)
			{
				$paymentDetails = json_decode($detail['paypal_detail'])->payment_gateway;

				foreach ($paymentDetails as $paymentDetail)
				{
					if ($paymentDetail->payment_gateways == 'adaptive_paypal')
					{
						$receiverList[$index]['receiver']  = $paymentDetail->payment_email_id;
					}
				}

				// Changed above 2 lines by sagar to make event owner as primary receiver
				$receiverList[$index]['amount']  = $vars->amount;
				$receiverList[$index]['primary'] = true;
				$index++;
			}

			return $receiverList;
		}
		elseif ($pg_plugin == 'stripe' && !empty($orderid) && !empty($pluginParams->enableconnect))
		{
			$receiverList                = array();
			$receiverList[0]             = array();
			$tamount                     = 0;
			$receiverList[0]['receiver'] = $businessPayEmial;
			$receiverList[0]['amount']   = $adaptiveDetails['0']['commission'];
			$receiverList[0]['primary']  = false;

			if (!empty($adaptiveDetails[$businessPayEmial]))
			{
				// Primary account
				unset($adaptiveDetails[$businessPayEmial]);
			}

			// Add other receivers
			$index = 1;

			foreach ($adaptiveDetails as $detail)
			{
				$paymentDetails = json_decode($detail['paypal_detail'])->payment_gateway;

				foreach ($paymentDetails as $paymentDetail)
				{
					if ($paymentDetail->payment_gateways == 'stripe')
					{
						$receiverList[$index]['vendor_detail']  = $paymentDetail;
						$receiverList[$index]['vendorId']  = $adaptiveDetails[0]['vendor'];

						// Changed above 2 lines by sagar to make event owner as primary receiver
						$receiverList[$index]['amount']  = $vars->amount;
						$receiverList[$index]['primary'] = true;
						$index++;
					}
				}
			}

			return $receiverList;
		}

		return;
	}

	/**
	 * Get data for stripe payment
	 *
	 * @param   string  $data    data for stripe payment
	 * @param   int     $refund  refund 1 or 0
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function stripeAddPayout($data, $refund = 0)
	{
		$db             = Factory::getDbo();
		$transaction_id = $data['data']['object']['charge'];

		if (!$transaction_id)
		{
			return;
		}

		// Get Event Owner ID
		$query = $db->getQuery(true);

		// Select campaign owner id
		$query->select($db->quoteName(array('e.userid', 'o.original_amount')));
		$query->from($db->quoteName('#__jticketing_integration_xref', 'e'));
		$query->join('INNER', $db->quoteName('#__jticketing_order', 'o') .
				' ON (' . $db->quoteName('e.id') . ' = ' . $db->quoteName('o.event_details_id') . ')');
		$query->where($db->quoteName('o.transaction_id') . ' = ' . "'" . $db->quote($transaction_id) . "'");

		// Reset the query using our newly populated query object.
		$db->setQuery($query);

		// Load the result
		$orderObject = $db->loadObject();

		// Get Payout ID
		$query = $db->getQuery(true);
		$query->select($db->quoteName(array('id')));
		$query->from($db->quoteName('#__jticketing_ticket_payouts', 'p'));
		$query->where($db->quoteName('p.transction_id') . ' = ' . "'" . $db->quote($transaction_id) . "'");
		$db->setQuery($query);

		// Get payout ID
		$payout_id = $db->loadResult();

		// Add Payout
		$res = new stdClass;

		if ($payout_id)
		{
			$res->id = $payout_id;
		}
		else
		{
			$res->id = '';
		}

		$res->user_id       = $orderObject->userid;
		$res->payee_name    = Factory::getUser($orderObject->userid)->name;
		$res->date          = date("Y-m-d H:i:s");
		$res->transction_id = $transaction_id;

		// If fee is refunded then means total amount is paid to campaign promoter
		if ($refund == 1)
		{
			$res->amount = $orderObject->original_amount;
		}
		else
		{
			$res->amount = $orderObject->original_amount - ($data['data']['object']['amount'] / 100);
		}

		$res->status     = 1;
		$res->ip_address = '';
		$res->type       = 'stripe';

		if ($res->id)
		{
			if (!$db->updateObject('#__jticketing_ticket_payouts', $res, 'id'))
			{
			}
		}
		else
		{
			if (!$db->insertObject('#__jticketing_ticket_payouts', $res, 'id'))
			{
			}
		}

		return true;
	}

	/**
	 * Add data to reminder queue
	 *
	 * @param   array  $reminderData  Reminder data
	 * @param   array  $user          User data
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function addtoReminderQueue($reminderData, $user)
	{
		$data['start_date']   = $reminderData->startdate;
		$data['due_date']   = $reminderData->enddate;
		$data['type']					= 'assign';
		$data['todo_id']				= '';
		$data['recommend_friends']		= array($user);

		// Set the plugin details
		$plg_name   = 'jlike_events';
		$plg_type   = 'content';
		$element    = 'com_jticketing.event';

		// @TODO Snehal Get xref ID of the event
		$element_id = $reminderData->id;
		$options = array('element' => $element, 'element_id' => $element_id, 'plg_name' => $plg_name, 'plg_type' => $plg_type);

		if (!empty($data))
		{
			$res       = $this->ComjlikeMainHelper->updateTodos($data, $options);

			return $res;
		}
	}

	/**
	 * Method to get event information from order id
	 *
	 * @param   int  $orderId  order id to get info
	 *
	 * @return  array
	 *
	 * @since   3.2.0
	 */
	public function getSplitPaymentDetails($orderId)
	{
		$orderInformation = array();
		$order     = JT::order($orderId);
		$vendor    = JT::event()->loadByIntegration($order->event_details_id)->getVendorDetails();

		$orderInformation['commissonCutPrice'] 	= (int) $order->getAmount() - (int) $order->getFee();
		$orderInformation['commission'] 		= (int) $order->getFee();
		$orderInformation['vendor'] 			= $vendor->vendor_id;
		$orderInformation['paypal_detail'] 		= $vendor->params;
		$adaptiveDetails[0] 					= $orderInformation;

		return $adaptiveDetails;
	}
}
