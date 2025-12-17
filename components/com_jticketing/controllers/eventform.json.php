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
use Joomla\CMS\Session\Session;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Response\JsonResponse;
use Joomla\CMS\Component\ComponentHelper;

require_once JPATH_ADMINISTRATOR . '/components/com_jticketing'. '/controller.php';
require_once JPATH_ADMINISTRATOR . '/components/com_jticketing/models/venue.php';

$helperPath = JPATH_SITE . '/components/com_jticketing/helpers/time.php';

if (!class_exists('JticketingTimeHelper'))
{
	JLoader::register('JticketingTimeHelper', $helperPath);
	JLoader::load('JticketingTimeHelper');
}

/**
 * Event controller class.
 *
 * @since  1.6
 */
class JticketingControllerEventForm extends JticketingController
{
	/**
	 * Get venue list
	 *
	 * @return null
	 *
	 * @since   1.6
	 */
	public function getVenueList()
	{
		$input  = Factory::getApplication()->getInput()->post;
		$eventData["radioValue"] = $input->get('radioValue', '', 'STRING');
		$eventData["silentVendor"] = $input->get('silentVendor', '', 'STRING');
		$eventData["eventId"] = $input->get('eventId', '', 'INT');
		$eventData["venueId"] = $input->get('venueId', '', 'INT');

		if ($eventData["silentVendor"] == 0)
		{
			$eventData["vendor_id"] = $input->get('vendor_id', '', 'INTEGER');
		}
		else
		{
			$eventData["created_by"] = $input->get('created_by', '', 'STRING');
		}

		$config = Factory::getConfig();
		$user   = Factory::getUser();

		$eventData["eventStartDate"] = $input->get('eventStartTime', '', 'STRING');
		$startDate = Factory::getDate($eventData["eventStartDate"], $user->getParam('timezone', $config->get('offset')));
		$startDate->setTimezone(new DateTimeZone('UTC'));

		$eventData["eventStartDate"] = $startDate->toSql(true);

		$eventData["eventEndDate"] = $input->get('eventEndTime', '', 'STRING');
		$endDate = Factory::getDate($eventData["eventEndDate"], $user->getParam('timezone', $config->get('offset')));
		$endDate->setTimezone(new DateTimeZone('UTC'));

		$eventData["eventEndDate"] = $endDate->toSql(true);
		$model = $this->getModel('eventform');
		$results = $model->getVenueList($eventData);
		echo json_encode($results);
		jexit();
	}

	/**
	 * Method to get all existing events
	 *
	 * @return  string
	 *
	 * @since  1.6
	 */
	public function getAllMeetings()
	{
		$app = Factory::getApplication();

		if (!Session::checkToken())
		{
			echo new JsonResponse(null, Text::_('JINVALID_TOKEN'), true);
			$app->close();
		}

		$venueId = $app->getInput()->getInt('venueId');
		$venue = JT::venue($venueId);

		if (!$venue->id)
		{
			echo new JsonResponse(null, null, true);
			$app->close();
		}

		echo new JsonResponse($venue->getOnlineEvents());
		$app->close();
	}

	/**
	 * Method to get all existing events
	 *
	 * @return	void
	 *
	 * @since	1.6
	 */
	public function getScoID()
	{
		$post     = Factory::getApplication()->getInput()->post;
		$venueId  = $post->get('venueId');
		$venueurl = $post->get('venueurl');

		// Load AnnotationForm Model
		$licenceContent = JT::Venue($venueId);
		$licence        = json_decode($licenceContent->params);

		if (!empty($venueId))
		{
			// TRIGGER After create event
			PluginHelper::importPlugin('tjevents');
			$result = Factory::getApplication()->triggerEvent('onGetscoID', array($licence, $venueurl));
			echo json_encode($result);
		}

		jexit();
	}

