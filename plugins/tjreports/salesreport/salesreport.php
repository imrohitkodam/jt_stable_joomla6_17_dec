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

JLoader::import('com_tjreports.models.reports', JPATH_SITE . '/components');

/**
 * Sales report plugin of TJReport
 *
 * @since  1.0.0
 */
class TjreportsModelSalesreport extends TjreportsModelReports
{
	protected $default_order = 'o.order_id';

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
			'ticket_sold'              => array('title' => 'NUMBEROFTICKETS_SOLD', 'table_column' => ''),
			'event_name'               => array('title' => 'EVENT_NAME', 'table_column' => ''),
			'event_creator'            => array('title' => 'EVENT_OWNER', 'table_column' => ''),
			'original_amt'             => array('title' => 'ORIGINAL_AMOUNT', 'table_column' => ''),
			'coupon_disc'              => array('title' => 'COM_JTICKETING_COUPON_DISCOUNT', 'table_column' => ''),
			'amt_after_disc'           => array('title' => 'COM_JTICKETING_AMOUNT_AFTER_DISCOUNT', 'table_column' => ''),
			'tax_amt'                  => array('title' => 'COM_JTICKETING_ORDER_TAX', 'table_column' => ''),
			'total_paid_amt'           => array('title' => 'COM_JTICKETING_TOTAL_PAID', 'table_column' => ''),
			'site_admin_commision'     => array('title' => 'COM_JTICKETING_COMMISSION', 'table_column' => ''),
			'amt_to_pay_to_eventowner' => array('title' => 'COM_JTICKETING_NETAMTTOPAY_EVENT', 'table_column' => '')
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

		if (file_exists(JPATH_SITE . '/components/com_jticketing/models/user.php')) { require_once JPATH_SITE . '/components/com_jticketing/models/user.php'; }
		$userModel  = BaseDatabaseModel::getInstance('User', 'JticketingModel');
		$nameFilter = $userModel->getNameFilterOptions($myTeam);

		JLoader::import('components.com_jticketing.models.events', JPATH_SITE);
		$eventsModel = BaseDatabaseModel::getInstance('Events', 'JticketingModel');
		$eventFilter = $eventsModel->getEventFilterOptions($myTeam);

