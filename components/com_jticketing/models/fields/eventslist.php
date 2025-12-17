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

use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\Factory;
use Joomla\CMS\User\User;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

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
		$superUser     = $user->authorise('core.admin');
		$comParams    = JT::config();
		$utilities    = JT::utilities();

		$eventsModel = JT::model('events', array('ignore_request' => true));

		// If admin get all events else get events by creator
		if ($superUser)
		{
			// Get all events options, Param false to return option not all events
			$eventList = $eventsModel->getItems();
		}
		else
		{
			// Get all events options, Param false to return option not all events
			$eventsModel->setState('filter_creator', $user->id);
			$eventList = $eventsModel->getItems();
		}

		if (Factory::getApplication()->getInput()->get('view') != 'couponform')
		{
			$options[] = HTMLHelper::_('select.option', '', Text::_('SELONE_EVENT'));
		}

		if (!empty($eventList))
		{
			foreach ($eventList as $key => $event)
			{
				$eventObj   = JT::event($event->id);
				$eventId    = htmlspecialchars($eventObj->integrationId);
				$eventName  = htmlspecialchars($eventObj->getTitle());

				if ($comParams->get('enable_eventstartdateinname') && null!== $eventObj->getStartDate())
				{
					$startDate   = $utilities->getFormatedDate($eventObj->getStartDate());
					$eventName   = $eventName . '(' . $startDate . ')';
				}

				$options[]  = HTMLHelper::_('select.option', $eventId, $eventName);
			}
		}

		return $options;
	}
}
