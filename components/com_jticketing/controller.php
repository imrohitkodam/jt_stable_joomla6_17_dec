<?php
/**
 * @package     JTicketing
 * @subpackage  com_jticketing
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die(';)');

use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;

/**
 * Main controller of JTicketing
 *
 * @since  1.0.0
 */
class JticketingController extends BaseController
{
	/**
	 * Method to display a view.
	 *
	 * @param   boolean  $cachable   If true, the view output will be cached
	 * @param   string   $urlparams  An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return  mixed       This object to support chaining.
	 *
	 * @since   1.5
	 */
	public function display($cachable = false, $urlparams = false)
	{
		$view   = $this->input->get('view');
		$layout = $this->input->get('layout');
		$id     = $this->input->getInt('id');

		// Check for edit form.
		if ($view == 'eventform' && $layout == 'edit' && !$this->checkEditId('com_jticketing.edit.eventform', $id))
		{
			$this->setMessage(Text::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id), 'error');

			$JTRouteHelper = new JTRouteHelper;
			$link     = 'index.php?option=com_jticketing&view=events&layout=my';
			$url = $JTRouteHelper->JTRoute($link);

			// Check if there is a return value
			$return = $this->input->get('return', null, 'base64');

			if (!is_null($return) && Uri::isInternal(base64_decode($return)))
			{
				$url = base64_decode($return);
			}

			// Redirect to the list screen.
			$this->setRedirect(Route::_($url, false));

			return false;
		}

		return parent::display();
	}

	/**
	 * Function to update easysocial APP for my events
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function updateEasysocialApp()
	{
		$lang = Factory::getLanguage();
		$lang->load('plg_app_user_jticketMyEvents', JPATH_ADMINISTRATOR);

		// Get storeid,useris and total from ajax responce.
		$input = Factory::getApplication()->getInput();
		$category_id = $input->get('category_id', '', 'INT');
		$userid = $input->get('uid', '', 'INT');
		$limit = $input->get('total', '', 'INT');

		// Load app modal getitem function.
		require_once JPATH_SITE . '/components/com_jticketing/helpers/event.php';
		require_once JPATH_SITE . '/components/com_jticketing/models/events.php';
		$app    = Factory::getApplication();
		$app->getInput()->set('filter_creator', $userid);
		$app->getInput()->set('filter_events_cat', $category_id);
		$JticketingModelEvents = new JticketingModelEvents;
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query = $JticketingModelEvents->getListQuery();

		if ($limit)
		{
			$query .= ' limit ' . $limit;
		}

		$db->setQuery($query);
		$events = $db->LoadObjectList();
		$app->getInput()->set('filter_events_cat', '');
		$query_total = $db->getQuery(true);
		$query_total = $JticketingModelEvents->getListQuery();
		$db->setQuery($query_total);
		$events_total_data = $db->LoadObjectList();
		$event_count = count($events_total_data);

		// Set events return by modal of easysocial app.
		$this->set('events', $events);
		$this->set('total', $event_count);
		$Itemid = JT::utilities()->getItemId('index.php?option=com_jticketing&view=events');
		$allevent_link = Uri::root() . substr(Route::_('index.php?option=com_jticketing&view=events&Itemid=' . $Itemid), strlen(Uri::base(true)) + 1);

		if ($events)
		{
			$random_container = 'jticket_pc_es_app_my_products';
			$html = '<div id="jticket_pc_es_app_my_products">';

			foreach ($events as $eventdata)
			{
				ob_start();
				include JPATH_SITE . '/components/com_jticketing/views/events/tmpl/eventpin.php';
				$html .= ob_get_contents();
				ob_end_clean();
			}

			$html .= '</div>';
			$html .= '<div class="clearfix"></div>';
		}
		else
		{
			$user = Factory::getUser($userid);
			$html  = '<div class="empty" style="display:block;">';
			$html .= Text::sprintf('APP_JTICKETMYEVENTS_NO_EVENTS_FOUND', $user->name);
			$html .= '</div>';
		}

		if ($event_count > $limit)
		{
			$html .= "
			<div class='row-fluid span12'>
				<div class='pull-right'>
					<a href='" . $allevent_link . "'>" . Text::_('APP_JTICKETMYEVENTS_SHOW_ALL') . " (" . $event_count . ") </a>
				</div>
				<div class='clearfix'>&nbsp;</div>
			</div>";
		}

		$js = 'initiateJticketPins();';
		$data['html'] = $html;
		$data['js'] = $js;
		echo json_encode($data);
		jexit();
	}
}