		$dispFilters = array(
			array(
				'event_creator' => array(
					'search_type' => 'select', 'select_options' => $nameFilter, 'type' => 'equal', 'searchin' => 'events.created_by'
				),
				'event_name' => array(
					'search_type' => 'select', 'select_options' => $eventFilter, 'type' => 'equal', 'searchin' => 'o.event_details_id'
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
		$db              = $this->_db;
		$query           = parent::getListQuery();
		$colToshow       = $this->getState('colToshow');
		$filters         = $this->getState('filters');
		$createdByClause = $myTeamClause = false;
		$hasUsers        = array();
		$user            = Factory::getUser();
		$userId          = $user->id;
		$integration     = JT::getIntegration(true);

		if ((int) $filters['report_filter'] === 1)
		{
			$createdByClause = true;
		}
		elseif ((int) $filters['report_filter'] === -1)
		{
			$hasUsers = JticketingHelper::getSubusers();
			$myTeamClause = true;
		}

		$query->select($db->quoteName('o.event_details_id', 'event_details_id'));
		$query->from($db->quoteName('#__jticketing_order', 'o'));

		$query->join('LEFT', $db->quoteName('#__jticketing_integration_xref', 'intxref')
		. ' ON (' . $db->quoteName('intxref.id') . ' = ' . $db->quoteName('o.event_details_id') . ')');

		$query->join('LEFT', $db->qn('#__jticketing_users', 'user') . 'ON (' . $db->qn('user.order_id') . ' = ' . $db->qn('o.id') . ')');

		$query->where('o.status="C"');

		if (in_array('ticket_sold', $colToshow))
		{
			$query->select('SUM(o.ticketscount) as ticket_sold');
		}

		if (in_array('event_creator', $colToshow))
		{
			if ($integration == 1)
			{
				// Jom social
				$subQuery = $db->getQuery(true);
				$subQuery->select('c.name as creator');
				$subQuery->from($db->quoteName('#__users') . ' AS c');

				$subQuery->where($db->quoteName('c.id') . '=' . $db->quoteName('events.creator'));

				$query->select('(' . $subQuery . ') as event_creator');

				$query->join('LEFT', $db->quoteName('#__community_events', 'events') . ' ON (' . $db->quoteName('events.id')
					. ' = ' . $db->quoteName('o.event_details_id') . ')');

				$query->where($db->quoteName('intxref.source') . ' = ' . $db->quote('com_community'));
			}
			elseif ($integration == 2)
			{
				// Native
				$subQuery = $db->getQuery(true);
				$subQuery->select('c.name as creator');
				$subQuery->from($db->quoteName('#__users') . ' AS c');
				$query->join('LEFT', $db->quoteName('#__jticketing_events', 'events') . ' ON (' . $db->quoteName('events.id')
					. ' = ' . $db->quoteName('o.event_details_id') . ')');
				$subQuery->where($db->quoteName('c.id') . '=' . $db->quoteName('events.created_by'));

				$query->select('(' . $subQuery . ') as event_creator');

				$query->where($db->quoteName('intxref.source') . ' = ' . $db->quote('com_jticketing'));
			}
			elseif ($integration == 3)
			{
				// Jevent
				$subQuery = $db->getQuery(true);
				$subQuery->select('c.name as creator');
				$subQuery->from($db->quoteName('#__users') . ' AS c');
				$query->join('LEFT', $db->quoteName('#__jevents_vevdetail', 'events') . ' ON (' . $db->quoteName('events.evdet_id')
					. ' = ' . $db->quoteName('o.event_details_id') . ')');

				$query->join('INNER', $db->quoteName('#__jevents_vevent', 'jev') . ' ON (' . $db->quoteName('jev.detail_id')
				. ' = ' . $db->quoteName('events.evdet_id') . ')');

				$subQuery->where($db->quoteName('c.id') . '=' . $db->quoteName('jev.created_by'));

				$query->select('(' . $subQuery . ') as event_creator');

				$query->where($db->quoteName('intxref.source') . ' = ' . $db->quote('com_jevents'));
			}
			elseif ($integration == 4)
			{
				// Easy social

				$subQuery = $db->getQuery(true);
				$subQuery->select('c.name as creator');
				$subQuery->from($db->quoteName('#__users') . ' AS c');
				$query->join('LEFT', $db->quoteName('#__social_clusters', 'events') . ' ON (' . $db->quoteName('events.id')
					. ' = ' . $db->quoteName('o.event_details_id') . ')');
				$subQuery->where($db->quoteName('c.id') . '=' . $db->quoteName('events.creator_uid'));

				$query->select('(' . $subQuery . ') as event_creator');

				$query->where($db->quoteName('intxref.source') . ' = ' . $db->quote('com_easysocial'));
			}
		}

		if (in_array('tax_amt', $colToshow))
		{
			$query->select('SUM(o.order_tax) as tax_amt');
		}

		if (in_array('original_amt', $colToshow))
		{
			$query->select('SUM(o.original_amount) as original_amt');
		}

		// Integration selecting events
		if (in_array('event_name', $colToshow))
		{
			if ($integration == 1)
			{
				$query->select('comm.title AS event_name');
				$query->select('comm.startdate AS event_start_date');
				$query->join('LEFT', $db->qn('#__community_events', 'comm') . 'ON (' . $db->qn('comm.id') . ' = ' . $db->qn('intxref.eventid') . ')');
				$query->where($db->qn('intxref.source') . ' = ' . $db->quote("com_community"));
			}
			elseif ($integration == 2)
			{
				$query->select('event.title AS event_name');
				$query->select('event.startdate AS event_start_date');
				$query->join('LEFT', $db->qn('#__jticketing_events', 'event') . 'ON (' . $db->qn('event.id') . ' = ' . $db->qn('intxref.eventid') . ')');
				$query->where($db->qn('intxref.source') . ' = ' . $db->quote("com_jticketing"));
			}
			elseif ($integration == 3)
			{
				$query->select('je.summary AS event_name');
				$query->join('LEFT', $db->qn('#__jevents_vevdetail', 'je') . 'ON (' . $db->qn('je.evdet_id') . ' = ' . $db->qn('intxref.eventid') . ')');
				$query->where($db->qn('intxref.source') . ' = ' . $db->quote("com_jevents"));
			}
			elseif ($integration == 4)
			{
				$query->select('es.title AS event_name');
				$query->join('LEFT', $db->qn('#__social_clusters', 'es') . 'ON (' . $db->qn('es.id') . ' = ' . $db->qn('intxref.eventid') . ')');
				$query->where($db->qn('intxref.source') . ' = ' . $db->quote("com_easysocial"));
			}
		}

		if (in_array('coupon_disc', $colToshow))
		{
			$query->select('SUM(o.coupon_discount) as coupon_disc');
		}

		if (in_array('total_paid_amt', $colToshow))
		{
			$query->select('SUM(o.amount) as total_paid_amt');
		}

		if (in_array('site_admin_commision', $colToshow))
		{
			$query->select('SUM(o.fee) as site_admin_commision');
		}

		if (in_array('amt_after_disc', $colToshow))
		{
			$query->select('(SUM(o.original_amount)-SUM(o.coupon_discount))  as amt_after_disc');
		}

		if (in_array('amt_to_pay_to_eventowner', $colToshow))
		{
			$query->select('(SUM(o.amount)-SUM(o.fee)) as amt_to_pay_to_eventowner');
		}

		if ($createdByClause )
		{
			if ($integration == 1)
			{
				// Jom social
				$query->where($db->quoteName('intxref.source') . ' = ' . $db->quote('com_community'));

				// If loged In user is admin show all events/enrollments
				$query->where($db->quoteName('intxref.userid') . ' = ' . (int) $userId);
			}
			elseif ($integration == 2)
			{
				// Native
				$query->where($db->quoteName('intxref.source') . '=' . $db->quote('com_jticketing'));

				$query->where($db->quoteName('intxref.userid') . ' = ' . (int) $userId);
			}
			elseif ($integration == 3)
			{
				// Jevent
				$query->where($db->quoteName('intxref.source') . ' = ' . $db->quote('com_jevents'));

				$query->where($db->quoteName('intxref.userid') . ' = ' . (int) $userId);
			}
			elseif ($integration == 4)
			{
				// Easy social
				$query->where($db->quoteName('intxref.source') . '=' . $db->quote('com_easysocial'));

				// If loged In user is admin show all events/enrollments
				$query->where($db->quoteName('intxref.userid') . ' = ' . (int) $userId);
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

		$query->group('o.event_details_id');

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
		$detail = array('client' => 'com_jticketing', 'title' => Text::_('PLG_TJREPORTS_SALESREPORT_TITLE'));

		return $detail;
	}

	/**
	 * Method to get an array of data items.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 *
	 * @since   __DELPOY_VERSION__
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
			if ($eventTitleShow && array_key_exists("event_start_date", $item))
			{
				$eventStartDate = $utilities->getFormatedDate($item['event_start_date']);
				$item['event_name'] = $item['event_name'] . ' (' . $eventStartDate . ')';
			}

			return $items;
		}
	}
}
