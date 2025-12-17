<?php
/**
 * @package     JTicketing
 * @subpackage  com_jticketing
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\Filesystem\File;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\MVC\Controller\BaseController;

/**
 * JticketingController helper
 *
 * @package     Jticketing
 * @subpackage  site
 * @since       2.2
 */
class JticketingController extends BaseController
{
	/**
	 * Display.
	 *
	 * @param   boolean  $cachable   cachable status.
	 * @param   boolean  $urlparams  urlparams status.
	 *
	 * @return mixed
	 *
	 * @since 3.0
	 */
	public function display($cachable = false, $urlparams = false)
	{
		require_once JPATH_ADMINISTRATOR . '/components/com_jticketing'. '/helpers/jticketing.php';

		$view   = $this->input->get('view', 'cp');
		$layout = $this->input->get('layout');
		$id     = $this->input->getInt('id');

		// Check for edit form.
		if ($view == 'event' && $layout == 'edit' && !$this->checkEditId('com_jticketing.edit.event', $id))
		{
			$this->setMessage(Text::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id), 'error');
			$this->setRedirect(Route::_('index.php?option=com_jticketing&view=events', false));

			return false;
		}

		$this->input->set('view', $view);

		return parent::display();
	}

	/**
	 * Display.
	 *
	 * @return void
	 *
	 * @since 3.0
	 */

	public function getplugindata()
	{
		$jinput = Factory::getApplication()->getInput();

		$plug_name = $jinput->getString('plug_name', '');
		$plug_type = $jinput->getString('plug_type', '');
		$plug_task = $jinput->getString('plug_task', '');
		PluginHelper::importPlugin($plug_type, $plug_name);

		$result = Factory::getApplication()->triggerEvent($plug_task, array());
		echo $result[0];
		jexit();
	}

	/**
	 * Method for creating activities for previous created event
	 *
	 * @return boolean
	 *
	 * @since   2.0
	 */
	public function migrateData()
	{
		$app = Factory::getApplication();
		BaseDatabaseModel::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jticketing/models');
		$jticketingModelMigration = BaseDatabaseModel::getInstance('Migration', 'JticketingModel');

		$result = $jticketingModelMigration->migrateData();

		foreach ($result as $key => $value)
		{
			if (!$value)
			{
				$app->enqueueMessage(ucfirst($key) . Text::_('COM_JTICKETING_MIGRATION_ERROR_MESSAGE'), 'error');
			}
			else
			{
				$app->enqueueMessage(ucfirst($key) . Text::_('COM_JTICKETING_MIGRATION_SUCCESS_MESSAGE'), 'message');
			}
		}

		$redirect = Route::_('index.php?option=com_jticketing&view=cp', false);
		$this->setRedirect($redirect);
	}

	/**
	 * Download log on import users.
	 *
	 * @return  mixed
	 *
	 * @since   3.1.0
	 */
	public function downloadLog()
	{
		$prefix   = Factory::getApplication()->getInput()->getVar('prefix');
		$session  = Factory::getSession();
		$config   = Factory::getConfig();

		$filename = $session->get($prefix . '_filename');

		$file = $config->get('log_path') . '/' . $filename;

		if (!empty($filename) && File::exists($file))
		{
			header('Content-Description: File Transfer');
			header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename="' . basename($file) . '"');
			header('Expires: 0');
			header('Cache-Control: must-revalidate');
			header('Pragma: public');
			header('Content-Length: ' . filesize($file));
			readfile($file);
			jexit();
		}
		else
		{
			header("Location: " . $_SERVER["HTTP_REFERER"]);
		}
	}
}
