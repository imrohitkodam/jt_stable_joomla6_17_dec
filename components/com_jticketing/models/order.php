<?php
/**
 * @package     JTicketing
 * @subpackage  com_jticketing
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die(';)');
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Plugin\PluginHelper;


if (file_exists(JPATH_SITE . '/components/com_tjvendors/models/vendors.php')) { require_once JPATH_SITE . '/components/com_tjvendors/models/vendors.php'; }
if (file_exists(JPATH_SITE . '/components/com_jticketing/helpers/common.php')) { require_once JPATH_SITE . '/components/com_jticketing/helpers/common.php'; }
if (file_exists(JPATH_SITE . '/components/com_jticketing/helpers/route.php')) { require_once JPATH_SITE . '/components/com_jticketing/helpers/route.php'; }
if (file_exists(JPATH_SITE . '/components/com_jticketing/helpers/order.php')) { require_once JPATH_SITE . '/components/com_jticketing/helpers/order.php'; }

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Registry\Registry;

/**
 * Model for buy for creating order and other
 *
 * @package     JTicketing
 * @subpackage  component
 * @since       1.0
 */
class JticketingModelOrder extends AdminModel
{
	/**
	 * Constructor
	 *
	 * @since   1.0
	 */
	public function __construct()
	{
		parent::__construct();
		$this->jticketingmainhelper 	= new jticketingmainhelper;
		$this->jticketingfrontendhelper = new jticketingfrontendhelper;
		$this->JticketingCommonHelper 	= new JticketingCommonHelper;
		$this->JTRouteHelper 			= new JTRouteHelper;
		$this->jTOrderHelper 			= new JticketingOrdersHelper;

		$TjGeoHelper = JPATH_ROOT . '/components/com_tjfields/helpers/geo.php';

		if (!class_exists('TjGeoHelper'))
		{
			JLoader::register('TjGeoHelper', $TjGeoHelper);
			JLoader::load('TjGeoHelper');
		}

		$this->TjGeoHelper = new TjGeoHelper;

		// Add social library according to the social integration
		$params = JT::config();
		$socialintegration = $params->get('integrate_with', 'none');

		// Load main file
		if (file_exists(JPATH_LIBRARIES . '/techjoomla/jsocial/jsocial.php')) { require_once JPATH_LIBRARIES . '/techjoomla/jsocial/jsocial.php'; }
		if (file_exists(JPATH_LIBRARIES . '/techjoomla/jsocial/joomla.php')) { require_once JPATH_LIBRARIES . '/techjoomla/jsocial/joomla.php'; }

		if ($socialintegration != 'none')
		{
			if ($socialintegration == 'JomSocial')
			{
				if (file_exists(JPATH_LIBRARIES . '/techjoomla/jsocial/jomsocial.php')) { require_once JPATH_LIBRARIES . '/techjoomla/jsocial/jomsocial.php'; }
			}
			elseif ($socialintegration == 'EasySocial')
			{
				if (file_exists(JPATH_LIBRARIES . '/techjoomla/jsocial/easysocial.php')) { require_once JPATH_LIBRARIES . '/techjoomla/jsocial/easysocial.php'; }
			}
		}
	}

