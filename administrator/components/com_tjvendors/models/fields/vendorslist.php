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
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Form\Field\ListField;

if (JVERSION < '4.0.0')
{
	HTMLHelper::_('behavior.multiselect'); // only for list tables

}

/**
 * Supports an HTML select list of vendors
 *
 * @since  1.3.2
 */
class JFormFieldVendorsList extends ListField
{
	/**
	 * The form field type.
	 *
	 * @var   string
	 * @since 1.3.2
	 */
	protected $type = 'vendorslist';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return array An array of HTMLHelper options.
	 *
	 * @since  1.3.2
	 */
	protected function getOptions()
	{
		$lang = Factory::getLanguage();
		$lang->load('com_tjvendors', JPATH_ADMINISTRATOR);
		$options   = array();
		$options[] = HTMLHelper::_('select.option', '', Text::_('COM_TJVENDORS_VENDOR_SELECT_VENDOR'));

		// Get all vendors options, Param false to return option not all vendors
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->qn(array('v.vendor_id', 'v.vendor_title')))
			->from($db->qn('#__tjvendors_vendors', 'v'))
			->join('LEFT', $db->qn('#__vendor_client_xref', 'x') . 'ON (' . $db->qn('x.vendor_id') . ' = ' . $db->qn('v.vendor_id') . ')')
			->where($db->qn('x.client') . ' = ' . $db->quote($this->element['client']))
			->where($db->qn('x.state') . ' = ' . $db->quote('1'));
		$db->setQuery($query);
		$vendorsList = $db->loadAssocList();

		if (!empty($vendorsList))
		{
			foreach ($vendorsList as $key => $vendor)
			{
				$options[]  = HTMLHelper::_('select.option', htmlspecialchars($vendor['vendor_id']), htmlspecialchars($vendor['vendor_title']));
			}
		}

		return $options;
	}
}
