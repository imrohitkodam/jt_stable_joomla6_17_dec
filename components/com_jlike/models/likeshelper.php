<?php
/**
 * @package     JLike
 * @subpackage  com_jlike
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Table\Table;


/**
 * Methods supporting a list of Jlike records.
 *
 * @since  1.6
 */
class JlikeModelLikesHelper extends ListModel
{
	protected $comjlikeHelper;

	/**
	 * Return all options for filter - used in all campaigns layout.
	 *
	 * @param   string  $default   default select option.
	 *
	 * @param   array   $elements  elements for select option.
	 *
	 * @param   string  $name      name of select tag.
	 *
	 * @return	string select box.
	 *
	 * @since	1.6
	 */
	public function LikecontentClassificationSelectOptions($default, $elements, $name)
	{
		$options         = array();
		$options[]       = HTMLHelper::_('select.option', "0", Text::_('SELECT_ELEMENT'));
		$brodfile        = JPATH_SITE . "/components/com_jlike/classification.ini";
		$classifications = parse_ini_file($brodfile);

		if (!empty($elements))
		{
			foreach ($elements as $element)
			{
				$element = trim($element);

				if (is_array($classifications) && array_key_exists($element, $classifications))
				{
					$elementini = $classifications[$element];
				}
				else
				{
					$elementini = $element;
				}

				$options[] = HTMLHelper::_('select.option', $element, $elementini);
			}
		}

		$class = ' size="1" onchange="this.form.submit();" name=" ' . $name . '"';

		return HTMLHelper::_('select.genericlist', $options, $name, $class, "value", "text", $default);
	}

	/**
	 * Loads all options for filter - used in all campaigns layout.
	 *
	 * @param   object  $user  user obj.
	 *
	 * @return	string select box.
	 *
	 * @since	1.6
	 */
	public function Likecontent_classification($user)
	{
		$mainframe = Factory::getApplication();
		$input     = Factory::getApplication()->getInput();
		$layout    = $input->get('layout', 'default', 'STRING');
		$db = Factory::getDBO();

		$where = '';

		if ($layout != 'all')
		{
			if ($user->id)
			{
				$where = ' likes.userid =' . $db->quote($user->id);
			}

			$default = $mainframe->getUserStateFromRequest('com_jlike' . 'filter_likecontent_classification', 'filter_likecontent_classification');
			$name    = 'filter_likecontent_classification';
		}
		else
		{
			$default = $mainframe->getUserStateFromRequest('com_jlike' . 'filter_all_likecontent_classification', 'filter_all_likecontent_classification');
			$name    = 'filter_all_likecontent_classification';
		}

		$query = $db->getQuery(true);
		$query->select("distinct (likecontent.element)")
		->from('#__jlike_content AS likecontent')
		->join('INNER', '#__jlike_likes as likes ON (likes.content_id = likecontent.id)');

		if (!empty($where))
		{
			$query->where($where);
		}

		$db->setQuery($query);

		if (JVERSION < '3.0')
		{
			$elements = $db->loadResultArray();
		}
		else
		{
			$elements = $db->loadColumn();
		}

		$select = $this->LikecontentClassificationSelectOptions($default, $elements, $name);

		return $select;
	}

	/**
	 * Likecontent_list.
	 *
	 * @param   object  $user  user obj.
	 *
	 * @return string
	 *
	 * @since 3.0
	 */
	public function Likecontent_list($user)
	{
		$mainframe = Factory::getApplication();

		$selectlist = '';
		$db = Factory::getDBO();

		if ($user->id)
		{
			$query = $db->getQuery(true);
			$query->select("distinct(likelist.title) as list_name,likelist.id")
			->from('#__jlike_like_lists AS likelist')
			->where('likelist.user_id =' . $db->quote($user->id));

			$db->setQuery($query);
			$datas = $db->loadObjectList();

			$default = $mainframe->getUserStateFromRequest('com_jlike' . 'filter_likecontent_list', 'filter_likecontent_list');

			$options   = array();
			$options[] = HTMLHelper::_('select.option', "0", "Select List");

			foreach ($datas as $data)
			{
				if ($data->list_name)
				{
					$options[] = HTMLHelper::_('select.option', $data->id, $data->list_name);
				}
			}

			$class = 'class="" size="1" onchange="this.form.submit();" name="filter_likecontent_list"';
			$selectlist = HTMLHelper::_('select.genericlist', $options, "filter_likecontent_list", $class, "value", "text", $default);
		}

		return $selectlist;
	}

