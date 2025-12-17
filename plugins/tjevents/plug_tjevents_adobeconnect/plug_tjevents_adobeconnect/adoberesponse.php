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

use Joomla\CMS\Http\Response;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Language\Text;

/**
 * JTicketing Adobe response parser class
 *
 * @since  3.0.0
 */
class JTicketingEventAdobeResponse extends CMSObject
{
	/**
	 * Normalised response from adobe API
	 *
	 * @var    array
	 * @since  3.0.0
	 */
	private $responseBody = [];

	/**
	 * Joomla http response object
	 *
	 * @var    Response
	 * @since  3.0.0
	 */
	private $response = null;

	/**
	 * Constructor activating the default information of the class
	 *
	 * @param   Response  $response  The Http response object
	 *
	 * @since   3.0.0
	 */
	public function __construct(Response $response = null)
	{
		$this->response = $response;

		$xml = simplexml_load_string($response->body);

		if ($xml === false)
		{
			return;
		}

		$this->responseBody = [];

		foreach ($xml as $element)
		{
			// If it has attributes it's an element
			if (! empty($element->attributes()))
			{
				$this->responseBody[$element->getName()] = $this->normalize(json_decode(json_encode($element), true));
				continue;
			}

			// If it doesn't have attributes it is a collection
			$elementName = $this->toCamelCase($element->getName());
			$this->responseBody[$elementName] = [];

			foreach ($element->children() as $elementChild)
			{
				$this->responseBody[$elementName][] = $this->normalize(json_decode(json_encode($elementChild), true));
			}
		}
	}

	/**
	 * Method to recursive transform the array
	 *
	 * @param   array  $arr  data to be bind with the object
	 *
	 * @return  array
	 *
	 * @since   3.0.0
	 */
	private function normalize($arr)
	{
		$ret = [];

		if (isset($arr['@attributes']))
		{
			$arr = array_merge($arr, $arr['@attributes']);
			unset($arr['@attributes']);
		}

		foreach ($arr as $key => $value)
		{
			if (is_array($value))
			{
				$value = $this->normalize($value);
			}

			$ret[$this->toCamelCase($key)] = $value;
		}

		return $ret;
	}

	/**
	 * Method to Converts any string to camelCase
	 *
	 * @param   string  $term  String to convert
	 *
	 * @return  string
	 *
	 * @since   3.0.0
	 */
	private function toCamelCase($term)
	{
		$term = preg_replace_callback('/[\s_-](\w)/', function ($matches)
		{
			return mb_strtoupper($matches[1]);
		}, $term
		);

		$term[0] = mb_strtolower($term[0]);

		return $term;
	}

	/**
	 * Method to Converts the Camel Case to a string replace with the letter
	 *
	 * @param   string  $term    The string to convert
	 * @param   string  $letter  The letter to replace with
	 *
	 * @return  boolean  True on success
	 *
	 * @since   3.0.0
	 */
	protected static function camelCaseTransform($term, $letter)
	{
		return mb_strtolower(preg_replace('/([A-Z])/', $letter . '$1', $term));
	}

