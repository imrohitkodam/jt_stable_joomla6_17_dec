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
use Joomla\CMS\Router\Route;

/** @var $this JticketingViewOrder */

$session   = Factory::getSession();
$session->set('JT_orderid', '');
$session->set("JT_fee", '');
echo $msg = Text::_('OPERATION_CANCELLED');

$user = Factory::getUser();
$input   = Factory::getApplication()->getInput();
$eventid = $input->get('eventid', '', 'INT');

$linkCreateEvent = '';
$itemId      = $this->utilities->getItemId($linkCreateEvent);
$integration = JT::getIntegration(true);

if ($integration == 2)
{
	$linkCreateEvent = Route::_(Uri::base() . '?option=com_jticketing&view=events' . '&Itemid=' . $itemId);
}

if ($integration == 3)
{
	$linkCreateEvent = Route::_(Uri::base() . '?option=com_jevents&task=month.calendar' . '&Itemid=' . $itemId);
}

if ($integration == 1)
{
	$linkCreateEvent = Route::_(Uri::base() . '?option=com_community&view=events&task=viewevent' . '&Itemid=' . $itemId);
}

echo "<div style='float:right'><a href='" . $linkCreateEvent . "'>" . Text::_('BACK') . "</a></div>";
