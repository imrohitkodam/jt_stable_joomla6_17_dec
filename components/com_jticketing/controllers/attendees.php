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
use Joomla\CMS\Log\Log;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\User\User;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\Filesystem\File;
use Joomla\CMS\Session\Session;
use Joomla\CMS\User\UserHelper;
use Joomla\Filesystem\Folder;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Response\JsonResponse;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\MVC\Controller\AdminController;

if (file_exists(JPATH_SITE . '/components/com_jticketing/events/attendee.php')) { require_once JPATH_SITE . '/components/com_jticketing/events/attendee.php'; }
if (file_exists(JPATH_SITE . '/components/com_jticketing/models/enrollment.php')) { require_once JPATH_SITE . '/components/com_jticketing/models/enrollment.php'; }
if (file_exists(JPATH_ADMINISTRATOR . '/components/com_users/models/user.php')) { require_once JPATH_ADMINISTRATOR . '/components/com_users/models/user.php'; }

/**
 * Enrollments list controller class.
 *
 * @since  2.1
 */
class JticketingControllerAttendees extends AdminController
{
	public $jtModelEnrollment = null;

	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     JControllerLegacy
	 * @since   2.1
	 */
	public function __construct($config = array())
	{
		$this->jtModelEnrollment = new JticketingModelEnrollment;

		$lang         = Factory::getLanguage();
		$extension    = 'com_jticketing';
		$base_dir     = JPATH_ADMINISTRATOR;
		$language_tag = '';
		$reload       = true;
		$lang->load($extension, $base_dir, $language_tag, $reload);

		parent::__construct($config);
	}

	/**
	 * Method to do Update approval
	 *
	 * @return  void
	 *
	 * @since   2.1
	 */
	public function update()
	{
		Session::checkToken() or Session::checkToken('get') or jexit('Invalid Token');

		// Check for permission to enrollment
		$app                 = Factory::getApplication();
		$user                = Factory::getUser();
		$post                = $app->getInput()->post;
		$notify              = $post->get('notify', false);
		$data                = array();

		// Enrollment id
		$id = $post->get('id', 0, 'INT');

		// Actual event id
		$eid = $post->get('eid', 0, 'INT');

		// Check permissions here
		$isAuthorisedEnroll = $user->authorise('core.enroll', 'com_jticketing.event' . (int) $eid);

		if (!$isAuthorisedEnroll)
		{
			$app->enqueueMessage(Text::_('COM_JTICKETING_NOT_AUTHORIED'), 'Error');
			echo new JsonResponse('', '', true);
			jexit();
		}

		if ($id)
		{
			$data['id'] = $id;
			$data['status'] = $post->get('value', 'P', 'STRING');
			$data['notify'] = $notify;

			// Call enrollment model to update enrollment entries

			$this->jtModelEnrollment->update($data);
		}
		else
		{
			$app->enqueueMessage(Text::_('COM_JTICKETING_ENROLLMENT_SOMETHING_IS_WRONG'), 'error');
		}

		echo new JsonResponse;

		jexit();
	}

