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
class JticketingControllerVenueForm extends FormController
{
	/**
	 * Constructor
	 *
	 * @throws Exception
	 */
	public function __construct()
	{
		$this->view_list = 'venues';
		parent::__construct();
	}

	/**
	 * Method to load the online events params by using the xml form stored under the plugin itself
	 *
	 * @throws Exception
	 *
	 * @return string
	 */
	public function getelementparams()
	{
		$app = Factory::getApplication();

		$params = array();
		$element  = $app->getInput()->getString('element');
		$venueId  = $app->getInput()->getInt('venue_id');

		if (empty($element))
		{
			echo new JsonResponse(null, null, true);
			$app->close();
		}

		if ($venueId)
		{
			$venue = JT::venue($venueId);
			$params['plugin'] = json_decode($venue->getParams());
		}

		$plugRes = $this->buildForm($params, $element);

		if ($plugRes)
		{
			echo new JsonResponse($plugRes);
			$app->close();
		}

		echo new JsonResponse(null, $this->getError(), true);
		$app->close();
	}

	/**
	 * Method to build html from plugin xml
	 *
	 * @param   array   $params   Array of essensial information
	 * @param   string  $element  provider name eg. zoom
	 *
	 * @return string  The html of the form
	 */
	public function buildForm($params, $element)
	{
		$app = Factory::getApplication();
		$lang = Factory::getLanguage();

		$form = null;
		$formPath = JPATH_SITE . '/plugins/tjevents/' . $element . '/' . $element . '/form/' . $element . '.xml';
		$test = $element . '_' . 'plugin';

		$lang->load('plg_tjevents_' . $element, JPATH_ADMINISTRATOR);

		$form = Form::getInstance($test, $formPath, array('control' => 'jform'));
		$form->bind($params);
		$fieldSet = $form->getFieldset('plugin');

		if ($app->isClient('administrator')) {
			$html           = $form->renderFieldset('plugin');
			$html = str_replace('class="required radio"', 'class="radio"', $html);

			return $html;
		} else {
			$html = array();
			foreach ($fieldSet as $field)
			{
				$html[] = '<div class="form-group row"> ' .
					'<div class="form-label col-12 col-md-4">' . $field->label .'</div>' .
					'<div class="col-12 col-md-8">' . $field->input .'</div>' .
				'</div>';
			}

			return implode('', $html);
		}
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
			$model = $this->getModel('venue', 'JticketingModel');

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
			$app->setUserState('com_jticketing.edit.venue.id', null);

			// Flush the data from the session.
			$app->setUserState('com_jticketing.edit.venue.data', null);

			// Redirect to the list screen.
			if ($state == '0')
			{
				$this->setMessage(Text::_('COM_JTICKETING_VENUE_UNPUBLISHED'));
			}
			else
			{
				$this->setMessage(Text::_('COM_JTICKETING_VENUE_PUBLISHED'));
			}

			$this->setRedirect(Route::_('index.php?option=com_jticketing&view=venues', false));
		}
		else
		{
			throw new Exception(500);
		}
	}

	/**
	 * To Fetch state list from Db
	 *
	 * @return  list of state
	 *
	 * @since  1.0.0
	 */
	public function getRegionListFromCountryID()
	{
		$TjGeoHelper = JPATH_ROOT . '/components/com_tjfields/helpers/geo.php';

		if (!class_exists('TjGeoHelper'))
		{
			JLoader::register('TjGeoHelper', $TjGeoHelper);
			JLoader::load('TjGeoHelper');
		}

		$tjGeoHelper = new TjGeoHelper;

		$jinput = Factory::getApplication()->getInput();
		$country = $jinput->get('country', '', 'STRING');
		echo json_encode($tjGeoHelper->getRegionListFromCountryID($country));

		jexit();
	}

	/**
	 * Method to Get Location.
	 *
	 * @return json
	 *
	 * @since   1.0
	 */
	public function getLocation()
	{
		$post = Factory::getApplication()->getInput()->post;

		$model = $this->getModel('venue');
		$result = $model->getCurrentLocation($post);

		echo json_encode($result);

		jexit();
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
		$model = $this->getModel('venue', 'JticketingModel');

		// Get the user data.
		$data = Factory::getApplication()->getInput()->get('jform', array(), 'array');

		if (empty($data['created_by']))
		{
			$data['created_by'] = Factory::getUser()->id;
		}

		$data['userName'] = Factory::getUser($data['created_by'])->name;

		// Jform tweaing starts.
		// JForm tweak - Save all jform array data in a new array for later reference.
		$all_jform_data = $data;

		// Jform tweak - Get all posted data.
		$post = Factory::getApplication()->getInput()->post;

		$data['userName'] = Factory::getUser($data['created_by'])->name;

		$form = $model->getForm();

		if ($data['online'] == 1)
		{
			$pluginFormPath = JPATH_SITE . '/plugins/tjevents/' . $data['online_provider'] . '/' . $data['online_provider']
			. '/form/' . $data['online_provider'] . '.xml';
			$form->loadFile($pluginFormPath, true, '/form/*');
		}
		else
		{
			$data['online_provider'] = '';
			$data['params'] = '';
		}

		if (!$form)
		{
			$app->enqueueMessage($model->getError(), 'error');
			/* Save the data in the session.
			Tweak.*/
			$app->setUserState('com_jticketing.edit.venue.data', $all_jform_data);

			/* Tweak *important.*/
			$app->setUserState('com_jticketing.edit.venue.id', $all_jform_data['id']);

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
			$app->setUserState('com_jticketing.edit.venue.data', $all_jform_data);

			/* Tweak *important.*/
			$app->setUserState('com_jticketing.edit.venue.id', $all_jform_data['id']);

			// Redirect back to the edit screen.
			$id = (int) $app->getUserState('com_jticketing.edit.venue.id');
			$this->setMessage(Text::sprintf('COM_JTICKETING_VENUE_ERROR_MSG_SAVE', $model->getError()), 'warning');

			if ($app->isClient("administrator"))
			{
				$this->setRedirect(Route::_('index.php?option=com_jticketing&view=venue&layout=edit&id=' . $id, false));
			}

			if ($app->isClient("site"))
			{
				$this->setRedirect(
				Route::_('index.php?option=com_jticketing&view=venueform&layout=default' . $this->getRedirectToItemAppend($id, $urlVar), false)
				);
			}

			return false;
		}

		$com_params    = ComponentHelper::getParams('com_jticketing');

		$silentVendor = $com_params->get('silent_vendor');

		if ($silentVendor == 0)
		{
			$TjvendorFrontHelper = new TjvendorFrontHelper;
			$data['vendor_id']   = $TjvendorFrontHelper->checkVendor('', 'com_jticketing');
		}

		$return = $model->save($data);

		// Check for errors.
		if ($return === false)
		{
			/* Save the data in the session.
			$app->setUserState('com_jticketing.edit.event.data', $data);
			Tweak.*/
			$app->setUserState('com_jticketing.edit.venue.data', $all_jform_data);

			/* Tweak *important.*/
			$app->setUserState('com_jticketing.edit.venue.id', $all_jform_data['id']);

			// Redirect back to the edit screen.
			$id = (int) $app->getUserState('com_jticketing.edit.venue.id');
			$this->setMessage(Text::sprintf('COM_JTICKETING_VENUE_ERROR_MSG_SAVE', $model->getError()), 'warning');

			if ($app->isClient("administrator"))
			{
				$this->setRedirect(Route::_('index.php?option=com_jticketing&view=venue&layout=edit&id=' . $id, false));
			}

			if ($app->isClient("site"))
			{
				$this->setRedirect(Route::_('index.php?option=com_jticketing&view=venueform&layout=default&id=' . $id, false));
			}

			return false;
		}

		$msg      = Text::_('COM_JTICKETING_MSG_SUCCESS_SAVE_VENUE');
		$input = Factory::getApplication()->getInput();
		$id = $input->get('id');

		if (empty($id))
		{
			$id = $return;
		}

		$task = $input->get('task');

		if ($task == 'apply')
		{
			$redirect = Route::_('index.php?option=com_jticketing&view=venue&layout=edit&id=' . $id, false);
			$app->enqueueMessage($msg);
			$app->redirect($redirect);
		}

		if ($task == 'save2new')
		{
			$redirect = Route::_('index.php?option=com_jticketing&view=venue&layout=edit', false);
			$app->enqueueMessage($msg);
			$app->redirect($redirect);
		}

		// Clear the profile id from the session.
		$app->setUserState('com_jticketing.edit.venue.id', null);

		// Check in the profile.
		if ($return)
		{
			$model->checkin($return);
		}

		// Redirect to the list screen.
		

		if ($app->isClient("administrator"))
		{
			$redirect = Route::_('index.php?option=com_jticketing&view=venues', false);
		}

		if ($app->isClient("site"))
		{
			$itemId   = JT::utilities()->getItemId('index.php?option=com_jticketing&view=venues');
			$redirect = Route::_('index.php?option=com_jticketing&view=venues&Itemid=' . $itemId, false);
		}

		$app->enqueueMessage($msg);
		$app->redirect($redirect);

		// Flush the data from the session.
		$app->setUserState('com_jticketing.edit.venue.data', null);
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
		}

		if ($recordId)
		{
			$append .= '&' . $urlVar . '=' . $recordId;
		}

		$itemId = $this->input->getInt('Itemid');
		$return = $this->getReturnPage();
		$catId  = $this->input->getInt('catid');
		
		$itemId   = JT::utilities()->getItemId('index.php?option=com_jticketing&view=venues');
		$append .= '&Itemid=' . $itemId;

		if ($catId)
		{
			$append .= '&catid=' . $catId;
		}

		if ($return)
		{
			$append .= '&return=' . base64_encode($return);
		}

		return $append;
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

		// Redirect to the edit screen.
		$id = $cid[0];
		$this->setRedirect(
			Route::_(
				'index.php?option=' . $this->option . '&view=' . $this->view_item . '&id=' . $id
				. $this->getRedirectToItemAppend(), false
			)
		);

		return true;
	}
}
