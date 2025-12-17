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
defined('_JEXEC') or die;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;

// Load the inventory model to get the Item details
require_once __DIR__ . '/jlike_likes.php';

/**
 * Jlike Model comment
 *
 * @package     Jlike
 * @subpackage  site
 * @since       2.2
 */
class JlikeModelComment extends BaseDatabaseModel
{
	protected $ComjlikeHelper;

	protected $comjlikeMainHelper;

	/**
	 * construct
	 *
	 * @since	1.6
	 */
	public function __construct()
	{
		parent::__construct();

		$helperPath = JPATH_SITE . '/components/com_jlike/helper.php';

		if (!class_exists('ComjlikeHelper'))
		{
			// Require_once $path;
			JLoader::register('ComjlikeHelper', $helperPath);
			JLoader::load('ComjlikeHelper');
		}

		$this->ComjlikeHelper = new ComjlikeHelper;

		$helperPath = JPATH_SITE . '/components/com_jlike/helpers/main.php';

		if (!class_exists('ComjlikeMainHelper'))
		{
			// Require_once $path;
			JLoader::register('ComjlikeMainHelper', $helperPath);
			JLoader::load('ComjlikeMainHelper');
		}

		$this->comjlikeMainHelper = new ComjlikeMainHelper;
	}

	/**
	 * SaveComment.
	 *
	 * @param   Array  $commentData  Comments data options
	 * @param   Array  $extraParams  Extra params
	 *
	 * @return array
	 *
	 * @since 3.0
	 */
	public function SaveComment($commentData, $extraParams)
	{
		$db          = Factory::getDBO();
		$update_obj  = new stdClass;
		$resultArray = array();

		$comment = $commentData['comment'];

		if ($commentData['annotation_id'])
		{
			$update_obj->id = $commentData['annotation_id'];
		}
		else
		{
			$JlikeModeljlike_Likes = new JlikeModeljlike_Likes;
			$content_id = $JlikeModeljlike_Likes->getConentId($extraParams);
			$update_obj->content_id = $content_id;
		}

		$update_obj->user_id         = Factory::getUser()->id;
		$update_obj->state           = 1;
		$update_obj->privacy         = 0;
		$update_obj->annotation      = $comment;
		$update_obj->images      = $commentData['images'];

		if ($commentData['parent_id'] != 0 || $commentData['parent_id'] != '')
		{
			$update_obj->parent_id = $commentData['parent_id'];
		}

		if (!empty($extraParams['type']))
		{
			$update_obj->type = $extraParams['type'];
		}

		$update_obj->note = $commentData['note_type'];

		// Check if comment type is Review
		if ($update_obj->note == '2')
		{
			$checkCommentHasParent = $this->checkCommentHasParent($update_obj->id);

			if (!empty($checkCommentHasParent))
			{
				$update_obj->note = 0;
			}
		}

		$update_obj->annotation_date = Factory::getDate()->toSQL();

		try
		{
			if (!empty($update_obj->id))
			{
				$db->updateObject('#__jlike_annotations', $update_obj, 'id', true);
				$resultArray['annotation_id'] = $update_obj->id;
			}
			else
			{
				$db->insertObject('#__jlike_annotations', $update_obj);
				$resultArray['annotation_id'] = $db->insertid();
			}
		}
		catch (Exception $e)
		{
			$resultArray['error'] = $e->getMessage();
		}

		// Parse the Comment
		$parsedMention                = self::parsedMention($comment, $extraParams);
		$parsedComment                = self::replaceSmileyAsImage($parsedMention, $extraParams);
		$resultArray['comment']       = $parsedComment;
		$commentData['parsedComment'] = $parsedComment;

		if ($resultArray['annotation_id'])
		{
			// Append inserted comment entry id in action log data
			$extraParams['entry_id'] = $resultArray['annotation_id'];

			// Trigger the after save event.
			Factory::getApplication()->triggerEvent('onAfterJlikeCommentSave', array($extraParams, false));
		}

		self::sendNotification($commentData, $extraParams);

		return $resultArray;
	}

