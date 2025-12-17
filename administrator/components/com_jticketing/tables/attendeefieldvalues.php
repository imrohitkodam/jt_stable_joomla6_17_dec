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
use Joomla\CMS\Table\Table;

/**
 * question Table class
 *
 * @since  1.5
 */
class JticketingTableAttendeeFieldValues extends Table
{
	/**
	 * Constructor
	 *
	 * @param   JDatabaseDriver  &$db  Database object
	 *
	 * @since  1.5
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__jticketing_attendee_field_values', 'id', $db);
	}
}
