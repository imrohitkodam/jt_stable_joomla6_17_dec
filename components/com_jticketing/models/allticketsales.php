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
use Joomla\CMS\Pagination\Pagination;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;


/**
 * Model for all ticket sales
 *
 * @package     JTicketing
 * @subpackage  component
 * @since       1.0
 */
class JticketingModelallticketsales extends BaseDatabaseModel
{
	public $data;

	public $total;

	public $pagination;

	public $jticketingmainhelper;

	/**
	 * Constructor.
	 *
	 * @see     JController
	 * @since   1.6
	 */
	public function __construct()
	{
		parent::__construct();
		$input      = Factory::getApplication()->getInput();
		$mainframe  = Factory::getApplication();
		$option     = $input->get('option');

		// Get pagination request variables
		$limit      = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->get('list_limit'), 'int');
		$limitstart = $input->get('limitstart', '0', 'INT');

		// In case limit has been changed, adjust it
		$limitstart = ($limit !== 0 ? (floor($limitstart / $limit) * $limit) : 0);
		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);
		$this->jticketingmainhelper = new jticketingmainhelper;
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
			$query       = $this->_buildQuery();
			$this->data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
		}

		$mainframe        = Factory::getApplication();
		$filter_order     = '';
		$filter_order_Dir = '';
		$orderDir         = array(
			'asc',
			'desc'
		);
		$filter_type      = $mainframe->getUserStateFromRequest($this->option . 'filter_order', 'filter_order', 'goal_amount', 'cmd');
		$filter_order_Dir = $mainframe->getUserStateFromRequest('filter_order_Dir', 'filter_order_Dir', 'desc', 'word');

		if (in_array(strtolower($filter_order_Dir), $orderDir))
		{
			$filter_order_Dir = $filter_order_Dir;
		}

		if ($filter_type == 'eticketscount' && $this->data)
		{
			$this->data = JT::utilities()->multiDSort($this->data, $filter_type, $filter_order_Dir);
		}

		return $this->data;
	}

	/**
	 * Method _buildQuery
	 *
	 * @return  JDatabaseQuery
	 *
	 * @since   1.0
	 */
	public function _buildQuery()
	{
		$integration = JT::getIntegration(true);
		$user        = Factory::getUser();
		$db          = Factory::getDbo();
		$app         = Factory::getApplication();
		$option      = $app->getInput()->get('option');
		$where       = $this->_buildContentWhere();
		$query       = $this->getSalesDataSite($user->id, $where);
		$query->group($db->quoteName('a.event_details_id'));

		$filter_order     = $app->getUserStateFromRequest($option . 'filter_order', 'filter_order', 'title', 'cmd');
		$filter_order_Dir = $app->getUserStateFromRequest($option . 'filter_order_Dir', 'filter_order_Dir', 'desc', 'word');

		if ($filter_order)
		{
			$table = '';

			if ($filter_order == 'cdate')
			{
				$table = '#__jticketing_order';
			}
			elseif ($filter_order == 'title')
			{
				switch ($integration)
				{
					case 1:
						$table = '#__community_events';
						break;

					case 2:
						$table = '#__jticketing_events';
						break;

					case 3:
						$table = '#__jevents_vevdetail';
						$filter_order = 'summary';
						break;
				}
			}

			if ($table)
			{
				$columnNames = array_keys($db->getTableColumns($table));

				foreach ($columnNames as $columnName)
				{
					$allowed_fields[] = $columnName;
				}

				if (in_array($filter_order, $allowed_fields) && in_array($filter_order_Dir, array('asc', 'desc')))
				{
					switch ($filter_order)
					{
						case 'title':
							$query->order($db->quoteName('event.' . $filter_order) . ' ' . $filter_order_Dir);
							break;

						case 'cdate':
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
	 * @return string
	 *
	 * @throws Exception
	 */
	public function _buildContentWhere()
	{
		$app     = Factory::getApplication();
		$eventid = $app->getUserStateFromRequest($this->option . 'search_event', 'search_event', '', 'string');

		if (!$eventid)
		{
			return '';
		}

		$xrefid = JT::event($eventid, JT::getIntegration())->integrationId;

		return ($xrefid) ? "a.event_details_id=" . $xrefid : "";
	}

	/**
	 * Method _buildContentXrefid
	 *
	 * @return string
	 *
	 * @throws Exception
	 */
	public function _buildContentXrefid()
	{
		$mainframe = Factory::getApplication();
		$xrefid    = '';

		$eventid = $mainframe->getUserStateFromRequest($this->option . 'search_event', 'search_event', '', 'string');

		if ($eventid)
		{
			return $xrefid;
		}

		$eventid = $mainframe->getInput()->get('event', '', 'INT');

		if ($eventid)
		{
			$xrefid = JT::event($eventid, JT::getIntegration())->integrationId;
		}

		return $xrefid;
	}

	/**
	 * Method getTotal
	 *
	 * @return integer
	 *
	 * @throws Exception
	 */
	public function getTotal()
	{
		// Lets load the content if it doesn’t already exist
		if (empty($this->total))
		{
			$query        = $this->_buildQuery();
			$this->total = $this->_getListCount($query);
		}

		return $this->total;
	}

	/**
	 * Method getPagination
	 *
	 * @return object
	 *
	 * @throws Exception
	 */
	public function getPagination()
	{
		// Lets load the content if it doesn’t already exist
		if (empty($this->pagination))
		{
			$this->pagination = new Pagination($this->getTotal(), $this->getState('limitstart'), $this->getState('limit'));
		}

		return $this->pagination;
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
	 * Method Eventdetails
	 *
	 * @return data
	 */
	public function Eventdetails()
	{
		$input     = Factory::getApplication()->getInput();
		$eventid = $input->get('event', '', 'INT');

		$query = "SELECT title FROM #__community_events WHERE id = {$eventid}";
		$this->_db->setQuery($query);
		$this->data = $this->_db->loadResult();

		return $this->data;
	}

	/**
	 * Method to get frontend sales report query
	 *
	 * @param   int     $creator  user id of event owner
	 * @param   string  $where    condition
	 *
	 * @return  JDatabaseQuery $query
	 *
	 * @since 3.2.0
	 */
	public function getSalesDataSite($creator, $where)
	{
		$integration = JT::getIntegration();
		$db          = Factory::getDbo();
		$query       = $db->getQuery(true);

		$query->select('a.order_id as order_id, SUM(a.amount) as eamount, SUM(a.fee) as ecommission, SUM(a.ticketscount)
			as eticketscount, a.*');
		$query->from('#__jticketing_order AS a');
			$query->join(
				'LEFT',
				$db->quoteName('#__jticketing_integration_xref', 'i') . ' ON ' .
				$db->quoteName('a.event_details_id') . ' = ' . $db->quoteName('i.id')
			);

		if ($integration == 'com_community')
		{
			$query->select('event.id AS evid, event.title, event.thumb, event.startdate AS startdate');
			$query->join(
				'LEFT',
				$db->quoteName('#__community_events', 'event') . ' ON ' .
				$db->quoteName('event.id') . ' = ' . $db->quoteName('i.eventid')
			);
			$query->where($db->quoteName('i.source') . ' = \'com_community\'');
			$query->where($db->quoteName('event.creator') . ' = ' . $db->quote($creator));
		}
		elseif ($integration == 'com_jticketing')
		{
			$query->select('event.id AS evid, event.title, event.image as thumb, event.startdate AS startdate');
			$query->join(
				'LEFT',
				$db->quoteName('#__jticketing_events', 'event') . ' ON ' .
				$db->quoteName('event.id') . ' = ' . $db->quoteName('i.eventid')
			);
			$query->where($db->quoteName('i.source') . ' = \'com_jticketing\'');
			$query->where($db->quoteName('event.created_by') . ' = ' . $db->quote($creator));
		}
		elseif ($integration == 'com_jevents')
		{
			$query->join(
				'LEFT',
				$db->quoteName('#__jevents_repetition', 'rep') . ' ON ' .
				$db->quoteName('i.eventid') . ' = ' . $db->quoteName('rep.rp_id')
			);

			$query->select('i.id AS evid, vevent.summary as title');
			$query->select($db->quoteName('vevent.dtstart', 'startdate'));
			$query->join(
				'LEFT',
				$db->quoteName('#__jevents_vevent', 'event') . ' ON ' .
				$db->quoteName('event.ev_id') . ' = ' . $db->quoteName('rep.eventid')
			);
			$query->join(
				'LEFT',
				$db->quoteName('#__jevents_vevdetail', 'vevent') . ' ON ' .
				$db->quoteName('vevent.evdet_id') . ' = ' . $db->quoteName('event.detail_id')
			);
			$query->where($db->quoteName('i.source') . ' = \'com_jevents\'');
			$query->where($db->quoteName('event.created_by') . ' = ' . $db->quote($creator));
		}
		elseif ($integration == 'com_easysocial')
		{
			$query->select('event.id AS evid, event.title as title, event_det.start AS startdate');
			$query->join(
				'LEFT',
				$db->quoteName('#__social_clusters', 'event') . ' ON ' .
				$db->quoteName('event.id') . ' = ' . $db->quoteName('i.eventid')
			);
			$query->join(
				'LEFT',
				$db->quoteName('#__social_events_meta', 'event_det') . ' ON ' .
				$db->quoteName('event.id') . ' = ' . $db->quoteName('event_det.cluster_id')
			);
			$query->where($db->quoteName('i.source') . ' = \'com_easysocial\'');
			$query->where($db->quoteName('event.creator_uid') . ' = ' . $db->quote($creator));
		}

		$query->where($db->quoteName('a.status') . ' IN (\'C\',\'DP\')');

		if (!empty($where))
		{
			$query->where($where);
		}

		return $query;
	}
}
