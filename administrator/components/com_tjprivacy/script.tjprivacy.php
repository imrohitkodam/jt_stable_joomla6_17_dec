<?php
/**
 * @version    SVN: <svn_id>
 * @package    TJPrivacy
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2017-2018 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */
 
defined('_JEXEC') or die();
use Joomla\CMS\Factory;
use Joomla\CMS\Installer\InstallerAdapter;
use Joomla\CMS\Installer\InstallerHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\Database\DatabaseInterface;

class com_tjprivacyInstallerScript
{
	// Used to identify new install or update
	private $componentStatus = "install";

	/**
	 * Database driver
	 *
	 * @var    DatabaseInterface
	 * @since  1.0
	 */
	private $db;

	/**
	 * Constructor
	 *
	 * @since  1.0
	 */
	public function __construct()
	{
		$this->db = Factory::getContainer()->get(DatabaseInterface::class);
	}

	/**
	 * Method to run before an install/update/uninstall method
	 *
	 * @param   string             $type    install, update or discover_update
	 * @param   InstallerAdapter   $parent  Class calling this method
	 *
	 * @return void
	 */
	public function preflight(string $type, InstallerAdapter $parent): void
	{
	}

	/**
	 * Runs after install, update or discover_update
	 *
	 * @param   string             $type    install, update or discover_update
	 * @param   InstallerAdapter   $parent  Class calling this method
	 *
	 * @return void
	 */
	public function postflight(string $type, InstallerAdapter $parent): void
	{
	}

	/**
	 * Method to install the component
	 *
	 * @param   InstallerAdapter  $parent  Class calling this method
	 *
	 * @return void
	 */
	public function install(InstallerAdapter $parent): void
	{
		$this->installSqlFiles($parent);
	}

	/**
	 * method to update the component
	 *
	 * @param   InstallerAdapter  $parent  Class calling this method
	 *
	 * @return void
	 */
	public function update(InstallerAdapter $parent): void
	{
		$this->componentStatus = "update";
	}

	/**
	 * method to install sql files
	 *
	 * @param   InstallerAdapter  $parent  Class calling this method
	 *
	 * @return void
	 */
	public function installSqlFiles(InstallerAdapter $parent): void
	{
		// Lets create the table
		$this->runSQL($parent, 'install.sql');
	}

	/**
	 * Execute sql files
	 *
	 * @param   InstallerAdapter  $parent   Class calling this method
	 * @param   string            $sqlfile  Sql file name
	 *
	 * @return  boolean
	 */
	private function runSQL(InstallerAdapter $parent, string $sqlfile): bool
	{
		// Obviously you may have to change the path and name if your installation SQL file ;)
		if (method_exists($parent, 'extension_root'))
		{
			$sqlfile = $parent->getPath('extension_root') . '/admin/sqlfiles/' . $sqlfile;
		}
		else
		{
			$sqlfile = $parent->getParent()->getPath('extension_root') . '/sqlfiles/' . $sqlfile;
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
								Text::sprintf(
									'JLIB_INSTALLER_ERROR_SQL_ERROR',
									$e->getMessage()
								),
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
