<?php
/**
 * @package     JTicketing
 * @subpackage  com_jticketing
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die;

use Joomla\CMS\Factory;

require_once JPATH_SITE . '/components/com_jticketing/models/eventform.php';

/**
 * jticketing Model
 *
 * @since  0.0.1
 */
class JTicketingModelEvent extends JticketingModelEventForm
{
	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  mixed  The data for the form.
	 *
	 * @since   2.3.5
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = Factory::getApplication()->getUserState('com_jticketing.edit.event.data', array());
		if (empty($data))
		{
			$data = $this->getItem();
			if (!empty($data->startdate)) {
				$startDateTime = explode(' ', $data->startdate);
				$data->eventstart_date = date('Y-m-d', strtotime($startDateTime[0]));
				$data->start_time = isset($startDateTime[1]) ? $startDateTime[1] : '';
			}
			if (!empty($data->enddate)) {
				$endDateTime = explode(' ', $data->enddate);
				$data->eventend_date = date('Y-m-d', strtotime($endDateTime[0]));
				$data->end_time = isset($endDateTime[1]) ? $endDateTime[1] : '';
			}
		}
		return $data;
	}
}
