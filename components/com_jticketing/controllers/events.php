<?php
/**
 * @package     JTicketing
 * @subpackage  com_jticketing
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

require_once JPATH_ADMINISTRATOR . '/components/com_jticketing'. '/controller.php';

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\Utilities\ArrayHelper;

/**
 * Jtevent controller
 *
 * @since  1.0
 */
class JticketingControllerEvents extends JticketingController
{
	/**
	 * Proxy for getModel.
	 *
	 * @param   string  $name    model name
	 * @param   string  $prefix  prefix
	 *
	 * @return  model object
	 *
	 * @since   1.0
	 */
	public function &getModel($name = 'Events', $prefix = 'JticketingModel', $config = array())
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));

		return $model;
	}



	/**
	 * Method to clone existing Events
	 *
	 * @return void
	 *
	 * since  2.6.0
	 */
	public function duplicate()
	{
		// Check for request forgeries
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));
		$app   = Factory::getApplication();
		$pks = $this->input->post->get('cid', array(), 'array');
		ArrayHelper::toInteger($pks);

		try
		{
			if (empty($pks))
			{
				throw new Exception(Text::_('COM_JTICKETING_NO_ELEMENT_SELECTED'));
			}

			$model = $this->getModel('eventform');
			$model->duplicate($pks);
			$app->enqueueMessage(Text::_('COM_JTICKETING_VENUE_CAPACITY_CREATE_ERROR'), 'error');
			$this->setMessage(Text::plural('COM_JTICKETING_ITEMS_SUCCESS_DUPLICATED', count($pks)));
		}
		catch (Exception $e)
		{
			$this->setMessage($e->getMessage(), 'error');
		}

		$menu = $app->getMenu();
		$items = $menu->getItems('link', 'index.php?option=com_jticketing&view=events&layout=my');

		if (isset($items[0]))
		{
			$itemId = $items[0]->id;
		}
		else 
		{
			$itemId   = JT::utilities()->getItemId('index.php?option=com_jticketing&view=events&layout=my');
		}

		// Overrride the redirect Uri.
		$redirectUri = 'index.php?option=com_jticketing&view=events&layout=my&Itemid='. $itemId .'&extension=' . $this->input->get('extension', '', 'CMD');
		$this->setRedirect(Route::_($redirectUri, false), $this->message, $this->messageType);
	}

	/**
	 * Method to save a user's profile data.
	 *
	 * @return    void
	 *
	 * @throws Exception
	 * @since    1.6
	 */
	public function publish()
	{
		// Initialise variables.
		$app = Factory::getApplication();
		$params  = $app->getParams('com_jticketing');
		$adminApproval = $params->get('event_approval');
		$cid = $app->getInput()->get('cid', array(), 'post', 'array');

		// Checking if the user can remove object
		$user = Factory::getUser();

		if ($user->authorise('core.edit.state', 'com_jticketing') && $adminApproval == 0)
		{
			$model = $this->getModel('eventform');
			$task = $app->getInput()->get('task');
			$state = ($task == 'publish') ? 1 : 0;

			// Attempt to save the data.

			foreach ($cid as $id)
			{
				$return = $model->publish($id, $state);

				// Check for errors.
				if ($return === false)
				{
					$this->setMessage($model->getError(), 'warning');
				}
			}

			// Redirect to the list screen.
			if ($state)
			{
				$this->setMessage(Text::sprintf(Text::_('COM_JTICKETING_N_ITEMS_PUBLISHED'),count($cid)));
			}
			else 
			{
				$this->setMessage(Text::sprintf(Text::_('COM_JTICKETING_N_ITEMS_UNPUBLISHED'),count($cid)));
			}

			$menu = $app->getMenu();
			$items = $menu->getItems('link', 'index.php?option=com_jticketing&view=events&layout=my');

			if (isset($items[0]))
			{
				$itemId = $items[0]->id;
			}
			else 
			{
				$itemId   = JT::utilities()->getItemId('index.php?option=com_jticketing&view=events&layout=my');
			}

			// Clear the profile id from the session.
			$app->setUserState('com_jticketing.edit.eventform.id', null);

			// Flush the data from the session.
			$app->setUserState('com_jticketing.edit.eventform.data', null);

			// Redirect to the edit screen.
			$this->setRedirect(
				Route::_(
					'index.php?option=com_jticketing&view=events&layout=my&Itemid=' . $itemId, false
				)
			);
		}
		else
		{
			throw new Exception(500);
		}
	}

	/**
	 * Method to save a user's profile data.
	 *
	 * @return    void
	 *
	 * @throws Exception
	 * @since    1.6
	 */
	public function unpublish()
	{
		$this->publish();
	}

	/**
	 * Method to delete the model state.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function delete()
	{
		$app        = Factory::getApplication();
		$input		= Factory::getApplication()->getInput();
		$cid 		= $input->post->get('cid', array(), 'array');
		ArrayHelper::toInteger($cid);
		$count                = array();
		$count['valid']       = 0;
		$count['invalid']     = 0;
		$orderCount           = 0;
		$JTicketingModelEvent = JT::model('Eventform');

		foreach ($cid as $id)
		{
			$eventData = $JTicketingModelEvent->getItem($id);
			$currentDate = Factory::getDate();
			$eventObj = JT::event($id);

			$integrationId = JT::event($id)->integrationId;
			$orders = JT::model('orders');
			$orders = $orders->getOrders(
				array('event_details_id' => $integrationId)
			);

			foreach ($orders as $key => $order)
			{
				$orderDetails = JT::order($order->id);

				if (!empty($orderDetails->id))
				{
					if ($orderDetails->getStatus() === COM_JTICKETING_CONSTANT_ORDER_STATUS_COMPLETED)
					{
						$orderCount++;
					}
				}
			}

			if ($orderCount == 0)
			{
				$confirm = $JTicketingModelEvent->delete($id);

				if ($confirm == "true")
				{
					$count['valid'] = $count['valid'] + 1;
				}
			}
			else
			{
				$count['invalid'] = $count['invalid'] + 1;
			}
		}

		if ($count['valid'] != 0)
		{
			if ($count['valid'] > 1)
			{
				$languageConstantValid = 'COM_JTICKETING_N_ITEMS_DELETED_EVENTS';
			}
			else
			{
				$languageConstantValid = 'COM_JTICKETING_N_ITEMS_DELETED_1';
			}

			$app->enqueueMessage($count['valid'] . Text::_($languageConstantValid));
		}

		if ($count['invalid'] != 0)
		{
			if ($count['invalid'] > 1)
			{
				$languageConstantInvalid = 'COM_JTICKETING_DELETED_ERROR_MULTIPLE';
			}
			else
			{
				$languageConstantInvalid = 'COM_JTICKETING_DELETED_ERROR_SINGLE';
			}

			$app->enqueueMessage($count['invalid'] . Text::_($languageConstantInvalid), 'error');
		}

		$menu = $app->getMenu();
		$items = $menu->getItems('link', 'index.php?option=com_jticketing&view=events&layout=my');

		if (isset($items[0]))
		{
			$itemId = $items[0]->id;
		}
		else 
		{
			$itemId   = JT::utilities()->getItemId('index.php?option=com_jticketing&view=events&layout=my');
		}

		$this->setRedirect(
			Route::_(
				'index.php?option=com_jticketing&view=events&layout=my&Itemid=' . $itemId, false
			)
		);
	}
}
