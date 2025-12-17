<?php
/**
 * @package     JTicketing
 * @subpackage  com_jticketing
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * Jticketing model.
 *
 * @since  1.6
 */
class JticketingModelOrderItem extends AdminModel
{
	/**
	 * Method to get the record form.
	 *
	 * @param   string  $data      An optional array of data for the form to interogate.
	 * @param   string  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  mixed   A JForm object on success, false on failure
	 *
	 * @since   1.0
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_jticketing.orderitem', 'orderitem', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Method to get a table object, load it if necessary.
	 *
	 * @param   string  $type    The table name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  JTable  A JTable object
	 *
	 * @since   1.6
	 */
	public function getTable($type = 'Orderitem', $prefix = 'JTicketingTable', $config = array())
	{
		$this->addTablePath(JPATH_ADMINISTRATOR . '/components/com_jticketing/tables');

		return Table::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to get a single record.
	 *
	 * @param   integer  $pk  The id of the primary key.
	 *
	 * @return	mixed	Object on success, false on failure.
	 *
	 * @since	2.0
	 */
	public function getItem($pk = null)
	{
		if ($pk !== null)
		{
			$db = Factory::getDbo();
			$query = $db->getQuery(true);
			$query->select(array('o.*', 'oi.*'));
			$query->from($db->quoteName('#__jticketing_order', 'o'));
			$query->join('INNER',
			$db->quoteName('#__jticketing_order_items', 'oi') . ' ON (' . $db->quoteName('o.id') . ' = ' . $db->quoteName('oi.order_id') . ')');
			$query->where($db->quoteName('oi.id') . '=' . (int) $pk);
			$db->setQuery($query);

			return $db->loadObject();
		}

		return false;
	}

	/**
	 * Prepare and sanitise the table prior to saving.
	 *
	 * @param   data  $data  TO  ADD
	 *
	 * @return void
	 *
	 * @since    1.6
	 */
	public function save($data)
	{
		if (parent::save((array) $data))
		{
			$id = (int) $this->getState($this->getName() . '.id');

			// If collect_attendee_info_checkout setting off then will add entry for attendee table only
			$com_params = ComponentHelper::getParams('com_jticketing');
			$attendeeInfo = $com_params->get('collect_attendee_info_checkout');

			if ($attendeeInfo == 0)
			{
				$this->saveAttendee($id, $data);
			}
		}
	}

	/**
	 * Function for to get and order items data.
	 *
	 * @param   integer  $orderID  TO  ADD
	 *
	 * @return void
	 *
	 * @since    1.6
	 */
	public function getOrderItems($orderID)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select(array('*'));
		$query->from($db->quoteName('#__jticketing_order_items'));
		$query->where($db->quoteName('order_id') . ' = ' . $db->quote($orderID));
		$db->setQuery($query);
		$orderItems = $db->loadObjectlist();

		return $orderItems;
	}

	/**
	 * Function for to save attendee form.
	 *
	 * @param   integer   $itemId  Order Item ID
	 * @param   stdClass  $data    Order Item data
	 *
	 * @return  mixed return true on success otherwise false;
	 *
	 * @since    1.6
	 */
	public function saveAttendee($itemId, $data)
	{
		$user    = Factory::getUser();
		$attendeeData = array();
		$attendeeData['ticket_id'] = $data->type_id;
		$attendeeData['event_id'] = $data->eventid;
		$attendeeData['owner_id'] = $user->id;
		$attendeeData['owner_email'] = $user->email;

		if (file_exists(JPATH_SITE . '/components/com_jticketing/models/attendeeform.php')) { require_once JPATH_SITE . '/components/com_jticketing/models/attendeeform.php'; }
		$model = BaseDatabaseModel::getInstance('AttendeeForm', 'JticketingModel');

		$attendeeId = $model->save($attendeeData);

		if ($attendeeId)
		{
			$attendeeField = array();
			$attendeeField['attendee_id'] = (int) $attendeeId;
			$attendeeField['order_items_id'] = (int) $itemId;

			$this->updateorderItems($attendeeField);
		}
	}

	/**
	 * Function to update order item from the attendee model
	 *
	 * @param   integer[]  $data  TO  ADD
	 *
	 * @return  mixed    Object on success, false on failure.
	 *
	 * @since    1.6
	 */
	public function updateorderItems($data)
	{
		$res              = new StdClass;
		$res->id          = '';
		$res->attendee_id = $data['attendee_id'];

		$db    = Factory::getDbo();

		// If order items id present update it
		if ($data['order_items_id'])
		{
			$currentOrderItems   = array();
			$currentOrderItems[] = $data['order_items_id'];
			$res->id             = $data['order_items_id'];

			if (!$db->updateObject('#__jticketing_order_items', $res, 'id'))
			{
				echo $db->stderr();

				return false;
			}
			else
			{
				return true;
			}
		}
		else
		{
			return false;
		}
	}
}
