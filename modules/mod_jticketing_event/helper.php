<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2025 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * Helper class for module
 *
 * @package     JTicketing
 * @subpackage  component
 * @since       1.0
 */
class ModJticketingEventHelper
{
	/**
	 * Get data
	 *
	 * @param   Array  $params  com_jticketing params
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function getData($params, $latitude = 0, $longitude = 0, $onEventId = 0)
	{
		// FrontendHelper is loaded here
		JLoader::import('frontendhelper', JPATH_SITE . '/components/com_jticketing/helpers');
		$jticketingFrontendHelper = new Jticketingfrontendhelper;
		$orderByDir      = $params->get('order_dir');
		$noOfEventShow = $params->get('no_of_event_show');
		$distance      = $params->get('distance_limit');
		$featuredEvent   = $params->get('featured_event');
		$showTime        = $params->get('show_time');
		$ticketType      = $params->get('ticket_type');
		$image            = $params->get('image');
		$defaultCatid     = $params->get('defaultCatid');
		$orderBy     = $params->get('event_order_by');
		$date             = date("Y-m-d H:i:s");

		$input = Factory::getApplication()->input;
		$tagId = $input->get('tagid', '', array());

		if (empty($tagId))
		{
			$tagId = $params->get('tags', array());
		}

		$where = array();

		$db      = Factory::getDbo();

		$query = $db->getQuery(true);
		$query->select(array('e.*'));
		$query->select(
		$db->qn(
			array('c.path','v.name', 'v.online_provider', 'v.address', 'v.country', 'v.state_id','v.city', 'v.zipcode')
		)
		);
		$query->select($db->quoteName('v.params', 'venue_params'));
		$query->from($db->qn('#__jticketing_events', 'e'));
		$query->join('LEFT', $db->qn('#__jticketing_venues', 'v') . ' ON (' . $db->qn('v.id') . ' = ' . $db->qn('e.venue') . ')');
		$query->join('LEFT', $db->qn('#__categories', 'c') . ' ON (' . $db->qn('e.catid') . ' = ' . $db->qn('c.id') . ')');
		$query->where($db->qn('e.state') . '=' . $db->quote(1) . ' AND ' . $db->qn('c.extension') . '=' . $db->quote('com_jticketing'));

		if($distance > 0){
			$query->where('ST_Distance_Sphere(POINT(' . (float)$longitude . ', ' . (float)$latitude . '),POINT(v.longitude, v.latitude)) <= '. ($distance * 1000));
		}
		if (!empty($defaultCatid))
		{
			$query->where($db->qn('e.catid') . ' = ' . $db->quote($defaultCatid));
		}

		if ($featuredEvent == 1)
		{
			$query->where($db->qn('e.featured') . ' = ' . $db->quote(1));
		}

		if ($showTime == "upcoming")
		{
			$query->where($db->qn('e.startdate') . ' >= ' . $db->quote($date));
		}

		if ($showTime == "past")
		{
			$query->where('e.enddate <= UTC_TIMESTAMP()');
		}

		if ($showTime == "ongoing")
		{
			$query->where("e.enddate >= UTC_TIMESTAMP()");
		}

		if ($showTime == "today")
		{
			$today = date("Y-m-d");
			$query->where(('DATE(e.startdate)') . ' = ' . $db->quote($today));
		}

		if (is_array($tagId) && count($tagId) === 1)
		{
			$tagId = current($tagId);
		}

		if ($onEventId > 0)
		{
			$query->where("e.id != ".$onEventId);
		}

		if (is_array($tagId))
		{
			$tagId = implode(',', ArrayHelper::toInteger($tagId));

			if ($tagId)
			{
				$subQuery = $db->getQuery(true)
				->select('DISTINCT content_item_id')
				->from($db->quoteName('#__contentitem_tag_map'))
				->where('tag_id IN (' . $tagId . ')')
				->where('type_alias = ' . $db->quote('com_jticketing.event'));

				$query->innerJoin('(' . (string) $subQuery . ') AS tagmap ON tagmap.content_item_id = e.id');
			}
		}
		elseif ($tagId)
		{
			$query->innerJoin(
					$db->quoteName('#__contentitem_tag_map', 'tagmap')
					. ' ON tagmap.tag_id = ' . (int) $tagId
					. ' AND tagmap.content_item_id = e.id'
					. ' AND tagmap.type_alias = ' . $db->quote('com_jticketing.event')
					);
		}

		$query->group($db->qn('e.id'));
		$query->order($db->qn('e.' . $orderBy) . $orderByDir);
		$query->setLimit($noOfEventShow);

		$db->setQuery($query);
		$event = $db->loadObjectList();

		if ($event)
		{
			for ($i = 0; $i < count($event); $i++)
			{
				$eventData['event'] = $event[$i];
				$eventId        = $event[$i]->id;

				$query = $db->getQuery(true);
				$query->select('i.eventid,i.id');
				$query->from($db->qn('#__jticketing_integration_xref', 'i'));
				$query->where($db->qn('i.source') . ' = ' . $db->quote('com_jticketing') . ' AND' . $db->qn('i.eventid') . ' = ' . $db->quote($eventId));
				$db->setQuery($query);
				$integrationDetails       = $db->loadObject();

				if ($integrationDetails)
				{
					BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_jticketing/models');
					$jtickeitngModelEventFrom = BaseDatabaseModel::getInstance('EventForm', 'JticketingModel');
					$eventImageData = $jtickeitngModelEventFrom->getItem($integrationDetails->eventid);
				}

				if (isset($eventImageData->image->media_m))
				{
					$eventData['image'] = $eventImageData->image->media_m;
				}
				else
				{
					$eventData['image'] = '';
				}

				$eventData['ticket_types'] = $jticketingFrontendHelper->getTicketTypes($integrationDetails->id);

				if (count($eventData['ticket_types']) == 1)
				{
					foreach ($eventData['ticket_types'] as $ticketInfo)
					{
						$eventData['event_max_ticket'] = $ticketInfo->price;
						$eventData['event_min_ticket'] = $ticketInfo->price;
					}
				}
				else
				{
					$maxTicketPrice = -9999999;
					$minTicketPrice = 9999999;

					foreach ($eventData['ticket_types'] as $ticketInfo)
					{
						if ($ticketInfo->price > $maxTicketPrice)
						{
							$maxTicketPrice = $ticketInfo->price;
						}

						if ($ticketInfo->price < $minTicketPrice)
						{
							$minTicketPrice = $ticketInfo->price;
						}
					}

					$eventData['event_max_ticket'] = $maxTicketPrice;
					$eventData['event_min_ticket'] = $minTicketPrice;
				}

				if (empty($eventData['event']->location) && $eventData['event']->venue != '0')
				{
					$venueDetails = JT::model('venueform')->getItem($eventData['event']->venue);

					if (!empty($venueDetails->online) && $venueDetails->online_provider == 'plug_tjevents_adobeconnect')
					{
						$eventData['location'] = 'Adobe-' . $venueDetails->name;
					}
					else
					{
						$address = $eventData['event']->address;
						$eventData['location'] = $venueDetails->name . ' - ' . $address;
					}
				}

				$result[]              = $eventData;
			}

			return $result;
		}
	}

	/**
	 * function to sort
	 *
	 * @param   integer  $array   array
	 * @param   integer  $column  column
	 * @param   integer  $order   order
	 * @param   integer  $count   count
	 *
	 * @return  array
	 *
	 * @since   1.0
	 */
	public function multi_d_sort($array, $column, $order, $count)
	{
		foreach ($array as $key => $row)
		{
			$orderby[$key] = $row->$column;
		}

		if ($order == 'ASC')
		{
			array_multisort($orderby, SORT_ASC, $array);
		}
		else
		{
			if (!empty($array))
			{
				array_multisort($orderby, SORT_DESC, $array);
			}
		}

		return $array;
	}

