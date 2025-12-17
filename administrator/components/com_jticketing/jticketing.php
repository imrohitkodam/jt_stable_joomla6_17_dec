<?php
declare(strict_types=1);

/**
 * @package     JTicketing
 * @subpackage  com_jticketing
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\MVC\Controller\BaseController;

defined('_JEXEC') or die('Restricted access');

// Access check.
if (!Factory::getUser()->authorise('core.manage', 'com_jticketing'))
{
	throw new Exception(Text::_('JERROR_ALERTNOAUTHOR'));
}

if (!defined('DS'))
{
	define('DS', DIRECTORY_SEPARATOR);
}

define('JTICKETING_WRAPPER_CLASS', 'jticketing-wrapper');

if (file_exists(JPATH_ROOT . '/media/techjoomla_strapper/tjstrapper.php'))
{
	require_once JPATH_ROOT . '/media/techjoomla_strapper/tjstrapper.php';
	TjStrapper::loadTjAssets('com_jticketing');
}

require_once JPATH_SITE . "/components/com_jticketing/helpers/main.php";
require_once JPATH_SITE . "/components/com_jticketing/helpers/frontendhelper.php";
require_once JPATH_SITE . "/components/com_jticketing/helpers/order.php";

// Get bootstrap

$JticketingHelperadmin = JPATH_ADMINISTRATOR . '/components/com_jticketing/helpers/jticketing.php';

if (!class_exists('JticketingHelperadmin'))
{
	JLoader::register('JticketingHelperadmin', $JticketingHelperadmin);
	JLoader::load('JticketingHelperadmin');
}

$jticketingfrontendhelper = JPATH_ROOT . '/components/com_jticketing/helpers/frontendhelper.php';

if (!class_exists('jticketingfrontendhelper'))
{
	JLoader::register('jticketingfrontendhelper', $jticketingfrontendhelper);
	JLoader::load('jticketingfrontendhelper');
}

$jteventHelper = JPATH_ROOT . '/components/com_jticketing/helpers/event.php';

if (!class_exists('jteventHelper'))
{
	JLoader::register('jteventHelper', $jteventHelper);
	JLoader::load('jteventHelper');
}

$mediaHelperPath = JPATH_SITE . '/components/com_jticketing/helpers/media.php';

if (!class_exists('jticketingMediaHelper'))
{
	JLoader::register('jticketingMediaHelper', $mediaHelperPath);
	JLoader::load('jticketingMediaHelper');
}

$JticketingmainHelper = JPATH_SITE . '/components/com_jticketing/helpers/main.php';

if (!class_exists('jticketingmainhelper'))
{
	JLoader::register('jticketingmainhelper', $JticketingmainHelper);
	JLoader::load('jticketingmainhelper');
}

$JticketingmainHelper = JPATH_SITE . '/components/com_jticketing/helpers/order.php';

if (!class_exists('JticketingOrdersHelper'))
{
	JLoader::load('JticketingOrdersHelper');
}

// Load JTicketing bootstrap file
include_once  JPATH_SITE . '/components/com_jticketing/includes/jticketing.php';
JT::init('admin');

define('COM_JTICKETING_WRAPPER_CLASS', "jticketing-wrapper");

// Joomla 6: JVERSION check removed
		if (false) // Legacy < '4.0.0')
{
	// Joomla 6: formbehavior.chosen removed - using native select
}

$document = Factory::getDocument();
$rootUrl = Uri::root();

$document->addScriptDeclaration('var jtRootURL= "' . $rootUrl . '";');

$lang = Factory::getLanguage();
$lang->load('com_jticketing_common', JPATH_SITE, $lang->getTag(), true);

$controller = BaseController::getInstance('Jticketing');
$controller->execute(Factory::getApplication()->getInput()->get('task'));
$controller->redirect();
