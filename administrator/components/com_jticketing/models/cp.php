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
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * Main model class
 *
 * @package     JTicketing
 * @subpackage  component
 * @since       1.0
 */
class JticketingModelcp extends BaseDatabaseModel
{
		protected $downloadid;

		protected $extensionsDetails;

	/**
	 * Constructor.
	 *
	 * @see     JController
	 * @since   1.8
	 */
	public function __construct()
	{
		// Get db object and integration
		$this->db = Factory::getDbo();
		$this->source = JT::getIntegration();

		// Get download id
		$params           = ComponentHelper::getParams('com_jticketing');
		$this->downloadid = $params->get('downloadid');

		// Setup vars
		$this->extensionsDetails = new stdClass;
		$this->extensionsDetails->extension        = 'com_jticketing';
		$this->extensionsDetails->extensionElement = 'pkg_jticketing';
		$this->extensionsDetails->extensionType    = 'package';
		$this->extensionsDetails->updateStreamName = 'JTicketing';
		$this->extensionsDetails->updateStreamType = 'extension';
		$this->extensionsDetails->updateStreamUrl  = 'https://techjoomla.com/updates/stream/jticketing.xml?format=xml';
		$this->extensionsDetails->downloadidParam  = $this->downloadid;

		parent::__construct();
		global $option;
	}

	/**
	 * Returns a box object.
	 *
	 * @param   string  $title    get title
	 * @param   string  $content  get content
	 * @param   array   $type     get type
	 *
	 * @return  string    A database object
	 */
	public function getbox($title, $content, $type = null)
	{
		$html = '
		<table cellspacing="0px" cellpadding="0px" border="0" class="tbTitle">
		<tbody>
			<tr>

				<td width="" class="tbTitleMiddle">
					<h5>' . $title . '</h5>
				</td>

			</tr>
			<tr>
				<td class="boxBody"><div >' . $content . '</div></td>
			</tr>
			<tr>

				<td width="" class="tbBottomMiddle">&nbsp;</td>
			</tr>
		</tbody>
		</table>
	';

		return $html;
	}

	/**
	 * Method for getAllOrderIncome
	 *
	 * @return  array  $result
	 *
	 * @since   1.8
	 */
	public function getAllOrderIncome()
	{
		$query = "SELECT FORMAT(SUM(amount),2) FROM #__jticketing_order WHERE status ='C'";
		$this->_db->setQuery($query);
		$result = $this->_db->loadResult();

		return $result;
	}

	/**
	 * Method for getMonthIncome
	 *
	 * @return  array  $result
	 *
	 * @since   1.8
	 */
	public function getMonthIncome()
	{
		$db = Factory::getDbo();

		// $backdate = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s').' - 30 days'));
		$curdate    = date('Y-m-d');
		$back_year  = date('Y') - 1;

		if (date('m') == 12)
		{
			$back_month = 01;
		}
		else
		{
			$back_month = date('m') + 1;
		}

		$backdate   = $back_year . '-' . $back_month . '-' . '01';
		$query = "SELECT FORMAT( SUM( amount ) , 2 ) as amount , MONTH( cdate ) AS MONTHSNAME,YEAR( cdate ) AS YEARNM FROM #__jticketing_order
		WHERE cdate >=DATE('" . $backdate . "') AND cdate <= DATE('" . $curdate . "')
		AND   status ='C' GROUP BY YEARNM,MONTHSNAME ORDER BY YEAR(cdate),MONTH( cdate ) ASC";
		$db->setQuery($query);
		$result = $db->loadObjectList();

		return $result;
	}

	/**
	 * Method for getAllmonths
	 *
	 * @return  array  $months
	 *
	 * @since   1.8
	 */
	public function getAllmonths()
	{
		$date2      = date('Y-m-d');
		$back_year  = date('Y') - 1;
		$back_month = date('m');
		$months 	= array();
		$date1      = $back_year . '-' . $back_month . '-' . '01';

		// Convert dates to UNIX timestamp
		$time1      = strtotime($date1);
		$time2      = strtotime($date2);
		$tmp        = date('mY', $time2);

		$months[] = array(
			"month" => date('F', $time1),
			"year" => date('Y', $time1)
		);

		while ($time1 < $time2)
		{
			$time1 = strtotime(date('Y-m-d', $time1) . ' +1 month');

			if (date('mY', $time1) != $tmp && ($time1 < $time2))
			{
				$months[] = array(
					"month" => date('F', $time1),
					"year" => date('Y', $time1)
				);
			}
		}

		// $months[] = array("month"    => date('F', $time2), "year"    => date('Y', $time2));
		$months[] = array(
			"month" => date('F', $time2),
			"year" => date('Y', $time2)
		);

		$months = array_reverse($months);

		return $months;
	}

