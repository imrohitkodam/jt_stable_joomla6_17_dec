<?php
/**
 * @package     JLike
 * @subpackage  com_jlike
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access
defined('_JEXEC') or die;
use Joomla\Data\DataObject;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Application\CMSApplication;
use Joomla\Registry\Registry;

/**
 * Class supporting a list of JLike records.
 *
 * @since  1.0.0
 */
class JlikeModelRecommend extends ListModel
{
	protected $params;

	protected $user;

	protected $db;

	/**
	 * Class constructor.
	 *
	 * @since   1.6
	 */
	public function __construct()
	{
		$this->params = ComponentHelper::getParams('com_jlike');
		$this->user   = Factory::getUser();
		$this->db     = Factory::getDbo();

		if (!class_exists('comjlikeHelper'))
		{
			// Require_once $path;
			$helperPath = JPATH_SITE . '/components/com_jlike/helper.php';
			if (file_exists($helperPath)) {
				require_once $helperPath;
			}
		}

		parent::__construct();
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// Initialise variables.
		// List state information.
		parent::populateState('u.username', 'asc');

		$app = Factory::getApplication();

		$search = $app->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$limit = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'), 'uint');
		$this->setState('list.limit', $limit);

		$limitstart = $app->getInput()->get('limitstart', 0, 'uint');
		$this->setState('list.start', $limitstart);
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param   string  $id  A prefix for the store id.
	 *
	 * @return  string  A store id.
	 *
	 * @since	1.6
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.state');

		return parent::getStoreId($id);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return	array DataObjectbaseQuery
	 *
	 * @since	1.6
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db = $this->getDbo();

		// $query     = $db->getQuery(true);
		$oluser_id = Factory::getUser()->id;

		$input = Factory::getApplication()->getInput();

		// $socialIntegration   = $input->get('socialIntegration', 'joomla', 'STRING');
		$socialIntegration = 'joomla';

		$plg_type   = $input->get('plg_type', 'content');
		$plg_name   = $input->get('plg_name', '');
		$element    = $input->get('element', '');
		$elementId  = $input->get('id', '', 'INT');
		$type	    = $input->get('type', 'reco');

		// Get Social Integration form each component
		PluginHelper::importPlugin($plg_type, $plg_name);

		$socialIntegration = Factory::getApplication()->triggerEvent('onAfter' . $plg_name . 'GetSocialIntegration', array());

		if (isset($socialIntegration[0]))
		{
			$socialIntegration = $socialIntegration[0];
		}

		if (empty($socialIntegration))
		{
			$socialIntegration = 'joomla';
		}

		$socialIntegration = strtolower($socialIntegration);

		$which_users_in_list = 0;

		if ($socialIntegration == 'easysocial' || $socialIntegration == 'js' || $socialIntegration == 'jomsocial')
		{
			$which_users_in_list = $this->params->get('which_users_in_list');
		}

		switch ($socialIntegration)
		{
			case 'easysocial':

				// Get Only friends
				if ($which_users_in_list == 0)
				{
					$query = $this->getESFriends($oluser_id);
				}
				else
				{
					$query = $this->getAllUser();
				}
			break;

			case 'js':
			case 'jomsocial':

				// Get Only friends
				if ($which_users_in_list == 0)
				{
					$query = $this->getJSFriends($oluser_id);
				}
				else
				{
					$query = $this->getAllUser();
				}
			break;

			default:
				$query = $this->getAllUser();
		}

		/*
		$result = Factory::getApplication()->triggerEvent('onAfter' . $plg_name . 'GetAdditionalWhereCondition', array('id' => $elementId));

		if (isset($result[0]))
		{
			$componentSpecificCondition = $result[0];
		}

		$usersToRemove = array();

		if (!empty($componentSpecificCondition))
		{
			$usersToRemove = $componentSpecificCondition;
		}
		*/

		// Get all user which are already recommended & Assigned by this user.
		$recommendedUsers = $this->getTypewiseUsers($elementId, $element, $type);

		if (!empty($recommendedUsers))
		{
			$usersToRemove = $recommendedUsers;
		}

		/*if (!empty($componentSpecificCondition) && !empty($recommendedUsers))
		{
			$usersToRemove = array_merge($componentSpecificCondition, $recommendedUsers);
			$usersToRemove = array_unique($usersToRemove);
		}*/

		if (!empty($usersToRemove))
		{
			$usersToRemove = implode(',', $usersToRemove);
			$query->where($db->quoteName('u.id') . ' NOT IN (' . $usersToRemove . ')');
		}

		$query->where($db->quoteName('u.id') . ' <> (' . $db->quote($oluser_id) . ')');

		return $query;
	}

