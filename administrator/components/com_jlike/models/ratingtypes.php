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

/**
 * JLike Model Rating Types
 *
 * @since  3.0.0
 */
class JLikeModelRatingtypes extends ListModel
{
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
				'id', 'a.id',
				'client', 'a.client',
				'code', 'a.code',
				'is_default', 'a.is_default',
				'title', 'a.title',
				'state', 'a.state',
				'show_title', 'a.show_title',
				'title_required', 'a.title_required',
				'show_rating', 'a.show_rating',
				'rating_required', 'a.rating_required',
				'rating_scale', 'a.rating_scale',
				'show_review', 'a.show_review',
				'review_required', 'a.review_required',
				'tjucm_type_id', 'a.tjucm_type_id',
				'show_all_rating', 'a.show_all_rating',
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
	 * @since  3.0.0
	 */
	protected function populateState($ordering = 'a.client', $direction = 'asc')
	{
		// Initialise variables.
		$app = Factory::getApplication('administrator');

		// Load the filter state.
		$search = $app->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$this->setState('filter.active', $this->getUserStateFromRequest($this->context . '.filter.active', 'filter_active', '', 'cmd'));

		$published = $app->getUserStateFromRequest($this->context . '.filter.state', 'filter_published', '', 'string');
		$this->setState('filter.state', $published);

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

		// Create the base select statement.
		$query->select('a.*');
		$query->from($db->quoteName('#__jlike_rating_types', 'a'));

		// Filter by id
		$id = $this->getState('filter.id');

		if (is_numeric($id))
		{
			$query->where($db->quoteName('a.id') . ' = ' . (int) $id);
		}

		// Filter by Code
		$code = $this->getState('filter.code');

		if (!empty($code))
		{
			$query->where($db->quoteName('a.code') . ' = ' . $db->quote($code));
		}

		// Filter by published state
		$published = $this->getState('filter.state');

		if (is_numeric($published))
		{
			$query->where('a.state = ' . (int) $published);
		}
		elseif ($published === '')
		{
			$query->where('(a.state IN (0, 1))');
		}

		// Filter by search in title
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			$like = $db->quote('%' . $search . '%');
			$query->where($db->quoteName('a.client') . ' LIKE ' . $like . 'OR' . $db->quoteName('a.title') . 'LIKE ' . $like);
		}

		// Add the list ordering clause.
		$query->order($db->escape($this->getState('list.ordering', 'a.id')) . ' ' . $db->escape($this->getState('list.direction', 'DESC')));

		return $query;
	}

	/**
	 * Method to get the rating type data.
	 *
	 * @return  array  An array in id => title format.
	 *
	 * @since   3.0.0
	 */
	public function getRatingTypesById()
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true);

		$query->select(array('id', 'title'))
			->from($db->quoteName('#__jlike_rating_types'));
		$db->setQuery($query);

		return $db->loadAssocList('id', 'title');
	}
}
