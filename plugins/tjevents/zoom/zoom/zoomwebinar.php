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
 * JTicketing event class for Zoom Webinar.
 *
 * @since  3.0.0
 */
class JTicketingEventZoomWebinar extends JTicketingEventZoom implements JticketingEventOnline
{
	/**
	 * Zoom webinar ID
	 *
	 * @var    string
	 * @since  3.0.0
	 */
	private $webinarId;

	/**
	 * Zoom webinar start URL
	 *
	 * @var    string
	 * @since  3.0.0
	 */
	private $startUrl;

	/**
	 * Zoom webinar join URL
	 *
	 * @var    string
	 * @since  3.0.0
	 */
	private $joinUrl;

	/**
	 * Zoom webinar details
	 *
	 * @var    object
	 * @since  3.0.0
	 */
	private $zoomWebinar;

	/**
	 * Zoom webinar type
	 *
	 * 5 - Webinar.
	 * 6 - Recurring webinar with no fixed time.
	 * 9 - Recurring webinar with a fixed time.
	 *
	 * @var    int
	 * @since  3.0.0
	 */
	private $type;

	/**
	 * Zoom webinar status
	 *
	 * @var    string
	 * @since  3.0.0
	 */
	private $status;

	/**
	 * Constructor activating the default information of the webinar
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

		$this->webinarId = $params->get('zoom.id', 0);
		$this->startUrl  = $params->get('zoom.start_url', '');
		$this->joinUrl   = $params->get('zoom.join_url', '');
		$this->type      = (int) $params->get('zoom.type', 2);
		$this->status    = $params->get('zoom.status', '');
	}

	/**
	 * Method to create/update webinar on the zoom cloud
	 *
	 * @param   array  $data  The webinar data to be bind with the object
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

			if ($this->webinarId != $data['existing_event'])
			{
				$this->webinarId = $data['existing_event'];
			}

			return $this->update($data);
		}

		return $this->create($this->getHostUser(), $data);
	}

	/**
	 * Method to get the list of all the webinar
	 *
	 * https://marketplace.zoom.us/docs/api-reference/zoom-api/webinars/webinars
	 *
	 * @param   array  $query  filters used to retrieve webinars
	 *
	 * @return  array
	 *
	 * @since   3.0.0
	 */
	public function list(array $query = [])
	{
		if (empty($query['type']))
		{
			// Only future webinars
			$query['type'] = 'upcoming';
		}

		$webinars = array();
		$response = $this->getData("users/{$this->getHostUser()}/webinars", $query);

		if ($response['code'] != 200)
		{
			$this->setError($response['message']);

			return $webinars;
		}

		$webinars = $response['body']['webinars'];

		// Now add the title key in the array to comply with the standard version of response
		foreach ($webinars as &$webinar)
		{
			$webinar['title'] = $webinar['topic'];
		}

		return $webinars;
	}

	/**
	 * Method to create webinar on the zoom cloud
	 *
	 * https://marketplace.zoom.us/docs/api-reference/zoom-api/webinars/webinarcreate
	 *
	 * @param   string  $userId  Host id of the webinar
	 * @param   array   $data    Webinar data
	 *
	 * @return  boolean  True on success
	 *
	 * @since   3.0.0
	 */
	private function create(string $userId, $data)
	{
		$data = $this->prepareWebinarData($data);
		$data = $this->postData("users/{$userId}/webinars", $data);

		if ($data['code'] != 201)
		{
			$this->setError($data['message']);

			return false;
		}

		return $this->updateParams($data['body']);
	}

	/**
	 * Method to get the details of single webinar
	 *
	 * https://marketplace.zoom.us/docs/api-reference/zoom-api/webinars/webinar
	 *
	 * @return  array|false  Array of webinar details on success false otherwise
	 *
	 * @since   3.0.0
	 */
	private function webinar()
	{
		if (empty($this->zoomWebinar))
		{
			$data = $this->getData("webinars/{$this->webinarId}");

			if ($data['code'] != 200)
			{
				$this->setError($data['message']);

				return false;
			}

			$this->zoomWebinar = $data['body'];
		}

		return $this->zoomWebinar;
	}

	/**
	 * Method to remove the webinar details
	 *
	 * https://marketplace.zoom.us/docs/api-reference/zoom-api/webinars/webinardelete
	 *
	 * @return  boolean  True on success
	 *
	 * @since   3.0.0
	 */
	public function delete()
	{
		$data = $this->deleteData("webinars/{$this->webinarId}");

		if ($data['code'] != 204)
		{
			$this->setError($data['message']);

			return false;
		}

		return true;
	}

	/**
	 * Method to update the webinar details
	 *
	 * https://marketplace.zoom.us/docs/api-reference/zoom-api/webinars/webinarupdate
	 *
	 * @param   array  $data  Webinar data
	 *
	 * @return  boolean True on success
	 *
	 * @since   3.0.0
	 */
	private function update($data)
	{
		$dataParams = $data['params'];
		$data       = $this->prepareWebinarData($data);
		$data       = $this->patchData("webinars/{$this->webinarId}", $data);

		if ($data['code'] != 204)
		{
			$this->setError($data['message']);

			return false;
		}

		// Reset the webinar data
		$this->zoomWebinar = null;
		$data = $this->webinar();

		if ($data)
		{
			return $this->updateParams($data, $dataParams);
		}

		return false;
	}

