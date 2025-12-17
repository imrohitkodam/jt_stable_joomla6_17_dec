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
use Joomla\Filesystem\File;
use Joomla\CMS\HTML\HTMLHelper;

/**
 * JFormFieldIntegrations class
 *
 * @package     JTicketing
 * @subpackage  component
 * @since       1.0
 */
class JFormFieldIntegrations extends FormField
{
	/**
	 * Method to get the field input markup.
	 *
	 * @since  1.6
	 *
	 * @return   string  The field input markup
	 */
	public function getInput ()
	{
		return $this->fetchElement(
			$this->name,
			$this->value,
			$this->element,
			isset($this->options['controls']) ? $this->options['controls'] : ''
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
	 * @return  array country list
	 *
	 * @since   1.0
	 */
	public function fetchElement ($name, $value, &$node, $control_name)
	{
		$communityMainFile = JPATH_SITE . '/components/com_community/community.php';
		$cbMainFile = JPATH_SITE . '/components/com_comprofiler/comprofiler.php';
		$esMainFile = JPATH_SITE . '/components/com_easysocial/easysocial.php';
		$jeventsMainFile = JPATH_SITE . '/components/com_jevents/jevents.php';

		if ($name == 'jform[integration]')
		{
			$options = array();
			$options[] = HTMLHelper::_('select.option', '2', Text::_('COM_JTICKETING_NATIVE'));

			if (File::exists($communityMainFile))
			{
				$options[] = HTMLHelper::_('select.option', '1', Text::_('COM_JTICKETING_JOMSOCIAL'));
			}

			if (File::exists($esMainFile))
			{
				$options[] = HTMLHelper::_('select.option', '4', Text::_('COM_JTICKETING_EASYSOCIAL'));
			}

			if (File::exists($jeventsMainFile))
			{
				$options[] = HTMLHelper::_('select.option', '3', Text::_('COM_JTICKETING_JEVENT'));
			}

			$fieldName = $name;
		}

		if ($name == 'jform[social_integration]')
		{
			$options = array();
			$options[] = HTMLHelper::_('select.option', 'joomla', Text::_('COM_JTICKETING_INTERATION_JOOMLA'));

			if (File::exists($communityMainFile))
			{
				$options[] = HTMLHelper::_('select.option', 'jomsocial', Text::_('COM_JTICKETING_INTERATION_JOMSOCIAL'));
			}

			if (File::exists($esMainFile))
			{
				$options[] = HTMLHelper::_('select.option', 'EasySocial', Text::_('COM_JTICKETING_EASYSOCIAL'));
			}

			if (File::exists($cbMainFile))
			{
				$options[] = HTMLHelper::_('select.option', 'cb', Text::_('COM_JTICKETING_INTERATION_CB'));
			}

			$fieldName = $name;
		}

		$class = (JVERSION >= '4.0.0') ? 'form-select' : 'inputbox';
		$html = HTMLHelper::_('select.genericlist',  $options, $fieldName, 'class="' . $class . '"  ', 'value', 'text', $value, $control_name . $name);

		return $html;
	}
}
