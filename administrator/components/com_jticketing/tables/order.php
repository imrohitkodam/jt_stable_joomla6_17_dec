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
use Joomla\CMS\Factory;

/**
 * question Table class
 *
 * @since  1.5
 */
class JticketingTableOrder extends Table
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
		parent::__construct('#__jticketing_order', 'id', $db);
	}

	/**
	 * Overloaded store method for the order table.
	 *
	 * @param   boolean  $updateNulls  Toggle whether null values should be updated.
	 *
	 * @return  boolean  True on success, false on failure.
	 *
	 * @since   2.5.0
	 */
	public function store($updateNulls = false)
	{
		$date = Factory::getDate()->toSql();
		$this->mdate = $date;

		if (empty($this->id))
		{
			$this->cdate = $date;
		}

		// Attempt to store the data.
		return parent::store($updateNulls);
	}
}
