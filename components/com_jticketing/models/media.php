<?php
/**
 * @package     JTicketing
 * @subpackage  com_jticketing
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\Filesystem\File;
use Joomla\CMS\Image\Image;
use Joomla\CMS\Language\Text;

if (file_exists(JPATH_LIBRARIES . "/techjoomla/media/storage/local.php")) { require_once JPATH_LIBRARIES . "/techjoomla/media/storage/local.php"; }
if (file_exists(JPATH_LIBRARIES . "/techjoomla/media/xref.php")) { require_once JPATH_LIBRARIES . "/techjoomla/media/xref.php"; }

/**
 * Methods supporting a jticketing media.
 *
 * @since  2.0.0
 */
class JticketingModelMedia extends AdminModel
{
	private $fileStorage = 'local';

	private $fileAccess = 'public';

	/**
	 * Constructor
	 *
	 * @since   1.0
	 */
	public function __construct()
	{
		parent::__construct();
		$jtParams = ComponentHelper::getParams('com_jticketing');
		$this->storagePath = $jtParams->get('jticketing_media_upload_path', '/media/com_jticketing/events/');
	}

	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param   type    $type    The table type to instantiate
	 * @param   string  $prefix  A prefix for the table class name. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return	JTable	A database object
	 *
	 * @since	2.0
	 */
	public function getTable($type = 'Media', $prefix = 'JticketingTable', $config = array())
	{
		$app = Factory::getApplication();

		if ($app->isClient("administrator"))
		{
			return Table::getInstance($type, $prefix, $config);
		}
		else
		{
			$this->addTablePath(JPATH_ADMINISTRATOR . '/components/com_jticketing/tables');

			return Table::getInstance($type, $prefix, $config);
		}
	}

	/**
	 * Method to get the record form.
	 *
	 * @param   array    $data      An optional array of data for the form to interogate.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return    JForm    A JForm object on success, false on failure
	 *
	 * @since    2.0
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Initialise variables.
		$app = Factory::getApplication();

		// Get the form.
		$form = $this->loadForm('com_jticketing.media', 'media', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  mixed  The user id on success, false on failure.
	 *
	 * @since  2.0
	 */
	public function save($data)
	{
		$mediaData = array();

		if ($data)
		{
			if (isset($data['upload_type']) && $data['upload_type'] == "link")
			{
				$mediaData = $this->uploadLink($data);
			}
			elseif (isset($data['upload_type']) && $data['upload_type'] == "move")
			{
				$mediaData = $this->moveFile($data);
			}
			else
			{
				$mediaData = $this->uploadFile($data);
			}
		}

		if ($mediaData)
		{
			$mediaData['storage'] = $this->fileStorage;
			$mediaData['created_by'] = isset($data['created_by']) ? $data['created_by'] : Factory::getUser()->id;
			$mediaData['created_date'] = Factory::getDate()->toSql();
			$mediaData['access'] = $this->fileAccess;
			$mediaData['params'] = '';

			if (parent::save($mediaData))
			{
				$mediaData['id'] = $this->getState($this->getName() . '.id');
				$mediaType = explode(".", $mediaData['type']);
				$mediaPath = Uri::root() . $this->storagePath;

				if ($mediaType[0] == 'image')
				{
					$mediaData['media'] = $mediaPath . '/images/' . $mediaData['source'];
					$mediaData['media_s'] = $mediaPath . '/images/S_' . $mediaData['source'];
					$mediaData['media_m'] = $mediaPath . '/images/M_' . $mediaData['source'];
					$mediaData['media_l'] = $mediaPath . '/images/L_' . $mediaData['source'];
				}
				elseif ($mediaType[0] == 'video')
				{
					if ($mediaType[1] == 'youtube')
					{
						$mediaData['media'] = $mediaData['source'];
					}
					else
					{
						$mediaData['media'] = $mediaPath . '/videos/' . $mediaData['source'];
					}
				}
				elseif ($mediaType[0] == 'application')
				{
					$mediaData['media'] = $mediaPath . '/applications/' . $mediaData['source'];
				}
				elseif ($mediaType[0] == 'audio')
				{
					$mediaData['media'] = $mediaPath . '/audios/' . $mediaData['source'];
				}

				return $mediaData;
			}
		}

		return;
	}

