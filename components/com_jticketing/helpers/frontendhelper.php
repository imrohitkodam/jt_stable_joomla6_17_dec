<?php
/**
 * @package     JTicketing
 * @subpackage  com_jticketing
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
use Joomla\Registry\Registry;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\Filesystem\File;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Form\Form;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Table\Table;

ini_set('memory_limit', '1000M');
HTMLHelper::_('bootstrap.renderModal', 'a.modal');
require_once JPATH_ADMINISTRATOR . '/components/com_jticketing/models/venue.php';

/**
 * jticketingfrontendhelper
 *
 * @since  1.0
 */
class Jticketingfrontendhelper
{
	public $jticketingmainhelper, $jt_params, $enable_self_enrollment, $supress_buy_button, 
		$accesslevels_for_enrollment, $buyTicketItemId;

	/**
	 * Constructor
	 *
	 * @since   1.0
	 */
	public function __construct()
	{
		$path = JPATH_ROOT . '/components/com_jticketing/helpers/main.php';

		if (!class_exists('jticketingmainhelper'))
		{
			JLoader::register('jticketingmainhelper', $path);
			JLoader::load('jticketingmainhelper');
		}

		$this->jticketingmainhelper = new jticketingmainhelper;
		$db                         = Factory::getDbo();
	}

	/**
	 * Render booking HTML
	 *
	 * @param   int     $eventid         id of event
	 * @param   int     $userid          userid
	 * @param   object  $eventdata       eventdata
	 * @param   string  $redirectionUrl  redirection url
	 *
	 * @return  array   HTML
	 *
	 * @since   1.0
	 */
	public function renderBookingHTML($eventid, $userid = '', $eventdata = '', $redirectionUrl = '')
	{
		$this->jt_params = JT::config();
		$showbook        = $eventdata->isAllowedToBuy($userid);

		$return              = array();
		$return['startdate'] = $eventdata->getStartDate();
		$return['enddate']   = $eventdata->getEndDate();

		$isboughtEvent         = $eventdata->isBought();
		$return['isPaidEvent'] = $eventdata->isPaid();
		$com_params            = ComponentHelper::getParams('com_jticketing');
		$enableWaitingList     = $com_params->get('enable_waiting_list');
		$integration           = $com_params->get('integration');
		$singleTicketPerUser   = $com_params->get('single_ticket_per_user');

		if (file_exists(JPATH_SITE . '/components/com_jticketing/models/enrollment.php')) { require_once JPATH_SITE . '/components/com_jticketing/models/enrollment.php'; }
		$enrollmentModel = new JticketingModelEnrollment;

		if (!empty($userid))
		{
			$isEnrolled = $enrollmentModel->isAlreadyEnrolled($eventid, $userid);
		}

		$return['isboughtEvent'] = 0;

		if ((!empty($isEnrolled) && $isEnrolled == 1 && ($integration != 2 || $eventdata->online_events == 1)) || !empty($isboughtEvent))
		{
			$return['isboughtEvent'] = 1;
		}

		if (empty($userid))
		{
			$userid = Factory::getUser()->id;
		}

		$user = Factory::getUser($userid);

		$this->enable_self_enrollment      = $this->jt_params->get('enable_self_enrollment', '0', 'INT');
		$this->supress_buy_button          = $this->jt_params->get('supress_buy_button', '', 'INT');
		$this->accesslevels_for_enrollment = $this->jt_params->get('accesslevels_for_enrollment');
		$groups                            = $user->getAuthorisedViewLevels();
		$this->buyTicketItemId             = 0;

		if (!empty($this->accesslevels_for_enrollment))
		{
			// Check access levels for enrollment
			foreach ($groups as $group)
			{
				if (is_array($this->accesslevels_for_enrollment))
				{
					if (in_array($group, $this->accesslevels_for_enrollment, true))
					{
						$allowAccessLevelEnrollment = 1;
						break;
					}
				}
			}
		}

		if ($showbook == 1)
		{
			$enroll = 0;

			$userAuthorisedEnroll = $user->authorise('core.enroll', 'com_jticketing.event.' . $eventid);

			// Show enroll button if - Quick book is set, self enrolment permission is set and ticket is not bought
			if ($this->enable_self_enrollment == 1 && $userAuthorisedEnroll == '1')
			{
				$enroll = 1;
				$displayEnrollButton = 0;

				if ($integration == 2)
				{
					if (!empty($eventdata->online_events) == 1 && (!empty($isEnrolled) || $eventdata->created_by == $userid))
					{
						$displayEnrollButton = 1;
					}
				}

				if (empty($isEnrolled) && $displayEnrollButton != 1)
				{
					$itemid = Factory::getApplication()->getInput()->get('Itemid');
					$enrollTicketLink = Route::_('index.php?option=com_jticketing&task=enrollment.save&selected_events=' . $eventid .
						'&cid=' . $userid .
						'&Itemid=' . $itemid . '&notify_user_enroll=1', false
						);

					if ($redirectionUrl)
					{
						$enrollTicketLink = Route::_('index.php?option=com_jticketing&task=enrollment.save&selected_events=' . $eventid
							. '&cid=' . $userid . '&notify_user_enroll=1'
							. '&Itemid=' . $itemid
							. '&redirectUrl=' . $redirectionUrl, false
							);
					}

					$enrollTicketLink .= '&' . Session::getFormToken() . '=1';
					$return['enrol_link'] = $enrollTicketLink;

					$return['enrol_button'] = "<a href=" . $enrollTicketLink . " class='btn
					btn-default btn-success com_jt_book com_jticketing_button w-100 booking-btn'>";
					$return['enrol_button'] .= Text::_('COM_JTICKETING_ENROLL_BUTTON') . "</a>";
				}

				elseif((!empty($isEnrolled) && $isEnrolled == 1 && $integration == 2 && $eventdata->online_events == 0)
					|| (!empty($isEnrolled) && $isEnrolled == 1 && $integration != 2) )
				{
					$buttonTxt = Text::_('COM_JTICKETING_EVENTS_ENROLLED_BTN');
					$return['enrolled_button'] = "<button type='button' class='btn btn-info disabled w-100 booking-btn'>" . $buttonTxt . "</button>";
				}
				elseif ((!empty($isEnrolled) && $isEnrolled == 2))
				{
					$pendingButtonTxt = Text::_('COM_JTICKETING_EVENTS_ENROLL_PENDING_BUTTON');
					$return['enroll_pending_button'] = "<button type='button' class='btn btn-info disabled w-100 booking-btn'>" . $pendingButtonTxt . "</button>";
				}
				elseif ((!empty($isEnrolled) && $isEnrolled == 3))
				{
					$cancelButtonTxt = Text::_('COM_JTICKETING_EVENTS_ENROLL_CANCEL_BTN');
					$return['enroll_cancel_button'] = "<button type='button' class='btn btn-info disabled w-100 booking-btn'>" . $cancelButtonTxt . "</button>";
				}
			}

			require_once JPATH_SITE . "/components/com_jticketing/helpers/route.php";
			$JTRouteHelper = new JTRouteHelper;
			$buyLink = 'index.php?option=com_jticketing&view=order&layout=default&eventid=' . $eventid;
			$buyTicketLink = $JTRouteHelper->JTRoute($buyLink);

			if ($redirectionUrl)
			{
				$session = Factory::getSession();
				$session->set('redirectUrl', $redirectionUrl);
			}

			if ($integration == 2)
			{
				if (((empty($isboughtEvent) && $eventdata->online_events == 1 && $eventdata->created_by != $userid)
					|| $eventdata->online_events == 0 && (empty($singleTicketPerUser))
					|| (!empty($singleTicketPerUser) && empty($isboughtEvent))) && ($enroll == 0))
				{
					$return['buy_button_link'] = $buyTicketLink;
					$return['buy_button'] = "<a	href="
											. $buyTicketLink
											. " class='btn  btn-primary btn-success com_jt_book com_jticketing_button w-100 booking-btn'>";
					$return['buy_button'] .= Text::_('COM_JTICKETING_BUY_BUTTON') . "</a>";
				}
				elseif (!empty($singleTicketPerUser) && !empty($isboughtEvent))
				{
					if (!empty($userid))
					{
						if (file_exists(JPATH_SITE . '/components/com_jticketing/models/attendees.php')) { require_once JPATH_SITE . '/components/com_jticketing/models/attendees.php'; }
						$attendeesModel = new JticketingModelAttendees;

						$attendees      = $attendeesModel->getAttendees($eventdata->id, $userid);
						$viewTicketLink = Route::_('index.php?option=com_jticketing&view=mytickets&tmpl=component&layout=ticketprint&attendee_id='
							. $attendees->id, false
							);

						$modalConfig = array('width' => '800px', 'height' => '300px', 'modalWidth' => 80, 'bodyHeight' => 70);
						$modalConfig['url'] = $viewTicketLink;
						$modalConfig['title'] = Text::_('COM_JTICKETING_VIEW_TICKET_BUTTON');
						$jtEventViewTicketBtnHTML = HTMLHelper::_('bootstrap.renderModal', 'jtEventViewTicketBtn' . $attendees->id, $modalConfig);

						$return['viewTicket_button_link'] = $viewTicketLink;
						$return['viewTicket_button'] = $jtEventViewTicketBtnHTML;

						// Joomla 6: JVERSION check removed
		if (false) // Legacy < '4.0.0')
						{
							$return['viewTicket_button'] .= '<a data-target="#jtEventViewTicketBtn' . $attendees->id . '" data-toggle="modal" class="af-relative af-d-block btn btn-primary btn-info com_jt_book com_jticketing_button w-100 booking-btn">';
						}
						else
						{
							$return['viewTicket_button'] .= '<a data-bs-target="#jtEventViewTicketBtn' . $attendees->id . '" data-bs-toggle="modal" class="af-relative af-d-block btn btn-primary btn-info com_jt_book com_jticketing_button w-100 booking-btn">';
						}

						$return['viewTicket_button'] .= Text::_('COM_JTICKETING_VIEW_TICKET_BUTTON') . '</a>';
					}
				}
			}
			elseif ($enroll == 0)
			{
				$return['buy_button_link'] = $buyTicketLink;
				$return['buy_button'] = "<a	href="
										. $buyTicketLink
										. " class='btn btn-primary btn-success com_jt_book com_jticketing_button w-100 booking-btn'>";
				$return['buy_button'] .= Text::_('COM_JTICKETING_BUY_BUTTON') . "</a>";
			}
			elseif (!empty($singleTicketPerUser) && !empty($isboughtEvent))
			{
				if (!empty($userid))
				{
					if (file_exists(JPATH_SITE . '/components/com_jticketing/models/attendees.php')) { require_once JPATH_SITE . '/components/com_jticketing/models/attendees.php'; }
					$attendeesModel = new JticketingModelAttendees;

					$attendees = $attendeesModel->getAttendees(
								array('event_id' => $eventdata->id,
									'owner_id' => $userid)
							);
					$viewTicketLink = Route::_('index.php?option=com_jticketing&view=mytickets&tmpl=component&layout=ticketprint&attendee_id='
						. $attendees->id, false
						);

					$modalConfig = array('width' => '800px', 'height' => '300px', 'modalWidth' => 80, 'bodyHeight' => 70);
					$modalConfig['url'] = $viewTicketLink;
					$modalConfig['title'] = Text::_('COM_JTICKETING_VIEW_TICKET_BUTTON');
					$jtEventViewTicketBtnHTML = HTMLHelper::_('bootstrap.renderModal', 'jtEventViewTicketBtn' . $attendees->id, $modalConfig);

					$return['viewTicket_button_link'] = $viewTicketLink;
					$return['viewTicket_button'] = $jtEventViewTicketBtnHTML;

					// Joomla 6: JVERSION check removed
		if (false) // Legacy < '4.0.0')
					{
						$return['viewTicket_button'] .= '<a data-target="#jtEventViewTicketBtn' . $attendees->id . '" data-toggle="modal" class="af-relative af-d-block btn btn-default btn-info com_jt_book com_jticketing_button w-100 booking-btn">';
					}
					else
					{
						$return['viewTicket_button'] .= '<a data-bs-target="#jtEventViewTicketBtn' . $attendees->id . '" data-bs-toggle="modal" class="af-relative af-d-block btn btn-default btn-info com_jt_book com_jticketing_button w-100 booking-btn">';
					}

					$return['viewTicket_button'] .= Text::_('COM_JTICKETING_VIEW_TICKET_BUTTON') . '</a>';
				}
			}
		}
		elseif ($showbook == 2 && $enableWaitingList != 'none' && (empty($isEnrolled)))
		{
			if (file_exists(JPATH_SITE . '/components/com_jticketing/models/waitlistform.php')) { require_once JPATH_SITE . '/components/com_jticketing/models/waitlistform.php'; }
				$waitlistFormModel = new JTicketingModelWaitlistForm;
				$isAdded = $waitlistFormModel->isAlreadyAddedToWaitlist($eventid, $userid);

			if (!empty($isAdded))
			{
					$buttonTxt = Text::_('COM_JTICKETING_EVENTS_WAITLISTED_BTN');
					$return['waitlisted_button'] = "<button type='button' class='btn btn-info disabled w-100 booking-btn'>" . $buttonTxt . "</button>";
			}
			else
			{
				$waitinglistLink = Route::_('index.php?option=com_jticketing&task=waitlistform.save&id=0&eventid=' . $eventid, false);

				if (!empty($redirectionUrl))
				{
					$waitinglistLink = Route::_('index.php?option=com_jticketing&task=waitlistform.save&id=0&eventid=' .
						$eventid . '&redirectUrl=' . $redirectionUrl, false
						);
				}

				$waitinglistLink .= '&' . Session::getFormToken() . '=1';
				$return['waitinglist_button_link'] = $waitinglistLink;

				$return['waitinglist_button'] = "<a	href="
											. $waitinglistLink
											. " class='btn  btn-default btn-info com_jt_book com_jticketing_button w-100 booking-btn'>";
				$return['waitinglist_button'] .= Text::_('COM_JTICKETING_WAITINGLIST_BUTTON') . "</a>";
			}
		}
		elseif($showbook == 2 && (!empty($isEnrolled) && $isEnrolled == 1))
		{
			$buttonTxt = Text::_('COM_JTICKETING_EVENTS_ENROLLED_BTN');
					$return['enrolled_button'] = "<button type='button' class='btn btn-info disabled w-100 booking-btn'>" . $buttonTxt . "</button>";
		}
		elseif ($integration == 2 && !empty($singleTicketPerUser) && !empty($isboughtEvent) && $showbook == 2
			&& $enableWaitingList != 'none' && (!empty($isEnrolled)))
		{
			if (!empty($userid))
			{
				if (file_exists(JPATH_SITE . '/components/com_jticketing/models/attendees.php')) { require_once JPATH_SITE . '/components/com_jticketing/models/attendees.php'; }
				$attendeesModel = new JticketingModelAttendees;

				$attendees      = $attendeesModel->getAttendees($eventdata->id, $userid);
				$viewTicketLink = Route::_('index.php?option=com_jticketing&view=mytickets&tmpl=component&layout=ticketprint&attendee_id='
					. $attendees->id, false
					);

				$modalConfig = array('width' => '800px', 'height' => '300px', 'modalWidth' => 80, 'bodyHeight' => 70);
				$modalConfig['url'] = $viewTicketLink;
				$modalConfig['title'] = Text::_('COM_JTICKETING_VIEW_TICKET_BUTTON');
				$jtEventViewTicketBtnHTML = HTMLHelper::_('bootstrap.renderModal', 'jtEventViewTicketBtn' . $attendees->id, $modalConfig);

				$return['viewTicket_button_link'] = $viewTicketLink;
				$return['viewTicket_button'] = $jtEventViewTicketBtnHTML;

				// Joomla 6: JVERSION check removed
		if (false) // Legacy < '4.0.0')
				{
					$return['viewTicket_button'] .= '<a data-target="#jtEventViewTicketBtn' . $attendees->id . '" data-toggle="modal" class="af-relative af-d-block btn btn-primary btn-info com_jt_book com_jticketing_button w-100 booking-btn">';
				}
				else
				{
					$return['viewTicket_button'] .= '<a data-bs-target="#jtEventViewTicketBtn' . $attendees->id . '" data-bs-toggle="modal" class="af-relative af-d-block btn btn-primary btn-info com_jt_book com_jticketing_button w-100 booking-btn">';
				}

				$return['viewTicket_button'] .= Text::_('COM_JTICKETING_VIEW_TICKET_BUTTON') . '</a>';
			}
		}

		if (!empty($eventdata->event_url))
		{
			$return['details_button_link'] = $eventdata->event_url;
			$return['details_button'] = "<a	href="
									. $eventdata->event_url
								. " class='btn btn-primary com_jticketing_button w-100 booking-btn'>"
								. Text::_('COM_JTICKETING_DETAILS') . "</a>";
		}

		return $return;
	}

