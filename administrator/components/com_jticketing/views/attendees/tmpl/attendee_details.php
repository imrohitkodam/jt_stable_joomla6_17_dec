<?php
/**
 * @version    SVN: <svn_id>
 * @package    Com_Jticekting
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2018 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

$input = Factory::getApplication()->getInput();
$attendee_id = $input->get('attendee_id', '', 'INT');
$data_present = 0;
?>

<div class="page-header">
	<h3><?php echo Text::_('COM_JTICKETING_ATTENDEE_DETAILS_HEADER'); ?></h3>
</div>

<div class="row-fuild jticketing-controls">
	<div class="form-horizontal ">
		<?php
		if (!empty($this->extraFieldslabel))
		{
			foreach ($this->extraFieldslabel as $field_data)
			{
				if (!empty($field_data->attendee_value[$attendee_id]->field_value))
				{
					$data_present = 1;
					?>
					<div class="control-group">
						<label class="control-label af-font-600">
							<?php echo Text::_($field_data->label);?>
						</label>
						<div class="controls">
							<?php echo $this->escape($field_data->attendee_value[$attendee_id]->field_value);?>
						</div>
					</div>
					<?php
				}
			}
		}

		if (!empty($this->customerNote))
		{
			?>
			<div class="control-group">
				<label class="control-label af-font-600">
					<?php echo Text::_('COM_JTICKETING_USER_COMMENT'); ?>
				</label>
				<div class="controls">
					<?php echo $this->escape($this->customerNote); ?>
				</div>
			</div>
			<?php
		}

		if ($data_present == 0)
		{
			echo Text::_('COM_JTICKETING_NO_DATA_PRESENT_ATTENDEE');
		}
		?>
	</div>
</div>
<!--ROW_FUILD ENDS-->
