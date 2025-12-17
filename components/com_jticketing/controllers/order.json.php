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
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Response\JsonResponse;
use Joomla\CMS\Component\ComponentHelper;

/**
 * JTicketing
 *
 * @since  1.6
 */
class JticketingControllerOrder extends jticketingController
{
	public $JticketingCommonHelper, $jticketingMainHelper, $com_params, $user, $igReq, $session, $error;
	/**
	 * Router instance
	 *
	 * @var  object
	 */
	public $jtRouteHelper;

	/**
	 * Factory get Application
	 *
	 * @var  object
	 */
	public $app;

	/**
	 * Factory get Application input
	 *
	 * @var  object
	 */
	public $input;

	/**
	 * Constructor
	 *
	 * @since   1.0
	 */
	public function __construct()
	{
		parent::__construct();
		$this->JticketingCommonHelper = new JticketingCommonHelper;
		$this->jtRouteHelper = new JTRouteHelper;
		$this->jticketingMainHelper = new jticketingmainhelper;
		$this->com_params = ComponentHelper::getParams('com_jticketing');
		$this->user       = Factory::getUser();
		$this->app        = Factory::getApplication();
		$this->input      = $this->app->input;
		$this->igReq        = array('ignore_request' => true);

		// @TODO remove when loginValidate method will be removed
		$this->session    = Factory::getSession();
	}

	/**
	 * Function loadState
	 *
	 * @return null|object
	 */
	public function loadState()
	{
		if (!Session::checkToken())
		{
			echo new JsonResponse(null, Text::_('JINVALID_TOKEN'), true);
			$this->app->close();
		}

		$jinput         = Factory::getApplication()->getInput();
		$country        = $jinput->get('country', '', 'STRING');
		$model          = JT::model("order", array('ignore_request' => true));
		$regionList     = $model->getRegionList($country);

		echo new JsonResponse($regionList);
		$this->app->close();
	}

	/**
	 * Get getcoupon
	 *
	 * @return  array
	 *
	 * @since  1.0.0
	 */
	public function applyCoupon()
	{
		if (!Session::checkToken())
		{
			echo new JsonResponse(null, Text::_('JINVALID_TOKEN'), true);
			$this->app->close();
		}

		$couponCode = $this->input->get('couponCode');
		$orderId = $this->input->getInt('orderId');
		$order = JT::order($orderId);

		if ($order->applyCoupon($couponCode))
		{
			$this->display();
			$this->app->close();
		}

		echo new JsonResponse(null, $order->getError(), true);
		$this->app->close();
	}

	/**
	 * Get getcoupon
	 *
	 * @return  array
	 *
	 * @since  2.5.0
	 */
	public function removeCoupon()
	{
		if (!Session::checkToken())
		{
			echo new JsonResponse(null, Text::_('JINVALID_TOKEN'), true);
			$this->app->close();
		}

		$couponCode = $this->input->get('couponCode');
		$orderId    = $this->input->getInt('orderId');
		$order      = JT::order($orderId);

		if ($order->removeCoupon($couponCode))
		{
			$this->display();
			$this->app->close();
		}

		echo new JsonResponse(null, $order->getError(), true);
		$this->app->close();
	}

	/**
	 * Function loginValidate
	 *
	 * @return  array
	 *
	 * @since  1.0.0
	 */
	public function loginValidate()
	{
		$orderId     = $this->input->getInt('orderId');
		$order       = JT::order($orderId);

		if (empty($order->id))
		{
			echo new JsonResponse(null, Text::_('COM_JTICKETING_SOMETHING_WENT_WRONG'), true);
			$this->app->close();
		}

		$userRedirectionLink = $this->jtRouteHelper->JTRoute('index.php?option=com_jticketing&view=order&layout=billing&orderId=' . $orderId, false);

		if ($this->user->id)
		{
			echo new JsonResponse($userRedirectionLink);
			$this->app->close();
		}

		// Now login the user
		if (!$this->app->login(array('username' => $this->input->getString('email'), 'password' => $this->input->getString('password'))))
		{
			echo new JsonResponse(null, Text::_('JTICKETING_CHECKOUT_ERROR_LOGIN'), true);
			$this->app->close();
		}

		$user = Factory::getUser();
		$order->user_id = $user->id;
		$order->email = $user->email;
		$order->name  = $user->name;

		if (!$order->save())
		{
			echo new JsonResponse(null, $order->getError(), true);
			$this->app->close();
		}

		$orderItems = $order->getItems();

		foreach ($orderItems as $orderItem)
		{
			$attendee = JT::attendee($orderItem->attendee_id);
			$attendee->owner_id = $user->id;
			$attendee->owner_email = empty($attendee->owner_email) ? $user->email : $attendee->owner_email;

			if (!$attendee->save())
			{
				echo new JsonResponse(null, $attendee->getError(), true);
				$this->app->close();
			}
		}

		if (($order->getAmount(false) <= 0) && !empty($order->user_id))
		{
			$userRedirectionLink = $this->freeTicketCheckout($order);

			if (empty($userRedirectionLink))
			{
				echo new JsonResponse(null, $this->getError(), true);
				$this->app->close();
			}
		}

		echo new JsonResponse($userRedirectionLink);
		$this->app->close();
	}

