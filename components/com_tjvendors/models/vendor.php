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
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Table\Table;

$fronthelperPath = JPATH_SITE . '/components/com_tjvendors/helpers/fronthelper.php';
if (file_exists($fronthelperPath)) {
	require_once $fronthelperPath;
}

$tjvendorsPath = JPATH_ADMINISTRATOR . '/components/com_tjvendors/helpers/tjvendors.php';
if (file_exists($tjvendorsPath)) {
	require_once $tjvendorsPath;
}
include_once JPATH_SITE . '/components/com_tjvendors/includes/tjvendors.php';

/**
 * Tjvendors model.
 *
 * @since  1.6
 */
class TjvendorsModelVendor extends AdminModel
{
	/**
	 * @var    string  client data
	 * @since  1.6
	 */
	private $vendor_client = '';

	/**
	 * @var      string    The prefix to use with controller messages.
	 * @since    1.6
	 */
	protected $text_prefix = 'COM_TJVENDORS';

	/**
	 * @var     string      Alias to manage history control
	 * @since   3.2
	 */
	public $typeAlias = 'com_tjvendors.vendor';

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
	public function getTable($type = 'Vendor', $prefix = 'TjvendorsTable', $config = array())
	{
		// Load tables to fix - unable to load the vendors data using the model object,
		// When it is created outside the tjvendors component
		Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_tjvendors/tables');

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
			'com_tjvendors.vendor', 'vendor',
			array('control' => 'jform',
				'load_data' => $loadData,
			)
		);

		if (empty($form))
		{
			return false;
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
		$app = Factory::getApplication();
		$input = $app->getInput();
		$client = $input->get('client', '', 'STRING');

		$data = Factory::getApplication()->getUserState('com_tjvendors.edit.vendor.data', array());

		if (empty($data))
		{
			if (empty($this->item))
			{
				$this->item = $this->getItem();
			}

			if (!empty($this->item->vendor_id))
			{
				if (!empty($client))
				{
					$tjvendorFrontHelper = new TjvendorFrontHelper;
					$gatewayDetails = $tjvendorFrontHelper->getPaymentDetails($this->item->vendor_id, $client);

					if (!empty($gatewayDetails) && !empty($gatewayDetails->params))
					{
						$this->item->payment_gateway = json_decode($gatewayDetails->params)->payment_gateway;
					}
				}
			}

			$data = $this->item;
		}

		// Joomla 6 requires non-null data for preprocessForm
		return $this->item ?? new \stdClass();
	}

	/**
	 * Method to get a single record.
	 *
	 * @param   integer  $pk  The id of the primary key.
	 *
	 * @return  mixed    Object on success, false on failure.
	 *
	 * @since    1.6
	 */
	public function getItem($pk = null)
	{
		$item = parent::getItem($pk);

		Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_tjvendors/tables');
		$vendorXref = Table::getInstance('VendorClientXref', 'TjvendorsTable');
		$vendorXref->load(array('vendor_id' => $item->vendor_id));
		$item->params = $vendorXref->params;

		if (isset($item->country))
		{
			$country = TJVendors::utilities()->getCountry((int) $item->country);
			$item->country_name = $country->country;
		}

		if (isset($item->region))
		{
			$region = TJVendors::utilities()->getRegion((int) $item->region);
			$item->region_name = $region->region;
		}

		if (!empty($item->other_city))
		{
			$item->city_name = (property_exists($item, 'other_city') ? $item->other_city : '');
		}
		else
		{
			$city = TJVendors::utilities()->getCity((int) $item->city);
			$item->city_name = (property_exists($city, 'city') ? $city->city : '');
		}

		return $item;
	}

