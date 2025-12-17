<?php
/**
 * @version    CVS: 1.0.0
 * @package    Com_Activitystream
 * @author     Parth Lawate <contact@techjoomla.com>
 * @copyright  2016 Parth Lawate
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Installer\InstallerAdapter;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\Database\DatabaseInterface;

/**
 * Script file of activitystream component
 *
 * @since  0.0.1
 */
class Com_ActivityStreamInstallerScript
{
	/**
	 * Database driver
	 *
	 * @var DatabaseInterface
	 */
	private $db;

	/** @var array The list of extra modules and plugins to install */
	private $queue = array(
		// Plugins => { (folder) => { (element) => (published) }* }*
		'plugins' => array(
			'privacy' => array(
				'activitystream' => 1
			),
			'api' => array(
				'tjactivity' =>0
				)
		),
	);

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->db = Factory::getContainer()->get(DatabaseInterface::class);
	}

	/**
	 * method to install the component
	 *
	 * @param   InstallerAdapter  $parent  parent
	 *
	 * @return void
	 */
	public function install(InstallerAdapter $parent): void
	{
	}

	/**
	 * method to uninstall the component
	 *
	 * @param   InstallerAdapter  $parent  parent
	 *
	 * @return void
	 */
	public function uninstall(InstallerAdapter $parent): void
	{
		$status          = new \stdClass;
		$status->plugins = array();

		// Plugins uninstallation
		if (count($this->queue['plugins']))
		{
			foreach ($this->queue['plugins'] as $folder => $plugins)
			{
				if (count($plugins))
				{
					foreach ($plugins as $plugin => $published)
					{
						$sql = $this->db->getQuery(true)->select($this->db->quoteName('extension_id'))
						->from($this->db->quoteName('#__extensions'))
						->where($this->db->quoteName('type') . ' = ' . $this->db->quote('plugin'))
						->where($this->db->quoteName('element') . ' = ' . $this->db->quote($plugin))
						->where($this->db->quoteName('folder') . ' = ' . $this->db->quote($folder));
						$this->db->setQuery($sql);

						$id = (int) ($this->db->loadResult() ?? null);

						if ($id)
						{
							$installer         = new Installer();
							$installer->setDatabase($this->db);
							$result            = $installer->uninstall('plugin', $id);
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
	}

	/**
	 * method to update the component
	 *
	 * @param   InstallerAdapter  $parent  parent
	 *
	 * @return void
	 */
	public function update(InstallerAdapter $parent): void
	{
		$this->installSqlFiles($parent);
	}

	/**
	 * method to run before an install/update/uninstall method
	 *
	 * @param   string            $type    type
	 * @param   InstallerAdapter  $parent  parent
	 *
	 * @return void
	 */
	public function preflight(string $type, InstallerAdapter $parent): void
	{
	}

	/**
	 * method to run after an install/update/uninstall method
	 *
	 * @param   string            $type    type
	 * @param   InstallerAdapter  $parent  parent
	 *
	 * @return void
	 */
	public function postflight(string $type, InstallerAdapter $parent): void
	{
		$src = $parent->getParent()->getPath('source');
 		$status = new \stdClass;
 		$status->plugins = array();

		// Plugins installation
 		if (count($this->queue['plugins']))
 		{
			foreach ($this->queue['plugins'] as $folder => $plugins)
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
 						$query = $this->db->getQuery(true)
 							->select('COUNT(*)')
 							->from($this->db->quoteName('#__extensions'))
 							->where($this->db->quoteName('element') . ' = ' . $this->db->quote($plugin))
 							->where($this->db->quoteName('folder') . ' = ' . $this->db->quote($folder));
 						$this->db->setQuery($query);
 						$count = (int) ($this->db->loadResult() ?? 0);

 						$installer = new Installer();
 						$installer->setDatabase($this->db);
 						$result = $installer->install($path);

						$status->plugins[] = array('name' => 'plg_' . $plugin, 'group' => $folder, 'result' => $result);

 						if ($published && !$count)
 						{
 							$query = $this->db->getQuery(true)
 								->update($this->db->quoteName('#__extensions'))
 								->set($this->db->quoteName('enabled') . ' = ' . $this->db->quote('1'))
 								->where($this->db->quoteName('element') . ' = ' . $this->db->quote($plugin))
 								->where($this->db->quoteName('folder') . ' = ' . $this->db->quote($folder));
 							$this->db->setQuery($query);
 							$this->db->execute();
 						}

					}
				}
			}

		}

		// Install SQL FIles
		$this->installSqlFiles($parent);
	}

	/**
	 * installSqlFiles
	 *
	 * @param   InstallerAdapter  $parent  parent
	 *
	 * @return  bool
	 */
	public function installSqlFiles(InstallerAdapter $parent): bool
	{
		// Obviously you may have to change the path and name if your installation SQL file ;)
		if (method_exists($parent, 'extension_root'))
		{
			$sqlfile = $parent->getPath('extension_root') . '/admin/sql/install.mysql.utf8.sql';
		}
		else
		{
			$sqlfile = $parent->getParent()->getPath('extension_root') . '/sql/install.mysql.utf8.sql';
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
						$this->db->setQuery($query);

						try
						{
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
