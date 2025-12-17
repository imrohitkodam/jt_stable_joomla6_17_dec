<?php
/**
 * @package     Hierarchy
 * @subpackage  Plg_Actionlog_Hierarchy
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (c) 2009-2018 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later.
 */

// No direct access.
defined('_JEXEC') or die();

JLoader::register('ActionlogsHelper', JPATH_ADMINISTRATOR . '/components/com_actionlogs/helpers/actionlogs.php');

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\Database\DatabaseInterface;

/**
 * Hierarchy Actions Logging Plugin.
 *
 * @since  1.1.1
 */
class PlgActionlogHierarchy extends CMSPlugin
{
	/**
	 * Application object.
	 *
	 * @var    \Joomla\CMS\Application\CMSApplication
	 * @since  1.1.1
	 */
	protected $app;

	/**
	 * Database object.
	 *
	 * @var    DatabaseInterface
	 * @since  1.1.1
	 */
	protected $db;

	/**
	 * Load plugin language file automatically so that it can be used inside component
	 *
	 * @var    boolean
	 * @since  1.1.1
	 */
	protected $autoloadLanguage = true;

	/**
	 * Proxy for ActionlogsModelUserlog addLog method
	 *
	 * This method adds a record to #__action_logs contains (message_language_key, message, date, context, user)
	 *
	 * @param   array   $messages            The contents of the messages to be logged
	 * @param   string  $messageLanguageKey  The language key of the message
	 * @param   string  $context             The context of the content passed to the plugin
	 * @param   int     $userId              ID of user perform the action, usually ID of current logged in user
	 *
	 * @return  void
	 *
	 * @since   1.1.1
	 */
	protected function addLog($messages, $messageLanguageKey, $context, $userId = null)
	{
		JLoader::register('ActionlogsModelActionlog', JPATH_ADMINISTRATOR . '/components/com_actionlogs/models/actionlog.php');

		/* @var ActionlogsModelActionlog $model */
		$model = BaseDatabaseModel::getInstance('Actionlog', 'ActionlogsModel');
		$model->addLog($messages, $messageLanguageKey, $context, $userId);
	}

	/**
	 * On after manager assigned to the user
	 *
	 * @param   ARRAY    $data   hierarchy data
	 * @param   BOOLEAN  $isNew  flag for new hierarchy
	 *
	 * @return  void
	 *
	 * @since   1.1.1
	 */
	public function onAfterHierarchySaveHierarchy($data, $isNew)
	{
		if (!$this->params->get('logActionForAssigningManager', 1) || !$isNew)
		{
			return;
		}

		$option       = $this->app->getInput()->getCmd('option');
		$actor        = Factory::getUser();
		$manager      = Factory::getUser($data['reports_to']);
		$user = Factory::getUser($data['user_id']);
		$action       = 'assignmanager';
		$userId       = $actor->id;
		$userName     = $actor->username;

		$messageLanguageKey = 'PLG_ACTIONLOG_HIERARCHY_ASSIGN_MANAGER';

		$message = array(
			'action'                    => $action,
			'actorAccountLink'          => 'index.php?option=com_users&task=user.edit&id=' . $userId,
			'actorName'                 => $userName,
			'managerForUser'            => $manager->username,
			'managerForUserAccountLink' => 'index.php?option=com_users&task=user.edit&id=' . $manager->id,
			'user'                      => $user->username,
			'userAccountLink'           => 'index.php?option=com_users&task=user.edit&id=' . $user->id
		);

		$this->addLog(array($message), $messageLanguageKey, $option, $userId);
	}

	/**
	 * On after manager removed for the user
	 *
	 * @param   ARRAY  $data  hierarchy data
	 *
	 * @return  void
	 *
	 * @since   1.1.1
	 */
	public function onAfterHierarchyDeleteHierarchy($data)
	{
		if (!$this->params->get('logActionForRemoveManager', 1) || empty($data['id']))
		{
			return;
		}

		$option       = $this->app->getInput()->getCmd('option');
		$actor        = Factory::getUser();
		$manager      = Factory::getUser($data['reports_to']);
		$user = Factory::getUser($data['user_id']);
		$action       = 'assignmanager';
		$userId       = $actor->id;
		$userName     = $actor->username;

		$messageLanguageKey = 'PLG_ACTIONLOG_HIERARCHY_REMOVE_MANAGER';

		$message = array(
			'action'                    => $action,
			'actorAccountLink'          => 'index.php?option=com_users&task=user.edit&id=' . $userId,
			'actorName'                 => $userName,
			'managerForUser'            => $manager->username,
			'managerForUserAccountLink' => 'index.php?option=com_users&task=user.edit&id=' . $manager->id,
			'user'                      => $user->username,
			'userAccountLink'           => 'index.php?option=com_users&task=user.edit&id=' . $user->id
		);

		$this->addLog(array($message), $messageLanguageKey, $option, $userId);
	}
}
