<?php
/**
 * @package     JLike
 * @subpackage  com_jlike
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Plugin\PluginHelper;

// Load the inventory model to get the Item details
require_once __DIR__ . '/jlike_likeshelper.php';


/**
 * JlikeModelAnnotations
 *
 * @package     Jlike
 * @subpackage  site
 * @since       2.2
 */
class JlikeModeljlike_Likes extends JlikeModeljlike_Likeshelper
{
	protected $jlikehelperObj;

	protected $jlikemainhelperObj;

	/**
	 * construct
	 *
	 * @since	1.6
	 */
	public function __construct()
	{
		parent::__construct();
		$helperPath = JPATH_SITE . '/components/com_jlike/helper.php';

		if (!class_exists('comjlikeHelper'))
		{
			// Require_once $path;
			if (file_exists($helperPath)) {
				require_once $helperPath;
			}
		}

		$this->jlikehelperObj = new comjlikeHelper;

		$helperPath = JPATH_SITE . '/components/com_jlike/helpers/main.php';

		if (!class_exists('ComjlikeMainHelper'))
		{
			// Require_once $path;
			if (file_exists($helperPath)) {
				require_once $helperPath;
			}
		}

		$this->jlikemainhelperObj = new ComjlikeMainHelper;
	}

	/**
	 * getUserlabels.
	 *
	 * @param   integer  $contentId  id.s
	 *
	 * @return array
	 *
	 * @since 3.0
	 */
	public function getUserlabels($contentId = 0)
	{
		$db     = Factory::getDBO();
		$userId = Factory::getUser()->id;

		$sql = $db->getQuery(true);
		$sql->select($db->qn(array('id','title')));
		$sql->from($db->qn('#__jlike_like_lists'));
		$sql->where($db->qn('user_id') . ' = ' . $db->q($userId));

		$db->setQuery($sql);
		$res = $db->loadObjectList();

		if ($contentId > 0)
		{
			$list_ids = array();

			foreach ($res as $list)
			{
				$list_ids[] = $list->id;
			}

			if (!empty($list_ids))
			{
				$list_ids = implode(',', $list_ids);
				$sql = $db->getQuery(true);
				$sql->select($db->qn('list_id'));
				$sql->from($db->qn('#__jlike_likes_lists_xref'));
				$sql->where($db->qn('content_id') . ' = ' . $db->q($contentId));
				$sql->where($db->qn('list_id') . ' IN (' . $list_ids . ')');
				$db->setQuery($sql);
				$content_lists = $db->loadColumn();

				foreach ($res as $ind => $listobj)
				{
					$res[$ind]->checked = '';

					if (in_array($listobj->id, $content_lists))
					{
						$res[$ind]->checked = 'checked';
					}
				}
			}
		}

		return $res;
	}

	/**
	 * getbttonset.
	 *
	 * @param   integer  $bid  id.
	 *
	 * @return array
	 *
	 * @since 3.0
	 */
	public function getbttonset($bid)
	{
		$db  = Factory::getDBO();
		$sql = $db->getQuery(true);
		$sql->select('*');
		$sql->from($db->qn('#__jlike'));
		$sql->where($db->qn('id') . ' = ' . $db->q($bid));
		$db->setQuery($sql);
		$item = $db->loadObject();

		return $item;
	}

