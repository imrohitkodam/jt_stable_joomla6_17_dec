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

use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;

/**
 * View class for a list of Jticketing.
 *
 * @since  1.6
 */
class JticketingViewPDFTemplates extends BaseHtmlView
{
	/**
	 * The user object
	 *
	 * @var  \JUser|null
	 *
	 * @since  2.4.0
	 */
	protected $user;

	/**
	 * Jticketing Config Parameter
	 *
	 * @since  2.4.0
	 */
	protected $params;

	/**
	 * @var  \JPagination
	 *
	 * @since  2.4.0
	 */
	protected $pagination;

	/**
	 * The model state
	 *
	 * @var  CMSObject
	 *
	 * @since  2.4.0
	 */
	protected $state;

	/**
	 * The coupons object
	 *
	 * @var  \stdClass
	 *
	 * @since  2.4.0
	 */
	protected $items;

	/**
	 * @var  \JForm
	 *
	 * @since  2.4.0
	 */
	public $filterForm;

	/**
	 * @var  array
	 *
	 * @since  2.4.0
	 */
	public $activeFilters;

	/**
	 * Function Adding toolbar action on coupons list view
	 *
	 * @since  2.4.0
	 */
	protected $addTJtoolbar;

	/**
	 * tjvendor table object
	 *
	 * @since  2.4.0
	 */
	protected $tjvendorTable;

	/**
	 * JTicketing Integrationxref table object
	 *
	 * @since  2.4.0
	 */
	protected $jticketingTableIntegrationxref;

	/**
	 * An ACL object to verify user rights.
	 *
	 * @var    JObject
	 * @since  2.4.0
	 */
	public $canEdit;

	/**
	 * An ACL object to verify user rights.
	 *
	 * @var    JObject
	 * @since  2.4.0
	 */
	public $canCheckin;

	/**
	 * An ACL object to verify user rights.
	 *
	 * @var    JObject
	 * @since  2.4.0
	 */
	public $canChange;

	public $utilities;
	/**
	 * Display the view
	 *
	 * @param   string  $tpl  An optional associative array.
	 *
	 * @return  mixed Array|False
	 *
	 * @since  1.6
	 */
	public function display($tpl = null)
	{
		$this->params = ComponentHelper::getParams('com_jticketing');
		$this->utilities     = JT::utilities();
		$app          = Factory::getApplication();

		$this->user          = Factory::getUser();
		$this->state         = $this->get('State');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');
		$this->items         = $this->get('Items');
		$this->pagination    = $this->get('Pagination');

		$this->canEdit    = $this->user->authorise('core.edit', 'com_jticketing');
		$this->canChange  = $this->user->authorise('core.edit.state', 'com_jticketing');
		$this->canCheckin = $this->user->authorise('core.manage', 'com_jticketing');

		// Validate user login.
		if (empty($this->user->id))
		{
			$msg = Text::_('COM_JTICKETING_MESSAGE_LOGIN_FIRST');

			// Get current url.
			$current = Uri::getInstance()->toString();
			$url     = base64_encode($current);
			$app->enqueueMessage($msg);
			$app->redirect(Route::_('index.php?option=com_users&view=login&return=' . $url, false));
		}

		$menu       = $app->getMenu();
		$menuItem   = $menu->getItems('link', 'index.php?option=com_jticketing&view=pdftemplates', true);

		$this->listingPageItemId = 0;

		if (!empty($menuItem->id))
		{
			$this->listingPageItemId = $menuItem->id;
		}

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors));
		}

		Table::addIncludePath(JPATH_ROOT . '/administrator/components/com_tjvendors/tables');
		$this->tjvendorTable = Table::getInstance('vendor', 'TJVendorsTable', array());
		$this->jticketingTableIntegrationxref = Table::getInstance('integrationxref', 'JTicketingTable', array());

		// @TODO Change this to getEventName() from class.
		$this->jticketingmainhelper = new Jticketingmainhelper;

		$this->addTJtoolbar();
		$this->sidebar = ""; // Joomla 6: HTMLHelperSidebar::render() removed

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
			$tjbar->appendButton('pdftemplate.add', 'TJTOOLBAR_NEW', '', 'class="btn btn-sm btn-success"');
		}

		if ($canDo->{'core.edit.own'} && isset($this->items[0]))
		{
			$tjbar->appendButton('pdftemplate.edit', 'TJTOOLBAR_EDIT', '', 'class="btn btn-sm btn-success"');
		}

		if ($canDo->{'core.edit.state'})
		{
			if (isset($this->items[0]))
			{
				$tjbar->appendButton('pdftemplates.delete', 'TJTOOLBAR_DELETE', '', 'class="btn btn-sm btn-danger"');
			}
		}

		$this->toolbarHTML = $tjbar->render();
	}
}