	/**
	 * Call from Ajax
	 *
	 * @return  void
	 *
	 * @since   1.7
	 */
	public function getUpdatedBillingInfo()
	{
		$model = $this->getModel('user');
		$res = $model->getUpdatedBillingInfo();
	}

	/**
	 * Function checkUserEmailId
	 *
	 * @return  boolean
	 *
	 * @since  1.0.0
	 */
	public function checkUserEmailId()
	{
		if (!Session::checkToken())
		{
			echo new JsonResponse(null, Text::_('JINVALID_TOKEN'), true);
			$this->app->close();
		}

		$email      = $this->input->get('email', '', 'STRING');

		/* @var $userModel JTicketingModelUser */
		$userModel 	= JT::model('user');

		// Validate user email.
		if (!$userModel->validateEmail($email))
		{
			echo new JsonResponse(null, $userModel->getError(), true);
			$this->app->close();
		}

		$model      = $this->getModel('order');
		$status     = $model->checkuserExistJoomla($email);

		if ($status)
		{
			echo new JsonResponse($status, Text::_('COM_JTICKETING_MAIL_EXISTS'), true);
			$this->app->close();
		}

		echo new JsonResponse($status);
		$this->app->close();
	}

	// @TODO:Add this in booking ticket email

	/**
	 * Get verifyBookingID
	 *
	 * @return  array
	 *
	 * @since  1.0.0
	 */
	public function verifyBookingID()
	{
		$book_id              = $this->input->get('book_id', '', 'STRING');
		$jticketingmainhelper = new jticketingmainhelper;
		$order                = $jticketingmainhelper->verifyBookingID($book_id);

		echo json_encode($order);
		jexit();
	}

	/**
	 * Get total Ammount
	 *
	 * @return  array
	 *
	 * @since  2.5.0
	 */
	public function getTotalAmount()
	{
		$result     = array();
		$amount     = $this->input->get('amt', '', 'STRING');
		$totalPrice = $this->input->get('totalPrice', '', 'STRING');
		$utilities  = JT::utilities();

		// Get all amount calulation rounded and formatted
		$roundedAmount   = $utilities->getRoundedPrice($amount);
		$formattedAmount = $utilities->getFormattedPrice($amount);

		// Get total price of current ticket type
		$roundedTotalPrice   = $utilities->getRoundedPrice($totalPrice);
		$formattedTotalPrice = $utilities->getFormattedPrice($totalPrice);

		$result['roundedTotalPrice']   = $roundedTotalPrice;
		$result['formattedTotalPrice'] = $formattedTotalPrice;
		$result['rounded_amount']      = $roundedAmount;
		$result['formatted_amount']    = $formattedAmount;

		echo new JsonResponse($result);
	}

	/**
	 * Method to add data in order Item table.
	 *
	 * @return  string
	 *
	 * @since  2.5.0
	 */
	public function addItem()
	{
		if (!Session::checkToken())
		{
			$this->app->enqueueMessage(Text::_('JINVALID_TOKEN'), 'error');
			echo new JsonResponse(null, null, true);
			$this->app->close();
		}

		$orderId = $this->input->getInt('orderId');
		$typeID  = $this->input->getInt('typeId');
		$ticket  = JT::tickettype($typeID);
		$order   = JT::order($orderId);

		$couponCode = $this->input->get('couponCode');

		if ($couponCode)
		{
			$order->removeCoupon($couponCode);
		}
		if ($order->addItem($ticket))
		{
			$this->display();
			$this->app->close();
		}

		echo new JsonResponse(null, $order->getError(), true);
		$this->app->close();
	}