	/**
	 * Retrieves a list of friends (Jomsocial)
	 *
	 * @param   int  $id  The user's id
	 *
	 * @return   Array
	 *
	 * @since   1.0
	 */
	public function getJSFriends($id)
	{
		$db = Factory::getDBO();

		$query = $db->getQuery(true);

		$query->select('DISTINCT(a.' . $db->quoteName('connect_to') . ') AS ' . $db->quoteName('friendid'));
		$query->select($db->quoteName(array('u.name', 'u.username')));
		$query->from($db->quoteName('#__community_connection', 'a'));
		$join_condn = $db->quoteName('#__users') . ' AS u ' . ' ON a.' . $db->quoteName('connect_from') . '=' . $db->Quote($id);

		$join_condn .= ' AND a.' . $db->quoteName('connect_to') . ' =u.' . $db->quoteName('id');
		$join_condn .= ' AND a.' . $db->quoteName('status') . '=' . $db->Quote(1);

		$query->join('INNER', $join_condn);

		return $query;
	}

	/**
	 * Function to get already recommended users.
	 *
	 * @param   INT     $elementId  element ID
	 * @param   STRING  $element    com_tjlms.course
	 * @param   STRING  $type       Type reco or assign
	 *
	 * @return  boolean
	 *
	 * @since  1.0.0
	 */
	public function getTypewiseUsers($elementId, $element, $type = "reco")
	{
		$oluser_id = Factory::getUser()->id;
		$db        = Factory::getDbo();
		$Rquery    = $db->getQuery(true);
		$Rquery->select($db->quoteName('t.assigned_to'));
		$Rquery->from($db->quoteName('#__jlike_todos', 't'));
		$Rquery->join('INNER', $db->quoteName('#__jlike_content', 'c') . ' ON (' . $db->quoteName('c.id') . ' = ' . $db->quoteName('t.content_id') . ')');
		$Rquery->where($db->quoteName('t.type') . ' = ' . $db->quote($type));
		$Rquery->where($db->quoteName('t.assigned_by') . ' = ' . (int) $oluser_id);
		$Rquery->where($db->quoteName('c.element_id') . ' = ' . (int) $elementId);
		$Rquery->where($db->quoteName('c.element') . ' = ' . $db->quote($element));

		$db->setQuery($Rquery);

		return $db->loadColumn();
	}

	/**
	 * To get the records
	 *
	 * @return  Object
	 *
	 * @since  1.0.0
	 */
	public function getItems()
	{
		$items = parent::getItems();

		// Get integration
		$input      = Factory::getApplication()->getInput();
		$plg_type   = $input->get('plg_type', 'content', 'STRING');
		$plg_name   = $input->get('plg_name', '', 'STRING');
		$helperPath = JPATH_SITE . '/components/com_jlike/helpers/main.php';

		if (!class_exists('ComjlikeMainHelper'))
		{
			// Require_once $path;
			if (file_exists($helperPath)) {
				require_once $helperPath;
			}
		}

		$plgData = array("plg_type" => $plg_type, "plg_name" => $plg_name);
		$sLibObj = ComjlikeMainHelper::getSocialLibraryObject('', $plgData);

		foreach ($items as $item)
		{
			$item->avatar = $sLibObj->getAvatar(Factory::getUser($item->friendid), 50);
		}

		return $items;
	}

