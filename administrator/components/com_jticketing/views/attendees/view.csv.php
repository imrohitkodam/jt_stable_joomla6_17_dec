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
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;

// Import CSV library view
jimport('techjoomla.view.csv');

/**
 * View for events
 *
 * @package     JTicketing
 * @subpackage  component
 * @since       1.0
 */
class JticketingViewAttendees extends TjExportCsv
{
	public $data = array();

	public $recordCnt = 0;

	public $fileName;

	/**
	 * Display view
	 *
	 * @param   STRING  $tpl  template name
	 *
	 * @return  Object|Boolean in case of success instance and failure - boolean
	 *
	 * @since   1.0
	 */
	public function display($tpl = null)
	{
		$app   = Factory::getApplication();
		$input = $app->input;
		$user  = Factory::getUser();
		$userAuthorisedExport = $user->authorise('core.create', 'com_jticketing');
		$this->fileName = preg_replace('/\s+/', '', Text::_('COM_JTICKETING') . '_' . Text::_('COM_JTICKETING_ATTENDEES_LIST'));

		if ($userAuthorisedExport !== true || !$user->id)
		{
			// Redirect to the list screen.
			$redirect = Route::_('index.php?option=com_jticketing&view=attendees', false);

			$app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'error');
			$app->redirect($redirect);
			
			return false;
		}

		if ($input->get('task') == 'download')
		{
			$fileName = $input->get('file_name');
			$this->download($fileName);
			Factory::getApplication()->close();

			return true;
		}

		$this->items     = $this->get('Items');
		$this->recordCnt = count($this->items);
		$this->data      = $this->items;

		$hideColumns = array('fname', 'lname', 'firstname', 'lastname', 'name', 'email',
			'attendee_email', 'coupon_code', 'customer_note', 'order_status', 'owner_id',
			'event_id', 'id', 'cdate', 'ticketcount', 'title', 'ticket_type_title'
		);

		foreach ($this->items as $key => $item)
		{
			$this->data[$key]                = new stdClass;
			$this->data[$key]->order_id = $item->order_id;
			$this->data[$key]->enrollment_id = $item->enrollment_id;

			// Attendee Name formatting
			if (!empty($item->fname))
			{
				$this->data[$key]->attendeeName = $this->escape(ucfirst($item->fname) . ' ' . ucfirst($item->lname));
			}
			elseif (!empty($item->firstname))
			{
				$this->data[$key]->attendeeName = $this->escape($item->firstname . ' ' . $item->lastname);
			}
			else
			{
				$this->data[$key]->attendeeName = $this->escape($item->name);
			}

			$this->data[$key]->username = $this->escape($item->username);

			// Attendee email formatting
			$this->data[$key]->email = (!empty($item->attendee_email)) ? $this->escape($item->attendee_email) : $this->escape($item->owner_email);

			$this->data[$key]->eventTitle      = $this->escape($item->title);
			$this->data[$key]->ticketTypeTitle = $this->escape($item->ticket_type_title);
			$this->data[$key]->status          = $item->status;
			$this->data[$key]->checkintime     = $item->checkintime;

			$this->data[$key]->checkin = empty($item->checkin) ?
			Text::_('COM_JTICKETING_ATTENDEE_VIEW_CSV_EXPORT_STATUS_NOT_ATTENDED') :
			Text::_('COM_JTICKETING_ATTENDEE_VIEW_CSV_EXPORT_STATUS_ATTENDED');

			$objectKeys = array_keys((array) $item);

			foreach ($objectKeys as $objectKey)
			{
				if (!in_array($objectKey, $hideColumns) && !isset($this->data[$key]->$objectKey))
				{
					$this->data[$key]->$objectKey = $this->escape($item->$objectKey);
				}
			}
		}

		parent::display();
	}
}
