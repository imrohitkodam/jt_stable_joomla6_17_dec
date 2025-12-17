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

defined('_JEXEC') or die(';)');
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Plugin\PluginHelper;

if (file_exists(JPATH_SITE . '/components/com_jticketing/helpers/common.php')) { require_once JPATH_SITE . '/components/com_jticketing/helpers/common.php'; }
if (file_exists(JPATH_SITE . '/components/com_jticketing/helpers/main.php')) { require_once JPATH_SITE . '/components/com_jticketing/helpers/main.php'; }

/**
 * Model for buy for creating order and other
 *
 * @package     JTicketing
 * @subpackage  component
 * @since       1.0
 */
class JticketingModelUser extends AdminModel
{
	public $jticketingmainhelper;

	public $jticketingfrontendhelper;

	public $jticketingCommonHelper;

	/**
	 * Constructor
	 *
	 * @since   1.0
	 */
	public function __construct()
	{
		parent::__construct();
		$this->jticketingfrontendhelper = new jticketingfrontendhelper;
		$this->jticketingCommonHelper = new JticketingCommonHelper;
		$this->jticketingmainhelper = new Jticketingmainhelper;

		$TjGeoHelper = JPATH_ROOT . '/components/com_tjfields/helpers/geo.php';

		if (!class_exists('TjGeoHelper'))
		{
			JLoader::register('TjGeoHelper', $TjGeoHelper);
			JLoader::load('TjGeoHelper');
		}

		$this->TjGeoHelper = new TjGeoHelper;
		$this->_db         = Factory::getDbo();
		$this->filter = InputFilter::getInstance();
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
		$form = $this->loadForm('com_jticketing.user', 'user', array('control' => 'jform', 'load_data' => $loadData));

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
	public function getTable($type = 'User', $prefix = 'JticketingTable', $config = array())
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
	 * @param   data  $data  TO  ADD
	 *
	 * @return void|boolean  on success void on failure false
	 *
	 * @since    1.6
	 */
	public function save($data = '')
	{
		$com_params  = ComponentHelper::getParams('com_jticketing');
		$integration = $com_params->get('integration');
		$socialIntegration = $com_params->get('integrate_with', 'none');
		$streamBuyTicket = $com_params->get('streamBuyTicket', 0);
		$session = Factory::getSession();

		// Save customer note
		$odata                   = new StdClass;
		$odata->id               = $data['order_id'];
		$odata->customer_note    = $data['comment'];

		if (!$this->_db->updateObject('#__jticketing_order', $odata, 'id'))
		{
		}

		$orderId = $data['order_id'];

		// Guest chckout.
		if ($data['checkout_method'] == 'guest')
		{
			if ($orderId)
			{
				$orderInfo = array();
				$orderInfo['id'] = $orderId;
				$orderInfo['email'] = $data['email1'];

				// Update the order details.
				if (file_exists(JPATH_SITE . '/components/com_jticketing/models/order.php')) { require_once JPATH_SITE . '/components/com_jticketing/models/order.php'; }
				$orderModel = BaseDatabaseModel::getInstance('Order', 'JticketingModel');
				$orderModel->updateOrderDetails($orderId, $orderInfo);

				// Get order items for this order.
				if (file_exists(JPATH_SITE . '/components/com_jticketing/models/orderitem.php')) { require_once JPATH_SITE . '/components/com_jticketing/models/orderitem.php'; }
				$ordrItemModel = BaseDatabaseModel::getInstance('Orderitem', 'JticketingModel');
				$orderItems = $ordrItemModel->getOrderItems($orderId);

				// Update attendeeform with owner_email
				if (count($orderItems))
				{
					foreach ($orderItems as $orderItem)
					{
						if (file_exists(JPATH_SITE . '/components/com_jticketing/models/attendeeform.php')) { require_once JPATH_SITE . '/components/com_jticketing/models/attendeeform.php'; }
						$attendeeFormModel = BaseDatabaseModel::getInstance('AttendeeForm', 'JticketingModel');
						$attendeeFormModel->updateAttendeeOwner($orderItem->attendee_id, $ownerId = 0, $ownerEmail = $data['email1']);
					}
				}
			}
		}

		// Register new account.
		elseif ($data['checkout_method'] == 'register')
		{
			$userid = 0;
			$userid = $this->registerUser($data);

			if (!$userid)
			{
				return false;
			}
			else
			{
				if ($orderId)
				{
					$orderInfo = array();
					$orderInfo['id'] = $orderId;
					$orderInfo['user_id'] = isset($data['user_id']) ? Factory::getUser()->id : $data['user_id'];

					// Update the order details.
					if (file_exists(JPATH_SITE . '/components/com_jticketing/models/order.php')) { require_once JPATH_SITE . '/components/com_jticketing/models/order.php'; }
					$orderModel = BaseDatabaseModel::getInstance('Order', 'JticketingModel');
					$orderModel->updateOrderDetails($orderId, $orderInfo);

					// Get order items for this order.
					if (file_exists(JPATH_SITE . '/components/com_jticketing/models/orderitem.php')) { require_once JPATH_SITE . '/components/com_jticketing/models/orderitem.php'; }
					$ordrItemModel = BaseDatabaseModel::getInstance('Orderitem', 'JticketingModel');
					$orderItems = $ordrItemModel->getOrderItems($orderId);

					// Update attendeeform with owner_email
					if (count($orderItems))
					{
						foreach ($orderItems as $orderItem)
						{
							if (file_exists(JPATH_SITE . '/components/com_jticketing/models/attendeeform.php')) { require_once JPATH_SITE . '/components/com_jticketing/models/attendeeform.php'; }
							$attendeeFormModel = BaseDatabaseModel::getInstance('AttendeeForm', 'JticketingModel');
							$attendeeFormModel->updateAttendeeOwner($orderItem->attendee_id, $ownerId = $data['user_id'], $ownerEmail = '');
						}
					}
				}
			}
		}

		if ($orderId)
		{
			$res = $this->billingaddr($data['user_id'], $data, $orderId);
			$userTable = $this->getTable();
			$userTable->bind($res);

			// Check and store the object.
			if (!$userTable->check())
			{
				if ($userTable->getError())
				{
					$this->setError($userTable->getError());

					return false;
				}
			}

			$row = (object) $res;

			if (!$this->_db->insertObject('#__jticketing_users', $row, 'id'))
			{
				echo $this->_db->stderr();
			}

			$params = ComponentHelper::getParams('com_jticketing');

			// Save customer note in order table
			$order = new stdClass;

			if ($orderId)
			{
				$order->id            = $orderId;
				$order->customer_note = empty($data['comment']) ? '' : $this->filter->clean($data['comment'], 'string');

				if ($data['user_id'])
				{
					$order->name  = Factory::getUser($data['user_id'])->name;
					$order->email = Factory::getUser($data['user_id'])->email;
				}
				else
				{
					$order->name  = $this->filter->clean($data['fnam'], 'string') . " "
					. $this->filter->clean($data['lnam'], 'string');
					$order->email = $this->filter->clean($data['email1'], 'string');
				}

				if (!$this->_db->updateObject('#__jticketing_order', $order, 'id'))
				{
					echo $this->_db->stderr();
				}
			}

			// TRIGGER After Billing data save
			PluginHelper::importPlugin('system');
			Factory::getApplication()->triggerEvent('onAfterJtBillingsaveData', array($data, $_POST, $res['order_id'], $data['user_id']));
		}
	}

	/**
	 * Register new user while creating order
	 *
	 * @param   ARRAY  $regdata1  regdata1 contains user entered email
	 *
	 * @return  int    $userid
	 *
	 * @since   1.0
	 */
	public function registerUser($regdata1)
	{
		$regdata['fnam']       = $regdata1['firstname'];
		$regdata['user_name']  = $regdata1['user_email'];
		$regdata['user_email'] = $regdata1['user_email'];
		$registrationModel = JT::model('registration');

		if (!$registrationModel->store($regdata))
		{
			return false;
		}

		$user = Factory::getUser();

		return $userid = $user->id;
	}

	/**
	 * Check if joomla user exists
	 *
	 * @return  Boolean true or false
	 *
	 * @since   1.0
	 */
	public function getUpdatedBillingInfo()
	{
		// First update the current orderid with new logged in user id.
		$session  = Factory::getSession();
		$orderId  = $session->get('JT_orderid');

		if ($orderId)
		{
			$orderInfo = array();
			$orderInfo['user_id'] = Factory::getUser()->id;

			// Update the order details.
			if (file_exists(JPATH_SITE . '/components/com_jticketing/models/order.php')) { require_once JPATH_SITE . '/components/com_jticketing/models/order.php'; }
			$orderModel = BaseDatabaseModel::getInstance('Order', 'JticketingModel');
			$orderModel->updateOrderDetails($orderId, $orderInfo);

			// Get order items for this order.
			if (file_exists(JPATH_SITE . '/components/com_jticketing/models/orderitem.php')) { require_once JPATH_SITE . '/components/com_jticketing/models/orderitem.php'; }
			$ordrItemModel = BaseDatabaseModel::getInstance('Orderitem', 'JticketingModel');
			$orderItems = $ordrItemModel->getOrderItems($orderId);

			// Update attendeeform.
			if (count($orderItems))
			{
				foreach ($orderItems as $oi)
				{
					if (file_exists(JPATH_SITE . '/components/com_jticketing/models/attendeeform.php')) { require_once JPATH_SITE . '/components/com_jticketing/models/attendeeform.php'; }
					$attendeeFormModel = BaseDatabaseModel::getInstance('AttendeeForm', 'JticketingModel');
					$attendeeFormModel->updateAttendeeOwner($oi->attendee_id, $owner_id = Factory::getUser()->id);
				}
			}
		}

		$this->user     = Factory::getUser();
		$this->country  = $this->TjGeoHelper->getCountryList();
		$this->userdata = $this->getUserData();

		$this->params   = ComponentHelper::getParams('com_jticketing');
		$this->enable_bill_vat = $this->params->get('enable_bill_vat');
		$this->default_country = $this->params->get('default_country');
		$profileImport         = $this->params->get('profile_import');
		$this->tnc             = $this->params->get('tnc');
		$this->article         = $this->params->get('article');

		$this->default_country_mobile_code = $this->params->get('default_country_mobile_code');
		$JTicketingIntegrationsHelper = new JTicketingIntegrationsHelper;
		$cdata                = '';

		if ($profileImport)
		{
			$cdata = JT::integration()->profileImport();
		}

		// Added by manoj
		$this->userbill = array();

		if (isset($this->userdata['BT']))
		{
			$this->userbill = $this->userdata['BT'];
		}
		elseif (is_array($cdata))
		{
			$this->userbill = $cdata['userbill'];
		}

		// Let's figure out and collect all data needed to return view layout HTML.
		$billPath = JT::utilities()->getViewPath('order', 'default_billing');

		// Get HTML.
		ob_start();
		include $billPath;
		$html = ob_get_contents();
		ob_end_clean();

		$data                 = array();
		$data['billing_html'] = $html;

		echo json_encode($data);
		jexit();
	}

	/**
	 * Get billing data
	 *
	 * @param   integer  $orderId  country id
	 *
	 * @return  array state list
	 *
	 * @since   1.0
	 */
	public function getUserData($orderId = 0)
	{
		$params   = ComponentHelper::getParams('com_jticketing');
		$user     = Factory::getUser();
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		$userdata = array();

		if ($orderId and !($user->id))
		{
			$query->select('u.*');
			$query->from($db->quoteName('#__jticketing_users', 'u'));
			$query->where($db->quoteName('u.order_id') . ' = ' . $db->quote($orderId));
			$query->order($db->quoteName('u.id') . ' DESC');
		}
		else
		{
			$query->select('u.*');
			$query->from($db->quoteName('#__jticketing_users', 'u'));
			$query->where($db->quoteName('u.user_id') . ' = ' . $db->quote($user->id));
			$query->order($db->quoteName('u.id') . ' DESC');
		}

		$db->setQuery($query, 0, 1);
		$result = $db->loadObjectList();

		if (!empty($result))
		{
			if ($result[0]->address_type == 'BT')
			{
				$userdata['BT'] = $result[0];
			}
			elseif (!empty($result[1]->address_type) == 'BT')
			{
				$userdata['BT'] = $result[1];
			}
		}
		else
		{
			$row             = new stdClass;
			$row->user_email = $user->email;
			$userdata['ST']  = $row;
		}

		return $userdata;
	}

	/**
	 * Update billing address while creating order
	 *
	 * @param   ARRAY  $billingArr  billing data
	 *
	 * @return  array    $billingData
	 *
	 * @since   2.5.0
	 */
	public function generateBillingData($billingArr = array())
	{
		$user = Factory::getUser();
		$name = ($user && $user->name) ? explode(" ", $user->name) : [];
		$billingData = array();

		if (!empty($billingArr['email1']))
		{
			$billingData['user_email'] = $this->filter->clean($billingArr['email1'], 'string');
		}
		else
		{
			$billingData['user_email'] = $user->email;
		}

		$billingData['address_type'] = 'BT';

		if (!empty($billingArr['fname']))
		{
			$billingData['firstname'] = $this->filter->clean($billingArr['fname'], 'string');
		}
		else
		{
			$billingData['firstname'] = $name[0];
		}

		// Avoid php warning.
		if (!isset($name[1]))
		{
			$name[1] = '';
		}

		$billingData['lastname'] = empty($billingArr['lname']) ? $name[1] : $this->filter->clean($billingArr['lname'], 'string');

		if (!empty($billingArr['country']))
		{
			$billingData['country_code'] = $this->filter->clean($billingArr['country'], 'string');
		}

		if (!empty($billingArr['registration_type']))
		{
			$billingData['registration_type'] = $this->filter->clean($billingArr['registration_type']);
		}

		if (!empty($billingArr['business_name']))
		{
			$billingData['business_name'] = $this->filter->clean($billingArr['business_name'], 'string');
		}

		if (!empty($billingArr['vat_num']))
		{
			$billingData['vat_number'] = $this->filter->clean($billingArr['vat_num'], 'string');
		}

		if (!empty($billingArr['addr']))
		{
			$billingData['address'] = $this->filter->clean($billingArr['addr'], 'string');
		}

		if (!empty($billingArr['city']))
		{
			$billingData['city'] = $this->filter->clean($billingArr['city'], 'string');
		}

		if (!empty($billingArr['state']))
		{
			$billingData['state_code'] = $this->filter->clean($billingArr['state'], 'string');
		}

		if (!empty($billingArr['zip']))
		{
			$billingData['zipcode'] = $this->filter->clean($billingArr['zip'], 'cmd');
		}

		if (!empty($billingArr['country_mobile_code']))
		{
			$billingData['country_mobile_code'] = $this->filter->clean($billingArr['country_mobile_code'], 'string');
		}

		if (!empty($billingArr['phone']))
		{
			$billingData['phone'] = $this->filter->clean($billingArr['phone'], 'string');
		}

		$billingData['approved'] = '1';

		return $billingData;
	}

	/**
	 * Insert registered user data into jticketing users.
	 *
	 * @param   int  $userID   userID
	 * @param   int  $orderID  orderID
	 *
	 * @return  false
	 *
	 * @since   1.0
	 */
	public function addRegisterdUserIntoJtuser($userID, $orderID)
	{
		if (!empty($userID))
		{
			$db = Factory::getDbo();
			$query = $db->getQuery(true);
			$query->select('*');
			$query->from($db->quoteName('#__users'));
			$query->where($db->quoteName('id') . ' = ' . $userID);
			$db->setQuery($query);
			$regUserData = $db->loadObject();

			$userInfo  = JT::order($orderID)->getbillingdata();

			$obj = new stdClass;

			if (!empty($userInfo->id))
			{
				$obj->id = $userInfo->id;
			}

			$obj->user_id    = $userID;
			$obj->order_id   = $orderID;
			$obj->firstname  = $regUserData->name;
			$obj->user_email = $regUserData->email;
			$obj->address_type = 'BT';

			if (!empty($userInfo->id))
			{
				// Insert registered user data into jticketing users.
				if (!$db->updateObject('#__jticketing_users', $obj, 'id'))
				{
					echo $db->stderr();

					return false;
				}
			}
			else
			{
				// Insert registered user data into jticketing users.
				if (!$db->insertObject('#__jticketing_users', $obj, 'id'))
				{
					echo $db->stderr();

					return false;
				}
			}
		}
	}

	/**
	 * Returns array of user object for frontend listing.
	 *
	 * @param   array  $options  Filters
	 *
	 * @since  2.5.0
	 *
	 * @return object
	 */
	public function getUser($options = array())
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('DISTINCT *');
		$query->from($db->quoteName('#__jticketing_users'));

		if (isset($options['order_id']))
		{
			$query->where($db->quoteName('order_id') . ' = ' . $db->quote($options['order_id']));
		}

		if (isset($options['user_id']))
		{
			$query->where($db->quoteName('user_id') . ' = ' . $db->quote($options['user_id']));
		}

		$db->setQuery($query);

		return $db->loadObjectList();
	}