	/**
	 * Function to save Recommendation & Assignment
	 *
	 * @param   ARRAY  $data         formdata
	 * @param   ARRAY  $options      plugin details
	 * @param   INT    $notify_user  notification flag
	 *
	 * @return  boolean
	 *
	 * @since  1.0.0
	 */
	public function assignRecommendUsers($data, $options, $notify_user = 1)
	{
		$type = 'reco';

		// Add Table Path
		Table::addIncludePath(JPATH_ROOT . '/administrator/components/com_jlike/tables');

		// Get a db connection.
		require_once JPATH_SITE . '/components/com_jlike/helpers/integration.php';

		if ($data['type'] == "assign")
		{
			$type = $data['type'];
		}

		PluginHelper::importPlugin('system');

		if ($type == 'reco')
		{
			// Trigger Brfore recommend
			$getResponse = Factory::getApplication()->triggerEvent('onBeforeRecommend', array($data));

			if (isset($getResponse[0]))
			{
				$data = $getResponse[0];
			}
		}
		elseif ($type == 'assign')
		{
			// Trigger assign recommend
			$getResponse = Factory::getApplication()->triggerEvent('onBeforeAssignment', array($data));

			if (isset($getResponse[0]))
			{
				$data = $getResponse[0];
			}
		}

		// Get content id
		if (!empty($options['element']) && !empty($options['element_id']))
		{
			$options['element']    = $options['element'];
			$options['plg_name']   = $options['plg_name'];
			$options['plg_type']   = $options['plg_type'];
			$options['element_id'] = (int) $options['element_id'];

			// First parameter file name and second parameter is prefix
			$table = Table::getInstance('Content', 'JlikeTable', array('dbo', $this->db));

			// Check if already assiged User content_id
			$table->load(array('element' => $options['element'], 'element_id' => (int) $options['element_id']));

			// Get URL and title form respective component
			PluginHelper::importPlugin($options['plg_type'], $options['plg_name']);
			$elementdata = Factory::getApplication()->triggerEvent('onAfter' . $options['plg_name'] . 'GetElementData', array($options['element_id']));

			$elementdata = $elementdata[0];

			$options['element_title'] = !empty($elementdata['title']) ? $elementdata['title'] : $options['element_title'];
			$options['element_short_desc'] = !empty($elementdata['short_desc']) ? $elementdata['short_desc'] : $options['element_short_desc'];
			$options['url'] = !empty($elementdata['url']) ? $elementdata['url'] : $options['url'];

			if (!$table->id)
			{
				try
				{
					// If add entry in conten
					$table->element_id = (int) $options['element_id'];
					$table->element    = $options['element'];
					$table->url        = $options['url'];
					$table->title      = $options['element_title'];
					$table->store();
					$content_id        = $table->id;
				}
				catch (RuntimeException $e)
				{
					Factory::getApplication()->enqueueMessage($e->getMessage());

					return false;
				}
			}
			else
			{
				$content_id = $table->id;
			}
		}

		// Insert record in todos table
		$table = '';

		$db = Factory::getDbo();

		$query = $db->getQuery(true);
		$query->select($db->quoteName('id'));
		$query->from($db->quoteName('#__jlike_todos'));
		$query->where($db->quoteName('content_id') . " = " . $db->quote($content_id));
		$query->where($db->quoteName('assigned_to') . " = " . $db->quote($data['recommend_friends'][0]));

		$db->setQuery($query);
		$result = (int) ($db->loadColumn()[0] ?? null);

		if ($result)
		{
			$data['todo_id'] = $result;
			$data['modified_date'] = Factory::getDate()->toSql(true);
		}

		// Updating the todo record
		if (empty($data['recommend_friends']) and $data['todo_id'])
		{
			$table = Table::getInstance('recommendation', 'JlikeTable', array('dbo', $this->db));

			// Check if already Enrolled User
			$table->load(array('id' => (int) $data['todo_id']));
			$usersToRecommend = array($table->assigned_to);
		}
		else
		{
			$usersToRecommend = $data['recommend_friends'];
		}

		foreach ($usersToRecommend as $eachrecommendation)
		{
			try
			{
				// First parameter file name and second parameter is prefix
				$table = Table::getInstance('recommendation', 'JlikeTable', array('dbo', $this->db));

				if (isset($data['todo_id']))
				{
					$table->load(array('id' => (int) $data['todo_id']));
				}

				if (!$table->id)
				{
					$table->id = '';
				}

				$table->modified_date = Factory::getDate()->toSql(true);
				$table->assigned_to = $eachrecommendation;

				// Require_once $path;
				$techJoomlaCommonPath = JPATH_SITE . '/libraries/techjoomla/common.php';
				if (file_exists($techJoomlaCommonPath)) {
					require_once $techJoomlaCommonPath;
				}

				$techjoomlaCommon = new TechjoomlaCommon;

				if ($type == 'assign')
				{
					if ($data['start_date'])
					{
						$table->start_date = $techjoomlaCommon->getDateInUtc($data['start_date']);
					}
					else
					{
						if (!$table->id)
						{
							$table->start_date = $techjoomlaCommon->getDateInUtc($data['start_date']);
						}
					}

					if ($data['due_date'])
					{
						$tempdate        = new DateTime($data['due_date']);
						$tempdate->setTime(23, 59, 59);
						$new_date        = $tempdate->format('Y-m-d H:i:s');
						$due_date        = $techjoomlaCommon->getDateInUtc($new_date);
						$table->due_date = $due_date;
					}

					$options['start_date'] = $table->start_date;
					$options['due_date']   = $table->due_date;
					$options['sender_msg'] = $table->sender_msg;
				}

				$table->content_id = $content_id;

				if (isset($data['sender_msg']))
				{
					$table->sender_msg = $data['sender_msg'];
				}

				$table->created_by  = isset($data['created_by']) ? $data['created_by'] : $this->user->id;
				$table->assigned_by = isset($data['assigned_by']) ? $data['assigned_by'] : $this->user->id;
				$table->status      = isset($data['status']) ? $data['status'] : 'S';
				$table->state       = isset($data['state']) ? $data['state'] : '1';

				if (isset($data['created_date']))
				{
					$table->created_date = $data['created_date'];
				}
				else
				{
					$OnDate              = Factory::getDate();
					$table->created_date = $OnDate->toSql(true);
				}

				// @Todo Get content title.
				$table->title = $options['element_id'];
				$table->type  = $data['type'];
				$table->store();
				$recid        = $table->id;

				// Email Notification flag set
				if ($notify_user == 1)
				{
					// Get integration
					$socialIntegration = ComjlikeMainHelper::getSocialIntegration($options['plg_type'], $options['plg_name']);

					// Notification sender & receiver
					$sender   = $this->user;
					$receiver = Factory::getUser($table->assigned_to);

					// Notification message
					if ($table->type == 'reco')
					{
						$msg = Text::sprintf(Text::_("COM_JLIKE_RECOMMENDATIONS_NOTIFICATION"), $sender->name, $elementdata['title']);

						if (!empty($table->sender_msg))
						{
							Text::sprintf(Text::_("COM_JLIKE_USER_MESSAGE_RECOMMEND"), $table->sender_msg);
						}
					}
					else
					{
						if (!empty($data['todo_id']))
						{
							if ($sender->id == $receiver->id)
							{
								$msg = Text::sprintf(Text::_("COM_JLIKE_SETGOAL_NOTIFICATION_UPDATE"), $sender->name, $elementdata['title']);
							}
							else
							{
								$msg = Text::sprintf(Text::_("COM_JLIKE_ASSIGN_NOTIFICATION_UPDATE"), $sender->name, $elementdata['title']);
							}
						}
						else
						{
							if ($sender->id == $receiver->id)
							{
								$msg = Text::sprintf(Text::_("COM_JLIKE_SETGOAL_NOTIFICATION"), $sender->name, $elementdata['title']);
							}
							else
							{
								$msg = Text::sprintf(Text::_("COM_JLIKE_ASSIGN_NOTIFICATION"), $sender->name, $elementdata['title']);
							}
						}

						if (!empty($table->sender_msg))
						{
							Text::sprintf(Text::_("COM_JLIKE_USER_MESSAGE_ASSIGN"), $table->sender_msg);
						}
					}

					// To Enable/Disable notification
					$send_notification = $this->params->get('send_auto_reminders');

					if ($send_notification)
					{
						// Send notification
						$itemlink  = $elementdata['url'];

						$link = '';

						$app = Factory::getApplication();

						// ACTOR MAIL BODY
						if ($app->isClient("site") && $itemlink)
						{
							$link = Uri::root() . substr(Route::_($itemlink), strlen(Uri::base(true)) + 1);
						}
						elseif ($app->isClient("administrator") && $itemlink)
						{
							$link = Uri::base() . substr(Route::_($itemlink), strlen(Uri::base(true)) + 1);
							$parsed_url  = str_replace(Uri::base(true), "", $link);

							// $appInstance = JApplication::getInstance('site');
							$appInstance = CMSApplication::getInstance('site');
							$router      = $appInstance->getRouter();
							$uri         = $router->build($parsed_url);
							$parsed_url  = $uri->toString();
							$link        = str_replace("/administrator", "", $parsed_url);
						}

						if ($link)
						{
							$link = '<a href="' . $link . '">' . $elementdata['title'] . '</a>';
						}

						if ($table->type == 'reco')
						{
							$body = Text::_('COM_JLIKE_RECOMMENDATIONS_MAIL_CONTENT');
						}
						else
						{
							if (!empty($data['todo_id']))
							{
								if ($sender->id == $receiver->id)
								{
									$body = Text::_('COM_JLIKE_SETGOAL_MAIL_CONTENT_UPDATE');
								}
								else
								{
									$body = Text::_('COM_JLIKE_ASSIGNMENT_MAIL_CONTENT_UPDATE');
								}
							}
							else
							{
								if ($sender->id == $receiver->id)
								{
									$body = Text::_('COM_JLIKE_SETGOAL_MAIL_CONTENT');
								}
								else
								{
									$body = Text::_('COM_JLIKE_ASSIGNMENT_MAIL_CONTENT');
								}
							}

							$start_date = Factory::getDate($data['start_date'])->Format(Text::_('COM_JLIKE_DATE_FORMAT'));

							$due_date = isset($data['due_date']) ? Factory::getDate($data['due_date'])->Format(Text::_('COM_JLIKE_DATE_FORMAT')) :
							Factory::getDate($table->due_date)->Format(Text::_('COM_JLIKE_DATE_FORMAT'));

							$body = str_replace('{short_desc}', $options['element_short_desc'], $body);
							$body = str_replace('{start_date}', $start_date, $body);
							$body = str_replace('{due_date}', $due_date, $body);
						}

						$body = str_replace('{user_msg}', $table->sender_msg, $body);
						$body = str_replace('{receiver}', Factory::getUser($table->assigned_to)->name, $body);
						$body = str_replace('{sender}', Factory::getUser($table->assigned_by)->name, $body);

						if (!empty($link))
						{
							$body = str_replace('{title}', $link, $body);
						}
						else
						{
							$body = str_replace('<h3>{title}</h3>', $link, $body);
						}

						$comJlikeSubHelper = new ComjlikeSubHelper;

						if (!empty($msg))
						{
							$body = $msg;
						}

						// Get email content from respective plugin
						PluginHelper::importPlugin($options['plg_type'], $options['plg_name']);
						$notificationDetails = Factory::getApplication()->triggerEvent('onAfterGet' . $options['plg_name'] . 'RecommendationNotificationDetails',
							array($options, $data, $elementdata)
						);

						if (!empty($notificationDetails[0]))
						{
							$notificationDetails = $notificationDetails[0];

							$options['notifyClient']       = $notificationDetails['notifyClient'];
							$options['notifyKey']          = $notificationDetails['notifyKey'];
							$options['replacementsObj']    = $notificationDetails['replacementsObj'];
							$options['optionsRegistryObj'] = $notificationDetails['optionsRegistryObj'];
						}
						else
						{
							$replacements                     = new stdClass;
							$replacements->notification->user = Factory::getUser($table->assigned_to)->name;
							$replacements->notification->msg  = $body;

							$optionsRegistryObj = new Registry;

							$options['notifyClient']       = 'jlike';
							$options['notifyKey']          = 'jlike.recommend';
							$options['replacementsObj']    = $replacements;
							$options['optionsRegistryObj'] = $optionsRegistryObj;
						}

						$comJlikeSubHelper->sendNotificationToUsers($socialIntegration, $body, $table->assigned_to, $options);
					}
				}

				if ($table->type == 'reco')
				{
					// Trigger after recommend
					Factory::getApplication()->triggerEvent('onAfterRecommend', array(
							$recid,
							$eachrecommendation,
							$this->user->id,
							$options,
							$notify_user
						)
					);
				}
				elseif ($table->type == 'assign')
				{
					Factory::getApplication()->triggerEvent('onAfterAssignment', array(
							$recid,
							$eachrecommendation,
							$this->user->id,
							$options,
							$notify_user
						)
					);
				}
			}
			catch (RuntimeException $e)
			{
				Factory::getApplication()->enqueueMessage($e->getMessage());

				return false;
			}
		}

		return true;
	}

