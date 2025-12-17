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
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

// Joomla 6: formbehavior.chosen removed - using native select

require_once JPATH_SITE . '/components/com_tjvendors/includes/tjvendors.php';
/**
 * Orders view
 *
 * @package  JTicketing
 * @since    1.0.0
 */
class JticketingVieworders extends BaseHtmlView
{
	/**
	 * The order object
	 *
	 * @var  JTicketingOrder
	 */
	public $orderinfo = null;

	/**
	 * The event object
	 *
	 * @var  JTicketingEvent
	 */
	public $event = null;

	/**
	 * The order model object
	 *
	 * @var  JticketingModelorders
	 */
	public $jticketingOrdersModel = '';

	/**
	 * page title
	 *
	 * @var  string
	 */
	public $PageTitle = '';

	/**
	 * Dropdown list of events
	 *
	 * @var  array
	 */
	public $eventList = array();

	/**
	 * Dropdown list of events
	 *
	 * @var  array
	 */
	public $status_event = '';

	/**
	 * Flag to hold event is present or not in the system
	 *
	 * @var  boolean
	 */
	public $noeventsfound = '';

	/**
	 * Order payment statuses
	 *
	 * @var  array
	 */
	public $searchPaymentStatuses = '';

	/**
	 * The jticketing component config
	 *
	 * @var  Joomla\Registry\Registry
	 */
	public $jticketingparams = '';

	/**
	 * The jticketing utility class
	 *
	 * @var  JTicketingUtilities
	 */
	public $utilities = '';

	/**
	 * Order payment statuses
	 *
	 * @var  array
	 */
	public $payment_statuses = '';

	/**
	 * Check whether logged in user is vendor or not
	 *
	 * @var  Boolean|int
	 */
	public $vendorCheck = null;

	/**
	 * Check whether payment gateway is enabled
	 *
	 * @var  Boolean
	 */
	public $checkGatewayDetails = null;

	/**
	 * User checkout information
	 *
	 * @var  Object
	 */
	public $useInfo = null;

	/**
	 * An array of items
	 *
	 * @var  array
	 */
	public $items;

	/**
	 * Id of menu
	 *
	 * @var  int
	 */
	public $Itemid;

	/**
	 * The pagination object
	 *
	 * @var  JPagination
	 */
	public $pagination;

	/**
	 * The model state
	 *
	 * @var  object
	 */
	public $state;

	/**
	 * Form object for search filters
	 *
	 * @var  Joomla\CMS\Form\Form
	 */
	public $filterForm;

	/**
	 * Logged in User
	 *
	 * @var  Joomla\CMS\User\User
	 */
	public $user;

	/**
	 * The active search filters
	 *
	 * @var  array
	 */
	public $activeFilters;

	/**
	 * The default route helper object
	 *
	 * @var  JTRouteHelper
	 */
	public $jtRouteHelper;

	/**
	 * The parameter from url for opening layout in pop-up
	 *
	 * @var  String
	 */
	public $tmpl;

	/**
	 * Default integration value
	 *
	 * @var  String
	 */
	public $integration;

	public $TjGeoHelper;

	public $ticketTypes;

	/**
	 * Method to display events
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public function display($tpl = null)
	{
		$app                    = Factory::getApplication();
		$this->jticketingparams = JT::config();
		$this->integration      = $this->jticketingparams->get('integration');
		$this->state            = $this->get('State');
		$this->jtRouteHelper    = new JTRouteHelper;
		$this->utilities        = JT::utilities();
		$this->ordersListingFields = $this->jticketingparams->get('orders_listing_fields', ['COUPON_CODE_DIS','COM_JTICKETING_FEE','PAY_METHOD'], 'ARRAY');

		// Native Event Manager.
		if ($this->integration < 1)
		{
			?>
			<div class="alert alert-info alert-help-inline">
			<?php echo Text::_('COMJTICKETING_INTEGRATION_NOTICE');?>
			</div>
			<?php

			return;
		}

		$this->user = Factory::getUser();
		$layout = $app->getInput()->getString("layout", 'default');
		$this->tmpl = $app->getInput()->getString("tmpl", '');

		/* @var $orderModel JTicketingModelOrder */
		$orderModel                  = JT::model('order');
		$this->jticketingOrdersModel = $this->getModel();
		JLoader::register('TJVendors', JPATH_SITE . "/components/com_tjvendors/includes/tjvendors.php");
		$vendor = TJVendors::vendor()->loadByUserId($this->user->id, JT::getIntegration());
		$this->vendorCheck           = $vendor->getId();
		$this->checkGatewayDetails   = $vendor->getPaymentConfig() ? true : false;
		$this->payment_statuses      = $orderModel->getOrderStatues('fullforms');