	/**
	 * Validate the status code and set an error if something is wrong
	 *
	 * @return  boolean  True on success
	 *
	 * @since   3.0.0
	 */
	public function isValidResponse()
	{
		$status = false;
		$error  = '';

		switch ($this->responseBody['status']['code'])
		{
			case 'ok':
				$status = true;
				break;

			case 'invalid':
				$invalid = $this->responseBody['status']['invalid'];

				switch ($invalid['subcode'])
				{
					case 'duplicate':
						$error = Text::sprintf(Text::_('PLG_JTICKETING_VENUE_ADOBE_CONNECT_ERROR_MESSAGE_DUPLICATE'), $invalid['field']);
					break;
					case 'format' :
						$error = Text::sprintf(Text::_('PLG_JTICKETING_VENUE_ADOBE_CONNECT_ERROR_MESSAGE_WRONG_FORMAT'), $invalid['field']);
					break;
					case 'illegal-operation' :
						$error = Text::sprintf(Text::_('PLG_JTICKETING_VENUE_ADOBE_CONNECT_ERROR_MESSAGE_VIOLATES_INTEGRITY_RULE'), $invalid['field']);
					break;
					case 'missing' :
						$error = Text::sprintf(Text::_('PLG_JTICKETING_VENUE_ADOBE_CONNECT_ERROR_MESSAGE_PARAMETER_MISSING'), $invalid['field']);
					break;
					case 'no-such-item' :
						$error = Text::sprintf(Text::_('PLG_JTICKETING_VENUE_ADOBE_CONNECT_ERROR_MESSAGE_INFORMATION_NOT_EXIST'), $invalid['field']);
					break;
					case 'range' :
						$error = Text::sprintf(Text::_('PLG_JTICKETING_VENUE_ADOBE_CONNECT_ERROR_MESSAGE_OUTSIDE_RANGE'), $invalid['field']);
					break;

					case 'session-schedule-conflict':
						$error = Text::_('PLG_JTICKETING_VENUE_ADOBE_CONNECT_ERROR_MESSAGE_CONFLICT');
						break;

					default :
						$error = ucfirst($invalid['subcode']) . ' - ' . Text::_('PLG_JTICKETING_VENUE_ADOBE_CONNECT_ERROR_MESSAGE_TO_ADMIN');
				}

			break;

			case 'no-access':

				$invalid = $this->responseBody['status'];

				switch ($invalid['subcode'])
				{
					case 'account-expired':
						$error = Text::_('PLG_JTICKETING_VENUE_ADOBE_CONNECT_ERROR_MESSAGE_EXPIRED');
					break;
					case 'denied':
						$error = Text::_('PLG_JTICKETING_VENUE_ADOBE_CONNECT_ERROR_MESSAGE_DONT_HAVE_PERMISSION');
					break;
					case 'no-login' :
						$error = Text::_('PLG_JTICKETING_VENUE_ADOBE_CONNECT_ERROR_MESSAGE_NOT_LOOGED_IN');
					break;
					case 'no-quota' :
						$error = Text::_('PLG_JTICKETING_VENUE_ADOBE_CONNECT_ERROR_MESSAGE_ACCOUNT_LIMIT_REACH');
					break;
					case 'not-available' :
						$error = Text::_('PLG_JTICKETING_VENUE_ADOBE_CONNECT_ERROR_MESSAGE_RESOURSE_UNAVILABLE');
					break;
					case 'not-secure' :
						$error = Text::_('PLG_JTICKETING_VENUE_ADOBE_CONNECT_ERROR_MESSAGE_SSL');
					break;
					case 'pending-activation' :
						$error = Text::_('PLG_JTICKETING_VENUE_ADOBE_CONNECT_ERROR_MESSAGE_PENDING_ACTIVATION');
					break;
					case 'pending-license' :
						$error = Text::_('PLG_JTICKETING_VENUE_ADOBE_CONNECT_ERROR_MESSAGE_PENDING_LICENSE');
					break;
					case 'sco-expired' :
						$error = Text::_('PLG_JTICKETING_VENUE_ADOBE_CONNECT_ERROR_MESSAGE_SCO_EXPIRED');
					break;
					case 'sco-not-started' :
						$error = Text::_('PLG_JTICKETING_VENUE_ADOBE_CONNECT_ERROR_MESSAGE_SCO_NOT_STARTED');
					break;

					default :
						$error = ucfirst($invalid['subcode']) . ' - ' . Text::_('PLG_JTICKETING_VENUE_ADOBE_CONNECT_ERROR_MESSAGE_TO_ADMIN');
				}

				break;

			case 'no-data':
				$error = Text::_(
					'PLG_JTICKETING_VENUE_ADOBE_CONNECT_ERROR_MESSAGE_NO_DATA');
				break;

			case 'too-much-data':
				$error = Text::_('PLG_JTICKETING_VENUE_ADOBE_CONNECT_ERROR_MESSAGE_TOO_MUCH_DATA');
				break;

			default:
				$error = Text::_('PLG_JTICKETING_VENUE_ADOBE_CONNECT_ERROR_MESSAGE_INVALID_RESPONSE');
		}

		$this->setError($error);

		return $status;
	}

	/**
	 * Parse an array of header values containing ";" separated data into an
	 * array of associative arrays representing the header key value pair
	 * data of the header. When a parameter does not contain a value, but just
	 * contains a key, this function will inject a key with a '' string value.
	 *
	 * @param   array  $header  Header to parse into components.
	 *
	 * @return  array  The parsed header values.
	 *
	 * @since   3.0.0
	 */
	public function parseHeader($header)
	{
		static $trimmed = "\"'  \n\t\r";
		$params = $matches = [];

		foreach ($this->headerNormalize($header) as $val)
		{
			$part = [];

			foreach (preg_split('/;(?=([^"]*"[^"]*")*[^"]*$)/', $val) as $kvp)
			{
				if (preg_match_all('/<[^>]+>|[^=]+/', $kvp, $matches))
				{
					$m = $matches[0];

					if (isset($m[1]))
					{
						$part[trim($m[0], $trimmed)] = trim($m[1], $trimmed);
					}
					else
					{
						$part[] = trim($m[0], $trimmed);
					}
				}
			}

			if ($part)
			{
				$params[] = $part;
			}
		}

		return $params;
	}

	/**
	 * Converts an array of header values that may contain comma separated
	 * headers into an array of headers with no comma separated values.
	 *
	 * @param   array  $header  header to normalise
	 *
	 * @return  array  Returns the normalized header field values.
	 *
	 * @since   3.0.0
	 */
	private function headerNormalize($header)
	{
		if (! is_array($header))
		{
			return array_map('trim', explode(',', $header));
		}

		$result = [];

		foreach ($header as $value)
		{
			foreach ((array) $value as $v)
			{
				if (mb_strpos($v, ',') === false)
				{
					$result[] = $v;
					continue;
				}

				foreach (preg_split('/,(?=([^"]*"[^"]*")*[^"]*$)/', $v) as $vv)
				{
					$result[] = trim($vv);
				}
			}
		}

		return $result;
	}

	/**
	 * Method to get the header details
	 *
	 * @return  string
	 *
	 * @since   3.0.0
	 */
	public function getHeader()
	{
		return $this->response->headers;
	}

	/**
	 * Method to get the response body
	 *
	 * @return  string
	 *
	 * @since   3.0.0
	 */
	public function getBody()
	{
		return $this->responseBody;
	}
}
