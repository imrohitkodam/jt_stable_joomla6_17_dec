<?php
/**
 * @package     TJVendors
 * @subpackage  com_tjvendors
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Response\JsonResponse;
use Joomla\CMS\Router\Route;

HTMLHelper::addIncludePath(JPATH_COMPONENT . '/helpers/tjvendors.php');

/**
 * Vendor controller class.
 *
 * @since  1.6
 */
class TjvendorsControllerVendor extends FormController
{
	/**
	 * Constructor
	 *
	 * @throws Exception
	 */
	public function __construct()
	{
		$this->view_list = 'vendors';

		parent::__construct();
	}

	/**
	 * Gets the URL arguments to append to an item redirect.
	 *
	 * @param   integer  $recordId  The primary key id for the item.
	 * @param   string   $urlVar    The name of the URL variable for the id.
	 *
	 * @return  string  The arguments to append to the redirect URL.
	 *
	 * @since   1.6
	 */
	protected function getRedirectToItemAppend($recordId = null, $urlVar = 'vendor_id')
	{
		$input  = Factory::getApplication()->getInput();
		$client = $input->get('client', '', 'STRING');
		$append = parent::getRedirectToItemAppend($recordId, $urlVar);
		$append .= '&client=' . $client;

		return $append;
	}

	/**
	 * Check for duplicate users
	 *
	 * @return null
	 *
	 * @since   1.6
	 */
	public function checkDuplicateUser()
	{
		$input   = Factory::getApplication()->getInput()->post;
		$user    = $input->get('user', '', 'STRING');
		$model   = $this->getModel('vendor');
		$results = $model->checkDuplicateUser($user);
		echo json_encode($results);
		Factory::getApplication()->close();
	}

	/**
	 * Vendor approval
	 *
	 * @return null
	 *
	 * @since   1.1
	 */
	public function vendorApprove()
	{
		$input                 = Factory::getApplication()->getInput();
		$vendor_id             = $input->post->get('vendor_id', '', 'INTEGER');
		$vendorApprove         = $input->post->get('vendorApprove', '', 'INTEGER');
		$client                = $input->get('client', '', 'STRING');
		$model                 = $this->getModel('vendor', '', array());
		$data['vendor_id']     = $vendor_id;
		$data['vendor_client'] = $client;
		$data['approved']      = $vendorApprove;
		$result                = $model->save($data);
		echo new JsonResponse($result, Text::_('COM_TJVENDORS_VENDOR_APPROVAL_ERROR'), true);
	}

	/**
	 * Build payment gateway fields
	 *
	 * @return null
	 *
	 * @since   1.6
	 */
	public function generateGatewayFields()
	{
		$input           = Factory::getApplication()->getInput()->post;
		$payment_gateway = $input->get('payment_gateway', '', 'STRING');
		$parentTag       = $input->get('parent_tag', '', 'STRING');
		$vendor_id       = $input->get('vendor_id', '', 'INTEGER');
		$model           = $this->getModel('vendor', '', array());
		$results         = $model->generateGatewayFields($payment_gateway, $parentTag);
		echo json_encode($results);
		Factory::getApplication()->close();
	}

	/**
	 * Gets the URL arguments to append to a list redirect.
	 *
	 * @return  string  The arguments to append to the redirect URL.
	 *
	 * @since   1.6
	 */
	protected function getRedirectToListAppend()
	{
		$input  = Factory::getApplication()->getInput()->post;
		$client = $input->get('client', '', 'STRING');
		$append = parent::getRedirectToListAppend();
		$append .= '&client=' . $client;

		return $append;
	}

	/**
	 * Save vendor data
	 *
	 * @param   integer  $key     key.
	 *
	 * @param   integer  $urlVar  url
	 *
	 * @return  string  The arguments to append to the redirect URL.
	 *
	 * @since   1.6
	 */
	public function save($key = null, $urlVar = null)
	{
		// Initialise variables.
		$app    = Factory::getApplication();
		$model  = $this->getModel('Vendor', 'TjvendorsModel', array());
		$input  = $app->getInput();
		$client = $input->get('client', '', 'STRING');

		// Get the user data.
		$data = $app->getInput()->get('jform', array(), 'array');

		// Validate the posted data.
		$form = $model->getForm();

		if (!$form)
		{
			$app->enqueueMessage($model->getError(), 'error');

			return false;
		}

		// Validate the posted data.
		$validate = $model->validate($form, $data);

		// Check for errors.
		if ($validate === false)
		{
			// Get the validation messages.
			$errors = $model->getErrors();

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
			// Redirect back to the edit screen.
			$id = (int) $app->getUserState('com_tjvendors.edit.vendor.id');
			$app->setUserState('com_tjvendors.edit.vendor.data', $data);

			$this->setRedirect(Route::_('index.php?option=com_tjvendors&view=vendor&layout=edit&client=' . $client . '&vendor_id=' . $id, false));

			return false;
		}

		$return = $model->save($data);

		// Check for errors.
		if ($return === false)
		{
			$app->setUserState('com_tjvendors.edit.vendor.data', $data);

			// Redirect back to the edit screen.
			$id = (int) $app->getUserState('com_tjvendors.edit.vendor.id');
			$this->setMessage(Text::sprintf('COM_TJVENDORS_VENDOR_ERROR_MSG_SAVE', $model->getError()), 'warning');
			$this->setRedirect(Route::_('index.php?option=com_tjvendors&view=vendor&layout=edit&client=' . $client . '&vendor_id=' . $id, false));

			return false;
		}

		$msg = Text::_('COM_TJVENDORS_MSG_SUCCESS_SAVE_VENDOR');
		$id = $input->get('vendor_id');

		if (empty($id))
		{
			$id = $return;
		}

		$task = $input->get('task');

		if ($task == 'apply')
		{
			$redirect = Route::_('index.php?option=com_tjvendors&view=vendor&layout=edit&client=' . $client . '&vendor_id=' . $id, false);
			$app->enqueueMessage($msg, 'success');
			$app->redirect($redirect);
		}

		if ($task == 'save2new')
		{
			$redirect = Route::_('index.php?option=com_tjvendors&view=vendor&layout=edit&client=' . $client, false);
			$app->enqueueMessage($msg, 'success');
			$app->redirect($redirect);
		}

		// Clear the profile id from the session.
		$app->setUserState('com_tjvendors.edit.vendor.id', null);

		// Check in the profile.
		if ($return)
		{
			$model->checkin($return);
		}

		// Redirect to the list screen.
		$redirect = Route::_('index.php?option=com_tjvendors&view=vendors&client=' . $client, false);
		$app->enqueueMessage($msg, 'success');
		$app->redirect($redirect);

		// Flush the data from the session.
		$app->setUserState('com_tjvendors.edit.vendor.data', null);
	}
}
