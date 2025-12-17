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
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

/**
 * Installer Database Controller
 *
 * @since  1.8
 */
class JLikeControllerDatabase extends BaseController
{
	/**
	 * Tries to fix missing database updates
	 *
	 * @return  void
	 *
	 * @since   2.5
	 * @todo    Purge updates has to be replaced with an events system
	 */
	public function fix()
	{
		// Get a handle to the Joomla! application object
		$application = Factory::getApplication();

		$model = $this->getModel('database');
		$model->fix();

		// Purge updates
		BaseDatabaseModel::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_joomlaupdate/models', 'JoomlaupdateModel');
		$updateModel = BaseDatabaseModel::getInstance('default', 'JoomlaupdateModel');
		$updateModel->purge();

		// Refresh versionable assets cache
		Factory::getApplication()->flushAssets();

		// Add a message to the message queue
		$application->enqueueMessage(Text::_('COM_JLIKE_DATABASE_UPDATED'), 'success');

		$this->setRedirect(Route::_('index.php?option=com_jlike', false));
	}

	/**
	 * Function to add jlike notification templates in Tjnotification
	 *
	 * @return  void
	 */
	public function addNotificationTmpls()
	{
		$app   = Factory::getApplication();
		$model = $this->getModel('database');

		$model->addNotificationTemplates();

		$app->enqueueMessage(Text::_('COM_JLIKE_NOTIFICATION_ADDEDD_SUCCESSFULLY'), 'success');

		$this->setRedirect(Route::_('index.php?option=com_jlike', false));
	}
}
