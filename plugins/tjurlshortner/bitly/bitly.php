<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Tjurlshortner.bitly Plugin
 *
 * @copyright   Copyright (C) 2020 Techjoomla. All rights reserved.
 * @license     http:/www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Http\Http;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Http\Response;
use Joomla\CMS\Plugin\CMSPlugin;

/**
 * Class for Bitly Tjurlshortner Plugin
 *
 * @since  __DEPLOY_VERSION__
 */
class PlgTjurlshortnerBitly extends CMSPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected $autoloadLanguage = true;

	public $postUrl = "https://api-ssl.bit.ly/v3/shorten";

	/**
	 * This method return short URL
	 *
	 * @param   String  $longUrl  URL
	 *
	 * @return  Array
	 */
	public function getShortUrl($longUrl)
	{
		try
		{
			$access_token = $this->params->get('access_token');
			$result = array();
			$url    = $this->postUrl . "?access_token=" . $access_token . "&longUrl=" . urlencode($longUrl);

			$http     = new Http;
			$headers  = array('Content-Type' => 'application/json');
			$response = $http->post($url, json_encode($access_token), $headers);
			$output   = json_decode($response->body, true);

			if (isset($output['data']['hash']))
			{
				$result['url']         = $output['data']['url'];
				$result['hash']        = $output['data']['hash'];
				$result['global_hash'] = $output['data']['global_hash'];
				$result['long_url']    = $output['data']['long_url'];
				$result['new_hash']    = $output['data']['new_hash'];
			}

			$result['status_code'] = $output['status_code'];

			if ($result['status_code'] == 200)
			{
				return $result;
			}
			else
			{
				throw new Exception(Text::_('PLG_TJURLSHORTNER_GETTING_URL_FAILED'));
			}
		}
		catch (Exception $e)
		{
			return false;
		}
	}
}
