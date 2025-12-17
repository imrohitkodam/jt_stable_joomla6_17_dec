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
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\SqlField;
use Joomla\Database\ParameterType;

$class = (JTICKETING_LOAD_BOOTSTRAP_VERSION == "bs5") ? "form-select" : "";
$field 		= $displayData['field'];
$fieldValue = $displayData['fieldValue'];
$i 			= $displayData['i'];

$fieldParams = isset($field->params) ? json_decode($field->params, true) : null;
// print_r($field);die;

$db    = Factory::getDbo();
$query = $db->getQuery(true);
$sql   = $fieldParams['query'];

// Run the query with a having condition because it supports aliases
$query->setQuery($sql);

if (!$sql)
{
	return '';
}

try {
    $db->setQuery($query);
    $items = $db->loadObjectList();
} catch (Exception $e) {
    // If the query failed, we fetch all elements
    $db->setQuery($sql);
    $items = $db->loadObjectList();
}

$field->default_selected_option = [];

foreach ($items as $item) {
    $field->default_selected_option[] = $item->text;
}

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
                value="<?php echo $option;?>">
                <?php echo $option;?>
        </option>
    <?php
	}
}
?>
</select>
