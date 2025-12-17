<?php
/**
 * @package     JTicketing.Plugin
 * @subpackage  JTicketing,Jomsocial
 *
 * @copyright   Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * Supports an HTML select list of categories
 *
 * @since  3.3.0
 */
class JFormFieldGroupcategories extends FormField
{
	protected $type = 'groupcategories';

	/**
	 * Method to get the field input markup.
	 *
	 * @return   string  The field input markup.
	 *
	 * @since  3.3.0
	 */
	public function getInput()
	{
		return self::fetchElement(
			$this->name,
			$this->value,
			$this->element,
			isset($this->options['control']) ? $this->options['control'] : ''
		);
	}

	/**
	 * Method to get a element
	 *
	 * @param   string  $name          Field name
	 * @param   string  $value         Field value
	 * @param   string  &$node         Node
	 * @param   string  $control_name  Controler name
	 *
	 * @return  string  A store id.
	 *
	 * @since	3.3.0
	 */
	public function fetchElement($name, $value, &$node, $control_name)
	{
		$db = Factory::getDbo();

		// To get the orders against event
		BaseDatabaseModel::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_community/models', 'CommunityAdminModel');
		$groupsModel = BaseDatabaseModel::getInstance('groups', 'CommunityAdminModel', array('ignore_request' => true));
		$list        = $groupsModel->getCategories();
		$options     = array();

		foreach ($list as $eachCat)
		{
			$options[] = HTMLHelper::_('select.option', $eachCat->id, isset($eachCat->name) ? $eachCat->name : $eachCat->name);
		}

		$addedField = 'class="inputbox"';
		$fieldName  = $name;

		$html = '<div id="grpCategoriesField">';
		$html .= HTMLHelper::_('select.genericlist', $options, $fieldName, $addedField, 'value', 'text', $value, $control_name . $name);

		$html .= '</div>';

		return $html;
	}
}
