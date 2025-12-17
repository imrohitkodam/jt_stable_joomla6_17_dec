<?php
/**
 * @package     TJCertificate
 * @subpackage  com_tjcertificate
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2020 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die();

use Joomla\CMS\Language\Text;

/**
 * TJCertificate language constant class for common methods
 *
 * @since  _DEPLOY_VERSION_
 */
class TJCertificateLanguage
{
	/**
	 * Language constants to be used in js
	 *
	 * @return   void
	 *
	 * @since   _DEPLOY_VERSION_
	 */
	public function JsLanguageConstant()
	{
		// Tjmedia js
		Text::script('COM_TJCERTIFICATE_CONFIRM_DELETE_ATTACHMENT');
		Text::script('COM_TJCERTIFICATE_MEDIA_INVALID_FILE_TYPE');
		Text::script('COM_TJCERTIFICATE_MEDIA_UPLOAD_ERROR');
		Text::script('COM_TJCERTIFICATE_DELETE_CERTIFICATE_MESSAGE');
		Text::script('COM_TJCERTIFICATE_EXPIRY_DATE_VALIDATION_MESSAGE');
	}
}
