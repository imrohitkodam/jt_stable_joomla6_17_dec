<?php
/**
 * @package     JTicketing
 * @subpackage  plugin-jticketing
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * Supports an HTML select list
 *
 * @since  1.0.0
 */
class JFormFieldTjlesson extends FormField
{
	/**
	 * The form field type.
	 *
	 * @var   string
	 * @since 1.0.0
	 */
	protected $type = 'tjlesson';

	/**
	 * Method to get the field input markup.
	 *
	 * @return   string  The field input markup.
	 *
	 * @since  1.0.0
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
	 * Method to get a element
	 *
	 * @param   string  $name         Field name
	 * @param   string  $value        Field value
	 * @param   string  &$node        Node
	 * @param   string  $controlName  Controler name
	 *
	 * @return  string  A store id.
	 *
	 * @since	1.0.0
	 */
	public function fetchElement($name, $value, &$node, $controlName)
	{
		JLoader::import('components.com_tjlms.models.lessons', JPATH_SITE);
		$lessonsModel = BaseDatabaseModel::getInstance('lessons', 'tjlmsModel', array('ignore_request' => true));

		$currentDate = Factory::getDate()->toSql();
		$lessonsModel->setState("filter.end_date", $currentDate);
		$allLessons = $lessonsModel->getItems();

		$options = array();

		foreach ($allLessons as $lesson)
		{
			$options[] = HTMLHelper::_('select.option', $lesson->id, $lesson->title);
		}

		$addedField = 'class="inputbox" multiple="multiple" size="5"';

		return HTMLHelper::_('select.genericlist', $options, $name, $addedField, 'value', 'text', $value, $controlName . $name);
	}
}
