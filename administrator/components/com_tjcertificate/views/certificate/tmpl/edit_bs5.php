<?php
/**
 * @package     TJCertificate
 * @subpackage  com_tjcertificate
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Layout\LayoutHelper;

HTMLHelper::addIncludePath(JPATH_COMPONENT . '/helpers/html');

HTMLHelper::_('jquery.token');
HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');

$options['relative'] = true;
HTMLHelper::_('script', 'com_tjcertificate/tjCertificateService.min.js', $options);
HTMLHelper::_('script', 'com_tjcertificate/template.min.js', $options);

$app = Factory::getApplication();
$input = $app->input;

$client    = $input->getCmd('client', '');
$extension = $input->getCmd('extension', '');

$clientUrlAppend = '';

if (!empty($extension))
{
	$clientUrlAppend = '&extension=' . $extension;
}
elseif (!empty($client))
{
	$clientUrlAppend = '&client=' . $client;
}

?>
<div class="tj-page">
	<div class="row-fluid">
	<form action="<?php echo Route::_('index.php?option=com_tjcertificate&view=certificate&layout=edit&id=' . (int) $this->item->id . $clientUrlAppend, false);?>"
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
		<div class="row-fluid">
			<?php echo $this->form->renderField('unique_certificate_id'); ?>
			<?php echo $this->form->renderField('certificate_template_id'); ?>
			<?php echo $this->form->renderField('generated_body'); ?>
			<?php echo $this->form->renderField('client'); ?>
			<?php echo $this->form->renderField('client_id'); ?>
			<?php echo $this->form->renderField('user_id'); ?>
			<?php echo $this->form->renderField('comment'); ?>
			<?php echo $this->form->renderField('issued_on'); ?>
			<?php echo $this->form->renderField('expired_on'); ?>
			<?php echo $this->form->renderField('state'); ?>
		</div>
		<input type="hidden" name="task" value="" />
		<?php echo HTMLHelper::_('form.token'); ?>
	</div>
	</form>
</div>
</div>
<!-- Modal -->
<?php
	echo LayoutHelper::render('preview');
?>
<script type="text/javascript">

	jQuery(document).ready(function () {

		template.previewTemplate('jform_generated_body');

		jQuery(document).on("change", "#jform_certificate_template_id", function () {
			template.loadCustomTemplate(this.value);
		});
	});

</script>
