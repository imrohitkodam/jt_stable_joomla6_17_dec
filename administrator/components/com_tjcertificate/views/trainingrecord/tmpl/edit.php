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

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Uri\Uri;

HTMLHelper::addIncludePath(JPATH_COMPONENT . '/helpers/html');

HTMLHelper::_('jquery.token');
if (JVERSION < '4.0.0')
{
HTMLHelper::_('behavior.framework');
}
HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');
// Joomla 6: formbehavior.chosen removed - using native select

$options['relative'] = true;
HTMLHelper::_('script', 'com_tjcertificate/tjCertificateService.min.js', $options);
HTMLHelper::_('script', 'com_tjcertificate/certificate.min.js', $options);
?>
<div class="tj-page">
	<div class="row-fluid">
	<form action="<?php echo Route::_('index.php?option=com_tjcertificate&view=trainingrecord&layout=edit&id=' . (int) $this->item->id, false);?>"
	 method="post" enctype="multipart/form-data" name="adminForm" id="adminForm" class="form-validate">
	 <?php if (!empty( $this->sidebar))
		{
		?>
			<div id="j-sidebar-container" class="span2">
				<?php echo $this->sidebar; ?>
			</div>
			<div id="j-main-container" class="span10">
		<?php
		}
		else
		{
			?>
			<div id="j-main-container">
		<?php
		}
		?>
		<div class="form-horizontal">
		<?php
			echo HTMLHelper::_('bootstrap.startTabSet', 'myTab', array('active' => 'general'));
			echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'general', Text::_('COM_TJCERTIFICATE_TITLE_CERTIFICATE')); ?>
		<div class="row-fluid">
			<?php echo $this->form->renderField('id'); ?>

			<?php
				if ($this->isAgencyEnabled)
				{
					echo $this->form->renderField('agency_id');
				}
			?>

			<?php echo $this->form->renderField('assigned_user_id'); ?>
			<?php echo $this->form->renderField('name'); ?>
			<?php echo $this->form->renderField('unique_certificate_id'); ?>
			<?php echo $this->form->renderField('cert_url'); ?>
			<?php echo $this->form->renderField('issuing_org'); ?>
			<?php echo $this->form->renderField('issued_on'); ?>
			<?php echo $this->form->renderField('expired_on'); ?>
			<?php echo $this->form->renderField('status'); ?>
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
							<i class="icon-download" aria-hidden="true"></i>
						</a>
						<i class="icon-trash"
								title="<?php echo Text::_('COM_TJCERTIFICATE_ATTACHMENT_DELETE');?>"
								data-mid="<?php echo $this->item->mediaData[0]->media_id;?>"
								data-aid="<?php echo $this->item->id;?>"
								onclick="certificate.deleteAttachment(this)"></i>
					</span>
					<?php } ?>
				</div>
			</div>
			<?php echo $this->form->renderField('state'); ?>
			<?php echo $this->form->renderField('comment'); ?>

		</div>
		<input type="hidden" id="assigned_user_id" value="<?php echo $this->item->user_id; ?>" />
		<input type="hidden" name="jform[created_by]" value="<?php echo Factory::getUser()->id;?>" />
		<input type="hidden" name="task" value="" />
		<?php echo HTMLHelper::_('form.token'); ?>
	</div>
	</form>
</div>
</div>
<?php
	echo LayoutHelper::render('preview');
?>
<script type="text/javascript">
var allowedAttachments = '<?php echo $this->allowedFileExtensions; ?>';
var attachmentMaxSize  = '<?php echo $this->uploadLimit; ?>';

</script>
