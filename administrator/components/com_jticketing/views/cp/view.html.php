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
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * Main view class
 *
 * @package     JTicketing
 * @subpackage  component
 * @since       1.0
 */

class JticketingViewcp extends HtmlView
{
	public $utilities;

	public $eventUrl;

	/**
	 * Function to display.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths
	 *
	 * @return  void.
	 *
	 * @since	1.8
	 */
	public function display($tpl = null)
	{
		$model                    = $this->getModel();
		$com_params               = ComponentHelper::getParams('com_jticketing');
		$this->downloadid         = $com_params->get('downloadid');
		$this->currency           = $com_params->get('currency');
		$this->siteadmin_comm_per = $com_params->get('siteadmin_comm_per');
		$input             = Factory::getApplication()->getInput();
		$layout            = $input->get('layout');
		$model             = $this->getModel();
		$this->ordersArray = $model->getOrdersArray();
		$this->salesArray  = $model->getSalesArray();

		if (isset($this->siteadmin_comm_per) and $this->siteadmin_comm_per > 0)
		{
			$this->commisionsArray = $model->getCommisionsArray();
		}

		$this->ticketSalesLastweek = $model->getTicketSalesLastweek();
		$com_params     = ComponentHelper::getParams('com_jticketing');
		$this->currency = $com_params->get('currency');

		// Get data from the model
		$orderscount                   = $this->get('orderscount');
		$this->latestVersion           = $model->getLatestVersion();
		$tot_periodicorderscount       = $this->get('periodicorderscount');
		$this->tot_periodicorderscount = $tot_periodicorderscount;
		$statsforbar                   = $model->statsforbar();
		$this->statsforbar             = $statsforbar;
		$this->getTjHousekeepingData   = $model->getTjHousekeepingData();

		if (empty($this->getTjHousekeepingData))
		{
			$this->returnUrl = Route::_('index.php?option=com_tjnotifications&view=notifications&extension=com_jticketing');
			Factory::getApplication()->enqueueMessage(Text::sprintf('COM_JTICKETING_COMPONENT_EMAIL_TEMPLATE_WARNING', $this->returnUrl), 'Warning');
		}

		// Calling line-graph function
		$this->statsForPie = $model->statsForPie();

		// Get data from the model
		$this->allincome     = $this->get('AllOrderIncome');
		$this->monthIncome   = $this->get('MonthIncome');
		$this->allMonthName  = $this->get('Allmonths');
		$this->topFiveEvents = $this->get('TopFiveEvents');
		$this->dashboardData = $this->get('DashboardData');

		if ($this->dashboardData['integrationSource'] == 'com_jevents')
		{
			$this->eventUrl = 'index.php?option=com_jevents&task=icalevent.list';
		}

		if ($this->dashboardData['integrationSource'] == 'com_community')
		{
			$this->eventUrl = 'index.php?option=com_community&view=events';
		}

		if ($this->dashboardData['integrationSource'] == 'com_easysocial')
		{
			$this->eventUrl = 'index.php?option=com_easysocial&view=events';
		}

		// Get installed version from xml file
		$xml           = simplexml_load_file(JPATH_ADMINISTRATOR . '/components/com_jticketing'. '/jticketing.xml');
		$version       = (string) $xml->version;
		$this->version = $version;
		$model         = $this->getModel();
		$model->refreshUpdateSite();
		$JticketingHelper = new JticketingHelper;
		$JticketingHelper->addSubmenu('cp');
		$this->_setToolBar();

		if (!$layout)
		{
			$this->setLayout('default');
		}

		// Joomla 6: HTMLHelperSidebar::render() removed - sidebar functionality removed

		$this->utilities = JT::utilities();

		parent::display($tpl);
	}

	/**
	 * Function to set tool bar.
	 *
	 * @return void
	 *
	 * @since	1.8
	 */
	public function _setToolBar()
	{
		$document = Factory::getDocument();
		HTMLHelper::_('stylesheet', 'components/com_jticketing/assets/css/jticketing.css');
		$bar = Toolbar::getInstance('toolbar');

			ToolbarHelper::custom('cp.migrate', 'refresh', 'refresh', 'JTOOLBAR_MIGRATE', false);
			ToolbarHelper::title(Text::_('COM_JTICKETING_COMPONENT') . Text::_('COM_JTICKETING_COMPONENT_DASHBOARD'), 'dashboard');

		$input = Factory::getApplication()->getInput();

		$toolbar = Toolbar::getInstance('toolbar');
		$toolbar->appendButton(
			'Custom', '&nbsp;&nbsp;<a id="tjHouseKeepingFixDatabasebutton" class="btn btn-default hidden"><span class="icon-refresh"></span>'
			. '&nbsp;' . Text::_('COM_JTICKETING_MIGRATE') . '</a>'
		);

		ToolbarHelper::preferences('com_jticketing');
	}
}
