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
 * Class for Google Analytics
 *
 * @since  1.1.0
 */
class TJAnalyticsProviderGoogleAnalytics implements TJAnalyticsProviderInterface
{
	protected $analyticsMode;

	protected $tmContainerId;

	protected $propertyId;

	protected $domain;

	protected $loadJs;

	protected $loadJsEc;

	protected $trackUserid;

	protected $anonymizeIp;

	protected $dimensionId;

	// @protected $namespace;

	protected $trackNoJs;

	/**
	 * Constructor
	 *
	 * @param   Registry  $params  Plugin config
	 */
	public function __construct($params)
	{
		$this->analyticsMode = $params->get('mode_ga');

		$this->tmContainerId = $params->get('container_id_ga');

		$this->propertyId    = $params->get('property_id_ga');
		$this->domain        = $params->get('domain_ga');
		$this->loadJs        = $params->get('load_js_ga');
		$this->loadJsEc      = $params->get('load_js_ec_ga');
		$this->trackUserid   = $params->get('track_userid_ga');
		$this->anonymizeIp   = $params->get('anonymize_ip_ga');
		$this->dimensionId   = $params->get('dimension_id_ga');

		// $this->namespace   = $params->get('namespace_ga');

		$this->trackNoJs     = $params->get('track_nojs_ga');
	}

	/**
	 * Get script to be added before head close
	 *
	 * @return null|string
	 */
	public function getHeadClose()
	{
		$javascript = "";

		// Google tag manager && Container ID given?
		if ($this->analyticsMode == 'tagmanager')
		{
			if (empty($this->tmContainerId))
			{
				return $javascript;
			}

			$javascript .= "
			(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
			new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
			j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
			'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
			})(window,document,'script','dataLayer','" . $this->tmContainerId . "');
			";

			return $javascript;
		}

		// START Google analytics
		if (!$this->propertyId)
		{
			return $javascript;
		}

		// Load analytics JS?
		if ((int) $this->loadJs == 1)
		{
			$javascript .= "

			(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
			(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
			m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
			})(window,document,'script','https://www.google-analytics.com/analytics.js','ga');";
		}

		// Start tracking
		$javascript .= "
			ga('create', '" . $this->propertyId . "', '" . $this->domain . "');";

		// Track userid?
		if ((int) $this->trackUserid == 1 && Factory::getUser()->id)
		{
			$javascript .= "
			ga('set', 'userId', '" . md5(Factory::getUser()->id) . "');";
		}

		// Load analytics ecommerce JS?
		if (!empty($this->loadJsEc))
		{
			$ec = ($this->loadJsEc == 'enhanced_ec') ? "ga('require', 'ec');" : "ga('require', 'ecommerce');";

			$javascript .= "
			" . $ec;
		}

		// Anonymize IP?
		if ((int) $this->anonymizeIp == 1)
		{
			$javascript .= "
			ga('set', 'anonymizeIp', true);";
		}

		// Custom dimentions tracking
		if (!empty($this->dimensionId))
		{
			$javascript .= "
			var productTypeDimensionId = '" . $this->dimensionId . "';";
		}

		// Send pageview
		$javascript .= "
			ga('send', 'pageview');";

		// Custom event tracking

		/*if (!empty($this->namespace))
		{
$javascript .= <<<GA
jQuery(window).load(function () {
	jQuery("[data-{$this->namespace}-category]").on( "click", function() {
		ga(
			'send',
			'event',
			jQuery(this).data('{$this->namespace}-category'),
			jQuery(this).data('{$this->namespace}-action'),
			jQuery(this).data('{$this->namespace}-name'),
			jQuery(this).data('{$this->namespace}-value')
		);
	});
});
GA;
		}*/

		return $javascript;
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
		if ((int) $this->trackNoJs !== 1)
		{
			return $scripts;
		}

		// Google tag manager && Container ID given?
		if ($this->analyticsMode == 'tagmanager')
		{
			if (empty($this->tmContainerId))
			{
				return $scripts;
			}

			$scripts[] = '	<noscript>
				<iframe src="https://www.googletagmanager.com/ns.html?id=' . $this->tmContainerId . '"
					height="0" width="0" style="display:none;visibility:hidden"></iframe>
			</noscript>';
		}

		return $scripts;
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
	/*public static function trackEvent($category, $action = '', $name = '', $value = '')
	{
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
	/*public static function sendEvent($category = '', $action = '', $name = '', $value = '')
	{
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
	/*public static function queueEvent($category = '', $action = '', $name = '', $value = '')
	{
	}*/
}
