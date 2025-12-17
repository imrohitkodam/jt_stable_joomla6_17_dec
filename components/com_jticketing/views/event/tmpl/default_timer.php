<?php
/**
 * @package     JTicketing
 * @subpackage  com_jticketing
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;

$document = Factory::getDocument();
$renderer = $document->loadRenderer('modules');
$input = Factory::getApplication()->getInput();
$eventDisplayTitle = $input->set('ev_dtitle', 1);
$input->set('ev_tit','');
$input->set('ev_ddate', 1);
$input->set('ev_ddate_format', 1);
$input->set('ev_d', 30);
$input->set('ev_m', 11);
$input->set('ev_y', 2015);
$input->set('ev_ddleft', 1);
$input->set('ev_dhour', 1);
$input->set('ev_h', 0);
$input->set('ev_min', 0);
$input->set('ev_dlink', 1);
$input->set('ev_ltitle', '');
$input->set('ev_l', '');
$input->set('ev_js', 1);
$input->set('ev_endtime', 'Time\'s Up');
echo $renderer->render('jt_event_custom_position','',null);

