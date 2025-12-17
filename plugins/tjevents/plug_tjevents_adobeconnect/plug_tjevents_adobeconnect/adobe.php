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
use Joomla\CMS\Http\Http;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\LogEntry;
use Joomla\CMS\Log\Logger\FormattedtextLogger;

/**
 * JTicketing event class for Adobe.
 *
 * @since  3.0.0
 */
class JTicketingEventAdobe extends JTicketingEventJticketing
{
	/**
	 * Username of the API credentials
	 *
	 * @var    string
	 * @since  3.0.0
	 */
	private $username = '';

	/**
	 * Password of the API credentials
	 *
	 * @var    string
	 * @since  3.0.0
	 */
	private $password = '';

	/**
	 * API end point
	 *
	 * @var    string
	 * @since  3.0.0
	 */
	private $apiPoint = '';

	/**
	 * The sco id of the to create an event event
	 *
	 * @var    string
	 * @since  3.0.0
	 */
	private $source_sco_id = null;

	/**
	 * Permission type to create an event
	 *
	 * @var    string
	 * @since  3.0.0
	 */
	private $meeting_permission = null;

	/**
	 * The HTTP request client
	 *
	 * @var    Http
	 * @since  3.0.0
	 */
	private $client = null;

	/**
	 * Adobe connect session holder
	 *
	 * @var    string
	 * @since  3.0.0
	 */
	private $breezecookie = null;

	/**
	 * Constructor activating the default information of the Adobe object
	 *
	 * @param   JTicketingEventJticketing  $event  The event object
	 * @param   JTicketingVenue            $venue  The venue object
	 *
	 * @since   3.0.0
	 */
	public function __construct(JTicketingEventJticketing $event, JTicketingVenue $venue = null)
	{
		parent::__construct($event->id);

		$venueObj = JT::venue($event->venue);

		if (empty($venueObj->id) && !is_null($venue))
		{
			$venueObj = $venue;
		}

		$params                   = new Registry($venueObj->getParams());
		$this->username           = $params->get('api_username');
		$this->password           = $params->get('api_password');
		$this->meeting_permission = $params->get('meeting_permission');
		$this->source_sco_id      = $params->get('source_sco_id');
		$this->root_folder        = null;
		$this->template_folder    = null;
		$this->client             = new Http;
		$this->apiPoint           = $params->get('host_url');

		$this->apiPoint .= '/api/xml';
		$this->makeAuth();
	}

	/**
	 * make auth-request with stored username and password
	 *
	 * https://helpx.adobe.com/adobe-connect/webservices/login.html
	 *
	 * @since  3.0.0
	 *
	 * @return  boolean
	 */
	private function makeAuth()
	{
		$response = $this->makeRequest('login', array('login' => $this->username, 'password' => $this->password));

		if ($response->isValidResponse())
		{
			$headers = $response->getHeader();
			$setCookie = isset($headers['set-cookie']) ? $headers['set-cookie'] : $headers['Set-Cookie'];
			$cookieHeader = $response->parseHeader($setCookie);
			$this->breezecookie = $cookieHeader[0]['BREEZESESSION'];

			return true;
		}

		$this->setError($response->getError());

		return false;
	}

	/**
	 * Make request
	 *
	 * @param   string  $action  action
	 * @param   array   $params  parameters
	 *
	 * @since  3.0.0
	 *
	 * @return  JTicketingEventAdobeResponse
	 */
	public function makeRequest($action, array $params = array())
	{
		$url = $this->apiPoint;
		$url = Uri::getInstance($url);
		$url->setQuery($params);
		$url->setVar('action', $action);
		$log = array();

		if ($this->breezecookie)
		{
			$url->setVar('session', $this->breezecookie);
		}

		try
		{
			$response = $this->client->get($url->toString());
		}
		catch (Exception $e)
		{
			$log['body']    = json_decode((string) $response->body, true);
			$log['message'] = $e->getMessage();
			$log['code']    = $e->getCode();
			$log['method']  = $action;
			$log['url']     = $url->toString();

			$this->logResponse($log, true);

			return new JTicketingEventAdobeResponse($response);
		}

		$responseObj    = new JTicketingEventAdobeResponse($response);
		$resBody        = $responseObj->getBody();
		$log['body']    = $resBody;
		$log['message'] = '';
		$log['method']  = $action;
		$log['code']    = $resBody['status']['code'];
		$log['url']     = $url->toString();

		$this->logResponse($log);

		return $responseObj;
	}

