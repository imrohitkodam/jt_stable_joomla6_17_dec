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
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Response\JsonResponse;

require_once JPATH_ADMINISTRATOR . '/components/com_jticketing'. '/controller.php';

/**
 * Controller for payment
 *
 * @package     JTicketing
 * @subpackage  component
 * @since       1.0
 */
class JticketingControllerpayment extends jticketingController
{
	/**
	 * Confirm payment
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function confirmpayment()
	{
		$model    = $this->getModel('payment');
		$session  = Factory::getSession();
		$jinput   = Factory::getApplication()->getInput();
		$order_id = $session->get('JT_orderid');

		if (!$order_id)
		{
			$order_id = $jinput->get("orderid", '', 'int');
		}

		$session->set('JT_order_id', $order_id);
		$pg_plugin = $jinput->get('processor');
		$response  = $model->confirmpayment($pg_plugin, $order_id);
	}

	/**
	 * process payment and pass data to model
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function processpayment()
	{
		$mainframe = Factory::getApplication();
		$jinput    = Factory::getApplication()->getInput();
		$session   = Factory::getSession();

		if ($session->has('payment_submitpost'))
		{
			$post = $session->get('payment_submitpost');
			$session->clear('payment_submitpost');
		}
		else
		{
			$post = $jinput->post->getArray();
		}

		$orderId  = $jinput->get('order_id', '', 'STRING');

		$pg_plugin  = $jinput->get('processor');
		$model      = $this->getModel('payment');

		if ($pg_plugin == 'razorpay') 
		{
			// Get the JSON payload from Razorpay
			$input = file_get_contents('php://input');
			$event = json_decode($input, true);
			$entity = $event['payload']['payment']['entity'];
			$notes = $event['payload']['payment']['entity']['notes'];

			if ($notes['client'] == 'jticketing')
			{
				$orderId = $notes['order_id'];
				$post = $entity;
			}
		}

		if (empty($post) || empty($pg_plugin))
		{
			Factory::getApplication()->enqueueMessage(Text::_('SOME_ERROR_OCCURRED'), 'error');

			return;
		}

		$response = $model->processpayment($post, $pg_plugin, $orderId);
		$msg      = !empty($response['msg']) ? $response['msg'] : '';

		if (isset($response['status']) && !$response['status'])
		{
			$mainframe->enqueueMessage($msg, 'error');
		}
		else
		{
			$mainframe->enqueueMessage($msg, 'success');
		}

		$mainframe->redirect($response['return']);
	}

	/**
	 * Change payment gateway
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function changegateway()
	{
		$app        = Factory::getApplication();

		if (!Session::checkToken())
		{
			echo new JsonResponse(null, Text::_('JINVALID_TOKEN'), true);
			$app->close();
		}

		$jinput     = Factory::getApplication()->getInput();
		$orderID    = $jinput->getInt('order_id');

		if (!empty($orderID))
		{
			$model  = $this->getModel('payment');
			$html   = $model->changegateway();

			echo new JsonResponse($html);
			$app->close();
		}

		$app->enqueueMessage(Text::_('COM_JTICKETING_SOMETHING_WENT_WRONG'), 'error');
		echo new JsonResponse;
		$app->close();
	}

	/**
	 * Redirect to stripe
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function authStripeConnect()
	{
		$input = Factory::getApplication()->getInput();
		PluginHelper::importPlugin('payment', 'stripe');
		$authUrl    = Factory::getApplication()->triggerEvent('onStripeConnectAuthUrl', array());

		if (!empty($authUrl[0]))
		{
			header('Location:' . $authUrl[0]);
		}
	}

	/**
	 * Function to get IPN response from Payment gateway.
	 *
	 * @return  redirects
	 *
	 * @since  1.0.0
	 */
	public function notify()
	{
		$app = Factory::getApplication();
		$jinput    = $app->input;
		$session   = Factory::getSession();

		if ($session->has('payment_submitpost'))
		{
			$post = $session->get('payment_submitpost');
			$session->clear('payment_submitpost');
		}
		else
		{
			$post = $jinput->post->getArray();
		}

		$pg_plugin = $jinput->get('processor', '', 'STRING');
		$model     = $this->getModel('payment');
		$order_id  = $jinput->get('order_id', '', 'STRING');

		if (empty($post) || empty($pg_plugin))
		{
			$app->enqueueMessage(Text::_('SOME_ERROR_OCCURRED'), 'error');

			return;
		}

		$response = $model->processpayment($post, $pg_plugin, $order_id);

		echo new JsonResponse($response['status'], $response['msg']);

		jexit();
	}

	/**
	 * Get autorisation url for stripe
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function StoreStripeConnectParmas()
	{
		$input   = Factory::getApplication()->getInput();
		$ac_code = $input->get('code', '', 'STRING');

		PluginHelper::importPlugin('payment', 'stripe');

		//  Params auth code, component name
		$result = Factory::getApplication()->triggerEvent('onStoreStripeConnectParmas', array($ac_code, 'com_jticketing'));

		$session  = Factory::getSession();
		$redirect = $session->get('url_create_event');

		if ($result[0])
		{
			$app = Factory::getApplication();
			$app->enqueueMessage(Text::_('COM_JGIVE_STRIPE_CONNECTED'), 'success');
			$this->setRedirect($redirect);
		}
		else
		{
			$this->setRedirect($redirect);
		}
	}

	/**
	 * Add stripe data to payout
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function stripeAddPayout()
	{
		// If( $pg_plugin == 'stripe')
		{
			// Retrieve the request's body and parse it as JSON
			$body = @file_get_contents('php://input');

			// Grab the event information
			$post = $event_json = json_decode($body, true);
			file_put_contents('stripeweb.txt', json_encode($post), FILE_APPEND);

			if ($post['type'] == "application_fee.created")
			{
				$model = $this->getModel('payment');

				// Parmas data, refund flag
				$model->stripeAddPayout($post, 0);
			}
			elseif ($post['type'] == "application_fee.refunded")
			{
				// Parmas data, refund flag
				$model->stripeAddPayout($post, 1);
			}
			else
			{
				return true;
			}
		}

		return true;
	}
}
