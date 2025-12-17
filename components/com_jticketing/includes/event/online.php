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

/**
 * JTicketing online event interface
 *
 * @since  3.0.0
 */
interface JTicketingEventOnline
{
	/**
	 * Constructor activating the default information of the event
	 *
	 * @param   JTicketingEventJticketing  $event  The event object
	 * @param   JTicketingVenue            $venue  The venue object
	 *
	 * @since   3.0.0
	 */
	public function __construct(JTicketingEventJticketing $event, JTicketingVenue $venue = null);

	/**
	 * Method to create/update event
	 *
	 * @param   array  $data  The event data to be bind with the object
	 *
	 * @return  boolean  True on success
	 *
	 * @since   3.0.0
	 */
	public function save($data);

	/**
	 * Method to get the list of all the event
	 *
	 * @param   array  $query  filters used to retrieve meetings
	 *
	 * @return  array  List of events
	 *
	 * @since   3.0.0
	 */
	public function list(array $query = []);

	/**
	 * Method to remove the meeting details
	 *
	 * @return  boolean True on success
	 *
	 * @since   3.0.0
	 */
	public function delete();

	/**
	 * Method to add registrant against event
	 *
	 * @param   JTicketingAttendee  $attendee  Attendee Object
	 * @param   array               $data      Registrant data
	 *
	 * @return  boolean  True on success
	 *
	 * @since   3.0.0
	 */
	public function addAttendee(JTicketingAttendee $attendee, $data = []);

	/**
	 * Method to get Meeting attendance
	 *
	 * @return  boolean|Array  False on failure and return attendee arrey on success
	 *
	 * @since   3.0.0
	 */
	public function getAttendance();

	/**
	 * Method to get Meeting Recording Url
	 *
	 * @return  boolean|String  False on failure and return recording Url
	 *
	 * @since   3.0.0
	 */
	public function getRecording();

	/**
	 * Method to delete registrant against event
	 *
	 * @param   JTicketingAttendee  $attendee  Attendee Object
	 *
	 * @return  boolean  True on success
	 *
	 * @since   3.0.0
	 */
	public function deleteAttendee(JTicketingAttendee $attendee);

	/**
	 * Return the online provider event id
	 *
	 * @return  string  The online provider meeting id
	 *
	 * @since   3.0.0
	 */
	public function getOnlineEventId();

	/**
	 * Return the event join url for participant
	 *
	 * @param   JTicketingAttendee  $attendee  Attendee Object
	 *
	 * @return  string  The join URL required to attend event
	 *
	 * @since   3.0.0
	 */
	public function getJoinUrl(JTicketingAttendee $attendee);

	/**
	 * Validate credentials
	 *
	 * @return  Boolean  True on success
	 *
	 * @since   3.0.0
	 */
	public function isValidCredentials();

	/**
	 * Getting replacements for ticket mail tags
	 *
	 * @param   JTicketingAttendee  $attendee  Attendee Object
	 *
	 * @return  string
	 *
	 * @since   3.0.0
	 */
	public function getMailReplacementTags(JTicketingAttendee $attendee);

	/**
	 * Update Event params after saving the event.
	 *
	 * @param   int  $id  Event id
	 *
	 * @return  boolean
	 *
	 * @since   3.3.1
	 */
	public function updateParamsAfterEventSave();
}
