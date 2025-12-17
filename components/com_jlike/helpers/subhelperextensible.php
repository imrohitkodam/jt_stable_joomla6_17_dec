<?php
/**
 * @package     JLike
 * @subpackage  com_jlike
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access.
defined('_JEXEC') or die();
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Registry\Registry;

require_once JPATH_SITE . '/components/com_jlike/helpers/subhelperextensibleparent.php';
require_once JPATH_LIBRARIES . '/techjoomla/tjnotifications/tjnotifications.php';

/**
 * Main helper
 *
 * @package     Jlike
 * @subpackage  site
 * @since       2.2
 */
class ComjlikeSubHelperExtensible extends ComjlikeSubHelperExtensibleParent
{
	/**
	 * sendNotifnAfterLikeDislikeCountIncreased
	 *
	 * @param   integer  $likeDislike   likeDislike
	 * @param   string   $comment       comment
	 * @param   integer  $annotationid  annotationid
	 * @param   array    $extraParams   Extra params
	 *
	 * @return  void
	 *
	 * @since 1.0
	 */
	public function sendNotifnAfterLikeDislikeCountIncreased($likeDislike, $comment, $annotationid, $extraParams)
	{
		$plg_name = $plg_type = '';

		if (!empty($extraParams))
		{
			$plg_type = $extraParams['plg_type'];
			$plg_name = $extraParams['plg_name'];
		}

		$jlikemainhelperObj        = new ComjlikeMainHelper;
		$params                    = $jlikemainhelperObj->getjLikeParams($plg_type, $plg_name);

		if (($likeDislike == 2 && $params->get('js_notification_on_like')) || ($likeDislike == 3 && $params->get('js_notification_on_dislike')))
		{
			$integration = $jlikemainhelperObj->getSocialIntegration($plg_type, $plg_name);
			$this->notificationOnReplyOrLike($annotationid, $comment, $likeDislike, $integration, $extraParams);
		}
	}

	/**
	 * method to add the user id in likes table when he/she dislike the comment
	 *
	 * @param   integer  $annotationid  (string) name of view
	 * @param   String   $comment       (string) name of view
	 * @param   Array    $extraParams   Array of plug name and plug type
	 *
	 * @return  Integer|String
	 *
	 * @since 1.0
	 */
	public function increaseDislikeCount($annotationid, $comment, $extraParams=array())
	{
		$userId          = Factory::getUser()->id;
		$comjlikeHelper  = new comjlikeHelper;
		$userLikeDislike = $comjlikeHelper->getUserCurrentLikeDislike($annotationid, $userId);
		$response        = '';

		// $dislike=3; // identify that notification for dislike
		$dislike         = 0;

		// Like or Unlike
		if ($userLikeDislike == 0)
		{
			// Dislike (user record not present in the table)
			$this->insertIntoIncreaseLikeDislikeCount($annotationid, 0, 1);
			$response = 1;
			$dislike  = 3;
		}
		elseif ($userLikeDislike == 1)
		{
			// Like to dislike (user record present in the table update the record)
			$this->updateIntoIncreaseLikeDislikeCount($annotationid, $userId, 0, 1);
			$response = 2;
			$dislike  = 3;
		}
		elseif ($userLikeDislike == 2)
		{
			// Unlike (user already like the comment but now want to unlike it)
			$this->deleteFromIncreaseLikeDislikeCount($annotationid, $userId);
			$response = 1;
			$dislike  = 0;
		}

		$this->sendNotifnAfterLikeDislikeCountIncreased($dislike, $comment, $annotationid, $extraParams);

		return $response;
	}

	/**
	 * insertIntoIncreaseLikeDislikeCount
	 *
	 * @param   String   $annotationid  (string) name of view
	 * @param   integer  $like          like
	 * @param   integer  $dislike       dislike
	 *
	 * @return  void|String
	 *
	 * @since 1.0
	 */
	public function insertIntoIncreaseLikeDislikeCount($annotationid, $like, $dislike)
	{
		$db 					   = Factory::getDBO();
		$insert_obj                = new stdClass;
		$insert_obj->content_id    = 0;
		$insert_obj->annotation_id = $annotationid;
		$insert_obj->userid        = Factory::getUser()->id;
		$insert_obj->like          = $like;
		$insert_obj->dislike       = $dislike;
		$insert_obj->date          = time();

		if (!$db->insertObject('#__jlike_likes', $insert_obj, 'id'))
		{
			return $db->stderr();
		}
	}

