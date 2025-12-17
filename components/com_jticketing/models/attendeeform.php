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
use Joomla\CMS\MVC\Model\AdminModel;

/**
 * Model for buy for creating order and other
 *
 * @package     JTicketing
 * @subpackage  component
 * @since       1.0
 */
class JticketingModelAttendeeForm extends AdminModel
{
	/**
	 * Constructor
	 *
	 * @since   1.0
	 */
	public function __construct()
	{
		parent::__construct();
		JLoader::register('JTRouteHelper', JPATH_SITE . '/components/com_jticketing/helpers/route.php');
		$this->JTRouteHelper = new JTRouteHelper;
	}

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
		$form = $this->loadForm('com_jticketing.attendees', 'attendees', array('control' => 'jform', 'load_data' => $loadData));

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
	public function getTable($type = 'Attendees', $prefix = 'JticketingTable', $config = array())
	{
		$this->addTablePath(JPATH_ADMINISTRATOR . '/components/com_jticketing/tables');

		return Table::getInstance($type, $prefix, $config);
	}

	/**
	 * Prepare and sanitise the table prior to saving.
	 *
	 * @param   Array  $attendeeDetails  TO  ADD
	 *
	 * @return Boolean
	 *
	 * @since    1.0
	 */
	public function save($attendeeDetails)
	{
		/*
			Validate Data.
			Extract has following params :
			$id, $eventId, $userId, $emailId, $ticketId, $enrollment_id, $approve
		*/

		$enrollmentId = 0;

		extract($attendeeDetails);

		$enrollmentData = array();

		if (!empty($id) && (int) $id)
		{
			$enrollmentData['id'] = $id;
		}
		else
		{
			$enrollmentData['id'] = 0;
		}

		if (isset($event_id) && (int) $event_id)
		{
			$enrollmentData['event_id'] = $event_id;
		}

		if (isset($owner_id) && (int) $owner_id)
		{
			$enrollmentData['owner_id'] = $owner_id;
		}

		if (isset($ticket_type_id) && (int) $ticket_type_id)
		{
			$enrollmentData['ticket_type_id'] = $ticket_type_id;
		}

		if (isset($owner_email))
		{
			$enrollmentData['owner_email'] = $owner_email;
		}

		if (isset($enrollment_id))
		{
			$enrollmentData['enrollment_id'] = $enrollment_id;
		}

		if (isset($status))
		{
			$enrollmentData['status'] = $status;
		}

		if ((!empty($enrollmentData['id']) && (int) $enrollmentData['id']) || $enrollmentData['event_id'])
		{
			// Get the state before saving data
			$this->getState();

			// Need to update enrollment Id twice with prefixes
			if (parent::save($enrollmentData))
			{
				// Update the enrollment id to enrollment/attendee table.
				$enrollmentId = (int) $this->getState($this->getName() . '.id');

				if (!empty($enrollmentData['id']))
				{
					$enrollmentId = $enrollmentData['id'];
				}

				$enrollmentData['id'] = $enrollmentId;
				$enrollmentData['enrollment_id'] = $this->generateEnrollmentId($enrollmentId);

				parent::save($enrollmentData);

				return $enrollmentId;
			}
		}
		else
		{
			return false;
		}
	}

	/**
	 * Update attendee owner
	 *
	 * @param   INT     $attendeeId  email of joomla user
	 * @param   INT     $ownerId     order creator ID
	 * @param   STRING  $ownerEmail  order creator Email
	 *
	 * @return  orderItems order item object
	 *
	 * @since   1.0
	 */
	public function updateAttendeeOwner($attendeeId, $ownerId = 0, $ownerEmail = '')
	{
		$obj = new stdClass;

		$obj->id = $attendeeId;

		if ($ownerId)
		{
			$obj->owner_id = $ownerId;
		}

		if ($ownerEmail)
		{
			$obj->owner_email = $ownerEmail;
		}

		if ($ownerId || $ownerEmail)
		{
			// Update order entry.
			if (!$this->_db->updateObject('#__jticketing_attendees', $obj, 'id'))
			{
				echo $this->_db->stderr();

				return false;
			}
			else
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Method to generate enrollment ID
	 *
	 * @param   INT  $attendeeId  primary key of attendee table
	 *
	 * @return   string|Boolean  string on success & false on failure
	 *
	 * @since   2.5.0
	 */
	public function generateEnrollmentId($attendeeId)
	{
		$jtconfig     = JT::config();
		$enrollmentId = '';

		if (!empty($jtconfig->get('enrollment_id_prefix', 'JT')))
		{
			$enrollmentId = $jtconfig->get('enrollment_id_prefix', 'JT');
		}

		if (!empty($jtconfig->get('append_eventID', 0)))
		{
			$attendee = $this->getItem($attendeeId);
			$event = JT::event()->loadByIntegration($attendee->event_id);
			$eventId = $event->getId();

			$appendZerosToEventId = $jtconfig->get('append_zeros_to_event_id', 0);

			if (!empty($appendZerosToEventId))
			{
				if (strlen($eventId) > 1)
				{
					$appendZerosToEventId -= (strlen($eventId) - 1);
				}
	
				if ($appendZerosToEventId > 0)
				{
					$enrollmentId = str_pad($enrollmentId, strlen($enrollmentId) + $appendZerosToEventId, "0", STR_PAD_RIGHT);
				}
			}

			if (!empty($eventId))
			{
				$enrollmentId .= $eventId;
			}
		}

		if (!empty($jtconfig->get('random_enrollmentID', 1)))
		{
			// Generate random characters to make complex Qr code
			$utilities = JT::Utilities();
			$randomNumber = $utilities->generateRandomString($jtconfig->get('count_random', 15));
			$enrollmentId .= $randomNumber;
		}

		$appendZeros = $jtconfig->get('append_zeros', 0);

		if (!empty($appendZeros))
		{
			if (strlen($attendeeId) > 1)
			{
				$appendZeros -= (strlen($attendeeId) - 1);
			}

			if ($appendZeros > 0)
			{
				$enrollmentId = str_pad($enrollmentId, strlen($enrollmentId) + $appendZeros, "0", STR_PAD_RIGHT);
			}
		}

		if (!empty($attendeeId))
		{
			return $enrollmentId . $attendeeId;
		}

		return false;
	}
}
