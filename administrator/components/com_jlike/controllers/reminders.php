<?php
/**
 * @package     JLike
 * @subpackage  com_jlike
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access.
defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Factory;
use Joomla\Utilities\ArrayHelper;

/**
 * Reminders list controller class.
 *
 * @since  1.6
 */
class JlikeControllerReminders extends AdminController
{
	/**
	 * Method to clone existing Reminders
	 *
	 * @return void
	 */
	public function duplicate()
	{
		// Check for request forgeries
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		$pks = $this->getInput()->post->get('cid', array(), 'array');
		ArrayHelper::toInteger($pks);

		try
		{
			if (empty($pks))
			{
				throw new Exception(Text::_('COM_JLIKE_NO_ELEMENT_SELECTED'));
			}

			$model = $this->getModel();
			$model->duplicate($pks);
			$this->setMessage(Text::plural('COM_JLIKE_ITEMS_SUCCESS_DUPLICATED', count($pks)));
		}
		catch (Exception $e)
		{
			$this->setMessage($e->getMessage(), 'error');
		}

		// Overrride the redirect Uri.
		$redirectUri = 'index.php?option=' . $this->option . '&view=' . $this->view_list . '&extension=' . $this->getInput()->get('extension', '', 'CMD');
		$this->setRedirect(Route::_($redirectUri, false), $this->message, $this->messageType);
	}

	/**
	 * Proxy for getModel.
	 *
	 * @param   string  $name    Optional. Model name
	 * @param   string  $prefix  Optional. Class prefix
	 * @param   array   $config  Optional. Configuration array for model
	 *
	 * @return  object	The Model
	 *
	 * @since    1.6
	 */
	public function getModel($name = 'reminder', $prefix = 'JlikeModel', $config = array())
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));

		return $model;
	}

	/**
	 * Method to save the submitted ordering values for records via AJAX.
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function saveOrderAjax()
	{
		// Get the input
		$input = Factory::getApplication()->getInput();
		$pks   = $input->post->get('cid', array(), 'array');
		$order = $input->post->get('order', array(), 'array');

		// Sanitize the input
		ArrayHelper::toInteger($pks);
		ArrayHelper::toInteger($order);

		// Get the model
		$model = $this->getModel();

		// Save the ordering
		$return = $model->saveorder($pks, $order);

		if ($return)
		{
			echo "1";
		}

		// Close the application
		Factory::getApplication()->close();
	}

	/**
	 * Check in of one or more records.
	 *
	 * Overrides AdminController::checkin to redirect to URL with extension.
	 *
	 * @return  boolean  True on success
	 *
	 * @since   3.6.0
	 */
	public function checkin()
	{
		// Process parent checkin method.
		$result = parent::checkin();

		// Overrride the redirect Uri.
		$redirectUri = 'index.php?option=' . $this->option . '&view=' . $this->view_list . '&extension=' . $this->getInput()->get('extension', '', 'CMD');
		$this->setRedirect(Route::_($redirectUri, false), $this->message, $this->messageType);

		return $result;
	}
}
