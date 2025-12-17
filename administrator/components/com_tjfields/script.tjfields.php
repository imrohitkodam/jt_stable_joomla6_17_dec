<?php
/**
 * @package    Tjfields
 *
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2017 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die();
use Joomla\Filesystem\Folder;
use Joomla\Filesystem\File;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Installer\InstallerAdapter;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\Database\DatabaseInterface;


/**
 * script
 *
 * @package     Tjfields
 * @subpackage  com_tjfields
 * @since       1.1
 */
class Com_TjfieldsInstallerScript
{
	/**
	 * Database driver
	 *
	 * @var DatabaseInterface
	 */
	private $db;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->db = Factory::getContainer()->get(DatabaseInterface::class);
	}

	// Used to identify new install or update
	private $componentStatus = "install";

	private $installation_queue = array(
		'modules' => array(
			'site' => array(
					'mod_tjfields_search' => array('tj-filters-mod-pos', 1)
						)
		)
	);

	/**
	 * method to run before an install/update/uninstall method
	 *
	 * @param   string            $type    install, update or discover_update
	 * @param   InstallerAdapter  $parent  parent
	 *
	 * @return void
	 */
	public function preflight(string $type, InstallerAdapter $parent): void
	{
		// Delete sql file if exist as related column is added through script
		if (File::exists(JPATH_SITE . '/administrator/components/com_tjfields/sql/updates/mysql/1.3.1.sql'))
		{
			File::delete(JPATH_SITE . '/administrator/components/com_tjfields/sql/updates/mysql/1.3.1.sql');
		}

		// Delete file field class if exists as this is replaced by tjfile.php
		if (File::exists(JPATH_SITE . '/administrator/components/com_tjfields/models/fields/file.php'))
		{
			File::delete(JPATH_SITE . '/administrator/components/com_tjfields/models/fields/file.php');
		}

		// Delete file field xml if exists as this is replaced by tjfile.xml
		if (File::exists(JPATH_SITE . '/administrator/components/com_tjfields/models/forms/types/forms/file.xml'))
		{
			File::delete(JPATH_SITE . '/administrator/components/com_tjfields/models/forms/types/forms/file.xml');
		}
	}

	/**
	 * Runs after install, update or discover_update
	 *
	 * @param   string            $type    install, update or discover_update
	 * @param   InstallerAdapter  $parent  parent
	 *
	 * @return  void
	 * 
	 * @since 1.1
	 */
	public function postflight(string $type, InstallerAdapter $parent): void
	{
		// Create a new query object.
		$query = $this->db->getQuery(true);
		$query->select('*');
		$query->from($this->db->quoteName('#__assets'));
		$query->where($this->db->quoteName('name') . ' = ' . $this->db->quote('com_tjfields'));
		$this->db->setQuery($query);

		// Get the com_tjfields asset_id which joomla adds while installing the package
		$tjFieldsAsset = $this->db->loadAssoc();

		$query1 = $this->db->getQuery(true);

			// Fields to update.
			$fields = array(
			$this->db->quoteName('rules') . ' = ' . $this->db->quote('{"core.field.addfieldvalue":{"1":1,"9":1,"6":1,"7":1,"2":1,"3":1,"4":1,"5":1},
			"core.field.viewfieldvalue":{"6":1,"7":1,"2":1,"4":1,"5":1},
			"core.field.editfieldvalue":{"6":1,"7":1,"4":1,"5":1},
			"core.field.editownfieldvalue":{"6":1,"7":1,"2":1,"3":1,"4":1,"5":1}}')
			);

			// Conditions for which records should be updated.
			$conditions = array(
			$this->db->quoteName('id') . ' = ' . (int) $tjFieldsAsset['id']
			);

			// Update the com_tjfields rules with default permissions
			$query1->update($this->db->quoteName('#__assets'))->set($fields)->where($conditions);

			$this->db->setQuery($query1);

			$this->db->execute();

		// Install subextensions
		$status = $this->_installSubextensions($parent);

		$msgBox = array();

		if (version_compare(JVERSION, '3.0', 'lt'))
		{
			$document = Factory::getDocument();
			$document->addStyleSheet(Uri::root() . '/media/techjoomla_strapper/css/bootstrap.min.css');
		}
	}

	/**
	 * Installs subextensions (modules, plugins) bundled with the main extension
	 *
	 * @param   InstallerAdapter  $parent  parent
	 *
	 * @return  \stdClass
	 */
	private function _installSubextensions(InstallerAdapter $parent): \stdClass
	{
		$src = $parent->getParent()->getPath('source');

		$status = new \stdClass;
		$status->modules = array();

		// Modules installation
		if (count($this->installation_queue['modules']))
		{
			foreach ($this->installation_queue['modules'] as $folder => $modules)
			{
				if (count($modules))
				{
					foreach ($modules as $module => $modulePreferences)
					{
						// Install the module
						if (empty($folder))
						{
							$folder = 'site';
						}

						$path = "$src/modules/$folder/$module";

						if (!is_dir($path))
						{
							$path = "$src/modules/$folder/mod_$module";
						}

						if (!is_dir($path))
						{
							$path = "$src/modules/$module";
						}

						if (!is_dir($path))
						{
							$path = "$src/modules/mod_$module";
						}

						if (!is_dir($path))
						{
							$fortest = '';

							// Continue;
						}

						// Was the module already installed?
						$sql = $this->db->getQuery(true)
							->select('COUNT(*)')
							->from('#__modules')
							->where($this->db->quoteName('module') . ' = ' . $this->db->quote('mod_' . $module));
						$this->db->setQuery($sql);

						$count = (int) ($this->db->loadResult() ?? 0);

						$installer = new Installer();
						$installer->setDatabase($this->db);
						$result = $installer->install($path);

						$status->modules[] = array(
							'name' => $module,
							'client' => $folder,
							'result' => $result,
							'status' => $modulePreferences[1]
						);

						// Modify where it's published and its published state
						if (!$count)
						{
							// A. Position and state
							list($modulePosition, $modulePublished) = $modulePreferences;

							if ($modulePosition == 'cpanel')
							{
								$modulePosition = 'icon';
							}

							$sql = $this->db->getQuery(true)
								->update($this->db->quoteName('#__modules'))
								->set($this->db->quoteName('position') . ' = ' . $this->db->quote($modulePosition))
								->where($this->db->quoteName('module') . ' = ' . $this->db->quote('mod_' . $module));

							if ($modulePublished)
							{
								$sql->set($this->db->quoteName('published') . ' = ' . $this->db->quote('1'));
							}

							$this->db->setQuery($sql);
							$this->db->execute();

							// B. Change the ordering of back-end modules to 1 + max ordering
							if ($folder == 'admin')
							{
								$query = $this->db->getQuery(true);
								$query->select('MAX(' . $this->db->quoteName('ordering') . ')')
									->from($this->db->quoteName('#__modules'))
									->where($this->db->quoteName('position') . '=' . $this->db->quote($modulePosition));
								$this->db->setQuery($query);
								$position = (int) ($this->db->loadResult() ?? 0);
								$position++;

								$query = $this->db->getQuery(true);
								$query->update($this->db->quoteName('#__modules'))
									->set($this->db->quoteName('ordering') . ' = ' . $this->db->quote($position))
									->where($this->db->quoteName('module') . ' = ' . $this->db->quote('mod_' . $module));
								$this->db->setQuery($query);
								$this->db->execute();
							}

							// C. Link to all pages
							$query = $this->db->getQuery(true);
							$query->select('id')->from($this->db->quoteName('#__modules'))
								->where($this->db->quoteName('module') . ' = ' . $this->db->quote('mod_' . $module));
							$this->db->setQuery($query);
							$moduleid = $this->db->loadResult() ?? null;

							$query = $this->db->getQuery(true);
							$query->select('*')->from($this->db->quoteName('#__modules_menu'))
								->where($this->db->quoteName('moduleid') . ' = ' . $this->db->quote($moduleid));
							$this->db->setQuery($query);
							$assignments = $this->db->loadObjectList();
							$isAssigned = !empty($assignments);

							if (!$isAssigned)
							{
								$o = (object) array(
									'moduleid'	=> $moduleid,
									'menuid'	=> 0
								);
								$this->db->insertObject('#__modules_menu', $o);
							}
						}
					}
				}
			}
		}

		return $status;
	}

	/**
	 * Runs on uninstallation
	 *
	 * @param   STRING  $parent  parent
	 *
	 * @return void
	 */
	public function install($parent)
	{
		$this->installSqlFiles($parent);
	}

	/**
	 * Runs on post installation
	 *
	 * @param   STRING  $status  status
	 * @param   STRING  $parent  parent
	 *
	 * @return void
	 */
	private function _renderPostUninstallation($status, $parent)
	{
		?>
		<?php $rows = 0;?>
		<h2><?php echo Text::_('TJ-Fields Uninstallation Status'); ?></h2>
		<table class="adminlist">
			<thead>
				<tr>
					<th class="title" colspan="2"><?php echo Text::_('Extension'); ?></th>
					<th width="30%"><?php echo Text::_('Status'); ?></th>
				</tr>
			</thead>
			<tbody>
				<tr class="row0">
					<td class="key" colspan="2"><?php echo 'TjFields ' . Text::_('Component'); ?></td>
					<td><strong style="color: green"><?php echo Text::_('Removed'); ?></strong></td>
				</tr>
			</tbody>
		</table>
		<?php
	}

	/**
	 * Runs on uninstallation
	 *
	 * @param   STRING  $parent  parent
	 *
	 * @return void
	 */
	public function uninstall($parent)
	{
		// Show the post-uninstallation page
		$this->_renderPostUninstallation($status, $parent);
	}

	/**
	 * method to update the component
	 *
	 * @param   STRING  $parent  parent
	 *
	 * @return void
	 */
	public function update($parent)
	{
		$this->componentStatus = "update";
		$this->installSqlFiles($parent);
		$this->fix_db_on_update();
	}

	/**
	 * method to fix database on update
	 *
	 * @return void
	 */
	public function fix_db_on_update()
	{
		$field_array = array();
		$query = "SHOW COLUMNS FROM `#__tjfields_fields`";
		$this->db->setQuery($query);
		$columns = $this->db->loadobjectlist();

		for ($i = 0; $i < count($columns); $i++)
		{
			$field_array[] = $columns[$i]->Field;
		}

		if (!in_array('filterable', $field_array))
		{
			$query = "ALTER TABLE `#__tjfields_fields`
						ADD COLUMN `filterable` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0 - For not filterable field. 1 for filterable field'";
			$this->db->setQuery($query);

			if (!$this->db->execute() )
			{
				Log::add('Unable to Alter #__tjfields_fields table. (While adding filterable column )', Log::ERROR, 'jerror');

				return false;
			}
		}

		if (!in_array('asset_id', $field_array))
		{
			$query = "ALTER TABLE `#__tjfields_fields`
						ADD COLUMN `asset_id` int(10) DEFAULT '0'";
			$this->db->setQuery($query);

			if (!$this->db->execute() )
			{
				Log::add('Unable to Alter #__tjfields_fields table. (While adding asset_id column )', Log::ERROR, 'jerror');

				return false;
			}
		}

		if (!in_array('showonlist', $field_array))
		{
			$query = "ALTER TABLE `#__tjfields_fields` ADD COLUMN `showonlist` tinyint(1) NOT NULL DEFAULT '0'";
			$this->db->setQuery($query);

			if (!$this->db->execute())
			{
				Log::add('Unable to Alter #__tjfields_fields table. (While adding filterable showonlist )', Log::ERROR, 'jerror');

				return false;
			}
		}

		$query = "CREATE TABLE IF NOT EXISTS `#__tjfields_category_mapping` (
				  `id` INT(11) NOT NULL AUTO_INCREMENT,
				  `field_id` INT(11) NOT NULL,
				  `category_id` INT(11) NOT NULL COMMENT 'CATEGORY ID FROM JOOMLA CATEGORY TABLE FOR CLIENTS EG CLIENT=COM_QUICK2CART.PRODUCT',
				  PRIMARY KEY (`id`)
				)DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";
		$this->db->setQuery($query);
		$this->db->execute();

		$field_array = array();
		$query = "SHOW COLUMNS FROM `#__tjfields_fields_value`";
		$this->db->setQuery($query);
		$columns = $this->db->loadobjectlist();

		for ($i = 0; $i < count($columns); $i++)
		{
			$field_array[] = $columns[$i]->Field;
		}

		if (!in_array('option_id', $field_array))
		{
			$query = "ALTER TABLE `#__tjfields_fields_value`
						ADD COLUMN `option_id` int(11) DEFAULT NULL";
			$this->db->setQuery($query);

			if (!$this->db->execute())
			{
				Log::add('Unable to Alter #__tjfields_fields_value table. (While adding option_id column )', Log::ERROR, 'jerror');

				return false;
			}
		}

		$field_array = array();
		$query = "SHOW COLUMNS FROM `#__tjfields_groups`";
		$this->db->setQuery($query);
		$columns = $this->db->loadobjectlist();

		for ($i = 0; $i < count($columns); $i++)
		{
			$field_array[] = $columns[$i]->Field;
		}

		if (!in_array('title', $field_array))
		{
			$query = "ALTER TABLE `#__tjfields_groups`
						ADD COLUMN `title` varchar(255) NOT NULL after `name`";
			$this->db->setQuery($query);

			if (!$this->db->execute() )
			{
				Log::add('Unable to Alter #__tjfields_groups table. (While adding title column )', Log::ERROR, 'jerror');

				return false;
			}
			else
			{
				$query = $this->db->getQuery(true);
				$query->select('*');
				$query->from($this->db->quoteName('#__tjfields_groups'));
				$this->db->setQuery($query);
				$groups = $this->db->loadObjectList();

				foreach ($groups as $group)
				{
					$group->title = $group->name;

					$this->db->updateObject('#__tjfields_groups', $group, 'id', true);
				}
			}
		}

		if (!in_array('asset_id', $field_array))
		{
			$query = "ALTER TABLE `#__tjfields_groups`
						ADD COLUMN `asset_id` int(10) DEFAULT '0'";
			$this->db->setQuery($query);

			if (!$this->db->execute() )
			{
				Log::add('Unable to Alter #__tjfields_groups table. (While adding asset_id column )', Log::ERROR, 'jerror');

				return false;
			}
		}

		// Add params column in tjfields_fields table to store fields attributes - added in v1.4
		$this->addparamsColumn();

		// Add title column in tjfields_fields table to store fields title - added in v1.4
		$this->addTitleColumn();
	}

	/**
	 * method to add title column
	 *
	 * @return void
	 */
	public function addTitleColumn()
	{
		$field_array = array();
		$query = "SHOW COLUMNS FROM `#__tjfields_fields`";
		$this->db->setQuery($query);
		$columns = $this->db->loadobjectlist();

		for ($i = 0; $i < count($columns); $i++)
		{
			$field_array[] = $columns[$i]->Field;
		}

		if (!in_array('title', $field_array))
		{
			$query = "ALTER TABLE `#__tjfields_fields` ADD COLUMN `title` varchar(255) NOT NULL after `core`";
			$this->db->setQuery($query);

			if (!$this->db->execute())
			{
				Log::add('Unable to Alter #__tjfields_fields table. (While adding title column )', Log::ERROR, 'jerror');

				return false;
			}
			else
			{
				$query = $this->db->getQuery(true);
				$query->select('*');
				$query->from($this->db->quoteName('#__tjfields_fields'));
				$this->db->setQuery($query);
				$fields = $this->db->loadObjectList();

				foreach ($fields as $field)
				{
					$field->title = $field->label;

					$this->db->updateObject('#__tjfields_fields', $field, 'id', true);
				}
			}
		}
	}

	/**
	 * method to add params column
	 *
	 * @return boolean
	 */
	public function addparamsColumn()
	{
		$field_array = array();
		$query = "SHOW COLUMNS FROM `#__tjfields_fields`";
		$this->db->setQuery($query);
		$columns = $this->db->loadobjectlist();

		for ($i = 0; $i < count($columns); $i++)
		{
			$field_array[] = $columns[$i]->Field;
		}

		if (!in_array('params', $field_array))
		{
			$query = "ALTER TABLE `#__tjfields_fields` ADD COLUMN `params` text COMMENT 'stores fields extra attributes in json format'";
			$this->db->setQuery($query);

			if (!$this->db->execute())
			{
				Log::add('Unable to Alter #__tjfields_fields table. (While adding params column )', Log::ERROR, 'jerror');

				return false;
			}
			else
			{
				$query = $this->db->getQuery(true);
				$query->select('*');
				$query->from('#__tjfields_fields');
				$this->db->setQuery($query);
				$fields = $this->db->loadObjectList();

				$param = array();

				foreach ($fields as $field)
				{
					if (!empty($field->min))
					{
						$param['min'] = $field->min;
					}

					if (!empty($field->max))
					{
						$param['max'] = $field->max;
					}

					if (!empty($field->rows))
					{
						$param['rows'] = $field->rows;
					}

					if (!empty($field->cols))
					{
						$param['cols'] = $field->cols;
					}

					if (!empty($field->format))
					{
						$param['format'] = $field->format;
					}

					if (!empty($field->default_value))
					{
						$param['default'] = $field->default_value;
					}

					if (!empty($field->placeholder))
					{
						$param['placeholder'] = $field->placeholder;
					}

					$field->params = json_encode($param);

					$this->db->updateObject('#__tjfields_fields', $field, 'id', true);
				}

				$deleteColumn = array("min", "max", "rows", "cols", "format", "default_value", "placeholder");

				foreach ($deleteColumn as $pm)
				{
					$query = "ALTER TABLE `#__tjfields_fields` DROP COLUMN " . $pm;

					$this->db->setQuery($query);

					if (!$this->db->execute())
					{
						Log::add('Unable to delete column ' . $pm, Log::ERROR, 'jerror');

						return false;
					}
				}
			}
		}
	}

	/**
	 * method to table columns
	 *
	 * @param   STRING  $parent  parent
	 *
	 * @return mixed
	 */
	public function installSqlFiles($parent)
	{
		// Install country table(#__tj_country) if it does not exists
		$check = $this->checkTableExists('tj_country');

		if (!$check)
		{
			// Lets create the table
			$this->runSQL($parent, 'country.sql');
		}
		else
		{
			$newColumns = array('id', 'country', 'country_3_code', 'country_code', 'country_dial_code', 'country_text', 'ordering');
			$oldColumns = $this->getColumns('#__tj_country');

			$dropTableFlag = 0;

			foreach ($newColumns as $column)
			{
				if (!in_array($column, $oldColumns))
				{
					$dropTableFlag = 1;
					break;
				}
			}

			if ($dropTableFlag)
			{
				// Backup old table
				$backup = $this->renameTable('#__tj_country', '#__tj_country_backup');

				if ($backup)
				{
					// Lets create the table with new structure
					$this->runSQL($parent, 'country.sql');

					$componentsArray = array('com_jgive', 'com_jticketing', 'com_quick2cart', 'com_socialads', 'com_tjlms', 'com_tjvendors');

					foreach ($componentsArray as $key => $component)
					{
						$sql = $this->db->getQuery(true)
						->select('id')
						->from($this->db->quoteName($backup))
						->where($this->db->quoteName($component) . ' = "0"');

						$this->db->setQuery($sql);
						$countryIdList = $this->db->loadAssocList();

						$countryArray = array_column($countryIdList, 'id');
						$countryList = str_replace("'", "", implode(',', $countryArray));

						if ($countryList)
						{
							$query = $this->db->getQuery(true);
							$query->update($this->db->quoteName('#__tj_country'))
								->set($this->db->quoteName($component) . ' = "0"')
								->where($this->db->quoteName('id') . ' IN (' . $countryList . ')');
							$this->db->setQuery($query);
							$this->db->execute();
						}
					}
				}
			}
		}

		// Install region table(#__tj_region) if it does not exists
		$check = $this->checkTableExists('tj_region');

		if (!$check)
		{
			// Lets create the table
			$this->runSQL($parent, 'region.sql');
		}
		else
		{
			$newColumns = array('id', 'country_id', 'region_3_code', 'region_code', 'region', 'region_text', 'ordering');
			$oldColumns = $this->getColumns('#__tj_region');

			$dropTableFlag = 0;

			foreach ($newColumns as $column)
			{
				if (! in_array($column, $oldColumns))
				{
					$dropTableFlag = 1;
					break;
				}
			}

			if ($dropTableFlag)
			{
				// Backup old table
				$backup = $this->renameTable('#__tj_region', '#__tj_region_backup');

				if ($backup)
				{
					// Lets create the table with new structure
					$this->runSQL($parent, 'region.sql');
				}
			}
		}

		// Install city table(#__tj_city) if it does not exists
		$check = $this->checkTableExists('tj_city');

		if (!$check)
		{
			// Lets create the table
			$this->runSQL($parent, 'city.sql');
		}
		else
		{
			$newColumns = array('id', 'city', 'country_id', 'region_id', 'city_text', 'zip', 'ordering');
			$oldColumns = $this->getColumns('#__tj_city');

			$dropTableFlag = 0;

			foreach ($newColumns as $column)
			{
				if (! in_array($column, $oldColumns))
				{
					$dropTableFlag = 1;
					break;
				}
			}

			if ($dropTableFlag)
			{
				// Backup old table
				$backup = $this->renameTable('#__tj_city', '#__tj_city_backup');

				if ($backup)
				{
					// Lets create the table with new structure
					$this->runSQL($parent, 'city.sql');
				}
			}
		}
	}

	/**
	 * method to check if table exists
	 *
	 * @param   STRING  $table  existing name
	 *
	 * @return boolean
	 */
	public function checkTableExists($table)
	{
		$config = Factory::getConfig();
		$dbname = $config->get('db');
		$dbprefix = $config->get('dbprefix');

		$query = " SELECT table_name
		 FROM information_schema.tables
		 WHERE table_schema='" . $dbname . "'
		 AND table_name='" . $dbprefix . $table . "'";

		$this->db->setQuery($query);
		$check = $this->db->loadResult() ?? null;

		if ($check)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * method to table columns
	 *
	 * @param   STRING  $table  existing name
	 *
	 * @return array
	 */
	public function getColumns($table)
	{
		$field_array = array();
		$query = "SHOW COLUMNS FROM " . $table;
		$this->db->setQuery($query);
		$columns = $this->db->loadobjectlist();

		for ($i = 0; $i < count($columns); $i++)
		{
			$columns_array[] = $columns[$i]->Field;
		}

		return $columns_array;
	}

	/**
	 * method to rename table
	 *
	 * @param   STRING  $table     existing name
	 * @param   STRING  $newTable  updated name
	 *
	 * @return boolean
	 */
	public function renameTable($table, $newTable)
	{
		$newTable = $newTable . '_' . date('d-m-Y_H_m_s');

		$query = "RENAME TABLE `" . $table . "` TO `" . $newTable . "`";
		$this->db->setQuery($query);

		if ($this->db->execute())
		{
			return $newTable;
		}

		return false;
	}

	/**
	 * method to execute sql file
	 *
	 * @param   STRING  $parent   parent
	 * @param   STRING  $sqlfile  sql file
	 *
	 * @return boolean
	 */
	public function runSQL($parent,$sqlfile)
	{
		// Obviously you may have to change the path and name if your installation SQL file ;)
		if (method_exists($parent, 'extension_root'))
		{
			$sqlfile = $parent->getPath('extension_root') . '/administrator/sql/' . $sqlfile;
		}
		else
		{
			$sqlfile = $parent->getParent()->getPath('extension_root') . '/sql/' . $sqlfile;
		}

		// Don't modify below this line
		$buffer = file_get_contents($sqlfile);

		if ($buffer !== false)
		{
			$queries = $this->db->splitSql($buffer);

			if (count($queries) != 0)
			{
				foreach ($queries as $query)
				{
					$query = trim($query);

					if ($query != '' && $query[0] != '#')
					{
						try
						{
							$this->db->setQuery($query);
							$this->db->execute();
						}
						catch (\RuntimeException $e)
						{
							Log::add(
								Text::sprintf('JLIB_INSTALLER_ERROR_SQL_ERROR', $e->getMessage()),
								Log::WARNING,
								'jerror'
							);

							return false;
						}
					}
				}
			}
		}

		return true;
	}
}