	/**
	 * Method to get a single record.
	 *
	 * @param   integer  $pk  The id of the primary key.
	 *
	 * @return	mixed	Object on success, false on failure.
	 *
	 * @since	2.0
	 */
	public function getItem($pk = null)
	{
		if ($item = parent::getItem($pk))
		{
			$jtParams = ComponentHelper::getParams('com_jticketing');
			$mediaType = explode(".", $item->type);
			$path = $jtParams->get('jticketing_media_upload_path', 'media/com_jticketing/events');
			$mediaPath = Uri::root() . $path;
			$item->media = '';

			if ($mediaType[0] == 'image')
			{
				$item->media = $mediaPath . '/images/' . $item->source;
				$item->media_s = $mediaPath . '/images/S_' . $item->source;
				$item->media_m = $mediaPath . '/images/M_' . $item->source;
				$item->media_l = $mediaPath . '/images/L_' . $item->source;
			}
			elseif ($mediaType[0] == 'video')
			{
				if ($mediaType[1] == 'youtube')
				{
					$item->media = $item->source;
				}
				else
				{
					$item->media = $mediaPath . '/videos/' . $item->source;
				}
			}
			elseif ($mediaType[0] == 'application')
			{
				$item->media = $mediaPath . '/applications/' . $item->source;
			}
			elseif ($mediaType[0] == 'audio')
			{
				$item->media = $mediaPath . '/audios/' . $item->source;
			}

			return $item;
		}

		return false;
	}

	/**
	 * Method to delete media record
	 *
	 * @param   string  &$mediaId  post data
	 *
	 * @return	JForm	A JForm object on success, false on failure
	 *
	 * @since   2.0
	 */
	public function delete(&$mediaId)
	{
		$result    = $this->getTable('mediaxref');
		$result->load(array('media_id' => (int) $mediaId));

		PluginHelper::importPlugin('system');
		Factory::getApplication()->triggerEvent('onBeforeJtMediaDelete', array($mediaId));

		if ($result->id)
		{
			$modelMediaXref = BaseDatabaseModel::getInstance('MediaXref', 'JTicketingModel');
			$modelMediaXref->delete($result->id);
		}

		$media = $this->getItem($mediaId);
		$filePath = JPATH_ROOT . '/media/com_jticketing/events/images/' . $media->source;

		if (parent::delete($mediaId))
		{
			if (File::exists($filePath))
			{
				File::delete($filePath);

				return true;
			}
		}

		return false;
	}

	/**
	 * Method to upload the file (image/video/PDF/Audio)
	 *
	 * @param   ARRAY|Object  $files   fileData
	 *
	 * @param   STRING        $path    file path
	 *
	 * @param   Integer       $access  access for uploading
	 *
	 * @return	array
	 *
	 * @since   2.0
	 */
	public function uploadFile($files, $path, $access = null)
	{
		$jtParams   = ComponentHelper::getParams('com_jticketing');

		// Image and video specific validation
		$mediaMaxSize = $jtParams->get('jticketing_media_size', '15');

		$config                                               = array();
		$config['uploadPath']                                 = $path;
		$config['saveData']                                   = 1;
		$config['state']                                      = '0';
		$config['size']                                       = $mediaMaxSize;

		if ($access !== null)
		{
			$config['auth'] = $access;
		}

		$config['imageResizeSize']                            = array();
		$config['imageResizeSize']['small']['small_width']    = $jtParams->get('small_width', '128');
		$config['imageResizeSize']['small']['small_height']   = $jtParams->get('small_height', '128');
		$config['imageResizeSize']['medium']['medium_width']  = $jtParams->get('medium_width', '240');
		$config['imageResizeSize']['medium']['medium_height'] = $jtParams->get('medium_height', '240');
		$config['imageResizeSize']['large']['large_width']    = $jtParams->get('large_width', '400');
		$config['imageResizeSize']['large']['large_height']   = $jtParams->get('large_height', '400');

		$mediaLib = TJMediaStorageLocal::getInstance($config);

		return $mediaLib->upload($files);
	}

	/**
	 * Method to upload video file link
	 *
	 * @param   array  $data  post data
	 *
	 * @return	array
	 *
	 * @since   2.0
	 */
	public function uploadLink($data)
	{
		$config   = array();
		$mediaLib = TJMediaStorageLocal::getInstance($config);

		return $mediaLib->uploadLink($data);
	}

