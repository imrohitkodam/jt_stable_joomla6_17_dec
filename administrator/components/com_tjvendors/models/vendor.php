<?php
/**
 * @package     TJVendors
 * @subpackage  com_tjvendors
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access.
defined('_JEXEC') or die;

/**
 * Tjvendors model.
 *
 * @since  1.6
 */
$vendorModelPath = JPATH_SITE . '/components/com_tjvendors/models/vendor.php';
if (file_exists($vendorModelPath)) {
	require_once $vendorModelPath;
}
