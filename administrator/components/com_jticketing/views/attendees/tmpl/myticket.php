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
defined('_JEXEC') or die();
ob_start();
include JPATH_SITE . '/components/com_jticketing/views/mytickets/tmpl/ticketprint.php';
$html = ob_get_contents();
ob_end_clean();
echo $html;
