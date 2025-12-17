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
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\HTML\HTMLHelper;

require_once JPATH_SITE . '/components/com_jlike/helpers/subhelperextensible.php';

/**
 * Main helper
 *
 * @package     Jlike
 * @subpackage  site
 * @since       2.2
 */
class ComjlikeSubHelper extends ComjlikeSubHelperExtensible
{
	/**
	 * Notification to content owner after comment added
	 *
	 * @param   String  $comment               (string) name of view
	 * @param   String  $cnt_id                (string) name of view
	 * @param   String  $element               (string) name of view
	 * @param   String  $url                   (string) name of view
	 * @param   String  $title                 (string) name of view
	 * @param   String  $plg_name              (string) name of view
	 * @param   String  $parent_id             (string) name of view
	 * @param   String  $plg_type              (string) name of view
	 * @param   String  $notification_content  (string) name of view
	 * @param   array   $extraParams           Extra params
	 *
	 * @return  void if exit override view then return path
	 *
	 * @since 1.0
	 */
	public function notification($comment, $cnt_id,
		/** @scrutinizer ignore-unused */ $element,
		/** @scrutinizer ignore-unused */ $url,
		/** @scrutinizer ignore-unused */ $title, $plg_name, $parent_id, $plg_type, $notification_content, $extraParams)
	{
		PluginHelper::importPlugin($plg_type, $plg_name);
		$userid = Factory::getApplication()->triggerEvent('onAfterGet' . $plg_name . 'OwnerDetails', array($cnt_id));
		$owner_id = 0;

		if (!empty($userid))
		{
			$owner_id = $userid[0];
		}

		$jlikemainhelperObj        = new ComjlikeMainHelper;
		$integration = $jlikemainhelperObj->getSocialIntegration($plg_type, $plg_name);

		// Do not send notification if content owner itself took action on his own content
		if (!empty($owner_id) && $owner_id != Factory::getUser()->id)
		{
			$this->sendNotificationToUsers($integration, $notification_content, $owner_id, $extraParams);
		}

		// Notification to parent on replies on comments
		$params                    = $jlikemainhelperObj->getjLikeParams($plg_type, $plg_name);
		$js_notification_replies = $params->get('js_notification_replies');

		if ($js_notification_replies && !empty($parent_id))
		{
			// Identify function call for reply notification
			$reply = 1;
			$this->notificationOnReplyOrLike($parent_id, $comment, $reply, $integration, $extraParams);
		}
	}

	/**
	 * Save like or dislike details.
	 *
	 * @param   object  $data  post data.
	 *
	 * @since   2.2
	 * @return  array
	 */
	public function registerLike($data)
	{
		$params 	= ComponentHelper::getParams('com_jlike');
		$contentres = $this->fetchContentToAddComments($data['element'], $data['element_id']);
		$element_id = $this->insertLikesContentIfNotExist(/** @scrutinizer ignore-type */ $contentres, $data);
		$like_id 	= $this->fetchAndInsertJlikeLikes($element_id);
		$verbArray 	= $this->updateJlikeLikes($element_id, /** @scrutinizer ignore-type */ $data, $like_id);

		// Save the status releated things, in future rating etc
		$statusMgt = $params->get('statusMgt', 0);

		if ($statusMgt && ($data['method'] == "like" || $data['method'] == "unlike"))
		{
			$like_statusId = $data['like_statusId'];

			if ($data['method'] == "unlike")
			{
				// @TODO get completed status id dymanically
				$like_statusId = isset($data['statusParam']) ? $data['statusParam'] : 0;
			}

			$comjlikeHelper = new comjlikeHelper;
			$comjlikeHelper->storeExtraData($element_id, /** @scrutinizer ignore-type */ $like_statusId);
		}

		return $this->returnRegisterLikeData($element_id, /** @scrutinizer ignore-type */ $data, $verbArray, $like_id);
	}

	/**
	 * fetchContentToAddComments
	 *
	 * @param   integer  $element     element
	 * @param   integer  $element_id  element_id
	 *
	 * @return  Array
	 *
	 * @since 1.0
	 */
	public function fetchContentToAddComments($element, $element_id)
	{
		$db    = Factory::getDBO();
		$query = $db->getQuery(true);
		$query->select($db->quoteName(array('jc.id','jc.like_cnt','jc.dislike_cnt')));
		$query->from($db->quoteName('#__jlike_content', 'jc'));
		$query->where($db->quoteName('jc.element') . ' = ' . $db->quote($element));
		$query->where($db->quoteName('jc.element_id') . ' = ' . $db->quote($element_id));
		$db->setQuery($query);

		return $db->loadObject();
	}

