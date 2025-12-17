<?php
/**
 * @package     TJVendors
 * @subpackage  com_tjvendors
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access
defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\Access\Access;
use Joomla\Filesystem\File;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

/**
 * vendor Table class
 *
 * @since  1.6
 */
class TjvendorsTablevendor extends Table
{
	/**
	 * Constructor
	 *
	 * @param   JDatabase  &$db  A database connector object
	 */
	public function __construct(&$db)
	{
		// array('typeAlias' => 'com_tjvendors.vendor')
		// 		// );

		parent::__construct('#__tjvendors_vendors', 'vendor_id', $db);
	}

	/**
	 * Overloaded bind function to pre-process the params.
	 *
	 * @param   array  $array   Named array
	 * @param   mixed  $ignore  Optional array or list of parameters to ignore
	 *
	 * @return  null|string  null is operation was satisfactory, otherwise returns an error
	 *
	 * @see     Table:bind
	 * @since   1.5
	 */
	public function bind($array, $ignore = '')
	{
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

		if (! Factory::getUser()->authorise('core.admin', 'com_tjvendors.vendor.' . $array['vendor_id']))
		{
			$actions = Access::getActionsFromFile(JPATH_ADMINISTRATOR . '/components/com_tjvendors/access.xml', "/access/section[@name='vendor']/");
			$default_actions = Access::getAssetRules('com_tjvendors.vendor.' . $array['vendor_id'])->getData();
			$array_jaccess = array();

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

		return parent::bind($array, $ignore);
	}

	/**
	 * This function convert an array of Rule objects into an rules array.
	 *
	 * @param   array  $jaccessrules  An array of Rule objects.
	 *
	 * @return  array
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
	 * Overloaded check function
	 *
	 * @return bool
	 */
	public function check()
	{
		$db = Factory::getDbo();
		$this->alias = trim($this->alias);

		if (!$this->alias)
		{
			$this->alias = $this->vendor_title;
		}

		if ($this->alias)
		{
			if (Factory::getConfig()->get('unicodeslugs') == 1)
			{
				$this->alias = OutputFilter::stringURLUnicodeSlug($this->alias);
			}
			else
			{
				$this->alias = OutputFilter::stringURLSafe($this->alias);
			}
		}

		// Check if event with same alias is present
		$table = Table::getInstance('Vendor', 'TjVendorsTable', array('dbo', $db));

		if ($table->load(array('alias' => $this->alias)) && ($table->vendor_id != $this->vendor_id || $this->vendor_id == 0))
		{
			$msg = Text::_('COM_TJVENDORS_SAVE_ALIAS_WARNING');

			while ($table->load(array('alias' => $this->alias)))
			{
				$this->alias = JString::increment($this->alias, 'dash');
			}

			Factory::getApplication()->enqueueMessage($msg, 'warning');
		}

		// If there is an ordering column and this is a new row then get the next ordering value
		if (property_exists($this, 'ordering') && $this->vendor_id == 0)
		{
			$this->ordering = self::getNextOrder();
		}

		$app = Factory::getApplication();
		$files = $app->getInput()->files->get('jform', array(), 'raw');
		$array = $app->getInput()->get('jform', array(), 'ARRAY');

		if (! empty($files['vendor_logo']))
		{
			$this->vendor_logo = "";
			$singleFile = $files['vendor_logo'];

			// Check if the server found any error.
			$fileError = $singleFile['error'];
			$message = '';

			if ($fileError > 0 && $fileError != 4)
			{
				switch ($fileError)
				{
					case 1:
						$message = Text::_('COM_TJVENDOR_FILE_SIZE_EXCEEDS');
						break;
					case 2:
						$message = Text::_('COM_TJVENDOR_FILE_SIZE_EXCEEDS_FORM');
						break;
					case 3:
						$message = Text::_('COM_TJVENDOR_FILE_PARTIAL_UPLOAD_ERROR');
						break;
				}

				if ($message != '')
				{
					$app->enqueueMessage($message, 'warning');

					return false;
				}
			}
			elseif ($fileError == 4)
			{
				if (isset($array['vendor_logo']))
				{
					$this->vendor_logo = $array['vendor_logo'];
				}
			}
			else
			{
				// Check for filesize
				$fileSize = $singleFile['size'];

				if ($fileSize > 26214400)
				{
					$app->enqueueMessage('COM_TJVENDOR_FILE_BIGGER_UPLOAD_ERROR', 'warning');

					return false;
				}

				$filename = File::stripExt($singleFile['name']);
				$extension = File::getExt($singleFile['name']);
				$filename = md5(time()) . $filename;
				$filepath = '/media/com_tjvendor/vendor/' . $filename . '.' . $extension;
				$uploadPath = JPATH_ROOT . $filepath;
				$fileTemp = $singleFile['tmp_name'];

				if (! File::exists($uploadPath))
				{
					if (! File::upload($fileTemp, $uploadPath))
					{
						$app->enqueueMessage('COM_TJVENDOR_FILE_MOVING_ERROR', 'warning');

						return false;
					}
				}

				$this->vendor_logo = $filepath;
			}
		}

		return parent::check();
	}

	/**
	 * Method to set the publishing state for a row or list of rows in the database
	 * table.  The method respects checked out rows by other users and will attempt
	 * to checkin rows that it can after adjustments are made.
	 *
	 * @param   mixed    $pks     An optional array of primary key values to update.  If not
	 *                            set the instance property value is used.
	 * @param   integer  $state   The publishing state. eg. [0 = unpublished, 1 = published]
	 * @param   integer  $userId  The user id of the user performing the operation.
	 *
	 * @return   boolean  True on success.
	 *
	 * @since    1.0.4
	 *
	 * @throws Exception
	 */
	public function publish($pks = null, $state = 1, $userId = 0)
	{
		// Initialise variables.
		$k = $this->_tbl_key;

		// Sanitize input.
		ArrayHelper::toInteger($pks);
		$userId = (int) $userId;
		$state = (int) $state;

		// If there are no primary keys set check to see if the instance key is set.
		if (empty($pks))
		{
			if ($this->$k)
			{
				$pks = array(
					$this->$k,
				);
			}
			// Nothing to set publishing state on, return false.
			else
			{
				throw new Exception(500, Text::_('JLIB_DATABASE_ERROR_NO_ROWS_SELECTED'));
			}
		}

		// Build the WHERE clause for the primary keys.
		$where = $k . '=' . implode(' OR ' . $k . '=', $pks);

		// Determine if there is checkin support for the table.
		if (! property_exists($this, 'checked_out') || ! property_exists($this, 'checked_out_time'))
		{
			$checkin = '';
		}
		else
		{
			$checkin = ' AND (checked_out = 0 OR checked_out = ' . (int) $userId . ')';
		}

		// Update the publishing state for rows with the given primary keys.
		$this->_db->setQuery('UPDATE `' . $this->_tbl . '`' . ' SET `state` = ' . (int) $state . ' WHERE (' . $where . ')' . $checkin);
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

		// If the Table instance value is in the list of primary keys that were set, set the instance.
		if (in_array($this->$k, $pks))
		{
			$this->state = $state;
		}

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

		return 'com_tjvendors.vendor.' . (int) $this->$k;
	}

	/**
	 * Returns the parent asset's id. If you have a tree structure, retrieve the parent's id using the external key field
	 *
	 * @param   Table    $table  Table name
	 * @param   integer  $id     Id
	 *
	 * @see Table::_getAssetParentId
	 *
	 * @return mixed The id on success, false on failure.
	 */
	protected function _getAssetParentId(Table $table = null, $id = null)
	{
		// We will retrieve the parent-asset from the Asset-table
		$assetParent = Table::getInstance('Asset');

		// Default: if no asset-parent can be found we take the global asset
		$assetParentId = $assetParent->getRootId();

		// The item has the component as asset-parent
		$assetParent->loadByName('com_tjvendors');

		// Return the found asset-parent-id
		if ($assetParent->id)
		{
			$assetParentId = $assetParent->id;
		}

		return $assetParentId;
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
