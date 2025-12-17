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
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Date\Date;



require_once JPATH_SITE . '/components/com_jticketing/controller.php';
require_once JPATH_ADMINISTRATOR . '/components/com_jticketing/models/venue.php';

$helperPath = JPATH_SITE . '/components/com_jticketing/helpers/time.php';

// Joomla 6: JLoader removed - use require_once
if (!class_exists('JticketingTimeHelper') && file_exists($helperPath))
{
	require_once $helperPath;
}

$frontHelperPath = JPATH_SITE . '/components/com_tjvendors/helpers/fronthelper.php';
if (file_exists($frontHelperPath))
{
	require_once $frontHelperPath;
}

/**
 * JTicketing EventForm controller
 *
 * @since  1.0.0
 */
class JticketingControllerEventForm extends FormController
{
	/**
	 * The router class object to build the SEF urls
	 *
	 * @var    object JTRouteHelper class object
	 * @since  1.0.0
	 */
	protected $JTRouteHelper;

	/**
	 * Constructor
	 *
	 * @since   1.0
	 */
	public function __construct()
	{
		parent::__construct();

		$this->JTRouteHelper = new JTRouteHelper;
	}

	/**
	 * Method to save a user's profile data.
	 *
	 * @param   string  $key     The name of the primary key of the URL variable.
	 * @param   string  $urlVar  The name of the URL variable if different from the primary key (sometimes required to avoid router collisions).
	 *
	 * @return  boolean|void  Incase of error boolean and in case of success void
	 *
	 * @since  1.6
	 */
	public function save($key = null, $urlVar = null)
	{
		$this->checkToken();
		$app   = Factory::getApplication();
		$db = Factory::getDbo();
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

		$myEvent = 'index.php?option=com_jticketing&view=events&layout=my&Itemid=' . $itemId;

		$user = Factory::getUser();

		if (!$user->id)
		{
			$msg      = Text::_('COM_JTICKETING_MESSAGE_LOGIN_FIRST');
			$this->setRedirect($myEvent, $msg);

			return false;
		}

		$recordId = $this->input->getInt('id');

		$app->setUserState('com_jticketing.edit.eventform.data', null);

		/* @var $model JticketingModelEventForm */
		$model = $this->getModel();

		$data = $this->input->get('jform', array(), 'array');
		$data['privacy_consent'] = $this->input->get('accept_privacy_term');

		$form = $model->getForm($data, false);

		if (!$form)
		{
			$this->setMessage($model->getError(), 'error');
			$this->setRedirect(Route::_('index.php?option=com_jticketing&view=eventform' . $this->getRedirectToItemAppend($recordId, $urlVar), false));

			return false;
		}

		$startDateTime = new Date($data['eventstart_date'] . ' ' . $data['start_time']);
		$endDateTime = new Date($data['eventend_date'] . ' ' . $data['end_time']);

		// Assign the formatted date back in SQL format
		$data['startdate'] = $startDateTime->toSql();  // Final combined start datetime
		$data['enddate'] = $endDateTime->toSql();
		$validData = $model->validate($form, $data);

		// Check for errors.
		if ($validData === false)
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

			$app->setUserState('com_jticketing.edit.eventform.data', $data);
			$this->setRedirect(Route::_('index.php?option=com_jticketing&view=eventform' . $this->getRedirectToItemAppend($recordId, $urlVar), false));

			return false;
		}

		Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jticketing/tables');
		$venueDetails = Table::getInstance('Venue', 'JticketingTable', array('dbo', $db));
		$venueDetails->load(array('id' => $data['venue']));

		if ($venueDetails->seats_capacity == 0) {
			$eventSeats = 0;
			foreach ($data['tickettypes'] as $key => $tickettype) {
				// check only limited seat available in event ticket types
				if(isset($tickettype['unlimited_seats']) && $tickettype['unlimited_seats'] != 0) {
					// Prevent blank screen here
					$app->setUserState('com_jticketing.edit.eventform.data', $data);
					$app->enqueueMessage(Text::_('COM_JTICKETING_VENUE_CAPACITY_CREATE_ERROR'), 'error');
					$this->setRedirect(Route::_('index.php?option=com_jticketing&view=eventform' . $this->getRedirectToItemAppend($recordId, $urlVar), false));

					return false;
				}

				$eventSeats += $tickettype['available'];
			}

			// check ticket seats count must be less than OR equal to venue capacity
			if ($eventSeats > $venueDetails->capacity_count) {
				$app->setUserState('com_jticketing.edit.eventform.data', $data);
				$app->enqueueMessage(Text::_('COM_JTICKETING_VENUE_CAPACITY_ERROR'), 'error');
				$this->setRedirect(Route::_('index.php?option=com_jticketing&view=eventform' . $this->getRedirectToItemAppend($recordId, $urlVar), false));

				return false;
			}
		}