	/**
	 * Method for save vendor information
	 *
	 * @param   Integer  $vendor_id        used for inserting data for that vendor
	 *
	 * @param   Integer  $payment_gateway  used for inserting data for that vendor
	 *
	 * @param   Integer  $paymentDetails   payment details
	 *
	 * @return  boolean
	 */
	public function addMultiVendor($vendor_id, $payment_gateway, $paymentDetails)
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('max(' . $db->quoteName('id') . ')');
		$query->from($db->quoteName('#__vendor_client_xref'));
		$db->setQuery($query);
		$res = $db->loadColumn()[0] ?? null;
		$fields = array($db->quoteName('vendor_id') . ' = ' . $db->quote($vendor_id),
		$db->quoteName('payment_gateway') . ' = ' . $db->quote($payment_gateway),
		$db->quoteName('params') . ' = ' . $db->quote($paymentDetails),
		);

		// Conditions for which records should be updated.
		$conditions = array($db->quoteName('id') . ' = ' . $res);

		$query->update($db->quoteName('#__vendor_client_xref'))->set($fields)->where($conditions);
		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (Exception $e)
		{
			Factory::getApplication()->enqueueMessage(Text::_('COM_TJVENDORS_DB_EXCEPTION_WARNING_MESSAGE'), 'error');
		}

