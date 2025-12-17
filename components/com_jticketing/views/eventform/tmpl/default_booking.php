<?php
/**
 * @package     JTicketing
 * @subpackage  com_jticketing
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

/** @var $this JticketingViewEventform */

?>
<div class="event_details_form af-mt-10">
	<div class="row">
		<div class="col-sm-4 col-xs-12 af-mb-10">
			<?php echo $this->form->getLabel('booking_start_date');?>
			<?php echo $this->form->getInput('booking_start_date');?>
		</div>

		<div class="col-sm-4 col-xs-12 af-mb-10">
			<?php echo $this->form->getLabel('booking_end_date');?>
			<?php echo $this->form->getInput('booking_end_date');?>
		</div>


		<?php $idealTime = $this->params->get('enable_ideal_time');

		if (!empty($idealTime))
		{
			?>
		<div class="col-sm-6 col-xs-12 af-mb-10">
			<?php echo $this->form->getLabel('ideal_time'); ?>
			<?php echo $this->form->getInput('ideal_time');?>
		</div>

			<?php
		}
			?>
	</div>
</div>
