<?php
/**
 * @package     JLike
 * @subpackage  com_jlike
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;
use Joomla\CMS\Schema\ChangeSet;
use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\Registry\Registry;
use Joomla\Component\Installer\Administrator\Model\DatabaseModel;


/**
 * Jlike Manage Model
 *
 * @since  1.6
 */
class JlikeModelDatabase extends BaseDatabaseModel
{
	/**
	 * Gets the changeset object.
	 *
	 * @return  ChangeSet
	 */
	public function getItems()
	{
		$folder = JPATH_ADMINISTRATOR . '/components/com_jlike/sql/updates/';

		try
		{
			$changeSet = ChangeSet::getInstance($this->getDbo(), $folder);
		}
		catch (RuntimeException $e)
		{
			Factory::getApplication()->enqueueMessage($e->getMessage(), 'warning');

			return false;
		}

		return $changeSet;
	}

	/**
	 * + Techjoomla - Dummy override
	 * Fix schema version if wrong.
	 *
	 * @param   JSchemaChangeSet  $changeSet  Schema change set.
	 *
	 * @return   mixed  string schema version if success, false if fail.
	 */
	public function fixSchemaVersion($changeSet)
	{
		// We don't want to update anything related to core Joomla after db upgrade fix
		$schema = $this->getSchemaVersion();

		return $schema;
	}

	/**
	 * Fix Joomla version in #__extensions table if wrong
	 *
	 * @return   mixed  string update version if success, false if fail.
	 */
	public function fixUpdateVersion()
	{
		$table = Table::getInstance('Extension');
		$table->load(array('type' => 'component', 'element' => 'com_jlike'));
		$cache = new Registry($table->manifest_cache);

		// Get installed version from xml file
		$xml     = simplexml_load_file(JPATH_ADMINISTRATOR . '/components/com_jlike/jlike.xml');
		$version = (string) $xml->version;

		$cache->set('version', $version);
		$table->manifest_cache = $cache->toString();

		if ($table->store())
		{
			return $version;
		}

		return false;
	}

	/**
	 * Function to add jlike notification templates in Tjnotification
	 *
	 * @return  void
	 */
	public function addNotificationTemplates()
	{
		if (ComponentHelper::isEnabled('com_tjnotifications'))
		{
			Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_tjnotifications/tables');
			BaseDatabaseModel::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_tjnotifications/models');
			$notificationsModel = BaseDatabaseModel::getInstance('Notification', 'TJNotificationsModel');

			$filePath = JPATH_ADMINISTRATOR . '/components/com_jlike/tjnotificationTemplates.json';
			$str = file_get_contents($filePath);
			$json = json_decode($str, true);

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
				}
			}
		}
	}
}
