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
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Response\JsonResponse;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Uri\Uri;

JLoader::register('JticketingCommonHelper', JPATH_SITE . '/components/com_jticketing/helpers/common.php');

/**
 * Venue controller class.
 *
 * @since  1.6
 */
class JticketingControllerPDFTemplate extends FormController
{
	/**
	 * Constructor
	 *
	 * @throws Exception
	 */
	public function __construct()
	{
		$this->view_list = 'pdftemplate';
		parent::__construct();
	}

	/**
	 * Method to Publish the element.
	 *
	 * @return   void
	 *
	 * @since    1.6
	 *
	 */
	public function publish()
	{
		// Initialise variables.
		$app = Factory::getApplication();

		// Checking if the user can remove object
		$user = Factory::getUser();

		if ($user->authorise('core.edit', 'com_jticketing') || $user->authorise('core.edit.state', 'com_jticketing'))
		{
			$model = $this->getModel('pdftemplate', 'JticketingModel');

			// Get the user data.
			$id = $app->getInput()->getInt('id');
			$state = $app->getInput()->getInt('state');

			// Attempt to save the data.
			$return = $model->publish($id, $state);

			// Check for errors.
			if ($return === false)
			{
				$this->setMessage(Text::sprintf('Save failed: %s', $model->getError()), 'warning');
			}

			// Clear the profile id from the session.
			$app->setUserState('com_jticketing.edit.pdftemplate.id', null);

			// Flush the data from the session.
			$app->setUserState('com_jticketing.edit.pdftemplate.data', null);

			// Redirect to the list screen.
			if ($state == '0')
			{
				$this->setMessage(Text::_('COM_JTICKETING_PDF_TEMPLATE_UNPUBLISHED'));
			}
			else
			{
				$this->setMessage(Text::_('COM_JTICKETING_PDF_TEMPLATE_PUBLISHED'));
			}

			$this->setRedirect(Route::_('index.php?option=com_jticketing&view=pdftemplates', false));
		}
		else
		{
			throw new Exception(500);
		}
	}

	/**
	 * Method to save a user's profile data.
	 *
	 * @param   string  $key     TO ADD
	 * @param   string  $urlVar  TO ADD
	 *
	 * @return  boolean|void  Incase of error boolean and in case of success void
	 *
	 * @since    1.6
	 */
	public function save($key = null, $urlVar = null)
	{
		// Check for request forgeries.
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		// Initialise variables.
		$app   = Factory::getApplication();
		$model = $this->getModel('pdftemplate', 'JticketingModel');

		// Get the user data.
		$data = Factory::getApplication()->getInput()->get('jform', array(), 'array');

		$form = $model->getForm();

		if (!$form)
		{
			$app->enqueueMessage($model->getError(), 'error');

			return false;
		}

		// Validate the posted data.
		if (!empty($form))
		{
			$data = $model->validate($form, $data);
		}

		// Get the validation messages.
		$errors = $model->getErrors();

		// Check for errors.
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

			/* Save the data in the session.
			Tweak.*/
			$app->setUserState('com_jticketing.edit.pdftemplate.data', $all_jform_data);

			/* Tweak *important.*/
			$app->setUserState('com_jticketing.edit.pdftemplate.id', $all_jform_data['id']);

			// Redirect back to the edit screen.
			$id = (int) $app->getUserState('com_jticketing.edit.pdftemplate.id');
			$this->setMessage(Text::sprintf('COM_JTICKETING_PDF_TEMPLATE_ERROR_MSG_SAVE', $model->getError()), 'warning');

			if ($app->isClient("administrator"))
			{
				$this->setRedirect(Route::_('index.php?option=com_jticketing&view=pdftemplate&layout=edit&id=' . $id, false));
			}

			if ($app->isClient("site"))
			{
				$this->setRedirect(
				Route::_('index.php?option=com_jticketing&view=pdftemplate&layout=default' . $this->getRedirectToItemAppend($id, $urlVar), false)
				);
			}

			return false;
		}

		$msg      = Text::_('COM_JTICKETING_MSG_SUCCESS_SAVE_PDF_TEMPLATE');
		$input = Factory::getApplication()->getInput();
		$id = $input->get('id');
		$return = $model->save($data);

		if (empty($id))
		{
			$id = $return;
		}