	/**
	 * Validate Credentials
	 *
	 * @return  Boolean
	 *
	 * @since   3.0.0
	 */
	public function isValidCredentials()
	{
		if (!$this->breezecookie)
		{
			return false;
		}

		return true;
	}

	/**
	 * Save user data
	 *
	 * @param   object  $userDetail  user details
	 *
	 * @since   3.0.0
	 *
	 * @return   true
	 */
	public function createUser($userDetail)
	{
		// When license user is not present in techjoomlaapi_users table
		if ($userDetail->email == $this->getUserName())
		{
			return true;
		}

		$intialName = $userDetail->name;

		$name       = explode(" ", $intialName);
		$firstName  = $name['0'];

		if (empty($name['1']))
		{
			$lastName = "-";
		}
		else
		{
			$lastName = $name['1'];
		}

		$password = JT::utilities()->generateRandomString(8);

		$result = $this->makeRequest('principal-update',
				array(
					'first-name'   => $firstName,
					'last-name'    => $lastName,
					'email'        => $userDetail->email,
					'password'     => $password,
					'type'         => 'user',
					'has-children' => 0
				)
			);

		$responseBody     = $result->getBody();

		if (!$result->isValidResponse())
		{
			$this->setError($result->getError());

			return false;
		}

		$data             = array();
		$data['email']    = $userDetail->email;
		$data['password'] = base64_encode($password);
		$userData         = json_encode($data);

		$profile            = array();
		$profile['user_id'] = $userDetail->id;
		$profile['token']   = $userData;

		try
		{
			$apiUser     = JT::techjoomlaapi();
			$credentials = $apiUser->loadByUserId($userDetail->id);

			if (!$apiUser->user_id)
			{
				$apiUser->save($profile);
			}
		}
		catch (Exception $e)
		{
		}

		return true;
	}

	/**
	 * Get user data
	 *
	 * @param   string  $userId  user id
	 *
	 * @since   3.0.0
	 *
	 * @return   array
	 */
	public function getUserAPIData($userId)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		$query->select('*');
		$query->from('`#__techjoomlaAPI_users` AS t');
		$query->where('t.user_id = ' . $db->quote($userId));
		$db->setQuery($query);