	/**
	 * This function is used to get client name from backend selected integration
	 *
	 * @return  void
	 *
	 * @since   1.0
	 *
	 * @deprecated 3.2.0 The method is deprecated and will be removed in the next version.
	 */
	public function loadHelperClasses()
	{
		$path                             = JPATH_ROOT . '/components/com_jticketing/helpers/main.php';
		$jticketingfrontendhelper         = JPATH_ROOT . '/components/com_jticketing/helpers/frontendhelper.php';
		$JTicketingIntegrationsHelperPath = JPATH_ROOT . '/components/com_jticketing/helpers/integrations.php';
		$helperPath                       = JPATH_SITE . '/components/com_jticketing/helpers/event.php';
		$mediaHelperPath                  = JPATH_SITE . '/components/com_jticketing/helpers/media.php';
		$field_manager_path               = JPATH_SITE . '/components/com_tjfields/helpers/tjfields.php';

		if (!class_exists('jticketingmainhelper'))
		{
			JLoader::register('jticketingmainhelper', $path);
			JLoader::load('jticketingmainhelper');
		}

		if (!class_exists('jticketingfrontendhelper'))
		{
			JLoader::register('jticketingfrontendhelper', $jticketingfrontendhelper);
			JLoader::load('jticketingfrontendhelper');
		}

		if (!class_exists('JTicketingIntegrationsHelper'))
		{
			JLoader::register('JTicketingIntegrationsHelper', $JTicketingIntegrationsHelperPath);
			JLoader::load('JTicketingIntegrationsHelper');
		}

		if (!class_exists('jteventHelper'))
		{
			JLoader::register('jteventHelper', $helperPath);
			JLoader::load('jteventHelper');
		}

		if (file_exists($field_manager_path))
		{
			if (!class_exists('TjfieldsHelper'))
			{
				JLoader::register('TjfieldsHelper', $field_manager_path);
				JLoader::load('TjfieldsHelper');
			}
		}

		if (!class_exists('jticketingMediaHelper'))
		{
			JLoader::register('jticketingMediaHelper', $mediaHelperPath);
			JLoader::load('jticketingMediaHelper');
		}
	}

