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
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Form\Field\ListField;

FormHelper::loadFieldClass('list');

/**
 * Supports an HTML select list of events
 *
 * @since  2.1
 */
class JFormFieldEvents extends ListField
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	2.1
	 */
	protected $type = 'events';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return	array		An array of JHtml options.
	 *
	 * @since   2.1
	 */
	protected function getOptions()
	{
		$user          = Factory::getUser();
		$jtEventHelper = new JteventHelper;
		$options       = array();
		$superUser     = $user->authorise('core.admin');
		$canEnrollAll  = $user->authorise('core.enrollall', 'com_jticketing');
		$canEnrollOwn  = $user->authorise('core.enrollown', 'com_jticketing');
		$allEvents     = new stdClass;
		$comParams    = JT::config();
		$utilities    = JT::utilities();

		// If admin get all events else get events by creator
		$eventsModel = JT::model('events', array('ignore_request' => true));

		if ($superUser)
		{
			// Get all events options, Param false to return option not all events
			$allEvents = $eventsModel->getItems();
		}
		else
		{
			// Get all events options, Param false to return option not all events
			if ($canEnrollAll)
			{
				$allEvents = $eventsModel->getItems();
			}
			elseif ($canEnrollOwn)
			{
				$eventsModel->setState('filter_creator', $user->id);
				$allEvents = $eventsModel->getItems();
			}
		}

		foreach ($allEvents as $event)
		{
			$eventName  = htmlspecialchars($event->title);

			if ($comParams->get('enable_eventstartdateinname'))
			{
				$startDate   = $utilities->getFormatedDate($event->startdate);
				$eventName   = $eventName . '(' . $startDate . ')';
			}

			$options[] = HTMLHelper::_('select.option', $event->id, $eventName);
		}

		return $options;
	}

	/**
	 * Method to get a list of options for a list input externally and not from xml.
	 *
	 * @return	array		An array of JHtml options.
	 *
	 * @since   2.1
	 */
	public function getOptionsExternally()
	{
		return $this->getOptions();
	}
}
