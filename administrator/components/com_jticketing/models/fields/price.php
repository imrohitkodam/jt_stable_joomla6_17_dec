<?php
/**
 * @package     JTicketing
 * @subpackage  com_jticketing
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\TextField;
use Joomla\CMS\Component\ComponentHelper;

if (file_exists(JPATH_LIBRARIES . '/techjoomla/tjmoney/tjmoney.php')) { require_once JPATH_LIBRARIES . '/techjoomla/tjmoney/tjmoney.php'; }

/**
 * Supports an HTML select list of categories
 *
 * @since  1.6
 */
class JFormFieldPrice extends TextField
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	1.6
	 */
	protected $type = 'price';

	/**
	 * Method to get the field input markup.
	 *
	 * @return	string	The field input markup.
	 *
	 * @since	1.6
	 */
	protected function getInput()
	{
		$input = parent::getInput();
		$app = Factory::getApplication();
		$params = ComponentHelper::getParams('com_jticketing');

		$currencyCode = $params->get('currency', '', 'STRING');
		$currencyCodeOrSymbol = $params->get('currency_code_or_symbol', 'code', 'STRING');

		$tjCurrency   = new TjMoney($currencyCode);
		$symbolOrCode = $tjCurrency->getSymbol();

		if ($currencyCodeOrSymbol === 'code')
		{
			$symbolOrCode = $tjCurrency->getCode();
		}

		// Initialize variables.
		$html = array();

		if ($app->isClient("administrator"))
		{
			$html[] = "<div class='input-group'>";
			$html[] = $input;

			if ($symbolOrCode)
			{
				$html[] = "<span class='input-group-text'>" . $symbolOrCode . "</span></div>";
			}
		}
		else
		{
			$html[] = "<div class='input-group'>";
			$html[] = $input;

			if ($symbolOrCode)
			{
				$html[] = "<span class='input-group-text'>" . $symbolOrCode . "</span></div>";
			}
		}

		return implode($html);
	}
}
