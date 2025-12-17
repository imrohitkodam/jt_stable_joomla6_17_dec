<?php
/**
 * @package     JTicketing
 * @subpackage  com_jticketing
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

// Extract the display data which will have the $eventOptions
extract($displayData);

HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.keepalive');

// Joomla 6: JVERSION check removed
		if (false) // Legacy < '4.0.0')
{
	// Joomla 6: formbehavior.chosen removed - using native select
}
?>
<div class="contentpane component min-height-400">
<form action="<?php echo Route::_('index.php?option=com_jticketing&view=enrollment&layout=attendeemove&tmpl=component', false); ?>"
 method="post" name="adminForm1" id="adminForm1">
	<div id="enroll-user" class='row-fluid attendeemove-popup'>
		<div>
			<div class="control-label">
				<label id="jform_title-lbl" for="jform_title" class="hasTooltip required" title="<?php echo Text::_('COM_JTICKETING_SELECT_EVENT_TO_ENROLLMENT_DESCRIPTION') ?>">
					<?php echo Text::_('COM_JTICKETING_SELECT_EVENT_TO_ENROLLMENT'); ?><span class="star">&nbsp;*</span>
				</label>
			</div>
			<div class="controls selected-event">
				<?php
				echo HTMLHelper::_('select.genericlist', $eventOptions, 'selected_event', 'class="btn input-medium form-select" size="5" required="required" name="groupfilter"', "value", "text", '');
				?>
				<button class="btn btn-primary moveAttendeeBtn mt-2 float-end mb-2" type="submit" value="Submit"/><?php echo Text::_('COM_JTICKETING_MOVE_ATTENDEE_BUTTON'); ?></button>
			</div>
		</div>
	</div>
	<input type="hidden" name="task" value="enrollment.moveAttendee" />
	<input type="hidden" name="attendeeId" id="attendeeId" value="" />
	<input type="hidden" name="userId" id="userId" value="" />
	<input type="hidden" name="eventId" id="eventId" value="" />
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
</div>