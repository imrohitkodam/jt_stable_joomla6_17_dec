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
use Joomla\CMS\Table\Table;
use Joomla\CMS\Language\Text;

/**
 * Hello Table class
 *
 * @since  0.0.1
 */
class JTicketingTableTickettypes extends Table
{
	/**
	 * Constructor
	 *
	 * @param   JDatabaseDriver  &$db  A database connector object
	 */
	public function __construct(&$db)
	{
		$this->setColumnAlias('published', 'state');
		parent::__construct('#__jticketing_types', 'id', $db);
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

		if ($this->price < 0 && $this->price != 0)
		{
			Factory::getApplication()->enqueueMessage(Text::_('COM_JTICKETING_TICKET_PRICE_NEGATIVE_ERROR'), 'error');

			return false;
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

		return 'com_jticketing.tickettypes.' . (int) $this->$k;
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
		if (empty($this->ticket_startdate) || $this->ticket_startdate == '0000-00-00 00:00:00')
		{
			$this->ticket_startdate = null;
		}

		if (empty($this->ticket_enddate) || $this->ticket_enddate == '0000-00-00 00:00:00')
		{
			$this->ticket_enddate = null;
		}

		// Attempt to store the data.
		return parent::store(true);
	}
}
