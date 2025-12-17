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

use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Toolbar\ToolbarHelper;

if (file_exists(JPATH_SITE . '/components/com_jticketing/helpers/main.php')) { require_once JPATH_SITE . '/components/com_jticketing/helpers/main.php'; }

// Import Csv export button
if (file_exists(JPATH_LIBRARIES . '/techjoomla/tjtoolbar/button/csvexport.php')) { require_once JPATH_LIBRARIES . '/techjoomla/tjtoolbar/button/csvexport.php'; }

/**
 * View class for a list of Jticketing.
 *
 * @since  2.1
 */
class JticketingViewAttendees extends HtmlView
{
	protected $items;

	protected $pagination;

	protected $state;

	protected $canDo;

	public $filterForm;

	public $activeFilters;

	public $sidebar;

	public $extra_sidebar;

	public $modal_params;

	public $body;

	public $collect_attendee_info_checkout;

	public $jticketingTimehelper;

	public $tmpl;

	public $component;

	public $filterHide = true;

	public $sampleAttendeeImportFilepath;

	public $timezoneFilepath;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a Error object.
	 */
	public function display($tpl = null)
	{
		$app         = Factory::getApplication();
		$jinput      = $app->input;
		$this->canDo = ContentHelper::getActions('com_jticketing');
		$comParams   = ComponentHelper::getParams('com_jticketing');
		$layout      = Factory::getApplication()->getInput()->get('layout', 'default');
		$this->user  = Factory::getUser();
		$isAdmin     = $this->user->authorise('core.admin');
		$this->tmpl  = $jinput->get('tmpl', '');
		$utilities   = JT::utilities();
		$this->sampleAttendeeImportFilepath = Uri::root() . 'media/com_jticketing/samplecsv/AttendeeImport.csv';
		$this->timezoneFilepath = Uri::root() . 'media/com_jticketing/samplecsv/timeZone.csv';

		$jticketingmainhelper = new jticketingmainhelper;
		$this->collect_attendee_info_checkout = $comParams->get('collect_attendee_info_checkout', '', 'INT');

		$this->isEnrollmentEnabled  = $comParams->get('enable_self_enrollment', 0, 'INT');
		$this->isEnrollmentApproval = $comParams->get('enable_enrollment_approval', 0, 'INT');
		$this->enableAttendeeMove   = $comParams->get('enable_attendee_move', 0, 'INT');
		$this->dateFormat = $comParams->get('date_format_show');
		if ($this->dateFormat == "custom")
		{
			$this->dateFormat = $comParams->get('custom_format');
		}
		
		$this->nameCardField = $comParams->get('attendee_field', '');
		$this->attendeeListingFields = $comParams->get('attendee_listing_fields', ['COM_JTICKETING_ENROLMENT_USER_USERNAME','COM_JTICKEITNG_ATTENDEE_EMAIL','COM_JTICKETING_ENROLMENT_ACTION','COM_JTICKETING_CHECKIN'], 'ARRAY');

		if (!$comParams->get('entry_number_assignment', 0,'INT'))
		{
			$this->attendeeListingFields = array_diff($this->attendeeListingFields, ['COM_JTICKEITNG_ATTENDEE_ENTRY_NUMBER']);
		}
		
		// Autorize that logged in use is accessing the view.
		if (empty($this->user->id))
		{
			// If so, the user must login to view the order details.
			$msg     = Text::_('JERROR_ALERTNOAUTHOR');
			$current = Uri::getInstance()->toString();
			$url     = base64_encode($current);

			$app->enqueueMessage($msg, 'error');
			$app->redirect(Route::_('index.php?option=com_users&view=login&return=' . $url, false));
		}

		// Check if user is logged in and have super admin access.
		if (!$this->user->authorise('core.create', 'com_jticketing'))
		{
			$app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'error');

			return false;
		}

		$this->state         = $this->get('State');
		$input               = Factory::getApplication()->getInput();

