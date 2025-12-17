<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla | <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2018 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */
// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

JLoader::import('com_tjreports.models.reports', JPATH_SITE . '/components');

/**
 * Attendee report plugin of TJReport
 *
 * @since  1.0.0
 */
class TjreportsModelAttendeereport extends TjreportsModelReports
{
	protected $default_order = 'attendee.enrollment_id';

	protected $default_order_dir = 'ASC';

	public $showSearchResetButton = false;

	public $columns = null;

	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     JModelLegacy
	 * @since   1.6
	 */
	public function __construct($config = array())
	{
		// Joomla fields integration
		// Define custom fields table, alias, and table.column to join on
		$this->customFieldsTable       = '#__tjreports_com_users_user';
		$this->customFieldsTableAlias  = 'tjrcuu';
		$this->customFieldsQueryJoinOn = 'attendee.owner_id';

		if (method_exists($this, 'tableExists'))
		{
			$this->customFieldsTableExists = $this->tableExists();
		}

		if (file_exists(JPATH_ADMINISTRATOR . '/components/com_jticketing/helpers/jticketing.php')) { require_once JPATH_ADMINISTRATOR . '/components/com_jticketing/helpers/jticketing.php'; }

		$lang = Factory::getLanguage();
		$base_dir = JPATH_SITE . '/administrator';
		$lang->load('com_jticketing', $base_dir);

		$this->columns = array();

		$this->columns = array(
			'enrollment_id' => array('title' => 'PLG_TJREPORTS_ATTENDEEREPORT_ATTENDEE_ID',
										'table_column' => '', ),
			'name'        => array('title' => 'PLG_TJREPORTS_ATTENDEEREPORT_ATTENDER_NAME',
									'table_column' => '', ),
			'usergroup'   => array('title' => 'PLG_TJREPORTS_ATTENDEEREPORT_USERGROUP', 'disable_sorting' => true),
			'Email'       => array('title' => 'PLG_TJREPORTS_ATTENDEEREPORT_ATTENDER_EMAIL',
									'table_column' => 'attendee.owner_email',),
			'eventName'   => array('title' => 'EVENT_NAME', 'table_column' => '',),
			'checkintime' => array('title' => 'PLG_TJREPORTS_ATTENDEEREPORT_ATTENDER_CHECKEDINTIME', 'table_column' => '',),
			'checkin'     => array('title' => 'PLG_TJREPORTS_ATTENDEEREPORT_ATTENDER_CHECKEDIN', 'table_column' => '',),
			'status'      => array('title' => 'PLG_TJREPORTS_ATTENDEEREPORT_NOTIFY', 'table_column' => '',),
			'spent_time'  => array('title' => 'PLG_TJREPORTS_ATTENDEEREPORT_SPEND_TIME', 'table_column' => '',)
		);

		parent::__construct($config);
	}

	/**
	 * Get style for left sidebar menu
	 *
	 * @return ARRAY Keys of data
	 *
	 * @since   2.0
	 * */
	public function getStyles()
	{
		return array(
			Uri::root(true) . '/media/com_jticketing/css/jticketing.css',
		);
	}

	/**
	 * Create an array of filters
	 *
	 * @return    void
	 *
	 * @since    1.0
	 */
	public function displayFilters()
	{
		$selected   = null;
		$created_by = 0;
		$myTeam     = false;
		$reportOptions = JticketingHelper::getReportFilterValues($this, $selected, $created_by, $myTeam);

		if (file_exists(JPATH_SITE . '/components/com_jticketing/models/user.php')) { require_once JPATH_SITE . '/components/com_jticketing/models/user.php'; }
		$userModel  = BaseDatabaseModel::getInstance('User', 'JticketingModel');
		$nameFilter = $userModel->getNameFilterOptions($myTeam);

		JLoader::import('components.com_jticketing.models.events', JPATH_SITE);
		$eventsModel = BaseDatabaseModel::getInstance('Events', 'JticketingModel');
		$eventFilter = $eventsModel->getEventFilterOptions($myTeam);

		$activeArray   = array();
		$activeArray[] = HTMLHelper::_('select.option', '', Text::_('PLG_TJREPORTS_ATTENDEEREPORT_EVENTS_TYPE_FILTER'));
		$activeArray[] = HTMLHelper::_('select.option', '0', Text::_('JYES'));
		$activeArray[] = HTMLHelper::_('select.option', '1', Text::_('JNO'));

		$dispFilters = array(
			array(
				'enrollment_id' => array(
					'search_type' => 'text', 'type' => 'equal', 'searchin' => 'attendee.enrollment_id'
				),
				'name' => array(
					'search_type' => 'select', 'select_options' => $nameFilter, 'type' => 'equal', 'searchin' => 'users.id'
				),
				'eventName' => array(
					'search_type' => 'select', 'select_options' => $eventFilter, 'type' => 'equal', 'searchin' => 'attendee.event_id'
				),
				'usergroup' => array(
					'search_type' => 'select', 'select_options' => $this->getUserGroupFilter()
				)
			)
		);

		if (count($reportOptions) > 1)
		{
			$dispFilters[1] = array();
			$dispFilters[1]['report_filter'] = array(
					'search_type' => 'select', 'select_options' => $reportOptions
				);
		}

		// Joomla fields integration
		// Call parent function to set filters for custom fields
		if (method_exists(get_parent_class($this), 'setCustomFieldsDisplayFilters'))
		{
			parent::setCustomFieldsDisplayFilters($dispFilters);
		}

		return $dispFilters;
	}

