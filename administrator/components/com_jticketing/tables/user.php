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
defined('_JEXEC') or die;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Component\ComponentHelper;

/**
 * question Table class
 *
 * @since  1.5
 */
class JticketingTableUser extends Table
{
	/**
	 * Constructor
	 *
	 * @param   JDatabaseDriver  &$db  Database object
	 *
	 * @since  1.5
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__jticketing_users', 'id', $db);
	}

	/**
	 * Overloaded check function
	 *
	 * @return bool
	 */
	public function check()
	{
		$params               = ComponentHelper::getParams('com_jticketing');
		$showSelectedFields   = $params->get('show_selected_fields', '', 'STRING');
		$billingFieldsToShow = $params->get('billing_info_field', array(), 'ARRAY');
		$requiredFieldsArray  = array('firstname', 'country_mobile_code', 'user_email');

		foreach ($requiredFieldsArray as $rf)
		{
			if ($showSelectedFields && !empty($billingFieldsToShow))
			{
				if (!in_array($this->{$rf}, $billingFieldsToShow) && trim($this->{$rf}) == '')
				{
					$this->setError(Text::sprintf('COM_JTICKETING_WARNING_PROVIDE_VALID_FIELD', $rf));

					return false;
				}
			}
			else
			{
				if (trim($this->{$rf}) == '')
				{
					$this->setError(Text::sprintf('COM_JTICKETING_WARNING_PROVIDE_VALID_FIELD', $rf));

					return false;
				}
			}
		}

		return parent::check();
	}
}