	/**
	 * Method to add registrant against webinar
	 *
	 * https://marketplace.zoom.us/docs/api-reference/zoom-api/webinars/webinarregistrantcreate
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

		$response = $this->postData("webinars/{$this->webinarId}/registrants", $attendeeData);

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
	 * Method to get webinar attendance
	 *
	 * https://marketplace.zoom.us/docs/api-reference/zoom-api/reports/reportwebinarparticipants
	 *
	 * @return  boolean|Array  False on failure and return attendee arrey on success
	 *
	 * @since   3.0.0
	 */
	public function getAttendance()
	{
		$response = $this->getData("report/webinars/{$this->webinarId}/participants");

		if ($response['code'] != 200)
		{
			$this->setError($response['message']);

			return false;
		}

		$participants = $response['body']['participants'];
		$attendees = array();

		foreach ($participants as $participant)
		{
			/* @var $attendee JTicketingTableAttendees */
			$attendee = JT::table('attendees');
			$attendee->load(array('owner_email' => $participant['user_email'], 'event_id' => $this->integrationId));

			if (!$attendee)
			{
				continue;
			}

			$startDate = Factory::getDate($participant['join_time'])->toSql();

			if (!empty ($attendees[$attendee->id]['checkin']) && ($startDate > $attendees[$attendee->id]['checkin']))
			{
				$startDate = $attendees[$attendee->id]['checkin'];
			}

			$endDate = Factory::getDate($participant['leave_time'])->toSql();

			if (!empty ($attendees[$attendee->id]['checkout']) && ($endDate < $attendees[$attendee->id]['checkout']))
			{
				$endDate = $attendees[$attendee->id]['checkout'];
			}

			$attendees[$attendee->id]['email'] = $participant['user_email'];
			$attendees[$attendee->id]['checkin'] = $startDate;
			$attendees[$attendee->id]['checkout'] = $endDate;
			$attendees[$attendee->id]['registrantId'] = $participant['user_id'];
			$attendees[$attendee->id]['spentTime'] += $participant['duration'];
		}

		return $attendees;
	}

	/**
	 * Method to delete registrant against webinar
	 *
	 * https://marketplace.zoom.us/docs/api-reference/zoom-api/webinars/webinarregistrantstatus
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
		$data = array();
		$data['id']    = $attendeeParams->get('zoom.registrant_id', '');
		$data['email'] = $attendee->owner_email;

		if (!$data['id'] || !$data['email'])
		{
			return false;
		}

		$attendeeData = array();
		$attendeeData['action'] = 'cancel';
		$attendeeData['registrants'][] = $data;
		$response = $this->putData("webinars/{$this->webinarId}/registrants/status", $attendeeData);

		if ($response['code'] != 204)
		{
			$this->setError($response['message']);

			return false;
		}

		return true;
	}

	/**
	 * This method prepare the params data to be stored against the webinar
	 *
	 * @param   array  $webinarData  Zoom webinar details
	 *
	 * @param   array  $originalParams  original params in database
	 *
	 * @return  boolean  True on success
	 *
	 * @since   3.0.0
	 */
	private function updateParams(array $webinarData, $originalParams = array())
	{
		$paramData = array(
				'id' => $webinarData['id'],
				'host_id' => $webinarData['host_id'],
				'type' => $webinarData['type'],
				'status'   => $webinarData['status'],
				'start_time' => $webinarData['start_time'],
				'start_url' => $webinarData['start_url'],
				'join_url' => $webinarData['join_url'],
				'password' => $webinarData['password'],
				// @FIXME use encrypted password
				'h323_password' => $webinarData['h323_password'],
				'pstn_password' => $webinarData['pstn_password'],
				'encrypted_password' => $webinarData['encrypted_password'],
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
	 * This method prepare the webinar data to be created on the zoom
	 *
	 * @param   array  $data  webinar data
	 *
	 * @return  array
	 *
	 * @since   3.0.0
	 */
	private function prepareWebinarData($data)
	{
		/**
		 * Webinar start time. We support two formats for `start_time` - local time and GMT.
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

		return array(
				'params'=>$data['params'],
				'topic' => $data['title'],
				'start_time' => Factory::getDate($data['startdate'], 'UTC')->format('Y-m-d\TH:i:s\Z'),
				'agenda' => (strlen($data['long_description']) > 1000) ? '' : $data['long_description'],
				'type'   => 5,
				// 'duration' => ((Factory::getDate($data['enddate'], 'UTC')->toUnix() - Factory::getDate($data['startdate'], 'UTC')->toUnix()) / 60),
				'settings' => array(
									"approval_type" => ($meetingInViewPage)?2:$params->get('viewmeeting', 0),
									"registrants_confirmation_email" => false)
				);
	}

	/**
	 * Return the online provider webinar id
	 *
	 * @return  string
	 *
	 * @since   3.0.0
	 */
	public function getOnlineEventId()
	{
		return $this->webinarId;
	}

	/**
	 * Return the webinar join URL for participant
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
	 * Method to get Webinar Recording Url
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

		$response = $this->patchData("meetings/{$this->webinarId}/recordings/settings", array('viewer_download' => $download, 'password' => ''));

		if ($response['code'] != 204)
		{
			$this->setError($response['message']);

			return false;
		}

		$params = new Registry($this->getParams());

		if ($params->get('zoom.recording_url', ''))
		{
			return str_replace("/download/","/play/",$params->get('zoom.recording_url', ''));
		}

		$response = $this->getData("webinars/{$this->webinarId}/recordings");

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
