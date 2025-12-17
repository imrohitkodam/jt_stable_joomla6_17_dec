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
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Response\JsonResponse;

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
	public $fields;

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
		$user                 = Factory::getUser();
		$this->userid         = $user->id;
		$app                  = Factory::getApplication();
		$input                = $app->input;
		$this->jtParams       = JT::config();
		$this->utilities      = JT::utilities();
		$this->order = JT::order($input->getInt('orderId'));
		$this->event = JT::event()->loadByIntegration($this->order->event_details_id);

		if (!$this->order->id || !$this->event->getId())
		{
			echo new JsonResponse(null, Text::_("COM_JTICKETING_SOMETHING_WENT_WRONG"), true);
			$app->close();
		}

		// Check if user is accessing his own order.
		if ((!$this->jtParams->get('allow_buy_guest') && !($user->id)) ||($this->order->user_id !== (int) $user->id))
		{
			echo new JsonResponse(null, Text::_('JERROR_ALERTNOAUTHOR'), true);
			$app->close();
		}

		// If Event Owners not allowed to buy ticket return false
		if (isset($user->id) && ($user->id == $this->event->getCreator()) && !$this->jtParams->get('eventowner_buy'))
		{
			echo new JsonResponse(null, Text::_('COM_JTICKETING_EVENT_OWNER_CANT_BUY'), true);
			$app->close();
		}

		// If event buyer access order view directly by url when booking is closed or not yet started
		if (!$this->event->isAllowedToBuy())
		{
			echo new JsonResponse(null, Text::_('COM_JTICKETING_ORDER_NOT_ACCESSIBLE'), true);
			$app->close();
		}

		$this->integration    = JT::getIntegration();

		// As attendees are against order items get order items against orders.
		$this->orderItems = $this->order->getItems();
		$this->ticketTypeData = $this->event->getTicketTypes();

		echo new JsonResponse($this->loadTemplate($tpl));
		$app->close();
	}
}
