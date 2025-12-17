<?php
/**
 * @package     TJVendors
 * @subpackage  com_tjvendors
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2023 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access.
defined('_JEXEC') or die();

use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\HTML\HTMLHelper;

require_once JPATH_LIBRARIES . '/techjoomla/tjmoney/tjmoney.php';
use Joomla\CMS\Form\Field\ListField;

/**
 * JFormFieldCurrencyList class
 *
 * @package     JTicketing
 * @subpackage  component
 * @since       1.0
 */

class JFormFieldCurrencyList extends ListField
{
	/**
	 * The form field type.
	 *
	 * @var   string
	 * @since 1.6
	 */
	protected $type = 'currencylist';

	/**
	 * Fiedd to decide if options are being loaded externally and from xml
	 *
	 * @var   integer
	 * @since 2.2
	 */
	protected $loadExternally = 0;

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return array An array of HTMLHelper options.
	 *
	 * @since   11.4
	 */
	protected function getOptions()
	{
		$options = array();
		$currencies = TjMoney::getCurrencies();

		if (!empty($currencies))
		{
			foreach ($currencies as $key => $currency)
			{
				$currencyCode    = $currency['alphabeticCode'];
				$currencyTitle   = $currency['currency'];

				$options[] = HTMLHelper::_('select.option', $currencyCode, $currencyTitle);
			}
		}

		return $options;
	}
}
