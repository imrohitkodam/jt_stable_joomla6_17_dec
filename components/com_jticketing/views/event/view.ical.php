<?php
/**
 * @package     JTicketing
 * @subpackage  com_jticketing
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Unauthorized Access');
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Factory;

/**
 * Class for Jticketing Event view
 *
 * @package  JTicketing
 * @since    1.5
 */
class JticketingViewEvent extends HtmlView
{
	/**
	 * Method to display event
	 *
	 * @param   object  $tpl  template name
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function display($tpl = null)
	{
		return $this->export();
	}

	/**
	 * Method to display event
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function export()
	{
		$app         = Factory::getApplication();
		$user        = Factory::getUser();
		$this->state = $this->get('State');

		$input   = $app->input;
		$eventId = $input->get('id', '', 'INT');
		$event   = JT::event($eventId);

		header('Content-type: text/calendar; charset=utf-8');
		require_once JPATH_SITE . '/components/com_jticketing/views/event/tmpl/default_ical.php';
		$ts       = substr(md5(rand(0, 100)), 0, 5);
		$fileName = 'calendar_' . $event->getTitle() . '_' . $ts . '.ics';
		header('Content-Disposition: inline; filename=' . $fileName);
		exit;
	}
}
