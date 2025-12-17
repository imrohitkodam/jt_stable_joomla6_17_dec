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

JLoader::registerNamespace('Firebase\JWT', JPATH_PLUGINS . '/tjevents/zoom/zoom/Firebase', false, false, 'psr4');

use Firebase\JWT\JWT;
use Joomla\CMS\Http\Http;
use Joomla\CMS\Http\Response;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;
use Joomla\CMS\Log\LogEntry;
use Joomla\CMS\Log\Logger\FormattedtextLogger;

/**
 * JTicketing event class for Zoom.
 *
 * @since  3.0.0
 */
class JTicketingEventZoom extends JTicketingEventJticketing
{
	/**
	 * API key of the zoom integration
	 *
	 * @var    string
	 * @since  3.0.0
	 */
	private $apiKey = '';

	/**
	 * API secret of the zoom integration
	 *
	 * @var    string
	 * @since  3.0.0
	 */
	private $apiSecret = '';

	/**
	 * API host user id
	 * This is default user id stored agianst the venue
	 *
	 * @var    string
	 * @since  3.0.0
	 */
	private $hostUser = '';

	/**
	 * API end point
	 *
	 * @var    string
	 * @since  3.0.0
	 */
	private $apiPoint = 'https://api.zoom.us/v2/';

	/**
	 * The HTTP request client
	 *
	 * @var    Http
	 * @since  3.0.0
	 */
	private $client = null;

	/**
	 * Constructor activating the default information of the zoom object
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

		$params = new Registry($venueObj->getParams());
		$this->hostUser  = $params->get('host_id');
		$this->accountId = $params->get('account_id');
		$this->clientId  = $params->get('client_id');
		$this->clientSecret  = $params->get('client_secret');
		$this->client    = new Http;
	}

	/**
	 * Method to generate the appropriate headers for the request
	 *
	 * @return  Array  A valid request header
	 *
	 * @since   3.0.0
	 */
	private function headers()
	{
		$host = "https://zoom.us/oauth/token?grant_type=account_credentials&account_id=" . $this->accountId;
		$curlHost = curl_init($host);
		$authorization = base64_encode($this->clientId . ":" . $this->clientSecret);
		curl_setopt($curlHost, CURLOPT_HTTPHEADER, array('Content-Type:application/json','Authorization: Basic '.$authorization));
		curl_setopt($curlHost, CURLOPT_POST, 1);
		curl_setopt($curlHost, CURLOPT_RETURNTRANSFER, TRUE);
		$return = curl_exec($curlHost);
		curl_close($curlHost);
		$authorizationJson = json_decode($return,true);
		
		return [
				'Authorization' => 'Bearer ' . $authorizationJson["access_token"],
				'Content-Type' => 'application/json',
				'Accept' => 'application/json',
		];
	}

	/**
	 * Method to generate signed JWT token using the API key and Secret
	 *
	 * @return  string  A valid JWT token
	 *
	 * @since   3.0.0
	 */
	private function generateJWT()
	{
		$token = [
				'iss' => $this->apiKey,
				'exp' => time() + 60,
		];

		return JWT::encode($token, $this->apiSecret);
	}

	/**
	 * Method to GET data from server
	 *
	 * @param   string  $method  Method name to invoke
	 * @param   array   $fields  Required data to call method
	 *
	 * @return  Array  A response from the server
	 *
	 * @since   3.0.0
	 */
	protected function getData($method, $fields = [])
	{
		$url = $this->apiPoint . $method;
		$url = Uri::getInstance($url);
		$url->setQuery($fields);

		try
		{
			$response = $this->client->get($url->toString(), $this->headers());

			return $this->result($response, 'get', $url->toString());
		}
		catch (Exception $e)
		{
			$result['message'] = $e->getMessage();
			$result['code'] = $e->getCode();
			$log = $result;
			$log['method'] = 'get';
			$log['url'] = $url->toString();
			$this->logResponse($log, true);

			return $result;
		}
	}

	/**
	 * Method to post data to server
	 *
	 * @param   string  $method  Method name to invoke
	 * @param   array   $fields  Required data to call method
	 *
	 * @return  Array  A response from the server
	 *
	 * @since   3.0.0
	 */
	protected function postData($method, $fields)
	{
		$body = \json_encode($fields, JSON_PRETTY_PRINT);

		try
		{
			$response = $this->client->post($this->apiPoint . $method, $body, $this->headers());

			return $this->result($response, 'post', $this->apiPoint . $method);
		}
		catch (Exception $e)
		{
			$result['message'] = $e->getMessage();
			$result['code'] = $e->getCode();
			$log = $result;
			$log['method'] = 'post';
			$log['url'] = $this->apiPoint . $method;
			$this->logResponse($log, true);

			return $result;
		}
	}