	/**
	 * saveCmtInAnnotion
	 *
	 * @param   integer  $content_id   content_id
	 * @param   Array    $commentData  Comments data options
	 * @param   Array    $extraParams  Extra params
	 *
	 * @return  integer|boolean
	 *
	 * @since 1.0
	 */
	public function saveCmtInAnnotion($content_id, $commentData, $extraParams)
	{
		$comment   = $commentData['comment'];
		$parent_id = $commentData['parent_id'];
		$note_type = $commentData['note_type'];
		$images = $commentData['images'];

		$db    = Factory::getDBO();
		$CommentToSave             = new StdClass;
		$CommentToSave->user_id    = Factory::getUser()->id;
		$CommentToSave->content_id = $content_id;
		$CommentToSave->annotation = $db->escape($comment);
		$CommentToSave->privacy    = 0;
		$CommentToSave->state      = 1;
		$CommentToSave->parent_id  = $parent_id;
		$CommentToSave->images  = json_encode($images);

		if (isset($extraParams['type']) && $extraParams['type'])
		{
			$CommentToSave->type = $extraParams['type'];
		}

		/* This is comment means not a note
		 * 0 : Comment
		 * 1 : Note
		 * 2 : Reviews
		 */
		$CommentToSave->note = $note_type;
		$CommentToSave->annotation_date = Factory::getDate()->toSQL();
		$commentSaved = $db->insertObject('#__jlike_annotations', $CommentToSave);

		if ($commentSaved)
		{
			return $db->insertid();
		}

		return false;
	}

	/**
	 * sendNotifnAfterCommtSavd
	 *
	 * @param   integer  $annotation_id  annotation_id
	 * @param   array    $commentData    Comments data options
	 * @param   array    $extraParams    Extra params
	 *
	 * @return  array
	 *
	 * @since 1.0
	 */
	public function sendNotifnAfterCommtSavd($annotation_id, $commentData, $extraParams)
	{
		$resultArray = array();
		$element = $extraParams['element'];
		$cont_id = $extraParams['cont_id'];
		$url     = $extraParams['url'];
		$title   = $extraParams['title'];
		$comment   = $commentData['comment'];
		$parent_id = $commentData['parent_id'];

		// Check notification is on & integration is jomsocial
		$plg_type = !empty($extraParams['plg_type']) ? $extraParams['plg_type']: '';
		$plg_name = !empty($extraParams['plg_name']) ? $extraParams['plg_name']: '';

		// Activity Stream Integration with cb,JS,jomwall
		$jlikemainhelperObj        = new ComjlikeMainHelper;
		$params                    = $jlikemainhelperObj->getjLikeParams($plg_type, $plg_name);
		$js_notification = $params->get('js_notification');

		if ($js_notification)
		{
			$notification_msg = Text::_("COM_JLIKE_ADDED_COMMENT") . $title;

			$getOwner = Factory::getApplication()->triggerEvent('onAfterGet' . $extraParams['plg_name'] . 'OwnerDetails', array($extraParams['cont_id']));

			// Get email content from respective plugin
			$this->setNotificationDetails('CommentNotificationDetails', array($extraParams, $annotation_id, $commentData),
							$extraParams, $getOwner[0], 'jlike.comment');

			$this->notification(
				$comment, $cont_id, $element, $url,
				$title, $plg_name, $parent_id,
				$plg_type, $notification_msg, $extraParams
			);
		}

		// Activity stream after saving comments
		$comjlikeHelper = new comjlikeHelper;
		$comjlikeHelper->activityStream($comment, $cont_id, $element, $url, $title, $plg_name, $plg_type);

		// Parse the Comment
		require_once JPATH_SITE . '/components/com_jlike/models/jlike_likes.php';
		$JlikeModeljlike_Likes = new JlikeModeljlike_Likes;
		$comment = $JlikeModeljlike_Likes->parsedMention($comment, $extraParams);
		$resultArray['annotation_id'] = $annotation_id;
		$resultArray['comment'] = $comment;

		// Get Comment Date
		$annotationModel = BaseDatabaseModel::getInstance('Annotation', 'JLikeModel', array('ignore_request' => true));
		$annotationDetails = $annotationModel->getData($annotation_id);

		$time = HTMLHelper::date($annotationDetails->annotation_date, Text::_('COM_JLIKE_COMMENT_TIME_FORMAT'), true);

		$resultArray['date'] = HTMLHelper::date($annotationDetails->annotation_date, Text::_('COM_JLIKE_COMMENT_DATE_FORMAT'));
		$resultArray['date'] .= Text::_('COM_JLIKE_COMMENT_DATE_TIME_SEPERATOR') . $time;

		return $resultArray;
	}