	/**
	 * Method to remove order item.
	 *
	 * @return  string
	 *
	 * @since  2.5.0
	 */
	public function removeItem()
	{
		if (!Session::checkToken())
		{
			echo new JsonResponse(null, Text::_('JINVALID_TOKEN'), true);
			$this->app->close();
		}

		$typeId  = $this->input->get('typeId', '', 'INT');
		$orderId = $this->input->get('orderId', '', 'INT');
		$ticket  = JT::tickettype($typeId);
		$order   = JT::order($orderId);

		$couponCode = $this->input->get('couponCode');

		if ($couponCode)
		{
			$order->removeCoupon($couponCode);
		}

		if ($order->removeItem($ticket))
		{
			$this->display();
			$this->app->close();
		}

		echo new JsonResponse(null, $order->getError(), true);
		$this->app->close();
	}

	/**
	 * Method to get attendee layout
	 *
	 * @return string
	 *
	 * @since  2.5.0
	 */
	public function proceedToCheckout()
	{
		if (!Session::checkToken())
		{
			echo new JsonResponse(null, Text::_('JINVALID_TOKEN'), true);
			$this->app->close();
		}

		$orderId        = $this->input->getInt('orderId');
		$jtParams       = JT::config();
		$order          = JT::order($orderId);

		// Check order have order items against it and user have authorized
		if (empty($order->getItems()))
		{
			echo new JsonResponse(null, Text::_('COM_JTICKETING_BOOK_TICKET_FIRST_FOR_PROCEED'), true);
			$this->app->close();
		}

		if (!$order->reValidateAmount())
		{
			echo new JsonResponse(null, Text::_('COM_JTICKETING_INVALID_ORDER_AMOUNT_VALIDATION'), true);
			$this->app->close();
		}

		// Redirect user to billing layout.
		$userRedirectionLink = $this->jtRouteHelper->JTRoute('index.php?option=com_jticketing&view=order&layout=billing&orderId=' . $orderId, false);

		// If the tickets are free for logged in user then add the billing data and forward user to invoice page.

		if ($order->getAmount(false) <= 0 && $jtParams->get('collect_attendee_info_checkout') != 1 && !empty($order->user_id))
		{
			if ($jtParams->get('billing_for_free_event') == 1)
			{
				$userRedirectionLink = $this->jtRouteHelper->JTRoute('index.php?option=com_jticketing&view=order&layout=billing&orderId=' . $orderId, false);
			}
			else
			{
				$userRedirectionLink = $this->freeTicketCheckout($order);
			}

			if (empty($userRedirectionLink))
			{
				echo new JsonResponse(null, $this->getError(), true);
				$this->app->close();
			}
		}
		elseif ($jtParams->get('collect_attendee_info_checkout') == 1)
		{
			// Check if attendee data config is set and redirect accordingly.
			$userRedirectionLink = $this->jtRouteHelper->JTRoute('index.php?option=com_jticketing&view=order&layout=attendee&orderId=' . $orderId, false);
		}

		echo new JsonResponse($userRedirectionLink);
		$this->app->close();
	}