	/**
	 * Method to checkin for ticket
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function checkin()
	{
		$input = Factory::getApplication()->getInput();

		// Get some variables from the request
		$attendeeIds = $input->get('cid', array(), 'post', 'array');
		$success = 0;

		$attendeeCount = count($attendeeIds);

		foreach ($attendeeIds as $attendeeId)
		{
			$notify = $input->get('notify_user_' . $attendeeId, '', 'post', 'STRING');

			// As model set ID in state have to create model instance in foreach
			$checkinModel = $this->getModel('checkin');
			$data = array();
			$data['attendee_id'] = $attendeeId;
			$data['state'] = 1;
			$data['notify'] = $notify;

			if ($checkinModel->save($data))
			{
				$success ++;
			}
		}

		if ($success == $attendeeCount)
		{
			$msg = Text::_('COM_JTICKETING_CHECKIN_SUCCESS_MSG');

			$this->setRedirect(Route::_('index.php?option=com_jticketing&view=attendees', false), $msg);
		}
		else
		{
			$msg = $checkinModel->getError();

			$this->setRedirect(Route::_('index.php?option=com_jticketing&view=attendees', false), $msg, 'error');
		}
	}

	/**
	 * Method to undo checkin for ticket
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function undochekin()
	{
		$input = Factory::getApplication()->getInput();

		// Get some variables from the request
		$attendeeIds = $input->get('cid', array(), 'post', 'array');
		$success = 0;
		$attendeeCount = count($attendeeIds);
		$checkinModel = $this->getModel('checkin');

		foreach ($attendeeIds as $attendeeId)
		{
			$data = array();
			$data['attendee_id'] = $attendeeId;
			$data['state'] = 0;

			if ($checkinModel->save($data))
			{
				$success++;
			}
		}

		if ($success == $attendeeCount)
		{
			$msg = Text::_('COM_JTICKETING_CHECKIN_FAIL_MSG');
		}
		else
		{
			$msg = $checkinModel->getError();
		}

		$this->setRedirect(Route::_('index.php?option=com_jticketing&view=attendees', false), $msg);
	}

	/**
	 * Method to checkin for ticket
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function emailtoSelected()
	{
		$mainframe = Factory::getApplication();
		$input     = $mainframe->input;
		$session   = Factory::getSession();

		$subject = $input->get('jt-message-subject', '', 'POST', 'STRING');
		$body = $input->get('jt-message-body', '', 'post', 'string', JREQUEST_ALLOWHTML);

		$img_path     = 'img src="' . Uri::root();
		$res = new stdclass;
		$res->content = str_replace('img src="' . Uri::root(), 'img src="', $body);
		$res->content = str_replace('img src="', $img_path, $res->content);
		$res->content = str_replace("background: url('" . Uri::root(), "background: url('", $res->content);
		$res->content = str_replace("background: url('", "background: url('" . Uri::root(), $res->content);

		$attendeeIds = $session->get('selected_order_item_ids');
		require_once JPATH_SITE . '/components/com_jticketing/models/attendees.php';
		$attendeesModel = new JticketingModelAttendees;
		$selectedEmails = $attendeesModel->getAttendeeEmail($attendeeIds);

		$cid   = array_unique($selectedEmails);
		$model = $this->getModel('attendees');
		$msg   = Text::_('COM_JTICKETING_EMAIL_SUCCESSFUL');

		if ($model->emailtoSelected($cid, $subject, $body, $attachmentPath))
		{
			$msg = Text::_('COM_JTICKETING_EMAIL_SUCCESSFUL');
			$mainframe->enqueueMessage($msg, 'success');
		}
		else
		{
			$msg = $model->getError();
			$mainframe->enqueueMessage($msg, 'error');
		}


		$mainframe->redirect(Route::_(Uri::base() . 'index.php?option=com_jticketing&view=attendees'), false);
	}

	/**
	 * Method to redirect to contact us view
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function cancelEmail()
	{
		$mainframe = Factory::getApplication();
		$contact_ink = Route::_(Uri::base() . 'index.php?option=com_jticketing&view=attendees');
		$mainframe->redirect($contact_ink);
	}

	/**
	 * Method to redirect to contact us view
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function redirectforEmail()
	{
		$mainframe = Factory::getApplication();
		$input = Factory::getApplication()->getInput();
		$cids	= $input->get('cid', '', 'POST', 'ARRAY');
		$session =& Factory::getSession();
		$session->set('selected_order_item_ids', $cids);
		$session->get('selected_order_item_ids');
		$contact_link = Route::_(Uri::base() . 'index.php?option=com_jticketing&view=attendees&layout=contactus');
		$mainframe->redirect($contact_link);
	}

	/**
	 * CSV file data store in entroll table of Tjlms.
	 *
	 * @return  void
	 *
	 * @since   3.1.0
	 */
	public function csvImport()
	{
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');
		$logFileName = 'com_jticketing.manage_enrollment_import_' . Factory::getDate() . '.log';

		Log::addLogger(array('text_file' => $logFileName), Log::ALL, array('com_jticketing'));
		$oluser_id = Factory::getUser()->id;

		// Set log file name to session
		$session     = Factory::getSession();
		$session->set('enrollment_filename', $logFileName);

		$ret = array();

		/* If user is not logged in*/
		if (!$oluser_id)
		{
			$ret['OUTPUT']['flag'] = 0;
			$ret['OUTPUT']['msg'] = Text::_('COM_JTICKETING_MUST_LOGIN_TO_UPLOAD');
			echo json_encode($ret);
			jexit();
		}

		$input = Factory::getApplication()->getInput();
		$files = $input->files;
		$post = $input->post;

		$file_to_upload = $files->get('FileInput', '', 'ARRAY');
		$notify_user = $post->get('notifyUser', '', 'STRING');
		$notify_user = ($notify_user == 'true') ? 1 : 0;

		/* Save csv content to question table */
		$result = $this->saveCsvContent($file_to_upload, $notify_user);

		$ret['OUTPUT'] = $result;
		echo json_encode($ret);
		jexit();
	}

