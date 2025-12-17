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

use Joomla\CMS\Language\Text;

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

<div class="subform-repeatable-group" data-base-name="<?php echo $basegroup; ?>" data-group="<?php echo $group; ?>">
	<?php if (!empty($buttons)) : ?>
	<div class="btn-toolbar text-right">
		<div class="btn-group">
			<?php if (!empty($buttons['add'])) : ?><a class="group-add btn btn-sm button btn-success"><span class="fa fa-plus"></span> </a><?php endif; ?>
			<?php if (!empty($buttons['remove'])) : ?><a class="group-remove btn btn-sm button btn-danger"><span class="fa fa-trash"></span> </a><?php endif; ?>
			<?php if (!empty($buttons['move'])) : ?><a class="group-move btn btn-sm button btn-primary"><span class="fa fa-move"></span> </a><?php endif; ?>
		</div>
	</div>
	<?php endif; ?>
	<div class="row-fluid">
<?php foreach ($form->getFieldsets() as $fieldset) : ?>
<fieldset class="<?php if (!empty($fieldset->class)){ echo $fieldset->class; } ?>">
	<?php if (!empty($fieldset->label)) : ?>
	<legend><?php echo Text::_($fieldset->label); ?></legend>
	<?php endif; ?>
<?php foreach ($form->getFieldset($fieldset->name) as $field) : ?>
	<?php echo $field->renderField(); ?>
<?php endforeach; ?>
</fieldset>
<?php endforeach; ?>
	</div>
</div>