	/**
	 * This is function is used to get rsvp button
	 *
	 * @return  void
	 *
	 * @since   1.0
	 *
	 * @deprecated 3.2.0 The method is deprecated and will be removed in the next version.
	 */
	public function showrsvp()
	{
		$this->loadHelperClasses();
		$jtickeketing_component_enabled = ComponentHelper::isEnabled('com_jticketing', true);
		$jtickeketing_module_enabled = ModuleHelper::isEnabled('mod_jticketing_buy', true);
		$event_link = $event->getPermalink();

		if ($jtickeketing_component_enabled and $jtickeketing_module_enabled)
		{
			$com_params           = ComponentHelper::getParams('com_jticketing');
			$integration          = $com_params->get('integration');
			$jticketingmainhelper = new jticketingmainhelper;
			$eventid              = $event->id;
			$showbuybutton        = JT::event($eventid)->isAllowedToBuy();
			$isEventbought        = $event->isBought();

			if (File::exists(JPATH_ROOT . '/components/com_jticketing/jticketing.php'))
			{
				if ($showbuybutton and empty($isEventbought))
				{
					$show_rsvp = 0;
					$lang      = Factory::getLanguage();
					$extension = 'mod_jticketing_buy';
					$base_dir  = JPATH_SITE;
					$lang->load($extension, $base_dir);

					return 0;
				}
			}
		}

		return 1;
	}

	/**
	 * This function is used to get client name from backend selected integration
	 *
	 * @param   int  $integration  backend selected integration
	 *
	 * @return  void
	 *
	 * @since   1.0
	 *
	 * @deprecated 3.2.0 The method is deprecated and will be removed in the next version.
	 */
	public function getClientName($integration)
	{
		return JT::getIntegration();
	}

	/**
	 * This is function is used to get all fields like universal fields(from tjfields),core fields(in JTicketing)
	 *
	 * @param   object  $eventid      format of output
	 * @param   object  $fieldnames   fieldnames
	 * @param   object  $reultFormat  eventid
	 *
	 * @return  void
	 *
	 * @since   1.0
	 *
	 * @deprecated 3.2.0 Use JT::model('attendeefields')->getAttendeeFields
	 * ($eventId, $attendeeId = 0);
	 */
	public function getAllfields($eventid = '', $fieldnames = '*', $reultFormat = "object")
	{
		$db      = Factory::getDbo();
		$where   = array();
		$where[] = " WHERE state=1 ";

		// Find core fields which comes while installing JTIcketing.
		$where_core = implode(" AND ", $where);
		$query      = "SELECT " . $fieldnames . "
		 FROM #__jticketing_attendee_fields " . $where_core . "
		 AND core=1 order by ordering";
		$db->setQuery($query);
		$fields['core_fields'] = $db->loadObjectlist();

		if ($reultFormat == 'array')
		{
			$fields['core_fields'] = $db->loadAssocList();
		}

		// Find  fields which are created using field manager.
		if (file_exists(JPATH_ROOT . '/components/com_tjfields/helpers/tjfields.php'))
		{
			$filedHelperPath = JPATH_ROOT . '/components/com_tjfields/helpers/tjfields.php';

			if (!class_exists('TjfieldsHelper'))
			{
				JLoader::register('TjfieldsHelper', $filedHelperPath);
				JLoader::load('TjfieldsHelper');
			}

			$TjfieldsHelper                      = new TjfieldsHelper;
			$fields['universal_attendee_fields'] = $TjfieldsHelper->getUniversalFields('com_jticketing.ticket');

			if ($fields['universal_attendee_fields'])
			{
				foreach ($fields['universal_attendee_fields'] AS $key => &$val)
				{
					$val->default_selected_option = $TjfieldsHelper->getOptions($val->id);

					// Set as universal fields, this is important
					$val->is_universal = 1;
				}
			}
		}

		if (!$eventid)
		{
			return $fields;
		}

		if ($eventid)
		{
			$integration = JT::getIntegration();
			$intxrefidevid = JT::event($eventid, $integration)->integrationId;

			// If no integration id found, return
			if (!$intxrefidevid)
			{
				return $fields;
			}

			$where[] = " eventid=$intxrefidevid ";
		}

		$where_custom = implode(" AND ", $where);
		$query        = "SELECT " . $fieldnames . "
		 FROM #__jticketing_attendee_fields " . $where_custom . "
		 AND core<>1 ORDER BY ordering";
		$db->setQuery($query);
		$fields['attendee_fields'] = $db->loadObjectlist();

		if ($reultFormat == 'array')
		{
			$fields['attendee_fields'] = $db->loadAssocList();
		}

		return $fields;
	}

	/**
	 * This is function is used to store event details in JTicketing
	 *
	 * @param   object  $objpassed   format of output
	 * @param   object  $source      com_jticketing/com_jevents
	 * @param   object  $postparams  post data
	 *
	 * @return  void
	 *
	 * @since   1.0
	 *
	 * @deprecated 3.2.0 The method is deprecated and will be removed in the next version.
	 */
	public function saveEvent($objpassed, $source, $postparams = '')
	{
		$db  = Factory::getDbo();
		$obj = $objpassed->eventdata;

		if (!isset($objpassed->eventid))
		{
			if (!$db->insertObject('#__jticketing_events', $obj, 'id'))
			{
				echo $db->stderr();

				return false;
			}

			$event_id = $db->insertid();

			// Saving the event id into integration table
			$obj->event_id      = $event_id;
			$obj->saving_method = 'save';
		}
		else
		{
			$event_id = $objpassed->eventid;
			$obj->id  = $objpassed->eventid;

			if (!$db->updateObject('#__jticketing_events', $obj, 'id'))
			{
			}

			$obj->event_id      = $objpassed->eventid;
			$obj->saving_method = 'edit';
		}

		$obj->eventid     = $obj->event_id;
		$obj->paypalemail = $objpassed->paypalemail;
		$integration_id   = $this->saveIntegrationDetails($obj, $source);
		$file_field       = "event_image";
		$file_error       = $_FILES[$file_field]['error'];

		if (!$file_error == 4)
		{
			// Upload event image
			$uploadSuccess = $this->uploadImage($integration_id);
		}

		// This is Event integration ID which is used in all reference tables of JTicketing AND other products
		$obj->event_integrationid  = $integration_id;
		$com_params                = ComponentHelper::getParams('com_jticketing');
		$obj->currency             = $com_params->get('currency');
		$obj->event_integration_id = $integration_id;

		// Trigger plugins OnAfterJTEventUpdate
		PluginHelper::importPlugin('system');
		Factory::getApplication()->triggerEvent('OnAfterJTEventUpdate', array($obj, $postparams));

		return $event_id;
	}

	/**
	 * This is function is used to delete ticket types
	 *
	 * @param   int  $delete_ids  id of jticketing_types table
	 *
	 * @return  void
	 *
	 * @since   1.0
	 *
	 * @deprecated 3.2.0 The method is deprecated and will be removed in the next version.
	 */
	public function deleteTicket($delete_ids)
	{
		$db = Factory::getDbo();

		foreach ($delete_ids as $key => $value)
		{
			$query = 'DELETE FROM #__jticketing_types
			 WHERE id="' . $value . '"';
			$db->setQuery($query);

			if (!$db->execute())
			{
				echo $db->stderr();

				return false;
			}
		}
	}

	/**
	 * This is function is used to delete ticket types
	 *
	 * @param   int    $post     post data
	 * @param   int    $eventid  eventid
	 * @param   array  $data     added by komal for csv import
	 *
	 * @return  void
	 *
	 * @since   1.0
	 *
	 * @deprecated 3.2.0 The method is deprecated and will be removed in the next version.
	 */

