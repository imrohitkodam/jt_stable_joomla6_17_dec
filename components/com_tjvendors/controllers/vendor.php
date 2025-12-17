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
defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;

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
		$this->input = Factory::getApplication()->getInput();

		$this->vendor_client = $this->input->get('client', '', 'STRING');

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
		$append = parent::getRedirectToItemAppend($recordId);
		$append .= '&client=' . $this->vendor_client;

		return $append;
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
		$append = parent::getRedirectToListAppend();
		$append .= '&client=' . $this->vendor_client;

		return $append;
	}

	/**
	 * Method to save a vendor profile data.
	 *
	 * @param   string  $key     The name of the primary key of the URL variable.
	 * @param   string  $urlVar  The name of the URL variable if different from the primary key (sometimes required to avoid router collisions).
	 *
	 * @return	void
	 *
	 * @since	1.6
	 */
	public function save($key = null, $urlVar = null)
	{
		// Check for request forgeries.
		Session::checkToken() or Factory::getApplication()->close();
		$params = ComponentHelper::getParams('com_tjvendors');
		$vendorApproval = $params->get('vendor_approval');

		// Initialise variables.
		$app = Factory::getApplication();
		$model = $this->getModel('Vendor', 'TjvendorsModel');

		// Get the user data.
		$data = Factory::getApplication()->getInput()->get('jform', array(), 'array');
		$data['vendor_client'] = $app->getInput()->get('client', '', 'STRING');

		$data['user_id'] = Factory::getUser()->id;

		// Validate the posted data.
		$form = $model->getForm();

		if (! $form)
		{
			throw new \Exception($model->getError(), 500);

			return false;
		}

		// Validate the posted data.
		$validate  = $model->validate($form, $data);

		if ($vendorApproval && empty($data['vendor_id']))
		{
			$data['approved'] = 0;
			$data['state'] = 0;
		}
		else
		{
			$data['approved'] = 1;
			$data['state'] = 1;
		}

		// Check for errors
		if ($validate === false)
		{
			// Get the validation messages.
			$errors = $model->getErrors();

			// Push up to three validation messages out to the user.
			for ($i = 0, $n = count($errors); $i < $n; $i ++)
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
			// Save the data in the session.
			$app->setUserState('com_tjvendors.edit.vendor.data', $data);

			// Redirect back to the edit screen.
			$id = $app->getInput()->get('vendor_id', '', 'INTEGER');
			$client = $app->getInput()->get('client', '', 'STRING');

			$this->setRedirect(Route::_('index.php?option=com_tjvendors&view=vendor&layout=editinfo&vendor_id=' . $id . '&client=' . $client, false));

			return false;
		}

		// Attempt to save the data.
		$return = $model->save($data);

		// Check for errors.
		if ($return === false)
		{
			// Save the data in the session.
			$app->setUserState('com_tjvendors.edit.vendor.data', $all_jform_data);

			// Redirect back to the edit screen.
			$client = $app->getInput()->get('client', '', 'STRING');

			$id = $app->getUserState('com_tjvendors.edit.vendor.data.vendor_id');
			$this->setMessage(Text::sprintf('Save failed', $model->getError()), 'warning');
			$dynamicLink = '&client=' . $data['vendor_client'] . '&vendor_id=' . $id;

			$this->setRedirect(
					Route::_(
					'index.php?option=com_tjvendors&view=vendor&layout=edit' . $dynamicLink, false
					)
					);

			return false;
		}

		$user_id = Factory::getUser()->id;

		// Get a db connection.
		$db = Factory::getDbo();

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName('vendor_id'));
		$query->from($db->quoteName('#__tjvendors_vendors'));
		$query->where($db->quoteName('user_id') . ' = ' . $user_id);
		$db->setQuery($query);
		$vendor_id = $db->loadColumn()[0] ?? null;
		$input = Factory::getApplication()->getInput();

		$client = $input->get('client', '', 'STRING');

		$itemId= 0;
		if ($client)
		{
			$menu = $app->getMenu();
			$items = $menu->getItems('link', 'index.php?option=com_tjvendors&view=vendor&client=' . $client);
	
			if (isset($items[0]))
			{
				$itemId = $items[0]->id;
			}
		}

		// Redirect to the list screen.
		$this->setMessage(Text::_('COM_TJVENDORS_MSG_SUCCESS_SAVE_VENDOR'));
		$this->setRedirect(
				Route::_(
				'index.php?option=com_tjvendors&view=vendor&layout=default&vendor_id=' . $vendor_id . '&client=' . $client . '&Itemid=' . $itemId, false
				)
				);

		// Flush the data from the session.
		$app->setUserState('com_tjvendors.edit.vendor.data', null);
	}

	/**
	 * Cancel description
	 *
	 * @param   integer  $key  The key
	 *
	 * @return description
	 */
	public function cancel($key=null)
	{
		$app = Factory::getApplication();
		$input = $app->getInput();
		$data = $input->get('jform', array(), 'array');
		$client = $input->get('client', '', 'STRING');

		$itemId= 0;
		if ($client)
		{
			$menu = $app->getMenu();
			$items = $menu->getItems('link', 'index.php?option=com_tjvendors&view=vendor&client=' . $client);
	
			if (isset($items[0]))
			{
				$itemId = $items[0]->id;
			}
		}

		if (!empty($data['vendor_id']))
		{
			$this->setRedirect(
			Route::_('index.php?option=com_tjvendors&view=vendor&layout=default&vendor_id=' . $data['vendor_id'] . '&client=' . $client . '&Itemid=' . $itemId, false)
			);
		}
		else
		{
			$app->redirect("index.php");
		}
	}

	/**
	 * Build a form
	 *
	 * @return null
	 *
	 * @since   1.6
	 */
	public function generateGatewayFields()
	{
		$input  = Factory::getApplication()->getInput()->post;
		$payment_gateway = $input->get('payment_gateway', '', 'STRING');
		$parentTag = $input->get('parent_tag', '', 'STRING');
		$vendor_id = $input->get('vendor_id', '', 'INTEGER');
		$model = $this->getModel('vendor');
		$results = $model->generateGatewayFields($payment_gateway, $parentTag);
		echo json_encode($results);
		Factory::getApplication()->close();
	}
}
