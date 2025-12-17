<?php
/** 
 * 
 * https://github.com/googleapis/google-api-php-client install 
 * 
 * Developer.cloud.google.com create service account ... download the json file ... [PARAM 1]
 * note down the service account email look slike this: calendar-service-account@solar-nation-357807.iam.gserviceaccount.com [param 1.1]
 * also not down Unique id of the service account. looks like this: 104796633321957999923 [param 1.2]
 * then give is account wide access--
 * https://github.com/spatie/laravel-google-calendar/discussions/210
 * 
 * create calendar via email id of the org admin user looks like this: ashwin.datye@vowellms.com [PARAM 2] id.name@org.com 
 * then notedown calendar id [PARAM 3]
 * to that calendar, add above service account email id under settings-> give access to specific account
*/

use Joomla\CMS\Http\Http;
use Joomla\CMS\Http\Response;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Log\LogEntry;
use Joomla\CMS\Log\Logger\FormattedtextLogger;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;
use Joomla\CMS\Factory;
require JPATH_PLUGINS . '/tjevents/googlemeet/googlemeet/vendor/autoload.php';

use Google\Client;


/**
 * JTicketing event class for Google Meet.
 *
 * @since  3.0.0
 */
class JTicketingEventGooglemeet extends JTicketingEventJticketing implements JticketingEventOnline
{
	
	private $hostEmail;
	private $googleclientservice;
	private $serviceaccountjson;
	private $calendarid;

	private $meetingId;
	private $meetingUrl;

  	/**
	 * Constructor activating the default information of the google object
	 *
	 * @param   JTicketingEventJticketing  $event  The event object
	 * @param   JTicketingVenue            $venue  The venue object
	 *
	 * @since   3.0.0
	 */
	public function __construct(JTicketingEventJticketing $event, JTicketingVenue $venue = null)
	{
    	Log::addLogger( array( 'text_file' => 'googlemeet.txt', "text_file_no_php" => true), Log::ERROR);
		$this->logdata("_constructor  1.0   ");

		parent::__construct($event->id);
		$params;
		$venueObj = JT::venue($event->venue);

		if (empty($venueObj->id) && !is_null($venue))
		{
			$venueObj = $venue;
		}
		$params  = new Registry($venueObj->getParams());

		$this->serviceaccountjson = $params["serviceaccountjson"];
		$this->hostEmail  = $params->get('hostemail');
    	$this->calendarid = $params->get('calendarid');
		
		$creds = json_decode($this->serviceaccountjson, true);
		// print_r($this->serviceaccountjson);die;
		$this->logdata('Google cred: ' . (is_array($creds) ? implode(',', $creds) : $creds));
		try{
			//construct new google meet sdk client
			$client = new Google\Client();	
			$client->setAuthConfig($creds); //$this->serviceaccountjson ); //example string converted to in memory json file : 'solar-nation-357807-b529e8a12a1b.json');
			$client->setSubject($this->hostEmail);
			$client->setApplicationName("test_calendar");
			$client->setScopes(Google_Service_Calendar::CALENDAR);
			//$client->setAccessType('offline');
			$this->googleclientservice = new Google_Service_Calendar($client);


		}catch (Exception $e){
			$this->logdata( 'Google Message: ' . $e->getMessage());
		}

		if (!empty($event)) //its an event edit
		{
			$params = new Registry($event->getParams());
			$this->meetingId  = $params->get('googlemeetingid', 0);
			$this->meetingUrl = $params->get('join_url', '');
		} 

		$this->logdata("__construct(  Finish");

	}

  	public function isValidCredentials()
	{
		return true;
	}