	/**
	 * Enroll attendee to respective events from csv
	 *
	 * @param   MIXED   $file_to_upload  file object
	 * @param   STRING  $notify_user     notify user
	 *
	 * @return  ARRAY
	 *
	 * @since   3.1.0
	 */
	public function saveCsvContent($file_to_upload, $notify_user)
	{
		// Default usergroup for newly registered user
		$params = ComponentHelper::getParams('com_users');
		$this->defaultUserGroup = $params->get('new_usertype');

		// Initialize values
		$tzList = DateTimeZone::listIdentifiers(DateTimeZone::ALL);
		$headerRow  = true;
		$messages   = $invalidEvents = $badGroups = array();
		$newUsers = $badUserAccess = $missingDetails = $notCreateUsers = $invalidRows = $updateUsers = $badTimeZoneCnt = $lineno = 0;
		$alreadyEnrolledCnt = $enrollSuccess = 0;
		$userId = 0;
		$logLink = '';
		$output = array('return' => 1, 'messages' => array());

		$csvFileName = $file_to_upload['name'];
		Log::add(Text::sprintf('COM_JTICKETING_MANAGEENROLLMENTS_LOG_CSV_FILE_NAME', $csvFileName), Log::INFO, 'com_jticketing');
		Log::add(Text::_("COM_JTICKETING_MANAGEENROLLMENTS_LOG_CSV_START"), Log::INFO, 'com_jticketing');

		$handle = fopen($file_to_upload['tmp_name'], 'r');
		$userFieldsName = array();

		while (($data = fgetcsv($handle)) !== false)
		{
			if ($headerRow)
			{
				$lineno++;

				// Parsing the CSV header
				$headers = array();
				$userFieldsName = array();

				foreach ($data as $d)
				{
					$pattern     = "/U_fields/";

					if (preg_match($pattern, $d))
					{
						$userReplaceHead = preg_replace($pattern, " ", $d);
					}
					
					$header = strtolower(preg_replace('/[^A-Za-z0-9\-]/', '', $d));

					if (isset($userReplaceHead) && $userReplaceHead)
					{
						if (preg_match('/[\[\]\']/', $userReplaceHead))
						{
							$userFieldsName[] = trim(str_replace(array('[', ']'), '', $userReplaceHead));
							$header           = $userReplaceHead;
						}
					}

					$headers[] = $header;
				}

				$headerRow = false;
			}
			elseif (count($headers) == count($data))
			{
				$data = array_map("trim", $data);
				$userData[] = array_combine($headers, $data);
			}
			else
			{
				$invalidRows++;
			}
		}

		if (empty($userData))
		{
			array_push($messages, array('error' => Text::_('COM_JTICKETING_TITLE_MANAGEENROLLMENTS_IMPORT_BLANK_FILE')));
			$output['messages'] = $messages;

			return $output;
		}

		if (!empty($userFieldsName))
		{
			$fieldIds = array();

			foreach ($userFieldsName as $fieldName)
			{
				$field = $this->checkFieldExist($fieldName, 'com_users.user');

				if ($field)
				{
					$fieldIds[$fieldName] = $field['id'];
				}
				else
				{
					array_push($messages, array('error' => Text::sprintf('COM_JTICKETING_TITLE_MANAGEENROLLMENTS_IMPORT_FIELD_NAME_NOT_EXIST', $fieldName)));
					$output['messages'] = $messages;

					return $output;
				}
			}
		}

		$optionalKeys = array('id', 'firstname', 'lastname', 'username', 'addgroups', 'removegroups' , 'timezone');
		$missingKeys  = array_diff($optionalKeys, $headers);
		$dummyData  = array_combine($missingKeys, array_fill(0, count($missingKeys), ""));
		$totalUser    = count($userData);

		// Create enrolment instance
		BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_jticketing/models');
		$jticketingModelEnrollment = BaseDatabaseModel::getInstance('Enrollment', 'JticketingModel');

		foreach ($userData as $eachUser)
		{
			$updateUsersFlag = 0;
			$lineno++;

			foreach ($eachUser as $i => $eUser)
			{
				$pattern     = "/ufields/";
				$userReplaceHead = preg_replace($pattern, " ", $i);

				if (preg_match('/[\[\]\'^£$%&*()}{@#~?><>,|=_+¬-]/', $userReplaceHead))
				{
					$userFieldName              = trim(str_replace (array('[', ']'), '' , $userReplaceHead));
					$userFieldArray[$userFieldName] = $eUser;
				}
			}

			if (!empty($eachUser['timezone']))
			{
				$timezone = array_map('trim', explode("/", $eachUser['timezone']));
				$eachUser['timezone'] = implode("/", array_map('ucwords', $timezone));

				if (in_array($eachUser['timezone'], $tzList))
				{
					$eachUser['params']['timezone'] = $eachUser['timezone'];
				}
				else
				{
					$msg = "COM_JTICKETING_MANAGEENROLLMENTS_BAD_TIMEZONE_USER";
					Log::add(Text::sprintf($msg, $lineno), Log::ERROR, 'com_jticketing');
					$badTimeZoneCnt++;
					continue;
				}
			}

			// Avoid warning for missing keys
			$eachUser = array_merge($eachUser, $dummyData);
			$identifier = '';

			if (!empty($eachUser['usermatchkey']))
			{
				$identifier = $eachUser['usermatchkey'];
			}

			if (!$identifier || $identifier == 'id')
			{
				$userId = $eachUser['id'];
			}
			else
			{
				if (!in_array($identifier, array('id', 'email', 'username')))
				{
					array_push($messages, array('error' => Text::_('COM_JTICKETING_CSV_IMPORT_COLUMN_MISSING')));
					$output['messages'] = $messages;

					return $output;
				}

				if ($identifier == 'username')
				{
					$userId = UserHelper::getUserId($eachUser['username']);
				}
				elseif ($identifier == 'email')
				{
					$userId = $this->getValidateUser($eachUser['email']);
				}
			}

			$email  = $eachUser['email'];

			if (!empty($userId))
			{
				$user = Factory::getUser($userId);
				$userId = $user->id;
			}

			if (empty($userId) && (empty($email)))
			{
				$missingDetails++;
				continue;
			}

			if ($userId)
			{
				$updateUsersFlag = 1;
			}

			if (!empty($userFieldArray))
			{
				$eachUser['com_fields'] = $userFieldArray;
			}

			$eachUser['id'] = $userId;
			$model = $this->getModel('attendees');
			$userId  = $model->createUpdateUser($eachUser);

			if (empty($userId))
			{
				Log::add(Text::sprintf("COM_JTICKETING_MANAGEENROLLMENTS_BAD_USER", $email), Log::INFO, 'com_jticketing');
				$notCreateUsers++;
				continue;
			}
			else
			{
				if ($updateUsersFlag == 1)
				{
					$updateUsers++;
				}
				else
				{
					$newUsers++;
				}
			}

			// User validation ended
			$addevents = $eachUser['addevents'];

			if (!empty($addevents))
			{
				$eventIds = explode("|", $addevents);

				// Enrollment starts here
				foreach ($eventIds as $eventId)
				{
					if ($eventId)
					{
						// In csv file we given event Id so we change the code acording to that
						// $event   = JT::event()->loadByIntegration($eventId);
						$event   = JT::event($eventId);

						if (empty($event->id))
						{
							Log::add(Text::sprintf("COM_JTICKETING_MANAGEENROLLMENTS_INVALID_EVENT", $eventId), Log::INFO, 'com_jticketing');
							$invalidEvents[$eventId] = $eventId;

							continue;
						}
					}

					$alreadyEnrolled = $jticketingModelEnrollment->isAlreadyEnrolled($event->id, $userId);
					$assignmentMsg = 0;

					if (!$alreadyEnrolled)
					{
						$data['userId'] = $userId;
						$data['eventId'] = $eventId;

						if ($notify_user == 1)
						{
							$data['notify'] = $notify_user;
						}

						$successfulEnroled = $jticketingModelEnrollment->save($data);

						if ($successfulEnroled)
						{
							Log::add(Text::sprintf("COM_JTICKETING_MANAGEENROLLMENTS_LOG_NEW_ENROLL", $userId, $eventId), Log::INFO, 'com_jticketing');
							$enrollSuccess ++;
						}
						else
						{
							Log::add(Text::sprintf("COM_JTICKETING_MANAGEENROLLMENTS_LOG_BAD_ACCESS", $userId, $eventId), Log::INFO, 'com_jticketing');
							$badUserAccess++;
						}
					}
					elseif (!$assignmentMsg)
					{
						Log::add(Text::sprintf("COM_JTICKETING_MANAGEENROLLMENTS_LOG_ALREADY_ENROLL", $userId, $eventId), Log::INFO, 'com_jticketing');
						$alreadyEnrolledCnt ++;
					}
				}
			}
		}

		Log::add(Text::_("COM_JTICKETING_MANAGEENROLLMENTS_LOG_CSV_END"), Log::INFO, 'com_jticketing');

		// Log file Path
		$logFilepath = Route::_('index.php?option=com_jticketing&view=attendees&task=downloadLog&prefix=enrollment');

		$session  = Factory::getSession();
		$config   = Factory::getConfig();
		$filename = $session->get('enrollment_filename');
		$logfile  = $config->get('log_path') . '/' . $filename;

		if (File::exists($logfile))
		{
			$logLink = '<a href="' . $logFilepath . '" >' . Text::_("COM_JTICKETING_ENROLLMENT_CSV_SAMPLE") . '</a>';
			$logLink =	Text::sprintf('COM_JTICKETING_LOG_FILE_PATH', $logLink);
		}

		// Handle Messages
		$message = Text::sprintf('COM_JTICKETING_MANAGEENROLLMENTS_IMPORT_TOTAL_ROWS_CNT_MSG', $totalUser) . ' ' . $logLink;
		array_push($messages, array('success' => $message));

		if ($missingDetails > 0)
		{
			$message = ($missingDetails == 1) ? 'COM_JTICKETING_MANAGEENROLLMENTS_MANDATORY_FIELDS_ONE' : 'COM_JTICKETING_MANAGEENROLLMENTS_MANDATORY_FIELDS';
			array_push($messages, array('error' => Text::sprintf($message, $missingDetails)));
		}

		if ($badUserAccess > 0)
		{
			$message = 'COM_JTICKETING_TITLE_MANAGEENROLLMENTS_IMPORT_BAD_USER_ACCESS';
			array_push($messages, array('error' => Text::sprintf($message, $badUserAccess)));
		}

		if ($newUsers > 0)
		{
			$message = ($newUsers == 1) ? 'COM_JTICKETING_TITLE_MANAGEENROLLMENTS_IMPORT_NEW_USER_MSG' :
			'COM_JTICKETING_TITLE_MANAGEENROLLMENTS_IMPORT_NEW_USERS_MSG';
			array_push($messages, array('success' => Text::sprintf($message, $newUsers)));
		}

		if ($updateUsers > 0)
		{
			$message = ($updateUsers == 1) ? 'COM_JTICKETING_TITLE_MANAGEENROLLMENTS_IMPORT_UPDATE_USER_MSG' :
			'COM_JTICKETING_TITLE_MANAGEENROLLMENTS_IMPORT_UPDATE_USERS_MSG';
			array_push($messages, array('success' => Text::sprintf($message, $updateUsers)));
		}

		if ($notCreateUsers > 0)
		{
			$message = ($notCreateUsers == 1) ? 'COM_JTICKETING_MANAGEENROLLMENTS_BAD_USERDATA' : 'COM_JTICKETING_MANAGEENROLLMENTS_BAD_USERDATA';
			array_push($messages, array('error' => Text::sprintf($message, $notCreateUsers)));
		}

		if ($enrollSuccess > 0)
		{
			$message = ($enrollSuccess == 1) ?
				'COM_JTICKETING_TITLE_MANAGEENROLLMENTS_IMPORT_NEWLY_SINGLE_USER_ENROLLED_MSG' :
				'COM_JTICKETING_TITLE_MANAGEENROLLMENTS_IMPORT_NEWLY_ENROLLED_MSG';
			array_push($messages, array('success' => Text::sprintf($message, $enrollSuccess)));
		}

		if ($alreadyEnrolledCnt > 0)
		{
			$message = ($alreadyEnrolledCnt == 1) ?
				'COM_JTICKETING_TITLE_MANAGEENROLLMENTS_IMPORT_ALREADY_ENROLLED_MSG_ONE' :
				'COM_JTICKETING_TITLE_MANAGEENROLLMENTS_IMPORT_ALREADY_ENROLLED_MSG';
			array_push($messages, array('notice' => Text::sprintf($message, $alreadyEnrolledCnt)));
		}

		$badevents = count($invalidEvents);

		if ($badevents > 0)
		{
			$message = ($badevents == 1) ? 'COM_JTICKETING_MANAGEENROLLMENTS_BAD_EVENT_ID' : 'COM_JTICKETING_MANAGEENROLLMENTS_BAD_EVENT_IDS';
			array_push($messages, array('error' => Text::sprintf($message, implode(',', $invalidEvents))));
		}

		$badGroupsCnt = count($badGroups);

		if ($badGroupsCnt > 0)
		{
			$message = ($badGroupsCnt == 1) ? 'COM_JTICKETING_MANAGEENROLLMENTS_BAD_GROUP_ONE' : 'COM_JTICKETING_MANAGEENROLLMENTS_BAD_GROUP';
			array_push($messages, array('error' => Text::sprintf($message, implode(',', $badGroups))));
		}

		if ($badTimeZoneCnt > 0)
		{
			$message = ($badTimeZoneCnt == 1) ? 'COM_JTICKETING_MANAGEENROLLMENTS_BAD_TIMEZONE_ONE' : 'COM_JTICKETING_MANAGEENROLLMENTS_BAD_TIMEZONE';
			array_push($messages, array('error' => Text::sprintf($message, $badTimeZoneCnt)));
		}

		$output['messages'] = $messages;

		return $output;
	}