	/**
	 * upload media files and links
	 *
	 * @return JSON
	 *
	 * @since   2.0
	 */
	public function uploadMedia()
	{
		Session::checkToken() or die('Invalid Token');
		$app        = Factory::getApplication();
		$input      = $app->input;
		$model      = $this->getModel('Media', 'JTicketingModel');
		$uploadFile = $input->post->get('upload_type', '', 'string');
		$isGallary  = $input->post->get('isGallary', '', 'INT');

		$returnData = array();

		if ($uploadFile == "link")
		{
			$data = array();
			$data['name']        = $app->getInput()->post->get('name', '', 'string');
			$data['type']        = $app->getInput()->post->get('type', '', 'string');
			$data['upload_type'] = $uploadFile;
			$returnData[0]       = $model->uploadLink($data);

			if ($returnData[0] == false)
			{
				$returnData[0]['valid'] = 0;
				echo new JsonResponse($returnData[0], Text::_('COM_JTICKETING_MEDIA_INVALID_URL_TYPE'), true);
				$app->close();
			}
		}
		else
		{
			$files    = $app->getInput()->files->get('file', '', 'array');
			$fileType = explode("/", $files[0]['type']);
			$comMediaParam  = ComponentHelper::getParams('com_media');

			// Joomla 6: JVERSION check removed
		if (false) // Legacy < '4.0.0')
			{
				$allowedExtension = explode(',', $comMediaParam->get('upload_extensions'));
			}
			else
			{
				$allowedExtension = explode(',', $comMediaParam->get('restrict_uploads_extensions'));
			}

			$extension   = pathinfo($files[0]['name'], PATHINFO_EXTENSION);
			if (!isset($extension) || !in_array($extension, $allowedExtension))
			{
				echo new JsonResponse($returnData, Text::_('COM_JTICKETING_FILE_TYPE_NOT_ALLOWED'), true);
				$app->close();
			}

			// Image and video specific validation
			$jtParams          = ComponentHelper::getParams('com_jticketing');
			$storagePath       = $jtParams->get('jticketing_media_upload_path', 'media/com_jticketing/events');
			$mediaPath         = JPATH_SITE . '/' . $storagePath . '/' . strtolower($fileType[0] . 's');

			if ($isGallary && ($fileType[0] === 'video' || $fileType[0] === 'image'))
			{
				$returnData = $model->uploadFile($files, $mediaPath, 1);
			}
			elseif (!$isGallary && $fileType[0] === 'image')
			{
				$returnData = $model->uploadFile($files, $mediaPath, 1);
			}
			else
			{
				echo new JsonResponse($returnData, Text::_('COM_JTICKETING_MEDIA_INVALID_FILE_TYPE'), true);
				$app->close();
			}
		}

		if ($returnData)
		{
			echo new JsonResponse($returnData, Text::_('COM_JTICKETING_MEDIA_FILE_UPLOADED'));
		}
	}

	/**
	 * Delete media file
	 *
	 * @return JSON
	 *
	 * @since   2.0
	 */
	public function deleteMedia()
	{
		Session::checkToken() or die('Invalid Token');
		$jtParams   = ComponentHelper::getParams('com_jticketing');
		$user       = Factory::getUser();
		$app        = Factory::getApplication();
		$model      = $this->getModel('Media', 'JTicketingModel');

		if (!$user->id)
		{
			echo new JsonResponse(1, Text::_('JERROR_ALERTNOAUTHOR'), true);
			$app->close();
		}

		$authorise = ($user->authorise("core.delete", 'com_jticketing') == 1 ? true : false);

		// If I don't have access or if I am not admin
		if (!$authorise && !$user->authorise('core.admin'))
		{
			echo new JsonResponse(1, Text::_('JERROR_ALERTNOAUTHOR'), true);
			$app->close();
		}

		$mediaId  = $this->input->get('id', '0', 'INT');
		$clientId = $this->input->get('client_id', '0', 'INT');

		if (!$mediaId)
		{
			return false;
		}

		$storagePath = $jtParams->get('jticketing_media_upload_path', 'media/com_jticketing/events');
		$client      = 'com_jticketing.event';
		$returnData  = $model->deleteMedia($mediaId, $storagePath, $client, $clientId);

		if (!$returnData)
		{
			echo new JsonResponse(1, $model->getError(), true);

			$app->close();
		}
		else
		{
			echo new JsonResponse(1, Text::_('COM_JTICKETING_MEDIA_FILE_DELETED'));

			$app->close();
		}
	}

	/**
	 * Get Rounded value
	 *
	 * @return JSON
	 *
	 * @since   2.0
	 */
	public function getRoundedValue()
	{
		$price = $this->input->get('price', 'float');

		$roundedValue = JT::utilities()->getRoundedPrice($price);

		echo new JsonResponse($roundedValue);
	}
}
