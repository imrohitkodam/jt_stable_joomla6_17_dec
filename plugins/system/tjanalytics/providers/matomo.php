<?php
/**
 * @package    PlgSystemTjAnalytics
 * @author     Techjoomla <extensions@techjoomla.com>
 *
 * @copyright  Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;

/**
 * Class for Matomo(Piwik)
 *
 * @since  1.1.0
 */
class TJAnalyticsProviderMatomo implements TJAnalyticsProviderInterface
{
	protected $url;

	protected $analyticsMode;

	protected $tmContainerId;

	protected $siteId;

	protected $trackUserid;

	protected $enableDNTDetection;

	protected $disableCookies;

	protected $trackNoJs;

	// @protected $prependDomain;
	// @protected $namespace;

	/**
	 * Constructor
	 *
	 * @param   Registry  $params  Plugin config
	 */
	public function __construct($params)
	{
		$this->url                = trim($params->get('url_matomo'), '/');
		$this->analyticsMode      = $params->get('mode_matomo');

		$this->tmContainerId      = $params->get('container_id_matomo');

		$this->siteId             = $params->get('site_id_matomo');
		$this->trackUserid        = $params->get('track_userid_matomo');
		$this->enableDNTDetection = $params->get('enable_donottrack_detection_matomo');
		$this->disableCookies     = $params->get('disable_cookies_matomo');
		$this->trackNoJs          = $params->get('track_nojs_matomo');

		/*$this->prependDomain    = $params->get('prepend_domain_matomo');
		$this->namespace          = $params->get('namespace_matomo');*/

		/*$this->userTypeSlotId   = $params->get('usertype_slot_id');
		$this->pageTypeSlotId     = $params->get('pagetype_slot_id');
		$this->isSetGuestUserId   = $params->get('piwik_guests_id');*/
	}

	/**
	 * Function getHeadClose
	 *
	 * @return  null|string
	 */
	public function getHeadClose()
	{
		$javascript = "";

		if (!$this->url)
		{
			return;
		}

		// Tag manager
		if ($this->analyticsMode == 'tagmanager')
		{
			if (empty($this->tmContainerId))
			{
				return;
			}

			$javascript .= "
			var _mtm = _mtm || [];
			_mtm.push({'mtm.startTime': (new Date().getTime()), 'event': 'mtm.Start'});
			var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
			g.type='text/javascript'; g.async=true; g.defer=true;
			g.src='" . $this->url . "/js/container_" . $this->tmContainerId . ".js'; s.parentNode.insertBefore(g,s);";

			return $javascript;
		}

		$script   = array();
		$script[] = "var _paq = window._paq || [];";

		// Tracker methods like "setCustomDimension" should be called before "trackPageView"

		/*
		Set custom dimensions
		$script[] = "_paq.push(['setCustomDimension',{$this->userTypeSlotId},'{$this->userTypeValues}']);";
		*/

		// Track userid?
		if ($this->trackUserid == 1 && Factory::getUser()->id)
		{
			$userId   = md5(Factory::getUser()->id);
			$script[] = "_paq.push(['setUserId', '{$userId}']);";
		}

		// Prepend domain in document title?

		/*if ($this->prependDomain == 1)
		{
			$script[] = '_paq.push(["setDocumentTitle", document.domain + "/" + document.title]);';
		}*/

		// Enable client side DO NOT TRACK detection?
		if ($this->enableDNTDetection == 1)
		{
			$script[] = '_paq.push(["setDoNotTrack", true]);';
		}

		// Disabel all tracking cookies?
		if ($this->disableCookies == 1)
		{
			$script[] = '_paq.push(["disableCookies"]);';
		}

		$script[] = "_paq.push(['trackPageView']);";
		$script[] = "_paq.push(['enableLinkTracking']);";

		$script[] = "(function() {";
		$script[] = "	var u='{$this->url}/';";
		$script[] = "	_paq.push(['setTrackerUrl', u+'matomo.php']);";
		$script[] = "	_paq.push(['setSiteId', '{$this->siteId}']);";
		$script[] = "	var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];";
		$script[] = "	g.type='text/javascript'; g.async=true; g.defer=true; g.src=u+'matomo.js';";
		$script[] = "	s.parentNode.insertBefore(g,s);";
		$script[] = "})();";

		return implode($script, "\n");

		/*$session = Factory::getSession();
		$events_queue	= (array) $session->get('analytics_tracking.queue');

		$script = <<<PIWIK
var _paq = _paq || [];
jQuery(window).load( function () {
	jQuery(document).on("click", "[data-{$this->namespace}-category]", function() {
		_paq.push([
			'trackEvent',
			jQuery(this).data('{$this->namespace}-category'),
			jQuery(this).data('{$this->namespace}-action'),
			jQuery(this).data('{$this->namespace}-name'),
			jQuery(this).data('{$this->namespace}-value')
		]);
	});

	if (typeof telemetry_environment != 'undefined') {
		env = telemetry_environment;

		EkTelemetryServiceV3.init(env);
	}

	jQuery('.modal').on('shown.bs.modal', function () {
		EkTelemetryServiceV3.sendImpressionPopupOpen(this);
	});

	jQuery('body').click(function(ele){
		processAndSendEvent('click', ele);
	});

	jQuery('body').change(function(ele){
		processAndSendEvent('change', ele);
	});

	function processAndSendEvent(triggerType, ele) {
		EkTelemetryServiceV3.sendInteractClick(triggerType, ele);
	};
});

PIWIK;

		$script .= "jQuery(window).load( function () { ";

		foreach ($events_queue as $event)
		{
			$script .= "_paq.push([\"trackEvent\", '" . $event['category'] . "', '" . $event['action'] . "', '" . $event['name'] . "',
			 '" . $event['value'] . "']);" . "\n";
		}

		$script .= "});";

		$session->set('analytics_tracking.queue', array());

		return $script;*/
	}

