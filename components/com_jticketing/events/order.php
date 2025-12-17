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
use Joomla\CMS\Plugin\PluginHelper;

/**
 * JTicketing triggers class for order.
 *
 * @since       2.1
 *
 * @deprecated  2.5.0  JticketingTriggerEvent classes will be replaced its methods will available in order's model
 */
class JticketingTriggerOrder
{
	/**
	 * Trigger for order status change
	 *
	 * @param   JTicketingOrder  $orderDetails  Order Details
	 * @param   Array            $params        Other required Data
	 *
	 * @return  void
	 */
	public function onOrderStatusChange($orderDetails, $params)
	{
		$comParams                      = JT::config();
		$affectIntegrationAttendeeSeats = $comParams->get('affect_js_native_seats');
		$integration                    = $comParams->get('integration');
		$eventInfo                      = JT::event()->loadByIntegration($orderDetails->event_details_id);
		$eventFormModel                 = JT::model('EventForm');

		if ($integration == 2)
		{
			if ($params['action'] != 'delete')
			{
				// Insert or update jliketodo depends on the data passed
				$eventFormModel->saveTodo($params);
			}
			else
			{
				$eventFormModel->deleteTodo($params);
			}
		}

		if ($affectIntegrationAttendeeSeats == 1)
		{
			$integration = JT::getIntegration(true);

			if ($integration == 4)
			{
				// Update easysocial attendee count
				$path = JPATH_ADMINISTRATOR . '/components/com_easysocial/includes/easysocial.php';
				JLoader::register('ES', $path, true);

				$eventId = $eventInfo->id;
				$userId  = $orderDetails->user_id;

				if ($orderDetails->getStatus() === COM_JTICKETING_CONSTANT_ORDER_STATUS_COMPLETED)
				{
					$task = 'going';
				}
				elseif($orderDetails->getStatus() === COM_JTICKETING_CONSTANT_ORDER_STATUS_PENDING)
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
		}

		PluginHelper::importPlugin('jticketing');
		Factory::getApplication()->triggerEvent('onAfterJtOrderStatusChange', array($orderDetails->id, $orderDetails->getStatus()));
	}
}
