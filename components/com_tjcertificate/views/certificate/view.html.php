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
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Filesystem\File;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\MVC\View\HtmlView;

require_once JPATH_ADMINISTRATOR . '/components/com_tjcertificate/includes/tjcertificate.php';

/**
 * Certificate view
 *
 * @since  1.0.0
 */
class TjCertificateViewCertificate extends HtmlView
{
	public $certificate = null;

	public $uniqueCertificateId = null;

	public $showSearchBox = null;

	public $contentHtml = null;

	public $item;

	public $mediaPath = null;

	public $certificateUrl = null;

	public $fileName = null;

	public $downloadPermission = null;

	public $linkedInProfileUrl = null;

	public $imagePath = null;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a Error object.
	 *
	 * @since  1.0.0
	 */
	public function display($tpl = null)
	{
		$this->params = ComponentHelper::getParams('com_tjcertificate');
		$input        = Factory::getApplication()->input;

		$this->uniqueCertificateId = $input->get('certificate', '', 'STRING');
		$this->showSearchBox       = $input->getInt('show_search', $this->params->get('show_search_box'));
		$this->tmpl                = $input->get('tmpl', '', 'STRING');

		include_once  JPATH_SITE . '/components/com_tjcertificate/helpers/common.php';

		$app = Factory::getApplication();
		$tjcertificatehelper = new TJCertificateHelper();
		$itemId   = $tjcertificatehelper->getItemId('index.php?option=com_tjcertificate&view=certificates&layout=my');
		$redirectBackUrl = Route::_('index.php?option=com_tjcertificate&view=certificates&layout=my&Itemid=' . $itemId, false);

		if (!empty($this->uniqueCertificateId))
		{
			$certificate = TJCERT::Certificate();
			$this->certificate = $certificate::validateCertificate($this->uniqueCertificateId);
		}

		if (!$this->certificate)
		{
			$app->enqueueMessage(Text::_('COM_TJCERTIFICATE_ERROR_CERTIFICATE_EXPIRED'), 'error');

			$app->redirect($redirectBackUrl);
		}
		else if (!$this->certificate->id)
		{
			$app->enqueueMessage(Text::_('COM_TJCERTIFICATE_ERROR_CERTIFICATE_EXPIRED'), 'error');

			$app->redirect($redirectBackUrl);
		}
		elseif ($this->certificate->id)
		{
			// If certificate view is private then view is available only for certificate owner
			if (!$this->params->get('certificate_scope', '1') && Factory::getUser()->id != $this->certificate->getUserId())
			{
				$app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'error');

				$app->redirect($redirectBackUrl);
			}

			$this->fileName    = $this->certificate->unique_certificate_id . '.png';
			$this->mediaPath   = 'media/com_tjcertificate/certificates/';
			$this->certVersion = md5($this->certificate->issued_on);
			$this->imagePath   = Uri::root() . $this->mediaPath . $this->fileName . '?ver=' . $this->certVersion;

			$certificateUrl = 'index.php?option=com_tjcertificate&view=certificate&certificate=' . $this->certificate->unique_certificate_id;
			$this->certificateUrl = Uri::root() . substr(Route::_($certificateUrl), strlen(Uri::base(true)) + 1);
			$this->downloadPermission = $this->certificate->canDownload();

			if ($this->params->get('linkedin_profile_btn'))
			{
				$this->linkedInProfileUrl = $this->certificate->getAddToLinkedInProfileUrl();
			}

			// Get HTML
			$clientId = $this->certificate->getClientId();
			$client   = $this->certificate->getClient();
			$model = TJCERT::model('Certificate', array('ignore_request' => true));
			$this->contentHtml = $model->getCertificateProviderInfo($clientId, $client);

			PluginHelper::importPlugin('content');
			$result = Factory::getApplication()->triggerEvent('onGetCertificateClientData', array($clientId, $client));
			$this->item = $result[0];
		}

		parent::display($tpl);
	}
}
