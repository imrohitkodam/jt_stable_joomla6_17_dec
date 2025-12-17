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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Controller\BaseController;

require_once JPATH_ADMINISTRATOR . '/components/com_jticketing'. '/controller.php';
if (file_exists(JPATH_SITE . '/components/com_jticketing/events/order.php')) { require_once JPATH_SITE . '/components/com_jticketing/events/order.php'; }

/**
 * controller for showing order
 *
 * @package     JTicketing
 * @subpackage  component
 * @since       1.0
 */
class JticketingControllerorders extends BaseController
{
	/**
	 * Changes order status for example pending to completed
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function save()
	{
		$mainframe		= Factory::getApplication();
		$linkForOrders	= 'index.php?option=com_jticketing&view=orders';

		if (!Session::checkToken())
		{
			$mainframe->enqueueMessage(Text::_('JINVALID_TOKEN'), 'error');

			$mainframe->redirect($linkForOrders);
		}

		$input			= $mainframe->input;
		$post			= $input->post;
		$statusArray 	= [COM_JTICKETING_CONSTANT_ORDER_STATUS_PENDING,
							COM_JTICKETING_CONSTANT_ORDER_STATUS_COMPLETED,
							COM_JTICKETING_CONSTANT_ORDER_STATUS_DECLINE,
							COM_JTICKETING_CONSTANT_ORDER_STATUS_FAILED,
							COM_JTICKETING_CONSTANT_ORDER_STATUS_UNDER_REVIEW,
							COM_JTICKETING_CONSTANT_ORDER_STATUS_REFUND,
							COM_JTICKETING_CONSTANT_ORDER_STATUS_CANCEL_REVERSED,
							COM_JTICKETING_CONSTANT_ORDER_STATUS_REVERSED];

		$orderId     	= $post->get('order_id');

		/** @var $order JticketingOrder */
		$order        	= JT::order($orderId);
		$event		 	= JT::event()->loadByIntegration($order->event_details_id);
		$user         	= Factory::getUser();
		$redirectview	= $post->get('redirectview', '', 'STRING');
		$status 		= $post->get('payment_status', '', 'STRING');

		// Allow to change status to event creator or Admin access user.
		if ($user->authorise('core.admin', 'com_jticketing') === false && $user->authorise('core.manage', 'com_jticketing') === false
			&& $user->id != $event->getCreator())
		{
			$mainframe->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'Error');
			$mainframe->redirect(!empty($redirectview) ? $redirectview : $linkForOrders);
		}

		// Check if status is the status from the status array.
		if (!in_array($status, $statusArray))
		{
			$this->setRedirect($linkForOrders);
		}

		/** @var $ordersModel JticketingModelorders */
		$ordersModel 	= JT::model('orders');
		$result 		= $ordersModel->changeOrderStatus($order, $status);

		if ($result)
		{
			$mainframe->enqueueMessage(Text::_('COM_JTICKETING_ORDER_STATUS_CHANGED'), 'success');
		}
		else
		{
			$mainframe->enqueueMessage($ordersModel->getError(), 'Error');
		}

		$mainframe->redirect(!empty($redirectview) ? $redirectview : $linkForOrders);
	}

	/**
	 * Changes order status for example pending to completed
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function remove()
	{
		Session::checkToken() or Session::checkToken('get') or jexit('Invalid Token');

		$model = $this->getModel('orders');
		$orderid = $this->input->post->get('cid', array(), 'array');

		if ($model->delete($orderid))
		{
			$msg = Text::_('COM_JTICKETING_ORDER_DELETED_SCUSS');
		}
		else
		{
			$msg = Text::_('COM_JTICKETING_ORDER_DELETED_ERROR');
		}

		$this->setRedirect("index.php?option=com_jticketing&view=orders", $msg);
	}

	/**
	 * cancel to redirect to control panel
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function cancel()
	{
		$this->setRedirect('index.php?option=com_jticketing');
	}

	/**
	 * function to csv export data
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function csvexport()
	{
		$com_params    = ComponentHelper::getParams('com_jticketing');
		$currency      = $com_params->get('currency');
		$model         = $this->getModel('attendees');
		$model_results = $model->getData();
		$db            = Factory::getDbo();
		$query         = "SELECT d.ad_id, d.ad_title, d.ad_payment_type, d.ad_creator,d.ad_startdate, d.ad_enddate,
							i.processor, i.ad_credits_qty, i.cdate, i.ad_amount,i.status,i.id
							FROM #__ad_data AS d RIGHT JOIN #__ad_payment_info AS i ON d.ad_id = i.ad_id";
		$db->setQuery($query);
		$results = $db->loadObjectList();
		$csvData = null;
		$csvData .= "Attender_Name,Bought_On,Ticket_Type,Ticket_Rate,Number_of_tickets_bought,Total_Amount_(A-B)";
		$csvData .= "\n";
		$filename = "Jt_attendees_" . date("Y-m-d_H-i", time());
		header("Content-type: application/vnd.ms-excel");
		header("Content-disposition: csv" . date("Y-m") . ".csv");
		header("Content-disposition: filename=" . $filename . ".csv");
		$totalnooftickets = $totalprice = $totalcommission = $totalearn = 0;

		foreach ($model_results as $result)
		{
			$totalnooftickets = $totalnooftickets + $result->ticketcount;
			$totalprice       = $totalprice + $result->amount;
			$totalearn        = $totalearn + $result->totalamount;
			$csvData .= '"' . $result->name . '"' . ',';
			$csvData .= '"' . (JVERSION < "1.6.0" ? HTMLHelper::_('date', $result->cdate, '%Y/%m/%d')
			:HTMLHelper::_('date', $result->cdate, "Y-m-d")) . '"' . ',';
			$csvData .= '"' . $result->ticket_type_title . '"' . ',';
			$csvData .= '"' . $result->amount . ' ' . $currency . '"' . ',';
			$csvData .= '"' . $result->ticketcount . '"' . ',';
			$csvData .= '"' . $result->totalamount . $currency . '"';
			$csvData .= "\n";
		}

		$csvData .= '" "," ","' . Text::_('TOTAL') . '","';
		$csvData .= number_format($totalnooftickets, 2, '.', '') . '","';
		$csvData .= number_format($totalprice, 2, '.', '') . $currency . '","';
		$csvData .= number_format($totalearn, 2, '.', '') . $currency . '"';
		$csvData .= "\n";
		print $csvData;
		jexit();
	}
}
