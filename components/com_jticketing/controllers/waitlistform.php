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

use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Component\ComponentHelper;
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
	public function save()
	{
		Session::checkToken() or Session::checkToken('get') or jexit('Invalid Token');
		$app   = Factory::getApplication();
		$user  = Factory::getUser();
		$params        = JT::config();

		// Validate user login.
		if (empty($user->id))
		{
			$msg = Text::_('COM_JTICKETING_MESSAGE_LOGIN_FIRST');

			$redirectUrl = $app->getInput()->get('redirectUrl', '', 'STRING');

			$eventId     = $app->getInput()->get('eventid', '0', 'INT');
			$waitinglistLink = Uri::root() . 'index.php?option=com_jticketing&task=waitlistform.refreshSaveToken&eventid=' .
						$eventId . '&redirectUrl=' . $redirectUrl;

			$url     = base64_encode($waitinglistLink);
			$app->enqueueMessage($msg);
			$app->redirect(Route::_('index.php?option=com_users&view=login&return=' . $url, false));
		}

		Session::checkToken() or Session::checkToken('get') or jexit('Invalid Token');

		$eventId     = $app->getInput()->get('eventid', '0', 'INT');
		$redirectUrl = $app->getInput()->get('redirectUrl', '', 'STRING');
		$id          = $app->getInput()->get('id', '', 'INT');
		$status      = $app->getInput()->get('status', '', 'STRING');
		$userId      = $app->getInput()->get('userid', '', 'INT');

		// Check permissions here
		$canEnrollAll = $user->authorise('core.enrollall', 'com_jticketing');
		$canEnrollOwn = $user->authorise('core.enrollown', 'com_jticketing');
		$canEnroll    = $user->authorise('core.enroll', 'com_jticketing');

		// Get Jticketing config/params
		$com_params = ComponentHelper::getParams('com_jticketing');
		$enableWaitingList = $com_params->get('enable_waiting_list');

		$data = array();

		if (!empty($eventId))
		{
			$data['eventId'] = $eventId;
		}

		$data['userId']   = $user->id;

		if (!empty($userId))
		{
			$data['userId'] = $userId;
		}

		if (!empty($id))
		{
			$data['id'] = $id;
		}

		$data['status']    = 'WL';

		if (!empty($status))
		{
			$data['status'] = $status;
		}

		$data['behaviour'] = 'E-commerce';

		if (($canEnrollAll || $canEnrollOwn || $canEnroll) && ($enableWaitingList == 'classroom_training'))
		{
			$data['behaviour'] = 'classroom_training';
		}

		if (empty($data))
		{
			return false;
		}

		$waitlistformModel = $this->getModel('waitlistform');
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

			if ($redirectUrl)
			{
				$redirectUrl = base64_decode($redirectUrl);

				$app->enqueueMessage(Text::_('COM_JTICKETING_WAITING_LIST_SUCCESS_MSG'), 'success');
				$app->redirect($redirectUrl);
			}
			elseif (empty($redirectUrl))
			{
				// In case redirect url is not set, redirect user to homepage
				$app->enqueueMessage(Text::_('COM_JTICKETING_WAITING_LIST_SUCCESS_MSG'), 'success');
				$app->redirect(Uri::root());
			}
			else
			{
				$app->enqueueMessage(Text::_('COM_JTICKETING_WAITING_LIST_SUCCESS_MSG'), 'success');
				$app->redirect(Route::_('index.php?option=com_jticketing&view=event&layout=edit&id=' . $eventId, false));
			}
		}

		if (!empty($error))
		{
			$app->enqueueMessage($error, 'error');

			if ($redirectUrl)
			{
				$redirectUrl = base64_decode($redirectUrl);

				$app->redirect($redirectUrl);
			}
			elseif (empty($redirectUrl))
			{
				// In case redirect url is not set, redirect user to homepage
				$app->redirect(Uri::root());
			}
			else
			{
				$app->redirect(Route::_('index.php?option=com_jticketing&view=event&layout=edit&id=' . $eventId, false));
			}
		}
	}

	/**
	 * save function to refresh token
	 *
	 * @return  boolean|void  Incase of error boolean and in case of success void
	 *
	 * @since   2.4.3
	 */
	public function refreshSaveToken()
	{
		$app   = Factory::getApplication();
		$redirectUrl = $app->getInput()->get('redirectUrl', '', 'STRING');
		$eventId     = $app->getInput()->get('eventid', '0', 'INT');

		$waitinglistLink = Route::_('index.php?option=com_jticketing&task=waitlistform.save&id=0&eventid=' .
						$eventId . '&redirectUrl=' . $redirectUrl, false
						);

		$session = Factory::getSession();

		$waitinglistLink .= '&' . Session::getFormToken() . '=1';

		$app->redirect($waitinglistLink);
	}
}
