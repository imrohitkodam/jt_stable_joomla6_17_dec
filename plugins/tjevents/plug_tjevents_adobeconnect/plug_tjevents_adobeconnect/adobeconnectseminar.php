<?php
/**
 * @package     JTicketing
 * @subpackage  com_jticketing
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\Registry\Registry;

defined('_JEXEC') or die();


/**
 * JTicketing event class for Adobeconnect seminars.
 *
 * @since  3.0.0
 */
class JTicketingEventAdobeConnectseminar extends JTicketingEventAdobe implements JticketingEventOnline
{
	/**
	 * Adobe seminar room ID
	 *
	 * @var    int
	 * @since  3.0.0
	 */
	private $seminarId = 0;

	/**
	 * Adobe folder Id
	 *
	 * @var    int
	 * @since  3.0.0
	 */
	private $sourceId = 0;

	/**
	 * Adobe seminar url
	 *
	 * @var    string
	 * @since  3.0.0
	 */
	private $seminarUrl = '';

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

		$this->seminarId  = $params->get('event_sco_id', 0);
		$this->seminarUrl = $params->get('event_url', '');
		$this->sourceId   = $params->get('event_source_sco_id', 0);
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

			if ($this->seminarId != $data['existing_event'])
			{
				$this->seminarId = $data['existing_event'];
			}

