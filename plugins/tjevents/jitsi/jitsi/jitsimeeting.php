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

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\Registry\Registry;
use Joomla\CMS\Language\Text;

/**
 * JTicketing event class for Jitsi meetings.
 *
 * @since  3.0.0
 */
class JTicketingEventJitsimeeting extends JTicketingEventJticketing implements JticketingEventOnline
{
	/**
	 * Constructor activating the default information of the event
	 *
	 * @param   JTicketingEventJticketing  $event  The event object
	 * @param   JTicketingVenue            $venue  The venue object
	 *
	 * @since   3.0.0
	 */
	public function __construct(JTicketingEventJticketing $event, JTicketingVenue $venue = null)
	{
		parent::__construct($event->id);
	}

	/**
	 * Validate credentials
	 *
	 * @return  Boolean  True on success
	 *
	 * @since   3.0.0
	 */
	public function isValidCredentials()
	{
		return true;
	}

	/**
	 * Method to save the Event object to the database
	 *
	 * @param   array  $data  The event data to be bind with the object
	 *
	 * @return  boolean  True on success
	 *
	 * @since   3.0.0
	 */
	public function save($data)
	{
		$oldParams = new Registry($this->params);

		if (!empty($data['params']) && JT::utilities()->isJSON($data['params']))
		{
			$data['params'] = json_decode($data['params'], true);
		}

		if (!isset($data['params']['jitsi']['roomId']))
		{
			$data['params']['jitsi']['roomId'] = $this->generateRoomName();

			// $oldParams->set('jitsi.password', UserHelper::genRandomPassword());
			$this->params = json_encode($data['params']);
		}

		return true;
	}

	/**
	 * Method to get Meeting attendance
	 *
	 * @return  boolean|Array  False on failure and return attendee arrey on success
	 *
	 * @since   3.0.0
	 */
	public function getAttendance()
	{
		// Implement and return the core attendee details

		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from($db->quoteName('#__jitsi_attendee'));
		$query->where($db->quoteName('eventid') . ' = ' . $db->q($this->getId()));
		$db->setQuery($query);
		$participants = $db->loadObjectList();

		$attendees = array();

		foreach ($participants as $participant)
		{
			$attendee = JT::table('attendees');
			$attendee->load(array('owner_email' => $participant->email, 'event_id' => $this->integrationId));

			if (!$attendee || !$attendee->id)
			{
				continue;
			}

			$startDate = Factory::getDate($participant->intime)->toSql();

			if (!empty ($attendees[$attendee->id]['checkin']))
			{
				if ($startDate > $attendees[$attendee->id]['checkin'])
				{
					$startDate = $attendees[$attendee->id]['checkin'];
				}
			}

			$endDate = Factory::getDate($participant->outtime)->toSql();

			if (!empty ($attendees[$attendee->id]['checkout']))
			{
				if ($endDate < $attendees[$attendee->id]['checkout'])
				{
					$endDate = $endDate;
				}
			}

			$attendees[$attendee->id]['email'] = $participant->email;
			$attendees[$attendee->id]['checkin'] = $startDate;
			$attendees[$attendee->id]['checkout'] = $endDate;

			// This is to pass spent time in secs for the further checkin process
			$attendees[$attendee->id]['spentTime'] += $participant->timespent * 60;
		}

		return $attendees;
	}

	/**
	 * Return the event join url for participant
	 *
	 * @param   JTicketingAttendee  $attendee  Attendee Object
	 *
	 * @return  string  The join URL required to attend event
	 *
	 * @since   3.0.0
	 */
	public function getJoinUrl(JTicketingAttendee $attendee)
	{
		$JTRouteHelper = JPATH_SITE . '/components/com_jticketing/helpers/route.php';

		if (!class_exists('JTRouteHelper'))
		{
			JLoader::register('JTRouteHelper', $JTRouteHelper);
			JLoader::load('JTRouteHelper');
		}

		$JTRouteHelper = new JTRouteHelper;
		$jitsiLink = "index.php?option=com_jticketing&view=event&layout=online&tmpl=component&id=" . $this->id;

		return $JTRouteHelper->JTRoute($jitsiLink, false);
	}

	/**
	 * Method to get the list of all the event
	 *
	 * @param   array  $query  filters used to retrieve meetings
	 *
	 * @return  array  List of events
	 *
	 * @since   3.0.0
	 */
	public function list(array $query = [])
	{
		return array();
	}

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
	public function addAttendee(JTicketingAttendee $attendee, $data = [])
	{
		return true;
	}

	/**
	 * Method to remove the meeting details
	 *
	 * @return  boolean True on success
	 *
	 * @since   3.0.0
	 */
	public function delete()
	{
		return true;
	}

	/**
	 * Method to delete registrant against event
	 *
	 * @param   JTicketingAttendee  $attendee  Attendee Object
	 *
	 * @return  boolean  True on success
	 *
	 * @since   3.0.0
	 */
	public function deleteAttendee(JTicketingAttendee $attendee)
	{
		return true;
	}

	/**
	 * Method to get Meeting Recording Url
	 *
	 * @return  boolean|String  False on failure and return recording Url
	 *
	 * @since   3.0.0
	 */
	public function getRecording()
	{
		$this->setError(Text::_('PLG_TJEVENTS_JITSI_VIDEO_NO_RECORDING_AVAILBLE'));

		return false;
	}

	/**
	 * Method to generate unique room id to host meeting
	 *
	 * @return  String  Room id
	 *
	 * @since   3.0.0
	 */
	private function generateRoomName()
	{
		return sprintf(
				'%04x%04x%04x%04x%04x%04x%04x%04x',
				mt_rand(0, 0xffff),
				mt_rand(0, 0xffff),
				mt_rand(0, 0xffff),
				mt_rand(0, 0x0fff) | 0x4000,
				mt_rand(0, 0x3fff) | 0x8000,
				mt_rand(0, 0xffff),
				mt_rand(0, 0xffff),
				mt_rand(0, 0xffff)
				);
	}

	/**
	 * Getting replacements for online Ticket Mail tags
	 *
	 * @param   JTicketingAttendee  $attendee  Attendee Object
	 *
	 * @return  string
	 *
	 * @since   3.0.0
	 */
	public function getMailReplacementTags(JTicketingAttendee $attendee)
	{
		return '';
	}

	/**
	 * Update Event params after saving the event.
	 *
	 * @param   int  $id  Event id
	 *
	 * @return  boolean
	 *
	 * @since   3.3.1
	 */
	public function updateParamsAfterEventSave()
	{
		return true;
	}
}
