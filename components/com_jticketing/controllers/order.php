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
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;

require_once JPATH_ADMINISTRATOR . '/components/com_jticketing'. '/controller.php';

/**
 * JTicketing
 *
 * @since  1.6
 */
class JticketingControllerOrder extends JticketingController
{
	/**
	 * Method to add data in order table.
	 *
	 * @return  string|boolean  on success URL
	 *
	 * @since  2.5.0
	 */
	public function addOrder()
	{
		Session::checkToken() or Session::checkToken('get') or jexit('Invalid Token');

		$app        = Factory::getApplication();
		$input      = $app->input;
		$eventID    = $input->getInt('eventId');
		$event      = JT::event($eventID);
		$user       = Factory::getUser();
		$config     = JT::config();
		$allowGuest = $config->get('allow_buy_guest', 0);
		$integration    = JT::getIntegration();
		$jtRouteHelper = new JTRouteHelper;

		// Check if guest checkout is not allowed and user is not logged in.
		if ((!$allowGuest && !$user->id) || ($integration == 'com_jticketing' && $event->online_events && !$user->id))
		{
			// Get menu of all-events by passing  event category.
			$menu       = $app->getMenu();
			$eventsUrl  = 'index.php?option=com_jticketing&view=events&layout=default&catid=' . $event->getCategory();
			$menuItem   = $menu->getItems('link', $eventsUrl, true);

			$itemId = 0;

			if (!empty($menuItem->id))
			{
				$itemId = $menuItem->id;
			}

			// Pass the url by using base64 encode so that after login user will redirected to event page.
			$loginPageMenu = $menu->getItems('link', 'index.php?option=com_users&view=login', true);

			if (!empty($loginPageMenu->id))
			{
				$loginPageItemId = $loginPageMenu->id;
			}

			$eventUrl               = 'index.php?option=com_jticketing&view=event&id=' . $eventID . '&Itemid=' . $itemId;
			$url                    = base64_encode($eventUrl);
			$redirect = Route::_('index.php?option=com_users&view=login&Itemid=' . $loginPageItemId . '&return=' . $url, false);

			$app->redirect($redirect);

			return;
		}

		$order = JT::order();
		$order->bind(
			array(
			'event_details_id' => $event->integrationId,
			'user_id' => $user->id,
			'name' => $user->name,
			'email' => $user->email)
			);

		if ($order->loadOrCreate())
		{
			if ($allowGuest && !empty($order->id))
			{
				Factory::getSession()->set('JT_orderId', $order->id);
			}

			if ($config->get('add_default_ticket'))
			{
				// Adding orderItem for the order with has no orderItems against 1st ticket of event
				$order      = JT::order($order->id);
				$orderItems = $order->getItems();
				$singleTicket = $config->get('single_ticket_per_user', 0);

				if (empty($orderItems) && !$singleTicket)
				{
					// Get the tickets of event
					$tickets = $event->getTicketTypes();

					if (!$order->addItem(JT::tickettype($tickets[0]->id)))
					{
						$app->enqueueMessage($order->getError(), 'error');

						return false;
					}
				}
			}

			$buyLink = 'index.php?option=com_jticketing&view=order&orderId=' . $order->id;
			$link = $jtRouteHelper->JTRoute($buyLink, false);

			$app->redirect($link);

			return;
		}

		$app->enqueueMessage($order->getError(), 'error');

		return false;
	}

	/**
	 * Function change gateway
	 *
	 * @return  array
	 *
	 * @since  1.0.0
	 */
	public function changegateway()
	{
		$model = $this->getModel('payment');
		$model->changegateway();
	}

	// @TODO:Add this in booking ticket email

	/**
	 * Get checkGeustForOnlineEvent
	 *
	 * @deprecated  2.5.0 use the alternative methods from the libraries
	 *
	 * @return  array
	 *
	 * @since  1.0.0
	 */
	public function checkGeustForOnlineEvent()
	{
		$redirect = Route::_('index.php?option=com_jticketing&view=order&layout=default_online', false);
		$this->setRedirect($redirect, $msg);
	}

	/**
	 * Function to create order
	 *
	 * @return  array
	 *
	 * @deprecated  2.5.0 use the alternative methods from the libraries
	 *
	 * @since  1.0.0
	 */
	public function createOrder()
	{
		$input   = Factory::getApplication()->getInput();
		$eventId = $input->get('event_id');
		$ticketId = $input->get('ticket_id');
		$user    = Factory::getUser();

		if (!empty($eventId) && !empty($user->id))
		{
			$data = array();
			$data['user_id'] = $user->id;
			$data['eventid'] = $eventId;

			if (!empty($ticketId))
			{
				$data['ticket_id'] = $ticketId;
			}

			if (!empty($data))
			{
				$model  = $this->getModel('order');
				$result = $model->createOrder($data);

				$input  = Factory::getApplication()->getInput();
				$option = $input->get('option');

				$mainframe  = Factory::getApplication();

				if ($option == 'com_jticketing' && $result)
				{
					if ($mainframe->isClient("site"))
					{
						$redirect = Route::_("index.php?option=com_jticketing&view=event&id=" . $eventId, false);
						$msg = Text::_('COM_JTICKETING_ENROLL_SUCCESS_MSG');
						$this->setRedirect($redirect, $msg);
					}
				}
				else
				{
					$errorMsg = Text::_('COM_JTICKETING_EVENTS_BOOKING_IS_CLOSED');
					$mainframe->enqueueMessage($errorMsg, 'error');

					$mainframe->redirect(Route::_("index.php?option=com_jticketing&view=event&id=" . $eventId, false));
				}
			}
		}
	}
}
