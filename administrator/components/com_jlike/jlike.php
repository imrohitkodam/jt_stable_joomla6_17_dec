<?php
/**
 * @package     JLike
 * @subpackage  com_jlike
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access
defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Filesystem\File;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Uri\Uri;

// Access check.
if (!Factory::getUser()->authorise('core.manage', 'com_jlike'))
{
	throw new Exception(Text::_('JERROR_ALERTNOAUTHOR'));
}

$tjStrapperPath = JPATH_SITE . '/media/techjoomla_strapper/tjstrapper.php';

if (File::exists($tjStrapperPath))
{
	require_once $tjStrapperPath;
	TjStrapper::loadTjAssets('com_jlike');
}

$document = Factory::getDocument();
$document->addStyleSheet(Uri::root() . 'components/com_jlike/assets/css/like.css');
$document->addStyleSheet(Uri::base() . 'components/com_jlike/assets/css/like.css');
$helperPath = JPATH_SITE . '/' . 'components/com_jlike/helper.php';

if (!class_exists('comjlikeHelper'))
{
	// Require_once $path;
	if (file_exists($helperPath)) {
		require_once $helperPath;
	}
}
// Load laguage constant in javascript
ComjlikeHelper::getLanguageConstant();

$helperPath = JPATH_ADMINISTRATOR . '/' . 'components/com_jlike/helpers/jlike.php';

if (!class_exists('JLikeHelper'))
{
	// Require_once $path;
	if (file_exists($helperPath)) {
		require_once $helperPath;
	}
}

// Load bootstrap on joomla > 3.0 ; This option will be usefull if site is joomla 3.0 but not a bootstrap template
if (JVERSION > '3.0')
{
	$params = ComponentHelper::getParams('com_jlike');
	$load_bootstrap = $params->get('load_bootstrap');

	// Check config
	if ($load_bootstrap)
	{
		// Load bootstrap CSS.
		HTMLHelper::_('bootstrap.loadcss');
	}
}

// Include dependancies

$controller	= BaseController::getInstance('Jlike');
$controller->execute(Factory::getApplication()->getInput()->get('task'));
$controller->redirect();
