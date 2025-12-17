<?php
/**
 * @package     Hierarchy
 * @subpackage  Plg_Privacy_Hierarchy
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2018 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die();
use Joomla\CMS\User\User;
use Joomla\CMS\Table\User as UserTable;

JLoader::register('PrivacyPlugin', JPATH_ADMINISTRATOR . '/components/com_privacy/helpers/plugin.php');
JLoader::register('PrivacyRemovalStatus', JPATH_ADMINISTRATOR . '/components/com_privacy/helpers/removal/status.php');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\Database\DatabaseInterface;

/**
 * Hierarchy Privacy Plugin.
 *
 * @since  1.1.1
 */
class PlgPrivacyHierarchy extends PrivacyPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 *
	 * @since  1.1.1
	 */
	protected $autoloadLanguage = true;

	/**
	 * Database object
	 *
	 * @var    DatabaseInterface
	 * @since  1.1.1
	 */
	protected $db;

	/**
	 * Processes an export request for Hierarchy user data
	 *
	 * This event will collect data for the following tables:
	 *
	 * - #__hierarchy_users
	 *
	 * @param   PrivacyTableRequest  $request  The request record being processed
	 * @param   JUser                $user     The user account associated with this request if available
	 *
	 * @return  PrivacyExportDomain[]
	 *
	 * @since   1.1.1
	 */
	public function onPrivacyExportRequest(PrivacyTableRequest $request, User $user = null)
	{
		if (!$user)
		{
			return array();
		}

		/** @var JTableUser $user */
		$userTable = UserTable::getTable();
		$userTable->load($user->id);

		$domains = array();
		$domains[] = $this->createHierarchyUsers($userTable);

		return $domains;
	}

	/**
	 * Create the domain for the Hierarchy user role
	 *
	 * @param   JTableUser  $user  The JTableUser object to process
	 *
	 * @return  PrivacyExportDomain
	 *
	 * @since   1.1.1
	 */
	private function createHierarchyUsers(UserTable $user)
	{
		$domain = $this->createDomain('Hierarchy Users', 'Users hierarchy details');

		$query = $this->db->getQuery();
		$query->select($this->db->quoteName(array('id', 'user_id', 'reports_to', 'context', 'context_id', 'created_by', 'modified_by')));
		$query->from($this->db->quoteName('#__hierarchy_users'));
		$query->where('(' . $this->db->qn('user_id') . '=' . $user->id . ' OR ' . $this->db->qn('reports_to') .
			'=' . $user->id . ' OR ' . $this->db->qn('created_by') . '=' . $user->id . ' OR ' .
			$this->db->qn('modified_by') . '=' . $user->id . ')'
		);

		$list = $this->db->setQuery($query)->loadAssocList();

		if (!empty($list))
		{
			foreach ($list as $data)
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
	 * @param   JUser                $user     The user account associated with this request if available
	 *
	 * @return  PrivacyRemovalStatus
	 *
	 * @since   1.1.1
	 */
	public function onPrivacyCanRemoveData(PrivacyTableRequest $request, User $user = null)
	{
		$status = new PrivacyRemovalStatus;

		if (!$user->id)
		{
			return $status;
		}
	}

	/**
	 * Removes the data associated with a remove information request
	 *
	 * This event will pseudoanonymise the user account
	 *
	 * @param   PrivacyTableRequest  $request  The request record being processed
	 * @param   JUser                $user     The user account associated with this request if available
	 *
	 * @return  void
	 *
	 * @since   1.1.1
	 */
	public function onPrivacyRemoveData(PrivacyTableRequest $request, User $user = null)
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

		// 1. Delete data from #__kart_customer_address
		$query = $db->getQuery()
			->delete($db->quoteName('#__hierarchy_users'))
			->where('(' . $db->qn('user_id') . '=' . $user->id . ' OR ' . $db->qn('reports_to') . '=' . $user->id . ')');

		$db->setQuery($query);
		$db->execute();
	}
}