			return $this->update($data);
		}

		return $this->create($data);
	}

	/**
	 * Method to create webinar on the adobe cloud
	 *
	 * https://blogs.adobe.com/connectsupport/adobe-connect-9-1-seminar-session-creation-via-the-xml-api/
	 *
	 * https://helpx.adobe.com/in/adobe-connect/webservices/seminar-session-sco-update.html
	 *
	 * @param   array  $data  Seminar data
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

		if ($userCredential)
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

		// Step 1. Create a seminar room
		$meetingRoom = $this->createMeetingRoom($data['title']);

		if (!$meetingRoom->isValidResponse())
		{
			$this->setError($meetingRoom->getError());

			return false;
		}

		$meetingRoomData = $meetingRoom->getBody();
		$meetingRoomData = $meetingRoomData['sco'];

		// Step 2 Use the acl-field-update call to set the seminar-expected-load value
		$permission = $this->updatePermission($meetingRoomData['scoId'], $this->getMeetingPermission());

		if (!$permission->isValidResponse())
		{
			$this->setError($permission->getError());

			return false;
		}

		// Add host to the seminar room

		$rolePermission = $this->addUserToMeeting($email, $meetingRoomData['scoId'], 'host');

		if (!$rolePermission || !$rolePermission->isValidResponse())
		{
			$this->setError($rolePermission->getError());

			return false;
		}

		// Step 3 Use the sco-update call to create the sco for the Seminar Session:
		$seminarScoData = $this->createSeminarSco($data, $meetingRoomData['scoId']);
		$seminarData    = $seminarScoData->getBody();

		if (!$seminarScoData->isValidResponse())
		{
			$this->setError($seminarScoData->getError());

			return false;
		}

		// Step 4 Use the seminar-session-sco-update call to set the Session’s date/time as well as assign it to a Seminar Room
		$seminarDetails = $this->updateSeminarDetails($meetingRoomData['scoId'], $data, $seminarData['sco']['scoId']);

		if (!$seminarDetails->isValidResponse())
		{
			$this->setError($seminarDetails->getError());

			return false;
		}

		$aclUpdate = $this->updateAcl($data, $seminarData['sco']['scoId']);

		if (!$aclUpdate->isValidResponse())
		{
			$this->setError($aclUpdate->getError());

			return false;
		}

		$params                        = json_decode($this->params);
		$params['event_url']           = $meetingRoomData['urlPath'];
		$params['event_source_sco_id'] = $meetingRoomData['scoId'];
		$params['event_sco_id']        = $seminarData['sco']['scoId'];
		$this->params                  = json_encode($params);

		return true;
	}

	/**
	 * Method to update event on the Adobeconnect cloud
	 *
	 * @param   array  $data  The event data to be bind with the object
	 *
	 * @return  boolean  True on success
	 *
	 * @since   3.0.0
	 */
	public function update($data)
	{
		// Update the seminar details
		$seminarDetails = $this->updateSeminarDetails($this->sourceId, $data, $this->seminarId);

		if (!$seminarDetails->isValidResponse())
		{
			$this->setError($seminarDetails->getError());

			return false;
		}

		$aclUpdate = $this->updateAcl($data, $this->seminarId);

		if (!$aclUpdate->isValidResponse())
		{
			$this->setError($aclUpdate->getError());

			return false;
		}

		return true;
	}

	/**
	 * Create a seminar details
	 *
	 * @param   array  $data      Event data
	 * @param   array  $sourceId  Folder Id
	 *
	 * @since   3.0.0
	 *
	 * @return   JTicketingEventAdobeResponse
	 */
	private Function createSeminarSco($data, $sourceId)
	{
		return $this->makeRequest('sco-update',
				array(
						'type'       => 'seminarsession',
						'name'       => urlencode($data['title']),
						'folder-id'  => $sourceId,
					)
				);
	}

	/**
	 * Update the metadata of seminar
	 *
	 * @param   int    $permissionId  Permission id
	 * @param   array  $data          Event data
	 * @param   int    $scoId         Seminar Sco data
	 *
	 * @since   3.0.0
	 *
	 * @return   JTicketingEventAdobeResponse
	 */
	private Function updateSeminarDetails($permissionId, $data, $scoId)
	{
		$beginDate = $this->getFormattedDate($data['beginDate']);
		$endDate   = $this->getFormattedDate($data['onlineEndDate']);

		return $this->makeRequest('seminar-session-sco-update',
				array(
						'sco-id'        => $scoId,
						'source-sco-id' => $permissionId,
						'parent-acl-id' => $permissionId,
						'date-begin'    => urlencode($beginDate),
						'date-end'      => urlencode($endDate),
					)
				);
	}

	/**
	 * Update the ACL of seminar
	 *
	 * @param   array  $data   Event data
	 * @param   int    $scoId  Seminar API response
	 *
	 * @since   3.0.0
	 *
	 * @return   JTicketingEventAdobeResponse
	 */
	private function updateAcl($data, $scoId)
	{
		$beginDate = $this->getFormattedDate($data['beginDate']);
		$endDate   = $this->getFormattedDate($data['onlineEndDate']);
		$ticket    = $data['tickettypes'];

		if ($ticket['tickettypes0']['unlimited_seats'] == 1)
		{
			$ticketCount = 1000000000;
		}
		else
		{
			$ticketCount = $ticket['tickettypes0']['available'];
		}

		return $this->makeRequest('acl-field-update',
				array(
						'acl-id'     => $scoId,
						'field-id'   => '311',
						'value'      => $ticketCount,
						'date-begin' => urlencode($beginDate),
						'date-end'   => urlencode($endDate)
					)
				);
	}

	/**
	 * Method to get the list of all the event
	 *
	 * @param   array  $query  filters used to retrieve seminars
	 *
	 * @return  array
	 *
	 * @since   3.0.0
	 */
	public function list(array $query = [])
	{
		$result = $this->makeRequest('report-bulk-objects');

		if (!$result || !$result->isValidResponse())
		{
			$this->setError($result->getError());

			return false;
		}

		$response = $result->getBody();

		$meetings = $response['reportBulkObjects'];
		$lists = array();

		foreach ($meetings as &$event)
		{
			if ($event['type'] == 'meeting' && $event['dateCreated'])
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
	 * Method to remove the seminar details
	 *
	 * @return  array
	 *
	 * @since   3.0.0
	 */
	public function delete()
	{
		$userDetail     = Factory::getUser($userId);
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

		if (!$this->deleteMeeting($this->sourceId))
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
		$email          = '';
		$userCredential = JT::techjoomlaapi()->loadByUserId($attendee->owner_id);

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

		$rolePermission = $this->addUserToMeeting($email, $this->sourceId, 'view');

		if (!$rolePermission || !$rolePermission->isValidResponse())
		{
			$this->setError($rolePermission->getError());

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

		$removeUser = $this->removeUserFromMeeting($email, $this->sourceId);

		if (!$removeUser || !$removeUser->isValidResponse())
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
		return $this->seminarId;
	}

	/**
	 * Method to get seminar attendance
	 *
	 * @return  array  Returns attendees who attended the event
	 *
	 * @since   3.0.0
	 */
	public function getAttendance()
	{
		$attendance = $this->getMeetingAttendance($this->sourceId);

		if (!$attendance || !$attendance->isValidResponse())
		{
			$this->setError($attendance->getError());

			return false;
		}

		// Get attendee list from api call
		$attendance = $attendance->getBody();

		$meetingInfo = $this->getScoInfo($this->seminarId);

		if (!$meetingInfo || !$meetingInfo->isValidResponse())
		{
			$this->setError($meetingInfo->getError());

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

			if (!$attendee)
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
	 * Method to get seminar recording Url
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

		if (!$recordingUrl && !$recordingUrl = $this->getMeetingRecording($this->sourceId))
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
