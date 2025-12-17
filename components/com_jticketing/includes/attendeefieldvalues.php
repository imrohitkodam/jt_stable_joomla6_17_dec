<?php
/**
 * @package     JTicketing
 * @subpackage  com_jticketing
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Factory;

/**
 * JTicketing event class.
 *
 * @since  2.7.0
 */
class JTicketingAttendeeFieldValues extends CMSObject
{
	/**
	 * The auto incremental primary key of the Attendee field values
	 *
	 * @var    integer
	 * @since  2.7.0
	 */
	public $id = 0;

	/**
	 * Attendee table primary key - Foreign key of the attendee
	 *
	 * @var    integer
	 * @since  2.7.0
	 */
	public $attendee_id = 0;

	/**
	 * Attendee fields table primary key - Foreign key of the attendee field
	 *
	 * @var    integer
	 * @since  2.7.0
	 */
	public $field_id = 0;

	/**
	 * Field Value
	 *
	 * @var    string
	 * @since  2.7.0
	 */
	public $field_value = 1;

	/**
	 * Field Source
	 *
	 * @var    string
	 * @since  2.7.0
	 */
	public $field_source = 1;

	/**
	 * holds the already loaded instances of the Order Items
	 *
	 * @var    array
	 * @since  2.7.0
	 */
	protected static $attendeefieldValueObj = array();

	/**
	 * Constructor activating the default information of the Attendee field values
	 *
	 * @param   int  $id  The unique attendee field values key to load.
	 *
	 * @since   2.7.0
	 */
	public function __construct($id = 0)
	{
		if (!empty($id))
		{
			$this->load($id);
		}
	}

	/**
	 * Returns the global attendee field value object
	 *
	 * @param   integer  $id  The primary key of the attendee field value to load (optional).
	 *
	 * @return  JTicketingAttendeeFieldValues  The attendee fields value object.
	 *
	 * @since   2.7.0
	 */
	public static function getInstance($id = 0)
	{
		if (!$id)
		{
			return new JTicketingAttendeeFieldValues;
		}

		if (empty(self::$attendeefieldValueObj[$id]))
		{
			self::$attendeefieldValueObj[$id] = new JTicketingAttendeeFieldValues($id);
		}

		return self::$attendeefieldValueObj[$id];
	}

	/**
	 * Method to load a attendee field values properties
	 *
	 * @param   int  $id  The Attendee field values id
	 *
	 * @return  boolean  True on success
	 *
	 * @since   2.7.0
	 */
	public function load($id)
	{
		$table = JT::table("attendeefieldvalues");

		if ($table->load($id))
		{
			$this->setProperties($table->getProperties());

			$this->id           = (int) $table->get('id');
			$this->attendee_id  = (int) $table->get('attendee_id');
			$this->field_id     = (int) $table->get('field_id');
			$this->field_value  = $table->get('field_value');
			$this->field_source = $table->get('field_source');

			return true;
		}

		return false;
	}

	/**
	 * Method to bind an associative array of data to a attendee field values object
	 *
	 * @param   array  $array  The associative array to bind to the object
	 *
	 * @return  boolean  True on success
	 *
	 * @since   2.7.0
	 */
	public function bind($array)
	{
		$this->attendee_id = $array['attendee_id'];
		$this->field_id = $array['field_id'];
		$this->field_value = $array['field_value'];
		$this->field_source = $array['field_source'];

		return true;
	}

	/**
	 * Method to save the Attendee field values object to the database
	 *
	 * @return  boolean  True on success
	 *
	 * @since   2.7.0
	 */
	public function save()
	{
		$isNew = $this->isNew();
		$table = JT::table('attendeefieldvalues');

		// Allow an exception to be thrown.
		try
		{
			$table->bind($this->getProperties());

			// Check and store the object.
			if (!$table->check())
			{
				$this->setError($table->getError());

				return false;
			}

			// Store the order item in the database
			$result = $table->store();

			// Set the id for the order item object in case we created a new order item.
			if ($result && $isNew)
			{
				$this->load($table->get('id'));

				return true;
			}
			elseif ($result && !$isNew)
			{
				$this->load($this->id);

				return true;
			}
		}
		catch (\Exception $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		return $result;
	}

	/**
	 * Method to delete the Attendee field values object from the database
	 *
	 * @return  boolean  True on success
	 *
	 * @since   2.7.0
	 */
	public function delete()
	{
		// Create the user table object
		$table = JT::table('attendeefieldvalues');

		if (!$table->delete($this->id))
		{
			$this->setError($table->getError());

			return false;
		}

		return true;
	}

	/**
	 * Method to check is order item new or not
	 *
	 * @return  boolean  True on success
	 *
	 * @since   2.7.0
	 */
	private function isNew()
	{
		return $this->id < 1;
	}

	/**
	 * Method to load a attendee field values by Attendee id and field id
	 *
	 * @param   int     $id       The Attendee id
	 * @param   int     $fieldId  The Attendee field id
	 * @param   String  $source   The source of field values i.e com_jticketing for core fields
	 *                            and com_tjfields.com_jticketing.ticket for jtfields
	 *
	 * @return  Array|Boolean  false on fail and Array on success
	 *
	 * @since   2.7.0
	 */
	public function loadByAttendeeId($id, $fieldId = null, $source = 'com_jticketing')
	{
		if (empty($id))
		{
			return false;
		}

		/* @var $attendeefieldValuesModel JticketingModelAttendeefieldvalueslist*/
		$attendeefieldValuesModel = JT::model('attendeefieldvalueslist', array('ignore_request' => true));

		$attendeefieldValuesModel->setState('filter.attendee_id', $id);
		$attendeefieldValuesModel->setState('filter.field_source', $source);

		if (!empty($fieldId))
		{
			$attendeefieldValuesModel->setState('filter.field_id', $fieldId);
		}

		return $attendeefieldValuesModel->getItems();
	}
}