	/**
	 * getbttonset.
	 *
	 * @param   integer  $cont_id       id.
	 * @param   integer  $element       element.
	 * @param   integer  $pwltcb_check  pwltcb_check.
	 * @param   array    $extraParams   Additional parameters
	 *
	 * @return array
	 *
	 * @since 3.0
	 */
	public function getData($cont_id, $element, $pwltcb_check, $extraParams = array())
	{
		$db                 = Factory::getDBO();
		$userid             = Factory::getUser()->id;
		$data               = $pwltcb = array();
		$data['likeaction'] = $data['dislikeaction'] = '';
		$data['likecount']  = $data['dislikecount'] = 0;

		$query = $db->getQuery(true);

		$query->select($db->qn(array('jl.id','jl.like','jl.dislike')));
		$query->from($db->qn('#__jlike_likes', 'jl'));
		$query->from($db->qn('#__jlike_content', 'jc'));
		$query->where($db->qn('jc.element_id') . ' = ' . $db->q((int) $cont_id));
		$query->where($db->qn('jc.element') . ' = ' . $db->q($element));
		$query->where($db->qn('jl.userid') . ' = ' . $db->q((int) $userid));
		$query->where($db->qn('jl.content_id') . ' = ' . $db->qn('jc.id'));
		$db->setQuery($query);
		$res = $db->loadObject();

		if ($res)
		{
			if ($res->like)
			{
				$data['likeaction']    = 'unlike';
				$data['dislikeaction'] = 'dislike';
			}
			elseif ($res->dislike)
			{
				$data['dislikeaction'] = 'undislike';
				$data['likeaction']    = 'like';
			}
			else
			{
				$data['likeaction']    = 'like';
				$data['dislikeaction'] = 'dislike';
			}
		}
		else
		{
			$data['likeaction']    = 'like';
			$data['dislikeaction'] = 'dislike';
		}

		$query = $db->getQuery(true);
		$query->select($db->qn(array('jc.id', 'jc.like_cnt', 'jc.dislike_cnt')));
		$query->from($db->qn('#__jlike_content', 'jc'));
		$query->where($db->qn('jc.element_id') . ' = ' . $db->q((int) $cont_id));
		$query->where($db->qn('jc.element') . ' = ' . $db->q($element));

		$db->setQuery($query);
		$res = $db->loadObject();

		if (!empty($res))
		{
			$data['likecount']		= (!empty($res->like_cnt)) ? $res->like_cnt: 0;
			$data['dislikecount']		= (!empty($res->dislike_cnt)) ?$res->dislike_cnt: 0;
			$data['content_id']		= $res->id;

			if ($pwltcb_check == 1)
			{
				$pwltcb               = $this->jlikehelperObj->getPeoplelikedthisContentBefor($res->id, $extraParams);
			}
		}

		$data['pwltcb']        = $pwltcb;
		$data['liketext']      = Text::_('LIKE');
		$data['unliketext']    = Text::_('UNLIKE');
		$data['disliketext']   = Text::_('DISLIKE');
		$data['undisliketext'] = Text::_('UNDISLIKE');

		return $data;
	}

	/**
	 * getUserRatingAvg.
	 *
	 * @param   object  $result  result obj.
	 *
	 * @return object
	 *
	 * @since 3.0
	 */
	public function getUserRatingAvg($result)
	{
		$params        = $this->jlikemainhelperObj->getjLikeParams();
		$rating_length = $params->get('rating_length');

		foreach ($result as $row)
		{
			$userRating     = $row->user_rating;
			$ratingUpto     = $row->rating_upto;
			$avarageRatingA = $rating_length * $userRating;

			$row->user_rating = $avarageRatingA / $ratingUpto;
		}

		return $result;
	}

	/**
	 * Method to get product rating avarage
	 *
	 * @param   integer  $element_id  id.
	 * @param   integer  $element     element.
	 *
	 * @return integer
	 *
	 * @since 3.0
	 */
	public function getProductRatingAvg($element_id, $element)
	{
		$params        = $this->jlikemainhelperObj->getjLikeParams();
		$rating_length = $params->get('rating_length');
		$db            = Factory::getDBO();
		$query = $db->getQuery(true);
		$query->select($db->qn(array('jc.element_id', 'ant.annotation', 'ant.user_id')));
		$query->select($db->qn(array('ant.parent_id', 'ant.annotation_date', 'u.name', 'u.email', 'jr.rating_upto', 'jr.user_rating')));
		$query->select($db->qn(array('jc.id', 'ant.id', 'ant.annotation'), array('contentid', 'annotation_id', 'smileyannotation')));
		$query->from($db->qn('#__jlike_content', 'jc'));
		$query->join('INNER', $db->qn('#__jlike_annotations', 'ant') . ' ON (' . $db->qn('jc.id') . ' = ' . $db->qn('ant.content_id') . ')');
		$query->join('INNER', $db->qn('#__jlike_rating', 'jr') . ' ON (' . $db->qn('jc.id') . ' = ' . $db->qn('jr.content_id') . ' AND '
		. $db->qn('jr.user_id') . ' = ' . $db->qn('ant.user_id') . ')');
		$query->join('INNER', $db->qn('#__users', 'u') . ' ON (' . $db->qn('u.id') . ' = ' . $db->qn('ant.user_id') . ')');
		$query->where($db->qn('jc.element_id') . ' = ' . $db->q((int) $element_id));
		$query->where($db->qn('jc.element') . ' = ' . $db->q($element));
		$query->where($db->qn('ant.state') . ' = 1');
		$query->where($db->qn('ant.note') . ' = 2');

		$db->setQuery($query);
		$result = $db->loadObjectList();

		$i     = 0;
		$count = 0;

		foreach ($result as $row)
		{
			$avarageRatingA   = $rating_length * $row->user_rating;
			$row->user_rating = $avarageRatingA / $row->rating_upto;
			$i                = $i + $row->user_rating;
			$count++;
		}

		if ($count != 0)
		{
			$avg = $i / $count;
		}
		else
		{
			$avg = 0;
		}

		return $avg;
	}

