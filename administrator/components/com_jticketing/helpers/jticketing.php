<?php
/**
 * @package     JTicketing
 * @subpackage  com_jticketing
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Filesystem\File;
use Joomla\CMS\HTML\HTMLHelper;
// Joomla 6: CMSObject deprecated - use stdClass or specific classes
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

// Load JTicketing bootstrap file
include_once  JPATH_SITE . '/components/com_jticketing/includes/jticketing.php';

// Load JTicketing bootstrap file
include_once  JPATH_SITE . '/components/com_jticketing/includes/jticketing.php';

/**
 * Class for showing toolbar in backend jticketing toolbar
 *
 * @package     JTicketing
 * @subpackage  component
 * @since       1.0
 */
class JticketingHelper
{
	/**
	 * function for showing toolbar in backend jticketing toolbar
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public static function addSubmenu()
	{
		// Joomla 6: JVERSION checks removed - always use modern approach
		if (false) // Legacy code path disabled for Joomla 6
		{
			$params               = JT::config();
			$integration          = $params->get('integration', '', 'INT');
			$enableWaitingList    = $params->get('enable_waiting_list', '', 'STRING');
			$enableCertification  = JT::event()->isCertificationEnabled();
			$input                = Factory::getApplication()->getInput();
			$vName                = $input->get('view', '', 'STRING');
			$client               = $input->get('client', '', 'STRING');
			$extension            = $input->get('extension', '', 'STRING');
			$client_ticket_fields = $client_event_fields = $client_ticket_groups = $client_event_groups = 0;
			$fieldsIntegration    = $params->get('fields_integrate_with', 'com_tjfields', 'STRING');

			if ($client == 'com_jticketing.ticket' && $vName == 'fields')
			{
				$client_ticket_fields = 1;
			}
			elseif ($client == 'com_jticketing.event' && $vName == 'fields')
			{
				$client_event_fields = 1;
			}
			elseif ($client == 'com_jticketing.ticket' && $vName == 'groups')
			{
				$client_ticket_groups = 1;
			}
			elseif ($client == 'com_jticketing.event' && $vName == 'groups')
			{
				$client_event_groups = 1;
			}

			// Define view paths
			$events_view                 = 'index.php?option=com_jticketing&view=events';
			$categories_view             = 'index.php?option=com_categories&view=categories&extension=com_jticketing';
			$sales_view                  = 'index.php?option=com_jticketing&view=allticketsales';
			$orders_view                 = 'index.php?option=com_jticketing&view=orders';
			$enrollments_list_view       = 'index.php?option=com_jticketing&view=attendees';
			$email_config_view           = 'index.php?option=com_jticketing&view=email_config';
			$notification_templates_view = 'index.php?option=com_tjnotifications&view=notifications&extension=com_jticketing';
			$subscriptions_view          = 'index.php?option=com_tjnotifications&view=subscriptions&extension=com_jticketing';
			$catimpexp                   = 'index.php?option=com_jticketing&view=catimpexp';
			$reminder_view               = 'index.php?option=com_jlike&view=reminders&extension=com_jticketing';
			$coupon_view                 = 'index.php?option=com_jticketing&view=coupons';
			$event_field_view            = 'index.php?option=com_tjfields&view=fields&client=com_jticketing.event';
			$event_field_group_view      = 'index.php?option=com_tjfields&view=groups&client=com_jticketing.event';
			$attendee_field_view         = 'index.php?option=com_tjfields&view=fields&client=com_jticketing.ticket';
			$attendee_field_group_view   = 'index.php?option=com_tjfields&view=groups&client=com_jticketing.ticket';
			$venues                      = 'index.php?option=com_jticketing&view=venues';
			$venues_categories           = 'index.php?option=com_categories&view=categories&extension=com_jticketing.venues';
			$vendor_view                 = 'index.php?option=com_tjvendors&view=vendors&client=com_jticketing';
			$ticket_attendee_view        = 'index.php?option=com_jticketing&view=attendeecorefields&client=com_jticketing';
			$country_view                = 'index.php?option=com_tjfields&view=countries&client=com_jticketing';
			$regions_view                = 'index.php?option=com_tjfields&view=regions&client=com_jticketing';
			$waitlist_View               = 'index.php?option=com_jticketing&view=waitinglist';
			$certificateTemplatesView    = 'index.php?option=com_tjcertificate&view=templates&extension=com_jticketing.event';
			$issuedcertificateView       = 'index.php?option=com_tjcertificate&view=certificates&extension=com_jticketing.event';
			$eventJoomlaFieldView        = 'index.php?option=com_fields&context=com_jticketing.event';
			$eventJoomlaFieldGroupView   = 'index.php?option=com_fields&view=groups&context=com_jticketing.event';

			// Joomla 6: HTMLHelperSidebar::addEntry() removed - sidebar functionality removed
			// Removed sidebar entry:Text::_('JT_CP'), 'index.php?option=com_jticketing&view=cp', $vName == 'cp');

			// Joomla 6: HTMLHelperSidebar::addEntry() removed - sidebar functionality removed
			// Removed sidebar entry: Text::_('COM_JTICKETING_TITLE_VENUES_CATS'), $venues_categories, $vName == 'categories' && $extension == 'com_jticketing.venues'

			// Joomla 6: HTMLHelperSidebar::addEntry() removed - sidebar functionality removed
			// Removed sidebar entry:Text::_('COM_JTICKETING_TITLE_VENUES'), $venues, $vName == 'venues');

			// Showing Native event and event category menus
			if ($integration == 2)
			{
				// Joomla 6: HTMLHelperSidebar::addEntry() removed - sidebar functionality removed
			// Removed sidebar entry:Text::_('COM_JTICKETING_TITLE_EVENTS'), $events_view, $vName == 'events');
				// Joomla 6: HTMLHelperSidebar::addEntry() removed - sidebar functionality removed
			// Removed sidebar entry:Text::_('COM_JTICKETING_SUBMENU_CATEGORIES'), $categories_view, $vName == 'categories' && $extension == 'com_jticketing');
				// Joomla 6: HTMLHelperSidebar::addEntry() removed - sidebar functionality removed
			// Removed sidebar entry:Text::_('COM_JTICKETING_TITLE_CATIMPORTEXPORT'), $catimpexp, $vName == 'catimpexp');
			}

			// Joomla 6: HTMLHelperSidebar::addEntry() removed - sidebar functionality removed
			// Removed sidebar entry:Text::_('COM_JTICKETING_TICKET_SALES_REPORT'), $sales_view, $vName == 'allticketsales');
			// Joomla 6: HTMLHelperSidebar::addEntry() removed - sidebar functionality removed
			// Removed sidebar entry:Text::_('COM_JTICKETING_ORDERS'), $orders_view, $vName == 'orders');
			// Joomla 6: HTMLHelperSidebar::addEntry() removed - sidebar functionality removed
			// Removed sidebar entry:Text::_('COM_JTICKETING_ATTENDEES'), $enrollments_list_view, $vName == 'attendees');

			if ($enableWaitingList != 'none')
			{
				// Joomla 6: HTMLHelperSidebar::addEntry() removed - sidebar functionality removed
			// Removed sidebar entry:Text::_('COM_JTICKETING_WAITING_LIST'), $waitlist_View, $vName == 'waitinglist');
			}

			if ($vName == 'categories')
			{
				ToolbarHelper::title('Jticketing: Categories (Events)');
			}

			// Joomla 6: HTMLHelperSidebar::addEntry() removed - sidebar functionality removed
			// Removed sidebar entry:Text::_('COM_JTICKETING_EMAIL_CONFIG'), $email_config_view, $vName == 'email_config');
			// Joomla 6: HTMLHelperSidebar::addEntry() removed - sidebar functionality removed
			// Removed sidebar entry:Text::_('COM_JTICKETING_EMAIL_TEMPLATE'), $notification_templates_view, $vName == 'notifications');

			// Joomla 6: HTMLHelperSidebar::addEntry() removed - sidebar functionality removed
			// Removed sidebar entry: Text::_('COM_JTICKETING_NOTIFICATIONS_SUBSCRIPTIONS'), $subscriptions_view, $vName == 'subscriptions'

			if ($enableCertification)
			{
				// Joomla 6: HTMLHelperSidebar::addEntry() removed - sidebar functionality removed
			// Removed sidebar entry:Text::_('COM_JTICKETING_CERTIFICATE_TEMPLATE'), $certificateTemplatesView, $vName == "templates");
				// Joomla 6: HTMLHelperSidebar::addEntry() removed - sidebar functionality removed
			// Removed sidebar entry:Text::_('COM_JTICKETING_CERTIFICATE_ISSUED'), $issuedcertificateView, $vName == "certificates");
			}

			// Joomla 6: HTMLHelperSidebar::addEntry() removed - sidebar functionality removed
			// Removed sidebar entry:Text::_('COM_JTICKETING_REMINDER_TYPES'), $reminder_view, $vName == 'reminders');
			// Joomla 6: HTMLHelperSidebar::addEntry() removed - sidebar functionality removed
			// Removed sidebar entry:Text::_('COM_JTICKETING_COUPONS'), $coupon_view, $vName == 'coupons');

			if ($fieldsIntegration == 'com_tjfields')
			{
				// Joomla 6: HTMLHelperSidebar::addEntry() removed - sidebar functionality removed
			// Removed sidebar entry:Text::_('COM_JTICKETING_EVENT_FIELD_MENU'), $event_field_view, $client_event_fields == 1);
				// Joomla 6: HTMLHelperSidebar::addEntry() removed - sidebar functionality removed
			// Removed sidebar entry:Text::_('COM_JTICKETING_EVENT_GROUP_MENU'), $event_field_group_view, $client_event_groups == 1);
			}
			elseif ($fieldsIntegration == 'com_fields')
			{
				// Joomla 6: HTMLHelperSidebar::addEntry() removed - sidebar functionality removed
			// Removed sidebar entry:Text::_('COM_JTICKETING_EVENT_FIELD_MENU'), $eventJoomlaFieldView, $vName == 'fields.fields');
				// Joomla 6: HTMLHelperSidebar::addEntry() removed - sidebar functionality removed
			// Removed sidebar entry:Text::_('COM_JTICKETING_EVENT_GROUP_MENU'), $eventJoomlaFieldGroupView, $vName == 'fields.groups');
			}

			// Joomla 6: HTMLHelperSidebar::addEntry() removed - sidebar functionality removed
			// Removed sidebar entry:Text::_('COM_JTICKETING_ATTENDEE_FIELD_MENU'), $attendee_field_view, $client_ticket_fields == 1);
			// Joomla 6: HTMLHelperSidebar::addEntry() removed - sidebar functionality removed
			// Removed sidebar entry:Text::_('COM_JTICKETING_ATTENDEE_FIELDS_GROUP'), $attendee_field_group_view, $client_ticket_groups == 1);
			// Joomla 6: HTMLHelperSidebar::addEntry() removed - sidebar functionality removed
			// Removed sidebar entry:Text::_('COM_JTICKETING_CORE_ATTENDEE_FIELDS'), $ticket_attendee_view, $vName == "attendeecorefields");

			// Event Category fields and fieldgroups
			if (ComponentHelper::isEnabled('com_fields'))
			{
				// Joomla 6: HTMLHelperSidebar::addEntry() removed - sidebar functionality removed
			// Removed sidebar entry: Text::_('COM_JTICKETING_EVENT_CATEGORIES_FIELD'), 'index.php?option=com_fields&context=com_jticketing.categories', $vName == 'fields.fields'

			// Joomla 6: HTMLHelperSidebar::addEntry() removed - sidebar functionality removed
			// Removed sidebar entry: Text::_('COM_JTICKETING_EVENT_CATEGORIES_FIELD_GROUPS'), 'index.php?option=com_fields&view=groups&context=com_jticketing.categories', $vName == 'fields.groups'
			}

				// Joomla 6: HTMLHelperSidebar::addEntry() removed - sidebar functionality removed
			// Removed sidebar entry: Text::_('COM_JTICKETING_EVENT_VENUES_FIELD'), 'index.php?option=com_fields&context=com_jticketing.venue', $vName == 'fields.fields'

				// Joomla 6: HTMLHelperSidebar::addEntry() removed - sidebar functionality removed
			// Removed sidebar entry: Text::_('COM_JTICKETING_EVENT_VENUES_FIELD_GROUPS'), 'index.php?option=com_fields&view=groups&context=com_jticketing.venue', $vName == 'fields.groups'

			// Joomla 6: HTMLHelperSidebar::addEntry() removed - sidebar functionality removed
			// Removed sidebar entry:Text::_('COM_JTICKETING_VENDORS'), $vendor_view, $vName == "vendors");
			// Joomla 6: HTMLHelperSidebar::addEntry() removed - sidebar functionality removed
			// Removed sidebar entry:Text::_('COM_JTICKETING_COUNTRIES'), $country_view, $vName == "countries");
			// Joomla 6: HTMLHelperSidebar::addEntry() removed - sidebar functionality removed
			// Removed sidebar entry:Text::_('COM_JTICKETING_REGIONS'), $regions_view, $vName == "regions");

			$enabledPlugins = PluginHelper::getPlugin('tjreports');

			if (!empty($enabledPlugins))
			{
				$report = 'index.php?option=com_tjreports&client=com_jticketing&task=reports.defaultReport';
				// Joomla 6: HTMLHelperSidebar::addEntry() removed - sidebar functionality removed
			// Removed sidebar entry:Text::_('COM_JTICKETING_TITLE_TJREPORT'), $report, $vName == 'reports');
			}
		}
	}

	/**
	 * Gets a list of the actions that can be performed.
	 *
	 * @return	JObject
	 *
	 * @since	1.6
	 */
	public static function getActions()
	{
		$user      = Factory::getUser();
		$result    = new \stdClass;
		$assetName = 'com_jticketing';
		$actions   = array(
						'core.admin',
						'core.manage',
						'core.create',
						'core.edit',
						'core.edit.own',
						'core.edit.state',
						'core.delete'
		);

		foreach ($actions as $action)
		{
			$result->$action = $user->authorise($action, $assetName);
		}

		return $result;
	}

