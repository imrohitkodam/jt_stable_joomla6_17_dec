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
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Access\Access;

HTMLHelper::_('bootstrap.renderModal', 'a.modal');
require_once JPATH_SITE . "/components/com_jticketing/includes/jticketing.php";

/** @var $event JTicketingEventJticketing */
$event                  = $displayData;
$redirectionUrl         = !empty($event->returnUrl) ? $event->returnUrl :
$event->getUrl();
$eventId                = $event->getId();
$showbook               = $event->isAllowedToBuy();
$config        		    = JT::config();
$enableWaitingList 	    = $config->get('enable_waiting_list');
$integration       	    = $config->get('integration');
$user                   = Factory::getUser();
$userId                 = $user->id;
$isboughtEvent          = $event->isBought($userId);
$enableSelfEnrollment   = $config->get('enable_self_enrollment', '0', 'INT');
$event->meetingInPage     = (isset($event->meetingView) && isset($event->meetingView['viewmeeting'])) ? $event->meetingView['viewmeeting'] : null;

include_once  JPATH_SITE . '/components/com_jticketing/includes/jticketing.php';
JT::init();
$config = JT::config();

$bsVersion = $config->get('bootstrap_version', '', 'STRING');

if (empty($bsVersion))
{
	$bsVersion = (JVERSION >= '4.0.0') ? 'bs5' : 'bs3';
}

if (!empty($event->isOnline()))
{
	$beforeEventStartTime = (int) $config->get('show_em_btn', 5);
	$showJoinButton = 0;
	$currentTime = Factory::getDate()->toSql();
	$time = strtotime($event->getStartDate());
	$time = $time - ($beforeEventStartTime * 60);
	$current = strtotime($currentTime);
	$date = date("Y-m-d H:i:s", $time);
	$datetime = strtotime($date);
	$meetingStartedToggleMessageHTML = '';

	if ($datetime < $current || $event->getCreator() == $userId)
	{
		$showJoinButton = 1;
	}

	// Display enter meeting toggle message to the event buyer if plenty of time left for the event start
	if ($datetime > $current && $event->getCreator() != $userId)
	{
		$meetingStartedToggleMessageHTML = ' data-toggle="tooltip" data-bs-toggle="tooltip" data-placement="top" data-bs-placement="top" title=" ' . Text::sprintf("COM_JT_MEETING_ACCESS", $beforeEventStartTime) . '"';
	}
}

if (empty($isboughtEvent) && empty(JT::event($eventId)->getTicketTypes()) && $enableWaitingList == 'none')
{
	echo "<button type='button' class='btn btn-info disabled w-100 booking-btn'>" . Text::_('COM_JTICKETING_EVENTS_UNAUTHORISED') . "</button>";

	return;
}

