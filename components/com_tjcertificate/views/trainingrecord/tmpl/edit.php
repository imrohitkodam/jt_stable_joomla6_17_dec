<?php
/**
 * @package     TJCertificate
 * @subpackage  com_tjcertificate
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2020 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('bootstrap.renderModal');
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('bootstrap.framework');
HTMLHelper::StyleSheet('media/com_tjcertificate/css/tjCertificate.css');
HTMLHelper::_('jquery.token');
HTMLHelper::_('formbehavior.chosen', 'select');

$options['relative'] = true;
HTMLHelper::_('script', 'com_tjcertificate/tjCertificateService.min.js', $options);
HTMLHelper::_('script', 'com_tjcertificate/certificate.min.js', $options);
?>
<form action="" class="form-validate form-horizontal"
    method="post" name="adminForm" id="adminForm" enctype="multipart/form-data">
	<div class="container-fluid">
		<div class="row">
			<div class="col-sm-6">
					<h3 class="header">
						<?php
							echo (empty($this->item->id)) ? Text::_('COM_TJCERTIFICATE_ADD_EXTERNAL_CERTIFICATE') : Text::_('COM_TJCERTIFICATE_EDIT_EXTERNAL_CERTIFICATE');
						?>
					</h3>
				<div class="form-group" style="display:none;">
					<label class="col-sm-6"><?php echo $this->form->getLabel('id'); ?></label>
					<div class="col-sm-6"><?php echo $this->form->getInput('id'); ?></div>
				</div>
				<?php
					// Check agency is enabled
					if ($this->isAgencyEnabled && $this->manage)
					{
						echo $this->form->renderField('agency_id'); 
					}

					if ($this->manage)
					{
						echo $this->form->renderField('assigned_user_id');
					}

					 echo $this->form->renderField('name');
					 echo $this->form->renderField('unique_certificate_id');
					 echo $this->form->renderField('cert_url');
					 echo $this->form->renderField('issuing_org');
					 echo $this->form->renderField('issued_on');
					 echo $this->form->renderField('expired_on');
					 echo $this->form->renderField('status');

					 if ($this->manage)
					 {
						echo $this->form->renderField('state');
					 }
					 ?>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('cert_file'); ?></div>
					<div class="controls ">
						<?php echo $this->form->getInput('cert_file'); ?>
						 <?php 	
						 if (!empty($this->item->mediaData[0])) 
						 {
							$downloadAttachmentLink = Uri::root() . 'index.php?option=com_tjcertificate&task=trainingrecord.downloadAttachment&id=' . $this->item->mediaData[0]->media_id . '&recordId=' . $this->item->id;
							echo '<input type="hidden" name="oldFiles" value="'. $this->item->mediaData[0]->media_id . '">';
						?>
						<span class="help-block fileupload">
							<?php echo $this->item->mediaData[0]->title;?>
							<a
								class="p-5"
								href="<?php echo $downloadAttachmentLink;?>"
								target=""
								title="<?php echo $this->escape(strip_tags($this->item->mediaData[0]->title)); ?>">
								<i class="fa fa-download" aria-hidden="true"></i>
							</a>						
							<i class="fa fa-trash-o"
									title="<?php echo Text::_('COM_TJCERTIFICATE_ATTACHMENT_DELETE');?>"
									data-mid="<?php echo $this->item->mediaData[0]->media_id;?>"
									data-aid="<?php echo $this->item->id;?>"
									onclick="certificate.deleteAttachment(this)"></i>
						</span>
						<?php } ?>
					</div>
				</div>
					<?php
					 echo $this->form->renderField('created_by', null, null, ['class' => 'hidden']);
					 echo $this->form->renderField('comment'); 
				?>
			</div>
		</div>
	</div>	

	<div class="control-group">
		<div class="controls">
			<button onclick="Joomla.submitbutton('trainingrecord.save');" type="button" class="btn btn-primary"><?php echo Text::_('JSUBMIT'); ?></button>
			<button type="button" class="btn btn-default"  onclick="Joomla.submitbutton('trainingrecord.cancel')">
					<span><?php echo Text::_('JCANCEL'); ?></span>
			</button>
		</div>
	</div>
	
	<input type="hidden" id="assigned_user_id" value="<?php echo $this->item->user_id; ?>" />
	<input type="hidden" name="jform[id]" id="id" value="<?php echo $this->item->id; ?>" />
	<input type="hidden" name="option" value="com_tjcertificate"/>
	<input type="hidden" name="task" value="trainingrecord.save"/>
	<input type="hidden" name="site" value="f"/>
	<?php echo HTMLHelper::_('form.token'); ?>
</form>

<script type="text/javascript">
var allowedAttachments = '<?php echo $this->allowedFileExtensions; ?>';
var attachmentMaxSize  = '<?php echo $this->uploadLimit; ?>';

</script>
