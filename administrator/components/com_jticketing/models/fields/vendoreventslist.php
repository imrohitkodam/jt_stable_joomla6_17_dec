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
use Joomla\CMS\Table\Table;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Form\Field\ListField;

/**
 * Supports an HTML select list of events
 *
 * @since  2.4.0
 */
class JFormFieldVendorEventsList extends ListField
{
	/**
	 * The form field type.
	 *
	 * @var   string
	 * @since 2.4.0
	 */
	protected $type = 'vendoreventslist';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return array An array of JHtml options.
	 *
	 * @since  2.4.0
	 */
	protected function getOptions()
	{
		// Get vendor id, in edit coupon case will return id and in new coupon case will return null
		$vendorId = (int) $this->form->getValue('vendor_id');

		// If vendor id then return user id of that vendor.
		if ($vendorId)
		{
			Table::addIncludePath(JPATH_ROOT . '/administrator/components/com_tjvendors/tables');
			$tjvendorsTablevendor = Table::getInstance('vendor', 'TjvendorsTable', array());
			$tjvendorsTablevendor->load(array('vendor_id' => (int) $vendorId));
		}

		$options       = array();
		$jtEventHelper = new JteventHelper;

		// If vendor id then return all events of that respective vendor
		if ($vendorId)
		{
			$eventsModel = JT::model('events', array('ignore_request' => true));
			$eventsModel->setState('filter_creator', (int) $tjvendorsTablevendor->user_id);
			$eventList = $eventsModel->getItems();
		}

		if (!empty($eventList))
		{
			foreach ($eventList as $key => $event)
			{
				$eventId    = (int) $event->xref_id;
				$eventName  = htmlspecialchars($event->title);
				$options[]  = HTMLHelper::_('select.option', $eventId, $eventName);
			}
		}

		return $options;
	}
}
