<?php
/**
 * @package     JTicketing
 * @subpackage  com_jticketing
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;

$lang = Factory::getLanguage();
$lang->load('plug_jticketing_tax_default', JPATH_ADMINISTRATOR);

/**
 * Tax plugin
 *
 * @since  1.0.0
 */
class PlgJticketingtaxjticketing_Tax_Default extends CMSPlugin
{
	/**
	 * function to add tax
	 *
	 * @param   integer  $amt  integer
	 *
	 * @deprecated 2.5.0 use onJtCalculateTax
	 *
	 * @return  Object
	 */
	public function onJtAddTax($amt)
	{
		$tax_per   = $this->params->get('tax_per');
		$tax_value = ($tax_per * $amt) / 100;

		$return           = new Stdclass;
		$return->percent  = $tax_per . "%";
		$return->taxvalue = $tax_value;

		return $return;
	}

	/**
	 * function to add tax
	 *
	 * @param   JTicketingOrder  $order  order object with all necessary data
	 *
	 * @return  stdClass Object with tax details and the breakups
	 */
	public function onJtCalculateTax(JTicketingOrder $order)
	{
		$return = new Stdclass;

		$taxPercentage = (float) $this->params->get('tax_per', 0);

		try
		{
			if ($taxPercentage)
			{
				$taxValue = ($taxPercentage * $order->getAmount(false)) / 100;

				$return->plugin = "TAX";
				$return->total = $taxValue;
				$return->breakup = array();

				$breakup = new stdClass;
				$breakup->value = $taxValue;
				$breakup->percentage = $taxPercentage;
				$breakup->text = 'TAX';
				array_push($return->breakup, $breakup);
			}
		}
		catch (Exception $e)
		{
			// Do nothing on the exception Just avoid the termination of code
		}

		return $return;
	}
}
