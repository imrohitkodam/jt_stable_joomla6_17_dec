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

// Load frontend venues model
if (file_exists(JPATH_SITE . '/components/com_jticketing/models/orders.php')) { require_once JPATH_SITE . '/components/com_jticketing/models/orders.php'; }
