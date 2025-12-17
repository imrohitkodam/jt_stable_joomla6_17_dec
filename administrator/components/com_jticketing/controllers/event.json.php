<?php
/**
 * @package     JTicketing
 * @subpackage  com_jticketing
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Response\JsonResponse;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\MVC\Controller\FormController;

require_once JPATH_SITE . '/components/com_tjvendors/includes/tjvendors.php';

/**
 * com_jticketing Controller
 *
 * @subpackage  com_jticketing
 * @since       0.0.9
 */
class JTicketingControllerEvent extends FormController
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
		$eventData["silentVendor"] = $input->get('silentVendor', '', 'INTEGER');
		$eventData["eventId"] = $input->get('eventId', '', 'INT');
		$eventData["venueId"] = $input->get('venueId', '', 'INT');

		if ($eventData["silentVendor"] != 1)
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

		$model = $this->getModel('event');
		$results = $model->getVenueList($eventData);
		echo json_encode($results);
		jexit();
	}

	/**
	 * Method to get all existing events
	 *
	 * @return  string
	 *
	 * @since   1.6
	 */
	public function getAllMeetings()
	{
		$app = Factory::getApplication();

		if (!Session::checkToken())
		{
			echo new JsonResponse(null, Text::_('JINVALID_TOKEN'), true);
			$app->close();
		}

		$venueId = $app->getInput()->post->getInt('venueId');
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
	 * @deprecated 3.0.0 this function will be removed without replacement
	 *
	 * @return	void
	 *
	 * @since	1.6
	 */
	public function getScoID()
	{
		$post = Factory::getApplication()->getInput()->post;
		$venueId = $post->get('venueId');
		$venueurl = $post->get('venueurl');

		// Load AnnotationForm Model
		$model = BaseDatabaseModel::getInstance('Venue', 'JticketingModel');
		$licenceContent = $model->getItem($venueId);
		$licence = (object) $licenceContent->params;

		if (!empty($venueId))
		{
			// TRIGGER After create event
			PluginHelper::importPlugin('tjevents', $licenceContent->online_provider);
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
		$uploadFile = $app->getInput()->post->get('upload_type', '', 'string');
		$isGallary  = $app->getInput()->post->get('isGallary', '', 'INT');
		$jtParams   = ComponentHelper::getParams('com_jticketing');
		$model      = $this->getModel('Media', 'JTicketingModel');

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
			$storagePath = $jtParams->get('jticketing_media_upload_path', 'media/com_jticketing/events');
			$mediaPath   = JPATH_SITE . '/' . $storagePath . '/' . strtolower($fileType[0] . 's');

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
			$app->close();
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
		if (file_exists(JPATH_SITE . '/components/com_jticketing/helpers/common.php')) { require_once JPATH_SITE . '/components/com_jticketing/helpers/common.php'; }
		$jtParams = ComponentHelper::getParams('com_jticketing');
		$app      = Factory::getApplication();
		$model    = $this->getModel('Media', 'JTicketingModel');

		$user = Factory::getUser();

		if (!$user->id)
		{
			echo new JsonResponse(1, Text::_('JERROR_ALERTNOAUTHOR'), true);
			$app->close();
		}

		$mediaId  = $this->input->get('id', '0', 'INT');
		$clientId = $this->input->get('client_id', '0', 'INT');

		$authorise = ($user->authorise("core.delete", 'com_jticketing') == 1 ? true : false);

		// If I don't have access or if I am not admin
		if (!$authorise || !$user->authorise('core.admin'))
		{
			echo new JsonResponse(1, Text::_('JERROR_ALERTNOAUTHOR'), true);
			$app->close();
		}

		if (!$mediaId)
		{
			echo new JsonResponse(1, Text::_('JERROR_ALERTNOAUTHOR'), true);
			$app->close();
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

	/**
	 * Check email of vendor
	 *
	 * @return JSON
	 *
	 * @since   2.0
	 */
	public function checkUserEmail()
	{
		$data              = array();
		$userId            = $this->input->get('user', 0, 'INT');
		$vendor            = Tjvendors::vendor()->loadByUserId($userId, 'com_jticketing');
		$paymentConfig     = $vendor->getPaymentConfig();
		$data['check']     = $paymentConfig ? true : false;
		$data['vendor_id'] = $paymentConfig ? $vendor->vendor_id : 0;

		echo json_encode($data);
		jexit();
	}

	/**
	 * Function to get category specific event
	 *
	 * @return  JSON
	 *
	 * @since  1.0.0
	 */
	public function getCategorySpecificEventCount()
	{
		$input = Factory::getApplication()->getInput();
		$catId = $input->get('catid');

		$db   = Factory::getDbo();
		$query = $db->getQuery(true);

		$query->select('id');
		$query->from($db->quoteName('#__jticketing_events'));
		$query->where($db->quoteName('catid') . " = " . (int) $catId);
		$query->where($db->quoteName('state') . " = " . 1);

		$db->setQuery($query);
		$events = $db->loadObjectlist();

		if (!empty($events))
		{
			echo new JsonResponse(count($events));
		}
		else
		{
			echo new JsonResponse(0);
		}
	}

	/**
	 * Get events details
	 *
	 * @return  file
	 *
	 * @since 1.0.0
	 */
	/*public function getEventsDetails()
	{
		$db       = Factory::getDbo();
		$input    = Factory::getApplication()->getInput();
		$event_id = $input->get('event_id');
		$query    = $db->getQuery(true);

		$query->select($db->quoteName('e.id'));
		$query->select($db->quoteName('t.eventid'));
		$query->select($db->quoteName('t.id', 'ticketid'));
		$query->select($db->quoteName('t.price'));
		$query->select($db->quoteName('t.title'));
		$query->from($db->quoteName('#__jticketing_events', 'e'));
		$query->join('LEFT', $db->quoteName('#__jticketing_types', 't') . 'ON(' . $db->quoteName('t.eventid') . '=' . $db->quoteName('e.id') . ')');
		$query->where($db->quoteName('e.id') . ' = ' . $db->quote($event_id));
		$db->setQuery($query);
		$var = $db->loadObjectlist();
		$var = json_encode($var);
		echo new JsonResponse($var);
	}*/

	/**
	 * Method to check if free event or not
	 *
	 * @return  int  id of event
	 */
	/*public function isFreeEvent()
	{
		require_once JPATH_SITE . "/components/com_jticketing/helpers/main.php";
		$input   = Factory::getApplication()->getInput();
		$eventid = $input->get('event_id');

		$jticketingmainhelper = new jticketingmainhelper;
		$freeEvent            = $jticketingmainhelper->isFreeEvent($eventid);

		if (empty($freeEvent))
		{
			echo new JsonResponse(1);
		}
		else
		{
			echo new JsonResponse(0);
		}
	}*/

	/**
	 * Method to get category specific events
	 *
	 * @return  int  id of event
	 */
	/*public function getEvents()
	{
		require_once JPATH_SITE . "/components/com_jticketing/helpers/main.php";
		$input   = Factory::getApplication()->getInput();
		$catId = $input->get('cat_id');
		$params = array();
		$params['category_id'] = $catId;

		$jticketingmainhelper = new jticketingmainhelper;
		$events               = $jticketingmainhelper->getEvents($params);

		if (!empty($events))
		{
			echo new JsonResponse($events);
		}
		else
		{
			echo new JsonResponse(0);
		}
	}*/

	/**
	 * Method to get event name depends on eventid
	 *
	 * @return  int  id of event
	 */
	/*public function getEventName()
	{
		require_once JPATH_SITE . "/components/com_jticketing/helpers/main.php";

		$db       = Factory::getDbo();
		$input    = Factory::getApplication()->getInput();
		$eventId  = $input->get('event');

		$jticketingmainhelper = new Jticketingmainhelper;
		$query                = $jticketingmainhelper->getEventName($eventId);

		$db->setQuery($query);
		$events = $db->loadResult();

		if (!empty($events))
		{
			echo new JsonResponse($events);
		}
		else
		{
			echo new JsonResponse(0);
		}
	}*/
}
