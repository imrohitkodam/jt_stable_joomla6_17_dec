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
?>

<script type="text/javascript">
	Joomla.submitbutton = function (task) {
		if (task == 'pathnodegraph.cancel')
		{
			Joomla.submitform(task, document.getElementById('adminForm'));
		}
		else
		{
			if (task != 'adminForm.cancel' && document.formvalidator.isValid(document.getElementById('adminForm')))
			{
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
		action="<?php echo Route::_('index.php?option=com_jlike&view=pathnodegraph&layout=edit&pathnode_graph_id=' . (int) $this->item->pathnode_graph_id, false);?>"
		method="post" enctype="multipart/form-data" name="adminForm" id="adminForm" class="form-validate">
		<?php if(!empty($this->sidebar)): ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
<?php else : ?>
	<div id="j-main-container">
<?php endif;?>
		<?php echo HTMLHelper::_('bootstrap.startTabSet', 'myTab', array('active' => 'general')); ?>
		<?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'general', Text::_('COM_JLIKE_TITLE_ADDPATHNODE', true)); ?>
		<div class="form-horizontal">
			<div class="row-fluid">
				<div class="span12 form-horizontal">
					<fieldset class="adminform">
						<div class="control-group">
							<div class="control-label">



								<?php echo $this->form->getLabel('pathnode_graph_id'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('pathnode_graph_id'); ?>
							</div>
						</div>


						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('path_id'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('path_id'); ?>
							</div>
						</div>

						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('lft'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('lft'); ?>
							</div>
						</div>

						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('node'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('node'); ?>
							</div>
						</div>
						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('rgt'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('rgt'); ?>
							</div>
						</div>

						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('order'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('order'); ?>
							</div>
						</div>
						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('isPath'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('isPath'); ?>
							</div>
						</div>

					<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('this_compulsory'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('this_compulsory'); ?>
							</div>
						</div>

						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('delay'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('delay'); ?>
							</div>
						</div>

						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('duration'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('duration'); ?>
							</div>
						</div>

						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('visibility'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('visibility'); ?>
							</div>
						</div>



					</fieldset>
				</div>
			</div>
			<?php echo HTMLHelper::_('bootstrap.endTabSet'); ?>
</div>
		<?php echo HTMLHelper::_('bootstrap.endTabSet'); ?>
			<input type="hidden" name="task" value="" />
			<?php echo HTMLHelper::_('form.token'); ?>
		</div>
	</form>
</div>
