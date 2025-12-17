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
use Joomla\CMS\Language\Text;

/**
 * JFormFieldDateformat class
 *
 * @package     JTicketing
 * @subpackage  component
 * @since       1.0
 */
class JFormFieldDateformat extends FormField
{
	/**
	 * The form field type.
	 *
	 * @var   string
	 * @since 1.6
	 */
	protected $type = 'dateformat';

	/**
	 * Fiedd to decide if options are being loaded externally and from xml
	 *
	 * @var   integer
	 * @since 2.2
	 */
	protected $loadExternally = 0;

	/**
	 * Method to get the field input markup.
	 *
	 * @since  1.6
	 *
	 * @return   array  The field input markup
	 */
	public function getInput ()
	{
		return $this->fetchElement(
			$this->name,
			$this->value,
			$this->element,
			isset($this->options['control']) ? $this->options['control'] : ''
		);
	}

	/**
	 * Method fetchElement
	 *
	 * @param   string  $name          name of element
	 * @param   string  $value         value of element
	 * @param   string  &$node         node
	 * @param   string  $control_name  control name
	 *
	 * @return  array  date format list
	 *
	 * @since   1.0
	 */
	public function fetchElement ($name, $value, &$node, $control_name)
	{
		$sqlGmtTimestamp = "2012-01-01 20:00:00";

		$dateFormat = array("Y-m-d H:i:s",
							"D, M d h:i A", "F j, Y, g:i a", "m.d.y", "j, n, Y", "h-i-s, j-m-y", "H:i:s"
							);

		foreach ($dateFormat as $date)
		{
			$options[] = HTMLHelper::_('select.option', $date, HTMLHelper::date(
					$sqlGmtTimestamp, $date, true
				)
			);
		}

		$options[] = HTMLHelper::_('select.option', 'custom', Text::_('COM_JTICKETING_DATE_FORMAT_CUSTOME'));
		$class = (JVERSION >= '4.0.0') ? 'form-select' : 'inputbox';

		return HTMLHelper::_('select.genericlist',  $options, $name, 'class="' . $class . '" ', 'value', 'text', $value, $control_name . $name);
	}
}