	/**
	 * Method for statsforbar
	 *
	 * @return  array  $statistics
	 *
	 * @since   1.8
	 */
	public function statsforbar()
	{
		$db                   = Factory::getDbo();
		$year1                = '';
		$session              = Factory::getSession();
		$jticketing_from_date = $session->get('jticketing_from_date');
		$jticketing_end_date  = $session->get('jticketing_end_date');

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select('COUNT(ticketscount) as value,DAY(cdate) as day,MONTH(cdate) as month');

		if (!empty($year1))
		{
			$query->select('YEAR(cdate) as year');
		}

		$query->from('#__jticketing_order');

		if ($jticketing_from_date && $jticketing_end_date)
		{
			$query->where("DATE(cdate) BETWEEN DATE('" . $jticketing_from_date . "') AND DATE('" . $jticketing_end_date . "')");
		}
		else
		{
			$jtid = Factory::getApplication()->getInput()->get('jtid');
			$session->set('jticketing_jtid', $jtid);
			$statistics = array();
		}

		$query->group('DATE(cdate)');
		$query->order('DATE(cdate)');

		$db->setQuery($query);
		$statistics[] = $db->loadObjectList();

		return $statistics;
	}

	/**
	 * Method for statsforbar
	 *
	 * @return  array  $statsforpie
	 *
	 * @since   1.8
	 */
	public function statsForPie()
	{
		$db      = Factory::getDbo();
		$session = Factory::getSession();
		$jticketing_from_date = $session->get('jticketing_from_date');
		$jticketing_end_date  = $session->get('jticketing_end_date');

		$where                = '';

		if ($jticketing_from_date)
		{
			// For graph
			$where .= " AND DATE(cdate) BETWEEN DATE('" . $jticketing_from_date . "') AND DATE('" . $jticketing_end_date . "')";
		}
		else
		{
			$statsforpie = array();
		}

		// Pending orders
		$query = " SELECT COUNT(id) AS orders FROM #__jticketing_order WHERE status= 'P'" . $where;
		$db->setQuery($query);
		$statsforpie[] = $db->loadObjectList();

		// Confirmed Orders
		$query = " SELECT COUNT(id) AS orders FROM #__jticketing_order WHERE status= 'C'" . $where;
		$db->setQuery($query);
		$statsforpie[] = $db->loadObjectList();

		// Denied Orders
		$query = " SELECT COUNT(id) AS orders FROM #__jticketing_order WHERE status= 'D'" . $where;
		$db->setQuery($query);
		$statsforpie[] = $db->loadObjectList();

		// Failed Orders
		$query = " SELECT COUNT(id) AS orders FROM #__jticketing_order WHERE status= 'E'" . $where;
		$db->setQuery($query);
		$statsforpie[] = $db->loadObjectList();

		// Under Review  Orders
		$query = " SELECT COUNT(id) AS orders FROM #__jticketing_order WHERE status= 'UR'" . $where;
		$db->setQuery($query);
		$statsforpie[] = $db->loadObjectList();

		// Refunded Orders
		$query = " SELECT COUNT(id) AS orders FROM #__jticketing_order WHERE status= 'RF'" . $where;
		$db->setQuery($query);
		$statsforpie[] = $db->loadObjectList();

		// Canceled Orders
		$query = " SELECT COUNT(id) AS orders FROM #__jticketing_order WHERE status= 'CRV'" . $where;
		$db->setQuery($query);
		$statsforpie[] = $db->loadObjectList();

		// Reversed Orders
		$query = " SELECT COUNT(id) AS orders FROM #__jticketing_order WHERE status= 'RV'" . $where;
		$db->setQuery($query);
		$statsforpie[] = $db->loadObjectList();

		return $statsforpie;
	}

