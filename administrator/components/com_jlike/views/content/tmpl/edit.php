<?php
/**
 * @package    Com_jlike
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2017 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die();
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

HTMLHelper::addIncludePath(JPATH_COMPONENT . '/helpers/html');

HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');
if (!empty($this->extra_sidebar))
{
    $this->sidebar .= $this->extra_sidebar;
}
?>
<script type="text/javascript">
	Joomla.submitbutton = function (task) {
		if (task == 'content.cancel')
		{
			Joomla.submitform(task, document.getElementById('adminForm'));
		}
		else
		{
			if ( document.formvalidator.isValid(document.getElementById('adminForm')))
			{

			var element_id = document.getElementById('jform_element_id').value.trim();
			var element = document.getElementById('jform_element').value.trim();
			var title = document.getElementById('jform_title').value.trim();
			var url = document.getElementById('jform_url').value.trim();
			if (element_id == "" || element == "" || title == "" || url == "" )
			{
				alert('<?php echo $this->escape(Text::_('JGLOBAL_VALIDATION_FORM_FAILED')); ?>');
				return false;
			}
				Joomla.submitform(task, document.getElementById('adminForm'));
			}
			else
			{
				alert('<?php echo $this->escape(Text::_('JGLOBAL_VALIDATION_FORM_FAILED')); ?>');
			}
		}
	}
</script>


	<form
		action="<?php echo Route::_('index.php?option=com_jlike&view=content&layout=edit&id=' . (int) $this->item->id, false);?>"
		method="post" enctype="multipart/form-data" name="adminForm" id="adminForm" class="form-validate">
		<?php if(!empty($this->sidebar)): ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
<?php else : ?>
	<div id="j-main-container">
<?php endif;?>
<div class="form-horizontal">
			<div class="row-fluid">
				<div class="span12 form-horizontal">
					<fieldset class="adminform">
						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('id'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('id'); ?>
							</div>
						</div>


						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('element_id'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('element_id'); ?>
							</div>
						</div>

						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('url'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('url'); ?>
							</div>
						</div>

						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('element'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('element'); ?>
							</div>
						</div>

						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('title'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('title'); ?>
							</div>
						</div>
					<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('like_cnt'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('like_cnt'); ?>
							</div>
						</div>

						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('dislike_cnt'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('dislike_cnt'); ?>
							</div>
						</div>
					</fieldset>
				</div>
			</div>
			</div>
			<input type="hidden" name="task" value="" />
			<?php echo HTMLHelper::_('form.token'); ?>
		</div>
	</form>
</div>
