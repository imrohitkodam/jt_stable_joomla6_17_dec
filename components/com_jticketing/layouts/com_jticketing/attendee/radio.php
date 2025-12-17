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

$field 		= $displayData['field'];
$fieldValue = $displayData['fieldValue'];
$i 			= $displayData['i'];
$j 			= 0;
$defaultChecked    = '';
$fieldParams = isset($field->params) ? json_decode($field->params, true) : null;
if (!empty($fieldParams))
{
	if (isset($fieldParams['default']))
	{
		$defaultChecked = $fieldParams['default'];
	}
}
?>
<label class="w-100"><?php echo Text::_($field->label); if ($field->required) echo "<span class='required-star'>&nbsp;*</span>"; ?></label>
<div class="custom-radio-btn">
<?php
	if (!is_array($field->default_selected_option))
	{
		$defaultSelectedOption = $field->default_selected_option; 
		$fieldOptions     = explode("|", $defaultSelectedOption);

		if (!empty($fieldOptions))
		{
			foreach ($fieldOptions AS $option)
			{
				$selectedString = "";
				if ((!empty($fieldValue->field_value) && trim($fieldValue->field_value) == $option) ||
					(empty($fieldValue->field_value) && $defaultChecked == $option))
				{
					$selectedString = "checked='checked'";
				}

				$j++;
				?>
				<input <?php if (!empty($selectedString)) echo $selectedString; ?>
				type = "radio" id ="<?php echo "attendee_field_".$field->id.$j;?>"
				name = "attendee_field[<?php echo $i; ?>][<?php echo  $field->id; ?>]"
				value = "<?php echo $option;?>"
				class = "<?php echo  $field->validation_class; ?>" >

				<?php echo $option; 
			}
		}
	}
	else
	{
			$fieldOptions = $field->default_selected_option;
			$fieldParams = $field->params;
			if (!empty($fieldOptions))
			{
			foreach($fieldOptions AS $option)
			{
				$selectedString = "";
				if (!empty($fieldValue->field_value) && trim($fieldValue->field_value) == $option->value)
				{
					$selectedString = "checked='checked'";
				}
				else
				{
					if ($defaultChecked == $option->value)
					{
						$selectedString = "checked='checked'";
					}
				}

				$j++;
				?>
					
			<input <?php if (!empty($selectedString)) echo $selectedString; ?>
				type="radio"
				id="<?php echo "attendee_field_" . $field->id . $j;?>"
				name="attendee_field[<?php echo $i; ?>][<?php echo  $field->id; ?>]"
				value="<?php echo $option->value;?>"
				class="<?php if ($field->required) echo "required"; echo  $field->validation_class; ?>"
				required= <?php echo ($field->required == '1') ? 'true' : '';?> >
			<?php echo $option->options;?>
			&nbsp;
			<?php
			}
		}
	}
	?>
	</div>
