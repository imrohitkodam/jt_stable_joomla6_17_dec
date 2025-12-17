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
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

/**
 * render plugin selection of type online event
 *
 * @since  1.0
 */
class JFormFieldState extends FormField
{
	protected $type = 'State';

	/**
	 * Method to get the field input markup.
	 *
	 * @return	string	The field input markup.
	 *
	 * @since	1.6
	 */
	protected function getInput()
	{
		return self::fetchElement($this->name, $this->value, $this->element, $this->options['control']);
	}

	/**
	 * Returns html element select plugin
	 *
	 * @param   string  $name          Name of control
	 * @param   string  $value         Value of control
	 * @param   string  &$node         Node name
	 * @param   array   $control_name  Control Name
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	protected function fetchElement($name, $value, &$node, $control_name)
	{
		$options = array();
		$options[] = HTMLHelper::_('select.option', "", Text::_('COM_JTICKETING_VENUES_STATE'));

		return HTMLHelper::_('select.genericlist',
					$options,
					'jform[state_id]',
					'class="input-style"  required="required" aria-invalid="false" size="1" ',
					'value', 'text', $this->value, 'state_id');
	}
}
