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
use Joomla\CMS\Table\Table;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;


class JticketingModelRecurringEvents extends AdminModel
{
    /**
     * Method to get the table for recurring events.
     *
     * @param string $type
     * @param string $prefix
     * @param array  $config
     *
     * @return Table
     */
    public function getTable($type = 'RecurringEvent', $prefix = 'JticketingTable', $config = array())
    {
        return Table::getInstance($type, $prefix, $config);
    }
    /**
     *  Overrides getForm to disable form handling by always returning false.
     */
    public function getForm($data = array(), $loadData = true)
    {
        return false; 
    }

    /**
     * Method to save the recurring event data.
     *
     * @param array $data
     * @return bool
     */
    public function save($data)
    {
        $db = Factory::getDbo();
        $isUpdate = !empty($data['event_id']); 
        $repeatVia = isset($data['repeat_via']) ? $data['repeat_via'] : '';
    
        if ($data['recurring_type'] === 'No_repeat') {
            if ($isUpdate) {
                $query = $db->getQuery(true)
                    ->delete($db->quoteName('#__jticketing_recurring_events'))
                    ->where($db->quoteName('event_id') . ' = ' . (int)$data['event_id']);
                $db->setQuery($query);
                $db->execute();
            }
            return true;
        }
        if ($repeatVia === 'rep_until') {
            unset($data['repeat_count']);
            if (!empty($data['repeat_until'])) {
                $repeatUntil = new DateTime($data['repeat_until']);
                $startDate = new DateTime($data['startdate']);
                if ($repeatUntil <= $startDate) {
                    $this->setError(Text::_('COM_JTICKETING_ERROR_REPEAT_UNTIL_GREATER_THAN_STARTDATE'));
                    return false;
                }
            }
        } elseif ($repeatVia === 'rep_count') {
            unset($data['repeat_until']);
            if (isset($data['repeat_count']) && (int)$data['repeat_count'] < 0) {
                $this->setError(Text::_('COM_JTICKETING_ERROR_REPEAT_COUNT_POSITIVE'));
                return false;
            }
        } else {
            $this->setError(Text::_('COM_JTICKETING_ERROR_REPEAT_COUNT_OR_UNTIL_REQUIRED'));
            return false;
        }
        $startDateTime = new DateTime($data['startdate']);
        $endTime = (new DateTime($data['enddate']))->format('H:i:s');
        $recurringType = $data['recurring_type'];
        $interval = isset($data['repeat_interval']) ? (int)$data['repeat_interval'] : 0;
        $repeatCount = isset($data['repeat_count']) ? (int)$data['repeat_count'] : 0;
        $repeatUntil = !empty($data['repeat_until']) ? (new DateTime($data['repeat_until']))->setTime(23, 59, 59) : null;
    
        $db = Factory::getDbo();
    
        // Fetch existing recurrence settings
        $existingCount = 0;
        $existingRecurrences = [];
    
        if ($isUpdate) {
            $query = $db->getQuery(true)
                ->select('r_id, start_date')
                ->from($db->quoteName('#__jticketing_recurring_events'))
                ->where($db->quoteName('event_id') . ' = ' . (int)$data['event_id'])
                ->order('start_date ASC');
            $db->setQuery($query);
            $existingRecurrences = $db->loadAssocList();
    
            $existingCount = count($existingRecurrences);
        }
    
        // Generate new recurrences based on updated start date
        $recurrences = [];
        $lastRecurrenceEndDate = null;
    
        // Loop through and create recurrences
        for ($i = 0; ($repeatCount > 0 && $i < $repeatCount) || ($repeatUntil && $startDateTime <= $repeatUntil); $i++) {
            if ($repeatUntil && $startDateTime > $repeatUntil) {
                break; 
            }
            if ($i > 0) { 
                switch ($recurringType) {
                    case 'Daily':
                        $startDateTime->modify('+' . ($interval + 1) . ' days');
                        break;
                    case 'Weekly':
                        $startDateTime->modify('+' . ($interval + 1) . ' weeks');
                        if ($repeatUntil && $startDateTime > $repeatUntil) {
                            break 2; 
                        }
                        break;
                    case 'Monthly':
                        $startDateTime->modify('+' . ($interval + 1) . ' months');
                        if ($repeatUntil && $startDateTime > $repeatUntil) {
                            break 2; 
                        }
                        break;
                    case 'Yearly':
                        $startDateTime->modify('+' . ($interval + 1) . ' years');
                        if ($repeatUntil && $startDateTime > $repeatUntil) {
                            break 2; 
                        }
                        break;
                }
            }
            if ($repeatUntil && $startDateTime >= $repeatUntil) {
                break; 
            }
            $recurrences[] = [
                'event_id'   => $data['event_id'],
                'start_date' => $startDateTime->format('Y-m-d'),
                'start_time' => $startDateTime->format('H:i:s'),
                'end_date'   => $startDateTime->format('Y-m-d'),
                'end_time'   => $endTime,
            ];

            $lastRecurrenceEndDate = clone $startDateTime;
        }
        $newCount = count($recurrences);
        for ($i = 0; $i < max($existingCount, $newCount); $i++) {
            if ($i < $existingCount && $i < $newCount) {
                // Update existing recurrence
                $query = $db->getQuery(true)
                    ->update($db->quoteName('#__jticketing_recurring_events'))
                    ->set([
                        $db->quoteName('start_date') . ' = ' . $db->quote($recurrences[$i]['start_date']),
                        $db->quoteName('start_time') . ' = ' . $db->quote($recurrences[$i]['start_time']),
                        $db->quoteName('end_date') . ' = ' . $db->quote($recurrences[$i]['end_date']),
                        $db->quoteName('end_time') . ' = ' . $db->quote($recurrences[$i]['end_time']),
                    ])
                    ->where($db->quoteName('r_id') . ' = ' . (int)$existingRecurrences[$i]['r_id']);
                $db->setQuery($query);
                $db->execute();
            } elseif ($i >= $existingCount) {
                // Insert new recurrence
                $columns = ['event_id', 'start_date', 'start_time', 'end_date', 'end_time'];
                $values = [
                    $db->quote($recurrences[$i]['event_id']),
                    $db->quote($recurrences[$i]['start_date']),
                    $db->quote($recurrences[$i]['start_time']),
                    $db->quote($recurrences[$i]['end_date']),
                    $db->quote($recurrences[$i]['end_time']),
                ];
                $query = $db->getQuery(true)
                    ->insert($db->quoteName('#__jticketing_recurring_events'))
                    ->columns($db->quoteName($columns))
                    ->values(implode(',', $values));
                $db->setQuery($query);
                $db->execute();
            } elseif ($i >= $newCount) {
                // Delete excess recurrence
                $query = $db->getQuery(true)
                    ->delete($db->quoteName('#__jticketing_recurring_events'))
                    ->where($db->quoteName('r_id') . ' = ' . (int)$existingRecurrences[$i]['r_id']);
                $db->setQuery($query);
                $db->execute();
            }
        }
    
        // Update the events table with the last recurrence end datetime
        if ($lastRecurrenceEndDate) {
            $eventsTable = Table::getInstance('Event', 'JticketingTable');
            if ($eventsTable->load($data['event_id'])) {
                $eventsTable->enddate = $lastRecurrenceEndDate->format('Y-m-d') . ' ' . $endTime;
                if (!$eventsTable->store()) {
                    Factory::getApplication()->enqueueMessage(Text::_('COM_JTICKETING_ERROR_UPDATE_EVENT_END_DATE') . implode(', ', $eventsTable->getErrors()), 'error');                    return false;
                }
            } else {
                Factory::getApplication()->enqueueMessage(Text::_('COM_JTICKETING_NO_RECURRING_EVENTS_FOUND'), 'error');
                return false;
            }
        }
    
        return true;
    }
    /**
     * Method to retrieve all recurring event IDs (r_id) for a given event ID.
     *
     * @param int $eventId The ID of the main event.
     * @return array An array of recurring event IDs (r_id).
     */
    public function getAllRIdsByEventId($eventId)
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true)
                    ->select($db->quoteName('r_id'))
                    ->from($db->quoteName('#__jticketing_recurring_events'))
                    ->where($db->quoteName('event_id') . ' = ' . $db->quote($eventId));
        $db->setQuery($query);

        return $db->loadColumn(); 
    }

    /**
     * Saves recurring event attendees by linking the attendee ID to all recurring event IDs 
     * associated with the given event details ID.
     *
     * @param int $attendeeId The attendee's ID.
     * @param int $eventDetailsId The event details ID.
     * @return bool True on success, false if no event ID is found.
     */

    public function saveRecurringEventAttendees($attendeeId, $eventDetailsId)
    {
        // Get the event ID from #__jticketing_integration_xref
        $db = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select($db->quoteName('eventid'))
            ->from($db->quoteName('#__jticketing_integration_xref'))
            ->where($db->quoteName('id') . ' = ' . (int) $eventDetailsId);
        $db->setQuery($query);
        $eventId = $db->loadResult();

        if (!$eventId) {
            return false; // No event ID found
        }

        // Retrieve all r_id values for the given event_id
        $rIds = $this->getAllRIdsByEventId($eventId);

        if (!empty($rIds)) {
            foreach ($rIds as $rId) {
                $columns = ['attendee_id', 'r_id'];
                $values = [$db->quote($attendeeId), $db->quote($rId)];

                $query->clear()
                    ->insert($db->quoteName('#__jticketing_recurring_event_attendees'))
                    ->columns($db->quoteName($columns))
                    ->values(implode(',', $values));

                $db->setQuery($query);
                $db->execute();
            }
        }

        return true;
    }

    /**
     * Fetch recurring events for a specific attendee
     *
     * @param int $attendeeId
     * @return array
     */
    public function getRecurringEvents($attendee_id)
    {
        // Get the database object
        $db = Factory::getDbo();

        // Define the query to fetch recurring event data, check-in status, attendee status, field values, and check-in time
        $query = $db->getQuery(true)
            ->select('re.r_id, re.start_date, re.start_time, re.end_date, re.end_time, 
                    cd.checkin, cd.checkintime, att.status, 
                    fv1.field_value AS first_name, fv2.field_value AS last_name')
            ->from('#__jticketing_recurring_events AS re')
            ->join('INNER', '#__jticketing_recurring_event_attendees AS rea ON rea.r_id = re.r_id')
            ->join('LEFT', '#__jticketing_checkindetails AS cd ON cd.attendee_id = rea.attendee_id AND cd.r_id = re.r_id')
            ->join('LEFT', '#__jticketing_attendees AS att ON att.id = rea.attendee_id')
            ->join('LEFT', '#__jticketing_attendee_field_values AS fv1 ON fv1.attendee_id = rea.attendee_id AND fv1.field_id = 1')
            ->join('LEFT', '#__jticketing_attendee_field_values AS fv2 ON fv2.attendee_id = rea.attendee_id AND fv2.field_id = 2')
            ->where('rea.attendee_id = ' . (int) $attendee_id);

        // Execute the query and get results
        $db->setQuery($query);
        $result = $db->loadObjectList();

        return $result ? $result : [];
    }   
}