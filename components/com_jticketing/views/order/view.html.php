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
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

/**
 * View for checkout
 *
 * @package     JTicketing
 * @subpackage  component
 * @since       1.0
 */
class JticketingViewOrder extends BaseHtmlView
{
	/**
	 * Default country code
	 *
	 * @var  string
	 */
	public $defaultCountry;

	/**
	 * User ID
	 *
	 * @var  integer Id
	 */
	public $userid;

	/**
	 * Config param
	 *
	 * @var  Joomla\Registry\Registry
	 */
	public $jtParams;

	/**
	 * Event integration set
	 *
	 * @var  integer
	 */
	public $integration;

	/**
	 * utilities class
	 *
	 * @var  JTicketingUtilities
	 */
	public $utilities;

	/**
	 * order class
	 *
	 * @var JTicketingOrder
	 */
	public $order;

	/**
	 * event class
	 *
	 * @var  JTicketingEvent
	 */
	public $event;

	/**
	 * Order items array
	 *
	 * @var  object
	 */
	public $orderItems;

	/**
	 * attendee fields array
	 *
	 * @var  array
	 */
	public $fields = array();

	/**
	 * Default mobile country code
	 *
	 * @var  string
	 */
	public $defaultCountryMobileCode;

	/**
	 * Determine whether a free ticket is available or not in the ticket types
	 *
	 * @var  integer
	 */
	public $ticketPriceNotFree;

	/**
	 * The country list generated from the com_tjfield database
	 *
	 * @var  array
	 */
	public $country;

	/**
	 * The ticket type data
	 *
	 * @var  array
	 */
	public $ticketTypeData = array();

	/**
	 * Available gateways
	 *
	 * @var  array
	 */
	public $gateways = array();

	/**
	 * User billing data
	 *
	 * @var  object
	 */
	public $userbill;

	/**
	 * Attendee fields model
	 *
	 * @var  JTicketingModelAttendeefields
	 */
	public $attendeeFieldsModel;

	/**
	 * Hold the attendee is available for edit or not
	 *
	 * @var  boolean
	 */
	public $attendeeEdit = true;

	/**
	 * link to the attendee layout
	 *
	 * @var  string
	 */
	public $attendeeRedirectLink = '';

	/**
	 * config for showing consent
	 *
	 * @var  string
	 */
	public $concent = '';

	/**
	 * config for showing article link in consent checkbox
	 *
	 * @var  string
	 */
	public $orderArticle = '';

