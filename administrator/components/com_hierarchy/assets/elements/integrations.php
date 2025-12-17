<?php
/**
 * @version    SVN: <svn_id>
 * @package    Com_Hierarchy
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2017 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Filesystem\File;

/**
 * JFormFieldIntegrations class
 *
 * @package     Com_Hierarchy
 * @subpackage  component
 * @since       1.0
 */
class FormFieldIntegrations extends FormField
{
	/**
	 *Function to construct a hierarchy view
	 *
	 * @since  3.0
	 */
	public function __construct()
	{
		$this->communityMainFile = JPATH_SITE . '/components/com_community/community.php';
		$this->jeventsMainFile   = JPATH_SITE . '/components/com_jevents/jevents.php';
		$this->esMainFile        = JPATH_SITE . '/components/com_easysocial/easysocial.php';
		$this->easyProMainFile   = JPATH_SITE . '/components/com_jsn/jsn.php';
		$this->cbMainFile        = JPATH_SITE . '/components/com_comprofiler/comprofiler.php';

		parent::__construct();
	}

	/**
	 * Method to get the field input markup.
	 *
	 * @since  1.6
	 *
	 * @return   string  The field input markup
	 */
	public function getInput ()
	{
		return $this->fetchElement($this->name, $this->value, $this->element, $this->options['controls']);
	}

	/**
	 * Method fetchElement
	 *
	 * @param   string  $name         name of element
	 * @param   string  $value        value of element
	 * @param   string  $controlName  control name
	 *
	 * @return  array extensions list
	 *
	 * @since   1.0
	 */
	public function fetchElement ($name, $value, $controlName)
	{
		if ($name == 'jform[integration]')
		{
			$options = array();
			$options[] = HTMLHelper::_('select.option', '2', Text::_('COM_HIERARCHY_NATIVE'));

			if (File::exists($this->communityMainFile))
			{
				$options[] = HTMLHelper::_('select.option', '1', Text::_('COM_HIERARCHY_JOMSOCIAL'));
			}

			if (File::exists($this->jeventsMainFile))
			{
				$options[] = HTMLHelper::_('select.option', '3', Text::_('COM_HIERARCHY_JEVENT'));
			}

			if (File::exists($this->esMainFile))
			{
				$options[] = HTMLHelper::_('select.option', '4', Text::_('COM_HIERARCHY_EASYSOCIAL'));
			}

			if (File::exists($this->easyProMainFile))
			{
				$options[] = HTMLHelper::_('select.option', '5', Text::_('COM_HIERARCHY_EASYPROFILE'));
			}

			if (File::exists($this->cbMainFile))
			{
				$options[] = HTMLHelper::_('select.option', '6', Text::_('COM_HIERARCHY_COMMUNITY_BUILDER'));
			}

			$fieldName = $name;
		}

		$html = HTMLHelper::_('select.genericlist',  $options, $fieldName, 'class="inputbox"', 'value', 'text', $value, $controlName . $name);

		return $html;
	}
}
