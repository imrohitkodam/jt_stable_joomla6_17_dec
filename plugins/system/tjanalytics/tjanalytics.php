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
use Joomla\CMS\Plugin\CMSPlugin;

JLoader::register('TJAnalyticsProviderInterface', JPATH_SITE . '/plugins/system/tjanalytics/providerinterface.php');
JLoader::register('TJAnalyticsProvider', JPATH_SITE . '/plugins/system/tjanalytics/provider.php');

/**
 * Techjoomla Analytics plugin
 *
 * @since  1.0.0
 */
Class PlgSystemTjanalytics extends CMSPlugin
{
	/**
	 * Affects constructor behavior. If true, language files will be loaded automatically.
	 *
	 * @var    boolean
	 * @since  1.0.1
	 */
	protected $autoloadLanguage = true;

	protected $provider;

	protected $instance;

	/**
	 * Constructor.
	 *
	 * @param   object  &$subject  The object to observe.
	 * @param   array   $config    An optional associative array of configuration settings.
	 *
	 * @since   1.0.0
	 */

	public function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);

		$app            = Factory::getApplication();
		$this->provider = $this->params->get('provider', 'googleanalytics');
		$this->instance = TJAnalyticsProvider::getInstance($this->provider, $this->params);

		if (!$this->instance
			|| !$this->instance instanceof TJAnalyticsProviderInterface
			|| ($app->isClient('administrator') && !$this->params->get('enable_admin')))
		{
			return;
		}

		/*$session = Factory::getSession();
		$this->events_queue = $session->get('analytics_tracking.queue');*/
	}

	/**
	 * Function called on after render page
	 *
	 * @return  void|boolean
	 *
	 * @since   1.0.1
	 */
	public function onBeforeCompileHead()
	{
		$app = Factory::getApplication();

		if (!$this->instance
			|| !$this->instance instanceof TJAnalyticsProviderInterface
			|| ($app->isClient('administrator') && !$this->params->get('enable_admin')))
		{
			return;
		}

		$document = Factory::getDocument();
		$document->addScriptDeclaration($this->instance->getHeadClose());

		return true;
	}

	/**
	 * Function called on after render page
	 *
	 * @return  void|boolean
	 *
	 * @since   1.0.1
	 */
	public function onAfterRender()
	{
		$app = Factory::getApplication();

		if (!$this->instance
			|| !$this->instance instanceof TJAnalyticsProviderInterface
			|| ($app->isClient('administrator') && !$this->params->get('enable_admin')))
		{
			return;
		}

		$scriptsArray = $this->instance->getBodyCloseScripts();

		if (!empty($scriptsArray))
		{
			$buffer = $app->getBody();

			foreach ($scriptsArray as $script)
			{
				$buffer = str_replace("</body>", $script . "</body>", $buffer);
			}

			$app->setBody($buffer);
		}

		return true;
	}
}
