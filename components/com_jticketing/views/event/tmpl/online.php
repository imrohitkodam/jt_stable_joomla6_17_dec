<?php
/**
 * @package    JTicketing
 * @subpackage  com_jticketing
 *
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Plugin\PluginHelper;

HTMLHelper::_('behavior.keepalive');

/* @var $this JticketingViewEvent*/

$eventUrl = $this->event->getUrl();
$app = Factory::getApplication();

if (!$this->event->isOnline())
{
	$app->redirect($eventUrl);

	return false;
}

$user = Factory::getUser();

// Get the attendee id using the event id and user ID
/** @var $attendees JticketingModelAttendees */
$attendees = JT::model('attendees');
$attendeeId = $attendees->getAttendees(
		['owner_id' => $user->id, 'event_id' => $this->event->getIntegrationId(),
				'status' => COM_JTICKETING_CONSTANT_ATTENDEE_STATUS_APPROVED,
				'limit' => 1]
		);

$attendeeId = !empty($attendeeId[0]->id) ? $attendeeId[0]->id : 0;
$attendee = JT::attendee($attendeeId);

if (!$attendee->id && $this->event->getCreator() != $user->id)
{
	$app->redirect($eventUrl);

	return false;
}

$venue = JT::venue($this->event->venue);

PluginHelper::importPlugin('tjevents');
$results = Factory::getApplication()->triggerEvent('onJtRenderLayout' . ucfirst($venue->online_provider), array ($this->event, $attendee));

echo $results['0'];