	/**
	 * Method for getperiodicorderscount
	 *
	 * @return  integer  $result
	 *
	 * @since   1.8
	 */
	public function getperiodicorderscount()
	{
		$session = Factory::getSession();
		$jticketing_from_date = $session->get('jticketing_from_date');
		$jticketing_end_date  = $session->get('jticketing_end_date');
		$where                = '';

		if ($jticketing_from_date)
		{
			$where = " AND DATE(cdate) BETWEEN DATE('" . $jticketing_from_date . "') AND DATE('" . $jticketing_end_date . "')";
		}
		else
		{
			$jticketing_from_date = date('Y-m-d');
			$backdate             = date('Y-m-d', strtotime(date('Y-m-d') . ' - 30 days'));
			$where                = " AND DATE(cdate) BETWEEN DATE('" . $backdate . "') AND DATE('" . $jticketing_from_date . "')";
		}

		$query = "SELECT FORMAT(SUM(amount),2) FROM #__jticketing_order WHERE status ='C' " . $where;
		$this->_db->setQuery($query);
		$result = $this->_db->loadResult();

		if (!$result)
		{
			return 0;
		}

		return $result;
	}

	/**
	 * Method for getOrdersArray
	 *
	 * @return  array  $orders
	 *
	 * @since   1.8
	 */
	public function getOrdersArray()
	{
		$db    = Factory::getDbo();
		$query = "SELECT amount,status
		FROM #__jticketing_order";
		$db->setQuery($query);
		$orders 	 = array();
		$data        = $db->loadObjectList();
		$count       = count($data);

		// Set default counts
		$orders['P'] = $orders['C'] = $orders['D'] = $orders['RF'] = $orders['UR'] = $orders['RV'] = $orders['CRV'] = $orders['F'] = 0;

		if ($data)
		{
			for ($i = 0; $i < $count; $i++)
			{
				if ($data[$i]->status == 'P')
				{
					$orders['P'] += $data[$i]->amount;
				}

				if ($data[$i]->status == 'C')
				{
					$orders['C'] += $data[$i]->amount;
				}

				if ($data[$i]->status == 'D')
				{
					$orders['D'] += $data[$i]->amount;
				}

				if ($data[$i]->status == 'RF')
				{
					$orders['RF'] += $data[$i]->amount;
				}

				if ($data[$i]->status == 'UR')
				{
					$orders['UR'] += $data[$i]->amount;
				}

				if ($data[$i]->status == 'RV')
				{
					$orders['RV'] += $data[$i]->amount;
				}

				if ($data[$i]->status == 'CRV')
				{
					$orders['CRV'] += $data[$i]->amount;
				}

				if ($data[$i]->status == 'F')
				{
					$orders['F'] += $data[$i]->amount;
				}
			}
		}

		return $orders;
	}

	/**
	 * Method for getSalesArray
	 *
	 * @return  integer  $orders
	 *
	 * @since   1.8
	 */
	public function getSalesArray()
	{
		$db    = Factory::getDbo();
		$query = "SELECT amount,status FROM #__jticketing_order";
		$db->setQuery($query);
		$data  = $db->loadObjectList();
		$count = count($data);
		$orders = 0;

		if ($data)
		{
			for ($i = 0; $i < $count; $i++)
			{
				if ($data[$i]->status == 'C')
				{
					$orders += $data[$i]->amount;
				}
			}
		}

		return $orders;
	}

	/**
	 * Method for getCommisionsArray
	 *
	 * @return  integer  $array
	 *
	 * @since   1.8
	 */
	public function getCommisionsArray()
	{
		$db    = Factory::getDbo();
		$query = "SELECT amount,status,fee
		FROM #__jticketing_order";
		$db->setQuery($query);
		$data  = $db->loadObjectList();
		$count = count($data);
		$orders = 0;

		if ($data)
		{
			for ($i = 0; $i < $count; $i++)
			{
				if ($data[$i]->status == 'C')
				{
					$orders += $data[$i]->fee;
				}
			}
		}

		return $orders;
	}

