<?php
/**
 * @package     TJVendors
 * @subpackage  com_tjvendors
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Table\Table;
use Joomla\String\StringHelper;

/**
 * TJVendors factory class.
 *
 * This class perform the helpful operation required to  TJVendors package
 *
 * @since  1.4.0
 */
class TJVendors
{
	/**
	 * Holds the record of the loaded TJVendors classes
	 *
	 * @var    array
	 * @since  1.4.0
	 */
	private static $loadedClass = array();

	/**
	 * Holds the record of the component config
	 *
	 * @var    Joomla\Registry\Registry
	 * @since  1.4.0
	 */
	private static $config = null;

	/**
	 * Retrieves a table from the table folder
	 *
	 * @param   string  $name    The table file name
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  Table|boolean object or false on failure
	 *
	 * @since   1.4.0
	 **/
	public static function table($name, $config = array())
	{
		Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_tjvendors/tables');
		$table = Table::getInstance($name, 'TjvendorsTable', $config);

		return $table;
	}

	/**
	 * Retrieves a model from the model folder
	 *
	 * @param   string  $name    The model name
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  BaseDatabaseModel|boolean object or false on failure
	 *
	 * @since   1.4.0
	 **/
	public static function model($name, $config = array())
	{
		BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_tjvendors/models', 'TJVendorsModel');
		$model = BaseDatabaseModel::getInstance($name, 'TJVendorsModel', $config);

		return $model;
	}

	/**
	 * Magic method to create instance of Tjvendors library
	 *
	 * @param   string  $name       The name of the class
	 * @param   mixed   $arguments  Arguments of class
	 *
	 * @return  mixed   return the Object of the respective class if exist OW return false
	 *
	 * @since   1.4.0
	 **/
	public static function __callStatic($name, $arguments)
	{
		self::loadClass($name);

		$className = 'Tjvendors' . StringHelper::ucfirst($name);

		if (class_exists($className))
		{
			if (method_exists($className, 'getInstance'))
			{
				return call_user_func_array(array($className, 'getInstance'), $arguments);
			}

			return new $className;
		}

		return false;
	}

	/**
	 * Load the class library if not loaded
	 *
	 * @param   string  $className  The name of the class which required to load
	 *
	 * @return  boolean True on success
	 *
	 * @since   1.4.0
	 **/
	public static function loadClass($className)
	{
		if (! isset(self::$loadedClass[$className]))
		{
			$className = (string) StringHelper::strtolower($className);

			$path = JPATH_SITE . '/components/com_tjvendors/includes/' . $className . '.php';

			include_once $path;

			self::$loadedClass[$className] = true;
		}

		return self::$loadedClass[$className];
	}

	/**
	 * Load the component configuration
	 *
	 */
	public static function config()
	{
		if (empty(self::$config))
		{
			self::$config = ComponentHelper::getParams('com_tjvendors');
		}

		return self::$config;
	}

	/**
	 * Initializes the css, js and necessary dependencies
	 *
	 * @param   string  $location  The location where the assets needs to load
	 *
	 * @return  void
	 *
	 * @since   1.4.0
	 */
	public static function init($location = 'site')
	{
		static $loaded = null;
		$docType = Factory::getDocument()->getType();
		$versionClass = self::version();

		if (isset($loaded[$location]) && ($docType != 'html'))
		{
			return;
		}

		if (file_exists(JPATH_ROOT . '/media/techjoomla_strapper/tjstrapper.php'))
		{
			require_once JPATH_ROOT . '/media/techjoomla_strapper/tjstrapper.php';
			TjStrapper::loadTjAssets('com_tjvendors');
		}

		$version = $versionClass->getMediaVersion();
		$options = array("version" => $version);

		HTMLHelper::_('jquery.framework');
		HTMLHelper::_('script', 'media/com_tjvendor/dist/app.min.js', $options);
		HTMLHelper::_('script', 'media/com_tjvendor/js/tjvendors.js', $options);
		HTMLHelper::stylesheet('media/techjoomla_strapper/vendors/no-more-tables.css', $options);

		// Load Boostrap
		if (self::config()->get('load_bootstrap') == '1')
		{
			define('COM_TJVENDORS_WRAPPAER_CLASS', "tjBs3");
			HTMLHelper::_('stylesheet', 'media/techjoomla_strapper/bs3/css/bootstrap.min.css');
			HTMLHelper::_('stylesheet', 'media/techjoomla_strapper/vendors/font-awesome/css/font-awesome.min.css');
		}
		else
		{
			define('COM_TJVENDORS_WRAPPAER_CLASS', '');
		}

		$loaded[$location] = true;
	}
}
