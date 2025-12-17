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
defined('_JEXEC') or die('Restricted access');
use Joomla\Data\DataObject;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Response\JsonResponse;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
require_once JPATH_SITE . '/components/com_jlike/models/annotationform.php';

/**
 * Methods supporting a list of Jlike records.
 *
 * @since  1.6
 */
class JlikeModelAnnotations extends ListModel
{
	private $ttotal = null;

	private $ppagination = null;

	/**
	 * Constructor.
	 *
	 * @see        JController
	 * @since      1.6
	 */
	public function __construct()
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'annotation', 'likeannotations.annotation',
				'title',
				'element',
				'list_name',
				'name', 'users.name',
				'like_cnt', 'likecontent.like_cnt',
				'dislike_cnt', 'likecontent.dislike_cnt',
			);
		}

		parent::__construct($config);
	}

	/**
	 * Build where part of query for annotations.
	 *
	 * @return	array
	 */
	public function buildWherePartForAnnotations()
	{
		// Build query as you want
		$db = Factory::getDBO();
		$user = Factory::getUser();
		$app = Factory::getApplication();
		$jinput  = $app->input;
		$layout = $jinput->get('layout', 'default', 'STRING');

		$wherePart = array();
		$temp1 = 'filter_search_likecontent';
		$filter_search_likecontent = $app->getUserStateFromRequest('com_jlike' . 'filter_search_likecontent', $temp1, '', 'string');
		$tmp = 'filter_likecontent_classification';
		$filter_likecontent_classification = $app->getUserStateFromRequest('com_jlike' . 'filter_likecontent_classification', $tmp);
		$filter_likecontent_list           = $app->getUserStateFromRequest('com_jlike' . 'filter_likecontent_list', 'filter_likecontent_list');

		if ($filter_likecontent_list)
		{
			$wherePart[] = " likelist.id =" . $db->quote($filter_likecontent_list);
		}

		$filter_likecontent_user = $app->getUserStateFromRequest('com_jlike' . 'filter_likecontent_user', 'filter_likecontent_user');

		if ($layout == 'default')
		{
			if ($user->id)
			{
				$wherePart[] = ' likeannotations.user_id=' . $db->quote($user->id);
			}
		}
		elseif ($layout == 'all')
		{
			if ($filter_likecontent_user)
			{
				$wherePart[] = ' likeannotations.user_id=' . $db->quote($filter_likecontent_user);
			}

			$wherePart[] = ' likeannotations.privacy=' . $db->quote(0);
		}

		if ($filter_search_likecontent)
		{
			$filter_search_likecontent = $db->quote('%' . $filter_search_likecontent . '%');
			$wherePart[] = " likecontent.title LIKE " . $filter_search_likecontent;
		}

		if ($filter_likecontent_classification)
		{
			$filter_likecontent_classification = $db->quote('%' . $filter_likecontent_classification . '%');
			$wherePart[] = " likecontent.element LIKE " . $filter_likecontent_classification;
		}

		return $wherePart;
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   Elements order
	 * @param   string  $direction  Order direction
	 *
	 * @return void
	 *
	 * @throws Exception
	 *
	 * @since    1.6
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// Initialise variables.
		$app = Factory::getApplication();

		// List state information
		$limit = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->get('list_limit'));
		$this->setState('list.limit', $limit);

		$limitstart = $app->getUserStateFromRequest('limitstart', 'limitstart', 0);
		$this->setState('list.start', $limitstart);

		// Receive & set filters
		if ($filters = $app->getUserStateFromRequest($this->context . '.filter', 'filter', array(), 'array'))
		{
			foreach ($filters as $name => $value)
			{
				$this->setState('filter.' . $name, $value);
			}
		}

		// Set ordering.
		$orderCol = $app->getUserStateFromRequest($this->context . 'filter_order', 'filter_order', '', 'cmd');

		// Set ordering direction.
		$listOrder = $app->getUserStateFromRequest($this->context . 'filter_order_Dir', 'filter_order_Dir');

		if (!in_array($orderCol, $this->filter_fields))
		{
			$orderCol = 'title';
		}

		if (!in_array(strtoupper($listOrder), array('ASC', 'DESC', '')))
		{
			$listOrder = 'desc';
		}

		$this->setState('list.ordering', $orderCol);

		$this->setState('list.direction', $listOrder);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return   DataObjectbaseQuery
	 *
	 * @since    1.6
	 */
	protected function getListQuery()
	{
		$db = Factory::getDBO();
		$query = $db->getQuery(true);
		$user = Factory::getUser();
		$app = Factory::getApplication();

		$where = $this->buildWherePartForAnnotations();

		$query->select('likecontent.*');
		$query->select($db->qn(array('likeannotations.annotation', 'likelist.title', 'users.name'), array('annotation', 'list_name', 'username')));
		$query->from($db->qn('#__jlike_likes', 'likes'));
		$query->join('LEFT', $db->qn('#__jlike_content', 'likecontent') . ' ON ( ' . $db->qn('likecontent.id') . ' = ' . $db->qn('likes.content_id') . ')');
		$query->join('INNER', $db->qn('#__jlike_annotations', 'likeannotations') . ' ON (' . $db->qn('likeannotations.content_id') . '
		= ' . $db->qn('likecontent.id') . ' AND ' . $db->qn('likeannotations.user_id') . ' = ' . $db->qn('likes.userid') . ')'
		);
		$query->join('LEFT', $db->qn('#__jlike_likes_lists_xref', 'listxref') . '
		 ON (' . $db->qn('likes.content_id') . ' = ' . $db->qn('listxref.content_id') . ')');
		$query->join('LEFT', $db->qn('#__jlike_like_lists', 'likelist') . ' ON (' . $db->qn('listxref.list_id') . ' = ' . $db->qn('likelist.id') .
		' AND ' . $db->qn('likes.userid') . ' = ' . $db->qn('likelist.user_id') . ')'
		);
		$query->join('LEFT', $db->qn('#__users', 'users') . ' ON ( ' . $db->qn('likes.userid') . ' = ' . $db->qn('users.id') . ')');
		$query->where('likes.userid = ' . $db->quote($user->id) . ' AND ( likes.like = 1 OR likes.dislike = 1 )');

		if (!empty($where))
		{
			$whereStr = implode(' AND ', $where);
			$query->where($whereStr);
		}

		$query->group('likecontent.id, likeannotations.user_id');

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
	 * Method to get an array of data items
	 *
	 * @return  mixed An array of data on success, false on failure.
	 */
	public function getItems()
	{
		$items = parent::getItems();

		return $items;
	}

	/**
	 * Method to get an array of data items
	 *
	 * @param   object  $data  data.
	 *
	 * @return  mixed An array of data on success, false on failure.
	 */
	public function getAnnotations($data)
	{
		// Code Written For Private Comment to Check Context Value
		PluginHelper::importPlugin('content');
		$triggerResult = Factory::getApplication()->triggerEvent('onBeforeListComments', array($data));

		if (!empty($triggerResult) && !$triggerResult[0])
		{
			$result = new stdClass;
			$result->success = false;
			$result->result  = Text::_('JERROR_ALERTNOAUTHOR');
			echo new JsonResponse($result);
			Factory::getApplication()->close();
		}
		// End Code of Private Comment

		// Create a new query object
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		$query = $this->getListQueryForTodos();

		// Reset the query using our newly populated query object.
		$db->setQuery($query);

		// Load the results as a list of stdClass objects (see later for more options on retrieving data).
		$items = $db->loadObjectList();

		$annotationFormModel = BaseDatabaseModel::getInstance('AnnotationForm', 'JlikeModel');

		$helperPath = JPATH_SITE . '/components/com_jlike/helpers/main.php';

		if (!class_exists('ComjlikeMainHelper'))
		{
			// Require_once $path;
			if (file_exists($helperPath)) {
				require_once $helperPath;
			}
		}

		$ComjlikeMainHelper = new ComjlikeMainHelper;
		$sLibObj            = $ComjlikeMainHelper->getSocialLibraryObject('', $data);

		if (count($items) > 0)
		{
			foreach ($items as $key => $item)
			{
				$item->annotation_date = HTMLHelper::date($item->annotation_date, 'Y-m-d H:i:s', true);
				$item->is_mine = false;

				// Parse the Comment
				$item->annotation_html = $annotationFormModel->parsedMention($item->annotation, $data);
				$item->annotation_html = $annotationFormModel->replaceSmileyAsImage($item->annotation_html);

				if ($item->user_id == Factory::getUser()->id || $item->user_id == $data["user_id"])
				{
					$item->is_mine = true;
				}

				if ($item->parent_id == '0')
				{
					$item->parent_id = null;
				}

				$item->user       = new stdclass;
				$item->user->id   = $item->user_id;
				$item->user->name = Factory::getUser($item->user_id)->name;

				$ment_usr = Factory::getUser($item->user_id);

				$link = '';
				$link = $profileUrl = $sLibObj->getProfileUrl($ment_usr);

				if ($profileUrl)
				{
					if (!parse_url($profileUrl, PHP_URL_HOST))
					{
						$link = Uri::root() . substr(Route::_($sLibObj->getProfileUrl($ment_usr)), strlen(Uri::base(true)) + 1);
					}
				}

				$item->user->profile_link  = $link;
				$item->user->avatar        = $sLibObj->getAvatar($ment_usr, 50);
			}
		}

		return $items;
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return   DataObjectbaseQuery
	 *
	 * @since    1.6
	 */
	protected function getListQueryForTodos()
	{
		// Create a new query object.
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Create the base insert statement.
		$query->clear();

		// Add the list ordering clause.
		$orderCol  = $this->state->get('list.ordering');
		$orderDirn = $this->state->get('list.direction');

		// Select the required fields from the table.
		$query
			->select(
				$this->getState(
					'list.select', 'DISTINCT a.id as annotation_id, a.* '
				)
			);

		$query->from('`#__jlike_annotations` AS a');

		// Join over the users for the checked out user.
		$query->select('user_id.name AS editor');

		// $query->join('LEFT', $db->qn('#__users', 'uc') . ' ON ( ' . $db->qn('uc.id') . ' = ' . $db->qn('a.checked_out') . ')');

		// Join over the created by field 'created_by'
		$query->join('LEFT', $db->qn('#__users', 'user_id') . ' ON ( ' . $db->qn('user_id.id') . ' = ' . $db->qn('a.user_id') . ')');

		if (!Factory::getUser()->authorise('core.edit', 'com_jlike'))
		{
			$query->where($db->qn('a.state') . ' = 1');
		}

		$content_id = $this->getState("content_id", '');

		if (!empty($content_id))
		{
			$query->where($db->qn('a.content_id') . ' = ' . $db->quote((int) ($content_id)));
		}

		$context = $this->getState("context", '');

		if ($context)
		{
			$query->where($db->qn('a.context') . ' = ' . $db->quote($context));
		}

		$parent_id = $this->getState("parent_id", '');

		if (!empty($parent_id))
		{
			$query->where($db->qn('a.parent_id') . ' = ' . $db->quote((int) ($parent_id)));
		}

		// Filter by search in title
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where($db->qn('a.id') . ' = ' . $db->quote((int) substr($search, 3)));
			}
			else
			{
				$search = $db->quote('%' . $db->escape($search, true) . '%');
			}
		}

		if ($orderCol && $orderDirn)
		{
			$query->order($db->escape($orderCol . ' ' . $orderDirn));
		}

		$limitstart = $this->getState('list.start', 0);
		$limit = $this->getState('list.limit', 5);

		$query->setLimit($limit, $limitstart);

		return $query;
	}

	/**
	 * Get comments
	 *
	 * @param   array  $data  Data
	 *
	 * @return bool
	 */
	public function getHybridAnnotations($data)
	{
		// Create a new query object.
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		$query = $this->getListQueryForTodos();

		// Reset the query using our newly populated query object.
		$db->setQuery($query);

		// Load the results as a list of stdClass objects (see later for more options on retrieving data).
		$items = $db->loadObjectList();

		$annotationFormModel = BaseDatabaseModel::getInstance('AnnotationForm', 'JlikeModel');

		$helperPath = JPATH_SITE . '/components/com_jlike/helpers/main.php';

		if (!class_exists('ComjlikeMainHelper'))
		{
			// Require_once $path;
			if (file_exists($helperPath)) {
				require_once $helperPath;
			}
		}

		$ComjlikeMainHelper = new ComjlikeMainHelper;
		$sLibObj            = $ComjlikeMainHelper->getSocialLibraryObject('', $data);

		if (count($items) > 0)
		{
			foreach ($items as $key => $item)
			{
				$item->annotation_date = HTMLHelper::date($item->annotation_date, 'Y-m-d H:i:s', true);
				$item->is_mine = false;

				// Parse the Comment
				$item->annotation_html = $annotationFormModel->parsedMention($item->annotation, $data);
				$item->annotation_html = $annotationFormModel->replaceSmileyAsImage($item->annotation_html);

				if ($item->user_id == Factory::getUser()->id)
				{
					$item->is_mine = true;
				}

				if ($item->parent_id == '0')
				{
					$item->parent_id = null;
				}

				$item->user       = new stdclass;
				$item->user->id   = $item->user_id;
				$item->user->name = Factory::getUser($item->user_id)->name;

				$ment_usr = Factory::getUser($item->user_id);

				$link = '';
				$link = $profileUrl = $sLibObj->getProfileUrl($ment_usr);

				if ($profileUrl)
				{
					if (!parse_url($profileUrl, PHP_URL_HOST))
					{
						$link = Uri::root() . substr(Route::_($sLibObj->getProfileUrl($ment_usr)), strlen(Uri::base(true)) + 1);
					}
				}

				$item->user->profile_link  = $link;
				$item->user->avatar        = $sLibObj->getAvatar($ment_usr, 50);
			}
		}

		return $items;
	}

	/**
	 * Likecontent_classification.
	 *
	 * @param   object  $user  user obj.
	 *
	 * @return void
	 *
	 * @since 3.0
	 */
	public function Likecontent_classification($user)
	{
		$db = Factory::getDBO();
		$query = $db->getQuery(true);
		$app = Factory::getApplication();

		$query->select('distinct(likecontent.element)')
			->from($db->qn('#__jlike_likes', 'likes'))
			->join('LEFT', $db->qn('#__jlike_content', 'likecontent') . ' ON ( ' . $db->qn('likes.content_id') . '  = ' . $db->qn('likecontent.id') . ')');

		if ($user->id)
		{
			$query->where($db->qn('likes.userid') . ' = ' . $db->quote($user->id));
		}

		$db->setQuery($query);
		$users = $db->loadColumn();

		$options         = array();
		$options[]       = HTMLHelper::_('select.option', "0", Text::_('SELECT_ELEMENT'));
		$brodfile        = JPATH_SITE . "/components/com_jlike/classification.ini";
		$classifications = parse_ini_file($brodfile);

		if ($users)
		{
			foreach ($users as $element)
			{
				$element = trim($element);

				if ($element)
				{
					if (array_key_exists($element, $classifications))
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
		}

		$tmp = 'filter_likecontent_classification';
		$filter_likecontent_classification = $app->getUserStateFromRequest('com_jlike.filter_likecontent_classification', $tmp);

		$this->setState('filter_likecontent_classification', $filter_likecontent_classification);

		/*$options=array();
		$options[]=HTMLHelper::_('select.option',"0","Select Classification");
		foreach($users AS $user)
		{
		if($user->element)
		$options[]=HTMLHelper::_('select.option',$user->element,$user->element);
		}*/

		return $options;
	}

	/**
	 * Likecontent_list.
	 *
	 * @param   object  $user  user obj.
	 *
	 * @return void
	 *
	 * @since 3.0
	 */
	public function Likecontent_list($user = '')
	{
		$app = Factory::getApplication();
		$db = Factory::getDBO();
		$query = $db->getQuery(true);

		$query->select('distinct(likelist.title) as list_name, likelist.id')
			->from($db->qn('#__jlike_like_lists', 'likelist'));

		if (!empty($user->id))
		{
			$query->where($db->qn('likelist.user_id') . ' = ' . $db->quote($user->id));
		}

		$db->setQuery($query);
		$datas = $db->loadObjectList();

		$filter_likecontent_list = $app->getUserStateFromRequest('com_jlike.filter_likecontent_list', 'filter_likecontent_list');
		$this->setState('filter_likecontent_list', $filter_likecontent_list);

		$options   = array();
		$options[] = HTMLHelper::_('select.option', "0", "Select List");

		foreach ($datas AS $data)
		{
			if ($data->list_name)
			{
				$options[] = HTMLHelper::_('select.option', $data->id, $data->list_name);
			}
		}

		return $options;
	}

	/**
	 * Likecontent_user.
	 *
	 * @param   object  $user  user obj.
	 *
	 * @return void
	 *
	 * @since 3.0
	 */
	public function Likecontent_user($user)
	{
		$app = Factory::getApplication();
		$db = Factory::getDBO();
		$query = $db->getQuery(true);

		$query->select('distinct(likes.userid) as userid,users.name as username')
			->from($db->qn('#__jlike_likes', 'likes'))
			->join('LEFT', $db->qn('#__users', 'users') . ' ON ( ' . $db->qn('likes.userid') . ' = ' . $db->qn('users.id') . ')');

		$db->setQuery($query);
		$datas = $db->loadObjectList();

		$filter_likecontent_user = $app->getUserStateFromRequest('com_jlike.filter_likecontent_user', 'filter_likecontent_user');
		$this->setState('filter_likecontent_user', $filter_likecontent_user);

		$options   = array();
		$options[] = HTMLHelper::_('select.option', "0", "Select User");

		foreach ($datas AS $data)
		{
			if ($data->username)
			{
				$options[] = HTMLHelper::_('select.option', $data->userid, $data->username);
			}
		}

		return $options;
	}
}
