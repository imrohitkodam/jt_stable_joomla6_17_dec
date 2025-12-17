<?php
/**
 * @package     ActivityStream
 * @subpackage  Privacy.activitystream
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2018 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later
 */

// No direct access.
defined('_JEXEC') or die();

use Joomla\CMS\User\User;
use Joomla\CMS\Table\User as UserTable;
use Joomla\CMS\Factory;

if (file_exists(JPATH_ADMINISTRATOR . '/components/com_privacy/helpers/plugin.php')) {
	require_once JPATH_ADMINISTRATOR . '/components/com_privacy/helpers/plugin.php';
}

if (file_exists(JPATH_ADMINISTRATOR . '/components/com_privacy/helpers/removal/status.php')) {
	require_once JPATH_ADMINISTRATOR . '/components/com_privacy/helpers/removal/status.php';
}

// Joomla 6 compatibility - define PrivacyPlugin if not exists
if (!class_exists('PrivacyPlugin')) {
	class PrivacyPlugin extends \Joomla\CMS\Plugin\CMSPlugin {
		protected function createDomain($name, $description = '') {
			return new class($name, $description) {
				public $name;
				public $description;
				public $items = [];
				public function __construct($name, $description) {
					$this->name = $name;
					$this->description = $description;
				}
				public function addItem($item) {
					$this->items[] = $item;
				}
			};
		}
		protected function createItemFromArray(array $data, $itemId = null) {
			return (object) $data;
		}
	}
}

if (!class_exists('PrivacyRemovalStatus')) {
	class PrivacyRemovalStatus {
		public $canRemove = true;
		public $reason = '';
	}
}

/**
 * Privacy plugin managing ActivityStream user data
 *
 * @since  1.0.0
 */
class PlgPrivacyActivitystream extends PrivacyPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 *
	 * @since  1.0.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * Database object
	 *
	 * @var    \Joomla\Database\DatabaseInterface
	 * @since  1.0.0
	 */
	protected $db;

	/**
	 * Processes an export request for Activity Stream user data
	 *
	 * This event will collect data for the following tables:
	 *
	 * - #__tj_activities
	 *
	 * @param   PrivacyTableRequest  $request  The request record being processed
	 * @param   User                 $user     The user account associated with this request if available
	 *
	 * @return  PrivacyExportDomain[]
	 *
	 * @since   1.0.0
	 */
	public function onPrivacyExportRequest($request, User $user = null)
	{
		if (!$user)
		{
			return array();
		}

		/** @var UserTable $userTable */
		$userTable = UserTable::getTable();
		$userTable->load($user->id);

		$domains = array();

		// Create the domain for the Activity Stream User data
		$domains[] = $this->createActivityStreamDomain($userTable);

		return $domains;
	}

	/**
	 * Create the domain for the Activity Stream User data
	 *
	 * @param   UserTable  $user  The UserTable object to process
	 *
	 * @return  PrivacyExportDomain
	 *
	 * @since   1.0.0
	 */
	private function createActivityStreamDomain(UserTable $user)
	{
		$domain = $this->createDomain('ActivityStream', 'ActivityStream data');
		$db = $this->db;
		$query = $db->getQuery(true)
			->select($db->quoteName(array('id', 'actor_id', 'object_id', 'target_id', 'type', 'template')))
			->from($db->quoteName('#__tj_activities'))
			->where(
					$db->quoteName('actor_id') . ' = ' . $db->quote($user->id)
				);
		$userData = $db->setQuery($query)->loadAssocList();

		if (!empty($userData))
		{
			foreach ($userData as $data)
			{
				$domain->addItem($this->createItemFromArray($data, $data['id']));
			}
		}

		return $domain;
	}

	/**
	 * Performs validation to determine if the data associated with a remove information request can be processed
	 *
	 * This event will not allow a super user account to be removed
	 *
	 * @param   PrivacyTableRequest  $request  The request record being processed
	 * @param   User                 $user     The user account associated with this request if available
	 *
	 * @return  PrivacyRemovalStatus
	 *
	 * @since   1.0.0
	 */
	public function onPrivacyCanRemoveData($request, User $user = null)
	{
		$status = new PrivacyRemovalStatus;

		if (!$user)
		{
			return $status;
		}

		return $status;
	}

	/**
	 * Removes the data associated with a remove information request
	 *
	 * This event will remove the user account
	 *
	 * @param   PrivacyTableRequest  $request  The request record being processed
	 * @param   User                 $user     The user account associated with this request if available
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public function onPrivacyRemoveData($request, User $user = null)
	{
		// This plugin only processes data for registered user accounts
		if (!$user)
		{
			return;
		}

		// If there was an error loading the user do nothing here
		if ($user->guest)
		{
			return;
		}

		$db = $this->db;

		// Delete Activity Stream user data :
		$query = $db->getQuery(true)
					->delete($db->quoteName('#__tj_activities'))
					->where($db->quoteName('actor_id') . ' = ' . $db->quote($user->id));
		$db->setQuery($query);
		$db->execute();
	}
}
