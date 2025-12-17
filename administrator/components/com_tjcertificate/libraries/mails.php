<?php
/**
 * @package     TJCertificate
 * @subpackage  com_tjcertificate
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2020 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;
require_once JPATH_LIBRARIES . '/techjoomla/tjnotifications/tjnotifications.php';

use Joomla\CMS\Factory;
use Joomla\CMS\User\User;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\Registry\Registry;

/**
 * Class TjCertificateMails
 *
 * @since  __DEPLOY_VERSION__
 */
class TjCertificateMails
{
	protected $params;

	protected $siteConfig;

	protected $sitename;

	protected $siteadminname;

	protected $user;

	protected $client;

	protected $tjnotifications;

	protected $siteinfo;

	/**
	 * Method acts as a consturctor
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct()
	{
		$this->params              = ComponentHelper::getParams('com_tjcertificate');
		$this->siteConfig          = Factory::getConfig();
		$this->sitename            = $this->siteConfig->get('sitename');
		$this->siteadminname       = $this->siteConfig->get('fromname');
		$this->user                = Factory::getUser();
		$this->client              = TJCERT::getClient();
		$this->tjnotifications     = new Tjnotifications;

		$this->siteinfo            = new stdClass;
		$this->siteinfo->sitename  = $this->sitename;
	}

	/**
	 * Send mails when record is created
	 *
	 * @param   OBJECT  $recordDetails  Record Detail
	 *
	 * @return void
	 * 
	 * @since	__DEPLOY_VERSION__
	 */
	public function onAfterCreateRecord($recordDetails)
	{
		$adminRecipients = array();
		$db = Factory::getDbo();

		// Get all admin users
		$query = $db->getQuery(true);

		$query->select('id');
		$query->from($db->quoteName('#__users'));
		$query->where($db->quoteName('sendEmail') . '= 1');
		$db->setQuery($query);
		$adminUsers = $db->loadObjectList();

		foreach ($adminUsers as $adminUser)
		{
			$adminRecipients[]                = Factory::getUser($adminUser->id);
			$adminRecipients['email']['to'][] = Factory::getUser($adminUser->id)->email;
		}

		$userEmailArray = array();

		if ($recordDetails->getUserId())
		{
			$user = Factory::getUser($recordDetails->getUserId());
			$userEmailArray[] = $user->email;
		}

		$recipients   = array('email' => array('to' => $userEmailArray));

		$adminkey   = "createRecordMailToAdmin";
		$userkey    = "createRecordMailToUser";

		$siteInfo           = new stdClass;
		$siteInfo->sitename = $this->sitename;

		$replacements           = new stdClass;
		$replacements->info     = $this->siteinfo;
		$replacements->record   = $recordDetails;
		$replacements->user     = $user;

		$options = new Registry;
		$options->set('record', $recordDetails);

		// Mail to User after record added on behalf

		if ($recordDetails->getUserId() != $recordDetails->created_by)
		{
			$assignUserkey = "assignRecordMailToUser";
			$replacements->assigner = Factory::getUser($recordDetails->created_by);

			$this->tjnotifications->send($this->client, $assignUserkey, $recipients, $replacements, $options);
		}
		else
		{
			// Mail to site admin
			$this->tjnotifications->send($this->client, $adminkey, $adminRecipients, $replacements, $options);

			// Mail to User
			$this->tjnotifications->send($this->client, $userkey, $recipients, $replacements, $options);
		}

		return;
	}

	/**
	 * Send mail when record is edited
	 *
	 * @param   OBJECT  $recordDetails  Record Detail
	 * 
	 * @param   int     $isPublished    Record state
	 *
	 * @return void
	 * 
	 * @since	__DEPLOY_VERSION__
	 */
	public function onAfterRecordStateChange($recordDetails, $isPublished)
	{
		$userEmailArray = array();

		if ($recordDetails->user_id)
		{
			$recordOwner = Factory::getUser($recordDetails->user_id);
			$userEmailArray[] = $recordOwner->email;
		}

		$recipients   = array('email' => array('to' => $userEmailArray));

		$replacements         = new stdClass;
		$replacements->info   = $this->siteinfo;
		$replacements->record = $recordDetails;
		$replacements->user   = $recordOwner;

		$siteinfo = new stdClass;
		$siteinfo->sitename = $this->sitename;

		$options = new Registry;
		$options->set('record', $recordDetails);

		// Mail to record owner
		$this->tjnotifications->send($this->client, "recordApprovedMailToUser", $recipients, $replacements, $options);

		return;
	}
}