	/**
	 * getValidateId.
	 *
	 * @param   integer  $id  user id
	 *
	 * @return  int
	 *
	 * @since   3.1.2
	 */
	public function getValidateUser($id)
	{
		$userId = 0;

		if ($id)
		{
			$db    = Factory::getDbo();
			$query = $db->getQuery(true);

			$query->select($db->quoteName('id'));
			$query->from($db->quoteName('#__users'));
			$query->where($db->quoteName('id') . ' = ' . $db->quote($id) . 'OR' . $db->quoteName('email') . ' = ' . $db->quote($id));
			$db->setQuery($query);
			$result = $db->loadObject();

			if ($result)
			{
				$userId = $result->id;
			}
		}

		return $userId;
	}

	/**
	 * Check field exist in lms.
	 *
	 * @param   string  $fieldName  The field name.
	 *
	 * @param   string  $context  The context name.
	 *
	 * @return  integer  The field id or 0 if not found.
	 *
	 * @since   1.5.0
	 */
	public function checkFieldExist($fieldName, $context)
	{
		$checkedfield = 0;

		if (!empty($fieldName))
		{
			$db = Factory::getDbo();

			$query = $db->getQuery(true);
			$query->select('id');
			$query->from('`#__fields`');
			$query->where('name = "' . (string) $fieldName . '"');
			$query->where('context = "' . (string) $context . '"');

			$db->setQuery($query);

			$checkedfield = $db->loadAssoc();
		}

		return $checkedfield;
	}

