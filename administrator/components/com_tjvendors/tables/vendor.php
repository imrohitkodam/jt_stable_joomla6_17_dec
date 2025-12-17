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
defined('_JEXEC') or die;

use Joomla\CMS\Access\Access;
use Joomla\CMS\Factory;
use Joomla\Filesystem\File;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;
use Joomla\Registry\Registry;
use Joomla\String\StringHelper;
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
	 * @param   Joomla\Database\DatabaseDriver  $db  A database connector object
	 */
	public function __construct($db)
	{
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

		if (!empty($array['vendor_id']) && !Factory::getUser()->authorise('core.admin', 'com_tjvendors.vendor.' . $array['vendor_id']))
		{
			$actions = Access::getActionsFromFile(
				JPATH_ADMINISTRATOR . '/components/com_tjvendors/access.xml',
				"/access/section[@name='vendor']/"
			);
			$default_actions = Access::getAssetRules('com_tjvendors.vendor.' . $array['vendor_id'])->getData();
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
				$this->alias = StringHelper::increment($this->alias, 'dash');
			}

			Factory::getApplication()->enqueueMessage($msg, 'warning');
		}

		// If there is an ordering column and this is a new row then get the next ordering value
		if (property_exists($this, 'ordering') && $this->vendor_id == 0)
		{
			$this->ordering = self::getNextOrder();
		}

		$app = Factory::getApplication();
		$vendor_id = $app->getInput()->get('vendor_id');

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
					$app->enqueueMessage(Text::_('COM_TJVENDOR_FILE_BIGGER_UPLOAD_ERROR'), 'warning');
					$this->setError(Text::_('COM_TJVENDOR_FILE_BIGGER_UPLOAD_ERROR'));

					return false;
				}

				$filename   = File::stripExt($singleFile['name']);
				$filename   = File::makeSafe($filename);
				$extension  = File::getExt($singleFile['name']);
				$fileType   = $singleFile['type'];
				$filename   = md5(time()) . $filename;
				$filepath   = '/media/com_tjvendor/vendor/' . $filename . '.' . $extension;
				$uploadPath = JPATH_ROOT . $filepath;
				$fileTemp   = $singleFile['tmp_name'];

				// If tmp_name is empty, then the file was bigger than the PHP limit
				if (!empty($fileTemp))
				{
					// Get the mime type this is an image file
					$mime = $this->getMimeType($fileTemp, true);

					// Did we get anything useful?
					if ($mime !== false)
					{
						$result = in_array($mime, array('image/jpeg', 'image/png', 'image/jpg'));

						// If the mime type is not allowed we don't upload it and show the mime code error to the user
						if ($result === false)
						{
							$app->enqueueMessage(Text::_('COM_TJVENDORS_WRONG_FILE_UPLOAD'), 'warning');
							$this->setError(Text::_('COM_TJVENDORS_WRONG_FILE_UPLOAD'));

							return false;
						}
					}
					else
					{
						$app->enqueueMessage(Text::_('COM_TJVENDORS_WRONG_FILE_UPLOAD'), 'warning');
						$this->setError(Text::_('COM_TJVENDORS_WRONG_FILE_UPLOAD'));

						return false;
					}
				}
				else
				{
					$app->enqueueMessage(Text::_('COM_TJVENDORS_WRONG_FILE_UPLOAD'), 'warning');
					$this->setError(Text::_('COM_TJVENDORS_WRONG_FILE_UPLOAD'));

					return false;
				}

				// Validate file extension
				if (!empty($extension))
				{
					if (($extension !== "png") && ($extension !== "jpg") && ($extension !== "jpeg"))
					{
						$app->enqueueMessage(Text::_('COM_TJVENDORS_WRONG_FILE_UPLOAD'), 'warning');
						$this->setError(Text::_('COM_TJVENDORS_WRONG_FILE_UPLOAD'));

						return false;
					}
				}
				else
				{
					$app->enqueueMessage(Text::_('COM_TJVENDORS_WRONG_FILE_UPLOAD'), 'warning');
					$this->setError(Text::_('COM_TJVENDORS_WRONG_FILE_UPLOAD'));

					return false;
				}

				// Validate file type
				if (!empty($fileType))
				{
					if (($fileType !== "image/png") && ($fileType !== "image/jpg") && ($fileType !== "image/jpeg"))
					{
						$app->enqueueMessage(Text::_('COM_TJVENDORS_WRONG_FILE_UPLOAD'), 'warning');
						$this->setError(Text::_('COM_TJVENDORS_WRONG_FILE_UPLOAD'));

						return false;
					}
				}
				else
				{
					$app->enqueueMessage(Text::_('COM_TJVENDORS_WRONG_FILE_UPLOAD'), 'warning');
					$this->setError(Text::_('COM_TJVENDORS_WRONG_FILE_UPLOAD'));

					return false;
				}

				if (! File::exists($uploadPath))
				{
					if (! File::upload($fileTemp, $uploadPath))
					{
						$app->enqueueMessage(Text::_('COM_TJVENDOR_FILE_MOVING_ERROR'), 'warning');
						$this->setError(Text::_('COM_TJVENDOR_FILE_MOVING_ERROR'));

						return false;
					}
				}

				$this->vendor_logo = $filepath;
			}
		}

		return parent::check();
	}

	/**
	 * Get the Mime type
	 *
	 * @param   string   $file     The link to the file to be checked
	 * @param   boolean  $isImage  True if the passed file is an image else false
	 *
	 * @return  mixed    the mime type detected false on error
	 *
	 * @since   3.7.2
	 */
	private function getMimeType($file, $isImage = false)
	{
		// If we can't detect anything mime is false
		$mime = false;

		try
		{
			if ($isImage && function_exists('exif_imagetype'))
			{
				$mime = image_type_to_mime_type(exif_imagetype($file));
			}
			elseif ($isImage && function_exists('getimagesize'))
			{
				$imagesize = getimagesize($file);
				$mime      = isset($imagesize['mime']) ? $imagesize['mime'] : false;
			}
			elseif (function_exists('mime_content_type'))
			{
				// We have mime magic.
				$mime = mime_content_type($file);
			}
			elseif (function_exists('finfo_open'))
			{
				// We have fileinfo
				$finfo = finfo_open(FILEINFO_MIME_TYPE);
				$mime  = finfo_file($finfo, $file);
				finfo_close($finfo);
			}
		}
		catch (\Exception $e)
		{
			// If we have any kind of error here => false;
			return false;
		}

		// If we can't detect the mime try it again
		if ($mime === 'application/octet-stream' && $isImage === true)
		{
			$mime = $this->getMimeType($file, false);
		}

		// We have a mime here
		return $mime;
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
}
