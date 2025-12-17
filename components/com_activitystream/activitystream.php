<?php
/**
 * @version    SVN: <svn_id>
 * @package    ActivityStream
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2017 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;

// Include dependancies
spl_autoload_register(function ($class) {
	if (strpos($class, 'Activitystream') === 0) {
		$path = JPATH_COMPONENT . '/' . str_replace('Activitystream', '', $class) . '.php';
		if (file_exists($path)) {
			require_once $path;
		}
	}
});

if (file_exists(JPATH_COMPONENT . '/controller.php')) {
	require_once JPATH_COMPONENT . '/controller.php';
}

$lang = Factory::getLanguage();
$lang->load('com_activitystream', JPATH_SITE);

// Execute the task.
$controller = BaseController::getInstance('Activitystream');
$controller->execute(Factory::getApplication()->input->get('task'));
$controller->redirect();
