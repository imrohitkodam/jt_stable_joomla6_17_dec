<?php
/**
 * @package     JTicketing
 * @subpackage  com_jticketing
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Date\Date;

require_once JPATH_SITE . "/components/com_tjfields/filterFields.php";

/**
 * jticketing View
 *
 * @since  0.0.1
 */
class JTicketingViewEvent extends HtmlView
{
	use TjfieldsFilterField;

	/**
	 * View form
	 *
	 * @var         form
	 */
	public $form = null;

	protected $fieldsIntegration;

	protected $mediaGalleryObj;

	protected $form_extra;

	protected $formExtraFields;

	/**
	 * jticketing event object
	 *
	 * @var  JTicketingEventJticketing
	 */
	public $event = null;

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

	/**
	 * Display Function
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		// Get the Data
		$this->form  = $this->get('Form');
		$currentTime = new DateTime('now', new DateTimeZone('UTC'));
		$startTime = $currentTime->format('H:i');
		$endTime = $currentTime->modify('+1 hour')->format('H:i');
		$this->form->setFieldAttribute('start_time', 'default', $startTime);
		$this->form->setFieldAttribute('end_time', 'default', $endTime);

		$item = $this->get('Item');
		JticketingHelper::getLanguageConstant();
		$this->params              = JT::config();
		$this->enableCertification = JT::event()->isCertificationEnabled();
		$this->show_tags           = $this->params->get('show_tags', '0', 'INT');
		$this->silentVendor        = $this->params->get('silent_vendor');
		$this->accessLevel         = $this->params->get('show_access_level');
		BaseDatabaseModel::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jticketing/models', 'venue');
		$JTicketingModelVenue      = BaseDatabaseModel::getInstance('Venue', 'JTicketingModel');
		$this->venueDetails        = $JTicketingModelVenue->getItem($item->venue);
		$this->mediaSize           = $this->params->get('jticketing_media_size', '15');
		$this->enableOnlineVenues  = $this->params->get('enable_online_events');
		$this->venueName           = empty($this->venueDetails->name) ? '':
		$this->venueDetails->name;
		$this->venueId             = $item->venue;
		$attendeeCoreFields        = JT::model('attendeecorefields', array('ignore_request' => true));
		$attendeeCoreFields->setState('filter.state', 1);
		$this->attendeeList        = $attendeeCoreFields->getItems();
		$adaptivePayment           = $this->params->get('gateways');
		$this->tncForCreateEvent   = $this->params->get('tnc_for_create_event', '0');
		$this->eventArticle        = $this->params->get('create_eventform_privacy_terms_article', '0');
		$this->event               = JT::event($item->id);

		if (!is_array($adaptivePayment)) {
			$adaptivePayment = explode(',', $adaptivePayment);
		}

		$this->arra_check          = in_array('adaptive_paypal', $adaptivePayment);
		$this->fieldsIntegration   = $this->params->get('fields_integrate_with', 'com_tjfields', 'STRING');
		$this->mediaGalleryObj     = 0;
		$this->mainframe           = Factory::getApplication();
		$this->isAdmin             = 0;

		if (!empty($item))
		{
			$input  = Factory::getApplication()->getInput();
			$input->set("content_id", $item->id);
			$this->form_extra = array();
			$this->formExtraFields = array();

			BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_jticketing/models', 'eventform');
			$jTicketingModelEventForm = BaseDatabaseModel::getInstance('EventForm', 'JTicketingModel');

			// The function getFormExtra is defined in Tj-fields filterFields trait.
			$this->form_extra = $jTicketingModelEventForm->getFormExtra(
				array(
					"category" => $item->catid,
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

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			$app = Factory::getApplication();
			$app->enqueueMessage(implode('<br />', $errors), 'error');

			return false;
		}

		// Assign the Data
		$this->com_params = JT::config();
		$this->item = $item;

		if (empty($this->item->id))
		{
			$endDate = new Date('now +1 hour');
			$this->form->setValue('enddate', NULL, $endDate);
		}

		$this->googleMapApiKey = $this->params->get('google_map_api_key');
		$this->collect_attendee_info_checkout = $this->params->get('collect_attendee_info_checkout');

		// Set the toolbar
		$this->addToolBar();

		// Display the template
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function addToolBar()
	{
		$input = Factory::getApplication()->getInput();

		// Hide Joomla Administrator Main menu
		$input->set('hidemainmenu', true);

		$isNew = ($this->item->id == 0);

		if ($isNew)
		{
			$title = Text::_('COM_JTICKETING_MANAGER_JTICKETING_NEW');
		}
		else
		{
			$title = Text::_('COM_JTICKETING_MANAGER_JTICKETING_EDIT');
		}

		ToolbarHelper::title($title, 'event');
		ToolbarHelper::apply('event.apply');
		ToolbarHelper::save('event.save');
		ToolbarHelper::save2new('event.save2new');
		ToolbarHelper::cancel(
			'event.cancel',
			$isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE'
		);
	}
}