	/** Get all jtext for javascript
	 *
	 * @return   void
	 *
	 * @since   1.0
	 */
	public static function getLanguageConstant()
	{
		$params = ComponentHelper::getParams('com_jticketing');
		$mediaSize = $params->get('jticketing_media_size', '15');

		// For venue valiation
		Text::script('COM_JTICKETING_INVALID_FIELD');
		Text::script('COM_JTICKETING_ONLINE_EVENTS_PROVIDER');
		Text::script('COM_JTICKETING_FORM_LBL_VENUE_ADDRESS');
		Text::script('COM_TJMEDIA_VALIDATE_YOUTUBE_URL');
		Text::script('JGLOBAL_CONFIRM_DELETE');
		Text::script('COM_JTICKETING_ORDER_DELETE_CONF');
		Text::script('COM_JTICKETING_FORM_LBL_VENUE_TITLE');
		Text::script('COM_JTICKETING_FORM_LBL_EVENT_DESCRIPTION');
		Text::script('COM_JTICKETING_CUSTOM_LOCATION');
		Text::script('COM_JTICKETING_FORM_LBL_EVENT_DESCRIPTION');
		Text::sprintf('COM_TJMEDIA_VALIDATE_MEDIA_SIZE', $mediaSize, 'MB', array('script' => true));
		Text::script('COM_JTICKETING_EMPTY_DESCRIPTION_ERROR');
		Text::script('COM_JTICKETING_FORM_LBL_EVENT_DATE_ERROR');
		Text::script('COM_JTICKETING_FORM_LBL_EVENT_BOOKING_DATE_ERROR');
		Text::script('COM_JTICKETING_FORM_LBL_EVENT_BOOKING_EVENT_END_ERROR');
		Text::script('COM_JTICKETING_ENTER_NUMERICS');
		Text::script('JGLOBAL_VALIDATION_FORM_FAILED');
		Text::script('COM_JTICKETING_MIN_AMT_SHOULD_GREATER_MSG');
		Text::script('COM_JTICKETING_DUPLICATE_COUPON');
		Text::script('COM_JTICKETING_DATE_START_ERROR_MSG');
		Text::script('COM_JTICKETING_DATE_END_ERROR_MSG');
		Text::script('COM_JTICKETING_DATE_ERROR_MSG');
		Text::script('COM_JTICKETING_NO_VENUE_ERROR_MSG');
		Text::script('COM_JTICKETING_NO_ONLINE_VENUE_ERROR');
		Text::script('COM_JTICKETING_PAYMENT_DETAILS_ERROR_MSG1');
		Text::script('COM_JTICKETING_PAYMENT_DETAILS_ERROR_MSG2');
		Text::script('COM_JTICKETING_VENDOR_FORM_LINK');
		Text::script('COM_JTICKETING_VALIDATE_ROUNDED_PRICE');
		Text::script('COM_JTICKETING_PRIVACY_TERMS_AND_CONDITIONS_ERROR');
		Text::script('COM_JTICKETING_FORM_EVENT_DEFAULT_VENUE_OPTION');
		Text::script('COM_JTICKETING_FORM_SELECT_EXISTING_EVENT_OPTION');
		Text::script('COM_JTICKETING_TICKET_CAPACITY_ALERT');
		Text::script('COM_JTICKETING_VENUE_CAPACITY_ERROR');
		Text::script('COM_JTICKETING_FILE_TYPE_NOT_ALLOWED');
		Text::script('COM_JTICKETING_ARE_YOU_SURE_YOU_TO_DELETE_THE_ATTENDEE');
		Text::script('COM_JTICKETING_EVENT_RELATED_AJAX_FAIL_ERROR_MESSAGE');

		// Order status messages
		Text::script('COM_JTICKETING_ORDER_STATUS_MESSAGE1');
		Text::script('COM_JTICKETING_ORDER_STATUS_REFUND');
		Text::script('COM_JTICKETING_ORDER_STATUS_FAILED');
		Text::script('COM_JTICKETING_ORDER_STATUS_DECLINE');
		Text::script('COM_JTICKETING_ORDER_STATUS_CANCEL_REVERSED');
		Text::script('COM_JTICKETING_ORDER_STATUS_REVERSED');
		Text::script('COM_JTICKETING_ORDER_STATUS_MESSAGE2');
		Text::script('COM_JTICKETING_ORDER_STATUS_CHANGED');

		// Ticket start date
		Text::script('COM_JTICKETING_BOOKING_START_DATE_WITH_EVENT_DATE_ERROR');

		// Ticket end date
		Text::script('COM_JTICKETING_TICKET_END_DATE_GREATER_BOOKING_END_DATE_ERROR');
		Text::script('COM_JTICKETING_TICKET_END_DATE_LESS_BOOKING_START_DATE_ERROR');
		Text::script('COM_JTICKETING_TICKET_END_DATE_GREATER_EVENT_END_DATE_ERROR');
		Text::script('COM_JTICKETING_TICKET_END_DATE_LESS_EVENT_MODIFICATION_DATE_ERROR');
		Text::script('COM_JTICKETING_COUPON_NO_EVENT_FOUND');
		Text::script('COM_JTICKETING_SAVE_THE_EVENT_CHANGED_DATES');

		// Seat count
		Text::script('COM_JTICKETING_INVALID_SEAT_COUNT_ERROR');
		Text::script('COM_JTICKETING_ERROR');
		Text::script('COM_JTICKETING_TICKET_ATTENDEE_EXTRA_FIELDS_DATA_ERROR');

		// coupon validation
		Text::script('COM_JTICKETING_COUPON_PERCENTAGE_ERROR');
		Text::script('COM_JTICKETING_ERROR_REPEAT_UNTIL_GREATER_THAN_STARTDATE');
		Text::script('COM_JTICKETING_FORM_LBL_REPEAT_COUNT_INVALID');
		Text::script('COM_JTICKETING_FORM_LBL_REPEAT_INTERVAL_REQUIRED');
		Text::script('COM_JTICKETING_FORM_LBL_REPEAT_INTERVAL_INVALID');
		Text::script('COM_JTICKETING_FORM_LBL_REPEAT_UNTIL_REQUIRED');
		Text::script('COM_JTICKETING_FORM_LBL_REPEAT_COUNT_REQUIRED');
		
		// Event Validation
		Text::script('COM_JTICKETING_START_NUMBER_FOR_EVENT_LEVEL_SEQUENCE');
		Text::script('COM_JTICKETING_START_NUMBER_FOR_SEQUENCE');
	}

