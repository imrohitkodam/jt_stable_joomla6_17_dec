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
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;

if (file_exists(JPATH_SITE . '/components/com_tjvendors/helpers/fronthelper.php')) { require_once JPATH_SITE . '/components/com_tjvendors/helpers/fronthelper.php'; }
if (file_exists(JPATH_ADMINISTRATOR . '/components/com_tjvendors/tables/vendorclientxref.php')) { require_once JPATH_ADMINISTRATOR . '/components/com_tjvendors/tables/vendorclientxref.php'; }

/**
 * View to edit
 *
 * @since  1.6
 */
class JticketingViewVenueform extends HtmlView
{
	protected $state;

	protected $item;

	protected $form;

	protected $params;

	protected $canSave;

	protected $checkVendorApproval;

	protected $allowedToCreate;

	/**
	 * Default link for venues view
	 *
	 * @var  string
	 */
	public $veneuesLink;

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
		$app  = Factory::getApplication();
		$user = Factory::getUser();
		$menus = $app->getMenu();

		// Validate user login.
		if (!$user->id)
		{
			$msg = Text::_('COM_JTICKETING_MESSAGE_LOGIN_FIRST');

			// Get current url.
			$current = Uri::getInstance()->toString();
			$url     = base64_encode($current);
			$app->enqueueMessage($msg);
			$app->redirect(Route::_('index.php?option=com_users&view=login&return=' . $url, false));
		}

		$this->com_params      = $this->params = ComponentHelper::getParams('com_jticketing');
		$this->state           = $this->get('State');
		$this->item            = $this->get('Item');
		$this->canSave         = $this->get('CanSave');
		$this->form            = $this->get('Form');
		$this->allowedToCreate = 0;
		$this->utilities 	   = JT::utilities();

		$tjvendorFrontHelper	= new TjvendorFrontHelper;
		$vendorCheck        	= $tjvendorFrontHelper->checkVendor('', 'com_jticketing');
		$silentVendor       	= $this->params->get('silent_vendor', 0, 'INTEGER');
		$vendorXrefTable 		= Table::getInstance('vendorclientxref', 'TjvendorsTable', array());

		// Create link for venues menu.
		$venuesMenuID = $this->utilities->getItemId('index.php?option=com_jticketing&view=venues');
		$this->veneuesLink 		= Route::_("index.php?option=com_jticketing&view=venues&Itemid=" . $venuesMenuID);

		$vendorXrefTable->load(
			array(
				'vendor_id' => $vendorCheck,
				'client' => 'com_jticketing'
			)
		);

		$this->checkVendorApproval = $vendorXrefTable->approved;

		if (($vendorCheck && $silentVendor == 0) || $silentVendor == 1)
		{
			$this->allowedToCreate = 1;
		}

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors));
		}

		if (empty($this->item->id))
		{
			$authorised = $user->authorise('core.create', 'com_jticketing');
		}
		else
		{
			$authorisedOwn = $user->authorise('core.edit.own', 'com_jticketing');

			if ($authorisedOwn)
			{
				$authorised = true;

				// Check if logged in user is event created_by.
				if ($this->item->created_by != $user->id)
				{
					$authorised = false;
				}
			}
		}

		if ($authorised !== true)
		{
			$app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'error');

			return false;
		}

		$this->googleMapApiKey    = $this->params->get('google_map_api_key');
		$this->integration        = $this->params->get('integration');
		$this->editId             = $this->item->id;
		$this->existingParams     = $this->item->params;
		$this->mediaGalleryObj    = 0;
		$this->isAdmin            = 0;

		$this->showVenueGallery = $this->params->get('venue_gallery', 0);

		if (isset($this->item->gallery))
		{
			$this->mediaGalleryObj = json_encode($this->item->gallery);
		}

		// Event detail view resized image setting
		$this->venueGalleryImage = $this->params->get('admin_venue_gallery_view', 'media_s');
		$this->mediaSize = $this->params->get('jticketing_media_size', '15');

		// Data: {element:element,venue_id:jQuery("[name='jform[id]']").val()},
		if (!empty($this->googleMapApiKey))
		{
			$this->googleMapLink = 'https://maps.googleapis.com/maps/api/js?libraries=places&key=' . $this->googleMapApiKey;
		} 
		else 
		{
			$this->googleMapLink = 'https://maps.googleapis.com/maps';
		}

		$this->EnableOnlineEvents = $this->params->get('enable_online_events');

		$this->addToolbar();

		parent::display($tpl);
	}

	/**
	 * Prepares the document
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	/*protected function _prepareDocument()
	{
		$app   = Factory::getApplication();
		$menus = $app->getMenu();
		$title = null;

		Because the application sets a default page title,
		we need to get it from the menu item itself
		$menu = $menus->getActive();

		if ($menu)
		{
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		}
		else
		{
			$this->params->def('page_heading', Text::_('COM_JTICKETING_FORM_EVENT_HEADING_CREATE'));
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
	}*/
	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	protected function addToolbar()
	{
		$this->toolbar = Toolbar::getInstance('toolbar');

		Factory::getApplication()->getInput()->set('hidemainmenu', true);
		$user    = Factory::getUser();
		$isNew   = ($this->item->id == 0);

		if ($isNew)
		{
			$viewTitle = Text::_('COM_JTICKETING_TITLE_VENUES');
		}
		else
		{
			$viewTitle = Text::_('COM_JTICKETING_TITLE_VENUES');
		}

		if (isset($this->item->checked_out))
		{
			$checkedOut = !($this->item->checked_out == 0 || $this->item->checked_out == $user->get('id'));
		}
		else
		{
			$checkedOut = false;
		}

		/*require_once JPATH_ADMINISTRATOR . '/components/com_jticketing/helpers/jticketing.php';
		$canDo = JTicketingHelper::getActions();
		ToolbarHelper::title($viewTitle, 'pencil-2');

		If not checked out, can save the item.
		ToolbarHelper::apply('venueform.apply', 'COM_JTICKETING_VENUE_SAVE');
		ToolbarHelper::save('venueform.save', 'COM_JTICKETING_VENUE_SAVE_AND_CLOSE');

		if (!$checkedOut && ($canDo->{'core.create'}))
		{
			ToolbarHelper::custom('venueform.save2new', 'save-new.png', 'save-new_f2.png', 'COM_JTICKETING_VENUE_SAVE_AND_NEW', false);
		}

		if (empty($this->item->id))
		{
			ToolbarHelper::cancel('venueform.cancel', 'COM_JTICKETING_VENUE_CANCEL');
		}
		else
		{
			ToolbarHelper::cancel('venueform.cancel', 'COM_JTICKETING_VENUE_CLOSE');
		}*/
	}
}
