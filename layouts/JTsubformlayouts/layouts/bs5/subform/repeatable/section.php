<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;

extract($displayData);

/**
 * Layout variables
 * -----------------
 * @var   Form    $form       The form instance for render the section
 * @var   string  $basegroup  The base group name
 * @var   string  $group      Current group name
 * @var   array   $buttons    Array of the buttons that will be rendered
 */
?>

<div class="subform-repeatable-group af-mb-20" data-base-name="<?php echo $basegroup; ?>" data-group="<?php echo $group; ?>">
	<?php if (!in_array($group, ['attendeefields0', 'tickettypes0'])) { ?>
	<hr>
	<?php } ?>

	<?php if (!empty($buttons)) : ?>
	<div class="btn-toolbar float-end m-2">
		<div class="btn-group">
			<?php if (!empty($buttons['add'])) : ?><button type="button" class="group-add btn btn-sm btn-success me-2" aria-label="<?php echo Text::_('JGLOBAL_FIELD_ADD'); ?>"><span class="icon-plus icon-white" aria-hidden="true"></span> </button><?php endif; ?>
			<?php if (!empty($buttons['remove'])) : ?><button type="button" class="group-remove btn btn-sm btn-danger me-2" aria-label="<?php echo Text::_('JGLOBAL_FIELD_REMOVE'); ?>"><span class="icon-minus icon-white" aria-hidden="true"></span> </button><?php endif; ?>
			<?php if (!empty($buttons['move'])) : ?><button type="button" class="group-move btn btn-sm btn-primary" aria-label="<?php echo Text::_('JGLOBAL_FIELD_MOVE'); ?>"><span class="icon-arrows-alt icon-white" aria-hidden="true"></span> </button><?php endif; ?>
		</div>
	</div>
	<?php endif; ?>
	<div class="row mt-3">
	<?php foreach ($form->getGroup('') as $key => $field) : ?>
		<?php
			$form = str_replace('control-group', 'col-xs-12 col-sm-6 form-group row form_'.$field->class, $field->renderField(array('hiddenLabel' => false)));
			$col = str_replace('control-label', 'col-xs-12 col-md-4 text-break', $form);
			$col = str_replace('controls', 'col-xs-12 col-md-8', $col);
			echo str_replace('form-label', 'col-xs-12', $col);
		?>
	<?php endforeach; ?>
	</div>
</div>