		$task = $input->get('task');

		if ($task == 'apply')
		{
			$redirect = Route::_('index.php?option=com_jticketing&view=pdftemplate&layout=edit&id=' . $id, false);
			$app->enqueueMessage($msg);
			$app->redirect($redirect);
		}

		if ($task == 'save2new')
		{
			$redirect = Route::_('index.php?option=com_jticketing&view=pdftemplate&layout=edit', false);
			$app->enqueueMessage($msg);
			$app->redirect($redirect);
		}

		// Clear the profile id from the session.
		$app->setUserState('com_jticketing.edit.pdftemplate.id', null);

		// Check in the profile.
		if ($return)
		{
			$model->checkin($return);
		}

		// Redirect to the list screen.
		if ($app->isClient("administrator"))
		{
			$redirect = Route::_('index.php?option=com_jticketing&view=pdftemplates', false);
		}
		else
		{
			$redirect = Route::_('index.php?option=com_jticketing&view=pdftemplates' . $this->getRedirectToItemAppend($id, $urlVar), false);
		}

		$app->enqueueMessage($msg);
		$app->redirect($redirect);

		// Flush the data from the session.
		$app->setUserState('com_jticketing.edit.pdftemplate.data', null);
	}

	/**
	 * Method to add a new venue.
	 *
	 * @return  mixed  True if the record can be added, an error object if not.
	 *
	 * @since   1.6
	 */
	public function add()
	{
		if (!parent::add())
		{
			// Redirect to the return page.
			$this->setRedirect($this->getReturnPage());

			return;
		}

		// Redirect to the edit screen.
		$this->setRedirect(
			Route::_(
				'index.php?option=' . $this->option . '&view=' . $this->view_item 
				. $this->getRedirectToItemAppend(), false
			)
		);

		return true;
	}

	/**
	 * Cancel description
	 *
	 * @return description
	 */
	public function cancel($key = null)
	{
		if (!parent::cancel($key))
		{
			// Redirect to the return page.
			$this->setRedirect($this->getReturnPage());

			return;
		}

		// Redirect to the edit screen.
		$this->setRedirect(
			Route::_(
				'index.php?option=' . $this->option . '&view=pdftemplates' 
				. $this->getRedirectToItemAppend(), false
			)
		);

		return true;
	}

	/**
	 * Get the return URL.
	 *
	 * If a "return" variable has been passed in the request
	 *
	 * @return  string	The return URL.
	 *
	 * @since   1.6
	 */
	protected function getReturnPage()
	{
		$return = $this->input->get('return', null, 'base64');

		if (empty($return) || !Uri::isInternal(base64_decode($return)))
		{
			return Uri::base();
		}
		else
		{
			return base64_decode($return);
		}
	}

	/**
	 * Gets the URL arguments to append to an item redirect.
	 *
	 * @param   integer  $recordId  The primary key id for the item.
	 * @param   string   $urlVar    The name of the URL variable for the id.
	 *
	 * @return  string	The arguments to append to the redirect URL.
	 *
	 * @since   1.6
	 */
	protected function getRedirectToItemAppend($recordId = null, $urlVar = 'a_id')
	{
		// Need to override the parent method completely.
		$tmpl   = $this->input->get('tmpl');
		$app = Factory::getApplication();

		$append = '';

		// Setup redirect info.
		if ($tmpl)
		{
			$append .= '&tmpl=' . $tmpl;
		}

		// TODO This is a bandaid, not a long term solution.
		/**
		 * if ($layout)
		 * {
		 *	$append .= '&layout=' . $layout;
		 * }
		 */

		if ($app->isClient("administrator"))
		{
			$append .= '&layout=edit';
			$itemId = $this->input->getInt('Itemid');
		}
		else 
		{
			$menu       = $app->getMenu();
			$menuItem   = $menu->getItems('link', 'index.php?option=com_jticketing&view=pdftemplates', true);

			$itemId = 0;

			if (!empty($menuItem->id))
			{
				$itemId = $menuItem->id;
			}
		}

		if ($recordId)
		{
			$append .= '&' . $urlVar . '=' . $recordId;
		}

		if ($itemId)
		{
			$append .= '&Itemid=' . $itemId;
		}

		return $append;
	}
}