	/**
	 * insertLikesContentIfNotExist
	 *
	 * @param   object  $fetchedContent  From fetchContentToAddComments.
	 * @param   object  $data            post data.
	 *
	 * @since   2.2
	 * @return  integer
	 */
	public function insertLikesContentIfNotExist($fetchedContent, $data)
	{
		if (empty($fetchedContent))
		{
			$db    = Factory::getDBO();
			$insert_obj             = new stdClass;
			$insert_obj->element_id = $data['element_id'];
			$insert_obj->element    = $data['element'];
			$insert_obj->url        = $data['url'];
			$insert_obj->title      = $data['title'];
			$db->insertObject('#__jlike_content', $insert_obj);

			return $db->insertid();
		}
		else
		{
			return $fetchedContent->id;
		}
	}

	/**
	 * Add comment
	 *
	 * @param   Array  $commentData  Comments data options
	 * @param   Array  $extraParams  Extra params
	 *
	 * @return  Array|boolean
	 *
	 * @since 1.0
	 */
	public function addComment($commentData, $extraParams)
	{
		$data = $extraParams;
		$data['element_id'] = $extraParams['cont_id'];
		$contentres = $this->fetchContentToAddComments($extraParams['element'], $extraParams['cont_id']);
		$content_id = $this->insertLikesContentIfNotExist(/** @scrutinizer ignore-type */ $contentres, /** @scrutinizer ignore-type */ $data);


		if ($content_id)
		{
			$saveCmtInAnnotion = $this->saveCmtInAnnotion($content_id, $commentData, $extraParams);

			if (!$saveCmtInAnnotion)
			{
				return false;
			}
			else
			{
				// Append inserted comment entry id in action log data
				$extraParams['entry_id'] = $saveCmtInAnnotion;

				// Trigger the after save event.
				Factory::getApplication()->triggerEvent('onAfterJlikeCommentSave', array($extraParams, true));

				return $this->sendNotifnAfterCommtSavd(/** @scrutinizer ignore-type */ $saveCmtInAnnotion, $commentData, $extraParams);
			}
		}
	}

	/**
	 * pushToActivityStreamAfterSaveData
	 *
	 * @param   integer  $content_id  content id
	 * @param   string   $annotation  annotation
	 * @param   integer  $privacy     privacy
	 *
	 * @return  void
	 *
	 * @since 1.0
	 */
	public function pushToActivityStreamAfterSaveData($content_id, $annotation, $privacy)
	{
		require_once JPATH_SITE . '/components/com_jlike/helpers/integration.php';
		$db    = Factory::getDBO();
		$params                        = ComponentHelper::getParams('com_jlike');
		$allow_activity_stream_comment = $params->get('allow_activity_stream_comment');

		if ($allow_activity_stream_comment == 1)
		{
			$query = $db->getQuery(true);
			$query->select('*')
				->from($db->quoteName('#__jlike_content'))
				->where($db->quoteName('id') . ' = ' . $db->quote($content_id));
			$db->setQuery($query);
			$content = $db->loadObject();

			$activityObj          = new stdClass;
			$activityObj->comment = $annotation;
			$activityObj->userid  = Factory::getUser()->id;
			$activityObj->element = '';
			$activityObj->url     = $content->url;
			$activityObj->title   = $content->title;
			$activityObj->access  = 0;
			$activityObj->note    = 1;

			if ($privacy)
			{
				$activityObj->access = 40;
			}

			$comjlikeIntegrationHelper = new comjlikeIntegrationHelper;
			$comjlikeIntegrationHelper->pushtoactivitystream($activityObj, 'comment', 0);
		}
	}

	/**
	 * checkAndInsertLikesXref
	 *
	 * @param   mixed    $labelCheck  labelCheck
	 * @param   integer  $content_id  content id
	 *
	 * @return  void
	 *
	 * @since 1.0
	 */
	public function checkAndInsertLikesXref($labelCheck, $content_id)
	{
		$db    = Factory::getDBO();

		foreach ($labelCheck as $list_id)
		{
			$list_id = $db->quote($list_id);

			$query = $db->getQuery(true);
			$query->select('*')
				->from($db->quoteName('#__jlike_likes_lists_xref'))
				->where($db->quoteName('content_id') . ' = ' . $db->quote($content_id))
				->where($db->quoteName('list_id') . ' = ' . $db->quote($list_id));

			$db->setQuery($query);
			$res = $db->loadObject();

			if (!$res)
			{
				$insert_obj             = new stdClass;
				$insert_obj->content_id = $content_id;
				$insert_obj->list_id    = $list_id;
				$db->insertObject('#__jlike_likes_lists_xref', $insert_obj);
			}
		}
	}

