<?php
/**
 * @package     JTicketing
 * @subpackage  com_jticketing
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

$attendee_records_config = 5;
if ($this->item->allow_view_attendee == 1 && count($this->item->eventAttendeeInfo) >= 1)
{
	?>
	<div class="row">
		<div class="col-sm-12 d-flex justify-content-center my-1">
			<span class="searchAttendee af-float-right col-sm-6 d-flex justify-content-center my-3" id="SearchAttendeeinputbox">
				<input type="text" 
				id="attendeeInput" 
				onkeyup="jtSite.event.searchAttendee()" 
				class="af-mb-5 form-control w-75"
				placeholder="<?php echo Text::_('COM_JGIVE_EVENT_ATTENDEE_SEARCH_PLACEHOLDER');?>" 
				title="<?php echo Text::_('COM_JGIVE_EVENT_ATTENDEE_SEARCH_PLACEHOLDER');?>"
				size="35">
			</span>
		</div>
	</div>
	<div class="row">
		<div class="col-sm-12 no-more-tables">
			<table class="table user-list" id="eventAttender">
				<tbody id="jticketing_attendee_pic">
					<?php
						$j = 1;

						foreach ($this->item->eventAttendeeInfo as $this->eventAttendeeInfo)
						{
							if ($j <= $attendee_records_config)
							{
								echo $this->loadTemplate("attendeelist");
							}
							else
							{
								break;
							}
							
							$j++;
						}
					?>
				</tbody>
			</table>
		</div>
	</div>
	<input type="hidden" id="attendee_pro_pic_index" value="<?php echo $j; ?>" />
	<input type="hidden" id="event_id" name="event_id" value="<?php echo $this->item->id;?>"/>
	<?php
		if ($this->item->eventAttendeeCount > $attendee_records_config  && $this->item->allow_view_attendee == 1)
		{
		?>
			<button id="btn_showMorePic" class="btn btn-info btn-md pull-right" type="button" onclick="jtSite.event.viewMoreAttendee()">
				<?php
					echo Text::_('COM_JTICKETING_SHOW_MORE_ATTENDEE');
				?>
			</button>
		<?php
		}
}?>

<script type="text/javascript">
var gbl_jticket_index = 0 ;
var attedee_count = <?php
							if ($this->item->eventAttendeeCount)
							{
								echo $this->item->eventAttendeeCount;
							}
							else
							{
								echo 0;
							}
							?>;
var gbl_jticket_pro_pic = 0;
var jticket_baseurl = "<?php echo Uri::root(); ?>";
</script>
