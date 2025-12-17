<?php
/**
 * @package     TJVendors
 * @subpackage  com_tjvendors
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;

include_once JPATH_SITE . '/components/com_tjvendors/includes/tjvendors.php';

spl_autoload_register(function ($class) {
	if (strpos($class, 'Tjvendors') === 0) {
		$path = JPATH_COMPONENT . '/' . strtolower(substr($class, 9)) . '.php';
		if (file_exists($path)) {
			require_once $path;
		}
	}
});

$controllerPath = JPATH_COMPONENT . '/controller.php';
if (file_exists($controllerPath)) {
	require_once $controllerPath;
}

$TjvendorFrontHelper = JPATH_ROOT . '/components/com_tjvendors/helpers/fronthelper.php';

if (!class_exists('TjvendorFrontHelper'))
{
	if (file_exists($TjvendorFrontHelper)) {
		require_once $TjvendorFrontHelper;
	}
}

TJVendors::init();

// Execute the task.
$controller = BaseController::getInstance('Tjvendors');
$controller->execute(Factory::getApplication()->getInput()->get('task'));
$controller->redirect();
