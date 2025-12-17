<?php
/**
 * @package     JTicketing
 * @subpackage  com_jticketing
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Component\Router\RouterBase;

/**
 * Class JTicketingRouter
 *
 * @since  3.3
 */
class JTicketingRouter extends RouterBase
{
	private  $views = array(
						'events','event','eventform','order',
						'orders','mytickets','mypayouts',
						'calendar','attendee_list', 'attendees','allticketsales','venues','venueform','enrollment','waitinglist','coupons', 'couponform', 'categories', 'pdftemplates', 'pdftemplate'
						);

	private  $specialViews = array('events', 'event', 'orders', 'order','eventform','venues','venueform', 'coupons', 'couponform', 'categories', 'pdftemplates', 'pdftemplate');

	private  $viewsNeedingEventId = array('event', 'eventform', 'orders', 'order','venues','venueform');

	private  $viewWithOrderId = array('event', 'eventform', 'orders', 'order','venues','venueform');

	private  $viewsNeedingTmpl = array('mytickets');

	/**
	 * Build the route for the com_content component
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

		// Get a menu item based on Itemid or currently active
		$app  = Factory::getApplication();
		$menu = $app->getMenu();
		$db   = Factory::getDbo();

		// We need a menu item.  Either the one specified in the query, or the current active one if none specified
		if (empty($query['Itemid']))
		{
			$menuItem = $menu->getActive();
			$menuItemGiven = false;
		}
		else
		{
			$menuItem = $menu->getItem($query['Itemid']);
			$menuItemGiven = true;
		}

		// Check again
		if ($menuItemGiven && isset($menuItem) && $menuItem->component != 'com_jticketing')
		{
			unset($query['Itemid']);
		}

		// Check if view is set.
		if (isset($query['view']))
		{
			$view = $query['view'];
		}
		else
		{
			// We need to have a view in the query or it is an invalid URL
			return $segments;
		}

		// Add the view only for normal views, for special its just the slug
		if (isset($query['view']) && !in_array($query['view'], $this->specialViews))
		{
			$segments[] = $query['view'];
			unset($query['view']);
		}

		// Handle the special views
		if ($view == 'events')
		{
			if (!empty($query['filter_events_cat']))
			{
				$catId = (int) $query['filter_events_cat'];

				if ($catId)
				{
					// Joomla 6: JVERSION check removed
		if (false) // Legacy < '4.0.0')
					{
						Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_categories/tables');
						$category = Table::getInstance('Category', 'CategoriesTable', array('dbo', $db));
					}
					else
					{
						$category = Table::getInstance('CategoryTable', '\\Joomla\\Component\\Categories\\Administrator\\Table\\');
					}

					$category->load(array('id' => $catId, 'extension' => 'com_jticketing'));
					$segments[] = $category->alias;
					unset($query['filter_events_cat']);
					unset($query['view']);
				}
				else
				{
					$segments[] = '';
					unset($query['filter_events_cat']);
					unset($query['view']);
				}
			}

			unset($query['view']);
			unset($query['layout']);
		}

		if ($view == 'event')
		{
			if (isset($query['id']))
			{
				$eventTable = $this->_getEventRow($query['id'], 'id');
				$segments[] = $eventTable->alias;
				unset($query['id']);
				unset($query['view']);
			}
		}

		if ($view == 'eventform')
		{
			if (isset($query['id']))
			{
				$eventTable = $this->_getEventRow($query['id'], 'id');
				$segments[] = 'edit';
				$segments[] = $eventTable->alias;
				unset($query['id']);
				unset($query['view']);
			}
		}

		if ($view == 'order' && isset($query['orderId']))
		{
			$segments[] = $query['view'];

			// Check if layout is not empty.
			if (!empty($query['layout']))
			{
				$segments[] = $query['layout'];
			}
			else
			{
				$segments[] = 'default';
			}

			$segments[] = $query['orderId'];
			unset($query['view']);
			unset($query['layout']);
			unset($query['orderId']);
		}

		if ($view == 'venueform')
		{
			if (isset($query['id']))
			{
				$venueTable = $this->_getVenueRow($query['id'], 'id');
				$segments[] = 'edit';
				$segments[] = $venueTable->alias;
				unset($query['id']);
				unset($query['view']);
				unset($query['layout']);
			}
		}

		if ($view == 'attendee_list')
		{
			unset($query['event']);

			if (isset($query['attendee_id']))
			{
				$segments[] = $query['layout'];
				$segments[] = $query['attendee_id'];
				unset($query['tmpl']);
				unset($query['layout']);
				unset($query['attendee_id']);
			}
		}

		if ($view == 'attendees')
		{
			unset($query['event']);

			if (isset($query['attendee_id']))
			{
				$segments[] = $query['layout'];
				$segments[] = $query['attendee_id'];
				unset($query['tmpl']);
				unset($query['layout']);
				unset($query['attendee_id']);
			}
		}

		if ($view == 'mytickets')
		{
			if (isset($query['attendee_id']))
			{
				$segments[] = $query['layout'];
				$segments[] = $query['attendee_id'];
				unset($query['tmpl']);
				unset($query['layout']);
				unset($query['attendee_id']);
			}
		}

		if ($view == 'orders' && isset($query['orderid']))
		{
				$segments[] = $query['view'];
				$segments[] = $query['layout'];
				$segments[] = $query['orderid'];

				unset($query['orderid']);
				unset($query['layout']);
				unset($query['processor']);
				unset($query['view']);

				if (isset($query['sendmail']))
				{
					unset($query['sendmail']);
				}

				if (isset($query['email']))
				{
					$segments[] = $query['email'];
					unset($query['email']);
				}
		}

		if ($view == 'couponform')
		{
			if (isset($query['id']))
			{
				$segments[] = $query['view'];
				$segments[] = 'edit';
				$segments[] = $query['id'];
				unset($query['id']);
				unset($query['view']);
				unset($query['layout']);
			}
		}

		if ($view == 'pdftemplate')
		{
			if (isset($query['id']))
			{
				if (isset($query['view']))
				{
					$segments[] = $query['view'];
				}
		
				$segments[] = 'edit';
				$segments[] = $query['id'];
				unset($query['id']);
				unset($query['view']);
				unset($query['layout']);
			}
		}

		if (in_array($view, $this->viewsNeedingEventId) && isset($query['eventid']))
		{
			$eventTable = $this->_getEventRow($query['eventid'], 'id');

			if (!empty($eventTable->alias))
			{
				$segments[] = $eventTable->alias;
			}
			else
			{
				$segments[] = $eventTable->id;
			}

			unset($query['eventid']);
			unset($query['layout']);
		}

		// End Handle normal views
		if (in_array($view, $this->viewsNeedingTmpl))
		{
			unset($query['tmpl']);
		}

		return $segments;
	}

	/**
	 * Parse the segments of a URL.
	 *
	 * @param   array  &$segments  The segments of the URL to parse.
	 *
	 * @return  array  The URL attributes to be used by the application.
	 *
	 * @since  1.5
	 */
	public function parse(&$segments)
	{
		$item = $this->menu->getActive();
		$vars = array();
		$db = Factory::getDbo();

		// Count route segments
		$count = count($segments);

		if ($count == 1)
		{
			// Joomla 6: JVERSION check removed
		if (false) // Legacy < '4.0.0')
			{
				Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_categories/tables');
				$categoryTable = Table::getInstance('Category', 'CategoriesTable', array('dbo', $db));
			}
			else
			{
				$categoryTable = Table::getInstance('CategoryTable', '\\Joomla\\Component\\Categories\\Administrator\\Table\\');
			}

			$categoryTable->load(array('alias' => $segments[0], 'extension' => 'com_jticketing'));

			if ($categoryTable->id)
			{
				$vars['view'] = 'events';
				$vars['filter_events_cat'] = $categoryTable->id;
			}
			elseif ($eventTableId = (isset($this->_getEventRow($segments[0])->id) ? $this->_getEventRow($segments[0])->id : 0))
			{
				$vars['view'] = 'event';
				$vars['id'] = $eventTableId;
			}
			elseif (in_array($segments[0], $this->views))
			{
				$vars['view'] = $segments[0];
			}

			array_shift($segments);
		}
		else
		{
			$vars['view'] = $segments[0];

			switch ($vars['view'])
			{
				case 'orders':
					if (isset($segments[1]))
					{
						$vars['layout'] = $segments[1];
						$vars['orderid'] = $segments[2];

						if (isset($segments[3]))
						{
							$vars['email'] = $segments[3];
						}
					}
					break;

				case 'mytickets':
				case 'attendee_list':
				case 'attendees':
					if (isset($segments[1]))
					{
						$vars['layout'] = $segments[1];
						$vars['attendee_id'] = $segments[2];

					}
					break;

				case 'couponform':
					if (isset($segments[2]))
					{
						$vars['id'] = $segments[2];
					}
					break;

				case 'pdftemplate':
					if (isset($segments[2]))
					{
						$vars['id'] = $segments[2];
					}
					break;

				case 'order' :
					if (isset($segments[1]))
					{
						$vars['layout'] 	= $segments[1];
						$vars['orderId'] 	= $segments[2];
					}
					break;

				default:
					if (in_array($vars['view'], $this->viewsNeedingEventId))
					{
						$eventTable = $this->_getEventRow($segments[1]);
						$vars['eventid'] = $eventTable->id;
					}
					else
					{
						if ($vars['view'] == 'edit' && $eventTableId = $this->_getEventRow($segments[1])->id)
						{
							$vars['view'] = 'eventform';
							$eventTable = $this->_getEventRow($segments[1]);
							$vars['eventid'] = $eventTable->id;
							$vars['id'] = $eventTable->id;
						}

						if ($vars['view'] == 'edit' && $eventTableId = $this->_getVenueRow($segments[1])->id)
						{
							$venueTable = $this->_getVenueRow($segments[1]);
							$vars['view'] = 'venueform';
							$vars['id'] = $venueTable->id;
						}
					}
			}

			if ($count = 2)
			{
				if ($vars['view'] == 'edit' && $eventTableId = $this->_getEventRow($segments[1])->id)
				{
					$vars['view'] = 'eventform';
					$vars['id'] = $eventTableId;
				}
			}

			if (in_array($vars['view'], $this->viewsNeedingTmpl))
			{
				$vars['tmpl'] = 'component';
			}
		}

		// For joomla 4
		if (!empty($segments))
		{
			$segments = array();
		}

		return $vars;
	}

