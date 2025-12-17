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
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Table\Table;
use Joomla\String\StringHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\HTML\HTMLHelper;

$language = Factory::getLanguage();
$language->load('com_tjcertificate');

/**
 * Certificate factory class.
 *
 * This class creates table and model object by instantiating
 *
 * @since  1.0.0
 */
class TJCERT
{
	/**
	 * Holds the record of the loaded TJCertificate classes
	 *
	 * @var    array
	 * @since  1.0.0
	 */
	private static $loadedClass = array();

	/**
	 * Holds the record of the component config
	 *
	 * @var    Joomla\Registry\Registry
	 * @since  1.0.0
	 */
	private static $config = null;

	public static $client = "com_tjcertificate";

	public static $mediaPath = "media/com_tjcertificate/external";

	/**
	 * Retrieves a table from the table folder
	 *
	 * @param   string  $name  The table file name
	 *
	 * @return	Table object
	 *
	 * @since 	1.0.0
	 **/
	public static function table($name)
	{
		Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_tjcertificate/tables');

		return Table::getInstance($name, 'TjCertificateTable');
	}

	/**
	 * Retrieves a model from the model folder
	 *
	 * @param   string  $name    The model name to instantiate
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return	BaseDatabaseModel object
	 *
	 * @since 	1.0.0
	 **/
	public static function model($name, $config = array())
	{
		BaseDatabaseModel::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_tjcertificate/models');

		return BaseDatabaseModel::getInstance($name, 'TjCertificateModel', $config);
	}

	/**
	 * Magic method to create instance of TJCertificate library
	 *
	 * @param   string  $name       The name of the class
	 * @param   mixed   $arguments  Arguments of class
	 *
	 * @return  mixed   return the Object of the respective class if exist or return false
	 *
	 * @since   1.0.0
	 **/
	public static function __callStatic($name, $arguments)
	{
		self::loadClass($name);

		$className = 'TJCertificate' . StringHelper::ucfirst($name);

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
	 * @since   1.0.0
	 **/
	public static function loadClass($className)
	{
		if (! isset(self::$loadedClass[$className]))
		{
			$className = (string) StringHelper::strtolower($className);

			$path = JPATH_ADMINISTRATOR . '/components/com_tjcertificate/libraries/' . $className . '.php';

			include_once $path;

			self::$loadedClass[$className] = true;
		}

		return self::$loadedClass[$className];
	}

	/**
	 * Initializes js lang constant dependencies
	 *
	 * @param   string  $location  The location where the assets needs to load
	 * 
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function init($location = 'site')
	{
		self::Language()->JsLanguageConstant();

		if ($location == 'site')
		{
			HTMLHelper::stylesheet('media/com_tjcertificate/vendors/font-awesome-4.1.0/css/font-awesome.min.css');
		}
	}

	/**
	 * Method to get client
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function getClient()
	{
		return self::$client;
	}

	/**
	 * Method to get external media path
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function getMediaPath()
	{
		return self::$mediaPath;
	}
}
