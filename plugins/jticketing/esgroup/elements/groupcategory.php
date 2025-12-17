<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_tjlms
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;
use Joomla\CMS\Form\FormField;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;


/**
 * Supports an HTML select list of categories
 *
 * @since  3.3.0
 */
class JFormFieldGroupcategory extends FormField
{
	protected $type = 'groupcategory';

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

		$esmainfile = JPATH_ROOT . '/administrator/components/com_easysocial/includes/foundry.php';

		require_once $esmainfile;

		$userProfileId = $creator_uid = Foundry::user()->id;

		$model = FD::model('Groups');
		$list  = $model->getCreatableCategories($userProfileId);

		$options = array();

		foreach ($list as $eachCat)
		{
			$options[] = HTMLHelper::_('select.option', $eachCat->id, isset($eachCat->title) ? $eachCat->title : $eachCat->name);
		}

		$addedField = 'class="inputbox"';
		$fieldName  = $name;

		$html  = '<div id="grpCategoriesField">';
		$html .= HTMLHelper::_('select.genericlist', $options, $fieldName, $addedField, 'value', 'text', $value, $control_name . $name);

		$html .= '</div>';

		return $html;
	}
}