	/**
	 * This function delete all table entry associated to like.
	 *
	 * @param   Integer  $content_id  Like content id.
	 * @param   Integer  $user_id     user_id.
	 *
	 * @return array
	 *
	 * @since 3.0
	 */
	public function getUpdateLableList($content_id, $user_id)
	{
		$db = Factory::getDBO();
		$this->comjlikeHelper = new comjlikeHelper;
		$allLables = $this->comjlikeHelper->getLableList($user_id);

		try
		{
			// Delete the xref table entry first 	$query->join('LEFT', '`#__categories` AS c ON c.id=ki.category');
			$query = $db->getQuery(true)
						->select('list.id')
						->from('#__jlike_likes_lists_xref AS lref')
						->join('INNER', '#__jlike_like_lists AS list ON list.id = lref.list_id');
			$query->where('lref.content_id = ' . $db->quote($content_id));
			$query->where('list.user_id = ' . $db->quote($user_id));

			$db->setQuery($query);
			$UserContLables = $db->loadColumn();
		}
		catch (Exception $e)
		{
			$UserContLables = array();
		}

		if (is_array($allLables))
		{
			foreach ($allLables as $key => $lable)
			{
				if (in_array($lable->id, $UserContLables))
				{
					$allLables[$key]->checked = 1;
				}
				else
				{
					$allLables[$key]->checked = 0;
				}
			}
		}

		return $allLables;
	}

	/**
	 * Likecontent_user.
	 *
	 * @param   object  $user  user obj.
	 *
	 * @return array
	 *
	 * @since 3.0
	 */
	public function Likecontent_user($user)
	{
		$mainframe = Factory::getApplication();
		$db = Factory::getDBO();

		$query = $db->getQuery(true);
		$query->select("distinct(likes.userid) as userid,users.name as username")
		->from('#__jlike_likes AS likes')
		->join('LEFT', '#__users as users ON (likes.userid = users.id)');

		$db->setQuery($query);
		$datas = $db->loadObjectList();

		$filter_likecontent_user = $mainframe->getUserStateFromRequest('com_jlike.filter_likecontent_user', 'filter_likecontent_user');
		$this->setState('filter_likecontent_user', $filter_likecontent_user);

		$options   = array();
		$options[] = HTMLHelper::_('select.option', "0", "Select User");

		foreach ($datas as $data)
		{
			if ($data->username)
			{
				$options[] = HTMLHelper::_('select.option', $data->userid, $data->username);
			}
		}

		return $options;
	}

	/**
	 * GetLineChartValues.
	 *
	 * @return array
	 *
	 * @since 3.0
	 */
	public function getLineChartValues()
	{
		$input = Factory::getApplication()->getInput();
		$post  = $input->getArray($_POST);

		if (isset($post['todate']))
		{
			$to_date = $post['todate'] . ' + 1 days';
		}
		else
		{
			$to_date = date('Y-m-d', strtotime(date('Y-m-d') . ' + 1 days'));
		}

		if (isset($post['fromdate']))
		{
			$from_date = $post['fromdate'];
		}
		else
		{
			$from_date = date('Y-m-d', strtotime(date('Y-m-d') . ' - 30 days'));
		}

		$diff     = strtotime($to_date) - strtotime($from_date);
		$days     = round($diff / 86400);

		$db  = Factory::getDBO();
		$que = $db->getQuery(true);
		$que->select("jl.like,jl.dislike,jl.date")
		->from('#__jlike_likes as jl')
		->where('jl.date >= ' . $db->quote(strtotime($from_date)) . ' AND jl.date <= ' . $db->quote(strtotime($to_date)));
		$db->setQuery($que);
		$like_result = $db->loadObjectList();

		$line_chart = array();
		$line_chart['days_arr'] = [];
		$line_chart['like_arr'] = [];
		$line_chart['dislike_arr'] = [];

		for ($i = 0; $i <= $days; $i++)
		{
			$ondate                   = date('Y-m-d', strtotime($from_date . ' +  ' . $i . 'days'));
			$line_chart['days_arr'][] = $ondate;
			$like_cnt                 = 0;
			$dislike_cnt              = 0;

			foreach ($like_result as $k => $v)
			{
				if ($ondate === date('Y-m-d', $v->date))
				{
					$like_cnt += $v->like;
					$dislike_cnt += $v->dislike;
				}
			}

			$line_chart['like_arr'][] = $like_cnt;
			$line_chart['dislike_arr'][] = $dislike_cnt;
		}

		return $line_chart;
	}

