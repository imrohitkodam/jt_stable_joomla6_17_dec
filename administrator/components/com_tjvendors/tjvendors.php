<?php
/**
 * @package     TJVendors
 * @subpackage  com_tjvendors
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use Joomla\Filesystem\File;
use Joomla\CMS\MVC\Controller\BaseController;

// Access check.
if (!Factory::getUser()->authorise('core.manage', 'com_tjvendors'))
{
	throw new Exception(Text::_('JERROR_ALERTNOAUTHOR'));
}

// Include dependancies
include_once  JPATH_SITE . '/components/com_tjvendors/includes/tjvendors.php';

$tjStrapperPath = JPATH_SITE . '/media/techjoomla_strapper/tjstrapper.php';

if (File::exists($tjStrapperPath))
{
	require_once $tjStrapperPath;
	TjStrapper::loadTjAssets('com_tjvendors');
}

spl_autoload_register(function ($class) {
	if (strpos($class, 'Tjvendors') === 0) {
		$path = JPATH_COMPONENT_ADMINISTRATOR . '/' . strtolower(substr($class, 9)) . '.php';
		if (file_exists($path)) {
			require_once $path;
		}
	}
});

$controller = BaseController::getInstance('Tjvendors');
$controller->execute(Factory::getApplication()->getInput()->get('task'));
$controller->redirect();
$document = Factory::getDocument();
$document->addScript(Uri::root(true) . '/media/com_tjvendor/js/tjvendors.js');

$tjvendorFrontHelper = JPATH_ROOT . '/components/com_tjvendors/helpers/fronthelper.php';

if (!class_exists('TjvendorFrontHelper'))
{
	if (file_exists($tjvendorFrontHelper)) {
		require_once $tjvendorFrontHelper;
	}
}

$tjvendorsHelper = JPATH_ADMINISTRATOR . '/components/com_tjvendors/helpers/tjvendors.php';

if (!class_exists('TjvendorsHelper'))
{
	if (file_exists($tjvendorsHelper)) {
		require_once $tjvendorsHelper;
	}
}

TJVendors::init();