	/**
	 * Method to get report filter values
	 *
	 * @param   object   $model        Model class object
	 * @param   INT      &$selected    First selected option
	 * @param   INT      &$created_by  Course creator id
	 * @param   Boolean  &$myTeam      Sets whether user is a manager or not
	 *
	 * @return  mixed  An array of options
	 *
	 * @since   1.6.1
	 */
	public static function getReportFilterValues($model, &$selected, &$created_by, &$myTeam)
	{
		$reportId       = $model->getState('reportId', 0);
		$user           = Factory::getUser();
		$userId         = $user->id;
		$viewAll        = $user->authorise('core.viewall', 'com_tjreports.tjreport.' . $reportId);
		$reportOptions  = self::getReportFilterOptions($viewAll, $selected);

		$filters = $model->getState('filters');

		if (empty($filters['report_filter']))
		{
			$filters['report_filter'] = $selected;
			$model->setState('filters', $filters);
		}

		$created_by			= (int) $filters['report_filter'] === 1 ? $userId : 0;
		$myTeam				= (int) $filters['report_filter'] === -1 ? true : false;

		return $reportOptions;
	}

	/**
	 * Method to get report filter options
	 *
	 * @param   Boolean  $all        Option to see all reports
	 * @param   INT      &$selected  First selected option
	 *
	 * @return  mixed  An array of options
	 *
	 * @since   1.6.1
	 */
	public static function getReportFilterOptions($all = true, &$selected = null)
	{
		$options 	= array();

		$subUsers 	= self::getSubusers();

		if (count($subUsers))
		{
			$selected = -1;
			array_unshift($options, HTMLHelper::_('select.option', $selected, Text::_('COM_JTICKETING_REPORT_MY_TEAM')));
		}

		$canDo = self::getActions();

		if ($canDo->{'core.create'})
		{
			$selected = 1;
			array_unshift($options, HTMLHelper::_('select.option', $selected, Text::_('COM_JTICKETING_REPORT_CREATED_BY_ME')));
		}

		if ($all)
		{
			$selected = 0;
			array_unshift($options, HTMLHelper::_('select.option', $selected, Text::_('COM_JTICKETING_REPORT_ALL')));
		}

		return $options;
	}

