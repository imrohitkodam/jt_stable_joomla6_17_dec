<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */


defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Plugin\PluginHelper;

if (!defined('DS'))
{
	define('DS', DIRECTORY_SEPARATOR);
}

/*load language file for plugin frontend*/
$lang = Factory::getLanguage();
$lang->load('plug_sys_jticketing', JPATH_ADMINISTRATOR);

/**
 * System plugin for jticketing to run cron and other
 *
 * @package     JTicketing
 * @subpackage  component
 * @since       1.0
 */
class PlgSystemplug_Sys_Jticketing extends CMSPlugin
{
	/**
	 * function called on after render page and runs cron
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function onAfterRender()
	{
		$mainframe = Factory::getApplication();

		if ($mainframe->isClient("administrator"))
		{
			return;
		}

		$com_params  = ComponentHelper::getParams('com_jticketing');
		$integration = $com_params->get('use_sys_plugin');

		// Send Emails using system plugin is config use_sys_plugin so
		$use_sys_plugin_cron = $com_params->get('use_sys_plugin_cron');

		if ($use_sys_plugin_cron)
		{
			$r = $this->process_email_queue($com_params);
		}
	}

	/**
	 * Process emnail Queue
	 *
	 * @param   string  $com_params  Jticketing params
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function process_email_queue($com_params)
	{
		$crontime = $this->params->get('crontime');
		$pkey_for_reminder = $com_params->get('pkey_for_reminder');
		$database = Factory::getDbo();
		$query    = "SELECT max(sent_date) AS last_email_date FROM #__jticketing_queue WHERE sent=1";
		$database->setQuery($query);

		// Get last email sent date
		$last_email_date = $database->loadResult();
		$present_time    = time();
		$jticketingfrontendhelper_path = JPATH_ROOT . '/components/com_jticketing/helpers/frontendhelper.php';
		$JticketingModelorders_path = JPATH_ROOT . '/components/com_jticketing/models/orders.php';

		if (!class_exists('jticketingfrontendhelper'))
		{
			JLoader::register('jticketingfrontendhelper', $jticketingfrontendhelper_path);
			JLoader::load('jticketingfrontendhelper');
		}

		if (!class_exists('JticketingModelorders'))
		{
			JLoader::register('JticketingModelorders', $JticketingModelorders_path);
			JLoader::load('JticketingModelorders');
		}

		$jticketingfrontendhelper = new jticketingfrontendhelper;
		$jticketingfrontendhelper->loadHelperClasses();

		// Calculate future time to send mails
		$future_time     = strtotime($last_email_date) + ($crontime * 60);
		$result          = "";

		if (!$last_email_date)
		{
			$last_email_date = time();
			$present_time    = $future_time = $last_email_date;
		}

		if ($present_time >= $future_time)
		{
			$plug_call        = 1;
			$input = Factory::getApplication()->input;
			$input->set('pkey', $pkey_for_reminder);
			$JticketingModelorders = new JticketingModelorders;
			$com_params->get('use_sys_plugin_cron');
			$result           = $JticketingModelorders->sendReminder($plug_call);
		}

		return $result;
	}

	/**
	 * Call this function after checkin
	 *
	 * @param   ARRAY  $data  checkin array
	 *
	 * @return  void
	 *
	 * @since   2.0
	 */
	public function onAfterJtEventCheckin($data)
	{
		JLoader::register('JticketingMailHelper', JPATH_SITE . '/components/com_jticketing/helpers/mail.php');

		$jticketingMailHelper = new JticketingMailHelper;

		$comParams = ComponentHelper::getParams('com_jticketing');

		// Send checkin mail
		if (!empty($data['checkin'] && $data['notify'] == 'on'))
		{
			$jticketingMailHelper->checkInMail($data);
		}

		// Added plugin trigger to be executed after check in done
		PluginHelper::importPlugin('tjevent');
		Factory::getApplication()->triggerEvent('onAfterEventAttendance', array($data));
	}

	/**
	 * To delete the user in techjoomlaapi_users table
	 *
	 * Method is called after user data is deleted from the database
	 *
	 * @param   array    $user     Holds the user data
	 * @param   boolean  $success  True if user was successfully stored in the database
	 * @param   string   $msg      Message
	 *
	 * @return  void
	 *
	 * @since   DEPLOY_VERSION
	 */
	public function onUserAfterDelete($user, $success, $msg)
	{
		if (!$success)
		{
			return false;
		}

		$userId = ArrayHelper::getValue($user, 'id', 0, 'int');

		$techJoomlaAPI = JT::techjoomlaapi();

		// To check wether id is present in techjoomlaapi_users table
		if ($techJoomlaAPI->loadByUserId($userId))
		{
			$techJoomlaAPI->delete();
		}
	}
}
