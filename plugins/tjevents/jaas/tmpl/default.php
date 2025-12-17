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

Text::script('PLG_TJEVENTS_JAAS_VIDEO_CALL_END');
$eventUrl = $event->getUrl();

$params = new Registry($event->getParams());
$venue = JT::venue($event->venue);
$jaasConfig = new Registry($venue->getParams());
$domain = new Uri($jaasConfig->get('domain'));
HTMLHelper::script($domain->toString() . "/external_api.js");
HTMLHelper::_('jquery.token');

$jwt = null;

$publicDomain = "https://8x8.vc";

PluginHelper::importPlugin('tjevents');

try
{
	$results = Factory::getApplication()->triggerEvent('onJtGenerateJaasJwtToken', array ($attendee, $event));
	$jwt = $results[0];
	Factory::getApplication()->triggerEvent('onJtValidateJwt', array ($jwt, $event));
}
catch (Exception $e)
{
	$app = Factory::getApplication();
	$app->enqueueMessage(Text::_('PLG_TJEVENTS_JAAS_INVALID_USER'), 'error');
	$app->redirect($eventUrl);

	return false;
}

$user = Factory::getUser();
$name = $attendee->getFirstName() . " " . $attendee->getLastName();

if (empty($name))
{
	$name = $user->name;
}

$keys = explode('/', $jaasConfig->get('apikey'));
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
	if (domain !== public_domain && "{$params->get('jaas.password')}" && event.role == "moderator") {
		api.executeCommand("password", "{$params->get('jaas.password')}");
	}
});

api.on("readyToClose", () => {
	attendeeCheckout();
	api.dispose();
	window.location.href= "$eventUrl";
	jQuery("#meet").addClass("hangoutMessage").html(Joomla.Text._('PLG_TJEVENTS_JAAS_VIDEO_CALL_END'));
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
jQuery("#meet").addClass("hangoutMessage").html(Joomla.Text._('PLG_TJEVENTS_JAAS_VIDEO_CALL_END'));
});
EOT;

}

$script = <<<EOT
jQuery(document).ready(function() {
	const public_domain = "$publicDomain";
	const domain = "{$domain->getHost()}";
	const settings = "{$jaasConfig->get('setting')}";
	const toolbar = "{$jaasConfig->get('toolbar')}";
	const options = {
		$jwtString
		roomName:"$keys[0]/{$params->get('jaas.roomId')}",
		width:"{$jaasConfig->get('width')}%",
		height:{$jaasConfig->get('height')},
		parentNode: document.querySelector("#meet"),
		configOverwrite: {
			startAudioOnly:{$jaasConfig->get('start_with_audio')}  == 1,
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
			VIDEO_QUALITY_LABEL_DISABLED: {$jaasConfig->get('disable_video_quality_indicator')} == 1,
			SETTINGS_SECTIONS: settings.split(","),
			TOOLBAR_BUTTONS: toolbar.split(","),
		},
		onload: attendeeCheckin(),
	};

	const api = new JitsiMeetExternalAPI(domain, options);

	api.executeCommand("subject", "{$event->getTitle()}");
	api.executeCommand('email', '{$attendee->getEmail()}');

	api.on("videoConferenceJoined", (event) => {
		if(domain === public_domain && "{$params->get('jaas.password')}"){
			api.executeCommand("password", "{$params->get('jaas.password')}");
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
