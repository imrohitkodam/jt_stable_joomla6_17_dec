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
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

JLoader::import('com_tjreports.models.reports', JPATH_SITE . '/components');

/**
 * Customer report plugin of TJReport
 *
 * @since  1.0.0
 */
class TjreportsModelCustomerreport extends TjreportsModelReports
{
	protected $default_order = 'o.user_id';

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
		$this->customFieldsQueryJoinOn = 'o.user_id';

		if (method_exists($this, 'tableExists'))
		{
			$this->customFieldsTableExists = $this->tableExists();
		}

		if (file_exists(JPATH_ADMINISTRATOR . '/components/com_jticketing/helpers/jticketing.php')) { require_once JPATH_ADMINISTRATOR . '/components/com_jticketing/helpers/jticketing.php'; }

		$path = JPATH_SITE . '/components/com_jticketing/helpers/main.php';

		if (!class_exists('Jticketingmainhelper'))
		{
			JLoader::register('Jticketingmainhelper', $path);
			JLoader::load('Jticketingmainhelper');
		}

		// Load language files
		$lang = Factory::getLanguage();
		$base_dir = JPATH_SITE . '/administrator';
		$lang->load('com_jticketing', $base_dir);
		$this->columns = array();

		$this->columns = array(
			'user_id'       => array('title' => 'PLG_TJREPORTS_CUSTOMERREPORT_CUSTOMER_ID', 'table_column' => '',),
			'name'          => array('title' => 'PLG_TJREPORTS_CUSTOMERREPORT_CUSTOMER_NAME', 'table_column' => 'u.name'),
			'username'      => array('title' => 'PLG_TJREPORTS_CUSTOMERREPORT_USERUSERNAME', 'table_column' => 'u.username'),
			'email'         => array('title' => 'PLG_TJREPORTS_CUSTOMERREPORT_USER_EMAIL', 'table_column' => 'u.email'),
			'active'        => array('title' => 'PLG_TJREPORTS_CUSTOMERREPORT_USER_BLOCKED', 'table_column' => ''),
			'usergroup'     => array('title' => 'PLG_TJREPORTS_CUSTOMERREPORT_USERGROUP', 'disable_sorting' => true),
			'enrolledUsers' => array('title' => 'PLG_TJREPORTS_CUSTOMERREPORT_TOTAL_EVENTS_ENROLLED', 'table_column' => ''),
			'attendedUsers' => array('title' => 'PLG_TJREPORTS_CUSTOMERREPORT_TOTAL_EVENTS_ATTENDEED', 'table_column' => ''),
			'lastVisitDate' => array('title' => 'PLG_TJREPORTS_CUSTOMERREPORT_USER_LAST_VISIT_DATE', 'table_column' => 'u.lastvisitDate'),
			'registerDate'  => array('title' => 'PLG_TJREPORTS_CUSTOMERREPORT_USER_REGISTRATION_DATE', 'table_column' => 'u.registerDate'),
			'likeCount'     => array('title' => 'PLG_TJREPORTS_CUSTOMERREPORT_LIKES_COUNT', 'table_column' => ''),
			'dislikeCount'  => array('title' => 'PLG_TJREPORTS_CUSTOMERREPORT_DISLIKES_COUNT', 'table_column' => ''),
			'commentsCount' => array('title' => 'PLG_TJREPORTS_CUSTOMERREPORT_COMMENTS_COUNT', 'table_column' => ''),
			'notesCount'    => array('title' => 'PLG_TJREPORTS_CUSTOMERREPORT_NOTES_COUNT', 'table_column' => '')
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

		// Load language files
		$lang = Factory::getLanguage();
		$lang->load('plg_tjreports_customerreport', JPATH_ADMINISTRATOR, 'en-GB', true);

		$reportOptions  = JticketingHelper::getReportFilterValues($this, $selected, $created_by, $myTeam);

		if (file_exists(JPATH_SITE . '/components/com_jticketing/models/user.php')) { require_once JPATH_SITE . '/components/com_jticketing/models/user.php'; }
		$userModel  = BaseDatabaseModel::getInstance('User', 'JticketingModel');
		$userFilter = $userModel->getUserFilterOptions($myTeam);
		$nameFilter = $userModel->getNameFilterOptions($myTeam);

		$activeArray = array();
		$activeArray[] = HTMLHelper::_('select.option', '', Text::_('PLG_TJREPORTS_CUSTOMERREPORT_FILTER_SELECT_EVENT_TYPE'));
		$activeArray[] = HTMLHelper::_('select.option', '0', Text::_('JYES'));
		$activeArray[] = HTMLHelper::_('select.option', '1', Text::_('JNO'));

		$dispFilters = array(
			array(
				'user_id' => array(
					'search_type' => 'text', 'type' => 'equal', 'searchin' => 'o.user_id'
				),
				'username' => array(
					'search_type' => 'select', 'select_options' => $userFilter, 'type' => 'equal', 'searchin' => 'u.id'
				),
				'name' => array(
					'search_type' => 'select', 'select_options' => $nameFilter, 'type' => 'equal', 'searchin' => 'u.id'
				),
				'email' => array(
					'search_type' => 'text', 'searchin' => 'u.email'
				),
				'active' => array(
					'search_type' => 'select', 'select_options' => $activeArray, 'type' => 'equal', 'searchin' => 'u.block'
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

		$jTicketingMainHelper = new Jticketingmainhelper;
		$integration          = JT::getIntegration(true);

		$createdByClause = $myTeamClause = false;
		$hasUsers = array();
		$user     = Factory::getUser();
		$userId   = $user->id;

		if ((int) $filters['report_filter'] === 1)
		{
			$createdByClause = true;
		}
		elseif ((int) $filters['report_filter'] === -1)
		{
			$hasUsers = JticketingHelper::getSubusers();
			$myTeamClause = true;
		}

		$query->select($db->quoteName('o.user_id', 'user_id'));
		$query->from($db->quoteName('#__jticketing_order', 'o'));

		$query->join('INNER', $db->qn('#__jticketing_integration_xref', 'i') . 'ON (' . $db->qn('o.event_details_id') . ' = ' . $db->qn('i.id') . ')');

		$query->join('INNER', $db->quoteName('#__users', 'u') . ' ON (' . $db->quoteName('o.user_id') . ' = ' . $db->quoteName('u.id') . ')');

		$eventList    = array();
		$eventIntegId = array();

		if ($createdByClause || (!$user->authorise('core.admin')))
		{
			$eventsModel = JT::model('events');
			$eventsModel->setState('filter.created_by', $userId);
			$eventList   = $eventsModel->getItems();

			if (!empty($eventList))
			{
				foreach ($eventList as $key => $event)
				{
					$eventIntegId[] = JT::event($event->id)->integrationId;
				}
			}
		}
		else
		{
			// If layout = default find all events which are created by that user
			$ordersModel = JT::model('orders', array("ignore_request" => true));
			$orders = $ordersModel->getItems();

			if (!empty($orders))
			{
				foreach ($orders as $order)
				{
					$eventIntegId[] = JT::event($order->evid)->integrationId;
				}
			}
		}

		if (!empty($eventIntegId))
		{
			$query->where($db->quoteName('o.event_details_id') . ' IN ("' . implode('","', $eventIntegId) . '")');
		}

		if ($createdByClause )
		{
			if ($integration == 1)
			{
				// Jom social
				$query->where($db->quoteName('i.source') . ' = ' . $db->quote('com_community'));

				// If loged In user is admin show all events/enrollments
				$query->where($db->quoteName('i.userid') . ' = ' . (int) $userId);
			}
			elseif ($integration == 2)
			{
				// Native
				$query->where($db->quoteName('i.source') . '=' . $db->quote('com_jticketing'));

				$query->where($db->quoteName('i.userid') . ' = ' . (int) $userId);
			}
			elseif ($integration == 3)
			{
				// Jevent
				$query->where($db->quoteName('i.source') . ' = ' . $db->quote('com_jevents'));

				$query->where($db->quoteName('i.userid') . ' = ' . (int) $userId);
			}
			elseif ($integration == 4)
			{
				// Easy social
				$query->where($db->quoteName('i.source') . '=' . $db->quote('com_easysocial'));

				// If loged In user is admin show all events/enrollments
				$query->where($db->quoteName('i.userid') . ' = ' . (int) $userId);
			}
		}

		if ($myTeamClause)
		{
			if ($hasUsers)
			{
				$query->where('o.user_id IN(' . implode(',', $hasUsers) . ')');
			}
			else
			{
				$query->where('o.user_id=0');
			}
		}

		// Integration selecting events
		if ($integration == 1)
		{
			$query->join('LEFT', $db->qn('#__community_events', 'event') . 'ON (' . $db->qn('event.id') . ' = ' . $db->qn('i.eventid') . ')');
			$query->where($db->qn('i.source') . ' = ' . $db->quote("com_community"));
		}
		elseif ($integration == 2)
		{
			$query->join('LEFT', $db->qn('#__jticketing_events', 'event') . 'ON (' . $db->qn('event.id') . ' = ' . $db->qn('i.eventid') . ')');
			$query->where($db->qn('i.source') . ' = ' . $db->quote("com_jticketing"));
		}
		elseif ($integration == 3)
		{
			$query->join('LEFT', $db->qn('#__jevents_vevdetail', 'je') . 'ON (' . $db->qn('je.evdet_id') . ' = ' . $db->qn('i.eventid') . ')');
			$query->join('INNER', $db->quoteName('#__jevents_vevent', 'jev') . ' ON (' . $db->quoteName('jev.detail_id')
				. ' = ' . $db->quoteName('je.evdet_id') . ')');
			$query->where($db->qn('i.source') . ' = ' . $db->quote("com_jevents"));
		}
		elseif ($integration == 4)
		{
			$query->join('LEFT', $db->qn('#__social_clusters', 'es') . 'ON (' . $db->qn('es.id') . ' = ' . $db->qn('i.eventid') . ')');
			$query->where($db->qn('i.source') . ' = ' . $db->quote("com_easysocial"));
		}

		if (in_array('active', $colToshow))
		{
			$query->select('IF(u.block=1,"' . Text::_('JNO') . '","' . Text::_('JYES') . '") AS active');
		}

		if (in_array('enrolledUsers', $colToshow))
		{
			$subQuery = $db->getQuery(true);
			$subQuery->select('SUM(IF(ordr.status="C",1, 0))');
			$subQuery->from('#__jticketing_order as ordr');

			if (!empty($eventIntegId))
			{
				$subQuery->where($db->quoteName('ordr.event_details_id') . ' IN ("' . implode('","', $eventIntegId) . '")');
			}

			$subQuery->where($db->quoteName('ordr.user_id') . ' = ' . $db->quoteName('o.user_id'));

			$query->select('(' . $subQuery . ') as enrolledUsers');
		}

		if (in_array('attendedUsers', $colToshow))
		{
			$query->select($db->quoteName('i.checkin', 'attendedUsers'));
		}

		if (in_array('usergroup', $colToshow))
		{
			$subQuery = $db->getQuery(true);
			$subQuery->select('ugm.group_id');
			$subQuery->from($db->quoteName('#__user_usergroup_map') . ' as ugm');
			$subQuery->where($db->quoteName('ugm.user_id') . ' = ' . $db->quoteName('o.user_id'));
			$query->select('(SELECT GROUP_CONCAT(ug.title SEPARATOR ", ") from  #__usergroups ug where ug.id IN(' . $subQuery . ')) as usergroup');

			if (isset($filters['usergroup']) && !empty($filters['usergroup']))
			{
				$subQuery = $db->getQuery(true);
				$subQuery->select('ugm.user_id');
				$subQuery->from($db->quoteName('#__user_usergroup_map') . ' as ugm');
				$subQuery->where($db->quoteName('ugm.group_id') . ' = ' . (int) $filters['usergroup']);
				$query->where('o.user_id IN(' . $subQuery . ')');
			}
		}

		if (array_intersect(array('likeCount'), $colToshow))
		{
			$subQuery = $db->getQuery(true);
			$subQuery->select('COUNT(`like`) as likeCount');
			$subQuery->from($db->quoteName('#__jlike_likes') . ' as jl');
			$subQuery->where($db->quoteName('jl.userid') . ' = ' . $db->quoteName('o.user_id'));
			$subQuery->where($db->quoteName('jl.like') . ' = ' . $db->quote(1));
			$query->select('(' . $subQuery . ') as likeCount');
		}

		if (array_intersect(array('dislikeCount'), $colToshow))
		{
			$subQuery = $db->getQuery(true);
			$subQuery->select('COUNT(`dislike`) as dislikeCount');
			$subQuery->from($db->quoteName('#__jlike_likes') . ' as jl');
			$subQuery->where($db->quoteName('jl.userid') . ' = ' . $db->quoteName('o.user_id'));
			$subQuery->where($db->quoteName('jl.dislike') . ' = ' . $db->quote(1));
			$query->select('(' . $subQuery . ') as dislikeCount');
		}

		if (array_intersect(array('commentsCount'), $colToshow))
		{
			$subQuery = $db->getQuery(true);
			$subQuery->select('COUNT(*) as commentsCount');
			$subQuery->from($db->quoteName('#__jlike_annotations') . ' as ja');
			$subQuery->where($db->quoteName('ja.user_id') . ' = ' . $db->quoteName('o.user_id'));
			$subQuery->where($db->quoteName('ja.note') . ' = ' . $db->quote(0));
			$query->select('(' . $subQuery . ') as commentsCount');
		}

		if (array_intersect(array('notesCount'), $colToshow))
		{
			$subQuery = $db->getQuery(true);
			$subQuery->select('COUNT(*) as notesCount');
			$subQuery->from($db->quoteName('#__jlike_annotations') . ' as ja');
			$subQuery->where($db->quoteName('ja.user_id') . ' = ' . $db->quoteName('o.user_id'));
			$subQuery->where($db->quoteName('ja.note') . ' = ' . $db->quote(1));
			$query->select('(' . $subQuery . ') as notesCount');
		}

		$query->group('o.user_id');

		return $query;
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

		$utilities = JT::utilities();

		foreach ($items as &$item)
		{
			if (empty($item['lastVisitDate']) || $item['lastVisitDate'] == '0000-00-00 00:00:00')
			{
				$item['lastVisitDate'] = ' - ';
			}
			else
			{
				$item['lastVisitDate'] = $utilities->getFormatedDate($item['lastVisitDate']);
			}

			if (empty($item['registerDate']) || $item['registerDate'] == '0000-00-00 00:00:00')
			{
				$item['registerDate'] = ' - ';
			}
			else
			{
				$item['registerDate'] = $utilities->getFormatedDate($item['registerDate']);
			}
		}

		return $items;
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
		$detail = array('client' => 'com_jticketing', 'title' => Text::_('PLG_TJREPORTS_CUSTOMERREPORT_TITLE_CUSTOMER_REPORT'));

		return $detail;
	}
}
