<?php
/**
 * @package     JTicketing
 * @subpackage  com_jticketing
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Date\Date;

$helperPath = JPATH_SITE . '/components/com_jticketing/helpers/time.php';
JLoader::register('JticketingCommonHelper', JPATH_SITE . '/components/com_jticketing/helpers/common.php');

if (! class_exists('JticketingTimeHelper'))
{
	JLoader::register('JticketingTimeHelper', $helperPath);
	JLoader::load('JticketingTimeHelper');
}

/**
 * JTicketing Event controller
 *
 * @since  0.0.9
 */
class JTicketingControllerEvent extends FormController
{
	/**
	 * Method to save a event record.
	 *
	 * @param   string  $key     The name of the primary key of the URL variable.
	 * @param   string  $urlVar  The name of the URL variable if different from the primary key (sometimes required to avoid router collisions).
	 *
	 * @return  boolean  True if successful, false otherwise.
	 *
	 * @since   1.0.0
	 */
	public function save($key = null, $urlVar = null)
	{
		// Check for request forgeries.
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		// Initialise variables.
		$app = Factory::getApplication();
		$model = $this->getModel('Event', 'JticketingModel');
		$recordId = $this->input->getInt('id');

		// Get the user data.
		$data = Factory::getApplication()->getInput()->get('jform', array(), 'array');
		$data['privacy_consent'] = $app->getInput()->get('accept_privacy_term', '');

		if (empty($data['created_by']))
		{
			$data['created_by'] = Factory::getUser()->id;
		}

		if (isset($data['online_events']) && $data['online_events'] == '1')
		{
			$beginDate = $data['startdate'];
			$endDate = $data['enddate'];

			if ($data['venuechoice'] == 'existing' && $data['id'] == 0)
			{
				$onlineScoId = $app->getInput()->get('event_sco_id', 0);
			}
		}

		// Validate the posted data.
		$form = $model->getForm($data, false);

		if (! $form)
		{
			// Prevent blank screen here
			$app->setUserState('com_jticketing.edit.event.data', $data);
			$this->setMessage($model->getError(), 'error');
			$this->setRedirect(Route::_('index.php?option=com_jticketing&view=event&layout=edit&id=' . $recordId, false));

			return false;
		}

		if ($data['id'] && $data['venue'])
		{
			$data['location'] = '';
		}
		$startDateTime = new Date($data['eventstart_date'] . ' ' . $data['start_time']);
		$endDateTime = new Date($data['eventend_date'] . ' ' . $data['end_time']);
		$data['startdate'] = $startDateTime->toSql();
		$data['enddate'] = $endDateTime->toSql();
		$validData = $model->validate($form, $data);

		if (! empty($data['id']))
		{
			JLoader::register('TJVendors', JPATH_SITE . "/components/com_tjvendors/includes/tjvendors.php");
			$vendor = TJVendors::vendor()->loadByUserId(null, JT::getIntegration());
			$data['vendor_id'] = $vendor->getId();
		}

		// Check for errors.
		if ($validData === false)
		{
			$errors = $model->getErrors();

			// Push up to three validation messages out to the user.
			for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i ++)
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

			$app->setUserState('com_jticketing.edit.event.data', $data);
			$this->setRedirect(Route::_('index.php?option=com_jticketing&view=event&layout=edit&id=' . $recordId, false));

			return false;
		}

		$db              = Factory::getDbo();
		Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jticketing/tables');
		$venueDetails = Table::getInstance('Venue', 'JticketingTable', array('dbo', $db));
		$venueDetails->load(array('id' => $data['venue']));

		if ($venueDetails->seats_capacity == 0) {
			$eventSeats = 0;
			foreach ($data['tickettypes'] as $key => $tickettype) {
				// check only limited seat available in event ticket types
				if(isset($tickettype['unlimited_seats']) && $tickettype['unlimited_seats'] != 0) {
					// Prevent blank screen here
					$app->setUserState('com_jticketing.edit.event.data', $data);
					$app->enqueueMessage(Text::_('COM_JTICKETING_VENUE_CAPACITY_CREATE_ERROR'), 'error');
					$this->setRedirect(Route::_('index.php?option=com_jticketing&view=event&layout=edit&id=' . $recordId, false));

					return false;
				}

				$eventSeats += $tickettype['available'];
			}

			// check ticket seats count must be less than OR equal to venue capacity
			if ($eventSeats > $venueDetails->capacity_count) {
				$app->setUserState('com_jticketing.edit.event.data', $data);
				$app->enqueueMessage(Text::_('COM_JTICKETING_VENUE_CAPACITY_ERROR'), 'error');
				$this->setRedirect(Route::_('index.php?option=com_jticketing&view=event&layout=edit&id=' . $recordId, false));

				return false;
			}
		}