	/**
	 * Get a event row based on alias or id
	 *
	 * @param   mixed   $event  The id or alias of the event to be loaded
	 * @param   string  $input  The field to match to load the event
	 *
	 * @return  object  The event JTable object
	 */
	private function _getEventRow($event, $input = 'alias')
	{
		$com_params = ComponentHelper::getParams('com_jticketing');
		$integration = $com_params->get('integration');

		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		if ($integration == 1)
		{
			$query->select($db->quoteName(array('xref.id', 'xref.eventid')));
			$query->from($db->quoteName('#__jticketing_integration_xref', 'xref'));
			$query->select($db->quoteName(array('comm.id', 'comm.title')));
			$query->join('LEFT', $db->quoteName('#__community_events', 'comm')
			. ' ON (' . $db->quoteName('xref.eventid') . ' = ' . $db->quoteName('comm.id') . ')');
			$query->where($db->quoteName('comm.id') . ' = ' . $db->quote($event));
			$db->setQuery($query);
			$events = $db->loadObject();

			$obj = new stdClass;
			if ($events)
			{
				$obj->id = $events->eventid;
			}

			return $obj;
		}
		elseif ($integration == 2)
		{
			Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jticketing/tables');
			$table = Table::getInstance('Event', 'JticketingTable', array('dbo', $db));
			$table->load(array($input => $event));

			return $table;
		}
		elseif ($integration == 3)
		{
			$query->select($db->quoteName(array('xref.id', 'xref.eventid')));
			$query->from($db->quoteName('#__jticketing_integration_xref', 'xref'));
			$query->join('LEFT', $db->quoteName('#__jevents_vevent', 'jevent')
			. ' ON (' . $db->quoteName('xref.eventid') . ' = ' . $db->quoteName('jevent.ev_id') . ')');
			$query->where($db->quoteName('jevent.ev_id') . ' = ' . $db->quote($event));
			$db->setQuery($query);
			$events = $db->loadObject();

			$obj = new stdClass;
			if ($events)
			{
				$obj->id = $events->eventid;
			}

			return $obj;
		}
		elseif ($integration == 4)
		{
			Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_easysocial/tables');
			$table = Table::getInstance('Cluster', 'SocialTable', array('dbo', $db));
			$table->load(array($input => $event));

			return $table;
		}
	}

	/**
	 * Get a venue row based on alias or id
	 *
	 * @param   mixed   $venue  The id or alias of the event to be loaded
	 * @param   string  $input  The field to match to load the event
	 *
	 * @return  object  The event JTable object
	 */
	private function _getVenueRow($venue, $input = 'alias')
	{
		$db    = Factory::getDbo();
		Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jticketing/tables');
		$table = Table::getInstance('Venue', 'JticketingTable', array('dbo', $db));
		$table->load(array($input => $venue));

		return $table;
	}
}