		return $db->loadAssoc();
	}

	/**
	 * Get Adobe user details by email id
	 *
	 * @param   string  $email  User email
	 *
	 * @return  mixed
	 */
	public function getUserByEmail($email)
	{
		$result = $this->makeRequest('principal-list', array('filter-email' => $email));

		if (!$result->isValidResponse())
		{
			$this->setError($result->getError());

			return false;
		}

		return $result;
	}

	/**
	 * Get Sco id configured with venue details
	 *
	 * @since  3.0.0
	 *
	 * @return  string
	 */
	public function getScoId()
	{
		return $this->source_sco_id;
	}

	/**
	 * create meeting room
	 *
	 * @param   string  $name  name
	 * @param   string  $url   url
	 *
	 * @since  3.0.0
	 *
	 * @return array
	 */
	public function createMeetingRoom($name, $url = '')
	{
		return $this->makeRequest('sco-update',
				array(
						'type'       => 'meeting',
						'name'       => urlencode($name),
						'folder-id'  => $this->getScoId(),
						'url-path'   => $url
				)
				);
	}

	/**
	 * Set meeting permissions
	 *
	 * @param   int     $scoId              sco id
	 * @param   string  $meetingPermission  permission
	 *
	 * @since  3.0.0
	 *
	 * @return mixed
	 */
	public function updatePermission($scoId, $meetingPermission)
	{
		return $this->makeRequest('permissions-update',
				array(
						'acl-id'        => $scoId,
						'principal-id' => 'public-access',
						'permission-id' => $meetingPermission
				)
				);
	}

	/**
	 * Add current user as a host to meeting
	 *
	 * @param   int     $email  userDetail
	 * @param   string  $scoId  scoId
	 * @param   string  $role   role
	 *
	 * @since  3.0.0
	 *
	 * @return mixed
	 */
	public function addUserToMeeting($email, $scoId, $role)
	{
		$userDetails = $this->getUserByEmail($email, true);

		if (!$userDetails)
		{
			return false;
		}

		$userDetails = $userDetails->getBody();
		$userDetails = $userDetails['principalList']['0'];

		return $this->makeRequest('permissions-update',
				array(
						'principal-id'  => $userDetails['principalId'],
						'acl-id'        => $scoId,
						'permission-id' => $role
				)
				);
	}

	/**
	 * Get meeting permission details
	 *
	 * @since  3.0.0
	 *
	 * @return  string
	 */
	public function getMeetingPermission()
	{
		return $this->meeting_permission;
	}

	/**
	 * Get the username from licecnse details
	 *
	 * @since  3.0.0
	 *
	 * @return  string
	 */
	public function getUserName()
	{
		return $this->username;
	}

	/**
	 * Method to log the HTTP response
	 *
	 * @param   array    $data   Data array to log the response
	 * @param   boolean  $error  Flag to indicate that this is an error
	 *
	 * @return  boolean  True on success
	 *
	 * @since   3.0.0
	 */
	public function logResponse($data, $error = false)
	{
		static $logApiResponse = null;
		static $logBody = 0;

		if (is_null($logApiResponse))
		{
			PluginHelper::importPlugin('tjevents', 'plug_tjevents_adobeconnect');
			$plugin = PluginHelper::getPlugin('tjevents', 'plug_tjevents_adobeconnect');
			$params = new Registry($plugin->params);
			$logApiResponse = $params->get('debug_integration', 1);
			$logBody = $params->get('log_response', 0);
		}

		if (!$logApiResponse)
		{
			return true;
		}

		if (!$logBody)
		{
			$data['body'] = '';
		}

		$format = '{DATETIME} | {PRIORITY} | {CLIENTIP} | {MESSAGE} | {METHOD | URL | CODE | RESPONSEMESSAGE}';
		$priority = Log::INFO;

		if ($error)
		{
			$priority = Log::ERROR;
		}

		$message = json_encode($data['body']) . " | " . $data['method'] . " | " . $data["url"] . " | " . $data['code'] . " | " . $data['message'];
		$options = array("text_file" => "adobconnect_event_debug.log", "text_file_no_php" => true, 'text_entry_format' => $format);
		$formatLogger = new FormattedtextLogger($options);
		$entry = new LogEntry($message, $priority);
		$formatLogger->addEntry($entry);

		return true;
	}

	/**
	 * delete meeting
	 *
	 * @param   int  $scoId  Sco Id
	 *
	 * @return array
	 */
	public function deleteMeeting($scoId)
	{
		$result = $this->makeRequest('sco-delete',
			array(
				'sco-id'     => $scoId
			)
		);

		if (!$result->isValidResponse())
		{
			$this->setError($result->getError());

			return false;
		}

		return $result;
	}

	/**
	 * Return the event join URL for participant/Host
	 *
	 * @param   JTicketingAttendee  $attendee  Attendee Object
	 *
	 * @return  string
	 *
	 * @since   3.0.0
	 */
	public function getJoinUrl(JTicketingAttendee $attendee)
	{
		$eventParams = new Registry($this->params);
		$url         = str_replace('/api/xml', '', $this->apiPoint);
		$url         = $url . $eventParams->get('event_url');

		// Auto connect to adobconnect when meeting launch
		try
		{
			$user           = Factory::getUser();
			$userCredential = JT::techjoomlaapi()->loadByUserId($user->id);

			if (!$userCredential && $user->email != $this->getUserName())
			{
				$this->setError(Text::_('PLG_JTICKETING_VENUE_ADOBE_CONNECT_USER_NOT_FOUND'));

				return false;
			}

			$userData       = json_decode($userCredential->token, true);

			// JSON Decode
			$this->username    = $userData['email'];
			$this->password    = base64_decode($userData['password']);

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

			$response = $loggedUserInfo->getBody();

			$loginEmail = $response['common']['user']['login'];

			// This is for license user
			if ($loginEmail == $user->email)
			{
				return $url . "?session=" . $this->breezecookie;
			}

			if (!$this->makeAuth())
			{
				$this->setError(Text::_('PLG_JTICKETING_VENUE_ADOBE_CONNECT_USER_NOT_CREDENTAILS'));

				return false;
			}

			if (!empty($this->breezecookie))
			{
				// Set session to meeting url
				$url    = $url . "?session=" . $this->breezecookie;
			}
		}
		catch (Exception $e)
		{
		}

		return $url;
	}

	/**
	 * Remove current user from meeting
	 *
	 * @param   string  $email  userDetail
	 * @param   int     $scoId  scoId
	 *
	 * @since  3.0.0
	 *
	 * @return mixed
	 */
	public function removeUserFromMeeting($email , $scoId)
	{
		$userDetails = $this->getUserByEmail($email, true);

		if (!$userDetails)
		{
			return false;
		}

		$userDetails = $userDetails->getBody();
		$userDetails = $userDetails['principalList']['0'];

		return $this->makeRequest('permissions-update',
				array(
						'principal-id'  => $userDetails['principalId'],
						'acl-id'        => $scoId,
						'permission-id' => 'remove'
				)
			);
	}

	/**
	 * Get the formatted date
	 *
	 * @param   string  $date  date of events
	 *
	 * @since  3.0.0
	 *
	 * @return  string
	 */
	public function getFormattedDate($date)
	{
		$timezone = new DateTimeZone(Factory::getConfig()->get('offset'));
		$dateTime = preg_split('/\s+/', $date);
		$date     = new DateTime($dateTime[0] . 'T' . $dateTime[1], $timezone);

		return $date->format(DateTime::ISO8601);
	}

	/**
	 * Get Recording url
	 *
	 * @param   int  $scoId  sco Id
	 *
	 * @return  mixed
	 */
	public function getMeetingRecording($scoId)
	{
		$recordingUrl = '';

		$recording = $this->makeRequest('sco-contents',
						array(
							'sco-id'  => $scoId,
							'filter-icon'  => 'archive',

						)
					);

		if (!$recording || !$recording->isValidResponse())
		{
			$this->setError($recording->getError());

			return false;
		}

		$recording = $recording->getBody();

		if (empty($recording['scos']))
		{
			$this->setError(Text::_('PLG_JTICKETING_ADOBE_CONNECT_RECORDING_NOT_AVAILABLE'));

			return false;
		}

		$recordingUrl = $recording['scos'][0]['urlPath'];
		$eventParams  = new Registry($this->params);
		$eventParams->set('recording_url', str_replace('/api/xml', '', $this->getApiPoint()) . $recordingUrl);
		$this->params = $eventParams->toString();

		$data           = array();
		$data['id']     = $this->id;
		$data['params'] = $this->params;

		if (!parent::save($data))
		{
			$this->setError(Text::_('PLG_JTICKETING_ADOBE_CONNECT_RECORDING_FAIL'));

			return false;
		}

		return str_replace('/api/xml', '', $this->getApiPoint()) . $recordingUrl;
	}

	/**
	 * Get url configured with venue details
	 *
	 * @since  3.0.0
	 *
	 * @return  string
	 */
	public function getApiPoint()
	{
		return $this->apiPoint;
	}

	/**
	 * Get Breeze cookie
	 *
	 * @return string
	 */
	public function getBreezeCookie()
	{
		return $this->breezecookie;
	}

	/**
	 * get list of users who attended meeting
	 *
	 * @param   int  $scoId  Meeting unique Id
	 *
	 * @return array
	 */
	public function getMeetingAttendance($scoId)
	{
		return $this->makeRequest('report-meeting-attendance',
			array(
				'sco-id'    => $scoId
				)
			);
	}

	/**
	 * Get meeting/seminar Details
	 *
	 * @param   int  $scoId  meeting/seminar Id
	 *
	 * @return array
	 */
	public function getScoInfo($scoId)
	{
		return  $this->makeRequest('sco-info',
			array(
				'sco-id' => $scoId,
			)
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
		$apiUser        = JT::techjoomlaapi();
		$userCredential = $apiUser->loadByUserId($attendee->owner_id);

		// Credentials array
		$details = '';

		if (!empty($userCredential))
		{
			$userData = json_decode($userCredential->token, true);
			$details .= Text::_('PLG_JTICKETING_ADOBE_CONNECT_USERNAME') . $userData['email'];
			$details .= '<br>' . Text::_('PLG_JTICKETING_ADOBE_CONNECT_PASSWORD') . base64_decode($userData['password']);
		}

		return $details;
	}
}