	/**
	 * Method to save attendee data.
	 *
	 * @return  array
	 *
	 * @since  2.5.0
	 */
	public function addAttendee()
	{
		if (!Session::checkToken())
		{
			echo new JsonResponse(null, Text::_('JINVALID_TOKEN'), true);
			$this->app->close();
		}

		$post                       = $this->input->post;
		$data                       = $post->get('attendee_field', '', 'ARRAY');
		$attendeeFields             = '';
		parse_str($data[0], $attendeeFields);

		$orderId                    = $this->input->getInt('orderId');
		$order                      = JT::order($orderId);

		if (!$order->reValidateAmount())
		{
			echo new JsonResponse(null, Text::_('COM_JTICKETING_INVALID_ORDER_AMOUNT_VALIDATION'), true);
			$this->app->close();
		}

		$attendeeFieldValuesModel   = JT::model('attendeefieldvalues');
		$config                     = JT::config();

		if ($config->get('collect_attendee_info_checkout') == 1 && !empty($order->getItems()))
		{
			/* @var $model JTicketingModelAttendeefields */
			$model = JT::model("attendeefields");
			$fields = $model->getFields(['core' => 1]);
			$fieldId = 0;

			// Get the email field id
			foreach ($fields as $field)
			{
				if ($field->name == 'email')
				{
					$fieldId = $field->id;
					break;
				}
			}

			// Arrange data as per new attendeeform model
			foreach ($attendeeFields['attendee_field'] as $attendeeField)
			{
				// if no email option is selected then send email ticket to attendee
				$sendEticketTo = empty($attendeeFields['sendTicket']) ? 'ticketToAttendee' : $attendeeFields['sendTicket'];
				$session              = Factory::getSession();
				$session->set('sendTicket',$sendEticketTo);
				$validate = $this->validateAttendeeData($order->event_details_id, $attendeeField);

				// IF validate passess then save attendee data.
				if ($validate)
				{
					$attendee = JT::attendee($attendeeField['attendee_id']);
					$attendee->ticket_type_id = $attendeeField['ticket_type'];
					$attendee->event_id = $order->event_details_id;
					$attendee->owner_id = $this->user->id;
					$attendee->owner_email = empty($attendeeField[$fieldId]) ? $this->user->email : $attendeeField[$fieldId];
					$attendeeParams = json_decode($attendee->getParams());
					$attendeeParams->sendTicket = $sendEticketTo;
					$attendee->setParams(new Registry($attendeeParams));

					if ($attendee->save())
					{
						$orderItem = JT::orderitem($attendeeField['order_items_id']);
						$orderItem->attendee_id = $attendee->id;
						$attendeeField['attendee_id'] = $attendee->id;

						if (!$orderItem->save())
						{
							echo new JsonResponse(null, $orderItem->getError(), true);
							$this->app->close();
						}

						if (!$attendeeFieldValuesModel->save($attendeeField))
						{
							echo new JsonResponse(null, Text::_('COM_JTICKETING_SOMETHING_WENT_WRONG'), true);
							$this->app->close();
						}
						// Load the recurring event model
						JLoader::register('JticketingModelRecurringEvents', JPATH_ADMINISTRATOR . '/components/com_jticketing/models/recurringevents.php');
						$recurringModel = new JticketingModelRecurringEvents();

						// Call the model method
						$recurringModel->saveRecurringEventAttendees($attendee->id, $order->event_details_id);

					}
					else
					{
						echo new JsonResponse(null, Text::_('COM_JTICKETING_SOMETHING_WENT_WRONG'), true);
						$this->app->close();
					}
				}
			}

			$billingLink    = $this->jtRouteHelper->JTRoute('index.php?option=com_jticketing&view=order&layout=billing&orderId=' . $orderId, false);

			// If order amount is 0 then complete the order.
			if ($order->getAmount(false) <= 0 && Factory::getUser()->id != 0)
			{
				if ($config->get('billing_for_free_event') == 1)
				{
					$billingLink    = $this->jtRouteHelper->JTRoute('index.php?option=com_jticketing&view=order&layout=billing&orderId=' . $orderId, false);
				}
				else
				{
					$billingLink = $this->freeTicketCheckout($order);
				}

				if (empty($billingLink))
				{
					echo new JsonResponse(null, $this->getError(), true);
					$this->app->close();
				}
			}

			echo new JsonResponse($billingLink);
			$this->app->close();
		}

		$menu       = $this->app->getMenu();
		$event      = JT::event()->loadByIntegration($order->event_details_id);
		$eventsUrl  = 'index.php?option=com_jticketing&view=events&layout=default&catid=' . $event->getCategory();
		$menuItem   = $menu->getItems('link', $eventsUrl, true);

		// Pass the url by using base64 encode so that after login user will redirected to event page.
		$eventUrl               = 'index.php?option=com_jticketing&view=event&id=' . $event->id . '&Itemid=' . $menuItem->id;

		echo new JsonResponse($eventUrl);
		$this->app->close();
	}