  	public function save($originaldata)
	{
		// print_r($originaldata);die;
    	$this->logdata("Save() ");
		$this->logdata(implode(",", $originaldata));
		try{
      		if($originaldata['venuechoice'] == 'existing') {

				$this->logdata("Save()-- Edit 2.0");
				//existing event
				$event = $this->googleclientservice->events->get($this->calendarid, $this->meetingId);
				$this->logdata("event   ". $event->id);
				$event->summary = $originaldata['title'];
				$event->description = $originaldata['title'];
				$event->start = array(
						'dateTime' =>  Factory::getDate($data['startdate'], 'UTC')->format('Y-m-d\TH:i:s\Z') //$data["starttime"] //'2022-08-02T13:20:00+05:30'
					);
				$event->end = array (
						'dateTime' =>  Factory::getDate($data['enddate'], 'UTC')->format('Y-m-d\TH:i:s\Z') //$data["endtime"]//'2022-08-02T13:25:00+05:30'
				);
				
				$resultevent = $this->googleclientservice->events->patch($this->calendarid, $this->meetingId, $event,['sendUpdates'=>'all']);

				$this->logdata("EDITED meeting with id: ".$resultevent->id."  subject: ".$resultevent->summary);
				
			} else {
				$this->logdata("Save() NEW 1.0");
				$event = new Google_Service_Calendar_Event(array(
				'summary' => $originaldata['title'], //$data["eventname"],
				'description' => $originaldata['title'], //$data["eventname"],
				'start' => array(
					'dateTime' =>  Factory::getDate($originaldata['startdate'], 'UTC')->format('Y-m-d\TH:i:s\Z') //$data["starttime"] //'2022-08-02T13:20:00+05:30'
				),
				'end' => array(
					'dateTime' =>  Factory::getDate($originaldata['enddate'], 'UTC')->format('Y-m-d\TH:i:s\Z') //$data["endtime"]//'2022-08-02T13:25:00+05:30'
				),
				"conferenceData" => array(
					"createRequest" => array(
					  "conferenceSolutionKey" => array(
					  "type" => "hangoutsMeet" 
					  ),
					  "requestId" => "xrandoxrfm".rand(100000, 999999)
					)
				)
				));

				
				$this->logdata("save 1.1");

				$event = $this->googleclientservice->events->insert($this->calendarid, $event, ['sendUpdates'=>'all' ,'conferenceDataVersion'=>'1']);

				$this->logdata("save 1.2");

				$newmeetingdata = array(
					'googlemeetingid' => $event->id,
					'host_id' => 'GOOGLE HOST',
					'start_time' => Factory::getDate($data['startdate'], 'UTC')->format('Y-m-d\TH:i:s\Z'),
					'join_url' => $event->hangoutLink,
				);

				$this->logdata("NEW Google meeting with id: ".$newmeetingdata['googlemeetingid']." .. JOIN URL:  ".$newmeetingdata['join_url']);

				$originaldata = array_merge( $originaldata, $newmeetingdata);
				$this->params = json_encode($originaldata);
			}

		} catch (Exception $e){
			$this->logdata( 'Google Error: ' . $e->getMessage());
			return false;
		}

		return true;
	}

  
	/**
	 * Method to get the log the data
	 *
	 *
	 * @param   array  $data store the log
	 *
	 * @return  array
	 *
	 * @since   3.0.0
	 */
	public function logdata($data)
	{
    	Log::add($data, Log::ERROR);
		return true;
	}


	/**
	 * Method to get the list of all the event
	 *
	 *
	 * @param   array  $query  filters used to retrieve meetings
	 *
	 * @return  array
	 *
	 * @since   3.0.0
	 */
	public function list(array $query = [])
	{
		$this->logdata("public function list(");
		 return null;
	}

	

	
	/**
	 * Method to remove the meeting details
	 *
	 *
	 * @return  boolean  True on success
	 *
	 * @since   3.0.0
	 */
	public function delete()
	{
		$this->logdata("delete meeting");
		$response = $this->googleclientservice->events->delete($this->calendarid, $this->meetingId);
		$this->logdata($response. "  emply resposnse");
		return true;
	}

