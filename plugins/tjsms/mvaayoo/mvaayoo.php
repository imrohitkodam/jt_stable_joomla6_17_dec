<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Tjsms.mvaayoo
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

/**
 * Class for Mvaayoo Tjsms Plugin
 *
 * @since  1.0.0
 */
class PlgTjsmsMvaayoo extends CMSPlugin
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
	 * @since   1.0
	 */
	public function __construct($subject, $config)
	{
		parent::__construct($subject, $config);

		$this->username = $this->params->get('username');
		$this->password = $this->params->get('password');
		$this->senderid = $this->params->get('senderid');
		$this->timeout  = 30;
		$this->url      = 'http://api.mVaayoo.com/mvaayooapi/MessageCompose';
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
			return array("error" => Text::_('PLG_TJSMS_MVAAYOO_ERROR_INVALID_NUMBER'));
		}

		// Check the message
		if (trim($message) == "" || strlen($message) == 0)
		{
			return array("error" => Text::_('PLG_TJSMS_MVAAYOO_ERROR_INVALID_MESSAGE'));
		}

		$return = array();

		$dcs = 0;

		// If message have unicode language then set dcs to 8.
		if (strlen($message) != strlen(utf8_decode($message)))
		{
			$dcs = 8;
		}

		// Urlencode your message
		$message = urlencode($message);

		// Create jhttp object
		$headers = array('Content-Type' => 'application/x-www-form-urlencoded');
		$options = new Registry;
		$options->set('timeout', $this->timeout);
		$http    = new Http($options);

		try
		{
			// @https://api.mVaayoo.com/mvaayooapi/MessageCompose?user=xxx:yyy
			// &senderID=zzz&receipientno=1234567890&dcs=0&msgtxt=Hello

			$this->url .= '?user=' . $this->username . ':' . $this->password;
			$this->url .= '&senderID=' . $this->senderid;
			$this->url .= '&receipientno=' . $phone;
			$this->url .= '&msgtxt=' . $message;
			$this->url .= '&dcs=' . $dcs;
			$this->url .= '&template_id=' . $templateId;

			// $this->url .= '&state=' . 4;

			$response  = $http->get($this->url, $headers);

			if ($response->code !== 200)
			{
				$this->logdata( 'mvaayoo response: ' . $response->body);
				$this->logdata( 'mvaayoo response code: ' . $response->code);
				// throw new Exception($response->body, $response->code);
			}

			$return['success'] = 1;
		}
		catch (Exception $e)
		{
			$return['success'] = 0;
			$return['code']    = $e->getCode();
			$return['message'] = $e->getMessage();
			$return['trace']   = $e->getTrace();

			$this->logdata( 'mvaayoo Message: ' . $e->getMessage());
			$this->logdata( 'mvaayoo Message code: ' . $e->getCode());

			// throw new Exception($e->getMessage(), $e->getCode());
		}

		return $return;
	}

	/**
	 * Functions to send SMS
	 *
	 * @param   string  $phone       phone
	 * @param   string  $message     message
	 * @param   int     $templateId  SMS provider template Id
	 *
	 * @return  array  Returns array containing keys as phone, message and status
	 *
	 * @since  1.0
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
