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
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Form\Field\TextareaField;
use Joomla\CMS\Component\ComponentHelper;

HTMLHelper::_('behavior.formvalidator');
$document = Factory::getDocument();

/**
 * Class for mapping fields for cb to fill in billing form
 *
 * @package     JTicketing
 * @subpackage  component
 * @since       1.0
 */
class JFormFieldcbfieldmapping extends TextareaField
{
	protected $type = 'cbfieldmapping';

	/**
	 * Function to get input
	 *
	 * @return  array
	 *
	 * @since  1.0.0
	 */
	public function getInput()
	{
		return $textarea = $this->fetchElement(
			$this->name,
			$this->value, $this->element,
			isset($this->options['control']) ? $this->options['control'] : ''
		);
	}

	protected $name = 'cb_fieldmap';

	/**
	 * Function to fetch element
	 *
	 * @param   array  $name          name
	 * @param   array  $value         value
	 * @param   array  &$node         node
	 * @param   array  $control_name  control_name
	 *
	 * @return  array
	 *
	 * @since  1.0.0
	 */
	public function fetchElement($name, $value, &$node, $control_name)
	{
		$rows = $node->attributes()->rows;
		$cols = $node->attributes()->cols;
		$class = ($node->attributes('class') ? 'class="' . $node->attributes('class') . '"' : 'class="text_area"');

		// To render field which already saved in db
		$fieldvalue = trim($this->renderedfield());

		// For first time installation check value or textarea is empty
		if (($fieldvalue == ''))
		{
			$fieldvalue = 'firstname=name' . "\n";
			$fieldvalue .= 'lastname=lastname ' . "\n";
			$fieldvalue .= 'user_email=email' . "\n";
		}

		$fieldavi = 'firstname=name' . "\n";
		$fieldavi .= 'lastname=lastname' . "\n";
		$fieldavi .= 'address=' . "\n";
		$fieldavi .= 'address2=' . "\n";
		$fieldavi .= 'city=' . "\n";
		$fieldavi .= 'zipcode=' . "\n";
		$fieldavi .= 'phone=' . "\n";
		$fieldavi .= 'website_address=' . "\n";
		$fieldavi .= 'user_email=email' . "\n";

	$html = '<textarea name="' . $control_name . $name . '" cols="' . $cols
			. '" rows="' . $rows . '" ' . $class . ' id="' . $control_name . $name . '" >' . $fieldvalue . '</textarea>';

	// Joomla 6: JVERSION check removed
		if (false) // Legacy < '3.0.0')
	{
		$html .= '<span style="float:left;">  ' . Text::_('COM_JTICKETING_FIELDS_CB') . ':</span>';
	}
	else
	{
		$html .= '  ' . Text::_('COM_JTICKETING_FIELDS_CB') . ':';
	}

	return $html .= '<textarea  cols="' . $cols . '" rows="' . $rows . '" ' . $class . ' disabled="disabled" >' . $fieldavi . '</textarea>';
	}

	/**
	 * Function to render field
	 *
	 * @return  array
	 *
	 * @since  1.0.0
	 */
	public function renderedfield()
	{
		$params = ComponentHelper::getParams('com_jticketing');
		$mapping = trim($params->get('cb_fieldmap', '') ? $params->get('cb_fieldmap', '') : '');
		$field_explode = explode('\n', $mapping);
		$fieldvalue = '';

		// Check value exist in array
		if (isset($mapping))
		{
			foreach ($field_explode as $field)
			{
				$fieldvalue .= $field . "\n";
			}
		}

		return $fieldvalue;
	}
}