	/**
	 * Method to create small, medium and large images of original image
	 *
	 * @param   string  $src       source path with file name
	 *
	 * @param   string  $imgPath   destination path
	 *
	 * @param   string  $fileName  new file name
	 *
	 * @return	return
	 *
	 * @since   2.0
	 */
	public function resizeImage($src, $imgPath, $fileName)
	{
		// Creating a new JImage object, passing it an image path
		$image = new Image($src);
		$file = explode(".", $fileName);
		$destPath = JPATH_SITE . '/' . $imgPath;
		$format = '';
		$jtParams = ComponentHelper::getParams('com_jticketing');

		if ($file[1] == 'jpeg' || $file[1] == 'jpg')
		{
			$format = IMAGETYPE_JPEG;
		}
		elseif ($file[1] == 'png')
		{
			$format = IMAGETYPE_PNG;
		}
		elseif ($file[1] == 'gif')
		{
			$format = IMAGETYPE_GIF;
		}

		// Small image
		if ($format)
		{
			$smallWidth = $jtParams->get('small_width', '128');
			$smallHeight = $jtParams->get('small_height', '128');
			$destFile = 'S_' . $fileName;
			$newImage = $image->resize($smallWidth, $smallHeight);
			$newImage->toFile($destPath . $destFile, $format);
		}

		// Medium image
		if ($format)
		{
			$mediumWidth = $jtParams->get('medium_width', '240');
			$mediumHeight = $jtParams->get('medium_height', '240');
			$destFile = 'M_' . $fileName;
			$newImage = $image->resize($mediumWidth, $mediumHeight);
			$newImage->toFile($destPath . $destFile, $format);
		}

		// Large image
		if ($format)
		{
			$largeWidth = $jtParams->get('large_width', '400');
			$largeHeight = $jtParams->get('large_height', '400');
			$destFile = 'L_' . $fileName;

			// Resize the image using the SCALE_INSIDE method
			$newImage = $image->resize($largeWidth, $largeHeight);

			// Write it to disk
			$newImage->toFile($destPath . $destFile, $format);
		}

		return true;
	}

	/**
	 * Method to Move the file
	 *
	 * @param   ARRAY  $fileData  fileData
	 *
	 * @return	return
	 *
	 * @since   2.0
	 */
	public function moveFile($fileData)
	{
		$fileName = File::makeSafe($fileData['name']);
		$fileType = explode(".", $fileData['type']);
		$type = strtolower($fileType[0]);
		$fileExt = strtolower(File::getExt($fileName));
		$sourceFile = Factory::getDate()->format('YmdHism') . '-' . rand(1, 5) . '.' . $fileExt;
		$destPath = $this->storagePath . '/images/' . $sourceFile;

			if (File::move($fileData['tmp_name'], JPATH_SITE . '/' . $destPath))
			{
				$this->resizeImage(JPATH_SITE . '/' . $destPath, $this->storagePath . '/images/', $sourceFile);

				$returnData = array();

				// File original name
				$returnData['name'] = $fileName;
				$returnData['original_filename'] = $fileName;
				$returnData['type'] = $fileData['type'];
				$returnData['source'] = $sourceFile;
				$returnData['size'] = '';
				$returnData['path'] = $this->storagePath;

				return $returnData;
			}

		return false;
	}

	/**
	 * Method to check the media data is exist for deleting
	 *
	 * @param   Integer  $mediaId    Media Data Id
	 * @param   Integer  $client_id  Event Id
	 *
	 * @return	boolean
	 *
	 * @since   2.0
	 */
	public function checkMediaDataIsExist($mediaId, $client_id)
	{
		$user = Factory::getUser();
		$uid = $user->id;

		$authorise = ($user->authorise("core.delete", 'com_jticketing') == 1 ? true : false);

		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select(array('m.media_id, m.client_id, m.client, jtm.id, jtm.created_by'));
		$query->from($db->qn('#__media_files_xref', 'm'));
		$query->join('LEFT', $db->qn('#__jticketing_media_files', 'jtm') . 'ON (' . $db->qn('m.media_id') . ' = ' . $db->qn('jtm.id') . ')');
		$query->where($db->qn('m.media_id') . ' = ' . (int) $mediaId);
		$query->where($db->qn('m.client_id') . ' = ' . (int) $client_id);
		$query->where($db->qn('m.client') . ' = ' . $db->quote('com_jticketing.event'));
		$db->setQuery($query);
		$mediaData = $db->loadAssoc();

		// If I don't have access or if I am not admin
		if (!$authorise || !$user->authorise('core.admin'))
		{
			return false;
		}
		// I have access to delete, but this media is not mine
		elseif ($user->id != $mediaData['created_by'])
		{
			return false;
		}
		// Media doesn't exist
		elseif (empty($mediaData))
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	/**
	 * Method to get a event's media files from media xref.
	 *
	 * @param   INT  $clientId    event id
	 *
	 * @param   INT  $clientName  clientName
	 *
	 * @param   INT  $isGallery   isGallery
	 *
	 * @return array.
	 *
	 * @since	2.0
	 */
	public function getEventMedia($clientId, $clientName, $isGallery = 0)
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName(array('id', 'media_id', 'client_id')));
		$query->from($db->quoteName('#__tj_media_files_xref'));
		$query->where($db->quoteName('client_id') . '=' . (int) $clientId);
		$query->where($db->quoteName('is_gallery') . '=' . (int) $isGallery);
		$query->where($db->quoteName('client') . '=' . $db->quote($clientName));
		$db->setQuery($query);