	/**
	 * Retrieves a list of friends (Easysocial)
	 *
	 * @param   int    $id       The user's id
	 * @param   Array  $options  An array of options. state - SOCIAL_FRIENDS_STATE_PENDING or SOCIAL_FRIENDS_STATE_FRIENDS
	 *
	 * @return   Array
	 *
	 * @since   1.0
	 */
	public function getESFriends($id, $options = array())
	{
		require_once JPATH_ROOT . '/administrator/components/com_easysocial/includes/foundry.php';

		$config = FD::config();

		$db = FD::db();

		// $sql = $db->sql();

		$query = $db->getQuery(true);
		$query->select('a.*, if( a.target_id= ' . $db->Quote($id) . ', a.actor_id, a.target_id) AS friendid');
		$query->select($db->qn(array('u.name', 'u.username')));
		$query->from($db->qn('#__social_friends', 'a'));
		$query->join('INNER', '#__users AS u ON u.id = if( a.target_id = ' . $db->Quote($id) . ', a.actor_id, a.target_id)');
		$query->join('INNER', $db->qn('#__social_profiles_maps', 'upm') . ' ON ( ' . $db->qn('u.id') . ' = ' . $db->qn('upm.user_id') . ')');
		$query->join('INNER', $db->qn('#__social_profiles', 'up') . ' on (' . $db->qn('upm.`profile_id') . ' = ' . $db->qn('up.id') . '
		AND ' . $db->qn('up.community_access = 1') . ')');

		if ($config->get('users.blocking.enabled') && !Factory::getUser()->guest)
		{
			$query->join('LEFT', $db->qn('#__social_block_users', 'bus') . ' ON ( ' . $db->qn('u.id') . ' = ' . $db->qn('bus.user_id') . '
			AND ' . $db->qn('bus.target_id') . ' = ' . $db->Quote(Factory::getUser()->id) . ')');
		}

		$query->where($db->qn('u.block') . ' = ' . $db->Quote('0'));

		if ($config->get('users.blocking.enabled') && !Factory::getUser()->guest)
		{
			$query->where($db->qn('bus.id') . ' IS NULL');
		}

		$query->where($db->qn('a.state') . '= 1 ');

		return $query;
	}

