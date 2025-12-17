<?php
/**
 * @package     TJCertificate
 * @subpackage  com_tjcertificate
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2020 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\Data\DataObject;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\ListModel;

/**
 * Methods supporting a list of records.
 *
 * @since  1.0.0
 */
class TjCertificateModelTemplates extends ListModel
{
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
				'id', 'ct.id',
				'title', 'ct.title',
				'client', 'ct.client',
				'is_public', 'ct.is_public',
				'ordering', 'ct.ordering',
				'state', 'ct.state',
				'created_by', 'ct.created_by',
			);
		}

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
	protected function populateState($ordering = 'ct.ordering', $direction = 'desc')
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

		$extension = Factory::getApplication()->getInput()->get('extension', '', 'CMD');

		$this->setState('filter.component', $extension);

		// Create the base select statement.
		$query->select(array('ct.*', 'IF(users.name IS NULL,"' . Text::_('COM_TJCERTIFICATE_BLOCKED_USER') . '",users.name) AS uname'));
		$query->from($db->quoteName('#__tj_certificate_templates', 'ct'));
		$query->join('LEFT', $db->quoteName('#__users', 'users') . ' ON (' . $db->quoteName('ct.created_by') . ' = ' . $db->quoteName('users.id') . ')');

		// Filter by dashboard_id
		$id = $this->getState('filter.id');

		if (!empty($id))
		{
			$query->where($db->quoteName('ct.id') . ' = ' . (int) $id);
		}

		// Filter by client
		$client = $this->getState('filter.client');

		if (!empty($client))
		{
			$query->where($db->quoteName('ct.client') . ' = ' . $db->quote($client));
		}
		elseif (!empty($extension))
		{
			$query->where($db->quoteName('ct.client') . ' = ' . $db->quote($extension));
		}

		// Filter by search in title.
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('ct.id = ' . (int) substr($search, 3));
			}
			else
			{
				$search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
				$query->where('(ct.title LIKE ' . $search . ' )');
			}
		}

		// Filter by created_by
		$created_by = $this->getState('filter.created_by');

		if (!empty($created_by))
		{
			$query->where($db->quoteName('ct.created_by') . ' = ' . (int) $created_by);
		}

		// Filter by is_public
		$isPublic = $this->getState('filter.is_public');

		if (!empty($isPublic))
		{
			$query->where($db->quoteName('ct.is_public') . ' = ' . (int) $isPublic);
		}

		// Filter by state
		$state = $this->getState('filter.state');

		if (is_numeric($state))
		{
			$query->where('ct.state = ' . (int) $state);
		}
		elseif ($state === '')
		{
			$query->where('(ct.state = 0 OR ct.state = 1)');
		}

		// Add the list ordering clause.
		$orderCol  = $this->state->get('list.ordering');
		$orderDirn = $this->state->get('list.direction');

		if ($orderCol && $orderDirn)
		{
			$query->order($db->escape($orderCol . ' ' . $orderDirn));
		}

		return $query;
	}
}
