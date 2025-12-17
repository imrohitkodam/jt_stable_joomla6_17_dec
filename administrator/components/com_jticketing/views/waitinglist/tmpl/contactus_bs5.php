<?php
/**
 * @version    SVN: <svn_id>
 * @package    Com_Jticekting
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2018 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Editor\Editor;

HTMLHelper::_('behavior.formvalidator');
?>

<form  name="adminForm" id="adminForm" class="form-validate" method="post" enctype="multipart/form-data">
	<div class="form-horizontal">
		<div class="row-fluid">
			<fieldset>
				<div class="control-group">
					<div class="control-label"><?php echo Text::_('COM_JTICKETING_ENTER_EMAIL_ID') ?> *</div>
					<div class="controls">
						<textarea id="selected_emails" name="selected_emails" class="form-control required" style="width:670px" readonly="true" >
						<?php echo implode(",", $this->selectedEmails);?></textarea>
					</div>
				</div>

				<div class="control-group">
					<div class="control-label"><?php echo Text::_('COM_JTICKETING_ENTER_EMAIL_SUBJECT') ?> *</div>
					<div class="controls">
						<input type="text" id="jt-message-subject" name="jt-message-subject"
						class="form-control required" style="width:670px" placeholder="<?php echo  Text::_('COM_JTICKETING_ENTER_EMAIL_SUBJECT') ?>">
					</div>
				</div>

				<div class="control-group">
					<div class="control-label"><?php echo Text::_('COM_JTICKETING_EMAIL_BODY') ?> *</div>
					<div class="controls">
						<?php
						$getEditor  = Factory::getConfig()->get('editor');
						$editor     = Editor::getInstance($getEditor);
						echo $editor->display("jt-message-body", "", 670, 600, 60, 20, true);
						?>
					</div>
				</div>
			</fieldset>
		</div>
		
		<input type="hidden" name="waitlist_id"  id="waitlist_id"  value="" />
		<input type="hidden" name="option" value="com_jticketing" />
		<input type="hidden" name="sendto" id="sendto"  value="<?php echo $sendto; ?>" />
		<input type="hidden" name="controller" value="waitinglist" />
		<input type="hidden" name="task" value="" />
		<?php echo HTMLHelper::_('form.token'); ?>


	</div>
</form>