	/**
	 * Method for getTicketSalesLastweek
	 *
	 * @return  array  $msgsPerDay
	 *
	 * @since   1.8
	 */
	public function getTicketSalesLastweek()
	{
		$db         = Factory::getDbo();

		// PHP date format Y-m-d to match sql date format is 2013-05-15

		// Get dates for past 6 days
		$msgsPerDay = array();

		for ($i = 6, $k = 0; $i > 0; $i--, $k++)
		{
			$msgsPerDay[$k]       = new stdClass;
			$msgsPerDay[$k]->date = date('Y-m-d', strtotime(date('Y-m-d') . ' - ' . $i . ' days'));
		}

		// Get today's date
		$msgsPerDay[$k]       = new stdClass;
		$msgsPerDay[$k]->date = date('Y-m-d');

		// Find number of messages per day
		for ($i = 6; $i >= 0; $i--)
		{
			// Date format here is 2013-05-15
			$query = "SELECT count(ticketscount) AS count
			FROM #__jticketing_order AS cm
			WHERE status='C' AND date(mdate)='" . $msgsPerDay[$i]->date . "'";
			$db->setQuery($query);
			$count = $db->loadResult();

			if ($count)
			{
				$msgsPerDay[$i]->count = $count;
				$msgsPerDay[$i]->date = date("d/m", strtotime($msgsPerDay[$i]->date));
			}
			else
			{
				$msgsPerDay[$i]->count = 0;
				$msgsPerDay[$i]->date = date("d/m", strtotime($msgsPerDay[$i]->date));
			}
		}

		return $msgsPerDay;
	}

	/**
	 * Method for getLatestVersion
	 *
	 * @return  mixed  boolean or array
	 *
	 * @since   1.8
	 */
	public function getLatestVersion()
	{
		// Trigger plugin
		PluginHelper::importPlugin('system', 'tjupdates');
		$latestVersion = Factory::getApplication()->triggerEvent('onGetLatestVersion', array($this->extensionsDetails));

		return (isset($latestVersion[0]) ? $latestVersion[0] : false);
	}

	/**
	 * Refreshes the Joomla! update sites for this extension as needed
	 *
	 * @return  void
	 */
	public function refreshUpdateSite()
	{
		// Trigger plugin
		PluginHelper::importPlugin('system', 'tjupdates');
		Factory::getApplication()->triggerEvent('onRefreshUpdateSite', array($this->extensionsDetails));
	}

	/**
	 * Function for getting top 5 events based on the orders count
	 *
	 * @return  Object List of top 5 events data
	 *
	 * @since   1.8
	 */
	public function getTopFiveEvents()
	{
		$db = $this->getDbo();
		$query = $db->getQuery(true);
		$query->select('e.id');
		$query->select('e.title');
		$query->select('SUM(o.amount) as salesAmount');
		$query->select('COUNT(o.id) as orderCount');
		$query->from($db->quoteName('#__jticketing_events', 'e'));
		$query->join('LEFT', $db->qn('#__jticketing_integration_xref', 'i') . ' ON (' . $db->qn('i.eventid') . ' = ' . $db->qn('e.id') . ')');
		$query->join('LEFT', $db->qn('#__jticketing_order', 'o') . ' ON (' . $db->qn('o.event_details_id') . ' = ' . $db->qn('i.eventid') . ')');
		$query->where($db->quoteName('o.status') . ' = ' . $db->quote('C'));
		$query->where($db->quoteName('i.source') . ' = ' . $db->quote('com_jticketing'));
		$query->group($db->quoteName('e.id'));
		$query->order($db->quoteName('salesAmount') . 'DESC');
		$query->setLimit(5);
		$db->setQuery($query);
		$topFiveEvents = $db->loadObjectList();

		return $topFiveEvents;
	}

