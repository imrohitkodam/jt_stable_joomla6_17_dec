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
use Joomla\CMS\User\User;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Component\ComponentHelper;

/**
 * View class for a list of Jticketing.
 *
 * @since  1.6
 */
class JticketingViewCoupons extends BaseHtmlView
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

		// Native Event Manager.
		if ($this->params->get('integration') < 1)
		{
			Factory::getApplication()->enqueueMessage(Text::_('COMJTICKETING_INTEGRATION_NOTICE'), 'Warning');

			return false;
		}

		$this->user          = Factory::getUser();
		$this->state         = $this->get('State');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');
		$this->items         = $this->get('Items');
		$this->pagination    = $this->get('Pagination');

		$this->canEdit    = $this->user->authorise('coupon.edit', 'com_jticketing');
		$this->canChange  = $this->user->authorise('coupon.edit.state', 'com_jticketing');
		$this->canCheckin = $this->user->authorise('core.manage', 'com_jticketing');

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

		JticketingHelper::addSubmenu('coupons');
		$this->addToolbar();
		$this->sidebar = ""; // Joomla 6: HTMLHelperSidebar::render() removed

		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since  1.6
	 */
	protected function addToolbar()
	{
		JLoader::register('JticketingHelper', JPATH_ADMINISTRATOR . '/components/com_jticketing'. '/components/com_jticketing/helpers/jticketing.php');
		$canDo = ContentHelper::getActions('com_jticketing');

		ToolbarHelper::title(Text::_('COM_JTICKETING_COMPONENT') . Text::_('COM_JTICKETING_TITLE_COUPONS'), 'list');

		if ($canDo->{'coupon.create'})
		{
			ToolbarHelper::addNew('coupon.add', 'JTOOLBAR_NEW');
		}

		if ($canDo->{'coupon.edit'} && isset($this->items[0]))
		{
			ToolbarHelper::editList('coupon.edit', 'JTOOLBAR_EDIT');
		}

		if ($canDo->{'coupon.edit.state'})
		{
			ToolbarHelper::divider();
			ToolbarHelper::publish('coupons.publish', 'JTOOLBAR_PUBLISH', true);
			ToolbarHelper::unpublish('coupons.unpublish', 'JTOOLBAR_UNPUBLISH', true);

			if (isset($this->items[0]->checked_out))
			{
				ToolbarHelper::checkin('coupons.checkin');
			}
		}

		if (isset($this->items[0]))
		{
			if ($canDo->{'coupon.delete'})
			{
				ToolbarHelper::deleteList(Text::_('COM_JTICKETING_ARE_YOU_SURE_YOU_TO_DELETE_THE_COUPON'), 'coupons.delete', 'JTOOLBAR_DELETE');
			}
		}

		if ($canDo->{'core.admin'})
		{
			ToolbarHelper::preferences('com_jticketing');
		}

		// Joomla 6: HTMLHelperSidebar::setAction() removed - sidebar functionality removed
	}
}
