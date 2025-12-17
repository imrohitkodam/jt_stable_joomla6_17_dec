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
use Joomla\CMS\Table\Table;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
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
 * Venue controller class.
 *
 * @since  1.6
 */
class JticketingControllerVenueForm extends JticketingController
{
	/**
	 * upload media files and links
	 *
	 * @return JSON
	 *
	 * @since   2.4.0
	 */
	public function uploadMedia()
	{
		Session::checkToken() or die('Invalid Token');
		$app        = Factory::getApplication();
		$input      = $app->input;
		$uploadFile = $input->post->get('upload_type', '', 'string');
		$model      = $this->getModel('Media', 'JTicketingModel');
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

			// Image and video specific validation
			$jtParams          = ComponentHelper::getParams('com_jticketing');
			$storagePath       = $jtParams->get('jticketing_venue_media_upload_path', 'media/com_jticketing/venues');
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
	 * @since  2.4.0
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

		// Storgae path and client
		$storagePath = $jtParams->get('jticketing_venue_media_upload_path', 'media/com_jticketing/venues');
		$client      = 'com_jticketing.venue';

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
	 * get details of specified venue
	 *
	 * @return JSON
	 *
	 * @since   2.0
	 */
	public function getCapacity()
	{
		Session::checkToken() or die('Invalid Token');
		$app        = Factory::getApplication();
		$db   = Factory::getDbo();
		$venueId            = $this->input->get('venue', 0, 'INT');

		Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jticketing/tables');
		$venue = Table::getInstance('Venue', 'JticketingTable', array('dbo', $db));
		$venue->load(array('id' => $venueId));

		if (!empty($venue))
		{
			echo new JsonResponse($venue);

			$app->close();
		}
		else
		{
			echo new JsonResponse(1, Text::_('COM_JTICKETING_VENUE_NOT_EXIST'));

			$app->close();
		}
	}
}
