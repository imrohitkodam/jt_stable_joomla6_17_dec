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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Helper\ModuleHelper;

require_once JPATH_SITE . "/components/com_jticketing/includes/jticketing.php";

$config      = JT::config();
$integration     = $config->get('integration');

if ($integration < 1)
{
	// Native Event Manager.
	echo Text::_('COMJTICKETING_INTEGRATION_NOTICE');

	return false;
}

$session         = Factory::getSession();
$input           = Factory::getApplication()->input;
$Itemid          = $input->get('Itemid', '', 'INT');

if ($Itemid)
{
	$session->set('JT_Itemid', $Itemid);
}

$doc = Factory::getDocument();
$doc->addStyleSheet(Uri::base() . 'modules/mod_jticketing_buy/css/jticketing.css');

require_once JPATH_SITE . "/components/com_jticketing/helpers/main.php";
$jticketingmainhelper = new jticketingmainhelper;

$option = $input->getString('option');
$view = $input->get('view', '');
$task = $input->get('task', '');

// This is only For Joomla Day Site
$eventId = $params->get('eventid', 0);

if ($eventId)
{
	$input->set('eventid', $eventId);
}


// Now get the configuration and check whether the component is the same as per the integration

$integration = JT::getIntegration();

if ($option != $integration)
{
	// Not a valid view or component
	return;
}

switch ($integration)
{
	case "com_jevents":
		$eventId = $input->get('eventid', '', 'INT');

		if (!$eventId)
		{
			if ($task == 'icalevent.detail')
			{
				$eventId = $input->get('evid', '', 'INT');
			}
			elseif ($view == 'icalrepeat' || $task == 'icalrepeat.detail')
			{
				$rpID = $input->get('evid', '', 'INT');

				if ($rpID)
				{
					$eventId = JT::event($rpID, "com_jevents")->getId();
				}
			}
		}
		break;
	case "com_community":
		$eventId = $input->get('eventid', '', 'INT');

		if ($view != "events" && $task != 'viewevent')
		{
			return false;
		}

		break;
	case "com_easysocial":
	case "com_jticketing":
		$eventId = $input->get('id', '', 'INT');
		break;
}

if (!$eventId)
{
	return false;
}

$lang = Factory::getLanguage();
$lang->load('mod_jticketing_buy', JPATH_SITE);

JT::init();

require ModuleHelper::getLayoutPath('mod_jticketing_buy');
