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
use Joomla\CMS\Component\ComponentHelper;



/**
 * JlikeModeljlike_likes
 *
 * @package     Jlike
 * @subpackage  site
 * @since       2.2
 */
class JlikeModelAnnotations extends ListModel
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @since    1.6
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id',
				'a.id',
				'ordering',
				'a.ordering',
				'state',
				'a.state',
				'user_id',
				'a.user_id',
				'content_id',
				'a.content_id',
				'annotation',
				'a.annotation',
				'privacy',
				'a.privacy',
				'annotation_date',
				'a.annotation_date',
				'parent_id',
				'a.parent_id'

			);
		}

		parent::__construct($config);
	}

	/**
	 *  Method to auto-populate the model state.  Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   Ordering to fetch rec.
	 * @param   string  $direction  Direction to fetch rec.
	 *
	 * @return  null
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// Initialise variables.
		$app = Factory::getApplication('administrator');

		// Load the filter state.
		$search = $app->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$published = $app->getUserStateFromRequest($this->context . '.filter.state', 'filter_published', '', 'string');
		$this->setState('filter.state', $published);

		// Load the parameters.
		$params = ComponentHelper::getParams('com_jlike');
		$this->setState('params', $params);

		// List state information.
		parent::populateState('a.id', 'asc');
	}

	/**
	 * Method to get a store id based on model configuration state. This is necessary because the model is
	 * used by the component and different modules that might need different sets of data or different ordering requirements.
	 *
	 * @param   integer  $id  id.
	 *
	 * @return  return store id.
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.state');

		return parent::getStoreId($id);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return	DataObjectbaseQuery
	 *
	 * @since	1.6
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select($this->getState('list.select', 'a.*'));
		$query->from('`#__jlike_annotations` AS a');

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

		// Get only comments, Exclude notes
		$query->where('(a.note=0)');

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
				$search = $db->Quote('%' . $db->escape($search, true) . '%');
				$query->where('a.annotation LIKE ' . $search);
			}
		}

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
	 * Method to get an items.
	 *
	 * @return	mixed	Object Item list
	 */
	public function getItems()
	{
		$items = parent::getItems();

		return $items;
	}
}
