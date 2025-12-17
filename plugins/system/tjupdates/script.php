<?php
/**
 * @package    PlgSystemTjupdates
 *
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (C) 2009 - 2020 Techjoomla. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access.
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;

/**
 * Plugin class for installation script.
 *
 * @package  PlgSystemTjupdates
 *
 * @since    1.0.2
 */
class PlgSystemTjupdatesInstallerScript
{
	/**
	 * Function to post flight
	 *
	 * @param   STRING  $type    type
	 *
	 * @param   ARRAY   $parent  parent
	 *
	 * @return  boolean true
	 *
	 * @since   1.0
	 *
	 */
	public function postflight($type, $parent)
	{
		// Enable plugin when installed
		if ($type == 'install')
		{
			$db = Factory::getDbo();
			$query = $db->getQuery(true);

			// Fields to update.
			$fields = array(
				$db->quoteName('enabled') . ' = ' . 1
			);

			// Conditions for which records should be updated.
			$conditions = array(
				$db->quoteName('element') . ' = ' . $db->quote('tjupdates'),
				$db->quoteName('type') . ' = ' . $db->quote('plugin'),
				$db->quoteName('folder') . ' = ' . $db->quote('system'),

			);

			$query->update($db->quoteName('#__extensions'))->set($fields)->where($conditions);
			$db->setQuery($query);
			$db->execute();
		}

		return true;
	}
}
