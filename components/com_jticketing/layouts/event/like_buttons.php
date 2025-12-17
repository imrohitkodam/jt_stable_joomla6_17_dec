<?php
/**
 * @package     JTicketing
 * @subpackage  com_jticketing
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
Use Joomla\CMS\Plugin\PluginHelper;

$event                  = $displayData;
$show_comments          = $this->getOptions()->get('show_comments');
$show_like_buttons      = $this->getOptions()->get('show_like_buttons');
$jlikeparams            = array();
$jlikeparams['url']     = $this->getOptions()->get('eventUrl');
$jlikeparams['eventid'] = (int) $event->id;
$jlikeparams['title']   = $event->title;

PluginHelper::importPlugin('content', 'jlike_events');
$grt_response = Factory::getApplication()->triggerEvent('onBeforeDisplaylike', array('com_jticketing.event', $jlikeparams, $show_comments, $show_like_buttons));

if (!empty($grt_response['0']))
{
	echo $grt_response['0'];
}