	/**
	 * Method to get sub users
	 *
	 * @param   INT  $userId  Userid whose managers to get
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 *
	 * @since   1.6.1
	 */
	public static function getSubusers($userId = null)
	{
		static $subusers = array();

		if ($userId === null)
		{
			$user 	= Factory::getUser();
			$userId	= $user->get('id');
		}

		if (!isset($subusers[$userId]))
		{
			$subusers[$userId] = array();

			if (self::isHierarchyEnabled())
			{
				// Joomla 6: JLoader removed - use require_once
				$hierarchyPath = JPATH_ADMINISTRATOR . '/components/com_hierarchy/models/hierarchy.php';
				if (file_exists($hierarchyPath))
				{
					require_once $hierarchyPath;
				}
				$hierarchyModel = BaseDatabaseModel::getInstance('Hierarchy', 'HierarchyModel');
				$subuser = $hierarchyModel->getSubUsers($userId, true);

				if (is_array($subuser))
				{
					$subusers[$userId] = $subuser;
				}
			}
		}

		return $subusers[$userId];
	}

	/**
	 * Check if hierarchy integration is enabled
	 *
	 * @return   boolean
	 *
	 * @since   1.0
	 */
	public static function isHierarchyEnabled()
	{
		static $isEnabled;

		if (!isset($isEnabled))
		{
			$isEnabled = self::onJtIsComponentEnabled('hierarchy');
		}

		return $isEnabled;
	}

	/**
	 * Check if heirarchy integration is enabled
	 *
	 * @param   STRING  $component  Component Name
	 *
	 * @return   boolean
	 *
	 * @since   1.0
	 */
	private static function onJtIsComponentEnabled($component)
	{

		$isEnabled = false;

		if (File::exists(JPATH_ROOT . '/components/com_' . $component . '/' . $component . '.php'))
		{
			if (ComponentHelper::isEnabled('com_' . $component, true))
			{
				$isEnabled = true;
			}
		}

		return $isEnabled;
	}
}
