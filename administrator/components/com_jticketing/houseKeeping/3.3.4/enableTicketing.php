<?php
/**
 * @package     JTicketing
 * @subpackage  com_jticketing
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;

/**
 * JTicketing Enable Ticketing Migration
 *
 * @since  3.3.4
 */
class TjHouseKeepingEnableTicketing extends TjModelHouseKeeping
{
	public $title       = "Update 'enable_ticket' column";

	public $description = "Update the value for 'enable_ticket' column for previously created events";

	/**
	 * This function will update the data for the Event having tickets
	 *
	 * @return  array $result
	 *
	 * @since  3.1.0
	 */
	public function migrate()
	{
		$result = array();

		try
		{
			$db    = Factory::getDbo();

			// Get all the events from integration table
			$query = $db->getQuery(true);
			$query->select('*');
			$query->from($db->quoteName('#__jticketing_integration_xref'));
			$db->setQuery($query);
			$events = $db->loadObjectList();

			foreach ($events as $eventIntegration)
			{
				// Get all the tickets related to one Event
				$query = $db->getQuery(true);
				$query->select('*');
				$query->from($db->quoteName('#__jticketing_types'));
				$query->where($db->quoteName('eventid') . '=' . $eventIntegration->id);
				$db->setQuery($query);
				$tickets = $db->loadObjectList();

				if (!empty($tickets))
				{
					// if the event has ticket, update the 'enable_ticket' to 1
					$query = $db->getQuery(true);
					$query->update($db->quoteName('#__jticketing_integration_xref'))
						->set($db->quoteName('enable_ticket') . ' = ' . 1)
						->where($db->quoteName('id') . ' = ' . (int) $eventIntegration->id);
					$db->setQuery($query);
					$db->execute();
				}
			}

		$result['status']  = true;
		$result['message'] = "Migration completed successfully.";

			return $result;
		}
		catch (Exception $e)
		{
			$result['err_code'] = '';
			$result['status']   = false;
			$result['message']  = $e->getMessage();
		}

		return $result;
	}
}
