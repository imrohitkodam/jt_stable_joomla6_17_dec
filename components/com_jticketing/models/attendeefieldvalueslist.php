<?php
/**
 * @package     JTicketing
 * @subpackage  com_jticketing
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\ListModel;

/**
 * Model for showing list of attendee field values
 *
 * @package     JTicketing
 * @subpackage  component
 * @since       2.7.0
 */
class JticketingModelAttendeefieldvalueslist extends ListModel
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see        JController
	 * @since      2.7.0
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'afv.id',
				'attendee_id', 'afv.attendee_id',
				'field_id', 'afv.field_id',
				'field_value', 'afv.field_value',
				'field_source', 'afv.field_source',
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
	 * @since    2.7.0
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		$app  = Factory::getApplication();
		$list = $app->getUserState($this->context . '.list');

		$ordering  = isset($list['filter_order'])     ? $list['filter_order']     : null;
		$direction = isset($list['filter_order_Dir']) ? $list['filter_order_Dir'] : null;

		$list['limit']     = $app->getUserStateFromRequest($this->context . '.list.limit', 'limit', $app->get('list_limit'), 'uint');
		$list['start']     = $app->getInput()->getInt('start', 0);
		$list['ordering']  = $ordering;
		$list['direction'] = $direction;

		$app->setUserState($this->context . '.list', $list);
		$app->getInput()->set('list', null);

		$context = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $context);

		// List state information.
		parent::populateState('afv.id', 'asc');
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return   JDatabaseQuery
	 *
	 * @since    2.7.0
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select('afv.*');

		$query->from($db->qn('#__jticketing_attendee_field_values', 'afv'));

		// Filter by search in title
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('afv.id = ' . (int) substr($search, 3));
			}
			else
			{
				$search = $db->Quote('%' . $db->escape($search, true) . '%');

				$conditions = array();
				$conditions[] = $db->qn('afv.attendee_id') . 'LIKE' . $search;
				$conditions[] = $db->qn('afv.field_id') . 'LIKE' . $search;
				$conditions[] = $db->qn('afv.field_value') . 'LIKE' . $search;
				$conditions[] = $db->qn('afv.field_source') . 'LIKE' . $search;
				$query->orWhere($conditions);
			}
		}

		// Filtering attendee id
		$filterAttendeeId = $this->state->get("filter.attendee_id");

		if (!empty($filterAttendeeId))
		{
			$query->where($db->qn('afv.attendee_id') . ' = ' . $db->escape($filterAttendeeId));
		}

		// Filtering field id
		$filterFieldId = $this->state->get("filter.field_id");

		if (!empty($filterFieldId))
		{
			$query->where($db->qn('afv.field_id') . ' = ' . $db->escape($filterFieldId));
		}

		// Filtering field id
		$filterSource = $this->getState("filter.field_source");

		if (!empty($filterSource))
		{
			if ($filterSource === 'com_jticketing')
			{
				$query->select($db->qn('af.name'));
				$query->join('left', $db->qn('#__jticketing_attendee_fields', 'af')
						. 'ON (' . $db->qn('afv.field_id') . ' = ' . $db->qn('af.id') . ')');
			}
			elseif ($filterSource === 'com_tjfields.com_jticketing.ticket')
			{
				$query->select($db->qn('af.name'));
				$query->join('left', $db->qn('#__tjfields_fields', 'af')
						. 'ON (' . $db->qn('afv.field_id') . ' = ' . $db->qn('af.id') . ')');
				$query->where($db->qn('af.client') . ' = ' . $db->q('com_jticketing.ticket'));
			}

			$query->where($db->qn('afv.field_source') . ' = ' . $db->q($filterSource));
		}

		// Add the list ordering clause.

		$orderCol  = $this->getState('list.ordering', 'id');
		$orderDirn = $this->getState('list.direction', 'DESC');

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
		$items    = parent::getItems();
		$newItems = array();

		foreach ($items as $item)
		{
			// Add the name property in the field value object to identify field correctly.
			$newItem       = JT::AttendeeFieldValues($item->id);
			$newItem->name = $item->name;
			$newItems[]    = $newItem;
		}

		return $newItems;
	}
}
