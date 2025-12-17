<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @copyright  Copyright (C) 2005 - 2025. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * Shika is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Language\Text;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Installer\InstallerAdapter;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Log\Log;
use Joomla\Database\DatabaseInterface;

/**
 * JTicketing Installer
 *
 * @since  1.0.0
 */
class Pkg_jticketingInstallerScript
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

	/** @var array The list of extra modules and plugins to install */
	private array $installation_queue = array(
		// @modules => { (folder) => { (module) => { (position), (published) } }* }*
		'modules' => array(
			'admin' => array(),
			'site' => array(
				'mod_jticketing_buy'       => 0,
				'mod_jticketing_event'     => 0,
				'mod_jticketing_menu'      => 0,
				'mod_jticketing_calendar'  => 0
			)
		),

		// @plugins => { (folder) => { (element) => (published) }* }*
		'plugins' => array(
			'actionlog' => array(
				'jticketing' => 1
			),
			'api' => array(
				'jticket' => 1,
				'jlike'   => 1
			),
			'community' => array(
				'addfields' => 0
			),
			'content' => array(
				'jlike_events' => 1,
				'tjintegration' => 0,
				'eventinfo' => 1,
				'schema_events' => 1
			),
			'jevents' => array(
				'addfields' => 0
			),
			'jticketingtax' => array(
				'jticketing_tax_default' => 0
			),
			'payment' => array(
				'2checkout'       => 0,
				'alphauserpoints' => 0,
				'authorizenet'    => 1,
				'bycheck'         => 1,
				'byorder'         => 1,
				'ccavenue'        => 0,
				'jomsocialpoints' => 0,
				'linkpoint'       => 1,
				'paypal'          => 1,
				'paypalpro'       => 0,
				'payu'            => 1,
				'amazon'          => 0,
				'ogone'           => 0,
				'paypal_adaptive_payment' => 0,
				'razorpay' => 0
			),
			'privacy' => array(
				'jticketing' => 1
			),
			'search' => array(
				'jtevents' => 1
			),
			'system' => array(
				'jticketing_j3'        => 1,
				'plug_sys_jticketing'  => 1,
				'tjassetsloader'       => 1,
				'jticketingactivities' => 1,
				'tjupdates'            => 1,
				'tjanalytics'          => 0
			),
			'tjevents' => array(
				'plug_tjevents_adobeconnect' => 1,
				'zoom' => 1,
				'jitsi' => 1,
				'jaas' => 1,
				'googlemeet' => 1,
			),
			'tjlmsdashboard' => array(
				'eventlist' => 1,
			),
			'tjsms' => array(
				'twilio' => 0,
				'mvaayoo' => 0
			),
			'tjreports' => array(
				'attendeereport'      => 1,
				'customerreport'      => 1,
				'eventcategoryreport' => 1,
				'eventreport'         => 1,
				'salesreport'         => 1
			),
			'jticketing' => array(
				'rsform'          => 0,
				'tjlesson'        => 0,
				'esgroup'         => 0,
				'jsgroup'         => 0,
				'joomlausergroup' => 0,
				'jteventreminder' => 0
			),
			'tjurlshortner' => array(
				'bitly'   => 0
			),
			'user' => array(
				'tjnotificationsmobilenumber' => 0
			),
			'finder' => array(
				'jticketingevents' => 0
			),
		),

		'applications' => array(
			'easysocial' => array(
				'jticketMyEvents'       => 0,
				'jticket_boughttickets' => 0,
				'jticketingtickettypes' => 1
			)
		),

		'libraries' => array(
			'activity'   => 1,
			'techjoomla' => 1,
			'lib_tjbarcode' => 1
		),

		'dynamics' => array(
			'jticketing' => 1
		)
	);

	/** @var array Obsolete files and folders to remove*/
	private array $removeFilesAndFolders = array(
		'files' => array(
			'administrator/components/com_jticketing/views/orders/tmpl/all.php',
			'administrator/components/com_jticketing/views/orders/tmpl/my.php',
			'administrator/components/com_jticketing/views/orders/tmpl/my.xml',
			'administrator/components/com_jticketing/views/events/tmpl/all_list.php',
			'administrator/components/com_jticketing/views/events/tmpl/create.php',
			'administrator/components/com_jticketing/views/events/tmpl/single.php',
			'plugins/payment/authorizenet/authorizenet/com_jticketing_authorizenet.log',
			'plugins/payment/2checkout/2checkout/com_jticketing_2checkout.log',
			'plugins/payment/alphauserpoints/alphauserpoints/com_jticketing_alphauserpoints.log',
			'plugins/payment/ccavenue/ccavenue/com_jticketing_ccavenue.log',
			'plugins/payment/ogone/ogone/com_jticketing_ogone.log',
			'plugins/payment/paypal/paypal/com_jticketing_paypal.log',
			'plugins/payment/amazon/amazon/com_jticketing_amazon.log',
			'plugins/payment/paypalpro/paypalpro/com_jticketing_paypalpro.log',
			'plugins/payment/payu/payu/com_jticketing_payu.log',
			'plugins/payment/transfirst/transfirst/com_jticketing_transfirst.log',
			'components/com_jticketing/views/event/tmpl/default_location.php',
			'components/com_jticketing/views/events/tmpl/eventpin.php',
			'components/com_jticketing/views/events/tmpl/filters.php',
			'components/com_jticketing/views/eventform/tmpl/default_attendeefields.php',
			'components/com_jticketing/views/eventform/tmpl/default_tickettypes.php',
			'components/com_jticketing/assets/js/fblike.js',
			'components/com_jticketing/assets/js/masonry.pkgd.min.js',
			'plugins/payment/razorpay/razorpay/com_jticketing_razorpay.log',
		),
		'folders' => array(
			'components/com_jticketing/bootstrap',
			'components/com_jticketing/views/createevent',
			'components/com_jticketing/views/checkout',
			'components/com_jticketing/views/eventl',
			'components/com_jticketing/views/buy',
			'components/com_jticketing/views_bs2',
			'components/com_jticketing/models/createevent',
			'components/com_jticketing/models/eventl',
			'components/com_jticketing/helpers/dompdf/lib/ttf2ufm',
			'administrator/components/com_jticketing/views/dashboard',
			'administrator/components/com_jticketing/views/settings',
			'administrator/components/com_jticketing/models/settings',
			'administrator/components/com_jticketing/controllers/settings',
		)
	);

	/**
	 * method to run before an install/update/uninstall method
	 *
	 * @param   string            $type    install, update or discover_update
	 * @param   InstallerAdapter  $parent  Parent installer adapter
	 *
	 * @return void
	 */
	public function preflight(string $type, InstallerAdapter $parent): void
	{
		// Add version checks here if you want
	}

	/**
	 * Runs after install, update or discover_update
	 *
	 * @param   string            $type    install, update or discover_update
	 * @param   InstallerAdapter  $parent  Parent installer adapter
	 *
	 * @return void
	 */
	public function postflight(string $type, InstallerAdapter $parent): void
	{
		// Install subextensions
		$status = $this->_installSubextensions($parent);

		// Remove obsolete files and folders
		try
		{
			$this->_removeObsoleteFilesAndFolders($this->removeFilesAndFolders);
		}
		catch (Exception $e)
		{
			Log::add('JTicketing: Error removing obsolete files - ' . $e->getMessage(), Log::WARNING, 'com_jticketing');
		}

		echo '<br/>' . '
		<div align="center"> <div class="alert alert-success" style="background-color:#DFF0D8;border-color:#D6E9C6;color: #468847;padding: 8px;"> <div style="font-weight:bold;"></div> <h4><a href="https://techjoomla.com/table/extension-documentation/documentation-for-jticketing/" target="_blank">' . Text::_('COM_JTICKETING_PRODUCT_DOC') . '</a> | <a href="https://techjoomla.com/documentation-for-jticketing/jticketing-faqs.html" target="_blank">' . Text::_('COM_JTICKETING_PRODUCT_FAQ') . '</a></h4> </div> </div>';

		// Install Techjoomla Straper
		$straperStatus = $this->_installStraper($parent);

		// Show the post-installation page
		$this->_renderPostInstallation($straperStatus, $status, $parent);

		if ($type == 'install')
		{
			echo '<p><strong style="color:green">' . Text::_('COM_JTICKETING_' . $type . '_TEXT') . '</p></strong>';
		}
		else
		{
			echo '<p><strong style="color:green">' . Text::_('COM_JTICKETING_' . $type . '_TEXT') . '</p></strong>';
		}
		$this->_addLayout($parent);

		// Install default reports from tjreports extension
		$this->_installDefaultTJReportsPlugin();
	}

	/**
	 * method to install the component
	 *
	 * @param   InstallerAdapter  $parent  Parent installer adapter
	 *
	 * @return void
	 */
	public function install(InstallerAdapter $parent): void
	{
		// $parent is the class calling this method
	}

	/**
	 * method to uninstall the component
	 *
	 * @param   InstallerAdapter  $parent  Parent installer adapter
	 *
	 * @return void
	 */
	public function uninstall(InstallerAdapter $parent): void
	{
		?>
		<?php $rows = 1;?>
		<table class="table-condensed table">
			<thead>
				<tr>
					<th class="title" colspan="2">Extension</th>
					<th width="30%">Status</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="3"></td>
				</tr>
			</tfoot>
			<tbody>
				<tr class="row0">
					<td class="key" colspan="2"><h4>TjFields component</h4></td>
					<td><strong style="color: red">Uninstalled</strong></td>
				</tr>
			</tbody>
		</table>
		<?php
	}

	/**
	 * Method to update the component
	 *
	 * @param   InstallerAdapter  $parent  Parent installer adapter
	 *
	 * @return void
	 */
	public function update(InstallerAdapter $parent): void
	{
		$config  = Factory::getConfig();
		$configdb = $config->get('db');
		$dbprefix = $config->get('dbprefix');

		// Add any DB migration logic here if needed
	}

	/**
	 * Installs TechJoomla Strapper
	 *
	 * @param   InstallerAdapter  $parent  Parent installer adapter
	 *
	 * @return  array  Strapper installation status
	 */
	private function _installStraper(InstallerAdapter $parent): array
	{
		$src = $parent->getParent()->getPath('source');

		// Install the FOF framework
		$source = $src . '/tj_strapper';
		$target = JPATH_ROOT . '/media/techjoomla_strapper';

		$haveToInstallStraper = false;

		if (!file_exists($target))
		{
			$haveToInstallStraper = true;
		}
		else
		{
			$straperVersion = array();

			if (File::exists($target . '/version.txt'))
			{
				$rawData                     = file_get_contents($target . '/version.txt');
				$info                        = explode("\n", $rawData);
				$straperVersion['installed'] = array(
					'version' => trim($info[0]),
					'date'    => new Date(trim($info[1])),
				);
			}
			else
			{
				$straperVersion['installed'] = array(
					'version' => '0.0',
					'date'    => new Date('2011-01-01'),
				);
			}

			$rawData                   = file_get_contents($source . '/version.txt');
			$info                      = explode("\n", $rawData);
			$straperVersion['package'] = array(
				'version' => trim($info[0]),
				'date'    => new Date(trim($info[1])),
			);

			$haveToInstallStraper = $straperVersion['package']['date']->toUnix() > $straperVersion['installed']['date']->toUnix();
		}

		$installedStraper = false;

		if ($haveToInstallStraper)
		{
			$versionSource    = 'package';
			$installer        = new Installer();
			$installer->setDatabase($this->db);
			$installedStraper = $installer->install($source);
		}
		else
		{
			$versionSource = 'installed';
		}

		if (!isset($straperVersion))
		{
			$straperVersion = array();

			if (File::exists($target . '/version.txt'))
			{
				$rawData                     = file_get_contents($target . '/version.txt');
				$info                        = explode("\n", $rawData);
				$straperVersion['installed'] = array(
					'version' => trim($info[0]),
					'date'    => new Date(trim($info[1])),
				);
			}
			else
			{
				$straperVersion['installed'] = array(
					'version' => '0.0',
					'date'    => new Date('2011-01-01'),
				);
			}

			$rawData                   = file_get_contents($source . '/version.txt');
			$info                      = explode("\n", $rawData);
			$straperVersion['package'] = array(
				'version' => trim($info[0]),
				'date'    => new Date(trim($info[1])),
			);
			$versionSource             = 'installed';
		}

		if (!($straperVersion[$versionSource]['date'] instanceof Date))
		{
			$straperVersion[$versionSource]['date'] = new Date();
		}

		return array(
			'required'  => $haveToInstallStraper,
			'installed' => $installedStraper,
			'version'   => $straperVersion[$versionSource]['version'],
			'date'      => $straperVersion[$versionSource]['date']->format('Y-m-d'),
		);
	}

	/**
	 * Installs subextensions (modules, plugins) bundled with the main extension
	 *
	 * @param   InstallerAdapter  $parent  Parent installer adapter
	 *
	 * @return  \stdClass  The subextension installation status
	 */
	private function _installSubextensions(InstallerAdapter $parent): \stdClass
	{
		$src = $parent->getParent()->getPath('source');

		$status          = new \stdClass();
		$status->modules = array();
		$status->plugins = array();
		$status->libraries = array();
		$status->applications = array();
		$status->dynamics = array();

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
							continue;
						}

						// Was the module already installed?
						$sql = $this->db->getQuery(true)
							->select('COUNT(*)')
							->from($this->db->quoteName('#__modules'))
							->where($this->db->quoteName('module') . ' = ' . $this->db->quote('mod_' . $module));
						$this->db->setQuery($sql);
						$count = (int) ($this->db->loadResult() ?? 0);

						$installer = new Installer();
						$installer->setDatabase($this->db);
						$result    = $installer->install($path);

						$status->modules[] = array(
							'name'   => 'mod_' . $module,
							'client' => $folder,
							'result' => $result,
						);

						// Modify where it's published and its published state
						if (!$count)
						{
							// A. Position and state
							list($modulePosition, $modulePublished) = $modulePreferences;

							if ($modulePosition === 'cpanel')
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
							if ($folder === 'admin')
							{
								$query = $this->db->getQuery(true);
								$query->select('MAX(' . $this->db->quoteName('ordering') . ')')
									->from($this->db->quoteName('#__modules'))
									->where($this->db->quoteName('position') . ' = ' . $this->db->quote($modulePosition));
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
							$query->select($this->db->quoteName('id'))
								->from($this->db->quoteName('#__modules'))
								->where($this->db->quoteName('module') . ' = ' . $this->db->quote('mod_' . $module));
							$this->db->setQuery($query);
							$moduleid = (int) ($this->db->loadResult() ?? 0);

							$query = $this->db->getQuery(true);
							$query->select('*')
								->from($this->db->quoteName('#__modules_menu'))
								->where($this->db->quoteName('moduleid') . ' = ' . $this->db->quote($moduleid));
							$this->db->setQuery($query);
							$assignments = $this->db->loadObjectList();
							$isAssigned  = !empty($assignments);

							if (!$isAssigned)
							{
								$o = (object) array(
									'moduleid' => $moduleid,
									'menuid'   => 0,
								);
								$this->db->insertObject('#__modules_menu', $o);
							}
						}
					}
				}
			}
		}

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
						$query = $this->db->getQuery(true)
							->select('COUNT(*)')
							->from($this->db->quoteName('#__extensions'))
							->where($this->db->quoteName('element') . ' = ' . $this->db->quote($plugin))
							->where($this->db->quoteName('folder') . ' = ' . $this->db->quote($folder));
						$this->db->setQuery($query);
						$count = (int) ($this->db->loadResult() ?? 0);

						$installer = new Installer();
						$installer->setDatabase($this->db);
						$result    = $installer->install($path);

						$status->plugins[] = array(
							'name'   => 'plg_' . $plugin,
							'group'  => $folder,
							'result' => $result,
						);

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

		// Libraries installation
		if (isset($this->installation_queue['libraries']) && count($this->installation_queue['libraries']))
		{
			foreach ($this->installation_queue['libraries'] as $folder => $status1)
			{
				$path = "$src/libraries/$folder";

				if (file_exists($path))
				{
					$query = $this->db->getQuery(true)
						->select('COUNT(*)')
						->from($this->db->quoteName('#__extensions'))
						->where(
							'( ' .
							$this->db->quoteName('name') . ' = ' . $this->db->quote($folder) .
							' OR ' .
							$this->db->quoteName('element') . ' = ' . $this->db->quote($folder) .
							' )'
						)
						->where($this->db->quoteName('type') . ' = ' . $this->db->quote('library'));
					$this->db->setQuery($query);
					$count = (int) ($this->db->loadResult() ?? 0);

					$installer = new Installer();
					$installer->setDatabase($this->db);
					$result    = $installer->install($path);

					$status->libraries[] = array(
						'name'   => $folder,
						'group'  => $folder,
						'result' => $result,
						'status' => $status1,
					);

					if ($status1 && !$count)
					{
						$query = $this->db->getQuery(true)
							->update($this->db->quoteName('#__extensions'))
							->set($this->db->quoteName('enabled') . ' = ' . $this->db->quote('1'))
							->where(
								'( ' .
								$this->db->quoteName('name') . ' = ' . $this->db->quote($folder) .
								' OR ' .
								$this->db->quoteName('element') . ' = ' . $this->db->quote($folder) .
								' )'
							)
							->where($this->db->quoteName('type') . ' = ' . $this->db->quote('library'));
						$this->db->setQuery($query);
						$this->db->execute();
					}
				}
			}
		}

		// Application Installations
		if (count($this->installation_queue['applications']))
		{
			foreach ($this->installation_queue['applications'] as $folder => $applications)
			{
				if (count($applications))
				{
					foreach ($applications as $app => $published)
					{
						$path = "$src/applications/$folder/$app";

						if (!is_dir($path))
						{
							$path = "$src/applications/$folder/plg_$app";
						}
						if (!is_dir($path))
						{
							$path = "$src/applications/$app";
						}
						if (!is_dir($path))
						{
							$path = "$src/applications/plg_$app";
						}
						if (!is_dir($path))
						{
							continue;
						}

						if (file_exists(JPATH_ADMINISTRATOR . '/components/com_easysocial/includes/installer/installer.php'))
						{
							require_once JPATH_ADMINISTRATOR . '/components/com_easysocial/includes/installer/installer.php';

							$installer = new SocialInstaller;
							// The $path here refers to your application path
							$installer->load($path);
							$result = $installer->install();
							$status->applications[] = array(
								'name'   => $app,
								'group'  => $folder,
								'result' => $result,
								'status' => $published,
							);
						}
					}
				}
			}
		}

		// Dynamics Installations
		if (count($this->installation_queue['dynamics']))
		{
			foreach ($this->installation_queue['dynamics'] as $folder => $dynamics)
			{
				if ($dynamics)
				{
					$path = "$src/dynamics/$folder";
					// check sorce and destination folder is exist
					if (is_dir($path) && is_dir(JPATH_ADMINISTRATOR . "/components/com_acym/dynamics"))
					{
						$srcPath = "$src/dynamics/$folder";
						$dst     = JPATH_ADMINISTRATOR . "/components/com_acym/dynamics/jticketing";

						// Check folder is exist or not
						if (!is_dir($dst))
						{
							mkdir($dst, 0755, true);  // Create the destination folder with necessary permissions
						}

						// delete old files if exist
						$files = array_diff(scandir($dst), array('.', '..'));  // Get all files except . and ..
						foreach ($files as $file)
						{
							$filePath = $dst . '/' . $file;
							unlink($filePath);  // Delete the file
						}

						// Copy new files
						$files = array_diff(scandir($srcPath), array('.', '..'));  // Get all files except . and ..
						foreach ($files as $file)
						{
							$srcFilePath = $srcPath . '/' . $file;
							$dstFilePath = $dst . '/' . $file;

							copy($srcFilePath, $dstFilePath);  // Copy the file
						}

						$status->dynamics[] = array(
							'name'   => $folder,
							'group'  => "Acymailing",
							'result' => true,
							'status' => 1,
						);
					}
				}
			}
		}

		return $status;
	}

	/**
	 * Removes obsolete files and folders
	 *
	 * @param   array  $removeFilesAndFolders  Contain File and folter which has to remove
	 *
	 * @return void
	 */
	private function _removeObsoleteFilesAndFolders($removeFilesAndFolders): void
	{
		// Remove files
		if (!empty($removeFilesAndFolders['files']))
		{
			foreach ($removeFilesAndFolders['files'] as $file)
			{
				$f = JPATH_ROOT . '/' . $file;

				if (!File::exists($f))
				{
					continue;
				}

				File::delete($f);
			}
		}

		// Remove folders
		if (!empty($removeFilesAndFolders['folders']))
		{
			foreach ($removeFilesAndFolders['folders'] as $folder)
			{
				$f = JPATH_ROOT . '/' . $folder;

				if (!file_exists($f))
				{
					continue;
				}

				Folder::delete($f);
			}
		}
	}

	/**
	 * Renders the post-installation message
	 *
	 * @param   array             $straperStatus  Strapper installation status
	 * @param   \stdClass         $status         Installation status
	 * @param   InstallerAdapter  $parent         Parent installer adapter
	 *
	 * @return void
	 */
	private function _renderPostInstallation(array $straperStatus, \stdClass $status, InstallerAdapter $parent): void
	{
		$document = Factory::getDocument();
		?>
		<?php $rows = 1;?>

		<link rel="stylesheet" type="text/css" href=""/>
		<div class="techjoomla-bootstrap" >
		<div class="alert alert-info">
			<div class="row-fluid">
				<strong>In order to complete the Update please make sure you do ALL the following steps.</strong>
			</div>
			<div class="row-fluid">
				1.Go to the JTicketing Control Panel and look for the <strong>Migrate Data</strong> button in the top left corner. Click that. If all goes well you should get various success messages.
			</div>
			<div class="row-fluid">
				2.If you had setup the Reminder cron replace it with the new Jlike reminder cron.
			</div>
			<div class="row-fluid">
				3. A lot of the HTML in this release has been optimised and rewritten introducing structural changes and new elements on most of the pages. Any overrides you have done should be reviewed and redone for the extension to work correctly and to get full benefit of new features. To see the new UI you need to remove your overrides.
			</div>
		</div>
		<div class="techjoomla-bootstrap" >
		<table class="table-condensed table">
			<thead>
				<tr>
					<th class="title" colspan="2">Extension</th>
					<th width="30%">Status</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="3"></td>
				</tr>
			</tfoot>
			<tbody>
				<tr class="row0">
					<td class="key" colspan="2">
						<strong>TechJoomla Strapper <?php echo $straperStatus['version']; ?></strong> [<?php echo $straperStatus['date']; ?>]
					</td>
					<td><strong>
						<span style="color: <?php echo $straperStatus['required'] ? ($straperStatus['installed'] ? 'green' : 'red') : '#660'; ?>; font-weight: bold;">
							<?php echo $straperStatus['required'] ? ($straperStatus['installed'] ? 'Installed' : 'Not Installed') : 'Already up-to-date'; ?>
						</span>
					</strong></td>
				</tr>
				<tr class="row0">
					<td class="key" colspan="2"><h4>TjFields component</h4></td>
					<td><strong style="color: green">Installed</strong></td>
				</tr>
				<tr class="row0">
					<td class="key" colspan="2">JTicketing component</td>
					<td><strong style="color: green">Installed</strong></td>
				</tr>
				<tr class="row0">
					<td class="key" colspan="2"><h4>TjActivityStream component</h4></td>
					<td><strong style="color: green">Installed</strong></td>
				</tr>

				<?php if (count($status->modules)) : ?>
				<tr>
					<th>Module</th>
					<th>Client</th>
					<th></th>
				</tr>
				<?php foreach ($status->modules as $module) : ?>
				<tr class="row<?php echo ($rows++ % 2); ?>">
					<td class="key"><?php echo $module['name']; ?></td>
					<td class="key"><?php echo ucfirst($module['client']); ?></td>
					<td><strong style="color: <?php echo ($module['result']) ? "green" : "red";?>"><?php echo ($module['result']) ? 'Installed' : 'Not installed';?></strong></td>
				</tr>
				<?php endforeach;?>
				<?php endif;?>
				<?php if (count($status->plugins)) : ?>
				<tr>
					<th>Plugin</th>
					<th>Group</th>
					<th></th>
				</tr>
				<?php foreach ($status->plugins as $plugin) : ?>
				<tr class="row<?php echo ($rows++ % 2);?>">
					<td class="key"><?php echo ucfirst($plugin['name']);?></td>
					<td class="key"><?php echo ucfirst($plugin['group']);?></td>
					<td><strong style="color: <?php echo ($plugin['result']) ? "green" : "red";?>"><?php echo ($plugin['result']) ? 'Installed' : 'Not installed';?></strong></td>
				</tr>
				<?php endforeach; ?>
				<?php endif; ?>
				<?php if (!empty($status->libraries) && count($status->libraries)) : ?>
				<tr class="row1">
					<th>Library</th>
					<th></th>
					<th></th>
					</tr>
				<?php foreach ($status->libraries as $libraries) : ?>
				<tr class="row2 <?php //echo ($rows++ % 2); ?>">
					<td class="key"><?php echo ucfirst($libraries['name']);?></td>
					<td class="key"></td>
					<td><strong style="color: <?php echo ($libraries['result']) ? "green" : "red";?>"><?php echo ($libraries['result']) ? 'Installed' : 'Not installed';?></strong>
					<?php
						if (!empty($libraries['result'])) // if installed then only show msg
						{
							echo $mstat = ($libraries['status'] ? "<span class=\"label label-success\">Enabled</span>" : "<span class=\"label label-important\">Disabled</span>");
						}
					?>
					</td>
				</tr>
				<?php endforeach;?>
				<?php endif;?>

				<?php if (!empty($status->applications) && count($status->applications)) : ?>
				<tr class="row1">
					<th>EasySocial App</th>
					<th></th>
					<th></th>
					</tr>
				<?php foreach ($status->applications as $app_install) : ?>
				<tr class="row2 <?php  ?>">
					<td class="key"><?php echo ucfirst($app_install['name']);?></td>
					<td class="key"></td>
					<td><strong style="color: <?php echo ($app_install['result']) ? "green" : "red";?>"><?php echo ($app_install['result']) ? 'Installed' : 'Not installed';?></strong>
					<?php
						if (!empty($app_install['result'])) // if installed then only show msg
						{
							echo $mstat = ($app_install['status'] ? "<span class=\"label label-success\">Enabled</span>" : "<span class=\"label label-important\">Disabled</span>");
						}
					?>
					</td>
				</tr>
				<?php endforeach;?>
				<?php endif;?>

			</tbody>
		</table>

	</div>
		<?php
	}

	/**
	 * Adds the overridden subform layout files
	 *
	 * @param   InstallerAdapter  $parent  Parent installer adapter
	 *
	 * @return void
	 */
	public function _addLayout(InstallerAdapter $parent): void
	{
		$src = $parent->getParent()->getPath('source');
		$JTsubformlayouts = $src . "/layouts/JTsubformlayouts";

		if (Folder::exists(JPATH_SITE . '/layouts/JTsubformlayouts/layouts'))
		{
			Folder::delete(JPATH_SITE . '/layouts/JTsubformlayouts/layouts');
		}

		Folder::copy($JTsubformlayouts, JPATH_SITE . '/layouts/JTsubformlayouts/layouts');
	}

	/**
	 * Install default tjReports
	 * and install them with extension
	 *
	 * @return  int  Number of installed reports (if available)
	 */
	public function _installDefaultTJReportsPlugin(): int
	{
		$installed = 0;

		try
		{
			$app        = Factory::getApplication();
			$component  = $app->bootComponent('com_tjreports');
			$mvcFactory = $component->getMVCFactory();
			$model      = $mvcFactory->createModel('Reports', 'Administrator', ['ignore_request' => true]);

			if ($model && method_exists($model, 'addTjReportsPlugins'))
			{
				$installed = (int) $model->addTjReportsPlugins();
			}
		}
		catch (\Throwable $e)
		{
			// com_tjreports not installed or other issue; fail silently
		}

		return $installed;
	}
}
