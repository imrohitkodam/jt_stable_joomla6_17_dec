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
 * Hello Table class
 *
 * @since  0.0.1
 */
class JTicketingTableAttendeefields extends Table
{
	/**
	 * Constructor
	 *
	 * @param   JDatabaseDriver  &$db  A database connector object
	 */
	public function __construct(&$db)
	{
		$this->setColumnAlias('published', 'state');
		parent::__construct('#__jticketing_attendee_fields', 'id', $db);
	}

	/**
	 * Overloaded check function
	 *
	 * @return bool
	 */
	public function check()
	{
		// If there is an ordering column and this is a new row then get the next ordering value
		if (property_exists($this, 'ordering') && $this->id == 0)
		{
			$this->ordering = self::getNextOrder();
		}

		return parent::check();
	}

	/**
	 * Define a namespaced asset name for inclusion in the #__assets table
	 *
	 * @return string The asset name
	 *
	 * @see Table::_getAssetName
	 */
	protected function _getAssetName()
	{
		$k = $this->_tbl_key;

		return 'com_jticketing.attendeefields.' . (int) $this->$k;
	}

	/**
	 * Delete a record by id
	 *
	 * @param   mixed  $pk  Primary key value to delete. Optional
	 *
	 * @return bool
	 */
	public function delete($pk = null)
	{
		$this->load($pk);
		$result = parent::delete($pk);

		return $result;
	}
}
