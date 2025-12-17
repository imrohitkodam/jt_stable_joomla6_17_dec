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
Use Joomla\String\StringHelper;
use Joomla\CMS\Pagination\Pagination;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * Model pending paid
 *
 * @package     JTicketing
 * @subpackage  component
 * @since       1.0
 */
class TticketingModelPendingPaid extends BaseDatabaseModel
{
	public $data;

	public $total;

	public $pagination;

	/**
	 * Constructor.
	 *
	 * @see     JController
	 *
	 * @since   1.6
	 */
	public function __construct()
	{
		parent::__construct();

		$mainframe = Factory::getApplication();
		$input = $mainframe->input;

		// Get pagination request variables
		$limit = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->get('list_limit'), 'int');
		$limitstart = $input->get('limitstart', '0', 'INT');

		// In case limit has been changed, adjust it
		$limitstart = ($limit !== 0 ? (floor($limitstart / $limit) * $limit) : 0);
		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);
	}

	/**
	 * Get data for ticket sales
	 *
	 * @return  object  $this->data  payout data
	 *
	 * @since   1.0
	 */
	public function getData()
	{
		if (empty($this->data))
		{
			$query = $this->_buildQuery();
			$this->data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
		}

		$mainframe = Factory::getApplication();
		$input     = $mainframe->input;
		$option    = $input->get('option');
		$filter_type = $mainframe->getUserStateFromRequest($option . 'filter_order', 'filter_order', 'goal_amount', 'cmd');
		$filter_order_Dir = $mainframe->getUserStateFromRequest('com_jomgive.filter_order_Dir', 'filter_order_Dir', 'desc', 'word');

		if ($filter_type == 'eticketscount')
		{
			$this->data = JT::utilities()->multiDSort($this->data, $filter_type, $filter_order_Dir);
		}

		return $this->data;
	}

	/**
	 * Method _buildQuery
	 *
	 * @return  string
	 *
	 * @since   1.0
	 */
	public function _buildQuery()
	{
		$integration = JT::getIntegration(true);
		$where       = $this->_buildContentWhere();

		if ($integration == 1)
		{
			$query = "SELECT a.order_id as order_id,
			sum(order_tax)as eorder_tax,
			sum(original_amount)as eoriginal_amount,
			sum(coupon_discount)as ecoupon_discount,
			sum(amount)as eamount,sum(a.fee) as ecommission,
			sum(a.ticketscount) as eticketscount,b.id AS evid,a.*,b.title,b.thumb
			FROM #__jticketing_order AS a , #__community_events AS b,#__jticketing_integration_xref as integr
			WHERE a.event_details_id = integr.id
			AND  a.status='C' AND integr.eventid=b.id AND integr.source='com_community'
				" . $where;
		}
		elseif ($integration == 2)
		{
			$query = "SELECT  count(oitems.id) AS soldtickets,oitems.*,events.title,types.count,types.eventid FROM #__jticketing_order_items AS
			oitems INNER JOIN #__jticketing_order as ordera ON  ordera.id=oitems.order_id
			INNER JOIN #__jticketing_types  AS types ON  oitems.type_id=types.id
			INNER JOIN #__jticketing_events AS events ON  types.eventid=events.id
			WHERE oitems.payment_status IN('C','DP')" . $where . " GROUP BY ordera.event_details_id";
		}
		elseif ($integration == 3)
		{
			$query = "SELECT a.order_id as order_id,
			sum(order_tax)as eorder_tax,sum(original_amount)as eoriginal_amount,
			sum(coupon_discount)as ecoupon_discount,
			SUM( a.amount ) AS eamount,
			SUM( a.fee ) AS ecommission,
			SUM( a.ticketscount ) AS eticketscount, b.evdet_id AS evid, a.* , b.summary as title
			FROM #__jticketing_order AS a
			LEFT JOIN #__jticketing_integration_xref AS i ON a.event_details_id = i.id
			LEFT JOIN #__jevents_vevdetail AS b ON b.evdet_id = i.eventid
			WHERE a.status =  'C' AND i.eventid=b.evdet_id AND i.source='com_jevents'" . $where;
		}

		$mainframe = Factory::getApplication();
		$option    = $mainframe->getInput()->get('option');
		$db        = Factory::getDbo();
		$qry1      = '';
		$filter_order     = $mainframe->getUserStateFromRequest($option . 'filter_order', 'filter_order', 'title', 'cmd');
		$filter_order_Dir = $mainframe->getUserStateFromRequest($option . 'filter_order_Dir', 'filter_order_Dir', 'desc', 'word');

		// @TO DO Ordering for only
		if ($filter_order)
		{
			if ($filter_order == 'cdate')
			{
				$qry1 = "SHOW COLUMNS FROM #__jticketing_order";
			}
			elseif($filter_order == 'title')
			{
				switch ($integration)
				{
					case 1 :
						$qry1 = "SHOW COLUMNS FROM #__community_events";
					break;

					case 2 :
						$qry1 = "SHOW COLUMNS FROM #__jticketing_events";
					break;

					case 3 :
						$qry1 = "SHOW COLUMNS FROM #__jevents_vevdetail";
						$filter_order = 'summary';
					break;
				}
			}
			else
			{
			}

			if ($qry1)
			{
				$db->setQuery($qry1);
				$exists1 = $db->loadobjectlist();
				$allowed_fields = array();

				foreach ($exists1 as $key1 => $value1)
				{
					$allowed_fields[] = $value1->Field;
				}

				if (in_array($filter_order, $allowed_fields))
				{
					switch ($filter_order)
					{
						case 'title' :
							$query .= " ORDER BY events . $filter_order $filter_order_Dir";
						break;

						case 'cdate' :
							$query .= " ORDER BY events . $filter_order $filter_order_Dir";
						break;
					}
				}
			}
		}

		return $query;
	}

	/**
	 * Method _buildContentWhere
	 *
	 * @return string
	 *
	 * @throws Exception
	 */
	public function _buildContentWhere()
	{
		$mainframe = Factory::getApplication();
		$input     = $mainframe->input;
		$option    = $input->get('option');
		$search_event = $mainframe->getUserStateFromRequest($option . 'search_event', 'search_event', '', 'string');
		$where = "";

		if ($search_event == 0)
		{
			return '';
		}

		$eventid     = StringHelper::strtolower($search_event);
		$integration = JT::getIntegration();
		$xrefid      = JT::event($eventid, $integration)->integrationId;
		$where[]     = " AND ordera.event_details_id={$xrefid}";

		return $where1 = (count($where)? '' . implode(' AND ', $where) : '');
	}

	/**
	 * Method getTotal
	 *
	 * @return int
	 *
	 * @throws Exception
	 */
	public function getTotal()
	{
		// Lets load the content if it doesn’t already exist
		if (empty($this->total))
		{
			$query = $this->_buildQuery();
			$this->total = $this->_getListCount($query);
		}

		return $this->total;
	}

	/**
	 * Method getPagination
	 *
	 * @return Object
	 *
	 * @throws Exception
	 */
	public function getPagination()
	{
		// Lets load the content if it doesn’t already exist
		if (empty($this->pagination))
		{

			$this->_pagination = new Pagination($this->getTotal(), $this->getState('limitstart'), $this->getState('limit'));
		}

		return $this->_pagination;
	}

	/**
	 * Method getEventName
	 *
	 * @return event name
	 *
	 * @throws Exception
	 */
	public function getEventName()
	{
		$input       = Factory::getApplication()->getInput();
		$eventId     = $input->get('event', '', 'INT');
		$integration = JT::getIntegration();

		return JT::event($eventId, $integration)->getTitle();
	}

	/**
	 * Method getEventid
	 *
	 * @return Mixed
	 *
	 * @throws Exception
	 *
	 * @deprecated 3.2.0 This method will be removed in next version
	 */
	public function getEventid()
	{
		$jticketingmainhelper = new jticketingmainhelper;
		$where = '';

		$query = $jticketingmainhelper->getSalesDataAdmin('', '', $where);

		return $query;
	}

	/**
	 * Method Eventdetails
	 *
	 * @return string
	 */
	public function Eventdetails()
	{
		$mainframe = Factory::getApplication();
		$input = $mainframe->input;
		$eventid = $input->get('event', '', 'INT');

		$query = "SELECT title FROM #__community_events WHERE id = {$eventid}";
		$this->_db->setQuery($query);
		$this->data = $this->_db->loadResult();

		return $this->data;
	}

	/**
	 * Method pendingcount
	 *
	 * @param   int  $eventid  EventId
	 *
	 * @return int
	 */
	public function pendingcount($eventid)
	{
		$query = "SELECT  count(oitems.id) AS soldtickets FROM #__jticketing_order_items AS
		oitems INNER JOIN #__jticketing_order as ordera ON  ordera.id=oitems.order_id
		INNER JOIN #__jticketing_types  AS types ON  oitems.type_id=types.id
		INNER JOIN #__jticketing_events AS events ON  types.eventid=events.id
		WHERE oitems.payment_status IN('DP') AND ordera.event_details_id=" . $eventid . " GROUP BY ordera.event_details_id";
		$this->_db->setQuery($query);

		return $data = $this->_db->loadResult();
	}

	/**
	 * Method confirmcount
	 *
	 * @param   int  $eventid  EventId
	 *
	 * @return data
	 */
	public function confirmcount($eventid)
	{
		$query = "SELECT  count(oitems.id) AS soldtickets FROM #__jticketing_order_items AS
			oitems INNER JOIN #__jticketing_order as ordera ON  ordera.id=oitems.order_id
			INNER JOIN #__jticketing_types  AS types ON  oitems.type_id=types.id
			INNER JOIN #__jticketing_events AS events ON  types.eventid=events.id
			WHERE oitems.payment_status IN('C') AND ordera.event_details_id=" . $eventid . " GROUP BY ordera.event_details_id";

		$this->_db->setQuery($query);

		return $data = $this->_db->loadResult();
	}
}
