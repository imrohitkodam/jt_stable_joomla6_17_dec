<?php
/**
 * @package     JLike
 * @subpackage  com_jlike
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * View class for a list of Jlike.
 *
 * @since  1.0.0
 */
class JLikeViewDashboard extends HtmlView
{
	protected $downloadid;

	protected $version;

	protected $latestVersion;

	protected $linechart;

	protected $data;

	protected $mostLikedData;

	protected $checkMigrate;

	protected $todate;

	protected $fromdate;

	protected $sidebar;

	protected $hasNotificationTmpls;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a Error object.
	 *
	 * @since  1.0.0
	 */
	public function display($tpl = null)
	{
		global $option, $mainframe;

		// Get download id
		$params           = ComponentHelper::getParams('com_jlike');
		$this->downloadid = $params->get('downloadid');

		// Get installed version from xml file
		$xml           = simplexml_load_file(JPATH_COMPONENT . '/jlike.xml');
		$version       = (string) $xml->version;
		$this->version = $version;

		$model = $this->getModel();
		$model->refreshUpdateSite();

		// Get new version
		$this->latestVersion = $this->get('LatestVersion');

		$JlikeHelper = new JLikeHelper;
		$JlikeHelper->addSubmenu('dashboard');

		// Get chart data
		$linechart       = $this->get('LineChartValues');
		$this->linechart = $linechart;

		// Get other required data necessary for dashboard
		$this->data = $this->get('DashboardData');

		// Get Most liked content
		$this->mostLikedData = $this->_GetMostLikes();

		$checkMigrate       = $this->get('checkMigrate');
		$this->checkMigrate = $checkMigrate;

		$this->hasNotificationTmpls = $model->hasNotificationTemplates();

		$input = Factory::getApplication()->getInput();
		$post  = $input->getArray($_POST);

		if (isset($post['todate']))
		{
			$to_date = $post['todate'];
		}
		else
		{
			$to_date = date('Y-m-d', strtotime(date('Y-m-d') . ' + 1 days'));
		}

		if (isset($post['fromdate']))
		{
			$from_date = $post['fromdate'];
		}
		else
		{
			$from_date = date('Y-m-d', strtotime(date('Y-m-d') . ' - 30 days'));
		}

		$this->todate   = $to_date;
		$this->fromdate = $from_date;

		$this->sidebar = '';
		// Default layout is default.php
		$layout = Factory::getApplication()->getInput()->get('layout', 'dashboard');
		$this->setLayout($layout);

		$this->_setToolbar();

		parent::display($tpl);
	}

	/**
	 * Get Most liked content
	 *
	 * @return  integer
	 *
	 * @since   2.5
	 * @todo    Purge updates has to be replaced with an events system
	 */
	protected function _GetMostLikes()
	{
		require_once JPATH_SITE . '/components/com_jlike/helper.php';
		$jlikehelperobj = new comjlikeHelper;

		return $mostlikes = $jlikehelperobj->GetMostLikes(10);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since  1.6
	 */
	protected function _setToolbar()
	{
		$params = ComponentHelper::getParams('com_jlike');
		ToolbarHelper::title(Text::_('COM_JLIKE_DASHBOARD'), 'jlike.png');

		/*// To Display database fix button
		if ($params->get('get_fixdb_btn', '0') == 1)
		{
			ToolbarHelper::custom('database.fix', 'refresh', 'refresh', 'COM_JLIKE_TOOLBAR_DATABASE_FIX', false);
		}*/

		if (!$this->hasNotificationTmpls)
		{
			ToolbarHelper::custom('database.addNotificationTmpls', 'refresh', 'notification', 'COM_JLIKE_TOOLBAR_ADD_NOTIFICATION_TEMPLATES', false);
		}

		$toolbar = Toolbar::getInstance('toolbar');
		$toolbar->appendButton(
			'Custom', '<a id="tjHouseKeepingFixDatabasebutton" class="btn btn-default hidden"><span class="icon-refresh"></span>'
			. ' ' . Text::_('COM_JLIKE_FIX_DATABASE') . '</a>'
		);

		ToolbarHelper::preferences('com_jlike');
	}
}
