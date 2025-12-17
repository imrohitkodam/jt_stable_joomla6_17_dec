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
use Joomla\CMS\Language\Text;
$class = (JTICKETING_LOAD_BOOTSTRAP_VERSION == "bs5") ? "form-select" : "";
$field 		= $displayData['field'];
$fieldValue = $displayData['fieldValue'];
$i 			= $displayData['i'];
?>
<label for="attendee_field_<?php echo  $field->id; ?>_<?php echo  $i; ?>"><?php
	echo (isset($field->label)) ?  Text::_($field->label) : Text::_($field->placeholder);
	 if ($field->required) echo "<span class='required-star'>&nbsp;*</span>"; ?>
</label>
<select  class="<?php echo "chzn-done"; echo $field->validation_class ?> w-100 <?php echo $class;?>"
		name="attendee_field[<?php echo $i; ?>][<?php echo  $field->id; ?>][]"
		id="attendee_field_<?php echo  $field->id; ?>_<?php echo  $i; ?>"
		<?php echo ($field->required == '1') ? 'required' : '';?>
		>
<?php
$defaultSelectedOption = $field->default_selected_option;

if (!empty($defaultSelectedOption))
{
	// Universal Fields returns array
	if (is_array($defaultSelectedOption))
	{
		$fieldOptions = $defaultSelectedOption;
	}
	else
	{
		$fieldOptions = explode("|", $defaultSelectedOption);
	}

	foreach ($fieldOptions AS $option)
	{
		$selectedString = '';
		if (is_array($defaultSelectedOption))
		{
			if (isset($fieldValue->field_value) && ($option->options == trim($fieldValue->field_value)))
			{
				$selectedString = 'selected="selected"';
			}
		}
		else
		{
			if (isset($fieldValue->field_value) && ($option == $fieldValue->field_value))
			{
					$selectedString = 'selected="selected"';
			}
		}
			?>
<option <?php if (!empty($selectedString)) echo $selectedString; ?>
		value="<?php echo (is_array($defaultSelectedOption)) ?  $option->value : $option;?>">
		<?php if (is_array($defaultSelectedOption)) echo $option->options; else echo $option;?>
</option>
<?php
	}
}
?>
</select>
