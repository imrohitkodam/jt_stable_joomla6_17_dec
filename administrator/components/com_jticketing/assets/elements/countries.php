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
use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;

/**
 * Class for get html select box  for countries
 *
 * @package     JTicketing
 * @subpackage  component
 * @since       1.0
 */
class JFormFieldCountries extends FormField
{
	public $TjGeoHelper;
	/**
	 * Get html select box  for countries
	 *
	 * @return  html select box
	 *
	 * @since   1.0
	 */
	public function getInput()
	{
		return $this->fetchElement(
			$this->name,
			$this->value,
			$this->element,
			isset($this->options['control']) ? $this->options['control'] : ''
		);
	}

	/**
	 * Get country data
	 *
	 * @param   string  $name          name of element
	 * @param   string  $value         value of element
	 * @param   string  &$node         node
	 * @param   string  $control_name  control name
	 *
	 * @return  array country list
	 *
	 * @since   1.0
	 */
	public function fetchElement($name, $value, &$node, $control_name)
	{
		$TjGeoHelper = JPATH_ROOT . '/components/com_tjfields/helpers/geo.php';

		if (!class_exists('TjGeoHelper'))
		{
			JLoader::register('TjGeoHelper', $TjGeoHelper);
			JLoader::load('TjGeoHelper');
		}

		$this->TjGeoHelper = new TjGeoHelper;
		$countries         = $this->TjGeoHelper->getCountryList();
		$options           = array();

		foreach ($countries as $country)
		{
			$options[] = HTMLHelper::_('select.option', $country['id'], $country['country']);
		}

		$fieldName = $name;
		$class = (JVERSION >= '4.0.0') ? 'form-select' : 'inputbox';

		return HTMLHelper::_('select.genericlist', $options, $fieldName, 'class="' . $class . ' required"', 'value', 'text', $value, $control_name . $name);
	}

	/**
	 * Get tooltip of element
	 *
	 * @param   string  $label         name of element
	 * @param   string  $description   description
	 * @param   string  &$node         node
	 * @param   string  $control_name  control name
	 * @param   string  $name          name of element
	 *
	 * @return  null
	 *
	 * @since   1.0
	 */
	public function fetchTooltip($label, $description, &$node, $control_name, $name)
	{
		return null;
	}
}