	/**
	 * Delete the lable list.
	 *
	 * @param   Integer  $lableListId  Its list id (lable id).
	 *
	 * @return array
	 *
	 * @since 3.0
	 */
	public function jlike_deleteList($lableListId)
	{
		$res = array();
		$res['status'] = 0;
		$res['statusMsg'] = Text::_('COM_JLIKE_INVALID_LABLE_ID', true);

		if (!empty($lableListId))
		{
			$db = Factory::getDBO();

			try
			{
				// Delete the xref table entry first
				$query = $db->getQuery(true)
							->delete('#__jlike_likes_lists_xref')
							->where('list_id=' . $db->quote($lableListId));
				$db->setQuery($query);

				if (!$db->execute())
				{
					$res['statusMsg'] = $db->getErrorMsg();

					return $res;
				}

				// Delete the like_list table entry first
				$query = $db->getQuery(true)
							->delete('#__jlike_like_lists')
							->where('id=' . $db->quote($lableListId));
				$db->setQuery($query);

				if (!$db->execute())
				{
					$res['statusMsg'] = $db->getErrorMsg();

					return $res;
				}

				$res['status'] = 1;
				$res['statusMsg'] = Text::_('COM_JLIKE_DELETED_SUCCESSFULLY', true);

				return $res;
			}
			catch (Exception $e)
			{
				$res['statusMsg'] = $e->getMessage();

				return $res;
			}
		}

		return $res;
	}

	/**
	 * Delete like from my like view.
	 *
	 * @param   Integer  $rowIds  Its like content ids.
	 *
	 * @return integer
	 *
	 * @since 3.0
	 */
	public function delete($rowIds)
	{
		if (is_array($rowIds))
		{
			$successCount = 0;

			foreach ($rowIds as $id)
			{
				if (!empty($id))
				{
					$status = $this->deleteMyLike($id);

					// If success then increament
					$successCount = ($status === 1) ? ($successCount + 1) : $successCount;
				}
			}

			return $successCount;
		}
	}

	/**
	 * This function delete all table entry associated to like.
	 *
	 * @param   Integer  $content_id  Its like content id.
	 *
	 * @return integer
	 *
	 * @since 3.0
	 */
	public function deleteMyLike($content_id)
	{
		$db = Factory::getDBO();
		$user = Factory::getUser();

		if (empty($user->id)  || !$content_id)
		{
			return -1;
		}

		try
		{
			// Delete only logged in user's likes
			Table::addIncludePath(JPATH_ROOT . '/components/com_jlike/tables');
			$likeTbl = Table::getInstance('like', 'JlikeTable');
			$likeTbl->load(array('content_id' => $content_id, 'userid' => $user->id));

			if (empty($likeTbl->id))
			{
				return -1;
			}

			$likeTbl->delete();

			// Decrement like count
			$this->decrementLikeCount($content_id);

			// @TODO check - is need to delete to delete entry from content table.
		}
		catch (RuntimeException $e)
		{
			throw new Exception($db->getErrorMsg());
		}

		return 1;
	}

	/**
	 * This function delete all table entry associated to like.
	 *
	 * @param   object  $data  It give new note, userid, contentid
	 *
	 * @return integer
	 *
	 * @since 3.0
	 */
	public function updateMyNote($data)
	{
		try
		{
			$db = Factory::getDbo();
			$query = $db->getQuery(true);

			// Fields to update.
			$fields = array(
				$db->quoteName('annotation') . ' = ' . $db->quote($data['note']),
			);

			// Conditions for which records should be updated.
			$conditions = array(
				$db->quoteName('user_id') . ' = ' . $db->quote($data['user_id']),
				$db->quoteName('id') . ' = ' . $db->quote($data['anno_id'])
			);

			$query->update($db->quoteName('#__jlike_annotations'))->set($fields)->where($conditions);
			$db->setQuery($query);

			if (!$db->execute())
			{
				return 0;
			}

			return 1;
		}
		catch (RuntimeException $e)
		{
			throw new Exception($db->getErrorMsg());
		}
	}

	/**
	 * This function delete all table entry associated to like.
	 *
	 * @param   Integer  $content_id  Like content id.
	 *
	 * @return integer
	 *
	 * @since 3.0
	 */
	public function decrementLikeCount($content_id)
	{
		$db = Factory::getDBO();

		try
		{
			$query = $db->getQuery(true);

			// Fields to update.
			$fields = array(
				$db->quoteName('like_cnt') . ' = like_cnt -1 '
			);

			// Conditions for which records should be updated.
			$conditions = array(
				$db->quoteName('id') . ' = ' . $db->quote($content_id)
			);

			$query->update($db->quoteName('#__jlike_content'))->set($fields)->where($conditions);
			$db->setQuery($query);

			if (!$db->execute())
			{
				return 0;
			}
		}
		catch (RuntimeException $e)
		{
			throw new Exception($db->getErrorMsg());
		}
	}
}
