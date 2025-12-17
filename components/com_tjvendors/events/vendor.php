<?php
/**
 * @package     TJVendors
 * @subpackage  com_tjvendors
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;

$mailsPath = JPATH_SITE . '/components/com_tjvendors/helpers/mails.php';
if (file_exists($mailsPath)) {
	require_once $mailsPath;
}

/**
 * TJ-vendors triggers class for vendors.
 *
 * @since  2.1
 */
class TjvendorsTriggerVendor
{
	/**
	 * Method acts as a consturctor
	 *
	 * @since   1.0.0
	 */
	public function __construct()
	{
		$app = Factory::getApplication();
		$this->user = Factory::getUser();
		$this->tjvendorsMailsHelper = new TjvendorsMailsHelper;
	}

	/**
	 * Trigger for vendor after save
	 *
	 * @param   int      $vendorDetails  Vendor Details
	 * @param   boolean  $isNew          isNew = true / !isNew = false
	 *
	 * @return  void
	 */
	public function onAfterVendorSave($vendorDetails, $isNew)
	{
		switch ($isNew)
		{
			/* New Vendor is created */
			case true:
					/* Send mail on Vendor create */
					$this->tjvendorsMailsHelper->onAfterVendorCreate((object) $vendorDetails);
				break;

			/* Vendor is editted */
			case false:
					/* Send mail on Vendor edit */
					$this->tjvendorsMailsHelper->onAfterVendorEdit((object) $vendorDetails);
				break;
		}

		PluginHelper::importPlugin('tjvendors');
		Factory::getApplication()->triggerEvent('onAfterTjVendorsVendorSave', array($vendorDetails, $isNew));

		return;
	}
}
