<?php
/**
 * @package     Jticketing
 * @subpackage  com_jticketing
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 -2010 Techjoomla, Tekdi Web Solutions . All rights reserved.
 * @license     GNU GPLv2 <http://www.gnu.org/licenses/old-licenses/gpl-2.0.html>
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Pagination\Pagination;
use Joomla\CMS\MVC\Model\ListModel;
Use Joomla\String\StringHelper;

/**
 * All ticket sales class.
 *
 * @since  2.0
 */
class JticketingModelallticketsales extends ListModel
{
	/**
	 * Constructor.
	 *
	 * @since   2.0
	 */
	public function __construct()
	{
		parent::__construct();
		global $mainframe, $option;
		$input     = Factory::getApplication()->getInput();
		$mainframe = Factory::getApplication();
		$option    = $input->get('option');

		$search_event = $mainframe->getUserStateFromRequest($option . 'search_event', 'search_event', '', 'string');
		$this->setState('search_event', $search_event);

		// Get pagination request variables
		$limit     = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->get('list_limit'), 'int');
		$limitstart = $input->get('limitstart', '0', 'INT');

		// In case limit has been changed, adjust it
		$limitstart = ($limit !== 0 ? (floor($limitstart / $limit) * $limit) : 0);
		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);
	}

	/**
	 * Method getData
	 *
	 * @return  array
	 *
	 * @since   2.0
	 */
	public function getData()
	{
		if (empty($this->_data))
		{
			$query = $this->_buildQuery();
			$this->_data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
		}

		global $mainframe, $option;
		$mainframe = Factory::getApplication();
		$filter_order_Dir = '';
		$filter_type = '';
		$filter_type = $mainframe->getUserStateFromRequest($option . 'filter_order', 'filter_order', 'goal_amount', 'cmd');
		$filter_order_Dir = $mainframe->getUserStateFromRequest('com_jomgive.filter_order_Dir', 'filter_order_Dir', 'desc', 'word');

		if ($filter_type == 'eticketscount')
		{
			$this->_data = JT::utilities()->multiDSort($this->_data, $filter_type, $filter_order_Dir);
		}

		return $this->_data;
	}

	/**
	 * Method _buildQuery
	 *
	 * @return  array
	 *
	 * @since   2.0
	 */
	public function _buildQuery()
	{
		global $mainframe, $option;
		$input     = Factory::getApplication()->getInput();
		$mainframe = Factory::getApplication();
		$db        = Factory::getDbo();
		$jticketingmainhelper = new jticketingmainhelper;
		$integration = JT::getIntegration(true);

		$where = $this->_buildContentWhere();
		$query = $this->getSalesDataAdmin($where);
		$query->group($db->quoteName('a.event_details_id'));

		$filter_order     = '';
		$filter_order_Dir = '';
		$qry1             = '';
		$filter_order     = $mainframe->getUserStateFromRequest($option . 'filter_order', 'filter_order', 'title', 'cmd');
		$filter_order_Dir = $mainframe->getUserStateFromRequest($option . 'filter_order_Dir', 'filter_order_Dir', 'desc', 'word');

		// @TODO Ordering for only
		if ($filter_order)
		{
			$table = '';

			if ($filter_order == 'cdate')
			{
				$table = '#__jticketing_order';
			}
			elseif($filter_order == 'title')
			{
				switch ($integration)
				{
					case 1 :
						$table = '#__community_events';
					break;

					case 2 :
						$table = '#__jticketing_events';

					break;

					case 3 :
						$table = '#__jevents_vevdetail';
						$filter_order = 'summary';
					break;
				}
			}
			else
			{
			}

			if ($table)
			{
				$columnNames = array_keys($db->getTableColumns($table));

				$allowed_fields = array();

				foreach ($columnNames as $columnName)
				{
					$allowed_fields[] = $columnName;
				}

				if (in_array($filter_order, $allowed_fields))
				{
					switch ($filter_order)
					{
						case 'title' :
							$query->order($db->quoteName('b.' . $filter_order) . ' ' . $filter_order_Dir);
						break;

						case 'cdate' :
							$query->order($db->quoteName('a.' . $filter_order) . ' ' . $filter_order_Dir);
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
	 * @return  array
	 *
	 * @since   2.0
	 */
	public function _buildContentWhere()
	{
		$jticketingmainhelper = new jticketingmainhelper;
		$input = Factory::getApplication()->getInput();
		global $mainframe, $option;
		$mainframe = Factory::getApplication();
		$option    = $input->get('option');
		$eventid   = $input->get('event', '', 'INT');

		$search_event = $this->getState('search_event');

		$where = array();

		if ($search_event != 0)
		{
			$integration = JT::getIntegration();
			$eventid = StringHelper::strtolower($search_event);
			$xrefid = JT::event($eventid, $integration)->integrationId;

			if ($xrefid)
			{
				$where [] = "a.event_details_id={$xrefid}";
			}

			return $where1 = (count($where)? '' . implode(' AND ', $where):'');
		}
		else
		{
			return '';
		}
	}

	/**
	 * Method getTotal
	 *
	 * @return  array
	 *
	 * @since   2.0
	 */
	public function getTotal()
	{
		// Lets load the content if it doesn’t already exist
		if (empty($this->total))
		{
			$query = $this->_buildQuery();
			$this->_total = $this->_getListCount($query);
		}

		return $this->_total;
	}

	/**
	 * Method getPagination
	 *
	 * @return  array
	 *
	 * @since   2.0
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
	 * @return  array
	 *
	 * @since   2.0
	 */
	public function getEventName()
	{
		$input                = Factory::getApplication()->getInput();
		$mainframe            = Factory::getApplication();
		$option               = $input->get('option');
		$eventid              = $input->get('event', '', 'INT');
		$jticketingmainhelper = new jticketingmainhelper;
		$query                = $jticketingmainhelper->getEventName($eventid);

		$this->_db->setQuery($query);
		$this->data = $this->_db->loadResult();

		return $this->_data;
	}

	/**
	 * Method getEventid
	 *
	 * @return  array
	 *
	 * @since   2.0
	 *
	 * @deprecated 3.2.0 This method will be removed in next version
	 */
	public function getEventid()
	{
		$jticketingmainhelper = new jticketingmainhelper;

		$query = $jticketingmainhelper->getSalesDataAdmin('', '', $where);
	}

	/**
	 * Method Eventdetails
	 *
	 * @return  array
	 *
	 * @since   2.0
	 */
	public function Eventdetails()
	{
		$input     = Factory::getApplication()->getInput();
		$mainframe = Factory::getApplication();
		$option    = $input->get('option');
		$eventid   = $input->get('event', '', 'INT');

		$query  = "SELECT title FROM #__community_events
					WHERE id = {$eventid}";
		$this->_db->setQuery($query);
		$this->_data = $this->_db->loadResult();

		return $this->_data;
	}

	/**
	 * Method to get backend sales report query
	 *
	 * @param   string  $where  condition
	 *
	 * @return  Mixed $query
	 *
	 * @since  3.2.0
	 */
	public function getSalesDataAdmin($where = '')
	{
		$integration = JT::getIntegration(true);
		$db = Factory::getDbo();
		$query = $db->getQuery(true);

		$query->select('a.order_id as order_id, SUM(order_tax) as eorder_tax,
			    SUM(original_amount) as eoriginal_amount, SUM(coupon_discount) as ecoupon_discount,
			    SUM(amount) as eamount, SUM(a.fee) as ecommission, SUM(a.ticketscount) as eticketscount,
			    a.*');
		$query->from('#__jticketing_order AS a');
		$query->join(
				'LEFT',
				$db->quoteName('#__jticketing_integration_xref', 'i') . ' ON ' .
				$db->quoteName('a.event_details_id') . ' = ' . $db->quoteName('i.id')
			);

		if ($integration == 1)
		{
			$query->select('b.id AS evid, b.title, b.thumb');
			$query->join(
				'LEFT',
				$db->quoteName('#__community_events', 'b') . ' ON ' .
				$db->quoteName('b.id') . ' = ' . $db->quoteName('i.eventid')
			);
			$query->where($db->quoteName('i.source') . ' = \'com_community\'');
		}
		elseif ($integration == 2)
		{
			$query->select('b.id AS evid, b.image AS thumb, b.title');
			$query->join('LEFT', $db->quoteName('#__jticketing_events', 'b') . ' ON ' . $db->quoteName('b.id') . ' = ' . $db->quoteName('i.eventid'));
			$query->where($db->quoteName('i.eventid') . ' = b.id');
			$query->where($db->quoteName('i.source') . ' = \'com_jticketing\'');
		}
		elseif ($integration == 3)
		{
			$query->select('b.ev_id AS evid, c.summary as title');
			$query->join('LEFT', $db->quoteName('#__jevents_vevent', 'b') . ' ON ' . $db->quoteName('b.ev_id') . ' = ' . $db->quoteName('i.eventid'));
			$query->join('LEFT', $db->quoteName('#__jevents_vevdetail', 'c') . ' ON ' . $db->quoteName('b.detail_id') . ' = ' . $db->quoteName('c.evdet_id'));
			$query->where($db->quoteName('i.eventid') . ' = b.ev_id');
			$query->where($db->quoteName('i.source') . ' = \'com_jevents\'');
		}
		elseif ($integration == 4)
		{
			$query->select('b.id AS evid, b.title as title');
			$query->join('LEFT', $db->quoteName('#__social_clusters', 'b') . ' ON ' . $db->quoteName('b.id') . ' = ' . $db->quoteName('i.eventid'));
			$query->where($db->quoteName('i.eventid') . ' = b.id');
			$query->where($db->quoteName('i.source') . ' = \'com_easysocial\'');
		}

		$query->where($db->quoteName('a.status') . ' = "C"');

		if (!empty($where))
		{
			$query->where($where);
		}

		return $query;
	}
}