	public function createTicketTypeObj($post, $eventid, $data = array())
	{
		$db            = Factory::getDbo();
		$jteventHelper = new jteventHelper;
		$obj           = new stdClass;

		// Added by komal for csv import

		if (isset($data['isImportCsv']))
		{
			$ids             = $post['ticket_type_id'];
			$title           = array('Free');
			$desc            = array('Free');
			$price           = array('0');
			$available       = array('');
			$access          = array('1');
			$state           = array('1');
			$count           = array('1');
			$dep             = array('');
			$unlimited_seats = array('1');
		}
		else
		{
			$ids             = $post->get('ticket_type_id', '', 'ARRAY');
			$title           = $post->get('ticket_type_title', '', 'ARRAY');
			$desc            = $post->get('ticket_type_desc', '', 'ARRAY');
			$price           = $post->get('ticket_type_price', '', 'ARRAY');
			$available       = $post->get('ticket_type_available', '', 'ARRAY');
			$access          = $post->get('ticket_type_access', '', 'ARRAY');
			$state           = $post->get('ticket_type_state', '', 'ARRAY');
			$count           = $post->get('ticket_type_count', '', 'ARRAY');
			$dep             = $post->get('ticket_type_deposit_price', '', 'ARRAY');
			$unlimited_seats = $post->get('ticket_type_unlimited_seats', '', 'ARRAY');
		}

		for ($i = 0; $i < count($ids); $i++)
		{
			$TicketTypesobj                  = new stdClass;
			$TicketTypesobj->id              = $ids[$i];
			$TicketTypesobj->count           = $available[$i];
			$TicketTypesobj->access          = $access[$i];
			$TicketTypesobj->state           = $state[$i];
			$TicketTypesobj->unlimited_seats = $unlimited_seats[$i];

			if ($available[$i] != 0)
			{
				$TicketTypesobj->available = $available[$i];
			}

			// Fix available seats while updating Events
			if ($TicketTypesobj->id)
			{
				$where = "	 id=" . $TicketTypesobj->id;
				$query = "SELECT id,count,available,access FROM #__jticketing_types
							WHERE  " . $where;
				$db->setQuery($query);
				$detailspresent = $db->loadObject();

				if (!empty($detailspresent))
				{
					$current_available_seats   = 0;
					$TicketTypesobj->available = $current_available_seats = $available[$i];

					// Fix available value in table if count increases OR decrerases manually
					// $TicketTypesobj->available =$jteventHelper->fixavailableSeats($current_available_seats, $detailspresent, $eventid);
				}
			}

			$TicketTypesobj->eventid     = $eventid;
			$TicketTypesobj->title       = $title[$i];
			$TicketTypesobj->desc        = $desc[$i];
			$TicketTypesobj->price       = $price[$i];
			$TicketTypesobj->deposit_fee = $dep[$i];
			$TicketTypesobj->access      = $access[$i];

			$this->createTicketTypes($TicketTypesobj, 'com_jticketing');
		}
	}

	/**
	 * This is function is used to get event data
	 *
	 * @param   int  $eventid  event id
	 *
	 * @return  void
	 *
	 * @since   1.0
	 *
	 * @deprecated 3.2.0 The method is deprecated and will be removed in the next version.
	 */
	public function getEvent($eventid)
	{
		$db = Factory::getDbo();
		if (file_exists(JPATH_ADMINISTRATOR . '/components/com_jticketing/models/event.php')) { require_once JPATH_ADMINISTRATOR . '/components/com_jticketing/models/event.php'; }
		$model  = new jticketingModelEvent;
		$result = $model->getEvent($eventid);

		return $result;
	}

	/**
	 * This is function is used to get event ca
	 *
	 * @return  void
	 *
	 * @since   1.0
	 *
	 * @deprecated 3.2.0 The method is deprecated and will be removed in the next version.
	 */
	public function getEventcat()
	{
		$db = Factory::getDbo();
		if (file_exists(JPATH_ADMINISTRATOR . '/components/com_jticketing/models/event.php')) { require_once JPATH_ADMINISTRATOR . '/components/com_jticketing/models/event.php'; }
		$model  = new jticketingModelEvent;
		$result = $model->getEventsCats();

		return $result;
	}

	/**
	 * This is function is used to delete ticket types
	 *
	 * @param   int  $eventid  eventid
	 * @param   int  $client   client name
	 *
	 * @return  void
	 *
	 * @since   1.0
	 *
	 * @deprecated 3.2.0 Use JT::event($eventId,JT::getIntegration())->integrationId; instead
	 */
	public function getIntegrationID($eventid, $client)
	{
		$db    = Factory::getDbo();
		$query = "SELECT id FROM #__jticketing_integration_xref WHERE source LIKE '" . $client . "' AND eventid=" . $eventid;
		$db->setQuery($query);

		return $rows = $db->loadResult();
	}

	/**
	 * This is function is used to delete ticket types
	 *
	 * @param   int  $integration_id  integration_id
	 * @param   int  $type_id         client name
	 *
	 * @return  Object
	 *
	 * @since   1.0
	 */
	public function getTicketTypes($integration_id, $type_id = '')
	{
		$db    = Factory::getDbo();
		$query = "SELECT * FROM #__jticketing_types WHERE  eventid='" . (int) $integration_id . "'";
		$db->setQuery($query);

		return $rows = $db->loadObjectlist();
	}

	/**
	 * This is function is used to get custom fields for event
	 *
	 * @param   int  $integration_id  integration_id
	 *
	 * @return  void
	 *
	 * @since   1.0
	 *
	 * @deprecated 3.2.0 The method is deprecated and will be removed in the next version.
	 */
	public function getEventCustomFields($integration_id)
	{
		$db          = Factory::getDbo();
		$tickettypes = $this->getTicketTypes($integration_id);
		$query       = "SELECT * FROM #__jticketing_field_values AS field_value
		LEFT JOIN #__jticketing_fields  as field ON field.id=field_value.field_id
		WHERE  event_id='" . $integration_id . "'";

		if (!empty($tickettypes))
		{
			if ($tickettypes[0]->id)
			{
				$query .= " AND ticket_type_id=" . $tickettypes[0]->id;
			}
		}

		$db->setQuery($query);
		$rows        = $db->loadObjectlist();
		$fieldsarray = new StdClass;

		foreach ($rows as $row)
		{
			$fieldname               = $row->field_title;
			$fielvalue               = $row->field_value;
			$fieldsarray->$fieldname = $fielvalue;
		}

		return $fieldsarray;
	}

	/**
	 * This is function is used to convert time
	 *
	 * @param   int  $passedtime  time to format
	 *
	 * @return  void
	 *
	 * @since   1.0
	 *
	 * @deprecated 3.2.0 The method is deprecated and will be removed in the next version.
	 */
	public function getTime($passedtime)
	{
		$db        = Factory::getDbo();
		$time_hour = $passedtime['hour'];
		$time_min  = $passedtime['min'];
		$time_ampm = $passedtime['ampm'];

		if (($time_ampm == 'PM') && ($time_hour < 12))
		{
			$time_hour = $time_hour + 12;
		}
		elseif (($time_ampm == 'AM') && ($time_hour == 12))
		{
			$time_hour = $time_hour - 12;
		}

		return $time_final = $time_hour . ":" . $time_min;
	}

	/**
	 * This is function is used to get city from country
	 *
	 * @param   int  $country  country id
	 *
	 * @return  void
	 *
	 * @since   1.0
	 *
	 * @deprecated 3.2.0 The method is deprecated and will be removed in the next version.
	 */
	public function getCity($country)
	{
		$db    = Factory::getDbo();
		$query = "SELECT c.city_id, c.city
		FROM #__tj_city AS c
		LEFT JOIN #__tj_country AS con
		ON c.country_code=con.country_code
		WHERE con.country_id=" . $country . "
		ORDER BY c.city";
		$db->setQuery($query);
		$rows = $db->loadAssocList();

		return $rows;
	}

	/**
	 * This is used to create order object from data
	 *
	 * @param   int  $data  data passed
	 *
	 * @return  void
	 *
	 * @since   1.0
	 *
	 * @deprecated 3.2.0 The method is deprecated and will be removed in the next version.
	 */
	public function createOrderObject($data)
	{
		$db = Factory::getDbo();

		if (!$data['integraton_id'])
		{
			$data['integraton_id'] = JT::event($data['eventid'], $data['client'])->integrationId;
		}

		$res                   = new StdClass;
		$res->event_details_id = $data['integraton_id'];

		if (isset($data['name']))
		{
			$res->name = $data['name'];
		}

		if (isset($data['email']))
		{
			$res->email = $data['email'];
		}

		if (isset($data['user_id']))
		{
			$res->user_id = $data['user_id'];
		}

		$res->coupon_code             = $data['coupon_code'];
		$res->coupon_discount         = $data['coupon_discount'];
		$res->coupon_discount_details = $data['coupon_discount_details'];
		$res->order_tax               = $data['order_tax'];
		$res->order_tax_details       = $data['order_tax_details'];
		$res->cdate                   = date("Y-m-d H:i:s");
		$res->mdate                   = date("Y-m-d H:i:s");

		if (isset($data['processor']))
		{
			$res->processor = $data['processor'];
		}

		if (isset($data['customer_note']))
		{
			$res->customer_note = $data['customer_note'];
		}

		$res->ticketscount = $data['no_of_tickets'];

		if (!$data['parent_order_id'])
		{
			$res->parent_order_id = 0;
		}
		else
		{
			$res->parent_order_id = $data['parent_order_id'];
		}

		$res->status = 'P';

		// This is calculated amount
		$res->original_amount = $data['original_amt'];
		$res->amount          = $data['amount'];
		$res->fee             = $data['fee'];
		$res->ip_address      = $_SERVER["REMOTE_ADDR"];

		return $res;
	}

