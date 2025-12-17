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
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\MVC\Controller\AdminController;

/**
 * Waiting list controller class.
 *
 * @since  2.2
 */
class JTicketingControllerWaitinglist extends AdminController
{
	/**
	 * Method to send email
	 *
	 * @return  void
	 *
	 * @since   2.2
	 */
	public function notifyUsersByEmail()
	{
		Session::checkToken() or Session::checkToken('get') or jexit('Invalid Token');

		$app     = Factory::getApplication();
		$session = Factory::getSession();
		$input   = $app->input;

		$subject = $input->get('jt-message-subject', '', 'POST', 'STRING');
		$body    = $input->get('jt-message-body', '', 'RAW');

		$safeHtmlFilter = InputFilter::getInstance(array(), array(), 1, 1);
		$body           = $safeHtmlFilter->clean($body, 'html');

		if (empty($subject))
		{
			$app->enqueueMessage(Text::_('COM_JTICKETING_EMAIL_SUBJECT_EMPTY'), 'error');

			$app->redirect(Route::_(Uri::base() . 'index.php?option=com_jticketing&view=waitinglist&layout=contactus'), false);
		}

		if (empty($body))
		{
			$app->enqueueMessage(Text::_('COM_JTICKETING_EMAIL_BODY_EMPTY'), 'error');

			$app->redirect(Route::_(Uri::base() . 'index.php?option=com_jticketing&view=waitinglist&layout=contactus'), false);
		}

		$waitlistIds = $session->get('waitlist_id');

		require_once JPATH_SITE . '/components/com_jticketing/models/waitinglist.php';
		$waitinglistModel = new JticketingModelWaitinglist;

		$selectedEmails = $waitinglistModel->getWaitlistUserEmails($waitlistIds);

		$cid   = array_unique($selectedEmails);
		$model = $this->getModel('waitinglist');

		if ($model->notifyUsersByEmail($cid, $subject, $body))
		{
			$msg = Text::_('COM_JTICKETING_EMAIL_SUCCESSFUL');
		}
		else
		{
			$msg = $model->getError();
		}

		$app->enqueueMessage($msg);
		$app->redirect(Route::_(Uri::base() . 'index.php?option=com_jticketing&view=waitinglist'), false);
	}

	/**
	 * Method to redirect to contact us view
	 *
	 * @return  void
	 *
	 * @since   2.2
	 */
	public function cancel()
	{
		$app = Factory::getApplication();
		$waitinglistLink = Route::_(Uri::base() . 'index.php?option=com_jticketing&view=waitinglist');
		$app->redirect($waitinglistLink);
	}

	/**
	 * Method to redirect to contact us view
	 *
	 * @return  void
	 *
	 * @since   2.2
	 */
	public function redirectForEmail()
	{
		Session::checkToken() or Session::checkToken('get') or jexit('Invalid Token');

		$app     = Factory::getApplication();
		$input   = $app->input;
		$cids	 = $input->get('cid', '', 'POST', 'ARRAY');

		$session = Factory::getSession();
		$session->set('waitlist_id', $cids);
		$session->get('waitlist_id');

		$link   = 'index.php?option=com_jticketing&view=waitinglist&layout=contactus';
		$itemId = JT::utilities()->getItemId($link, 0, 0);
		$app->redirect(Route::_($link . '&Itemid=' . $itemId, false));
	}

