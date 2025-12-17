<?php
/**
 * @package     JLike
 * @subpackage  com_jlike
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;
use Joomla\Data\DataObject;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
require_once JPATH_LIBRARIES . '/techjoomla/tjmail/mail.php';
/**
 * Methods supporting a list of Jlike records.
 *
 * @since  1.6
 */
class JlikeModelReminders extends ListModel
{
	/**
	* Constructor.
	*
	* @param   array  $config  An optional associative array of configuration settings.
	*
	* @see        JController
	* @since      1.6
	*/
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'a.id',
				'ordering', 'a.ordering',
				'state', 'a.state',
				'created_by', 'a.created_by',
				'modified_by', 'a.modified_by',
				'title', 'a.title',
				'days_before', 'a.days_before',
				'email_template', 'a.email_template',
				'subject', 'a.subject',
				'last_sent_limit', 'a.last_sent_limit',
				'content_type', 'a.content_type',
				'enable_cc', 'a.enable_cc',
			);
		}

		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   Elements order
	 * @param   string  $direction  Order direction
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	protected function populateState($ordering = 'a.title', $direction = 'asc')
	{
		// Initialise variables.
		$app = Factory::getApplication('administrator');

		// Load the filter state.
		$search = $app->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$published = $app->getUserStateFromRequest($this->context . '.filter.state', 'filter_published', '', 'string');
		$this->setState('filter.state', $published);

		// Filtering content_type
		$this->setState('filter.content_type', $app->getUserStateFromRequest($this->context . '.filter.content_type', 'filter_content_type', '', 'string'));

		// Load the parameters.
		$params = ComponentHelper::getParams('com_jlike');
		$this->setState('params', $params);

		// List state information.
		parent::populateState($ordering, $direction);
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param   string  $id  A prefix for the store id.
	 *
	 * @return   string A store id.
	 *
	 * @since    1.6
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.state');

		return parent::getStoreId($id);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return   DataObjectbaseQuery
	 *
	 * @since    1.6
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select', 'DISTINCT a.*'
			)
		);
		$query->from('`#__jlike_reminders` AS a');

		// Join over the users for the checked out user
		$query->select("uc.name AS editor");
		$query->join("LEFT", "#__users AS uc ON uc.id=a.checked_out");

		// Join over the user field 'created_by'
		$query->select('`created_by`.name AS `created_by`');
		$query->join('LEFT', '#__users AS `created_by` ON `created_by`.id = a.`created_by`');

		// Join over the user field 'modified_by'
		$query->select('`modified_by`.name AS `modified_by`');
		$query->join('LEFT', '#__users AS `modified_by` ON `modified_by`.id = a.`modified_by`');

		// Join over the reminder_contentds field 'content_id'
		$query->select('GROUP_CONCAT(rc.content_id) AS `contents`');
		$query->join('LEFT', '#__jlike_reminder_contentids AS `rc` ON `a`.id = rc.`reminder_id`');
		$query->group('a.id');

		// Filter by published state
		$published = $this->getState('filter.state');

		if (is_numeric($published))
		{
			$query->where('a.state = ' . (int) $published);
		}
		elseif ($published === '')
		{
			$query->where('(a.state IN (0, 1))');
		}

		// Filter by search in title
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('a.id = ' . (int) substr($search, 3));
			}
			else
			{
				$search = $db->Quote('%' . $db->escape($search, true) . '%');
				$query->where('( a.title LIKE ' . $search . '  OR  a.days_before LIKE ' . $search . '  OR  a.content_type LIKE ' . $search . ' )');
			}
		}

		// Filter by search in title
		$content_type = $this->getState('filter.content_type');

		if (!empty($content_type))
		{
			$query->where('a.content_type = ' . $db->quote($content_type));
		}

		// Filtering content_type Add the list ordering clause.
		$orderCol  = $this->state->get('list.ordering');
		$orderDirn = $this->state->get('list.direction');

		if ($orderCol && $orderDirn)
		{
			$query->order($db->escape($orderCol . ' ' . $orderDirn));
		}

		return $query;
	}

	/**
	 * Get an array of data items
	 *
	 * @return mixed Array of data items on success, false on failure.
	 */
	public function getItems()
	{
		$items = parent::getItems();

		return $items;
	}

	/**
	 * Send Reminders to Users before due date
	 *
	 * @return Array reminder sent deatils
	 */
	public function sendReminders()
	{
		$reminder_sent_count = $sent   = 0;
		$sent_details = $all_todos = $send = $todos = array();
		$db                  = Factory::getDBO();
		$jlikeparams         = ComponentHelper::getParams('com_jlike');
		$batch_size          = $jlikeparams->get('reminder_batch_size', 1);

		// Load file to call api of the table
		Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jlike/tables');

		$jinput = Factory::getApplication()->getInput();
		$jinput->set('filter_published', 1);
		$reminders = $this->getItems();

		foreach ($reminders as $reminder)
		{
			// Date conversion to compare reminder date
			$date           = Factory::getDate();
			$reminder_date  = new Date($date . "+" . $reminder->days_before . " days");
			$reminder_date  = $reminder_date->format('Y-m-d');
			// Check for `com_jticketing.event` content type
			if ($reminder->content_type === 'com_jticketing.event') 
			{
				$integration = JT::getIntegration(true);
				if($integration == 2)
				{
					$todo=$db->getQuery(true);
					$todo->select('t.id')
						->from($db->quoteName('#__jlike_todos').'As t')
						->where('t.content_id = '.$db->quote($reminder->contents));
					$db->setQuery($todo);
					$todoId=$db->loadResult();
				
					$content_query = $db->getQuery(true);
					$content_query->select('d.element_id')
						->from($db->quoteName('#__jlike_content') . ' AS d')
						->where('d.id = ' . $db->quote($reminder->contents));
				 
					$db->setQuery($content_query);
					$elementId = $db->loadResult();

					$eventDetails = JT::event($elementId); 
					
					$recurringEvents = JT::event()->getRecurringEventsByEventDetails($eventDetails);

					if (!empty($recurringEvents)) 
					{
						foreach ($recurringEvents as $recurring_event) 
						{
							$config = Factory::getConfig();
							$timezone = $config->get('offset', 'UTC');  
				
							$recurringStartDate = Factory::getDate($recurring_event->start_date, 'UTC');
							$recurringStartDate = $recurringStartDate->setTimezone(new DateTimeZone($timezone))->format('Y-m-d');
							$recurringReminderDate = (new DateTime($recurringStartDate))->modify('-' . $reminder->days_before . ' days')->format('Y-m-d');
								
							if ($recurringReminderDate === $reminder_date) 
							{
								$current_r_id = $recurring_event->r_id;
					
								$attendeeQuery = $db->getQuery(true)
									->select('attendee_id')
									->from($db->quoteName('#__jticketing_recurring_event_attendees'))
									->where('r_id = ' . $db->quote($current_r_id));
					
								$db->setQuery($attendeeQuery);
								$attendeeIds = $db->loadColumn();
					
								$this->sendJTReminderEmailsToAttendees($db, $attendeeIds, $reminder,$recurring_event->r_id,$todoId);
							}
						}
					} 
					else 
					{
						// Proceed with fetching xrefId and attendeeIds
						$xrefQuery = $db->getQuery(true)
							->select('id')
							->from($db->quoteName('#__jticketing_integration_xref'))
							->where('eventid = ' . $db->quote($eventDetails->eventid));

						$db->setQuery($xrefQuery);
						$xrefId = $db->loadResult();

						if ($xrefId) 
						{
							$attendeeQuery = $db->getQuery(true)
								->select('id')
								->from($db->quoteName('#__jticketing_attendees'))
								->where('event_id = ' . $db->quote($xrefId));

							$db->setQuery($attendeeQuery);
							$attendeeIds = $db->loadColumn();

							// Call sendJTReminderEmailsToAttendees only if the combination does not exist
							$this->sendJTReminderEmailsToAttendees($db, $attendeeIds, $reminder, null, $todoId);
						} 
						else 
						{
							echo Text::sprintf('COM_JTICKETING_NO_XREF_FOUND', $event_id) . "<br>";
						}
					}
				}

			}
			// For the general type of reminder get todos excluding content_ids of the other reminders with the same content_type
			$ltquery = $db->getQuery(true);
			$ltquery->select('distinct c.content_id');
			$ltquery->from($db->quoteName('#__jlike_reminder_contentids') . 'as c');
			$ltquery->join('LEFT', $db->quoteName('#__jlike_reminders') . 'as d on c.reminder_id=d.id');
			$ltquery->where('d.state = 1');

			if (empty($reminder->contents))
			{
				$ltquery->where('d.content_type = ' . $db->quote($reminder->content_type));
			}

			$query = $db->getQuery(true);
			$query->select(
			$db->quoteName(array('c.id', 'c.content_id', 'c.assigned_to', 'c.assigned_by', 'c.due_date', 'd.url', 'd.title', 'd.element', 'd.element_id'))
			);

			// Attach reminder_id in the todos with the help of select query
			$query->select($reminder->id . ' as reminder_id');
			$query->from($db->quoteName('#__jlike_todos') . 'as c');
			$query->join('LEFT', $db->quoteName('#__jlike_content') . 'as d on c.content_id=d.id');
			$query->join('LEFT', $db->quoteName('#__users') . 'as u on u.id=c.assigned_to');

			if (empty($reminder->contents))
			{
				$query->where('c.content_id not in (' . $ltquery . ')');
			}
			else
			{
				$query->where('c.content_id in (' . $reminder->contents . ')');
			}

			$query->where('d.element = ' . $db->quote($reminder->content_type));
			$query->where('date(c.due_date) = ' . $db->quote($reminder_date));
			$query->where('c.status != ' . $db->quote('C'));

			// Dont sent reminder if the user is blocked
			$query->where('u.block != 1');

			//  Dont sent reminder if already sent reminder previously
			$query->where('NOT EXISTS (select todo_id from ' . $db->quoteName('#__jlike_reminder_sent') . '
				 as rs where c.id = rs.todo_id and reminder_id = ' . $db->quote($reminder->id) . ')');
			$db->setQuery($query);
			$todo_s = $db->loadObjectList();
			$todos = array_merge($todos, $todo_s);
		}

		// Shuffle todos and apply the batch size
		shuffle($todos);
		$todos = array_slice($todos, 0, $batch_size);

		echo Text::_('COM_JLIKE_REMINDERSENT_DETAILS');

		// Add details in the logger file
		Log::addLogger(array(
				// Sets file name
				'text_file' => 'com_jlike.sentreminders.log'
				),Log::INFO,array('com_jlike'));

			if (!empty($todos))
			{
				foreach ($todos as $todo)
				{
					$user           = Factory::getUser($todo->assigned_to);

					// First parameter file name and second parameter is prefix
					$reminder_table = Table::getInstance('reminder', 'JlikeTable', array('dbo', $db));

					// Get jlike_remider_sent for per reminder Check if already reminder sent to the User
					$reminder_table->load(array('id' => (int) $todo->reminder_id));

					// Get content type
					$content_type = explode(".", $todo->element);

					// Tigger to Check content follows all criteria to send the reminder
					PluginHelper::importPlugin('content');
					$send       = Factory::getApplication()->triggerEvent('onAfterJlike' . $content_type[1] . 'ContentCheckforReminder', array(
																$todo->assigned_to,
																$todo->element_id
															)
							);

						// Content is published and not Completed yet
						if (empty($send) || (!empty($send) && $send[0] == 1))
						{
							// Calculate reminder date with the help of current date
							$date           = Factory::getDate();

							// First parameter file name and second parameter is prefix
							$table = Table::getInstance('Remindersent', 'JlikeTable', array('dbo', $db));

							// Get all jlike_remider_sent for per reminder Check if already reminder sent to the User
							$table->load(array('todo_id' => (int) $todo->id, 'reminder_id' => (int) $todo->reminder_id));
							$recipient     = $user->email;
							$subject       = $reminder_table->subject;
							$body          = $reminder_table->email_template;
							$due_date      = HTMLHelper::date($todo->due_date, Text::_('COM_JLIKE_REMINDER_DATE_FORMAT'));

							// Store values of tags in the array
							$this->course_reminder_mail = array();
							$this->course_reminder_mail['content_due_date'] = $due_date;
							$this->course_reminder_mail['username']         = $user->username;
							$this->course_reminder_mail['name']             = $user->name;
							$content_url                                    = Route::_(Uri::base() . $todo->url);
							$this->course_reminder_mail['content_url']      = $content_url;
							$this->course_reminder_mail['content_link']     = '<a href="' . $content_url . '">' . $todo->title . '</a>';
							$this->course_reminder_mail['content_title']    = $todo->title;
							$this->course_reminder_mail['days_before']      = $reminder_table->days_before;

							// Replace email body tags
							$body               = TjMail::TagReplace($body, $this->course_reminder_mail);

							// Replace email subject tags
							$subject            = TjMail::TagReplace($subject, $this->course_reminder_mail);

							$config = Factory::getConfig();

							$cc = !empty($reminder_table->cc) ? explode(',', $reminder_table->cc) : null;

							// If from mail is not configured in the reminder then take from Joomla config
							$from = !empty($reminder_table->mailfrom) ? $reminder_table->mailfrom : $config->get('mailfrom');

							// If from name is not configured in the reminder then take from Joomla config
							$fromName = !empty($reminder_table->fromname)?$reminder_table->fromname: $config->get('fromname');
							$replyTo = !empty($reminder_table->replyto) ? $reminder_table->replyto : null;
							$replyToName = !empty($reminder_table->replytoname) ? $reminder_table->replytoname : null;

							$result = Factory::getMailer()->sendMail($from, $fromName, $recipient, $subject, $body, true, $cc, null, null, $replyTo, $replyToName);

							if ($result)
							{
								// Update table in the jlike_reminder_logs  with the sent_on as current_date and time
								$table->reminder_id = $reminder_table->id;
								$table->todo_id     = $todo->id;
								$table->sent_on     = $date->toSql();
								$table->store();
								$sent = 1;
								$reason = Text::_('COM_JLIKE_REMINDERS_SENT_REASON');
								echo Text::sprintf('COM_JLIKE_REMINDERS_SENT', $user->username, $reminder_table->title, $reminder_table->days_before, $todo->title);
								$reminder_sent_count++;
							}
							else
							{
								// Reminder Mail not sent
								$reason = Text::_('COM_JLIKE_REMINDERS_NOT_SENT_REASON');
							}
						}
						else
						{
							// Content,content_catgory not published or content Completed
							$reason = Text::_('COM_JLIKE_REMINDERS_NOT_SENT_CONTENT_REASON');
						}

						$log = array('username' => $user->username,
									'days_before' => $reminder_table->days_before,
									'contenttitle' => $todo->title,
									'reminder_title' => $reminder_table->title,
									'reason' => $reason,
									'sent' => $sent
									);
						Log::add(json_encode($log), Log::INFO, 'com_jlike');
				}
			}

			if ($reminder_sent_count)
			{
				// Display sent reminders count
				echo Text::sprintf('COM_JLIKE_REMINDERS_SENT_COUNT', $reminder_sent_count);
			}
			else
			{
				echo Text::_('COM_JLIKE_NO_REMINDERS_TO_SENT');
			}

		return;
	}
	/**
	 * Sends reminder emails to attendees and logs them in the database.  
	 * Ensures no duplicate emails are sent for the same todo_id, reminder_id, and r_id.
	 */
	public function sendJTReminderEmailsToAttendees($db, $attendeeIds, $reminder, $currentRId, $todoId)
	{
		// Check if there are any attendee IDs
		if (!empty($attendeeIds)) {
			// Query to get the email of attendees based on their IDs
			$attendeesQuery = $db->getQuery(true)
				->select('a.attendee_id, a.field_value AS email,f1.field_value AS first_name, f2.field_value AS last_name')
				->from($db->quoteName('#__jticketing_attendee_field_values') . ' AS a')
				->leftJoin($db->quoteName('#__jticketing_attendee_field_values') . ' AS f1 ON f1.attendee_id = a.attendee_id AND f1.field_id = 1')  // First Name field
    			->leftJoin($db->quoteName('#__jticketing_attendee_field_values') . ' AS f2 ON f2.attendee_id = a.attendee_id AND f2.field_id = 2')  // Last Name field
				->where('a.attendee_id IN (' . implode(',', $db->quote($attendeeIds)) . ')')
				->where('a.field_id = 4') 
				->where($db->quoteName('a.field_source') . ' = ' . $db->quote('com_jticketing'));

			$db->setQuery($attendeesQuery);
			$attendees = $db->loadObjectList();

			// Check if we found any attendees
			if (!empty($attendees)) {
				foreach ($attendees as $attendee) {
					// Check if this reminder has already been sent for the current todo_id and r_id
					$checkSentQuery = $db->getQuery(true)
						->select('id')
						->from($db->quoteName('#__jlike_reminder_sent'))
						->where($db->quoteName('todo_id') . ' = ' . $db->quote($todoId))
						->where('DATE(' . $db->quoteName('sent_on') . ') = DATE(NOW())')
						->where($db->quoteName('attendee_id').' = '.$db->quote($attendee->attendee_id));

						if($currentRId)
						{
							$checkSentQuery->where($db->quoteName('r_id') . ' = ' . $db->quote($currentRId));
						}
						else{
							$checkSentQuery->where($db->quoteName('r_id') . ' IS NULL');

						}

					$db->setQuery($checkSentQuery);
					$isAlreadySent = $db->loadResult();

					// Skip sending email if it has already been sent
					if ($isAlreadySent) {
						continue; 
					}

					$recipient = $attendee->email;
					$subject = $reminder->subject;
					$body = $reminder->email_template;

					 // Fetch todo details (content_url and content_link)
					 $todoQuery = $db->getQuery(true)
					 ->select('*')
					 ->from($db->quoteName('#__jlike_todos'))
					 ->where($db->quoteName('id') . ' = ' . $db->quote($todoId));
 
					$db->setQuery($todoQuery);
					$todo = $db->loadObject();
					$fullName = $attendee->first_name . ' ' . $attendee->last_name;
	
					$body = str_replace('{content_title}', $reminder->title, $body);
					$body = str_replace('{username}', $attendee->email, $body);
					$body = str_replace('{name}', $fullName, $body);
					$body = str_replace('{content_link}', '<a href="' . Route::_(Uri::base() . $todo->url) . '">' . $todo->title . '</a>', $body);
					$body = str_replace('{content_url}', Route::_(Uri::base() . $todo->url), $body);
					$body = str_replace('{content_due_date}', HTMLHelper::date($todo->due_date, Text::_('COM_JLIKE_REMINDER_DATE_FORMAT')), $body);
					$body = str_replace('{days_before}', $reminder->days_before, $body);
 
					// Get the config settings for the sender's email
					$config = Factory::getConfig();
					$from = $config->get('mailfrom');
					$fromName = $config->get('fromname');

					// Send the email
					$result = Factory::getMailer()->sendMail($from, $fromName, $recipient, $subject, $body, true);

					// Check if the email was sent successfully
					if ($result) {
						// Insert a record into the `#__jlike_reminder_sent` table
						$insertQuery = $db->getQuery(true)
							->insert($db->quoteName('#__jlike_reminder_sent'))
							->columns(array($db->quoteName('todo_id'), $db->quoteName('reminder_id'), $db->quoteName('sent_on'), $db->quoteName('r_id'),$db->quoteName('attendee_id')))
							->values(
								$db->quote($todoId) . ', ' .
								$db->quote($reminder->id) . ', ' .
								$db->quote(Factory::getDate()->toSql()) . ', ' .
								($currentRId !== null ? $db->quote($currentRId) : 'NULL').', '.
								$db->quote($attendee->attendee_id)
							);

						$db->setQuery($insertQuery);
						$db->execute();

						if ($result) {
							echo Text::_('COM_JLIKE_REMINDERSENT_DETAILS');
							echo Text::sprintf('COM_JLIKE_REMINDERS_SENT',$attendee->email,$reminder->title,$reminder->days_before,$reminder->email_template);
						} 
					}
				}
			}
		}
	}
}