	/**
	 * This is used to create ticket types
	 *
	 * @param   object  $tickettypesobj  tickettypesobj
	 * @param   string  $client          com_jticketing
	 *
	 * @return  void
	 *
	 * @since   1.0
	 *
	 * @deprecated 3.2.0 The method is deprecated and will be removed in the next version.
	 */
	public function createTicketTypes($tickettypesobj, $client = 'com_jticketing')
	{
		$db = Factory::getDbo();

		if (!$tickettypesobj->id && $tickettypesobj->title && $tickettypesobj->eventid)
		{
			// Insert object
			if (!$db->insertObject('#__jticketing_types', $tickettypesobj, 'id'))
			{
				echo $db->stderr();

				return false;
			}

			$tickettypeid = $db->insertid();
		}
		else
		{
			$db->updateObject('#__jticketing_types', $tickettypesobj, 'id');
		}

		return $tickettypeid;
	}

	/**
	 * This is used to create order items
	 *
	 * @param   int  $orderdata  orderdata
	 *
	 * @return  void
	 *
	 * @since   1.0
	 *
	 * @deprecated 3.2.0 The method is deprecated and will be removed in the next version.
	 */
	public function createOrderItems($orderdata)
	{
		$db                = Factory::getDbo();
		$order_items_array = $updated_ticket_field_value_id = array();
		$tid               = 0;

		// Delete order items That are removed
		if (array_key_exists("order_id", $orderdata))
		{
			if ($orderdata['order_id'])
			{
				$current_order_items = array();

				foreach ($orderdata['attendee_field'] as $attkeyold => $fieldsold)
				{
					// If order items id present update it
					if ($fieldsold['order_items_id'])
					{
						$current_order_items[] = $fieldsold['order_items_id'];
					}
				}

				$sql = "SELECT id FROM #__jticketing_order_items WHERE order_id=" . $orderdata['order_id'];
				$db->setQuery($sql);
				$ids = $db->loadColumn();

				if (count($ids) > count($current_order_items))
				{
					$diff    = array_diff($ids, $current_order_items);
					$diffids = implode("','", $diff);
					$query   = "DELETE FROM #__jticketing_order_items	WHERE id IN ('" . $diffids . "')";
					$db->setQuery($query);

					if (!$db->execute())
					{
					}

					$query = "DELETE FROM #__jticketing_ticket_field_values	WHERE id IN ('" . $diffids . "')";
					$db->setQuery($query);

					if (!$db->execute())
					{
					}
				}
			}
		}

		// Delete order items That are removed
		if (array_key_exists("attendee_field", $orderdata))
		{
			foreach ($orderdata['attendee_field'] as $attkey => $fields)
			{
				$res           = new StdClass;
				$res->id       = '';
				$res->owner_id = Factory::getUser()->id;

				if ($fields['attendee_id'])
				{
					$attendee_id = $res->id = $fields['attendee_id'];
				}
				else
				{
					if (!$db->insertObject('#__jticketing_attendees', $res, 'id'))
					{
						echo $db->stderr();

						return false;
					}

					// Firstly create User Entry Field
					$attendee_id = $db->insertid();
				}

				$res               = new StdClass;
				$res->id           = '';
				$res->type_id      = $orderdata['all_event_data']['ticket_types']['0']->id;
				$res->ticketcount  = 1;
				$res->ticket_price = $orderdata['all_event_data']['ticket_types']['0']->price;

				if ($orderdata['order_id'])
				{
					$order_id = $res->order_id = $orderdata['order_id'];
				}
				else
				{
					$order_id = $res->order_id = $orderdata['inserted_orderid'];
				}

				$res->amount_paid = $orderdata['all_event_data']['ticket_types']['0']->deposit_fee + $fields['extra_amount'];
				$res->name        = $fields['name'];
				$res->email       = $fields['email'];

				// Insurance fees any extra fees
				$res->attribute_amount = $fields['attribute_amount'];
				$res->payment_status   = 'P';
				$res->attendee_id      = $attendee_id;

				// If order items id present update it
				if ($fields['order_items_id'])
				{
					$current_order_items[] = $fields['order_items_id'];
					$res->id               = $fields['order_items_id'];
					$insert_order_items_id = $fields['order_items_id'];

					if (!$db->updateObject('#__jticketing_order_items', $res, 'id'))
					{
					}
				}
				else
				{
					if (!$db->insertObject('#__jticketing_order_items', $res, 'id'))
					{
						echo $db->stderr();

						return false;
					}

					$insert_order_items_id = $db->insertid();
				}

				$order_items_array[] = $insert_order_items_id;

				// Save Custom user Entry Fields
				foreach ($fields as $key => $field)
				{
					$db->setQuery('SELECT id FROM `#__jticketing_attendee_fields` WHERE name LIKE  "' . $key . '"');
					$field_id = $db->loadResult();

					if ($field_id)
					{
						$row             = new stdClass;
						$row->id         = '';
						$field_id_exists = 0;
						$db->setQuery('SELECT id FROM `#__jticketing_attendee_field_values` WHERE attendee_id="' . $attendee_id . '" AND field_id=' . $field_id);
						$field_id_exists  = $db->loadResult();
						$row->field_id    = $field_id;
						$row->attendee_id = $attendee_id;
						$row->field_value = $field;

						if ($field_id_exists)
						{
							$row->id = $field_id_exists;

							if (!$db->updateObject('#__jticketing_attendee_field_values', $row, 'id'))
							{
							}
						}
						else
						{
							if (!$db->insertObject('#__jticketing_attendee_field_values', $row, 'id'))
							{
							}
						}
					}

					// Saving Ticket type fields
					if (!$field_id)
					{
						$db->setQuery('SELECT id FROM `#__jticketing_ticket_fields` WHERE name LIKE  "' . $key . '"');
						$field_id = $db->loadResult();

						if ($field_id)
						{
							$field_id_exists = 0;
							$qry             = 'SELECT id FROM `#__jticketing_ticket_field_values`
							WHERE order_items_id="' . $insert_order_items_id . '" AND field_id=' . $field_id;
							$db->setQuery($qry);
							$field_id_exists                       = $db->loadResult();
							$resdt                                 = new stdClass;
							$resdt->id                             = $field_id_exists;
							$ticket_field_ids[$tid]['field_id']    = $resdt->field_id = $field_id;
							$ticket_field_ids[$tid]['attendee_id'] = $resdt->attendee_id = $attendee_id;
							$tid++;
							$resdt->order_items_id = $insert_order_items_id;
							$resdt->field_value    = $field;

							if ($field_id_exists)
							{
								$updated_ticket_field_value_id[] = $resdt->id;

								if (!$db->updateObject('#__jticketing_ticket_field_values', $resdt, 'id'))
								{
								}
							}
							elseif ($db->insertObject('#__jticketing_ticket_field_values', $resdt, 'id'))
							{
								$updated_ticket_field_value_id[] = $db->insertid();
							}
						}
					}
				}
			}
		}

		// Delete records that are removed
		if (!empty($updated_ticket_field_value_id))
		{
			$otemsstr = implode("','", $order_items_array);
			$ids      = $diff = array();
			$sql      = "SELECT id FROM #__jticketing_ticket_field_values WHERE order_items_id IN ('" . $otemsstr . "')";
			$db->setQuery($sql);
			$ids  = $db->loadColumn();
			$diff = array_diff($ids, $updated_ticket_field_value_id);

			if (!empty($diff))
			{
				$diffids = implode("','", $diff);
				$query   = "DELETE FROM #__jticketing_ticket_field_values	WHERE id IN ('" . $diffids . "')";
				$db->setQuery($query);

				if (!$db->execute())
				{
				}
			}
		}

		// Update Total Values in main order
		if (!empty($order_id))
		{
			$sql = "SELECT sum(attribute_amount) as total_attribute_amount,sum(ticket_price) as total_ticket_price,
			sum(amount_paid) as total_amount_paid  FROM #__jticketing_order_items WHERE order_id=" . $order_id;
			$db->setQuery($sql);
			$totaldata                = $db->loadObjectlist();
			$total_original_amt       = $totaldata[0]->total_ticket_price + $totaldata[0]->total_attribute_amount;
			$total_paid_amt           = $totaldata[0]->total_amount_paid + $totaldata[0]->total_attribute_amount;
			$res_tot                  = new StdClass;
			$res_tot->id              = $order_id;
			$res_tot->original_amount = $total_original_amt;
			$res_tot->amount          = $total_paid_amt;
			$db->updateObject('#__jticketing_order', $res_tot, 'id');
		}
	}

