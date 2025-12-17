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
use Joomla\CMS\Table\Table;

/**
 * Methods supporting a list of coupons.
 *
 * @since  2.4.0
 */
class JticketingModelCoupons extends ListModel
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
			'id', 'a.id',
			'state', 'a.state',
			'ordering', 'a.ordering',
			'name', 'a.name',
			'code', 'a.code',
			'value', 'a.value',
			'limit', 'a.limit',
			'valid_from', 'a.valid_from',
			'valid_to','a.valid_to',
			'created_by', 'a.created_by',
			'used', 'a.used',
			'vendor_id', 'a.vendor_id',
			'max_per_user', 'a.max_per_user',
			'group_discount_tickets', 'a.group_discount_tickets',
			'vendors',
			'event_integration_id'
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

		// Create a new query object.
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select($this->getState('list.select', 'DISTINCT a.*'));
		$query->from('`#__jticketing_coupon` AS a');

		// Join over the users for the checked out user.
		$query->select('uc.name AS editor');

		$query->join('LEFT', '#__users AS uc ON uc.id=a.checked_out');

		// Check if the user is a guest (not logged in)
		if ($user->guest)
		{
			// If the user is not logged in, return an empty query
			$query->where('0');
			return $query;
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
				$search = $db->Quote('%' . $db->escape($search, true) . '%');
				$query->where('( a.name LIKE ' . $search . ' OR a.code LIKE ' . $search .
				' OR a.value LIKE ' . $search . ' OR a.used LIKE ' . $search . ' OR a.limit LIKE ' . $search .
				' )');
			}
		}

		// Allow seeing only own created coupons list to the vendor, admin can seen all coupons.
		if (!$user->authorise('core.admin'))
		{
			$tjvendorFrontHelper           = new TjvendorFrontHelper;
			$vendorId    = $tjvendorFrontHelper->checkVendor($user->id, 'com_jticketing'); 

			if($vendorId)
				$query->where('(a.created_by = ' . (int) $user->id . ' OR a.vendor_id = ' . (int) $vendorId . ' )');
			else
				// Registered users can see all coupons, assuming 'state = 1' means "published"
				$query->where('(a.state = 1)');
		}

		// Filtering by Vendors
		if ($this->state->get('filter.vendors') != '')
		{
			$query->where('a.vendor_id = ' . (int) $this->state->get("filter.vendors"));
		}

		// Filtering by event
		if ($this->state->get('event_integration_id', '') != '')
		{
			$query->where('FIND_IN_SET(' . (int) $this->state->get("event_integration_id") . ', a.event_ids)');
		}

		// Filtering by State
		if ($this->state->get('filter.state') != '')
		{
			$query->where('a.state = ' . (int) $this->state->get("filter.state"));
		}

		// Add the list ordering clause.
		$orderCol  = $this->state->get('list.ordering', 'a.id');
		$orderDirn = $this->state->get('list.direction', 'DESC');

		$query->order($db->escape($orderCol) . ' ' . $db->escape($orderDirn));

		return $query;
	}

	/**
	 * Method return logged-in user orders count, where user has applied coupon
	 *
	 * @param   array  $options  options include state, coupon code and logged-in user id
	 *
	 * @return  integer
	 *
	 * @since   2.4.0
	 */
	public function getUsedCouponCount($options)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('o.id');
		$query->from($db->quoteName('#__jticketing_order', 'o'));
		$query->where($db->quoteName('o.coupon_discount') . "> 0");
		$query->where($db->quoteName('o.status') . 'IN("C","RF","RV")');
		$query->where($db->quoteName('o.coupon_code') . " = " . $db->quote($db->escape($options['code'])));
		$query->where($db->quoteName('o.user_id') . " = " . $db->quote($options['userId']));

		$db->setQuery($query);

		return count($db->loadObjectList());
	}

	/**
	 * Method getTable.
	 *
	 * @param   String  $type    Type
	 * @param   String  $prefix  Prefix
	 * @param   Array   $config  Config
	 *
	 * @return Id
	 *
	 * @since    1.8.1
	 */
	public function getTable($type = 'coupon', $prefix = 'JticketingTable', $config = array())
	{
		$app = Factory::getApplication();

		if ($app->isClient("administrator"))
		{
			return Table::getInstance($type, $prefix, $config);
		}
		else
		{
			$this->addTablePath(JPATH_ADMINISTRATOR . '/components/com_jticketing/tables');

			return Table::getInstance($type, $prefix, $config);
		}
	}

	/**
	 * Method publish.
	 *
	 * @param   Integer  $id     Id
	 * @param   String   $state  State
	 *
	 * @return void
	 *
	 * @since    1.8.1
	 */
	public function publish($id, $state)
	{
		$table = $this->getTable();
		$table->load($id);
		$table->state = $state;

		return $table->store();
	}

	/**
	 * Method delete.
	 *
	 * @param   Integer  $id  Id
	 *
	 * @return void
	 *
	 * @since    1.8.1
	 */
	public function delete($id)
	{
		$table = $this->getTable();

		return $table->delete($id);
	}

	/**
	* Get applicable coupons based on event ID and total item count
	*
	* @param   int  $eventid         Event ID to filter coupons
	* @param   int  $totalItemCount  Total number of items for group discount validation
	*
	* @return  array  List of applicable coupons as an array of objects
	*
	* @since   5.0.4
	*/
	public function getApplicableCoupons($eventId, $totalItemCount)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		// Build the query to fetch applicable coupons with additional table data
		$query->select($db->quoteName([
				'c.name', 'c.code', 'c.value', 'c.val_type', 'c.group_discount_tickets',
				't.title','t.eventid']))
			->from($db->quoteName('#__jticketing_coupon', 'c'))
			->join('LEFT', $db->quoteName('#__jticketing_types', 't') . ' ON ' . $db->quoteName('c.event_ids') . ' = ' . $db->quoteName('t.eventid'))
			->where($db->quoteName('c.group_discount_tickets') . ' <= ' . (int)$totalItemCount)
			->where('FIND_IN_SET(' . (int)$eventId . ', ' . $db->quoteName('c.event_ids') . ')');

		$db->setQuery($query);
		$coupons = $db->loadObjectList();

		return $coupons;
	}
}
