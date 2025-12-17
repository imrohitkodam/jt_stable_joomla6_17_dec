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
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Response\JsonResponse;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Component\ComponentHelper;

if (file_exists(JPATH_LIBRARIES . '/techjoomla/common.php')) { require_once JPATH_LIBRARIES . '/techjoomla/common.php'; }
require_once JPATH_ADMINISTRATOR . '/components/com_jticketing'. '/controller.php';

/**
 * Event controller class.
 *
 * @since  1.7
 */
class JticketingControllerEvent extends JticketingController
{
	public $techjoomlacommon;
	
	/**
	 * Class constructor.
	 *
	 * @since   1.6
	 */
	public function __construct()
	{
		$this->techjoomlacommon = new TechjoomlaCommon;

		parent::__construct();
	}

	/**
	 * Cancel description
	 *
	 * @return description
	 */
	public function cancel()
	{
		$app        = Factory::getApplication();
		$previousId = (int) $app->getUserState('com_raector_crm.edit.project.id');

		if ($previousId)
		{
			// Get the model.
			$model = $this->getModel('Project', 'Raector_crmModel');
			$model->checkin($previousId);
		}

		$menu =& JSite::getMenu();
		$item = $menu->getActive();
		$this->setRedirect(Route::_($item->link, false));
	}

	/**
	 * remove description
	 *
	 * @return  boolean|void  Incase of error boolean and in case of success void
	 */
	public function remove()
	{
		// Check for request forgeries.
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		// Initialise variables.
		$app   = Factory::getApplication();
		$model = $this->getModel('Event', 'JticketingModel');

		// Get the user data.
		$data = Factory::getApplication()->getInput()->get('jform', array(), 'array');

		// Validate the posted data.
		$form = $model->getForm();

		if (!$form)
		{
			$app = Factory::getApplication();
			$app->enqueueMessage($model->getError(), 'error');

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
			$app->setUserState('com_jticketing.edit.event.data', $data);

			// Redirect back to the edit screen.
			$id = (int) $app->getUserState('com_jticketing.edit.event.id');
			$this->setRedirect(Route::_('index.php?option=com_jticketing&view=event&layout=edit&id=' . $id, false));

			return false;
		}

		// Attempt to save the data.
		$return = $model->delete($data);

		// Check for errors.
		if ($return === false)
		{
			// Save the data in the session.
			$app->setUserState('com_jticketing.edit.event.data', $data);

			// Redirect back to the edit screen.
			$id = (int) $app->getUserState('com_jticketing.edit.event.id');
			$this->setMessage(Text::sprintf('COM_JTICKETING_EVENT_DELETE_FAILED', $model->getError()), 'warning');
			$this->setRedirect(Route::_('index.php?option=com_jticketing&view=event&layout=edit&id=' . $id, false));

			return false;
		}

		// Check in the profile.
		if ($return)
		{
			$model->checkin($return);
		}

		// Clear the profile id from the session.
		$app->setUserState('com_jticketing.edit.event.id', null);

		// Redirect to the list screen.
		$this->setMessage(Text::_('COM_JTICKETING_ITEM_DELETED_SUCCESSFULLY'));
		$menu =& JSite::getMenu();
		$item = $menu->getActive();
		$this->setRedirect(Route::_($item->link, false));

		// Flush the data from the session.
		$app->setUserState('com_jticketing.edit.event.data', null);
	}

	/**
	 * remove renderbook
	 *
	 * @return void
	 */
	public function renderbook()
	{
		$eventid = 1;
		$data = $this->renderBookingHTML($eventid);
		print_r($data);
	}

	/**
	 * Render booking HTML
	 *
	 * @param   int  $eventid  id of event
	 * @param   int  $userid   userid
	 *
	 * @return  mixed on succuss return array or false
	 *
	 * @since   1.0
	 */
	public function renderBookingHTML($eventid, $userid='')
	{
		require_once JPATH_SITE . "/components/com_jticketing/models/event.php";

		if (empty($eventid))
		{
			return false;
		}

		$model = new JticketingModelEvent;

		return $model->renderBookingHTML($eventid, $userid);
	}

