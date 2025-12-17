<?php
/**
 * @package     JTicketing
 * @subpackage  com_jticketing
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Factory;

/**
 * Methods supporting a list of Jticketing records.
 *
 * @since  1.6
 */
class JticketingModelOrderItems extends ListModel
{
	/**
	 * Method to get an array of data items
	 *
	 * @return  mixed An array of data on success, false on failure.
	 */
	public function getItems()
	{
		$items = parent::getItems();

		return $items;
	}

	/**
	 * Returns array of order object for frontend listing.
	 *
	 * @param   array  $options  Filters
	 *
	 * @since  2.5.0
	 *
	 * @return object
	 */
	public function getOrderItems($options = array())
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('DISTINCT *');
		$query->from($db->quoteName('#__jticketing_order_items'));

		if (isset($options['order_id']))
		{
			$query->where($db->quoteName('order_id') . ' = ' . $db->quote($options['order_id']));
		}

		if (isset($options['type_id']))
		{
			$query->where($db->quoteName('type_id') . ' = ' . $db->quote($options['type_id']));
		}

		if (isset($options['attendee_id']))
		{
			$query->where($db->quoteName('attendee_id') . ' = ' . $db->quote($options['attendee_id']));
		}

		$db->setQuery($query);

		return $db->loadObjectList('id');
	}
}
