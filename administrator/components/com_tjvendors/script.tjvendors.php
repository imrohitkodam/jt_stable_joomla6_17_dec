<?php
/**
 * @package     TJVendors
 * @subpackage  com_tjvendors
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die();

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\Filesystem\Folder;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Installer\InstallerAdapter;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseInterface;
use Joomla\Component\Menus\Administrator\Table\MenuTable;

/**
 * Script for migration
 *
 * @package  TJvendor
 *
 * @since    1.0
 */
class Com_TjvendorsInstallerScript
{
	/**
	 * Database driver
	 *
	 * @var DatabaseInterface
	 */
	private $db;

	/**
	 * Used to identify new install or update
	 *
	 * @var string
	 */
	private $componentStatus = "install";

	/**
	 * The list of extra modules and plugins to install
	 *
	 * @var array
	 */
	private $queue = array(
		// plugins => { (folder) => { (element) => (published) }* }*
		'plugins' => array(
			'actionlog' => array('tjvendors' => 1),
			'privacy'   => array('tjvendors' => 1),
			'tjvendors' => array('tjvendors' => 1)
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
	 * Method to run before an install/update/uninstall method
	 *
	 * @param   string            $type    The type of change (install, update or discover_install)
	 * @param   InstallerAdapter  $parent  The class calling this method
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function preflight(string $type, InstallerAdapter $parent): void
	{
		// Payment gateway migration
		$this->updatePaymentGatewayConfig();
	}

	/**
	 * Runs after install, update or discover_update
	 *
	 * @param   string            $type    The type of change (install, update or discover_install)
	 * @param   InstallerAdapter  $parent  The class calling this method
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function postflight(string $type, InstallerAdapter $parent): void
	{
		// Write template file for email template
		$this->_insertTjNotificationTemplates();

		// Add default permissions
		$this->defaultPermissionsFix();

		// Install Layouts
		$this->_addLayout($parent);

		// Install plugins
		$this->_installPlugins($parent);

		// Remove duplicate menu item
		$this->removeDuplicateMenus();
	}

	/**
	 * Method to install the component
	 *
	 * @param   InstallerAdapter  $parent  The class calling this method
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function install(InstallerAdapter $parent): void
	{
		$this->installSqlFiles($parent);
	}

	/**
	 * This method is called after a component is uninstalled.
	 *
	 * @param   InstallerAdapter  $parent  Parent object calling this method
	 *
	 * @return  object  Status object
	 *
	 * @since   1.0
	 */
	public function uninstall(InstallerAdapter $parent)
	{
		$status          = new \stdClass;
		$status->plugins = array();

		$src = $parent->getParent()->getPath('source');

		// Plugins uninstallation
		if (count($this->queue['plugins']))
		{
			foreach ($this->queue['plugins'] as $folder => $plugins)
			{
				if (count($plugins))
				{
					foreach ($plugins as $plugin => $published)
					{
						$query = $this->db->getQuery(true)
							->select($this->db->quoteName('extension_id'))
							->from($this->db->quoteName('#__extensions'))
							->where($this->db->quoteName('type') . ' = ' . $this->db->quote('plugin'))
							->where($this->db->quoteName('element') . ' = ' . $this->db->quote($plugin))
							->where($this->db->quoteName('folder') . ' = ' . $this->db->quote($folder));
						
						$this->db->setQuery($query);

						try
						{
							$id = (int) ($this->db->loadResult() ?? 0);
						}
						catch (\RuntimeException $e)
						{
							$id = 0;
						}

						if ($id)
						{
							$installer         = new Installer();
							$installer->setDatabase($this->db);
							$result            = $installer->uninstall('plugin', $id);
							$status->plugins[] = array(
								'name'   => 'plg_' . $plugin,
								'group'  => $folder,
								'result' => $result,
							);
						}
					}
				}
			}
		}

		return $status;
	}

	/**
	 * This method is called after a component is installed to install plugins.
	 *
	 * @param   InstallerAdapter  $parent  Parent object calling this method
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function _installPlugins(InstallerAdapter $parent): void
	{
		$src = $parent->getParent()->getPath('source');

		$status          = new \stdClass;
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

						try
						{
							$count = (int) ($this->db->loadResult() ?? 0);
						}
						catch (\RuntimeException $e)
						{
							$count = 0;
						}

						$installer = new Installer();
						$installer->setDatabase($this->db);
						$result    = $installer->install($path);

						$status->plugins[] = array(
							'name'   => 'plg_' . $plugin,
							'group'  => $folder,
							'result' => $result
						);

						if ($published && !$count)
						{
							$query = $this->db->getQuery(true)
								->update($this->db->quoteName('#__extensions'))
								->set($this->db->quoteName('enabled') . ' = 1')
								->where($this->db->quoteName('element') . ' = ' . $this->db->quote($plugin))
								->where($this->db->quoteName('folder') . ' = ' . $this->db->quote($folder));
							
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
							}
						}
					}
				}
			}
		}
	}

	/**
	 * Method to update the component
	 *
	 * @param   InstallerAdapter  $parent  The class calling this method
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function update(InstallerAdapter $parent): void
	{
		$this->componentStatus = "update";
		$this->installSqlFiles($parent);

		$check = $this->checkTableExists('tj_vendors');

		if ($check)
		{
			$oldVendorsData = $this->getOldData();

			if (!empty($oldVendorsData))
			{
				$this->updateData();
			}
		}
	}

	/**
	 * Method to install the SQL files
	 *
	 * @param   InstallerAdapter  $parent  The class calling this method
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function installSqlFiles(InstallerAdapter $parent)
	{
		// Create the tables
		$this->runSQL($parent, 'install.mysql.utf8.sql');
	}

	/**
	 * Method to run SQL queries from a file
	 *
	 * @param   InstallerAdapter  $parent   The class calling this method
	 * @param   string            $sqlfile  The SQL file to execute
	 *
	 * @return  boolean  True on success, false on failure
	 *
	 * @since   1.0
	 */
	public function runSQL(InstallerAdapter $parent, string $sqlfile): bool
	{
		// Get the SQL file path from the source directory
		$src = $parent->getParent()->getPath('source');
		$sqlfilePath = $src . '/admin/sql/' . $sqlfile;

		// Check if file exists
		if (!file_exists($sqlfilePath))
		{
			Log::add(
				Text::sprintf('JLIB_INSTALLER_ERROR_SQL_FILENOTFOUND', $sqlfilePath),
				Log::WARNING,
				'jerror'
			);

			return false;
		}

		// Read the file
		$buffer = file_get_contents($sqlfilePath);

		if ($buffer === false)
		{
			Log::add(
				Text::sprintf('JLIB_INSTALLER_ERROR_SQL_READBUFFER'),
				Log::WARNING,
				'jerror'
			);

			return false;
		}

		// Split the SQL file into individual queries - Joomla 6 way
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

		return true;
	}

	/**
	 * Method to migrate the old data
	 *
	 * @return  boolean
	 *
	 * @since   1.0
	 */
	public function updateData(): bool
	{
		$oldVendorsData = $this->getOldData();

		if (!empty($oldVendorsData))
		{
			foreach ($oldVendorsData as $oldData)
			{
				$com_params = ComponentHelper::getParams('com_jticketing');
				$currency   = $com_params->get('currency');

				$newVendorData                = new \stdClass;
				$newVendorData->user_id       = $oldData->user_id;
				$newVendorData->vendor_id     = $oldData->id;
				$newVendorData->state         = 1;
				$newVendorData->vendor_title  = Factory::getUser($oldData->user_id)->name;

				try
				{
					$this->db->insertObject('#__tjvendors_vendors', $newVendorData);
				}
				catch (\RuntimeException $e)
				{
					Log::add(
						Text::sprintf('JLIB_INSTALLER_ERROR_SQL_ERROR', $e->getMessage()),
						Log::WARNING,
						'jerror'
					);
					continue;
				}

				$newXrefData            = new \stdClass;
				$newXrefData->vendor_id = $oldData->id;
				$newXrefData->id        = $oldData->id;
				$newXrefData->client    = 'com_jticketing';

				try
				{
					$this->db->insertObject('#__vendor_client_xref', $newXrefData);
				}
				catch (\RuntimeException $e)
				{
					Log::add(
						Text::sprintf('JLIB_INSTALLER_ERROR_SQL_ERROR', $e->getMessage()),
						Log::WARNING,
						'jerror'
					);
					continue;
				}

				$newFeeData                      = new \stdClass;
				$newFeeData->vendor_id           = $oldData->id;
				$newFeeData->id                  = $oldData->id;
				$newFeeData->client              = 'com_jticketing';
				$newFeeData->currency            = $currency;
				$newFeeData->percent_commission  = $oldData->percent_commission;
				$newFeeData->flat_commission     = $oldData->flat_commission;

				try
				{
					$this->db->insertObject('#__tjvendors_fee', $newFeeData);
				}
				catch (\RuntimeException $e)
				{
					Log::add(
						Text::sprintf('JLIB_INSTALLER_ERROR_SQL_ERROR', $e->getMessage()),
						Log::WARNING,
						'jerror'
					);
					continue;
				}
			}

			return true;
		}

		return false;
	}

	/**
	 * Method to check if a table exists
	 *
	 * @param   string  $table  Table name without prefix
	 *
	 * @return  boolean
	 *
	 * @since   1.0
	 */
	public function checkTableExists(string $table): bool
	{
		$config   = Factory::getConfig();
		$dbname   = $config->get('db');
		$dbprefix = $config->get('dbprefix');

		$query = $this->db->getQuery(true)
			->select($this->db->quoteName('table_name'))
			->from($this->db->quoteName('information_schema.tables'))
			->where($this->db->quoteName('table_schema') . ' = ' . $this->db->quote($dbname))
			->where($this->db->quoteName('table_name') . ' = ' . $this->db->quote($dbprefix . $table));

		$this->db->setQuery($query);

		try
		{
			$check = $this->db->loadResult();
			return !empty($check);
		}
		catch (\RuntimeException $e)
		{
			return false;
		}
	}

	/**
	 * Method to get old data
	 *
	 * @return  array|null
	 *
	 * @since   1.0
	 */
	public function getOldData(): ?array
	{
		$query = $this->db->getQuery(true)
			->select('*')
			->from($this->db->quoteName('#__tj_vendors'));

		$this->db->setQuery($query);

		try
		{
			return $this->db->loadObjectList();
		}
		catch (\RuntimeException $e)
		{
			Log::add(
				Text::sprintf('JLIB_INSTALLER_ERROR_SQL_ERROR', $e->getMessage()),
				Log::WARNING,
				'jerror'
			);
			return null;
		}
	}

	/**
	 * Method to install default email templates
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function _insertTjNotificationTemplates(): void
	{
		$client = 'com_tjvendors';
		Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_tjnotifications/tables');

		$query = $this->db->getQuery(true)
			->select($this->db->quoteName('key'))
			->from($this->db->quoteName('#__tj_notification_templates'))
			->where($this->db->quoteName('client') . ' = ' . $this->db->quote($client));

		$this->db->setQuery($query);

		try
		{
			$existingKeys = $this->db->loadColumn();
		}
		catch (\RuntimeException $e)
		{
			$existingKeys = array();
		}

		BaseDatabaseModel::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_tjnotifications/models');
		$notificationsModel = BaseDatabaseModel::getInstance('Notification', 'TJNotificationsModel');

		$filePath = JPATH_ADMINISTRATOR . '/components/com_tjvendors/tjvendorsTemplate.json';

		if (!file_exists($filePath))
		{
			return;
		}

		$str  = file_get_contents($filePath);
		$json = json_decode($str, true);

		if (!is_array($json) || count($json) == 0)
		{
			return;
		}

		foreach ($json as $template => $array)
		{
			if (!in_array($array['key'], $existingKeys))
			{
				$notificationsModel->createTemplates($array);
			}
			else
			{
				$notificationsModel->updateTemplates($array, $client);
			}
		}
	}

	/**
	 * Add default ACL permissions
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function defaultPermissionsFix(): void
	{
		$columnArray = array('id', 'rules');
		$query = $this->db->getQuery(true)
			->select($this->db->quoteName($columnArray))
			->from($this->db->quoteName('#__assets'))
			->where($this->db->quoteName('name') . ' = ' . $this->db->quote('com_tjvendors'));

		$this->db->setQuery($query);

		try
		{
			$result = $this->db->loadObject();

			if ($result)
			{
				$obj        = new \stdClass;
				$obj->id    = $result->id;
				$obj->rules = '{"core.edit.own":{"1":1,"2":1,"7":1},"core.edit":{"7":0},"core.create":{"7":1,"2":1},"core.delete":{"7":1},"core.edit.state":{"7":1}}';

				$this->db->updateObject('#__assets', $obj, 'id');
			}
		}
		catch (\Exception $e)
		{
			Log::add(
				Text::_('COM_TJVENDORS_DB_EXCEPTION_WARNING_MESSAGE'),
				Log::WARNING,
				'jerror'
			);
		}
	}

	/**
	 * Update payment gateway configuration
	 *
	 * @return  boolean
	 *
	 * @since   1.3.0
	 */
	public function updatePaymentGatewayConfig(): bool
	{
		$config   = Factory::getConfig();
		$dbprefix = $config->get('dbprefix');

		$query = "SHOW TABLES LIKE '" . $dbprefix . "vendor_client_xref'";
		$this->db->setQuery($query);

		try
		{
			$tableExists = $this->db->loadResult();
		}
		catch (\RuntimeException $e)
		{
			return false;
		}

		if (empty($tableExists))
		{
			return false;
		}

		$query = "SHOW COLUMNS FROM `#__vendor_client_xref` LIKE 'payment_gateway'";
		$this->db->setQuery($query);

		try
		{
			$result = $this->db->loadResult();
		}
		catch (\RuntimeException $e)
		{
			return false;
		}

		if (!isset($result))
		{
			return false;
		}

		$query = $this->db->getQuery(true)
			->select('*')
			->from($this->db->quoteName('#__vendor_client_xref'))
			->where($this->db->quoteName('payment_gateway') . ' = ' . $this->db->quote('paypal'), 'OR')
			->where($this->db->quoteName('payment_gateway') . ' = ' . $this->db->quote('adaptive_paypal'));

		$this->db->setQuery($query);

		try
		{
			$vendorList = $this->db->loadObjectList();
		}
		catch (\RuntimeException $e)
		{
			return false;
		}

		foreach ($vendorList as $key => $value)
		{
			$param1                    = new \stdClass;
			$param1->payment_gateways  = $value->payment_gateway;

			$param2 = json_decode($value->params);

			$params = (object) array_merge((array) $param1, (array) $param2);

			$paymentArray                     = array();
			$paymentArray['payment_gateway0'] = $params;
			$paymentArrayList                 = array();
			$paymentArrayList['payment_gateway'] = $paymentArray;

			$vendorParams = json_encode($paymentArrayList);

			$vendorData            = new \stdClass;
			$vendorData->id        = $value->id;
			$vendorData->vendor_id = $value->vendor_id;
			$vendorData->params    = $vendorParams;

			try
			{
				$this->db->updateObject('#__vendor_client_xref', $vendorData, 'id');
			}
			catch (\RuntimeException $e)
			{
				Log::add(
					Text::sprintf('JLIB_INSTALLER_ERROR_SQL_ERROR', $e->getMessage()),
					Log::WARNING,
					'jerror'
				);
			}
		}

		return true;
	}

	/**
	 * Add subform layout for payment form
	 *
	 * @param   InstallerAdapter  $parent  The class calling this method
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	private function _addLayout(InstallerAdapter $parent): void
	{
		$src = $parent->getParent()->getPath('source');
		$vendorSubformLayouts = $src . "/layouts/com_tjvendors";

		if (Folder::exists(JPATH_SITE . '/layouts/com_tjvendors'))
		{
			Folder::delete(JPATH_SITE . '/layouts/com_tjvendors');
		}

		if (is_dir($vendorSubformLayouts))
		{
			Folder::copy($vendorSubformLayouts, JPATH_SITE . '/layouts/com_tjvendors');
		}
	}

	/**
	 * Remove duplicate menu items
	 *
	 * @return  void
	 *
	 * @since   2.3.0
	 */
	public function removeDuplicateMenus(): void
	{
		$table = new MenuTable($this->db);

		$query = $this->db->getQuery(true)
			->select($this->db->quoteName('id'))
			->from($this->db->quoteName('#__menu'))
			->where($this->db->quoteName('menutype') . ' = ' . $this->db->quote('main'))
			->where($this->db->quoteName('path') . ' IN (' . $this->db->quote('com-tjvendors-tjnotifications-menu') . ', ' . $this->db->quote('com_tjvendors_tjnotifications_menu') . ')');

		$this->db->setQuery($query);

		try
		{
			$data = $this->db->loadObjectList();

			foreach ($data as $key => $menuItem)
			{
				$table->delete($menuItem->id);
			}
		}
		catch (\RuntimeException $e)
		{
			Log::add(
				Text::sprintf('JLIB_INSTALLER_ERROR_SQL_ERROR', $e->getMessage()),
				Log::WARNING,
				'jerror'
			);
		}
	}
}