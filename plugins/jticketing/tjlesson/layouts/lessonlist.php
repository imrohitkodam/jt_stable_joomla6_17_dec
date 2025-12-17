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

use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('bootstrap.renderModal');

$data = $displayData;
$LessonName = $data['title'];
$target = '';

if (($data['launch_lesson_full_screen'] == "tab") &&($data['userStatus']['disableLesson'] == ""))
{
	$target = 'target="_blank"';
}

?>
<div class="<?php echo $data['pinclass'];?> tjlmspin">
	<div class="thumbnail af-p-0 af-br-0 tjlmspin__thumbnail">
		<div class="caption tjlmspin__caption">
			<div class="row">
				<div class="col-xs-12 col-md-5">
					<img class="af-d-inline pull-left af-pl-10" alt="<?php echo $data['format']; ?>" title="<?php echo ucfirst($data['format']); ?>"
						src="<?php echo Uri::root(true) . '/media/com_tjlms/images/default/icons/' . $data['format'] . '.png';?>"/>

					<div class="af-pl-10 af-text-truncate">
						<span class="af-d-inline fs-15" title="<?php echo ucfirst($LessonName);?>"><?php echo ucfirst($LessonName);?></span>
					</div>
				</div>

				<?php
				// Show Lesson Attempt's and Status to  Registered user only 
				?>
				<?php if (!empty(Factory::getUser()->id)){ ?>
					<div class="col-xs-12 col-md-3">
						<span class="af-pl-10">
							<?php if ($data['userStatus']['attemptsDone'] > 0){ ?>
								<b><?php echo Text::_("PLG_JTICKETING_TJLESSON_ATTEMPTS");?></b>
								&nbsp;<?php echo $data['userStatus']['attemptsDoneByAvailable'];?>
							<?php }else{ ?>
								<b><?php echo Text::_("PLG_JTICKETING_TJLESSON_NOT_VIEWED");?></b>
							<?php } ?>
						</span>
					</div>
					<div class="col-xs-12 col-md-2 text-uppercase af-font-600">
						<?php
						$lessonStatusCon = "";
	
						if(($data['userStatus']['status'] == 'completed')|| ($data['userStatus']['status'] == 'passed') || ($data['userStatus']['status'] == 'AP'))
							{
								$lessonStatusCon = "COMPLETED";
								$completionClass = 'label-success';
							}
							else if($data['userStatus']['status'] == 'failed')
							{
								$lessonStatusCon = "FAILED";
								$completionClass = 'label-danger';
							}
							else if($data['userStatus']['status'] == 'started')
							{
								$lessonStatusCon = "INCOMPLETE";
								$completionClass = 'label-warning';
							}
							else if($data['userStatus']['status'] == 'incomplete'){
								$lessonStatusCon = "INCOMPLETE";
								$completionClass = 'label-warning';
							}
							elseif ($data['userStatus']['status'] == 'not_started')
							{
								$lessonStatusCon = "NOT_STARTED";
								$completionClass = 'label-warning';
							}
							
							if(!empty($lessonStatusCon) && $data['userStatus']['attemptsDone'] > 0){?>
								<span class="label <?php echo $completionClass;?> af-ml-10">
									<?php echo $lessonStatusCon; ?>
								</span>
							<?php } ?>
					</div>
				<?php } ?>
				<div class="col-xs-12 col-md-2 text-center">	
					<?php if($data['userStatus']['launchButton'] == 1){ ?>
						<a class="af-br-0 btn btn-primary" title="<?php echo $this->escape($LessonName); ?>" href="<?php echo  $data['url']; ?>" <?php echo $target; ?> <?php echo $data['userStatus']['disableLesson'];?>>
							<?php echo Text::_("PLG_JTICKETING_TJLESSON_LAUNCH"); ?>
						</a>
					<?php }else{ 
						$lockIcon = "<i class='fa fa-lock mr-5' aria-hidden='true'></i>";
						?>
						<div class="col-xs-3">
						<button <?php echo $data['userStatus']['hoverTitle']; ?>
							class="af-br-0 btn btn-small bg-lightgrey" <?php echo $data['userStatus']['disableLesson'];?>><?php echo $lockIcon; ?>
								<span class="hidden-xs hidden-sm">
									<?php echo Text::_("PLG_JTICKETING_TJLESSON_LAUNCH"); ?>
								</span>
								<span class="glyphicon glyphicon-play hidden-md hidden-lg"></span>
							</button>
						</div>
					<?php } ?>
				</div>
			</div>
		</div>
	</div>
</div>

<script>
	jQuery(window).load(function () {

	jQuery('[rel="popover"]').on('hover', function (e) {
		jQuery('[rel="popover"]').not(this).popover('hide');
	});

	jQuery('[rel="popover"]').popover({
		html: true,
		trigger: 'hover',
		//container: this,
		placement: 'left',
		content: function () {
			return '<button type="button" id="close" class="close" onclick="popup_close(this);">&times;</button><div class="font-500">'+jQuery(this).attr('data-original-content')+'</div></div>';
		}
	});
});

function popup_close(btn)
{
	var div = jQuery(btn).closest('.popover').hide();
}
</script>