	/**
	 * Function for getting Dashboard Data
	 *
	 * @return  array of data
	 *
	 * @since   1.8
	 */
	public function getDashboardData()
	{
		$dashboardData = array();
		if (file_exists(JPATH_SITE . '/components/com_jticketing/helpers/main.php')) { require_once JPATH_SITE . '/components/com_jticketing/helpers/main.php'; }

		$dashboardData['totalEvents'] = $this->totalEvents();
		$dashboardData['integrationSource'] = $this->source;
		$dashboardData['ongoingEvents']  = $this->ongoingEvents();
		$dashboardData['pastEvents'] = $this->pastEvents();
		$dashboardData['upcomingEvents'] = $this->upcomingEvents();

		// Fetching Attendee count
		BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_jticketing/models', 'attendees');
		$jtickeitngModelAttendees = BaseDatabaseModel::getInstance('Attendees', 'JticketingModel');
		$attendeeRecordsCount        = $jtickeitngModelAttendees->getTotal();

		$dashboardData['totalAttendees'] = $attendeeRecordsCount;

		// Fetching all orders count
		BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_jticketing/models', 'orders');
		$jtickeitngModelOrders = BaseDatabaseModel::getInstance('orders', 'JticketingModel', array('ignore_request' => true));
		$ordersRecords         = $jtickeitngModelOrders->getItems();
		$ordersRecordCount     = $jtickeitngModelOrders->getTotal();

		$dashboardData['totalOrders'] = $ordersRecordCount;

		// Fetching commission amount
		$commissionAmount = 0;

		if ($ordersRecords)
		{
			foreach ($ordersRecords as $order)
			{
				if ($order->status == 'C')
				{
					$commissionAmount += $order->fee;
				}
			}
		}

		$dashboardData['commissionAmount'] = $commissionAmount;

		return $dashboardData;
	}

	/**
	 * Function for getting Dashboard Data
	 *
	 * @return  array of data
	 *
	 * @since   1.8
	 */
	public function totalEvents()
	{
		// Fetching No. of native publish event
		$query = $this->db->getQuery(true);
		$query->select('COUNT(i.id)');
		$query->from($this->db->quoteName('#__jticketing_integration_xref', 'i'));

		switch ($this->source)
		{
			case 'com_jticketing':
				$query->join('LEFT', $this->db->qn('#__jticketing_events', 'e') . ' ON (' . $this->db->qn('e.id') . ' = ' . $this->db->qn('i.eventid') . ')');
				$query->where($this->db->quoteName('i.source') . ' = ' . $this->db->quote($this->source));
				$query->where($this->db->quoteName('e.state') . ' = 1');
			break;

			case 'com_easysocial':
				$query->join('LEFT', $this->db->qn('#__social_clusters', 'e') . ' ON (' . $this->db->qn('e.id') . ' = ' . $this->db->qn('i.eventid') . ')');
				$query->where($this->db->quoteName('i.source') . ' = ' . $this->db->quote($this->source));
				$query->where($this->db->quoteName('e.state') . ' = 1');
			break;

			case 'com_community' :
				$query->join('LEFT', $this->db->qn('#__community_events', 'e') . ' ON (' . $this->db->qn('e.id') . ' = ' . $this->db->qn('i.eventid') . ')');
				$query->where($this->db->quoteName('i.source') . ' = ' . $this->db->quote($this->source));
				$query->where($this->db->quoteName('e.published') . ' = 1');
			break;
			case 'com_jevents' :
				$query->join('LEFT', $this->db->qn('#__jevents_vevent', 'e') . ' ON (' . $this->db->qn('e.ev_id') . ' = ' . $this->db->qn('i.eventid') . ')');
				$query->where($this->db->quoteName('i.source') . ' = ' . $this->db->quote($this->source));
			break;
		}

		$this->db->setQuery($query);

		return $this->db->loadResult();
	}

