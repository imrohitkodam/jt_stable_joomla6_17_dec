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

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\Filesystem\Folder;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Response\JsonResponse;

/**
 * The certificate controller
 *
 * @since  1.0.0
 */
class TjCertificateControllerCertificate extends FormController
{
	/**
	 * The client for which the templates are being created.
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	protected $client;

	/**
	 * The extension for which the templates are being created.
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	protected $extension;

	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @since  __DEPLOY_VERSION__
	 * @see    BaseController
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		$app    = Factory::getApplication();
		$jinput = $app->input;

		if (empty($this->extension))
		{
			$this->extension = $jinput->get('extension', '');
		}

		if (empty($this->client))
		{
			$this->client = $jinput->get('client', '');
		}
	}

	/**
	 * Method to download issued certificate.
	 *
	 * @return  boolean|string Certificate pdf url.
	 *
	 * @since 1.0
	 */
	public function download()
	{
		$app   = Factory::getApplication();
		$input = $app->input;
		$uniqueCertificateId = $input->get('certificate', '');

		// Download for sending it in email
		$store = $input->get('store', '');

		if (empty($uniqueCertificateId))
		{
			$app->enqueueMessage(Text::_('COM_TJCERTIFICATE_ERROR_CERTIFICATE_EMPTY'), 'error');
			$app->redirect('index.php');
		}

		$certificate    = TJCERT::Certificate();

		$certificateObj = $certificate::validateCertificate($uniqueCertificateId);

		// If $uniqueCertificateId is not valid then object is empty so need to handle error (CALL TO A MEMBER FUNCTION CANDOWNLOAD() ON BOOLEAN)
		if (!$certificateObj->id)
		{
			$app->enqueueMessage(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
			$app->redirect('index.php');
		}

		// Check user having permission to download
		if (!$certificateObj->canDownload())
		{
			$app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'));
			$app->redirect('index.php');
		}

		if (!$certificateObj->id)
		{
			$app->enqueueMessage(Text::_('COM_TJCERTIFICATE_ERROR_CERTIFICATE_EXPIRED'), 'error');
			$app->redirect('index.php');
		}

		echo $certificateObj->pdfDownload($store);
	}

	/**
	 * Method to upload certificate image.
	 *
	 * @return  boolean|string image url.
	 *
	 * @since 1.0
	 */
	public function uploadCertificate()
	{
		$app           = Factory::getApplication();
		$input         = $app->input;
		$canvasOutput  = $input->get('image', '', 'RAW');
		$certificateId = $input->get('certificateId', '', 'STRING');
		$canvasOutput  = str_replace('data:image/png;base64,', '', $canvasOutput);

		// Replace all spaces with plus sign (helpful for larger images)
		$canvasOutput = str_replace(" ", "+", $canvasOutput);
		$canvasOutput = base64_decode($canvasOutput);
		$filename     = $certificateId . '.png';

		if (!Folder::exists(JPATH_SITE . '/media/com_tjcertificate/certificates/'))
		{
			Folder::create(JPATH_SITE . '/media/com_tjcertificate/certificates');
		}

		$filePath = 'media/com_tjcertificate/certificates/';
		$dir      = JPATH_SITE . '/' . $filePath;

		if (file_put_contents($dir . $filename, $canvasOutput))
		{
			echo new JsonResponse(Juri::root() . $filePath . $filename);
		}

		exit();
	}

	/**
	 * Gets the URL arguments to append to an item redirect.
	 *
	 * @param   integer  $recordId  The primary key id for the item.
	 * @param   string   $urlVar    The name of the URL variable for the id.
	 *
	 * @return  string  The arguments to append to the redirect URL.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function getRedirectToItemAppend($recordId = null, $urlVar = 'id')
	{
		$append = parent::getRedirectToItemAppend($recordId);

		if (!empty ($this->extension))
		{
			$append .= '&extension=' . $this->extension;
		}
		elseif (!empty ($this->client))
		{
			$append .= '&client=' . $this->client;
		}

		return $append;
	}

	/**
	 * Gets the URL arguments to append to a list redirect.
	 *
	 * @return  string  The arguments to append to the redirect URL.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function getRedirectToListAppend()
	{
		$append = parent::getRedirectToListAppend();

		if (!empty ($this->extension))
		{
			$append .= '&extension=' . $this->extension;
		}
		elseif (!empty ($this->client))
		{
			$append .= '&client=' . $this->client;
		}

		return $append;
	}
}
