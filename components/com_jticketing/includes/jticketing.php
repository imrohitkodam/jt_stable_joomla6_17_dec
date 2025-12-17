<?php
/**
 * @package     JTicketing
 * @subpackage  com_jticketing
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die();
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Table\Table;
use Joomla\String\StringHelper;
use Joomla\Filesystem\File;

require_once JPATH_SITE . '/components/com_jticketing/includes/defines.php';

/**
 * JTicketing factory class.
 *
 * This class perform the helpful operation required to JTicketing package
 *
 * @method  static JTicketingEventJticketing      event($id = null)
 * @method  static JTicketingVenue                venue($id = null)
 * @method  static JTicketingUtilities            utilities($id = null)
 * @method  static JTicketingTickettype           tickettype($id = null)
 * @method  static JTicketingOrderItem            orderItem($id = null)
 * @method  static JTicketingCoupon               coupon($id = null)
 * @method  static JTicketingOrder                order($id = null)
 * @method  static JTicketingAttendee             attendee($id = null)
 * @method  static JTicketingUser                 user($id = null)
 * @method  static JTicketingJtversion            Jtversion()
 * @method  static JTicketingAttendeeFieldValues  AttendeeFieldValues($id = null)
 *
 * @since  2.4.0
 */
class JT
{
	/**
	 * Holds the record of the loaded Jticketing classes
	 *
	 * @var    array
	 * @since  2.4.0
	 */
	private static $loadedClass = array();