	/**
	 * This is used to createMainOrder
	 *
	 * @param   int  $orderdata  orderdata
	 *
	 * @return  void
	 *
	 * @since   1.0
	 *
	 * @deprecated 3.2.0 The method is deprecated and will be removed in the next version.
	 */
	public function createMainOrder($orderdata)
	{
		$db  = Factory::getDbo();
		$res = $this->createOrderObject($orderdata);

		// Update order if orde_id present
		if (isset($orderdata['order_id']))
		{
			$res->id = $orderdata['order_id'];
			$db->updateObject('#__jticketing_order', $res, 'id');
			$insert_order_id = $orderdata['order_id'];
		}
		else
		{
			// Store Order to Jticketing Table
			$lang      = Factory::getLanguage();
			$extension = 'com_jticketing';
			$base_dir  = JPATH_ROOT;
			$lang->load($extension, $base_dir);
			$com_params     = ComponentHelper::getParams('com_jticketing');
			$integration    = $com_params->get('integration');
			$guest_reg_id   = $com_params->get('guest_reg_id');
			$auto_fix_seats = $com_params->get('auto_fix_seats');
			$currency       = $com_params->get('currency');
			$order_prefix   = $com_params->get('order_prefix');
			$separator      = $com_params->get('separator');
			$random_orderid = $com_params->get('random_orderid');
			$padding_count  = $com_params->get('padding_count');

			// Lets make a random char for this order take order prefix set by admin
			$order_prefix = (string) $order_prefix;

			// String length should not be more than 5
			$order_prefix = substr($order_prefix, 0, 5);

			// Take separator set by admin
			$separator     = (string) $separator;
			$res->order_id = $order_prefix . $separator;

			// Check if we have to add random number to order id
			$use_random_orderid = (int) $random_orderid;

			if ($use_random_orderid)
			{
				$random_numer = JT::utilities()->generateRandomString(5);
				$res->order_id .= $random_numer . $separator;

				// Order_id_column_field_length - prefix_length - no_of_underscores - length_of_random number
				$len = (23 - 5 - 2 - 5);
			}
			else
			{
				/* This length shud be such that it matches the column lenth of primary key
				It is used to add pading
				order_id_column_field_length - prefix_length - no_of_underscores*/
				$len = (23 - 5 - 2);
			}

			if (!$db->insertObject('#__jticketing_order', $res, 'id'))
			{
				echo $db->stderr();

				return false;
			}

			$insert_order_id = $orders_key = $sticketid = $db->insertid();
			$query = $db->getQuery(true);
			$query->select($db->quoteName(array('order_id')));
			$query->from($db->quoteName('#__jticketing_order'));
			$query->where($db->quoteName('id') . " = " . $db->quote($orders_key));
			$db->setQuery($query);
			$order_id      = (string) $db->loadResult();
			$maxlen        = 23 - strlen($order_id) - strlen($orders_key);
			$padding_count = (int) $padding_count;

			// Use padding length set by admin only if it is les than allowed(calculate) length
			if ($padding_count > $maxlen)
			{
				$padding_count = $maxlen;
			}

			if (strlen((string) $orders_key) <= $len)
			{
				$append = '';

				for ($z = 0; $z < $padding_count; $z++)
				{
					$append .= '0';
				}

				$append = $append . $orders_key;
			}

			$resd     = new stdClass;
			$resd->id = $orders_key;
			$order_id = $resd->order_id = $order_id . $append;

			if (!$db->updateObject('#__jticketing_order', $resd, 'id'))
			{
			}
		}

		return $insert_order_id;
	}

	/**
	 * This is used to createMainOrder
	 *
	 * @param   int  $orderdata  orderdata
	 *
	 * @return  void
	 *
	 * @since   1.0
	 *
	 * @deprecated 3.2.0 The method is deprecated and will be removed in the next version.
	 */
	public function createOrder($orderdata)
	{
		$db                            = Factory::getDbo();
		$insert_order_id               = $this->createMainOrder($orderdata);
		$orderdata['inserted_orderid'] = $insert_order_id;
		$this->createOrderItems($orderdata);

		return $insert_order_id;
	}

	/**
	 * This is used to get order data
	 *
	 * @param   int  $orderid  orderdata
	 *
	 * @return  void
	 *
	 * @since   1.0
	 *
	 * @deprecated 3.2.0 The method is deprecated and will be removed in the next version.
	 */
	public function getOrderData($orderid)
	{
		$db    = Factory::getDbo();
		$query = "SELECT * FROM `#__jticketing_order` WHERE `id`='" . $orderid . "'";
		$db->setQuery($query);
		$details = $db->loadObject();

		return $details;
	}

	/**
	 * This is used to get order
	 *
	 * @param   int  $orderid  orderdata
	 *
	 * @return  void
	 *
	 * @since   1.0
	 *
	 * @deprecated 3.2.0 The method is deprecated and will be removed in the next version.
	 */
	public function getOrder($orderid)
	{
		$db = Factory::getDbo();
		$db->setQuery('SELECT * FROM #__jticketing_order WHERE id=' . $orderid);
		$orderdata['orderdata'] = $db->loadObjectlist();
		$db->setQuery('SELECT * FROM #__jticketing_order_items WHERE order_id=' . $orderid);
		$orderdata['orderitems'] = $db->loadObjectlist();

		return $orderdata;
	}

	/**
	 * This is used to get order items
	 *
	 * @param   int  $orderid  orderid
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function getOrderItems($orderid)
	{
		$db = Factory::getDbo();
		$db->setQuery('SELECT * FROM #__jticketing_order_items WHERE order_id=' . $orderid);
		$orderdata = $db->loadObjectlist();

		return $orderdata;
	}

	/**
	 * This is used to get order items
	 *
	 * @param   int     $orderid    orderid
	 * @param   object  $orderdata  orderdata
	 *
	 * @return  void
	 *
	 * @since   1.0
	 *
	 * @deprecated 3.2.0 The method is deprecated and will be removed in the next version.
	 */
	public function updateOrder($orderid, $orderdata = '')
	{
		$db = Factory::getDbo();

		if (!$orderdata)
		{
			$orderdata = $this->getOrder();
		}

		$resd                          = new stdClass;
		$resd->id                      = $orderdata['orderdata']->id;
		$resd->status                  = $orderdata['orderdata']->status;
		$resd->parent_order_id         = $orderdata['orderdata']->parent_order_id;
		$resd->amount                  = $orderdata['orderdata']->amount;
		$resd->coupon_discount         = $orderdata['orderdata']->coupon_discount;
		$resd->coupon_discount_details = $orderdata['orderdata']->coupon_discount_details;

		if (!$db->updateObject('#__jticketing_order', $resd, 'id'))
		{
		}

		$this->updateOrderItems($orderdata);
	}

	/**
	 * This is used to get custom user entry fields
	 *
	 * @param   int  $params  params
	 *
	 * @return  void
	 *
	 * @since   1.0
	 *
	 * @deprecated 3.2.0 The method is deprecated and will be removed in the next version.
	 */
	public function getUserEntryField($params = '')
	{
		$db          = Factory::getDbo();
		$where_attnd = $details = $where = '';

		if (isset($params['field_id']))
		{
			$where .= " AND  atnfld_value.field_id=" . $params['field_id'];
		}

		if (isset($params['user_id']))
		{
			$where .= "  AND  attnds.owner_id=" . $params['user_id'];
		}

		if (isset($params['attendee_id']))
		{
			$where_attnd = " WHERE id=" . $params['attendee_id'];
		}

		$query = "SELECT id FROM #__jticketing_attendees AS attnds " . $where_attnd;
		$db->setQuery($query);
		$attendee_ids = $db->loadObjectlist();

		foreach ($attendee_ids AS $attendee_id)
		{
			$result = '';
			$query  = "SELECT fieldstable.name,atnfld_value.field_value,atnfld_value.field_id
			FROM #__jticketing_attendees AS attnds INNER JOIN #__jticketing_attendee_field_values AS atnfld_value
			ON attnds.id=atnfld_value.attendee_id INNER JOIN  #__jticketing_attendee_fields AS fieldstable
			ON fieldstable.id=atnfld_value.field_id WHERE field_source='com_jticketing'
			AND  attnds.id=" . $attendee_id->id . $where;
			$db->setQuery($query);
			$result = $db->loadObjectlist();

			if ($result)
			{
				$details[$attendee_id->id] = $result;
			}
		}

		return $details;
	}

	/**
	 * This is used to get custom user entry fields from tjfields
	 *
	 * @param   int  $params  params
	 *
	 * @return  ARRAY
	 *
	 * @since   1.0
	 *
	 * @deprecated 3.2.0 The method is deprecated and will be removed in the next version.
	 */
	public function getUniversalUserEntryField($params = '')
	{
		$db                 = Factory::getDbo();
		$field_manager_path = JPATH_SITE . "/components/com_tjfields/helpers/tjfields.php";

		if (file_exists($field_manager_path))
		{
			$TjfieldsHelper          = new TjfieldsHelper;
			$universalAttendeeFields = $TjfieldsHelper->getUniversalFields('com_jticketing.ticket');
			$where_attnd             = $where = '';
			$details                 = array();

			if (isset($params['field_id']))
			{
				$where .= " AND  atnfld_value.field_id=" . $params['field_id'];
			}

			if (isset($params['user_id']))
			{
				$where .= "  AND  attnds.owner_id=" . $params['user_id'];
			}

			if (isset($params['attendee_id']))
			{
				$where_attnd = " WHERE id=" . $params['attendee_id'];
			}

			$query = "SELECT id FROM #__jticketing_attendees AS attnds " . $where_attnd;
			$db->setQuery($query);
			$attendee_ids = $db->loadObjectlist();
			$result       = array();

			if ($universalAttendeeFields)
			{
				foreach ($attendee_ids AS $attendee_id)
				{
					$i = 0;

					foreach ($universalAttendeeFields AS $field)
					{
						$query = "SELECT atnfld_value.field_value FROM #__jticketing_attendees AS attnds
						INNER JOIN #__jticketing_attendee_field_values AS atnfld_value
						ON attnds.id=atnfld_value.attendee_id
						WHERE field_source='com_tjfields.com_jticketing.ticket' AND atnfld_value.field_id=" . $field->id . "
						AND  attnds.id=" . $attendee_id->id . $where;
						$db->setQuery($query);
						$resultobj = $db->loadObject();

						if (!empty($resultobj))
						{
							$result[$i]           = $resultobj;
							$result[$i]->name     = $field->name;
							$result[$i]->field_id = $field->id;
							$i++;
						}
					}

					if (!empty($result))
					{
						$details[$attendee_id->id] = $result;
					}
				}
			}

			if (!empty($details))
			{
				return $details;
			}
		}
	}

