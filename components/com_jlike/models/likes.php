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
use Joomla\Data\DataObject;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;


$likesChildClass = JPATH_SITE . '/components/com_jlike/models/likeshelper.php';
JLoader::register('JlikeModelLikesHelper', $likesChildClass);

/**
 * Methods supporting a list of Jlike records.
 *
 * @since  1.6
 */
class JlikeModelLikes extends JlikeModelLikesHelper
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see        JController
	 * @since      1.6
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'title', 'likecontent.title',
				'annotation', 'likeannotations.annotation',
				'element', 'likecontent.element',
				'like_cnt', 'likecontent.like_cnt',
				'dislike_cnt', 'likecontent.dislike_cnt',
			);
		}

		parent::__construct($config);
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
		$jinput  = Factory::getApplication()->getInput();
		$layout = $jinput->get('layout', 'default', 'STRING');

		// List state information
		$limit = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->get('list_limit'));
		$this->setState('list.limit', $limit);

		$limitstart = $app->getUserStateFromRequest('limitstart', 'limitstart', 0);
		$this->setState('list.start', $limitstart);

		/**
		if ($list = $app->getUserStateFromRequest($this->context . '.list', 'list', array(), 'array'))
		{
			foreach ($list as $name => $value)
			{
				// Extra validations
				switch ($name)
				{
					case 'fullordering':
						$orderingParts = explode(' ', $value);

						if (count($orderingParts) >= 2)
						{
							// Latest part will be considered the direction
							$fullDirection = end($orderingParts);

							if (in_array(strtoupper($fullDirection), array('ASC', 'DESC', '')))
							{
								$this->setState('list.direction', $fullDirection);
							}

							unset($orderingParts[count($orderingParts) - 1]);

							// The rest will be the ordering
							$fullOrdering = implode(' ', $orderingParts);

							if (in_array($fullOrdering, $this->filter_fields))
							{
								$this->setState('list.ordering', $fullOrdering);
							}
						}
						else
						{
							$this->setState('list.ordering', $ordering);
							$this->setState('list.direction', $direction);
						}
						break;

					case 'ordering':
						if (!in_array($value, $this->filter_fields))
						{
							$value = $ordering;
						}
						break;

					case 'direction':
						if (!in_array(strtoupper($value), array('ASC', 'DESC', '')))
						{
							$value = $direction;
						}
						break;

					case 'limit':
						$limit = $value;
						break;

					// Just to keep the default case
					default:
						$value = $value;
						break;
				}

				$this->setState('list.' . $name, $value);
			}
		}
		*/
		// Receive & set filters
		if ($filters = $app->getUserStateFromRequest($this->context . '.filter', 'filter', array(), 'array'))
		{
			foreach ($filters as $name => $value)
			{
				$this->setState('filter.' . $name, $value);
			}
		}

		if ($layout == 'all')
		{
			// Set ordering.
			$orderCol = $app->getUserStateFromRequest($this->context . 'all_filter_order', 'all_filter_order', '', 'cmd');

			// Set ordering direction.
			$listOrder = $app->getUserStateFromRequest($this->context . 'all_filter_order_Dir', 'all_filter_order_Dir');
		}
		else
		{
			// Set ordering.
			$orderCol = $app->getUserStateFromRequest($this->context . 'filter_order', 'filter_order', '', 'cmd');

			// Set ordering direction.
			$listOrder = $app->getUserStateFromRequest($this->context . 'filter_order_Dir', 'filter_order_Dir');
		}

		if (!in_array($orderCol, $this->filter_fields))
		{
			$orderCol = 'likecontent.title';
		}

		if (!in_array(strtoupper($listOrder ? $listOrder : ''), array('ASC', 'DESC', '')))
		{
			$listOrder = 'desc';
		}

		$this->setState('list.ordering', $orderCol);

		$this->setState('list.direction', $listOrder);
	}

	/**
	 * Build where part of query for all likes.
	 *
	 * @return	array
	 */
	public function buildWherePartForAllLikes()
	{
		$app = Factory::getApplication();
		$wherePart = array();
		$jinput = $app->input;
		$post = $jinput->post;
		$db = Factory::getDBO();

		// Date filter
		$defToDate = date('Y-m-d', strtotime(date('Y-m-d') . ' + 1 days'));
		$toDate = $post->get('todate', $defToDate,  'STRING');

		if (empty($toDate))
		{
			$toDate = $defToDate;
		}
		else
		{
			$toDate = date('Y-m-d', strtotime($toDate . ' + 1 days'));
		}

		$defFromDate = date('Y-m-d', strtotime(date('Y-m-d') . ' - 30 days'));
		$fromDate = $post->get('fromdate', $defFromDate, 'STRING');

		if (empty($fromDate))
		{
			$fromDate = $defFromDate;
		}

		$wherePart[] = " likes.date >= " . $db->quote(strtotime($fromDate)) . " AND likes.date <= " . $db->quote(strtotime($toDate));

		$searchString = $app->getUserStateFromRequest($this->context . 'all_filter_search', 'all_filter_search', '', 'string');

		if ($searchString)
		{
			$searchString = $db->quote('%' . $searchString . '%');
			$wherePart[] = " ( likecontent.title LIKE " . $searchString . " OR likecontent.element LIKE " . $searchString . " ) ";
		}

		$temp = 'filter_all_likecontent_classification';
		$filter_all_likecontent_classification = $app->getUserStateFromRequest('com_jlike' . 'filter_all_likecontent_classification', $temp);

		if ($filter_all_likecontent_classification)
		{
			$filter_all_likecontent_classification = $db->quote('%' . $filter_all_likecontent_classification . '%');
			$wherePart[] = " likecontent.element LIKE " . $filter_all_likecontent_classification;
		}

		return $wherePart;
	}

	/**
	 * Build where part of query for my likes
	 *
	 * @return	array
	 */
	public function buildWherePartForMyLikes()
	{
		$app = Factory::getApplication();
		$wherePart = array();
		$jinput = $app->input;
		$post = $jinput->post;
		$db = Factory::getDBO();

		$show_with_coments_only = $post->get('show_with_coments_only', '0',  'STRING');

		if ($show_with_coments_only == '1')
		{
			$wherePart[] = " likeannotations.annotation <> '' ";
		}

		$searchString = $app->getUserStateFromRequest($this->context . 'filter_search', 'filter_search', '', 'string');

		if ($searchString)
		{
			$searchString = $db->quote('%' . $searchString . '%');
			$wherePart[] = " ( likecontent.title LIKE " . $searchString . " OR likecontent.element LIKE " . $searchString . " ) ";
		}

		$temp = 'filter_likecontent_classification';
		$filter_likecontent_classification = $app->getUserStateFromRequest($this->context . 'filter_likecontent_classification', $temp);

		if ($filter_likecontent_classification)
		{
			$filter_likecontent_classification = $db->quote('%' . $filter_likecontent_classification . '%');
			$wherePart[] = " likecontent.element LIKE " . $filter_likecontent_classification;
		}

		$filter_likecontent_list = $app->getUserStateFromRequest($this->context . 'filter_likecontent_list', 'filter_likecontent_list');

		if ($filter_likecontent_list)
		{
			$wherePart[] = " likelist.id =" . $db->quote($filter_likecontent_list);
		}

		return $wherePart;
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
		$app = Factory::getApplication();
		$db = Factory::getDBO();
		$query = $db->getQuery(true);
		$jinput  = $app->input;
		$layout = $jinput->get('layout', 'default', 'STRING');

		if ($layout == 'all')
		{
			// Select the required fields from the table.
			$query->select("likecontent.*,likes.date")
			->from('#__jlike_content AS likecontent')
			->join('INNER', '#__jlike_likes AS likes ON likecontent.id = likes.content_id');

			$where = $this->buildWherePartForAllLikes();
		}
		else
		{
			$user = Factory::getUser();
			$query->select("likecontent.*,likeannotations.id as anno_id,
				likeannotations.annotation as annotation,likelist.title as list_name, likes.created,likes.modified")
			->from('#__jlike_content AS likecontent')
			->join('LEFT', '#__jlike_likes AS likes ON (likecontent.id = likes.content_id AND likes.userid = ' . $db->quote($user->id) . ' )')
			->join('LEFT', '#__jlike_annotations AS likeannotations
				ON ( likeannotations.content_id = likecontent.id AND likeannotations.user_id = likes.userid )')
			->join('LEFT', '#__jlike_likes_lists_xref AS listxref ON ( likecontent.id = listxref.content_id )')
			->join('LEFT', '#__jlike_like_lists AS likelist ON ( listxref.list_id = likelist.id AND likes.userid = likelist.user_id )')
			->where('likes.userid = ' . $db->quote($user->id) . ' AND ( likes.like = 1 OR likes.dislike = 1 )');

			$where = $this->buildWherePartForMyLikes();
		}

		if (!empty($where))
		{
			$whereStr = implode(' AND  ', $where);
			$query->where($whereStr);
		}

		$query->group('likecontent.id');

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
	 * Overrides the default function to check Date fields format, identified by
	 * "_dateformat" suffix, and erases the field if it's not correct.
	 *
	 * @return void
	 */
	/*protected function loadFormData()
	{
		$app              = Factory::getApplication();
		$filters          = $app->getUserState($this->context . '.filter', array());
		$error_dateformat = false;

		foreach ($filters as $key => $value)
		{
			if (strpos($key, '_dateformat') && !empty($value) && $this->isValidDate($value) == null)
			{
				$filters[$key]    = '';
				$error_dateformat = true;
			}
		}

		if ($error_dateformat)
		{
			$app->enqueueMessage(Text::_("COM_JLIKE_SEARCH_FILTER_DATE_FORMAT"), "warning");
			$app->setUserState($this->context . '.filter', $filters);
		}

		return parent::loadFormData();
	}*/

	/**
	 * Checks if a given date is valid and in a specified format (YYYY-MM-DD)
	 *
	 * @param   string  $date  Date to be checked
	 *
	 * @return bool
	 */
	/*private function isValidDate($date)
	{
		$date = str_replace('/', '-', $date);

		return (date_create($date)) ? Factory::getDate($date)->format("Y-m-d") : null;
	}*/

	/**
	 * Method to get an array of data items
	 *
	 * @param   string  $data  data
	 *
	 * @return  mixed An array of data on success, false on failure.
	 */
	public function getLikes($data)
	{
		$items = self::getItems();

		$helperPath = JPATH_SITE . '/components/com_jlike/helpers/main.php';

		if (!class_exists('ComjlikeMainHelper'))
		{
			// Require_once $path;
			JLoader::register('ComjlikeMainHelper', $helperPath);
			JLoader::load('ComjlikeMainHelper');
		}

		$ComjlikeMainHelper = new ComjlikeMainHelper;
		$sLibObj            = $ComjlikeMainHelper->getSocialLibraryObject('', $data);

		foreach ($items as $key => $item)
		{
			$item->user       = new stdclass;
			$item->user->id   = $item->userid;
			$item->user->name = Factory::getUser($item->userid)->name;

			$ment_usr = Factory::getUser($item->user->id);

			$link = '';
			$link = $profileUrl = $sLibObj->getProfileUrl($ment_usr);

			if ($profileUrl)
			{
				if (!parse_url($profileUrl, PHP_URL_HOST))
				{
					$link = Uri::root() . substr(Route::_($sLibObj->getProfileUrl($ment_usr)), strlen(Uri::base(true)) + 1);
				}
			}

			$item->user->avatar = $link;
		}

		return $items;
	}
}