	/**
	 * Method to get a JDatabaseQuery object for retrieving the data set from a database.
	 *
	 * @return  JDatabaseQuery  A JDatabaseQuery object to retrieve the data set.
	 *
	 * @since   1.6
	 */
	protected function getListQuery()
	{
		$db        = $this->_db;
		$query     = parent::getListQuery();
		$colToshow = $this->getState('colToshow');
		$filters   = $this->getState('filters');

		$user        = Factory::getUser();
		$userId      = $user->id;
		$integration = JT::getIntegration(true);

		$columns = array('attendee.id', 'attendee.event_id', 'attendee.owner_id',
			'chck.checkin', 'user.firstname', 'user.lastname');

		$query->select($db->quoteName($columns));

		$query->from($db->quoteName('#__jticketing_attendees', 'attendee'));

		$query->join('LEFT', $db->quoteName('#__jticketing_integration_xref', 'intxref')
		. ' ON (' . $db->quoteName('intxref.id') . ' = ' . $db->quoteName('attendee.event_id') . ')');

		$query->join('LEFT', $db->qn('#__jticketing_order_items', 'oitem') . 'ON (' . $db->qn('oitem.attendee_id')
			. ' = ' . $db->qn('attendee.id') . ')');

		$query->join('LEFT', $db->qn('#__jticketing_order', 'order') . 'ON (' . $db->qn('order.id')
			. ' = ' . $db->qn('oitem.order_id') . ')');

		$query->join('LEFT', $db->qn('#__jticketing_users', 'user') . 'ON (' . $db->qn('user.order_id') . ' = ' . $db->qn('order.id') . ')');

		$query->join('LEFT', $db->qn('#__jticketing_checkindetails', 'chck') . 'ON (' . $db->qn('chck.attendee_id')
			. ' = ' . $db->qn('attendee.id') . ')');

		// Integration selecting events
		if (in_array('eventName', $colToshow))
		{
			if ($integration == 1)
			{
				// Jomsocial
				$query->select($db->quoteName('events.id', 'event_id'));
				$query->select($db->quoteName('events.title', 'eventName'));
				$query->select($db->quoteName('events.startdate', 'eventStartDate'));

				$query->join('INNER', $db->quoteName('#__community_events', 'events')
			. ' ON (' . $db->quoteName('events.id') . ' = ' . $db->quoteName('intxref.eventid') . ')');
				$query->where($db->quoteName('intxref.source') . ' = ' . $db->quote('com_community'));
			}
			elseif ($integration == 2)
			{
				// Native
				$query->select($db->quoteName('events.id', 'event_id'));
				$query->select($db->quoteName('events.title', 'eventName'));
				$query->select($db->quoteName('events.startdate', 'eventStartDate'));

				$query->join('LEFT', $db->quoteName('#__jticketing_events', 'events')
			. ' ON (' . $db->quoteName('events.id') . ' = ' . $db->quoteName('intxref.eventid') . ')');
				$query->where($db->quoteName('intxref.source') . '=' . $db->quote('com_jticketing'));
			}
			elseif ($integration == 3)
			{
				// Jevent
				$query->select($db->quoteName('events.evdet_id', 'event_id'));
				$query->select($db->quoteName('events.summary', 'eventName'));

				$query->join('INNER', $db->quoteName('#__jevents_vevdetail', 'events')
			. ' ON (' . $db->quoteName('events.evdet_id') . ' = ' . $db->quoteName('intxref.eventid') . ')');

				$query->join('INNER', $db->quoteName('#__jevents_vevent', 'jev') . ' ON (' . $db->quoteName('jev.detail_id')
					. ' = ' . $db->quoteName('events.evdet_id') . ')');
				$query->where($db->quoteName('intxref.source') . ' = ' . $db->quote('com_jevents'));
			}
			elseif ($integration == 4)
			{
				// Easy social
				$query->select($db->quoteName('events.id', 'event_id'));
				$query->select($db->quoteName('events.title', 'eventName'));

				$query->join('INNER', $db->quoteName('#__social_clusters', 'events')
			. ' ON (' . $db->quoteName('events.id') . ' = ' . $db->quoteName('intxref.eventid') . ')');
				$query->where($db->quoteName('intxref.source') . '=' . $db->quote('com_easysocial'));
			}
		}

		if (in_array('enrollment_id', $colToshow))
		{
			$query->select('attendee.enrollment_id');
		}

		if (in_array('name', $colToshow))
		{
			$query->select('IF(attendee.owner_id=0,order.name,users.name) as name');
			$query->join('LEFT', $db->quoteName('#__users', 'users') . 'ON ( ' . $db->quoteName('users.id') . '=' . $db->quoteName('attendee.owner_id') . ')');
		}

		if (in_array('checkin', $colToshow))
		{
			$query->select('IF(chck.checkin=1,"' . Text::_('JYES') . '","' . Text::_('JNO') . '") as checkin');
		}

		if (in_array('checkintime', $colToshow))
		{
			$query->select('chck.checkintime');
		}

		if (in_array('spent_time', $colToshow))
		{
			$query->select('chck.spent_time');
		}

		if (in_array('status', $colToshow))
		{
			$query->select('IF(attendee.status="A","' . Text::_('PLG_TJREPORTS_ATTENDEEREPORT_STATUS_APPROVED') . '", IF(attendee.status="P", "'
				. Text::_('PLG_TJREPORTS_ATTENDEEREPORT_STATUS_PENDING') . '",
				IF(attendee.status="R","' . Text::_('PLG_TJREPORTS_ATTENDEEREPORT_STATUS_REJECTED') . '", "' . null . '"))) as status');
		}

