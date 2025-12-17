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
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

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

// In case of modal
$isModal = $input->get('layout') == 'modal' ? true : false;
$layout  = $isModal ? 'modal' : 'edit';
$tmpl    = $isModal || $input->get('tmpl', '', 'cmd') === 'component' ? '&tmpl=component' : '';
?>
<div class="tj-page">
	<div class="row">
		<form action="<?php echo Route::_('index.php?option=com_tjcertificate&view=template&layout=edit&id=' . (int) $this->item->id . $clientUrlAppend, false);
		?>" method="post" enctype="multipart/form-data" name="adminForm" id="adminForm" class="form-validate">
			<div class="form-vertical">
				<?php echo HTMLHelper::_('bootstrap.startTabSet', 'myTab', array('active' => 'general')); ?>
				<?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'general', Text::_('COM_TJCERTIFICATE_VIEW_CERTIFICATE_TEMPLATES')); ?>
				<br>
				<div class="row">
					<div class="col-sm-8">
						<?php echo $this->form->renderField('title'); ?>
						<?php

							if ($this->item->id == 0)
							{
								echo $this->form->renderField('unique_code');
							}
							else
							{
								$this->form->setFieldAttribute('unique_code', 'readonly', 'true');
								echo $this->form->renderField('unique_code');
							}
						?>
						<?php echo $this->form->renderField('sample_template'); ?>
						<?php echo $this->form->renderField('body'); ?>
						<?php echo $this->form->renderField('client'); ?>
						<?php echo $this->form->renderField('is_public'); ?>
						<?php echo $this->form->renderField('state'); ?>

						<?php echo $this->form->getInput('created_by'); ?>
						<?php echo $this->form->getInput('modified_on'); ?>
						<?php echo $this->form->getInput('modified_by'); ?>
						<?php echo $this->form->getInput('ordering'); ?>
						<?php echo $this->form->getInput('checked_out'); ?>
						<?php echo $this->form->getInput('checked_out_time'); ?>
					</div>
					<div class="col-sm-4">
						<div class="alert alert-info">
							<?php echo Text::_('COM_TJCERTIFICATE_CERTIFICATE_TEMPLATE_CSS_EDITOR_INFO'); ?>
						</div>
						<table class="table">
							<thead class="thead-default">
								<tr>
									<th>
										<?php echo $this->form->getInput('template_css');?>
									</th>
								</tr>
							</thead>
						</table>

						<div class="alert alert-info">
							<?php echo Text::_('COM_TJCERTIFICATE_CERTIFICATE_TEMPLATE_REPLACEMENT_TAG_INFO'); ?>
						</div>
						<table class="table table-bordered">
							<thead class="thead-default">
								<tr>
									<th><?php echo Text::_('COM_TJCERTIFICATE_CERTIFICATE_TEMPLATE_REPLACEMENT_TAG_TITLE'); ?></th>
									<th><?php echo Text::_('COM_TJCERTIFICATE_CERTIFICATE_TEMPLATE_REPLACEMENT_TAG_DESC'); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php

								if (!empty($this->replacementTags))
								{
									$this->replacementTags = json_decode($this->replacementTags);

									foreach ($this->replacementTags as $tags)
									{
									?>
									<tr>
										<td scope="row"><?php echo '{' . $this->escape($tags->name) . '}'; ?></td>
										<td><?php echo $this->escape($tags->description); ?></td>
									</tr>
									<?php
									}
								} ?>
							</tbody>
						</table>
					</div>
				</div>
				<?php echo HTMLHelper::_('bootstrap.endTab'); ?>
				<br>
				<?php echo LayoutHelper::render('joomla.edit.params', $this); ?>
				<input type="hidden" name="task" value="" />
				<input type="hidden" name="client" value="<?php echo $client; ?>" />
				<input type="hidden" name="extension" value="<?php echo $extension; ?>" />
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

		template.previewTemplate('jform_body');

		jQuery(document).on("change", "#jform_sample_template", function () {
			template.loadDefaultTemplate(this.value);
		});
	});

</script>
