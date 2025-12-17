
<?php
/**
 *  @package AdminTools
 *  @copyright Copyright (c)2010-2011 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 *  @version $Id$
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
defined('_JEXEC') or die();
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Installer\InstallerAdapter;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseInterface;

if (!defined('DS'))
{
	define('DS', DIRECTORY_SEPARATOR);
}
class com_jlikeInstallerScript
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
	// @private $oldversion="";
	private $installation_queue = array(
		// modules => { (folder) => { (module) => { (position), (published) } }* }*
		'modules' => array(
			'admin' => array(

			),
			'site' => array(
				'mod_jlike_most_likes'   => array('position-7', 0),
				'mod_jlike_recent_likes' => array('position-7', 0),
			),
		),
		// plugins => { (folder) => { (element) => (published) }* }*
		'plugins' => array(
			'content' => array(
				'articles'       => 0,
				'communitypolls' => 0,
				'easyblog'       => 0,
				'easydiscuss'    => 0,
				'flexicontent'   => 0,
				'jevents'        => 0,
				'ohanah'         => 0,
				'paths'          => 0,
				'phocagallery'   => 0,
				'redshop'        => 0,
				'virtuemart'     => 0,
			),
			'api' => array(
				'jlike' => 1,
			),
			'community' => array(
				'jomsocial' => 0,
			),
			'hikashop' => array(
				'hikashop' => 0,
			),
			'k2' => array(
				'k2' => 0,
			),
			'kunena' => array(
				'kunena' => 0,
			),
			'jcomments' => array(
				'jcomments' => 0,
			),
			'komento' => array(
				'komento' => 0,
			),
			'system' => array(
				'sobipro'        => 0,
				'api'            => 0,
				'tjassetsloader' => 1,
				'todo_redirect'  => 0,
				'tjupdates'      => 1,
			),
			'privacy' => array(
				'jlike' => 1,
			),
			'actionlog' => array(
				'jlike' => 1,
			),
		),
		'libraries' => array(
			'activity'   => 1,
			'techjoomla' => 1,
		),
	);

	private $uninstall_queue = array(
		// modules => { (folder) => { (module) => { (position), (published) } }* }*
		'modules' => array(
			'admin' => array(

			),
			'site' => array(
				'jlike_most_likes'   => array('position-7', 0),
				'jlike_recent_likes' => array('position-7', 0),
			),
		),
		// plugins => { (folder) => { (element) => (published) }* }*
		'plugins' => array(
			'content' => array(
				'jlike_easyblog'     => 0,
				'jlike_easydiscuss'  => 0,
				'jlike_flexicontent' => 0,
				'jlike_jevents'      => 0,
				'jlike_articles'     => 0,
				'jlike_phocagallery' => 0,
				'jlike_redshop'      => 0,
				'jlike_virtuemart'   => 0,
			),
			'community' => array(
				'jlike_jomsocial' => 0,
			),
			'hikashop' => array(
				'jlike_hikashop' => 0,
			),
			'k2' => array(
				'jlike_k2' => 0,
			),
			'kunena' => array(
				'jlike_kunena' => 0,
			),
			'jcomments' => array(
				'jlike_jcomments' => 0,
			),
			'komento' => array(
				'jlike_komento' => 0,
			),
			'system' => array(
				'jlike_sobipro' => 0,
				'jlike_api'     => 0,
			),
			'privacy' => array(
				'jlike' => 0,
			),
			'actionlog' => array(
				'jlike' => 0,
			),
		),
	);

	/** @var array The list of obsolete extra modules and plugins to uninstall when upgrading the component */
	private $obsolete_extensions_uninstallation_que = array(
		// modules => { (folder) => { (module) }* }*
		'modules' => array(
			'admin' => array(
			),
			'site' => array(
			),
		),
		// plugins => { (folder) => { (element) }* }*
		'plugins' => array(
			'system' => array(
				'jlike_sys_plugin',
			),
		),
	);

	/** @var array Obsolete files and folders to remove */
	private $removeFilesAndFolders = array(
		'files'	 => array(
			'administrator/components/com_jlike/views/dashboard/tmpl/default.php',
			'components/com_jlike/assets/scripts/jquery-1.7.1.min.js',
		),
		'folders' => array(
		),
	);

	/**
	 * method to run before an install/update/uninstall method
	 *
	 * @param string $type
	 * @param mixed $parent
	 * @return void
	 */
	public function preflight(string $type, /** @scrutinizer ignore-unused */ $parent): void
	{
		// $parent is the class calling this method
		// $type is the type of change (install, update or discover_install)
	}

	public function install(/** @scrutinizer ignore-unused */ $parent): void
	{
		$this->migratelikes();
	}

	public function migratelikes()
	{
		if (Folder::exists(JPATH_ROOT . '/' . 'components' . '/' . 'com_community') || Folder::exists(JPATH_ROOT . '/' . 'components' . '/' . 'com_jomlike'))
		{
			?>
				<link rel="stylesheet" type="text/css" href="<?php echo Uri::root() . 'media/techjoomla_strapper/css/bootstrap.min.css'; ?>"/>
				<script src="<?php echo Uri::root() . 'components/com_jlike/assets/scripts/jquery-1.7.1.min.js'; ?>" type="text/javascript"></script>
				<script language="JavaScript">
					function migrateoldlikes(success_msg,error_msg)
						{
							jQuery.ajax({
															url: 'index.php?option=com_jlike&tmpl=component&task=migrateLikes&tmpl=component',
															type: 'POST',
															dataType: 'json',
															timeout: 3500,
															error: function(){
																jQuery('#migrate_msg').css("display", "block");
																jQuery('#migrate_msg').addClass("alert alert-error");
																jQuery('#migrate_msg').text(error_msg);
															},
															beforeSend: function(){
																jQuery('#jlike-loading-image').show();
															},
															complete: function(){
																jQuery('#jlike-loading-image').hide();
															},
															success: function(response)
															{
																		jQuery('#migrate_msg').css("display", "block");
																		jQuery('#migrate_msg').addClass("alert alert-success");
																		jQuery('#migrate_msg').text(success_msg);
																		jQuery('#migrate_button').css("display", "none");
															}
							});

						}

				</script>
				<div class="techjoomla-bootstrap" >
						<div class="well well-large center">
								<?php
								// $limit_populate_link=Route::_(Uri::base().'index.php?option=com_jlike&tmpl=component&task=migrateLikes');
								?>
									<div class="alert" id="migrate_msg" style='display:none'></div>
									<div>
										<div class='jlike-loading-image' style="background: url('<?php echo Uri::root() . '/' . 'components' . '/' . 'com_jlike/assets/images/ajax-loading.gif'; ?>') no-repeat scroll 0 0 transparent"></div>
										<button class="btn btn-success" style="margin-top:20px;" id="migrate_button" onclick="migrateoldlikes('<?php echo Text::_('Data successfully migrated!!'); ?>','<?php echo Text::_('There is some error while migrating your data!'); ?>')"><?php echo Text::_('Migrate old Likes data to Jlike'); ?></button>
									</div>
						</div>
					</div>
						 <!-- Button to trigger modal -->

<?php
		}
	}

	/**
	 * Runs after install, update or discover_update
	 * @param string $type install, update or discover_update
	 * @param mixed $parent
	 */
	public function postflight(string $type, $parent): void
	{
		// Install subextensions
		$status = $this->_installSubextensions($parent);
		// Install FOF
		// $fofStatus = $this->_installFOF($parent);

		// Uninstall obsolete subextensions
		// $uninstall_status = $this->_uninstallObsoleteSubextensions($parent);

		// Remove obsolete files and folders
		$removeFilesAndFolders = $this->removeFilesAndFolders;
		$this->_removeObsoleteFilesAndFolders($removeFilesAndFolders);

		// Add default permissions
		$this->deFaultPermissionsFix();
		$straperStatus = array();
		$fofStatus     = array();

		// Install Techjoomla Straper
		if (is_dir($parent->getParent()->getPath('source') . '/strapper'))
		{
			$straperStatus = $this->_installStraper($parent);
		}

		$this->installNotificationsTemplates();

		/* install zoo element and write config to database*/

		$document = Factory::getDocument();
		$document->addStyleSheet(Uri::root() . '/media/techjoomla_strapper/css/bootstrap.min.css');
		// Do all releated Tag line/ logo etc
		$this->taglinMsg();
		// Show the post-installation page
		$this->_renderPostInstallation($status, $fofStatus, $straperStatus, $parent);

		// Set default layouts to load
		$this->setDefaultLayout($type);
	}

	/**
	 * Renders the post-installation message
	 * @param mixed $status
	 * @param mixed $fofStatus
	 * @param mixed $straperStatus
	 * @param mixed $parent
	 */
	private function _renderPostInstallation($status, $fofStatus, $straperStatus, /** @scrutinizer ignore-unused */ $parent)
	{
		// $document = JFactory::getDocument();
		?>
		<?php //$rows = 1;?>
		<link rel="stylesheet" type="text/css" href="<?php echo Uri::root() . 'media/techjoomla_strapper/css/bootstrap.min.css'; ?>"/>
		<div class="techjoomla-bootstrap" >
			<table class="table-condensed table">
			<thead>
				<tr class="row1">
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
				<tr class="row2">
					<td class="key" colspan="2"><strong>jLike component</strong></td>
					<td><strong style="color: green">Installed</strong></td>
				</tr>
					<tr class="row2">
					<td class="key" colspan="2">
						<strong>Framework on Framework (FOF) <?php echo $fofStatus['version']; ?></strong> [<?php echo $fofStatus['date']; ?>]
					</td>
					<td><strong>
						<span style="color: <?php echo $fofStatus['required'] ? ($fofStatus['installed'] ? 'green' : 'red') : '#660'; ?>; font-weight: bold;">
							<?php echo $fofStatus['required'] ? ($fofStatus['installed'] ? 'Installed' : 'Not Installed') : 'Already up-to-date'; ?>
						</span>
					</strong></td>
				</tr>
				<tr class="row2">
					<td class="key" colspan="2">
						<strong>TechJoomla Strapper <?php echo $straperStatus['version']; ?></strong> [<?php echo $straperStatus['date']; ?>]
					</td>
					<td><strong>
						<span style="color: <?php echo $straperStatus['required'] ? ($straperStatus['installed'] ? 'green' : 'red') : '#660'; ?>; font-weight: bold;">
							<?php echo $straperStatus['required'] ? ($straperStatus['installed'] ? 'Installed' : 'Not Installed') : 'Already up-to-date'; ?>
						</span>
					</strong></td>
				</tr>
			<?php
			/*		$installer = clone Installer::getInstance ();
					$baseTmpPath = $installer->getPath ( 'source' );
					$zoopath = JPATH_ROOT.DS.'media'.DS.'zoo';
				?>
						<tr class="row1">
						<th colspan='2'>Element</th>
						<th></th>
						</tr>
						<tr class="row2">
								<td colspan="2" class="key">Zoo</td>
								<td>
					<?php
						if ( ! Folder::copy ($baseTmpPath.DS.'zoo_element' , $zoopath.DS.'elements',null,1 ) )
						{?>
									<strong style="color:red;">Not installed</strong>
					<?php
						}
						else
						{?>
								<strong style="color:green;">Installed</strong>
						<?php
						}	?>
							</td>
						</tr>
<?php*/

							?>

				<?php if (count($status->modules)) { ?>
				<tr class="row1">
					<th>Module</th>
					<th>Client</th>
					<th></th>
					</tr>
				<?php foreach ($status->modules as $module) { ?>
				<tr class="row2 <?php //echo ($rows++ % 2);?>">
					<td class="key"><?php echo $module['name']; ?></td>
					<td class="key"><?php echo $module['client']; ?></td>
					<td><strong style="color: <?php echo ($module['result']) ? "green" : "red"; ?>"><?php echo ($module['result']) ? 'Installed' : 'Not installed'; ?></strong>
					<?php
						if (!empty($module['result']))
						{ // if installed then only show msg
						echo $module['status'] ? "<span class=\"label label-success\">Enabled</span>" : "<span class=\"label label-important\">Disabled</span>";
						}
					?>

					</td>
				</tr>
				<?php }?>
				<?php } ?>
				<?php if (count($status->plugins)) { ?>
				<tr class="row1">
					<th colspan="2">Plugin</th>
			<!--		<th>Group</th> -->
					<th></th>
				</tr>
				<?php
					$oldplugingroup = "";

				foreach ($status->plugins as $plugin)
				{
					//print"<pre>";print_r($status); die("dddd");
					if ($oldplugingroup != $plugin['group'])
					{
						$oldplugingroup = $plugin['group']; ?>
					<tr class="row0">
						<th colspan="2"><strong><?php echo $oldplugingroup . " Plugins"; ?></strong></th>
						<th></th>
				<!--		<td></td> -->
					</tr>
				<?php
					} ?>
				<tr class="row2 <?php //echo ($rows++ % 2);?>">
					<td colspan="2" class="key"><?php echo $plugin['name']; ?></td>
		<!--			<td class="key"><?php //echo ucfirst($plugin['group']);?></td> -->
					<td><strong style="color: <?php echo ($plugin['result']) ? "green" : "red"; ?>"><?php echo ($plugin['result']) ? 'Installed' : 'Not installed'; ?></strong>
					<?php
						if (!empty($plugin['result']))
						{
							echo $plugin['status'] ? "<span class=\"label label-success\">Enabled</span>" : "<span class=\"label label-important\">Disabled</span>";
						} ?>
					</td>
				</tr>
				<?php
				} ?>
				<?php } ?>

				<?php if (count($status->libraries)) { ?>
				<tr class="row1">
					<th>Library</th>
					<th></th>
					<th></th>
					</tr>
				<?php foreach ($status->libraries as $libraries) { ?>
				<tr class="row2 <?php //echo ($rows++ % 2);?>">
					<td class="key"><?php echo ucfirst($libraries['name']); ?></td>
					<td class="key"></td>
					<td><strong style="color: <?php echo ($libraries['result']) ? "green" : "red"; ?>"><?php echo ($libraries['result']) ? 'Installed' : 'Not installed'; ?></strong>
					<?php
						if (!empty($libraries['result']))
						{ // if installed then only show msg
						echo $mstat = ($libraries['status'] ? "<span class=\"label label-success\">Enabled</span>" : "<span class=\"label label-important\">Disabled</span>");
						}
					?>

					</td>
				</tr>
				<?php }?>
				<?php } ?>

		<?php
		if (!empty($status->app_install))
		{
			if (count($status->app_install)) { ?>
				<tr class="row1">
					<th>My likes EasySocial App</th>
					<th></th>
					<th></th>
				</tr>

				<?php foreach ($status->app_install as $app_install) { ?>
					<tr class="row2">
						<td class="key"><?php echo ucfirst($app_install['name']); ?></td>
						<td class="key"></td>
						<td><strong style="color: <?php echo ($app_install['result']) ? "green" : "red"; ?>"><?php echo ($app_install['result']) ? 'Installed' : 'Not installed'; ?></strong>
						<?php
							// If installed then only show msg
							if (!empty($app_install['result']))
							{
								echo $mstat = ($app_install['status'] ? "<span class=\"label label-success\">Enabled</span>" : "<span class=\"label label-important\">Disabled</span>");
							}
						?>

						</td>
					</tr>
				<?php }?>
			<?php }
		} ?>

			</tbody>
		</table>
		</div> <!-- end akeeba bootstrap -->

		<?php

		//die("i am in renderpostinstallation");
	}

	/**
	 * Installs subextensions (modules, plugins) bundled with the main extension
	 *
	 * @param mixed $parent
	 * @return \stdClass The subextension installation status
	 */
	private function _installSubextensions($parent): \stdClass
	{
		$src = $parent->getParent()->getPath('source');

		$status          = new \stdClass();
		$status->modules = array();
		$status->plugins = array();

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
						{// if not dir
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

							// $fortest='';
							//continue;
						}

						// Was the module already installed?
						$sql = $this->db->getQuery(true)
							->select('COUNT(*)')
							->from('#__modules')
							->where($this->db->quoteName('module') . ' = ' . $this->db->quote('mod_' . $module));
						$this->db->setQuery($sql);

						$count = (int) ($this->db->loadResult() ?? 0);

						$installer = new Installer();
						if (method_exists($installer, 'setDatabase'))
						{
							$installer->setDatabase($this->db);
						}
						$result            = $installer->install($path);
						$status->modules[] = array(
							'name'   => $module,
							'client' => $folder,
							'result' => $result,
							'status' => $modulePreferences[1],
						);
						//print"<pre>ddd";print_r ($status->modules); die;
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
								$position = (int) ($this->db->loadColumn()[0] ?? 0);
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
							$moduleid = (int) ($this->db->loadColumn()[0] ?? 0);

							$query = $this->db->getQuery(true);
							$query->select('*')->from($this->db->quoteName('#__modules_menu'))
								->where($this->db->quoteName('moduleid') . ' = ' . $this->db->quote($moduleid));
							$this->db->setQuery($query);
							$assignments = $this->db->loadObjectList();
							$isAssigned  = !empty($assignments);
							if (!$isAssigned)
							{
								$o = (object) array(
									'moduleid'	 => $moduleid,
									'menuid'	   => 0,
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
							->where('( ' . ($this->db->quoteName('name') . ' = ' . $this->db->quote($plugin)) . ' OR ' . ($this->db->quoteName('element') . ' = ' . $this->db->quote($plugin)) . ' )')
							->where($this->db->quoteName('folder') . ' = ' . $this->db->quote($folder));
						$this->db->setQuery($query);
						$count = $this->db->loadResult();

						$installer = new Installer();
						if (method_exists($installer, 'setDatabase'))
						{
							$installer->setDatabase($this->db);
						}
						$result    = $installer->install($path);

						$status->plugins[] = array('name' => $plugin, 'group' => $folder, 'result' => $result, 'status' => $published);

						if ($published && !$count)
						{
							$query = $this->db->getQuery(true)
								->update($this->db->quoteName('#__extensions'))
								->set($this->db->quoteName('enabled') . ' = ' . $this->db->quote('1'))
								->where('( ' . ($this->db->quoteName('name') . ' = ' . $this->db->quote($plugin)) . ' OR ' . ($this->db->quoteName('element') . ' = ' . $this->db->quote($plugin)) . ' )')
								->where($this->db->quoteName('folder') . ' = ' . $this->db->quote($folder));
							$this->db->setQuery($query);
							$this->db->execute();
						}
					}
				}
			}
		}

		if (count($this->installation_queue['libraries']))
		{
			foreach ($this->installation_queue['libraries']  as $folder => $status1)
			{
				$path = "$src/libraries/$folder";

				if (!is_dir($path))
				{
					continue;
				}

				$query = $this->db->getQuery(true)
					->select('COUNT(*)')
					->from($this->db->quoteName('#__extensions'))
					->where('( ' . ($this->db->quoteName('name') . ' = ' . $this->db->quote($folder)) . ' OR ' . ($this->db->quoteName('element') . ' = ' . $this->db->quote($folder)) . ' )')
					->where($this->db->quoteName('folder') . ' = ' . $this->db->quote($folder));
				$this->db->setQuery($query);
				$count = $this->db->loadResult();

				$installer = new Installer();
				if (method_exists($installer, 'setDatabase'))
				{
					$installer->setDatabase($this->db);
				}
				$result    = $installer->install($path);

				$status->libraries[] = array('name' => $folder, 'group' => $folder, 'result' => $result, 'status' => $status1);

				if ($status1 && !$count)
				{
					$query = $this->db->getQuery(true)
						->update($this->db->quoteName('#__extensions'))
						->set($this->db->quoteName('enabled') . ' = ' . $this->db->quote('1'))
						->where('( ' . ($this->db->quoteName('name') . ' = ' . $this->db->quote($folder)) . ' OR ' . ($this->db->quoteName('element') . ' = ' . $this->db->quote($folder)) . ' )')
						->where($this->db->quoteName('folder') . ' = ' . $this->db->quote($folder));
					$this->db->setQuery($query);
					$this->db->execute();
				}
			}
		}

		// Install EasySocial plugin
		if (file_exists(JPATH_ADMINISTRATOR . '/components/com_easysocial/includes/foundry.php'))
		{
			$path = $src . "/plugins/easysocial/jlikeMyLikes";
			if (Folder::exists($path))
			{
				require_once JPATH_ADMINISTRATOR . '/components/com_easysocial/includes/foundry.php';
				$installer     = Foundry::get('Installer');
				$installer->load($path);

				$plg_install           = $installer->install();
				$status->app_install[] = array('name' => 'jlikeMyLikes', 'group' => 'jlikeMyLikes', 'result' => $plg_install, 'status' => '1');
			}
		}

		return $status;
	}

	private function _installFOF($parent)
	{
		$src = $parent->getParent()->getPath('source');
		// Install the FOF framework

		/*$source = $src.'/fof';*/
		//changed by manoj
		$source = $src . DS . 'tj_lib_fof';
		/*print " source ".$source;
		 die (" iam in installfof");
*/
		if (!defined('JPATH_LIBRARIES'))
		{
			$target = JPATH_ROOT . DS . 'libraries' . DS . 'fof';
		}
		else
		{
			$target = JPATH_LIBRARIES . DS . 'fof';
		}
		$haveToInstallFOF = false;
		if (!Folder::exists($target))
		{
			$haveToInstallFOF = true;
		}
		else
		{
			$fofVersion = array();
			if (File::exists($target . DS . 'version.txt'))
			{
				// $rawData = JFile::read($target.DS.'version.txt');
				$rawData                 = file_get_contents($target . DS . 'version.txt');
				$info                    = explode("\n", /** @scrutinizer ignore-type */ $rawData);
				$fofVersion['installed'] = array(
					'version'	 => trim($info[0]),
					'date'		   => new Date(trim($info[1])),
				);
			}
			else
			{
				$fofVersion['installed'] = array(
					'version'	 => '0.0',
					'date'		   => new Date('2011-01-01'),
				);
			}
			$rawData               = file_get_contents($source . DS . 'version.txt');
			$info                  = explode("\n", $rawData);
			$fofVersion['package'] = array(
				'version'	 => trim($info[0]),
				'date'		   => new Date(trim($info[1])),
			);

			$haveToInstallFOF = $fofVersion['package']['date']->toUNIX() > $fofVersion['installed']['date']->toUNIX();
		}

		$installedFOF = false;
		if ($haveToInstallFOF)
		{
			$versionSource = 'package';
			$installer     = new Installer();
			if (method_exists($installer, 'setDatabase'))
			{
				$installer->setDatabase($this->db);
			}
			$installedFOF  = $installer->install($source);
		}
		else
		{
			$versionSource = 'installed';
		}

		if (!isset($fofVersion))
		{
			$fofVersion = array();
			if (File::exists($target . DS . 'version.txt'))
			{
				$rawData                 = file_get_contents($target . DS . 'version.txt');
				$info                    = explode("\n", $rawData);
				$fofVersion['installed'] = array(
					'version'	 => trim($info[0]),
					'date'		   => new Date(trim($info[1])),
				);
			}
			else
			{
				$fofVersion['installed'] = array(
					'version'	 => '0.0',
					'date'		   => new Date('2011-01-01'),
				);
			}
			$rawData               = file_get_contents($source . DS . 'version.txt');
			$info                  = explode("\n", $rawData);
			$fofVersion['package'] = array(
				'version'	 => trim($info[0]),
				'date'		   => new Date(trim($info[1])),
			);
			$versionSource = 'installed';
		}

		if (!($fofVersion[$versionSource]['date'] instanceof Date))
		{
			$fofVersion[$versionSource]['date'] = new Date();
		}

		return array(
			'required'	  => $haveToInstallFOF,
			'installed'	 => $installedFOF,
			'version'	   => $fofVersion[$versionSource]['version'],
			'date'		     => $fofVersion[$versionSource]['date']->format('Y-m-d'),
		);
	}

	private function _installStraper($parent)
	{
		$src = $parent->getParent()->getPath('source');

		// Install the FOF framework
		$source = $src . DS . 'strapper';
		$target = JPATH_ROOT . DS . 'media' . DS . 'techjoomla_strapper';

		$haveToInstallStraper = false;
		if (!Folder::exists($target))
		{
			$haveToInstallStraper = true;
		}
		else
		{
			$straperVersion = array();
			if (File::exists($target . DS . 'version.txt'))
			{
				$rawData                     = file_get_contents($target . DS . 'version.txt');
				$info                        = explode("\n", /** @scrutinizer ignore-type */ $rawData);
				$straperVersion['installed'] = array(
					'version'	 => trim($info[0]),
					'date'		   => new Date(trim($info[1])),
				);
			}
			else
			{
				$straperVersion['installed'] = array(
					'version'	 => '0.0',
					'date'		   => new Date('2011-01-01'),
				);
			}
			$rawData                   = file_get_contents($source . DS . 'version.txt');
			$info                      = explode("\n", $rawData);
			$straperVersion['package'] = array(
				'version'	 => trim($info[0]),
				'date'		   => new Date(trim($info[1])),
			);

			$haveToInstallStraper = $straperVersion['package']['date']->toUNIX() > $straperVersion['installed']['date']->toUNIX();
		}

		$installedStraper = false;
		if ($haveToInstallStraper)
		{
			$versionSource    = 'package';
			$installer        = new Installer();
			if (method_exists($installer, 'setDatabase'))
			{
				$installer->setDatabase($this->db);
			}
			$installedStraper = $installer->install($source);
		}
		else
		{
			$versionSource = 'installed';
		}

		if (!isset($straperVersion))
		{
			$straperVersion = array();
			if (File::exists($target . DS . 'version.txt'))
			{
				$rawData                     = file_get_contents($target . DS . 'version.txt');
				$info                        = explode("\n", $rawData);
				$straperVersion['installed'] = array(
					'version'	 => trim($info[0]),
					'date'		   => new Date(trim($info[1])),
				);
			}
			else
			{
				$straperVersion['installed'] = array(
					'version'	 => '0.0',
					'date'		   => new Date('2011-01-01'),
				);
			}
			$rawData                   = file_get_contents($source . DS . 'version.txt');
			$info                      = explode("\n", $rawData);
			$straperVersion['package'] = array(
				'version'	 => trim($info[0]),
				'date'		   => new Date(trim($info[1])),
			);
			$versionSource = 'installed';
		}

		if (!($straperVersion[$versionSource]['date'] instanceof Date))
		{
			$straperVersion[$versionSource]['date'] = new Date();
		}

		return array(
			'required'	  => $haveToInstallStraper,
			'installed'	 => $installedStraper,
			'version'	   => $straperVersion[$versionSource]['version'],
			'date'		     => $straperVersion[$versionSource]['date']->format('Y-m-d'),
		);
	}

	/**
	 * method to install the component
	 *
	 * @param mixed $parent
	 * @return void
	 */

	/**
	 * Uninstalls subextensions (modules, plugins) bundled with the main extension
	 *
	 * @param Installer $parent
	 * @return CMSObject The subextension uninstallation status
	 */
	private function _uninstallSubextensions(/** @scrutinizer ignore-unused */ $parent): \stdClass
	{
		$status          = new \stdClass();
		$status->modules = array();
		$status->plugins = array();

		// Modules uninstallation
		if (count($this->uninstall_queue['modules']))
		{
			foreach ($this->uninstall_queue['modules'] as $folder => $modules)
			{
				if (count($modules))
				{
					foreach ($modules as $module => $modulePreferences)
					{
						// Find the module ID
						$sql = $this->db->getQuery(true)
							->select($this->db->quoteName('extension_id'))
							->from($this->db->quoteName('#__extensions'))
							->where($this->db->quoteName('element') . ' = ' . $this->db->quote('mod_' . $module))
							->where($this->db->quoteName('type') . ' = ' . $this->db->quote('module'));
						$this->db->setQuery($sql);
						$id = $this->db->loadResult();
						// Uninstall the module
						if ($id)
						{
							$installer = new Installer();
							if (method_exists($installer, 'setDatabase'))
							{
								$installer->setDatabase($this->db);
							}
							$result            = $installer->uninstall('module', $id, 1);
							$status->modules[] = array(
								'name'   => 'mod_' . $module,
								'client' => $folder,
								'result' => $result,
							);
						}
					}
				}
			}
		}

		// Plugins uninstallation
		if (count($this->uninstall_queue['plugins']))
		{
			foreach ($this->uninstall_queue['plugins'] as $folder => $plugins)
			{
				if (count($plugins))
				{
					foreach ($plugins as $plugin => $published)
					{
						$sql = $this->db->getQuery(true)
							->select($this->db->quoteName('extension_id'))
							->from($this->db->quoteName('#__extensions'))
							->where($this->db->quoteName('type') . ' = ' . $this->db->quote('plugin'))
							->where($this->db->quoteName('element') . ' = ' . $this->db->quote($plugin))
							->where($this->db->quoteName('folder') . ' = ' . $this->db->quote($folder));
						$this->db->setQuery($sql);

						$id = (int) ($this->db->loadResult() ?? 0);
						if ($id)
						{
							$installer = new Installer();
							if (method_exists($installer, 'setDatabase'))
							{
								$installer->setDatabase($this->db);
							}
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

	private function _renderPostUninstallation($status, /** @scrutinizer ignore-unused */ $parent)
	{
		?>
<?php $rows = 0; ?>
<h2><?php echo Text::_('jLike Uninstallation Status'); ?></h2>
<table class="adminlist">
	<thead>
		<tr>
			<th class="title" colspan="2"><?php echo Text::_('Extension'); ?></th>
			<th width="30%"><?php echo Text::_('Status'); ?></th>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<td colspan="3"></td>
		</tr>
	</tfoot>
	<tbody>
		<tr class="row0">
			<td class="key" colspan="2"><?php echo 'Jlike ' . Text::_('Component'); ?></td>
			<td><strong style="color: green"><?php echo Text::_('Removed'); ?></strong></td>
		</tr>
		<?php if (count($status->modules)) { ?>
		<tr>
			<th><?php echo Text::_('Module'); ?></th>
			<th><?php echo Text::_('Client'); ?></th>
			<th></th>
		</tr>
		<?php foreach ($status->modules as $module) { ?>
		<tr class="row<?php echo ++$rows % 2; ?>">
			<td class="key"><?php echo $module['name']; ?></td>
			<td class="key"><?php echo ucfirst($module['client']); ?></td>
			<td><strong style="color: <?php echo ($module['result']) ? "green" : "red"; ?>"><?php echo ($module['result']) ? Text::_('Removed') : Text::_('Not removed'); ?></strong></td>
		</tr>
		<?php }?>
		<?php } ?>
		<?php if (count($status->plugins)) { ?>
		<tr>
			<th><?php echo Text::_('Plugin'); ?></th>
			<th><?php echo Text::_('Group'); ?></th>
			<th></th>
		</tr>
		<?php foreach ($status->plugins as $plugin) { ?>
		<tr class="row<?php echo ++$rows % 2; ?>">
			<td class="key"><?php echo ucfirst($plugin['name']); ?></td>
			<td class="key"><?php echo ucfirst($plugin['group']); ?></td>
			<td><strong style="color: <?php echo ($plugin['result']) ? "green" : "red"; ?>"><?php echo ($plugin['result']) ? Text::_('Removed') : Text::_('Not removed'); ?></strong></td>
		</tr>
		<?php } ?>
		<?php } ?>

	</tbody>
</table>
<?php
	}

	/**
	 * Runs on uninstallation
	 *
	 * @param mixed $parent
	 */
	public function uninstall($parent): void
	{
		// Uninstall subextensions
		$status = $this->_uninstallSubextensions($parent);

		// Show the post-uninstallation page
		$this->_renderPostUninstallation($status, $parent);
	}

	/**
	 * method to update the component
	 *
	 * @param mixed $parent
	 * @return void
	 */
	public function update($parent): void
	{
		// Obviously you may have to change the path and name if your installation SQL file ;)
		if (method_exists($parent, 'extension_root'))
		{
			$sqlfile = $parent->getPath('extension_root') . DS . 'install.sql';
		}
		else
		{
			$sqlfile = $parent->getParent()->getPath('extension_root') . DS . 'install.sql';
		}
		
		// Check if SQL file exists
		if (!File::exists($sqlfile))
		{
			Factory::getApplication()->enqueueMessage(
				Text::sprintf('JLIB_INSTALLER_ERROR_SQL_ERROR', 'SQL file not found: ' . $sqlfile),
				'warning'
			);
		}
		else
		{
			// Don't modify below this line
			$buffer = file_get_contents($sqlfile);
			
			if ($buffer === false || empty($buffer))
			{
				Factory::getApplication()->enqueueMessage(
					Text::sprintf('JLIB_INSTALLER_ERROR_SQL_ERROR', 'Unable to read SQL file or file is empty: ' . $sqlfile),
					'warning'
				);
			}
			else
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
								Factory::getApplication()->enqueueMessage(
									Text::sprintf('JLIB_INSTALLER_ERROR_SQL_ERROR', $e->getMessage()),
									'warning'
								);
							}
						}
					}
				}
			}
		}

		//since version 1.0.2
		$this->fix_db_on_update();

		// Delete non existing files
		$this->deleteNonExistingFiles();
	}

	// end of update

	/**
	 * Delete files that should not exist
	 *
	 * @return  void
	 */
	public function deleteNonExistingFiles()
	{
		include_once JPATH_ADMINISTRATOR . '/components/com_jlike/deletelist.php';

		foreach ($files as $file)
		{
			if (File::exists(JPATH_ROOT . $file) && !File::delete(JPATH_ROOT . $file))
			{
				$app->enqueueMessage(Text::sprintf('FILES_JOOMLA_ERROR_FILE_FOLDER', $file));
			}
		}

		foreach ($folders as $folder)
		{
			if (Folder::exists(JPATH_ROOT . $folder) && !Folder::delete(JPATH_ROOT . $folder))
			{
				$app->enqueueMessage(Text::sprintf('FILES_JOOMLA_ERROR_FILE_FOLDER', $folder));
			}
		}
	}

	public function fix_db_on_update(): void
	{
		$jlike_likes_columns = array('id' => 'int(11)', 'content_id' => 'int(11)', 'annotation_id' => 'int(11)', 'userid' => 'int(11)', 'like' => 'INT(11)', 'dislike' => 'INT(11)', 'date' => 'text', 'created' => 'datetime', 'modified' => 'datetime');

		$query = "SHOW COLUMNS FROM #__jlike_likes";
		$this->db->setQuery($query);

		$res = $this->db->loadColumn();

		foreach ($jlike_likes_columns as $c => $t)
		{
			if (!in_array($c, $res))
			{
				$query = "ALTER TABLE #__jlike_likes add column $c $t;";
				$this->db->setQuery($query);
				$this->db->execute();
			}
		}

		$jlike_annotations_columns = array('id' => 'int(11)', 'ordering' => 'int(11)', 'state' => 'TINYINT(1)', 'user_id' => 'int(11)', 'content_id' => 'INT(11)', 'annotation' => 'text', 'privacy' => 'int(11)', 'annotation_date' => 'timestamp', 'parent_id' => 'int(11)', 'note' => 'TINYINT(1)', 'type' => 'VARCHAR(255)', 'context' => 'VARCHAR(255)', 'checked_out_time' => 'datetime');

		$query = "SHOW COLUMNS FROM #__jlike_annotations";
		$this->db->setQuery($query);

		$res = $this->db->loadColumn();

		foreach ($jlike_annotations_columns as $c => $t)
		{
			if (!in_array($c, $res))
			{
				$query = "ALTER TABLE #__jlike_annotations add column $c $t;";
				$this->db->setQuery($query);

				try
				{
					$this->db->execute();
				}
				catch (\RuntimeException $e)
				{
					Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
				}
			}
		}

		// Update #__jlike_pathnode_graph - start
		$jlike_pathnode_graph_columns = array('visibility' => 'TINYINT(4)');

		$query = "SHOW COLUMNS FROM #__jlike_pathnode_graph";
		$this->db->setQuery($query);

		$res = $this->db->loadColumn();

		foreach ($jlike_pathnode_graph_columns as $c => $t)
		{
			if (!in_array($c, $res))
			{
				$query = "ALTER TABLE #__jlike_pathnode_graph add column $c $t;";
				$this->db->setQuery($query);

				try
				{
					$this->db->execute();
				}
				catch (\RuntimeException $e)
				{
					Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
				}
			}
		}
		// Update #__jlike_pathnode_graph - end

		// Update #__jlike_todos - start
		$jlike_todos_columns = array('context' => 'TINYINT(4)');

		$query = "SHOW COLUMNS FROM #__jlike_todos";
		$this->db->setQuery($query);

		$res = $this->db->loadColumn();

		foreach ($jlike_todos_columns as $c => $t)
		{
			if (!in_array($c, $res))
			{
				$query = "ALTER TABLE #__jlike_todos add column $c $t;";
				$this->db->setQuery($query);

				try
				{
					$this->db->execute();
				}
				catch (\RuntimeException $e)
				{
					Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
				}
			}
		}
		// Update #__jlike_todos - end

		/*since version 1.0.2
		//check if column - type exists
		$query="SHOW COLUMNS FROM #__jlike_annotations WHERE `Field` = 'id' AND `Type` = 'int(11)'";
		$db->setQuery($query);
		$check=$db->loadResult();
		if(!$check)
		{
			$query="ALTER TABLE  `#__jlike_annotations` ADD  `id` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST";
			$db->setQuery($query);
			//$db->loadResult();
			if ( !$db->execute() ) {
				JError::raiseError( 500, $db->stderr() );
			}
		}
		//since version 1.0.2
		//check if column - type exists
		$query="SHOW COLUMNS FROM #__jlike_annotations WHERE `Field` = 'annotation_date' AND `Type` = 'timestamp'";
		$db->setQuery($query);
		$check=$db->loadResult();
		if(!$check)
		{
			$query="ALTER TABLE  `#__jlike_annotations` ADD  `annotation_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER  `privacy`";
			$db->setQuery($query);
			//$db->loadResult();
			if ( !$db->execute() ) {
				JError::raiseError( 500, $db->stderr() );
			}
		}
		//since version 1.0.2
		//check if column - type exists
		$query="SHOW COLUMNS FROM #__jlike_annotations WHERE `Field` = 'parent_id' AND `Type` = 'int(11)'";
		$db->setQuery($query);
		$check=$db->loadResult();
		if(!$check)
		{
			$query="ALTER TABLE  `#__jlike_annotations` ADD  `parent_id` INT( 11 ) NOT NULL AFTER `annotation_date`";
			$db->setQuery($query);
			//$db->loadResult();
			if ( !$db->execute() ) {
				JError::raiseError( 500, $db->stderr() );
			}
		}

		$query="SHOW COLUMNS FROM #__jlike_likes WHERE `Field` = 'annotation_id' AND `Type` = 'int(11)'";
		$db->setQuery($query);
		$check=$db->loadResult();
		if(!$check)
		{
			$query="ALTER TABLE  `#__jlike_likes` ADD  `annotation_id` INT( 11 ) NOT NULL DEFAULT 0 AFTER `content_id`";
			$db->setQuery($query);
			//$db->loadResult();
			if ( !$db->execute() ) {
				JError::raiseError( 500, $db->stderr() );
			}
		}

		//since version 1.2
		//check if column - ordering exists
		$query="SHOW COLUMNS FROM #__jlike_annotations WHERE `Field` = 'ordering' AND `Type` = 'int(11)'";
		$db->setQuery($query);
		$check=$db->loadResult();
		if(!$check)
		{
			$query="ALTER TABLE  `#__jlike_annotations` ADD  `ordering` INT( 11 ) NOT NULL AFTER `id`";
			$db->setQuery($query);
			//$db->loadResult();
			if ( !$db->execute() ) {
				JError::raiseError( 500, $db->stderr() );
			}
		}

		$query="SHOW COLUMNS FROM #__jlike_annotations WHERE `Field` = 'state' AND `Type` = 'TINYINT(1)'";
		$db->setQuery($query);
		$check=$db->loadResult();
		if(!$check)
		{
			$query="ALTER TABLE  `#__jlike_annotations` ADD  `state` TINYINT(1) NOT NULL  DEFAULT 1 AFTER `ordering`";
			$db->setQuery($query);
			//$db->loadResult();
			if ( !$db->execute() ) {
				JError::raiseError( 500, $db->stderr() );
			}
		}

		//since jlike 1.1
		$query="SHOW COLUMNS FROM #__jlike_annotations WHERE `Field` = 'note' AND `Type` = 'TINYINT(1)'";
		$db->setQuery($query);
		$check=$db->loadResult();
		if(!$check)
		{
			$query="ALTER TABLE  `#__jlike_annotations` ADD  `note` TINYINT(1) NOT NULL  DEFAULT 1 AFTER `state`";
			$db->setQuery($query);
			//$db->loadResult();
			if ( !$db->execute() ) {
				JError::raiseError( 500, $db->stderr() );
			}
		}*/

		$jlike_todos_columns = array(
			'id'               => 'int(11)',
			'content_id'       => 'int(11)',
			'assigned_by'      => 'int(11)',
			'assigned_to'      => 'int(11)',
			'created_date'     => 'datetime',
			'start_date'       => 'datetime',
			'due_date'         => 'datetime',
			'status'           => 'varchar(100)',
			'title'            => 'varchar(255)',
			'type'             => 'varchar(100)',
			'system_generated' => 'tinyint(4)',
			'parent_id'        => 'int(11)',
			'list_id'          => 'int(11)',
			'modified_date'    => 'datetime',
			'modified_by'      => 'int(11)',
			'can_override'     => 'tinyint(4)',
			'overriden'        => 'tinyint(4)',
			'params'           => 'text',
			'todo_list_id'     => 'int(11)',
			'ideal_time'       => 'int(11)',
			'sender_msg'       => 'text',
			'created_by'       => 'int(11)',
			'state'            => 'tinyint(1)',
		);
		$this->alterTables("#__jlike_todos", $jlike_todos_columns);

		// $field_array = array();
		$query = "CREATE TABLE IF NOT EXISTS `#__jlike_content_inviteX_xref` (
			  `id` int(15) NOT NULL AUTO_INCREMENT,
			  `content_id` int(15) NOT NULL,
			  `importEmailId` int(15) NOT NULL ,
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ";
		$this->db->setQuery($query);
		$this->db->execute();

		// Table structure for table `#__jlike_reminders`
		$query = "CREATE TABLE IF NOT EXISTS `#__jlike_reminders` (
 				`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,

 				`ordering` INT(11)  NOT NULL ,
 				`state` TINYINT(1)  NOT NULL ,
 				`checked_out` INT(11)  NOT NULL ,
 				`checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
 				`created_by` INT(11)  NOT NULL ,
 				`modified_by` INT(11)  NOT NULL ,
 				`title` VARCHAR(255)  NOT NULL ,
 				`days_before` INT(11)  NOT NULL ,
 				`email_template` TEXT NOT NULL ,
 				`subject` VARCHAR(255)  NOT NULL ,
 				`content_type` VARCHAR(255) NOT NULL ,
 				`cc` VARCHAR(255)  NOT NULL ,
 				 `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'email',
 				PRIMARY KEY (`id`)
 			) DEFAULT COLLATE=utf8mb4_unicode_ci; ";
		$this->db->setQuery($query);
		$this->db->execute();

		// Table structure for table `#__jlike_reminder_contentids`

		$query = "
 				CREATE TABLE IF NOT EXISTS `#__jlike_reminder_contentids` (
 				  `reminder_id` int(11) NOT NULL,
 				  `content_id` int(11) NOT NULL
 				) DEFAULT CHARSET=utf8; ";
		$this->db->setQuery($query);
		$this->db->execute();

		// Table structure for table `#__jlike_reminder_sent`

		$query = "CREATE TABLE IF NOT EXISTS `#__jlike_reminder_sent` (
 			  `id` int(11) NOT NULL AUTO_INCREMENT,
 			  `todo_id` int(11) NOT NULL,
 			  `reminder_id` int(11) NOT NULL,
 			  `sent_on` datetime NOT NULL,
 			  PRIMARY KEY (`id`)
 			) DEFAULT CHARSET=utf8; ";
		$this->db->setQuery($query);
		$this->db->execute();
	}

	/**
	 * alterTables
	 *
	 * @param   string  $table   Table name
	 * @param   array   $colums  colums name
	 *
	 * @return  void
	 *
	 * @since  1.0.0
	 */
	public function alterTables($table, $colums): void
	{
		$query = "SHOW COLUMNS FROM {$table}";
		$this->db->setQuery($query);

		$res = $this->db->loadColumn();

		foreach ($colums as $c => $t)
		{
			if (!in_array($c, $res))
			{
				$query = "ALTER TABLE {$table} add column $c $t;";
				$this->db->setQuery($query);
				$this->db->execute();
			}
		}
	}

	/**
	 * Removes obsolete files and folders
	 *
	 * @param array $removeFilesAndFolders
	 */
	private function _removeObsoleteFilesAndFolders($removeFilesAndFolders)
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
				rmdir($f);
			}
		}
	}

	/*	Tag line, version etc
	 *
	 *
	 * */
	public function taglinMsg()
	{
		/*:TODO*/
	}

	// end of tagline msg

	/**
	 * Uninstalls obsolete subextensions (modules, plugins) bundled with the main extension
	 *
	 * @param Installer $parent
	 * @return CMSObject The subextension uninstallation status
	 */
	private function _uninstallObsoleteSubextensions(/** @scrutinizer ignore-unused */ $parent): \stdClass
	{
		$status          = new \stdClass();
		$status->modules = array();
		$status->plugins = array();

		// Modules uninstallation
		if (count($this->obsolete_extensions_uninstallation_que['modules']))
		{
			foreach ($this->obsolete_extensions_uninstallation_que['modules'] as $folder => $modules)
			{
				if (count($modules))
				{
					foreach ($modules as $module)
					{
						// Find the module ID
						$sql = $this->db->getQuery(true)
							->select($this->db->quoteName('extension_id'))
							->from($this->db->quoteName('#__extensions'))
							->where($this->db->quoteName('element') . ' = ' . $this->db->quote('mod_' . $module))
							->where($this->db->quoteName('type') . ' = ' . $this->db->quote('module'));
						$this->db->setQuery($sql);
						$id = $this->db->loadResult();
						// Uninstall the module
						if ($id)
						{
							$installer = new Installer();
							if (method_exists($installer, 'setDatabase'))
							{
								$installer->setDatabase($this->db);
							}
							$result            = $installer->uninstall('module', $id, 1);
							$status->modules[] = array(
								'name'   => 'mod_' . $module,
								'client' => $folder,
								'result' => $result,
							);
						}
					}
				}
			}
		}

		// Plugins uninstallation
		if (count($this->obsolete_extensions_uninstallation_que['plugins']))
		{
			foreach ($this->obsolete_extensions_uninstallation_que['plugins'] as $folder => $plugins)
			{
				if (count($plugins))
				{
					foreach ($plugins as $plugin)
					{
						$sql = $this->db->getQuery(true)
							->select($this->db->quoteName('extension_id'))
							->from($this->db->quoteName('#__extensions'))
							->where($this->db->quoteName('type') . ' = ' . $this->db->quote('plugin'))
							->where($this->db->quoteName('element') . ' = ' . $this->db->quote($plugin))
							->where($this->db->quoteName('folder') . ' = ' . $this->db->quote($folder));
						$this->db->setQuery($sql);

						$id = $this->db->loadResult();
						if ($id)
						{
							$installer = new Installer();
							if (method_exists($installer, 'setDatabase'))
							{
								$installer->setDatabase($this->db);
							}
							$result            = $installer->uninstall('plugin', $id, 1);
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
	 * Add default ACL permissions if already set by administrator
	 *
	 * @return  void
	 */
	public function deFaultPermissionsFix(): void
	{
		$query = $this->db->getQuery(true);

		$query->select($this->db->quoteName(array('id', 'rules')));
		$query->from($this->db->quoteName('#__assets'));
		$query->where($this->db->quoteName('name') . '= ' . $this->db->quote('com_jlike'));
		$this->db->setQuery($query);
		$result = $this->db->loadobject();

		if ($result && strlen(trim($result->rules)) <= 3)
		{
			$obj        = new \stdClass();
			$obj->id    = $result->id;
			$obj->rules = '{"core.create":{"2":1},"core.edit":{"2":1},"core.edit.own":{"2":1},"core.edit.state":{"2":1}}';

			try
			{
				$this->db->updateObject('#__assets', $obj, 'id');
			}
			catch (\RuntimeException $e)
			{
				Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
			}
		}
	}

	/**
	 * Installed Notifications Templates
	 *
	 * @return  void
	 */
	public function installNotificationsTemplates()
	{
		if (ComponentHelper::isEnabled('com_tjnotifications'))
		{
			Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_tjnotifications/tables');
			BaseDatabaseModel::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_tjnotifications/models');
			$notificationsModel = BaseDatabaseModel::getInstance('Notification', 'TJNotificationsModel');

			$filePath = JPATH_ADMINISTRATOR . '/components/com_jlike/tjnotificationTemplates.json';
			$str      = file_get_contents($filePath);
			$json     = json_decode($str, true);

			$existingKeys = $notificationsModel->getKeys('jlike');

			if (count($json) != 0)
			{
				foreach ($json as $template => $array)
				{
					// If template doesn't exist then we add notification template.
					if (!in_array($array['key'], $existingKeys))
					{
						$notificationsModel->createTemplates($array);
					}
					else
					{
						$notificationsModel->updateTemplates($array, 'jlike');
					}
				}
			}
		}
	}

	/**
	 * Set default bootstrap layouts to load
	 *
	 * @param   string  $type  install, update or discover_update
	 * 
	 * @return void
	 *
	 * @since 3.0.0
	 */
	public function setDefaultLayout($type): void
	{
		if ($type == 'install' && JVERSION >= '4.0.0')
		{
			$query = $this->db->getQuery(true);
			$query->select('*');
			$query->from($this->db->quoteName('#__extensions'));
			$query->where($this->db->quoteName('type') . ' = ' . $this->db->quote('component'));
			$query->where($this->db->quoteName('element') . ' = ' . $this->db->quote('com_jlike'));
			$this->db->setQuery($query);
			$data = $this->db->loadObject();

			if ($data)
			{
				$params = json_decode($data->params);

				if (!empty($params) && isset($params->bootstrap_version))
				{
					$query = $this->db->getQuery(true);
					$params->bootstrap_version = 'bs5';
					$fields = array($this->db->quoteName('params') . ' = ' . $this->db->quote(json_encode($params)));
					$conditions = array($this->db->quoteName('extension_id') . ' = ' . $data->extension_id);
					$query->update($this->db->quoteName('#__extensions'))->set($fields)->where($conditions);
					$this->db->setQuery($query);
					$this->db->execute();
				}
			}
		}
	}
}//end class
