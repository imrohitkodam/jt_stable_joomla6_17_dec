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
defined('_JEXEC') or die(';)');

use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\MVC\Model\AdminModel;

/**
 * Model for buy for creating order and other
 *
 * @package     JTicketing
 * @subpackage  component
 * @since       1.0
 */
class JticketingModelAttendeefieldvalues extends AdminModel
{
	/**
	 * Method to get the record form.
	 *
	 * @param   string  $data      An optional array of data for the form to interogate.
	 * @param   string  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  JForm   A JForm object on success, false on failure
	 *
	 * @since   1.0
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Initialise variables.
		$app = Factory::getApplication();

		// Get the form.
		$form = $this->loadForm('com_jticketing.attendeefieldvalues', 'attendeefieldvalues', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Get an instance of JTable class
	 *
	 * @param   string  $type    Name of the JTable class to get an instance of.
	 * @param   string  $prefix  Prefix for the table class name. Optional.
	 * @param   array   $config  Array of configuration values for the JTable object. Optional.
	 *
	 * @return  JTable|bool JTable if success, false on failure.
	 */
	public function getTable($type = 'Attendeefieldvalues', $prefix = 'JticketingTable', $config = array())
	{
		$this->addTablePath(JPATH_ADMINISTRATOR . '/components/com_jticketing/tables');

		return Table::getInstance($type, $prefix, $config);
	}

	/**
	 * Prepare and sanitise the table prior to saving.
	 *
	 * @param   data  $data  TO  ADD
	 *
	 * @return  boolean
	 *
	 * @since    1.6
	 */
	public function save($data)
	{
		$attendeeId = $data['attendee_id'];
		$filter = InputFilter::getInstance();
		$return = true;

		// Save Custom user Entry Fields
		foreach ($data as $key => $field)
		{
			if ($key == 'order_items_id' || $key == 'ticket_type' || $key == 'attendee_id')
			{
				continue;
			}

			$db    = Factory::getDbo();

			// Using id for Event specific custom fields
			$query = $db->getQuery(true);
			$query->select($db->quoteName(array('id')));
			$query->from($db->quoteName('#__jticketing_attendee_fields'));
			$query->where($db->quoteName('id') . ' LIKE ' . $db->quote($key));
			$db->setQuery($query);
			$fieldId = $db->loadResult();

			if ($fieldId)
			{
				$fieldSource = "com_jticketing";
			}
			else
			{
				// Using name for Universal custom fields
				$query = $db->getQuery(true);
				$query->select($db->quoteName(array('id')));
				$query->from($db->quoteName('#__tjfields_fields'));
				$query->where($db->quoteName('name') . ' LIKE ' . $db->quote($key));
				$db->setQuery($query);
				$fieldId = $db->loadResult();
				$fieldSource = "com_tjfields.com_jticketing.ticket";
			}

			if ($fieldId)
			{
				$row             = new stdClass;
				$row->id         = '';
				$fieldIdExists = 0;

				// Changed this for phpcs error
				$query = $db->getQuery(true);
				$query->select($db->qn(array('id')));
				$query->from($db->qn('#__jticketing_attendee_field_values'));
				$query->where($db->qn('attendee_id') . ' = ' . $db->quote($attendeeId));
				$query->where($db->qn('field_id') . ' = ' . $db->quote($fieldId));
				$query->where($db->qn('field_source') . ' = ' . $db->quote($fieldSource));

				// Important to use field source in query
				$db->setQuery($query);
				$fieldIdExists = $db->loadResult();

				if ($fieldSource)
				{
					$row->field_source = $filter->clean($fieldSource, 'string');
				}

				if ($fieldId)
				{
					$row->field_id = $filter->clean($fieldId, 'int');
				}

				if ($attendeeId)
				{
					$row->attendee_id = $filter->clean($attendeeId, 'int');
				}

				if (is_array($field))
				{
					$field = implode('|', $field);
				}

				$row->field_value = '';

				if ($field)
				{
					$row->field_value = $filter->clean($field, 'string');
				}

				if ($fieldIdExists)
				{
					$row->id = $fieldIdExists;
				}

				$data = (array) $row;

				if (!parent::save($data))
				{
					$return = false;
				}
			}
		}

		return $return;
	}

	/**
	 * Method to delete the attendee information agianst the attendee
	 *
	 * @param   JTicketingAttendee  $attendee  The attendee object
	 *
	 * @return  mixed    false on failure
	 *
	 * @since   2.5.0
	 */
	public function deleteAttendeeInfo(JTicketingAttendee $attendee)
	{
		if (!$attendee->id)
		{
			return false;
		}

		/**
		 * Performing a simple delete query because we don't have primary/unique key on attendee_id
		 */
		$query = $this->_db->getQuery(true);
		$query->delete($this->_db->qn('#__jticketing_attendee_field_values'));
		$query->where($this->_db->qn('attendee_id') . '=' . (int) $attendee->id);
		$this->_db->setQuery($query);

		// @TODO remove the tjfield data
		return $this->_db->execute();
	}
}
