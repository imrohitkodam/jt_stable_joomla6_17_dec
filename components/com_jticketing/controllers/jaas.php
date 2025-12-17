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
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\MVC\Controller\BaseController;

JLoader::register('JTicketingEvent', JPATH_SITE . '/components/com_jticketing/includes/event.php');
JLoader::register('JTicketingEventJticketing', JPATH_SITE . '/components/com_jticketing/includes/event/jticketing.php');

/**
 * Jaas Event controller class.
 *
 * @since  3.3.1
 */
class JticketingControllerJaas extends BaseController
{
	/**
	 * Store the jaas meet recording through the webhook
	 *
	 * @return  void
	 *
	 * since 3.3.1
	 */
	public function recording()
	{
		// Handling the event from HTTP response
		$payload = @file_get_contents('php://input');
		$record  = json_decode( $payload, FALSE );
		$roomId  = explode('/', $record->fqn);

		// Extract the event details set in roomId - vpaas-magic-cookie-c2824d584eac4489a1e32e4e164d5a3c/JaasEvent-25
		$details = explode('-', $roomId[1]);

		// Load parent class to save method of it not jaasmeeting class
		$event   = new JTicketingEventJticketing($details[1]);
		$params  = json_decode($event->params, true);

		$params['jaas']['preAuthenticatedLink'] = $record->data->preAuthenticatedLink;
		$params['jaas']['recordingLink']=  Uri::root() . "media/com_jticketing/events/recording/event-" . $event->alias . ".mp4";
		$event->params = json_encode($params);

		// Saving recording link to the params
		$event->save(array());

		// Downloading the recording and saving it to the media
		$output_filename = JPATH_SITE . "/media/com_jticketing/events/recording/event-" . $event->alias . ".mp4";
		$fp = fopen($output_filename, 'w');
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $record->data->preAuthenticatedLink);
		curl_setopt($ch, CURLOPT_VERBOSE, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		$result = curl_exec($ch);
		curl_close($ch);

		$fp = fopen($output_filename, 'w');
		fwrite($fp, $result);
		fclose($fp);
	}
}
