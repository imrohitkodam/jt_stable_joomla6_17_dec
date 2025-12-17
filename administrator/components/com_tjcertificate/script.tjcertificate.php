<?php
/**
 * @package     TJCertificate
 * @subpackage  com_tjcertificate
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Table\Table;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Factory;

/**
 * TjCertificateInstallerScript class.
 *
 * @package     TJCertificate
 * @subpackage  com_tjcertificate
 * @since       1.0
 */
class Com_TjcertificateInstallerScript
{
	/** @var array The list of extra modules and plugins to install */
	private $installation_queue = array(

		// Plugins => { (folder) => { (element) => (published) }* }*
		'plugins' => array(
				'tjqueue' => array(
					'certificate' => 0,
				)
			)
		);

	/**
	 * Runs after install, update or discover_update
	 *
	 * @param   string      $type    install, update or discover_update
	 * @param   Installer  $parent  parent
	 *
	 * @return  boolean
	 */
	public function postflight($type, $parent)
	{
		JLoader::import('components.com_tjcertificate.includes.tjcertificate', JPATH_ADMINISTRATOR);

		$dataBaseModel = TJCERT::model('Database', array('ignore_request' => true));

		try
		{
			$dataBaseModel->allowEditOwnForManagerGroup();
		}
		catch (Exception $e)
		{
			return false;
		}

		$this->installNotificationsTemplates();

		// Install subextensions
		$this->_installSubextensions($parent);

		return true;
	}

	/**
	 * Installed Notifications Templates
	 *
	 * @return  void
	 */
	public function installNotificationsTemplates()
	{
		Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_tjnotifications/tables');
		BaseDatabaseModel::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_tjnotifications/models');
		$notificationsModel = BaseDatabaseModel::getInstance('Notification', 'TJNotificationsModel');

		$filePath = JPATH_ADMINISTRATOR . '/components/com_tjcertificate/tjcertificateTemplate.json';
		$str = file_get_contents($filePath);
		$json = json_decode($str, true);

		$existingKeys = $notificationsModel->getKeys('com_tjcertificate');

		if (count($json) != 0)
		{
			foreach ($json as $template => $array)
			{
				// If template doesn't exist then we add notification template.
				if (!in_array($array['key'], $existingKeys))
				{
					$notificationsModel->createTemplates($array);
				}
			}
		}
	}

	/**
	 * Installs subextensions (modules, plugins) bundled with the main extension
	 *
	 * @param   Installer $parent
	 * 
	 * @return CMSObject The subextension installation status
	 */
	private function _installSubextensions($parent)
	{
		$src = $parent->getParent()->getPath('source');

		$db = Factory::getDbo();

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
						$query = $db->getQuery(true)->select('COUNT(*)')->from($db->qn('#__extensions'))->where($db->qn('element') . ' = ' . $db->q($plugin))->where($db->qn('folder') . ' = ' . $db->q($folder));
						$db->setQuery($query);
						$count = $db->loadResult();

						$installer = new Installer;
						$installer->setDatabase($db);
						$result    = $installer->install($path);

						if ($published && !$count)
						{
							$query = $db->getQuery(true)->update($db->qn('#__extensions'))->set($db->qn('enabled') . ' = ' . $db->q('1'))->where($db->qn('element') . ' = ' . $db->q($plugin))->where($db->qn('folder') . ' = ' . $db->q($folder));
							$db->setQuery($query);
							$db->execute();
						}
					}
				}
			}
		}
	}
}
