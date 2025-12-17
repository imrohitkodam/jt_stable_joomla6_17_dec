<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2017 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die;
?>
<div class="row-fluid form-horizontal-desktop">
	<div class="span6">
		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('booking_start_date');?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('booking_start_date'); ?>
			</div>
		</div>

		<div class="control-group">
			<div class="control-label" id="booking-end">
				<?php echo $this->form->getLabel('booking_end_date'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('booking_end_date'); ?>
			</div>
		</div>
	</div>

	<div class="span6">
		<?php $idealTime = $this->params->get('enable_ideal_time');

		if (!empty($idealTime))
		{
			?>
		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('ideal_time'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('ideal_time');?>
			</div>
		</div>
			<?php
		}
			?>
	</div>
</div>