	/**
	 * Get the number of reply against comment
	 *
	 * @param   object  $result  result.
	 *
	 * @return object
	 *
	 * @since 3.0
	 */
	public function getReplyAgainstComment($result)
	{
		foreach ($result as $ind => $row)
		{
			$db = Factory::getDBO();
			$query = $db->getQuery(true);
			$query->select($db->qn('id'));
			$query->from($db->qn('#__jlike_annotations'));
			$query->where($db->qn('parent_id') . ' = ' . $db->q((int) $row->annotation_id));
			$query->group('id');

			$db->setQuery($query);
			$result[$ind]->children = $db->loadColumn();
			$row->replycount        = count($result[$ind]->children);
		}

		return $result;
	}

	/**
	 * getReviewsCount.
	 *
	 * @param   integer  $cont_id    result.
	 * @param   string   $element    element.
	 * @param   integer  $note_type  Type of note.
	 * @param   integer  $loginuser  User.
	 *
	 * @return array<mixed,mixed|integer>
	 *
	 * @since 3.0
	 */
	public function getReviewsCount($cont_id, $element, $note_type = '', $loginuser = '')
	{
		$db            = Factory::getDBO();
		$query = $db->getQuery(true);

		if ($element == 'com_content.category')
		{
			$element = 'com_content.article';
		}

		if ($loginuser == 'loginuser')
		{
			$query->where($db->qn('ant.user_id') . ' = ' . $db->q(Factory::getUser()->id));
		}

		$comment_count = array();

		$query->select('count(ant.id) as comment_count');
		$query->from($db->qn('#__jlike_content', 'jc'));
		$query->join('INNER', $db->qn('#__jlike_annotations', 'ant') . ' ON (' . $db->qn('jc.id') . ' = ' . $db->qn('ant.content_id') . ')');
		$query->join('INNER', $db->qn('#__users', 'u') . ' ON (' . $db->qn('u.id') . ' = ' . $db->qn('ant.user_id') . ')');
		$query->where($db->qn('jc.element_id') . ' = ' . $db->q((int) $cont_id));
		$query->where($db->qn('jc.element') . ' = ' . $db->q($element));
		$query->where($db->qn('ant.annotation') . ' <> ""');
		$query->where($db->qn('ant.parent_id') . ' =  0 ');
		$query->where($db->qn('ant.state') . ' = 1');
		$query->where($db->qn('ant.note') . ' = ' . $db->q($note_type));

		$db->setQuery($query);
		$comment_count[0] = $result = $db->loadResult();

		$params        = $this->jlikemainhelperObj->getjLikeParams();
		$comment_limit = $params->get('no_of_commets_to_show');

		if ($comment_limit)
		{
			if ($comment_limit < $result)
			{
				$result           = $result - $comment_limit;
				$comment_count[1] = $result;

				return $comment_count;
			}
		}

		return $comment_count[0];
	}

	/**
	 * getCommentsCount.
	 *
	 * @param   integer  $cont_id    result.
	 * @param   string   $element    element.
	 * @param   integer  $note_type  Type of note.
	 * @param   integer  $exParam    Params set in plugin
	 *
	 * @return array<mixed,mixed|integer>
	 *
	 * @since 3.0
	 */
	public function getCommentsCount($cont_id, $element, $note_type = '', $exParam = array())
	{
		$db = Factory::getDBO();
		$query = $db->getQuery(true);

		if ($element == 'com_content.category')
		{
			$element = 'com_content.article';
		}

		// If comment type is passed

		if (isset($exParam['type']) && $exParam['type'] !== '' && $exParam['type'])
		{
			$query->where($db->qn('ant.type') . ' = ' . $db->q($exParam['type']));
		}

		$comment_count = array();

		$query->select('count(ant.id) as comment_count');
		$query->from($db->qn('#__jlike_content', 'jc'));
		$query->join('LEFT', $db->qn('#__jlike_annotations', 'ant') . ' ON (' . $db->qn('jc.id') . ' = ' . $db->qn('ant.content_id') . ')');
		$query->join('LEFT', $db->qn('#__users', 'u') . ' ON (' . $db->qn('u.id') . ' = ' . $db->qn('ant.user_id') . ')');
		$query->where($db->qn('jc.element_id') . ' = ' . $db->q((int) $cont_id));
		$query->where($db->qn('jc.element') . ' = ' . $db->q($element));
		$query->where($db->qn('ant.annotation') . ' <> ""');
		$query->where($db->qn('ant.parent_id') . ' =  0 ');
		$query->where($db->qn('ant.state') . ' = 1');
		$query->where($db->qn('ant.note') . ' = ' . $db->q($note_type));

		$db->setQuery($query);
		$comment_count[0] = $result = $db->loadResult();

		$params        = $this->jlikemainhelperObj->getjLikeParams();
		$comment_limit = $params->get('no_of_commets_to_show');

		if ($comment_limit)
		{
			if ($comment_limit < $result)
			{
				$result           = $result - $comment_limit;
				$comment_count[1] = $result;

				return $comment_count;
			}
		}

		return $comment_count[0];
	}