	/**
	 * Method to validate attendee data.
	 *
	 * @param   INT    $eventDetailsId  Event details id
	 * @param   Array  $attendeeField   Array of attendee data
	 *
	 * @return  boolean | array
	 *
	 * @since  2.5.0
	 */
	public function validateAttendeeData($eventDetailsId, $attendeeField)
	{
		$attendeefields     = JT::model('attendeefields');
		$allFields          = $attendeefields->getAttendeeFields($eventDetailsId, $attendeeField['attendee_id']);
		$session            = Factory::getSession();
		$sendTicket         = $session->get('sendTicket');

		foreach ($allFields as $field)
		{
			// Check if value is present and if array is there then is it having no values, also check for specific client. TicketToBuyer will bring empty attendee email
			if (((empty($attendeeField[$field->id]) || ($attendeeField[$field->id][0] == '') || empty(($attendeeField[$field->id])))
				&& ($field->source == "com_jticketing") && $sendTicket != 'ticketToBuyer'
				|| (empty($attendeeField[$field->name]) || ($attendeeField[$field->name][0] == '') || empty(($attendeeField[$field->name])))
				&& ($field->source == "com_tjfields.com_jticketing.ticket"))
				&& ($field->required === '1') && $sendTicket != 'ticketToBuyer')
			{
				$msg = Text::_('COM_JTICKETING_FILL_ALL_REQUIRED_FIELDS');
				echo new JsonResponse(null, $msg, true);
				jexit();
			}

			$att1 = (!empty($attendeeField[$field->id])) ? $attendeeField[$field->id] : '';
			$att2 = (!empty($attendeeField[$field->name])) ? $attendeeField[$field->name] : '';

			// Check if selected value from multiple options is same as fields options.
			if (((is_array($att1)) || ($field->type == 'radio') || (is_array($att2))) && ($field->type != 'sql'))
			{
				$key            = 'id';
				$optionValues   = '';

				if (!empty($field->client) && $field->client === 'com_jticketing.ticket')
				{
					$key = 'name';
				}

				// Check if field options are in strng format or in array format.
				if (!is_array($field->default_selected_option))
				{
					$optionValues = explode('|', $field->default_selected_option);
				}
				elseif (is_array($field->default_selected_option))
				{
					$optionValues = array();

					foreach ($field->default_selected_option as $option)
					{
						$optionValues[] = $option->value;
					}
				}

				// Check for radio field that value is in field option array.
				if ($field->type == 'radio' && is_array($optionValues))
				{
					if (isset($attendeeField[$field->$key]) && !in_array($attendeeField[$field->$key], $optionValues))
					{
						$msg = Text::sprintf('COM_JTICKETING_INVALID_ATTENDEE_FIELD_VALUE', ucfirst($field->name));
						echo new JsonResponse(null, $msg, true);
						jexit();
					}
				}
				else
				{
					if (is_array($optionValues))
					{
						// Get the ans count and matched ans count with the options and compare both.
						$ansCount       = count($attendeeField[$field->$key]);
						$optionCount    = count(array_intersect($attendeeField[$field->$key], $optionValues));

						if ($ansCount !== $optionCount)
						{
							$msg = Text::sprintf('COM_JTICKETING_INVALID_ATTENDEE_FIELD_VALUE', ucfirst($field->label));
							echo new JsonResponse(null, $msg, true);
							jexit();
						}
					}
				}
			}
		}

		return true;
	}

