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
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('bootstrap.tooltip');
// Joomla 6: formbehavior.chosen removed - using native select

// Import CSS
$document = Factory::getDocument();
HTMLHelper::_('stylesheet', 'components/com_jticketing/assets/css/jticketing.css');

// Call helper function
JticketingHelper::getLanguageConstant();
$cid = Factory::getApplication()->getInput()->get('id', 0, 'INT');

Factory::getDocument()->addScriptDeclaration("
	var cid = '" . $cid . "';
	var root_url = '" . URI::base() . "';
	jtAdmin.coupon.initCouponJs();
	Joomla.submitbutton = function(task){jtAdmin.coupon.couponSubmitButton(task);}
");
?>
<div class="sa-coupon">
	<form action="<?php echo Route::_('index.php?option=com_jticketing&layout=edit&id=' . (int) $this->item->id); ?>"
	method="post" enctype="multipart/form-data" name="adminForm" id="coupon-form" class="form-validate">
		<div class="form-horizontal">
			<div class="row-fluid">
				<div class="span10 form-horizontal">
					<fieldset class="adminform">
						<div class="control-group">
							<div class="control-label"><?php echo $this->form->getLabel('vendor_id');?></div>
							<div class="controls"><?php echo $this->form->getInput('vendor_id');?></div>
						</div>
						<div class="control-group">
							<div class="control-label"><?php echo $this->form->getLabel('event_ids');?></div>
							<div class="controls"><?php echo $this->form->getInput('event_ids');?></div>
						</div>
						<div class="control-group">
							<div class="control-label"><?php echo $this->form->getLabel('name');?></div>
							<div class="controls"><?php echo $this->form->getInput('name');?></div>
						</div>
						<div class="control-group">
							<div class="control-label"><?php echo $this->form->getLabel('code');?></div>
							<div class="controls" id = "code"><?php echo $this->form->getInput('code');?></div>
						</div>
						<div class="control-group">
							<div class="control-label"><?php echo $this->form->getLabel('value');?></div>
							<div class="controls"><?php echo $this->form->getInput('value');?></div>
							<div class="controls"><?php echo Text::_('COM_JTICKETING_FORM_VALUE_NOTE');?></div>
						</div>
						<div class="control-group">
							<div class="control-label"><?php echo $this->form->getLabel('val_type');?></div>
							<div class="controls"><?php echo $this->form->getInput('val_type');?></div>
						</div>
						<div class="control-group">
							<div class="control-label"><?php echo $this->form->getLabel('limit');?></div>
							<div class="controls"><?php echo $this->form->getInput('limit');?></div>
						</div>
						<div class="control-group">
							<div class="control-label"><?php echo $this->form->getLabel('state');?></div>
							<div class="controls"><?php echo $this->form->getInput('state');?></div>
						</div>
						<div class="control-group">
							<div class="control-label"><?php echo $this->form->getLabel('max_per_user');?></div>
							<div class="controls"><?php echo $this->form->getInput('max_per_user');?></div>
						</div>
						<div class="control-group">
							<div class="control-label"><?php echo $this->form->getLabel('valid_from'); ?></div>
							<div class="controls"><?php echo $this->form->getInput('valid_from'); ?></div>
						</div>
						<div class="control-group">
							<div class="control-label"><?php echo $this->form->getLabel('valid_to');?></div>
							<div class="controls"><?php echo $this->form->getInput('valid_to');?></div>
						</div>
						<div class="control-group">
							<div class="control-label"><?php echo $this->form->getLabel('description');?></div>
							<div class="controls"><?php echo $this->form->getInput('description');?></div>
						</div>
						<?php
							echo $this->form->renderField('id');
							echo $this->form->renderField('ordering');
							echo $this->form->renderField('created_by');
							echo $this->form->renderField('checked_out');
							echo $this->form->renderField('checked_out_time');
						?>
					</fieldset>
				</div>
			</div>
			<input type="hidden" name="task" value="" />
			<?php echo HTMLHelper::_('form.token'); ?>
		</div>
	</form>
</div>
