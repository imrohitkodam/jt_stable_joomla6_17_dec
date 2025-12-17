<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Component\ComponentHelper;

/**
 * View for events
 *
 * @package     JTicketing
 * @subpackage  component
 * @since       1.0
 */
class JticketingViewEvents extends HtmlView
{
	protected $items;

	protected $pagination;

	protected $state;

	protected $params;

	public $creator;

	public $utilities;

	/**
	 * Method to display events
	 *
	 * @param   object  $tpl  tpl
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function display($tpl = null)
	{
		$this->utilities = JT::utilities();
		$app  = Factory::getApplication();
		$user = Factory::getUser();

		// Default layout is default.
		$this->layout = Factory::getApplication()->getInput()->get('layout', 'default');
		$this->setLayout($this->layout);

		if ($this->layout == 'my')
		{
			// Validate user login.
			if (empty($user->id))
			{
				$msg = Text::_('COM_JTICKETING_MESSAGE_LOGIN_FIRST');

				// Get current url.
				$current = Uri::getInstance()->toString();
				$url     = base64_encode($current);
				$app->enqueueMessage($msg);
				$app->redirect(Route::_('index.php?option=com_users&view=login&return=' . $url, false));
			}
		}

		$this->state      = $this->get('State');
		$this->params     = $app->getParams('com_jticketing');

		$model = $this->getModel('events');

		if ($app->getInput()->get('catid', '', 'INT') > 1)
		{
			$model->setState('filter_events_cat', $app->getInput()->get('catid', '', 'INT'));
		}

		$this->PageTitle = $this->params->get('page_title', '');

		$this->items = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->dateFormat = $this->params->get('date_format_show');
		if ($this->dateFormat == "custom")
		{
			$this->dateFormat = $this->params->get('custom_format');
		}

		$this->onlineEventsEnabled = $this->params->get('enable_online_events', 0, 'INT');

		// Get integration set
		$this->integration = $this->params->get('integration', '', 'INT');

		// Native Event Manager.
		if ($this->integration != COM_JTICKETING_CONSTANT_INTEGRATION_NATIVE)
		{
			/** @var $eventsModel JticketingModelEvents */
			$eventsModel = JT::model('events');
			$allEventsLink = $eventsModel->getAllEventsLink($this->integration);

			if ($allEventsLink)
			{
				$app->redirect($allEventsLink);
			}

			$app->enqueueMessage(Text::_('COM_JTICKETING_INVALID_INTEGRATION_SET'), 'message');

