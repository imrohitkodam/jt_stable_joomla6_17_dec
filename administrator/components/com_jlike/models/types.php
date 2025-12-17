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
 * JLike Model Paths
 *
 * @since  1.6
 */
class JLikeModelTypes extends ListModel
{
	private $item = null;

	protected $searchInFields = array('text','a.type_title','someotherfieldtosearchin');
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     BaseDatabaseModel
	 * @since   12.2
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'path_type_id',
				'type_title',
				'identifier',
				'params',
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
	 * @since   12.2
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// Initialise variables.
		$app = Factory::getApplication();

		// Load the filter state.
		$search = $app->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');

		// Omit double (white-)spaces and set state
		$this->setState('filter.search', preg_replace('/\s+/', ' ', $search ? $search : ''));

		$orderCol = $app->getInput()->get('filter_order', 'ASC');

		if (!(in_array($orderCol, $this->filter_fields)))
		{
			$orderCol = 'path_type_id';
		}

		$this->setState('list.ordering', $orderCol);
		$listOrder = $app->getInput()->get('filter_order_Dir', 'ASC');

		if (!in_array(strtoupper($listOrder), array('ASC', 'DESC', '')))
		{
			$listOrder = 'ASC';
		}

		$this->setState('list.direction', $listOrder);

		parent::populateState('null', 'asc');
	}

	/**
	 * Method to get a DataObjectbaseQuery object for retrieving the data set from a database.
	 *
	 * @return  DataObjectbaseQuery  A DataObjectbaseQuery object to retrieve the data set.
	 *
	 * @since   12.2
	 */
	protected function getListQuery()
	{
		// Initialize variables.
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('*')->from($db->quoteName('#__jlike_path_type'));

		// Filter by search in title
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
		$like = $db->quote('%' . $search . '%');
		$query->where($db->quoteName('type_title') . ' LIKE ' . $like);
		}

		// Query for ordering of the elements
		$query->order($db->escape($this->getState('list.ordering', 'null')) . ' ' . $db->escape($this->getState('list.direction', 'null')));

		return $query;
	}

	/**
	 * Method to delete path.
	 *
	 * @param   Array  $path_type_ids  array of path_type_id id
	 *
	 * @return  void
	 *
	 * @since   12.2
	 */
	public function delete($path_type_ids)
	{
		if (!empty($path_type_ids))
		{
			$db    = Factory::getDbo();
			$deleteQuery = $db->getQuery(true);
			$deleteQuery->delete($db->quoteName('#__jlike_path_type'));
			$deleteQuery->where($db->quoteName('path_type_id') . ' IN ( ' . implode(',', $path_type_ids) . ' )');
			$db->setQuery($deleteQuery);
			$result = $db->execute();
		}
	}
}
