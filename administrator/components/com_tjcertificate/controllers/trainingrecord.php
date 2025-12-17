<?php
/**
 * @package     TJCertificate
 * @subpackage  com_tjcertificate
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2020 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\Filesystem\Folder;
use Joomla\Filesystem\File;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Router\Route;

require_once JPATH_LIBRARIES . '/techjoomla/media/storage/local.php';

/**
 * The Tj Certificate Training Record controller
 *
 * @since  __DEPLOY_VERSION__
 */
class TjCertificateControllerTrainingRecord extends FormController
{
	/**
	 * Method to save a training record data.
	 *
	 * @param   string  $key     The name of the primary key of the URL variable.
	 * @param   string  $urlVar  The name of the URL variable if different from the primary key (sometimes required to avoid router collisions).
	 *
	 * @return  boolean|void  Incase of error boolean and in case of success void
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function save($key = null, $urlVar = null)
	{
		// Check for request forgeries.
		$this->checkToken();
		$app      = Factory::getApplication();
		$user     = Factory::getUser();
		$recordId = $app->getInput()->getInt('id');
		$params   = ComponentHelper::getParams('com_tjcertificate');
		$task     = $this->getTask();

		if (!$user->id)
		{
			throw new Exception(Text::_('JERROR_ALERTNOAUTHOR'), 403);
		}

		$data  = $app->getInput()->get('jform', array(), 'array');

		$model = $this->getModel();

		// Validate the posted data.
		$form = $model->getForm($data, false);

		if (!$form)
		{
			throw new \Exception($model->getError(), 500);
		}

		$validData = $model->validate($form, $data);

		// Check for validation errors.
		if ($validData === false)
		{
			// Get the validation messages.
			$errors = $model->getErrors();

			if (!empty($errors))
			{
				// Push up to three validation messages out to the user.
				for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++)
				{
					if ($errors[$i] instanceof Exception)
					{
						$app->enqueueMessage($errors[$i]->getMessage(), 'warning');
					}
					else
					{
						$app->enqueueMessage($errors[$i], 'warning');
					}
				}
			}

			// Save the data in the session.
			$app->setUserState('com_tjcertificate.edit.trainingrecord.data', $data);

			// Redirect back to the edit screen.
			$this->setRedirect(Route::_('index.php?option=com_tjcertificate&view=trainingrecord&layout=edit&id=' . $recordId, false));

			return false;
		}

		if ($validData['assigned_user_id'])
		{
			$validData['user_id'] = $validData['assigned_user_id'];
		}
		else
		{
			$validData['user_id'] = $user->id;
		}

		$validData['client']      = "external";
		$validData['state']       = isset($validData['state']) ? $validData['state'] : "-1";
		$validData['is_external'] = 1;

		$file = $app->getInput()->files->get('jform', array(), 'array');

		if (!empty($file['cert_file']))
		{
			$validData['old_media_ids'] = $app->getInput()->get('oldFiles', 0, 'INT');
			$uploadData = $model->uploadMedia($file, $validData);
			$validData['cert_file'] = $uploadData['source'];
		}

		$certificateModel = TJCERT::model('Certificate', array('ignore_request' => true));

		$certificateModel->save($validData);

		$modelMediaXref = TJMediaXref::getInstance();

		if ($uploadData['id'])
		{
			$mediaData['id'] = '';
			$mediaData['client_id'] = $certificateModel->getState('certificate.id');
			$mediaData['media_id'] = $uploadData['id'];
			$mediaData['client'] = TJCERT::getClient();
			$modelMediaXref->bind($mediaData);
			$modelMediaXref->save();
		}

		$this->setMessage(Text::_('COM_TJCERTIFICATE_TRAINING_RECORD_SAVE_SUCCESSFULLY'));

		if ($task === "apply")
		{
			// Redirect back to the edit screen.
			$this->setRedirect(
				Route::_('index.php?option=com_tjcertificate&view=trainingrecord&layout=edit&id=' . $certificateModel->getState('certificate.id'), false)
				);
		}

		// Save task using to "Save & Close" action which is used only in backend
		if ($task === "save")
		{
			$site = $app->getInput()->get('site', 'f', 'string');

			if ($site == 'f')
			{
				// Redirect to the list screen.
				$this->setRedirect(
					Route::_('index.php?option=com_tjcertificate&view=certificates&layout=my', false)
				);
			}
			else
			{
				// Redirect to the list screen.
				$this->setRedirect(
					Route::_('index.php?option=com_tjcertificate&view=certificates', false)
				);
			}
		}

		// Flush the data from the session.
		$app->setUserState('com_tjcertificate.edit.trainingrecord.data', null);
	}

	/**
	 * Cancel operation
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function cancel($key = null)
	{
		// Check for request forgeries.
		$this->checkToken('request');

		// Clear data from session.
		Factory::getApplication()->setUserState('com_tjcertificate.edit.trainingrecord.data', null);

		$this->setRedirect(Route::_('index.php?option=com_tjcertificate&view=certificates&layout=my', false));
	}

	/**
	 * Downloads the file requested by user
	 *
	 * @return  boolean|void
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function downloadAttachment()
	{
		$app  = Factory::getApplication();
		$user = Factory::getUser();

		if (!$user->id)
		{
			throw new Exception(Text::_('JERROR_ALERTNOAUTHOR'), 403);

			return false;
		}

		$clientId = $app->getInput()->get('recordId', '', 'INT');
		$mediaId  = $app->getInput()->get('id', '', 'INT');

		$manageOwn = $user->authorise('certificate.external.manageown', 'com_tjcertificate');
		$manage    = $user->authorise('certificate.external.manage', 'com_tjcertificate');

		// If manageOwn permission then check record owner can only download own record
		if ($manageOwn && !$manage)
		{
			$table = TJCERT::table("certificates");
			$table->load(array('id' => (int) $clientId, 'user_id' => $user->id));

			if (!$table->id)
			{
				throw new Exception(Text::_('JERROR_ALERTNOAUTHOR'), 403);
			}
		}

		$params = ComponentHelper::getParams('com_tjcertificate');

		if (!$mediaId && !$clientId)
		{
			return false;
		}

		$config              = array();
		$config['mediaId']   = $mediaId;

		// Assign client id as Record Id
		$config['client_id'] = $clientId;
		$config['client']    = TJCERT::getClient();
		$mediaPath           = TJCERT::getMediaPath();
		$mediaAttachmentData = TJMediaXref::getInstance($config);
		$folderName          = explode('.', $mediaAttachmentData->media->type);

		$downloadPath        = JPATH_SITE . '/' . $mediaPath;
		$downloadPath        = $downloadPath . '/' . $folderName[0] . '/' . $mediaAttachmentData->media->source;

		$media               = TJMediaStorageLocal::getInstance();
		$media->downloadMedia($downloadPath);
	}
}
