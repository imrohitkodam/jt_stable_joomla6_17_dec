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
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Component\ComponentHelper;
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
class SocialintegrationHelper
{
	/**
	 * GetUserProfileUrl.
	 *
	 * @param   integer  $userid              User id.
	 * @param   string   $integration_option  integration_option.
	 *
	 * @return string
	 *
	 * @since 3.0
	 */
	public function getUserProfileUrl($userid, $integration_option = '')
	{
		if (empty($integration_option))
		{
			$helperPath = JPATH_SITE . '/components/com_jlike/helper.php';

			if (!class_exists('ComjlikeHelper'))
			{
				// Require_once $path;
				JLoader::register('ComjlikeHelper', $helperPath);
				JLoader::load('ComjlikeHelper');
			}

			$jlikehelperObj = new ComjlikeMainHelper;

			$integration_option = $jlikehelperObj->getSocialIntegration();
		}

		$comjlikeHelper     = new comjlikeHelper;
		$link               = '';

		if ($integration_option == 'joomla')
		{
			// $itemid=jgiveFrontendHelper::getItemId('option=com_users');
			$link = '';
		}
		elseif ($integration_option == 'cb')
		{
			$installed = $comjlikeHelper->Checkifinstalled('com_comprofiler');

			if ($installed)
			{
				$itemid = $comjlikeHelper->getItemId('option=com_comprofiler');
				$URL = 'index.php?option=com_comprofiler&task=userprofile&user=' . $userid . '&Itemid=' . $itemid;
				$link   = Uri::root() . substr(Route::_($URL), strlen(Uri::base(true)) + 1);
			}
		}
		elseif ($integration_option == 'js')
		{
			$installed = $comjlikeHelper->Checkifinstalled('com_community');

			if ($installed)
			{
				$link   = '';
				$jspath = JPATH_ROOT . DS . 'components' . DS . 'com_community';

				if (file_exists($jspath))
				{
					include_once $jspath . DS . 'libraries' . DS . 'core.php';

					$link = Uri::root() . substr(CRoute::_('index.php?option=com_community&view=profile&userid=' . $userid), strlen(Uri::base(true)) + 1);
				}
			}
		}
		elseif ($integration_option == 'jomwall')
		{
			$installed = $comjlikeHelper->Checkifinstalled('com_awdwall');

			if ($installed)
			{
				if (!class_exists('AwdwallHelperUser'))
				{
					require_once JPATH_SITE . DS . 'components' . DS . 'com_awdwall' . DS . 'helpers' . DS . 'user.php';
				}

				$awduser = new AwdwallHelperUser;
				$Itemid  = $awduser->getComItemId();
				$link    = Route::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $userid . '&Itemid=' . $Itemid);
			}
		}
		elseif ($integration_option == 'EasySocial')
		{
			$espath = JPATH_ROOT . DS . 'components' . DS . 'com_easysocial';

			if ($espath)
			{
				$link = '';

				if (file_exists($espath))
				{
					require_once JPATH_ADMINISTRATOR . '/components/com_easysocial/includes/foundry.php';
					$user = Foundry::user($userid);
					$link = Route::_($user->getPermalink());
				}
			}
		}