	/**
	 * deleteFromIncreaseLikeDislikeCount
	 *
	 * @param   String   $annotationid  (string) name of view
	 * @param   integer  $userId        userId
	 *
	 * @return  void|String
	 *
	 * @since 1.0
	 */
	public function deleteFromIncreaseLikeDislikeCount($annotationid, $userId)
	{
		$db = Factory::getDBO();
		$query = $db->getQuery(true);
		$query->delete('#__jlike_likes')
			->where('annotation_id = ' . $db->quote($annotationid) . ' AND userid= ' . $db->quote($userId));
		$db->setQuery($query);

		if (!$db->execute($query))
		{
			return $db->stderr();
		}
	}

	/**
	 * updateIntoIncreaseLikeDislikeCount
	 *
	 * @param   String   $annotationid  (string) name of view
	 * @param   integer  $userId        userId
	 * @param   integer  $like          like
	 * @param   integer  $dislike       dislike
	 *
	 * @return  void|String
	 *
	 * @since 1.0
	 */
	public function updateIntoIncreaseLikeDislikeCount($annotationid, $userId, $like, $dislike)
	{
		$db = Factory::getDBO();
		$query = $db->getQuery(true);

		$fields = array(
			$db->quoteName('like') . ' = ' . $db->quote($like),
			$db->quoteName('dislike') . ' = ' . $db->quote($dislike)
		);

		$cond = array(
			$db->quoteName('annotation_id') . ' = ' . $db->quote($annotationid),
			$db->quoteName('userid') . ' = ' . $db->quote($userId)
		);

		$query->update($db->quoteName('#__jlike_likes'))->set($fields)->where($cond);
		$db->setQuery($query);

		if (!$db->execute($query))
		{
			return $db->stderr();
		}
	}

	/**
	 * method to add the user id in likes table when he/she dislike the comment
	 *
	 * @param   integer  $annotationid  (string) name of view
	 * @param   String   $comment       (string) name of view
	 * @param   Array    $extraParams   Array of plug name and plug type
	 *
	 * @return  Integer|String
	 *
	 * @since 1.0
	 */
	public function increaseLikeCount($annotationid, $comment, $extraParams=array())
	{
		$userId          = Factory::getUser()->id;
		$comjlikeHelper  = new comjlikeHelper;
		$userLikeDislike = $comjlikeHelper->getUserCurrentLikeDislike($annotationid, $userId);
		$response = '';
		$like     = 0;

		// Like or Unlike
		if ($userLikeDislike == 0)
		{
			// Like
			$this->insertIntoIncreaseLikeDislikeCount($annotationid, 1, 0);
			$response = 1;
			$like     = 2;
			$extraParams['method'] = 'like';
		}
		elseif ($userLikeDislike == 1)
		{
			// Unlike (user already like the comment but now want to unlike it)
			$this->deleteFromIncreaseLikeDislikeCount($annotationid, $userId);
			$like     = 0;
			$response = 1;
			$extraParams['method'] = 'unlike';
		}
		elseif ($userLikeDislike == 2)
		{
			// Dislike to like
			$this->updateIntoIncreaseLikeDislikeCount($annotationid, $userId, 1, 0);
			$like     = 2;
			$response = 2;
			$extraParams['method'] = 'dislike';
		}


		// Append inserted comment entry id in action log data
		$extraParams['entry_id'] = $annotationid;

		// Trigger the after save event.
		Factory::getApplication()->triggerEvent('onAfterJlikeLikeUnlikeCommentSave', array($extraParams));

		$this->sendNotifnAfterLikeDislikeCountIncreased($like, $comment, $annotationid, $extraParams);

		return $response;
	}

