<?php
/**
 * @package     JTicketing
 * @subpackage  com_jticketing
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\ListModel;

/**
 * Methods supporting a list of coupons.
 *
 * @since  2.4.0
 */
class JticketingModelCategories extends ListModel
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     \JController
	 * @since   2.4.0
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
			'name', 'a.name',
			'limit', 'a.limit',
			);
		}

		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   Coupon order
	 * @param   string  $direction  Coupon Order direction
	 *
	 * @return void
	 *
	 * @throws Exception
	 *
	 * @since  2.4.0
	 */
	protected function populateState($ordering = 'a.id', $direction = 'DESC')
	{
		$app  = Factory::getApplication();

		if ($filters = $app->getUserStateFromRequest($this->context . '.filter', 'filter', array(), 'array'))
		{
			foreach ($filters as $name => $value)
			{
				$this->setState('filter.' . $name, $value);
			}
		}

		$this->setState('filter_catid', $app->getInput()->get('cat_id', '', 'INT'));

		parent::populateState($ordering, $direction);
	}

	/**
	 * Get the query for retrieving a list of coupons to the model state.
	 *
	 * @return  \JDatabaseQuery
	 *
	 * @since   2.4.0
	 */
	protected function getListQuery()
	{
		$user = Factory::getUser();
		$app  = Factory::getApplication();

		// Create a new query object.
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select('*');
		$query->from($db->quoteName('#__categories', 'a'));
		$query->where($db->quoteName('extension') . '=' . $db->quote('com_jticketing'));
		$catIdForChild = $this->getState('filter_catid');
		$menuParams = $app->getParams('com_jticketing');
		$show_child_categories = $menuParams->get('show_child_categories');

		if (!$show_child_categories)
		{
			if (!$catIdForChild)
			{
				$query->where($db->quoteName('parent_id') . '=' . 1);
			}
			else 
			{
				$query->where($db->quoteName('parent_id') . '=' . (int) $catIdForChild);
			}
		}

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
				$query->where('( a.title LIKE ' . $search . ' )');
			}
		}

		// Add the list ordering clause.
		$orderCol  = $this->state->get('list.ordering', 'a.id');
		$orderDirn = $this->state->get('list.direction', 'DESC');

		$query->order($db->escape($orderCol) . ' ' . $db->escape($orderDirn));

		return $query;
	}
}