		$jtConfig = JT::config();
		if ($jtConfig->get('entry_number_assignment', 0,'INT'))
		{
			// check start number of sequence in event level input only number and alphabetical present
			if ($data['start_number_for_event_level_sequence'] && !preg_match('/^[a-zA-Z0-9]+$/', $data['start_number_for_event_level_sequence']))
			{
				$app->setUserState('com_jticketing.edit.eventform.data', $data);
				$app->enqueueMessage(Text::_('COM_JTICKETING_INVALID_FIELD') . ': ' .Text::_('COM_JTICKETING_START_NUMBER_FOR_EVENT_LEVEL_SEQUENCE') , 'error');
				$this->setRedirect(Route::_('index.php?option=com_jticketing&view=eventform' . $this->getRedirectToItemAppend($recordId, $urlVar), false));

				return false;
			}

			foreach ($data['tickettypes'] as $key => $tickettype) 
			{
				// check ticket level sequence config enabled before validate sequence number
				if(isset($tickettype['allow_ticket_level_sequence']) && $tickettype['allow_ticket_level_sequence']) 
				{
					// check start number of sequence in ticket level input only number and alphabetical present
					if ($tickettype['start_number_for_sequence'] && !preg_match('/^[a-zA-Z0-9]+$/', $tickettype['start_number_for_sequence']))
					{
						$app->setUserState('com_jticketing.edit.eventform.data', $data);
						$app->enqueueMessage(Text::_('COM_JTICKETING_INVALID_FIELD') . ': ' .Text::_('COM_JTICKETING_START_NUMBER_FOR_SEQUENCE') , 'error');
						$this->setRedirect(Route::_('index.php?option=com_jticketing&view=eventform' . $this->getRedirectToItemAppend($recordId, $urlVar), false));

						return false;
					}
				}
			}
		}

		foreach ($data['tickettypes'] as $key => $tickettype) 
		{
			// check start number of maximum ticket per order input only number
			if ($tickettype['max_ticket_per_order'] && !preg_match('/^[0-9]+$/', $tickettype['max_ticket_per_order']))
			{
				$app->setUserState('com_jticketing.edit.event.data', $data);
				$app->enqueueMessage(Text::_('COM_JTICKETING_INVALID_FIELD') . ': ' .Text::_('COM_JTICKETING_TICKET_LEVEL_MAXIMUM_TICKET_PER_ORDER') , 'error');
				$this->setRedirect(Route::_('index.php?option=com_jticketing&view=event&layout=edit&id=' . $recordId, false));

				return false;
			}
		}

		$extraJformData = array_diff_key($data, $validData);
		$filesData      = $app->getInput()->files->get('jform', array(), 'ARRAY');
		unset($filesData['image']);
		unset($filesData['gallery_file']);
		unset($extraJformData['vendor_id']);
		unset($extraJformData['privacy_consent']);
		$extraJformData = array_merge_recursive($extraJformData, $filesData);
		$extraFieldData = array();
		$extraFieldData['content_id']  = $data['id'];
		$extraFieldData['client']      = 'com_jticketing.event';
		$extraFieldData['fieldsvalue'] = $extraJformData;
		$extraFieldData['created_by']  = $data['created_by'];

		$validData['privacy_consent'] = $data['privacy_consent'];

		// Check if form file is present.

		if (!empty($validData['id']))
		{
			if ($validData['venue'])
			{
				$validData['location'] = '';
			}
		}

		$validData['userName']      = Factory::getUser($validData['created_by'])->name;
		$validData['enrollment']    = (int) $validData['enrollment'];

		if ($validData['id'] != 0)
		{
			$tjvendorFrontHelper = new TjvendorFrontHelper;
			$validData['vendor_id']   = $tjvendorFrontHelper->checkVendor($validData['created_by'], 'com_jticketing');
		}

