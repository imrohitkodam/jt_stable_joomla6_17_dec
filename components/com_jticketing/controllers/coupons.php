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

use Joomla\CMS\MVC\Controller\AdminController as BaseAdminController;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

/**
 * JTicketing Coupons controller
 *
 * @since  2.4.0
 */
class JticketingControllerCoupons extends BaseAdminController
{
	/**
	 * Proxy for getModel.
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional
	 * @param   array   $config  Configuration array for model. Optional
	 *
	 * @return object|boolean The model
	 *
	 * @since	2.4.0
	 */
	public function &getModel($name = 'Couponform', $prefix = 'JticketingModel', $config = array())
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));

		return $model;
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
		$cid = $app->getInput()->get('cid', array(), 'post', 'array');

		// Checking if the user can remove object
		$user = Factory::getUser();

		if ($user->authorise('coupon.edit.own', 'com_jticketing') || $user->authorise('core.edit.state', 'com_jticketing'))
		{
			$model = $this->getModel('Coupons');
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
			$itemId   = JT::utilities()->getItemId('index.php?option=com_jticketing&view=coupons');

			// Clear the profile id from the session.
			$app->setUserState('com_jticketing.edit.couponform.id', null);

			// Flush the data from the session.
			$app->setUserState('com_jticketing.edit.couponform.data', null);

			// Redirect to the edit screen.
			$this->setRedirect(
				Route::_(
					'index.php?option=com_jticketing&view=coupons&Itemid=' . $itemId . '&id=' . $id, false
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
	public function delete()
	{
		// Initialise variables.
		$app = Factory::getApplication();
		$cid = $app->getInput()->get('cid', array(), 'post', 'array');

		// Checking if the user can remove object
		$user = Factory::getUser();
		$itemId   = JT::utilities()->getItemId('index.php?option=com_jticketing&view=coupons');

		if ($user->authorise('coupons.delete', 'com_jticketing'))
		{
			$model = $this->getModel('Coupons');

			foreach ($cid as $id)
			{
				$return = $model->delete($id, $state);

				// Check for errors.
				if ($return === false)
				{
					$this->setMessage($model->getError(), 'warning');
				}
			}

			// Clear the profile id from the session.
			$app->setUserState('com_jticketing.edit.couponform.id', null);

			// Flush the data from the session.
			$app->setUserState('com_jticketing.edit.couponform.data', null);

			$this->setMessage(Text::sprintf(Text::_('COM_JTICKETING_N_ITEMS_DELETED'),count($cid)));

			// Redirect to the edit screen.
			$this->setRedirect(
				Route::_(
					'index.php?option=com_jticketing&view=coupons&Itemid=' . $itemId . '&id=' . $id, false
				)
			);
		}
		else
		{
			$this->setMessage(Text::sprintf(Text::_('COM_JTICKETING_COUPONS_DELETE_AUTHORISATION_FAILED')));

			$this->setRedirect(
				Route::_(
					'index.php?option=com_jticketing&view=coupons&Itemid=' . $itemId . '&id=' . $id, false
				)
			);
		}
	}
}
