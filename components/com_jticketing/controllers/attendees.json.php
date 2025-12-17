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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Response\JsonResponse;
use Joomla\CMS\MVC\Controller\AdminController;

/**
 * Jtickeing waiting list form controller.
 *
 * @since  2.1
 */
class JTicketingControllerAttendees extends AdminController
{


	/**
	 * Method to send emails to Attendees
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function emailtoSelected()
	{				
		$app            = Factory::getApplication();
		$input          = $app->input;

		$subject        = $input->post->get('messageSubject', '', 'POST', 'STRING');
		$body           = $input->post->get('messageBody', '','RAW');
		$isAdmin        = $input->post->get('isAdmin', '', 'POST', 'int');
		$emailIds       = $input->post->get('emailIds', '', 'POST', 'STRING');

		$img_path       = 'img src="' . Uri::root();
		$res            = new stdclass;
		$res->content   = str_replace('img src="' . Uri::root(), 'img src="', $body);
		$res->content   = str_replace('img src="', $img_path, $res->content);
		$res->content   = str_replace("background: url('" . Uri::root(), "background: url('", $res->content);
		$res->content   = str_replace("background: url('", "background: url('" . Uri::root(), $res->content);

		require_once JPATH_SITE . '/components/com_jticketing/models/attendees.php';

		// Get attendee Ids from the session
		// $attendeeIds    = $session->get('selected_order_item_ids');

		// Get attendee Ids from post request as session is not working for administrator
		$attendeeIds = explode(",", $emailIds);

		// Get unique and valid email ids
		$attendeesModel = new JticketingModelAttendees;
		$selectedEmails = $attendeesModel->getAttendeeEmail($attendeeIds);

		$cid            = array_unique($selectedEmails);
		$model          = $this->getModel('attendees');
		$message        = Text::_('COM_JTICKETING_EMAIL_SUCCESSFUL');
		$errorStatus    = false;

		// Send mails to selected Attendees
		if ($model->emailtoSelected($cid, $subject, $body))
		{
			$message           	= Text::_('COM_JTICKETING_EMAIL_SUCCESSFUL');
		}
		else
		{
			$message           	= $model->getError();
			$errorStatus   		= true;
		}

		// Assign redirect URL based on isAdmin, if true then administrator, if false then site
		$redirectURL           = Route::_(Uri::base() . 'index.php?option=com_jticketing&view=attendees');
		
		if($isAdmin)
		{
			$redirectURL       = Route::_(Uri::base() . 'administrator/index.php?option=com_jticketing&view=attendees');
		}
		
		echo new JsonResponse($redirectURL, $message, $errorStatus);
		
	}
}
