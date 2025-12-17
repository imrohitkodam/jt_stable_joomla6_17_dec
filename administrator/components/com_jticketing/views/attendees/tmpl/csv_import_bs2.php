<?php
/**
 * @package	  Jticketing
 * @copyright Copyright (C) 2009 -2018 Techjoomla, Tekdi Web Solutions . All rights reserved.
 * @license   GNU GPLv2 <http://www.gnu.org/licenses/old-licenses/gpl-2.0.html>
 * @link      http://www.techjoomla.com
 */

// No direct access
defined('_JEXEC') or die();

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

?>
<div id="import_events" class="form-horizontal">
	<div id="uploadform" class="csv-import-user-select control-group">
		<div class="control-label">
			<input id="notify_user_enroll" type="checkbox" name="notify_user_enroll" value="1" checked="checked">
						<?php echo Text::_('COM_JTICKETING_NOTIFY_ENROLLED_USER'); ?>
		</div>
		<div class="controls">
			<div class="fileupload fileupload-new pull-left" data-provides="fileupload">
				<div class="input-append">
					<div class="uneditable-input span4">
						<span class="fileupload-preview">
							&nbsp;
						</span>
					</div>
					<span class="btn btn-file">
						<span class="fileupload-new"><?php echo Text::_("COM_JTICKETING_FILE_SELECT");?></span>
						<input type="file" id="user-csv-upload" name="csvfile"
						onchange="jQuery('.fileupload-preview').html(jQuery(this)[0].files[0].name);">
					</span>
					<button class="btn btn-primary" id="upload-submit"
					onclick="jtCommon.enrollment.validate_import(document.getElementsByName('csvfile'),'1','.csv-import-user-select'); return false;">
						<i class="icon-upload icon-white"></i>
						<?php echo Text::_('COMJTICKETING_EVENT_IMPORT_CSV'); ?>
					</button>
				</div>
			</div>
			<div class="clearfix"></div>
			<hr class="hr hr-condensed">
		</div>
		<div class="clearfix"></div>
		<div>
			<div class="alert alert-warning" role="alert"><i class="icon-info"></i>
				<?php
					$link = '<a href="' . $this->sampleAttendeeImportFilepath . '">' . Text::_("COM_JTICKETING_CSV_SAMPLE") . '</a>';
					echo Text::sprintf('COM_JTICKETING_CSVHELP_ATTENDEE', $link);
				?>
				<br>
				<?php
					$link = '<a href="' . $this->timezoneFilepath . '">' . Text::_("COM_JTICKETING_CATEGORY_LINK") . '</a>';
					echo Text::sprintf('COM_JTICKETING_TIMEZONE_CSVHELP', $link);
				?>
			</div>
		</div>
	</div>
</div>

