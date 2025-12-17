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

use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

if (file_exists(JPATH_SITE . '/components/com_tjfields/helpers/geo.php')) { require_once JPATH_SITE . '/components/com_tjfields/helpers/geo.php'; }

/**
 * Orders view class.
 *
 * @since  2.5.0
 */
class JticketingVieworders extends HtmlView
{
	/**
	 * Default geo helper object
	 *
	 * @var  TjGeoHelper
	 */
	public $TjGeoHelper;

	/**
	 * Default Orders model object
	 *
	 * @var  JticketingModelorders
	 */
	public $jticketingOrdersModel;

	/**
	 * Default link for invoice
	 *
	 * @var  String
	 */
	public $linkForInvoice;

	/**
	 * Default Order object
	 *
	 * @var  JTicketingOrder
	 */
	public $orderinfo;

	/**
	 * Default array of orderitem objects
	 *
	 * @var  Array
	 */
	public $orderitems;

	/**
	 * Default array of ticket type objects
	 *
	 * @var  Array
	 */
	public $ticketTypes;

	/**
	 * Default Registry object of configs
	 *
	 * @var  JRegistry
	 */
	public $jticketingparams;

	/**
	 * Default integration value
	 *
	 * @var  String
	 */
	public $integration;

	/**
	 * Default status array
	 *
	 * @var  Array
	 */
	public $paymentStatuses;

	/**
	 * Default Company address
	 *
	 * @var  String
	 */
	public $companyAddress;

	/**
	 * Default Company Name
	 *
	 * @var  String
	 */
	public $companyName;

	/**
	 * Default Company vat no
	 *
	 * @var  String
	 */
	public $companyVatNo;

	/**
	 * Default pagination object
	 *
	 * @var  JPagination
	 */
	public $pagination;

	/**
	 * Method to display calendar
	 *
	 * @param   object  $tpl  tpl
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function display($tpl = null)
	{
		$mainframe 					= Factory::getApplication();
		$input 						= $mainframe->input;
		$this->jticketingparams		= JT::config();
		$this->integration 			= $this->jticketingparams->get('integration');
		$this->user  				= Factory::getUser();
		$layout 					= $input->get('layout', 'default');
		$this->jticketingmainhelper = new jticketingmainhelper;
		$this->utilities 			= JT::utilities();
		$this->ordersListingFields = $this->jticketingparams->get('orders_listing_fields', ['COUPON_CODE_DIS','COM_JTICKETING_FEE','PAY_METHOD'], 'ARRAY');

		JticketingHelper::getLanguageConstant();
		ToolbarHelper::preferences('com_jticketing');
		$this->setToolBar();

		// Access check: is this user allowed to access the backend of this component
		if ((!Factory::getUser()->authorise('core.admin', 'com_jticketing')
			&& !Factory::getUser()->authorise('core.manage', 'com_jticketing'))
			|| empty($this->user->id))
		{
			$mainframe->enqueueMessage('JERROR_ALERTNOAUTHOR', 'error');

			return false;
		}

		// Native Event Manager.
		if ($this->integration < 1)
		{
			$mainframe->enqueueMessage('COMJTICKETING_INTEGRATION_NOTICE', 'notice');

			return false;
		}

		// Get order status array with their full forms.
		$orderModel = JT::model('order');
		$this->paymentStatuses = $orderModel->getOrderStatues('fullforms') ? $orderModel->getOrderStatues('fullforms') : array();
		$bsVersion = (JVERSION < '4.0.0') ? '_bs2' : '_bs5';

		if ($layout === 'order' . $bsVersion)
		{
			$this->TjGeoHelper 		= new TjGeoHelper;
			$this->order_id			= $input->get('orderid', '', 'STRING');
			$this->companyName    	= $this->jticketingparams->get('company_name', '');
			$this->companyAddress 	= $this->jticketingparams->get('company_address', '');
			$this->companyVatNo  	= $this->jticketingparams->get('company_vat_no', '');

			/* var $this->orderinfo JTicketingOrder */
			$this->orderinfo			= JT::order()->loadByOrderId($this->order_id);
			$this->userInfo 			= $this->orderinfo->getbillingdata();
			$this->orderitems			= $this->orderinfo->getItems();
			$this->eventdetails 		= JT::event()->loadByIntegration($this->orderinfo->event_details_id);
			$this->ticketTypes			= $this->orderinfo->getItemTypes();

			if (empty($this->orderinfo))
			{
				$mainframe->enqueueMessage('JERROR_ALERTNOAUTHOR', 'error');

				return false;
			}
		}

		if ($layout === 'default')
		{
			// Get the State.
			$this->state        	= $this->get('State');

			// Get filter form.
			$this->filterForm 		= $this->get('FilterForm');

			// Get active filters.
			$this->activeFilters 	= $this->get('ActiveFilters');

			$this->jticketingOrdersModel 	= $this->getModel();
			$this->linkForInvoice 			= 'index.php?option=com_jticketing&view=orders&layout=order' . $bsVersion . '&tmpl=component';

			// Get data from the model
			$this->items        	= $this->get('Items');
			$this->activeFilters 	= $this->get('ActiveFilters');
			$this->filterForm    	= $this->get('FilterForm');
			$this->pagination		= $this->get('Pagination');

			// Following variables used more than once
			$this->sortColumn 		= $this->state->get('list.ordering');
			$this->sortDirection	= $this->state->get('list.direction');

			$JticketingHelper 	= new JticketingHelper;
			$JticketingHelper->addSubmenu('orders');
			// Joomla 6: HTMLHelperSidebar::render() removed - sidebar functionality removed
			$this->setLayout($layout);
		}

		parent::display($tpl);
	}

	/**
	 * Method to set toolbar
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function setToolBar()
	{
		$document = Factory::getDocument();
		HTMLHelper::_('stylesheet', 'components/com_jticketing/assets/css/jticketing.css');
		ToolbarHelper::title(Text::_('COM_JTICKETING_COMPONENT') . Text::_('ORDER_VIEW'), 'folder');
		ToolbarHelper::deleteList('', 'orders.remove', 'JTOOLBAR_DELETE');
	}
}