	/**
	 * Method to enroll to event
	 *
	 * @return  false|null
	 *
	 * @since   1.0
	 */
	public function enroll()
	{
		$app   = Factory::getApplication();
		$input = $app->input;
		$alreadyAddedCount = 0;
		$successCount = 0;
		$failureCount = 0;

		// Get some variables from the request
		$waitlistIds = (array) $input->get('cid', array(), 'post', 'array');
		$wIds = $input->get('wid');

		if (!empty($wIds))
		{
			$waitlistIds = (array) $wIds;
		}

		if (file_exists(JPATH_SITE . '/components/com_jticketing/models/waitlistform.php')) { require_once JPATH_SITE . '/components/com_jticketing/models/waitlistform.php'; }
		$model = BaseDatabaseModel::getInstance('WaitlistForm', 'JTicketingModel');

		BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_jticketing/models');
		$jticketingModelEnrollment = BaseDatabaseModel::getInstance('Enrollment', 'JticketingModel');
		$exceptions = array();

		foreach ($waitlistIds as $waitlistId)
		{
			$waitlistData = $model->getItem($waitlistId);
			$event        = JT::event()->loadByIntegration($waitlistData->event_id);

			$data = array();

			if (!empty($waitlistData->user_id))
			{
				$data['userId'] = $waitlistData->user_id;
			}

			if (!empty($waitlistData->event_id))
			{
				$data['eventId'] = $event->id;
			}

			if (empty($data))
			{
				return false;
			}

			$data['notify'] = true;

			$result = $jticketingModelEnrollment->save($data);

			if ($result)
			{
				if ((int) $result === 2)
				{
					++$alreadyAddedCount;
				}
				else
				{
					++$successCount;
				}
			}
			else
			{
				++$failureCount;
			}

			$name  = Factory::getUser($waitlistData->user_id)->name;
			$title = $event->getTitle();
			$error = $jticketingModelEnrollment->getError();

			if (!empty($error))
			{
				// Unsuccessful enrollments
				$exceptions[] = Text::sprintf('COM_JTICKETING_ENROLLMENT_ERROR_MESSAGES', $name, $title, $error);
			}
			else
			{
				$waitlistFormData['id']      = $waitlistId;
				$waitlistFormData['status']  = 'C';
				$waitlistFormData['eventId'] = $event->id;
				$waitlistFormData['userId']  = $waitlistData->user_id;

				$saveResult = $model->save($waitlistFormData);

				if (!empty($saveResult))
				{
					// Successful enrollments
					$error = Text::_("COM_JTICKETING_ENROLLMENT_SUCCESS_MESSAGE_SINGULAR");
					$exceptions[] = Text::sprintf('COM_JTICKETING_ENROLLMENT_ERROR_MESSAGES', $name, $title, $error);
				}
			}
		}

		$failureLog = '';

		if (count($exceptions))
		{
			$failureLog  = Text::_("COM_JTICKETING_FAILURE_LOG");
			$enrollmentTitleMessage = Text::sprintf('COM_JTICKETING_ENROLLMENT_MESSAGE', $successCount, $alreadyAddedCount, $failureCount, '');

			// Error handling & log writing
			$jticketingModelEnrollment->writeEnrollmentLog($exceptions, $enrollmentTitleMessage);
		}

		$taskLink = 'index.php?option=com_jticketing&view=enrollment&task=enrollment.jtEnrollmentDownloadLog';
		$file = '<b><a target="_blank" href="' . $taskLink . '" >' . $failureLog . '</a></b>';
		$enrollmentErrorLogMsg = Text::sprintf('COM_JTICKETING_ENROLLMENT_MESSAGE', $successCount, $alreadyAddedCount, $failureCount, $file);

		$app->enqueueMessage($enrollmentErrorLogMsg, 'Notice');
		$app->redirect(Route::_('index.php?option=com_jticketing&view=waitinglist', false));
	}

	/**
	 * The function is used to process waitlist
	 *
	 * @return  false
	 */
	public function processWaitlistQueue()
	{
		Factory::getLanguage()->load('lib_techjoomla', JPATH_SITE, null, false, true);
		$app   = Factory::getApplication();
		$input = $app->input;

		$comParams = ComponentHelper::getParams('com_jticketing');
		$enableWaitingList = $comParams->get('enable_waiting_list', '', 'STRING');

		$pkeyForWaitlistCron = $comParams->get("waitlistcron_key");
		$privateKeyInUrl     = $input->get('pkey', '', 'STRING');

		if ($pkeyForWaitlistCron != $privateKeyInUrl)
		{
			echo Text::_('COM_JTICKETING_WAITINGLIST_CRON_AUTHORIZATION');

			return;
		}

		BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_jticketing/models');
		$jticketingModelEnrollment = BaseDatabaseModel::getInstance('Enrollment', 'JticketingModel');

		$results = array();

		if ($enableWaitingList == 'none')
		{
			Factory::getApplication()->enqueueMessage(Text::_('COM_JTICKETING_AUTO_ADVANCE_WAITINGLIST_FOR_CLASSROOM_TRAINING'));

			return false;
		}

		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('DISTINCT waiting.event_id');
		$query->from($db->quoteName('#__jticketing_waiting_list', 'waiting'));
		$db->setQuery($query);
		$events = $db->loadAssocList();

		if (empty($events))
		{
			Factory::getApplication()->enqueueMessage(Text::_('COM_JTICKETING_MEETING_CRON_DATA_EMPTY'));

			return false;
		}

		foreach ($events as $key => $event)
		{
			// Get event specific waitlisted info
			$waitlistedData = self::getWaitlistedEventInfo($event['event_id']);

			if (!empty($waitlistedData))
			{
				// Proceed waiting list queue
				$results[] = self::processWaitlistQueuePerEvent($event['event_id'], $waitlistedData);
			}
		}

		$logTitle = Text::_('COM_JTICKETING_WAITING_LIST_LOG');

		foreach ($results as $result)
		{
			if (count($result))
			{
				// Error handling & log writing
				$jticketingModelEnrollment->writeEnrollmentLog($result, $logTitle);
			}

			$message = implode('<br>', $result);
			Factory::getApplication()->enqueueMessage($message);
		}
	}