		$return = $model->save($validData);

		if (!empty($data['id']))
		{
			$model->saveExtraFields($extraFieldData);
		}

		// Check for errors.
		if ($return === false)
		{
			$app->enqueueMessage($model->getError(), 'error');
			$app->setUserState('com_jticketing.edit.eventform.data', $data);
			$this->setRedirect(Route::_('index.php?option=com_jticketing&view=eventform' . $this->getRedirectToItemAppend($recordId, $urlVar), false));

			return false;
		}

		// Clear the record id and data from the session.
		$this->releaseEditId('com_jticketing.edit.event', $recordId);
		$app->setUserState('com_jticketing.edit.eventform.data', null);

		$params = JT::config();
		$adminApproval = $params->get('event_approval');

		if ($adminApproval == 1 && $data['state'] == 0)
		{
			$this->setMessage(Text::_('COM_JTICKETING_MSG_SUCCESS_SAVE_EVENT_FOR_APPROVAL'), 'success');
		}
		else
		{
			$this->setMessage(Text::_('COM_JTICKETING_MSG_SUCCESS_SAVE_EVENT'), 'success');
		}

		$app->setUserState('com_jticketing.edit.eventform.data', null);
		$this->setRedirect(Route::_($myEvent, false));
	}

	/**
	 * Method to send event emails
	 *
	 * @return  void
	 *
	 * @since   5.1.0
	 */
	public function sendEventEmails()
	{
		JLoader::register('JticketingMailHelper', JPATH_SITE . '/components/com_jticketing/helpers/mail.php');
		$model = $this->getModel('EventForm', 'JticketingModel');
		$pendingEvents = $model->getPendingEmailEvents();

		if (!empty($pendingEvents))
		{
			foreach ($pendingEvents as $event)
			{
				$emails = $model->getEmailsOfPastAttendees($event['id'], $event['title'], $event['catid']);

				if (!empty($emails))
				{
					JticketingMailHelper::sendEmailNotificationToAttendees($event['id'], $emails);
				}
				$db = Factory::getDbo();
				$query = $db->getQuery(true)
					->update('#__jticketing_events')
					->set('email_sent = 1')
					->where('id = ' . (int) $event['id']);
				$db->setQuery($query);
				$db->execute();
			}
		}
	}

	/**
	 * cancel a ad fields
	 *
	 * @return  boolean|void  Incase of error boolean and in case of success void
	 *
	 * @since   3.0
	 */
	public function cancel($key = null)
	{
		$recordId = $this->input->getInt('id');
		$app   = Factory::getApplication();
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

		$redirect = 'index.php?option=com_jticketing&view=events&layout=my&Itemid=' . $itemId;

		$msg      = Text::_('COM_JTICKETING_MSG_CANCEL_CREATE_EVENT');

		if ($recordId)
		{
			$msg = Text::_('COM_JTICKETING_MSG_CANCEL_EDIT_EVENT');
		}

		$model = $this->getModel();

		// Attempt to check-in the current record.
		if ($recordId && $model->checkin($recordId) === false)
		{
			// Check-in failed, go back to the record and display a notice.
			$this->setError(Text::sprintf('JLIB_APPLICATION_ERROR_CHECKIN_FAILED', $model->getError()));
			$this->setMessage($this->getError(), 'error');

			$this->setRedirect($redirect);

			return false;
		}

		// Clean the session data and redirect.
		$this->releaseEditId("com_jticketing.edit.eventform", $recordId);
		Factory::getApplication()->setUserState('com_jticketing.edit.eventform.data', null);

		$this->setMessage($msg, 'message');

		$url = $redirect;

		// Check if there is a return value
		$return = $this->input->get('return', null, 'base64');

		if (!is_null($return) && Uri::isInternal(base64_decode($return)))
		{
			$url = base64_decode($return);
		}

		// Redirect to the list screen.
		$this->setRedirect(Route::_($url, false));

		return true;
	}

	/**
	 * remove a ad fields
	 *
	 * @return  boolean|void  Incase of error boolean and in case of success void
	 *
	 * @since   3.0
	 */
	public function remove()
	{
		// Check for request forgeries.
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		// Initialise variables.
		$app = Factory::getApplication();
		$model = $this->getModel('EventForm', 'JticketingModel');

		// Get the user data.
		$data = Factory::getApplication()->getInput()->get('jform', array(), 'array');

		// Validate the posted data.
		$form = $model->getForm();

		if (!$form)
		{
			JError::raiseError(500, $model->getError());

			return false;
		}

		// Validate the posted data.
		$data = $model->validate($form, $data);

		// Check for errors.
		if ($data === false)
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

			// Save the data in the session.
			$app->setUserState('com_jticketing.edit.eventform.data', $data);

			// Redirect back to the edit screen.
			$id = (int) $app->getUserState('com_jticketing.edit.eventform.id');
			$this->setRedirect(Route::_('index.php?option=com_jticketing&view=event&layout=edit&id=' . $id, false));

			return false;
		}

		// Attempt to save the data.
		$return = $model->delete($data);

		// Check for errors.
		if ($return === false)
		{
			// Save the data in the session.
			$app->setUserState('com_jticketing.edit.eventform.data', $data);

			// Redirect back to the edit screen.
			$id = (int) $app->getUserState('com_jticketing.edit.eventform.id');
			$this->setMessage(Text::sprintf('Delete failed', $model->getError()), 'warning');
			$this->setRedirect(Route::_('index.php?option=com_jticketing&view=event&layout=edit&id=' . $id, false));

			return false;
		}

		// Check in the profile.
		if ($return)
		{
			$model->checkin($return);
		}

		// Clear the profile id from the session.
		$app->setUserState('com_jticketing.edit.eventform.id', null);

		// Redirect to the list screen.
		$this->setMessage(Text::_('COM_JTICKETING_ITEM_DELETED_SUCCESSFULLY'));
		$menu = & JSite::getMenu();
		$item = $menu->getActive();
		$this->setRedirect(Route::_($item->link, false));

		// Flush the data from the session.
		$app->setUserState('com_jticketing.edit.eventform.data', null);
	}

	/**
	 * Method to get edit venue
	 *
	 * Method to create online event
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function createSeminar()
	{
		$post = Factory::getApplication()->getInput()->post;
		$formData = new Registry($post->get('jform', '', 'array'));
		$unlimitedCount = $post->get('ticket_type_unlimited_seats', '', 'array');

		if ($unlimitedCount['0'] == 1)
		{
			$ticketCount = 'unlimited';
		}
		else
		{
			$ticketCount = array_sum($post->get('ticket_type_available', '', 'array'));
		}

		$venueId = $formData->get('venue');
		$Name = $formData->get('title');
		$startTime = $post->get('event_start_time_hour') . ':' . $post->get('event_start_time_min') . ' ' . $post->get('event_start_time_ampm');

		if ($post->get('event_start_time_min') != '00')
		{
			$startTimeFormated = date("H:i", strtotime($startTime));
		}
		else
		{
			$startTimeFormated = date("H:i", strtotime($post->get('event_start_time_hour') . ' ' . $post->get('event_start_time_ampm')));
		}

		$endTime = $post->get('event_end_time_hour') . ':' . $post->get('event_end_time_min') . ' ' . $post->get('event_end_time_ampm');

		if ($post->get('event_end_time_min') != '00')
		{
			$endTimeFormated = date("H:i", strtotime($endTime));
		}
		else
		{
			$endTimeFormated = date("H:i", strtotime($post->get('event_end_time_hour') . ' ' . $post->get('event_end_time_ampm')));
		}

		$beginDate = $formData->get('startdate') . 'T' . $startTimeFormated;
		$endDate = $formData->get('enddate') . 'T' . $endTimeFormated;

		// Load AnnotationForm Model
		$model = BaseDatabaseModel::getInstance('Venue', 'JticketingModel');
		$licenceContent = $model->getItem($venueId);
		$licence = (object) $licenceContent->params;
		$utilities = JT::utilities();
		$password = $utilities->generateRandomString(8);
		$userid = $post->get('jform_created_by');

		if ($userid == 0)
		{
			$userDetail = Factory::getUser();
		}
		elseif ($userid == -1)
		{
			$userDetail->id = 0;
		}
		else
		{
			$userDetail = Factory::getUser($userid);
		}

		// TRIGGER After create event
		if (!empty($licence))
		{
			PluginHelper::importPlugin('tjevents');

			if ($licence->event_type == 'meeting')
			{
				$result = Factory::getApplication()->triggerEvent('createMeeting', array($licence, $Name, $userDetail, $beginDate, $endDate, $ticketCount, $password));
			}
			elseif ($licence->event_type == 'seminar')
			{
				$result = Factory::getApplication()->triggerEvent('createSeminar', array($licence, $Name, $userDetail, $beginDate, $endDate, $ticketCount, $password));
			}
		}

		echo json_encode($result['0']);

		jexit();
	}

	/**
	 * Method to get all existing events
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function getAllMeetings()
	{
		$post = Factory::getApplication()->getInput()->post;
		$venueId = $post->get('venueId');

		// Load AnnotationForm Model
		$licenceContent = JT::Venue($venueId);
		$licence        = json_decode(!empty($licenceContent->params) ? $licenceContent->params : '');

		if (!empty($venueId))
		{
			// TRIGGER After create event
			PluginHelper::importPlugin('tjevents');
			$result = Factory::getApplication()->triggerEvent('onGetAllMeetings', array($licence));
			echo json_encode($result);
		}

		jexit();
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
    protected function getRedirectToItemAppend($recordId = null, $urlVar = 'id')
    {
        // Need to override the parent method completely.
        $tmpl   = $this->input->get('tmpl');

        $append = '';

        // Setup redirect info.
        if ($tmpl) {
            $append .= '&tmpl=' . $tmpl;
        }

        // @todo This is a bandaid, not a long term solution.
        /**
         * if ($layout)
         * {
         *  $append .= '&layout=' . $layout;
         * }
         */

        $append .= '&layout=default';
		$urlVar = $urlVar ? $urlVar : 'id';

        if ($recordId) {
            $append .= '&' . $urlVar . '=' . $recordId;
        }

		$itemId = JT::utilities()->getItemId('index.php?option=com_jticketing&view=eventform');

        $return = $this->getReturnPage();

        if ($itemId) {
            $append .= '&Itemid=' . $itemId;
        }

        if ($return) {
            $append .= '&return=' . base64_encode($return);
        }

        return $append;
    }

    /**
     * Get the return URL.
     *
     * If a "return" variable has been passed in the request
     *
     * @return  string  The return URL.
     *
     * @since   1.6
     */
    protected function getReturnPage()
    {
        $return = $this->input->get('return', null, 'base64');

        if (empty($return) || !Uri::isInternal(base64_decode($return))) {
            return Uri::base();
        } else {
            return base64_decode($return);
        }
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
		$itemId   = JT::utilities()->getItemId('index.php?option=com_jticketing&view=eventform');

		// Redirect to the edit screen.
		$this->setRedirect(
			Route::_(
				'index.php?option=com_jticketing&view=eventform&Itemid=' . $itemId, false
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
		$itemId   = JT::utilities()->getItemId('index.php?option=com_jticketing&view=eventform');

		// Redirect to the edit screen.
		$this->setRedirect(
			Route::_(
				'index.php?option=com_jticketing&view=eventform&Itemid=' . $itemId . '&id=' . $id, false
			)
		);

		return true;
	}

	/**
	 * Method Sends feedback emails to attendees of past events (ended yesterday)
	 * if the required plugins are enabled and emails haven't been sent yet.
	 *
	 * @return void
	 * @since  5.1.0
	*/
	public function sendFeedbackEmails()
	{
		JLoader::register('JticketingMailHelper', JPATH_SITE . '/components/com_jticketing/helpers/mail.php');
		
		$rsformEnabled       = PluginHelper::isEnabled('jticketing', 'rsform');
		$tjintegrationEnabled = PluginHelper::isEnabled('content', 'tjintegration');

		if (!($rsformEnabled && $tjintegrationEnabled))
		{
			return; 
		}

		$model = $this->getModel('EventForm', 'JticketingModel');
		$events = $model->fetchPendingFeedbackEventsWithEmails();

		if (!empty($events))
		{
			$db = Factory::getDbo();

			foreach ($events as $event)
			{
				if (!empty($event['emails']))
				{
					JticketingMailHelper::sendPostEventFeedbackEmail($event['id'], $event['emails']);

					// feedback_mail_sent = 1 update
					$query = $db->getQuery(true)
						->update('#__jticketing_events')
						->set('feedback_mail_sent = 1')
						->where('id = ' . (int) $event['id']);
					$db->setQuery($query);
					$db->execute();
				}
			}
		}
	}
}
