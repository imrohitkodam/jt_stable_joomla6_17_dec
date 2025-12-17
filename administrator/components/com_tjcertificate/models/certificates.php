<?php
/**
 * @package     TJCertificate
 * @subpackage  com_tjcertificate
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\Data\DataObject;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * Methods supporting a list of records.
 *
 * @since  1.0.0
 */
class TjCertificateModelCertificates extends ListModel
{
	protected $comMultiAgency = 'com_multiagency';

	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see        JController
	 * @since      1.0.0
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'ci.id',
				'certificate_template_id', 'ci.certificate_template_id',
				'client', 'ci.client',
				'user_id', 'ci.user_id',
				'state', 'ci.state',
				'issued_on', 'ci.issued_on',
				'expired_on', 'ci.expired_on',
				'agency_id'
			);
		}

		$this->params = ComponentHelper::getParams('com_tjcertificate');

		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   Ordering
	 * @param   string  $direction  Ordering dir
	 *
	 * @since    1.6
	 *
	 * @return  void
	 */
	protected function populateState($ordering = 'ci.id', $direction = 'desc')
	{
		$app = Factory::getApplication();

		$client = $app->getUserStateFromRequest($this->context . '.filter.client', 'client');
		$this->setState('filter.client', $client);

		parent::populateState($ordering, $direction);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return   DataObjectbaseQuery
	 *
	 * @since    1.0.0
	 */
	protected function getListQuery()
	{
		// Initialize variables.
		$db    = $this->getDbo();
		$query = $db->getQuery(true);
		$app   = Factory::getApplication();
		$user  = Factory::getUser();

		$extension = Factory::getApplication()->getInput()->get('extension', '', 'CMD');

		$this->setState('filter.component', $extension);

		// Filter by client
		$client = $this->getState('filter.client');

		// Create the base select statement.
		$query->select(array('ci.*', 'ct.title', 'users.name as uname'));
		$query->from($db->quoteName('#__tj_certificate_issue', 'ci'));

		$query->join('LEFT', $db->quoteName('#__tj_certificate_templates', 'ct') .
			' ON (' . $db->quoteName('ci.certificate_template_id') . ' = ' . $db->quoteName('ct.id') . ')');

		$query->join('LEFT', $db->quoteName('#__users', 'users') .
			' ON (' . $db->quoteName('ci.user_id') . ' = ' . $db->quoteName('users.id') . ')');

		if (ComponentHelper::isEnabled($this->comMultiAgency) && $this->params->get('enable_multiagency'))
		{
			$query->select('agency.title as title');

			$query->join('LEFT', $db->quoteName('#__tj_cluster_nodes', 'nodes') .
				' ON (' . $db->quoteName('users.id') . ' = ' . $db->quoteName('nodes.user_id') . ')');

			$query->join('LEFT', $db->quoteName('#__tj_clusters', 'clusters') .
				' ON (' . $db->quoteName('clusters.id') . ' = ' . $db->quoteName('nodes.cluster_id') .
				' AND ' . $db->quoteName('clusters.client') . ' = ' . $db->quote($this->comMultiAgency) . ')');

			$query->join('LEFT', $db->quoteName('#__tjmultiagency_multiagency', 'agency') .
				' ON (' . $db->quoteName('agency.id') . ' = ' . $db->quoteName('clusters.client_id') . ')');

			$agencyId = $this->getState('filter.agency_id');

			$canManageAllAgencyUser = $user->authorise('core.manage.all.agency.user', $this->comMultiAgency);

			// If don't have manage all user permission then get users of own agency
			if (!$canManageAllAgencyUser && !$agencyId)
			{
				// Subquery to get agency users
				$subquery  = $db->getQuery(true);
				$subquery->select($db->quoteName('ml.id'));
				$subquery->from($db->quoteName('#__tjmultiagency_multiagency', 'ml'));
				$subquery->join('INNER', $db->quoteName('#__tj_clusters', 'c') . ' ON ' . $db->quoteName('c.client_id') . ' = ' . $db->quoteName('ml.id'));
				$subquery->join('INNER', $db->quoteName('#__tj_cluster_nodes', 'cn') . ' ON ' . $db->quoteName('cn.cluster_id') . ' = ' . $db->quoteName('c.id'));
				$subquery->where($db->quoteName('ml.state') . ' = ' . 1);
				$subquery->where($db->quoteName('cn.user_id') . ' = ' . (int) $user->id);

				$query->where($db->quoteName('agency.id') . ' in (' . $subquery . ')');
			}
			elseif ($agencyId)
			{
				$query->where($db->quoteName('agency.id') . ' = ' . (int) $agencyId);
			}
		}

		// Filter by certificate id
		$id = $this->getState('filter.id');

		if (!empty($id))
		{
			$query->where($db->quoteName('ci.id') . ' = ' . (int) $id);
		}

		// Filter by certificate template id
		$certificateTemplateId = $this->getState('filter.certificate_template_id');

		if (!empty($certificateTemplateId))
		{
			$query->where($db->quoteName('ci.certificate_template_id') . ' = ' . (int) $certificateTemplateId);
		}

		if (!empty($client))
		{
			$query->where($db->quoteName('ci.client') . ' = ' . $db->quote($client));
		}
		elseif (!empty($extension))
		{
			$query->where($db->quoteName('ci.client') . ' = ' . $db->quote($extension));
		}

		// Filter by client id
		$clientId = $this->getState('filter.client_id');

		if (!empty($clientId))
		{
			$query->where($db->quoteName('ci.client_id') . ' = ' . $db->quote($clientId));
		}

		// Filter by user id
		$userId = $this->getState('filter.user_id');

		if (!empty($userId))
		{
			$query->where($db->quoteName('ci.user_id') . ' = ' . (int) $userId);
		}

		// Filter by client issued to
		$clientIssuedTo = $this->getState('filter.client_issued_to');

		if (!empty($clientIssuedTo))
		{
			$query->where($db->quoteName('ci.client_issued_to') . ' = ' . (int) $clientIssuedTo);
		}

		// Filter by search in title.
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('ci.id = ' . (int) substr($search, 3));
			}
			else
			{
				$search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
				$query->where(' (ct.title LIKE ' . $search . ' OR ci.unique_certificate_id LIKE '
					. $search . ' OR ci.client_issued_to_name LIKE ' . $search .
					' OR users.name LIKE ' . $search . ' )');
			}
		}

		// Filter by state
		$state = $this->getState('filter.state');

		if (is_numeric($state))
		{
			$query->where('ci.state = ' . (int) $state);
		}
		elseif ($state === '')
		{
			// Publish, Unpublish and Pending records available in frontend
			if ($app->isClient("site"))
			{
				$query->where('(ci.state IN (0,1,-1))');
			}
		}

		// Filter by Expired certificates
		$expired = $this->getState('filter.expired');

		if ($expired)
		{
			
			
			$query->where($db->quoteName('ci.expired_on') . ' <> ' . $db->quote('0000-00-00 00:00:00'));
			$query->where($db->quoteName('ci.expired_on') . ' < ' . $db->quote(Factory::getDate()->toSql()));
		}

		// Add the list ordering clause.
		$orderCol  = $this->state->get('list.ordering', 'ci.id');
		$orderDirn = $this->state->get('list.direction', 'desc');

		$query->group('ci.id');

		if ($orderCol && $orderDirn)
		{
			$query->order($db->escape($orderCol . ' ' . $orderDirn));
		}

		return $query;
	}
}
