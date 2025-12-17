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
defined('_JEXEC') or die();

use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;

if (file_exists(JPATH_LIBRARIES . '/techjoomla/tjmoney/tjmoney.php')) { require_once JPATH_LIBRARIES . '/techjoomla/tjmoney/tjmoney.php'; }

FormHelper::loadFieldClass('list');

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
	 * @return array An array of JHtml options.
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