	/**
	 * Function to download PDF
	 *
	 * @return  void
	 *
	 * @since  1.0.0
	 */
	public function downloadPDF()
	{
		$input    = Factory::getApplication()->getInput();
		$config = Factory::getConfig();
		$eventid  = $input->get('eventid', '', 'INT');
		$ticketid = $input->get('ticketid');

		$jticketingmainhelper = new jticketingmainhelper;

		$data    = $jticketingmainhelper->getticketDetails($eventid, $ticketid);
		$qr_path = '/media/com_jticketing/images/qr_code_' . Text::_("TICKET_PREFIX") . $data->attendee_id . '.png';

		$localFile_qr = JPATH_SITE . $qr_path;

		if (file_exists($localFile_qr))
		{
			unlink($localFile_qr);
		}

		$ticketFileName         = str_replace(" ", "_", $data->title);
		$ticketFileName         = str_replace("/", "", $data->title);
		$pdfname1          = 'Ticket_' . $ticketFileName . '_' . $data->attendee_id . ".pdf";
		$pdfname           = $config->get('tmp_path') . '/' . $pdfname1;
		$data->ticketprice = $data->totalamount;
		$data->nofotickets = $data->ticketscount;
		$data->totalprice  = $data->amount;
		$data->eid         = $eventid;
		$data->order_event_id = $data->order_event_id;
		$data->orderEventId = $data->order_event_id;
		$html              = $jticketingmainhelper->getticketHTML($data, $jticketing_usesess = 0);

		JT::model('Tickettypes')->generatepdf($html, $pdfname, 1);
	}

