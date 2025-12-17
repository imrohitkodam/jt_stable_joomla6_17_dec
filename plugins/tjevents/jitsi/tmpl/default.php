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
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;

HTMLHelper::_('behavior.keepalive');

/* @var $event JTicketingEventJitsimeeting */
/* @var $attendee JTicketingAttendee */

Text::script('PLG_TJEVENTS_JITSI_VIDEO_CALL_END');
$eventUrl = $event->getUrl();

$params = new Registry($event->getParams());
$venue = JT::venue($event->venue);
$jitsiConfig = new Registry($venue->getParams());
$domain = new Uri($jitsiConfig->get('domain'));
HTMLHelper::script($domain->toString() . "/external_api.js");
HTMLHelper::_('jquery.token');

$jwt = null;

$publicDomain = "meet.jit.si";

if ((int) $jitsiConfig->get('enablejwt') && ($publicDomain != $domain->getHost()))
{
	PluginHelper::importPlugin('tjevents');
	$results = Factory::getApplication()->triggerEvent('onJtGenerateJwtToken', array ($attendee, $event));

	$jwt = $results[0];
}

$user = Factory::getUser();
$name = $attendee->getFirstName() . " " . $attendee->getLastName();

if (empty($name))
{
	$name = $user->name;
}

$jwtString = '';

if ($jwt){
	$jwtString = 'jwt:"' .  $jwt . '",';
}

if ($event->isCreator())
{
	$events =
<<< EOT
/**
 * If we are on a self hosted Jitsi domain, we need to become moderators before setting a password
 * Issue: https://community.jitsi.org/t/lock-failed-on-jitsimeetexternalapi/32060
 */
api.addEventListener("participantRoleChanged", (event) => {
	if (domain !== public_domain && "{$params->get('jitsi.password')}" && event.role == "moderator") {
		api.executeCommand("password", "{$params->get('jitsi.password')}");
	}
});

api.on("readyToClose", () => {
	attendeeCheckout();
	api.dispose();
	window.location.href= "$eventUrl";
	jQuery("#meet").addClass("hangoutMessage").html(Joomla.Text._('PLG_TJEVENTS_JITSI_VIDEO_CALL_END'));
});

EOT;
}
else
{
	$events =
<<<EOT
api.on("readyToClose", () => {
attendeeCheckout();
api.dispose();
window.location.href= "$eventUrl";
jQuery("#meet").addClass("hangoutMessage").html(Joomla.Text._('PLG_TJEVENTS_JITSI_VIDEO_CALL_END'));
});
EOT;

}

$script = <<<EOT
jQuery(document).ready(function() {
	const public_domain = "$publicDomain";
	const domain = "{$domain->getHost()}";
	const settings = "{$jitsiConfig->get('setting')}";
	const toolbar = "{$jitsiConfig->get('toolbar')}";
	const options = {
		$jwtString
		roomName:"{$params->get('jitsi.roomId')}",
		width:"{$jitsiConfig->get('width')}%",
		height:{$jitsiConfig->get('height')},
		parentNode: document.querySelector("#meet"),
		configOverwrite: {
			startAudioOnly:{$jitsiConfig->get('start_with_audio')}  == 1,
			defaultLanguage: "en",
		},
	    userInfo: {
	        email: '{$attendee->getEmail()}',
	        displayName: '{$name}',
	    },
		interfaceConfigOverwrite: {
			DEFAULT_REMOTE_DISPLAY_NAME: "",
			SHOW_JITSI_WATERMARK: false,
			SHOW_POWERED_BY: false,
			BRAND_WATERMARK_LINK: "",
			LANG_DETECTION: true,
			CONNECTION_INDICATOR_DISABLED: false,
			VIDEO_QUALITY_LABEL_DISABLED: {$jitsiConfig->get('disable_video_quality_indicator')} == 1,
			SETTINGS_SECTIONS: settings.split(","),
			TOOLBAR_BUTTONS: toolbar.split(","),
		},
		onload: attendeeCheckin(),
	};

	const api = new JitsiMeetExternalAPI(domain, options);

	api.executeCommand("subject", "{$event->getTitle()}");
	api.executeCommand('email', '{$attendee->getEmail()}');

	api.on("videoConferenceJoined", (event) => {
		if(domain === public_domain && "{$params->get('jitsi.password')}"){
			api.executeCommand("password", "{$params->get('jitsi.password')}");
		}
	});
	
	$events
	window.api = api;
});

function attendeeCheckin()
{
	JTicketing.Ajax({
		url: Joomla.getOptions('system.paths').base + '/index.php?option=com_ajax',
		data: {
			plugin: "storeAttendee",
			group: "tjevents",
			attendee: "{$attendee->id}",
			eventid:  {$event->getId()} ,
			action: "joined"
		}
	}).fail(function(content) {
		console.log(content);
	});
}

function attendeeCheckout()
{
	JTicketing.Ajax({
		url: Joomla.getOptions('system.paths').base + '/index.php?option=com_ajax',
		data: {
			plugin: "storeAttendee",
			group: "tjevents",
			attendee: "{$attendee->id}",
			eventid:  {$event->getId()},
			action: "left"
		}
	}).fail(function(content) {
		console.log(content);
	});
}
EOT;

Factory::getDocument()->addScriptDeclaration($script);
?>

<div id="meet"></div>