	/**
	 * saveDataAnnotations
	 *
	 * @param   array    $data     (string) name of component
	 * @param   integer  $privacy  privacy
	 * @param   array    $res      (string) name of component
	 *
	 * @return  void
	 *
	 * @since 1.0
	 */
	public function saveDataAnnotations($data, $privacy, $res)
	{
		if (isset($data['annotation']))
		{
			$db	= Factory::getDBO();
			$data['annotation'] = strip_tags($data['annotation']);

			if (empty($res))
			{
				$insert_obj             = new stdClass;
				$insert_obj->content_id = $data['content_id'];
				$insert_obj->user_id    = Factory::getUser()->id;
				$insert_obj->annotation = $data['annotation'];
				$insert_obj->privacy    = $privacy;
				$insert_obj->note       = 1;
				$db->insertObject('#__jlike_annotations', $insert_obj);
			}
			else
			{
				$query = $db->getQuery(true);

				$fields = array(
					$db->quoteName('annotation') . ' = ' . $db->quote($data['annotation']),
					$db->quoteName('privacy') . ' = ' . $privacy
				);

				$cond = array(
					$db->quoteName('content_id') . ' = ' . $db->quote($data['content_id']),
					$db->quoteName('user_id') . ' = ' . $db->quote(Factory::getUser()->id),
					$db->quoteName('note') . ' = 1'
				);

				$query->update($db->quoteName('#__jlike_annotations'))->set($fields)->where($cond);
				$db->setQuery($query);
				$db->execute();
			}

			// Activity Stream Integration
			$this->pushToActivityStreamAfterSaveData(/** @scrutinizer ignore-type */ $data['content_id'], $data['annotation'], $privacy);
		}
	}

	/**
	 * validateBeforeCommentingAfterLike
	 *
	 * @param   integer  $content_id  content_id
	 *
	 * @return  boolean
	 *
	 * @since 1.0
	 */
	public function validateBeforeCommentingAfterLike($content_id)
	{
		$db	= Factory::getDBO();
		$query = $db->getQuery(true);
		$query->select($db->quoteName('jl.id'))
			->from($db->quoteName('#__jlike_likes', 'jl'))
			->join('INNER', $db->qn('#__jlike_content', 'jc') . ' ON (' . $db->qn('jc.id') . ' = ' . $db->qn('jl.content_id') . ')')
			->where($db->qn('jl.content_id') . ' = ' . $db->quote($content_id))
			->where($db->qn('jl.userid') . ' = ' . $db->quote(Factory::getUser()->id));

		$db->setQuery($query);

		return $db->loadResult();
	}

	/**
	 * savedata
	 *
	 * @param   array  $data  (string) name of component
	 *
	 * @return  Integer
	 *
	 * @since 1.0
	 */
	public function savedata($data)
	{
		$db	= Factory::getDBO();

		if (empty($data['content_id']))
		{
			$comjlikeHelper = new comjlikeHelper;
			$data['content_id'] = $comjlikeHelper->manageContent($data);
		}

		if ($data['content_id'] == '')
		{
			return 0;
		}

		$data['content_id'] = (int) $data['content_id'];
		$validateBeforeCommentingAfterLike = $this->validateBeforeCommentingAfterLike($data['content_id']);

		$query = $db->getQuery(true);
		$query->select('*')
			->from($db->qn('#__jlike_annotations'))
			->where($db->qn('content_id') . ' = ' . $db->quote($data['content_id']))
			->where($db->qn('user_id') . ' = ' . $db->quote(Factory::getUser()->id))
			->where($db->qn('note') . ' = 1');

		$db->setQuery($query);
		$res = $db->loadObject();

		$privacy = 0;

		if (isset($data['privacy']))
		{
			$privacy = (int) $data['privacy'];
		}

		$this->saveDataAnnotations($data, $privacy, $res);

		if (isset($data['label-check']))
		{
			$this->checkAndInsertLikesXref($data['label-check'], $data['content_id']);
		}

		return 1;
	}

