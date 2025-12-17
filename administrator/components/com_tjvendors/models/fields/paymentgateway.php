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

defined('_JEXEC') or die();

use Joomla\CMS\Form\FormField;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Form\Field\ListField;

/**
 * Supports an HTML select list of courses
 *
 * @since  1.0.0
 */
class JFormFieldPaymentGateway extends ListField
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	1.6
	 */
	protected $type = 'paymentGateway';

	/**
	 * Fiedd to decide if options are being loaded externally and from xml
	 *
	 * @var		integer
	 * @since	2.2
	 */
	protected $loadExternally = 0;

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return	array		An array of HTMLHelper options.
	 *
	 * @since   11.4
	 */
	protected function getOptions()
	{
		$type = "payment";
		$input = Factory::getApplication()->getInput();
		$client = $input->get('client', '', 'STRING');
		$options = array();
		$options[] = HTMLHelper::_('select.option', '', Text::_('COM_TJVENDOR_PAYMENT_DETAILS_DEFAULT'));

		if (!empty($client))
		{
			$com_params = ComponentHelper::getParams($client);
			$gateways = (array) $com_params->get('gateways');

			if (!empty($gateways))
			{
				foreach ($gateways as $detail)
				{
					$options[] = HTMLHelper::_('select.option', $detail, $detail);
				}
			}
		}
		else
		{
			$gateways = PluginHelper::getPlugin($type, $plugin = null);

			foreach ($gateways as $detail)
			{
				$options[] = HTMLHelper::_('select.option', $detail->name, $detail->name);
			}
		}

		if (!$this->loadExternally)
		{
			// Merge any additional options in the XML definition.
			$options = array_merge(parent::getOptions(), $options);
		}

		return $options;
	}
}