		if ($layout == 'default' || $layout == 'my')
		{
			// Validate user login.
			if (!$this->user->id)
			{
				$msg = Text::_('COM_JTICKETING_MESSAGE_LOGIN_FIRST');
				$current = Uri::getInstance()->toString();
				$url     = base64_encode($current);
				$app->enqueueMessage($msg);
				$app->redirect(Route::_('index.php?option=com_users&view=login&return=' . $url, false));
			}

			$this->items = $this->get('Items');
			$this->pagination = $this->get('Pagination');
			$this->filterForm    = $this->get('FilterForm');
			$this->activeFilters = $this->get('ActiveFilters');
			$this->searchPaymentStatuses = $orderModel->getOrderStatues('list');
			$comParams    = JT::config();

			$statusEvent            = array();

			$this->noeventsfound = (bool) 0;
			$statusEvent[] = HTMLHelper::_('select.option', '0', Text::_('SELONE_EVENT'));
			$ordersModel = JT::model('orders');
			$eventsModel = JT::model('events', array('ignore_request' => true));

			if ($layout == 'my')
			{
				if($this->integration == 3)
				{
					$this->eventList = $ordersModel->getBuyerJEvents($this->user->id);
				}
				else
				{
					$this->eventList = $ordersModel->getBuyerEvents($this->user->id);
				}
			}
			else
			{
				$eventsModel->setState('filter_creator', $this->user->id);
				$this->eventList = $eventsModel->getItems();
				$this->eventList = is_countable($this->eventList) ? $this->eventList : [];
			}

			if (!empty($this->eventList))
			{
				foreach ($this->eventList as $event)
				{
					$eventId    = (int) $event->xref_id;
					$eventName  = htmlspecialchars($event->title);

					if ($comParams->get('enable_eventstartdateinname'))
					{
						$startDate   = $this->utilities->getFormatedDate($event->startdate);
						$eventName   = $eventName . '(' . $startDate . ')';
					}

					$statusEvent[] = HTMLHelper::_('select.option', $eventId, $eventName);
				}
			}

			$this->status_event     = $statusEvent;
		}

		if ($layout == 'order')
		{
			$orderId                = $app->getInput()->get('orderid', '', 'STRING');
			$order			        = JT::order()->loadByOrderId($orderId);
			$eventObj               = JT::event();
			$eventDetails           = $eventObj->loadByIntegration($order->event_details_id);
			$userInfo               = $order->getbillingdata();

			// If user pay money from paypal sometime after paypal redirect session is cleared so not able to display last order
			if ($order->processor != 'paypal')
			{
				// Validate user login.
				if (!$this->user->id && !$this->jticketingparams->get('allow_buy_guest'))
				{
					$msg = Text::_('COM_JTICKETING_MESSAGE_LOGIN_FIRST');
					$current = Uri::getInstance()->toString();
					$url     = base64_encode($current);
					$app->enqueueMessage($msg);
					$app->redirect(Route::_('index.php?option=com_users&view=login&return=' . $url, false));
				}

				if ($this->user->id)
				{
					if (empty($order->id) || (!$order->isOwner() && !$eventDetails->isCreator()))
					{
						$app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'error');

						return;
					}
				}
				else
				{
					$email = $app->getInput()->get('email', '', 'STRING');

					if (md5($userInfo->user_email) != $email)
					{
						$app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'error');

						return;
					}
				}
			}

			$TjGeoHelper               = JPATH_ROOT . '/components/com_tjfields/helpers/geo.php';

			if (!class_exists('TjGeoHelper'))
			{
				JLoader::register('TjGeoHelper', $TjGeoHelper);
				JLoader::load('TjGeoHelper');
			}

			$this->TjGeoHelper     = new TjGeoHelper;
			$this->ticketTypes     = $order->getItemTypes();
			$this->ticketTypes     = ($this->ticketTypes && is_array($this->ticketTypes)) ? $this->ticketTypes : [];
			$this->orderinfo       = $order;
			$this->event           = $eventDetails;
			$this->useInfo         = $userInfo;
			$this->company_name    = $this->jticketingparams->get('company_name', '');
			$this->company_address = $this->jticketingparams->get('company_address', '');
			$this->company_vat_no  = $this->jticketingparams->get('company_vat_no', '');

			// Send google analytics data
			$ecTrackId = Factory::getApplication()->getInput()->get('ecTrackId', '', 'STRING');
			$googleAnalyticsOrderId = base64_decode($ecTrackId);

			// Send order data for google analytics in 'order incomplete state'
			$sendTransData = $this->jticketingparams->get('trackOnOrderFailure');

			if ($sendTransData == 1 || $this->orderinfo->getStatus() === COM_JTICKETING_CONSTANT_ORDER_STATUS_COMPLETED)
			{
				if ($googleAnalyticsOrderId == $this->orderinfo->order_id)
				{
					$document = Factory::getDocument();
					$document->addScriptDeclaration("
						jQuery(document).ready(function()
						{
							jtSite.order.addEcTrackingData('" . $this->orderinfo->id . "', 0);
						});
					");

					$uri = Uri::getInstance();
					$currentUrl = $uri->toString();
					$currentUrl = str_replace("ecTrackId=" . $ecTrackId, "ecTrackId=0", $currentUrl);
					$document->addScriptDeclaration("window.history.pushState( '', '', '" . $currentUrl . "');");
				}
			}
		}

		$this->PageTitle = $this->jticketingparams->get('page_title', '');

		parent::display($tpl);
	}
}