	/**
	 * Retrieves a list of users(Joomla)
	 *
	 * @return   Array
	 *
	 * @since   1.0
	 */
	public function getAllUser()
	{
		$db = Factory::getDBO();

		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select($this->getState('list.select', 'distinct(u.id) as friendid, u.name, u.username'));
		$query->from($db->qn('#__users', 'u'));
		$query->where($db->qn('u.block') . '= 0 ');

		// Filter by search in title
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where($db->qn('u.id') . ' = ' . (int) substr($search, 3));
			}
			else
			{
				$search = $db->Quote('%' . $db->escape($search, true) . '%');
				$query->where('(( u.name LIKE ' . $search . ' ) OR ( u.username LIKE ' . $search . ' ))');
			}
		}

		// Add the list ordering clause.
		$orderCol  = $this->state->get('list.ordering');
		$orderDirn = $this->state->get('list.direction');

		if ($orderCol && $orderDirn)
		{
			$query->order($db->escape($orderCol . ' ' . $orderDirn));
		}

		return $query;
	}

	/**
	 * To delete to do function
	 *
	 * @param   int  $todo_id  The user ID
	 *
	 * @return  boolean
	 *
	 * @since  1.0.0
	 */
	public function deleteTodo($todo_id)
	{
		if (!$todo_id)
		{
			return;
		}

		// Add Table Path
		Table::addIncludePath(JPATH_ROOT . '/administrator/components/com_jlike/tables');
		$table = Table::getInstance('recommendation', 'JlikeTable', array('dbo', $this->db));
		$table->load(array('id' => (int) $todo_id));

		return $table->delete();
	}
}
