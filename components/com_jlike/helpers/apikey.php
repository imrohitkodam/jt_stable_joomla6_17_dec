<?php
/**
 * @package     JLike
 * @subpackage  com_jlike
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access.
defined('_JEXEC') or die();
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;

if (!defined('DS'))
{
	define('DS', DIRECTORY_SEPARATOR);
}

// Component Helper

/**
 * MigrateHelper helper
 *
 * @package     Jlike
 * @subpackage  site
 * @since       2.2
 */
class ApikeyHelper
{
	/**
	 * GetApiKey.
	 *
	 * @param   Object  $user  User Obj.
	 *
	 * @return void
	 *
	 * @since 3.0
	 */
	public function getApiKey($user)
	{
		$db = Factory::getDBO();
		$app = Factory::getApplication();

		try
		{
			$query = $db->getQuery(true);

			$query->select("hash");
			$query->from("#__api_keys");
			$query->where("userid=" . $user);

			$db->setQuery($query);

			return $count = $db->loadResult();
		}
		catch (Exception $e)
		{
			echo $e->getMessage();
			$app->enqueueMessage($e->getMessage(), "error");
		}
	}
}
