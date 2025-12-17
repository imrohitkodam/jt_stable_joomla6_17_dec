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

use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\String\StringHelper;
use Joomla\CMS\Component\ComponentHelper;

/**
 * View class for a list of allticketsales.
 *
 * @since  1.6
 */
class JticketingViewallticketsales extends HtmlView
{
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
		HTMLHelper::_('bootstrap.tooltip');
		HTMLHelper::_('behavior.multiselect');

		$mainframe                  = Factory::getApplication();
		$input                      = Factory::getApplication()->getInput();
		$this->jticketingmainhelper = new jticketingmainhelper;
		$params                     = ComponentHelper::getParams('com_jticketing');
		$integration                = $params->get('integration');
		$JticketingHelper           = new JticketingHelper;

		$JticketingHelper->addSubmenu('allticketsales');

		// Native Event Manager.
		if ($integration < 1)
		{
			$this->sidebar = ""; // Joomla 6: HTMLHelperSidebar::render() removed

			ToolbarHelper::preferences('com_jticketing');
		?>
			<div class="alert alert-info alert-help-inline">
			<?php echo Text::_('COMJTICKETING_INTEGRATION_NOTICE');?>
			</div>
		<?php
			return false;
		}

		$option       = $input->get('option');
		$search_event = $mainframe->getUserStateFromRequest($option . 'search_event', 'search_event', '', 'string');
		$search_event = StringHelper::strtolower($search_event);
		$user         = Factory::getUser();
		$layout       = Factory::getApplication()->getInput()->get('layout', 'default');
		$status_event = array();
		$eventsModel  = JT::model('events');
		$eventlist    = $eventsModel->getItems();
		$this->filterForm = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');
		$status_event[] = HTMLHelper::_('select.option', '', Text::_('SELONE_EVENT'));

		if (!empty($eventlist))
		{
			foreach ($eventlist as $key => $event)
			{
				$eventObj = JT::event($event->id);

				if ($eventObj->getTitle())
				{
					$status_event[] = HTMLHelper::_('select.option', $eventObj->getId(), $eventObj->getTitle());
				}
			}
		}

		$eventid = Factory::getApplication()->getInput()->get('event');

		$this->status_event = $status_event;

		$this->user_filter_options = $this->get('UserFilterOptions');

		$user_filter = $mainframe->getUserStateFromRequest('com_jticketing' . 'user_filter', 'user_filter');

		$filter_order_Dir     = $mainframe->getUserStateFromRequest('com_jticketing.filter_order_Dir', 'filter_order_Dir', 'desc', 'word');
		$filter_type           = $mainframe->getUserStateFromRequest('com_jticketing.filter_order', 'filter_order', 'id', 'string');

		$lists['search_event'] = $search_event;

		$Data 	    = $this->get('Data');
		$pagination = $this->get('Pagination');

		$Itemid = $input->get('Itemid');

		if (empty($Itemid))
		{
			$Session = Factory::getSession();
			$Itemid  = $Session->get("JT_Menu_Itemid");
		}

		$this->Data         = $Data;
		$this->pagination   = $pagination;
		$this->lists        = $lists;
		$this->Itemid       = $Itemid;
		$this->status_event = $status_event;

		$title              = '';
		$lists['order_Dir'] = '';
		$lists['order']     = '';
		$title              = $mainframe->getUserStateFromRequest('com_jticketing' . 'title', '', 'string');
		$model        = JT::model('allticketsales', array('ignore_request' => true));
		$model->setState('limit', 0);
		$model->setState('limitstart', 0);
		$this->allData  = $model->getData();
		$this->totalnooftickets = 0;
		$this->totalamount = 0;
		$this->totaloriginalamt = 0;
		$this->totaldiscount = 0;
		$this->totalordertax = 0;
		$this->totalcommission = 0;
		$this->amtafterDisc = 0;
		$this->amtToBePaidEventowner = 0;

		foreach($this->allData as $data)
		{
			$this->totalnooftickets += $data->eticketscount;
			$this->totalamount += $data->eamount;
			$this->amtafterDisc += ($data->eoriginal_amount-$data->ecoupon_discount);
			$this->totaloriginalamt += $data->eoriginal_amount;
			$this->totaldiscount += $data->ecoupon_discount;
			$this->totalordertax += $data->eorder_tax;
			$this->totalcommission += $data->ecommission;
			$this->amtToBePaidEventowner += ($data->eamount-$data->ecommission);
		}


		if ($title == null)
		{
			$title = '-1';
		}

		$lists['title']       = $title;
		$lists['order_Dir']   = $filter_order_Dir;
		$lists['order']       = $filter_type;
		$lists['pagination']  = $pagination;

		$lists['user_filter'] = $user_filter;
		$this->lists          = $lists;

		// Joomla 6: JVERSION check removed
		if (false) // Legacy >= '3.0' && JVERSION < '4.0')
		{
			JHtmlBehavior::framework();
		}

		$this->setToolBar();

		$this->sidebar = ""; // Joomla 6: HTMLHelperSidebar::render() removed

		$this->setLayout($layout);
		$this->utilities = JT::utilities();

		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since	1.6
	 */
	public function setToolBar()
	{
		// JToolbarHelper::title(Text::_('COM_USERS_VIEW_USERS_TITLE'), 'user');
		$document = Factory::getDocument();
		HTMLHelper::_('stylesheet', 'components/com_jticketing/css/jticketing.css');
		$bar      = Toolbar::getInstance('toolbar');

		ToolbarHelper::title(Text::_('COM_JTICKETING_COMPONENT') . Text::_('COM_JTICKETING_SALES_VIEW'), 'dashboard');

		ToolbarHelper::back('COM_JTICKETING_HOME', 'index.php?option=com_jticketing&view=cp');

		$layout = Factory::getApplication()->getInput()->get('layout', 'default');

		if ($layout == 'default')
		{
			// Joomla 6: HTMLHelperSidebar::setAction() removed
		}

		ToolbarHelper::preferences('com_jticketing');
	}
}