		$jtConfig = JT::config();
		if ($jtConfig->get('entry_number_assignment', 0,'INT'))
		{
			// check start number of sequence in event level input only number and alphabetical present
			if ($data['start_number_for_event_level_sequence'] && !preg_match('/^[a-zA-Z0-9]+$/', $data['start_number_for_event_level_sequence']))
			{
				$app->setUserState('com_jticketing.edit.event.data', $data);
				$app->enqueueMessage(Text::_('COM_JTICKETING_INVALID_FIELD') . ': ' .Text::_('COM_JTICKETING_START_NUMBER_FOR_EVENT_LEVEL_SEQUENCE') , 'error');
				$this->setRedirect(Route::_('index.php?option=com_jticketing&view=event&layout=edit&id=' . $recordId, false));

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
						$app->setUserState('com_jticketing.edit.event.data', $data);
						$app->enqueueMessage(Text::_('COM_JTICKETING_INVALID_FIELD') . ': ' .Text::_('COM_JTICKETING_START_NUMBER_FOR_SEQUENCE') , 'error');
						$this->setRedirect(Route::_('index.php?option=com_jticketing&view=event&layout=edit&id=' . $recordId, false));

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

		$validData['userName'] = Factory::getUser($validData['created_by'])->name;
		$validData['privacy_consent'] = $data['privacy_consent'];
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

		$validData['privacy_consent'] = $data['privacy_consent'];
		$validData['beginDate'] = !empty($beginDate) ? $beginDate : '';
		$validData['onlineEndDate'] = !empty($endDate) ? $endDate : '';
		$validData['onlineScoId'] = !empty($onlineScoId) ? $onlineScoId : 0 ;
		$validData['enrollment'] = (int) !empty($validData['enrollment']) ? $validData['enrollment'] : 0;

		if (empty($validData['latitude']))
		{
			unset($validData['latitude']);
		}

		if (empty($validData['longitude']))
		{
			unset($validData['longitude']);
		}

		// @FixMe model save always return the boolean value and the new record value must be stored in the state
		$return = $model->save($validData);

		if (!empty($data['id']))
		{
			$model->saveExtraFields($extraFieldData);
		}

		// Check for errors.
		if ($return === false || $return === null)
		{
			$app->setUserState('com_jticketing.edit.event.data', $data);

			$this->setMessage(Text::sprintf('COM_JTICKETING_EVENT_ERROR_MSG_SAVE', $model->getError()), 'warning');
			$this->setRedirect(Route::_('index.php?option=com_jticketing&&view=event&layout=edit&id=' . $recordId, false));

			return false;
		}

		// Save succeeded, so check-in the record.
		if ($model->checkin($return) === false)
		{
			$app->setUserState('com_jticketing.edit.event.data', $data);
			$this->setError(\Text::sprintf('JLIB_APPLICATION_ERROR_CHECKIN_FAILED', $model->getError()));
			$this->setMessage($this->getError(), 'error');
			$this->setRedirect(Route::_('index.php?option=com_jticketing&view=events', false));

			return false;
		}

		$task = $this->getTask();

		$this->setMessage(Text::_('COM_JTICKETING_MSG_SUCCESS_SAVE_EVENT'), 'success');

		switch ($task)
		{
			case 'apply':
				// Since we want to edit the same record hold the id in the state
				$recordId = $return;
				$this->holdEditId('com_jticketing.edit.event', $recordId);
				$app->setUserState('com_jticketing.edit.event.data', null);
				$model->checkout($recordId);

				// Redirect back to the edit screen.
				$this->setRedirect(Route::_('index.php?option=com_jticketing&&view=event&layout=edit&id=' . $recordId, false));
				break;

			case 'save2new':
				// Clear the record id and data from the session.
				$this->releaseEditId('com_jticketing.edit.event', $recordId);
				$app->setUserState('com_jticketing.edit.event.data', null);

				// Redirect back to the edit screen.
				$this->setRedirect(Route::_('index.php?option=com_jticketing&&view=event&layout=edit&id=0', false));
				break;

			default:
				// Clear the record id and data from the session.
				$this->releaseEditId('com_jticketing.edit.event', $recordId);
				$app->setUserState('com_jticketing.edit.event.data', null);

				$url = 'index.php?option=com_jticketing&view=events';
				$return = $this->input->get('return', null, 'base64');

				if (! is_null($return) && Uri::isInternal(base64_decode($return)))
				{
					$url = base64_decode($return);
				}

				$this->setRedirect(Route::_($url, false));
		}
	}
}