	/**
	 * Method to add registrant against event
	 * @param   JTicketingAttendee  $attendee  Attendee Object
	 * @param   array               $data      Registrant data
	 *
	 * @return  boolean  True on success
	 *
	 * @since   3.0.0
	 */
	public function addAttendee(JTicketingAttendee $attendee, $data = [])
	{
		$this->logdata("public function addAttendee(");
		$attendeeData = array();
		$attendeeData['email']      = $attendee->getEmail();
		$attendeeData['first_name'] = $attendee->getFirstName();
		$attendeeData['last_name']  = $attendee->getLastName();

		$this->logdata(implode(",", $attendeeData));
		$this->logdata($this->meetingId);

		$event = $this->googleclientservice->events->get($this->calendarid, $this->meetingId);

		if (!empty($attendeeData) && isset($attendeeData['email']))
		{
			if(isset($event->attendees)){
				$event->attendees[] = array('email' => $attendeeData['email'],'displayName' => $attendeeData['first_name']." ".$attendeeData['last_name']);
			}else{
				$event->attendees = array(
					array('email' => $attendeeData['email'],'displayName' => $attendeeData['first_name']." ".$attendeeData['last_name']),
				); 
			}	
			
		}

		$this->logdata("addattendee 2.1");
		$resultevent = $this->googleclientservice->events->patch($this->calendarid, $this->meetingId, $event,['sendUpdates'=>'all']);
		

		$newParams = array();
		
		$newParams['join_url'] = $resultevent->hangoutLink;
		$attendeeParams = $attendee->getParams();
		$attendeeParams->set('googlemeet', $newParams);
		$attendee->setParams($attendeeParams);

		if (!$attendee->save())
		{
			$this->logdata($attendee->getError());
			return false;
		}

		return true;
	}

