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

use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Helper\ContentHelper;

/**
 * Coupons view class.
 *
 * @since  2.4.0
 */
class JticketingViewCoupons extends BaseHtmlView
{
	/**
	 * The user object
	 *
	 * @var  \JUser|null
	 */
	protected $user;

	/**
	 * The model state
	 *
	 * @var  Joomla\CMS\Object\CMSObject
	 */
	protected $state;

	/**
	 * The coupons object
	 *
	 * @var  \stdClass
	 */
	protected $items;

	/**
	 * Jticketing Config Parameter
	 */
	protected $params;

	/**
	 * @var  \JPagination
	 *
	 * @since  2.4.0
	 */
	protected $pagination;

	/**
	 * The Page Title String object
	 *
	 * @var
	 */
	protected $PageTitle;

	/**
	 * Function Adding toolbar action on coupons list view
	 */
	protected $addTJtoolbar;

	/**
	 * @var  \JForm
	 *
	 * @since  2.4.0
	 */
	public $filterForm;

	/**
	 * tjvendorTable table object
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
	 * @var  array
	 *
	 * @since  2.4.0
	 */
	public $activeFilters;

	/**
	 * Used for adding ToolBar action as per ACL
	 *
	 * @since  2.4.0
	 */
	public $toolbarHTML;

	/**
	 * Checking ACL of logged in user
	 *
	 * @since  2.4.0
	 */
	public $canChange;

	public $utilities;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 *
	 * @since  2.4.0
	 */
	public function display($tpl = null)
	{
		$app          = Factory::getApplication();
		$this->user   = Factory::getUser();

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

		$authorised = $this->user->authorise('coupon.view', 'com_jticketing');

		if ($authorised !== true)
		{
			$app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'error');

			return false;
		}

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors));
		}

		$this->params        = $app->getParams('com_jticketing');
		$this->utilities     = JT::utilities();
		$this->state         = $this->get('State');
		$this->items         = $this->get('Items');
		$this->pagination    = $this->get('Pagination');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');
		$this->canChange     = $this->user->authorise('coupon.edit.state', 'com_jticketing');
		Table::addIncludePath(JPATH_ROOT . '/administrator/components/com_tjvendors/tables');
		$this->tjvendorTable = Table::getInstance('vendor', 'TJVendorsTable', array());

		$this->couponsMenuItemId   = JT::utilities()->getItemId('index.php?option=com_jticketing&view=coupons');

		$this->addTJtoolbar();
		$this->_prepareDocument();
		parent::display($tpl);
	}

	/**
	 * Add the ACL based tjtoolbar.
	 *
	 * @return void
	 *
	 * @since  2.4.0
	 */
	protected function addTJtoolbar()
	{
		JLoader::register('JticketingHelper', JPATH_ADMINISTRATOR . '/components/com_jticketing/helpers/jticketing.php');
		$canDo = ContentHelper::getActions('com_jticketing');

		// Add toolbar buttons
		if (file_exists(JPATH_LIBRARIES . '/techjoomla/tjtoolbar/toolbar.php')) { require_once JPATH_LIBRARIES . '/techjoomla/tjtoolbar/toolbar.php'; }
		$tjbar = TJToolbar::getInstance('tjtoolbar', 'pull-right float-end');

		// Create New coupon
		if ($canDo->{'coupon.create'})
		{
			$tjbar->appendButton('couponform.add', 'TJTOOLBAR_NEW', '', 'class="btn btn-sm btn-success"');
		}

		// Edit coupon
		if ($canDo->{'coupon.edit.own'} && isset($this->items[0]))
		{
			$tjbar->appendButton('couponform.edit', 'TJTOOLBAR_EDIT', '', 'class="btn btn-sm btn-success"');
		}

		// Edit coupon state and delete coupon
		if ($canDo->{'coupon.edit.state'} && isset($this->items[0]))
		{
			$tjbar->appendButton('coupons.publish', 'TJTOOLBAR_PUBLISH', '', 'class="btn btn-sm btn-success"');
			$tjbar->appendButton('coupons.unpublish', 'TJTOOLBAR_UNPUBLISH', '', 'class="btn btn-sm btn-warning"');
			$tjbar->appendButton('coupons.delete', 'TJTOOLBAR_DELETE', '', 'class="btn btn-sm btn-danger"');
		}

		$this->toolbarHTML = $tjbar->render();
	}

	/**
	 * Method to display coupons
	 *
	 * @return  void
	 *
	 * @since   @since  2.4.0
	 */
	protected function _prepareDocument()
	{
		$app   = Factory::getApplication();
		$menus = $app->getMenu();

		// Because the application sets a default page title, we need to get it from the menu item itself
		$menu = $menus->getActive();

		if ($menu)
		{
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		}
		else
		{
			$this->params->def('page_heading', Text::_('COM_JTICKETING_COUPONS_PAGE_HEADING'));
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
}