	/**
	 * getMyContentLablesFromDB
	 *
	 * @param   Integer  $content_id  Like content id.
	 * @param   Integer  $user_id     user_id.
	 * @param   Integer  $lableArray  if set to 1 then array will be retured.
	 *
	 * @return String|Array
	 *
	 * @since 3.0
	 */
	public function getMyContentLablesFromDB($content_id, $user_id, $lableArray = 0)
	{
		//  Delete the xref table entry first 	$query->join('LEFT', '`#__categories` AS c ON c.id=ki.category');
		$db        = Factory::getDBO();
		$lableHtml = '';
		$query = $db->getQuery(true);
		$query->select('list.id,list.title,lref.content_id')
				->from('#__jlike_likes_lists_xref AS lref')
				->join('INNER', '#__jlike_like_lists AS list ON list.id = lref.list_id');
		$query->where('lref.content_id=' . $db->quote($content_id) . 'AND' . 'list.user_id=' . $db->quote($user_id));
		$db->setQuery($query);
		$lists = $db->loadObjectList();

		if ($lableArray)
		{
			return $lists;
		}

		foreach ($lists as $lable)
		{
			$lableHtml = $lableHtml . $lable->title . ', ';
		}

		//  Remove last occarance ,
		return rtrim($lableHtml, ", ");
	}

	/**
	 * Getting users lable list for particular article
	 *
	 * @param   Integer  $content_id  Like content id.
	 * @param   Integer  $user_id     user_id.
	 * @param   Integer  $lableArray  if set to 1 then array will be retured.
	 *
	 * @return String|Array
	 *
	 * @since 3.0
	 */
	public function getMyContentLables($content_id, $user_id, $lableArray = 0)
	{
		$lableHtml = '';

		if ($lableArray)
		{
			$lableHtml = array();
		}

		if ($content_id && $user_id)
		{
			try
			{
				return $this->getMyContentLablesFromDB($content_id, $user_id, $lableArray = 0);
			}
			catch (Exception $e)
			{
				//  $e->getMessage();
				return $lableHtml;
			}
		}
	}

	/**
	 * getAnnotationForNotification
	 *
	 * @param   string  $id  id
	 *
	 * @return  array
	 */
	public function getAnnotationForNotification($id)
	{
		$db    = Factory::getDBO();
		$query = $db->getQuery(true);
		$query->select('user_id')
			->from($db->quoteName('#__jlike_annotations'))
			->where($db->quoteName("id") . ' = ' . $db->quote($id));
		$db->setQuery($query);

		return $db->loadResult();
	}

	/**
	 * sendNotificationToUsers
	 *
	 * @param   string   $integration       integration
	 * @param   string   $notification_msg  notification_msg
	 * @param   integer  $owner_id          owner_id
	 * @param   Array    $extraParams       Array of plug name and plug type
	 *
	 * @return  array
	 */
	public function sendNotificationToUsers($integration, $notification_msg, $owner_id, $extraParams = array())
	{
		$commented_by_userid      = Factory::getUser()->id;
		$commented_by_name        = Factory::getUser()->name;
		$socialintegrationHelper  = new socialintegrationHelper;
		$commented_by_profile_url = Route::_($socialintegrationHelper->getUserProfileUrl($commented_by_userid));

		require_once JPATH_SITE . '/components/com_jlike/models/jlike_likes.php';

		$notification_subject = '<a href="' . $commented_by_profile_url . '" >' . $commented_by_name . '</a>' . $notification_msg;

		if ($integration == 'js' && ComponentHelper::isEnabled('com_community'))
		{
			// If jomsocial is installed
			$socialintegrationHelper->send_js_notification($commented_by_userid, $commented_by_name, $owner_id, $notification_subject);
		}
		elseif ($integration == 'easysocial' && ComponentHelper::isEnabled('com_easysocial'))
		{
			// If easysocial present on site
			$socialintegrationHelper->send_es_notification($commented_by_userid, $commented_by_name, $owner_id, $notification_msg);
		}

		if (ComponentHelper::isEnabled('com_tjnotifications'))
		{
			$recipients = array (
				// Add specific to, cc (optional), bcc (optional)
				'email' => array (
					'to' => array (Factory::getUser($owner_id)->email)
				)
			);

			$replacements = $extraParams['replacementsObj'];
			$options      = $extraParams['optionsRegistryObj'];

			Tjnotifications::send($extraParams['notifyClient'], $extraParams['notifyKey'], $recipients, $replacements, $options);
		}
	}