	/**
	 * Method to post data to server
	 *
	 * @param   string  $method  Method name to invoke
	 * @param   array   $fields  Required data to call method
	 *
	 * @return  Array  A response from the server
	 *
	 * @since   3.0.0
	 */
	protected function patchData($method, $fields)
	{
		$body = \json_encode($fields, JSON_PRETTY_PRINT);

		try
		{
			$response = $this->client->patch($this->apiPoint . $method, $body, $this->headers());

			return $this->result($response, 'patch', $this->apiPoint . $method);
		}
		catch (Exception $e)
		{
			$result['message'] = $e->getMessage();
			$result['code'] = $e->getCode();
			$log = $result;
			$log['method'] = 'patch';
			$log['url'] = $this->apiPoint . $method;
			$this->logResponse($log, true);

			return $result;
		}
	}

	/**
	 * Method to post data to server
	 *
	 * @param   string  $method  Method name to invoke
	 * @param   array   $fields  Required data to call method
	 *
	 * @return  Array  A response from the server
	 *
	 * @since   3.0.0
	 */
	protected function putData($method, $fields)
	{
		$body = \json_encode($fields, JSON_PRETTY_PRINT);

		try
		{
			$response = $this->client->put($this->apiPoint . $method, $body, $this->headers());

			return $this->result($response, 'put', $this->apiPoint . $method);
		}
		catch (Exception $e)
		{
			$result['message'] = $e->getMessage();
			$result['code'] = $e->getCode();
			$log = $result;
			$log['method'] = 'put';
			$log['url'] = $this->apiPoint . $method;
			$this->logResponse($log, true);

			return $result;
		}
	}

	/**
	 * Method to post data to server
	 *
	 * @param   string  $method  Method name to invoke
	 * @param   array   $fields  Required data to call method
	 *
	 * @return  Array  A response from the server
	 *
	 * @since   3.0.0
	 */
	protected function deleteData($method, $fields = [])
	{
		$url = $this->apiPoint . $method;
		$url = Uri::getInstance($url);
		$url->setQuery($fields);

		try
		{
			$response = $this->client->delete($url->toString(), $this->headers());

			return $this->result($response, 'delete', $url->toString());
		}
		catch (Exception $e)
		{
			$result['message'] = $e->getMessage();
			$result['code'] = $e->getCode();
			$log = $result;
			$log['method'] = 'delete';
			$log['url'] = $url->toString();
			$this->logResponse($log, true);

			return $result;
		}
	}

	/**
	 * Get the host of the venue
	 *
	 * @return  string  Host id configured with the venue
	 *
	 * @since   3.0.0
	 */
	public function getHostUser()
	{
		return $this->hostUser;
	}

	/**
	 * Method to parse the HTTP response
	 *
	 * @param   Response  $response  HTTP response object
	 * @param   string    $method    Requested method
	 * @param   string    $url       Request url
	 *
	 * @return  Array  A parsed response
	 *
	 * @since   3.0.0
	 */
	private function result(Response $response, $method, $url)
	{
		$result['body'] = json_decode((string) $response->body, true);
		$result['code'] = $response->code;
		$result['message'] = isset($result['body']['message']) ? $result['body']['message'] : '';
		$log = $result;
		$log['method'] = $method;
		$log['url'] = $url;

		$this->logResponse($log, !empty($result['message']));

		return $result;
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
	private function logResponse($data, $error = false)
	{
		static $logApiResponse = null;
		static $logBody = 0;

		if (is_null($logApiResponse))
		{
			PluginHelper::importPlugin('tjevents', 'zoom');
			$plugin = PluginHelper::getPlugin('tjevents', 'zoom');
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
		$options = array("text_file" => "zoom_event_debug.log", "text_file_no_php" => true, 'text_entry_format' => $format);
		$formatLogger = new FormattedtextLogger($options);
		$entry = new LogEntry($message, $priority);
		$formatLogger->addEntry($entry);

		return true;
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
		$data = $this->getData("users/{$this->getHostUser()}");

		if ($data['code'] != 200)
		{
			$this->setError($data['message']);

			return false;
		}

		return true;
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
}
