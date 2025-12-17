<?php
/*------------------------------------------------------------------------
 # jticketing- J2Store
# ------------------------------------------------------------------------
# author    Ramesh Elamathi - Weblogicx India http://www.weblogicxindia.com
# copyright Copyright (C) 2012 Weblogicxindia.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://j2store.org
# Technical Support:  Forum - http://j2store.org/forum/index.html
-------------------------------------------------------------------------*/


/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;

class JTicketingHelperUser
{

	
	/**
	 * Returns yes/no
	 * @param array [username] & [password]
	 * @param mixed Boolean
	 *
	 * @return array
	 */
	function login( $credentials, $remember=true, $return='' ) {

		$mainframe  = Factory::getApplication();

		if (strpos( $return, 'http' ) !== false && strpos( $return, Uri::base() ) !== 0) {
			$return = '';
		}

		$options = array();
		$options['remember'] = (boolean)$remember;
		//$options['return'] = $return;

		//preform the login action
		$success = $mainframe->login($credentials);

		if ( $return ) {
			$mainframe->redirect( $return );
		}

		return $success;
	}

	/**
	 * Returns yes/no
	 * @param mixed Boolean
	 * @return array
	 */
	function logout( $return='' ) {
		$mainframe  = Factory::getApplication();

		//preform the logout action//check to see if user has a joomla account
		//if so register with joomla userid
		//else create joomla account
		$success = $mainframe->logout();

		if (strpos( $return, 'http' ) !== false && strpos( $return, Uri::base() ) !== 0) {
			$return = '';
		}

		if ( $return ) {
			$mainframe->redirect( $return );
		}

		return $success;
	}

	/**
	 * Unblocks a user
	 *
	 * @param int $user_id
	 * @param int $unblock
	 * @return boolean
	 */
	function unblockUser($user_id, $unblock = 1)
	{
		$user =Factory::getUser( (int)$user_id );

		if ($user->get('id')) {
			$user->set('block', !$unblock);

			if (  ! $user->save()) {
				return false;
			}

			return true;
		}
		else {
			return false;
		}
	}

	/**
	 * Returns yes/no
	 * @param object
	 * @param mixed Boolean
	 * @return array
	 */
	function _sendMail( &$user, $details, $useractivation ) {
		$mainframe  = Factory::getApplication();

		$db		=Factory::getDbo();

		$name 		= $user->get('name');
		if(empty($name)) {
			$name 		= $user->get('email');
		}
		$email 		= $user->get('email');
		$username 	= $user->get('username');
		$activation	= $user->get('activation');
		$password 	= $details['password2']; // using the original generated pword for the email

		$usersConfig 	= ComponentHelper::getParams( 'com_users' );
		// $useractivation = $usersConfig->get( 'useractivation' );
		$sitename 		= $mainframe->get( 'sitename' );
		$mailfrom 		= $mainframe->get( 'mailfrom' );
		$fromname 		= $mainframe->get( 'fromname' );
		$siteURL		= Uri::base();

		$subject 	= Text::sprintf('J2STORE_ACCOUNT_DETAILS', $name, $sitename);
		$subject 	= html_entity_decode($subject, ENT_QUOTES);

		if ( $useractivation == 1 ){
			$message = Text::sprintf( 'J2STORE_SEND_MSG_ACTIVATE', $name, $sitename, $siteURL."index.php?option=com_user&task=activate&activation=".$activation, $siteURL, $email, $password);
		} else {
			$message = Text::sprintf('J2STORE_SEND_MSG', $name, $sitename, $siteURL, $email, $password );
		}

		$message = html_entity_decode($message, ENT_QUOTES);

		// Send email to user
		if ( ! $mailfrom  || ! $fromname ) {
			$fromname = $rows[0]->name;
			$mailfrom = $rows[0]->email;
		}

		$success = JticketingHelperUser::_doMail($mailfrom, $fromname, $email, $subject, $message);

		return $success;
	}

	/**
	 *
	 * @return unknown_type
	 */
	function _doMail( $from, $fromname, $recipient, $subject, $body, $actions=NULL, $mode=NULL, $cc=NULL, $bcc=NULL, $attachment=NULL, $replyto=NULL, $replytoname=NULL )
	{
		$success = false;

		$message =Factory::getMailer();
		$message->addRecipient( $recipient );
		$message->setSubject( $subject );
		$message->setBody( $body );
		$sender = array( $from, $fromname );
		$message->setSender($sender);
		$sent = $message->send();
		if ($sent == '1') {
			$success = true;
		}

		return $success;
	}
}
