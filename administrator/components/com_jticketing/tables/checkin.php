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

/**
 * Featured Table class.
 *
 * @since  2.0
 */
class JTicketingTableCheckin extends Table
{
	/**
	 * Constructor
	 *
	 * @param   JDatabaseDriver  &$db  Database connector object
	 *
	 * @since   2.0
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__jticketing_checkindetails', 'id', $db);
	}

	/**
	 * Overloaded store method for the checkin table.
	 *
	 * @param   boolean  $updateNulls  Toggle whether null values should be updated.
	 *
	 * @return  boolean  True on success, false on failure.
	 *
	 * @since   2.5.0
	 */
	public function store($updateNulls = false)
	{
		if (empty($this->checkintime))
		{
			$this->checkintime = null;
		}

		if (empty($this->checkouttime))
		{
			$this->checkouttime = null;
		}

		// Attempt to store the data.
		return parent::store(true);
	}
}
