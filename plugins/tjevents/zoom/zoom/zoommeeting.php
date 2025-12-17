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
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;
use Joomla\CMS\Plugin\PluginHelper;

/**
 * JTicketing event class for Zoom meetings.
 *
 * @since  3.0.0
 */
class JTicketingEventZoomMeeting extends JTicketingEventZoom implements JticketingEventOnline
{
	/**
	 * Zoom meeting ID
	 *
	 * @var    string
	 * @since  3.0.0
	 */
	private $meetingId;

	/**
	 * Zoom meeting start URL
	 *
	 * @var    string
	 * @since  3.0.0
	 */
	private $startUrl;

	/**
	 * Zoom meeting join URL
	 *
	 * @var    string
	 * @since  3.0.0
	 */
	private $joinUrl;

	/**
	 * Zoom meeting details
	 *
	 * @var    object
	 * @since  3.0.0
	 */
	private $zoomMeeting;

	/**
	 * Zoom meeting type
	 *
	 * 1 - Instant meeting.
	 * 2 - Scheduled meeting.
	 * 3 - Recurring meeting with no fixed time.
	 * 8 - Recurring meeting with a fixed time.
	 *
	 * @var    int
	 * @since  3.0.0
	 */
	private $type;

	/**
	 * Zoom meeting status
	 *
	 * @var    string
	 * @since  3.0.0
	 */
	private $status;

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
		parent::__construct($event, $venue);

		$params = new Registry($this->params);

