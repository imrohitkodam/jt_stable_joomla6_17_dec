<?php
/**
 * @package     JTicketing
 * @subpackage  com_jticketing
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
// Joomla 6: JLoader removed - use require_once
$attendeesControllerPath = JPATH_SITE . '/components/com_jticketing/controllers/attendees.php';
if (file_exists($attendeesControllerPath))
{
	require_once $attendeesControllerPath;
}
