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
use Joomla\Data\DataObject;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;


JLoader::import('components.com_jlike.models.todo', JPATH_SITE);
JLoader::import('components.com_jlike.models.pathuser', JPATH_SITE);
use Joomla\Utilities\ArrayHelper;

/**
 * JLike model Pathuser.
 *
 * @since  1.6
 */
class JLikeModelPathUsers extends ListModel
{
	private $item = null;

	protected $db;

	protected $loggedInUser;

	/**
	 * Constructor
	 *
	 * @throws  Exception
	 */
	public function __construct()
	{
		$config = array();

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

		$catId  = $app->getInput()->get('catId', '', 'INT');

		if (!empty($catId))
		{
			$this->setState('filter.cat_id', $catId);
		}

		// Get path filters
		$pathFilter = $app->getInput()->get('path_filters', 'all', 'WORD');

		$this->setState('filter.path_filters', $pathFilter);

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
		$query->select('p.path_id, p.path_title, p.path_description, p.params, p.path_type, p.subscribe_start_date, p.subscribe_end_date');
		$query->select('pul.status as path_status');
		$query->from($this->db->quoteName('#__jlike_paths', 'p'));
		$query->join('LEFT', $this->db->quoteName('#__jlike_pathnode_graph', 'png') .
		' ON (' . $this->db->quoteName('png.path_id') . ' = ' . $this->db->quoteName('p.path_id') . ')');
		$query->join('LEFT', $this->db->quoteName('#__jlike_path_user', 'pul') .
					' ON (' . $this->db->quoteName('p.path_id') . ' = ' . $this->db->quoteName('pul.path_id') . ')');
		$query->where($this->db->quoteName('p.state') . ' = ' . $this->db->quote(1));
		$query->where($this->db->quoteName('png.lft') . ' = ' . $this->db->quote(0));
		$query->where($this->db->quoteName('png.isPath') . ' = ' . $this->db->quote(1));

		// Filter by content id
		$depth = $this->getState('filter.depth');

		if ($depth === '0' || !empty($depth))
		{
			$query->where($this->db->quoteName('p.depth') . '=' . $this->db->quote($depth));
		}

		// Filter by content id
		$pathId = $this->getState('filter.path_id');

		if ($pathId)
		{
			$query->where($this->db->quoteName('graph.node') . '=' . (int) $pathId);
		}

		// Filter by category id
		$catId = $this->getState('filter.cat_id');

		if ($catId)
		{
			$query->where($this->db->quoteName('p.category_id') . '=' . (int) $catId);
		}

		// Filter by paths filter
		$pathFilter = $this->getState('filter.path_filters');

		// Fetch logged-in user's subscribed paths
		$currentUtcDate = Factory::getDate()->toSql();

		switch ($pathFilter)
		{
			case 'my':
				$query->join('INNER', $this->db->quoteName('#__jlike_path_user', 'pu') .
					' ON (' . $this->db->quoteName('p.path_id') . ' = ' . $this->db->quoteName('pu.path_id') . ')');
				$query->where($this->db->quoteName('pu.user_id') . ' = ' . (int) $this->loggedInUser->id);
				break;

			case "open":
				$query->where($this->db->quoteName('p.subscribe_start_date') . ' <= ' . $this->db->q($currentUtcDate));
				$query->where($this->db->quoteName('p.subscribe_end_date') . ' >= ' . $this->db->q($currentUtcDate));
				break;

			case "closed":
				$query->where($this->db->quoteName('p.subscribe_start_date') . ' < ' . $this->db->q($currentUtcDate));
				$query->where($this->db->quoteName('p.subscribe_end_date') . ' < ' . $this->db->q($currentUtcDate));
				break;

			case "opening_soon":
				$query->where($this->db->quoteName('p.subscribe_start_date') . ' > ' . $this->db->q($currentUtcDate));
				$query->where($this->db->quoteName('p.subscribe_end_date') . ' > ' . $this->db->q($currentUtcDate));
				break;

			default:
				break;
		}

		// Filter by access level.
		$levels = implode(',', $this->loggedInUser->getAuthorisedViewLevels());
		$query->where('p.access IN (' . $levels . ')');

		$query->order($this->db->quoteName('p.path_id') . ' ASC');

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

		// TODO : This code can be removable;
		foreach ($items as $item)
		{
			$item->isSubscribedPath = $pathUserModel->getPathUserDetails($item->path_id, $this->loggedInUser->id);

			$pathModel = BaseDatabaseModel::getInstance('Path', 'JLikeModel');
			$pathModel->getData($item->path_id);

			$item->isPathOpenToSubscribe = $pathModel->isPathOpenForSubscription();
		}

		return $items;
	}
}
