<?php
/**
 * @package     JTicketing
 * @subpackage  com_jticketing
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Language\Text;
use Joomla\String\StringHelper;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Component\ComponentHelper;

if (file_exists(JPATH_SITE . '/components/com_tjvendors/helpers/fronthelper.php')) { require_once JPATH_SITE . '/components/com_tjvendors/helpers/fronthelper.php'; }
if (file_exists(JPATH_ADMINISTRATOR . '/components/com_tjvendors/tables/vendorclientxref.php')) { require_once JPATH_ADMINISTRATOR . '/components/com_tjvendors/tables/vendorclientxref.php'; }

/**
 * JTicketing Couponform model
 *
 * @since  2.4.0
 */
class JticketingModelCouponform extends AdminModel
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @since   2.4.0
	 */
	public function __construct($config = array())
	{
		$config['event_after_delete'] = 'onAfterJtCouponDelete';
		$config['event_change_state'] = 'onAfterJtCouponChangeState';

		parent::__construct($config);
	}

	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param   string  $type    The table type to instantiate
	 * @param   string  $prefix  A prefix for the table class name. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  boolean|JTable   A database object
	 *
	 * @since  2.4.0
	 */
	public function getTable($type = 'Coupon', $prefix = 'JticketingTable', $config = array())
	{
		return Table::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to get the coupon form.
	 *
	 * The base form is loaded from XML and then an event is fired
	 * for users plugins to extend the form with extra fields.
	 *
	 * @param   array    $data      An optional array of data for the form to interogate.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  boolean|JForm  A JForm object on success, false on failure
	 *
	 * @Since 2.4.0
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Check admin and load admin form in case of admin venue form
		if (Factory::getApplication()->isClient('administrator'))
		{
			$form = $this->loadForm('com_jticketing.couponform', 'coupon', array('control' => 'jform', 'load_data' => $loadData));
		}
		else
		{
			$form = $this->loadForm('com_jticketing.couponform', 'couponform', array('control' => 'jform', 'load_data' => $loadData));
		}

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  mixed  The data for the form.
	 *
	 * @Since 2.4.0
	 */
	protected function loadFormData()
	{
		// Initialise variables.
		$app = Factory::getApplication();

		// Check admin and load admin form in case of admin venue form
		if ($app->isClient('administrator'))
		{
			// Check the session for previously entered form data.
			$data = $app->getUserState('com_jticketing.edit.coupon.data', array());
		}
		else
		{
			$data = $app->getUserState('com_jticketing.edit.couponform.data', array());
		}

		if (empty($data))
		{
			$data = $this->getItem();
		}

		if (!empty($data->event_ids))
		{
			$data->event_ids = explode(',', $data->event_ids);
		}

		return $data;
	}

	/**
	 * Method to save the coupon form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  mixed  The user id on success, false on failure.
	 *
	 * @since   2.4.0
	 * @throws  \Exception
	 */
	public function save($data)
	{
		$authorised = false;
		
		if (empty($data['valid_to']))
		{
			$data['valid_to'] = null;
		}

		if (empty($data['id']))
		{
			$authorised = $this->canAdd($data);
		}
		else
		{
			$authorised = $this->canEdit($data);
		}

		if (!$authorised)
		{
			$this->setError(Text::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED'));

			return false;
		}

		$app = Factory::getApplication();

		if (empty($data['created_by']))
		{
			$data['created_by'] = Factory::getUser()->id;
		}

		// Check logged in user is vendor.
		$tjvendorFrontHelper = new TjvendorFrontHelper;
		$isVendor            = $tjvendorFrontHelper->checkVendor($data['created_by'], 'com_jticketing');

		$vendorXrefTable = Table::getInstance('vendorclientxref', 'TjvendorsTable', array());
		$vendorXrefTable->load(array('vendor_id' => $isVendor, 'client' => 'com_jticketing'));
		$isVendorApproved = $vendorXrefTable->approved;
		$this->params     = ComponentHelper::getParams('com_jticketing');
		$silentVendor     = $this->params->get('silent_vendor', 0, 'INTEGER');

		if ($isVendor && $silentVendor)
		{
			$isVendorApproved = 1;
		}

		if ($app->isClient('site') && empty($data['vendor_id']) && (!$isVendor || $isVendorApproved != 1))
		{
			$this->setError(Text::_('JERROR_ALERTNOAUTHOR'));

			return false;
		}

		if ($app->isClient('site'))
		{
			$data['vendor_id'] = $isVendor;
		}

		// Check if vendor selected All Events options, then the coupon will apply to all event which are created by that vendor only

		if (empty($data['event_ids']))
		{
			$data['event_ids'] = array();
		}

		if (!isset($data['valid_from']) && !isset($data['valid_to']))
		{
			$data['valid_from'] = Factory::getDate()->toSql();
		}

		if (in_array(0, $data['event_ids']))
		{
			$eventId = '';
		}
		else
		{
			$eventId = (count($data['event_ids']) > 1) ? implode(',', $data['event_ids']) : $data['event_ids'][0];
		}

		$data['event_ids'] = StringHelper::trim($eventId, ',');
		unset($data['checked_out']);
		unset($data['checked_out_time']);

		$nullableFields = ['valid_from', 'valid_to'];

		foreach ($nullableFields as $field) {
			if (empty($data[$field])) {
				$data[$field] = null;
			}
		}

		if (parent::save($data))
		{
			PluginHelper::importPlugin('jticketing');
			$id               = (int) $this->getState($this->getName() . '.id');
			$data['couponId'] = $id;

			if ($data['id'] != 0)
			{
				Factory::getApplication()->triggerEvent('onAfterJtCouponSave', array($data, false));
			}
			else
			{
				Factory::getApplication()->triggerEvent('onAfterJtCouponSave', array($data, true));
			}

			return true;
		}

		return false;
	}

	/**
	 * Method to validate the coupon form data.
	 *
	 * @param   \JForm  $form   The form to validate against.
	 * @param   array   $data   The data to validate.
	 * @param   string  $group  The name of the field group to validate.
	 *
	 * @return  array|boolean  Array of filtered data if valid, false otherwise.
	 *
	 * @since   2.4.0
	 */
	public function validate($form, $data, $group = null)
	{
		$return = true;
		$couponValidFrom = Factory::getDate('now', 'UTC')->toUnix();

		if (!empty($data['valid_from']))
		{
			$couponValidFrom = Factory::getDate($data['valid_from'], 'UTC')->toUnix();
		}
		else
		{
			unset($data['valid_from']);
		}

		if (!empty($data['valid_to']))
		{
			$couponValidTo   = Factory::getDate($data['valid_to'], 'UTC')->toUnix();
		}
		else
		{
			unset($data['valid_to']);
		}

		// Check Coupon name validation
		$data['name'] = StringHelper::trim($data['name']);

		// Check Coupon discount validation
		$data['value'] = StringHelper::trim($data['value']);

		// Check Coupon code validation
		$data['code'] = StringHelper::trim($data['code']);

		// Checking for new coupon creation
		if ($data['id'] == 0)
		{
			if ($this->isUniqueCouponCode($data['code']))
			{
				$this->setError(Text::_('COM_JTICKETING_COUPON_SAVE_CODE_DUPLICATE_WARNING'));
				$return = false;
			}
		}

		// Check Coupon limit and max_per_user
		if (!empty($data['max_per_user']) && $data['max_per_user'] < 0)
		{
			$this->setError(Text::_('COM_JTICKETING_MIN_AMT_SHOULD_GREATER_MSG'));
			$return = false;
		}

		if ($data['max_per_user'] !== 0 && ((int) $data['max_per_user'] > (int) $data['limit']))
		{
			$this->setError(Text::_('COM_JTICKETING_COUPON_SAVE_CODE_LIMIT_WARNING'));
			$return = false;
		}

		// Valid Till date should be greater than Valid From
		if ($couponValidFrom && $couponValidTo)
		{
			if ($couponValidFrom > $couponValidTo)
			{
				$this->setError(Text::_('COM_JTICKETING_COUPON_SAVE_VALID_DATE_WARNING'));
				$return = false;
			}
		}

		$data = parent::validate($form, $data, $group);

		return (!$return) ? false : $data;
	}

	/**
	 * Method to check whether coupon code is taken.
	 *
	 * @param   String  $couponCode  Coupon Code
	 *
	 * @return  boolean  Coupon code is duplicate or not
	 *
	 * @since   2.4.0
	 */
	public function isUniqueCouponCode($couponCode)
	{
		$table = JT::table('coupon');

		return $table->load(array('code' => $couponCode));
	}

	/**
	 * Method to test whether a record can be deleted.
	 *
	 * @param   JticketingTableCoupon  $couponObject  A coupon table object
	 *
	 * @return  boolean  True if allowed to delete the record. Defaults to the permission for the component.
	 *
	 * @since   2.5.0
	 */
	protected function canDelete($couponObject)
	{
		$coupon = JT::coupon($couponObject->id);
		$user = Factory::getUser();

		return $user->authorise('coupon.delete', $this->option) && ( $coupon->isCreator() || $coupon->isVendor() || $user->authorise('core.manage'));
	}

	/**
	 * Method to test whether a record can be save.
	 *
	 * @param   stdClass  $coupon  A coupon data
	 *
	 * @return  boolean  True if allowed to delete the record. Defaults to the permission for the component.
	 *
	 * @since   2.5.0
	 */
	protected function canEdit($coupon)
	{
		$coupon = JT::coupon($coupon['id']);

		if (!$coupon->id)
		{
			return false;
		}

		$user = Factory::getUser();

		$editCoupon = $user->authorise('coupon.edit', $this->option);
		$editOwnCoupon = $user->authorise('coupon.edit.own', $this->option);

		if (!$editCoupon)
		{
			// Check for own coupon edit
			if ($editOwnCoupon)
			{
				// Check for owner
				return $coupon->isCreator() || $coupon->isVendor();
			}

			return false;
		}

		// Edit coupon is allowed check if he is the owner/vendor/super user
		return $coupon->isCreator() || $coupon->isVendor() || $user->authorise('core.manage');
	}

	/**
	 * Method to test whether a record can be save.
	 *
	 * @return  boolean  True if allowed to delete the record. Defaults to the permission for the component.
	 *
	 * @since   2.5.0
	 */
	protected function canAdd()
	{
		return Factory::getUser()->authorise('coupon.create', $this->option);
	}
}