// Show enroll button if - Quick book is set, self enrolment permission is set and ticket is not bought
if ($enableSelfEnrollment && $user->authorise('core.enroll', 'com_jticketing.event.' . $eventId) == '1' && $userId)
{
	if ($event->isEnrollmentCanceled($userId))
	{
		// Echo Enrollment cancel button
		echo "<button type='button' class='btn btn-info disabled w-100 booking-btn'>" . Text::_('COM_JTICKETING_EVENTS_ENROLL_CANCEL_BTN') . "</button>";

		return;
	}

	if ($event->isEnrollmentPending($userId))
	{
		// Echo enrollment pending button
		echo "<button type='button' class='btn btn-info disabled w-100 booking-btn'>"
		. Text::_('COM_JTICKETING_EVENTS_ENROLL_PENDING_BUTTON') . "</button>";

		return;
	}

	// Show Enter Meeting button for event creator for online event
	if ($isboughtEvent || ($event->getCreator() == $userId && !empty($event->isOnline())))
	{
		// Enrolled button
		if ($integration == COM_JTICKETING_CONSTANT_INTEGRATION_NATIVE && !empty($event->isOnline()))
		{
			if ($event->isOver())
			{
				echo '<button type="button" class="btn btn-info enable w-100 booking-btn" id="jt-meetingRecording"
				onclick = "jtSite.event.meetingRecordingUrl(this, ' . $eventId . ')" >' . Text::_('COM_JTICKETING_VIEW_MEETINGS_RECORDINGS') . '</button>';
				echo LayoutHelper::render('event.online_recording');

				return;
			}

			if ($showJoinButton == 1 && (!$event->meetingInPage))
			{
				echo '<button type="button" class="btn btn-info enable w-100 booking-btn" id="jt-enterMeeting" ' . $meetingStartedToggleMessageHTML . '
				data-loading-text="<i class=fa fa-spinner fa-spin></i> Loading.."
				onclick ="jtSite.event.onlineMeetingUrl(this, ' . $eventId . ')">' . Text::_("COM_JTICKETING_MEETING_BUTTON") . '</button>';

				return;
			}
			else if ($event->meetingView)
			{
				echo '<button type="submit" class="btn btn-primary enable w-100 booking-btn" id="join_meeting" ' . $meetingStartedToggleMessageHTML . '
				data-loading-text="<i class=fa fa-spinner fa-spin></i> Loading..">' . Text::_('COM_JTICKETING_MEETING_BUTTON') . '</button>';

				return;
			}
			else
			{
				echo '<span class="" data-toggle="tooltip" data-bs-toggle="tooltip" data-placement="top" data-bs-placement="top" title=" ' . Text::sprintf("COM_JT_MEETING_ACCESS", $beforeEventStartTime) . '">
				<button class="btn btn-info enable w-100 booking-btn" ' . $meetingStartedToggleMessageHTML . '>' . Text::_("COM_JTICKETING_MEETING_BUTTON") . '</button>
				</span>';

				return;
			}
		}

		echo "<button type='button' class='btn btn-info disabled w-100 booking-btn'>" . Text::_('COM_JTICKETING_EVENTS_ENROLLED_BTN') . "</button>";

		return;
	}

	if ($showbook)
	{
		$itemId = Factory::getApplication()->getInput()->get('Itemid');
		$redirect = '';

		if (!empty($redirectionUrl))
		{
			$redirectionUrl = base64_encode($redirectionUrl);
			$redirect = '&redirectUrl=' . $redirectionUrl;
		}

		$enrollTicketLink = Route::_('index.php?option=com_jticketing&task=enrollment.save&selected_events=' . $eventId .
			'&cid=' . $userId .
			'&Itemid=' . $itemId . '&notify_user_enroll=1' . $redirect, false
		);

		$enrollTicketLink .= '&' . Session::getFormToken() . '=1';

		echo "<a href='" . $enrollTicketLink . "' class='btn
		 btn-success com_jt_book com_jticketing_button w-100 booking-btn'>" . Text::_('COM_JTICKETING_ENROLL_BUTTON') . "</a>";

		return;
	}
}

if ($showbook)
{
	if ($integration == COM_JTICKETING_CONSTANT_INTEGRATION_NATIVE && !empty($event->isOnline()) && ($event->isCreator() || !empty($isboughtEvent)))
	{
		if ($showJoinButton == 1 && (!$event->meetingInPage))
		{
			echo '<button type="button" class="btn btn-info enable w-100 booking-btn" id="jt-enterMeeting" ' . $meetingStartedToggleMessageHTML . '
			data-loading-text="<i class=fa fa-spinner fa-spin></i> Loading.."
			onclick ="jtSite.event.onlineMeetingUrl(this, ' . $eventId . ')">' . Text::_('COM_JTICKETING_MEETING_BUTTON') . '</button>';

			return;
		}
		else if ($event->meetingView)
		{
			echo '<button type="submit" class="btn btn-primary enable w-100 booking-btn" id="join_meeting" ' . $meetingStartedToggleMessageHTML . '
			data-loading-text="<i class=fa fa-spinner fa-spin></i> Loading..">' . Text::_('COM_JTICKETING_MEETING_BUTTON') . '</button>';

			return;


		}
		else
		{
			echo '<span class="" data-toggle="tooltip" data-bs-toggle="tooltip" data-placement="top" data-bs-placement="top" title=" ' . Text::sprintf("COM_JT_MEETING_ACCESS", $beforeEventStartTime) . '">
			<button class="btn btn-info enable w-100 booking-btn" ' . $meetingStartedToggleMessageHTML . '> ' . Text::_("COM_JTICKETING_MEETING_BUTTON") . '</button>
			</span>';

			return;
		}
	}
	else
	{
		// Echo buy button link
		
		$Itemid = JT::utilities()->getItemId('index.php?option=com_jticketing&view=event');

		$bookTicketLink = Route::_('index.php?option=com_jticketing&task=order.addOrder&eventId=' . $eventId . '&Itemid=' . $Itemid, false
				);

		$bookTicketLink .= '&' . Session::getFormToken() . '=1';

		$buyButton = "<a href='" . $bookTicketLink . "' class='btn btn-info enable w-100 booking-btn'
		data-loading-text='<i class=fa fa-spinner fa-spin></i>Loading...''>" . Text::_('COM_JTICKETING_BUY_BUTTON') . "</a>";

		if (!empty($isboughtEvent))
		{
			$classvisibleXS = ($bsVersion == 'bs3') ? 'visible-xs' : 'd-block d-sm-none';
			?>
			<div class = "<?php echo $classvisibleXS;?>">
				<div class="info">
					<p><?php echo Text::_("COM_JTICKETING_ONLINE_EVENT_ALREADY_BOUGHT");?></p>
				</div>
				<?php echo $buyButton;?>
			</div>
			<div class="hidden-xs">
				<span class="tool-tip" data-toggle="tooltip" data-bs-toggle="tooltip" data-placement="top" data-bs-placement="top" title="<?php echo Text::_('COM_JTICKETING_ONLINE_EVENT_ALREADY_BOUGHT');?>">
					<?php echo $buyButton;?>
				</span>
			</div>
			<?php
		}
		else
		{
			echo $buyButton;

			return;
		}

		return;
	}
}

