<?php
/**
 * @package     JTicketing
 * @subpackage  com_jticketing
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Form\Field\ListField;

FormHelper::loadFieldClass('list');

/**
 * Class for mapping fields for joomla,cb,jomsocial to fill in billing form
 *
 * @package     JTicketing
 * @subpackage  component
 * @since       1.0
 */
class JFormFieldGatewayplg extends ListField
{
	public $layout = "joomla.form.field.list-fancy-select";

	public $type = 'Gatewayplg';
	/**
	 * Field for getting field labels
	 *
	 * @return  string gateways
	 *
	 * @since   1.0
	 */
	public function getInput()
	{
		$target = 'target="_blank" class="btn btn-small btn-primary"';

		$link = (JVERSION >= '4.0.0') ? "<br>" : "";
		$link .= '<a href="index.php?option=com_plugins&view=plugins&filter_folder=payment&filter_enabled=" ' . $target . '>';
		$html = $link . Text::_('COM_JTICKETING_SETTINGS_SETUP_PAYMENT_PLUGINS') . '</a>';

		return parent::getInput() . $html;
	}

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return array An array of JHtml options.
	 *
	 * @since   2.1
	 */
	protected function getOptions()
	{
		$db = Factory::getDbo();
		$condtion = array(0 => '\'payment\'');
		$condtionatype = join(',', $condtion);
		$query = $db->getQuery(true);
		$query->select(array('extension_id as id,name,element,enabled as published'));
		$query->from($db->qn('#__extensions'));
		$query->where('(folder LIKE ' . $condtionatype . ' )');
		$query->where($db->qn('enabled') . '=' . 1);
		$db->setQuery($query);
		$gatewayplugin = $db->loadobjectList();
		$options = array();

		foreach ($gatewayplugin as $gateway)
		{
			$gatewayname = ucfirst(str_replace('plugpayment', '', $gateway->element));
			$options[] = HTMLHelper::_('select.option', $gateway->element, $gatewayname);
		}

		// Joomla 6: JVERSION check removed
		if (false) // Legacy >= '4.0.0')
		{
			$this->class = 'class="form-select required"  multiple="multiple" size="5"';
		}
		else
		{
			$this->class = 'class="inputbox required"  multiple="multiple" size="5"';
		}

		return $options;
	}

	/**
	 * Field for getting gateways labels
	 *
	 * @param   string  $label         label of element
	 * @param   string  $description   description of element
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