	/**
	 * Method to get the record form.
	 *
	 * @param   string  $data      An optional array of data for the form to interogate.
	 * @param   string  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  JForm   A JForm object on success, false on failure
	 *
	 * @since   1.0
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Initialise variables.
		$app = Factory::getApplication();

		// Get the form.
		$form = $this->loadForm('com_jticketing.order', 'order', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Get an instance of JTable class
	 *
	 * @param   string  $type    Name of the JTable class to get an instance of.
	 * @param   string  $prefix  Prefix for the table class name. Optional.
	 * @param   array   $config  Array of configuration values for the JTable object. Optional.
	 *
	 * @return  JTable|bool JTable if success, false on failure.
	 */
	public function getTable($type = 'Order', $prefix = 'JticketingTable', $config = array())
	{
		$this->addTablePath(JPATH_ADMINISTRATOR . '/components/com_jticketing/tables');

		return Table::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to get an object.
	 *
	 * @param   integer  $id  The id of the object to get.
	 *
	 * @return  mixed    Object on success, false on failure.
	 */
	public function getItem($id = null)
	{
		$this->item = parent::getItem($id);

		return $this->item;
	}

	/**
	 * Prepare and sanitise the table prior to saving.
	 *
	 * @param   array  $data  TO  ADD
	 *
	 * @return   mixed  integer on success, false on failure.
	 *
	 * @since    1.6
	 */
	public function save($data)
	{
		$session     = Factory::getSession();
		$com_params  = JT::config();
		$integration = JT::getIntegration(true);

		$singleTicketPerUser = $com_params->get('single_ticket_per_user');
		$singleTicketPerUserPerCheckout = $com_params->get('max_noticket_peruserperpurchase');

		// Get ticket types for eventid
		$ticketTypeObject = JT::event($data['eventid'], JT::getIntegration())->getTicketTypes();
		$ticketTypeArr = array();

		foreach ($ticketTypeObject as $key => $value)
		{
			$ticketTypeArr[] = $value->id;
		}

		if (file_exists(JPATH_SITE . '/components/com_jticketing/models/eventform.php')) { require_once JPATH_SITE . '/components/com_jticketing/models/eventform.php'; }
		$eventModel = BaseDatabaseModel::getInstance('EventForm', 'JticketingModel');
		$eventdata = $eventModel->getItem($data['eventid']);

		$orderdata = array();

		if (empty($orderdata))
		{
			$orderdata['user_id']           = $data['user_id'];
			$orderdata['name']              = $data['name'];
			$orderdata['email']             = $data['email'];
			$orderdata['parent_order_id']   = 0;
			$ticketcount                    = 0;
			$ticketdata['type_ticketcount'] = $data['type_ticketcount'];
			$ticketdata['type_id']          = $data['type_id'];

			if ($integration == '2')
			{
				if (!empty($eventdata->online_events) || !empty($singleTicketPerUser) || $singleTicketPerUserPerCheckout == 1)
				{
					$ticketdata['type_ticketcount'] = array($ticketdata['type_id'][0] => '1');
					$ticketcount = '1';
				}
				else
				{
					foreach ($ticketdata['type_ticketcount'] as $key => $count)
					{
						$ticketcount += $count;
					}
				}
			}
			else
			{
				if (!empty($singleTicketPerUser) || $singleTicketPerUserPerCheckout == 1)
				{
					$ticketdata['type_ticketcount'] = array($ticketdata['type_id'][0] => '1');
					$ticketcount = '1';
				}
				else
				{
					foreach ($ticketdata['type_ticketcount'] as $key => $count)
					{
						$ticketcount += $count;
					}
				}
			}

			$orderdata['no_of_tickets']       = $ticketcount;
			$ticketdata['coupon_code']        = $data['coupon_code'];
			$allowTaxation                    = $com_params->get('allow_taxation');
			$eventData['eventid']             = $data['eventid'];
			$eventData['event_integraton_id'] = $data['event_integraton_id'];

			// Check whether type_id exists against event_id
			if ($ticketdata['type_id'] === array_intersect($ticketdata['type_id'], $ticketTypeArr))
			{
				// RecalculateAmount Based on Ticket Type and Ticket count
				$amountData                = $this->recalculateTotalAmount($ticketdata, $allowTaxation, $eventData);
			}
			else
			{
				return false;
			}

			$orderdata['original_amt'] = $amountData['original_amt'];

			if ($amountData['amt'] < 0)
			{
				$amountData['amt'] = 0;
				$amountData['fee'] = 0;
			}

			$orderdata['amount']          = $amountData['amt'];
			$orderdata['fee']             = $amountData['fee'];
			$orderdata['coupon_discount'] = $amountData['coupon_discount'];

			if (isset($amountData['order_tax']))
			{
				$orderdata['order_tax'] = $amountData['order_tax'];
			}
			else
			{
				$orderdata['order_tax'] = 0;
			}

			if (isset($amountData['order_tax']))
			{
				$orderdata['order_tax_details'] = $amountData['order_tax_details'];
			}
			else
			{
				$orderdata['order_tax_details'] = 0;
			}

			$orderdata['coupon_discount_details'] = (array_key_exists("coupon_discount_details", $amountData)) ? $amountData['coupon_discount_details'] : '';
			$orderdata['coupon_code']             = $amountData['coupon_code'];
			$eventIntegrationId                   = $orderdata['integraton_id'] = $data['event_integraton_id'];
		}

		$JtOrderId = $session->get('JT_orderid');
		$currentSessionTickets = $session->get('type_ticketcount_current');

		if (empty($currentSessionTickets))
		{
			$session->set('type_ticketcount_current', $ticketdata['type_ticketcount']);
			$session->set('type_ticketcount_old', $ticketdata['type_ticketcount']);
		}
		else
		{
			$session->set('type_ticketcount_old', $session->get('type_ticketcount_current'));
			$session->set('type_ticketcount_current', $ticketdata['type_ticketcount']);
		}

		$currentSessionTickets = $session->get('type_ticketcount_current');
		$oldSessionTickets     = $session->get('type_ticketcount_old');
		$removedTicketTypes = array();
		$addedTicketTypes   = array();

		foreach ($currentSessionTickets as $key => $value)
		{
			foreach ($oldSessionTickets as $oldkey => $oldval)
			{
				if ($key == $oldkey && $value < $oldval)
				{
					$removedTicketTypes[$key] = $oldval - $value;
				}

				if ($key == $oldkey && $value > $oldval)
				{
					$addedTicketTypes[$key] = $value - $oldval;
				}
			}
		}

		if (!empty($removedTicketTypes))
		{
			$ticketdata['removed_ticket_types'] = $removedTicketTypes;
		}

		// Create Main order
		if (!$orderdata['integraton_id'])
		{
			$integration                = JT::getIntegration();
			$orderdata['integraton_id'] = JT::event($orderdata['eventid'], $integration)->integrationId;
		}

		$ordData = array();

		// Check if id is set or not in data
		if (isset($data['id']))
		{
			$ordData['id'] = $data['id'];
		}

		$ordData['event_details_id'] = $orderdata['integraton_id'];

		if (isset($orderdata['name']))
		{
			$ordData['name'] = $orderdata['name'];
		}

		if (isset($orderdata['email']))
		{
			$ordData['email'] = $orderdata['email'];
		}

		if (isset($orderdata['user_id']))
		{
			$ordData['user_id'] = $orderdata['user_id'];
		}

		$utilities                          = JT::utilities();
		$ordData['coupon_code']             = $orderdata['coupon_code'];
		$ordData['coupon_discount']         = $utilities->getRoundedPrice($orderdata['coupon_discount']);
		$ordData['coupon_discount_details'] = $orderdata['coupon_discount_details'];
		$ordData['order_tax']               = $utilities->getRoundedPrice($orderdata['order_tax']);
		$ordData['order_tax_details']       = $orderdata['order_tax_details'];
		$ordData['cdate']                   = Factory::getDate()->toSql();
		$ordData['mdate']                   = Factory::getDate()->toSql();

		if (isset($orderdata['processor']))
		{
			$ordData['processor'] = $orderdata['processor'];
		}

		if (isset($orderdata['customer_note']))
		{
			$ordData['customer_note'] = $orderdata['customer_note'];
		}

		$ordData['ticketscount'] = $orderdata['no_of_tickets'];

		if (!$orderdata['parent_order_id'])
		{
			$ordData['parent_order_id'] = 0;
		}
		else
		{
			$ordData['parent_order_id'] = $orderdata['parent_order_id'];
		}

		$ordData['status'] = 'P';

		// This is calculated amount
		$ordData['original_amount'] = $utilities->getRoundedPrice($orderdata['original_amt']);
		$ordData['amount']          = $utilities->getRoundedPrice($orderdata['amount']);
		$ordData['fee']             = $utilities->getRoundedPrice($orderdata['fee']);
		$ordData['ip_address']      = $_SERVER["REMOTE_ADDR"];

		// Check whether type_id exists against event_id
		if ($ticketdata['type_id'] === array_intersect($ticketdata['type_id'], $ticketTypeArr))
		{
			if (parent::save($ordData))
			{
				$orderId = (int) $this->getState($this->getName() . '.id');

				if (isset($ordData['id']))
				{
					$orderId = $ordData['id'];
				}

				$db    = Factory::getDbo();
				$table = Table::getInstance('Order', 'JticketingTable', array('dbo', $db));

				if (empty($table->order_id))
				{
					$this->generateOrderID($orderId);
				}

				if (!empty($orderId))
				{
					if (file_exists(JPATH_SITE . '/components/com_jticketing/models/orderitem.php')) { require_once JPATH_SITE . '/components/com_jticketing/models/orderitem.php'; }
					$ordrItemModel = BaseDatabaseModel::getInstance('Orderitem', 'JticketingModel');
					$ticketdata['order_id'] = $orderId;
					$ticketdata['eventid'] = $orderdata['integraton_id'];
					$ordrItemModel->save($ticketdata);
				}

				return $orderId;
			}
			else
			{
				return false;
			}
		}
		else
		{
			return false;
		}
	}

	/**
	 * Apply coupon
	 *
	 * @param   integer  $originalAmount  originalAmount
	 * @param   integer  $eventId         event Id
	 * @param   integer  $couponCode      couponCode
	 *
	 * @return  array coupon amount
	 *
	 * @since   1.0
	 */
	public function applyCoupon($originalAmount, $eventId, $couponCode = '')
	{
		$couponCodeArr   = array("code" => trim($couponCode), "eventId" => (int) $eventId);
		$val             = 0;
		$couponDiscount  = $this->getCoupon($couponCodeArr);
		$vars            = array();

		if ($couponDiscount)
		{
			if ($couponDiscount['val_type'] == 1)
			{
				$val = ($couponDiscount['value'] / 100) * ($originalAmount);
			}
			else
			{
				$val = $couponDiscount['value'];
			}

			$vars['coupon_code'] = $couponDiscount['coupon_code'];
			$vars['coupon_discount_details'] = json_encode($couponDiscount);
		}

		$amt = $originalAmount - $val;
		$vars['original_amt']    = $originalAmount;
		$vars['amt']             = $amt;
		$vars['coupon_discount'] = $val;

		return $vars;
	}

	/**
	 * Update order Items in ajax calls in steps
	 *
	 * @param   integer  $ticketdata  orderdata for successed
	 * @param   integer  $orderid     order id
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function updateOrderItems($ticketdata, $orderid)
	{
		try
		{
			$session = Factory::getSession();
			$db    = Factory::getDbo();
			$query = $db->getQuery(true);
			$query->select($db->quoteName(array('id','order_id','attendee_id')));
			$query->from($db->quoteName('#__jticketing_order_items'));
			$query->where($db->quoteName('order_id') . ' = ' . $db->quote($orderid));
			$db->setQuery($query);
			$orderitems = $db->loadObjectlist();

			if (file_exists(JPATH_SITE . '/components/com_jticketing/models/event.php')) { require_once JPATH_SITE . '/components/com_jticketing/models/event.php'; }
			$ticketTypeModel = BaseDatabaseModel::getInstance('Tickettype', 'JticketingModel');

			// Firstly Delete ticket types in order items that are removed
			if (!empty($orderitems))
			{
				if (!empty($ticketdata['removed_ticket_types']))
				{
					foreach ($ticketdata['removed_ticket_types'] as $key => $count)
					{
						if ($count > 0)
						{
							$query = $db->getQuery(true);
							$query->delete($db->quoteName('#__jticketing_order_items'));
							$query->where($db->quoteName('order_id') . ' = ' . $db->quote($orderid));
							$query->where($db->quoteName('type_id') . ' = ' . $db->quote($key));
							$query->setLimit($count);
							$db->setQuery($query);
						}
					}
				}
			}

			foreach ($ticketdata['type_ticketcount'] as $key => $multipleTickets)
			{
				$query = $db->getQuery(true);
				$query->select($db->quoteName(array('id')));
				$query->from($db->quoteName('#__jticketing_order_items'));
				$query->where($db->quoteName('order_id') . ' = ' . $db->quote($orderid));
				$query->where($db->quoteName('type_id') . ' = ' . $db->quote($key));
				$db->setQuery($query);
				$orderitemIdArray           = $db->loadAssoclist();
				$resdetails                 = new stdClass;
				$resdetails->id             = '';
				$resdetails->order_id       = $orderid;
				$resdetails->ticketcount    = 1;
				$resdetails->type_id        = $key;
				$resdetails->payment_status = 'P';
				$ticketType = $ticketTypeModel->getItem($resdetails->type_id);
				$resdetails->ticket_price   = $ticketType->price;

				// @TODO For Deposit Change This to deposit Fee.
				$resdetails->amount_paid    = $resdetails->ticket_price;
				$totalUpdatedCount = 0;

				// Now update order items that already present
				if (!empty($orderitemIdArray))
				{
					foreach ($orderitemIdArray AS $key => $value)
					{
						$resdetails->id = $value['id'];

						if (!$db->updateObject('#__jticketing_order_items', $resdetails, 'id'))
						{
							echo $db->stderr();
						}

						$totalUpdatedCount++;
					}
				}

				if ($totalUpdatedCount)
				{
					$multipleTickets = $multipleTickets - $totalUpdatedCount;
				}

				// Insert Newly Created order items
				for ($i = 0; $i < $multipleTickets; $i++)
				{
					$resdetails->id = '';

					if (!$db->insertObject('#__jticketing_order_items', $resdetails, 'id'))
					{
						echo $db->stderr();
					}
				}
			}
		}
		catch (Exception $e)
		{
			$this->setError(Text::_('COM_JTICKETING_DATABASE_GENERIC_SQL_ERROR'));

			return false;
		}
	}

	/**
	 * Verify amount data for coupon code calculation and calculate final amount of order
	 *
	 * @param   ARRAY  $amountData     amountData
	 * @param   ARRAY  $allowTaxation  1 or 0
	 * @param   ARRAY  $eventData      eventData
	 *
	 * @return  array
	 *
	 * @since   1.0
	 */
	public function recalculateTotalAmount($amountData, $allowTaxation = 0, $eventData = '')
	{
		$vars    = array();
		$eventId = $eventData['eventid'];

		// Get user specific commission data.
		$userSpecificComm = $this->getUserSpecificCommision($eventId);
		$com_params          = ComponentHelper::getParams('com_jticketing');

		$siteAdminCommPer = isset($userSpecificComm->percent_commission)?$userSpecificComm->percent_commission:$com_params->get('siteadmin_comm_per');

		$siteAdminCommFlat = isset($userSpecificComm->flat_commission) ? $userSpecificComm->flat_commission :$com_params->get('siteadmin_comm_flat');

		$siteAdminCommCap = $com_params->get('siteadmin_comm_cap');

		$originalAmt       = 0;
		$typeTicketCounts  = $amountData['type_ticketcount'];
		$typeids           = $amountData['type_id'];

		if (file_exists(JPATH_SITE . '/components/com_jticketing/models/event.php')) { require_once JPATH_SITE . '/components/com_jticketing/models/event.php'; }
		$ticketTypesModel = BaseDatabaseModel::getInstance('Tickettype', 'JticketingModel');

		// Calculate original Amt to pay Based on ticket Types And Price.
		foreach ($typeTicketCounts AS $key => $multipleTickets)
		{
			$resDetails             = new stdClass;
			$resDetails->type_id    = $key;
			$resDetails->type_price = 0;
			$ticketTypes = $ticketTypesModel->getItem($resDetails->type_id);
			$resDetails->type_price = $ticketTypes->price;

			// @TODO For Deposit Change This to deposit Fee.
			$originalAmt += $resDetails->type_price * $multipleTickets;
		}

		if (!empty($amountData['coupon_code']))
		{
			$vars = $this->applyCoupon($originalAmt, $eventData['event_integraton_id'], $amountData['coupon_code']);
		}
		else
		{
			$vars['original_amt']    = $originalAmt;
			$vars['amt']             = $originalAmt;
			$vars['coupon_code']     = '';
			$vars['coupon_discount'] = 0;
		}

		// Calculated as 0.1+1  1
		if ($siteAdminCommCap < $vars['amt'] and $siteAdminCommCap > 0 )
		{
			$vars['fee'] = $siteAdminCommCap * $siteAdminCommPer / 100;
		}
		else
		{
			$vars['fee'] = $vars['amt'] * $siteAdminCommPer / 100;
		}

		if (isset($siteAdminCommFlat) and $siteAdminCommFlat > 0)
		{
			$fee = $vars['fee'] + $siteAdminCommFlat;

			// If fee is 1.1 And amt to pay is 1 in that case apply only percentage commission
			if ($fee <= $vars['amt'])
			{
				$vars['fee'] = $fee;
			}
		}

		if ($allowTaxation)
		{
			$taxAmt = $this->applyTax($vars);

			if (isset($taxAmt->taxvalue) and $taxAmt->taxvalue > 0)
			{
				$vars['order_tax']         = $taxAmt->taxvalue;
				$vars['amt']               = $vars['net_amt_after_tax'] = $vars['amt'] + $taxAmt->taxvalue;
				$vars['order_tax_details'] = json_encode($taxAmt);
			}
		}

		return $vars;
	}

	/**
	 * Calculate tax from and apply from taxation plugin
	 *
	 * @param   ARRAY  $vars  vars contains array for amt to apply tax
	 *
	 * @return  int    amount
	 *
	 * @since   1.0
	 */
	public function applyTax($vars)
	{
		// Set Required Sessions
		PluginHelper::importPlugin('jticketingtax');
		$taxResults = Factory::getApplication()->triggerEvent('onJtAddTax', array($vars['amt']));

		// Call the plugin and get the result
		if (isset($taxResults[0]) and $taxResults['0']->taxvalue > 0)
		{
			return $taxResults['0'];
		}
		else
		{
			return 0;
		}
	}

	/**
	 * Validate coupon for date and other condition
	 *
	 * @param   Array  $couponCode  couponCode
	 *
	 * @return  mixed  object on success, false on failure.
	 *
	 * @since   1.0
	 */
	public function getCoupon($couponCode)
	{
		$coupon        = JT::coupon();
		$isExistCoupon = $coupon->loadByCode($couponCode['code']);

		if ($isExistCoupon === false)
		{
			$invalidCoupon         = array();
			$invalidCoupon['msg']  = Text::_('COM_JTICKETING_ORDER_COUPON_INVALID');
			$invalidCoupon['flag'] = false;

			return $invalidCoupon;
		}

		$result = $coupon->isValid($couponCode);

		$c      = array();

		if ($result !== true)
		{
			return $result;
		}

		$table = JT::table('coupon');

		if ($table->load(array('code' => $couponCode['code'])))
		{
			return $c[] = array("value" => (int) $table->value, "val_type" => (int) $table->val_type, "coupon_code" => (string) $table->code);
		}
		else
		{
			return $c[] = array("error" => 1);
		}
	}

	/**
	 * Get Contry list from tjfields
	 *
	 * @param   integer  $country  country id
	 *
	 * @return  object state list
	 *
	 * @since   1.0
	 */
	public function getRegionList($country)
	{
		return $this->TjGeoHelper->getRegionListFromCountryID($country, 'com_jticketing');
	}

	/**
	 * Retrieve details for a country
	 *
	 * @return  object  $country  Details
	 *
	 * @since   1.0
	 */
	public function getCountry()
	{
		return $this->TjGeoHelper->getCountryList('com_jticketing');
	}

	/**
	 * Get Event data
	 *
	 * @return  array event list
	 *
	 * @since   1.0
	 */
	public function getEventdata()
	{
		try
		{
			$com_params   = ComponentHelper::getParams('com_jticketing');
			$integration  = $com_params->get('integration');
			$session      = Factory::getSession();
			$input        = Factory::getApplication()->getInput();
			$post         = $input->post;

			$eventId = $post->get('eventid');

			if (empty($eventId))
			{
				$eventId = $session->get('JT_eventid');
			}
			else
			{
				$eventId = $eventId;
			}

			if (empty($eventId))
			{
				$eventId = $input->get('eventid', '', 'INT');
			}

			$db    = Factory::getDbo();

			if ($integration == 1)
			{
				$query = $db->getQuery(true);
				$query->select('*');
				$query->from($db->quoteName('#__community_events'));
				$query->where($db->quoteName('id') . ' = ' . $db->quote($eventId));
			}
			elseif ($integration == 2)
			{
				$query = $db->getQuery(true);
				$query->select('*');
				$query->from($db->quoteName('#__jticketing_events'));
				$query->where($db->quoteName('id') . ' = ' . $db->quote($eventId));
			}
			elseif ($integration == 3)
			{
				$query = $db->getQuery(true);
				$query->select(array('event.*', 'DATE(FROM_UNIXTIME(event.dtstart))', 'DATE(FROM_UNIXTIME(event.dtend))'), array('startdate', 'enddate'));
				$query->from('#__jevents_vevent as e');
				$query->where($db->qn('e.id') . ' = ' . $db->quote($eventId));
				$query->join('LEFT', '#__jevents_vevdetail AS event ON event.evdet_id = e.detail_id');
			}
			elseif ($integration == 4)
			{
				$query = $db->getQuery(true);
				$query->select(array('event.*', 'event_det.start', 'event_det.end'), array('start', 'enddate'));
				$query->from($db->quoteName('#__social_clusters', 'event'));
				$query->join('INNER', $db->quoteName('#__social_events_meta', 'event_det')
						. 'ON (' . $db->quoteName('event_det.cluster_id') . ' = ' . $db->quoteName('event.id') . ')');
				$query->where($db->quoteName('event.id') . ' = ' . $db->quote($eventId));
			}

			$db->setQuery($query);
			$result = $db->loadobject();

			return $result;
		}
		catch (Exception $e)
		{
			$this->setError(Text::_('COM_JTICKETING_DATABASE_GENERIC_SQL_ERROR'));

			return false;
		}
	}

	/**
	 * Check if joomla user exists
	 *
	 * @param   INT  $email  email of joomla user
	 *
	 * @return  Boolean true or false
	 *
	 * @since   1.0
	 */
	public function checkuserExistJoomla($email)
	{
		try
		{
			$db    = Factory::getDbo();
			$query = $db->getQuery(true);
			$query->select($db->quoteName(array('id')));
			$query->from($db->quoteName('#__users'));
			$query->where($db->quoteName('email') . ' LIKE ' . $db->quote($email));
			$db->setQuery($query);
			$id = $db->loadResult();

			if ($id)
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		catch (Exception $e)
		{
			$this->setError(Text::_('COM_JTICKETING_DATABASE_GENERIC_SQL_ERROR'));

			return false;
		}
	}

	/**
	 * Get User specific % commission and flat commission
	 *
	 * @param   Int  $eventId  Event ID
	 *
	 * @return Array of data
	 */
	public function getUserSpecificCommision($eventId)
	{
		try
		{
			$db = Factory::getDbo();
			$event = JT::event();
			$eventDetails = $event->loadByIntegration($eventId);

			// Get user specific % commission and flat commission set by Admin
			$query = $db->getQuery(true)
			->select('*')
			->from($db->quoteName('#__tjvendors_fee'))
			->where($db->quoteName('vendor_id') . ' = ' . $db->quote($eventDetails->vendor_id));
			$db->setQuery($query);
			$userSpecificData = $db->loadObject();

			return $userSpecificData;
		}
		catch (Exception $e)
		{
			$this->setError(Text::_('COM_JTICKETING_DATABASE_GENERIC_SQL_ERROR'));

			return false;
		}
	}

	/**
	 * To generate order id with prefix
	 *
	 * @param   integer  $orderID  order id
	 *
	 * @return  void|boolean
	 *
	 * @since   1.0
	 */
	public function generateOrderID($orderID)
	{
		try
		{
			$db  = Factory::getDbo();

			// Update order if orde_id present
			if (!empty($orderID))
			{
				// Store Order to Jticketing Table
				$lang      = Factory::getLanguage();
				$extension = 'com_jticketing';
				$baseDir   = JPATH_ROOT;
				$lang->load($extension, $baseDir);
				$comParams     = ComponentHelper::getParams('com_jticketing');
				$integration   = $comParams->get('integration');
				$guestRegId    = $comParams->get('guest_reg_id');
				$autoFixSeats  = $comParams->get('auto_fix_seats');
				$currency      = $comParams->get('currency');
				$orderPrefix   = $comParams->get('order_prefix');
				$separator     = $comParams->get('separator');
				$randomOrderId = $comParams->get('random_orderid');
				$paddingCount  = $comParams->get('padding_count');

				// Lets make a random char for this order take order prefix set by admin
				$orderPrefix = (string) $orderPrefix;

				// String length should not be more than 5
				$orderPrefix = substr($orderPrefix, 0, 5);

				// Take separator set by admin
				$separator     = (string) $separator;
				$orderid_prefix = $orderPrefix . $separator;

				// Check if we have to add random number to order id
				$useRandomOrderId = (int) $randomOrderId;

				if ($useRandomOrderId)
				{
					$randomNumer = JT::utilities()->generateRandomString(5);
					$orderid_prefix .= $randomNumer . $separator;

					// Order_id_column_field_length - prefix_length - no_of_underscores - length_of_random number
					$len = (23 - 5 - 2 - 5);
				}
				else
				{
					/* This length shud be such that it matches the column lenth of primary key
					 It is used to add pading
					 order_id_column_field_length - prefix_length - no_of_underscores*/
					$len = (23 - 5 - 2);
				}

				$insertOrderId = $ordersKey = $sticketid = $orderID;
				$maxlen       = 23 - strlen($orderID) - strlen($ordersKey);
				$paddingCount = (int) $paddingCount;

				// Use padding length set by admin only if it is les than allowed(calculate) length
				if ($paddingCount > $maxlen)
				{
					$paddingCount = $maxlen;
				}

				if (strlen((string) $ordersKey) <= $len)
				{
					$append = '';

					for ($z = 0; $z < $paddingCount; $z++)
					{
						$append .= '0';
					}

					$append = $append . $ordersKey;
				}

				$resd     = new stdClass;
				$resd->id = $ordersKey;
				$orderId  = $resd->order_id = $orderid_prefix . $append;

				return $orderId;
			}
		}
		catch (Exception $e)
		{
			$this->setError(Text::_('COM_JTICKETING_DATABASE_GENERIC_SQL_ERROR'));

			return false;
		}
	}

	/**
	 * Function to get attendee info field
	 *
	 * @param   array  $eventData  eventdata
	 *
	 * @return  array|boolean  on sucess array on failure boolean
	 *
	 * @since   1.0
	 */
	public function getAttendeeInfoFields($eventData)
	{
		try
		{
			$db = Factory::getDbo();
			$query = $db->getQuery(true);
			$query->select('*');
			$query->from($db->quoteName('#__jticketing_order_items'));
			$query->where($db->quoteName('order_id') . ' = ' . $db->quote($eventData['order_id']));
			$db->setQuery($query);
			$this->orderitems = $db->loadObjectList();

			$this->fields = $this->jticketingfrontendhelper->getAllfields($eventData['eventid']);

			// Get ticket type and show ticket type title on the attendee data.
			$tickeTypeObj = JT::event($eventData['eventid'], JT::getIntegration())->getTicketTypes();

			$ticketTypeArr = array();

			foreach ($tickeTypeObj AS $key => $value)
			{
				$ticketTypeArr[$value->id] = $value->title;
			}

			if (!empty($this->orderitems))
			{
				foreach ($this->orderitems as $key => $orderitem)
				{
					$orderitemsId = $orderitem->id;

					if ($eventData['user_id'])
					{
						$paramsopass['user_id'] = $eventData['user_id'];
					}

					$attendeeId                            = $paramstopass['attendee_id'] = $orderitem->attendee_id;
					$paramstopass_ticket['order_items_id'] = $orderitemsId;

					if (file_exists(JPATH_SITE . '/components/com_jticketing/models/attendeefields.php')) { require_once JPATH_SITE . '/components/com_jticketing/models/attendeefields.php'; }
					$attendeeFieldsModel = BaseDatabaseModel::getInstance('Attendeefields', 'JticketingModel');

					// Get core and event specific field values  for this Attendee.
					$orderitemFieldValues = $attendeeFieldsModel->getUserEntryField($paramstopass);

					if (!empty($orderitemFieldValues))
					{
						foreach ($orderitemFieldValues AS $key => $orderitemFieldValue)
						{
							foreach ($orderitemFieldValue as $value)
							{
								$finalOrderItemsValue[$orderitem->attendee_id][$value->name] = $value->field_value;
							}
						}
					}

					// GetUniversal Field values for this Attendee.
					$orderitemFieldValuesUniversal = $attendeeFieldsModel->getUniversalUserEntryField($paramstopass);

					if (!empty($orderitemFieldValuesUniversal))
					{
						foreach ($orderitemFieldValuesUniversal AS $key => $orderitemFieldValueUniversal)
						{
							foreach ($orderitemFieldValueUniversal as $valueUniversal)
							{
								$finalOrderItemsValue[$orderitem->attendee_id][$valueUniversal->name] = $valueUniversal->field_value;
							}
						}
					}
				}
			}

			$orderitems = array();
			$orderitems['orderitems'] = $this->orderitems;
			$orderitems['fields'] = $this->fields;
			$orderitems['ticketTypeArr'] = $ticketTypeArr;

			return $orderitems;
		}
		catch (Exception $e)
		{
			$this->setError(Text::_('COM_JTICKETING_DATABASE_GENERIC_SQL_ERROR'));

			return false;
		}
	}

	/**
	 * Check if joomla user exists
	 *
	 * @param   INT    $orderid    orderid
	 * @param   ARRAY  $orderInfo  orderinfo array
	 *
	 * @return  Boolean true or false
	 *
	 * @since   1.0
	 */
	public function updateOrderDetails($orderid, $orderInfo = array())
	{
		try
		{
			$db = Factory::getDbo();
			$obj = new stdClass;

			$obj->id = $orderid;

			if (isset($orderInfo['user_id']))
			{
				$obj->user_id = $orderInfo['user_id'];
			}

			if (isset($orderInfo['email']))
			{
				$obj->email = $orderInfo['email'];
			}

			// Update order entry.
			if (!$db->updateObject('#__jticketing_order', $obj, 'id'))
			{
				echo $db->stderr();

				return false;
			}
			else
			{
				return true;
			}
		}
		catch (Exception $e)
		{
			$this->setError(Text::_('COM_JTICKETING_DATABASE_GENERIC_SQL_ERROR'));

			return false;
		}
	}

	/**
	 * Function to create order
	 *
	 * @param   array  $data  data
	 *
	 * @return  mixed  true on success, false on failure.
	 *
	 * @since  1.0.0
	 */
	public function createOrder($data)
	{
		try
		{
			// Check user is exist or not
			if (empty($data['user_id']))
			{
				throw new Exception(Text::_("COM_JTICKETING_USER_ID_REQUIRED"));
			}

			// Check event is exist or not
			if (empty($data['eventid']))
			{
				throw new Exception(Text::_("COM_JTICKETING_EVENT_ID_REQUIRED"));
			}

			$canBookTicket = $JT::event($data['eventid'])->isAllowedToBuy($data['user_id']);

			if (isset($data['eventid']) && $canBookTicket)
			{
				$data = $this->selectTicketType($data);

				if (!empty($data))
				{
					$result = $this->saveOrderDetails($data);

					return $result;
				}
			}
		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());

			return false;
		}
	}

	/**
	 * Function to select tickets types
	 *
	 * @param   array  $data  data
	 *
	 * @return  mixed  array on success, false on failure.
	 *
	 * @since  1.0.0
	 */
	public function selectTicketType($data)
	{
		try
		{
			$integration                 = JT::getIntegration();
			$event                       = JT::event($data['eventid'], $integration);
			$data['event_integraton_id'] = $event->integrationId;

			if (empty($data['event_integraton_id']))
			{
				throw new Exception(Text::_("COM_JTICKETING_EVENT_INTEGRATION_ID_REQUIRED"));
			}

			if (isset($data['ticket_id']))
			{
				$data['type_ticketcount'][$data['ticketid']] = 1;
				$data['type_id'][] = $data['ticket_id'];
			}
			else
			{
				$ticketTypes = $event->getTicketTypes();

				foreach ($ticketTypes as $ticketType)
				{
					if ((($ticketType->unlimited_seats == 0 && $ticketType->count != 0) || ($ticketType->unlimited_seats)) && $ticketType->price == 0)
					{
						$key = $ticketType->id;
						$data['type_ticketcount'][$key] = 1;
						$data['type_id'][] = $ticketType->id;
						break;
					}
				}

				if (empty($data['type_ticketcount']))
				{
					foreach ($ticketTypes as $ticketType)
					{
						if (($ticketType->unlimited_seats == 0 && $ticketType->count != 0) || ($ticketType->unlimited_seats))
						{
							$key = $ticketType->id;
							$data['type_ticketcount'][$key] = 1;
							$data['type_id'][] = $ticketType->id;
							break;
						}
					}
				}
			}

			if (empty($data['type_id']))
			{
				throw new Exception(Text::_("COM_JTICKETING_EVENT_TICKETS_NOT_AVAILABLE"));
			}

			return $data;
		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());

			return false;
		}
	}

