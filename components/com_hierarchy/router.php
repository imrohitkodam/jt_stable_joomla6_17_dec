<?php
/**
 * @version    SVN: <svn_id>
 * @package    Com_Hierarchy
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2017 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Component\Router\RouterBase;
use Joomla\CMS\Table\Table;

// Add Table Path
Table::addIncludePath(JPATH_ROOT . '/administrator/components/com_hierarchy/tables');

/**
 * Routing class from com_hierarchy
 *
 * @subpackage  com_hierarchy
 *
 * @since       1.0.0
 */
class HierarchyRouter extends RouterBase
{
/**
 * Build the route for the com_hierarchy component
 *
 * @param   array  &$query  An array of URL arguments
 *
 * @return   array  The URL arguments to use to assemble the subsequent URL.
 *
 * @since  1.5
 */
	public function build(&$query)
	{
		$segments = array();

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
	 * Parse the segments of a URL.
	 *
	 * @param   array  $segments  The segments of the URL to parse.
	 *
	 * @return  array  The URL attributes to be used by the application.
	 *
	 * @since  1.5
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