	/**
	 * Online Meeting URL
	 *
	 * @return  string  URL
	 *
	 * @since   1.0
	 */
	public function onlineMeetingUrl()
	{
		$app     = Factory::getApplication();
		$user = Factory::getUser();
		$url = 1;

		if (!Session::checkToken())
		{
			echo new JsonResponse(null, Text::_('JINVALID_TOKEN_NOTICE'), true);
			$app->close();
		}

		if (!$user->id)
		{
			echo new JsonResponse($url);
			$app->close();
		}

		$eventId = $app->getInput()->getInt('eventId');
		$event = JT::event($eventId);

		// Get the attendee id using the event id and user ID
		/** @var $attendees JticketingModelAttendees */
		$attendees = JT::model('attendees');
		$attendeeId = $attendees->getAttendees(
				['owner_id' => $user->id, 'event_id' => $event->getIntegrationId(),
						'status' => COM_JTICKETING_CONSTANT_ATTENDEE_STATUS_APPROVED,
						'limit' => 1]
				);

		$attendeeId = !empty($attendeeId[0]->id) ? $attendeeId[0]->id : 0;
		$event   = JT::event($eventId);
		$url     = $event->getJoinUrl(JT::attendee($attendeeId));

		if (!$url)
		{
			echo new JsonResponse(null, $event->getError(), true);
			$app->close();
		}

		echo new JsonResponse($url);
		$app->close();
	}

	/**
	 * Online Meeting Recording URL
	 *
	 * @return  string URL
	 *
	 * @since   1.0
	 */
	public function meetingRecordingUrl()
	{
		$app        = Factory::getApplication();

		if (!Session::checkToken())
		{
			echo new JsonResponse(null, Text::_('JINVALID_TOKEN_NOTICE'), true);
			$app->close();
		}

		$user = Factory::getUser();

		if (!$user->id)
		{
			echo new JsonResponse(1);
			$app->close();
		}

		$eventId = $app->getInput()->get('eventId', 0, 'INT');
		$eventData = JT::event($eventId);

		if (!$eventData->isBought($user->id) && !$eventData->isCreator($user->id))
		{
			echo new JsonResponse(null, Text::_('COM_JTICKETING_INVALID_ATTENDEE_ID'), true);
			$app->close();
		}

		$recordingtUrl = $eventData->getRecording();

		if (!$recordingtUrl)
		{
			echo new JsonResponse(null, $eventData->getError(), true);
			$app->close();
		}

		echo new JsonResponse($recordingtUrl, Text::_('COM_JTICKETING_GET_RECORDING_URL'));
		$app->close();
	}

	/**
	 * Function to save text activity
	 *
	 * @return  object  activities
	 *
	 * @since   1.6
	 */
	public function addPostedActivity()
	{
		$app	= Factory::getApplication();
		$input 	= $app->input;
		$activityData = array();
		$activityData['postData'] = $input->get('activity-post-text', '', 'STRING');

		$activityData['type'] = 'text';
		$activityData['eventid'] = $input->get('id', '0', 'INT');

		$itemId   = JT::utilities()->getItemId('index.php?option=com_jticketing&view=event');
		$redirect = Route::_('index.php?option=com_jticketing&view=event&id=' . $input->get('id', '0', 'INT') . '&Itemid=' . $itemId, false);

		if (!empty($activityData['postData']) && !ctype_space($activityData['postData']))
		{
			// Trigger jticketing activity plugin to add test activity
			PluginHelper::importPlugin('system');

			$result = Factory::getApplication()->triggerEvent('onPostActivity', array($activityData));

			if (empty($result[0]['error']))
			{
				$msg = Text::_("COM_JTICKETING_TEXT_ACTIVITY_POST_SUCCESS_MSG");
				$app->enqueueMessage($msg, 'success');
				$app->redirect($redirect);
			}
			else
			{
				$msg = Text::_("COM_JTICKETING_TEXT_ACTIVITY_POST_GUEST_ERROR_MSG");
				$app->enqueueMessage($msg, 'error');
				$app->redirect($redirect);
			}
		}

		$msg = Text::_("COM_JTICKETING_TEXT_ACTIVITY_POST_ENTER_VALID_DATA");
		$app->enqueueMessage($msg, 'error');
		$app->redirect($redirect);
	}

