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
// Joomla 6: formbehavior.chosen removed - Chosen.js is deprecated

/** @var $this JticketingViewCouponform */

Factory::getDocument()->addScriptDeclaration('
	jtSite.couponform.initCouponFormJs();
	Joomla.submitbutton = function(task){jtSite.couponform.couponSubmitButton(task);}
');
?>

<div id="jtwrap" class="tjBs3">
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
			<div class="col-xs-12 col-md-8 mt-2">
				<!--To Do : Add note for register user if has not created a single event-->
				<div class="control-group">
					<?php echo $this->form->getLabel('event_ids');?>
					<?php echo $this->form->getInput('event_ids');?>
				</div>
				<div class="control-group">
					<?php echo $this->form->getLabel('name');?>
					<?php echo $this->form->getInput('name');?>
				</div>
				<div class="control-group">
					<?php echo $this->form->getLabel('code');?>
					<?php echo $this->form->getInput('code');?>
				</div>
				<div class="control-group">
					<?php echo $this->form->getLabel('value');?>
					<?php echo $this->form->getInput('value');?>
				</div>
				<div class="control-group">
					<?php echo $this->form->getLabel('val_type');?>
					<?php echo $this->form->getInput('val_type');?>
				</div>
				<div class="control-group">
					<?php echo $this->form->getLabel('limit');?>
					<?php echo $this->form->getInput('limit');?>
				</div>
				<div class="control-group">
					<?php echo $this->form->getLabel('state');?>
					<?php echo $this->form->getInput('state');?>
				</div>
				<div class="control-group">
					<?php echo $this->form->getLabel('max_per_user');?>
					<?php echo $this->form->getInput('max_per_user');?>
				</div>
				<div class="control-group">
					<?php echo $this->form->getLabel('valid_from');?>
					<?php echo $this->form->getInput('valid_from');?>
				</div>
				<div class="control-group">
					<?php echo $this->form->getLabel('valid_to');?>
					<?php echo $this->form->getInput('valid_to');?>
				</div>
				<div class="control-group">
					<?php echo $this->form->getLabel('description');?>
					<?php echo $this->form->getInput('description');?>
				</div>

				<!--Action Button-->
				<div class="control-group">
					
						<button type="submit" class="validate btn  btn-default btn-success" onclick="Joomla.submitbutton('couponform.save'); return false;">
							<?php echo Text::_('JSUBMIT'); ?>
						</button>
						<a class="btn  btn-default" onclick="Joomla.submitbutton('couponform.cancel')" title="<?php echo Text::_('JCANCEL'); ?>">
							<?php echo Text::_('JCANCEL'); ?>
						</a>
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