		return true;
	}

	/**
	 * Method to check duplicate user.
	 *
	 * @param   integer  $user_id  user name.
	 *
	 * @return   array|boolean
	 *
	 * @since    1.6
	 */
	public function checkDuplicateUser($user_id)
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from($db->quoteName('#__tjvendors_vendors'));

		if (!empty($user_id))
		{
			$query->where($db->quoteName('user_id') . ' = ' . $user_id);
		}

		$db->setQuery($query);

		try
		{
			$rows = $db->loadAssoc();
		}
		catch (Exception $e)
		{
			Factory::getApplication()->enqueueMessage(Text::_('COM_TJVENDORS_DB_EXCEPTION_WARNING_MESSAGE'), 'error');
		}

		if (empty($rows))
		{
			return false;
		}

		if ($rows)
		{
			return $rows;
		}
	}

	/**
	 * Method to generate payment gateway fields.
	 *
	 * @param   string  $payment_gateway  payment gateway.
	 * @param   string  $parentTag        To load payment form below the gateway list.
	 *
	 * @return   array result
	 *
	 * @since    1.6
	 */
	public function generateGatewayFields($payment_gateway, $parentTag)
	{
		$app = Factory::getApplication();
		$client = $app->getUserStateFromRequest('vendor.client', 'vendor.client');
		$vendor_id = $app->getUserStateFromRequest('vendor.vendor_id', 'vendor.vendor_id');
		$tjvendorFrontHelper = new TjvendorFrontHelper;
		$vendorDetails = $tjvendorFrontHelper->getPaymentDetails($vendor_id, $client);
		$params = array();

		if (!empty($vendorDetails->params))
		{
			$params = json_decode($vendorDetails->params)->payment_gateway;
		}

		$form_path = JPATH_SITE . '/plugins/payment/' . $payment_gateway . '/' . $payment_gateway . '/form/' . $payment_gateway . '.xml';
		$test = $payment_gateway . '_' . 'plugin';

		if (jFile::exists($form_path))
		{
			$form = Form::getInstance($test, $form_path, array('control' => $parentTag));

			if ($vendor_id)
			{
				$paymentDetails = array();

				if (!empty($params))
				{
					foreach ($params as $key => $param)
					{
						foreach ($param as $key => $value)
						{
							if ($key != "payment_gateways" && $param->payment_gateways == $payment_gateway)
							{
								{
									$form->setValue($key, '', $value);
								}
							}
						}
					}
				}
			}

			$fieldSet = $form->getFieldset('payment_gateway');
			$html = array();

			foreach ($fieldSet as $field)
			{
				if ($app->isClient('administrator'))
				{
					if (JVERSION < '4.0.0')
					{
						$html[] = $field->renderField();
					}
					else
					{
						$paymentGatewayHtml = $field->renderField();
						$paymentGatewayHtml = str_replace('control-group', 'form-group row', $paymentGatewayHtml);
						$paymentGatewayHtml = str_replace('control-label', 'form-label col-md-3', $paymentGatewayHtml);
						$paymentGatewayHtml = str_replace('controls', 'col-md-9', $paymentGatewayHtml);
						$html[] = $paymentGatewayHtml;
					}
					
				}
				else
				{
					// To convert frontend subform in Bootstrap 3
					$tempForm = str_replace('control-group', 'col-xs-12 col-sm-6 form-group form_'
					. $field->class, $field->renderField(array('hiddenLabel' => false))
					);
					$col = str_replace('control-label', 'col-xs-12 col-md-3', $tempForm);
					$col = str_replace('controls', 'col-xs-12 col-md-8', $col);
					$html[] = $col;
				}
			}

			return $html;
		}

		return false;
	}

	/**
	 * Method for save user specific %commission, flat commission, client
	 *
	 * @param   Array  $data  Data
	 *
	 * @return boolean
	 */
	public function save($data)
	{
		$db = Factory::getDbo();
		$user = Factory::getUser();
		$app = Factory::getApplication();

		// Check if the user is already associated with a vendor
		$query = $db->getQuery(true)
			->select($db->quoteName('vendor_id'))
			->from($db->quoteName('#__tjvendors_vendors'))
			->where($db->quoteName('user_id') . ' = ' . (int) $data['user_id']);
		$db->setQuery($query);
		$existingVendorId = (int) ($db->loadColumn()[0] ?? null);

		if (!empty($existingVendorId) && (empty($data['vendor_id']) || $data['vendor_id'] != $existingVendorId))
		{
			// If the user is already associated with a vendor, throw an error
			$app->enqueueMessage(Text::_('COM_TJVENDORS_ERROR_USER_ALREADY_VENDOR'), 'error');
			return false;
		}

		$table    = $this->getTable();
		$db       = Factory::getDbo();
		$user     = Factory::getUser();
		$app      = Factory::getApplication();
		$input    = $app->getInput();
		$layout   = $input->get('layout', '', 'STRING');
		$xrefData = array();
		$tjvendorFrontHelper = new TjvendorFrontHelper;
		$tjVendorsParams = ComponentHelper::getParams('com_tjvendors');
		$vendorApprovalEnabled = $tjVendorsParams->get('vendor_approval', 0, 'INT');

		$vendorEventPath = JPATH_SITE . '/components/com_tjvendors/events/vendor.php';
		if (file_exists($vendorEventPath)) {
			require_once $vendorEventPath;
		}
		$tjvendorsTriggerVendor = new TjvendorsTriggerVendor;

		if (!$user->authorise('core.admin'))
		{
			$vendor_id = $tjvendorFrontHelper->getvendor();

			if ($vendor_id)
			{
				if ($user->authorise('core.edit.own', 'com_tjvendors'))
				{
					if ($user->id == $data['user_id'] && $vendor_id == $data['vendor_id'])
					{
						$authorised = true;
					}
					else
					{
						$authorised = false;
					}
				}
				else
				{
					return false;
				}
			}
			else
			{
				$authorised = $user->authorise('core.create', 'com_tjvendors');
			}

			if ($authorised !== true)
			{
				throw new Exception(Text::_('JERROR_ALERTNOAUTHOR'), 403);
			}
		}

		if (isset($data['payment_gateway']))
		{
			$paymentGatway['payment_gateway'] = $data['payment_gateway'];
			$xrefData['params'] = json_encode($paymentGatway);
			$data['params'] = '';
		}
		else
		{
			$xrefData['params'] = '';
		}

		if (empty($data['city']))
		{
			$data['other_city'] = '';
		}

		// To check if editing in registration form
		if (!empty($data['vendor_id']))
		{
			$data['modified_time'] = Factory::getDate('now')->toSQL();
			$data['modified_by']   = Factory::getUser()->id;
			$table->save($data);
			$tjvendorFrontHelper = new TjvendorFrontHelper;
			$vendorClients = $tjvendorFrontHelper->getClientsForVendor($data['vendor_id']);
			$count = 0;

			// Check if the vendor exists for another client
			foreach ($vendorClients as $client)
			{
				if ($client == $data['vendor_client'])
				{
					$count++;
				}
			}

			// If no client present then vendor registers for first time  for a client
			if ($count == 0)
			{
				$client_entry = new stdClass;
				$client_entry->client = $data['vendor_client'];
				$client_entry->vendor_id = $data['vendor_id'];
				$client_entry->params = $xrefData['params'];

				if ($vendorApprovalEnabled == 0 && $data['approved'] == '')
				{
					$data['approved'] = 1;
				}

				$client_entry->approved = $data['approved'];

				// Insert the object into the user profile table.
				Factory::getDbo()->insertObject('#__vendor_client_xref', $client_entry);
				$tjvendorsTriggerVendor->onAfterVendorSave($data, true);

				// Plugin trigger
				PluginHelper::importPlugin('tjvendors');
				Factory::getApplication()->triggerEvent('onAfterTjVendorVendorSave', array($data));

				return true;
			}
			else
			{
				$query = $db->getQuery(true);

				// Fields to update.
				if (isset($data['params']))
				{
					$fields = array(
						$db->quoteName('params') . ' = ' . $db->quote($xrefData['params']),
					);
				}
				else
				{
					$fields = array(
					$db->quoteName('approved') . ' = ' . $db->quote($data['approved']),
					);
				}

				// The vendor information is updated for that client
					$conditions = array(
					$db->quoteName('vendor_id') . ' = ' . $db->quote($data['vendor_id']),
					$db->quoteName('client') . ' = ' . $db->quote($data['vendor_client']),
				);

				$query->update($db->quoteName('#__vendor_client_xref'))->set($fields)->where($conditions);
				$db->setQuery($query);
				$result = $db->execute();

				/* Trigger on Vendor Edit / update*/
				$tjvendorsTriggerVendor->onAfterVendorSave($data, false);

				// Plugin trigger
				PluginHelper::importPlugin('tjvendors');
				Factory::getApplication()->triggerEvent('onAfterTjVendorVendorSave', array($data));

				return true;
			}
		}
		else
		{
			$data['created_time'] = Factory::getDate('now')->toSQL();
			$data['created_by']   = Factory::getUser()->id;

			// Vendor registers for the first time for a client
			if ($table->save($data) === true)
			{
				$data['vendor_id'] = $table->vendor_id;

				if (!empty($data['vendor_client']))
				{
					$client_entry = new stdClass;
					$client_entry->client = $data['vendor_client'];
					$client_entry->vendor_id = $data['vendor_id'];
					$client_entry->params = $xrefData['params'];

					if ($vendorApprovalEnabled == 0 && $data['approved'] == '')
					{
						$data['approved'] = 1;
					}

					$client_entry->approved = !empty( $data['approved']) ? $data['approved'] : '';

					// Insert the object into the vendor_client_xref table.
					$result = Factory::getDbo()->insertObject('#__vendor_client_xref', $client_entry);
				}

				/* Send mail on vendor creation */
				$tjvendorsTriggerVendor->onAfterVendorSave($data, true);

				// Plugin trigger
				PluginHelper::importPlugin('tjvendors');
				Factory::getApplication()->triggerEvent('onAfterTjVendorVendorSave', array());

				return $data['vendor_id'];
			}

			return false;
		}

		return false;
	}

	/**
	 * Set the onject values
	 *
	 * @param   string  $client  client value
	 *
	 * @return mixed
	 */
	public function setClient($client)
	{
		$this->vendor_client = $client;
	}

	/**
	 * Get an client value
	 *
	 * @return mixed
	 */
	public function getClient()
	{
		return $this->vendor_client;
	}

	/**
	 * Method to format payment config json structure, As we are taking data in subform and saving it in params, format the JSON structure
	 *
	 * @param   array  $data            Jform processed data
	 * @param   array  $paymentDetails  Current data of payment gateway
	 *
	 * @return object The formatted json structure
	 */
	public function formatPaymentStructure($data, $paymentDetails)
	{
		$client = $this->getClient();
		$tjvendorFrontHelper = new TjvendorFrontHelper;
		$vendorDetails = $tjvendorFrontHelper->getPaymentDetails($data['vendor_id'], $client);

		// Object  of old payment gateway params
		$oldParams = json_decode($vendorDetails->params);

		if (is_string($data['gateway']))
		{
			$oldParams->{$data['gateway']} = $paymentDetails;
		}

		return $oldParams;
	}

	/**
	 * Get get vendor_id
	 *
	 * @param   integer  $vendorId  integer
	 * @param   string   $client    string like com_jgive
	 * @param   string   $currency  string like USD, EUR
	 *
	 * @return  Array
	 */
	public static function getPayableAmount($vendorId, $client = '', $currency = '')
	{
		$date              = Factory::getDate();
		$com_params        = ComponentHelper::getParams('com_tjvendors');
		$bulkPayoutStatus  = $com_params->get('bulk_payout');
		$payoutDayLimit  = $com_params->get('payout_limit_days', '0', 'INT');
		$payoutDateLimit = $date->modify("-" . $payoutDayLimit . " day");

		// Query to get the credit amount
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('SUM(credit) as credit');
		$query->select('SUM(debit) as debit');
		$query->select($db->quoteName('currency'));
		$query->select($db->quoteName('client'));
		$query->from($db->quoteName('#__tjvendors_passbook'));
		$query->where($db->quoteName('vendor_id') . ' = ' . (int) $vendorId);
		$query->where($db->quoteName('transaction_time') . ' < ' . $db->quote($payoutDateLimit));

		if (!empty($client))
		{
			$query->where($db->quoteName('client') . ' = ' . $db->quote($client));
		}

		if (!empty($currency))
		{
			$query->where($db->quoteName('currency') . ' = ' . $db->quote($currency));
		}

		$query->group($db->quoteName('currency'));
		$query->group($db->quoteName('client'));
		$db->setQuery($query);
		$credit = $db->loadAssocList();
		$payableAmount = array();

		if (empty($credit))
		{
			return $payableAmount;
		}

		foreach ($credit as $creditAmount)
		{
			$payableAmount[$creditAmount['client']][$creditAmount['currency']] = $creditAmount['credit'] - $creditAmount['debit'];
		}

		/*Array
		(
			[com_jgive] => Array
				(
					[EUR] => 2
					[USD] => 20
				)

			[com_jticketing] => Array
				(
					[EUR] => 4
					[USD] => 2
				)

		)*/

		return $payableAmount;
	}

	/**
	 * Method to get a vendor details by vendor xref Id
	 *
	 * @param   integer  $xrefId  The id of the primary key.
	 *
	 * @return  mixed    Object on success, false on failure.
	 *
	 * @since    1.3.3
	 */
	public function getDetailsByXrefId($xrefId)
	{
		if (empty($xrefId))
		{
			return false;
		}

		Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_tjvendors/tables');
		$vendorXref = Table::getInstance('VendorClientXref', 'TjvendorsTable');
		$vendorXref->load(array('id' => $xrefId));
		$item = parent::getItem($vendorXref->vendor_id);

		return $item;
	}

	/**
	 * Method to get a vendor details by vendor Id
	 *
	 * @param   integer  $id  The vendor id
	 *
	 * @return  mixed    Object on success, false on failure.
	 *
	 * @since    1.3.3
	 */
	public function getDetailsByVendorId($id)
	{
		if (empty($id))
		{
			return false;
		}

		Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_tjvendors/tables');
		$vendorDetails = Table::getInstance('Vendor', 'TjvendorsTable');
		$vendorDetails->load(array('vendor_id' => $id));

		return $vendorDetails;
	}
}
