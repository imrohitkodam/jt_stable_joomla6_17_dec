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
$class = 'form-check-input';
$field 		= $displayData['field'];
$fieldValue = $displayData['fieldValue'];
$i 			= $displayData['i'];

?>
<label for="attendee_field_<?php echo  $field->id; ?>_<?php echo  $i; ?>"><?php
	echo (isset($field->label)) ?  Text::_($field->label) : Text::_($field->placeholder);
	echo ($field->required) ? "<span class='required-star'>&nbsp;*</span>" : "" ; ?>
</label>
<?php
	$value = '';

	if (!empty($displayData['attendee_id']))
	{
		$value = (!empty($finalOrderItemsValue[$displayData['attendee_id']][$field->name])) ? htmlspecialchars($finalOrderItemsValue[$displayData['attendee_id']][$field->name], ENT_COMPAT, 'UTF-8') : '';
	}
$checked   = $value ? ' checked' : ''; 
?>


<div class="form-check form-check-inline af-ml-10">
	<input
		type="checkbox"
		name="attendee_field[<?php echo $i; ?>][<?php echo  $field->id; ?>]"
		id="attendee_field_<?php echo  $field->id; ?>_<?php echo  $i; ?>"<?php if ($field->js_function) echo $field->js_function; ?>
		class = "<?php if ($field->required) echo "required"; echo $field->validation_class;?> <?php echo $class;?>"
		value="1"
		<?php echo $checked; ?>
	>
</div>