		if ($layout == 'attendee_details')
		{
			$attendee_id            = $input->get('attendee_id', '', 'INT');

			if (!empty($attendee_id))
			{
				require_once JPATH_SITE . '/components/com_jticketing/models/attendeeform.php';
				$attendeeFormModel = new JticketingModelAttendeeForm;
				$attendeeData      = $attendeeFormModel->getItem($attendee_id);

				$event         = JT::event()->loadByIntegration($attendeeData->event_id);
				$this->eventId = $event->getId();
				$eventCreator  = $event->getCreator();

				if ($this->user->id == $attendeeData->owner_id || $this->user->id == $eventCreator || $isAdmin)
				{
					$this->extraFieldslabel = JT::model('attendeefields')->extraFieldslabel($attendeeData->event_id, $attendee_id, $event->catid);

					// Get customer note
					$this->customerNote = $this->get('CustomerNote');
				}
			}
		}
		elseif ($layout == 'contactus')
		{
			$session                    = Factory::getSession();
			$this->selectedOrderItemIds = $session->get('selected_order_item_ids');
			require_once JPATH_SITE . '/components/com_jticketing/models/attendees.php';

			// Get unique and valide email ids
			$attendeesModel             = new JticketingModelAttendees;
			$this->selectedEmails       = $attendeesModel->getAttendeeEmail($this->selectedOrderItemIds);
			$this->addToolbar();

			// Accces config parameter attendee_email_limit
			$this->attendeeEmailLimit   = $comParams->get("attendee_email_limit");

			// If selectedEmails greater than config attendeeEmailLimit then display error
			if($this->selectedOrderItemIds && count($this->selectedEmails) > $this->attendeeEmailLimit)
			{
				$app->enqueueMessage(Text::sprintf('COM_JTICKETING_EMAIL_BULK_LIMIT_EXCEED', $this->attendeeEmailLimit), 'notice');
				$app->redirect(Route::_('index.php?option=com_jticketing&view=attendees', false));
			}

			$this->isAdmin              = $app->isClient("administrator");
		}
		elseif ($layout == 'myticket')
		{
			$this->attendeeId = $input->get('attendee_id', '0', 'INT');

			// JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_jticketing/models', 'attendees');
			// $orderItemModel = JModelLegacy::getInstance('Attendees', 'JticketingModel');

			if (!empty($this->attendeeId))
			{
				require_once JPATH_SITE . '/components/com_jticketing/models/attendeeform.php';
				$orderItemModel = new JticketingModelAttendeeForm;
				$orderData      = $orderItemModel->getItem($this->attendeeId);

				$event         = JT::event()->loadByIntegration($orderData->event_id);
				$this->eventId = $event->getId();
				$this->data    = $jticketingmainhelper->getticketDetails($this->eventId, $this->attendeeId);
				$eventCreator  = $event->getCreator();

				if (!empty($this->data) && ($this->user->id == $orderData->owner_id || $this->user->id == $eventCreator || $isAdmin))
				{
					if (isset($this->data->totalamount))
					{
						$this->data->ticketprice = $this->data->totalamount;
					}

					if (isset($this->data->ticketscount))
					{
						$this->data->nofotickets = $this->data->ticketscount;
					}

					if (isset($this->data->amount))
					{
						$this->data->totalprice = $this->data->amount;
					}

					$this->data->evid = $this->eventId;
					$this->data->orderEventId = $orderData->event_id;
					$this->html = $jticketingmainhelper->getticketHTML($this->data, $jticketing_usesess = 0);
				}
				else
				{
					echo '<b>' . Text::_('NO_TICKET') . '</b>';
				}
			}
		}
		else
		{
			$this->items	     = $this->get('Items');
			$this->pagination	 = $this->get('Pagination');
			$this->filterForm    = $this->get('FilterForm');
			$this->activeFilters = $this->get('ActiveFilters');

			// Modal pop up for attendee details
			$this->attendee_params           = array();
			$this->attendee_params['height'] = "400px";
			$this->attendee_params['width']  = "150px";

			// Modal pop up for ticket print params
			$this->print_params           = array();
			$this->print_params['height'] = "400px";
			$this->print_params['width']  = "300px";

			// Modal pop up for mass enrollment params
			$this->modal_params           = array();
			$this->modal_params['height'] = "500px";
			$this->modal_params['width']  = "500px";
			$this->modal_params['title']  = Text::_('COM_JTICKETING_TITLE_ENROLLMENTS_NEW');
			$this->modal_params['url']    = Route::_(URI::base(true) . '/index.php?option=com_jticketing&view=enrollment&tmpl=component');
			$this->body                   = "";

			// Modal pop up for csv attendee import params
			$this->csv_params           = array();
			$this->csv_params['height'] = "500px";
			$this->csv_params['width']  = "200px";
			$this->csv_params['title']  = Text::_('COMJTICKETING_EVENT_IMPORT_CSV');
			$this->csv_params['url']    = Route::_(URI::base(true) . '/index.php?option=com_jticketing&view=attendees&tmpl=component&layout=csv_import');

			// Modal pop up for mass enrollment params
			$this->attendeePrams           = array();
			$this->attendeePrams['height'] = "300px";
			$this->attendeePrams['width']  = "200px";

			$eventsModel = JT::model('events');
			$eventData   = array('eventStatus' => 'ongoing');

			// Show only my events in select list if enroll all permission is not allowed.
			if (!($this->user->authorise('core.enrollall', 'com_jticketing')))
			{
				$eventData['creatorId'] = $this->user->id;
			}

			// Get the options for event select list .
			$onGoingEvents      = $eventsModel->getEvents($eventData);
			$this->eventOptions = array();

			foreach ($onGoingEvents as $event)
			{
				if (JT::event($event->id)->getTicketTypes())
				{
					$startDate    = $utilities->getFormatedDate($event->startdate);
					$event->title = $event->title . '(' . $startDate . ')';

					$this->eventOptions[] = HTMLHelper::_('select.option', $event->id, $this->escape($event->title));
				}
			}

			// Pass the required data and load the layout file.
			$displayData                  = array('eventOptions' => $this->eventOptions);
			$this->attendeeBody           = '';
			$this->attendeeBody           = LayoutHelper::render(
												'attendeemove', $displayData, JPATH_ADMINISTRATOR . '/components/com_jticketing/views/enrollment/tmpl/'
											);
			$this->attendeePrams['title'] = Text::_('COM_JTICKETING_VIEW_ATTENDEES_MOVE_ATTENDEE_DESCRIPTION');

			// Get order status array with their full forms.
			$this->attendeesModel  = $this->getModel();
			$this->attendeeActions = $this->get('AttendeeActions');

			// Hiding extra filters and handling form submit in case of the pop layout.
			if ($this->tmpl === 'component' && !empty($this->activeFilters['events']))
			{
				$this->filterForm->setFieldAttribute('events', 'disabled', 'true', 'filter');
				$this->component = '&tmpl=component&filter[events]=' . $this->activeFilters['events'];
			}
		}

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors));
		}

		JticketingHelper::addSubmenu('attendees');

		// Do not rendar the unnessessary fields,rows,menus from a pop up view.
		if ($this->tmpl !== 'component')
		{
			$this->sidebar = ""; // Joomla 6: HTMLHelperSidebar::render() removed
		}

		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since	2.1
	 */
	protected function addToolbar()
	{
		$layout    = Factory::getApplication()->getInput()->get('layout', 'default');
		$comParams = JT::config();
		$toolbar   = Toolbar::getInstance('toolbar');
		$integration = $comParams->get('integration', '', 'INT');

		if ($layout == 'contactus')
		{
			Factory::getApplication()->getInput()->set('hidemainmenu', true);

			ToolbarHelper::title(Text::_('COM_JTICKETING_SEND_EMAIL'), 'jticketing email');

			ToolbarHelper::custom('attendees.emailtoSelected', 'envelope.png', 'send_f2.png', 'COM_JTICKETING_SEND_MAIL', false);
			ToolbarHelper::cancel('attendees.cancelEmail');
		}
		elseif ($this->tmpl !== 'component')
		{
			ToolbarHelper::divider();
			ToolbarHelper::custom('attendees.checkin', 'publish.png', '', Text::_('COM_JTICKETING_CHECKIN'));
			ToolbarHelper::custom('attendees.undochekin', 'unpublish.png', '', Text::_('COM_JTICKETING_CHECKIN_FAIL'));
			ToolbarHelper::custom('attendees.redirectforEmail', 'mail.png', '', Text::_('COM_JTICKETING_EMAIL_TO_ALL_SELECTED_PARTICIPANT'));

			if ($integration == 2)
			{
				// Joomla 6: JVERSION check removed
		if (false) // Legacy < '4.0.0')
				{
					$toolbar->appendButton('Custom', '&nbsp;<a class="btn" href="#import_attendees" data-toggle="modal" >
						<span class="icon-upload icon-white"></span>' . '&nbsp;' . htmlspecialchars(Text::_('COMJTICKETING_EVENT_IMPORT_CSV')) . '</a>'
					);
				}
				else
				{
					$toolbar->appendButton(
						'Custom', '&nbsp;&nbsp;<a
						class="btn btn-small btn-primary"
						onclick="document.getElementById(\'import_attendees\').open();"
						href="javascript:void(0);"><span class="icon-upload icon-white"></span> ' . Text::_('COMJTICKETING_EVENT_IMPORT_CSV') . '</a>'
					);
				}
			}

			ToolbarHelper::title(Text::_('COM_JTICKETING_TITLE_ATTENDEES'), 'list');
			ToolbarHelper::divider();

			$canDo  = $this->canDo;

			// Add New button manage enrollment view
			if (($canDo->{'core.enrollall'} || $canDo->{'core.enrollown'}) && $comParams->get('enable_self_enrollment'))
			{
				if ($this->tmpl !== 'component')
				{
					// Joomla 6: JVERSION check removed
		if (false) // Legacy < '4.0.0')
					{
						$toolbar->prependButton(
							'Custom', '<a class="btn btn-small btn-success" href="#myModalNew" data-toggle="modal" >
							<span class="icon-new icon-white"></span>' . htmlspecialchars(Text::_('COM_JTICKETING_TITLE_ENROLLMENTS_NEW')) . '</a>'
						);
					}
					else
					{
						$toolbar->prependButton(
							'Custom', '<a
							class="af-d-block af-relative btn btn-small  btn-primary"
							onclick="document.getElementById(\'myModalNew\').open();"
							href="javascript:void(0);"><span class="icon-new icon-white"></span> ' . Text::_('COM_JTICKETING_TITLE_ENROLLMENTS_NEW') . '</a>'
						);
					}
				}
			}
		}

		// Add common button here.
		$this->message               = array();
		$this->message['success']    = Text::_("COM_JTICKETING_EXPORT_FILE_SUCCESS");
		$this->message['error']      = Text::_("COM_JTICKETING_EXPORT_FILE_ERROR");
		$this->message['inprogress'] = Text::_("COM_JTICKETING_EXPORT_FILE_NOTICE");

		if (!empty($this->items))
		{
			$toolbar->appendButton('CsvExport',  $this->message);
		}

		// Retrun data according to the layout opened.
		if ($this->tmpl !== 'component')
		{
			ToolbarHelper::preferences('com_jticketing');
			// Joomla 6: HTMLHelperSidebar::setAction() removed
			$this->extra_sidebar = '';
		}
		else
		{
			return $toolbar->render();
		}
	}
}