	/**
	 * Method to add data in JTicketing users table.
	 *
	 * @return  string
	 *
	 * @since  2.5.0
	 */
	public function addBillingData()
	{
		if (!Session::checkToken())
		{
			echo new JsonResponse(null, Text::_('JINVALID_TOKEN'), true);
			$this->app->close();
		}

		$post                       = $this->input->post;
		$data                       = $post->get('billingValues', '', 'ARRAY');
		$unSerializedData           = array();
		parse_str($data[0], $unSerializedData);

		if (isset($unSerializedData['registration_type']))
		{
		$unSerializedData['bill']['registration_type'] = $unSerializedData['registration_type'];
		}

		if (JT::config()->get('enable_buy_as_business') == 0 || $unSerializedData['bill']['registration_type'] == 0)
		{
			$unSerializedData['bill']['business_name'] = "";
			$unSerializedData['bill']['vat_num'] = "";
		}

		/* @var $userModel JTicketingModelUser */
		$userModel 	= JT::model('user');

		// Validate user email.
		if (!$userModel->validateEmail($unSerializedData['bill']['email1']))
		{
			echo new JsonResponse(null, $userModel->getError(), true);
			$this->app->close();
		}

		$orderId                    = $post->getInt('orderId');
		$jtUserModel                = JT::model('user');
		$billingData                = $jtUserModel->generateBillingData($unSerializedData['bill']);

		if (isset($unSerializedData['jt_comment']))
		{
			$billingData['comment'] = $unSerializedData['jt_comment'];
		}

		$userConsent     			= !empty($unSerializedData['accept_privacy_term']) ? $unSerializedData['accept_privacy_term'] : 'off';
		$billingData['order_id']    = $orderId;
		$config                     = JT::config();

		// Handle guest checkout and on-the-fly registration.
		$order = JT::order($orderId);

		if (!$order->reValidateAmount())
		{
			echo new JsonResponse(null, Text::_('COM_JTICKETING_INVALID_ORDER_AMOUNT_VALIDATION'), true);
			$this->app->close();
		}

		// Handle guest checkout and on-the-fly registration.
		if ($config->get('allow_buy_guestreg') && $post->get('checkout_method', '', 'string') == 'register')
		{
			if ($jtUserModel->registerUser($billingData))
			{
				$this->user = Factory::getUser();
			}
			else
			{
				echo new JsonResponse(null, Text::_('COM_JTICKETING_USER_CREATION_FAILD'));
				$this->app->close();
			}

			// @TODO Alternative solution to update user details in order and order details
			$order->email = $this->user->email;
			$order->name  = $this->user->name;
			$order->user_id  = $this->user->id;

			if (!$order->save())
			{
				echo new JsonResponse(null, $order->getError(), true);
				$this->app->close();
			}

			$orderItems = $order->getItems();

			foreach ($orderItems as $orderItem)
			{
				$attendee = JT::attendee($orderItem->attendee_id);
				$attendee->owner_email = empty($attendee->owner_email) ? $this->user->email : $attendee->owner_email;
				$attendee->owner_id = $this->user->id;

				if (!$attendee->save())
				{
					echo new JsonResponse(null, $attendee->getError(), true);
					$this->app->close();
				}
			}
		}
		elseif ($config->get('allow_buy_guest') && empty($this->user->id) && $post->get('checkout_method', '', 'string') == '')
		{
			$order->email = $billingData['user_email'];
			$order->name  = $billingData['firstname'];

			if (!$order->save())
			{
				echo new JsonResponse(null, $order->getError(), true);
				$this->app->close();
			}

			$orderItems = $order->getItems();

			foreach ($orderItems as $orderItem)
			{
				$attendee = JT::attendee($orderItem->attendee_id);
				$attendee->owner_email = empty($attendee->owner_email) ? $billingData['user_email'] : $attendee->owner_email;

				if (!$attendee->save())
				{
					echo new JsonResponse(null, $attendee->getError(), true);
					$this->app->close();
				}
			}
		}
		else
		{
			$billingData['user_id']  = $this->user->id;
		}

		// Add privacy data.
		if ($userConsent === 'on')
		{
			/* @var $orderModel JTicketingModelOrder */
			$orderModel = JT::model('order');

			if (!$orderModel->saveConsent($order))
			{
				echo new JsonResponse(null, $orderModel->getError(), true);
				$this->app->close();
			}
		}

		if (!$order->addBillingData($billingData))
		{
			echo new JsonResponse(null, $order->getError(), true);
			$this->app->close();
		}

		// @TODO - call URL from router
		$billingLink    = $this->jtRouteHelper->JTRoute('index.php?option=com_jticketing&view=order&layout=payment&orderId=' . $orderId, false);

		if ($order->getAmount(false) <= 0)
		{
			$billingLink = $this->freeTicketCheckout($order);

			if (empty($billingLink))
			{
				echo new JsonResponse(null, $this->getError(), true);
				$this->app->close();
			}
		}

		echo new JsonResponse($billingLink);
		$this->app->close();
	}

	/**
	 * Method to update data in order Item table.
	 *
	 * @return  string
	 *
	 * @since  2.5.0
	 */
	public function UpdateItem()
	{
		if (!Session::checkToken())
		{
			echo new JsonResponse(null, Text::_('JINVALID_TOKEN'), true);
			$this->app->close();
		}

		$orderId        = $this->input->getInt('orderId');
		$typeID         = $this->input->getInt('typeId');
		$previousTypeId = $this->input->getInt('previousTypeId');
		$oldTicket      = JT::tickettype($previousTypeId);
		$newTicket      = JT::tickettype($typeID);
		$order          = JT::order($orderId);

		if ($order->removeItem($oldTicket) && $order->addItem($newTicket))
		{
			$this->display();
			$this->app->close();
		}

		echo new JsonResponse(null, $order->getError(), true);
		$this->app->close();
	}