		return $link;
	}

	/**
	 * GetUserAvtar.
	 *
	 * @param   Object  $user  User Obj.
	 *
	 * @return string
	 *
	 * @since 3.0
	 */
	public function getUserAvatar($user)
	{
		$comjlikeHelper          = new comjlikeHelper;
		$socialintegrationHelper = new socialintegrationHelper;
		$userid                  = $user->id;
		$useremail               = $user->email;
		$params                  = ComponentHelper::getParams('com_jlike');
		$integration_option      = $params->get('integration');
		$uimage                  = '';

		if ($integration_option == "joomla")
		{
			$uimage = $socialintegrationHelper->get_gravatar($useremail, '40', 'mm', 'g');
		}
		elseif ($integration_option == "cb")
		{
			$installed = $comjlikeHelper->Checkifinstalled('com_comprofiler');

			if ($installed)
			{
				$uimage = $socialintegrationHelper->getCBUserAvatar($userid);
			}
		}
		elseif ($integration_option == "js")
		{
			$installed = $comjlikeHelper->Checkifinstalled('com_community');

			if ($installed)
			{
				$uimage = $socialintegrationHelper->getJomsocialUserAvatar($userid);
			}
		}
		elseif ($integration_option == "jomwall")
		{
			$installed = $comjlikeHelper->Checkifinstalled('com_awdwall');

			if ($installed)
			{
				$uimage = $socialintegrationHelper->getJomwallUserAvatar($userid);
			}
		}
		elseif ($integration_option == "EasySocial")
		{
			$uimage = $socialintegrationHelper->getEasySocialUserAvatar($userid);
		}

		return $uimage;
	}

	/**
	 * GetUserProfileUrl.
	 *
	 * @param   integer  $userid  User id.
	 *
	 * @return string
	 *
	 * @since 3.0
	 */
	public function getEasySocialUserAvatar($userid)
	{
		require_once JPATH_ADMINISTRATOR . '/components/com_easysocial/includes/foundry.php';
		$user   = Foundry::user($userid);
		$uimage = $user->getAvatar();

		return $uimage;
	}

	/**
	 * GetUserProfileUrl.
	 *
	 * @param   integer  $userid  User id.
	 *
	 * @return string
	 *
	 * @since 3.0
	 */
	public function getCBUserAvatar($userid)
	{
		$db = Factory::getDBO();
		$q  = "SELECT a.id,a.username,a.name, b.avatar, b.avatarapproved
			FROM #__users a, #__comprofiler b
			WHERE a.id=b.user_id AND a.id=" . $userid;
		$db->setQuery($q);
		$user     = $db->loadObject();
		$img_path = Uri::root() . "images/comprofiler";

		if (isset($user->avatar) && isset($user->avatarapproved))
		{
			if (substr_count($user->avatar, "/") == 0)
			{
				$uimage = $img_path . '/tn' . $user->avatar;
			}
			else
			{
				$uimage = $img_path . '/' . $user->avatar;
			}
		}
		elseif (isset($user->avatar))
		{
			$uimage = Uri::root() . "/components/com_comprofiler/plugin/templates/default/images/avatar/nophoto_n.png";
		}
		else
		{
			$uimage = Uri::root() . "/components/com_comprofiler/plugin/templates/default/images/avatar/nophoto_n.png";
		}

		return $uimage;
	}

	/**
	 * GetUserProfileUrl.
	 *
	 * @param   integer  $userid  User id.
	 *
	 * @return string
	 *
	 * @since 3.0
	 */
	public function getJomsocialUserAvatar($userid)
	{
		$mainframe = Factory::getApplication();
		/*included to get jomsocial avatar*/
		$uimage    = '';
		$jspath    = JPATH_ROOT . DS . 'components' . DS . 'com_community';

		if (file_exists($jspath))
		{
			include_once $jspath . DS . 'libraries' . DS . 'core.php';

			$user   = CFactory::getUser($userid);
			$uimage = $user->getThumbAvatar();

			if (!$mainframe->isClient("site"))
			{
				$uimage = str_replace('administrator/', '', $uimage);
			}
		}

		return $uimage;
	}

	/**
	 * GetJomwallUserAvatar.
	 *
	 * @param   integer  $userid  User id.
	 *
	 * @return string
	 *
	 * @since 3.0
	 */
	public function getJomwallUserAvatar($userid)
	{
		if (!class_exists('AwdwallHelperUser'))
		{
			require_once JPATH_SITE . DS . 'components' . DS . 'com_awdwall' . DS . 'helpers' . DS . 'user.php';
		}

		$awduser = new AwdwallHelperUser;
		$uimage  = $awduser->getAvatar($userid);

		return $uimage;
	}

	/**
	 * GetJomwallUserAvatar.
	 *
	 * @param   string   $email  email.
	 * @param   integer  $s      80.
	 * @param   string   $d      mm.
	 * @param   string   $r      g.
	 *
	 * @return string
	 *
	 * @since 3.0
	 */
	public function get_gravatar($email, $s = 80, $d = 'mm', $r = 'g')
	{
		$url = 'http://www.gravatar.com/avatar/';
		$url .= md5(strtolower(trim($email)));
		$url .= "?s=$s&d=$d&r=$r";

		return $url;
	}

	/**
	 * notification_sender : the user who send notification.
	 *
	 * @param   string  $notification_to      to.
	 * @param   string  $username             username.
	 * @param   string  $notification_sender  sender.
	 * @param   string  $notification_msg     msg.
	 * @param   string  $notify_url           notify_url.
	 *
	 * @return void
	 *
	 * @since 3.0
	 */
	public function send_es_notification($notification_to, $username, $notification_sender, $notification_msg, $notify_url = '')
	{
		require_once JPATH_ADMINISTRATOR . '/components/com_easysocial/includes/foundry.php';

		// $recipient - can be either array of user id or id or user objects
		$recipient[]  = $notification_sender;

		/* If you do not want to send email, $emailOptions should be set to false
		 $emailOptions - An array of options to define in the mail
		 Email template */
		$emailOptions = false;

		/* If you do not want to send system notifications, set this to false.
		$systemOptions - The internal system notifications
		System notification template */

		$myUser        = Foundry::user($notification_to);
		$title         = $myUser->getName() . " " . $notification_msg;

		$tem = array();
		$tem['uid'] = 'Jlike_notification';
		$tem['actor_id'] = $notification_to;

		// $tem['type'] = $Jlike_notifive;
		$tem['title'] = $title;

		if (empty($notify_url))
		{
			$notify_url = Route::_($myUser->getPermalink());
		}

		$tem['url'] = $notify_url;
		$tem['image'] = '';

		$systemOptions = $tem;
		Foundry::notify('Jlike_notification.create', $recipient, $emailOptions, $systemOptions);
	}

	/**
	 * notification_sender : the user who send notification.
	 *
	 * @param   string  $notification_to      to.
	 * @param   string  $username             username.
	 * @param   string  $notification_sender  sender.
	 * @param   string  $notification_msg     msg.
	 *
	 * @return void
	 *
	 * @since 3.0
	 */
	public function send_js_notification($notification_to, $username, $notification_sender, $notification_msg)
	{
		// $invitex_settings	= cominvitexHelper::getconfigData();
		// $to_direct        =  $invitex_settings["reg_direct"];

		// $activitysocialintegrationprofiledata=new activitysocialintegrationprofiledata();

		// $invitee_profile_url=JRoute::_($activitysocialintegrationprofiledata->getUserProfileUrl($to_direct,$notification_to));
		// $notification_subject='<a href="'.$invitee_profile_url.'" >'.$username.'</a>'. $notification_msg;

		$model = CFactory::getModel('Notification');
		$model->add($notification_to, $notification_sender, $notification_msg, 'notif_system_messaging', '0', '');
	}
}