	/**
	 * getUserInfo.
	 *
	 * @param   object  $data  userdata.
	 *
	 * @return object
	 *
	 * @since 3.0
	 */
	public function getUserInfo($data)
	{
		$socialintegrationHelper = new socialintegrationHelper;

		foreach ($data as $row)
		{
			$user                  = new stdClass;
			$user->id              = $row->user_id;
			$user->email           = $row->email;
			$row->user_profile_url = $socialintegrationHelper->getUserProfileUrl($row->user_id);
			$row->avtar            = $socialintegrationHelper->getUserAvatar($user);
		}

		return $data;
	}

	/**
	 * Get the Content Entry Id
	 *
	 * @param   Array  $extraParams  Contain element, cont_id, url, title
	 *
	 * @return  Jlike Content table entry Id
	 */
	public function getConentId($extraParams)
	{
		$db = Factory::getDBO();
		$query = $db->getQuery(true);
		$element = $extraParams['element'];
		$cont_id = $extraParams['cont_id'];
		$url     = $extraParams['url'];
		$title   = $extraParams['title'];

		$query->select($db->qn(array('jc.id','jc.like_cnt','jc.dislike_cnt')));
		$query->from($db->qn('#__jlike_content', 'jc'));
		$query->where($db->qn('jc.element_id') . ' = ' . $db->q((int) $cont_id));
		$query->where($db->qn('jc.element') . ' = ' . $db->q($element));
		$db->setQuery($query);
		$contentres = $db->loadObject();

		if (!$contentres)
		{
			$insert_obj             = new stdClass;
			$insert_obj->element_id = $cont_id;
			$insert_obj->element    = $element;
			$insert_obj->url        = $url;
			$insert_obj->title      = $title;

			$db->insertObject('#__jlike_content', $insert_obj);
			$content_id = $db->insertid();
		}
		else
		{
			$content_id = $contentres->id;
		}

		return $content_id;
	}

	/**
	 * deleteRating.
	 *
	 * @param   integer  $annotation_id  content element id
	 *
	 * @since   2.2
	 *
	 * @return  void
	 */
	public function deleteRating($annotation_id)
	{
		$db    = Factory::getDBO();
		$query = $db->getQuery(true);
		$query->select($db->qn('content_id'));
		$query->from($db->qn('#__jlike_annotations'));
		$query->where($db->qn('id') . ' = ' . $db->q((int) $annotation_id));
		$db->setQuery($query);
		$content_id = $db->loadResult();

		if ($content_id)
		{
			$query = "DELETE FROM #__jlike_rating
					WHERE user_id=" . Factory::getUser()->id . "
					AND content_id= " . $content_id;
			$db->setQuery($query);

			if (!$db->execute($query))
			{
				return $db->stderr();
			}
			else
			{
				return;
			}
		}

		return;
	}

	/**
	 * getChildren.
	 *
	 * @param   integer  $annotation_id  annotation_id.
	 *
	 * @return array
	 *
	 * @since 3.0
	 */
	public function getChildren($annotation_id)
	{
		$arrChildren = array();

		$db    = Factory::getDBO();
		$query = $db->getQuery(true);
		$query->select($db->qn('id'));
		$query->from($db->qn('#__jlike_annotations'));
		$query->where($db->qn('parent_id') . ' = ' . $db->q((int) $annotation_id));
		$db->setQuery($query);
		$result = $db->loadColumn();

		if ($result)
		{
			foreach ($result as $row)
			{
				$arrChildren[] = $row;
			}

			return $arrChildren;
		}
	}

