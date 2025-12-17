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
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

JLoader::import('com_tjreports.models.reports', JPATH_SITE . '/components');

/**
 * Event report plugin of TJReport
 *
 * @since  1.0.0
 */
class TjreportsModelEventreport extends TjreportsModelReports
{
	protected $default_order = 'id';

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
		if (file_exists(JPATH_ADMINISTRATOR . '/components/com_jticketing/helpers/jticketing.php')) { require_once JPATH_ADMINISTRATOR . '/components/com_jticketing/helpers/jticketing.php'; }

		$lang = Factory::getLanguage();
		$base_dir = JPATH_SITE . '/administrator';
		$lang->load('com_jticketing', $base_dir);
		$this->columns = array();

		$this->columns = array(
			'id'               => array('title' => 'PLG_TJREPORTS_EVENTREPORT_EVENT_ID', 'table_column' => 'e.id'),
			'title'            => array('title' => 'COM_JTICKETING_EVENT_NAME', 'table_column' => 'e.title'),
			'venue'            => array('title' => 'PLG_TJREPORTS_EVENTREPORT_EVENT_VENUE', 'table_column' => ''),
			'type'             => array('title' => 'PLG_TJREPORTS_EVENTREPORT_EVENT_TYPE', 'table_column' => ''),
			'creator'          => array('title' => 'COM_JTICKETING_EVENTS_CREATOR', 'table_column' => ''),
			'enrolledUsers'    => array('title' => 'PLG_TJREPORTS_EVENTREPORT_ENROLLED_USERS_CNT', 'table_column' => ''),
			'attendedUsers'    => array('title' => 'PLG_TJREPORTS_EVENTREPORT_ATTENDED_USERS_CNT', 'table_column' => ''),
			'access'           => array('title' => 'PLG_TJREPORTS_EVENTREPORT_REPORT_USERGROUP', 'table_column' => 'vl.title'),
			'cat_title'        => array('title' => 'COM_JTICKETING_CATEGORY', 'table_column' => 'cat.title'),
			'startdate'        => array('title' => 'COM_JTICKETING_START_DATE', 'table_column' => 'e.startdate'),
			'enddate'          => array('title' => 'COM_JTICKETING_END_DATE', 'table_column' => 'e.enddate'),
			'booking_start_date' => array('title' => 'COM_JTICKETING_BOOKING_START_DATE', 'table_column' => 'e.booking_start_date'),
			'booking_end_date' => array('title' => 'COM_JTICKETING_BOOKING_END_DATE', 'table_column' => 'e.booking_end_date'),
			'status'           => array('title' => 'PLG_TJREPORTS_EVENTREPORT_EVENT_STATUS', 'table_column' => ''),
			'likeCount'        => array('title' => 'PLG_TJREPORTS_EVENTREPORT_LIKES_COUNT', 'table_column' => ''),
			'dislikeCount'     => array('title' => 'PLG_TJREPORTS_EVENTREPORT_DISLIKES_COUNT', 'table_column' => ''),
			'commentsCount'    => array('title' => 'PLG_TJREPORTS_EVENTREPORT_COMMENTS_COUNT', 'table_column' => ''),
			'featured'         => array('title' => 'COM_JTICKETING_EVENTS_FEATURED', 'table_column' => 'e.featured'),
			'created'          => array('title' => 'COM_JT_CREATED', 'table_column' => 'e.created')
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

		$reportOptions  = JticketingHelper::getReportFilterValues($this, $selected, $created_by, $myTeam);

		JLoader::import('components.com_jticketing.models.events', JPATH_SITE);
		$eventsModel = BaseDatabaseModel::getInstance('Events', 'JticketingModel');
		$catFilter = $eventsModel->getCatFilterOptions();

		JLoader::import('components.com_jticketing.models.events', JPATH_SITE);
		$eventsModel = BaseDatabaseModel::getInstance('Events', 'JticketingModel');
		$eventFilter = $eventsModel->getEventFilterOptions();

		JLoader::import('components.com_jticketing.models.venues', JPATH_SITE);
		$venuesModel = BaseDatabaseModel::getInstance('Venues', 'JticketingModel');
		$venueFilter = $venuesModel->getVenueFilterOptions();

		$accesslevelFilter = HTMLHelper::_('access.assetgroups');
		array_unshift($accesslevelFilter, HTMLHelper::_('select.option', '', Text::_('JOPTION_ACCESS_SHOW_ALL_LEVELS')));

		$typeArray = array();
		$typeArray[] = HTMLHelper::_('select.option', '', Text::_('PLG_TJREPORTS_EVENTREPORT_EVENTS_TYPE_FILTER'));
		$typeArray[] = HTMLHelper::_('select.option', '0', Text::_('COM_JTICKETING_SELECT_OFFLINE'));
		$typeArray[] = HTMLHelper::_('select.option', '1', Text::_('COM_JTICKETING_SELECT_ONLINE'));

		$statusArray = array();
		$statusArray[] = HTMLHelper::_('select.option', '', Text::_('COM_JTICKETING_EVENTS_PUBLISHED'));
		$statusArray[] = HTMLHelper::_('select.option', '1', Text::_('COM_JTICKETING_PUBLISHED'));
		$statusArray[] = HTMLHelper::_('select.option', '0', Text::_('COM_JTICKETING_UNPUBLISHED'));

		$dispFilters = array(
			array(
				'id' => array(
					'search_type' => 'text', 'type' => 'equal', 'searchin' => 'e.id'
				),
				'title' => array(
					'search_type' => 'select', 'select_options' => $eventFilter, 'type' => 'equal', 'searchin' => 'e.id'
				),
				'cat_title' => array(
					'search_type' => 'select', 'select_options' => $catFilter, 'type' => 'equal', 'searchin' => 'cat.id'
				),
				'type' => array(
					'search_type' => 'select', 'select_options' => $typeArray, 'type' => 'equal', 'searchin' => 'e.online_events'
				),
				'status' => array(
					'search_type' => 'select', 'select_options' => $statusArray, 'type' => 'equal', 'searchin' => 'e.state'
				),
				'access' => array(
					'search_type' => 'select', 'select_options' => $accesslevelFilter, 'type' => 'equal', 'searchin' => 'e.access'
				),
				'venue' => array(
					'search_type' => 'select', 'select_options' => $venueFilter, 'type' => 'equal', 'searchin' => 'e.venue'
				)
			),
		);

		if (count($reportOptions) > 1)
		{
			$dispFilters[1] = array();
			$dispFilters[1]['report_filter'] = array(
					'search_type' => 'select', 'select_options' => $reportOptions
				);
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
		$filters   = $this->getState('filters');
		$createdByClause = $myTeamClause = false;
		$hasUsers = array();
		$user     = Factory::getUser();
		$userId   = $user->id;

		if (isset($filters['report_filter']) && !empty($filters['report_filter']))
		{
			if ((int) $filters['report_filter'] === 1)
			{
				$createdByClause = true;
			}
			elseif ((int) $filters['report_filter'] === -1)
			{
				$hasUsers = JticketingHelper::getSubusers();
				$myTeamClause = true;
			}
		}

		$colToshow = (array) $this->getState('colToshow');

		$query->select(array('e.id'));
		$query->from($db->quoteName('#__jticketing_events', 'e'));
		$query->join('LEFT', $db->quoteName('#__categories', 'cat') . ' ON (' . $db->quoteName('e.catid') . ' = ' . $db->quoteName('cat.id') . ')');
		$query->join('INNER', $db->quoteName('#__jticketing_integration_xref', 'i') . ' ON (' .
								$db->quoteName('i.eventid') . ' = ' . $db->quoteName('e.id') . ')');
		$query->where($db->quoteName('i.source') . '=' . $db->quote('com_jticketing'));

		if (in_array('access', $colToshow))
		{
			$query->join('LEFT', $db->quoteName('#__viewlevels', 'vl') . ' ON (' . $db->quoteName('vl.id') . ' = ' . $db->quoteName('e.access') . ')');
		}

		if (in_array('type', $colToshow))
		{
			$query->select('IF(e.online_events=1,"' . Text::_('COM_JTICKETING_SELECT_ONLINE') . '","' . Text::_('COM_JTICKETING_SELECT_OFFLINE')
							. '") AS type');
		}

		if (in_array('status', $colToshow))
		{
			$query->select('IF(e.state=1,"' . Text::_('COM_JTICKETING_PUBLISHED') . '","' . Text::_('COM_JTICKETING_UNPUBLISHED')
							. '") AS status');
		}

		if (in_array('creator', $colToshow))
		{
			$subQuery = $db->getQuery(true);
			$subQuery->select('c.name as creator');
			$subQuery->from($db->quoteName('#__users') . ' AS c');
			$subQuery->where($db->quoteName('c.id') . '=' . $db->quoteName('e.created_by'));

			$query->select('(' . $subQuery . ') as creator');
		}

		if (in_array('venue', $colToshow))
		{
			$query->select('IF(e.venue=0,e.location,v.name) as venue');
			$query->join('LEFT', $db->quoteName('#__jticketing_venues', 'v') . 'ON ( ' . $db->quoteName('v.id') . '=' . $db->quoteName('e.venue') . ')');
		}

		if (in_array('enrolledUsers', $colToshow))
		{
			$subQuery = $db->getQuery(true);
			$subQuery->select('COUNT(attendee.id)');
			$subQuery->from($db->quoteName('#__jticketing_attendees') . 'as attendee');
			$subQuery->where($db->quoteName('attendee.event_id') . " = " . $db->quoteName('i.id'));
			$subQuery->where($db->quoteName('attendee.status') . ' = "A"');

			$query->select('(' . $subQuery . ') as enrolledUsers');
		}

		if (in_array('attendedUsers', $colToshow))
		{
			$query->select($db->quoteName('i.checkin', 'attendedUsers'));
		}

		if (array_intersect(array('likeCount', 'dislikeCount'), $colToshow))
		{
			$query->select(array('like_cnt as likeCount', 'dislike_cnt as dislikeCount'));
			$query->join('LEFT', $db->quoteName('#__jlike_content', 'jc') . ' ON (jc.element_id = e.id)');
		}

		if (in_array('commentsCount', $colToshow))
		{
			$subQuery = $db->getQuery(true);
			$subQuery->select('COUNT(jac.id)');
			$subQuery->from($db->quoteName('#__jlike_content') . ' as jcc');
			$subQuery->join('LEFT', '#__jlike_annotations AS jac ON jcc.id = jac.content_id');
			$subQuery->where(array("jcc.element = 'com_jticketing.event'", 'jcc.element_id = e.id', 'jac.note=0'));

			$query->select('(' . $subQuery . ') as commentsCount');
		}

		if ($createdByClause )
		{
			$query->where('e.created_by = ' . (int) $userId);
		}

		if ($myTeamClause)
		{
			if ($hasUsers)
			{
				$query->where('ordr.user_id IN(' . implode(',', $hasUsers) . ')');
			}
			else
			{
				$query->where('ordr.user_id=0');
			}
		}

		$query->group('e.id');

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
		$detail = array('client' => 'com_jticketing', 'title' => Text::_('PLG_TJREPORTS_EVENTREPORT_TITLE'));

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
		// Add additional columns which are not part of the query
		$items = parent::getItems();

		$params = JT::config('com_jticketing');
		$eventTitleShow = $params->get('enable_eventstartdateinname');

		$utilities = JT::utilities();

		foreach ($items as &$item)
		{
			if ($eventTitleShow)
			{
				$eventStartDate = $utilities->getFormatedDate($item['startdate']);
				$item['title'] = $item['title'] . ' (' . $eventStartDate . ')';
			}

			if (empty($item['startdate']) || $item['startdate'] == '0000-00-00 00:00:00')
			{
				$item['startdate'] = ' - ';
			}
			else
			{
				$item['startdate'] = $utilities->getFormatedDate($item['startdate']);
			}

			if (empty($item['enddate']) || $item['enddate'] == '0000-00-00 00:00:00')
			{
				$item['enddate'] = ' - ';
			}
			else
			{
				$item['enddate'] = $utilities->getFormatedDate($item['enddate']);
			}

			if ($item['booking_start_date'] == '0000-00-00 00:00:00')
			{
				$item['booking_start_date'] = $utilities->getFormatedDate($item['created']);
			}
			elseif (empty($item['booking_start_date']))
			{
				$item['booking_start_date'] = ' - ';
			}
			else
			{
				$item['booking_start_date'] = $utilities->getFormatedDate($item['booking_start_date']);
			}

			if ($item['booking_end_date'] == '0000-00-00 00:00:00')
			{
				$item['booking_end_date'] = $item['enddate'];
			}
			elseif (empty($item['booking_end_date']))
			{
				$item['booking_end_date'] = ' - ';
			}
			else
			{
				$item['booking_end_date'] = $utilities->getFormatedDate($item['booking_end_date']);
			}

			if (empty($item['created']) || $item['created'] == '0000-00-00 00:00:00')
			{
				$item['created'] = ' - ';
			}
			else
			{
				$item['created'] = $utilities->getFormatedDate($item['created']);
			}
		}

		return $items;
	}
}
