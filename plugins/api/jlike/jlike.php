<?php
/**
 * @version    SVN: <svn_id>
 * @package    JLike
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2016 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */
defined('_JEXEC') or die( 'Restricted access');

/**
 * Base Class for api plugin
 *
 * @package     JLike
 * @subpackage  component
 * @since       1.0
 */

class PlgAPIJlike extends ApiPlugin
{
	/**
	 * Jlike api plugin to load com_api classes
	 *
	 * @param   string  $subject  originalamount
	 * @param   array   $config   coupon_code
	 *
	 * @since   1.0
	 */
	public function __construct($subject, $config = array())
	{
		parent::__construct($subject, $config = array());

		// Load all required helpers.
		$component_path = JPATH_ROOT . '/components/com_jlike';

		if (!file_exists($component_path))
		{
			return;
		}

		$helperPath = JPATH_SITE . '/components/com_jlike/helper.php';

		if (!class_exists('comjlikeHelper'))
		{
			if (file_exists($helperPath)) {
				require_once $helperPath;
			}
		}

		$helperPath = JPATH_SITE . '/components/com_jlike/helpers/main.php';

		if (!class_exists('ComjlikeMainHelper'))
		{
			if (file_exists($helperPath)) {
				require_once $helperPath;
			}
		}

		$helperPath = JPATH_SITE . '/components/com_jlike/helpers/integration.php';

		if (!class_exists('comjlikeIntegrationHelper'))
		{
			if (file_exists($helperPath)) {
				require_once $helperPath;
			}
		}

		$helperPath = JPATH_SITE . '/components/com_jlike/helpers/socialintegration.php';

		if (!class_exists('socialintegrationHelper'))
		{
			if (file_exists($helperPath)) {
				require_once $helperPath;
			}
		}

		$helperPath = JPATH_SITE . '/components/com_jlike/helpers/content.php';

		if (!class_exists('comjlikeContentHelper'))
		{
			if (file_exists($helperPath)) {
				require_once $helperPath;
			}
		}

		$this->setResourceAccess('init', 'public','post');
		$this->setResourceAccess('annotations', 'public','get');

		ApiResource::addIncludePath(dirname(__FILE__) . '/jlike');
	}
}