// Single ticket is purchased Display view ticket button
if ($event->isBuyingLimitExceed($userId) && !empty($userId) && empty($event->isOver()))
{
	if ($event->isOnline())
	{
		if ($showJoinButton == 1 && (!$event->meetingInPage))
		{
			echo '<button type="button" class="btn btn-info enable w-100 booking-btn" id="jt-enterMeeting" ' . $meetingStartedToggleMessageHTML . '
			data-loading-text="<i class=fa fa-spinner fa-spin></i> Loading.."
			onclick ="jtSite.event.onlineMeetingUrl(this, ' . $eventId . ')">' . Text::_('COM_JTICKETING_MEETING_BUTTON') . '</button>';

			return;
		}
		else if ($event->meetingView)
		{
			echo '<button type="submit" class="btn btn-primary enable w-100 booking-btn" id="join_meeting" ' . $meetingStartedToggleMessageHTML . '
			data-loading-text="<i class=fa fa-spinner fa-spin></i> Loading..">' . Text::_('COM_JTICKETING_MEETING_BUTTON') . '</button>';

			return;

		}
		else
		{
			echo '<span class="" data-toggle="tooltip" data-bs-toggle="tooltip" data-placement="top" data-bs-placement="top" title=" ' . Text::sprintf("COM_JT_MEETING_ACCESS", $beforeEventStartTime) . '">
			<button class="btn btn-info enable w-100 booking-btn" ' . $meetingStartedToggleMessageHTML . '>' . Text::_("COM_JTICKETING_MEETING_BUTTON") . '</button>
			</span>';

			return;
		}
	}

	$attendeesModel = JT::model('attendees');
	$attendees = $attendeesModel->getAttendees(
		array('event_id' => $event->integrationId,
			'owner_id' => $userId,
			'status' => COM_JTICKETING_CONSTANT_ATTENDEE_STATUS_APPROVED)
	);

	$viewTicketLink = Route::_('index.php?option=com_jticketing&view=mytickets&tmpl=component&layout=ticketprint&attendee_id='
		. $attendees['0']->id, false
	);

	$modalConfig = array('width' => '800px', 'height' => '300px', 'modalWidth' => 80, 'bodyHeight' => 70);
	$modalConfig['url'] = $viewTicketLink;
	$modalConfig['title'] = Text::_('COM_JTICKETING_VIEW_TICKET_BUTTON');
	echo HTMLHelper::_('bootstrap.renderModal', 'jtActionsBtn' . $attendees['0']->id, $modalConfig);

	// Joomla 6: JVERSION check removed
		if (false) // Legacy < '4.0.0')
	{
		echo "<a data-target='#jtActionsBtn" . $attendees['0']->id . "' data-toggle='modal' class='af-relative af-d-block btn btn-info' title='"
		. Text::_('COM_JTICKETING_VIEW_TICKET_BUTTON_TOOLTIP') . "'
		com_jt_book com_jticketing_button w-100 booking-btn'>"
		. Text::_('COM_JTICKETING_VIEW_TICKET_BUTTON') . "</a>";
	}
	else
	{
		echo "<a data-bs-target='#jtActionsBtn" . $attendees['0']->id . "' data-bs-toggle='modal' class='af-relative af-d-block btn btn-info' title='"
		. Text::_('COM_JTICKETING_VIEW_TICKET_BUTTON_TOOLTIP') . "'
		com_jt_book com_jticketing_button w-100 booking-btn'>"
		. Text::_('COM_JTICKETING_VIEW_TICKET_BUTTON') . "</a>";
	}

	return;
}

