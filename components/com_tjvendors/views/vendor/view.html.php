<?php
/**
 * @package     TJVendors
 * @subpackage  com_tjvendors
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Table\Table;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;

/**
 * View to edit
 *
 * @since  1.6
 */
class TjvendorsViewVendor extends HtmlView
{
	protected $state;

	protected $item;

	protected $form;

	protected $vendor_id;

	protected $VendorDetail;

	protected $vendorClientXrefTable;

	protected $layout;

	protected $vendor;

	protected $input;

	protected $client;

	protected $params;

	protected $isClientExist;

	protected $default;

	protected $options;

	protected $countries;

	protected $vendorLogoProfileImg;
	protected $vendorLogoProfileImgPath;
	
	protected $vendorFormItemId;

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
		$this->params = ComponentHelper::getParams('com_tjvendors');
		$this->state  = $this->get('State');
		$this->vendor = $this->get('Item');
		$this->form   = $this->get('Form');
		$app          = Factory::getApplication();
		$this->input  = $app->getInput();
		$this->client = $this->input->get('client', '', 'STRING');

		BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_tjvendors/models', 'vendor');
		$tjvendorsModelVendor        = BaseDatabaseModel::getInstance('Vendor', 'TjvendorsModel');
		$tjvendorFrontHelper         = new TjvendorFrontHelper;
		$this->vendor_id             = $tjvendorFrontHelper->getvendor();
		$this->client                = $this->input->get('client', '', 'STRING');
		$this->isClientExist         = $tjvendorFrontHelper->isClientExist($this->client, $this->vendor_id);
		$this->vendorClientXrefTable = Table::getInstance('vendorclientxref', 'TjvendorsTable', array());
		$this->vendorClientXrefTable->load(array('vendor_id' => $this->vendor_id, 'client' => $this->client));
		$this->VendorDetail          = $tjvendorsModelVendor->getItem($this->vendor_id);

		$utilitiesObj = TJVendors::utilities();
		$this->countries = $utilitiesObj->getCountries();

		$this->default = null;

		if (isset($this->vendor->country))
		{
			$this->default = $this->vendor->country;
		}

		$this->vendorFormItemId = 0;
		if ($this->client)
		{
			$app  = Factory::getApplication();
			$menu = $app->getMenu();
			$items = $menu->getItems('link', 'index.php?option=com_tjvendors&view=vendor&client=' . $this->client);
	
			if (isset($items[0]))
			{
				$this->vendorFormItemId = $items[0]->id;
			}
		}

		$this->options = array();
		$this->options[] = HTMLHelper::_('select.option', 0, Text::_('COM_TJVENDORS_FORM_LIST_SELECT_OPTION'));

		foreach ($this->countries as $key => $value)
		{
			$country = $this->countries[$key];
			$id      = $country['id'];
			$value   = $country['country'];
			$this->options[] = HTMLHelper::_('select.option', $id, $value);
		}

		$this->vendorLogoProfileImg = "media/com_tjvendor/images/default.png";
		$this->vendorLogoProfileImgPath = Uri::root() . $this->vendorLogoProfileImg;

		$app->setUserState("vendor.client", $this->client);
		$app->setUserState("vendor.vendor_id", $this->vendor->vendor_id);
		$this->layout = $this->input->get('layout', '', 'STRING');
		Text::script('COM_TJVENDOR_PAYMENTGATEWAY_NO_FIELD_MESSAGE');
		Text::script('COM_TJVENDOR_DESCRIPTION_READ_MORE');
		Text::script('COM_TJVENDOR_DESCRIPTION_READ_LESS');

		if (isset($this->vendor->vendor_id) && $this->vendor_id != $this->vendor->vendor_id)
		{
			$app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'error');

			return false;
		}

		if (!empty($this->vendor_id) && $this->layout == "edit")
		{
			if (!empty($this->clientsForVendor))
			{
				foreach ($this->clientsForVendor as $client)
				{
					if ($client == $this->client)
					{
						$link = Route::_('index.php?option=com_tjvendors&view=vendor&layout=editinfo&vendor_id=' . $this->vendor_id . '&client=' . $this->client);
						$app->enqueueMessage(Text::_('COM_TJVENDOR_REGISTRATION_REDIRECT_MESSAGE'));
						$app->redirect($link);
					}
				}
			}

			$vendorId = $this->input->get('vendor_id', 0, 'INT');

			if (empty($vendorId))
			{
				$link = Route::_('index.php?option=com_tjvendors&view=vendor&client=' . $this->client);

				if (!$this->isClientExist)
				{
					$client = $tjvendorFrontHelper->getClientName($this->client);
					$vendorMsg = Text::_('COM_TJVENDORS_DISPLAY_YOU_ARE_ALREADY_A_VENDOR_AS') . ' ' .
					Text::_('COM_TJVENDORS_DISPLAY_DO_YOU_WANT_TO_ADD') . $client . Text::_('COM_TJVENDORS_DISPLAY_AS_A_CLIENT');
					$app->enqueueMessage($vendorMsg);
				}
				else
				{
					$app->enqueueMessage(Text::_('COM_TJVENDOR_REGISTRATION_REDIRECT_MESSAGE'));
				}

				$app->redirect($link);
			}
		}

		if ($this->layout != "edit")
		{
			if (Factory::getUser()->id && !$this->vendor_id)
			{
				$client = $app->getInput()->get('client', '', 'STRING');
				 // Check if the user is already on the editinfo page to prevent a redirect loop
				 if ($this->layout != 'editinfo') {
					$link = Route::_('index.php?option=com_tjvendors&view=vendor&layout=editinfo&client=' . $client);
					$app->redirect($link);
				}
			}
			elseif (!Factory::getUser()->id)
			{
				$link = Route::_('index.php?option=com_users&view=login');
				$app->redirect($link);
			}
		}

		// Check for errors
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors));
		}

		parent::display($tpl);
	}
}
