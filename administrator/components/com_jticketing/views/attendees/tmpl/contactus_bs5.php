<?php
// no direct access
defined( '_JEXEC' ) or die();

use Joomla\CMS\Factory;
use Joomla\CMS\Editor\Editor;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

// Joomla 6: JVERSION check removed
		if (false) // Legacy < '4.0.0')
{
HTMLHelper::_('behavior.framework');
}
HTMLHelper::_('behavior.formvalidator');
$input =Factory::getApplication()->getInput();

// Send Email to Selected Attendee
Text::script('COM_JTICKETING_EMAIL_SUBJECT_ERROR_MSG');
Text::script('COM_JTICKETING_EMAIL_BODY_ERROR_MSG');
?>
<span id="ajax_loader"></span>
<div class="row-fluid">
 <form  name="adminForm" id="adminForm" class="form-validate form-horizontal" method="post" enctype="multipart/form-data">
	<div class="form-group row">
		<div class="form-label col-md-4"><?php echo  Text::_('COM_JTICKETING_ENTER_EMAIL_ID') ?> *</div>
		<div class="col-md-8">
			<textarea id="selected_emails" name="selected_emails" readonly="true" ><?php echo implode("," , $this->selectedEmails);?>
			</textarea>
			<input type="hidden" name="selected_emailids" id="selected_emailids" value="<?php echo implode("," , $this->selectedOrderItemIds);?>">
		</div>
	</div>

	<div class="form-group row">
		<div class="form-label col-md-4"><?php echo  Text::_('COM_JTICKETING_ENTER_EMAIL_SUBJECT') ?> *</div>
		<div class="col-md-8">
			<input type="text" id="jt-message-subject" name="jt-message-subject"  class="span2 required  " style="width:233px" placeholder="<?php echo  Text::_('COM_JTICKETING_ENTER_EMAIL_SUBJECT') ?>">
		</div>
	</div>

	<div class="form-group row">
		<div class="form-label col-md-4"><?php echo  Text::_('COM_JTICKETING_EMAIL_BODY') ?> *</div>
		<div class="col-md-8">
			<?php
			$getEditor  = Factory::getConfig()->get('editor');
			$editor     = Editor::getInstance($getEditor);
			echo $editor->display("jt-message-body", "", 670, 600, 60, 20, true);

			?>
		</div>
	</div>
	<input type="hidden" name="selected_order_items"  id="selected_order_items"  value="" />
	<input type="hidden" name="option" value="com_jticketing" />
	<input type="hidden" name="sendto" id="sendto"  value="<?php echo $sendto; ?>" />
	<input type="hidden" name="controller" value="attendee" />
	<input type="hidden" name="task" value="" />
</form>
</div>
<?php
Factory::getDocument()->addScriptDeclaration("
Joomla.submitbutton = function(task){jtCommon.attendees.attendeesSubmitButton(task,".$this->isAdmin.");}
	");
?>