	/**
	 * This will find all online events this function call by cron
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function MarkAttendance()
	{
		// Initialize variables.
		$currentDate           = Factory::getDate()->toSql();
		$config			       = JT::config();
		$pkeyForMarkAttendance = $config->get('attendancecron_key', '');
		$app   				   = Factory::getApplication();
		$privateKeyInUrl       = $app->getInput()->get('pkey', '', 'STRING');
		$cronLimit 			   = $config->get('cron_limit');

		if ($pkeyForMarkAttendance != $privateKeyInUrl)
		{
			echo Text::_('COM_JTICKETING_ATTENDANCE_CRON_AUTHORIZATION');

			return;
		}

		$db           = Factory::getDbo();
		$query        = $db->getQuery(true);

		// Create the base select statement.
		$query->select((array('e.*')));
		$query->select($db->quoteName('x.id', 'xref'));
		$query->select($db->quoteName('v.params', 'venue_params'));
		$query->from($db->quoteName('#__jticketing_events', 'e'));
		$query->join('LEFT', $db->quoteName('#__jticketing_integration_xref', 'x') . 'ON (' .
							$db->quoteName('e.id') . '=' . $db->quoteName('x.eventid') . ')');
		$query->join('LEFT', $db->quoteName('#__jticketing_venues', 'v') . 'ON (' . $db->quoteName('e.venue') . '=' . $db->quoteName('v.id') . ')');

		$query->where($db->quoteName('e.enddate') . '<= ' . $db->quote($currentDate));
		$query->where($db->quoteName('e.state') . '=' . $db->quote('1'));
		$query->where($db->quoteName('e.online_events') . '=' . $db->quote('1'));
		$query->where($db->quoteName('x.source') . '= ' . $db->quote('com_jticketing'));
		$query->where($db->quoteName('x.cron_status') . '=' . $db->quote('0'));
		$db->setQuery($query, 0, $cronLimit);
		$onlineEvents = $db->loadAssocList();
		$onlineEvents = array_filter($onlineEvents);

		if (!empty($onlineEvents))
		{
			/*
			PluginHelper::importPlugin('tjevents');

			If SHIKA is installed
			if (Folder::exists(JPATH_SITE . '/components/com_tjlms'))
			{
				PluginHelper::importPlugin('tjevent');
			}*/