	/**
	 * Method to validate the email of the user.
	 *
	 * @param   String  $email  email of the user.
	 *
	 * @return  Boolean true on correct email and false on wrong formatted email
	 *
	 * @since   2.5.0
	 */
	public function validateEmail($email)
	{
		// Validate user email is correct or wrong.
		if (empty($email))
		{
			$this->setError(Text::_("COM_JTICKETING_BILLING_DATA_INVALID_EMAIL"));

			return false;
		}

		return true;
	}

	/**
	 * Function to get the user filter options
	 *
	 * @param   Boolean  $myteam  Fetch only my team users
	 *
	 * @return  array
	 *
	 * @since 3.2.0
	 */
	public function getUserFilterOptions($myteam = false)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('u.id,u.username');
		$query->from('#__users as u');

		if ($myteam)
		{
			if (file_exists(JPATH_ADMINISTRATOR . '/components/com_jticketing/helpers/jticketing.php')) { require_once JPATH_ADMINISTRATOR . '/components/com_jticketing/helpers/jticketing.php'; }
			$hasUsers = JticketingHelper::getSubusers();

			if (!empty($hasUsers))
			{
				$query->where('u.id IN(' . implode(',', $hasUsers) . ')');
			}
		}

		$db->setQuery($query);
		$users = $db->loadObjectList();

