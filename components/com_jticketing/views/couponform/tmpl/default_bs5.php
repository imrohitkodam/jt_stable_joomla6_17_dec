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

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Factory;

HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('formbehavior.chosen', '#jform_event_ids');
HTMLHelper::_('formbehavior.chosen', '#jform_val_type');

/** @var $this JticketingViewCouponform */

Factory::getDocument()->addScriptDeclaration('
	jtSite.couponform.initCouponFormJs();
	Joomla.submitbutton = function(task){jtSite.couponform.couponSubmitButton(task);}
');
?>

<div id="jtwrap" class="tjBs5">
	<div id="couponform">
		<!--Page header-->
		<div class="page-header">
			<h1>
			<?php
				if (!empty($this->item->id)):
					echo Text::_('COM_JTICKETING_COUPON_EDIT') . ':&nbsp' . $this->escape($this->item->name);
				else:
					echo Text::_('COM_JTICKETING_COUPON_ADD');
				endif;
				?>
			</h1>
		</div>
		<hr class="hr-condensed"/>
		<form id="coupon-form" action="<?php echo Route::_('index.php?option=com_jticketing&task=couponform.save&id=' . (int) $this->item->id);?>" method="post" class="form-validate form-horizontal" enctype="multipart/form-data">
		<div class="row">
			<div class="col-xs-12 col-md-8  mt-2">
				<!--To Do : Add note for register user if has not created a single event-->
				<div class="form-group row mb-3">
					<div class="form-label col-md-4"><?php echo $this->form->getLabel('event_ids');?></div>
					<div class="col-md-8"><?php echo $this->form->getInput('event_ids');?></div>
				</div>
				<div class="form-group row mb-3">
					<div class="form-label col-md-4"><?php echo $this->form->getLabel('name');?></div>
					<div class="col-md-8"><?php echo $this->form->getInput('name');?></div>
				</div>
				<div class="form-group row mb-3">
					<div class="form-label col-md-4"><?php echo $this->form->getLabel('code');?></div>
					<div class="col-md-8"><?php echo $this->form->getInput('code');?></div>
				</div>
				<div class="form-group row mb-3">
					<div class="form-label col-md-4"><?php echo $this->form->getLabel('value'); ?></div>
					<div class="col-md-8"><?php echo $this->form->getInput('value'); ?></div>
				</div>
				<div class="form-group row mb-3">
					<div class="form-label col-md-4"><?php echo $this->form->getLabel('val_type');?></div>
					<div class="col-md-8"><?php echo $this->form->getInput('val_type');?></div>
				</div>

				<div class="form-group row">
					<div class="form-label col-md-4"><?php echo $this->form->getLabel('group_discount');?></div>
					<div class="col-md-8"><?php echo $this->form->getInput('group_discount');?></div>
				</div>

				<div class="form-group row mb-4" id="group_discount_tickets_container">
					<div class="form-label col-md-4" id="group_discount_tickets_label">
						<?php echo $this->form->getLabel('group_discount_tickets');?>
					</div>
					<div class="col-md-8" id="group_discount_tickets_wrapper">
						<?php echo $this->form->getInput('group_discount_tickets');?>
					</div>
				</div>

				<div class="form-group row mb-3">
					<div class="form-label col-md-4"><?php echo $this->form->getLabel('limit'); ?></div>
					<div class="col-md-8"><?php echo $this->form->getInput('limit'); ?></div>
				</div>
				<div class="form-group row mb-3">
					<div class="form-label col-md-4"><?php echo $this->form->getLabel('state'); ?></div>
					<div class="col-md-8"><?php echo $this->form->getInput('state'); ?></div>
				</div>
				<div class="form-group row mb-3">
					<div class="form-label col-md-4"><?php echo $this->form->getLabel('max_per_user'); ?></div>
					<div class="col-md-8"><?php echo $this->form->getInput('max_per_user'); ?></div>
				</div>
				<div class="form-group row mb-3">
					<div class="form-label col-md-4"><?php echo $this->form->getLabel('valid_from'); ?></div>
					<div class="col-md-8"><?php echo $this->form->getInput('valid_from'); ?></div>
				</div>
				<div class="form-group row mb-3">
					<div class="form-label col-md-4"><?php echo $this->form->getLabel('valid_to'); ?></div>
					<div class="col-md-8"><?php echo $this->form->getInput('valid_to'); ?></div>
				</div>
				<div class="form-group row mb-3">
					<div class="form-label col-md-4"><?php echo $this->form->getLabel('description'); ?></div>
					<div class="col-md-8"><?php echo $this->form->getInput('description'); ?></div>
				</div>

				<!--Action Button-->
				<div class="form-group row mb-3">
					<div class="col-md-12">
						<a class="btn float-end  btn-default ms-2" onclick="Joomla.submitbutton('couponform.cancel')" title="<?php echo Text::_('JCANCEL'); ?>">
							<?php echo Text::_('JCANCEL'); ?>
						</a>
						<button type="submit" class="validate btn float-end btn-success ms-2" onclick="Joomla.submitbutton('couponform.save'); return false;">
							<?php echo Text::_('JSUBMIT'); ?>
						</button>
					</div>
				</div>
			</div>
		</div>

		<?php echo $this->form->renderField('vendor_id');?>
		<?php echo $this->form->renderField('created_by');?>
		<?php echo $this->form->renderField('ordering');?>
		<?php echo $this->form->renderField('checked_out');?>
		<?php echo $this->form->renderField('checked_out_time');?>

		<input type="hidden" name="jform[id]" id="id" value="<?php echo $this->item->id; ?>" />
		<input type="hidden" name="option" value="com_jticketing" />
		<input type="hidden" name="task" id="task" value="couponform.save" />
		<?php echo HTMLHelper::_('form.token');?>
		</form>
	</div>
</div>