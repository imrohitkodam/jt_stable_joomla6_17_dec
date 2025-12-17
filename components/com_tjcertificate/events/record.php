<?php
/**
 * @package     TJCertificate
 * @subpackage  com_tjcertificate
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2020 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;

require_once JPATH_ADMINISTRATOR . '/components/com_tjcertificate/libraries/mails.php';
require_once JPATH_LIBRARIES . '/techjoomla/tjnotifications/tjnotifications.php';

/**
 * Tjcertificate triggers class for record.
 *
 * @since  __DEPLOY_VERSION__
 */
class TjCertificateTriggerRecord
{
	/**
	 * Method acts as a consturctor
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct()
	{
		$app                            = Factory::getApplication();
		$this->menu                     = $app->getMenu();
		$this->params                   = ComponentHelper::getParams('com_tjcertificate');
		$this->siteConfig               = Factory::getConfig();
		$this->sitename                 = $this->siteConfig->get('sitename');
		$this->user                     = Factory::getUser();
		$this->tjnotifications          = new Tjnotifications;
		$this->tjCertificateMails       = new TjCertificateMails;
	}

	/**
	 * Trigger for record after save
	 *
	 * @param   OBJECT  $recordDetails  Record Details
	 * 
	 * @param   int     $isNew          isNew = true / !isNew = false
	 *
	 * @return  void
	 * 
	 * @since	__DEPLOY_VERSION__
	 */
	public function onAfterRecordSave($recordDetails, $isNew)
	{
		switch ($isNew && $recordDetails->notify)
		{
			case true:
					/* Send mail on record create */
					$this->tjCertificateMails->onAfterCreateRecord($recordDetails);
				break;
		}

		return;
	}

	/**
	 * Trigger for record state change
	 *
	 * @param   OBJECT  $recordDetails  Record Details
	 * 
	 * @param   int     $isPublished    isPublished = 1 / !isPublished = 0
	 *
	 * @return  void
	 * 
	 * @since	__DEPLOY_VERSION__
	 */
	public function onRecordStateChange($recordDetails, $isPublished)
	{
		switch ($isPublished)
		{
			case 1:
				/* Send mail on record approved */
				$this->tjCertificateMails->onAfterRecordStateChange($recordDetails, $isPublished);
				break;
		}

		return;
	}
}
