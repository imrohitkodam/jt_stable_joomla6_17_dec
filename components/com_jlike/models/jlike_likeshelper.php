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
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Log\Log;

// Load the inventory model to get the Item details
require_once __DIR__ . '/comment.php';


/**
 * JlikeModelAnnotations
 *
 * @package     Jlike
 * @subpackage  site
 * @since       2.2
 */
class JlikeModeljlike_Likeshelper extends JlikeModelComment
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
			JLoader::register('ComjlikeMainHelper', $helperPath);
			JLoader::load('ComjlikeMainHelper');
		}

		$this->jlikemainhelperObj = new ComjlikeMainHelper;
	}

	/**
	 * Method to get The comments related data e.g userids,user names, user profile pics, comments against user
	 *
	 * @param   integer  $eleId          id.
	 * @param   integer  $ele            element.
	 * @param   integer  $childs         getchildren.
	 * @param   array    $childsId       childrensId.
	 * @param   string   $order          ordering.
	 * @param   array    $annoIdsArr     annoIdsArr.
	 * @param   string   $viewmoreIdArr  viewmoreIdArr.
	 * @param   integer  $note_type      Type of note.
	 *
	 * @return object
	 *
	 * @since 3.0
	 */
	public function getRatingReviewData($eleId, $ele, $childs, $childsId, $order = 'DESC', $annoIdsArr = array(), $viewmoreIdArr = '', $note_type = '')
	{
		$db = Factory::getDBO();
		$query = $db->getQuery(true);

		// If call to print child comment
		if ($childs)
		{
			foreach ($childsId as $i => $v)
			{
				$childsId[$i] = (int) $v;
			}

			if (!empty($childsId[0]))
			{
				$query->where($db->qn('ant.id') . ' IN (' . implode(',', $childsId) . ')');
			}
		}
		else
		{
			// View more / latest or oldest
			if (!empty($viewmoreIdArr[0]))
			{
				$result  = array_merge((array) $annoIdsArr, (array) $viewmoreIdArr);

				foreach ($result as $i => $v)
				{
					$result[$i] = (int) $v;
				}

				$query->where($db->qn('ant.id') . ' NOT IN (' . implode(',', $result) . ')');
				$query->where($db->qn('ant.parent_id') . ' =  0 ');
			}
			elseif (!empty($annoIdsArr[0]))
			{
				foreach ($annoIdsArr as $i => $v)
				{
					$annoIdsArr[$i] = (int) $v;
				}

				$query->where($db->qn('ant.id') . ' NOT IN (' . implode(',', $annoIdsArr) . ')');
				$query->where($db->qn('ant.parent_id') . ' =  0 ');
			}
			else // On default page load or refresh
			{
				$query->where($db->qn('ant.parent_id') . ' =  0 ');
			}

			// LIMIT
			$params        = $this->jlikemainhelperObj->getjLikeParams();
			$comment_limit = $params->get('no_of_commets_to_show', 0, 'int');

			if (!empty($comment_limit))
			{
				$query->setLimit($comment_limit);
			}
		}

		$query->select($db->qn(array('jc.element_id', 'ant.annotation', 'ant.user_id', 'ant.images')));
		$query->select($db->qn(array('ant.parent_id', 'ant.annotation_date', 'u.name', 'u.email', 'jr.rating_upto', 'jr.user_rating')));
		$query->select($db->qn(array('jc.id', 'ant.id', 'ant.annotation'), array('contentid', 'annotation_id', 'smileyannotation')));
		$query->from($db->qn('#__jlike_content', 'jc'));
		$query->join('LEFT', $db->qn('#__jlike_annotations', 'ant') . ' ON (' . $db->qn('jc.id') . ' = ' . $db->qn('ant.content_id') . ')');
		$query->join('LEFT', $db->qn('#__jlike_rating', 'jr') . ' ON (' . $db->qn('jc.id') . ' = ' . $db->qn('jr.content_id') . ' AND '
		. $db->qn('jr.user_id') . ' = ' . $db->qn('ant.user_id') . ')');
		$query->join('INNER', $db->qn('#__users', 'u') . ' ON (' . $db->qn('u.id') . ' = ' . $db->qn('ant.user_id') . ')');
		$query->where($db->qn('jc.element_id') . ' = ' . $db->q((int) $eleId));
		$query->where($db->qn('jc.element') . ' = ' . $db->q($ele));
		$query->where($db->qn('ant.state') . ' = 1');
		$query->where($db->qn('ant.note') . ' = ' . $db->q($note_type));
		$query->order($db->qn('ant.annotation_date') . $order);

		$db->setQuery($query);
		$result = $db->loadObjectList();

		// Get the user rating avarage against review
		$result = $this->getUserRatingAvg($result);

		// Get the comment like & dislike count against comment
		$result = $this->getLikeDislikeCount($result);

		// Get the comment commentDateTime
		$result = $this->commentDateTime($result);

		foreach ($result as $key => $row)
		{
			$result[$key]->smileyannotation = $this->replaceSmileyAsImage($row->smileyannotation);
		}

		// Get the user info user profile pics & url
		$data = $this->getUserInfo($result);
		$data = $this->getReplyAgainstComment($result);

		return $data;
	}

	/**
	 * Method to get The comments related data e.g userids,user names, user profile pics, comments against user
	 *
	 * @param   integer   $contId    id.
	 * @param   integer   $ele       element.
	 * @param   integer   $children  getchildren.
	 * @param   array     $childsId  childrensId.
	 * @param   string    $order     ordering.
	 * @param   array     $annoArr   annotaionIdsArr.
	 * @param   string    $vmArr     viewmoreIdArr.
	 * @param   Interger  $notetype  Type of note.
	 * @param   array     $exParam   Extra parameter array
	 *
	 * @return object
	 *
	 * @since 3.0
	 */
	public function getCommentsData($contId, $ele, $children, $childsId, $order = 'DESC', $annoArr = array(),
		$vmArr = '', $notetype = '', $exParam = array())
	{
		$db = Factory::getDBO();

		// Create a new query object.
		$query = $db->getQuery(true);

		// If call to print child comment
		if ($children)
		{
			foreach ($childsId as $i => $v)
			{
				$childsId[$i] = (int) $v;
			}

			if (!empty($childsId[0]))
			{
				$query->where($db->qn('ant.id') . ' IN (' . implode(',', $childsId) . ')');
			}
		}
		else
		{
			// View more / latest or oldest
			if (!empty($vmArr[0]))
			{
				$result = array_merge((array) $annoArr, (array) $vmArr);

				foreach ($result as $i => $v)
				{
					$result[$i] = (int) $v;
				}

				$query->where($db->qn('ant.id') . 'NOT IN (' . implode(',', $result) . ')');
				$query->where($db->qn('ant.parent_id') . ' =  0 ');
			}
			elseif (!empty($annoArr[0]))
			{
				foreach ($annoArr as $i => $v)
				{
					$annoArr[$i] = (int) $v;
				}

				$query->where($db->qn('ant.id') . ' NOT IN (' . implode(',', $annoArr) . ')');
				$query->where($db->qn('ant.parent_id') . ' =  0 ');
			}
			else // On default page load or refresh
			{
				$query->where($db->qn('ant.parent_id') . ' =  0 ');
			}

			// LIMIT
			$params        = $this->jlikemainhelperObj->getjLikeParams($exParam['plg_type'], $exParam['plg_name']);
			$comment_limit = $params->get('no_of_commets_to_show', 0, 'int');

			if (!empty($comment_limit))
			{
				$query->setLimit($comment_limit);
			}
		}

		if (!empty($exParam['type']))
		{
			$exParam['type'] 	 = (string) preg_replace('/[^A-Z0-9_\.-]/i', '', $exParam['type']);
			$exParam['type'] 	 = ltrim($exParam['type'], '.');
			$query->where($db->qn('jc.type') . ' =  ' . $db->q($exParam['type']));
		}

		$query->select($db->qn(array('jc.element_id','ant.annotation','ant.parent_id','ant.annotation_date','u.name','u.email','u.block')));
		$query->select('u.id as user_id');
		$query->select('ant.user_id as commenter_id');
		$query->select($db->qn(array('jc.id', 'ant.id', 'ant.annotation'), array('contentid', 'annotation_id', 'smileyannotation')));
		$query->from($db->qn('#__jlike_content', 'jc'));
		$query->join('LEFT', $db->qn('#__jlike_annotations', 'ant') . ' ON (' . $db->qn('jc.id') . ' = ' . $db->qn('ant.content_id') . ')');
		$query->join('LEFT', $db->qn('#__users', 'u') . ' ON (' . $db->qn('u.id') . ' = ' . $db->qn('ant.user_id') . ')');
		$query->where($db->qn('jc.element_id') . ' = ' . $db->q((int) $contId));
		$query->where($db->qn('jc.element') . ' = ' . $db->q($ele));
		$query->where($db->qn('ant.state') . ' = 1');
		$query->where($db->qn('ant.note') . ' = ' . $db->q($notetype));
		$query->order($db->qn('ant.annotation_date') . $order);

		$db->setQuery($query);
		$result = $db->loadObjectList();

		// Get the comment like & dislike count against comment
		$result = $this->getLikeDislikeCount($result);

		// Get the comment commentDateTime
		$result = $this->commentDateTime($result);

		foreach ($result as $key => $row)
		{
			$result[$key]->smileyannotation = $this->replaceSmileyAsImage($row->smileyannotation);
		}

		$data = $this->getReplyAgainstComment($result);

		$helperPath = JPATH_SITE . '/components/com_jlike/helpers/main.php';

		if (!class_exists('ComjlikeMainHelper'))
		{
			// Require_once $path;
			if (file_exists($helperPath)) {
				require_once $helperPath;
			}
		}

		$ComjlikeMainHelper = new ComjlikeMainHelper;

		$plgData = array("plg_type" => $exParam['plg_type'], "plg_name" => $exParam['plg_name']);
		$sLibObj = $ComjlikeMainHelper->getSocialLibraryObject('', $plgData);

		foreach ($data as $index => $comment_info)
		{
			$comment_info->avtar    = Uri::root(true) . '/media/com_jlike/images/default/user.png';
			$comment_info->username = Text::_('COM_TJLMS_BLOCKED_USER');
			$comment_info->name = Text::_('COM_TJLMS_BLOCKED_USER');
			$comment_info->user_profile_url = '';

			if ($comment_info->user_id)
			{
				$commenter              = Factory::getUser($comment_info->user_id);
				$comment_info->user_id  = $commenter->id;
				$comment_info->block  	= $commenter->block;

				if (!$comment_info->block)
				{
					$comment_info->username = $commenter->username;
					$comment_info->name 	= $commenter->name;
					$comment_info->avtar    = $sLibObj->getAvatar($commenter, 50);
					$profileUrl = $sLibObj->getProfileUrl($commenter);
					$link = $profileUrl;

					if (!parse_url($profileUrl, PHP_URL_HOST))
					{
						$link = Uri::root() . substr(Route::_($profileUrl), strlen(Uri::base(true)) + 1);
					}

					$comment_info->user_profile_url = $link;
				}

				$comment_info->smileyannotation = self::parsedMention($comment_info->smileyannotation, $exParam);
			}
		}

		return $data;
	}

	/**
	 * DeleteReviews.
	 *
	 * @param   integer  $annotation_id  annotation_id.
	 *
	 * @return array
	 *
	 * @since 3.0
	 */
	public function DeleteReviews($annotation_id)
	{

		$annotationModel = BaseDatabaseModel::getInstance('Annotation', 'JLikeModel', array('ignore_request' => true));

		$annotationDetails = $annotationModel->getData($annotation_id);
		$images = json_decode($annotationDetails->images);
		foreach($images as $img){
			$this->deleteReviewImage($img);
		}

		$result = $this->getChildren($annotation_id);
		$this->deleteRating($annotation_id);

		if (!empty($result))
		{
			foreach ($result as $row)
			{
				$arr[] = $row;
			}
		}

		$arr   = array();
		$arr[] = $annotation_id;
		$count = count($arr);

		for ($i = 0; $i < $count; $i++)
		{
			$result = $this->getChildren($arr[$i]);

			if (!empty($result))
			{
				foreach ($result as $row)
				{
					$arr[] = $row;
				}
			}

			$count = count($arr);
		}

		if (isset($arr))
		{
			if (!empty($arr))
			{
				$db    = Factory::getDBO();
				$query = $db->getQuery(true);
				$query->delete($db->quoteName('#__jlike_annotations'));
				$query->where($db->qn('id') . ' IN (' . implode(',', $arr) . ')');
				$db->setQuery($query);

				if (!$db->execute())
				{
					$db->stderr();
				}
				else
				{
					return $arr;
				}
			}
		}
	}


	/**
	 * Delete review images.
	 *
	 * @param string filename.
	 *
	 * @return array
	 *
	 * @since 4.0
	 */
	public function deleteReviewImage($filename){
		$uploadDir = JPATH_ROOT . '/images/reviews/';
		$filePath = $uploadDir . basename($filename);
		return unlink($filePath);
	}

	/**
	 * DeleteComment.
	 *
	 * @param   integer  $annotation_id  annotation_id.
	 * @param   array    $extraParams    extraParams.
	 *
	 * @return array
	 *
	 * @since 3.0
	 */
	public function DeleteComment($annotation_id, $extraParams)
	{
		// $result = $this->getChildren($annotation_id);

		$annotationModel = BaseDatabaseModel::getInstance('Annotation', 'JLikeModel', array('ignore_request' => true));

		$annotationDetails = $annotationModel->getData($annotation_id);

		/*if (!empty($result))
		{
			foreach ($result as $row)
			{
				$arr[] = $row;
			}
		}*/

		$arr             = array();
		$arr['isParent'] = $annotationDetails->parent_id;
		$arr['data'][]   = $annotation_id;
		$count           = count($arr['data']);

		for ($i = 0; $i < $count; $i++)
		{
			$result = $this->getChildren($arr['data'][$i]);

			if (!empty($result))
			{
				foreach ($result as $row)
				{
					$arr['data'][] = $row;
				}
			}

			$count = count($arr['data']);
		}

		if (isset($arr['data']))
		{
				$db    = Factory::getDBO();
				$query = $db->getQuery(true);
				$query->delete($db->quoteName('#__jlike_annotations'));
				$query->where($db->qn('id') . ' IN (' . implode(',', $arr['data']) . ')');
				$db->setQuery($query);

				if (!$db->execute())
				{
					$db->stderr();
				}
				else
				{
					// Trigger the after save event.
					Factory::getApplication()->triggerEvent('onAfterJlikeCommentDelete', array($extraParams));

					return $arr;
				}
		}
	}

	/**
	 * manageListforContent.
	 *
	 * @param   ARRAY  $post  result obj.
	 *
	 * @return integer
	 *
	 * @since 3.0
	 */
	public function manageListforContent($post)
	{
		$post['list_id'] = (int) $post['list_id'];

		if (isset($post['list_id']) && !empty($post['list_id']))
		{
			if (isset($post['content_id']) && !empty($post['content_id']))
			{
				$content_id = (int) $post['content_id'];
			}
			else
			{
				$content_id = $this->jlikehelperObj->addContent((int) $post['element_id'], $post['element'], $post['url'], $post['title']);
			}

			$db = Factory::getDBO();
			$query = $db->getQuery(true);
			$query->select('*');
			$query->from($db->qn('#__jlike_likes_lists_xref'));
			$query->where($db->qn('content_id') . ' = ' . $db->q((int) $content_id));
			$query->where($db->qn('list_id') . ' = ' . $db->q((int) $post['list_id']));
			$db->setQuery($query);
			$res = $db->loadObject();

			if ($post['action'] == 'add')
			{
				if (!$res)
				{
					$insert_obj             = new stdClass;
					$insert_obj->content_id = $content_id;
					$insert_obj->list_id    = $post['list_id'];

					try
					{
						$db->insertObject('#__jlike_likes_lists_xref', $insert_obj);
					}
					catch (Exception $e)
					{
						Log::add($e->getMessage(), Log::ERROR, 'com_jlike');

						return 0;
					}
				}
			}
			else
			{
				if ($res)
				{
					$query = $db->getQuery(true);

					$conditions = array(
						$db->quoteName('content_id') . " = " . $content_id,
						$db->quoteName('list_id') . " = " . $post['list_id']
					);

					$query->delete($db->quoteName('#__jlike_likes_lists_xref'));
					$query->where($conditions);
					$db->setQuery($query);

					try
					{
						$db->execute();
					}
					catch (Exception $e)
					{
						Log::add($e->getMessage(), Log::ERROR, 'com_jlike');

						return 0;
					}
				}
			}

			return 1;
		}

		return 0;
	}
}
