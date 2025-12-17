<?php
/**
 * @package     TJVendors
 * @subpackage  com_tjvendors
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Table\Table;

/**
 * Methods supporting a list of Tjvendors records.
 *
 * @since  1.6
 */
class TjvendorsModelVendors extends ListModel
{
/**
	* Constructor.
	*
	* @param   array  $config  An optional associative array of configuration settings.
	*
	* @see        JController
	* @since      1.0
	*/
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'vendor_id', 'v.`vendor_id`',
				'vendor_title', 'v.`vendor_title`',
				'ordering', 'v.`ordering`',
				'state', 'v.`state`',
			);
		}

		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   Elements order
	 * @param   string  $direction  Order direction
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// Initialise variables.
		$app = Factory::getApplication('administrator');

		// Set ordering.
		$orderCol = $app->getUserStateFromRequest($this->context . '.filter_order', 'filter_order');

		if (!in_array($orderCol, $this->filter_fields))
		{
			$orderCol = 'v.vendor_id';
		}

		$this->setState('list.ordering', $orderCol);

		// Load the filter state.
		$search = $app->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$published = $app->getUserStateFromRequest($this->context . '.filter.state', 'filter_published', '', 'string');
		$this->setState('filter.state', $published);

		// Load the parameters.
		$params = ComponentHelper::getParams('com_tjvendors');
		$this->setState('params', $params);

		// List state information.
		parent::populateState('v.vendor_id', 'asc');
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return   JDatabaseQuery
	 *
	 * @since    1.0
	 */
	protected function getListQuery()
	{
		// Get client
		$input  = Factory::getApplication()->getInput();
		$client = $input->get('client', '', 'STRING');

		// Create a new query object.
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('v.*, vx.approved, vx.state');
		$query->from($db->quoteName('#__tjvendors_vendors', 'v'));
		$query->join('LEFT', $db->quoteName('#__vendor_client_xref', 'vx') .
		'ON (' . $db->quoteName('v.vendor_id') . ' = ' . $db->quoteName('vx.vendor_id') . ')');
		$query->where($db->quoteName('vx.client') . ' = ' . $db->quote($client));

		// Filter by search in title
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where($db->quoteName('v.vendor_id') . ' = ' . (int) substr($search, 3));
			}
			else
			{
				$search = $db->Quote('%' . $db->escape($search, true) . '%');
				$query->where('(' . $db->quoteName('v.vendor_id') . ' LIKE ' . $search . 'OR' . $db->quoteName('v.vendor_title') . ' LIKE ' . $search . ')');
			}
		}

		// Add the list ordering clause.
		$orderCol  = $this->state->get('list.ordering');
		$orderDirn = $this->state->get('list.direction');

		if (!in_array(strtoupper($orderDirn), array('ASC', 'DESC')))
		{
			$orderDirn = 'DESC';
		}

		$query->order($db->escape($orderCol . ' ' . $orderDirn));

		return $query;
	}

	/**
	 * Build an SQL query check for available data
	 *
	 * @param   integer  $vendor_id  for checking data for that vendor
	 *
	 * @return   result
	 *
	 * @since    1.0
	 */
	public function checkForAvailableRecords($vendor_id)
	{
		$db = $this->getDbo();
		$query = $db->getQuery(true);
		$query ->select('COUNT(*)');
		$query->from($db->quoteName('#__vendor_client_xref'));
		$query->where($db->quoteName('vendor_id') . ' = ' . $db->quote($vendor_id));
		$db->setQuery($query);

		try
		{
			$result = (int) ($db->loadColumn()[0] ?? 0);
		}
		catch (Exception $e)
		{
			Factory::getApplication()->enqueueMessage(Text::_('COM_TJVENDORS_DB_EXCEPTION_WARNING_MESSAGE'), 'error');
		}

		if (empty($result))
		{
			return false;
		}

		return $result;
	}

	/**
	 * Build an SQL query to delete vendor data
	 *
	 * @param   integer  $vendor_id  for deleting record of that vendor
	 *
	 * @return   void
	 *
	 * @since    1.0
	 */
	public function deleteVendor($vendor_id)
	{
		$db = $this->getDbo();
		$query = $db->getQuery(true);
		$query->delete($db->quoteName('#__tjvendors_vendors'));
		$query->where($db->quoteName('vendor_id') . ' = ' . $db->quote($vendor_id));
		$db->setQuery($query);
		$db->execute();
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @param   integer  $vendor_id  for deleting record of that vendor
	 *
	 * @param   string   $client     client from url
	 *
	 * @return   JDatabaseQuery
	 *
	 * @since    1.0
	 */
	public function deleteClientFromVendor($vendor_id, $client)
	{
		BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_tjvendors/models');
		$tjvendorsModelVendor     = BaseDatabaseModel::getInstance('Vendor', 'TjvendorsModel');
		$tjvendorsModelVendorFees = BaseDatabaseModel::getInstance('VendorFees', 'TjvendorsModel');
		$vendorData               = $tjvendorsModelVendor->getItem($vendor_id);
		$db                       = $this->getDbo();

		// Get vendor specific Fees Data
		$tjvendorsModelVendorFees->setState('vendor_id', $vendor_id);
		$vendorFeeData            = $tjvendorsModelVendorFees->getItems();

		if (!empty($vendorFeeData))
		{
			foreach ($vendorFeeData as $feeData)
			{
				// Getting Vendor Payable Amount here
				$result = $tjvendorsModelVendor->getPayableAmount($feeData->vendor_id, $feeData->client, $feeData->currency);

				// If Vendor Payable amount is remaining then don't allow to delete vendor
				if (!empty($result))
				{
					Factory::getApplication()->enqueueMessage(sprintf(Text::_("COM_TJVENDORS_VENDOR_DELETE_ERROR_MESSAGE"), $vendorData->vendor_title), 'error');

					return false;
				}
			}
		}

		$query = $db->getQuery(true);
		$query->delete($db->quoteName('#__vendor_client_xref'));
		$query->where($db->quoteName('vendor_id') . ' = ' . $db->quote($vendor_id));

		if (!empty($client))
		{
			$query->where($db->quoteName('client') . ' = ' . $db->quote($client));
		}

		$db->setQuery($query);

		try
		{
			$result = $db->execute();
		}
		catch (Exception $e)
		{
			Factory::getApplication()->enqueueMessage(Text::_('COM_TJVENDORS_DB_EXCEPTION_WARNING_MESSAGE'), 'error');
		}

		if (empty($result))
		{
			return false;
		}

		$availability = $this->checkForAvailableRecords($vendor_id, $client);

		if ($availability == 0)
		{
			$this->deleteVendor($vendor_id);
		}

		PluginHelper::importPlugin('tjvendors');
		Factory::getApplication()->triggerEvent('onAfterTjvendorVendorDelete', array($vendorData, $client));
	}

	/**
	 * Method To plublish and unpublish vendors
	 *
	 * @param   Array    $items   Vendor Ids
	 * @param   Integer  $state   State
	 * @param   String   $client  Client like com_jgive or com_jticketing
	 *
	 * @return  Boolean
	 *
	 * @since  1.0
	 */
	public function setItemState($items, $state, $client)
	{
		$db = Factory::getDbo();

		foreach ($items as $id)
		{
			Table::addIncludePath(JPATH_ROOT . '/administrator/components/com_tjvendors/tables');
			$tjvendorsTablevendorclientxref = Table::getInstance('vendorclientxref', 'TjvendorsTable', array());
			$tjvendorsTablevendorclientxref->load(array('vendor_id' => $id, 'client'    => $client));
			$updateState = new stdClass;

			// Must be a valid primary key value.
			$updateState->id    = $tjvendorsTablevendorclientxref->id;
			$updateState->state = $state;

			// Update their details in the users table using id as the primary key.
			Factory::getDbo()->updateObject('#__vendor_client_xref', $updateState, 'id');

			/* Send Mail when Admin users change vendor state of vendor, these mails are not needed.. */
			/*
			$vendorObject->load(array('vendor_id' => $id));
			$vendorObject->adminapproved = $state;

			$tjvendorsTriggerVendor->onAfterVendorSave($vendorObject, false);
			*/
			if (!$db->execute())
			{
				$this->setError($this->_db->getErrorMsg());

				return false;
			}
		}

		PluginHelper::importPlugin('tjvendors');
		Factory::getApplication()->triggerEvent('onAfterTjVendorsVendorStateChange', array($items, $state, $client));

		return true;
	}
}