	/**
	 * getLikeDislikeCount.
	 *
	 * @param   object  $result  result obj.
	 *
	 * @return object
	 *
	 * @since 3.0
	 */
	public function getLikeDislikeCount($result)
	{
		$db     = Factory::getDBO();
		$userId = Factory::getUser()->id;

		foreach ($result as $row)
		{
			// Get the like count against comment
			$query = $db->getQuery(true);
			$query->select('count(id) as likecount');
			$query->from($db->qn('#__jlike_likes'));
			$query->where($db->qn('annotation_id') . ' = ' . $db->q($row->annotation_id));
			$query->where($db->qn('like') . ' = 1');
			$query->group('annotation_id');
			$db->setQuery($query);
			$likecount      = $db->loadResult();
			$row->likeCount = $likecount;

			// Get the dislike count against comment
			$query = $db->getQuery(true);
			$query->select('count(id) as dislikecount');
			$query->from($db->qn('#__jlike_likes'));
			$query->where($db->qn('annotation_id') . ' = ' . $db->q($row->annotation_id));
			$query->where($db->qn('dislike') . ' = 1');
			$query->group('annotation_id');
			$db->setQuery($query);
			$dislikecount      = $db->loadResult();
			$row->dislikeCount = $dislikecount;

			// Check that the current user like or dislike this comment
			$query = $db->getQuery(true);
			$query->select($db->qn(array('like','dislike')));
			$query->from($db->qn('#__jlike_likes'));
			$query->where($db->qn('annotation_id') . ' = ' . $db->q($row->annotation_id));
			$query->where($db->qn('userid') . ' = ' . $db->q($userId));

			$db->setQuery($query);
			$data = $db->loadObject();

			if (!empty($data))
			{
				if ($data->like)
				{
					// User like on comment
					$row->userLikeDislike = 1;
				}
				elseif ($data->dislike)
				{
					// User dislike on comment
					$row->userLikeDislike = 2;
				}
			}
			else
			{
				$row->userLikeDislike = 0;
			}
		}

		return $result;
	}

	/**
	 * commentDateTime.
	 *
	 * @param   object  $result  result obj.
	 *
	 * @return object
	 *
	 * @since 3.0
	 */
	public function commentDateTime($result)
	{
		foreach ($result as $row)
		{
			$time = HTMLHelper::date($row->annotation_date, Text::_('COM_JLIKE_COMMENT_TIME_FORMAT'), true);

			$row->date = HTMLHelper::date($row->annotation_date, Text::_('COM_JLIKE_COMMENT_DATE_FORMAT'));
			$row->time = Text::_('COM_JLIKE_COMMENT_DATE_TIME_SEPERATOR') . $time;
		}

		return $result;
	}

	/**
	 * Get Note saved by user against lesson.
	 *
	 * @param   STRING   $element     result obj.
	 * @param   INTEGER  $element_id  result obj.
	 * @param   INTEGER  $user_id     result obj.
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 */
	public function geUserNote($element, $element_id, $user_id)
	{
		$db    = Factory::getDBO();
		$query = $db->getQuery(true);
		$query->select($db->qn('jc.id'));
		$query->from($db->qn('#__jlike_content', 'jc'));
		$query->where($db->qn('jc.element') . ' = ' . $db->q($element));
		$query->where($db->qn('jc.element_id') . ' = ' . $db->q((int) $element_id));

		$db->setQuery($query);
		$content_id = $db->loadResult();

		$note = '';

		if ($content_id)
		{
			$db    = Factory::getDBO();
			$query = $db->getQuery(true);
			$query->select($db->qn('annotation'));
			$query->from($db->qn('#__jlike_annotations'));
			$query->where($db->qn('content_id') . ' = ' . $db->q((int) $content_id));
			$query->where($db->qn('note') . ' = 1');
			$query->where($db->qn('user_id') . '=' . $db->q((int) $user_id));
			$db->setQuery($query);
			$note = $db->loadResult();
		}

		return $note;
	}

	/**
	 * Get the users list
	 *
	 * @param   Array  $urldata  Should contain must cont_id
	 * @param   Array  $exParam  Must contain plg_type and plg_name
	 *
	 * @return  $users
	 */
	public static function getUsersList($urldata, $exParam = array())
	{
		$plg_type = $exParam['plg_type'];
		$plg_name = $exParam['plg_name'];

		PluginHelper::importPlugin($plg_type, $plg_name);

		$users = Factory::getApplication()->triggerEvent('onAfter' . $plg_name . 'GetUsersList', array($urldata, $exParam));

		return empty($users[0]) ? '' : $users[0];
	}
}
