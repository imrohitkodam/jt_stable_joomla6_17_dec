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
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

/**
 * jticketing Model
 *
 * @since  0.0.1
 */
class JTicketingModelTickettype extends AdminModel
{
	/**
	 * @var      string    The prefix to use with controller messages.
	 * @since    1.6
	 */
	protected $text_prefix = 'COM_JTICKETING';

	/**
	 * @var     string      Alias to manage history control
	 * @since   3.2
	 */
	public $typeAlias = 'com_jticketing.tickettype';

	/**
	 * @var null  Item data
	 * @since  1.6
	 */
	protected $item = null;
	/**
	 * Method to get the record form.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  mixed    A JForm object on success, false on failure
	 *
	 * @since   1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm(
			'com_jticketing.tickettype',
			'tickettype',
			array(
				'control' => 'jform',
				'load_data' => $loadData
			)
		);

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Method to get the record form.
	 *
	 * @param   string  $type    Data for the form.
	 * @param   string  $prefix  True if the form is to load its own data (default case), false if not.
	 * @param   array   $config  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  table
	 *
	 * @since   1.6
	 */
	public function getTable($type = 'Tickettypes', $prefix = 'JticketingTable', $config = array())
	{
		return Table::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to get an object.
	 *
	 * @param   integer  $id  The id of the object to get.
	 *
	 * @return  mixed    Object on success, false on failure.
	 */
	public function getItem($id = null)
	{
		$this->item = parent::getItem($id);

		return $this->item;
	}

	/**
	 * get ticket types of the event
	 *
	 * @param   integer  $xrefId  id for the event in integration table
	 *
	 * @return integer   $db        ticket types ids
	 *
	 * @since  2.1
	 */
	public function getTicketTypes($xrefId)
	{
		$db = Factory::getDbo();

		// Create a new query object.
		$query = $db->getQuery(true);
		$query->select($db->quoteName('id'));
		$query->from($db->quoteName('#__jticketing_types'));
		$query->where($db->quoteName('eventid') . ' = ' . $db->quote($xrefId));
		$db->setQuery($query);

		return $db->loadAssocList();
	}

	/**
	 * check for orders for this ticket type
	 *
	 * @param   integer  $ticketTypeId  id for the ticket type
	 *
	 * @return integer   $res           id if there exists order against it
	 *
	 * @since  2.1
	 */
	public function checkOrderExistsTicketType($ticketTypeId)
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('id');
		$query->from($db->quoteName('#__jticketing_order_items'));

		if (!empty($ticketTypeId))
		{
			$query->where($db->quoteName('type_id') . ' = ' . $db->quote($ticketTypeId));
		}

		$db->setQuery($query);
		$res = $db->loadResult();

		return $res;
	}

	/**
	 * Method to validate the extraform data.
	 *
	 * @param   JForm   $form   The form to validate against.
	 * @param   array   $data   The data to validate.
	 * @param   string  $group  The name of the field group to validate.
	 *
	 * @return  mixed  Array of filtered data if valid, false otherwise.
	 *
	 * @deprecated 2.5.0 will be removed in the 2.4.0 this is moved under eventForm model class.
	 *
	 * @see     JFormRule
	 * @see     JFilterInput
	 * @since   12.2
	 */
	public function validateExtra($form, $data, $group = null)
	{
		$config = Factory::getConfig();
		$user   = Factory::getUser($data['created_by']);

		$bookingStartDt = Factory::getDate(
			($data['booking_start_date'] != '0000-00-00 00:00:00') ? $data['booking_start_date'] : $data['created'],
			$user->getParam('timezone', $config->get('offset'))
		);
		$bookingStartDt->setTimezone(new DateTimeZone('UTC'));
		$bookingStartDt = $bookingStartDt->toSql(true);

		$bookingEndDt = Factory::getDate(
			($data['booking_end_date'] != '0000-00-00 00:00:00') ? $data['booking_end_date'] : $data['enddate'],
			$user->getParam('timezone', $config->get('offset'))
		);
		$bookingEndDt->setTimezone(new DateTimeZone('UTC'));
		$bookingEndDt = $bookingEndDt->toSql(true);

		$ticketTypes = $data['tickettypes'];

		foreach ($ticketTypes as $ticketType)
		{
			if (!empty($ticketType['ticket_enddate']))
			{
				$ticketEndDt = Factory::getDate($ticketType['ticket_enddate'], $user->getParam('timezone', $config->get('offset')));
				$ticketEndDt->setTimezone(new DateTimeZone('UTC'));
				$ticketEndDt = $ticketEndDt->toSql(true);

				// Validate if ticket end-date <= booking end-date.
				if ($ticketEndDt > $bookingEndDt)
				{
					$this->setError(Text::_('COM_JTICKETING_TICKET_END_DATE_GREATER_BOOKING_END_DATE_ERROR'));

					return false;
				}

				// Validate if ticket end-date >= booking end-date.
				if ($ticketEndDt < $bookingStartDt)
				{
					$this->setError(Text::_('COM_JTICKETING_TICKET_END_DATE_LESS_BOOKING_START_DATE_ERROR'));

					return false;
				}
			}
		}
	}
}