	/**
	 * Get eventsNearMeAjax: all events based on latitude and longitude, to get events near users location.
	 * In  module params, when distance > 0, this function is called on ajax request. 
	 * 
	 * @param null
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */

	public static function eventsNearMeAjax(){
		$input = Factory::getApplication()->input;
		$latitude = $input->get('latitude', '', 0);
		$longitude = $input->get('longitude', '', 0);
		$eventId = $input->get('eventid', '', 0);
		$modid = $input->get('modid', '', 0);
		ob_start();
		require __DIR__ . '/tmpl/events_nearme.php';
		$html = ob_get_clean();
		
		return json_encode(['data' => $html, 'mod_jticketing_container' => $modid]);
	}

	/**
	 * Get suggested events for the user.
	 *
	 * @param   int|null  $currentEventId  ID of the current event to exclude from suggestions.
	 *
	 * @return  array     List of suggested events with event object, location, and image.
	 *
	 * @since   5.1.0
	 */
	public static function getSuggestedEvents($params, $currentEventId = null)
	{
		$noOfEventShow  = (int) $params->get('no_of_event_show', 10);

		$db   = Factory::getDbo();
		$user = Factory::getUser();

		// Build subqueries
		//sub query to fetch  events matching category
		$subCatid = $db->getQuery(true)
			->select('DISTINCT ev.catid')
			->from('#__jticketing_events AS ev')
			->join('INNER', '#__jticketing_attendees AS o ON ev.id = o.event_id')
			->where('o.owner_id = ' . (int) $user->id);

		//sub query to fetch  events matching venue
		$subVenue = $db->getQuery(true)
			->select('DISTINCT ev.venue')
			->from('#__jticketing_events AS ev')
			->join('INNER', '#__jticketing_attendees AS o ON ev.id = o.event_id')
			->where('o.owner_id = ' . (int) $user->id);

		//sub query to fetch  events matching event owner
		$subOrganizer = $db->getQuery(true)
			->select('DISTINCT ev.created_by')
			->from('#__jticketing_events AS ev')
			->join('INNER', '#__jticketing_attendees AS o ON ev.id = o.event_id')
			->where('o.owner_id = ' . (int) $user->id);

		// Match score CASE expression
		$matchScore = '(CASE ' .
		'WHEN e.catid IN (' . $subCatid . ') AND e.venue IN (' . $subVenue . ') AND e.featured = 1 THEN 5 ' .
		'WHEN e.catid IN (' . $subCatid . ') AND e.venue IN (' . $subVenue . ') AND CURRENT_DATE BETWEEN e.startdate AND e.enddate THEN 4 ' .
		'WHEN e.catid IN (' . $subCatid . ') AND e.venue IN (' . $subVenue . ') AND e.created_by IN (' . $subOrganizer . ') THEN 3 ' .
		'WHEN e.catid IN (' . $subCatid . ') AND e.venue IN (' . $subVenue . ') THEN 2 ' .
		'WHEN e.catid IN (' . $subCatid . ') AND e.featured = 1 THEN 1 ' .
		'ELSE 0 END)';

		// Main query
		$query = $db->getQuery(true)
		->select('e.*, ' . $matchScore . ' AS match_score')
		->from('#__jticketing_events AS e')
		->where('e.state = 1')
		->where('e.enddate > NOW()')
		->having($matchScore . ' > 0') // <-- Filter only those with match_score > 0
		->order('match_score DESC')
		->setLimit($noOfEventShow);

		//if user is on event details page then hide this event from suggestion
		if ($currentEventId !== null)
		{
			$query->where('e.id != ' . (int) $currentEventId);
		}
		
		$db->setQuery($query);
		$finalEvents = $db->loadObjectList();

		BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_jticketing/models');
		$jticketingModelEventForm = BaseDatabaseModel::getInstance('EventForm', 'JticketingModel');
		$wrapped = [];

		foreach ($finalEvents as $event)
		{
			$imagePath = '';
			$item = $jticketingModelEventForm->getItem($event->id);

			if (isset($item->image->media_m))
			{
				$imagePath = $item->image->media_m;

				if (!empty($imagePath) && !str_starts_with($imagePath, 'http'))
				{
					$imagePath = JUri::root() . ltrim($imagePath, '/');
				}
			}

			if (empty($event->location) && $event->venue != '0')
			{
				$venueDetails = JT::model('venueform')->getItem($event->venue);
				$address = !empty($venueDetails->address) ? $venueDetails->address : ' ';

				if (!empty($venueDetails->online) && $venueDetails->online_provider == 'plug_tjevents_adobeconnect')
				{
					$location = 'Adobe - ' . $venueDetails->name;
				}
				else
				{
					$location = $venueDetails->name . ' - ' . $address;
				}
			}
			else
			{
				$location = $event->location;
			}

			$wrapped[] = [
				'event'    => $event,
				'location' => $location,
				'image'    => $imagePath,
			];
		}

		return $wrapped;
	}

	/**
	 * Checks if the given user has purchased any event (completed orders only).
	 *
	 * @param   int  $userId  The ID of the user to check.
	 *
	 * @return  bool  True if the user has purchased at least one event, false otherwise.
	 *
	 * @since   5.1.0
	 */
	public function hasUserPurchasedAnyEvent($userId)
	{
		if (!$userId)
		{
			return false;
		}

		$db = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('COUNT(*)')
			->from('#__jticketing_order')
			->where('user_id = ' . (int) $userId)
			->where('status = ' . $db->quote('C'));

		$db->setQuery($query);

		return (bool) $db->loadResult();
	}
}
