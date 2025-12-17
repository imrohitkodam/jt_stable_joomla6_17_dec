<?php
/**
 * @package     JLike
 * @subpackage  com_jlike
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access
defined('_JEXEC') or die;
use Joomla\Data\DataObject;
use Joomla\CMS\Table\Table;

/**
 * like Table class
 *
 * @since  1.6
 */
class TableJLike_Likes extends Table
{
	protected $id = null;

	/**
	 * Constructor
	 *
	 * @param   DataObjectbase  &$db  A database connector object
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__jlike_likes', 'id', $db);

		// Hack to make this act a little like active record
		$this->_db->setQuery('SHOW columns FROM' . $this->_tbl);

		foreach ($this->_db->loadObjectList() as $k => $column)
		{
			$field = $column->Field;
			$this->$field = '';
		}
	}

	/**
	 * bind
	 *
	 * @param   string  $from  from
	 *
	 * @return  null|string  null is operation was satisfactory, otherwise returns an error
	 */
	public function bind($from)
	{
		$from = json_decode($from);
		parent::bind($from);
	}

	/**
	 * store
	 *
	 * @param   boolean  $updateNulls  updateNulls
	 *
	 * @return  boolean
	 */
	public function store($updateNulls = false)
	{
		foreach (get_object_vars($this) as $k => $v)
		{
			if (is_array($v) or is_object($v) or $k[0] == '_')
			{
				// Internal or NA field
				continue;
			}

			$set [] = $this->_db->nameQuote($k) . '=' . $this->_db->Quote($v);
		}

		$sql = 'REPLACE INTO ' . $this->_tbl . ' SET ' . implode(',', $set);

		$this->_db->setQuery($sql);

		if ($this->_db->execute())
		{
			return true;
		}
		else
		{
			$this->setError(get_class($this) . '::store failed - ' . $this->_db->getErrorMsg());

			return false;
		}
	}
}