		return $db->loadObjectList();
	}

	/**
	 * Method delete the file and the record from the table
	 *
	 * @param   Integer  $mediaId      media Id of files table
	 *
	 * @param   STRING   $storagePath  file path from params in config
	 *
	 * @param   STRING   $client       client(example -'com_jticketing.event')
	 *
	 * @param   Integer  $clientId     clientId
	 *
	 * @return	boolean
	 *
	 * @since   2.4.0
	 */
	public function deleteMedia($mediaId, $storagePath, $client, $clientId)
	{
		PluginHelper::importPlugin('system');
		Factory::getApplication()->triggerEvent('onBeforeJtMediaDelete', array($mediaId));

		if (file_exists(JPATH_LIBRARIES . "/techjoomla/media/tables/xref.php")) { require_once JPATH_LIBRARIES . "/techjoomla/media/tables/xref.php"; }
		if (file_exists(JPATH_LIBRARIES . "/techjoomla/media/tables/files.php")) { require_once JPATH_LIBRARIES . "/techjoomla/media/tables/files.php"; }
		$tableXref = Table::getInstance('Xref', 'TJMediaTable');

		$checkMediaDataExist = 0;

		// User allowed to delete only self added media. Check here media is present.
		if ($clientId)
		{
			$data = array('client_id' => $clientId, 'client' => $client, 'media_id' => $mediaId);
			$checkMediaDataExist = $tableXref->load($data);
		}

		$filetable = Table::getInstance('Files', 'TJMediaTable');

		// Load the object based on the id or throw a warning.
		$filetable->load($mediaId);

		$mediaType = explode(".", $filetable->type);
		$deletePath = $storagePath . '/' . $mediaType[0] . 's';

		$mediaPresent  = $tableXref->load(array('media_id' => $mediaId));

		// If the media is present against the client the delete the media and record form xref.
		if ($checkMediaDataExist)
		{
			$config = array('id' => $tableXref->id);
			$mediaXrefLib = TJMediaXref::getInstance($config);

			// Delete record form xref table
			if (!$mediaXrefLib->delete())
			{
				$this->setError(Text::_($mediaXrefLib->getError()));
			}
			else
			{
				$xrefMediaPresent = $tableXref->load(array('media_id' => $mediaId));

				if (!$xrefMediaPresent)
				{
					$mediaConfig = array('id' => $mediaId, 'uploadPath' => $deletePath);

					$mediaLib = TJMediaStorageLocal::getInstance($mediaConfig);

					if ($mediaLib->id)
					{
						if (!$mediaLib->delete())
						{
							$this->setError(Text::_($mediaLib->getError()));

							return false;
						}
					}
					else
					{
						$this->setError(Text::_($mediaXrefLib->getError()));

						return false;
					}
				}
				else
				{
					$this->setError(Text::_($xrefMediaPresent->getError()));

					return false;
				}

				return true;
			}
		}
		// If only media is present example client_id = 0
		elseif(!$mediaPresent)
		{
			$mediaLib = TJMediaStorageLocal::getInstance($mediaConfig = array('id' => $mediaId, 'uploadPath' => $deletePath));

			if ($mediaLib->id)
			{
				if ($mediaLib->delete())
				{
					return true;
				}
				else
				{
					$this->setError(Text::_($mediaLib->getError()));

					return false;
				}
			}
		}
		else
		{
			$this->setError(Text::_('COM_JTICKETING_MEDIA_FILE_USED'));

			return false;
		}
	}
}
