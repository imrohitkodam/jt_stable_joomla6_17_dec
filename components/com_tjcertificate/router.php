<?php
/**
 * @package     TJCertificate
 * @subpackage  com_tjcertificate
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\Component\Router\RouterBase;

/**
 * Class TjCertificateRouter
 *
 * @since  1.0.0
 */
class TjCertificateRouter extends RouterBase
{
	/**
	 * Build method for URLs
	 * This method is meant to transform the query parameters into a more human
	 * readable form. It is only executed when SEF mode is switched on.
	 *
	 * @param   array  &$query  An array of URL arguments
	 *
	 * @return  array  The URL arguments to use to assemble the subsequent URL.
	 *
	 * @since   1.0.0
	 */
	public function build(&$query)
	{
		$menu_views = array('certificates', 'certificate', 'trainingrecord', 'bulktrainingrecord');

		$segments = array();

		$app = Factory::getApplication();
		$menu = $app->getMenu();
		$db = Factory::getDbo();

		// We need a menu item.  Either the one specified in the query, or the current active one if none specified
		if (empty($query['Itemid']))
		{
			$menuItem = $menu->getActive();
			unset($query['Itemid']);
		}
		else
		{
			$menuItem = $menu->getItem($query['Itemid']);
		}

		// Check again
		if (isset($menuItem) && $menuItem->component != 'com_socialads')
		{
			unset($query['Itemid']);
		}

		// Are we dealing with an view for which menu is already created
		if (($menuItem) && isset($menuItem->query['view']) && isset($query['view']))
		{
			if ($menuItem->query['view'] == $query['view'] && in_array($query['view'], $menu_views))
			{
				$segments[] = $query['view'];
				unset($query['view']);
			}
		}

		if (isset($query['task']))
		{
			$segments[] = implode('/', explode('.', $query['task']));
			unset($query['task']);
		}

		if (isset($query['view']))
		{
			$segments[] = $query['view'];
			unset($query['view']);
		}

		if (isset($query['id']))
		{
			$segments[] = $query['id'];
			unset($query['id']);
		}

		return $segments;
	}

	/**
	 * Parse method for URLs
	 * This method is meant to transform the human readable URL back into
	 * query parameters. It is only executed when SEF mode is switched on.
	 *
	 * @param   array  &$segments  The segments of the URL to parse.
	 *
	 * @return  array  The URL attributes to be used by the application.
	 *
	 * @since   1.0.0
	 */
	public function parse(&$segments)
	{
		$vars = array();

		// View is always the first element of the array
		$vars['view'] = array_shift($segments);

		while (!empty($segments))
		{
			$segment = array_pop($segments);

			if (is_numeric($segment))
			{
				$vars['id'] = $segment;
			}
			else
			{
				$vars['task'] = $vars['view'] . '.' . $segment;
			}
		}

		return $vars;
	}
}