	/**
	 * Get script to be added before body close
	 *
	 * @return array
	 */
	public function getBodyCloseScripts()
	{
		$scripts = array();

		// Track when JS disabled?
		if ($this->analyticsMode == 'analytics' && (int) $this->trackNoJs !== 1 )
		{
			return $scripts;
		}

		// If siteId ID given?
		if (!empty($this->siteId))
		{
			$scripts[] = '	<noscript><p>' . $this->getTrackingPixel() . '</p></noscript>';
		}

		return $scripts;
	}

	/**
	 * Function getTrackingPixel
	 *
	 * @return  string
	 */
	protected function getTrackingPixel()
	{
		return "<img src=\"{$this->url}/matomo.php?idsite={$this->siteId}&amp;rec=1\" style=\"border:0;\" alt=\"\" />";
	}

	/**
	 * Track event
	 *
	 * @param   string  $category  Category
	 * @param   string  $action    Action
	 * @param   string  $name      Name
	 * @param   string  $value     Value
	 *
	 * @return void
	 */
	/*public function trackEvent($category, $action = '', $name = '', $value = '')
	{
		$data['category']	= $category;
		$data['action']		= $action;
		$data['name']		= $name;
		$data['value']		= $value;

		$html = '';

		foreach ($data as $k => $v)
		{
			$html .= ' data-' . $this->namespace . '-' . $k . '="' . $v . '" ';
		}

		echo $html;
	}*/

	/**
	 * Send event
	 *
	 * @param   string  $category  Category
	 * @param   string  $action    Action
	 * @param   string  $name      Name
	 * @param   string  $value     Value
	 *
	 * @return void
	 */
	/*public function sendEvent($category = '', $action = '', $name = '', $value = '')
	{
		$document = Factory::getDocument();
		$document->addScriptDeclaration("_paq.push([\"trackEvent\", '" . $category . "', '" . $action . "', '" . $name . "', '" . $value . "']);");
	}*/

	/**
	 * Queue event
	 *
	 * @param   string  $category  Category
	 * @param   string  $action    Action
	 * @param   string  $name      Name
	 * @param   string  $value     Value
	 *
	 * @return void
	 */
	/*public function queueEvent($category = '', $action = '', $name = '', $value = '')
	{
		$session 			= Factory::getSession();
		$events_queue		= $session->get('analytics_tracking.queue');

		$event = array();
		$event['category'] = $category;
		$event['action']   = $action;
		$event['name']     = $name;
		$event['value']    = $value;

		array_push($events_queue, $event);

		$session->set('analytics_tracking.queue', $events_queue);
	}*/
}
