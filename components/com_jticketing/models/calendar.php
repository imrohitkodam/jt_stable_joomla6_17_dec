<?php
/**
 * @package     JTicketing
 * @subpackage  com_jticketing
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\ListModel;

/**
 * Model for calendar
 *
 * @package     JTicketing
 * @subpackage  component
 * @since       1.0
 */
class JticketingModelCalendar extends ListModel
{
	/**
	 * Get all events for calendar
	 *
	 * @return  array  event list
	 *
	 * @since   1.0
	 */
	public function getEvents()
	{
		$this->populateState();
		$params = array();
		$params['category_id'] = $this->state->get("filter.filter_evntCategory");
		$Jticketingmainhelper = new Jticketingmainhelper;
		$data =	$Jticketingmainhelper->getEvents($params);

		if (empty($data)) {
			return array();
		}

		$colors = [
			"#FFB3B3", // Soft Red
			"#D9A7EB", // Light Purple
			"#FFD699", // Warm Yellow
			"#7EB5E6", // Sky Blue
			"#FFA07A", // Peach Orange
			"#A4DE02", // Fresh Green
			"#FF82C3", // Bright Pink
			"#2DC7C7", // Cool Cyan
		];
		$eventColors = [];

		foreach ($data as $k => $v)
		{
			$config = Factory::getConfig();
			date_default_timezone_set($config->get('offset'));

			if ($v['recurring_type'] === 'No_repeat') {
				$startDate = $v['startdate'];
				$endDate = $v['enddate'];
			} else {
				$startDate = $v['start_date'];
				$endDate = $v['end_date'];
			}

			$data[$k]['start'] = !empty($startDate) ? strtotime($startDate) . '000' : null;
			$data[$k]['end'] = !empty($endDate) ? strtotime($endDate) . '000' : ($data[$k]['start'] ?? null);

			$data[$k]['event_time'] = ($startDate ? date('G:i', strtotime($startDate)) : 'N/A') . '-' . ($endDate ? date('G:i', strtotime($endDate)) : 'N/A');
			$data[$k]['event_start_time'] = $startDate ? date('G:i', strtotime($startDate)) : 'N/A';

			if ($startDate && date('a', strtotime($startDate)) === 'am') {
				$data[$k]['event_title_time'] = str_replace("am", "a", date('a', strtotime($startDate)));
				$data[$k]['event_title_time'] = date('g', strtotime($startDate)) . $data[$k]['event_title_time'];
			} elseif ($startDate) {
				$data[$k]['event_title_time'] = str_replace("pm", "p", date('a', strtotime($startDate)));
				$data[$k]['event_title_time'] = date('g', strtotime($startDate)) . $data[$k]['event_title_time'];
			} else {
				$data[$k]['event_title_time'] = 'N/A';
			}

			$eventId = $v['id'];
			if (!isset($eventColors[$eventId])) {
				$eventColors[$eventId] = $colors[$eventId % count($colors)];
			}

			$data[$k]['background_color'] = $eventColors[$eventId];
		}

		return $data;
	}

	/**
	 * Method to get all events
	 *
	 * @param   object  $ordering   user id
	 * @param   object  $direction  user id
	 *
	 * @return  void
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// Initialise variables.
		$app = Factory::getApplication('site');

		// Filtering ServiceType
		$filter_evntCategory = $app->getUserStateFromRequest($this->context . '.filter.filter_evntCategory', 'filter_evntCategory', '', 'string');
		$this->setState('filter.filter_evntCategory', $filter_evntCategory);
	}
}
