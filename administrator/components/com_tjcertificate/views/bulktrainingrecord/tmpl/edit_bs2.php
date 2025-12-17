<?php
/**
 * @package     TJCertificate
 * @subpackage  com_tjcertificate
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2021 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

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
HTMLHelper::_('script', 'media/com_tjcertificate/vendors/loader/js/loadingoverlay.min.js');
HTMLHelper::_('script', 'com_tjcertificate/tjCertificateService.min.js', $options);
HTMLHelper::_('script', 'com_tjcertificate/certificate.min.js', $options);

$userLimit = $this->params->get('users_select_limit');
$message = Text::sprintf("COM_TJCERTIFICATE_USER_LIMIT_MESSAGE", $userLimit);

?>
<div class="tj-page">
	<div class="row-fluid">
	<form action=""
	 method="post" enctype="multipart/form-data" name="adminForm" id="adminForm" class="form-validate add-records">
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
			<?php
				if ($this->isAgencyEnabled)
				{
					echo $this->form->renderField('agency_id');
				}
			?>

			<?php echo $this->form->renderField('assigned_user_id'); ?>
			<?php echo $this->form->renderField('name'); ?>
			<?php echo $this->form->renderField('issuing_org'); ?>
			<?php echo $this->form->renderField('issued_on'); ?>
			<?php echo $this->form->renderField('expired_on'); ?>
			<?php echo $this->form->renderField('status'); ?>
			<?php echo $this->form->renderField('state'); ?>
			<?php echo $this->form->renderField('notify_users'); ?>

		</div>
		<input type="hidden" name="jform[created_by]" value="<?php echo Factory::getUser()->id;?>" />
		<input type="hidden" name="task" value="bulktrainingrecord.save" />
		<?php echo HTMLHelper::_('form.token'); ?>
	</div>
	</form>
</div>
</div>
<script type="text/javascript">

	var userLimit = '<?php echo $userLimit;?>';
	var message = '<?php echo $message;?>';

	jQuery("#jform_assigned_user_id").chosen({max_selected_options: userLimit});
	jQuery("#jform_assigned_user_id").bind("liszt:maxselected", function () {
		Joomla.renderMessages({'error': [message] });
	});

</script>