	/**
	 * checkCommentHasParent
	 *
	 * @param   Integer  $annotation_id  annotation_id
	 *
	 * @return  boolean  true/false
	 *
	 * @since  1.7.5
	 */
	public function checkCommentHasParent($annotation_id)
	{
		$db = Factory::getDBO();
		$query = $db->getQuery(true);
		$query->select('a.parent_id');
		$query->from('#__jlike_annotations AS a');
		$query->where('a.id = ' . $db->quote($annotation_id));
		$query->where('a.parent_id <> 0');

		$db->setQuery($query);

		return $db->loadAssoc();
	}

	/**
	 * Notification after comment
	 *
	 * @param   Array  $commentData  Comments data options
	 * @param   Array  $extraParams  Extra params
	 *
	 * @return  boolean  true/false
	 *
	 * @since  1.7.5
	 */
	public static function sendNotification($commentData, $extraParams)
	{
		$plg_type = !empty($extraParams['plg_type']) ? $extraParams['plg_type'] : '';
		$plg_name = !empty($extraParams['plg_name']) ? $extraParams['plg_name'] : '';

		// Get integration
		$socialIntegration = ComjlikeMainHelper::getSocialIntegration($plg_type, $plg_name);

		$actorId   = Factory::getUser()->id;
		$actorName = Factory::getUser($actorId)->name;

		// 1.-- Notify to mentions ---
		// Get the list of users who are mentioned in comment
		$mentioned_info  = self::getMentionsInfo($commentData['comment']);
		$mentioned_users = $mentioned_info[2];

		if (isset($mentioned_users))
		{
			// @param  mentioned users list (array), comment
			$commentData['notification_msg'] = str_replace("{actorName}", $actorName, Text::_("COM_JLIKE_MENTION_NOTIFICATION_MSG"));

			foreach ($mentioned_users as $key => $target_id)
			{
				self::notify($actorId, $target_id, $socialIntegration, $commentData, $extraParams);
			}
		}

		// 2.-- @ToDo Notify to comment owner that comment added on your conten ---
		// JlikeModelComment::notify($actorId, $target_id, $socialIntegration, $commentData, $extraParams);

		// 3. --@ToDo push Activity stream
	}

	/**
	 * Get the list of users who are mentioned in comment
	 *
	 * @param   String  $actorId            Comment to be parsed to get the list of mentioned users
	 * @param   String  $target_id          Comment to be parsed to get the list of mentioned users
	 * @param   String  $socialIntegration  Comment to be parsed to get the list of mentioned users
	 * @param   String  $commentData        Comment to be parsed to get the list of mentioned users
	 * @param   String  $extraParams        Comment to be parsed to get the list of mentioned users
	 *
	 * @return  boolean  true/false
	 *
	 * @since  1.7.5
	 */
	public static function notify($actorId, $target_id, $socialIntegration, $commentData, $extraParams)
	{
		// Create object of social library
		$jSocialObj = ComjlikeMainHelper::getSocialLibraryObject($socialIntegration, $extraParams);

		// Send notification
		switch ($socialIntegration)
		{
			case 'joomla':
				break;

			case 'easysocial':
				// Internal notification options
				$systemOptions = array(
					'uid' => $extraParams["uniqueElementId"],
					'actor_id' => $actorId,
					'target_id' => $target_id,
					'title' => strip_tags($commentData),
					'image' => "",
					'cmd' => $extraParams["command"],
					'url' => $extraParams["url"]
				);

				$sender   = Factory::getUser($actorId);
				$receiver = Factory::getUser($target_id);

				// Notify content creator about review via mail
				$emailOptions   = array(
					'title'             => $extraParams["mail_subject"],
					'template'          => $extraParams["template"],
					'receiver'          => $receiver->name,
					'actor'             => $sender->name,

					'url'               => Uri::root() . substr(Route::_($extraParams["url"], false), strlen(Uri::base(true)) + 1),
					'actionDescription' => $commentData
				);

				$msgid = $jSocialObj->sendNotification($sender, $receiver, $commentData, $systemOptions, $emailOptions);

				break;

			case 'jomsocial':
			case 'js':

				break;

			case 'cb':

				break;

			case 'jomwall':

				break;

			case 'easyprofile':
				break;
		}

		return true;
	}

