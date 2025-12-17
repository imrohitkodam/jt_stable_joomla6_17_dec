<?php
/**
 * @package     JTicketing
 * @subpackage  com_jticketing
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die();
use Joomla\CMS\MVC\Controller\BaseController;

require_once JPATH_ADMINISTRATOR . '/components/com_jticketing'. '/controller.php';

/**
 * Class for Jticketing Calendar List Controller
 *
 * @package  JTicketing
 * @since    1.5
 */
class JticketingControllercalendar extends BaseController
{
	/**
	 * For calender.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function getEventList()
	{
		include JPATH_ROOT . '/components/com_jticketing/models/calendar.php';
		$ob   = new JticketingModelCalendar;
		$data = $ob->getEvents();
		echo json_encode($data, true);
		jexit();
	}
}