		if (in_array('usergroup', $colToshow))
		{
			$subQuery = $db->getQuery(true);
			$subQuery->select('ugm.group_id');
			$subQuery->from($db->quoteName('#__user_usergroup_map') . ' as ugm');
			$subQuery->where($db->quoteName('ugm.user_id') . ' = ' . $db->quoteName('attendee.owner_id'));
			$query->select('(SELECT GROUP_CONCAT(ug.title SEPARATOR ", ") from  #__usergroups ug where ug.id IN(' . $subQuery . ')) as usergroup');

			if (isset($filters['usergroup']) && !empty($filters['usergroup']))
			{
				$subQuery = $db->getQuery(true);
				$subQuery->select('ugm.user_id');
				$subQuery->from($db->quoteName('#__user_usergroup_map') . ' as ugm');
				$subQuery->where($db->quoteName('ugm.group_id') . ' = ' . (int) $filters['usergroup']);
				$query->where('attendee.owner_id IN(' . $subQuery . ')');
			}
		}

		if ((int) $filters['report_filter'] === 1)
		{
			if ($integration == 1)
			{
				// Jom social
				$query->where($db->quoteName('intxref.source') . ' = ' . $db->quote('com_community'));

				// If loged In user is admin show all events/enrollments

				$query->where($db->quoteName('events.creator') . ' = ' . (int) $userId);
			}
			elseif ($integration == 2)
			{
				// Native
				$query->where($db->quoteName('intxref.source') . '=' . $db->quote('com_jticketing'));

				$query->where($db->quoteName('events.created_by') . ' = ' . (int) $userId);
			}
			elseif ($integration == 3)
			{
				// Jevent
				$query->where($db->quoteName('intxref.source') . ' = ' . $db->quote('com_jevents'));

				$query->where($db->quoteName('jev.created_by') . ' = ' . (int) $userId);
			}
			elseif ($integration == 4)
			{
				// Easy social
				$query->where($db->quoteName('intxref.source') . '=' . $db->quote('com_easysocial'));

				// If loged In user is admin show all events/enrollments

				$query->where($db->quoteName('events.creator_uid') . ' = ' . (int) $userId);
			}
		}
		elseif ((int) $filters['report_filter'] === -1)
		{
			$hasUsers = JticketingHelper::getSubusers();

			if (!empty($hasUsers))
			{
				$query->where('attendee.owner_id IN(' . implode(',', $hasUsers) . ')');
			}
			else
			{
				$query->where('attendee.owner_id=0');
			}
		}

		return $query;
	}

	/**
	 * Get client of this plugin
	 *
	 * @return array Client
	 *
	 * @since   2.0
	 * */
	public function getPluginDetail()
	{
		$detail = array('client' => 'com_jticketing', 'title' => Text::_('PLG_TJREPORTS_ATTENDEEREPORT_TITLE'));

		return $detail;
	}

	/**
	 * Method to get an array of data items.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 *
	 * @since   1.6
	 */
	public function getItems()
	{
		// Load language files
		$lang = Factory::getLanguage();
		$lang->load('plg_tjreports_attendeereport', JPATH_ADMINISTRATOR, 'en-GB', true);

		// Add additional columns which are not part of the query
		$items = parent::getItems();

		$params = JT::config('com_jticketing');
		$eventTitleShow = $params->get('enable_eventstartdateinname');

		$utilities = JT::utilities();

		foreach ($items as &$item)
		{
			if ($eventTitleShow && array_key_exists("eventStartDate", $item))
			{
				$eventStartDate = $utilities->getFormatedDate($item['eventStartDate']);
				$item['eventName'] = $item['eventName'] . ' (' . $eventStartDate . ')';
			}

			if (empty($item['checkintime']) || $item['checkintime'] == '0000-00-00 00:00:00')
			{
				$item['checkintime'] = ' - ';
			}
			else
			{
				$item['checkintime'] = $utilities->getFormatedDate($item['checkintime']);
			}

			if (empty($item['spent_time']))
			{
				$item['spent_time'] = ' - ';
			}
		}

		return $items;
	}
}
