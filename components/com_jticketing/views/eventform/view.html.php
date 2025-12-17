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
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Date\Date;
JLoader::register('JSocialHelper', JPATH_LIBRARIES . '/techjoomla/jsocial/helper.php');

if (file_exists(JPATH_SITE . '/components/com_tjvendors/helpers/fronthelper.php')) { require_once JPATH_SITE . '/components/com_tjvendors/helpers/fronthelper.php'; }
if (file_exists(JPATH_ADMINISTRATOR . '/components/com_tjvendors/tables/vendorclientxref.php')) { require_once JPATH_ADMINISTRATOR . '/components/com_tjvendors/tables/vendorclientxref.php'; }
JLoader::register('FieldsHelper', JPATH_ADMINISTRATOR . '/components/com_fields/helpers/fields.php');
require_once JPATH_SITE . '/components/com_tjvendors/includes/tjvendors.php';

/**
 * Event creation form
 *
 * @package     JTicketing
 * @subpackage  component
 * @since       1.0
 */
class JticketingViewEventform extends HtmlView
{
	protected $state;

	protected $item;

	protected $form;

	protected $form_extra;

	protected $JoomlaFields;

	protected $fieldsIntegration;

	protected $isAdmin;

	protected $silentVendor;

	protected $handle_transactions;

	protected $adaptivePayment;

	protected $params;

	protected $checkVendorApproval;

	protected $allowedToCreate;

	protected $enableCertification;

	protected $formExtraFields;

	/**
	 * jticketing event object
	 *
	 * @var  JTicketingEventJticketing
	 */
	public $event = null;

	/**
	 * Check whether payment gateway is present
	 *
	 * @var  Boolean
	 */
	protected $checkGatewayDetails;

