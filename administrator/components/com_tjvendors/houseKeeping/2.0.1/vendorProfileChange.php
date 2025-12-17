<?php
/**
 * @package     TJvendors
 * @subpackage  com_tjvendors
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2020 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Component\ComponentHelper;

/**
 * Update vendor default profile image path
 *
 * @since  __DEPLOY_VERSION__
 */
class TjHouseKeepingEmailTemplate extends TjModelHouseKeeping
{
	public $title       = "Update vendor's default profilelogo image path";
	public $description = "update vendor's default profilelogo image path";

	/**
	 * Method to update vendor's default profilelogo image path
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function migrate()
	{
		$result = array();

		try
		{
			$db    = Factory::getDbo();
			$query = $db->getQuery(true);
			$query->update("#__tjvendors_vendors AS tjv");
			$query->set("tjv.vendor_logo" . " = " . $db->quote('media/com_tjvendor/images/default.png'));
			$query->where("tjv.vendor_logo" . " = " . $db->quote('/administrator/components/com_tjvendors/assets/images/default.png'));
			$db->setQuery($query);
			$result = $db->execute();

			$result['status']  = true;
			$result['message'] = "Vendor's default profilelogo image path has updated successfully";

			return $result;
		}
		catch (Exception $e)
		{
			$result['err_code'] = '';
			$result['status']   = false;
			$result['message']  = $e->getMessage();
		}

		return $result;
	}
}