			foreach ($onlineEvents as $oevent)
			{
				$event = JT::event($oevent['id']);
				$resultAttendance = $event->getAttendance();

				if (!$resultAttendance)
				{
					$app->enqueueMessage($event->getError());

					continue;
				}

				if (!empty($resultAttendance))
				{
					// Create the base select statement.
					$attendeesModel = JT::model('attendees');

					foreach ($resultAttendance as $attendeeId => $result)
					{
						$spendTime  = gmdate('H:i:s', $result['spentTime']);

						$eventDetails = new StdClass;
						$eventDetails->eventId		= $event->integrationId;
						$eventDetails->checkintime	= $result['checkin'];
						$eventDetails->checkouttime	= $result['checkout'];
						$eventDetails->spendTime	= $spendTime;
						$uid = JT::attendee($attendeeId)->owner_id;

						$data = array(
							'attendee_id' => $attendeeId,
							'state'       => 1,
							'notify'       => 'on',
							'event_obj'   => $eventDetails,
							'isCron'      => true
							);

						$checkinModel = JT::model('checkin');

						if (!$checkinModel->save($data))
						{
							$app->enqueueMessage(Text::_('COM_JTICKETING_MEETING_ATTANDANCE_SAVING_FAIL'));
						}
					}
				}

				// Update cron status with run date
				$query = $db->getQuery(true);

				$query->update($db->quoteName('#__jticketing_integration_xref'));
				$query->set($db->quoteName('cron_date') . ' = ' . $db->quote($currentDate));
				$query->set($db->quoteName('cron_status') . ' = ' . $db->quote('1'));
				$query->where($db->quoteName('source') . ' = ' . $db->quote('com_jticketing'));
				$query->where($db->quoteName('eventid') . ' = ' . $db->quote($event->id));
				$query->where($db->quoteName('cron_status') . ' = ' . $db->quote('0'));

				$db->setQuery($query);

				if ($db->execute())
				{
					$app->enqueueMessage(Text::_('COM_JTICKETING_MEETING_CRON_SUCCESSFULLY'));
				}
			}
		}
		else
		{
			$app->enqueueMessage(Text::_('COM_JTICKETING_MEETING_CRON_DATA_EMPTY'));
		}
	}

	/**
	 * This will ensure only authorise user will get url send to mail
	 *
	 * @return  void
	 *
	 * @since   3.0.0
	 */
	public function getAuthorisedUrl()
	{
		$app      = Factory::getApplication();
		$input    = $app->input;
		$user     = Factory::getUser();
		$attendee = JT::attendee($input->get('attendeeId'));
		$event    = JT::event()->loadByIntegration($attendee->event_id);

		if (empty($user->id))
		{
			$url     = base64_encode(Uri::root() . $event->getUrl(false));
			$app->enqueueMessage(Text::_('COM_JTICKETING_PLEASE_LOGIN'));
			$app->redirect(Route::_('index.php?option=com_users&view=login&return=' . $url, false));
		}

		if ($attendee->owner_id != $user->id)
		{
			$app->enqueueMessage(Text::sprintf('COM_JTICKETING_NOT_PURCHASED', $event->getUrl(), $event->title), 'notice');

			return;
		}

		$url = $event->getJoinUrl($attendee);

		if (!$event->getJoinUrl($attendee))
		{
			$app->enqueueMessage($event->getError(), 'error');

			return;
		}

		$app->redirect($url);

		return;
	}

	/**
	 * This will delete the attendee if order is not present
	 *
	 * @return  void
	 *
	 * @since   4.0.2
	 */
	public function remove()
	{
		$app = Factory::getApplication();
		if (!Session::checkToken())
		{
			echo new JsonResponse(null, Text::_('JINVALID_TOKEN_NOTICE'), true);
			$app->close();
		}

		$model = $this->getModel('attendees');
		$attendeesId = $this->input->post->get('id', array(), 'array');

		$app        = Factory::getApplication();
		$db   = Factory::getDbo();
		$query        = $db->getQuery(true);

		$query->select($db->quoteName('attendee.id'));
		$query->select($db->quoteName('oitem.attendee_id'));
		$query->select($db->quoteName('order.id', 'order_id'));

		$query->from($db->quoteName('#__jticketing_attendees', 'attendee'));

		$query->join('LEFT', $db->qn('#__jticketing_order_items', 'oitem') . 'ON (' . $db->qn('oitem.attendee_id')
			. ' = ' . $db->qn('attendee.id') . ')');

		$query->join('LEFT', $db->qn('#__jticketing_order', 'order') . 'ON (' . $db->qn('order.id')
			. ' = ' . $db->qn('oitem.order_id') . ')');
		
		$query->where($db->qn('attendee.id'). '=' . (int) $attendeesId[0]);
		$db->setQuery($query);

		$result = $db->loadObject();

		if ($result->order_id)
		{
			echo new JsonResponse(null, Text::_('COM_JTICKETING_ATTENDEE_DELETED_ERROR'), true);
			$app->close();
		}

		if ($model->delete($attendeesId))
		{
			$msg = Text::_('COM_JTICKETING_ATTENDEE_DELETED_SCUSS');
		}
		else
		{
			$msg = Text::_('COM_JTICKETING_ATTENDEE_DELETED_ERROR');
		}

		echo new JsonResponse(null, $msg);
		$app->close();
	}
}
