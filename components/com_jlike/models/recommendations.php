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
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;

/**
 * Methods supporting a list of Jlike records.
 *
 * @since  1.5
 */
class JlikeModelRecommendations extends ListModel
{
	public $filter;

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
				'title', 'a.title',
				'assigned_to', 'a.assigned_to',
				'created_date', 'a.created_date',
				'start_date', 'a.start_date',
				'status', 'a.status',
				'due_date', 'a.due_date',
				'assigned_by', 'a.assigned_by',
			);
		}

		$this->filter = 1;

		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   Ordering
	 * @param   string  $direction  Ordering dir
	 *
	 * @since    1.6
	 *
	 * @return  void
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// Initialise variables.
		$app = Factory::getApplication();

		// List state information
		$limit = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'));
		$this->setState('list.limit', $limit);

		$limitstart = $app->getInput()->getInt('limitstart', 0);
		$this->setState('list.start', $limitstart);

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

		// Receive & set filters
		if ($filters = $app->getUserStateFromRequest($this->context . '.filter', 'filter', array(), 'array'))
		{
			foreach ($filters as $name => $value)
			{
				$this->setState('filter.' . $name, $value);
			}
		}

		$ordering = $app->getInput()->get('filter_order');

		if (!empty($ordering))
		{
			$list             = $app->getUserState($this->context . '.list');
			$list['ordering'] = $app->getInput()->get('filter_order');
			$app->setUserState($this->context . '.list', $list);
		}

		$orderingDirection = $app->getInput()->get('filter_order_Dir');

		if (!empty($orderingDirection))
		{
			$list              = $app->getUserState($this->context . '.list');
			$list['direction'] = $app->getInput()->get('filter_order_Dir');
			$app->setUserState($this->context . '.list', $list);
		}

		$list = $app->getUserState($this->context . '.list');

		if (!empty($list['ordering']))
		{
			if (!in_array($list['ordering'], $this->filter_fields))
			{
				$list['ordering'] = 'a.title';
			}

			$this->setState('list.ordering', $list['ordering']);
		}

		if (!empty($list['direction']))
		{
			if (!in_array(strtoupper($list['direction']), array('ASC', 'DESC', '')))
			{
				$list['direction'] = 'desc';
			}

			$this->setState('list.direction', $list['direction']);
		}
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return    DataObjectbaseQuery
	 *
	 * @since    1.6
	 */
	protected function getListQuery()
	{
		$input  = Factory::getApplication()->getInput();

		if ($this->getstate("type", ''))
		{
			$layout = $this->getstate("type", '');
		}
		else
		{
			$layout = $input->get('layout', '', 'WORD');
		}

		// Create a new query object.
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query
			->select(
				$this->getState(
					'list.select', 'DISTINCT a.*'
				)
			);

		$query->from('`#__jlike_todos` AS a');

		// Join over the content for content title & url
		$query->select('c.title AS content_title');
		$query->select('c.url AS content_url');
		$query->join('LEFT', '#__jlike_content AS c ON c.id=a.content_id');

		// Join over the users for the checked out user.
		$query->select('uc.name AS editor');
		$query->join('LEFT', '#__users AS uc ON uc.id=a.checked_out');

		// Join over the created by field 'created_by'
		$query->join('LEFT', '#__users AS created_by ON created_by.id = a.created_by');

		// Get only assignment of logged in user

		if ($this->getstate("user_id", ''))
		{
			$userid = $this->getstate("user_id", '');
		}
		else
		{
			$userid = Factory::getUser()->id;
		}

		// Get content Id related data

		if ($this->getstate("content_id", ''))
		{
			$content_id = $this->getstate("content_id", '');
		}
		else
		{
			$content_id = $input->getstate("content_id", '');
		}

		if (!empty($content_id))
		{
			$query->where('a.content_id = ' . $db->quote($content_id));
		}

		if ($this->getstate("assigned_by", ''))
		{
			$assigned_by = $this->getstate("assigned_by", '');
			$query->where('a.assigned_by = ' . $db->quote($assigned_by));
		}

		if ($this->getstate("assigned_to", ''))
		{
			$assigned_to = $this->getstate("assigned_to", '');
			$query->where('a.assigned_to = ' . $db->quote($assigned_to));
		}

		if ($this->getstate("client", ''))
		{
			$client = $this->getstate("client", '');
			$query->where('c.element = ' . $db->quote($client));
		}

		if ($this->getstate("element_id", ''))
		{
			$elementId = $this->getstate("element_id", '');
			$query->where('c.element_id = ' . $db->quote($elementId));
		}

		// Get content Id related data
		$status = $input->getstate("status", '');

		if ($status)
		{
			$query->where('a.status = ' . $db->quote($status));
		}

		if (!$userid)
		{
			$query->where('a.assigned_by = ' . 0);
			$query->where('a.assigned_to = ' . 0);
		}

		switch ($layout )
		{
			case 'recommendbyme':
				$query->where('a.assigned_by = ' . $db->quote($userid));
				$query->where('a.type = "reco"');
			break;

			case 'recommendtome':
				$query->where('a.assigned_to = ' . $db->quote($userid));
				$query->where('a.type = "reco"');
			break;

			case 'sentassign':
				$query->where('a.assigned_by = ' . $db->quote($userid));
				$query->where('a.type = "assign"');
			break;

			case 'myassign':
				$query->where('a.assigned_to = ' . $db->quote($userid));
				$query->where('a.type = "assign"');
			break;
		}

		$state = $input->getstate("state", '');

		if ($state !== '')
		{
			$query->where($db->quoteName('a.state') . "=" . $db->quote($state));
		}

		// Filter by search in title
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('a.id = ' . (int) substr($search, 3));
			}
			else
			{
				$search = $db->quote('%' . $db->escape($search, true) . '%');
			}
		}

		if ($this->filter == 0)
		{
		}
		else
		{
			// Add the list ordering clause.
			$orderCol  = $this->state->get('list.ordering');
			$orderDirn = $this->state->get('list.direction');

			if ($orderCol && $orderDirn)
			{
				$query->order($db->escape($orderCol . ' ' . $orderDirn));
			}
		}

		return $query;
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

		// Get the full from of status
		if ($items)
		{
			foreach ($items as $k => $item)
			{
				switch ($item->status)
				{
					case 'S';
						$item->status_title = Text::_("COM_JLIKE_STARTED");
					break;

					case 'C';
						$item->status_title = Text::_("COM_JLIKE_COMPLETED");
					break;

					case 'I';
						$item->status_title = Text::_("COM_JLIKE_INCOMPLETE");
					break;
				}
			}
		}

		return $items;
	}

	/**
	 * Method to get an array of data items
	 *
	 * @param   array  $data  data
	 *
	 * @return  mixed An array of data on success, false on failure.
	 */
	public function getTodos($data)
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
			// Assigned by user
			$ass_by = new stdClass;
			$ass_by->id  = $item->assigned_by;
			$ass_by->name = Factory::getUser($item->assigned_by)->name;

			$ment_usr = Factory::getUser($item->assigned_by);

			$link = '';
			$link = $profileUrl = $sLibObj->getProfileUrl($ment_usr);

			if ($profileUrl)
			{
				if (!parse_url($profileUrl, PHP_URL_HOST))
				{
					$link = Uri::root() . substr(Route::_($sLibObj->getProfileUrl($ment_usr)), strlen(Uri::base(true)) + 1);
				}
			}

			$ass_by->profile_link = $link;
			$ass_by->avatar       = $sLibObj->getAvatar($ment_usr, 50);

			unset($item->assigned_by);
			$items[$key]->assigned_by = $ass_by;

			// Assigned to user
			$ass_to = new stdClass;
			$ass_to->id = $item->assigned_to;
			$ass_to->name = Factory::getUser($item->assigned_to)->name;

			$ment_usr = Factory::getUser($item->assigned_to);

			$link = '';
			$link = $profileUrl = $sLibObj->getProfileUrl($ment_usr);

			if ($profileUrl)
			{
				if (!parse_url($profileUrl, PHP_URL_HOST))
				{
					$link = Uri::root() . substr(Route::_($sLibObj->getProfileUrl($ment_usr)), strlen(Uri::base(true)) + 1);
				}
			}

			$ass_to->profile_link = $link;
			$ass_to->avatar       = $sLibObj->getAvatar($ment_usr, 50);
			unset($item->assigned_to);
			$items[$key]->assigned_to = $ass_to;
		}

		return $items;
	}

	/**
	 * Get count of element.
	 *
	 * @param   array  $contentid  id
	 *
	 * @return	total count.
	 *
	 * @since	1.6
	 */
	public function getTotalRecommendation($contentid)
	{
		$db = Factory::getDBO();
		$app = Factory::getApplication();

		try
		{
			$query = $db->getQuery(true);

			$query->select("COUNT(`id`)");
			$query->from($db->quoteName('#__jlike_todos'));

			if (!empty($contentid))
			{
				$query->where("content_id=$contentid");
			}

			$db->setQuery($query);

			return $count = $db->loadResult();
		}
		catch (Exception $e)
		{
			echo $e->getMessage();
			$app->enqueueMessage($e->getMessage(), "error");
		}
	}

	/**
	 * Overrides the default function to check Date fields format, identified by
	 * "_dateformat" suffix, and erases the field if it's not correct.
	 *
	 * @return  formdata
	 */
	protected function loadFormData()
	{
		$app              = Factory::getApplication();
		$filters          = $app->getUserState($this->context . '.filter', array());
		$error_dateformat = false;

		foreach ($filters as $key => $value)
		{
			if (strpos($key, '_dateformat') && !empty($value) && !$this->isValidDate($value))
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
	}

	/**
	 * Method to get an array of data items
	 *
	 * @param   INt  $data  Contains the content_id
	 *
	 * @return  mixed An array of data on success, false on failure.
	 */
	public function getrecommendations($data)
	{
		$items = self::getItems();

		if (!empty($items))
		{
			return $items;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Checks if a given date is valid and in an specified format (YYYY-MM-DD)
	 *
	 * @param   string  $date  Contains the date to be checked
	 *
	 * @return express
	 */
	private function isValidDate($date)
	{
		return preg_match("/^(19|20)\d\d[-](0[1-9]|1[012])[-](0[1-9]|[12][0-9]|3[01])$/", $date) && date_create($date);
	}

	/**
	 * Method to get the starting number of items for the data set.
	 *
	 * @return  integer  The starting number of items available in the data set.
	 *
	 * @since   12.2
	 */
	public function getStart()
	{
		return $this->getState('list.start');
	}
}
