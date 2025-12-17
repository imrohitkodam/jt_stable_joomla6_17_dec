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

/**
 * Interface for Tj Analytics Provider
 *
 * @package  PlgSystemTjAnalytics
 * @since    1.1.0
 */
interface TJAnalyticsProviderInterface
{
	/**
	 * Get <script> to be added before head close
	 *
	 * @return  null|string
	 */
	public function getHeadClose();

	/**
	 * Get <noscript> or other <tags> HTML to be added before body close
	 *
	 * @return array
	 */
	public function getBodyCloseScripts();

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
	/*public function trackEvent($category, $action = '', $name = '', $value = '');*/

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
	/*public function sendEvent($category = '', $action = '', $name = '', $value = '');*/

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
	/*public function queueEvent($category = '', $action = '', $name = '', $value = '');*/
}