	/**
	 * Get the list of users who are mentioned in comment
	 *
	 * @param   String  $comment  Comment to be parsed to get the list of mentioned users
	 *
	 * @return  Array   Matches array contain 0=> Mentioned tag  1=> Mentioned user name 3=> Mentioned user Id
	 * (
	 * [0] => Array
	 * (
	 *  [0] => @[Madhuchandra R](80)
	 *  [1] => @[Ashwin K Date](86)
	 *  )
	 *  [1] => Array
	 *  (
	 *  [0] => Madhuchandra R
	 *  [1] => Ashwin K Date
	 *  )
	 *  [2] => Array
	 *  (
	 *  [0] => 80
	 *  [1] => 86
	 *  )
	 *  )
	 *
	 * @since  1.7.5
	 */
	public static function getMentionsInfo($comment = '')
	{
		if (!empty($comment))
		{
			preg_match_all('/@\[([^\]]+)\]\(([^ \)]+)\)/', $comment, $matches);

			return $matches;
		}
	}

	/**
	 * Replace smiley text to smiley image
	 *
	 * @param   String  $comment  Comment to parse
	 *
	 * @return  string Smiley parsed comment
	 *
	 * @since 1.0
	 */
	public function replaceSmileyAsImage($comment)
	{
		$replacements = array(
			":)" => "smile.jpg",
			":-)" => "smile.jpg",
			":(" => "sad.jpg",
			":-(" => "sad.jpg",
			";)" => "wink.jpg",
			";-)" => "wink.jpg",
			";(" => "cry.jpg",
			"B-)" => "cool.jpg",
			"B)" => "cool.jpg",
			":D" => "grin.jpg",
			":-D" => "grin.jpg",
			":o" => "shocked.jpg",
			":0" => "shocked.jpg",
			":-o" => "shocked.jpg",
			":-0" => "shocked.jpg",
			":-3" => "love.png"
		);

		$smileyimgPath = Uri::root(true) . '/components/com_jlike/assets/images/smileys';

		foreach ($replacements as $code => $image)
		{
			$html    = '<img src="' . $smileyimgPath . '/' . $image . '" alt="' . $code . '"/>';
			$comment = str_replace($code, $html, $comment);
		}

		return $comment;
	}

	/**
	 * Parsed Mentions with Avatar
	 *
	 * @param   String  $comment  Comment to parse
	 * @param   Array   $exParam  Should Contain plg_type, plg_name
	 *
	 * @return  String  Comment after parsing mentions
	 *
	 * @since 1.2.2
	 */
	public static function parsedMention($comment, $exParam)
	{
		$helperPath = JPATH_SITE . '/components/com_jlike/helpers/main.php';

		if (!class_exists('ComjlikeMainHelper'))
		{
			// Require_once $path;
			JLoader::register('ComjlikeMainHelper', $helperPath);
			JLoader::load('ComjlikeMainHelper');
		}

		$ComjlikeMainHelper = new ComjlikeMainHelper;
		$plgData            = array(
			"plg_type" => $exParam['plg_type'],
			"plg_name" => $exParam['plg_name']
		);
		$sLibObj            = $ComjlikeMainHelper->getSocialLibraryObject('', $plgData);

		preg_match_all('/@\[([^\]]+)\]\(([^ \)]+)\)/', $comment, $matches);
		$mentioned_users = $matches[2];

		/** Matches array contain 0=> Mentioned tag  1=> Mentioned user name 3=> Mentioned user Id
		Array
		(
		[0] => Array
		(
		[0] => @[Madhuchandra R](80)
		[1] => @[Ashwin K Date](86)
		)

		[1] => Array
		(
		[0] => Madhuchandra R
		[1] => Ashwin K Date
		)

		[2] => Array
		(
		[0] => 80
		[2] => 86
		)
		)*/

		// Replace the mentioned tag with user name & link to user profile
		foreach ($mentioned_users as $key => $ment_usrId)
		{
			$ment_usr = Factory::getUser($ment_usrId);

			$link = '';
			$link = $profileUrl = $sLibObj->getProfileUrl($ment_usr);

			if ($profileUrl)
			{
				if (!parse_url($profileUrl, PHP_URL_HOST))
				{
					$link = Uri::root() . substr(Route::_($sLibObj->getProfileUrl($ment_usr)), strlen(Uri::base(true)) + 1);
				}
			}

			// Profile link html $matches[1][$key]=> Mentioned User Name
			$profile_link_html = '<a href="' . $link . '" target="_blank">' . $matches[1][$key] . '</a>';

			// Replace the mentioned user tag with profile link
			$comment = str_replace($matches[0][$key], $profile_link_html, $comment);
		}

		return $comment;
	}
}