	/**
	 * This function used to get waitlisted event information
	 *
	 * @param   INT  $eventXrefId  event xred if
	 *
	 * @return  Array|null  waitlisted event data list
	 */

	public static function getWaitlistedEventInfo($eventXrefId)
	{
		$db = Factory::getDbo();
		$currentDate = HTMLHelper::date($input = 'now', 'Y-m-d H:i:s', false);
		$event = JT::event()->loadByIntegration($eventXrefId);

		if (!empty($event->id))
		{
			if ($currentDate < $event->getStartDate())
			{
				$query2  = $db->getQuery(true);
				$query2->select(array('wait.*'));
				$query2->from($db->quoteName('#__jticketing_waiting_list', 'wait'));
				$query2->where($db->quoteName('wait.event_id') . '= ' . $db->quote($eventXrefId));
				$query2->where($db->quoteName('wait.status') . '= ' . $db->quote('WL'));
				$query2->order('wait.created_date ASC');

				$db->setQuery($query2);
				$waitinglistData = $db->loadObjectList();

				return $waitinglistData;
			}
		}
	}

	/**
	 * The function used to proceed waitlisted queue
	 *
	 * @param   INT     $xrefId               event xref id
	 * @param   OBJECT  $waitlistedUsersData  waitlisted user data
	 *
	 * @return  array   exceptions
	 */
	private static function processWaitlistQueuePerEvent($xrefId, $waitlistedUsersData)
	{
		$comParams   = ComponentHelper::getParams('com_jticketing');
		$autoAdvanceWaitingList = $comParams->get('auto_advance_waiting_list');

		$exceptions = array();

		foreach ($waitlistedUsersData as $key1 => $waitingUserData)
		{
			if ($waitingUserData->behaviour == 'E-commerce' || (empty($autoAdvanceWaitingList) && $waitingUserData->behaviour == 'classroom_training'))
			{
				JLoader::register('JticketingMailHelper', JPATH_SITE . '/components/com_jticketing/helpers/mail.php');

				$seatsAvailableStatus = 1;
				$result = JticketingMailHelper::waitinglistMail($waitingUserData->id, $seatsAvailableStatus);

				$exceptions[] = $result['message'];
			}
			else
			{
				$exceptions[] = self::clearWaitlist($xrefId, $waitingUserData);
			}
		}

		return $exceptions;
	}

	/**
	 * The function used to clear waitlist
	 *
	 * @param   INT     $xrefId           event xref id
	 * @param   OBJECT  $waitingUserData  single waitlisted user data
	 *
	 * @return  string|null  return message
	 */
	public static function clearWaitlist($xrefId, $waitingUserData)
	{
		$event = JT::event()->loadByIntegration($xrefId);

		BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_jticketing/models');
		$waitlistFormModel = BaseDatabaseModel::getInstance('WaitlistForm', 'JTicketingModel');

		BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_jticketing/models');
		$jticketingModelEnrollment = BaseDatabaseModel::getInstance('Enrollment', 'JticketingModel');

		$exceptions = '';
		$data = array();
		$data['userId']  = $waitingUserData->user_id;
		$data['eventId'] = $event->id;

		if (!empty($data))
		{
			$data['notify'] = 1;
			$jticketingModelEnrollment->save($data);

			$name  = Factory::getUser($waitingUserData->user_id)->name;
			$title = $event->getTitle();
			$error = $jticketingModelEnrollment->getError();

			if (!empty($error))
			{
				// Unsuccessful enrollments
				$exceptions = Text::sprintf('COM_JTICKETING_ENROLLMENT_ERROR_MESSAGES', $name, $title, $error);
			}
			else
			{
				$waitlistFormData            = array();
				$waitlistFormData['id']      = $waitingUserData->id;
				$waitlistFormData['status']  = 'C';
				$waitlistFormData['eventId'] = $event->id;
				$waitlistFormData['userId']  = $waitingUserData->user_id;

				$waitlistFormModel->save($waitlistFormData);
				$error = $waitlistFormModel->getError();

				if (empty($error))
				{
					$error = Text::_("COM_JTICKETING_ENROLLMENT_SUCCESS_MESSAGE_SINGULAR");
					$exceptions = Text::sprintf('COM_JTICKETING_ENROLLMENT_ERROR_MESSAGES', $name, $title, $error);
				}
			}

			return $exceptions;
		}
	}
}
