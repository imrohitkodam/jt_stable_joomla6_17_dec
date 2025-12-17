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

JLoader::import('components.com_jlike.models.pathuser', JPATH_SITE);

/**
 * JLike Model Paths
 *
 * @since  1.6
 */
class JLikeModelPaths extends ListModel
{
	private $item = null;

	protected $db;

	protected $loggedInUser;

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
				'id', 'a.path_id',
				'title','a.path_title',
				'description','a.path_description'
			);
		}

		$this->db = Factory::getDbo();
		$this->loggedInUser = Factory::getUser();

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

		// List state information
		$limit = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->get('list_limit'));
		$this->setState('list.limit', $limit);

		$limitstart = $app->getUserStateFromRequest('limitstart', 'limitstart', 0);
		$this->setState('list.start', $limitstart);

		// List state information.
		parent::populateState($ordering, $direction);
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
		// Create a new query object.
		$query = $this->db->getQuery(true);

		// Select the required fields from the table.
		$query->select('p.*');

		$query->from($this->db->qn('#__jlike_paths', 'p'));

		// Filter by state
		$state = $this->getState('filter.state');

		if (!empty($state))
		{
			$query->where($this->db->quoteName('p.state') . '=' . $this->db->quote($state));
		}

		// Filter by path type
		$type = $this->getState('filter.path_type');

		if (!empty($type))
		{
			$query->where($this->db->quoteName('p.path_type') . '=' . $this->db->quote($type));
		}

		// Filter by search in title
		$catId = $this->getState('filter.category_id');

		if (!empty($catId))
		{
			$query->where($this->db->quoteName('category_id') . ' IN (' . $this->db->escape($catId) . ')');
		}

		// Filter by depth
		$depth = $this->getState('filter.depth');

		if ($depth === '0' || !empty($depth))
		{
			$query->where($this->db->quoteName('depth') . ' = ' . (int) $depth);
		}

		// Filter by pathId
		$pathId = $this->getState('filter.path_id');

		if (!empty($pathId))
		{
			$query->where($this->db->quoteName('p.path_id') . '=' . (int) $pathId);
		}

		// Filter by access level.
		$levels = implode(',', $this->loggedInUser->getAuthorisedViewLevels());
		$query->where('p.access IN (' . $levels . ')');

		return $query;
	}

	/**
	 * Method to get user subscribed path.
	 *
	 * @return Object|boolean Object on success, false on failure.
	 *
	 * @throws Exception
	 */
	public function getItems()
	{
		$items = parent::getItems();
		$pathUserModel = BaseDatabaseModel::getInstance('PathUser', 'JLikeModel');
		$pathModel = BaseDatabaseModel::getInstance('Path', 'JLikeModel');

		foreach ($items as $item)
		{
			$item->isSubscribedPath = $pathUserModel->getPathUserDetails($item->path_id, $this->loggedInUser->id);
			$item->isPathOfPaths = $pathModel->isPathOfPaths($item->path_id);
		}

		return $items;
	}
}
