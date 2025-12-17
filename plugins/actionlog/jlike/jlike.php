<?php
/**
 * @package     JLike
 * @subpackage  Actionlog.JLike
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2018 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

use Joomla\CMS\Table\Table;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\Actionlogs\Administrator\Model\ActionlogModel;
use Joomla\CMS\Component\ComponentHelper;

if (file_exists(JPATH_ADMINISTRATOR . '/components/com_actionlogs/helpers/actionlogs.php')) {
	require_once JPATH_ADMINISTRATOR . '/components/com_actionlogs/helpers/actionlogs.php';
}
Table::addIncludePath(JPATH_ROOT . '/administrator/components/com_jlike/tables');

/**
 * JLike Actions Logging Plugin.
 *
 * @since  2.1.2
 */
class PlgActionlogJLike extends CMSPlugin
{
	/**
	 * Load plugin language file automatically so that it can be used inside component
	 *
	 * @var    boolean
	 * @since  2.1.2
	 */
	protected $autoloadLanguage = true;

	/**
	 * On saving comments data logging method
	 *
	 * Method is called after comments data is stored in the database.
	 *
	 * @param   string   $commentData  com_jlike.
	 * @param   boolean  $isNew        True if a new comment is stored.
	 *
	 * @return  void
	 *
	 * @since   2.1.2
	 */
	public function onAfterJlikeCommentSave($commentData, $isNew)
	{
		if (!$this->params->get('logActionForCommentSave', 1))
		{
			return;
		}

		$context = Factory::getApplication()->getInput()->get('option');
		$jUser   = Factory::getUser();

		if ($isNew)
		{
			$messageLanguageKey = 'PLG_ACTIONLOG_JLIKE_COMMENT_ADDED';
			$action             = 'add';
		}
		else
		{
			$messageLanguageKey = 'PLG_ACTIONLOG_JLIKE_COMMENT_UPDATED';
			$action             = 'update';
		}

		$userId   = $jUser->id;
		$userName = $jUser->username;

		// Get event, campaign text from - com_jticketing.event or com_jgive.campaign
		$type = explode('.', $commentData['element']);

		// Get content URL
		$url = $this->getContentUrlForLog($commentData['url']);

		$message = array(
			'action'      => $action,
			'type'        => $type[1],
			'entry_id'    => $commentData['entry_id'],
			'title'       => $commentData['title'],
			'itemlink'    => $url,
			'userid'      => $userId,
			'username'    => $userName,
			'accountlink' => 'index.php?option=com_users&task=user.edit&id=' . $userId,
		);

		$this->addLog(array($message), $messageLanguageKey, $context, $userId);
	}

	/**
	 * On after deleting comment data logging method.
	 *
	 * Method is called after comments data is deleted from  the database.
	 *
	 * @param   string  $commentData  comment data.
	 *
	 * @return  void
	 *
	 * @since   2.1.2
	 */
	public function onAfterJlikeCommentDelete($commentData)
	{
		if (!$this->params->get('logActionForCommentDelete', 1))
		{
			return;
		}

		$context            = Factory::getApplication()->getInput()->get('option');
		$jUser              = Factory::getUser();
		$messageLanguageKey = 'PLG_ACTIONLOG_JLIKE_COMMENT_DELETED';
		$action             = 'delete';
		$userId             = $jUser->id;
		$userName           = $jUser->username;

		// Get event, campaign text from - com_jticketing.event or com_jgive.campaign
		$type = explode('.', $commentData['element']);

		// Get content URL
		$url = $this->getContentUrlForLog($commentData['url']);

		$message = array(
			'action'      => $action,
			'type'        => $type[1],
			'title'       => $commentData['title'],
			'itemlink'    => $url,
			'userid'      => $userId,
			'username'    => $userName,
			'accountlink' => 'index.php?option=com_users&task=user.edit&id=' . $userId,
		);

		$this->addLog(array($message), $messageLanguageKey, $context, $userId);
	}

	/**
	 * Method is called after todo data is stored in the database.
	 *
	 * @param   Object  $todoData  todo data
	 *
	 * @return  void
	 *
	 * @since   2.1.2
	 */
	public function onAfterJlikeTodoSave($todoData)
	{
		if (!$this->params->get('logActionForTodoSave', 1))
		{
			return;
		}

		$context = Factory::getApplication()->getInput()->get('option');
		$jUser   = Factory::getUser();

		if ($todoData['status'] == 'I')
		{
			$messageLanguageKey = 'PLG_ACTIONLOG_JLIKE_TODO_ADDED';
			$action             = 'add';
		}

		if ($todoData['status'] == 'C')
		{
			$messageLanguageKey = 'PLG_ACTIONLOG_JLIKE_TODO_UPDATED';
			$action             = 'update';
		}

		$userId   = $jUser->id;
		$userName = $jUser->username;

		// Get event, campaign text from - com_jticketing.event or com_jgive.campaign
		$type = explode('.', $todoData['client']);

		// Get content URL
		$url = $this->getContentUrlForLog($todoData['url']);

		$message = array(
			'action'      => $action,
			'type'        => $type[1],
			'entry_id'    => $todoData['entry_id'],
			'title'       => $todoData['title'],
			'itemlink'    => $url,
			'userid'      => $userId,
			'username'    => $userName,
			'accountlink' => 'index.php?option=com_users&task=user.edit&id=' . $userId
		);

		$this->addLog(array($message), $messageLanguageKey, $context, $userId);
	}

