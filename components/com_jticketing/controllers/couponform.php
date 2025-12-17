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
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Router\Route;

/**
 * JTicketing Couponform controller
 *
 * @since  2.4.0
 */
class JticketingControllerCouponForm extends FormController
{
	/**
	 * JTRouteHelper helper handle itemid
	 */
	protected $JTRouteHelper;

	/**
	 * Constructor
	 *
	 * @since  2.4.0
	 */
	public function __construct()
	{
		$this->view_list = 'coupons';
		parent::__construct();

		$this->JTRouteHelper = new JTRouteHelper;
	}

	/**
	 * Method to save coupon record.
	 *
	 * @param   string  $key     The name of the primary key of the URL variable.
	 * @param   string  $urlVar  The name of the URL variable if different from the primary key (sometimes required to avoid router collisions).
	 *
	 * @return  boolean  True if successful, false otherwise.
	 *
	 * @since   2.4.0
	 */
	public function save($key = 'id', $urlVar = 'id')
	{
		// Check for request forgeries.
		$this->checkToken();

		// Initialise variables.
		$app   = Factory::getApplication();
		$model = $this->getModel();

		$recordId = $this->input->getInt('id');

		// Get the coupon data.
		$data = $app->getInput()->get('jform', array(), 'array');

		// Validate the posted data.
		$form = $model->getForm($data, false);

		if (!$form)
		{
			throw new \Exception($model->getError(), 500);
		}

		$validData = $model->validate($form, $data);

		// Check for validation errors.
		if ($validData === false)
		{
			// Get the validation messages.
			$errors = $model->getErrors();

			if (!empty($errors))
			{
				// Push up to three validation messages out to the user.
				for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++)
				{
					if ($errors[$i] instanceof Exception)
					{
						$app->enqueueMessage($errors[$i]->getMessage(), 'warning');
					}
					else
					{
						$app->enqueueMessage($errors[$i], 'warning');
					}
				}
			}

			// Save the data in the session.
			$app->setUserState('com_jticketing.edit.couponform.data', $data);

			// Redirect back to the registration screen.
			$this->setRedirect(Route::_('index.php?option=com_jticketing&view=couponform' . $this->getRedirectToItemAppend($recordId, $urlVar), false));

			return false;
		}

		// Attempt to save the data.
		$return = $model->save($validData);

		// Check for errors.
		if (!$return)
		{
			// Save the data in the session.
			$app->setUserState('com_jticketing.couponform.data', $validData);

			// Redirect back to the edit screen.
			$this->setMessage($model->getError(), 'error');
			$this->setRedirect(Route::_('index.php?option=com_jticketing&view=couponform' . $this->getRedirectToItemAppend(), false));

			return false;
		}

		// Flush the data from the session.
		$app->setUserState('com_jticketing.couponform.data', null);

		// Redirect to the list screen.
		$this->setMessage(Text::_('COM_JTICKETING_MSG_SUCCESS_SAVE_COUPON'), 'success');
		$this->setRedirect($this->JTRouteHelper->JTRoute('index.php?option=com_jticketing&view=coupons'));

		return true;
	}

	/**
	 * Method to add a new record.
	 *
	 * @return  mixed  True if the record can be added, an error object if not.
	 *
	 * @since   1.6
	 */
	public function add()
	{
		$itemId   = JT::utilities()->getItemId('index.php?option=com_jticketing&view=coupons');

		// Redirect to the edit screen.
		$this->setRedirect(
			Route::_(
				'index.php?option=' . $this->option . '&view=' . $this->view_item . '&Itemid=' . $itemId, false
			)
		);

		return true;
	}

	/**
	 * Method to edit a record.
	 *
	 * @return  mixed  True if the record can be added, an error object if not.
	 *
	 * @since   1.6
	 */
	public function edit($key = 'id', $urlVar = 'id')
	{
		$input = Factory::getApplication()->getInput();
		$cid = $input->get('cid', array(), 'post', 'array');

		if (!count($cid))
		{
			return false;
		}

		$id = $cid[0];
		$itemId   = JT::utilities()->getItemId('index.php?option=com_jticketing&view=coupons');

		// Redirect to the edit screen.
		$this->setRedirect(
			Route::_(
				'index.php?option=' . $this->option . '&view=' . $this->view_item . '&Itemid=' . $itemId . '&id=' . $id, false
			)
		);

		return true;
	}

	/**
	 *Function to save
	 *
	 * @return  void
	 *
	 * @since  3.0
	 */
	public function cancel($key = null)
	{
		$itemId   = JT::utilities()->getItemId('index.php?option=com_jticketing&view=coupons');

		// Redirect to the edit screen.
		$this->setRedirect(
			Route::_(
				'index.php?option=' . $this->option . '&view=coupons&Itemid=' . $itemId, false
			)
		);
	}
}
