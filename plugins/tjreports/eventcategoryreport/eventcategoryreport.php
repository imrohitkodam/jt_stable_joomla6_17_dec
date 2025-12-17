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
use Joomla\CMS\Categories\Categories;
use Joomla\CMS\Language\Text;

JLoader::import('com_tjreports.models.reports', JPATH_SITE . '/components');

/**
 * Category report plugin of TJReport
 *
 * @since  1.0.0
 */
class TjreportsModelEventcategoryreport extends TjreportsModelReports
{
	protected $default_order = 'cat_title';

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
		JLoader::register('JticketingHelper', JPATH_ADMINISTRATOR . '/components/com_jticketing/helpers/jticketing.php');

		$lang = Factory::getLanguage();
		$base_dir = JPATH_SITE . '/administrator';
		$lang->load('com_jticketing', $base_dir);
		$this->columns = array();

		$this->columns = array(
			'cat_title'       => array('title' => 'COM_JTICKETING_CATEGORY', 'table_column' => 'cat.title'),
			'total_events'    => array('title' => 'PLG_TJREPORTS_CATEGORYREPORT_REPORT_TOTAL_EVENTS', 'table_column' => ''),
			'enrolled_users' => array('title' => 'PLG_TJREPORTS_CATEGORYREPORT_REPORT_TOTAL_EVENTS_ENROLLED', 'table_column' => ''),
			'attended_users' => array('title' => 'PLG_TJREPORTS_CATEGORYREPORT_REPORT_TOTAL_EVENTS_ATTENDEED', 'table_column' => ''),
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
	 * @return    array<integer,array<string,array<string,mixed|string>>>
	 *
	 * @since    1.0
	 */
	public function displayFilters()
	{
		$selected   = null;
		$created_by = 0;
		$myTeam     = false;

		$reportOptions = JticketingHelper::getReportFilterValues($this, $selected, $created_by, $myTeam);
		$eventsModel   = JT::model('Events');
		$catFilter     = $eventsModel->getCatFilterOptions();

		$dispFilters = array(
			array(
				'cat_title' => array(
					'search_type' => 'select', 'select_options' => $catFilter, 'type' => 'custom'
				)
			),
		);

		$filters = $this->getState('filters');

		if (isset($filters['cat_title']) && !empty($filters['cat_title']))
		{
			(int) $filters['cat_title'];

			$categories = Categories::getInstance('Jticketing');

			$cat = $categories->get((int) $filters['cat_title']);

			$childCat   = array();
			$childCat[] = $filters['cat_title'];

			if ($cat)
			{
				$children = $cat->getChildren();

				foreach ($children as $child)
				{
					$childCat[] = $child->id;
				}
			}

			$dispFilters[0]['cat_title']['searchin'] = 'cat.id IN (' . implode(",", $childCat) . ')';
		}

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
		$colToshow = (array) $this->getState('colToshow');

		$filters   = $this->getState('filters');
		$user      = Factory::getUser();
		$userId    = $user->id;

		// Must have columns to get details of non linked data like completion
		$query->select('u.id as user_id, cat.id');
		$query->from('`#__users` AS u');
		$query->join('INNER', '`#__jticketing_events` AS e ON u.id = e.created_by');
		$query->join('INNER', '`#__categories` AS cat ON e.catid = cat.id');
		$query->where('u.block=0');
		$query->where('e.state=1');

		if (in_array('total_events', $colToshow))
		{
			$query->select('COUNT(e.id) as total_events');
		}

		if (in_array('enrolled_users', $colToshow))
		{
			$subQuery = $db->getQuery(true);
			$subQuery->select('SUM(IF(atte.status="A",1, 0))');
			$subQuery->from('#__jticketing_attendees as atte');
			$subQuery->join('INNER', '`#__jticketing_integration_xref` as xref ON xref.id = atte.event_id');
			$subQuery->join('INNER', '`#__jticketing_events` as event ON event.id = xref.eventid');
			$subQuery->where($db->quoteName('xref.source') . ' = ' . $db->quote('com_jticketing'));
			$subQuery->where($db->quoteName('event.catid') . ' = ' . $db->quoteName('e.catid'));

			$query->select('(' . $subQuery->__toString() . ') enrolled_users ');
		}

		if (in_array('attended_users', $colToshow))
		{
			$subQuery = $db->getQuery(true);
			$subQuery->select('SUM(xref.checkin)');
			$subQuery->from('#__jticketing_integration_xref AS xref');
			$subQuery->join('INNER', '`#__jticketing_events` as event ON event.id = xref.eventid');
			$subQuery->where($db->quoteName('xref.source') . ' = ' . $db->quote('com_jticketing'));
			$subQuery->where($db->quoteName('event.catid') . ' = ' . $db->quoteName('e.catid'));

			$query->select('(' . $subQuery->__toString() . ') attended_users ');
		}

		if ((int) $filters['report_filter'] === 1)
		{
			$query->where('e.created_by = ' . (int) $userId);
		}
		elseif ((int) $filters['report_filter'] === -1)
		{
			$hasUsers = JticketingHelper::getSubusers();

			if ($hasUsers)
			{
				$query->where('u.id IN(' . implode(',', $hasUsers) . ')');
			}
			else
			{
				$query->where('u.id=0');
			}
		}

		$query->group($db->quoteName('cat.id'));

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
		$detail = array('client' => 'com_jticketing', 'title' => Text::_('PLG_TJREPORTS_CATEGORYREPORT_TITLE'));

		return $detail;
	}
}