	/**
	 * Method added for add event to Google Calender
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function addGoogleEvent()
	{
		$app = Factory::getApplication();
		$eventId = $app->getInput()->get('id', '', 'INTEGER');

		BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_jticketing/models', 'eventform');
		$jTicketingModelEventform = BaseDatabaseModel::getInstance('Eventform', 'JTicketingModel');
		$eventDetails = $jTicketingModelEventform->getItem($eventId);

		$model = $this->getModel('Event', 'JticketingModel');
		$url = $model->addGoogleEvent($eventDetails);
		$this->setRedirect(Route::_($url, false));
	}

	/**
	 * Method for getting Event specific orders and average orders data for showing graph
	 *
	 * @return   json
	 *
	 * since 2.0
	 */
	public function getEventOrderGrapgData()
	{
		$params = ComponentHelper::getParams('com_jticketing');
		$currency = $params->get('currency_symbol');

		$input = Factory::getApplication()->getInput();

		$this->techjoomlacommon = new TechjoomlaCommon;
		$lastTwelveMonth = $this->techjoomlacommon->getLastTwelveMonths();

		$duration = $input->get('filtervalue');
		$eventId = $input->get('eventId');

		$model = $this->getModel('event');
		$results = $model->getEventGarphData($duration, $eventId);

		if ($duration == 0)
		{
			$graphDuration = 7;
		}
		elseif ($duration == 1)
		{
			$graphDuration = 30;
			$arraychunkvar = 7;
		}
		elseif ($duration == 2)
		{
			$arraychunkvar = 30;

			$todate = Factory::getDate(date('Y-m-d'))->Format(Text::_('Y-m-d'));
			$backdate = date('Y-m-d', strtotime(date('Y-m-d') . ' - 1 year'));
			$graphDuration = round(abs(strtotime($todate) - strtotime($backdate)) / 86400);
		}

		// To order amount
		$totalOrdersAmt = 0;

		foreach ($results as $key => $result)
		{
			if (isset($result->order_amount))
			{
				$totalOrdersAmt += $result->order_amount;
			}
		}

		if ($duration == 0 || $duration == 1)
		{
			for ($i = 0; $i < $graphDuration; $i++)
			{
				// Making order Date Array
				$graphDataArr['orderDate'][$i] = date("Y-m-d", strtotime($i . " days ago"));

				// Making Average order Array
				$graphDataArr['orderAvg'][$i] = $totalOrdersAmt / $graphDuration;

				if (!empty($results))
				{
					// Making order amount Array
					for ($j = 0; $j < count($results); $j++)
					{
						if ($graphDataArr['orderDate'][$i] == $results[$j]->cdate)
						{
							$graphDataArr['orderAmount'][$i] = $results[$j]->order_amount;
							break;
						}
						else
						{
							$graphDataArr['orderAmount'][$i] = "0";
						}
					}
				}
				else
				{
					$graphDataArr['orderAmount'][$i] = "0";
				}
			}
		}
		elseif ($duration == 2)
		{
			// Making order amount Array
			for ($i = 0; $i < count($lastTwelveMonth); $i++)
			{
				$graphDataArr['orderDate'][$i] = $lastTwelveMonth[$i]['month'];
				$graphDataArr['orderAvg'][$i] = $totalOrdersAmt / $graphDuration;

				if (!empty($results))
				{
					for ($j = 0; $j < count($results); $j++)
					{
						$monthNum  = $results[$j]->month_name;
						$dateObj   = DateTime::createFromFormat('!m', $monthNum);
						$monthName = $dateObj->format('F');

						if ($lastTwelveMonth[$i]['month'] == $monthName)
						{
							$graphDataArr['orderAmount'][$i] = $results[$j]->order_amount;
							break;
						}
						else
						{
							$graphDataArr['orderAmount'][$i] = "0";
						}
					}
				}
				else
				{
					$graphDataArr['orderAmount'][$i] = "0";
				}
			}
		}

		$avgOrdersAmount = $totalOrdersAmt / $graphDuration;

		$graphDataArr['totalOrdersAmount'] = Text::_("COM_JTICKETING_EVENT_DETAIL_TOTAL_ORDERS_AMOUNT") .
											$currency . @number_format($totalOrdersAmt, 2, '.', ',');
		$graphDataArr['avgOrdersAmount'] = Text::_("COM_JTICKETING_EVENT_DETAIL_AVG_ORDERS_AMOUNT") .
											$currency . @number_format($avgOrdersAmount, 2, '.', ',');

		if ($duration == 1)
		{
			$graphOrderAmount = array_chunk($graphDataArr['orderAmount'], $arraychunkvar);
			$graphOrderAmountNewArr = array();

			$graphOrderAvgAmount = array_chunk($graphDataArr['orderAvg'], $arraychunkvar);
			$graphOrderAvgAmountNewArr = array();

			for ($i = 0; $i < count($graphOrderAmount); $i++)
			{
				$graphOrderAmountNewArr[] = array_sum($graphOrderAmount[$i]);
				$graphDataArr['orderAmount'] = $graphOrderAmountNewArr;

				// Avg Donation divide in chunk
				$graphOrderAvgAmountNewArr[] = array_sum($graphOrderAvgAmount[$i]);
				$graphDataArr['orderAvg'] = $graphOrderAvgAmountNewArr;
			}

			$graphOrderDate = array_chunk($graphDataArr['orderDate'], $arraychunkvar);
			$graphOrderDateNewArr = [];

			for ($i = 0; $i < count($graphOrderDate); $i++)
			{
				$graphOrderDateNewArr[] = reset($graphOrderDate[$i]);
				$graphDataArr['orderDate'] = $graphOrderDateNewArr;
			}
		}

		if ($duration == 0)
		{
			for ($k = 0; $k < count($graphDataArr['orderDate']); $k++)
			{
				$graphDataArr['orderDate'][$k] = date("D", strtotime($graphDataArr['orderDate'][$k]));
			}
		}
		elseif ($duration == 1)
		{
			for ($k = 0; $k < count($graphDataArr['orderDate']); $k++)
			{
				$graphDataArr['orderDate'][$k] = date("d/m", strtotime($graphDataArr['orderDate'][$k]));
			}
		}

		if ($duration == 0 || $duration == 1)
		{
			$graphDataArr['orderAvg'] = array_reverse($graphDataArr['orderAvg']);
			$graphDataArr['orderAmount'] = array_reverse($graphDataArr['orderAmount']);
			$graphDataArr['orderDate'] = array_reverse($graphDataArr['orderDate']);
		}

		echo json_encode($graphDataArr);
		jexit();
	}

	/**
	 * View more Attendee information
	 *
	 * @return  Attendee information
	 *
	 * since 1.7
	 */
	public function viewMoreAttendee()
	{
		$input = Factory::getApplication()->getInput();
		$post  = $input->post;

		$eventId         = $post->get('eventId', '', 'INT');
		$jticketing_index = $post->get('jticketing_index', '', 'INT');

		$model  = $this->getModel('event');
		$result = $model->viewMoreAttendee($eventId, $jticketing_index);

		echo json_encode($result);
		jexit();
	}
}
