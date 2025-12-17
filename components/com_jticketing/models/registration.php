<?php
/**
 * @package     JTicketing
 * @subpackage  com_jticketing
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die();
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Usergroup;
use Joomla\CMS\User\UserHelper;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * Model for registration
 *
 * @package     JTicketing
 * @subpackage  component
 * @since       1.0
 */

class JticketingModelRegistration extends BaseDatabaseModel
{
	/**
	 * Constructor
	 *
	 * @since   1.0
	 */
	public function __construct()
	{
		parent::__construct();
		global $mainframe, $option;
		$mainframe = Factory::getApplication();
	}

	/**
	 * Method to store a client record
	 *
	 * @param   array  $data  data
	 *
	 * @return  boolean  status of action
	 *
	 * @since   1.0
	 */
	public function store($data)
	{
		global $mainframe;
		$mainframe = Factory::getApplication();
		$jinput = $mainframe->input;
		$id = $jinput->get('cid');
		$session = Factory::getSession();
		$db 	= Factory::getDbo();

		$user = Factory::getUser();

		// Joomla user entry
		if (!$user->id)
		{
			$jticketingModelregistration = new jticketingModelregistration;
			$query = "SELECT id FROM #__users WHERE email = '" . $data['user_email'] . "' or username = '" . $data['user_name'] . "'";
			$this->_db->setQuery($query);
			$userexist = $this->_db->loadResult();
			$userid = "";
			$randpass = "";

			if (!$userexist)
			{
				// Generate the random password & create a new user
				$utilities = JT::utilities();
				$randpass  = $utilities->generateRandomString(8);
				$userid    = $jticketingModelregistration->createnewuser($data, $randpass);
			}
			else
			{
				$message = Text::_('USER_EXIST');

				return false;
			}

			if ($userid)
			{
				PluginHelper::importPlugin('user');

				if (!$userexist)
				{
					$jticketingModelregistration->SendMailNewUser($data, $randpass);
				}

				$user 	= array();
				$options = array('remember' => Factory::getApplication()->getInput()->getBool('remember', false));

				// Tmp user details
				$user 	= array();
				$user['username'] = $data['user_name'];
				$options['autoregister'] = 0;
				$user['email'] = $data['user_email'];
				$user['password'] = $randpass;
				$mainframe->login(array('username' => $data['user_name'], 'password' => $randpass), array('silent' => true));
			}
		}

		return true;
	}

	/**
	 * Method to create new user
	 *
	 * @param   array   $data      data
	 * @param   string  $randpass  random password
	 *
	 * @return  integer  id User id
	 *
	 * @since   1.0
	 */
	public function createnewuser($data, $randpass)
	{
		global $message;
		$user 		= clone Factory::getUser();
		$user->set('username', $data['user_name']);
		$user->set('password1', $randpass);
		$user->set('name', $data['fnam']);
		$user->set('email', $data['user_email']);

		// Password encryption
		// $salt  = UserHelper::genRandomPassword(32);
		$crypt = UserHelper::hashPassword($user->password1);
		// $user->password = "$crypt:$salt";
		$user->password = $crypt;

		// User group/type
		$user->set('id', '');
		$user->set('usertype', 'Registered');

		// Joomla 6: JVERSION check removed
		if (false) // Legacy >= '1.6.0')
		{
			$userConfig = ComponentHelper::getParams('com_users');

			// Default to Registered.
			$defaultUserGroup = $userConfig->get('new_usertype', 2);
			$user->set('groups', array($defaultUserGroup));
		}
		else
		{
			$userGroup = Usergroup::load(array('title' => 'Registered'));
			$user->set('gid', $userGroup->id);
		}

		$date = Factory::getDate();
		$user->set('registerDate', $date->toSQL());

		// True on success, false otherwise
		if (!$user->save())
		{
			echo $message = "not created because of " . $user->getError();
		}
		else
		{
			$message = "created of username-" . $user->username . "and sent mail of details please check";
		}

		return $user->id;
	}

	/**
	 * Create a random character generator for password
	 *
	 * @param   integer  $length  data
	 * @param   string   $chars   characters
	 *
	 * @return  string  random password
	 *
	 * @since   1.0
	 */
	public function rand_str($length = 32, $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890')
	{
		// Length of character list
		$chars_length = (strlen($chars) - 1);

		// Start our string
		$string = $chars[rand(0, $chars_length)];

		// Generate random string
		for ($i = 1; $i < $length; $i = strlen($string))
		{
			// Grab a random character from our list
			$r = $chars[rand(0, $chars_length)];

			// Make sure the same two characters don't appear next to each other
			if ($r != $string[$i - 1])
			{
				$string .= $r;
			}
		}

		// Return the string
		return $string;
	}

	/**
	 * Create a random character generator for password
	 *
	 * @param   array   $data      data
	 * @param   string  $randpass  random password
	 *
	 * @return  boolean
	 *
	 * @since   1.0
	 */
	public function SendMailNewUser($data, $randpass)
	{
		$app = Factory::getApplication();
		$mailfrom = $app->get('mailfrom');
		$fromname = $app->get('fromname');
		$sitename = $app->get('sitename');

		$email = $data['user_email'];
		$subject = Text::_('JT_REGISTRATION_SUBJECT');
		$find1 = array('{sitename}');
		$replace1 = array($sitename);
		$subject = str_replace($find1, $replace1, $subject);

		$message = Text::_('JT_REGISTRATION_USER');
		$find = array('{firstname}','{sitename}','{register_url}','{username}','{password}');
		$replace = array($data['user_name'],$sitename,Uri::root(),$data['user_name'],$randpass);
		$message = str_replace($find, $replace, $message);

		Factory::getMailer()->sendMail($mailfrom, $fromname, $email, $subject, $message);
		$messageadmin = Text::_('JT_REGISTRATION_ADMIN');
		$find2 = array('{sitename}','{username}');
		$replace2 = array($sitename,$data['user_name']);
		$messageadmin = str_replace($find2, $replace2, $messageadmin);

		Factory::getMailer()->sendMail($mailfrom, $fromname, $mailfrom, $subject, $messageadmin);

		return true;
	}
}