	/**
	 * method to send the notification to the user when
	 * 1> reply on comment  2> like or dislike on comment
	 * $callFrom =1 => reply on comment
	 * $callFrom =2 like comment
	 * $callFrom =3 dislike on comment
	 *
	 * @param   String  $parent_id    (string) name of view
	 * @param   String  $comment      (string) name of view
	 * @param   String  $callFrom     (string) name of view
	 * @param   String  $integration  social extension to which integration is set
	 * @param   Array   $extraParams  Array of plug name and plug type
	 *
	 * @return  void if exit override view then return path
	 *
	 * @since 1.0
	 */
	public function notificationOnReplyOrLike($parent_id,
		/** @scrutinizer ignore-unused */ $comment, $callFrom, $integration, $extraParams = array())
	{
		$ParentUser_id = $this->getAnnotationForNotification($parent_id);

		if (!empty($ParentUser_id))
		{
			$owner_id = $ParentUser_id;

			/*to add notification in JS */
			$notification_msg = '';

			if ($callFrom == 1)
			{
				$notification_msg = Text::_('COM_JLIKE_REPLY_ON_COMMNET');

				// Get email content from respective plugin
				$this->setNotificationDetails('CommentReplyNotificationDetails',
					array($extraParams, $parent_id, $owner_id),	$extraParams, $owner_id, 'jlike.commentreply');
			}
			elseif ($callFrom == 2)
			{
				$notification_msg = Text::_('COM_JLIKE_ON_LIKE');

				// Get email content from respective plugin
				$this->setNotificationDetails('LikeOnCommentNotificationDetails',
						array($extraParams, $parent_id, $owner_id),	$extraParams, $owner_id, 'jlike.commentlike');
			}
			elseif ($callFrom == 3)
			{
				$notification_msg = Text::_('COM_JLIKE_ON_DISLIKE');

				// Get email content from respective plugin
				$this->setNotificationDetails('DislikeOnCommentNotificationDetails',
						array($extraParams, $parent_id, $owner_id),	$extraParams, $owner_id, 'jlike.commentdislike');
			}

			$this->sendNotificationToUsers($integration, $notification_msg, /** @scrutinizer ignore-type */ $owner_id, $extraParams);
		}
	}

	/**
	 * setNotificationDetails
	 *
	 * @param   string  $plgFnName     Plugin function name
	 * @param   array   $plgFnParams   Plugin function params
	 * @param   array   &$extraParams  Extra parameter (plugin name, plugin type, etc.)
	 * @param   array   $ownerId       Content/Comment Owner Id
	 * @param   string  $notifyKey     Default Tjnotification Key (Client is always 'Jlike' for default notification)
	 *
	 * @return  void
	 *
	 * @since 3.0
	 */
	public function setNotificationDetails($plgFnName, $plgFnParams, &$extraParams, $ownerId, $notifyKey)
	{

		PluginHelper::importPlugin($extraParams['plg_type'], $extraParams['plg_name']);
		$notificationDetails = Factory::getApplication()->triggerEvent('onAfterGet' . $extraParams['plg_name'] . $plgFnName, $plgFnParams);

		if (!empty($notificationDetails[0]))
		{
			$notificationDetails = $notificationDetails[0];

			$extraParams['notifyClient']       = $notificationDetails['notifyClient'];
			$extraParams['notifyKey']          = $notificationDetails['notifyKey'];
			$extraParams['replacementsObj']    = $notificationDetails['replacementsObj'];
			$extraParams['optionsRegistryObj'] = $notificationDetails['optionsRegistryObj'];
		}
		else
		{
			if (!empty($ownerId))
			{
				$replacements                      = new stdClass;
				$replacements->notification 	   = new stdClass;
				$replacements->notification->owner = Factory::getUser($ownerId)->name;
				$replacements->notification->user  = Factory::getUser()->name;
				$replacements->notification->title = $extraParams['title'];

				$options = new Registry;

				$extraParams['notifyClient']       = 'jlike';
				$extraParams['notifyKey']          = $notifyKey;
				$extraParams['replacementsObj']    = $replacements;
				$extraParams['optionsRegistryObj'] = $options;
			}
		}
	}
}