	/**
	 * Function for getting Dashboard Data
	 *
	 * @return  array of data
	 *
	 * @since   1.8
	 */
	public function ongoingEvents()
	{
		// Fetching ongoing events count for native
		$query = $this->db->getQuery(true);

		if ($this->source == 'com_jevents')
		{
			$query->select('COUNT(e.ev_id)');
		}
		else
		{
			$query->select('COUNT(e.id)');
		}

		$query->from($this->db->quoteName('#__jticketing_integration_xref', 'i'));

		switch ($this->source)
		{
			case 'com_jticketing':
				$query->join('LEFT', $this->db->qn('#__jticketing_events', 'e') . ' ON (' . $this->db->qn('e.id') . ' = ' . $this->db->qn('i.eventid') . ')');
				$query->where($this->db->quoteName('i.source') . ' = ' . $this->db->quote($this->source));
				$query->where($this->db->quoteName('e.featured') . ' = 1');
				$query->where($this->db->quoteName('e.state') . ' = 1');
			break;

			case 'com_easysocial':
				$query->join('LEFT', $this->db->qn('#__social_clusters', 'e') . ' ON (' . $this->db->qn('e.id') . ' = ' . $this->db->qn('i.eventid') . ')');
				$query->where($this->db->quoteName('i.source') . ' = ' . $this->db->quote($this->source));
				$query->where($this->db->quoteName('e.featured') . ' = 1');
				$query->where($this->db->quoteName('e.state') . ' = 1');
			break;

			case 'com_jevents':
				$query->join('LEFT', $this->db->qn('#__jevents_vevent', 'e') . ' ON (' . $this->db->qn('e.ev_id') . ' = ' . $this->db->qn('i.eventid') . ')');
				$query->join('LEFT', $this->db->quoteName('#__jevents_vevdetail', 'jevent') .
				' ON (' . $this->db->quoteName('jevent.evdet_id') . ' = ' . $this->db->quoteName('e.detail_id') . ')');
				$query->where($this->db->quoteName('jevent.state') . ' = 1');
				$query->where($this->db->quoteName('i.source') . ' = ' . $this->db->quote($this->source));
			break;

			case 'com_community':
				$query->join('LEFT', $this->db->qn('#__community_events', 'e') . ' ON (' . $this->db->qn('e.id') . ' = ' . $this->db->qn('i.eventid') . ')');
				$query->join('INNER', $this->db->qn('#__community_featured', 'f') . ' ON (' . $this->db->qn('f.cid') . ' = ' . $this->db->qn('e.id') . ')');
				$query->where($this->db->quoteName('i.source') . ' = ' . $this->db->quote($this->source));
				$query->where($this->db->quoteName('e.published') . ' = 1');
			break;
		}

		$this->db->setQuery($query);

		return $this->db->loadResult();
	}

	/**
	 * Function for getting Dashboard Data
	 *
	 * @return  array of data
	 *
	 * @since   1.8
	 */
	public function pastEvents()
	{
		$today = date("Y-m-d H:i:s");

		// Fetching past events count for native
		$query = $this->db->getQuery(true);

		if ($this->source == 'com_jevents')
		{
			$query->select('COUNT(e.ev_id)');
		}
		else
		{
			$query->select('COUNT(e.id)');
		}

		$query->from($this->db->quoteName('#__jticketing_integration_xref', 'i'));

		switch ($this->source)
		{
			case 'com_jticketing':
				$query->join('LEFT', $this->db->qn('#__jticketing_events', 'e') . ' ON (' . $this->db->qn('e.id') . ' = ' . $this->db->qn('i.eventid') . ')');
				$query->where($this->db->quoteName('i.source') . ' = ' . $this->db->quote($this->source));
				$query->where($this->db->quoteName('e.enddate') . ' < ' . $this->db->quote($today));
				$query->where($this->db->quoteName('e.state') . ' = 1');
			break;

			case 'com_easysocial':
				$query->join('LEFT', $this->db->qn('#__social_clusters', 'e') . ' ON (' . $this->db->qn('e.id') . ' = ' . $this->db->qn('i.eventid') . ')');
				$query->join('LEFT', $this->db->qn('#__social_events_meta', 's') . ' ON (' . $this->db->qn('s.cluster_id') . ' = ' . $this->db->qn('e.id') . ')');
				$query->where($this->db->quoteName('i.source') . ' = ' . $this->db->quote($this->source));
				$query->where($this->db->quoteName('s.end') . ' < ' . $this->db->quote($today));
				$query->where($this->db->quoteName('e.state') . ' = 1');

			break;

			case 'com_community':
				$query->join('LEFT', $this->db->qn('#__community_events', 'e') . ' ON (' . $this->db->qn('e.id') . ' = ' . $this->db->qn('i.eventid') . ')');
				$query->where($this->db->quoteName('i.source') . ' = ' . $this->db->quote($this->source));
				$query->where($this->db->quoteName('e.enddate') . ' < ' . $this->db->quote($today));
				$query->where($this->db->quoteName('e.published') . ' = 1');

			break;

			case 'com_jevents':
				$query->join('LEFT', $this->db->qn('#__jevents_vevent', 'e') . ' ON (' . $this->db->qn('e.ev_id') . ' = ' . $this->db->qn('i.eventid') . ')');
				$query->join('LEFT', $this->db->quoteName('#__jevents_vevdetail', 'jevent') .
				' ON (' . $this->db->quoteName('jevent.evdet_id') . ' = ' . $this->db->quoteName('e.detail_id') . ')');
				$query->where($this->db->quoteName('jevent.state') . ' = 1');
				$query->where($this->db->quoteName('jevent.dtend') . ' < ' . $this->db->quote($today));
				$query->where($this->db->quoteName('i.source') . ' = ' . $this->db->quote($this->source));
			break;
		}

		$this->db->setQuery($query);

		return $this->db->loadResult();
	}

