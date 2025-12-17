<?php
/**
 * @package     TJVendors
 * @subpackage  com_tjvendors
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access.
defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Table\Table;

$fronthelperPath = JPATH_SITE . '/components/com_tjvendors/helpers/fronthelper.php';
if (file_exists($fronthelperPath)) {
	require_once $fronthelperPath;
}

/**
 * Tjvendors model.
 *
 * @since  1.6
 */
class TjvendorsModelPayout extends AdminModel
{
	/**
	 * @var      string    The prefix to use with controller messages.
	 * @since    1.6
	 */
	protected $text_prefix = 'COM_TJVENDORS';

	/**
	 * @var   	string  	Alias to manage history control
	 * @since   3.2
	 */
	public $typeAlias = 'com_tjvendors.payout';

	/**
	 * @var null  Item data
	 * @since  1.6
	 */
	protected $item = null;

	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param   string  $type    The table type to instantiate
	 * @param   string  $prefix  A prefix for the table class name. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return    Table    A database object
	 *
	 * @since    1.6
	 */
	public function getTable($type = 'payout', $prefix = 'TjvendorsTable', $config = array())
	{
		return Table::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to get the record form.
	 *
	 * @param   array    $data      An optional array of data for the form to interogate.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  Form  A Form object on success, false on failure
	 *
	 * @since    1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Initialise variables.
		$app = Factory::getApplication();

		// Get the form.
		$form = $this->loadForm(
			'com_tjvendors.payout', 'payout',
			array('control' => 'jform',
				'load_data' => $loadData,
			)
		);

		if (empty($form))
		{
			return false;
		}

		if ($form->getValue('total')) 
		{
			$form->setFieldAttribute('total', 'max', $form->getValue('total'));
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return   mixed  The data for the form.
	 *
	 * @since    1.6
	 */
	protected function loadFormData()
	{
		BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_tjvendors/models');
		$tjvendorsModelVendor     = BaseDatabaseModel::getInstance('Vendor', 'TjvendorsModel');

		// Check the session for previously entered form data.
		$data = Factory::getApplication()->getUserState('com_tjvendors.edit.payout.data', array());
		$com_params = ComponentHelper::getParams('com_tjvendors');
		$bulkPayoutStatus = $com_params->get('bulk_payout');

		if (empty($data))
		{
			if ($this->item === null)
			{
				$this->item = $this->getItem();
			}

		if ($bulkPayoutStatus != 0)
		{
			$payoutAmount = TjvendorsHelper::bulkPendingAmount($this->item->vendor_id, $this->item->currency);
			$this->item->bulk_total = $payoutAmount;
		}
		else
		{
			$payableAmount = $tjvendorsModelVendor->getPayableAmount($this->item->vendor_id, $this->item->client, $this->item->currency);

			if (!empty($payableAmount))
			{
				$this->item->total = $payableAmount[$this->item->client][$this->item->currency];
			}
		}

			$this->item->reference_order_id = '';
			BaseDatabaseModel::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_tjvendors/models', 'vendor');
			$tjvendorsModelVendor = BaseDatabaseModel::getInstance('Vendor', 'TjvendorsModel');
			$vendorDetail = $tjvendorsModelVendor->getItem();
			$this->item->vendor_title = $vendorDetail->vendor_title;
		}

		return $this->item;
	}

	/**
	 * Method for saving the pending payable amount
	 *
	 * @param   Array  $data  Data
	 *
	 * @return id
	 */
	public function save($data)
	{
		if (!isset($data['adaptive_payout']))
		{
			$com_params = ComponentHelper::getParams('com_tjvendors');
			$bulkPayoutStatus = $com_params->get('bulk_payout');
			$tjvendorFrontHelper = new TjvendorFrontHelper;

			BaseDatabaseModel::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_tjvendors/models', 'payout');
			$tjvendorsModelPayout = BaseDatabaseModel::getInstance('Payout', 'TjvendorsModel');

			$vendorDetail = $tjvendorsModelPayout->getItem();

			$client = $vendorDetail->client;

			// To get selected item
			$item = $this->getItem($data['id']);

			if ($bulkPayoutStatus != 0)
			{
				$vendorClients = TjvendorsHelper::getClients($vendorDetail->vendor_id);

				foreach ($vendorClients as $client)
				{
					$pending_amount = TjvendorsHelper::getTotalAmount($vendorDetail->vendor_id, $vendorDetail->currency, $client['client']);
					$data['debit'] = $pending_amount['total'];
					$data['total'] = 0;
					$data['transaction_time'] = Factory::getDate()->toSql();
					$data['client'] = $client['client'];
					$transactionClient = $tjvendorFrontHelper->getClientName($client['client']);
					$data['transaction_id'] = $transactionClient . '-' . $vendorDetail->currency . '-' . $vendorDetail->vendor_id . '-';
					$data['credit'] = 0;
					$data['id'] = '';
					$data['vendor_id'] = $vendorDetail->vendor_id;
					$params = array("customer_note" => "", "entry_status" => "debit_payout");
					$data['params'] = json_encode($params);

					if (parent::save($data))
					{
						$id = (int) $this->getState($this->getName() . '.id');
						$payout_update = new stdClass;

						// Must be a valid primary key value.
						$payout_update->id = $id;
						$payout_update->transaction_id = $data['transaction_id'] . $payout_update->id;

						// Update their details in the users table using id as the primary key.
						Factory::getDbo()->updateObject('#__tjvendors_passbook', $payout_update, 'id');
					}

					$message = Text::_('COM_TJVENDORS_PAYOUT_SUCCESSFULL_MESSAGE');
					Factory::getApplication()->enqueueMessage($message);
				}

				return true;
			}

			$data['debit'] = $data['total'];
			$payableAmount = TjvendorsHelper::getTotalAmount($item->vendor_id, $item->currency, $item->client);
			$data['total'] = $payableAmount['total'] - $data['debit'];
			$data['transaction_time'] = Factory::getDate()->toSql();
			$data['client'] = $vendorDetail->client;
			$transactionClient = $tjvendorFrontHelper->getClientName($data['client']);
			$data['transaction_id'] = $transactionClient . '-' . $vendorDetail->currency . '-' . $vendorDetail->vendor_id . '-';
			$data['id'] = '';
			$data['vendor_id'] = $item->vendor_id;
			$data['credit'] = '0.00';
			$params = array("customer_note" => "", "entry_status" => "debit_payout");
			$data['params'] = json_encode($params);
		}

		if (parent::save($data))
		{
			$id = (int) $this->getState($this->getName() . '.id');
			$payout_update = new stdClass;

			// Must be a valid primary key value.
			$payout_update->id = $id;
			$payout_update->transaction_id = $data['transaction_id'] . $payout_update->id;

			// Plugin trigger
			PluginHelper::importPlugin('tjvendors');
			Factory::getApplication()->triggerEvent('onAfterTjVendorPayoutPaid', array($id, $data, true));

			// Update their details in the users table using id as the primary key.
			$result = Factory::getDbo()->updateObject('#__tjvendors_passbook', $payout_update, 'id');

			return true;
		}

		return false;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @param   array  $data  An optional array of data for the form to interogate.
	 *
	 * @return   mixed  The data for the form.
	 *
	 * @since    1.6
	 */
	public function updatingCreditData($data)
	{
		$payout_detail = TjvendorsHelper::getTotalAmount($data['vendor_id'], $data['currency'], $data['client']);
		$payout_id = $payout_detail['id'];
		$object = new stdClass;

		// Must be a valid primary key value.
		$object->id = $payout_id;
		$object->transaction_id = $data['transaction_id'] . $object->id;

		try
		{
			// Update their details in the users table using id as the primary key.
			$result = Factory::getDbo()->updateObject('#__tjvendors_passbook', $object, 'id');
		}
		catch (Exception $e)
		{
			Factory::getApplication()->enqueueMessage(Text::_('COM_TJVENDORS_DB_EXCEPTION_WARNING_MESSAGE'), 'error');
		}

		if (empty($result))
		{
			return false;
		}
	}

	/**
	 * Method to add the amount when a product is purchased.
	 *
	 * @param   array  $data  data of order
	 *
	 * @return   boolean
	 *
	 * @since    1.6
	 */
	public function addCreditEntry($data)
	{
		$creditEntry = new stdClass;
		$creditEntry->vendor_id = $data['vendor_id'];
		$creditEntry->currency = $data['currency'];
		$creditEntry->total = $data['total'];
		$creditEntry->credit = $data['credit'];
		$creditEntry->debit = $data['debit'];
		$creditEntry->reference_order_id = $data['reference_order_id'];
		$creditEntry->transaction_time = $data['transaction_time'];
		$creditEntry->client = $data['client'];
		$creditEntry->transaction_id = $data['transaction_id'];
		$creditEntry->params = $data['params'];

		try
		{
			// Insert the object into the passbook table.
			$result = Factory::getDbo()->insertObject('#__tjvendors_passbook', $creditEntry);
		}
		catch (Exception $e)
		{
			Factory::getApplication()->enqueueMessage(Text::_('COM_TJVENDORS_DB_EXCEPTION_WARNING_MESSAGE'), 'error');
		}

		if (empty($result))
		{
			return false;
		}

		if ($result)
		{
			$this->updatingCreditData($data);
		}
	}

	/**
	 * Method to change payout status
	 *
	 * @param   integer  $payout_id   data of payout
	 *
	 * @param   integer  $paidUnpaid  payout status
	 *
	 * @return   boolean
	 *
	 * @since    1.6
	 */
	public function changePayoutStatus($payout_id, $paidUnpaid)
	{
		$object = new stdClass;

		// Must be a valid primary key value.
		$object->id = $payout_id;
		$object->status = $paidUnpaid;

		try
		{
			// Update their details in the users table using id as the primary key.
			$result = Factory::getDbo()->updateObject('#__tjvendors_passbook', $object, 'id');

			return true;
		}
		catch (Exception $e)
		{
			Factory::getApplication()->enqueueMessage(Text::_('COM_TJVENDORS_DB_EXCEPTION_WARNING_MESSAGE'), 'error');
		}
	}
}
