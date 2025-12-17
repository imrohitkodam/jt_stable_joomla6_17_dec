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

use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

BaseDatabaseModel::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jticketing/models');

/**
 * mail helper class
 *
 * @package     JTicketing
 * @subpackage  component
 * @since       1.0
 */
class JticketingReportsHelper
{
	/**
	 * Function to get the category filter options
	 *
	 * @param   Boolean  $default  Add option of 'Select default category'
	 *
	 * @return  array
	 *
	 * @since 1.0.0
	 *
	 * @deprecated 3.2.0 The method is deprecated and will be removed in the next version.
	 * Use events model's getCatFilterOptions instead.
	 */
	public function getCatFilterOptions($default = true)
	{
		$categories = HTMLHelper::_('category.options', 'com_jticketing');

		// Remove add to Root from category list
		array_pop($categories);

		if ($default)
		{
			$obj = new stdClass;
			$obj->value = '';
			$obj->text = Text::_('COM_JTICKETING_FILTER_SELECT_EVENT_CATEGORY');
			$obj->disable = '';
			array_unshift($categories, $obj);
		}

		return $categories;
	}

	/**
	 * Function to get the user filter options
	 *
	 * @param   Boolean  $myteam  Fetch only my team users
	 *
	 * @return  array
	 *
	 * @since 1.0.0
	 *
	 * @deprecated 3.2.0 The method is deprecated and will be removed in the next version.
	 * Use user model's getUserFilterOptions instead.
	 */
	public function getUserFilterOptions($myteam = false)
	{
		$db = Factory::getDbo();

		$query = $db->getQuery(true);
		$query->select('u.id,u.username');
		$query->from('#__users as u');

		if ($myteam)
		{
			$helperPath = JPATH_ADMINISTRATOR . '/components/com_jticketing/helpers/jticketing.php';
			if (file_exists($helperPath))
			{
				require_once $helperPath;
			}
			$hasUsers = JticketingHelper::getSubusers();

			if (!empty($hasUsers))
			{
				$query->where('u.id IN(' . implode(',', $hasUsers) . ')');
			}
		}

		$db->setQuery($query);
		$users = $db->loadObjectList();

		$userFilter = array();
		$userFilter[] = HTMLHelper::_('select.option', '', Text::_('COM_JTICKETING_FILTER_SELECT_USER'));

		foreach ($users as $eachUser)
		{
			$userFilter[] = HTMLHelper::_('select.option', $eachUser->id, $eachUser->username);
		}

		return $userFilter;
	}

	/**
	 * Function to get the user filter options
	 *
	 * @param   Boolean  $myteam  Fetch only my team users
	 *
	 * @return  array
	 *
	 * @since 1.0.0
	 *
	 * @deprecated 3.2.0 The method is deprecated and will be removed in the next version.
	 * Use user model's getUserFilterOptions instead.
	 */
	public function getNameFilterOptions($myteam = false)
	{
		$db = Factory::getDbo();

		$query = $db->getQuery(true);
		$query->select('u.id,u.name');
		$query->from('#__users as u');

		if ($myteam)
		{
			$helperPath = JPATH_ADMINISTRATOR . '/components/com_jticketing/helpers/jticketing.php';
			if (file_exists($helperPath))
			{
				require_once $helperPath;
			}
			$hasUsers = JticketingHelper::getSubusers();

			if (!empty($hasUsers))
			{
				$query->where('u.id IN(' . implode(',', $hasUsers) . ')');
			}
		}

		$db->setQuery($query);
		$names = $db->loadObjectList();

		$nameFilter = array();
		$nameFilter[] = HTMLHelper::_('select.option', '', Text::_('COM_JTICKETING_FILTER_SELECT_USER'));

		foreach ($names as $eachName)
		{
			$nameFilter[] = HTMLHelper::_('select.option', $eachName->id, $eachName->name);
		}

		return $nameFilter;
	}

	/**
	 * Function to get the event filter
	 *
	 * @param   INT  $created_by  Fetch creators events
	 *
	 * @return  array
	 *
	 * @since 1.0.0
	 *
	 * @deprecated 3.2.0 The method is deprecated and will be removed in the next version.
	 * Use events model's getEventFilterOptions instead.
	 */
	public function getEventFilterOptions($created_by = 0)
	{
		$eventsModel = JT::model('events', array('ignore_request' => true));

		if ($created_by)
		{
			$eventsModel->setState('filter_creator', $created_by);
		}

		$events        = $eventsModel->getItems();
		$eventFilter   = array();
		$eventFilter[] = HTMLHelper::_('select.option', '', Text::_('COM_JTICKETING_FILTER_SELECT_EVENT'));

		if (!empty($events))
		{
			foreach ($events as $event)
			{
				$eventFilter[] = HTMLHelper::_('select.option', $event->id, $event->title);
			}
		}

		return $eventFilter;
	}

	/**
	 * Function to get the venue filter options
	 *
	 * @param   INT  $created_by  Fetch creators venue
	 *
	 * @return  array
	 *
	 * @since 1.0.0
	 *
	 * @deprecated 3.2.0 The method is deprecated and will be removed in the next version.
	 * Use venues model's getVenueFilterOptions instead.
	 */
	public function getVenueFilterOptions($created_by = 0)
	{
		$db = Factory::getDbo();

		$query = $db->getQuery(true);
		$query->select($db->quoteName('e.venue', 'venue_id'));
		$query->select($db->quoteName('v.name', 'title'));
		$query->from($db->quoteName('#__jticketing_events', 'e'));
		$query->join('INNER', $db->quoteName('#__jticketing_venues', 'v') . ' ON (' . $db->quoteName('e.venue') . ' = ' . $db->quoteName('v.id') . ')');

		if ($created_by)
		{
			$query->where('created_by = ' . (int) $created_by);
		}

		$db->setQuery($query);
		$venues = $db->loadObjectList();

		$venueFilter = array();
		$venueFilter[] = HTMLHelper::_('select.option', '', Text::_('COM_JTICKETING_FILTER_SELECT_VENUE'));

		if (!empty($venues))
		{
			foreach ($venues as $eachVenue)
			{
				$venueFilter[] = HTMLHelper::_('select.option', $eachVenue->venue_id, $eachVenue->title);
			}
		}

		return $venueFilter;
	}
}
