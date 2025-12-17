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
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$field 				= $displayData['field'];
$fieldValue 		= $displayData['fieldValue'];
$i 					= $displayData['i'];
$date 				= '';
$attr				= array();

// Set the required in attr array.
if($field->required === '1')
{
	$attr['required'] = "";
}

if (isset($fieldValue->field_value))
{
	$date = Factory::getDate($fieldValue->field_value)->Format(Text::_('COM_JTICKETING_DATE_FORMAT_SHOW_SHORT'));
}

?>
<label><?php if (isset($field->placehoder)) $field->placehoder; else  echo Text::_($field->label); if ($field->required) echo "<span class='required-star'>&nbsp;*</span>"; ?></label>
<span class="date_field">
	<?php

	echo HTMLHelper::calendar($date, "attendee_field[" . $i . "][" . $field->id . "]", "attendee_field[" . $i . "][" . $field->id . "]",
			Text::_('COM_JTICKETING_DATE_FORMAT_CALENDER'), $attr);
		?>
</span>
