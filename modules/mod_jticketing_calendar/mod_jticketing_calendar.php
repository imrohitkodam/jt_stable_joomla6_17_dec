<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Helper\ModuleHelper;

$input = Factory::getApplication()->input;
require_once JPATH_SITE . "/components/com_jticketing/helpers/main.php";
require_once JPATH_SITE . "/components/com_jticketing/helpers/event.php";
require_once JPATH_SITE . "/components/com_jticketing/models/calendar.php";
$lang      = Factory::getLanguage();
$extension = 'com_jticketing';
$base_dir  = JPATH_SITE;
$lang->load($extension, $base_dir);
$JticketingModelCalendar = new JticketingModelCalendar;
$com_params = ComponentHelper::getParams('com_jticketing');
$integration = $com_params->get('integration');
$input = Factory::getApplication()->input;
$option = $input->get('option', '', 'STRING');
$view = $input->get('view', '', 'STRING');

if ($integration < 1)
{
	// Native Event Manager.
	echo Text::_('COMJTICKETING_INTEGRATION_NOTICE');

	return false;
}

if ($option == 'com_jticketing' and $view == 'calendar')
{
	return false;
}

if (file_exists(JPATH_ROOT . '/media/techjoomla_strapper/tjstrapper.php'))
{
	require_once JPATH_ROOT . '/media/techjoomla_strapper/tjstrapper.php';
	TjStrapper::loadTjAssets('com_jticketing');
}

// Category fillter
$state         = $JticketingModelCalendar->get('State');
$cat_options   = JT::model('events')->getEventCategories();
require	ModuleHelper::getLayoutPath('mod_jticketing_calendar');
