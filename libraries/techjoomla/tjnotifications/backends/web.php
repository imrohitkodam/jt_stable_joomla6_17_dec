<?php
/**
 * @package     Techjoomla.Libraries
 * @subpackage  Tjnotifications
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\LogEntry;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Registry\Registry;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

BaseDatabaseModel::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_tjnotifications/models', 'NotificationsModel');
BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_tjnotifications/models', 'NotificationsModel');

jimport('techjoomla.tjnotifications.backend');

/**
 * Tjnotifications class for web backend
 *
 * @package     Techjoomla.Libraries
 * @subpackage  Tjnotifications
 * @since       1.0
 */
class TjnotificationsBackendWeb extends TjnotificationsBackendBase
{
	/**
	 * Method to send the form data.
	 *
	 * @param   string  $client        A requird field same as component name.
	 * @param   string  $key           Key is unique in client.
	 * @param   array   $recipients    It's an array of user objects
	 * @param   object  $replacements  It is a object contains replacement.
	 * @param   object  $options       It is a object contains Jparameters like cc,bcc.
	 *
	 * @return  array|boolean
	 *
	 * @since 1.0
	 */
	public function send($client, $key, $recipients, $replacements, $options)
	{
		$params = ComponentHelper::getParams('com_tjnotifications');
		
		$systemOptions = array();
		
		if ($params->get('web_notification_provider', '', 'STRING') == 'easysocial')
		{
			$userId = self::getuserId($recipients['recipients']['web']['to'][0]);
			$language = Factory::getUser($userId)->getParam('language', Factory::getLanguage()->getTag());

			// To get user's specific language template
			$model       = ListModel::getInstance('Notifications', 'TjnotificationsModel', array('ignore_request' => true));
			$template    = $model->getTemplate($client, $key, $language, $backend = 'web');
			
			// If Key contain # then
			if (strpos($key, '#'))
			{
				// Regex for removing last part of the string
				// Eg if input string is global#vendor#course then the output is global#vendor
				$key = preg_replace('/#[^#]*$/', '', $key);
			}

			$systemOptions = array(
					'uid'       => $options->get('uniqueElementId'),
					'type'      => 'Tjnotifications',
					'cmd'       => $client . '.' . $key,
					'actorId'   => $options->get('from'),
					'actor_type'   => 'user',
					'target_id' => $options->get('to'),
					'target_type' => 'user',
					'title'     => parent::getBody($template->body, $replacements),
					'image'     => '',
					'url'       => $options->get('url')
				);
		}
		elseif ($params->get('web_notification_provider', '', 'STRING') == 'jomsocial')
		{
			$systemOptions['cmd']    = 'notif_system_messaging';
			$systemOptions['type']   = '0';
			$systemOptions['params']['url'] = $options->get('url');
		}
		
		// Send notification
		$from = Factory::getUser($options->get('from'));
		$to = Factory::getUser($options->get('to'));
		
		$this->socialLibraryObj = $this->getSocialLibraryObject();

		$notificationSend = $this->socialLibraryObj->sendNotification($from, $to, $options->get('actionDescription'), $systemOptions);

		return $notificationSend;
			
	}
	
	/**
	 * Get social library object depending on the integration set.
	 *
	 * @param   STRING  $integration_option  Soical integration set
	 *
	 * @return  Object Soical library object
	 *
	 * @since 1.0.0
	 */
	public function getSocialLibraryObject($integrationOption = '')
	{
		// Load main file
		if (file_exists(JPATH_LIBRARIES . '/techjoomla/jsocial/jsocial.php')) { require_once JPATH_LIBRARIES . '/techjoomla/jsocial/jsocial.php'; }
		if (file_exists(JPATH_LIBRARIES . '/techjoomla/jsocial/joomla.php')) { require_once JPATH_LIBRARIES . '/techjoomla/jsocial/joomla.php'; }

		if (!$integrationOption)
		{
			$params = ComponentHelper::getParams('com_tjnotifications');
			$integrationOption = $params->get('web_notification_provider', 'easysocial');
		}

		if ($integrationOption == 'easysocial')
		{
			if (file_exists(JPATH_LIBRARIES . '/techjoomla/jsocial/easysocial.php')) { require_once JPATH_LIBRARIES . '/techjoomla/jsocial/easysocial.php'; }
			$socialLibraryObject = new JSocialEasySocial;
		}
		elseif ($integrationOption == 'jomsocial')
		{
			if (file_exists(JPATH_LIBRARIES . '/techjoomla/jsocial/jomsocial.php')) { require_once JPATH_LIBRARIES . '/techjoomla/jsocial/jomsocial.php'; }
			$socialLibraryObject = new JSocialJomSocial;
		}

		return $socialLibraryObject;
	}
	
	/**
	 * Function to find the user id based on the emails in the mail object
	 *
	 * @param   string  $email  string of email addresses
	 *
	 * @return  integer  Integer or null
	 */
	protected static function getuserId($email)
	{
		if (!empty($email))
		{
			$db    = Factory::getDbo();
			$query = $db->getQuery(true);
			$query->select('id');
			$query->from($db->quoteName('#__users'));
			$query->where($db->quoteName('email') . " = '" . $email . "'");

			$db->setQuery($query);
			$result = $db->loadResult();

			if ($result)
			{
				return $result;
			}
			else
			{
				return null;
			}
		}
		else
		{
			return null;
		}
	}
}