// If booking date is not closed and waiting list is there
if ($event->isBookingStarted() && empty($event->isOver()) && $enableWaitingList != 'none')
{
	$waitlistFormModel = JT::model('waitlistform');
	$isAdded = $waitlistFormModel->isAlreadyAddedToWaitlist($eventId, $userId);

	if (!empty($isAdded))
	{
		// Echo waiting list button
		echo "<button type='button' class='btn btn-info disabled w-100 booking-btn'>" . Text::_('COM_JTICKETING_EVENTS_WAITLISTED_BTN') . "</button>";

		return;
	}

	$redirect = '';

	if (!empty($redirectionUrl))
	{
		$redirectionUrl = base64_encode($redirectionUrl);
		$redirect = '&redirectUrl=' . $redirectionUrl;
	}

	$Itemid = JT::utilities()->getItemId('index.php?option=com_jticketing&view=event');

	$waitinglistLink = Route::_('index.php?option=com_jticketing&task=waitlistform.save&eventid=' . $eventId . '&userid=' . $userId . $redirect, false);
	$waitinglistLink .= '&id=0&Itemid=' . $Itemid  .'&' . Session::getFormToken() . '=1';

	// Echo waiting list button
	echo "<a title='" . Text::_('COM_JTICKETING_WAITINGLIST_BUTTON_DESC') . "' href=" . $waitinglistLink . "
	class='btn  btn-info w-100 booking-btn'>"
	. Text::_('COM_JTICKETING_WAITINGLIST_BUTTON') . "</a>";

	return;
}

// If event is over and online recording are there
if ($event->isOver() && $event->isOnline() && ($isboughtEvent || $event->getCreator() == $userId))
{
	echo '<button type="button" class="btn btn-info enable w-100 booking-btn" id="jt-meetingRecording"
	onclick = "jtSite.event.meetingRecordingUrl(this, ' . $eventId . ')" >' . Text::_('COM_JTICKETING_VIEW_MEETINGS_RECORDINGS') . '</button>';
	echo LayoutHelper::render('event.online_recording');

	return;
}

// If event is end
if ($event->isOver())
{
	echo '<button class="btn disabled btn-danger w-100 booking-btn">' . Text::_("COM_JTICKETING_EVENTS_BOOKING_BTN_CLOSED") . '</button>';

	return;
}

// Event booking is not yet started
if (empty($event->isBookingStarted()))
{
	// Echo booking not started yet
	echo '<button class="btn disabled btn-danger w-100 booking-btn">' . Text::_('COM_JTICKETING_EVENTS_BOOKING_BTN_NOT_STARTED') . '
	</button>';

	return;
}

// Display enter meeting if booking time is finished but user already purchased the event or creator
if ($integration == COM_JTICKETING_CONSTANT_INTEGRATION_NATIVE
	&& !empty($event->isOnline()) && ($event->isCreator() || !empty($isboughtEvent))
	&&empty($event->isOver()))
{
	if ($showJoinButton == 1 && (!$event->meetingInPage))
	{
		echo '<button type="button" class="btn btn-info enable w-100 booking-btn" id="jt-enterMeeting" ' . $meetingStartedToggleMessageHTML . '
		data-loading-text="<i class=fa fa-spinner fa-spin></i> Loading.."
		onclick ="jtSite.event.onlineMeetingUrl(this, ' . $eventId . ')">' . Text::_('COM_JTICKETING_MEETING_BUTTON') . '</button>';

		return;
	}
	else if ($event->meetingView)
	{
		echo '<button type="submit" class="btn btn-primary enable w-100 booking-btn" id="join_meeting" ' . $meetingStartedToggleMessageHTML . '
		data-loading-text="<i class=fa fa-spinner fa-spin></i> Loading..">' . Text::_('COM_JTICKETING_MEETING_BUTTON') . '</button>';

		return;
	}
	else
	{
		echo '<span class="" data-toggle="tooltip" data-bs-toggle="tooltip" data-placement="top" data-bs-placement="top" title=" ' . Text::sprintf("COM_JT_MEETING_ACCESS", $beforeEventStartTime) . '">
		<button class="btn btn-info enable w-100 booking-btn" ' . $meetingStartedToggleMessageHTML . '>' . Text::_("COM_JTICKETING_MEETING_BUTTON") . '</button>
		</span>';

		return;
	}
}
// If event booking end
if ($event->isBookingEnd())
{
	echo '<button class="btn disabled btn-danger w-100 booking-btn">' . Text::_("COM_JTICKETING_EVENTS_BOOKING_BTN_CLOSED") . '</button>';
}