		$userFilter   = array();
		$userFilter[] = HTMLHelper::_('select.option', '', Text::_('COM_JTICKETING_FILTER_SELECT_USER'));

		if (!empty($users))
		{
			foreach ($users as $user)
			{
				$userFilter[] = HTMLHelper::_('select.option', $user->id, $user->username);
			}
		}

		return $userFilter;
	}

	/**
	 * Function to get the user filter options
	 *
	 * @param   Boolean  $myteam  Fetch only my team users
	 *
	 * @return  array
	 *
	 * @since 3.2.0
	 */
	public function getNameFilterOptions($myteam = false)
	{
		$db = Factory::getDbo();

		$query = $db->getQuery(true);
		$query->select('u.id,u.name');
		$query->from('#__users as u');

		if ($myteam)
		{
			if (file_exists(JPATH_ADMINISTRATOR . '/components/com_jticketing/helpers/jticketing.php')) { require_once JPATH_ADMINISTRATOR . '/components/com_jticketing/helpers/jticketing.php'; }
			$hasUsers = JticketingHelper::getSubusers();

			if (!empty($hasUsers))
			{
				$query->where('u.id IN(' . implode(',', $hasUsers) . ')');
			}
		}

		$db->setQuery($query);
		$users = $db->loadObjectList();

		$nameFilter   = array();
		$nameFilter[] = HTMLHelper::_('select.option', '', Text::_('COM_JTICKETING_FILTER_SELECT_USER'));

		if (!empty($users))
		{
			foreach ($users as $user)
			{
				$nameFilter[] = HTMLHelper::_('select.option', $user->id, $user->name);
			}
		}

		return $nameFilter;
	}
}
