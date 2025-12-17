<?php
/**
 * @package     Jlike
 * @subpackage  com_jlike
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access.
defined('_JEXEC') or die;
use Joomla\CMS\MVC\Controller\AdminController;

if (file_exists(JPATH_SITE . "/libraries/techjoomla/controller/houseKeeping.php")) {
	require_once JPATH_SITE . "/libraries/techjoomla/controller/houseKeeping.php";
}

/**
 * Vendors list controller class.
 *
 * @since  1.6
 */
class JlikeControllerDashboard extends AdminController
{
	use TjControllerHouseKeeping;
}