	/**
	 * Method to genrate free ticket order
	 *
	 * @param   JTicketingOrder  $order  Order Object
	 *
	 * @return string|boolean  in case of success return URL string
	 */
	private function freeTicketCheckout(JTicketingOrder $order)
	{
		$jtUserModel = JT::model('user', $this->igReq);
		$config		 = JT::config();
		$invoiceLink = '';

		if (empty($order->getbillingdata()))
		{
			// Genrate billing data array.
			$billingData                = $jtUserModel->generateBillingData(array());
			$billingData['order_id']    = $order->id;
			$billingData['user_id']     = $order->user_id;

			// Add the billing data.
			if (!$order->addBillingData($billingData))
			{
				$this->setError($order->getError());

				return false;
			}
		}

		/* This is to specify that the order is placed by event buyer and its order status mail is not
		to be sent*/
		Factory::getSession()->set('orderByEventBuyer', 1);

		// Update order status and relative opretions after it.
		if ($order->complete())
		{
			// Create invoice page link.
			$redUrl                  = "index.php?option=com_jticketing&view=orders&layout=order&sendmail=1&orderid=";
			$redUrl                 .= $order->order_id . "&processor=" . $order->processor;

			if ($config->get('allow_buy_guest'))
			{
				$userInfo 	= $order->getbillingdata();
				$redUrl 	.= "&email=" . md5(isset($userInfo->user_email) ? $userInfo->user_email : '');
			}

			$invoiceLink     = $this->jtRouteHelper->JTRoute($redUrl);
		}
		else
		{
			$this->setError($order->getError());
		}

		return $invoiceLink;
	}

	/**
	 * Method to get Ecommerce Google analytics data.
	 *
	 * @return  array
	 *
	 * @since  2.6.0
	 */
	public function getEcTrackingData()
	{
		$response = array();
		$config  = JT::config();
		$orderId = $this->input->get('orderId', '', 'INT');
		$stepId  = $this->input->get('stepId', '', 'INT');
		$order   = JT::order($orderId);
		$event   = JT::event()->loadByIntegration($order->event_details_id);
		$data    = new stdClass;
		$data->id = $event->id;
		$data->title = $event->getTitle();

		// Joomla 6: JVERSION check removed
		if (false) // Legacy < '4.0.0')
		{
			Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_categories/tables');
			$categoryTable = Table::getInstance('Category', 'CategoriesTable');
		}
		else
		{
			$categoryTable = Table::getInstance('CategoryTable', '\\Joomla\\Component\\Categories\\Administrator\\Table\\');
		}

		$categoryTable->load($event->getCategory());
		$data->category = $categoryTable->title;
		$ticketArray = array();
		$ticketAmount = 0;

		if (!empty($order->getItems()))
		{
			foreach ($order->getItems() as $orderItem)
			{
				$ticketId = $orderItem->type_id;
				$ticketType = JT::tickettype($ticketId);
				$ticketArray[] = $ticketType->title;
				$ticketAmount = $ticketAmount + $ticketType->price;
			}

			$data->subscription = implode(', ', $ticketArray);
			$data->price = $ticketAmount;
		}
		else
		{
			$data->variant = '';
			$data->price = '';
		}

		$data->quantity = $order->getTicketsCount();

		if ($config->get('ga_product_type_dimension') != '')
		{
			$data->productTypeDimensionValue = $config->get('ga_product_type_dimension');
		}

		$data->step_number = $stepId;
		$data->option = '';
		$data->order_id = $order->order_id;
		$data->revenue = $order->getAmount(false);
		$data->tax = $order->getOrderTax();
		$data->shipping = '';
		$data->coupon_code = $order->getCouponCode();

		if ($order->processor != '')
		{
			$data->option = $order->processor;
		}

		$response[] = $data;
		echo new JsonResponse($response);
		$this->app->close();
	}

	/**
	 * Sets error message.
	 *
	 * @param   string  $error  error message
	 *
	 * @return  Boolean
	 */
	public function setError($error)
	{
		$this->error = $error;
	}

	/**
	 * Sets error message.
	 *
	 * @param   string  $error  error message
	 *
	 * @return  Boolean
	 */
	public function getError($i = null, $toString = true)
	{
		return $this->error;
	}
}