	/**
	 * Method to display events
	 *
	 * @param   object  $tpl  tpl
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function display($tpl = null)
	{
		$document             = Factory::getDocument();
		$user                 = Factory::getUser();
		$this->userid         = $user->id;
		$app                  = Factory::getApplication();
		$input                = $app->input;
		$layout               = $input->get('layout', 'default');
		$this->jtParams       = JT::config();
		$this->utilities      = JT::utilities();
		$this->order = JT::order($input->getInt('orderId'));
		$this->event = JT::event()->loadByIntegration($this->order->event_details_id);
		$this->orderItems     = $this->order->getItems();
		$jtRouteHelper 		  = new JTRouteHelper;
		$gaEcommerce          = $this->jtParams ->get('ga_ec_analytics', 0);
		$session              = Factory::getSession();

		if (!$this->order->id || !$this->event->getId())
		{
			echo Text::_("COM_JTICKETING_SOMETHING_WENT_WRONG");

			return;
		}

		// Check if user is accessing his own order.
		if ((!$this->jtParams->get('allow_buy_guest') && !($user->id)) ||($this->order->user_id !== (int) $user->id))
		{
			$app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'error');
			$app->redirect($this->event->getUrl());
		}

		// If event buyer access order view directly by url when booking is closed or not yet started
		if (!$this->event->isAllowedToBuy())
		{
			$app->enqueueMessage(Text::_('COM_JTICKETING_ORDER_NOT_ACCESSIBLE'), 'message');
			$app->redirect($this->event->getUrl());
		}

		// If Event Owners not allowed to buy ticket return false
		if (isset($user->id) && ($user->id == $this->event->getCreator()) && !$this->jtParams->get('eventowner_buy'))
		{
			$app->enqueueMessage(Text::_('COM_JTICKETING_EVENT_OWNER_CANT_BUY'), 'message');
			$app->redirect($this->event->getUrl());
		}

		// If guest user Hijacking Booking Orders (same order use multiple guest users) 
		if (!($user->id) && $this->order->id != $session->get('JT_orderId'))
		{
			$app->enqueueMessage(Text::_('COM_JTICKETING_ORDER_AUTHORISATION_FAILED'), 'error');
			$app->redirect($this->event->getUrl());
		}

		$this->integration = JT::getIntegration();

		$pageTitle = Text::_('COM_JTICKETING_ORDER_BOOK_YOUR_TICKETS');
		$document->setTitle($pageTitle);
		$this->ticketTypeData = $this->event->getTicketTypes();

		// Get Order items
		if (!empty($this->orderItems))
		{
			// Check if attendee data config is set and redirect accordingly.
			$this->attendeeRedirectLink = $jtRouteHelper->JTRoute(
														'index.php?option=com_jticketing&view=order&layout=attendee&orderId=' . $this->order->id, false
														);

			foreach ($this->orderItems as $orderData)
			{
				if (empty($orderData->attendee_id))
				{
					$this->attendeeEdit = false;
				}
			}
		}

		$singleTicketPerUser 	= $this->jtParams->get('single_ticket_per_user', '0');
		$ticketPurchaseLimit 	= $this->jtParams->get('max_noticket_peruserperpurchase', '8');

		// In case of single ticket type is set and ticket type is one then add it by default
		if ($layout == 'default'
			&& (($singleTicketPerUser == '1') || ($this->event->isOnline())
			|| ($singleTicketPerUser == '0' && $ticketPurchaseLimit === '1'))
			&& count($this->orderItems) != 1
			&& count($this->ticketTypeData) === 1)
		{
			$ticketType 	= JT::tickettype(current($this->ticketTypeData)->id);
			$this->order->addItem($ticketType);
		}

		if ($layout == 'attendee')
		{
			// Get global and event specific attendee fields. global - TJField Event - attendee fields table
			$this->attendeeFieldsModel = JT::model('attendeefields');
		}

		if ($layout == 'billing')
		{
			$userData = JT::user();
			$this->concent        = $this->jtParams->get('article', '0');
			$this->orderArticle   = $this->jtParams->get('tnc', '');

			// Filling user info according to community
			$profileImport               = $this->jtParams->get('profile_import');

			// Attendee Model
			$JTicketingIntegrationsHelper = new JTicketingIntegrationsHelper;
			$cdata = array('userbill' => '');

			// @TODO - Snehal - move this to user model
			if ($user->id)
			{
				$userModel = JT::model('user');
				$userData = $userModel->getUserData();
			}

			if ($profileImport)
			{
				$cdata = JT::integration()->profileImport();
			}

			if ($user->id)
			{
				// Use profile data if more than 2 fields present in BT address.
				$this->userbill = (isset($userData['BT']) &&
								(count((array) $userData['BT']) >= 2)) ? $userData['BT'] : $cdata['userbill'];
			}

			$this->defaultCountryMobileCode = (!empty($this->userbill->country_mobile_code))
			? $this->userbill->country_mobile_code : $this->jtParams->get('default_country_mobile_code');

			// Show or hide billing information field(s). // do this is layout file
			$showSelectedFields = $this->jtParams->get('show_selected_fields');

			if ($showSelectedFields == 1)
			{
				$billingInfoFields = $this->jtParams->get('billing_info_field');

				if (isset($billingInfoFields))
				{
					foreach ($billingInfoFields as $field)
					{
						switch ($field)
						{
							case 'address':
								$this->address_config = 1;
							break;

							case 'country':
								$this->country_config = 1;
							break;

							case 'state':
								$this->state_config = 1;
							break;

							case 'city':
								$this->city_config = 1;
							break;

							case 'zip':
								$this->zip_config = 1;
							break;

							case 'customer_note':
								$this->customer_note_config = 1;
							break;
						}
					}
				}
			}
		}

		if ($layout == 'payment')
		{
			if (!$this->order->reValidateAmount())
			{
				$app->enqueueMessage(Text::_('COM_JTICKETING_INVALID_ORDER_AMOUNT_VALIDATION'), 'message');
				$app->redirect($this->event->getUrl());
			}

			PluginHelper::importPlugin('payment');

			$gateways = array();

			if (!empty($this->jtParams->get('gateways')))
			{
				$paymentConfig   = $this->jtParams->get('gateways');
				if (!is_array($paymentConfig))
				{
					$gateway       = $paymentConfig;
					$paymentConfig = array();
					$paymentConfig[]   = $gateway;
				}

				$gateways = Factory::getApplication()->triggerEvent('onTP_GetInfo', array($paymentConfig));
			}

			$newgateways = array();

			foreach ($gateways as $gateway)
			{
				if (!empty($gateway->id))
				{
					if (empty($gateway->name))
					{
						$gateway->name = $gateway->id;
					}

					$newgateways[] = $gateway;
				}
			}

			$this->gateways      = $newgateways;
		}

		if ($layout == 'default' && $gaEcommerce)
		{
			$document->addScriptDeclaration('jtSite.order.addEcTrackingData("' . $this->order->id . '", 1);');
		}

		$this->country       = $this->get('Country');
		$this->defaultCountry     = $this->jtParams->get('default_country');

		parent::display($tpl);
	}
}
