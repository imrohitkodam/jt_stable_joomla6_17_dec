<?php
/**
 * @package     JLike
 * @subpackage  Plg_Privacy_JLike
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2018 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die();

use Joomla\Data\DataObject;
use Joomla\CMS\User\User;
use Joomla\CMS\Table\User as UserTable;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

$privacyPluginPath = JPATH_ADMINISTRATOR . '/components/com_privacy/helpers/plugin.php';
if (file_exists($privacyPluginPath)) {
	require_once $privacyPluginPath;
}

$privacyRemovalPath = JPATH_ADMINISTRATOR . '/components/com_privacy/helpers/removal/status.php';
if (file_exists($privacyRemovalPath)) {
	require_once $privacyRemovalPath;
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

/**
 * JLike Privacy Plugin.
 *
 * @since  2.1.2
 */
class PlgPrivacyJLike extends PrivacyPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 *
	 * @since  2.1.2
	 */
	protected $autoloadLanguage = true;

	/**
	 * Database object
	 *
	 * @var    DataObjectbaseDriver
	 * @since  2.1.2
	 */
	protected $db;

	/**
	 * Reports the privacy related capabilities for this plugin to site administrators.
	 *
	 * @return  array
	 *
	 * @since   2.1.2
	 */
	public function onPrivacyCollectAdminCapabilities()
	{
		$this->loadLanguage();

		return array(
			Text::_('PLG_PRIVACY_JLIKE') => array(
				Text::_('PLG_PRIVACY_JLIKE_PRIVACY_CAPABILITY_USER_COMMENTS_DETAIL'),
				Text::_('PLG_PRIVACY_JLIKE_PRIVACY_CAPABILITY_USER_TODOS_DETAIL')
			)
		);
	}

	/**
	 * Processes an export request for JLike user data
	 *
	 * This event will collect data for the following tables:
	 *
	 * - #__jlike_annotations
	 * - #__jlike_todos
	 * - #__jlike_likes
	 * - #__jlike_likeStatusXref
	 * - #__jlike_like_lists
	 * - #__jlike_rating
	 *
	 * @param   PrivacyTableRequest  $request  The request record being processed
	 * @param   User                $user     The user account associated with this request if available
	 *
	 * @return  PrivacyExportDomain[]
	 *
	 * @since   2.1.2
	 */
	public function onPrivacyExportRequest($request, User $user = null)
	{
		if (!$user)
		{
			return array();
		}

		/** @var User $user */
		$userTable = User::getTable();
		$userTable->load($user->id);

		$domains = array();
		$domains[] = $this->createJLikeAnnotations($userTable);
		$domains[] = $this->createJLikeAssignedToTodos($userTable);
		$domains[] = $this->createJLikeAssignedByTodos($userTable);
		$domains[] = $this->createJLikeStatusXref($userTable);
		$domains[] = $this->createJLikeListOfLikes($userTable);
		$domains[] = $this->createJLikeRating($userTable);

		return $domains;
	}

	/**
	 * Create the domain for the JLike user comments
	 *
	 * @param   UserTable  $user  The User object to process
	 *
	 * @return  PrivacyExportDomain
	 *
	 * @since   2.1.2
	 */
	private function createJLikeAnnotations(UserTable $user)
	{
		$domain = $this->createDomain('Users comments', 'Comments added by this user in JLike');

		$query = $this->db->getQuery(true);
		$query->select($this->db->quoteName(array('id', 'ordering', 'state', 'user_id', 'content_id', 'annotation', 'privacy')));
		$query->select($this->db->quoteName(array('annotation_date', 'parent_id', 'note', 'type', 'context', 'checked_out_time')));

		$query->from($this->db->quoteName('#__jlike_annotations'));
		$query->where($this->db->quoteName('user_id') . '=' . $user->id);

		$roles = $this->db->setQuery($query)->loadAssocList();

		if (!empty($roles))
		{
			foreach ($roles as $role)
			{
				$domain->addItem($this->createItemFromArray($role, $role['id']));
			}
		}

		return $domain;
	}

	/**
	 * Create the domain for the JLike user assigned_to todos
	 *
	 * @param   UserTable  $user  The User object to process
	 *
	 * @return  PrivacyExportDomain
	 *
	 * @since   2.1.2
	 */
	private function createJLikeAssignedToTodos(UserTable $user)
	{
		$domain = $this->createDomain('Users todo', 'Todos assigned to this user in JLike');

		$query = $this->db->getQuery(true);
		$query->select($this->db->quoteName(array('id', 'asset_id', 'ordering', 'state', 'checked_out', 'checked_out_time', 'created_by', 'sender_msg')));
		$query->select($this->db->quoteName(array('content_id', 'assigned_by', 'assigned_to', 'created_date', 'start_date', 'due_date', 'status')));
		$query->select($this->db->quoteName(array('title', 'type', 'context', 'system_generated', 'parent_id', 'list_id', 'modified_date', 'modified_by')));
		$query->select($this->db->quoteName(array('can_override', 'overriden', 'params', 'todo_list_id', 'ideal_time')));

		$query->from($this->db->quoteName('#__jlike_todos'));
		$query->where($this->db->quoteName('assigned_to') . '=' . $user->id);

		$roles = $this->db->setQuery($query)->loadAssocList();

		if (!empty($roles))
		{
			foreach ($roles as $role)
			{
				$domain->addItem($this->createItemFromArray($role, $role['id']));
			}
		}

		return $domain;
	}

	/**
	 * Create the domain for the JLike user assigned_by todos
	 *
	 * @param   UserTable  $user  The User object to process
	 *
	 * @return  PrivacyExportDomain
	 *
	 * @since   2.1.2
	 */
	private function createJLikeAssignedByTodos(UserTable $user)
	{
		$domain = $this->createDomain('Users todo', 'Todos assigned by this user in JLike');

		$query = $this->db->getQuery(true);
		$query->select($this->db->quoteName(array('id', 'asset_id', 'ordering', 'state', 'checked_out', 'checked_out_time', 'created_by', 'sender_msg')));
		$query->select($this->db->quoteName(array('content_id', 'assigned_by', 'assigned_to', 'created_date', 'start_date', 'due_date', 'status')));
		$query->select($this->db->quoteName(array('title', 'type', 'context', 'system_generated', 'parent_id', 'list_id', 'modified_date')));
		$query->select($this->db->quoteName(array('modified_by', 'can_override', 'overriden', 'params', 'todo_list_id', 'ideal_time')));
		$query->from($this->db->quoteName('#__jlike_todos'));
		$query->where($this->db->quoteName('assigned_by') . '=' . $user->id);

		$roles = $this->db->setQuery($query)->loadAssocList();

		if (!empty($roles))
		{
			foreach ($roles as $role)
			{
				$domain->addItem($this->createItemFromArray($role, $role['id']));
			}
		}

		return $domain;
	}

	/**
	 * Create the domain for the JLike user like Xref table
	 *
	 * @param   UserTable  $user  The User object to process
	 *
	 * @return  PrivacyExportDomain
	 *
	 * @since   2.1.2
	 */
	private function createJLikeStatusXref(UserTable $user)
	{
		$domain = $this->createDomain('Users like', 'Likes of user in JLike');

		$query = $this->db->getQuery(true)
			->select($this->db->quoteName(array('id', 'content_id', 'status_id', 'user_id', 'cdate', 'mdate')))
			->from($this->db->quoteName('#__jlike_likeStatusXref'))
			->where($this->db->quoteName('user_id') . '=' . $user->id);

		$roles = $this->db->setQuery($query)->loadAssocList();

		if (!empty($roles))
		{
			foreach ($roles as $role)
			{
				$domain->addItem($this->createItemFromArray($role, $role['id']));
			}
		}

		return $domain;
	}

	/**
	 * Create the domain for the JLike user likes list
	 *
	 * @param   UserTable  $user  The User object to process
	 *
	 * @return  PrivacyExportDomain
	 *
	 * @since   2.1.2
	 */
	private function createJLikeListOfLikes(UserTable $user)
	{
		$domain = $this->createDomain('Users like list', 'Like list of user in JLike');

		$query = $this->db->getQuery(true)
			->select($this->db->quoteName(array('id', 'user_id', 'title', 'privacy')))
			->from($this->db->quoteName('#__jlike_like_lists'))
			->where($this->db->quoteName('user_id') . '=' . $user->id);

		$roles = $this->db->setQuery($query)->loadAssocList();

		if (!empty($roles))
		{
			foreach ($roles as $role)
			{
				$domain->addItem($this->createItemFromArray($role, $role['id']));
			}
		}

		return $domain;
	}

	/**
	 * Create the domain for the JLike user ratings
	 *
	 * @param   UserTable  $user  The User object to process
	 *
	 * @return  PrivacyExportDomain
	 *
	 * @since   2.1.2
	 */
	private function createJLikeRating(UserTable $user)
	{
		$domain = $this->createDomain('Users rating', 'Ratings of user in JLike');

		$query = $this->db->getQuery(true)
			->select($this->db->quoteName(array('id', 'user_id', 'content_id', 'rating_upto', 'user_rating', 'created_date', 'modified_date')))
			->from($this->db->quoteName('#__jlike_rating'))
			->where($this->db->quoteName('user_id') . '=' . $user->id);

		$roles = $this->db->setQuery($query)->loadAssocList();

		if (!empty($roles))
		{
			foreach ($roles as $role)
			{
				$domain->addItem($this->createItemFromArray($role, $role['id']));
			}
		}

		return $domain;
	}

	/**
	 * Removes the data associated with a remove information request
	 *
	 * This event will pseudoanonymise the user account
	 *
	 * @param   PrivacyTableRequest  $request  The request record being processed
	 * @param   User                $user     The user account associated with this request if available
	 *
	 * @return  void
	 *
	 * @since   2.1.2
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

		// 1. Delete data from #__jlike_annotations
		$query = $db->getQuery(true)
			->delete($db->quoteName('#__jlike_annotations'))
			->where($db->quoteName('user_id') . '=' . $user->id);

		$db->setQuery($query);
		$db->execute();

		// 2. Delete data from #__jlike_todos - assigned_by entries
		$query = $db->getQuery(true)
			->delete($db->quoteName('#__jlike_todos'))
			->where($db->quoteName('assigned_by') . '=' . $user->id || $db->quoteName('assigned_to') . '=' . $user->id);

		$db->setQuery($query);
		$db->execute();

		// 3. Delete data from #__jlike_likeStatusXref
		$query = $db->getQuery(true)
			->delete($db->quoteName('#__jlike_likeStatusXref'))
			->where($db->quoteName('user_id') . '=' . $user->id);

		$db->setQuery($query);
		$db->execute();

		// 4. Delete data from #__jlike_like_lists
		$query = $db->getQuery(true)
			->delete($db->quoteName('#__jlike_like_lists'))
			->where($db->quoteName('user_id') . '=' . $user->id);

		$db->setQuery($query);
		$db->execute();

		// 5. Delete data from #__jlike_rating
		$query = $db->getQuery(true)
			->delete($db->quoteName('#__jlike_rating'))
			->where($db->quoteName('user_id') . '=' . $user->id);

		$db->setQuery($query);
		$db->execute();

		// 6. Delete data from #__jlike_likes
		$query = $db->getQuery(true)
			->delete($db->quoteName('#__jlike_likes'))
			->where($db->quoteName('userid') . '=' . $user->id);

		$db->setQuery($query);
		$db->execute();
	}
}
