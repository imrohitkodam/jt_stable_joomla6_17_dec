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
defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Form\Field\ListField;

/**
 * Supports an HTML select list of courses
 *
 * @since  2.1.0
 */
class JFormFieldEventsList extends ListField
{
	/**
	 * The form field type.
	 *
	 * @var   string
	 * @since 2.1
	 */
	protected $type = 'eventslist';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return array An array of JHtml options.
	 *
	 * @since   2.1
	 */
	protected function getOptions()
	{
		$user          = Factory::getUser();
		$options       = array();
		$jtEventHelper = new JteventHelper;
		$published     = $this->element['published'] ? explode(',', (string) $this->element['published']) : array(1);

		$superUser    = $user->authorise('core.admin');
		$canEnrollAll = $user->authorise('core.enrollall', 'com_jticketing');
		$canEnrollOwn = $user->authorise('core.enrollown', 'com_jticketing');
		$comParams    = JT::config();
		$utilities    = JT::utilities();

		// If admin get all events else get events by creator
		$eventsModel = JT::model('events', array('ignore_request' => true));
		$eventsModel->setState('filter.state', $published);

		if ($superUser)
		{
			// Get all events options, Param false to return option not all events
			$eventList = $eventsModel->getItems();
		}
		else
		{
			// Get all events options, Param false to return option not all events

			if ($canEnrollAll)
			{
				$eventList = $eventsModel->getItems();
			}
			elseif ($canEnrollOwn)
			{
				$eventsModel->setState('filter_creator', (int) $user->id);
				$eventList = $eventsModel->getItems();
			}
		}

		$options[] = HTMLHelper::_('select.option', '', Text::_('SELONE_EVENT'));

		if (!empty($eventList))
		{
			foreach ($eventList as $key => $event)
			{
				$eventobj   = JT::event($event->id);
				$eventId    = (int) $eventobj->integrationId;
				$eventName  = htmlspecialchars($eventobj->getTitle());

				if ($comParams->get('enable_eventstartdateinname') && $eventobj->getStartDate() !== null)
				{
					$startDate   = $utilities->getFormatedDate($eventobj->getStartDate());
					$eventName   = $eventName . '(' . $startDate . ')';
				}

				$options[]  = HTMLHelper::_('select.option', $eventId, $eventName);
			}
		}

		return $options;
	}
}
