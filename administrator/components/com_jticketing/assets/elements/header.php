<?php
/**
 * @package     JTicketing
 * @subpackage  com_jticketing
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;

/**
 * Class for gettingheader tooltip for each elements
 *
 * @package     JTicketing
 * @subpackage  component
 * @since       1.0
 */
class JFormFieldHeader extends FormField
{
	public $type = 'Header';
	/**
	 * Field for getting field labels
	 *
	 * @return  html mapping fields
	 *
	 * @since   1.0
	 */
	public function getInput()
	{
		HTMLHelper::_('stylesheet', 'components/com_jticketing/assets/css/jticketing.css');
		$return = '
		<div class="jticketHeaderOuterDiv">
			<div class="jticketHeaderInnerDiv">
				' . Text::_($this->value) . '
			</div>
		</div>';

		return $return;
	}
}
