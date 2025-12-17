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

/**
 * JTicketing event class for Adobeconnect meetings.
 *
 * @since  3.0.0
 */
class JTicketingEventAdobeConnectmeeting extends JTicketingEventAdobe implements JticketingEventOnline
{
	/**
	 * Adobe meeting ID
	 *
	 * @var    int
	 * @since  3.0.0
	 */
	private $meetingId = 0;

	/**
	 * Adobe meeting url
	 *
	 * @var    string
	 * @since  3.0.0
	 */
	private $meetingUrl = '';

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

		$this->meetingId  = $params->get('event_sco_id', 0);
		$this->meetingUrl = $params->get('event_url', '');
	}

	/**
	 * Method to create/update event on the Adobeconnect cloud
	 *
	 * @param   array  $data  The event data to be bind with the object
	 *
	 * @return  boolean  True on success
	 *
	 * @since   3.0.0
	 */
	public function save($data)
	{
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

			$sourceInfo = $this->makeRequest('sco-info',
								array(
									'sco-id'  => $this->meetingId,
								)
							);

			$resBody                = $sourceInfo->getBody();
			$params                 = json_decode($this->params);
			$params['event_url']    = $resBody['sco']['urlPath'];
			$params['event_sco_id'] = $this->meetingId;
			$this->params           = json_encode($params);

			return $this->update($data);
		}

		return $this->create($data);
	}

	/**
	 * Method to create meeting on the adobe cloud
	 *
	 * @param   array  $data  meeting data
	 *
	 * @return  boolean  True on success
	 *
	 * @since   3.0.0
	 */
	private function create($data)
	{
		$userDetail     = Factory::getUser($data['created_by']);
		$userCredential = JT::techjoomlaapi()->loadByUserId($userDetail->id);
		$email          = '';
		$userexists		= new stdClass;

		if (!empty($userCredential))
		{
			// JSON Decode
			$user       = json_decode($userCredential->token, true);
			$email      = $user['email'];
			$userexists = $this->getUserByEmail($email);
		}

		if (empty((array) $userexists))
		{
			if (!$this->createUser($userDetail))
			{
				return false;
			}

			$email = $userDetail->email;
		}

		// Step 1. Create a meeting
		$meeting = $this->createMeeting($data);

		if (!$meeting->isValidResponse())
		{
			$this->setError($meeting->getError());

			return false;
		}

		$meetingData      = $meeting->getBody();
		$meetingData      = $meetingData['sco'];
		$this->meetingId  = $meetingData['scoId'];
		$this->meetingUrl = $meetingData['urlPath'];

		// Step 2 Use the acl-field-update call to set the meeting-expected-load value
		$permission = $this->updatePermission($this->meetingId, $this->getMeetingPermission());

		if (!$permission->isValidResponse())
		{
			$this->setError($permission->getError());

			return false;
		}

		// Add host to the meeting
		$rolePermission = $this->addUserTomeeting($email, $this->meetingId, 'host');

		if (!empty($rolePermission) && !$rolePermission->isValidResponse())
		{
			$this->setError($rolePermission->getError());

			return false;
		}
		elseif (empty($rolePermission))
		{
			return false;
		}

		$params                 = json_decode($this->params);
		$params['event_url']    = $this->meetingUrl;
		$params['event_sco_id'] = $this->meetingId;
		$this->params           = json_encode($params);

		return true;
	}

	/**
	 * create meeting
	 *
	 * @param   array  $data  meeting data
	 *
	 * @return  object
	 */
	private function createMeeting($data)
	{
		$beginDate   = $this->getFormattedDate($data['beginDate']);
		$endDate     = $this->getFormattedDate($data['onlineEndDate']);

		return $this->makeRequest('sco-update',
			array(
				'type'       => 'meeting',
				'name'       => urlencode($data['title']),
				'folder-id'  => $this->getScoId(),
				'date-begin' => urlencode($beginDate),
				'date-end'   => urlencode($endDate),
				'url-path'   => ''
			)
		);
	}

	/**
	 * update meeting
	 *
	 * @param   array  $data  The event data to be bind with the object
	 *
	 * @return  boolean  True on success
	 *
	 * @since   3.0.0
	 */
	private function update($data)
	{
		$beginDate   = $this->getFormattedDate($data['beginDate']);
		$endDate     = $this->getFormattedDate($data['onlineEndDate']);

		$result = $this->makeRequest('sco-update',
			array(
				'type'       => 'meeting',
				'name'       => urlencode($data['title']),
				'sco-id'     => $this->meetingId,
				'date-begin' => urlencode($beginDate),
				'date-end'   => urlencode($endDate),
				'url-path'   => $this->meetingUrl
			)
		);

		if (!$result->isValidResponse())
		{
			$this->setError($result->getError());

			return false;
		}

		return true;
	}

	/**
	 * Method to get the list of all the event
	 *
	 * @param   array  $query  filters used to retrieve meetings
	 *
	 * @return  array
	 *
	 * @since   3.0.0
	 */
	public function list(array $query = [])
	{
		$result = $this->makeRequest('report-bulk-objects');

		if (!$result->isValidResponse())
		{
			$this->setError($result->getError());

			return false;
		}

		$response = $result->getBody();

		$meetings = $response['reportBulkObjects'];
		$lists = array();

		foreach ($meetings as &$event)
		{
			if ($event['type'] == 'meeting' && !empty($event['dateCreated']))
			{
				$event['title']      = $event['name'];
				$event['id']         = $event['scoId'];
				$event['start_time'] = JT::utilities()->getFormatedDate($event['dateCreated']);

				array_push($lists, $event);
			}
		}

		return $lists;
	}

	/**
	 * Method to remove the meeting details
	 *
	 * @return  boolean  True on success
	 *
	 * @since   3.0.0
	 */
	public function delete()
	{
		$userDetail     = Factory::getUser();
		$userCredential = JT::techjoomlaapi()->loadByUserId($userDetail->id);

		if (!$userCredential && $user->email != $this->getUserName())
		{
			$this->setError(Text::_('PLG_JTICKETING_VENUE_ADOBE_CONNECT_USER_NOT_FOUND'));

			return false;
		}

		// JSON Decode
		$userData   = json_decode($userCredential->token, true);
		$email      = $userData['email'];
		$userexists = $this->getUserByEmail($email);

		if (empty((array) $userexists))
		{
			return false;
		}

		if (!$this->deleteMeeting($this->meetingId))
		{
			return false;
		}

		return true;
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
		$userCredential = JT::techjoomlaapi()->loadByUserId($attendee->owner_id);
		$email          = '';
		$userexists     = new stdClass;

		if ($userCredential)
		{
			// JSON Decode
			$user       = json_decode($userCredential->token, true);
			$email      = $user['email'];
			$userexists = $this->getUserByEmail($email);
		}

		if (empty((array) $userexists))
		{
			$attendeeData        = new stdClass;
			$attendeeData->email = $attendee->getEmail();
			$attendeeData->name  = $attendee->getFirstName() . " " . $attendee->getLastName();
			$attendeeData->id    = $attendee->owner_id;

			if (!$this->createUser($attendeeData))
			{
				return false;
			}

			$email = $attendeeData->email;
		}

		$rolePermission = $this->addUserToMeeting($email, $this->meetingId, 'view');

		if (!empty($rolePermission) && !$rolePermission->isValidResponse())
		{
			$this->setError($rolePermission->getError());

			return false;
		}
		elseif (empty($rolePermission))
		{
			return false;
		}

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
		$email          = '';
		$userCredential = JT::techjoomlaapi()->loadByUserId($attendee->owner_id);

		if (!$userCredential && $user->email != $this->getUserName())
		{
			$this->setError(Text::_('PLG_JTICKETING_VENUE_ADOBE_CONNECT_USER_NOT_FOUND'));

			return false;
		}

		// JSON Decode
		$user       = json_decode($userCredential->token, true);
		$email      = $user['email'];

		$removeUser = $this->removeUserFromMeeting($email, $this->meetingId);

		if (!empty($removeUser) && !$removeUser->isValidResponse())
		{
			$this->setError($removeUser->getError());

			return false;
		}
		elseif (empty($removeUser))
		{
			$this->setError(Text::_('PLG_JTICKETING_VENUE_ADOBE_CONNECT_ADOBE_USER_NOT_FOUND'));

			return false;
		}

		return true;
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
	 * Method to get Meeting attendance
	 *
	 * @return  array  Returns attendees who attended the event
	 *
	 * @since   3.0.0
	 */
	public function getAttendance()
	{
		$attendance = $this->getMeetingAttendance($this->meetingId);

		if (!empty($attendance) && !$attendance->isValidResponse())
		{
			$this->setError($attendance->getError());

			return false;
		}
		elseif (empty($attendance))
		{
			return false;
		}

		// Get attendee list from api call
		$attendance = $attendance->getBody();

		$meetingInfo = $this->getScoInfo($this->meetingId);

		if (!empty($meetingInfo) && !$meetingInfo->isValidResponse())
		{
			$this->setError($meetingInfo->getError());

			return false;
		}
		elseif (empty($meetingInfo))
		{
			return false;
		}

		// Get meeting details
		$meetingInfo = $meetingInfo->getBody();

		$licenseInfo = $this->makeRequest('common-info');

		if (!$licenseInfo->isValidResponse())
		{
			$this->setError($licenseInfo->getError());

			return false;
		}

		// Get license details
		$licenseBody  = $licenseInfo->getBody();
		$fromTimeZone = $licenseBody['common']['timeZoneJavaId'];
		$toTimeZone   = 'UTC';
		$attendees    = array();
		$attendee     = JT::table('attendees');

		$meetingStartTime = new DateTime($meetingInfo['sco']['dateBegin'], new DateTimeZone($fromTimeZone));
		$meetingEndTime   = new DateTime($meetingInfo['sco']['dateEnd'], new DateTimeZone($fromTimeZone));
		$meetingStartTime->setTimeZone(new DateTimeZone($toTimeZone));
		$meetingEndTime->setTimeZone(new DateTimeZone($toTimeZone));

		foreach ($attendance['reportMeetingAttendance'] as $participant)
		{
			$attendee->load(array('owner_email' => $participant['login'], 'event_id' => $this->integrationId));

			if (empty($attendee->id))
			{
				continue;
			}

			if ($attendee->id)
			{
				$startTime = new DateTime($participant['dateCreated'], new DateTimeZone($fromTimeZone));
				$endTime   = new DateTime($participant['dateEnd'],  new DateTimeZone($fromTimeZone));
				$startTime->setTimeZone(new DateTimeZone($toTimeZone));
				$endTime->setTimeZone(new DateTimeZone($toTimeZone));
				$interval  = $endTime->getTimestamp() - $startTime->getTimestamp();

				// If user enters meeting before meeting starts
				if ($meetingStartTime > $startTime)
				{
					$beforMeetingInterval = $meetingStartTime->getTimestamp() - $startTime->getTimestamp();
					$interval = ($interval - $beforMeetingInterval) < 0 ? 0 : ($interval - $beforMeetingInterval);
				}
				elseif ($endTime > $meetingEndTime)
				{
					// If user leaves meeting after meeting ends
					$afterMeetingInterval = $endTime->getTimestamp() - $meetingEndTime->getTimestamp();
					$interval = ($interval - $afterMeetingInterval) < 0 ? 0 : ($interval - $afterMeetingInterval);
				}

				if (array_key_exists($attendee->id, $attendees))
				{
					$attendees[$attendee->id]['spentTime'] += $interval;
					$attendees[$attendee->id]['checkin']   = $startTime->format('Y-m-d H:i:s');

					if ($meetingStartTime->format('Y-m-d H:i:s') > $attendees[$attendee->id]['checkin'])
					{
						$attendees[$attendee->id]['checkin'] = $meetingStartTime->format('Y-m-d H:i:s');
					}

					$attendee->id = 0;
				}
				else
				{
					$attendees[$attendee->id]['email']     = $participant['login'];
					$attendees[$attendee->id]['checkin']   = $startTime->format('Y-m-d H:i:s');
					$attendees[$attendee->id]['checkout']  = $endTime->format('Y-m-d H:i:s');
					$attendees[$attendee->id]['spentTime'] = $interval;

					if ($meetingEndTime->format('Y-m-d H:i:s') < $attendees[$attendee->id]['checkout'])
					{
						$attendees[$attendee->id]['checkout'] = $meetingEndTime->format('Y-m-d H:i:s');
					}

					$attendee->id = 0;
				}
			}
		}

		return $attendees;
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
		$params       = new Registry($this->params);
		$recordingUrl = $params->get('recording_url');
		$url          = '';

		if (!$recordingUrl && !$recordingUrl = $this->getMeetingRecording($this->meetingId))
		{
			return false;
		}

		$apiUser     = JT::techjoomlaapi();

		if ($recordingUrl)
		{
			$user           = Factory::getUser();
			$userCredential = JT::techjoomlaapi()->loadByUserId($user->id);

			if (!$userCredential && $user->email != $this->getUserName())
			{
				$this->setError(Text::_('PLG_JTICKETING_VENUE_ADOBE_CONNECT_USER_NOT_FOUND'));

				return false;
			}

			$userData = json_decode($userCredential->token, true);

			// JSON Decode
			$this->username    = $userData['email'];
			$this->password    = base64_decode($userData['password']);
			$url               = $url . $recordingUrl;

			// This is when we don't have login credentials of user and user have to go for forget password when this link gets open
			if ($this->password == 'xxxxx')
			{
				return $url;
			}

			$loggedUserInfo = $this->makeRequest('common-info');

			if (!$loggedUserInfo->isValidResponse())
			{
				$this->setError($loggedUserInfo->getError());

				return false;
			}

			$response   = $loggedUserInfo->getBody();
			$loginEmail = $response['common']['user']['login'];

			// This is for license user
			if ($loginEmail == $user->email)
			{
				return $url . "?session=" . $this->getBreezeCookie();
			}

			new JTicketingEventAdobe(JT::event($this->id));

			if ($this->getError())
			{
				$this->setError(Text::_('PLG_JTICKETING_VENUE_ADOBE_CONNECT_USER_NOT_CREDENTAILS'));

				return false;
			}

			if (!empty($this->getBreezeCookie()))
			{
				// Set session to seminar url
				$url    = $url . "?session=" . $this->getBreezeCookie();
			}
		}

		if (empty($url))
		{
			$this->setError(Text::_('PLG_JTICKETING_ADOBE_CONNECT_RECORDING_NOT_AVAILABLE'));

			return false;
		}

		return $url;
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
