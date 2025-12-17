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
use Joomla\Registry\Registry;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Access\Access;
use Joomla\Utilities\ArrayHelper;

/**
 * coupon Table class
 *
 * @since  1.0
 */
class JticketingTableCoupon extends Table
{
	public $valid_to = null;


	/**
	 * Constructor.
	 *
	 * @param   JDatabase  &$db  A database connector object
	 *
	 * @since   1.6
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__jticketing_coupon', 'id', $db);

		// Set the alias since the column is called state
		$this->setColumnAlias('published', 'state');
	}

	/**
	 * Overloaded bind function to pre-process the params.
	 *
	 * @param   array   $array   Named array
	 * @param   string  $ignore  ignore
	 *
	 * @return  null|string  null is operation was satisfactory, otherwise returns an error
	 *
	 * @see  JTable:bind
	 *
	 * @since      1.5
	 */
	public function bind($array, $ignore = '')
	{
		$input = Factory::getApplication()->getInput();
		$task = $input->getString('task', '');

		if (($task == 'save' || $task == 'apply') && (!Factory::getUser()->authorise('core.edit.state', 'com_jticketing') && $array['state'] == 1))
		{
			$array['state'] = 0;
		}

		if ($array['id'] == 0 && empty($array['created_by']))
		{
			$array['created_by'] = Factory::getUser()->id;
		}

		if (isset($array['params']) && is_array($array['params']))
		{
			$registry = new Registry;
			$registry->loadArray($array['params']);
			$array['params'] = (string) $registry;
		}

		if (isset($array['metadata']) && is_array($array['metadata']))
		{
			$registry = new Registry;
			$registry->loadArray($array['metadata']);
			$array['metadata'] = (string) $registry;
		}

		if (!Factory::getUser()->authorise('core.admin', 'com_jticketing.coupon.' . $array['id']))
		{
			$actions = Access::getActionsFromFile(
				JPATH_ADMINISTRATOR . '/components/com_jticketing/access.xml',
				"/access/section[@name='coupon']/");
			$default_actions = Access::getAssetRules('com_jticketing.coupon.' . $array['id'])->getData();
			$array_jaccess   = array();

			foreach ($actions as $action)
			{
				$array_jaccess[$action->name] = $default_actions[$action->name];
			}

			$array['rules'] = $this->RulestoArray($array_jaccess);
		}

		// Bind the rules for ACL where supported.
		if (isset($array['rules']) && is_array($array['rules']))
		{
			$this->setRules($array['rules']);
		}

		$nullableFields = ['valid_from', 'valid_to'];

		foreach ($nullableFields as $field) {
			if (empty($array[$field])) {
				$array[$field] = null;
			}
		}

		if (!isset($array['valid_to']) || $array['valid_to'] === '')
		{
			$array['valid_to'] = null;
			$this->valid_to = null;
		}

		return parent::bind($array, $ignore);
	}

	/**
	 * This function convert an array of JAccessRule objects into an rules array.
	 *
	 * @param   array  $jaccessrules  An array of JAccessRule objects.
	 *
	 * @return  array
	 *
	 * @since  3.0
	 */
	private function RulestoArray($jaccessrules)
	{
		$rules = array();

		foreach ($jaccessrules as $action => $jaccess)
		{
			$actions = array();

			foreach ($jaccess->getData() as $group => $allow)
			{
				$actions[$group] = ((bool) $allow);
			}

			$rules[$action] = $actions;
		}

		return $rules;
	}

	/**
	 * Method to set the publishing state for a row or list of rows in the database
	 * table.  The method respects checked out rows by other users and will attempt
	 * to checkin rows that it can after adjustments are made.
	 *
	 * @param   mixed    $pks     An optional array of primary key values to update.  If not set the instance property value is used.
	 * @param   integer  $state   The publishing state. eg. [0 = unpublished, 1 = published]
	 * @param   integer  $userId  The user id of the user performing the operation.
	 *
	 * @return    boolean    True on success.
	 *
	 * @since    1.0.4
	 */
	public function publish($pks = null, $state = 1, $userId = 0)
	{
		// Initialise variables.
		$k = $this->_tbl_key;

		// Sanitize input.
		ArrayHelper::toInteger($pks);
		$userId = (int) $userId;
		$state  = (int) $state;

		// If there are no primary keys set check to see if the instance key is set.
		if (empty($pks))
		{
			if ($this->$k)
			{
				$pks = array($this->$k);
			}
			// Nothing to set publishing state on, return false.
			else
			{
				$this->setError(Text::_('JLIB_DATABASE_ERROR_NO_ROWS_SELECTED'));

				return false;
			}
		}

		// Build the WHERE clause for the primary keys.
		$where = $k . '=' . implode(' OR ' . $k . '=', $pks);

		// Determine if there is checkin support for the table.

		if (!property_exists($this, 'checked_out') || !property_exists($this, 'checked_out_time'))
		{
			$checkin = '';
		}
		else
		{
			$checkin = ' AND (checked_out = 0 OR checked_out = ' . (int) $userId . ')';
		}

		// Update the publishing state for rows with the given primary keys.
		$this->_db->setQuery(
			'UPDATE `' . $this->_tbl . '`' .
			' SET `state` = ' . (int) $state .
			' WHERE (' . $where . ')' .
			$checkin
		);
		$this->_db->execute();

		// If checkin is supported and all rows were adjusted, check them in.
		if ($checkin && (count($pks) == $this->_db->getAffectedRows()))
		{
			// Checkin each row.
			foreach ($pks as $pk)
			{
				$this->checkin($pk);
			}
		}

		// If the JTable instance value is in the list of primary keys that were set, set the instance.

		if (in_array($this->$k, $pks))
		{
			$this->state = $state;
		}

		$this->setError('');

		return true;
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

		return 'com_jticketing.coupon.' . (int) $this->$k;
	}

	/**
	 * Returns the parent asset's id. If you have a tree structure, retrieve the parent's id using the external key field
	 *
	 * @param   integer  $table  table id
	 * @param   integer  $id     parents id
	 *
	 * @return  integer
	 *
	 * @see Table::_getAssetParentId
	 */
	protected function _getAssetParentId(Table $table = null, $id = null)
	{
		// We will retrieve the parent-asset from the Asset-table
		$assetParent = Table::getInstance('Asset');

		// Default: if no asset-parent can be found we take the global asset
		$assetParentId = $assetParent->getRootId();

		// The item has the component as asset-parent
		$assetParent->loadByName('com_jticketing');

		// Return the found asset-parent-id
		if ($assetParent->id)
		{
			$assetParentId = $assetParent->id;
		}

		return $assetParentId;
	}

	/**
	 * Delete data from table
	 *
	 * @param   integer  $pk  primary key of table
	 *
	 * @return  result
	 *
	 * @since  1.6
	 */
	public function delete($pk = null)
	{
		$this->load($pk);
		$result = parent::delete($pk);

		if ($result)
		{
		}

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
	public function store($updateNulls = true)
	{
		$this->valid_from = empty($this->valid_from) ? null : $this->valid_from;
		$this->valid_to = empty($this->valid_to) ? null : $this->valid_to;

		return parent::store($updateNulls);
	}
}