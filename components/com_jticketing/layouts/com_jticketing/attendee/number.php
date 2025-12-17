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
$class = (JTICKETING_LOAD_BOOTSTRAP_VERSION == "bs5") ? "form-control" : "";
$field      = $displayData['field'];
$fieldValue = $displayData['fieldValue'];
$i          = $displayData['i'];
$value  = isset($fieldValue->field_value) ? $fieldValue->field_value : '';
$numberDescription = (isset($field->description) && $field->description) ? $field->description : ''; // Changed variable name for clarity
?>
<label for="attendee_field_<?php echo  $field->id; ?><?php echo  $i; ?>">
<?php
    echo (isset($field->label)) ?  Text::_($field->label) : Text::_($field->placeholder);
    echo ($field->required) ? "<span class='required-star'>&nbsp;*</span>" : ""; ?>
</label>
<input  type="number"
        id = "attendee_field_<?php echo  $field->id; ?><?php echo  $i; ?>"
        <?php if($field->js_function) echo $field->js_function; ?>
        class = "<?php if ($field->required) echo "required"; echo $field->validation_class;?> <?php echo $class;?>"
        name = "attendee_field[<?php echo $i; ?>][<?php echo  $field->id; ?>]"
        placeholder="<?php echo(isset($field->placeholder)) ? Text::_($field->placeholder) : ($numberDescription ? Text::_($numberDescription) : '');?>"
        value="<?php echo $this->escape($value);?>"
        <?php if (isset($field->min)) echo 'min="' . (int) $field->min . '"'; ?>
        <?php if (isset($field->max)) echo 'max="' . (int) $field->max . '"'; ?>
/>