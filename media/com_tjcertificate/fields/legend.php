<?php
/**
 * @package     TJCertificate
 * @subpackage  com_tjcertificate
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2021 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access.
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Form\Field\ListField;

/**
 * Custom Legend field for component params.
 *
 * @package  Com_Tjcertificate
 * @since    1.0
 */
class JFormFieldLegend extends ListField
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	1.0
	 */
	protected $type = 'Legend';

	/**
	 * Method to get the field input markup.
	 *
	 * @return   string  The field input markup.
	 *
	 * @since    1.0
	 */
	public function getInput()
	{
		$document = Factory::getDocument();

		if (JVERSION < '3.0')
		{
			$element = (array) $this->element;
			$hint = $element['@attributes']['hint'];
		}
		else
		{
			$hint = $this->hint;

			/*Let's remove controls class from parent
			And, remove control-group class from grandparent*/
			$script = 'jQuery(document).ready(function(){
				jQuery("#' . $this->id . '").parent().removeClass("controls");
				jQuery("#' . $this->id . '").parent().parent().removeClass("control-group");
			});';

			$document->addScriptDeclaration($script);
		}

		// Show them a legend.
		$return = '<legend class="clearfix" id="' . $this->id . '">' . Text::_($this->value) . '</legend>';

		// Show them a hint below the legend.
		// Let them go - GaGa about the legend.
		if (!empty($hint))
		{
			$return .= '<span class="disabled">' . Text::_($hint) . '</span>';
			$return .= '<br/><br/>';
		}

		return $return;
	}
}