	/**
	 * This is used to get custom user entry fields from tjfields
	 *
	 * @param   int  $params  params
	 *
	 * @return  void
	 *
	 * @since   1.0
	 *
	 * @deprecated 3.2.0 The method is deprecated and will be removed in the next version.
	 */
	public function getTicketField($params = '')
	{
		$db    = Factory::getDbo();
		$query = "SELECT field_id,name,field_value FROM #__jticketing_ticket_field_values AS otems
		INNER JOIN #__jticketing_ticket_fields AS fields ON fields.id=otems.field_id
		 WHERE order_items_id=" . $params['order_items_id'];
		$db->setQuery($query);
		$result = $db->loadObjectlist();

		return $result;
	}

	/**
	 * This is used to update orderdata
	 *
	 * @param   int  $orderdata  orderdata
	 *
	 * @return  void
	 *
	 * @since   1.0
	 *
	 * @deprecated 3.2.0 The method is deprecated and will be removed in the next version.
	 */
	public function updateOrderItems($orderdata)
	{
		$db = Factory::getDbo();

		foreach ($orderdata['order_items'] as $key => $value)
		{
		}
	}

	/**
	 * This is used to get random no
	 *
	 * @param   int  $length  length
	 *
	 * @return  void
	 *
	 * @since   1.0
	 *
	 * @deprecated 3.2.0 use JT::utilities()->generateRandomString instead
	 */
	public function _random($length = 5)
	{
		$db     = Factory::getDbo();
		$salt   = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
		$len    = strlen($salt);
		$random = '';
		$stat   = @stat(__FILE__);

		if (empty($stat) || !is_array($stat))
		{
			$stat = array(
				php_uname()
			);
		}

		mt_srand(crc32(microtime() . implode('|', $stat)));

		for ($i = 0; $i < $length; $i++)
		{
			$random .= $salt[mt_rand(0, $len - 1)];
		}

		return $random;
	}

	/**
	 * This is used to get booking details
	 *
	 * @param   int  $id  order id of event
	 *
	 * @return  void
	 *
	 * @since   1.0
	 *
	 * @deprecated 3.2.0 The method is deprecated and will be removed in the next version.
	 */
	public function getbookingDetails($id)
	{
		$db    = Factory::getDbo();
		$user  = Factory::getUser();
		$query = "SELECT sum(oi.`ticket_price`+ oi.`attribute_amount`) as ticket_price ,
		event_details_id,sum(oi.`amount_paid`+oi.`attribute_amount`) as paid_amount ,oi.payment_status,t.title
		FROM `#__jticketing_order_items` as oi , `#__jticketing_order` as o, `#__jticketing_types` as t
		WHERE  oi. `order_id`= o.id AND  o.event_details_id = t.eventid
		AND oi. `order_id`='" . $id . "' AND (oi.`payment_status`='C' OR oi.`payment_status`='DP')";
		$db->setQuery($query);
		$details       = $db->loadObjectList();
		$camper_amount = $this->getCamperDetails($id);
		$data          = array(
			'order' => $details,
			'order_item' => $camper_amount
		);

		return $data;
	}

	/**
	 * This is used to get event name
	 *
	 * @param   int  $id  order id of event
	 *
	 * @return  void
	 *
	 * @since   1.0
	 *
	 * @deprecated 3.2.0 The method is deprecated and will be removed in the next version.
	 */
	public function getEventName($id)
	{
		$db    = Factory::getDbo();
		$query = "SELECT DISTINCT (e.title) FROM `#__jticketing_order_items` as oi,
		`#__jticketing_events_xref` as x,  `#__jticketing_events`as e WHERE oi. `type_id`=x.id
		and x.eventid = e.id AND oi.type_id = '" . $id . "'";
		$db->setQuery($query);
		$details = $db->loadResult();

		return $details;
	}

	/**
	 * This is used to get event name
	 *
	 * @param   int  $teid  order id of event
	 *
	 * @return  void
	 *
	 * @since   1.0
	 *
	 * @deprecated 3.2.0 The method is deprecated and will be removed in the next version.
	 */
	public function getCancellationfee($teid)
	{
		$db = Factory::getDbo();
		$q  = "SELECT DISTINCT(x.eventid) FROM `#__jticketing_order_items` as oi  ,
		`#__jticketing_events_xref` as x WHERE  oi.type_id = x.id and oi.id ='" . $teid . "'";
		$db->setQuery($q);
		$eid = $db->loadResult();
		$q   = "SELECT `field_value` FROM `#__jticketing_field_values` WHERE
		`event_id`='" . $eid . "'and `field_id`='3'";
		$db->setQuery($q);
		$details = $db->loadResult();

		return $details;
	}

	/**
	 * This is used to get event name
	 *
	 * @param   int  $id  get refund data
	 *
	 * @return  void
	 *
	 * @since   1.0
	 *
	 * @deprecated 3.2.0 The method is deprecated and will be removed in the next version.
	 */
	public function getRefundData($id)
	{
		$db    = Factory::getDbo();
		$query = "SELECT * FROM `#__jticketing_order_items` WHERE `id`='" . $id . "'";
		$db->setQuery($query);
		$details    = $db->loadObject();
		$refund_amt = $this->getCancellationfee($id);
		$data       = array(
			'order_item' => $details,
			'refund_fee' => $refund_amt
		);

		return $data;
	}

	/**
	 * This is used to get transfer fee
	 *
	 * @param   int  $teid  order id
	 *
	 * @return  void
	 *
	 * @since   1.0
	 *
	 * @deprecated 3.2.0 The method is deprecated and will be removed in the next version.
	 */
	public function getTransferfee($teid)
	{
		$db = Factory::getDbo();
		$q  = "SELECT DISTINCT(x.eventid) FROM `#__jticketing_order_items` as oi  ,
		`#__jticketing_events_xref` as x WHERE  oi.type_id = x.id and oi.id ='" . $teid . "'";
		$db->setQuery($q);
		$eid = $db->loadResult();
		$q   = "SELECT `field_value` FROM `#__jticketing_field_values` WHERE
		`event_id`='" . $eid . "'and `field_id`='2'";
		$db->setQuery($q);
		$details = $db->loadResult();

		return $details;
	}

	/**
	 * This is used to get transfer data
	 *
	 * @param   int  $id  order id
	 *
	 * @return  void
	 *
	 * @since   1.0
	 *
	 * @deprecated 3.2.0 The method is deprecated and will be removed in the next version.
	 */
	public function getTransferData($id)
	{
		$db    = Factory::getDbo();
		$query = "SELECT * FROM `#__jticketing_order_items` WHERE `id`='" . $id . "'";
		$db->setQuery($query);
		$details               = $db->loadObject();
		$refund_amt            = $this->getTransferfee($id);
		$details->price        = '';
		$details->transfer_fee = $refund_amt;
		$details               = array(
			$details
		);

		return $details;
	}

	/**
	 * This is used to check if late fee applied
	 *
	 * @param   date  $booking_end_date  order id
	 * @param   date  $event_start_date  order id
	 *
	 * @return  boolean  1 or 0
	 *
	 * @since   1.0
	 *
	 * @deprecated 3.2.0 The method is deprecated and will be removed in the next version.
	 */
	public function isLatefeeApply($booking_end_date, $event_start_date)
	{
		$db                = Factory::getDbo();
		$current_timestamp = time();
		$booking_end_date  = strtotime($booking_end_date);
		$startdate         = date('Y-m-d', strtotime($event_start_date));
		$event_startdate   = $startdate . " 23:59:59";
		$event_start_time  = strtotime($event_startdate);

		if ($current_timestamp > $booking_end_date and $current_timestamp <= $event_start_time)
		{
			return 1;
		}
		else
		{
			return 0;
		}
	}

	/**
	 * This is used to check if late fee applied
	 *
	 * @param   string  $source   com_jticketing/com_jevents
	 * @param   int     $eventid  com_jticketing/com_jevents
	 *
	 * @return  boolean  1 or 0
	 *
	 * @since   1.0
	 * @deprecated 3.2.0 Use JT::event($eventid, $source)->integrationId; instead
	 */
	public function getXreftableID($source, $eventid)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$columnArray = array('eventid','id');
		$query->select($db->quoteName($columnArray));
		$query->from($db->quoteName('#__jticketing_integration_xref'));
		$query->where($db->quoteName('source') . ' = ' . $db->quote($source));
		$query->where($db->quoteName('eventid') . ' = ' . $db->quote($eventid));
		$db->setQuery($query);
		$rows = $db->loadObject();

