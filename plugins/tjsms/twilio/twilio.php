<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Tjsms.twilio
 *
 * @copyright   Copyright (C) 2009 - 2020 Techjoomla. All rights reserved.
 * @license     http:/www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;
use Joomla\CMS\Http\Http;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Log\Log;

require __DIR__ . '/vendor/autoload.php';

// Use the REST API Client to make requests to the Twilio REST API
use Twilio\Rest\Client;

/**
 * Class for Twilio Tjsms Plugin
 *
 * @since  1.0.0
 */
class PlgTjsmsTwilio extends CMSPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 *
	 * @since  3.2.11
	 */
	protected $autoloadLanguage = true;

	/**
	 * Constructor
	 *
	 * @param   string  $subject  subject
	 * @param   array   $config   config
	 *
	 * @since   1.0.0
	 */
	public function __construct($subject, $config)
	{
		parent::__construct($subject, $config);

		$this->accountSid = $this->params->get('account_sid');
		$this->apiKey     = $this->params->get('api_key');
		$this->from       = $this->params->get('from');
		$this->timeout    = 30;
		$this->url        = 'https://api.twilio.com/2010-04-01/Accounts/' . $this->accountSid . '/Messages.json';
	}

	/**
	 * Function to send the message
	 *
	 * @param   string  $phone       Phone (if multiple phone numbers then comma seperated numbers)
	 * @param   string  $message     Message
	 *
	 * @param   int     $templateId  SMS provider template Id
	 *
	 * @return  array  Returns array containing keys as phone, message and status
	 *
	 * @since  1.0
	 */
	protected function send($phone, $message, $templateId)
	{
		// Check phone
		if (trim($phone) == "" || strlen($phone) == 0)
		{
			return array("error" => Text::_('PLG_TJSMS_SMS4INDIA_ERROR_INVALID_NUMBER'));
		}

		// Check the message
		if (trim($message) == "" || strlen($message) == 0)
		{
			return array("error" => Text::_('PLG_TJSMS_SMS4INDIA_ERROR_INVALID_MESSAGE'));
		}

		$return = array();

		// Store the numbers from the string to an array
		$phoneList = explode(",", $phone);

		// Take only the first 140 characters of the message
		// $message = substr($message, 0, 140);

		// Urlencode your message
		// $message = urlencode($message);

		// Create jhttp object
		$options = new Registry;
		$options->set('timeout', $this->timeout);
		$http    = new Http($options);

		// Send SMS to each number
		foreach ($phoneList as $phone)
		{
			// Check the mobile number

			/*if (strlen($phone) != 10 || !is_numeric($phone) || strpos($phone, ".") != false)
			{
				$result[] = array(
					'phone'   => $phone,
					'message' => $message,
					'result'  => false,
					'error'   => array('error' => Text::_('PLG_TJSMS_SMS4INDIA_ERROR_INVALID_NUMBER'))
				);

				continue;
			}*/

			try
			{
				/*
				EXCLAMATION_MARK='!'
				curl -X POST https://api.twilio.com/2010-04-01/Accounts/ACe4a95f606bea983308a4ba8a21/Messages.json \
				--data-urlencode "Body=Hi there$EXCLAMATION_MARK" \
				--data-urlencode "From=+15017122661" \
				--data-urlencode "To=+15558675310" \
				-u ACe4a95f606bd4424308a4ba8a21:your_auth_token
				*/

				$headers = array(
					// 'content-type'    => 'application/x-www-form-urlencoded',
					// 'x-rapidapi-host' => 'twilio-sms.p.rapidapi.com',

					// 'x-rapidapi-key'  => $this->apiKey
					'Authorization'   => $this->accountSid . ':' . $this->apiKey
				);

				$data = array (
					'from' => $this->from,
					'to'   => $phone,
					'body' => $message
				);

				$this->url .= '?From=' . $this->from;
				$this->url .= '&To=' . $phone;
				$this->url .= '&Body=' . $message;

				// $data = array();

				// $response   = $http->get($this->url, $headers);
				// $responseBody = json_decode($response->body);

				// $response = $http->post($this->url, $data, $headers);
				// $responseBody = json_decode($response->body);

				// Your Account SID and Auth Token from twilio.com/console
				$sid = $this->accountSid;
				$token = $this->apiKey;
				$client = new Client($sid, $token);

				// Use the client to do fun stuff like send text messages!
				$responseBody = $client->messages->create(
					// The number you'd like to send the message to
					$phone,
					[
						// A Twilio phone number you purchased at twilio.com/console
						'from' => $this->from,

						// The body of the text message you'd like to send
						'body' => $message
					]
				);

				$return['success'] = 1;

				if ($responseBody->status !== 'sent' && $responseBody->status !== 'queued')
				{
					$return['success'] = 0;

					$this->logdata( 'Twillo Message Not sent: ' . $responseBody->error_message, $responseBody->error_code);
				}

				// $results[] = array('phone' => $phone, 'message' => $message, 'result' => $response->code);
			}
			catch (Exception $e)
			{
				$return['success'] = 0;
				$return['code']    = $e->getCode();
				$return['message'] = $e->getMessage();
				$return['trace']   = $e->getTrace();

				$this->logdata( 'Twillo Message: ' . $e->getMessage());
				$this->logdata( 'Twillo Message code: ' . $e->getCode());

				// throw new Exception($e->getMessage(), $e->getCode());

				// $results[] = array('phone' => $phone, 'message' => $message, 'result' => $response->code);
			}
		}

		return $return;
	}

	/**
	 * Functions to send SMS
	 *
	 * @param   string  $phone       phone (if multiple phone numbers then comma seperated numbers)
	 * @param   string  $message     message
	 *
	 * @param   int     $templateId  SMS provider template Id
	 *
	 * @return  array
	 *
	 * @since  1.0.0
	 */
	public function onSend_SMS($phone, $message, $templateId = 0)
	{
		return $this->send($phone, $message, $templateId);
	}

	/**
	 * Functions to store alog
	 *
	 *
	 *
	 * @return  array
	 *
	 * @since  1.0.0
	 */
	public function logdata($data)
	{
    	Log::add($data, Log::ERROR);
		return true;
	}
}


Permission to send an SMS has not been enabled for the region indicated by the 'To' number: 301462XXXX