	/**
	 * Holds the record of the component config
	 *
	 * @var    Joomla\Registry\Registry
	 * @since  2.5.0
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
	 * @since   2.4.0
	 **/
	public static function table($name, $config = array())
	{
		Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jticketing/tables');
		$table = Table::getInstance($name, 'JTicketingTable', $config);

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
	 * @since   2.4.0
	 **/
	public static function model($name, $config = array())
	{
		BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_jticketing/models', 'JticketingModel');
		$model = BaseDatabaseModel::getInstance($name, 'JticketingModel', $config);

		return $model;
	}

	/**
	 * Magic method to create instance of JTicketing library
	 *
	 * @param   string  $name       The name of the class
	 * @param   mixed   $arguments  Arguments of class
	 *
	 * @return  mixed   return the Object of the respective class if exist OW return false
	 *
	 * @since   2.4.0
	 **/
	public static function __callStatic($name, $arguments)
	{
		self::loadClass($name);

		$className = 'JTicketing' . StringHelper::ucfirst($name);

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
	 * @since   2.4.0
	 **/
	public static function loadClass($className)
	{
		if (! isset(self::$loadedClass[$className]))
		{
			$className = (string) StringHelper::strtolower($className);

			$path = JPATH_SITE . '/components/com_jticketing/includes/' . $className . '.php';

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
			self::$config = ComponentHelper::getParams('com_jticketing');
		}

		return self::$config;
	}

	/**
	 * Method to load a event properties
	 *
	 * @param   boolean  $returnId  Flag to return id or name
	 *
	 * @return  Mixed  Integer if $returnId = true else string
	 *
	 * @since   2.5.0
	 */
	public static function getIntegration($returnId = false)
	{
		$integration = self::config()->get('integration');

		if ($returnId)
		{
			return $integration;
		}

		switch ($integration)
		{
			case COM_JTICKETING_CONSTANT_INTEGRATION_JOMSOCIAL:
				$return = "com_community";
				break;
			case COM_JTICKETING_CONSTANT_INTEGRATION_NATIVE:
				$return = "com_jticketing";
				break;
			case COM_JTICKETING_CONSTANT_INTEGRATION_JEVENTS:
				$return = "com_jevents";
				break;
			case COM_JTICKETING_CONSTANT_INTEGRATION_EASYSOCIAL:
				$return = "com_easysocial";
				break;
			default:
				$return = "com_jticketing";
				break;
		}

		return $return;
	}

	/**
	 * Initializes the css, js and necessary dependencies
	 *
	 * @param   string  $location  The location where the assets needs to load
	 *
	 * @return  void
	 *
	 * @since   2.5.0
	 */
	public static function init($location = 'site')
	{
		static $loaded     = null;
		$docType           = Factory::getDocument()->getType();
		$versionClass      = self::jtversion();
		$tjAnalyticsPlugin = PluginHelper::getPlugin('system', 'tjanalytics');
		$loc               = isset($loaded[$location])?$loaded[$location]:'';

		if (!$loc && ($docType == 'html'))
		{
			if (file_exists(JPATH_ROOT . '/media/techjoomla_strapper/tjstrapper.php'))
			{
				require_once JPATH_ROOT . '/media/techjoomla_strapper/tjstrapper.php';
				TjStrapper::loadTjAssets('com_jticketing');
			}

			$version = '2.5.0';

			try
			{
				$version = $versionClass->getMediaVersion();
			}
			catch (Exception $e)
			{
				// Silence is peace :)
			}

			if (!defined('JTICKETING_LOAD_BOOTSTRAP_VERSION'))
			{
				$params = ComponentHelper::getParams('com_jticketing');

				if (Factory::getApplication()->isClient("administrator"))
				{
					$bsVersion = (JVERSION >= '4.0.0') ? 'bs5' : 'bs3';
				}
				else
				{
					$bsVersion = $params->get('bootstrap_version', '', 'STRING');

					if (empty($bsVersion))
					{
						$bsVersion = (JVERSION >= '4.0.0') ? 'bs5' : 'bs3';
					}
				}

				define('JTICKETING_LOAD_BOOTSTRAP_VERSION', $bsVersion);
			}

			$options = array("version" => $version);

			if (self::config()->get('ga_ec_analytics') && $tjAnalyticsPlugin)
			{
				HTMLHelper::script('media/tjanalytics/js/google.js', $options);
			}

			HTMLHelper::script('media/com_jticketing/js/init.min.js', $options);
			HTMLHelper::script('media/com_jticketing/js/ajax.min.js', $options);
			HTMLHelper::script('media/com_jticketing/js/steps.min.js', $options);
			HTMLHelper::script('media/com_jticketing/js/jticketing.min.js', $options);

			HTMLHelper::stylesheet('media/com_jticketing/css/jticketing.min.css', $options);
			HTMLHelper::stylesheet('media/com_jticketing/css/artificiers.min.css', $options);
			HTMLHelper::stylesheet('media/com_jticketing/css/jt-tables.min.css', $options);

			// Joomla 6: JVERSION check removed
		if (false) // Legacy < '5.0.0')
			{
				HTMLHelper::stylesheet('media/techjoomla_strapper/vendors/font-awesome/css/font-awesome.min.css', $options);
			}
			else 
			{
				HTMLHelper::stylesheet('media/techjoomla_strapper/vendors/font-awesome/css/font-awesome-6-5-1.min.css', $options);
			}

			if (File::exists(JPATH_ROOT . '/media/com_jticketing/css/custom.css'))
			{
				HTMLHelper::stylesheet('media/com_jticketing/css/custom.css', $options);
			}

			if (self::config()->get('load_bootstrap'))
			{
				if (JTICKETING_LOAD_BOOTSTRAP_VERSION == 'bs3')
				{
					HTMLHelper::stylesheet('media/techjoomla_strapper/bs3/css/bootstrap.min.css', $options);
				}
				else
				{
					HTMLHelper::stylesheet('media/vendor/bootstrap/css/bootstrap.min.css', $options);
				}
			}

			// @TODO do we really need this? Confirm and refactor it
			// Joomla 6: JVERSION check removed
		if (false) // Legacy < '4.0.0')
			{
				HTMLHelper::_('behavior.tabstate');
			}
			
			HTMLHelper::_('bootstrap.tooltip');
			HTMLHelper::_('behavior.multiselect');

			$loaded[$location] = true;
		}
	}

	/**
	 * Proxy class to create an empty object of a online event provider
	 *
	 * @param   JTicketingVenue  $venue  Venue object where online event information is stored
	 *
	 * @return  JTicketingEventOnline  A Jticketing online event object
	 *
	 * @since   3.0.0
	 */
	public static function onlineEvent(JTicketingVenue $venue)
	{
		PluginHelper::importPlugin('tjevents');
		$provider   = $venue->getOnlineProvider();
		$event = self::event();
		$eventClass = 'JTicketingEvent' . StringHelper::ucfirst($provider);
		$implementedInterface = class_implements($eventClass);

		if (!class_exists($eventClass) && (!in_array('JTicketingEventOnline', $implementedInterface)))
		{
			throw new UnexpectedValueException(Text::sprintf('COM_JTICKETING_ERROR_EVENT_CLASS_NOT_FOUND', $eventClass));
		}

		return new $eventClass($event, $venue);
	}
}