			return false;
		}

		// Get ordering filters
		$this->filter_order     = $this->escape($this->state->get('list.ordering'));
		$this->filter_order_Dir = $this->escape($this->state->get('list.direction'));

		// Get itemid.
		$this->jticketingmainhelper = new jticketingmainhelper;
		$this->jticketingTimeHelper = new JticketingTimeHelper;

		$this->create_event_itemid  = $this->utilities->getItemId('index.php?option=com_jticketing&view=eventform');
		$this->event_details_itemid = $this->utilities->getItemId('index.php?option=com_jticketing&view=event');

		// Get itemid
		$this->singleEventItemid = $this->utilities->getItemId('index.php?option=com_jticketing&view=events&layout=default');

		if (empty($this->singleEventItemid))
		{
			$this->singleEventItemid = Factory::getApplication()->getInput()->get('Itemid');
		}

		$this->myEventsItemid     = $this->utilities->getItemId('index.php?option=com_jticketing&view=events&layout=my');
		$this->allEventsItemid    = $this->utilities->getItemId('index.php?option=com_jticketing&view=events&layout=default');
		$this->createEventsItemid = $this->utilities->getItemId('index.php?option=com_jticketing&view=eventform');
		$this->buyTicketItemId = $this->utilities->getItemId('index.php?option=com_jticketing&view=order&layout=default');

		// Category fillter
		$jteventHelper        = new jteventHelper;
		$this->cat_options    = JT::model('events')->getEventCategories();

		// Array of events type to show
		$this->events_to_show = array();
		$this->events_to_show[] = HTMLHelper::_('select.option', 'featured', Text::_('COM_JTK_FEATURED_CAMP'));
		$this->events_to_show[] = HTMLHelper::_('select.option', '0', Text::_('COM_JTK_FILTER_ONGOING'));
		$this->events_to_show[] = HTMLHelper::_('select.option', '-1', Text::_('COM_JTK_FILTER_PAST_EVNTS'));

		// Event type options array.
		$this->event_type   = array();
		$this->event_type[] = HTMLHelper::_('select.option', '', Text::_('COM_JTK_FILTER_SELECT_EVENT_DEFAULT'));
		$this->event_type[] = HTMLHelper::_('select.option', '0', Text::_('COM_JTK_FILTER_SELECT_EVENT_OFFLINE'));
		$this->event_type[] = HTMLHelper::_('select.option', '1', Text::_('COM_JTK_FILTER_SELECT_EVENT_ONLINE'));

		// Get filter value and set list
		$filter_event_cat = $app->getUserStateFromRequest('com_jticketing' . 'filter_events_cat', 'filter_events_cat');
		$lists['filter_events_cat'] = $filter_event_cat;

		// Ordering option
		$default_sort_by_option = $this->params->get('default_sort_by_option');
		$filter_order_Dir = $this->params->get('filter_order_Dir');
		$filter_order     = $app->getUserStateFromRequest('com_jticketing.filter_order', 'filter_order', $default_sort_by_option, 'string');
		$filter_order_Dir = $app->getUserStateFromRequest('com_jticketing.filter_order_Dir', 'filter_order_Dir', $filter_order_Dir, 'string');
		$this->ordering_options           = $this->get('OrderingOptions');
		$this->ordering_direction_options = $this->get('OrderingDirectionOptions');

		// Get days filter
		$this->days_options = $this->get('DayOptions');
		$filter_day = $app->getUserStateFromRequest('com_jticketing' . 'filter_day', 'filter_day');

		$JticketingModelEvents      = JT::model('events');
		$this->creator              = $JticketingModelEvents->getCreator();

		// Event type options array.
		$this->eventTypes   = array();
		$this->eventTypes[] = HTMLHelper::_('select.option', '', Text::_('COM_JTK_FILTER_SELECT_EVENT_DEFAULT'));
		$this->eventTypes[] = HTMLHelper::_('select.option', '0', Text::_('COM_JTK_FILTER_SELECT_EVENT_OFFLINE'));
		$this->eventTypes[] = HTMLHelper::_('select.option', '1', Text::_('COM_JTK_FILTER_SELECT_EVENT_ONLINE'));

		// Price filter
		$this->filterPrice = array(
			HTMLHelper::_('select.option', '', Text::_('COM_JTICKETING_SELECT_PRICE')),
			HTMLHelper::_('select.option', 'free', Text::_('COM_JTICKETING_FREE_EVENTS')),
			HTMLHelper::_('select.option', 'paid', Text::_('COM_JTICKETING_PAID_EVENTS')),
		);

		// Get creator and location filter
		$filter_creator  = $app->getUserStateFromRequest('com_jticketing' . 'filter_creator', 'filter_creator');
		$this->creator   = $this->get('Creator');
		$filter_location = $app->getUserStateFromRequest('com_jticketing' . 'filter_location', 'filter_location');
		$this->location  = $this->get('Location');
		$filter_tags     = $app->getUserStateFromRequest('com_jticketing' . 'filter_tags', 'filter_tags');
		$filter_price    = $app->getUserStateFromRequest('com_jticketing' . 'filter_tags', 'filter_price');
		$onlineEvents    = $app->getUserStateFromRequest('com_jticketing' . 'online_events', 'online_events');

		$advancedFilters   = array();
		$advancedFilters['online_events']     = $this->params->get('show_event_filter', 'basic');
		$advancedFilters['filter_creator']    = $this->params->get('show_creator_filter', 'basic');
		$advancedFilters['filter_events_cat'] = $this->params->get('show_category_filter', 'basic');
		$advancedFilters['filter_location']   = $this->params->get('show_location_filter', 'basic');
		$advancedFilters['filter_day']        = $this->params->get('show_date_filter', 'basic');
		$advancedFilters['filter_price']      = $this->params->get('show_price_filter', 'basic');
		$advancedFilters['filter_tags']       = $this->params->get('show_tags_filter', 'basic');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors));
		}

		// Set all filters in list
		$lists['filter_creator']   = $filter_creator;
		$lists['filter_location']  = $filter_location;
		$lists['filter_day']       = $filter_day;
		$lists['filter_tags']      = $filter_tags;
		$lists['filter_price']     = $filter_price;
		$lists['online_events']    = $onlineEvents;

		// Check if any advanced filter is set.
		$this->showAdvanced = false;

		foreach ($advancedFilters as $key => $filter)
		{
			if ($filter === 'advanced' && !empty($lists[$key]))
			{
				$this->showAdvanced = true;
			}
		}

		$lists['filter_order']     = $filter_order;
		$lists['filter_order_Dir'] = $filter_order_Dir;

		// Search and filter
		$filter_state            = $app->getUserStateFromRequest('com_jticketing' . 'search', 'search', '', 'string');
		$filter_events_to_show   = $app->getUserStateFromRequest('com_jticketing' . 'events_to_show', 'events_to_show');
		$lists['search']         = $filter_state;
		$lists['events_to_show'] = $filter_events_to_show;
		$this->lists = $lists;

		// Escape strings for HTML output.
		$this->pageclass_sfx = htmlspecialchars($this->params->get('pageclass_sfx') ? $this->params->get('pageclass_sfx') : '');
		$this->_prepareDocument();
		
		if ($this->layout == 'my')
		{
			// Setup toolbar
			$this->addTJtoolbar();
			$canDo = JticketingHelper::getActions();
			$this->isCreateDuplicates = $canDo->{'core.create'} ? 1 : 0;
			$this->adminApproval        = $this->params->get('event_approval');
			$this->canChange     = ($user->authorise('core.edit.state', 'com_jticketing') && $this->adminApproval == 0) ? 1 : 0;
		}

		parent::display($tpl);
	}

	/**
	 * Method to display events
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	protected function _prepareDocument()
	{
		$app   = Factory::getApplication();
		$menus = $app->getMenu();
		$title = null;

		// Because the application sets a default page title, we need to get it from the menu item itself
		$menu = $menus->getActive();

		if ($menu)
		{
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		}
		else
		{
			if ($this->layout == "my")
			{
				$this->params->def('page_heading', Text::_('COM_JTICKETING_EVENTS_PAGE_HEADING_MY'));
			}
			else
			{
				$this->params->def('page_heading', Text::_('COM_JTICKETING_EVENTS_PAGE_HEADING'));
			}
		}

		$title = $this->params->get('page_title', '');

		if (empty($title))
		{
			$title = $app->get('sitename');
		}
		elseif ($app->get('sitename_pagetitles', 0) == 1)
		{
			$title = Text::sprintf('JPAGETITLE', $app->get('sitename'), $title);
		}
		elseif ($app->get('sitename_pagetitles', 0) == 2)
		{
			$title = Text::sprintf('JPAGETITLE', $title, $app->get('sitename'));
		}

		$this->document->setTitle($title);

		if ($this->params->get('menu-meta_description'))
		{
			$this->document->setDescription($this->params->get('menu-meta_description'));
		}

		if ($this->params->get('menu-meta_keywords'))
		{
			$this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
		}

		if ($this->params->get('robots'))
		{
			$this->document->setMetadata('robots', $this->params->get('robots'));
		}
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
		require_once JPATH_ADMINISTRATOR . '/components/com_jticketing/helpers/jticketing.php';
		$canDo = JticketingHelper::getActions();
		$app = Factory::getApplication();
		$params  = $app->getParams('com_jticketing');
		$adminApproval = $params->get('event_approval');

		$smallButtonClass = JVERSION < '4.0' ? 'btn-small' : 'btn-sm';

		// Add toolbar buttons
		if (file_exists(JPATH_LIBRARIES . '/techjoomla/tjtoolbar/toolbar.php')) { require_once JPATH_LIBRARIES . '/techjoomla/tjtoolbar/toolbar.php'; }
		$tjbar = TJToolbar::getInstance('tjtoolbar', 'pull-right float-end');

		if ($canDo->{'core.create'})
		{
			$tjbar->appendButton('eventform.add', 'TJTOOLBAR_NEW', '', 'class="btn btn-sm btn-success"');

			if (isset($this->items[0]->state))
			{
				$tjbar->appendButton('events.duplicate', 'COM_JTICKETING_DUPLICATE', 'icon-copy', 'class="btn '. $smallButtonClass .' btn-success"');
			}
		}

		if ($canDo->{'core.edit.own'} && isset($this->items[0]))
		{
			$tjbar->appendButton('eventform.edit', 'TJTOOLBAR_EDIT', '', 'class="btn btn-sm btn-success"');
		}

		if ($canDo->{'core.edit.state'})
		{
			if (isset($this->items[0]))
			{
				if ($adminApproval == 0)
				{
					$tjbar->appendButton('events.publish', 'TJTOOLBAR_PUBLISH', '', 'class="btn btn-sm btn-success"');
					$tjbar->appendButton('events.unpublish', 'TJTOOLBAR_UNPUBLISH', '', 'class="btn btn-sm btn-warning"');
				}

				$tjbar->appendButton('events.delete', 'TJTOOLBAR_DELETE', '', 'class="btn btn-sm btn-danger"');
			}
		}

		$this->toolbarHTML = $tjbar->render();
	}
}
