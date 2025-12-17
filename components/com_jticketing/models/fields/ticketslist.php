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
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

/**
 * Supports an HTML select list of courses
 *
 * @since  2.1.0
 */
class JFormFieldTicketsList extends ListField
{
	/**
	 * The form field type.
	 *
	 * @var   string
	 * @since 2.1
	 */
	protected $type = 'ticketslist';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return array An array of JHtml options.
	 *
	 * @since   2.1
	 */
	protected function getOptions()
	{
		$user = Factory::getUser();
		$options       = array();

		// If user login then show option
		if ($user)
		{
			// Get all tickets options
			$ticketslist = JT::model('attendees')->getAttendees(array('owner_id' => $user->id));
		}

		$options[] = HTMLHelper::_('select.option', '', Text::_('COM_JTICKETING_SEL_TICKET'));

		if (!empty($ticketslist))
		{
			foreach ($ticketslist as $key => $ticket)
			{
				$id       = htmlspecialchars($ticket->id);
				$ticketId     = htmlspecialchars($ticket->enrollment_id);
				$options[] = HTMLHelper::_('select.option', $id, $ticketId);
			}
		}

		return $options;
	}
}
