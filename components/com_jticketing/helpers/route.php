<?php
/**
 * @package     JTicketing
 * @subpackage  com_jticketing
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;

// Component Helper
/**
 * JTRouteHelper
 *
 * @since  1.0
 */

class JTRouteHelper
{
	protected static $lookup = array();

	protected static $lang_lookup = array();

	/**
	 * Wrapper to JRoute to handle itemid We need to try and capture the correct itemid for different view
	 *
	 * @param   string   $url    Absolute or Relative URI to Joomla resource.
	 * @param   boolean  $xhtml  Replace & by &amp; for XML compliance.
	 * @param   integer  $ssl    Secure state for the resolved URI.
	 *
	 * @return  url with itemif
	 *
	 * @since  1.0
	 */
	public function JTRoute($url, $xhtml = true, $ssl = null)
	{
		static $eventsItemid = array();

		$mainframe = Factory::getApplication();
		$jinput = $mainframe->input;

		if (empty($eventsItemid[$url]))
		{
			$eventsItemid[$url] = JT::utilities()->getItemId($url);
		}

		$pos = strpos($url, '#');

		if ($pos === false)
		{
			if (isset($eventsItemid[$url]))
			{
				if (strpos($url, 'Itemid=') === false && strpos($url, 'com_jticketing') !== false)
				{
					$url .= '&Itemid=' . $eventsItemid[$url];
				}
			}
		}
		else
		{
			if (isset($eventsItemid[$url]))
			{
				$url = str_ireplace('#', '&Itemid=' . $eventsItemid[$view] . '#', $url);
			}
		}

		$routedUrl = Route::_($url, $xhtml, $ssl);
		$routedUrl = htmlspecialchars_decode($routedUrl);

		return $routedUrl;
	}

	/**
	 * Function to get the link of the event
	 *
	 * @param   INT  $id        Id
	 * @param   INT  $catid     Cat ID
	 * @param   INT  $language  Lang
	 *
	 * @return  STRING  $links
	 *
	 * @since  1.0.0
	 */
	public static function getEventRoute($id, $catid = 0, $language = 0)
	{
		$id = explode(':', $id);
		$JTRouteHelper = new JTRouteHelper;

		// Create the link
		$link = 'index.php?option=com_jticketing&view=event&id=' . $id[0];
		$eventUrl = $JTRouteHelper->JTRoute($link);

		return $eventUrl;
	}
}