	/**
	 * fetchAndInsertJlikeLikes
	 *
	 * @param   integer  $element_id  post data.
	 *
	 * @since   2.2
	 * @return  integer
	 */
	public function fetchAndInsertJlikeLikes($element_id)
	{
		$db    = Factory::getDBO();
		$query = $db->getQuery(true);
		$query->select($db->qn(array('jl.id','jl.like','jl.dislike')))
			->from($db->quoteName('#__jlike_likes', 'jl'))
			->where($db->quoteName('jl.content_id') . ' = ' . $db->quote($element_id))
			->where($db->quoteName('jl.userid') . ' = ' . $db->quote(Factory::getUser()->id));

		$db->setQuery($query);
		$likeres = $db->loadObject();

		if (!$likeres)
		{
			$insert_obj             = new stdClass;
			$insert_obj->content_id = $element_id;
			$insert_obj->userid     = Factory::getUser()->id;
			$insert_obj->created = Factory::getDate()->toSQL();
			$db->insertObject('#__jlike_likes', $insert_obj);

			return $db->insertid();
		}
		else
		{
			return $likeres->id;
		}
	}

	/**
	 * returnRegisterLikeData
	 *
	 * @param   integer  $element_id  element_id.
	 * @param   array    $data        data.
	 * @param   array    $verbArray   verbArray.
	 * @param   integer  $like_id     like_id.
	 *
	 * @since   2.2
	 * @return  array
	 */
	public function returnRegisterLikeData($element_id, $data, $verbArray, $like_id)
	{
		$db    = Factory::getDBO();
		$query = $db->getQuery(true);
		$query->select($db->qn(array('jc.id','jc.like_cnt','jc.dislike_cnt')))
			->from($db->quoteName('#__jlike_content', 'jc'))
			->where($db->quoteName('jc.id') . ' = ' . $db->quote($element_id));

		$db->setQuery($query);
		$res             = $db->loadObject();
		$res->element_id = $data['element_id'];
		$res->like_id    = $like_id;
		$res->link       = $data['url'];
		$res->title      = $data['title'];
		$res->timestamp  = $verbArray['timestamp'];
		$res->method     = $data['method'];
		$res->element    = $data['element'];
		$res->userid     = Factory::getUser()->id;
		$res->username   = Factory::getUser()->username;
		$res->verb       = $verbArray['like_uobjverb'];

		$extraParams  = $data['extraParams'];
		$plg_name = $plg_type = '';

		if (!empty($extraParams))
		{
			$plg_type = $extraParams['plg_type'];
			$plg_name = $extraParams['plg_name'];
		}

		// Activity Stream Integration with cb,JS,jomwall
		$comjlikeIntegrationHelper = new comjlikeIntegrationHelper;

		$jlikemainhelperObj        = new ComjlikeMainHelper;
		$params                    = $jlikemainhelperObj->getjLikeParams($plg_type, $plg_name);
		$allow_activity_stream     = $params->get('allow_activity_stream');

		$integration = $jlikemainhelperObj->getSocialIntegration($plg_type, $plg_name);

		if ($allow_activity_stream == 1)
		{
			$comjlikeIntegrationHelper->pushtoactivitystream($res, 'like', 0, $integration);
		}

		/* Activity Stream Integration with cb,JS,jomwall
		 * @pamans message, contnet id, element, url, title, plg_name, parent_id (for threaded comment)
		 * $comment, $cnt_id, $element, $url,      $title,  $plg_name, $parent_id, $plg_type, $for = '' */

		$sendLikeDislikeNotification = false;


		$notification_msg = $verbArray['like_uobjverb'] . ' ' . $data['title'];

		// Add content title to extraparams array
		$extraParams['title'] = $data['title'];

		// Get content owner
		$getOwner = Factory::getApplication()->triggerEvent('onAfterGet' . $extraParams['plg_name'] . 'OwnerDetails', array($data['element_id']));

		switch ($data['method'])
		{
			case 'like':

				if ($params->get('allow_like_notification'))
				{
					$sendLikeDislikeNotification = true;

					// Get email content from respective plugin
					$this->setNotificationDetails('LikesNotificationDetails', array($extraParams, $data),
							$extraParams, $getOwner[0], 'jlike.like');
				}
				break;

			case 'dislike':

				if ($params->get('allow_dislike_notification'))
				{
					$sendLikeDislikeNotification = true;

					// Get email content from respective plugin
					$this->setNotificationDetails('DislikesNotificationDetails', array($extraParams, $data),
						$extraParams, $getOwner[0], 'jlike.dislike');
				}
				break;
		}

		if ($sendLikeDislikeNotification)
		{
			$this->notification(
				$verbArray['like_uobjverb'], $data['element_id'], $data['element'], $data['url'],
				$res->title, $plg_name, 0, $plg_type, $notification_msg, $extraParams
			);
		}

		// $comment, $element_id, $element, $url, $title, $plg_name, $parent_id,$plg_type
		// Jomsociallike Integration

		PluginHelper::importPlugin('system');
		Factory::getApplication()->triggerEvent('onAfterregisterlike', array($res));

		return $res;
	}
}