		$this->meetingId = $params->get('zoom.id', 0);
		$this->startUrl  = $params->get('zoom.start_url', '');
		$this->joinUrl   = $params->get('zoom.join_url', '');
		$this->type      = (int) $params->get('zoom.type', 2);
		$this->status    = $params->get('zoom.status', '');
	}

	/**
	 * Method to create/update event on the zoom cloud
	 *
	 * @param   array  $data  The event data to be bind with the object
	 *
	 * @return  boolean  True on success
	 *
	 * @since   3.0.0
	 */
	public function save($data)
	{
		// @TODO Create refactor the eventform save method to use the event class save method.
		if ($this->id)
		{
			return $this->update($data);
		}

		if ($data['venuechoice'] == 'existing')
		{
			if (empty($data['existing_event']))
			{
				$this->setError(Text::_('COM_JTICKETING_ONLINE_EVENT_EMPTY_EXISTING_EVENT'));

				return false;
			}

			if ($this->meetingId != $data['existing_event'])
			{
				$this->meetingId = $data['existing_event'];
			}

			return $this->update($data);
		}

		return $this->create($this->getHostUser(), $data);
	}

	/**
	 * Method to get the list of all the event
	 *
	 * https://marketplace.zoom.us/docs/api-reference/zoom-api/meetings/meetings
	 *
	 * @param   array  $query  filters used to retrieve meetings
	 *
	 * @return  array
	 *
	 * @since   3.0.0
	 */
	public function list(array $query = [])
	{
		if (empty($query['type']))
		{
			// Only future meetings
			$query['type'] = 'upcoming';
		}

		$response = $this->getData("users/{$this->getHostUser()}/meetings", $query);
		$meetings = $response['body']['meetings'];

		// Now add the title key in the array to comply with the standard version of response
		foreach ($meetings as &$meeting)
		{
			$meeting['title'] = $meeting['topic'];
		}

		return $meetings;
	}

	/**
	 * Method to create event on the zoom cloud
	 *
	 * https://marketplace.zoom.us/docs/api-reference/zoom-api/meetings/meetingcreate
	 *
	 * @param   string  $userId  Host id of the meeting
	 * @param   array   $data    Meeting data
	 *
	 * @return  boolean  True on success
	 *
	 * @since   3.0.0
	 */
	private function create(string $userId, $data)
	{
		$data = $this->prepareMeetingData($data);
		$data = $this->postData("users/{$userId}/meetings", $data);

		if ($data['code'] != 201)
		{
			$this->setError($data['message']);

			return false;
		}

		return $this->updateParams($data['body']);
	}

	/**
	 * Method to get the details of single meeting
	 *
	 * https://marketplace.zoom.us/docs/api-reference/zoom-api/meetings/meeting
	 *
	 * @return  array|false  Array of meeting details on success false otherwise
	 *
	 * @since   3.0.0
	 */
	private function meeting()
	{
		if (empty($this->zoomMeeting))
		{
			$data = $this->getData("meetings/{$this->meetingId}");

			if ($data['code'] != 200)
			{
				$this->setError($data['message']);

				return false;
			}

			$this->zoomMeeting = $data['body'];
		}

		return $this->zoomMeeting;
	}

	/**
	 * Method to remove the meeting details
	 *
	 * https://marketplace.zoom.us/docs/api-reference/zoom-api/meetings/meetingdelete
	 *
	 * @return  boolean  True on success
	 *
	 * @since   3.0.0
	 */
	public function delete()
	{
		$data = $this->deleteData("meetings/{$this->meetingId}");

		if ($data['code'] != 204)
		{
			$this->setError($data['message']);

			return false;
		}

		return true;
	}

	/**
	 * Method to update the meeting details
	 *
	 * https://marketplace.zoom.us/docs/api-reference/zoom-api/meetings/meetingupdate
	 *
	 * @param   array  $data  Meeting data
	 *
	 * @return  boolean True on success
	 *
	 * @since   3.0.0
	 */
	private function update($data)
	{
		$dataParams = $data['params'] ? $data['params'] : [];
		$data       = $this->prepareMeetingData($data);
		$data       = $this->patchData("meetings/{$this->meetingId}", $data);

		if ($data['code'] != 204)
		{
			$this->setError($data['message']);

			return false;
		}

		// Reset the meeting data
		$this->zoomMeeting = null;
		$data = $this->meeting();

		if ($data)
		{
			return $this->updateParams($data, $dataParams);
		}

		return false;
	}

	/**
	 * Method to add registrant against event
	 *
	 * https://marketplace.zoom.us/docs/api-reference/zoom-api/meetings/meetingregistrantcreate
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
		$attendeeData = array();
		$attendeeData['email']      = $attendee->getEmail();
		$attendeeData['first_name'] = $attendee->getFirstName();
		$attendeeData['last_name']  = $attendee->getLastName();

		$attendeeParams = json_decode($attendee->getParams());

		if ($attendeeParams->sendTicket == 'ticketToBuyer')
		{
			$attendeeData['email']      = $attendee->owner_email;
		}

		$response = $this->postData("meetings/{$this->meetingId}/registrants", $attendeeData);

		if ($response['code'] != 201)
		{
			$this->setError($response['message']);

			return false;
		}

		$newParams = array();
		$newParams['registrant_id'] = $response['body']['registrant_id'];
		$newParams['join_url']      = $response['body']['join_url'];
		$attendeeParams = $attendee->getParams();
		$attendeeParams->set('zoom', $newParams);
		$attendee->setParams($attendeeParams);

		if (!$attendee->save())
		{
			$this->setError($attendee->getError());

			return false;
		}

		return true;
	}

	/**
	 * Method to get Meeting attendance
	 *
	 * https://marketplace.zoom.us/docs/api-reference/zoom-api/meetings/pastmeetingparticipants
	 *
	 * @return  boolean|Array  False on failure and return attendee arrey on success
	 *
	 * @since   3.0.0
	 */
	public function getAttendance()
	{
		$response = $this->getData("report/meetings/{$this->meetingId}/participants");

		if ($response['code'] != 200)
		{
			$this->setError($response['message']);

			return false;
		}

		$participants = $response['body']['participants'];
		$attendees = array();

		foreach ($participants as $participant)
		{
			$attendee = JT::table('attendees');
			$attendee->load(array('owner_email' => $participant['user_email'], 'event_id' => $this->integrationId));

			if (!$attendee)
			{
				continue;
			}

			if ($attendee->id)
			{
				$startDate = Factory::getDate($participant['join_time'])->toSql();

				if (!empty ($attendees[$attendee->id]['checkin']))
				{
					if ($startDate > $attendees[$attendee->id]['checkin'])
					{
						$startDate = $attendees[$attendee->id]['checkin'];
					}
				}

				$endDate = Factory::getDate($participant['leave_time'])->toSql();

				if (!empty ($attendees[$attendee->id]['checkout']))
				{
					if ($endDate < $attendees[$attendee->id]['checkout'])
					{
						$endDate = $endDate;
					}
				}

				$attendees[$attendee->id]['email'] = $participant['user_email'];
				$attendees[$attendee->id]['checkin'] = $startDate;
				$attendees[$attendee->id]['checkout'] = $endDate;
				$attendees[$attendee->id]['registrantId'] = $participant['user_id'];
				$attendees[$attendee->id]['spentTime'] += $participant['duration'];
			}
		}

		return $attendees;
	}

	/**
	 * Method to get Meeting Recording Url
	 *
	 * https://marketplace.zoom.us/docs/api-reference/zoom-api/cloud-recording/recordingget
	 *
	 * @return  boolean|String  False on failure and return recording Url
	 *
	 * @since   3.0.0
	 */
	public function getRecording()
	{
		$params = new Registry($this->getParams());

		PluginHelper::importPlugin('tjevents', 'zoom');
		$plugin   = PluginHelper::getPlugin('tjevents', 'zoom');
		$params   = new Registry($plugin->params);
		$download = $params->get('enable_downloading', 0);

		$response = $this->patchData("meetings/{$this->meetingId}/recordings/settings", array('viewer_download' => (int)$download, 'password' => ''));

		if ($response['code'] != 204)
		{
			$this->setError($response['message']);

			return false;
		}

		if ($params->get('zoom.recording_url', ''))
		{
			return str_replace("/download/","/play/",$params->get('zoom.recording_url', ''));
		}

		$response = $this->getData("meetings/{$this->meetingId}/recordings");

		if ($response['code'] != 200)
		{
			$this->setError($response['message']);

			return false;
		}

		$recordingFiles = $response['body']['recording_files'];
		$flag = false;

		foreach ($recordingFiles as $recordingFile)
		{
			if ($recordingFile['recording_type'] === 'shared_screen_with_speaker_view')
			{
				if ($recordingFile['status'] === 'completed')
				{
					$eventParams = new Registry($this->params);
					$eventParams->set('zoom.recording_url', $recordingFile['download_url']);
					$this->params = $eventParams->toString();

					$data = array();
					$data['id'] = $this->id;
					$data['params'] = $this->params;

					if (!parent::save($data))
					{
						$this->setError('PLG_TJEVENTS_ZOOM_ONLINE_EVENT_RECORDING_FAIL');

						return false;
					}

					$flag = true;

					return str_replace("/download/", "/play/", $recordingFile['download_url']);
				}

				$this->setError('PLG_TJEVENTS_ZOOM_ONLINE_EVENT_RECORDING_FAIL');

				return false;
			}
		}

		if (!$flag)
		{
			$this->setError('PLG_TJEVENTS_ZOOM_ONLINE_EVENT_RECORDING_NOT_AVAILABLE');

			return false;
		}
	}

	/**
	 * Method to delete registrant against event
	 *
	 * https://marketplace.zoom.us/docs/api-reference/zoom-api/meetings/meetingregistrantstatus
	 *
	 * @param   JTicketingAttendee  $attendee  Attendee Object
	 *
	 * @return  boolean  True on success
	 *
	 * @since   3.0.0
	 */
	public function deleteAttendee(JTicketingAttendee $attendee)
	{
		if (!$attendee->owner_id)
		{
			return false;
		}

		$attendeeParams = $attendee->getParams();

		$data['id']    = $attendeeParams->get('zoom.registrant_id', '');
		$data['email'] = $attendee->owner_email;

		if (!$data['id'] || !$data['email'])
		{
			return false;
		}

		$attendeeData['action'] = 'cancel';
		$attendeeData['registrants'][] = $data;
		$response = $this->putData("meetings/{$this->meetingId}/registrants/status", $attendeeData);

		if ($response['code'] != 204)
		{
			$this->setError($response['message']);

			return false;
		}

		return true;
	}

	/**
	 * This method prepare the params data to be stored against the event
	 *
	 * @param   array  $meetingData     Zoom meeting details
	 *
	 * @param   array  $originalParams  original params in database
	 *
	 * @return  boolean  True on success
	 *
	 * @since   3.0.0
	 */
	private function updateParams(array $meetingData, $originalParams = array())
	{
		$paramData = array(
				'id' => $meetingData['id'],
				'host_id' => $meetingData['host_id'],
				'type' => $meetingData['type'],
				'status'   => $meetingData['status'],
				'start_time' => $meetingData['start_time'],
				'start_url' => $meetingData['start_url'],
				'join_url' => $meetingData['join_url'],
				'password' => $meetingData['password'],
				// @FIXME use encrypted password
				'h323_password' => $meetingData['h323_password'],
				'pstn_password' => $meetingData['pstn_password'],
				'encrypted_password' => $meetingData['encrypted_password'],
		);

		if (is_string($originalParams))
		{
			if (json_decode($originalParams))
			{
				$originalParams = json_decode($originalParams);
			}
		}

		if (is_array($originalParams))
		{
			$originalParams['zoom'] = $paramData;
		}

		if (is_object($originalParams))
		{
			$originalParams->zoom = $paramData;
		}

		$this->params = json_encode($originalParams);

		return true;
	}

	/**
	 * This method prepare the meeting data to be created on the zoom
	 *
	 * @param   array  $data  Meeting data
	 *
	 * @return  array
	 *
	 * @since   3.0.0
	 */
	private function prepareMeetingData($data)
	{
		/**
		 * Meeting start time. We support two formats for `start_time` - local time and GMT.
		 * To set time as GMT the format should be `yyyy-MM-dd`T`HH:mm:ssZ`. Example: \"2020-03-31T12:02:00Z\"
		 * To set time using a specific timezone, use `yyyy-MM-dd`T`HH:mm:ss` format and specify the timezone
		 *
		 * [ID](https://marketplace.zoom.us/docs/api-reference/other-references/abbreviation-lists#timezones)
		 * in the `timezone` field OR leave it blank and the timezone set on your Zoom account will be used.
		 * You can also set the time as UTC as the timezone field.
		 *
		 * The `start_time` should only be used for scheduled and / or recurring webinars with fixed time.
		 */

		$venue = JT::venue($data['venue']);
		$params = new Registry($venue->getParams());
		$meetingInViewPage = $params->get('viewmeeting', 0);
		$waitingRoom = $params->get('waiting_room', 0);
		$joinBeforeHost = $params->get('join_before_host');
		$jbhTime = $params->get('jbh_time');
		$meetingPasscode = $params->get('meeting_passcode', '');

		return array(
				'params'=>$data['params'],
				'topic' => $data['title'],
				'start_time' => Factory::getDate($data['startdate'], 'UTC')->format('Y-m-d\TH:i:s\Z'),
				'agenda' => (strlen($data['long_description']) > 2000) ? '' : $data['long_description'],
				'type'   => 2,
				'password'   => $meetingPasscode,
				'duration' => ((Factory::getDate($data['enddate'], 'UTC')->toUnix() - Factory::getDate($data['startdate'], 'UTC')->toUnix()) / 60),
				'settings' => array(
									"approval_type" 				 => ($meetingInViewPage)?2:$params->get('viewmeeting', 0),
									"registrants_confirmation_email" => false,
									"waiting_room"					 => ($waitingRoom)?1:0,
									"join_before_host"				 => ($joinBeforeHost)?1:0,
									"jbh_time"						 => $jbhTime,
									)
		);
	}

	/**
	 * Method to create user against the zoom account
	 *
	 * https://marketplace.zoom.us/docs/api-reference/zoom-api/users/usercreate
	 *
	 * @param   JTicketingAttendee  $attendee  Attendee Object
	 *
	 * @return  boolean  True on success
	 *
	 * @since   3.0.0
	 */
	private function createUser(JTicketingAttendee $attendee)
	{
		$userData = array();
		$userData['email']      = $attendee->owner_email;
		$userData['first_name'] = 'Guest';
		$userData['last_name'] = '-';

		// @TODO write a method in the attendee class to retrieve the email field value if it exist
		if ($attendee->owner_id)
		{
			$user = Factory::getUser($attendee->owner_id);

			if ($user->id)
			{
				$userData['email'] = $user->email;
				$userData['first_name'] = $user->name;
			}
		}

		$userData['type'] = 1;
		$userData['password'] = "";

		$postData = array();
		$postData['user_info'] = $userData;
		$postData['action'] = 'autoCreate';

		$response = $this->postData("users", $postData);

		if ($response['code'] != 201)
		{
			$this->setError($response['message']);

			return false;
		}
	}

	/**
	 * Return the online provider event id
	 *
	 * @return  string
	 *
	 * @since   3.0.0
	 */
	public function getOnlineEventId()
	{
		return $this->meetingId;
	}

	/**
	 * Return the event join URL for participant
	 *
	 * @param   JTicketingAttendee  $attendee  Attendee Object
	 *
	 * @return  string
	 *
	 * @since   3.0.0
	 */
	public function getJoinUrl(JTicketingAttendee $attendee)
	{
		return $attendee->getParams()->get('zoom.join_url', $this->joinUrl);
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