	/**
	 * Method is called after todo data is deleted from the database.
	 *
	 * @param   array  $todoData  Holds the todo data.
	 *
	 * @return  void
	 *
	 * @since   2.1.2
	 */
	public function onAfterJlikeTodoDelete($todoData)
	{
		if (!$this->params->get('logActionForTodoDelete', 1))
		{
			return;
		}

		$context            = Factory::getApplication()->getInput()->get('option');
		$jUser              = Factory::getUser();
		$messageLanguageKey = 'PLG_ACTIONLOG_JLIKE_TODO_DELETED';
		$action             = 'delete';
		$userId             = $jUser->id;
		$userName           = $jUser->username;

		// Get event, campaign text from - com_jticketing.event or com_jgive.campaign
		$type = explode('.', $todoData['element']);

		// Get content URL
		$url = $this->getContentUrlForLog($todoData['url']);

		$message = array(
			'action'      => $action,
			'type'        => $type[1],
			'title'       => $todoData['title'],
			'itemlink'    => $url,
			'userid'      => $userId,
			'username'    => $userName,
			'accountlink' => 'index.php?option=com_users&task=user.edit&id=' . $userId
		);

		$this->addLog(array($message), $messageLanguageKey, $context, $userId);
	}

	/**
	 * Method is called after like, dislike, unlike, undislike data is stored in the database.
	 *
	 * @param   array  $commentData  Holds the likes data.
	 *
	 * @return  void
	 *
	 * @since   2.1.2
	 */
	public function onAfterJlikeLikeDislikeSave($commentData)
	{
		if (!$this->params->get('logActionForLikeDislikeSave', 1))
		{
			return;
		}

		$context            = Factory::getApplication()->getInput()->get('option');
		$jUser              = Factory::getUser();
		$messageLanguageKey = 'PLG_ACTIONLOG_JLIKE_LIKE';
		$action             = $commentData['method'] . 'd';
		$userId             = $jUser->id;
		$userName           = $jUser->username;

		// Get event, campaign text from - com_jticketing.event or com_jgive.campaign
		$type = explode('.', $commentData['element']);

		// Get content URL
		$url = $this->getContentUrlForLog($commentData['url']);

		$message = array(
			'action'      => $action,
			'type'        => $type[1],
			'entry_id'    => $commentData['entry_id'],
			'title'       => $commentData['title'],
			'itemlink'    => $url,
			'userid'      => $userId,
			'username'    => $userName,
			'accountlink' => 'index.php?option=com_users&task=user.edit&id=' . $userId
		);

		$this->addLog(array($message), $messageLanguageKey, $context, $userId);
	}

	/**
	 * Method is called after like, unlike to comments is stored in the database.
	 *
	 * @param   array  $commentData  Holds the likes data.
	 *
	 * @return  void
	 *
	 * @since   2.1.2
	 */
	public function onAfterJlikeLikeUnlikeCommentSave($commentData)
	{
		if (!$this->params->get('logActionForLikeUnlikeCommentSave', 1))
		{
			return;
		}

		$context            = Factory::getApplication()->getInput()->get('option');
		$jUser              = Factory::getUser();
		$messageLanguageKey = 'PLG_ACTIONLOG_JLIKE_LIKE_UNLIKE_COMMENTS';
		$action             = $commentData['method'] . 'd';
		$userId             = $jUser->id;
		$userName           = $jUser->username;

		// Get event, campaign text from - com_jticketing.event or com_jgive.campaign
		$type = explode('.', $commentData['element']);

		// Get content URL
		$url = $this->getContentUrlForLog($commentData['url']);

		$message = array(
			'action'      => $action,
			'type'        => $type[1],
			'entry_id'    => $commentData['entry_id'],
			'title'       => $commentData['title'],
			'itemlink'    => $url,
			'userid'      => $userId,
			'username'    => $userName,
			'accountlink' => 'index.php?option=com_users&task=user.edit&id=' . $userId
		);

		$this->addLog(array($message), $messageLanguageKey, $context, $userId);
	}

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
	 * @since   2.1.2
	 */
	protected function addLog($messages, $messageLanguageKey, $context, $userId = null)
	{
		if (JVERSION >= '4.4.0')
		{
			$model = Factory::getApplication()->bootComponent('com_actionlogs')
            ->getMVCFactory()->createModel('Actionlog', 'Administrator', ['ignore_request' => true]);
		}
		else if (JVERSION >= '4.0')
		{
			$model = new ActionlogModel;
		}
		else
		{
			if (file_exists(JPATH_ADMINISTRATOR . '/components/com_actionlogs/models/actionlog.php')) {
				require_once JPATH_ADMINISTRATOR . '/components/com_actionlogs/models/actionlog.php';
			}
			$model = BaseDatabaseModel::getInstance('Actionlog', 'ActionlogsModel');
		}

		/* @var ActionlogsModelActionlog $model */
		$model->addLog($messages, $messageLanguageKey, $context, $userId);
	}

	/**
	 * Method for handling SEF, non SEF URLs
	 *
	 * @param   string  $contentUrl  Holds content url
	 *
	 * @return  string
	 *
	 * @since   2.1.2
	 */
	private function getContentUrlForLog($contentUrl)
	{
		$url = $contentUrl;

		// Manage SEF, non SEF content URL
		if ($contentUrl)
		{
			if (strpos($contentUrl, Uri::root()) !== false)
			{
				$url = $contentUrl;
			}
			else
			{
				$url = Uri::root() . $contentUrl;
			}
		}

		return $url;
	}
}
