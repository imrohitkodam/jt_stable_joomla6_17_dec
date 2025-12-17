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
use Joomla\CMS\Session\Session;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Response\JsonResponse;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\MVC\Controller\AdminController;

/**
 * Jtickeing waiting list form controller.
 *
 * @since  2.1
 */
class JTicketingControllerWaitlistForm extends AdminController
{
	/**
	 * save function to add user to waitlist
	 *
	 * @return  boolean|void  Incase of error boolean and in case of success void
	 *
	 * @since   2.1
	 */
	public function changeStatus()
	{
		$app = Factory::getApplication();

		if (!Session::checkToken('get'))
		{
			$app->enqueueMessage(Text::_('JINVALID_TOKEN'), 'error');
			echo new JsonResponse;
			$app->close();
		}

		$user  = Factory::getUser();
		$input = $app->input;

		$eventId = $input->post->get('eventid', '', 'INT');
		$id      = $input->post->get('id', '', 'INT');
		$status  = $input->post->get('status', '', 'STRING');
		$userId  = $input->post->get('userid', '', 'INT');

		$data = array();

		if (!empty($eventId))
		{
			$data['eventId'] = $eventId;
		}

		$data['userId'] = $user->id;

		if (!empty($userId))
		{
			$data['userId'] = $userId;
		}

		if (!empty($id))
		{
			$data['id'] = $id;
		}

		$data['status'] = 'WL';

		if (!empty($status))
		{
			$data['status'] = $status;
		}

		if (empty($data))
		{
			return false;
		}

		if (file_exists(JPATH_SITE . '/components/com_jticketing/models/waitlistform.php')) { require_once JPATH_SITE . '/components/com_jticketing/models/waitlistform.php'; }
		$waitlistformModel = BaseDatabaseModel::getInstance('WaitlistForm', 'JTicketingModel');
		$waitlistId        = $waitlistformModel->save($data);
		$error             = $waitlistformModel->getError();

		if (!empty($waitlistId) && empty($error))
		{
			JLoader::register('JticketingMailHelper', JPATH_SITE . '/components/com_jticketing/helpers/mail.php');

			if ($data['status'] == 'WL')
			{
				$status = 0;
				JticketingMailHelper::waitinglistMail($waitlistId, $status);
			}

			echo new JResponseJson($waitlistId, Text::_('COM_JTICKETING_WAITING_LIST_STATUS_CHANGE_MSG'));
		}

		if (!empty($error))
		{
			echo new JsonResponse($error, $error, true);
		}
	}
}