	/**
	 * Function to save order details
	 *
	 * @param   array  $data  data
	 *
	 * @return  mixed  true on success, false on failure.
	 *
	 * @since  1.0.0
	 */
	public function saveOrderDetails($data)
	{
		try
		{
			$user = Factory::getUser($data['user_id']);
			isset($data['name']) ? $data['name'] : $data['name'] = $user->name;
			isset($data['email']) ? $data['email'] : $data['email'] = $user->email;
			isset($data['fnam']) ? $data['fnam'] : $data['fnam'] = $user->name;
			isset($data['email1']) ? $data['email1'] : $data['email1'] = $user->email;

			$data['coupon_code'] = '';

			if ($orderID = $this->save($data))
			{
				// To get orderitems data
				if (file_exists(JPATH_SITE . '/components/com_jticketing/models/orderitem.php')) { require_once JPATH_SITE . '/components/com_jticketing/models/orderitem.php'; }
				$orderItemsModel = BaseDatabaseModel::getInstance('Orderitem', 'JticketingModel');
				$attendeeData = array();

				if ($orderItems = $orderItemsModel->getOrderItems($orderID))
				{
					foreach ($orderItems as $key => $val)
					{
						$attendeeData['ticket_id'] = $val->type_id;
						$attendeeData['event_id'] = $data['event_integraton_id'];
						$attendeeData['owner_id'] = $user->id;
						$attendeeData['owner_email'] = $user->email;

						if (isset($val->attendee_id))
						{
							$attendeeData['id'] = $val->attendee_id;
						}

						// To save attendees data.
						if (file_exists(JPATH_SITE . '/components/com_jticketing/models/attendeeform.php')) { require_once JPATH_SITE . '/components/com_jticketing/models/attendeeform.php'; }
						$attendeesModel = BaseDatabaseModel::getInstance('AttendeeForm', 'JticketingModel');
						$attendeeId = $attendeesModel->save($attendeeData);

						$attendeeId = isset($val->attendee_id) ? $attendeeId : $val->attendee_id;

						$attendees = array();
						$attendees['order_items_id'] = $val->id;
						$attendees['ticket_type'] = $val->type_id;

						$attendees['1'] = $data['name'];
						$attendees['2'] = '';
						$attendees['3'] = '';
						$attendees['4'] = $data['email1'];
						$attendees['user_id'] = $data['user_id'];
						$attendees['order_id'] = $orderID;

						if ($attendeeId)
						{
							$attendees['attendee_id'] = (int) $attendeeId;

							if (file_exists(JPATH_SITE . '/components/com_jticketing/models/orderitem.php')) { require_once JPATH_SITE . '/components/com_jticketing/models/orderitem.php'; }
							$ordrItemModel = BaseDatabaseModel::getInstance('Orderitem', 'JticketingModel');
							$ordrItemModel->updateorderItems($attendees);

							if (file_exists(JPATH_SITE . '/components/com_jticketing/models/attendeefieldvalues.php')) { require_once JPATH_SITE . '/components/com_jticketing/models/attendeefieldvalues.php'; }
							$attendeeFieldValuesModel = BaseDatabaseModel::getInstance('Attendeefieldvalues', 'JticketingModel');
							$attendeeFieldValuesModel->save($attendees);
						}
					}
				}

				if (isset($data['user_id']))
				{
					$this->saveUserDetails($orderID, $data, $user);
				}

				return true;
			}
			else
			{
				throw new Exception(Text::_("COM_JTICKETING_EVENT_TICKETS_ORDER_FAIL_TO_SAVE"));
			}
		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());

			return false;
		}
	}

	/**
	 * Function to save user details
	 *
	 * @param   integer  $orderId  order id
	 *
	 * @param   array    $data     data
	 *
	 * @param   object   $user     user object
	 *
	 * @return  boolean  false when orderid not present.
	 *
	 * @since  1.0.0
	 */
	public function saveUserDetails($orderId, $data, $user)
	{
		try
		{
			if (empty($orderId))
			{
				return false;
			}

			$orderData = $this->getItem($orderId);
			$flag = 0;

			if ($orderData->amount != 0)
			{
				$flag = 1;
			}

			if (($orderData->amount == '0' && !empty($user->id)) || $flag == 1)
			{
				if (file_exists(JPATH_SITE . '/components/com_jticketing/helpers/common.php')) { require_once JPATH_SITE . '/components/com_jticketing/helpers/common.php'; }
				$JticketingCommonHelper = new JticketingCommonHelper;
				$result = $JticketingCommonHelper->createFreeTicket($user->id, $orderId, $flag);

				$data['url'] = $result;
			}

			$data['order_id']        = $orderId;
			$data['checkout_method'] = 'register';
			isset($data['comment']) ? $data['comment'] : $data['comment'] = '';

			// To save user data.
			if (file_exists(JPATH_SITE . '/components/com_jticketing/models/user.php')) { require_once JPATH_SITE . '/components/com_jticketing/models/user.php'; }
			$userModel = BaseDatabaseModel::getInstance('User', 'JticketingModel');

			if (!$userModel->save($data))
			{
				throw new Exception(Text::_("COM_JTICKETING_EVENT_TICKETS_USER_FAIL_TO_SAVE"));
			}
		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());
		}
	}

	/**
	 * Function to to update order and perform relative action after that.
	 *
	 * @param   JTicketingOrder  $order  Object of order
	 *
	 * @return  boolean  false when orderid not present.
	 *
	 * @since  2.5.0
	 */
	public function onAfterOrderComplete($order)
	{
		$comParams 				= JT::config();
		$socialIntegration 		= $comParams->get('integrate_with', 'none');
		$streamBuyTicket   		= $comParams->get('streamBuyTicket', 0);

		// Load order and update the order status.
		$eventDetails 		= JT::event()->loadByIntegration($order->event_details_id);

		// Update coupon count if coupon on current order is applied.
		if (!empty($order->getCouponCode()))
		{
			$coupon 		= JT::Coupon();

			$coupon->loadByCode($order->getCouponCode());
			$coupon->used += 1;

			if (!$coupon->save())
			{
				$this->setError($coupon->getError());

				return false;
			}
		}

		// Update the attendee status.
		if (!$order->updateAttendeeStatus())
		{
			$this->setError($order->getError());

			return false;
		}

		// Decrement the ticket type count.
		if (!$this->decrementTicketCount($order))
		{
			$this->setError("COM_JTICKETING_DECRESEING_TICKET_TYPE_COUNT");

			return false;
		}

		// Updating the attendee count in the integration.
		if (!$this->eventupdate($order, $order->user_id))
		{
			$this->setError($this->getError());

			return false;
		}

		// Adding entry for storing user activity.
		if ($socialIntegration != 'none' && $streamBuyTicket == 1 && !empty($order->user_id))
		{
			// Add in activity.
			$libClass    = $this->getJticketSocialLibObj();
			$eventLink   = '<a class="" href="' . $eventDetails->getUrl() . '">' . $eventDetails->getTitle() . '</a>';
			$originalMsg = Text::sprintf('COM_JTICKETING_PURCHASED_TICKET', $eventLink);
			$libClass->pushActivity($order->user_id, '', '', $originalMsg, '', '', 0);
		}

		// send order status mail only if order status is changed manually.
		if (Factory::getSession()->get('orderByEventBuyer') == 0)
		{
			// Send order status change Email
			JticketingMailHelper::sendOrderStatusEmail($order->id, 'C');
		}

		// Send Ticket Email.
		if (!$eventDetails->isOnline())
		{
			JticketingMailHelper::sendmailnotify($order->id, 'afterordermail');
		}

		JticketingMailHelper::sendInvoiceEmail($order->id);

		JticketingMailHelper::sendEventPurchaseEmail($order->id);

		PluginHelper::importPlugin('jticketing');
		Factory::getApplication()->triggerEvent('onAfterJtOrderComplete', array($order, false));

		$this->addPayoutEntry($order->id);

		return true;
	}

	/**
	 * Update sales data
	 *
	 * @param   JTicketingOrder  $order  order object
	 *
	 * @return  false
	 *
	 * @since  2.5.0
	 */
	private function decrementTicketCount($order)
	{
		if (!empty($order))
		{
			$orderDetails 	= $order->getItems();

			foreach ($orderDetails as $orderdetail)
			{
				$ticketType 		= JT::Tickettype($orderdetail->type_id);

				if (!$ticketType->unlimited_seats)
				{
					$ticketType->count 	= max(0, $ticketType->count - $orderdetail->ticketcount);

					$ticketType->save();
				}
			}

			return true;
		}

		return false;
	}

	/**
	 * Update event data for jomsocial and other integration for event members
	 *
	 * @param   JTicketingOrder  $order      object of order
	 * @param   int              $member_id  user id of payee
	 *
	 * @return  boolean
	 *
	 * @since  2.5.0
	 */
	private function eventupdate($order, $member_id)
	{
		$comParams             	= JT::config();
		$guestRegId           	= $comParams->get('guest_reg_id');
		$autoFixSeats         	= $comParams->get('auto_fix_seats');
		$affectJsNativeSeats 	= $comParams->get('affect_js_native_seats');
		$integration = $comParams->get('integration');
		$eventDetails 			= JT::event()->loadByIntegration($order->event_details_id);

		if ($integration == COM_JTICKETING_CONSTANT_INTEGRATION_JOMSOCIAL && $affectJsNativeSeats == 1)
		{
			$eventData = JT::event()->loadByIntegration($order->event_details_id);
			$db         = Factory::getDbo();
			$query 		= $db->getQuery(true);
			$query->select($db->qn('confirmedcount'));
			$query->from($db->qn('#__community_events'));
			$query->where($db->qn('id') . ' = ' . $db->quote($eventData->getId()));
			$db->setQuery($query);
			$cnt		= $db->loadResult();

			$arr                 = new stdClass;
			$arr->id             = $eventData->getId();
			$arr->confirmedcount = $cnt + $order->getTicketsCount();
			$db->updateObject('#__community_events', $arr, 'id');

			// Added for bug fix for bug  #12043
			if ($member_id)
			{
				$query 		= $db->getQuery(true);
				$query->select($db->qn('id'));
				$query->from($db->qn('#__community_events_members'));
				$query->where($db->qn('eventid') . ' = ' . $db->quote($eventData->getId()));
				$query->where($db->qn('memberid') . ' = ' . $db->quote($member_id));
				$query->where($db->qn('status') . ' = 0');
				$db->setQuery($query);
				$invitedRowId = $db->loadResult();

				if ($invitedRowId)
				{
					$update 			= new stdClass;
					$update->id 		= $invitedRowId;
					$update->status 	= 1;

					$db->updateObject('#__community_events_members', $update, 'id');
					$db->setQuery($query);
					$db->execute($query);
				}

				if ($order->getTicketsCount())
				{
					for ($i = 0; $i < $order->getTicketsCount(); $i++)
					{
						// Get site admin id
						if (!$member_id)
						{
							if (!empty($guestRegId))
							{
								$member_id = $guestRegId;
							}
						}

						if (!$member_id)
						{
							continue;
						}

						$query 		= $db->getQuery(true);
						$query->select($db->qn('id'));
						$query->from($db->qn('#__community_events_members'));
						$query->where($db->qn('eventid') . ' = ' . $db->quote($eventData->getId()));
						$query->where($db->qn('memberid') . ' = ' . $db->quote($member_id));
						$query->where($db->qn('status') . ' =1');

						$db->setQuery($query);
						$memberAlreadyPresent = $db->loadResult();

						if ($memberAlreadyPresent)
						{
							continue;
						}

						$dat             = new stdClass;
						$dat->id         = '';
						$dat->eventid    = $eventData->getId();
						$dat->memberid   = $member_id;
						$dat->status     = 1;
						$dat->permission = 3;
						$dat->invited_by = 0;
						$dat->approval   = 0;
						$dat->created    = Factory::getDate()->toSql();

						if (!$db->insertObject('#__community_events_members', $dat, 'id'))
						{
							echo $db->stderr();
						}
					}
				}
			}

			// For bug fix for bug  #12043
			if ($autoFixSeats == '1')
			{
				$this->fixJsSeatCount($order->event_details_id);
			}
		}
		elseif ($integration == 2)
		{
			if (!empty($order->user_id))
			{
				$eventFormModel = JT::model('EventForm');

				// Update the jiketodo.
				$eventData                = array();
				$eventData['eventId']     = $eventDetails->getId();
				$eventData['eventTitle']  = $eventDetails->getTitle();
				$eventData['startDate']   = $eventDetails->getStartDate();
				$eventData['endDate']     = $eventDetails->getEndDate();
				$eventData['assigned_to'] = $order->user_id;

				// Insert or update jiketodo depends on the data passed
				$eventFormModel->saveTodo($eventData);
			}

			// Email and event related opreations.
			$activityData 				= array();
			$activityData['status'] 	= 'C';
			$orderId 					= $order->order_id;

			// Trigger After Process Payment
			PluginHelper::importPlugin('system');

			// Old Trigger
			Factory::getApplication()->triggerEvent('onJtAfterProcessPayment', array($activityData, $orderId, $pg_plugin = ''));

			// New Trigger
			Factory::getApplication()->triggerEvent('onAfterJtProcessPayment', array($activityData, $orderId, $pg_plugin = ''));

			// If online event create user on adobe site and register for this event
			if ($eventDetails->isOnline() && $order->user_id)
			{
				// Get Order items
				$orderItemData = $order->getItems(true);

				if (!empty($orderItemData))
				{
					foreach ($orderItemData as $orderData)
					{
						$attendee  = JT::Attendee($orderData->attendee_id);

						/**
						 *  @TODO need better architecture for now we only have one attendee in case of multiple atendee this will fail
						 *
						 *  Also there is no error handling done for failed enrollments
						 */
						if (!$eventDetails->addAttendee($attendee))
						{
							$this->setError($eventDetails->getError());

							return false;
						}

						// Trigger email
						JticketingMailHelper::onlineEventNotify($order, $attendee, $eventDetails);
					}
				}
			}
		}
		elseif ($integration == 4 && $affectJsNativeSeats == 1)
		{
			// Update easysocial attendee count
			$path = JPATH_ADMINISTRATOR . '/components/com_easysocial/includes/easysocial.php';
			JLoader::register('ES', $path, true);

			$eventId = $eventDetails->getId();
			$userId  = $order->user_id;

			if ($order->getStatus() == COM_JTICKETING_CONSTANT_ORDER_STATUS_COMPLETED)
			{
				$task = 'going';
			}
			elseif($order->getStatus() == COM_JTICKETING_CONSTANT_ORDER_STATUS_PENDING)
			{
				$task = 'maybe';
			}
			else
			{
				$task = 'notgoing';
			}

			if (!empty($userId))
			{
				$event   = ES::event($eventId);
				$event->rsvp($task, $userId);
			}
		}

		return true;
	}

	/**
	 * Get Social library object
	 *
	 * @param   integer  $integrationOption  this may be joomla,jomsocial,Easysocial
	 *
	 * @return  object social library
	 *
	 * @since  2.5.0
	 *
	 */
	public function getJticketSocialLibObj($integrationOption = '')
	{
		$jtParams 			= JT::config();
		$integrationOption 	= $jtParams->get('integrate_with', 'none');

		if ($integrationOption == 'Community Builder')
		{
			$SocialLibraryObject = new JSocialCB;
		}
		elseif ($integrationOption == 'JomSocial')
		{
			$SocialLibraryObject = new JSocialJomsocial;
		}
		elseif ($integrationOption == 'Jomwall')
		{
			$SocialLibraryObject = new JSocialJomwall;
		}
		elseif ($integrationOption == 'EasySocial')
		{
			$SocialLibraryObject = new JSocialEasysocial;
		}
		elseif ($integrationOption == 'none')
		{
			$SocialLibraryObject = new JSocialJoomla;
		}

		return $SocialLibraryObject;
	}

	/**
	 * Method to save the consent of the user in tjprivacy
	 *
	 * @param   JTicketingOrder  $order  Jticketing Order object to add order info
	 *
	 * @return  Boolean true on success
	 *
	 * @since  2.5.0
	 *
	 */
	public function saveConsent($order)
	{
		// Create privacy data format and save using model.
		$userPrivacyData 				= array();
		$userPrivacyData['client'] 		= 'com_jticketing.order';
		$userPrivacyData['purpose'] 	= Text::_('COM_JTICKETING_USER_PRIVACY_TERMS_PURPOSE_FOR_ORDER');

		/* later pass privacy_terms_condition value 1*/
		$userPrivacyData['accepted'] 	= 1;
		$userPrivacyData['user_id'] 	= $order->user_id;
		$userPrivacyData['date'] 		= Factory::getDate()->toSql();
		$userPrivacyData['client_id'] 	= $order->id;

		if (file_exists(JPATH_SITE . '/components/com_tjprivacy/models/tjprivacy.php')) { require_once JPATH_SITE . '/components/com_tjprivacy/models/tjprivacy.php'; }
		$tjprivacyModel = BaseDatabaseModel::getInstance('Tjprivacy', 'TjprivacyModel', array('ignore_request' => true));

		if (!$tjprivacyModel->save($userPrivacyData))
		{
			$this->setError($tjprivacyModel->getError());

			return false;
		}

		return true;
	}

	/**
	 * Method to get the full form of the order statuses
	 *
	 * @param   String  $type  The type of status array you want, default is 'fullforms' for key as status and full form of status as Value
	 * 'statuses' is for only status as Value
	 * 'list' is for select list of status.
	 *
	 * @return  array array of the order status full forms and on failed condition boolean false
	 *
	 * @since  2.5.0
	 *
	 */
	public function getOrderStatues($type = 'fullforms')
	{
		if (empty($type))
		{
			return array();
		}

		$statues = array(COM_JTICKETING_CONSTANT_ORDER_STATUS_PENDING,
					COM_JTICKETING_CONSTANT_ORDER_STATUS_COMPLETED,
					COM_JTICKETING_CONSTANT_ORDER_STATUS_DECLINE,
					COM_JTICKETING_CONSTANT_ORDER_STATUS_FAILED,
					COM_JTICKETING_CONSTANT_ORDER_STATUS_UNDER_REVIEW,
					COM_JTICKETING_CONSTANT_ORDER_STATUS_REFUND,
					COM_JTICKETING_CONSTANT_ORDER_STATUS_CANCEL_REVERSED,
					COM_JTICKETING_CONSTANT_ORDER_STATUS_REVERSED,
					COM_JTICKETING_CONSTANT_ORDER_STATUS_INCOMPLETE
				);

		$fullForms = array(Text::_('JT_PSTATUS_PENDING'),
						Text::_('JT_PSTATUS_COMPLETED'),
						Text::_('JT_PSTATUS_DECLINED'),
						Text::_('JT_PSTATUS_FAILED'),
						Text::_('JT_PSTATUS_UNDERREVIW'),
						Text::_('JT_PSTATUS_REFUNDED'),
						Text::_('JT_PSTATUS_CANCEL_REVERSED'),
						Text::_('JT_PSTATUS_REVERSED'),
						Text::_('JT_PSTATUS_INITIATED')
					);

		if ($type === 'fullforms')
		{
			// In case status as key and full form of status as value is required.
			return array_combine($statues, $fullForms);
		}
		elseif ($type === 'statuses')
		{
			// In case array with only status as value required.
			return $statues;
		}

		// In case of select list is required. Default return the list
		$selectList 	= array();

		// Set default select option.
		$selectList[] 	= HTMLHelper::_('select.option', '0', Text::_('SEL_PAY_STATUS'));

		foreach ($statues as $key => $value)
		{
			$selectList[] = HTMLHelper::_('select.option', $value, $fullForms[$key]);
		}

		return $selectList;
	}

	/**
	 * Method to delete the billing information agianst the order
	 *
	 * @param   JTicketingOrder  $order  The order object
	 *
	 * @return  mixed    false on failure
	 *
	 * @since   2.5.0
	 */
	public function deleteBillingInfo(JTicketingOrder $order)
	{
		if (!$order->id)
		{
			return false;
		}

		/** We may have multiple billing info against the same order
		 * Performing a simple delete query because we don't have primary/unique key on order_id
		 */
		$query = $this->_db->getQuery(true);
		$query->delete($this->_db->qn('#__jticketing_users'));
		$query->where($this->_db->qn('order_id') . '=' . (int) $order->id);
		$this->_db->setQuery($query);

		return $this->_db->execute();
	}

	/**
	 * Fix database member count values for jomsocial
	 *
	 * @param   int  $eventId  eventId
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION
	 */
	public function fixJsSeatCount($eventId)
	{
		// Remove inconsitent entries from community event members table
		$db = Factory::getDbo();

		// Load list of all user who bought tickets for event
		$ordersModel = JT::model('orders');
		$orders = $ordersModel->getOrders(array('event_details_id' => $eventId));

		// Calculate ticket count for all users who bought tickets
		$members = array();

		foreach ($orders as $order)
		{
			$eventData = JT::event()->loadByIntegration($order->event_details_id);

			if (!array_key_exists($order->user_id, $members))
			{
				$members[$order->user_id]['ticketscount'] = $order->ticketscount;
			}
			else
			{
				$members[$order->user_id]['ticketscount'] = $members[$order->user_id]['ticketscount'] + $order->ticketscount;
			}

			$members[$order->user_id]['eventId'] = $eventData->getId();
		}

		$todelete = array();

		foreach ($members as $key => $val)
		{
			// Get all event members of type mebmer(permission=3)
			$query = $db->getQuery(true);
			$query->select($db->quoteName(array('id','status','invited_by')));
			$query->from($db->quoteName('#__community_events_members'));
			$query->where($db->quoteName('eventid') . ' = ' . $db->quote($val['eventId']));
			$query->where($db->quoteName('memberid') . ' = ' . $db->quote($key));
			$query->where($db->quoteName('permission') . ' = 3');
			$db->setQuery($query);
			$memberData = $db->loadObjectlist();

			// If count of a members attendance is greater than no. of tickets he bought
			if (count($memberData) > $val['ticketscount'])
			{
				$count = 0;

				foreach ($memberData as $member)
				{
					$count++;

					// If count of a members attendance is greater than no. of tickets he bought
					if ($count > $val['ticketscount'])
					{
						$todelete[] = $member->id;
					}
				}
			}
		}

		// Delete all rows calculated above
		if (!empty($todelete))
		{
			if (count($todelete) > 0)
			{
				$toDelete = implode(',', $todelete);
				$query = $db->getQuery(true);
				$query->delete($db->quoteName('#__community_events_members'));
				$query->where($db->quoteName('id') . ' IN (' . $toDelete . ')');
				$db->setQuery($query);
				$db->execute();
			}
		}

		return true;
	}

	/**
	 * Update payout entry
	 *
	 * @param   integer  $orderId  id for jticketing_order
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function addPayoutEntry($orderId)
	{
		$order 				 = JT::order($orderId);
		$pg_plugin			 = $order->processor;
		$plugin				 = PluginHelper::getPlugin('payment', $pg_plugin);
		$params 			 = ComponentHelper::getParams('com_jticketing');
		$handle_transactions = $params->get('handle_transactions');

		if ($pg_plugin == 'adaptive_paypal' || ($pg_plugin == 'paypal' && $handle_transactions == 1))
		{
			// Lets set the paypal email if admin is not handling transactions
			$jticketingmainhelper 	 = new jticketingmainhelper;
			$adaptiveDetails 	 = $jticketingmainhelper->getorderEventInfo($order->id);
			$com_params 	 = ComponentHelper::getParams('com_jticketing');
			$currency 	 = $com_params->get('currency');
			BaseDatabaseModel::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_tjvendors/models', 'payout');
			$tjvendorsModelPayout 	 = BaseDatabaseModel::getInstance('Payout', 'TjvendorsModel');

			foreach ($adaptiveDetails as $userReport)
			{
				$vendor_id  	 = $userReport['vendor'];
				$tjvendorsHelper = new TjvendorsHelper;
				$payableAmount 	 = $tjvendorsHelper->getTotalAmount($vendor_id, $currency, 'com_jticketing');

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

				$params 				 = array("customer_note" => $customerNote, "entry_status" => "debit_payout");
				$newPayoutData['params'] = json_encode($params);
				$tjvendorsModelPayout->save($newPayoutData);
			}
		}
	}

	/**
	 * Function to to update order and perform relative action after that.
	 *
	 * @param   JTicketingOrder  $order  Object of order
	 *
	 * @return  boolean  false when orderid not present.
	 *
	 * @since  2.5.0
	 */
	public function onAfterOrderCreated($order)
	{
		// Update coupon count if coupon on current order is applied.
		if (!empty($order->getCouponCode()))
		{
			$coupon 		= JT::Coupon();

			$coupon->loadByCode($order->getCouponCode());
			$coupon->used += 1;

			if (!$coupon->save())
			{
				$this->setError($coupon->getError());

				return false;
			}
		}

		return true;
	}

	/**
	 * Function to to update order and perform relative action after that.
	 *
	 * @param   JTicketingOrder  $order  Object of order
	 *
	 * @return  boolean  false when orderid not present.
	 *
	 * @since  2.5.0
	 */
	public function onAfterOrderFailed($order)
	{
		// Update coupon count if coupon on current order is applied.
		if (!empty($order->getCouponCode()))
		{
			$coupon 		= JT::Coupon();

			$coupon->loadByCode($order->getCouponCode());
			$coupon->used -= 1;

			if (!$coupon->save())
			{
				$this->setError($coupon->getError());

				return false;
			}
		}

		return true;
	}

	/**
	 * Function to add entry number once order is confirmed.
	 *
	 * @param   JTicketingOrder  $order  Object of order
	 *
	 * @return  boolean  false when orderid not present.
	 *
	 * @since  2.5.0
	 */
	public function onAfterOrderConfirmedAddEntryNumber($order)
	{
		$com_params  = JT::config();
		$integration = JT::getIntegration(true);

		$entryNumbeAssignment = $com_params->get('entry_number_assignment', 0,'INT');
		if ($entryNumbeAssignment && $order->getStatus() == COM_JTICKETING_CONSTANT_ORDER_STATUS_COMPLETED)
		{
			$db    = Factory::getDbo();
			$query = $db->getQuery(true);
			$query->select($db->quoteName(array('id','type_id', 'order_id','attendee_id','entry_number')));
			$query->from($db->quoteName('#__jticketing_order_items'));
			$query->where($db->quoteName('order_id') . ' = ' . $db->quote($order->id));
			$db->setQuery($query);
			$orderitems = $db->loadObjectlist();

			if (!empty($orderitems))
			{
				foreach ($orderitems as $key => $orderitem)
				{
					try
					{
						$db->setQuery("SET SESSION TRANSACTION ISOLATION LEVEL SERIALIZABLE")->execute();
						$db->transactionStart();				
						Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jticketing/tables');
						$ticketType = Table::getInstance('TicketTypes', 'JticketingTable', array('dbo', $db));
						$ticketType->load(array('id' => $orderitem->type_id));

						$event = JT::event()->loadByIntegration($order->event_details_id);
						$updatedEntryNumber = 1;

						// Step 2: Fetch and lock relevant order items
						$query = $db->getQuery(true)
							->select('*')
							->from($db->quoteName('#__jticketing_order_items'))
							->where($db->quoteName('type_id') . ' = ' . $db->quote($orderitem->type_id));
							$query = $query . ' FOR UPDATE';

						$db->setQuery($query);
						$lockedRows = $db->loadObjectList();

						if ($ticketType && $ticketType->allow_ticket_level_sequence)
						{
							if ($ticketType->start_number_for_sequence)
							{
								$updatedEntryNumber = $ticketType->start_number_for_sequence;
							}

							$db    = Factory::getDbo();
							$query = $db->getQuery(true);

							if (is_numeric($updatedEntryNumber)) {
								$query->select('MAX(CAST(' . $db->quoteName('entry_number') . ' AS UNSIGNED)) AS max_value');
							} 
							else 
							{
								$query->select('MAX(' . $db->quoteName('entry_number') . ') AS max_value');
							}

							$query->from($db->quoteName('#__jticketing_order_items'))
								->where($db->quoteName('id') . ' != ' . $db->quote($orderitem->id))
								->where($db->quoteName('type_id') . ' = ' . $db->quote($orderitem->type_id));

							$db->setQuery($query);
							$maxEntryNumber = $db->loadResult();

							if ($maxEntryNumber)
							{
								$updatedEntryNumber = $this->getNextValue($maxEntryNumber);
							}
						}
						else
						{
							if ($integration == '2')
							{
								if ($event->start_number_for_event_level_sequence)
								{
									$updatedEntryNumber = $event->start_number_for_event_level_sequence;
								}
							}
							else
							{
								if ($ticketType->start_number_for_sequence)
								{
									$updatedEntryNumber = $ticketType->start_number_for_sequence;
								}
							}

							$db = Factory::getDbo();
							$query = $db->getQuery(true);
							$query->select($db->quoteName('id'))
								->from($db->quoteName('#__jticketing_types'))
								->where($db->quoteName('eventid') . ' = ' . $db->quote($order->event_details_id))
								->where($db->quoteName('allow_ticket_level_sequence') . ' = 0');
								$db->setQuery($query);
							$results = $db->loadColumn();

							$db    = Factory::getDbo();
							$query = $db->getQuery(true);

							if (is_numeric($updatedEntryNumber)) {
								$query->select('MAX(CAST(' . $db->quoteName('entry_number') . ' AS UNSIGNED)) AS max_value');
							} 
							else 
							{
								$query->select('MAX(' . $db->quoteName('entry_number') . ') AS max_value');
							}

							$query->from($db->quoteName('#__jticketing_order_items'))
								->where($db->quoteName('id') . ' != ' . $db->quote($orderitem->id))
								->where($db->quoteName('type_id') . ' IN (' . implode(',', array_map([$db, 'quote'], $results)) . ')');

							$db->setQuery($query);
							$maxEntryNumber = $db->loadResult();

							if ($maxEntryNumber)
							{
								$updatedEntryNumber = $this->getNextValue($maxEntryNumber);
							}
						}

						$orderItem = JT::orderitem($orderitem->id);
						$orderItem->entry_number = $updatedEntryNumber;

						if (!$orderItem->save())
						{
							$this->setError($orderItem->getError());
							$db->transactionRollback();

							return false;
						}

						// Commit the transaction
    					$db->transactionCommit();
					}
					catch (Exception $e) {
						// Rollback the transaction on error
						$db->transactionRollback();
					
						// Log or display the error
						$this->setError($e->getMessage());
						return false;
					}
				}
			}
		}

		return true;
	}

	/**
	 * Function to get next value for entry numbers.
	 *
	 * @param   String  $input  last added entry number
	 *
	 * @return  String  next gererated value.
	 *
	 */
	protected function getNextValue($input)
	{
		if ($input == '9')
		{
			return '10';
		}

		// Split the input into an array of characters
		$chars = str_split($input);
		$length = count($chars);
	
		// Traverse from the last character to the first
		for ($i = $length - 1; $i >= 0; $i--) {
			// If it's a digit
			if (ctype_digit($chars[$i])) {
				if ($chars[$i] < '9') {
					$chars[$i]++; // Increment digit
					return implode('', $chars);
				} else {
					$chars[$i] = '0'; // Carry over
				}
			}
			// If it's a letter
			elseif (ctype_alpha($chars[$i])) {
				if ($chars[$i] === 'z') {
					$chars[$i] = 'a'; // Wrap around for lowercase
				} elseif ($chars[$i] === 'Z') {
					$chars[$i] = 'A'; // Wrap around for uppercase
				} else {
					$chars[$i] = chr(ord($chars[$i]) + 1); // Increment letter
					return implode('', $chars);
				}
			} else {
				// Non-alphanumeric characters are not handled; return input as is
				return $input;
			}
		}
	
		// If all characters rolled over (e.g., 999 -> 000), prepend a new character
		if (ctype_digit($input)) {
			return '1' . implode('', $chars);
		} elseif (ctype_alpha($input)) {
			return (ctype_lower($input[0]) ? 'a' : 'A') . implode('', $chars);
		}
	
		return $input; // Default fallback
	}
}
