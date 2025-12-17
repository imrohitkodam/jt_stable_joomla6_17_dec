<?php
/**
 * @package     TJVendors
 * @subpackage  Layout
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

/*
 * As Joomla doesn't provide bootstrap 3 layout and TJVendor run on bootstrap 3 templates added the overrides for the Joomla subform layout
*/
/**
 * Make thing clear
 *
 * @var Form   $form       The form instance for render the section
 * @var string  $basegroup  The base group name
 * @var string  $group      Current group name
 * @var array   $buttons    Array of the buttons that will be rendered
 */
extract($displayData);
?>
<br>
<div class="row subform-repeatable-group subform-repeatable-group-<?php echo $unique_subform_id; ?> bg-faded py-0 px-25 my-20 ml-0 mr-0" data-base-name="<?php echo $basegroup; ?>" data-group="<?php echo $group; ?>">
	<?php if (!empty($buttons)) : ?>
	<div class="btn-toolbar text-right">
		<?php if (!empty($buttons['add'])) : ?><a class="group-add-<?php echo $unique_subform_id; ?> btn btn-sm button btn-success"><span class="fa fa-2x fa-plus"></span> </a><?php endif; ?>
		<?php if (!empty($buttons['remove'])) : ?><a class="group-remove-<?php echo $unique_subform_id; ?> btn btn-sm button btn-danger"><span class="fa fa-2x fa-trash"></span> </a><?php endif; ?>
		<?php if (!empty($buttons['move'])) : ?><a class="group-move-<?php echo $unique_subform_id; ?> btn btn-sm button btn-primary"><span class="fa fa-2x fa-move"></span> </a><?php endif; ?>
	</div>
	<?php endif; ?>

<?php foreach ($form->getGroup('') as $key => $field) : ?>
	<?php
		if (JVERSION < '4.0.0')
		{
			$form = str_replace('control-group', 'col-xs-12 col-sm-6 form-group form_' . $field->class, $field->renderField(array('hiddenLabel' => false)));
			$col = str_replace('control-label', 'col-xs-12 col-md-3', $form);
			$col = str_replace('controls', 'col-xs-12 col-md-8', $col);
			echo str_replace('form-label', 'col-xs-12', $col);
		}
		else
		{
			echo $field->renderField(array('hiddenLabel' => false));
		}
	?>
<?php endforeach; ?>
</div>
