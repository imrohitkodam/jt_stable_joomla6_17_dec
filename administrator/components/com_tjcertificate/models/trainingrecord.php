<?php
/**
 * @package     TJCertificate
 * @subpackage  com_tjcertificate
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Form\Form;
use Joomla\CMS\Table\Table;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Language\Text;

require_once JPATH_LIBRARIES . '/techjoomla/media/storage/local.php';

/**
 * TjCertificate Training Record Model.
 *
 * @since  __DEPLOY_VERSION__
 */
class TjCertificateModelTrainingRecord extends AdminModel
{
	/**
	 * @var null  Item data
	 * @since  __DEPLOY_VERSION__
	 */
	protected $item = null;

	protected $comMultiAgency = 'com_multiagency';

	public $params;

	public $user;

	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct($config = array())
	{
		$this->params = ComponentHelper::getParams('com_tjcertificate');
		$this->user   = Factory::getuser();

		parent::__construct($config);
	}

	/**
	 * Method to get a certificate.
	 *
	 * @param   integer  $pk  An optional id of the object to get, otherwise the id from the model state is used.
	 *
	 * @return  mixed    certificate data object on success, false on failure.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getItem($pk = null)
	{
		if ($item = parent::getItem($pk))
		{
			if (!empty($item->id))
			{
				// Do any procesing on fields here if needed
				BaseDatabaseModel::addIncludePath(JPATH_SITE . '/libraries/techjoomla/media/models');

				// Create TJMediaXref class object
				$modelMediaXref = BaseDatabaseModel::getInstance('Xref', 'TJMediaModel', array('ignore_request' => true));
				$modelMediaXref->setState('filter.clientId', $item->id);
				$modelMediaXref->setState('filter.client', 'com_tjcertificate');
				$mediaData = $modelMediaXref->getItems();

				$item->mediaData = $mediaData;
			}
		}

		return $item;
	}

	/**
	 * Method to get the record form.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  Form|boolean  A Form object on success, false on failure
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form         = $this->loadForm('com_tjcertificate.trainingrecord', 'trainingrecord', array('control' => 'jform', 'load_data' => $loadData));
		$loggedInuser = Factory::getUser();

		$app                  = Factory::getApplication();
		$integrateMultiagency = $this->params->get('enable_multiagency');

		if (!$loggedInuser->authorise('certificate.external.manage', 'com_tjcertificate'))
		{
			$form->setFieldAttribute('assigned_user_id', 'required', 'false');
		}

		return empty($form) ? false : $form;
	}

	/**
	 * Returns a Table object, always creating it.
	 *
	 * @param   string  $type    The table type to instantiate
	 * @param   string  $prefix  A prefix for the table class name. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  Table    A database object
	 */
	public function getTable($type = 'Certificates', $prefix = 'TjCertificateTable', $config = array())
	{
		Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_tjcertificate/tables');

		return Table::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return	mixed	The data for the form.
	 *
	 * @since	__DEPLOY_VERSION__
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = Factory::getApplication()->getUserState('com_tjcertificate.edit.trainingrecord.data', array());

		if (empty($data))
		{
			$data = $this->getItem();
			$data->assigned_user_id = $data->user_id;
		}

		return $data;
	}

