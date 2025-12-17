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
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

// Extract the display data which will have the $eventOptions
extract($displayData);

HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.keepalive');
// Joomla 6: formbehavior.chosen removed - using native select
?>
<div class="contentpane component p-1 ms-3">
	<form action="<?php echo Route::_('index.php?option=com_jticketing&view=enrollment&layout=attendeemove&tmpl=component', false); ?>"
	 method="post" name="adminForm1" id="adminForm1">
		<div id="enroll-user" class='row justify-content-center align-items-center my-4 '>
			<div class="col-md-5">
				<div class="control-label">
					<label id="jform_title-lbl" for="jform_title" class="hasTooltip required af-font-600 mb-2 ms-2" title="<?php echo Text::_('COM_JTICKETING_SELECT_EVENT_TO_ENROLLMENT_DESCRIPTION') ?>">
						<?php echo Text::_('COM_JTICKETING_SELECT_EVENT_TO_ENROLLMENT'); ?><span class="star">&nbsp;*</span>
					</label>
					<?php
						echo HTMLHelper::_('select.genericlist', $eventOptions, 'selected_event', 'class="btn input-medium" size="10" name="groupfilter"', "value", "text", '');
					?>
				</div>
			</div>
			<div class="col-md-7 text-start mt-2">
				<button class="btn btn-primary pull-right ms-5 mt-4" type="submit" value="Submit"/><?php echo Text::_('COM_JTICKETING_MOVE_ATTENDEE_BUTTON'); ?></button>
			</div>
		</div>
		<input type="hidden" name="task" value="enrollment.moveAttendee" />
		<input type="hidden" name="attendeeId" id="attendeeId" value="" />
		<input type="hidden" name="userId" id="userId" value="" />
		<input type="hidden" name="eventId" id="eventId" value="" />
		<?php echo HTMLHelper::_('form.token'); ?>
	</form>
</div>
