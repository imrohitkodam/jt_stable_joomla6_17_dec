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
defined('_JEXEC') or die;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Toolbar\Toolbar;

/**
 * View class for a list of Jticketing.
 *
 * @since  1.6
 */
class JticketingViewVenues extends HtmlView
{
	protected $items;

	protected $pagination;

	protected $state;

	protected $params;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  Template name
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	public function display($tpl = null)
	{
		global $mainframe, $option;
		$input      = Factory::getApplication()->getInput();
		$mainframe  = Factory::getApplication();
		$this->params = $mainframe->getParams('com_jticketing');
		$user       = Factory::getUser();

		// Validate user login.
		if (empty($user->id))
		{
			$msg = Text::_('COM_JTICKETING_MESSAGE_LOGIN_FIRST');

			// Get current url.
			$current = Uri::getInstance()->toString();
			$url     = base64_encode($current);
			$mainframe->enqueueMessage($msg);
			$mainframe->redirect(Route::_('index.php?option=com_users&view=login&return=' . $url, false));
		}

		$this->state         = $this->get('State');
		$this->items         = $this->get('Items');
		$this->pagination    = $this->get('Pagination');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');
		$option              = $input->get('option');
		$venue_type          = $mainframe->getUserStateFromRequest($option . 'venue_type', 'venue_type', '', 'string');
		$venue_privacy       = $mainframe->getUserStateFromRequest($option . 'venue_privacy', 'venue_privacy', '', 'string');

		$venueTypeList    = array();
		$venueTypeList[]  = HTMLHelper::_('select.option', '', Text::_('COM_JTICKETING_FILTER_SELECT_VENUE_TYPE'));
		$venueTypeList[]  = HTMLHelper::_('select.option', '1', Text::_('COM_JTICKETING_VENUE_TYPEONLINE'));
		$venueTypeList[]  = HTMLHelper::_('select.option', '0', Text::_('COM_JTICKETING_VENUE_TYPEOFFLINE'));

		$this->venueTypeList    = $venueTypeList;
		$lists['venueTypeList'] = $venue_type;

		$venuePrivacyList    = array();
		$venuePrivacyList[]  = HTMLHelper::_('select.option', '', Text::_('COM_JTICKETING_FILTER_SELECT_VENUE_PRIVACY'));
		$venuePrivacyList[]  = HTMLHelper::_('select.option', '1', Text::_('COM_JTICKETING_VENUE_PRIVACY_PUBLIC'));
		$venuePrivacyList[]  = HTMLHelper::_('select.option', '0', Text::_('COM_JTICKETING_VENUE_PRIVACY_PRIVATE'));

		$this->venuePrivacyList    = $venuePrivacyList;
		$lists['venuePrivacyList'] = $venue_privacy;
		$this->lists               = $lists;

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors));
		}

		$this->venuesMenuItemId   = JT::utilities()->getItemId('index.php?option=com_jticketing&view=venues');

		// Get component params
		$this->PageTitle = $this->params->get('page_title', '');
		$this->addTJtoolbar();
		parent::display($tpl);
	}

	/**
	 * Setup ACL based tjtoolbar
	 *
	 * @return  void
	 *
	 * @since   2.2
	 */
	protected function addTJtoolbar()
	{
		JLoader::register('JticketingHelper', JPATH_ADMINISTRATOR . '/components/com_jticketing/helpers/jticketing.php');

		$state = $this->get('State');
		$canDo = JticketingHelper::getActions($state->get('filter.category_id'));

		// Add toolbar buttons
		if (file_exists(JPATH_LIBRARIES . '/techjoomla/tjtoolbar/toolbar.php')) { require_once JPATH_LIBRARIES . '/techjoomla/tjtoolbar/toolbar.php'; }
		$tjbar = TJToolbar::getInstance('tjtoolbar', 'pull-right float-end');

		if ($canDo->{'core.create'})
		{
			$tjbar->appendButton('venueform.add', 'TJTOOLBAR_NEW', '', 'class="btn btn-sm btn-success"');
		}

		if ($canDo->{'core.edit.own'} && isset($this->items[0]))
		{
			$tjbar->appendButton('venueform.edit', 'TJTOOLBAR_EDIT', '', 'class="btn btn-sm btn-success"');
		}

		if ($canDo->{'core.edit.state'})
		{
			if (isset($this->items[0]))
			{
				$tjbar->appendButton('venues.publish', 'TJTOOLBAR_PUBLISH', '', 'class="btn btn-sm btn-success"');
				$tjbar->appendButton('venues.unpublish', 'TJTOOLBAR_UNPUBLISH', '', 'class="btn btn-sm btn-warning"');
			}
		}

		if ($canDo->{'core.edit.state'})
		{
			if (isset($this->items[0]))
			{
				$tjbar->appendButton('venues.delete', 'TJTOOLBAR_DELETE', '', 'class="btn btn-sm btn-danger"');
			}
		}

		$this->toolbarHTML = $tjbar->render();
	}
}