	/**
	 * Function for getting Dashboard Data
	 *
	 * @return  array of data
	 *
	 * @since   1.8
	 */
	public function upcomingEvents()
	{
		$today = date("Y-m-d H:i:s");

		// Fetching upcoming events count for native
		$query = $this->db->getQuery(true);

		if ($this->source == 'com_jevents')
		{
			$query->select('COUNT(e.ev_id)');
		}
		else
		{
			$query->select('COUNT(e.id)');
		}

		$query->from($this->db->quoteName('#__jticketing_integration_xref', 'i'));

		switch ($this->source)
		{
			case 'com_jticketing':
				$query->join('LEFT', $this->db->qn('#__jticketing_events', 'e') . ' ON (' . $this->db->qn('e.id') . ' = ' . $this->db->qn('i.eventid') . ')');
				$query->where($this->db->quoteName('i.source') . ' = ' . $this->db->quote($this->source));
				$query->where($this->db->quoteName('e.startdate') . ' >' . $this->db->quote($today));
				$query->where($this->db->quoteName('e.state') . ' = 1');

			break;

			case 'com_easysocial':
				$query->join('LEFT', $this->db->qn('#__social_clusters', 'e') . ' ON (' . $this->db->qn('e.id') . ' = ' . $this->db->qn('i.eventid') . ')');
				$query->join('LEFT', $this->db->qn('#__social_events_meta', 's') . ' ON (' . $this->db->qn('s.cluster_id') . ' = ' . $this->db->qn('e.id') . ')');
				$query->where($this->db->quoteName('i.source') . ' = ' . $this->db->quote($this->source));
				$query->where($this->db->quoteName('s.start') . ' >= ' . $this->db->quote($today));
				$query->where($this->db->quoteName('e.state') . ' = 1');
			break;

			case 'com_community':
				$query->join('LEFT', $this->db->qn('#__community_events', 'e') . ' ON (' . $this->db->qn('e.id') . ' = ' . $this->db->qn('i.eventid') . ')');
				$query->where($this->db->quoteName('i.source') . ' = ' . $this->db->quote($this->source));
				$query->where($this->db->quoteName('e.startdate') . ' >= ' . $this->db->quote($today));
				$query->where($this->db->quoteName('e.published') . ' = 1');
			break;
			case 'com_jevents':
				$query->join('LEFT', $this->db->qn('#__jevents_vevent', 'e') . ' ON (' . $this->db->qn('e.ev_id') . ' = ' . $this->db->qn('i.eventid') . ')');
				$query->join('LEFT', $this->db->quoteName('#__jevents_vevdetail', 'jevent') .
				' ON (' . $this->db->quoteName('jevent.evdet_id') . ' = ' . $this->db->quoteName('e.detail_id') . ')');
				$query->where($this->db->quoteName('jevent.state') . ' = 1');
				$query->where($this->db->quoteName('jevent.dtstart') . ' > ' . $this->db->quote($today));
				$query->where($this->db->quoteName('i.source') . ' = ' . $this->db->quote($this->source));
			break;
		}

		$this->db->setQuery($query);

		return $this->db->loadResult();
	}

	/**
	 * Function for getting getTjHousekeepingData Data
	 *
	 * @return  array of data
	 *
	 * @since   3.1.0
	 */
	public function getTjHousekeepingData()
	{
		// To fetch data from tj_housekeeping table
		$query = $this->db->getQuery(true);
		$query->select('th.*');
		$query->from($this->db->quoteName('#__tj_houseKeeping', 'th'));
		$query->where($this->db->quoteName('th.client') . " = 'com_jticketing' AND " . $this->db->quoteName('th.version') . " =  '3.1.0' ");
		$this->db->setQuery($query);

		return $this->db->loadResult();
	}
}
