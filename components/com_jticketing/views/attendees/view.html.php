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

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Toolbar\ToolbarHelper;

if (file_exists(JPATH_SITE . '/components/com_jticketing/helpers/main.php')) { require_once JPATH_SITE . '/components/com_jticketing/helpers/main.php'; }

// Import Csv export button
if (file_exists(JPATH_LIBRARIES . '/techjoomla/tjtoolbar/button/csvexport.php')) { require_once JPATH_LIBRARIES . '/techjoomla/tjtoolbar/button/csvexport.php'; }

HTMLHelper::_('bootstrap.renderModal', 'a.modal');

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
		$this->user  = Factory::getUser();
		$isAdmin     = $this->user->authorise('core.admin');
		$this->canDo = ContentHelper::getActions('com_jticketing');
		$comParams   = ComponentHelper::getParams('com_jticketing');
		$layout      = Factory::getApplication()->getInput()->get('layout', 'default');
		$app         = Factory::getApplication();
		$utilities   = JT::utilities();

		// Autorize that logged in use is accessing the view.
		if (empty($this->user->id))
		{
			// If so, the user must login to view the order details.
			$msg     = Text::_('COM_JTICKETING_MESSAGE_LOGIN_FIRST');
			$current = Uri::getInstance()->toString();
			$url     = base64_encode($current);
			$app->enqueueMessage($msg);
			$app->redirect(Route::_('index.php?option=com_users&view=login&return=' . $url, false));
		}

		// Check if user is logged in and have super admin access.
		if (!$this->user->authorise('core.create', 'com_jticketing'))
		{
			$app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'error');

			return false;
		}

		$jticketingmainhelper = new jticketingmainhelper;
		$this->collect_attendee_info_checkout = $comParams->get('collect_attendee_info_checkout', '', 'INT');
		$this->isEnrollmentEnabled  = $comParams->get('enable_self_enrollment', '', 'INT');
		$this->isEnrollmentApproval = $comParams->get('enable_enrollment_approval', '', 'INT');
		$this->enableAttendeeMove   = $comParams->get('enable_attendee_move', 0, 'INT');
		$this->nameCardField        = $comParams->get('attendee_field', '');
		$this->attendeeListingFields = $comParams->get('attendee_listing_fields', ['COM_JTICKETING_ENROLMENT_USER_USERNAME','COM_JTICKEITNG_ATTENDEE_EMAIL','COM_JTICKETING_ENROLMENT_ACTION','COM_JTICKETING_CHECKIN'], 'ARRAY');

		$this->attendeeListingFields = $comParams->get('attendee_listing_fields', ['COM_JTICKETING_ENROLMENT_USER_USERNAME','COM_JTICKEITNG_ATTENDEE_EMAIL','COM_JTICKETING_ENROLMENT_ACTION','COM_JTICKETING_CHECKIN'], 'ARRAY');

		if (!$comParams->get('entry_number_assignment', 0,'INT'))
		{
			$this->attendeeListingFields = array_diff($this->attendeeListingFields, ['COM_JTICKEITNG_ATTENDEE_ENTRY_NUMBER']);
		}

		$this->state         = $this->get('State');
		$input               = Factory::getApplication()->getInput();
		$this->items	     = $this->get('Items');
		$this->pagination	 = $this->get('Pagination');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');

		if ($layout == 'attendee_details')
		{
			$attendee_id            = $input->get('attendee_id', '0', 'INT');

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

				if (!empty($this->data)
					&& ($this->user->id == $orderData->owner_id || $this->user->id == $eventCreator || $isAdmin))
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
		elseif ($layout == 'signin' || $layout == 'namecard')
		{
			$integration = JT::getIntegration();
			$eventId     = $this->state->get('filter.events');

			/* @var $event JTicketingEventJticketing */
			$event       = JT::event()->loadByIntegration($eventId);

			if ((int) $this->user->id !== $event->getCreator())
			{
				$app->enqueueMessage(Text::_('COM_JTICKETING_MESSAGE_LOGIN_FIRST'), 'error');

				return false;
			}

			$this->component = '&tmpl=component&filter[events]=' . $this->activeFilters['events'];
			$this->filterHide = false;
		}
		else
		{
			$this->sampleAttendeeImportFilepath = Uri::root() . 'media/com_jticketing/samplecsv/AttendeeImport.csv';
			$this->timezoneFilepath = Uri::root() . 'media/com_jticketing/samplecsv/timeZone.csv';

			// Modal pop up for mass enrollment params
			$this->modal_params           = array();
			$this->modal_params['height'] = "500px";
			$this->modal_params['width']  = "500px";
			$this->modal_params['url']    = Route::_(URI::base(true) . '/index.php?option=com_jticketing&view=enrollment&tmpl=component');
			$this->body                   = "";

			// Modal pop up for csv attendee import params
			$app          = Factory::getApplication();
			$jinput        = $app->input;
			$this->tmpl    = $jinput->get('tmpl', '');
			$this->csv_params           = array();
			$this->csv_params['height'] = "500px";
			$this->csv_params['width']  = "200px";
			$this->csv_params['url'] = Route::_(URI::base(true) . '/index.php?option=com_jticketing&view=attendees&tmpl=component&layout=csv_import');

			// Get order status array with their full forms.
			$this->attendeesModel  = $this->getModel();
			$this->attendeeActions = $this->get('AttendeeActions');

			// Modal pop up for mass enrollment params
			$this->attendeePrams           = array();
			$this->attendeePrams['height'] = "500px";
			$this->attendeePrams['width']  = "200px";
			$this->attendeePrams['title']  = Text::_('COM_JTICKETING_VIEW_ATTENDEES_MOVE_ATTENDEE_DESCRIPTION');

			// Modal pop up for csv attendee import params
			$this->csv_params           = array();
			$this->csv_params['height'] = "500px";
			$this->csv_params['width']  = "200px";
			$this->csv_params['title']  = Text::_('COMJTICKETING_EVENT_IMPORT_CSV');
			$this->csv_params['url']    = Route::_(URI::base(true) . '/index.php?option=com_jticketing&view=attendees&tmpl=component&layout=csv_import');

			$eventsModel = JT::model('events');
			$eventData   = array('eventStatus' => 'ongoing');

			// Show only my events in select list if enroll all permission is not allowed.
			if (!($this->user->authorise('core.enrollall', 'com_jticketing')))
			{
				$eventData['creatorId'] = $this->user->id;
			}

			// Get the options for event select list .
			$onGoingEvents      = $eventsModel->getEvents($eventData);
			$this->eventoptions = array();

			foreach ($onGoingEvents as $event)
			{
				if (JT::event($event->id)->getTicketTypes())
				{
					if ($comParams->get('enable_eventstartdateinname'))
					{
						$startDate    = $utilities->getFormatedDate($event->startdate);
						$event->title = $event->title . '(' . $startDate . ')';
					}

					$this->eventoptions[] = HTMLHelper::_('select.option', $event->id, $this->escape($event->title));
				}
			}

			// Pass the required data and load the layout file.
			$displayData        = array('eventOptions' => $this->eventoptions);
			$this->attendeeBody = '';
			$this->attendeeBody = LayoutHelper::render(
					'attendeemove', $displayData, JPATH_SITE . '/components/com_jticketing/views/enrollment/tmpl/'
					);

			if ($this->tmpl === 'component' && $layout != 'csv_import')
			{
				$this->filterForm->setFieldAttribute('events', 'disabled', 'true', 'filter');

				if (isset($this->activeFilters['events']))
				{
					$this->component = '&tmpl=component&filter[events]=' . $this->activeFilters['events'];
				}
			}
		}

		// Load backend languages
		$lang         = Factory::getLanguage();
		$extension    = 'com_jticketing';
		$base_dir     = JPATH_ADMINISTRATOR;
		$language_tag = '';
		$reload       = true;
		$lang->load($extension, $base_dir, $language_tag, $reload);

		$menu = $app->getMenu();
		$attendeesMenuItem = $menu->getItems('link', 'index.php?option=com_jticketing&view=attendees');
		$this->attendee_item_id = 0;
		if (isset($attendeesMenuItem[0]) )
		{
			$this->attendee_item_id = $attendeesMenuItem[0]->id;
		}

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors));
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
	protected function addTJtoolbar()
	{
		$toolbar   = Toolbar::getInstance('toolbar');
		$canDo     = $this->canDo;
		$comParams = JT::config();
		$integration = $comParams->get('integration', '', 'INT');

		// Add New button manage enrollment view
		if (($canDo->{'core.enrollall'} || $canDo->{'core.enrollown'}) && $comParams->get('enable_self_enrollment'))
		{
			$link = Route::_(URI::base(true) . '/index.php?option=com_jticketing&view=enrollment&tmpl=component');

			echo HTMLHelper::_(
				'bootstrap.renderModal', 'enrollmentpreviewModal',
					array(
						'url'         => $link, 'title' => Text::_('COM_JTICKETING_TITLE_ENROLLMENTS_NEW'),
						'height'      => '700px', 'width' => '600px',
						'bodyHeight'  => '70', 'modalWidth' => '80',
						'closeButton' => false, 'backdrop' => 'static',
						'keyboard'    => false,
						'footer'      => '<button type="button" class="btn btn-secondary" data-dismiss="modal" data-bs-dismiss="modal">' . Text::_('COM_JTICKETING_MODAL_CLOSE') . '</button>',
					)
				);

	        // Joomla 6: JVERSION check removed
		if (false) // Legacy < '4.0.0')
	        {
				$toolbar->appendButton('Custom', '<a class="modal af-d-block af-relative btn btn-sm
					btn-primary" href="' . $link . '" data-target="#enrollmentpreviewModal" data-toggle="modal" >
				<span class="icon-new icon-white"></span>' . htmlspecialchars(Text::_('COM_JTICKETING_TITLE_ENROLLMENTS_NEW')) . '</a>&nbsp;&nbsp;'
				);
			}
			else
			{
				$toolbar->appendButton(
					'Custom', '<a
				class="af-d-block af-relative btn btn-primary"
				onclick="document.getElementById(\'enrollmentpreviewModal\').open();"
				href="javascript:void(0);"> <span class="icon-new icon-white"></span> ' . Text::_('COM_JTICKETING_TITLE_ENROLLMENTS_NEW') . '</a>&nbsp;&nbsp;'
					);
	        }
	    }

		if ($integration == 2)
		{
			if (($canDo->{'core.enrollall'} || $canDo->{'core.enrollown'}) && $comParams->get('enable_self_enrollment'))
			{
				if ($this->tmpl !== 'component')
				{
					$link = Route::_(URI::base(true) . '/index.php?option=com_jticketing&view=attendees&tmpl=component&layout=csv_import');

					echo HTMLHelper::_(
						'bootstrap.renderModal', 'importCsvModal',
							array(
								'url'        => $link, 'title' => Text::_('COMJTICKETING_EVENT_IMPORT_CSV'),
								'height'     => '700px', 'width' => '800px',
								'bodyHeight' => '70', 'modalWidth' => '80',
							)
						);

					// Joomla 6: JVERSION check removed
		if (false) // Legacy < '4.0.0')
					{
						$toolbar->appendButton('Custom', '<a class="modal af-d-block af-relative btn btn-sm
							btn-primary" href="' . $link . '" data-target="#importCsvModal" data-toggle="modal" >
						<span class="icon-upload icon-white"></span>' . htmlspecialchars(Text::_('COMJTICKETING_EVENT_IMPORT_CSV')) . '</a>&nbsp;&nbsp;'
						);
					}
					else
					{
						$toolbar->appendButton(
							'Custom', '<a
						class="af-d-block af-relative btn  btn-primary"
						onclick="document.getElementById(\'importCsvModal\').open();"
						href="javascript:void(0);"> <span class="icon-upload icon-white"></span> ' . Text::_('COMJTICKETING_EVENT_IMPORT_CSV') . '</a>&nbsp;&nbsp;'
							);
					}
				}
			}
		}

		if (!empty($this->items))
		{
			if ($canDo->{'core.edit.state'})
			{
				if ($this->tmpl !== 'component')
				{
					ToolbarHelper::custom('attendees.checkin', 'publish.png', '', Text::_('COM_JTICKETING_CHECKIN_SUCCESS'));

					ToolbarHelper::custom('attendees.undochekin', 'unpublish.png', '', Text::_('COM_JTICKETING_CHECKIN_FAIL'));
				}

				$message = array();
				$message['success']    = Text::_("COM_JTICKETING_EXPORT_FILE_SUCCESS");
				$message['error']      = Text::_("COM_JTICKETING_EXPORT_FILE_ERROR");
				$message['inprogress'] = Text::_("COM_JTICKETING_EXPORT_FILE_NOTICE");

				$toolbar->appendButton('CsvExport',  $message);
			}
		}

		return $toolbar->render();
	}
}