	/**
	 * Method to get Meeting attendance
	 *
	 *
	 * @return  boolean|Array  False on failure and return attendee arrey on success
	 *
	 * @since   3.0.0
	 */
	public function getAttendance()
	{
		$event = $this->googleclientservice->events->get($this->calendarid, $this->meetingId);

		$creds = json_decode($this->serviceaccountjson, true);
		$newClient = new Google\Client();   
		$newClient->setAuthConfig($creds);
		$newClient->setSubject($this->hostEmail);
		$newClient->setApplicationName("test_calendar");
		$newClient->setScopes([
			'https://www.googleapis.com/auth/meetings.space.created',
			'https://www.googleapis.com/auth/meetings.space.readonly',
			'https://www.googleapis.com/auth/profile.emails.read',
			'https://www.googleapis.com/auth/userinfo.email',
			'https://www.googleapis.com/auth/contacts',
		]);

		$this->http_client = $newClient->authorize();

		try {

			// Get all conference records
			$conferenceResponse = $this->http_client->request('GET', 'https://meet.googleapis.com/v2/conferenceRecords', [
				'query' => [
					'filter' => 'space.meeting_code="'.$event->conferenceData->conferenceId.'"'
				]
			]);

			$conferenceRecords = json_decode($conferenceResponse->getBody(), true);

			$attendees = array();
			foreach ($conferenceRecords['conferenceRecords'] as $key => $record)
			{
				
				// Get all participants
				$participantsResponse = $this->http_client->request('GET', 'https://meet.googleapis.com/v2/'.$record['name'].'/participants', [
					'query' => [
						'parent' => $record['name']
						]
					]);
					
					$participants = json_decode($participantsResponse->getBody(), true);

				if(!empty($participants['participants']))
				{
					foreach ($participants['participants'] as $key => $participantRecord)
					{
						$signedinUserId = $participantRecord['signedinUser']['user']; 
						$explodeData = explode("/", $signedinUserId);
						$userID = $explodeData[1];

						// Get email id of user.
						$resourceName = 'people/' . $userID;
						$personFields = 'emailAddresses';
						$response = $this->http_client->request('GET', 'https://people.googleapis.com/v1/' . $resourceName, [
							'query' => [
								'personFields' => $personFields
								]
							]);
							
						$personData = json_decode($response->getBody(), true);
						$personEmail = $personData['emailAddresses'][0]['value'];

						if(!empty($personEmail))
						{
							$attendee = JT::table('attendees');
							$attendee->load(array('owner_email' => $personEmail, 'event_id' => $this->integrationId));
							$attendeeId = $attendee->id;
							
							// If attendee is not present
							if (!$attendeeId)
							{
								continue;
							}
							
							$startDate    = Factory::getDate($participantRecord['earliestStartTime'])->toSql();
							$endDate      = Factory::getDate($participantRecord['latestEndTime'])->toSql();
							
							$formatedStartDate = DateTime::createFromFormat('Y-m-d H:i:s', $startDate);
							$formatedEndDate = DateTime::createFromFormat('Y-m-d H:i:s', $endDate);
							$timeSpent = $formatedEndDate->getTimestamp() - $formatedStartDate->getTimestamp();
							
							$attendees[$attendeeId]['email'] = $personEmail;
							$attendees[$attendeeId]['checkin'] = $startDate;
							$attendees[$attendeeId]['checkout'] = $endDate;
							$attendees[$attendeeId]['spentTime'] = $timeSpent;
						}
					}
				}
			}
			
			return $attendees;
		} catch (Exception $e) {
			echo "Error: " . $e->getMessage();
		}
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
		$event = $this->googleclientservice->events->get($this->calendarid, $this->meetingId);

		$creds = json_decode($this->serviceaccountjson, true);
		$newClient = new Google\Client();   
		$newClient->setAuthConfig($creds);
		$newClient->setSubject($this->hostEmail);
		$newClient->setApplicationName("test_calendar");
		$newClient->setScopes([
			'https://www.googleapis.com/auth/meetings.space.created',
			'https://www.googleapis.com/auth/meetings.space.readonly'
		]);

		$this->http_client = $newClient->authorize();

		try {
			// Get all conference records
			$conferenceResponse = $this->http_client->request('GET', 'https://meet.googleapis.com/v2/conferenceRecords', [
				'query' => [
					'filter' => 'space.meeting_code="'.$event->conferenceData->conferenceId.'"'
				]
			]);

			$conferenceRecords = json_decode($conferenceResponse->getBody(), true);

			foreach ($conferenceRecords['conferenceRecords'] as $key => $record)
			{
				// Get Recording
				$recordingsResponse = $this->http_client->request('GET', 'https://meet.googleapis.com/v2/'.$record['name'].'/recordings', [
					'query' => [
						'parent' => $record['name']
					]
				]);
			
				$recordings = json_decode($recordingsResponse->getBody(), true);

				if(!empty($recordings['recordings']))
				{
					foreach ($recordings['recordings'] as $key => $recordingsRecord)
					{
						return $recordingsRecord['driveDestination']['exportUri'];
					}
				}
			}

			return false;
		} catch (Exception $e) {
			echo "Error: " . $e->getMessage();
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
		$this->logdata(" deleteAttendee(");
		$attendeeData = array();
		$attendeeData['email']      = $attendee->getEmail();
		

		$this->logdata(implode(",", $attendeeData));
		$this->logdata($this->meetingId);

		$event = $this->googleclientservice->events->get($this->calendarid, $this->meetingId);
		$this->logdata("delete 2.1");

		$localattendees = $event->attendees;

		//$event->attendees = array_merge($event->attendees, $newAttendee);

		foreach($localattendees as $k => $val) { //iterate and remove the attendee by email
			if($val['email'] == $attendee->getEmail()) {
				unset($localattendees[$k]);
				break;
			}
		}
		$event->attendees = $localattendees;
		$resultevent = $this->googleclientservice->events->patch($this->calendarid, $this->meetingId, $event,['sendUpdates'=>'all']);
		

		$newParams = array();
		
		$newParams['join_url'] = $resultevent->hangoutLink;
		$attendeeParams = $attendee->getParams();
		$attendeeParams->set('googlemeet', $newParams);
		$attendee->setParams($attendeeParams);

		if (!$attendee->save())
		{
			$this->logdata($attendee->getError());
			return false;
		}

		return true;
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
		$this->logdata("public function get join URL()");
    	//$this->logdata("attendee join URL");
		return  $this->meetingUrl;
	}

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
