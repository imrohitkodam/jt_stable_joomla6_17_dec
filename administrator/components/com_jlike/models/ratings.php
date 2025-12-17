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
use Joomla\Data\DataObject;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

if (file_exists(JPATH_SITE . '/components/com_jlike/helpers/main.php')) {
	require_once JPATH_SITE . '/components/com_jlike/helpers/main.php';
}

/**
 * JLike Model Ratings
 *
 * @since  3.0.0
 */
class JLikeModelRatings extends ListModel
{
	private $item = null;

	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     BaseDatabaseModel
	 * @since   3.0.0
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'a.`id`',
				'created_date', 'a.created_date',
				'rating', 'a.rating',
				'name', 'u.name',
				'state', 'a.state',
				'element', 'c.element',
				'content_id', 'a.content_id',
				'c.title', 'a.title',
				'a.review'
			);
		}

		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * This method should only be called once per instantiation and is designed
	 * to be called on the first call to the getState() method unless the model
	 * configuration flag to ignore the request is set.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 *
	 * @since   3.0.0
	 */
	protected function populateState($ordering = 'a.id', $direction = 'desc')
	{
		// Initialise variables.
		$app = Factory::getApplication();

		$orderCol = $app->getInput()->get('filter_order', 'DESC');

		if (!(in_array($orderCol, $this->filter_fields)))
		{
			$orderCol = 'a.id';
		}

		$this->setState('list.ordering', $orderCol);

		$listOrder = $app->getInput()->get('filter_order_Dir', 'DESC');

		if (!in_array(strtoupper($listOrder), array('ASC', 'DESC', '')))
		{
			$listOrder = 'DESC';
		}

		$this->setState('list.direction', $listOrder);

		if ($filters = $app->getUserStateFromRequest($this->context . '.filter', 'filter', array(), 'array'))
		{
			foreach ($filters as $name => $value)
			{
				// Exclude if blacklisted
				if (!in_array($name, $this->filterBlacklist))
				{
					$this->setState('filter.' . $name, $value);
				}
			}
		}

		parent::populateState($ordering, $direction);
	}

	/**
	 * Method to get a DataObjectbaseQuery object for retrieving the data set from a database.
	 *
	 * @return  DataObjectbaseQuery  A DataObjectbaseQuery object to retrieve the data set.
	 *
	 * @since   3.0.0
	 */
	protected function getListQuery()
	{
		// Initialize variables.
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$limit = $this->getState('list.limit');
		$start = $this->getState('list.start');

		// Create the base select statement.
		$query->select('a.*, c.title as content_title');
		$query->from($db->quoteName('#__jlike_ratings', 'a'));
		$query->join(
					'LEFT', $db->quoteName('#__users', 'u') . ' ON ' . $db->quoteName('u.id') . ' = ' . $db->quoteName('a.submitted_by')
					);

		$query->join(
					'LEFT', $db->quoteName('#__jlike_content', 'c') . ' ON ' . $db->quoteName('a.content_id') . ' = ' . $db->quoteName('c.id')
					);

		$filterContentId = $this->getState('filter.content_id');

		if ($filterContentId)
		{
			$query->where($db->quoteName('a.content_id') . '=' . $db->quote($filterContentId));
		}

		$filterElement = $this->getState('filter.element');

		if ($filterElement)
		{
			$query->where($db->quoteName('c.element') . '=' . $db->quote($filterElement));
		}

		// Filter by rating type
		$ratingTypeId = $this->getState('filter.rating_type_id');

		if ($ratingTypeId)
		{
			$query->where($db->quoteName('a.rating_type_id') . '=' . (int) $ratingTypeId);
		}

		$submittedBy = $this->getState('filter.submitted_by');

		if ($submittedBy)
		{
			$query->where($db->quoteName('a.submitted_by') . '=' . $db->quote($submittedBy));
		}

		// Filter by search in title
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			$like = $db->quote('%' . $search . '%');
			$query->where(
				$db->quoteName('u.name') . ' LIKE ' . $like . ' OR ' . $db->quoteName('a.title') . ' LIKE ' . $like .
				' OR ' . $db->quoteName('a.review') . ' LIKE ' . $like
			);
		}

		if ($limit != 0)
		{
			$query->setLimit($limit, $start);
		}

		// Add the list ordering clause.
		$query->order($db->escape($this->getState('list.ordering', 'a.id')) . ' ' . $db->escape($this->getState('list.direction', 'DESC')));

		return $query;
	}

	/**
	 * Method to get an items.
	 *
	 * @return	mixed	Object Item list
	 */
	public function getItems()
	{
		$items = parent::getItems();

		$ComjlikeMainHelper = new ComjlikeMainHelper;

		foreach ($items as $item)
		{
			$item->userName = Factory::getUser($item->submitted_by)->name;

			// Get date in local time zone
			$item->created_date = HTMLHelper::date($item->created_date, Text::_('COM_JLIKE_DATE_FORMAT_SHOW_LONG'));

			// Get extra date info
			$item->created_day = date_format(date_create($item->created_date),  Text::_('COM_JLIKE_DATE_FORMAT_DAY'));
			$item->created_date_month = date_format(date_create($item->created_date), Text::_('COM_JLIKE_DATE_FORMAT_DATE_MONTH_YEAR'));

			$sLibObj  = $ComjlikeMainHelper->getSocialLibraryObject('', array("plg_type" => '', "plg_name" => ''));
			$item->avtar   = $sLibObj->getAvatar(Factory::getUser($item->submitted_by), 50);
		}

		return $items;
	}
}
