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
use Joomla\CMS\Date\Date;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\Filesystem\File;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\MVC\Controller\AdminController;

BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_jticketing/models');

/**
 * Events list controller class.
 *
 * @since  1.5
 */
class JticketingControllerEvents extends AdminController
{
	/**
	 * Proxy for getModel.
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  The array of possible config values. Optional.
	 *
	 * @return  JModel
	 *
	 * @since   1.6
	 */
	public function getModel($name = 'event', $prefix = 'JticketingModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}

	/**
	 * Function used to get the formaated time
	 *
	 * @param   ARRAY  $data  Post data
	 *
	 * @return  string  $formattedTime  Final formatted time
	 *
	 * @since  1.0.0
	 */
	private function getFormattedTime($data)
	{
		// Start Date/Time
		$event_start_date      = explode(' ', $data->startdate);
		$event_start_time      = explode(':', $event_start_date[1]);
		$event_start_time_hour = $event_start_time[0];
		$event_start_time_min  = $event_start_time[1];

		// Convert hours into 12 hour format.
		if ($event_start_time_hour > 12)
		{
			$starthours = $event_start_time_hour - 12;
			$start_hour = $starthours;
			$starthours = $starthours . ":" . $event_start_time_min . ":00";
			$event_start_time_ampm = "pm";

			// $starthours = $starthours .":".$event_start_time_min. ":00 pm";
		}
		else
		{
			$start_hour = $event_start_time_hour;
			$starthours = $event_start_date[1];
			$event_start_time_ampm = "am";

			// $starthours = $event_start_date[1] . " am";
		}

		// End Date/Time
		$event_end_date      = explode(' ', $data->enddate);
		$event_end_time      = explode(':', $event_end_date[1]);
		$event_end_time_hour = $event_end_time[0];
		$event_end_time_min  = $event_end_time[1];

		// Convert hours into 12 hour format.
		if ($event_end_time_hour > 12)
		{
			$endhours = $event_end_time_hour - 12;
			$end_hour = $endhours;
			$endhours = $endhours . ":" . $event_end_time_min . ":00";

			$event_end_time_ampm = "pm";

			// $endhours = $endhours .":".$event_end_time_min. ":00 pm";
		}
		else
		{
			$endhours = $event_end_date[1];
			$end_hour = $event_end_time_hour;
			$event_end_time_ampm = "am";

			// $endhours = $event_end_date[1] . " am";
		}

		$formattedTime = array();

		// Set return values.
		$formattedTime['event_start_date']       = $event_start_date[0];
		$formattedTime['event_start_time']       = $starthours;
		$formattedTime['event_start_time_hours'] = $start_hour;
		$formattedTime['event_start_time_min']   = $event_start_time_min;
		$formattedTime['event_start_time_ampm']  = $event_start_time_ampm;
		$formattedTime['event_end_date']         = $event_end_date[0];
		$formattedTime['event_end_time']         = $endhours;
		$formattedTime['event_end_time_hours']   = $end_hour;
		$formattedTime['event_end_time_min']     = $event_end_time_min;
		$formattedTime['event_end_time_ampm']    = $event_end_time_ampm;

		return $formattedTime;
	}

	/**
	 * Method to csv Import
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function csvImport()
	{
		$app  = Factory::getApplication();
		$fileArray  = $app->getInput()->files->get('csvfile');

		if (!isset($fileArray['tmp_name']) || empty($fileArray) || !$fileArray['tmp_name']) 
		{
			$app->enqueueMessage(Text::_('COM_JTICKETING_ERROR_IN_MOVING'), 'warning');
			$app->redirect(Route::_('index.php?option=com_jticketing&view=events', false));

			return false;
		}

		// Start file heandling functionality *
		$fileName = File::stripExt($fileArray['name']);
		File::makeSafe($fileName);

		$uploads_dir = Factory::getApplication()->get('tmp_path') . '/' . $fileArray['name'];

		if (!File::upload($fileArray['tmp_name'], $uploads_dir))
		{
			$app->enqueueMessage(Text::_('COM_JTICKETING_ERROR_IN_MOVING'), 'warning');
			$app->redirect(Route::_('index.php?option=com_jticketing&view=events', false));

			return false;
		}

		if ($file = fopen($uploads_dir, "r"))
		{
			$ext = File::getExt($uploads_dir);

			if ($ext != 'csv')
			{
				$app->enqueueMessage(Text::_('NOT_CSV_MSG'), 'warning');
				$app->redirect(Route::_('index.php?option=com_jticketing&view=events', false));

				return false;
			}

			$rowNum = 0;

			while (($data = fgetcsv($file)) !== false)
			{
				if ($rowNum == 0)
				{
					// Parsing the CSV header
					$headers = array();

					foreach ($data as $d)
					{
						$headers[] = $d;
					}
				}
				else
				{
					// Parsing the data rows
					$rowData = array();

					foreach ($data as $d)
					{
						$rowData[] = $d;
					}

					if (isset($headers))
					{
						$eventData[] = array_combine($headers, $rowData);
					}
				}

				$rowNum++;
			}

			fclose($file);
		}
		else
		{
			// $msg = Text::_('File not open');
			$application = Factory::getApplication();
			$application->enqueueMessage(Text::_('COM_JTICKETING_SOME_ERROR_OCCURRED'), 'error');
			$app->redirect(Route::_('index.php?option=com_jticketing&view=events', false));

			return;
		}

		$output = array();
		$output['return'] = 1;
		$output['successmsg'] = '';
		$output['errormsg'] = '';
		$config = Factory::getConfig();
		$offset = $config->get('offset');

		if (!empty($eventData))
		{
			$location     = $booking_end_date = $booking_start_date = $enddate = $startdate = $emptyFile = 0;
			$catidx       = $titlex = $idnotfound = $catidnotfound = $sucess = $venueChoice = $miss_col = $badData = 0;
			$totalEvents  = count($eventData);
			$validVenue   = true;
			$invalidVenue = array();

			foreach ($eventData as $eachEvent)
			{
				foreach ($eachEvent as $key => $value)
				{
					if (!array_key_exists('Title', $eachEvent) || !array_key_exists('CategoryId', $eachEvent) || !array_key_exists('Start Date', $eachEvent)
						|| !array_key_exists('End Date', $eachEvent))
					{
						$miss_col = 1;
						break;
					}

					switch ($key)
					{
						case 'Id' :
							$data['id'] = 0;

							if (!empty ($value))
							{
								$data['id'] = $value;
							}

						break;

						case 'Title' :
							$data['title'] = $value;

						break;

						case 'CategoryId' :
							$data['catid'] = $value;

						break;

						case 'State' :
							$data['state'] = 0;

							if (!empty($value))
							{
								$data['state'] = $value;
							}

						break;

						case 'Description' :
							$data['long_description'] = $value;

						break;

						case 'Start Date' :
							$startDate = new Date($value, $offset);
							$startDate->setTimezone(new DateTimeZone('UTC'));
							$startDate = Factory::getDate($startDate)->toSql();
							$data['startdate'] = $startDate;

						break;

						case 'End Date' :
							$endDate = new Date($value, $offset);
							$endDate->setTimezone(new DateTimeZone('UTC'));
							$endDate = Factory::getDate($endDate)->toSql();
							$data['enddate'] = $endDate;

						break;

						case 'Booking Start Date' :

							if (empty($value))
							{
								$data['booking_start_date'] = '';
							}
							else
							{
								$bookingStartDate = new Date($value, $offset);
								$bookingStartDate->setTimezone(new DateTimeZone('UTC'));
								$bookingStartDate = Factory::getDate($bookingStartDate)->toSql();
								$data['booking_start_date'] = $bookingStartDate;
							}

						break;

						case 'Booking End Date' :

							if (empty($value))
							{
								$data['booking_end_date'] = '';
							}
							else
							{
								$bookingEndDate = new Date($value, $offset);
								$bookingEndDate->setTimezone(new DateTimeZone('UTC'));
								$bookingEndDate = Factory::getDate($bookingEndDate)->toSql();
								$data['booking_end_date'] = $bookingEndDate;
							}

						break;

						case 'Online Event' :
							$data['online_events'] = 0;

							if (!empty($value))
							{
								$data['online_events'] = $value;
							}

						break;

						case 'Venue Id' :
							$data['venue'] = 0;

							if (!empty($value))
							{
								$data['venue'] = $value;
							}

						break;

						case 'Location' :
							$data['location'] = $value;

						break;

						case 'Online Event Name' :
							$data['venuechoice'] = $value;

						break;

						case 'Access' :
							$data['access'] = $value;

						break;

						default :
						// All other fields would be treated as 'event fields' in field manager of jticketing
						$field_name = $this->checkEventField($key);

						if (!empty($field_name))
						{
							$data['extra_jform_data'] = $value;
						}

						break;
					}
				}

				$checkId = $this->getValidateId($data['id']);

				if ($checkId == 'notExistId')
				{
					$idnotfound ++;
				}

				$catidE = $this->categoryexit($data['catid']);

				if ($catidE == 'notExistCatId')
				{
					$catidnotfound ++;
				}

				if (!is_numeric($data['catid']))
				{
					$catidx ++;
					break;
				}

				if ($data['online_events'] == 1 and empty($data['venuechoice']))
				{
					$venueChoice ++;
				}

				if ($data['location'] != '' or $data['venue'] != 0)
				{
					$data['created_by'] = Factory::getUser()->id;
					$data['featured'] = 0;
				}
				else
				{
					$location ++;
				}

				// Validate venue if empty of not present from db.
				$validVenue = $this->validateVenue($data['venue'], $data['location']);

				if (!$validVenue)
				{
					$invalidVenue[] = $data['title'];
				}

				$ticketId = $this->checkTicket($data['id']);
				$output = array();
				$output['title']               = 'free';
				$output['id']                  = $ticketId;
				$output['desc']                = '';
				$output['state']               = '1';
				$output['price']               = '0';
				$output['access']              = '1';
				$output['unlimited_seats']     = '1';
				$output['available']           = '0';

				$data['tickettypes']['tickettypes0']  = $output;

				$model = $this->getModel('event');

				if ($data['title'] != '' && $catidnotfound == 0 && $catidx == 0 && $validVenue === true)
				{
					if ($model->save($data))
					{
						$sucess ++;
					}
					else
					{
						$badData ++;
						$output['errormsg'] .= $model->getError();
					}
				}
			}
		}
		else
		{
			$emptyFile ++;
		}

		if ($emptyFile == 1)
		{
			$output['errormsg'] = Text::sprintf('COM_JTICKETING_IMPORT_BLANK_FILE');
		}
		else
		{
			if ($miss_col)
			{
				$output['successmsg'] = "";
				$output['errormsg'] = Text::_('COM_JTICKETING_CSV_IMPORT_COLUMN_MISSING');
			}
			else
			{
				$output['successmsg'] = Text::sprintf('COM_JTICKETING_EVENTS_IMPORT_TOTAL_ROWS_CNT_MSG', $totalEvents) . "<br />";

				if ($sucess > 0)
				{
					$output['successmsg'] .= Text::sprintf('COM_JTICKETING_EVENTS_IMPORT_NEW_EVENTS_MSG', $sucess) . "<br />";
				}

				if ($catidx > 0)
				{
					$output['errormsg'] .= Text::sprintf('COM_JTICKETING_EVENTS_IMPORT_CAT_EVENTS_MSG', $catidx) . "<br />";
				}

				if ($catidnotfound > 0)
				{
					$output['errormsg'] .= Text::sprintf('COM_JTICKETING_EVENTS_IMPORT_CAT_EVENTS_MSG', $catidnotfound) . "<br />";
				}

				if ($idnotfound > 0)
				{
					$output['errormsg'] .= Text::sprintf('COM_JTICKETING_EVENTS_IMPORT_ID_EVENTS_MSG', $idnotfound) . "<br />";
				}

				if ($startdate > 0)
				{
					$output['errormsg'] .= Text::sprintf('COM_JTICKETING_EVENTS_IMPORT_STARTDATE_EVENTS_MSG', $startdate) . "<br />";
				}

				if ($enddate > 0)
				{
					$output['errormsg'] .= Text::sprintf('COM_JTICKETING_EVENTS_IMPORT_ENDDATE_EVENTS_MSG', $enddate) . "<br />";
				}

				if ($booking_start_date > 0)
				{
					$output['errormsg'] .= Text::sprintf('COM_JTICKETING_EVENTS_IMPORT_BOOKING_STARTDATE_EVENTS_MSG', $booking_start_date) . "<br />";
				}

				if ($booking_end_date > 0)
				{
					$output['errormsg'] .= Text::sprintf('COM_JTICKETING_EVENTS_IMPORT_BOOKING_ENDDATE_EVENTS_MSG', $booking_end_date) . "<br />";
				}

				if ($location > 0)
				{
					$output['errormsg'] .= Text::sprintf('COM_JTICKETING_EVENTS_IMPORT_LOCATION_EVENTS_MSG', $location) . "<br />";
				}

				if ($venueChoice > 0)
				{
					$output['errormsg'] .= Text::sprintf('COM_JTICKETING_EVENTS_IMPORT_VENUE_CHOICE_EVENTS_MSG', $venueChoice) . "<br />";
				}

				if (!empty($invalidVenue))
				{
					$output['errormsg'] .= Text::sprintf('COM_JTICKETING_EVENTS_CSV_INVALID_VENUE_ID', implode(", ", $invalidVenue)) . "<br />";
				}
			}
		}

		if ($output['errormsg'])
		{
			$app->enqueueMessage($output['errormsg'], 'error');
		}

		if ($output['successmsg'])
		{
			$app->enqueueMessage($output['successmsg'], 'success');
		}

		$app->redirect(Route::_('index.php?option=com_jticketing&view=events', false));

		return;
	}

	/**
	 * checkTicket.
	 *
	 * @param   integer  $id  event id
	 *
	 * @return  void
	 *
	 * @since   3.1.2
	 */
	public function checkTicket($id)
	{
		$ticketId = 0;
		$db    = Factory::getDbo();
		$query = "SELECT id FROM #__jticketing_integration_xref WHERE eventid ='{$id}'";
		$db->setQuery($query);
		$integration_xref = $db->loadResult();

		if ($integration_xref)
		{
			$query = "SELECT id FROM #__jticketing_types WHERE eventid ='{$integration_xref}'";
			$db->setQuery($query);
			$ticketId = $db->loadResult();
		}

		return $ticketId;
	}

	/**
	 * checkEventField.
	 *
	 * @param   integer  $label  label
	 *
	 * @return  void
	 *
	 * @since   3.1.2
	 */
	public function checkEventField($label)
	{
		$ticketId = 0;
		$db    = Factory::getDbo();
		$query = "SELECT name FROM #__tjfields_fields WHERE name  LIKE '{$label}'";
		$db->setQuery($query);

		return $field_name = $db->loadResult();
	}

	/**
	 * getValidateId.
	 *
	 * @param   integer  $id  event id
	 *
	 * @return  void
	 *
	 * @since   3.1.2
	 */
	public function getValidateId($id)
	{
		$eventId = '';

		if ($id)
		{
			$db    = Factory::getDbo();
			$query = "SELECT id FROM #__jticketing_events WHERE id ='{$id}'";
			$db->setQuery($query);
			$eventId = $db->loadResult();

			if ($eventId == '')
			{
				return 'notExistId';
			}
		}

		return $eventId;
	}

	/**
	 * categoryexit.
	 *
	 * @param   integer  $id  id
	 *
	 * @return  void
	 *
	 * @since   3.1.2
	 */
	public function categoryexit($id)
	{
		$catId = '';

		if ($id)
		{
			$db    = Factory::getDbo();
			$query = "SELECT id FROM #__categories WHERE id ='{$id}' AND extension = 'com_jticketing'";
			$db->setQuery($query);
			$catId = $db->loadResult();

			if ($catId == '')
			{
				return 'notExistCatId';
			}
		}

		return $catId;
	}

	/**
	 * Method to save the submitted ordering values for records via AJAX.
	 *
	 * @return  void
	 *
	 * @since   1.5
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
	 * Method to feature selected events.
	 *
	 * @return  void
	 *
	 * @since   1.5
	 */
	public function feature()
	{
		$input = Factory::getApplication()->getInput();
		$cid   = $input->get('cid', '', 'array');
		ArrayHelper::toInteger($cid);
		$model        = $this->getModel('events');
		$successCount = $model->setItemFeatured($cid, 1);

		if ($successCount)
		{
			if ($successCount > 1)
			{
				$msg = Text::sprintf(Text::_('COM_JTICKETING_N_ITEMS_FEATURED'), $successCount);
			}
			else
			{
				$msg = Text::sprintf(Text::_('COM_JTICKETING_N_ITEMS_FEATURED_1'), $successCount);
			}
		}
		else
		{
			$msg = Text::_('COM_JTICKETING_FEATURED_ERROR') . '</br>' . $model->getError();
		}

		$redirect = Route::_('index.php?option=com_jticketing&view=events', false);

		$this->setMessage($msg);
		$this->setRedirect($redirect);
	}

	/**
	 * Method to unfeature selected events.
	 *
	 * @return  void
	 *
	 * @since   1.5
	 */
	public function unfeature()
	{
		$input = Factory::getApplication()->getInput();
		$cid   = $input->get('cid', '', 'array');
		ArrayHelper::toInteger($cid);
		$model        = $this->getModel('events');
		$successCount = $model->setItemFeatured($cid, 0);

		if ($successCount)
		{
			if ($successCount > 1)
			{
				$msg = Text::sprintf(Text::_('COM_JTICKETING_N_ITEMS_UNFEATURED'), $successCount);
			}
			else
			{
				$msg = Text::sprintf(Text::_('COM_JTICKETING_N_ITEMS_UNFEATURED_1'), $successCount);
			}
		}
		else
		{
			$msg = Text::_('COM_JTICKETING_UNFEATURED_ERROR') . '</br>' . $model->getError();
		}

		$redirect = Route::_('index.php?option=com_jticketing&view=events', false);

		$this->setMessage($msg);
		$this->setRedirect($redirect);
	}

	/**
	 * Function that allows child controller access to model data
	 * after the item has been deleted.
	 *
	 * @param   BaseDatabaseModel  $model  The data model object.
	 * @param   array              $ids    The validated data.
	 *
	 * @return  void
	 *
	 * @since   1.5
	 */
	protected function postDeleteHook(BaseDatabaseModel $model, $ids = null)
	{
		$jteventHelperPath = JPATH_ROOT . '/components/com_jticketing/helpers/event.php';

		if (!class_exists('jteventHelper'))
		{
			JLoader::register('jteventHelper', $jteventHelperPath);
			JLoader::load('jteventHelper');
		}

		$jteventHelper = new jteventHelper;
		$integration_arr = array();
		$integration = JT::getIntegration();

		// Firstly find integration id based on event id
		foreach ($ids AS $evid)
		{
			$integration_arr[] = JT::event($evid, $integration)->integrationId;
		}

		/*Pass the integration id and delete event
			@TODO Snehal add validation for delete event
			$jteventHelper->delete_Event($integration_arr);
		*/
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

			if ($eventObj->enddate <= $currentDate)
			{
				$confirm = $JTicketingModelEvent->delete($id);

				if ($confirm == "true")
				{
					$count['valid'] = $count['valid'] + 1;
				}
			}
			else
			{
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

		$redirect = Route::_('index.php?option=com_jticketing&view=events', false);
		$this->setRedirect($redirect);
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

		$pks = $this->input->post->get('cid', array(), 'array');
		ArrayHelper::toInteger($pks);

		try
		{
			if (empty($pks))
			{
				throw new Exception(Text::_('COM_JTICKETING_NO_ELEMENT_SELECTED'));
			}

			$model = $this->getModel();
			$model->duplicate($pks);
			$this->setMessage(Text::plural('COM_JTICKETING_ITEMS_SUCCESS_DUPLICATED', count($pks)));
		}
		catch (Exception $e)
		{
			$this->setMessage($e->getMessage(), 'error');
		}

		// Overrride the redirect Uri.
		$redirectUri = 'index.php?option=' . $this->option . '&view=' . $this->view_list . '&extension=' . $this->input->get('extension', '', 'CMD');
		$this->setRedirect(Route::_($redirectUri, false), $this->message, $this->messageType);
	}

	/**
	 * Method to validate the givien venue id according to the location
	 *
	 * @param   Int     $id        id of the venue
	 * @param   String  $location  location of the venue
	 *
	 * @return Boolean
	 *
	 * since  2.6.0
	 */
	public function validateVenue($id, $location)
	{
		if (empty($id))
		{
			if (empty($location))
			{
				return false;
			}

			return true;
		}

		$venue = JT::venue($id);

		if (empty($venue->id))
		{
			return false;
		}

		return true;
	}

	/**
	 * Method to publish a list of articles.
	 *
	 * @return  void
	 *
	 * @since   3.3.0
	 */
	public function publish()
	{
		$ids    = $this->input->get('cid', array(), 'array');
		$values = array('publish' => 1, 'unpublish' => 0, 'archive' => 2, 'trash' => -2);
		$task   = $this->getTask();
		$value  = ArrayHelper::getValue($values, $task, 0, 'int');

		// Get the model.
		$modelEventform = JT::model('eventform');
		$i = 0;

		foreach ($ids as $id)
		{
			$result = $modelEventform->publish($id, $value);

			if ($result == 1)
			{
				$i = $i +1;
			}
		}

		// Change the state of the records.
		if ($result == 1 && $value == 1)
		{
			$msg = Text::sprintf('COM_JTICKETING_EVENT_PUBLISHED', $i);
			$type = 'message';
		}
		elseif ($result == 1 && $value == 0)
		{
			$msg = Text::sprintf('COM_JTICKETING_EVENT_UNPUBLISHED', $i);
			$type = 'message';
		}
		else
		{
			$msg = $modelEventform->getError();
			$type = 'error';
		}

		$this->setRedirect('index.php?option=com_jticketing&view=events', $msg, $type);
	}
}
