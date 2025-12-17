<?php
/**
 * @package     Jlike
 * @subpackage  com_jlike
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2018 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;

HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.multiselect'); // only for list tables

HTMLHelper::_('behavior.keepalive');

if (!empty($this->extra_sidebar))
{
    $this->sidebar .= $this->extra_sidebar;
}
?>

<form action="<?php echo Route::_('index.php?option=com_jlike&layout=edit&id=' . (int) $this->item->id); ?>"
 method="post" name="adminForm" id="adminForm" >

	<?php if (!empty($this->sidebar)): ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
	<?php else : ?>
	<div id="j-main-container">
	<?php endif;?>
		<div class="form-horizontal">
			<fieldset class="adminform">
				<div class="row">
					<div class="col-md-12">
						<?php // echo $this->form->renderField('client'); ?>
						<?php echo $this->form->renderField('title'); ?>
						<?php echo $this->form->renderField('code'); ?>
						<?php echo $this->form->renderField('show_title'); ?>
						<?php echo $this->form->renderField('title_required'); ?>
						<?php echo $this->form->renderField('show_rating'); ?>
						<?php echo $this->form->renderField('rating_scale'); ?>
						<?php echo $this->form->renderField('show_review'); ?>
						<?php
						if ($this->isCompInstalled)
						{
							echo $this->form->renderField('tjucm_type_id');
							?>

							<div class="control-group">
								<div class="controls">
									<span class="alert alert-info alert-help-inline alert_no_margin">
									<?php echo Text::_('COM_JLIKE_VIEW_RATING_UCM_INFO'); ?>
									</span>
								</div>
							</div>
							<?php
						}
						?>
						<?php echo $this->form->renderField('show_all_rating'); ?>
					<div>
				<div>
			</fieldset>
		</div>
		<input type="hidden" name="task" value=""/>
		<?php echo HTMLHelper::_('form.token'); ?>
	</div>
</form>