		return $rows;
	}

	/**
	 * Load Assets which are require for quick2cart.
	 *
	 * @return  null.
	 *
	 * @since   12.2
	 *
	 * @deprecated 3.2.0 Use JT::utilities()->loadjticketingAssetFiles(); intead
	 */
	public static function loadjticketingAssetFiles()
	{
		$qtcParams = ComponentHelper::getParams('com_jticketing');

		// Define wrapper class
		if (!defined('JTICKETING_WRAPPER_CLASS'))
		{
			$wrapperClass   = "jticketing-wrapper";
			$currentBSViews = $qtcParams->get('bootstrap_version', "bs3");

			if (version_compare(JVERSION, '3.0', 'lt'))
			{
				if ($currentBSViews == "bs3")
				{
					$wrapperClass = " jticketing-wrapper techjoomla-bootstrap ";
				}
				else
				{
					$wrapperClass = " jticketing-wrapper techjoomla-bootstrap ";
				}
			}
			else
			{
				$wrapperClass = " jticketing-wrapper ";

				if ($currentBSViews == "bs3")
				{
					$wrapperClass = " jticketing-wrapper tjBs3 ";
				}
				else
				{
					$wrapperClass = " jticketing-wrapper ";
				}
			}

			define('JTICKETING_WRAPPER_CLASS', $wrapperClass);
		}

		// Load js assets
		$tjStrapperPath = JPATH_SITE . '/media/techjoomla_strapper/tjstrapper.php';

		if (File::exists($tjStrapperPath))
		{
			require_once $tjStrapperPath;
			TjStrapper::loadTjAssets('com_jticketing');
		}

		// According to component option load boostrap3 css file and chagne the wrapper
	}

	/**
	 * This function is used to load javascripts in component
	 *
	 * @param   string  &$jsFilesArray  com_jticketing/com_jevents
	 *
	 * @return  boolean  1 or 0
	 *
	 * @since   1.0
	 *
	 * @deprecated 3.2.0 Use JT::utilities()->getJticketingJsFiles(&$jsFilesArray); instead
	 */
	public function getJticketingJsFiles(&$jsFilesArray)
	{
		$db       = Factory::getDbo();
		$input    = Factory::getApplication()->getInput();
		$option   = $input->get('option', '');
		$view     = $input->get('view', '');
		$app      = Factory::getApplication();
		$document = Factory::getDocument();

		// Load css files
		$comparams      = ComponentHelper::getParams('com_jticketing');
		$load_bootstrap = $comparams->get('load_bootstrap');

		// Load bootstrap.min.js before loading other files

		if (!$app->isClient('administrator'))
		{
			if ($load_bootstrap)
			{
				HTMLHelper::_('stylesheet', 'media/techjoomla_strapper/bs3/css/bootstrap.min.css');
			}

			// Get plugin 'relatedarticles' of type 'content'
			$plugin = PluginHelper::getPlugin('system', 'plug_sys_jticketing');

			if ($plugin)
			{
				// Get plugin params
				$pluginParams = new Registry($plugin->params);
				$load         = $pluginParams->get('loadBS3js');
			}

			if (!empty($load))
			{
				$jsFilesArray[] = 'media/techjoomla_strapper/bs3/js/bootstrap.min.js';
			}
		}

		return $jsFilesArray;
	}

	/**
	 * This is used to get venue name
	 *
	 * @param   int  $id  order id
	 *
	 * @return  Mixed
	 *
	 * @since   1.0
	 *
	 * @deprecated 3.2.0 Use JT::model('venueform')->getItem($venueId); instead
	 */
	public function getVenue($id)
	{
		return JT::model('VenueForm')->getItem($id);
	}

	/**
	 * Get global attendee fields
	 *
	 * @return  object $db
	 *
	 * @since   2.0
	 *
	 * @deprecated 3.2.0 Use JT::model('attendeecorefields')->getItems; instead
	 */
	public function getGlobalAtendeeFields()
	{
		$db = Factory::getDbo();
		$query1 = $db->getQuery(true);
		$query2 = $db->getQuery(true);
		$columnArray = array('id', 'label', 'type', 'name');
		$query2->select($columnArray);
		$query2->from($db->quoteName('#__jticketing_attendee_fields'));
		$query2->where($db->quoteName('core') . " = 1");
		$query2->where($db->quoteName('state') . " = 1");
		$query1->select($columnArray);
		$query1->from($db->quoteName('#__tjfields_fields'));
		$query1->where($db->quoteName('client') . " = " . $db->quote('com_jticketing.ticket'));
		$query1->where($db->quoteName('state') . " = 1");
		$query1->union($query2);
		$db->setQuery($query1);

		return $db->loadObjectList();
	}

	/**
	 * get ticket types of the event
	 *
	 * @param   integer  $xrefId  id for the event in integration table
	 *
	 * @return  Array   $db        ticket types ids
	 *
	 * @since  2.0
	 *
	 * @deprecated 3.2.0 Use JT::event($xrefId)->getTicketTypes(); instead
	 */
	public function getTicketTypeFields($xrefId)
	{
		$db = Factory::getDbo();

		// Create a new query object.
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from($db->quoteName('#__jticketing_types'));
		$query->where($db->quoteName('eventid') . ' = ' . $db->quote($xrefId));
		$db->setQuery($query);

		return $db->loadAssocList();
	}

	/**
	 * This is used to generate custom fields
	 *
	 * @param   int  $fieldset  be it attendee fields or ticket types
	 *
	 * @param   int  $event_id  event id in case of edit event
	 *
	 * @param   int  $source    for multiple integrations
	 *
	 * @return  $html
	 *
	 * @since   2.0
	 *
	 * @deprecated 3.2.0 Use JT::integration()->generateCustomFieldHtml($fieldtype, $event_id, $source);
	 * instead
	 */
	public function generateCustomFieldHtml($fieldset, $event_id, $source)
	{
		if ($source == "com_community")
		{
			$form_path = JPATH_ADMINISTRATOR . '/components/com_jticketing/models/forms/jomsocial/eventjs.xml';
		}
		else
		{
			$form_path = JPATH_ADMINISTRATOR . '/components/com_jticketing/models/forms/integration/eventint.xml';
		}

		$form = Form::getInstance('', $form_path);
		$ticketData = array();
		$ticketFields = array();
		$attendeeData = array();

		HTMLHelper::_('script', 'system/core.js', false, true);
		HTMLHelper::_('behavior.formvalidator');
		JLoader::register('JticketingCommonHelper', JPATH_SITE . '/components/com_jticketing/helpers/common.php');
		JticketingCommonHelper::getLanguageConstant();

		$document = Factory::getDocument();
		$document->addScriptDeclaration('var root_url = "' . Uri::root() . '"');

		if (!empty($event_id))
		{
			$xrefId                    = JT::event($event_id, $source)->integrationId;
			$jticketingTickettypeModel = JT::model('Tickettype');
			$ticketData                = $jticketingTickettypeModel->getTicketTypes($xrefId->id);
			$attendeeFieldModel        = JT::model('Attendeefields');
			$attendeeCoreFieldModel    = JT::model('Attendeecorefields');

			if (isset($xrefId->id))
			{
				$ticketData = $jticketingTickettypeModel->getTicketTypes($xrefId->id);
				$attendeeData = $attendeeCoreFieldModel->getAttendeeFields($xrefId->id);
			}

			$customFieldData = array();

			foreach ($ticketData as $key => $ticket)
			{
				$ticketFields["tickettypes" . $key] = $jticketingTickettypeModel->getItem($ticket['id']);
			}

			$customTicketFieldData[] = array("tickettypes" => $ticketFields);

			if (!empty($attendeeData))
			{
				foreach ($attendeeData as $key => $attendee)
				{
					$attendeeFields["attendeefields" . $key] = (array) $attendeeFieldModel->getItem($attendee['id']);
				}

				$customAttendeeFieldData[] = array("attendeefields" => $attendeeFields);
				$form->bind($customAttendeeFieldData);
			}

			$form->bind($customTicketFieldData);
		}

		$fieldSet = $form->getFieldset($fieldset);
		$html = array();

		foreach ($fieldSet as $field)
		{
			$html[] = $field->renderField();
		}

		return implode('', $html);
	}

	/**
	 * This is used get custom field types
	 *
	 * @param   int  $fieldType  be it attendee fields or ticket types
	 *
	 * @param   int  $event_id   event id in case of edit event
	 *
	 * @param   int  $source     for multiple integrations
	 *
	 * @return  customFields
	 *
	 * @since   2.0
	 *
	 * @deprecated 3.2.0 Use JT::event($event_id, $source)->getCustomFieldTypes($fieldtype);
	 */
	public function getCustomFieldTypes($fieldType, $event_id, $source)
	{
		if ($fieldType == "attendeeFields")
		{
			$customFields = JT::integration()->generateCustomFieldHtml('attendeefields', $event_id, $source);
		}
		else
		{
			$customFields = JT::integration()->generateCustomFieldHtml('ticket_types', $event_id, $source);
		}

		return $customFields;
	}

	/**
	 * Method to get ticket info by attendee
	 *
	 * @param   int  $userid  login user id
	 *
	 * @return  object
	 *
	 * @since   1.0
	 *
	 * @deprecated 3.2.0 Use JT::model('attendees')->getAttendees(array('owner_id' => $user->id)); instead
	 */
	public function getTicketByAttendee($userid)
	{
		$db   = Factory::getDbo();

		$query = $db->getQuery('true');
		$query->select('id, enrollment_id');
		$query->from($db->quoteName('#__jticketing_attendees'));
		$query->where($db->quoteName('owner_id') . '=' . $db->quote($userid));

		$db->setQuery($query);

		return $db->loadobjectlist();
	}
}
