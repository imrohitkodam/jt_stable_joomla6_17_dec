<?php
/**
 * @package     Joomla.Site
 * @subpackage  Com_JLike
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (c) 2009-2017 TechJoomla, Tekdi Technologies Pvt. Ltd. All rights reserved.
 * @license     GPLv2 <http://www.gnu.org/licenses/old-licenses/gpl-2.0.html>.
 * @link        http://techjoomla.com.
 */

// No direct access
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

// Import library dependencies
$todosModelPath = JPATH_SITE . '/components/com_jlike/models/todos.php';
if (file_exists($todosModelPath)) {
	require_once $todosModelPath;
}

/**
 * jLike Tjucm plugin class.
 *
 * @since  1.0.0
 */
class PlgSystemJLike_Todo_Redirect extends CMSPlugin
{
	protected $lang;

	protected $db;

	protected $app;

	protected $loggedInUser;

	protected $todosModel;

	protected $isAjax;

	/**
	 * Constructor - note in Joomla 2.5 PHP4.x is no longer supported so we can use this.
	 *
	 * @param   object  &$subject  The object to observe
	 * @param   array   $config    An array that holds the plugin configuration
	 *
	 * @since  1.0.0
	 */
	public function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);
		$this->lang = Factory::getLanguage();
		$this->lang->load('plg_system_jlike_todo_redirect', JPATH_ADMINISTRATOR);

		$this->db  = Factory::getContainer()->get('DatabaseDriver');
		$this->app = Factory::getApplication();
		$this->loggedInUser = Factory::getUser();
		$this->todosModel 	= BaseDatabaseModel::getInstance('Todos', 'JLikeModel');
	}

	/**
	 * Function onUserLogin
	 *
	 * @return  null
	 *
	 * @since  1.0.0
	 */
	public function onUserLogin()
	{
		$this->getApplication()->setUserState("com_jlike.todoSave", "");
		$this->getApplication()->setUserState("com_jlike.todoSaveMsg", "");
		$this->getApplication()->setUserState("com_jlike.todoSaveRedirectTimestamp", "");
	}

	/**
	 * Function onUserLogout
	 *
	 * @return  null
	 *
	 * @since  1.0.0
	 */
	public function onUserLogout()
	{
		$this->getApplication()->setUserState("com_jlike.todoSave", "");
		$this->getApplication()->setUserState("com_jlike.todoSaveMsg", "");
		$this->getApplication()->setUserState("com_jlike.todoSaveRedirectTimestamp", "");
	}

	/**
	 * Function onAfterRoute
	 *
	 * @return  boolean true or false
	 *
	 * @since  1.0.0
	 */
	public function onAfterRoute()
	{
		$isAjaxRequest = $this->getApplication()->getInput()->server->getString('HTTP_X_REQUESTED_WITH', '');

		$this->isAjax = ($isAjaxRequest == 'XMLHttpRequest') ? true : false;

		if ($this->isAjax || !$this->getApplication()->isClient('site'))
		{
			return;
		}

		$todoSave = $this->getApplication()->getUserState("com_jlike.todoSave");
		$pathId = $this->getApplication()->getUserState("com_jlike.pathId");

		if ($todoSave == 1)
		{
			$todoSaveMsg = $this->getApplication()->getUserState("com_jlike.todoSaveMsg");
			$todoSaveRedirectTimestamp = $this->getApplication()->getUserState("com_jlike.todoSaveRedirectTimestamp");

			$this->getApplication()->setUserState("com_jlike.todoSave", "");
			$this->getApplication()->setUserState("com_jlike.todoSaveMsg", "");
			$this->getApplication()->setUserState("com_jlike.todoSaveRedirectTimestamp", "");
			$this->getApplication()->setUserState("com_jlike.pathId", "");

			// Check if page doesn't reload in 10 Seconds after Todo save
			$todoRedirectiontime = $this->params->get('todo_redirection_time', '10');

			if ((time() - $todoSaveRedirectTimestamp) > $todoRedirectiontime)
			{
				return;
			}

			$isToDoUrl = $this->todosModel->getLastIncompleteToDo($this->loggedInUser->id, $pathId);

			if ($isToDoUrl != '')
			{
				$this->getApplication()->enqueueMessage($todoSaveMsg);
				$this->getApplication()->redirect($isToDoUrl);
			}
		}
	}
}