	/**
	 * Display view
	 *
	 * @param   STRING  $tpl  template name
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function display($tpl = null)
	{
		$app   = Factory::getApplication();
		$user  = Factory::getUser();
		$input = Factory::getApplication()->getInput();
		$this->form  = $this->get('Form');
		$currentTime = new DateTime('now', new DateTimeZone('UTC'));
		$startTime = $currentTime->format('H:i');
		$endTime = $currentTime->modify('+1 hour')->format('H:i');
		$this->form->setFieldAttribute('start_time', 'default', $startTime);
		$this->form->setFieldAttribute('end_time', 'default', $endTime);

		if (!$user->id)
		{
			$current = Uri::getInstance()->toString();
			$url     = base64_encode($current);
			$app->enqueueMessage(Text::_('COM_JTICKETING_MESSAGE_LOGIN_FIRST'));
			$app->redirect(Route::_('index.php?option=com_users&view=login&return=' . $url, false));
		}

		$jticketingmainhelper       = new jticketingmainhelper;
		$this->params               = JT::config();
		$this->state                = $this->get('State');
		$this->item                 = $this->get('Item');
		$this->event                = JT::event($this->item->id);
		$this->enableCertification  = $this->event->isCertificationEnabled();
		$this->show_tags            = $this->params->get('show_tags', '0', 'INT');
		$this->silentVendor         = $this->params->get('silent_vendor', 0);
		$this->enableOnlineVenues   = $this->params->get('enable_online_events');
		$this->accessLevel          = $this->params->get('show_access_level');
		JLoader::register('TJVendors', JPATH_SITE . "/components/com_tjvendors/includes/tjvendors.php");
		$vendor = TJVendors::vendor()->loadByUserId($user->id, JT::getIntegration());
		$this->vendorCheck          = $vendor->getId();
		$vendor                     = Tjvendors::vendor()->loadByUserId($user->id, 'com_jticketing');
		$this->checkGatewayDetails  = $vendor->getPaymentConfig() ? true : false;
		$this->isAdmin              = 0;
		$this->fieldsIntegration    = $this->params->get('fields_integrate_with');
		$this->handle_transactions  = $this->params->get('handle_transactions');
		$this->adaptivePayment      = $this->params->get('gateways');
		$this->form                 = $this->get('Form');
		$this->onlineEvents         = $this->params->get('enable_online_events', '0');
		$this->mediaSize            = $this->params->get('jticketing_media_size', '15');
		$this->adminApproval        = $this->params->get('event_approval');
		$this->tncForCreateEvent    = $this->params->get('tnc_for_create_event', '0');
		$this->eventArticle         = $this->params->get('create_eventform_privacy_terms_article', '0');
		$this->allowedToCreate      = 0;

		$vendorXrefTable = Table::getInstance('vendorclientxref', 'TjvendorsTable', array());

		$vendorXrefTable->load(
			array(
				'vendor_id' => $this->vendorCheck,
				'client' => 'com_jticketing'
			)
		);

		$this->checkVendorApproval = $vendorXrefTable->approved;
		$JSocialHelper			= new JSocialHelper;
		$tjvendorFrontHelper		= new TjvendorFrontHelper;

		$this->vendorProfileMenuId	= $JSocialHelper->getItemId('index.php?option=com_tjvendors&view=vendors&client=com_jticketing');
		$this->vendorCheck		= $tjvendorFrontHelper->checkVendor('', 'com_jticketing');
		$this->vendorProfileStatus	= TJVendors::vendor()->getVendorProfileStatus($user->id, 'com_jticketing');
		$this->profile_complete		= $this->params->get('profile_complete');

		if($this->profile_complete == 1 && $this->vendorProfileStatus < 100)
		{
			$app->enqueueMessage(Text::_('COM_JTICKETING_PLEASE_COMPLETE_PROFILE'), 'error');
			$app->redirect('index.php?option=com_tjvendors&view=vendor&layout=default&client=com_jticketing&vendor_id=' . $this->vendorCheck . '&ItemId=' . $this->vendorProfileMenuId);
		}

		if (($this->vendorCheck && $this->silentVendor == 0) || $this->silentVendor == 1)
		{
			$this->allowedToCreate = 1;
		}

		// Event detail view resized image setting
		$this->eventMainImage = $this->params->get('front_event_detail_view', 'media_s');

		// Event detail view resized image setting
		$this->eventGalleryImage = $this->params->get('front_event_gallery_view', 'media_s');

		if ($this->item->venue != 0)
		{
			BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_jticketing/models', 'venueform');
			$jticketingModelVenueform = BaseDatabaseModel::getInstance('Venueform', 'JTicketingModel');
			$this->venueDetails = $jticketingModelVenueform->getItem($this->item->venue);
			$this->venueName = $this->venueDetails->name;
			$this->venueId = $this->item->venue;
		}
		else
		{
			$this->venueId = 0;

			$editFormVenueData = $app->getUserState('com_jticketing.edit.eventform.data') ? $app->getUserState('com_jticketing.edit.eventform.data') : null;
			if ($editFormVenueData && isset($editFormVenueData['venue']) && $editFormVenueData['venue'])
			{
				$this->venueId = $editFormVenueData['venue'];
			}
		}

		$this->attendeeList = array();
		$attendeeCoreFields = JT::model('attendeecorefields', array('ignore_request' => true));
		$attendeeCoreFields->setState('filter.state', 1);
		$this->attendeeList = $attendeeCoreFields->getItems();

		if (!empty($this->item->id))
		{
			$app->getInput()->set("content_id", $this->item->id);

			$this->form_extra = array();

			BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_jticketing/models', 'eventform');
			$jTicketingModelEventForm = BaseDatabaseModel::getInstance('EventForm', 'JTicketingModel');

			// The function getFormExtra is defined in Tj-fields filterFields trait.
			$this->form_extra = $jTicketingModelEventForm->getFormExtra(
						array("category" => $this->item->catid,
							"clientComponent" => 'com_jticketing',
							"client" => 'com_jticketing.event',
							"view" => 'event',
							"layout" => 'edit')
							);

			if ($this->form_extra)
			{
				$this->formExtraFields = $this->form_extra->getFieldset();
			}
		}
		else
		{
			$endDate = new Date('now +1 hour');
			$this->form->setValue('enddate', NULL, $endDate);
		}


		$this->JoomlaFields = FieldsHelper::getFields('com_jticketing.event', $this->item, true);

		if (!empty($this->item->id))
		{
			$model = $this->getModel('eventform');
			$this->datas = $model->getvenuehtml($this->item);
		}

		// Get integration set.
		$this->integration = $this->params->get('integration', '', 'INT');
		$this->collect_attendee_info_checkout = $this->params->get('collect_attendee_info_checkout');
		$this->googleMapApiKey = $this->params->get('google_map_api_key');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			$app->enqueueMessage(implode('<br />', $errors), 'error');
			
			return false;
		}

		if (empty($this->item->id))
		{
			$authorised = $user->authorise('core.create', 'com_jticketing');
		}
		else
		{
			$authorised_own = $user->authorise('core.edit.own', 'com_jticketing');

			if ($authorised_own)
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

		if ($this->event->getId())
		{
			// Added by aniket for ticket types
			$this->ticket_types = $this->event->getTicketTypes();
		}

		// Escape strings for HTML output.
		$this->pageclass_sfx = htmlspecialchars($this->params->get('pageclass_sfx') ? $this->params->get('pageclass_sfx') : '');
		$this->_prepareDocument();

		parent::display($tpl);
	}

	/**
	 * Prepare document
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	protected function _prepareDocument()
	{
		$app	= Factory::getApplication();
		$menus	= $app->getMenu();
		$title	= null;

		// Because the application sets a default page title,
		// we need to get it from the menu item itself
		$menu = $menus->getActive();

		if ($menu)
		{
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		}
		else
		{
			$this->params->def('page_heading', Text::_('COM_JTICKETING_FORM_EVENT_HEADING_CREATE'));
		}

		// Added by Manoj - start.
		if (!empty($this->item->id))
		{
			$this->params->def('page_heading', Text::_('COM_JTICKETING_FORM_EVENT_HEADING_EDIT') . '-' . $this->item->title);
		}
		// Added by Manoj - end.

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
	 * Returns the form object
	 *
	 * @return  mixed  A \JForm object on success, false on failure
	 *
	 * @since   2.6.1
	 */
	public function getForm()
	{
		if (!is_object($this->form))
		{
			$this->form = $this->get('Form');
		}

		return $this->form;
	}
}
