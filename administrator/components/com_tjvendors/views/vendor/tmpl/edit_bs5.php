<?php
/**
 * @package     TJVendors
 * @subpackage  com_tjvendors
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

HTMLHelper::addIncludePath(JPATH_COMPONENT . '/helpers/html');
HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');

$lang = Factory::getLanguage();
$lang->load('plg_payment_paypal', JPATH_ADMINISTRATOR);
HTMLHelper::script(Uri::root(true) . '/libraries/techjoomla/assets/js/tjvalidator.js');

$script   = array();
$script[] = 'var _URL = window.URL || window.webkitURL;';
$script[] = 'var allowedMediaSizeErrorMessage = "' . Text::_("COM_TJVENDORS_VENDOR_LOGO_SIZE_VALIDATE") . $this->params->get("image_size") . "KB" . '"';
$script[] = 'var allowedImageTypeErrorMessage = "' . Text::_("COM_TJVENDORS_VENDOR_LOGO_IMAGE_TYPE_VALIDATION") . '"';
$script[] = 'const vendorAllowedMediaSize = "' . $max_images_size = $this->params->get('image_size') * 1024 . '"';
$script[] = 'var vendor_id = "' . $this->item->vendor_id . '"';
$script[] = 'var client = "' . $this->client . '"';
$script[] = 'var layout = "default"';
$script[] = 'var country = "' . $this->item->country . '"';
$script[] = 'var region = "' . $this->item->region . '"';
$script[] = 'var city   = "' . $this->item->city . '"';
$script[] = 'let CommonObj = new tjvendor.UI.CommonUI()';
$script[] = 'tjVAdmin.vendor.initVendorJs();';

Factory::getDocument()->addScriptDeclaration(implode("\n", $script));
?>
<form action="<?php echo Route::_('index.php?option=com_tjvendors&layout=edit&client=' . $this->input->get('client', '', 'INTEGER') . '&vendor_id=' . (int) $this->item->vendor_id); ?>" method="post" enctype="multipart/form-data" name="adminForm" id="adminForm" class="form-validate">
	<div class="form-horizontal">
		<?php
			echo HTMLHelper::_('bootstrap.startTabSet', 'myTab', array('active' => 'personal'));
				echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'personal', Text::_('COM_TJVENDORS_TITLE_PERSONAL', true)); ?>
					<div class="row">
						<div>&nbsp;</div>
						<div class="col-sm-12 col-md-6 form-horizontal">
							<fieldset class="adminform">
								<input type="hidden" name="jform[vendor_id]" value="<?php echo $this->item->vendor_id; ?>" />
								<input type="hidden" name="jform[checked_out_time]" value="<?php echo $this->item->checked_out_time; ?>" />
								<input type="hidden" name="jform[checked_out]" value="<?php echo $this->item->checked_out; ?>" />
								<input type="hidden" name="jform[state]" value="<?php echo $this->item->state; ?>" />
								<input type="hidden" name="jform[ordering]" value="<?php echo $this->item->ordering; ?>" />
								<input type="hidden" name="jform[vendor_client]" value="<?php echo $this->input->get('client', '', 'STRING'); ?>" />
								<input type="hidden" name="jform[created_by]" value="<?php echo Factory::getUser()->id;?>" />
								<input type="hidden" name="jform[modified_by]" value="<?php echo (isset($this->item->vendor_id)) ? Factory::getUser()->id : '0';?>" />
								<input type="hidden" name="jform[created_time]" value="<?php echo $this->item->created_time; ?>" />
								<input type="hidden" name="jform[modified_time]" value="<?php echo $this->item->modified_time; ?>" />
								<?php
									echo $this->form->renderField('user_id');
									echo $this->form->renderField('client');
									echo $this->form->renderField('vendor_title');
									echo $this->form->renderField('alias');
									echo $this->form->renderField('state');
									echo $this->form->renderField('vendor_description');
									echo $this->form->renderField('vendor_logo');

									if (!empty($this->item->vendor_logo))
									{
										$this->vendorLogoProfileImg = $this->item->vendor_logo;
										$this->vendorLogoProfileImgPath = Uri::root() . $this->vendorLogoProfileImg;
									}
								?>
									<input type="hidden" name="jform[vendor_logo]" id="jform_vendor_logo_hidden"
									value="<?php echo $this->vendorLogoProfileImg?>" />
									<div class="control-group">
										<div class="control-label">
											<label>&nbsp;</label>
										</div>
										<div class="controls ">
											<img src="<?php echo $this->vendorLogoProfileImgPath; ?>"
											class="col-md-3 img-thumbnail marginb10 img-polaroid">
										</div>
									</div>
									<div class="control-group">
										<div class="control-label">
											<label>&nbsp;</label>
										</div>
										<div class="controls">
											<div class="alert alert-warning">
												<?php echo sprintf(Text::_("COM_TJVENDORS_FILE_UPLOAD_ALLOWED_EXTENSIONS"), 'jpg, jpeg, png');?>
											</div>
										</div>
									</div>
							</fieldset>
						</div>
						<div class="col-xs-12 col-md-6 form-horizontal">
							<fieldset class="adminform">
								<?php
								echo $this->form->renderField('phone_number');
								echo $this->form->renderField('address');
								?>
								<div class="control-group" id="country_group">
									<div class="control-label">
										<label for="jform_country">
											<?php echo $this->form->getLabel('country'); ?>
										</label>
									</div>
									<div class="controls">
										<?php
											echo $this->dropdown = HTMLHelper::_('select.genericlist', $this->options, 'jform[country]',
											'aria-invalid="false" class="form-select" size="1" onchange="CommonObj.generateStates(id,\'' .
											1 . '\',\'' . $this->item->region . '\',\'' . $this->item->city . '\')"', 'value', 'text', $this->default, 'jform_country');
										?>
									</div>
								</div>
								<?php
								echo $this->form->renderField('region');
								echo $this->form->renderField('city');
								echo $this->form->renderField('other_city');
								echo $this->form->renderField('zip');
								echo $this->form->renderField('website_address');
								echo $this->form->renderField('vat_number');?>
							</fieldset>
						</div>
					</div>
			<?php echo HTMLHelper::_('bootstrap.endTab');
			echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'name', Text::_('COM_TJVENDORS_TITLE_PAYMENT_DETAILS')); ?>
				<div class="mt-3"></div>
				<?php
				$paymentGatewayHtml = $this->form->getInput('payment_gateway');
				$paymentGatewayHtml = str_replace('control-group', 'form-group row mt-3', $paymentGatewayHtml);
				$paymentGatewayHtml = str_replace('control-label', 'form-label col-md-3', $paymentGatewayHtml);
				$paymentGatewayHtml = str_replace('controls', 'col-md-9', $paymentGatewayHtml);
				echo $paymentGatewayHtml;
			echo HTMLHelper::_('bootstrap.endTab');
		echo HTMLHelper::_('bootstrap.endTabSet'); ?>
		<input type="hidden" name="task" value=""/>
		<input type="hidden" name="client" value="<?php echo $this->input->get('client', '', 'STRING');?>"/>
		<?php echo HTMLHelper::_('form.token'); ?>
	</div>
</form>