	/** 
	 * Method to upload file for timelog activity
	 *
	 * @param   Array  $file  File field array
	 *
	 * @param   array  $data  The form data
	 *
	 * @return array
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public function uploadMedia($file, $data)
	{
		$user = Factory::getUser();

		if (!empty($file['cert_file']))
		{
			$filePath = TJCERT::getMediaPath();
			$uploadedFileExtension = strtolower($this->params->get('upload_extensions', '', 'STRING'));
			$fileExtensionType     = explode(',', $uploadedFileExtension);

			$config               = array();
			$config['type']       = $fileExtensionType;
			$config['size']       = $this->params->get('upload_maxsize', '10');
			$config['auth']       = true;

			if (!empty($file['cert_file']['name']))
			{
				$fileType             = explode("/", $file['cert_file']['type']);
				$config['title']      = $file['cert_file']['name'];
				$config['uploadPath'] = JPATH_SITE . '/' . $filePath . '/' . strtolower($fileType[0]);

				$media     = TJMediaStorageLocal::getInstance($config);
				$mediaData = $media->upload(array($file['cert_file']));

				if (!empty($media->getError()))
				{
					$errorFiles[] = $media->getError() . ' (' . $attachments['media_file']['name'] . ')';
				}
				elseif ($mediaData[0]['id'])
				{
					$uploadedMediaId = $mediaData[0];

					if (!empty($data['old_media_ids']))
					{
						if ($data['old_media_ids'] != $mediaId)
						{
							$this->deleteMedia($data['old_media_ids'], $filePath, 'com_tjcertificate', $data['id']);
						}
					}
				}
			}

			// Check error exist in file
			if (!empty($errorFiles))
			{
				$this->setError($errorFiles);
			}
		}

		return $uploadedMediaId;
	}

	/**
	 * Method to delete media record
	 *
	 * @param   Integer  $mediaId     media Id of files table
	 * @param   STRING   $deletePath  file path from params in config
	 * @param   STRING   $client      client(example -'com_timelog.activity')
	 * @param   Integer  $clientId    clientId(example - Timelog activity id)
	 *
	 * @return	boolean  True if successful, false if an error occurs.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function deleteMedia($mediaId, $deletePath, $client, $clientId)
	{
		require_once JPATH_LIBRARIES . '/techjoomla/media/tables/xref.php';
		require_once JPATH_LIBRARIES . '/techjoomla/media/tables/files.php';
		$tableXref = Table::getInstance('Xref', 'TJMediaTable');
		$filetable = Table::getInstance('Files', 'TJMediaTable');

		// CheckMediaDataExist will return 1 when media is present clientId is Report Id
		$checkMediaDataExist = $tableXref->load(array('media_id' => $mediaId, 'client_id' => $clientId));

		// Making file delete path
		$mediaPresent = $filetable->load($mediaId);

		$mediaType  = explode(".", $filetable->type);
		$deletePath = $deletePath . '/' . $mediaType[0];

		// If Media is present
		if ($checkMediaDataExist)
		{
			// Get Object which include Media xref + Media File data of provided Media xref id
			$mediaXrefLib = TJMediaXref::getInstance(array('id' => $tableXref->id));

			// If media is not deleted it will return false here
			if ($mediaXrefLib->delete())
			{
				// If media xref delete then delete main entry from media_files
				$mediaLib = TJMediaStorageLocal::getInstance(array('id' => $mediaId, 'uploadPath' => $deletePath));

				// Checking Media is present or not
				if ($mediaLib->id)
				{
					// If Media is not deleted
					if (!$mediaLib->delete())
					{
						return false;
					}
				}

				return true;
			}
			else
			{
				return false;
			}
		}
		elseif ($mediaPresent)
		{
			$mediaLib = TJMediaStorageLocal::getInstance(array('id' => $mediaId, 'uploadPath' => $deletePath));

			if ($mediaLib->id)
			{
				if ($mediaLib->delete())
				{
					return true;
				}
				else
				{
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Method to validate the form data.
	 *
	 * @param   \Form  $form   The form to validate against.
	 * @param   Array   $data   The data to validate.
	 * @param   string  $group  The name of the field group to validate.
	 *
	 * @return  array|boolean  Array of filtered data if valid, false otherwise.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function validate($form, $data, $group = null)
	{
		$return = true;
		$return = parent::validate($form, $data, $group);

		if (!empty($data['expired_on']) && $data['expired_on'] != '0000-00-00 00:00:00')
		{
			if ($data['issued_on'] > $data['expired_on'])
			{
				$this->setError(Text::_('COM_TJCERTIFICATE_EXPIRY_DATE_VALIDATION_MESSAGE'));
				$return = false;
			}
		}

		if (ComponentHelper::isEnabled($this->comMultiAgency) && $this->params->get('enable_multiagency'))
		{
			$manageOwn = $this->user->authorise('core.manage.own.agency.user', $this->comMultiAgency);
			$manage    = $this->user->authorise('core.manage.all.agency.user', $this->comMultiAgency);

			if ($manageOwn && empty($manage))
			{
				$model = TJCERT::model('Agency', array('ignore_request' => true));

				// Get agencies of logged-in user and assigned user
				$assignedUserAgencies = $model->getUserAgencies($data['assigned_user_id']);
				$loggedInUserAgencies = $model->getUserAgencies($this->user->id);

				// Convert object to array
				$assignedUserAgencyArr = array_column($assignedUserAgencies, 'id');
				$loggedInUserAgencyArr = array_column($loggedInUserAgencies, 'id');

				// Compare both users agencies
				$result = array_intersect($loggedInUserAgencyArr, $assignedUserAgencyArr);

				if (empty($result))
				{
					$this->setError(Text::_('COM_TJCERTIFICATE_ORGANIZATION_INVALID_USER'));
					$return = false;
				}
			}
		}

		return $return;
	}
}
