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
use Joomla\CMS\Response\JsonResponse;
use Joomla\CMS\Session\Session;
use Joomla\Filesystem\Folder;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

/**
 * The template controller
 *
 * @since  1.0.0
 */
class TjCertificateControllerTemplate extends FormController
{
	protected $htmlTemplateFile = 'template.html';

	protected $cssTemplateFile = 'template.css';
	/**
	 * Function to load default template
	 *
	 * @return  object  object
	 */
	public function loadDefaultTemplate()
	{
		if (!Session::checkToken('get'))
		{
			echo new JResponseJson(null, Text::_('JINVALID_TOKEN'), true);
		}
		else
		{
			$app   = Factory::getApplication();
			$input = $app->input;

			$defaultTemplate = $input->get('defaultTemplate', "");

			if (empty($defaultTemplate))
			{
				echo new JResponseJson(null, Text::_('COM_TJCERTIFICATE_ERROR_SOMETHING_WENT_WRONG'), true);

				return;
			}

			$templateDetails = explode(".", $defaultTemplate);
			$component       = $templateDetails[0];
			$templateFolder  = $templateDetails[1];

			$templatePath = MEDIA_ROOT . '/' . $component . '/' . TJ_CERTIFICATE_TEMPLATE_FOLDER . '/' . $templateFolder;

			if (!Folder::exists($templatePath))
			{
				echo new JResponseJson(null, Text::_('COM_TJCERTIFICATE_ERROR_INVALID_SAMPLE_CERTIFICATE'), true);

				return;
			}

			$htmlData = file_get_contents($templatePath . '/' . $this->htmlTemplateFile);
			$cssData  = file_get_contents($templatePath . '/' . $this->cssTemplateFile);

			$TjCertificateTemplate = TJCERT::Template();

			$templateData = $TjCertificateTemplate->getEmogrify($htmlData, $cssData);

			if (!$templateData)
			{
				echo new JResponseJson(null, Text::_('COM_TJCERTIFICATE_ERROR_SOMETHING_WENT_WRONG'), true);

				return;
			}

			$templateData = stripslashes($templateData);

			echo new JsonResponse($templateData);
		}
	}

	/**
	 * Function to load custom template
	 *
	 * @return  object|void  object
	 */
	public function loadCustomTemplate()
	{
		if (!Session::checkToken('get'))
		{
			echo new JsonResponse(null, Text::_('JINVALID_TOKEN'), true);
		}
		else
		{
			$app   = Factory::getApplication();
			$input = $app->input;
			$templateId = $input->get('templateId');

			if (empty($templateId))
			{
				echo new JsonResponse(null, Text::_('COM_TJCERTIFICATE_ERROR_SOMETHING_WENT_WRONG'), true);

				return;
			}

			$tjCertificateTemplate = TJCERT::Template($templateId);

			echo new JsonResponse($tjCertificateTemplate->body);
		}
	}
}
