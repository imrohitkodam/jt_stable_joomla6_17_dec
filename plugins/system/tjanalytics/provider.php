<?php
/**
 * @package    PlgSystemTjAnalytics
 * @author     Techjoomla <extensions@techjoomla.com>
 *
 * @copyright  Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\Filesystem\File;
use Joomla\Registry\Registry;

/**
 * Class for Techjoomla Analytics Provider
 *
 * @since  1.1.0
 */
class TJAnalyticsProvider
{
	public static $analyticsProvider = null;

	/**
	 * Get an analyticsProvider object.
	 *
	 * Returns the global analyticsProvider object, only creating it if it doesn't already exist.
	 *
	 * @param   string    $provider  Provider name from plugin config
	 * @param   Registry  $params    Plugin config
	 *
	 * @return  object
	 *
	 * @since   1.1.0
	 */
	public static function getInstance($provider, Registry $params)
	{
		if (!self::$analyticsProvider)
		{
			self::$analyticsProvider = self::createInstance($provider, $params);
		}

		return self::$analyticsProvider;
	}

	/**
	 * Create an analyticsProvider object.
	 *
	 * Returns the global analyticsProvider object, only creating it if it doesn't already exist.
	 *
	 * @param   string    $provider  Provider name from plugin config
	 * @param   Registry  $params    Plugin config
	 *
	 * @return  boolean|object
	 *
	 * @since   1.1.0
	 */
	protected static function createInstance($provider, Registry $params)
	{
		$path      = dirname(__FILE__) . '/providers/' . $provider . '.php';
		$className = 'TJAnalyticsProvider' . $provider;

		if (!File::exists($path))
		{
			Factory::getApplication()->enqueueMessage($path . ' -- does not exist', 'error');

			return false;
		}

		require_once $path;

		if (!class_exists($className))
		{
			Factory::getApplication()->enqueueMessage('Class ' . $className . ' not found', 'error');

			return false;
		}

		return new $className($params);
	}
}
