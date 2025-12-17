<?php
/**
 * @version    CVS: 1.2.1
 * @package    Com_Jlike
 * @author     Sudhir Sapkal <contact@techjoomla.com>
 * @copyright  2016 Sudhir Sapkal
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

HTMLHelper::_('jquery.token');
HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('behavior.formvalidator');

$options['relative'] = true;
HTMLHelper::_('script', 'com_jlike/pathService.min.js', $options);
HTMLHelper::_('script', 'com_jlike/path.min.js', $options);

if (!empty($this->extra_sidebar))
{
	$this->sidebar .= $this->extra_sidebar;
}

Text::script('COM_JLIKE_PATH_CATEGORY');
?>

<form action="<?php echo Route::_('index.php?option=com_jlike&layout=edit&path_id=' . (int) $this->item->path_id); ?>"
 method="post" name="adminForm" id="adminForm" enctype="multipart/form-data" >

<?php if(!empty($this->sidebar)): ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
<?php else : ?>
	<div id="j-main-container">
<?php endif;?>

	<?php echo HTMLHelper::_('bootstrap.startTabSet', 'myTab', array('active' => 'general')); ?>
	<?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'general', Text::_('COM_JLIKE_TITLE_TYPE', true)); ?>
	<br>
	<div class="form-horizontal">
		<fieldset class="adminform">
				<div class="row">
				<div class="col-xs-12">
					<?php echo $this->form->renderField('path_title'); ?>
					<?php echo $this->form->renderField('alias'); ?>
					<div class="path-desc">
						<a class="btn btn-info pull-right" href="javascript:void(0);" data-toggle="popover-desc" title="<?php echo Text::_('COM_JLIKE_PATH_DESCRIPTION'); ?>" data-content="<?php echo Text::_('COM_JLIKE_PATH_DESCRIPTION_EXAMPLE_DESC'); ?>"><?php echo Text::_('COM_JLIKE_PATH_INFO_EXAMPLE_TITLE'); ?></a>
						<?php echo $this->form->renderField('path_description'); ?>
					</div>
					<?php
							// echo $this->form->getLabel('path_image'); ?>
					<?php
						// echo $this->form->getInput('path_image');
						?>

						<input type="hidden" name="jform[path_image]" id="jform_path_image_hidden" value="<?php echo $this->item->path_image; ?>" />

					<?php if(!empty($this->item->path_image)) {?>
							<a href="<?php echo Route::_(Uri::root(). 'media/com_jlike/images/' . $this->item->path_image, false);?>"><?php echo Text::_("COM_JLIKE_VIEW_FILE"); ?></a>
						<?php } ?>

					<?php echo $this->form->renderField('path_type'); ?>
					<?php echo $this->form->renderField('category_id'); ?>

					<div class="path-params">
						<a class="btn btn-info pull-right" href="javascript:void(0);" data-toggle="popover" title="<?php echo Text::_('COM_JLIKE_PATH_PARAMS'); ?>" data-content="<?php echo Text::_('COM_JLIKE_PATH_PARAMS_EXAMPLE_DESC'); ?>"><?php echo Text::_('COM_JLIKE_PATH_INFO_EXAMPLE_TITLE'); ?></a>
						<?php echo $this->form->renderField('params'); ?>
					</div>

					<?php echo $this->form->renderField('state'); ?>
					<?php echo $this->form->renderField('created_by'); ?>
					<?php echo $this->form->renderField('created_date'); ?>
					<?php echo $this->form->renderField('modified_by'); ?>
					<?php echo $this->form->renderField('modified_date'); ?>
					<?php echo $this->form->renderField('depth'); ?>
					<?php echo $this->form->renderField('subscribe_start_date'); ?>
					<?php echo $this->form->renderField('subscribe_end_date'); ?>
					<?php echo $this->form->renderField('access'); ?>

				<input type="hidden" id="old_image" name="old_image" value="<?php echo $this->item->path_image;?>" />
				</div>
			</div>
		</fieldset>

	</div>
		<?php echo HTMLHelper::_('bootstrap.endTab'); ?>
			<?php if (Factory::getUser()->authorise('core.admin','jlike')) : ?>
				<?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'permissions', Text::_('JGLOBAL_ACTION_PERMISSIONS_LABEL', true)); ?>
				<br>
				<?php echo $this->form->getInput('rules'); ?>
				<?php echo HTMLHelper::_('bootstrap.endTab'); ?>
			<?php endif; ?>
		<?php echo HTMLHelper::_('bootstrap.endTabSet'); ?>
		<input type="hidden" id="cat_id" value="<?php echo ($this->item->category_id[0]['id']);?>"/>
		<input type="hidden" id="cat_name" value="<?php echo ($this->item->category_id[0]['path']);?>"/>
		<input type="hidden" name="task" value=""/>
	<?php echo HTMLHelper::_('form.token'); ?>
		</div>
	</div>
</form>
<script type="text/javascript">
	jQuery(document).ready(function(){
		jQuery('[data-toggle="popover"]').popover();

		jQuery('[data-toggle="popover-desc"]').popover();

		<?php
		if (!empty($this->item->path_id))
		{
		?>
		// Load Path Type Categories on page loads
		path.getPathTypeCategories('<?php echo $this->form->getValue('path_type', 0); ?>', '<?php echo $this->form->getValue('category_id', 0); ?>');
		<?php
		}
		?>

		jQuery(document).on("change", "#jform_path_type", function(){
			<?php
			if (empty($this->item->path_id))
			{
			?>
				path.getPathParams();
			<?php
			}
			?>

			path.getPathTypeCategories(jQuery(this).val());
		});
	});
</script>
