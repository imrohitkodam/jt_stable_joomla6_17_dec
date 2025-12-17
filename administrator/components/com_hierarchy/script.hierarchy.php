<?php
/**
 * @version    SVN: <svn_id>
 * @package    Com_Hierarchy
 * @copyright  Copyright (C) 2016 - 2022 Techjoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later; see http://www.gnu.org/licenses/gpl-2.0.html
 * Hierarchy Management Extension is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */

// No direct access
defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\Installer\InstallerHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Installer\Installer;

/**
 * Hierarchy Installer
 *
 * @since  1.0.0
 */
class Com_HierarchyInstallerScript
{
	/** @var array The list of extra modules and plugins to install */
	private $oldversion = "";

	private $installation_queue = array(
		'plugins' => array(
			'privacy' => array(
				'hierarchy' => 1,
			),
			'actionlog' => array(
				'hierarchy' => 1,
			)
		)
	);

	private $uninstall_queue = array(
		'plugins' => array(
			'privacy' => array(
				'hierarchy' => 1
			),
			'actionlog' => array(
				'hierarchy' => 1
			)
		)
	);

	/**
	 * method to run before an install/update/uninstall method
	 *
	 * @param   JInstaller  $type    type
	 * @param   JInstaller  $parent  parent
	 *
	 * @return void
	 */
	public function preflight($type, $parent)
	{
	}

	/**
	 * method to install the component
	 *
	 * @param   JInstaller  $parent  parent
	 *
	 * @return  void
	 */
	public function install($parent)
	{
	}

	/**
	 * method to update the component
	 *
	 * @param   JInstaller  $parent  Parent
	 *
	 * @return void
	 */
	public function update($parent)
	{
		$this->installSqlFiles($parent);
	}

	/**
	 * installSqlFiles
	 *
	 * @param   JInstaller  $parent  parent
	 *
	 * @return  void
	 */
	public function installSqlFiles($parent)
	{
		$db = Factory::getDBO();

		if (!defined('DS')) {
			define('DS', DIRECTORY_SEPARATOR);
		}
		// Obviously you may have to change the path and name if your installation SQL file ;)
		if (method_exists($parent, 'extension_root'))
		{
			$sqlfile = $parent->getPath('extension_root') . DS . 'admin' . DS . 'sql' . DS . 'install.mysql.utf8.sql';
		}
		else
		{
			$sqlfile = $parent->getParent()->getPath('extension_root') . DS . 'sql' . DS . 'install.mysql.utf8.sql';
		}

		// Don't modify below this line
		$buffer = file_get_contents($sqlfile);

		if ($buffer !== false)
		{
			$queries = $db->splitSql($buffer);

			if (count($queries) != 0)
			{
				foreach ($queries as $query)
				{
					$query = trim($query);

					if ($query != '' && $query[0]!= '#')
					{
						$db->setQuery($query);

						try
						{
							$db->execute();
						}
						catch (\Exception $e)
						{
							$app = Factory::getApplication();
							$app->enqueueMessage(Text::sprintf('JLIB_INSTALLER_ERROR_SQL_ERROR', $e->getMessage()), 'error');

							return false;
						}
					}
				}
			}
		}
	}

	/**
	 * Runs after install, update or discover_update
	 *
	 * @param   string      $type    install, update or discover_update
	 *
	 * @param   JInstaller  $parent  parent
	 *
	 * @return  void
	 */
	public function postflight($type, $parent)
	{
		// Install subextensions
		$status = $this->_installSubextensions($parent);
	}

	/**
	 * Installs subextensions (modules, plugins) bundled with the main extension
	 *
	 * @param   JInstaller  $parent  parent
	 * 
	 * @return JObject The subextension installation status
	 */
	private function _installSubextensions($parent)
	{
		$src = $parent->getParent()->getPath('source');
		$db  = Factory::getDbo();

		$status = new CMSObject;
		$status->modules = array();

		// Plugins installation
		if (count($this->installation_queue['plugins']))
		{
			foreach ($this->installation_queue['plugins'] as $folder => $plugins)
			{
				if (count($plugins))
				{
					foreach ($plugins as $plugin => $published)
					{
						$path = "$src/plugins/$folder/$plugin";

						if (!is_dir($path))
						{
							$path = "$src/plugins/$folder/plg_$plugin";
						}

						if (!is_dir($path))
						{
							$path = "$src/plugins/$plugin";
						}

						if (!is_dir($path))
						{
							$path = "$src/plugins/plg_$plugin";
						}

						if (!is_dir($path))
						{
							continue;
						}

						// Was the plugin already installed?
						$query = $db->getQuery(true)
							->select('COUNT(*)')
							->from($db->qn('#__extensions'))
							->where('( ' . ($db->qn('name') . ' = ' . $db->q($plugin)) . ' OR ' . ($db->qn('element') . ' = ' . $db->q($plugin)) . ' )')
							->where($db->qn('folder') . ' = ' . $db->q($folder));
						$db->setQuery($query);
						$count = $db->loadResult();

						$installer = new Installer();
						$installer->setDatabase($db);
						$result = $installer->install($path);

						$status->plugins[] = array('name' => $plugin, 'group' => $folder, 'result' => $result, 'status' => $published);

						if ($published && !$count)
						{
							$query = $db->getQuery(true)
								->update($db->qn('#__extensions'))
								->set($db->qn('enabled') . ' = ' . $db->q('1'))
								->where('( ' . ($db->qn('name') . ' = ' . $db->q($plugin)) . ' OR ' . ($db->qn('element') . ' = ' . $db->q($plugin)) . ' )')
								->where($db->qn('folder') . ' = ' . $db->q($folder));
							$db->setQuery($query);
							$db->execute();
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
	 * @param   JInstaller  $parent  parent
	 *
	 * @return  void
	 */
	public function uninstall($parent)
	{
		// Uninstall subextensions
		$status = $this->_uninstallSubextensions($parent);
	}

	/**
	 * Uninstalls subextensions (modules, plugins) bundled with the main extension
	 *
	 * @param   JInstaller  $parent  parent
	 * 
	 * @return JObject The subextension uninstallation status
	 */
	private function _uninstallSubextensions($parent)
	{
		jimport('joomla.installer.installer');

		$db = Factory::getDbo();

		$status = new CMSObject;
		$status->modules = array();
		$status->plugins = array();

		$src = $parent->getParent()->getPath('source');

		// Plugins uninstallation
		if (count($this->uninstall_queue['plugins']))
		{
			foreach ($this->uninstall_queue['plugins'] as $folder => $plugins)
			{
				if (count($plugins))
				{
					foreach ($plugins as $plugin => $published)
					{
						$sql = $db->getQuery(true)
							->select($db->qn('extension_id'))
							->from($db->qn('#__extensions'))
							->where($db->qn('type') . ' = ' . $db->q('plugin'))
							->where($db->qn('element') . ' = ' . $db->q($plugin))
							->where($db->qn('folder') . ' = ' . $db->q($folder));
						$db->setQuery($sql);
						$id = $db->loadResult();

						if ($id)
						{
							$installer = new Installer();
							$installer->setDatabase($db);
							$result = $installer->uninstall('plugin', $id);
							$status->plugins[] = array(
								'name' => 'plg_' . $plugin,
								'group' => $folder,
								'result' => $result
							);
						}
					}
				}
			}
		}

		return $status;
	}
}
