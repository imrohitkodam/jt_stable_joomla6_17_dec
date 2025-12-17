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

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

HTMLHelper::addIncludePath(JPATH_COMPONENT . '/helpers/html');
HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');
HTMLHelper::script(Uri::root(true) . '/libraries/techjoomla/assets/js/tjvalidator.js');

$script   = array();
$script[] = 'var layout = "edit"';
$script[] = 'let CommonObj = new tjvendor.UI.CommonUI();';
$script[] = 'var _URL = window.URL || window.webkitURL;';
$script[] = 'var allowedMediaSizeErrorMessage = "' . Text::_("COM_TJVENDORS_VENDOR_LOGO_SIZE_VALIDATE") . $this->params->get("image_size") . "KB" . '"';
$script[] = 'var allowedImageDimensionErrorMessage = "' . Text::_("COM_TJVENDORS_VENDOR_LOGO_DIMENSIONS_VALIDATE") . '"';
$script[] = 'var allowedImageTypeErrorMessage = "' . Text::_("COM_TJVENDORS_VENDOR_LOGO_IMAGE_TYPE_VALIDATION") . '"';
$script[] = 'const vendorAllowedMediaSize = "' . $max_images_size = $this->params->get("image_size") * 1024 . '"';
$script[] = 'var country = "' . $this->vendor->country . '"';
$script[] = 'var region = "' . $this->vendor->region . '"';
$script[] = 'var city   = "' . $this->vendor->city . '"';
$script[] = 'tjVSite.vendor.initVendorJs();';

Factory::getDocument()->addScriptDeclaration(implode("\n", $script));
?>
<div id="tjv-wrapper" class="<?php echo COM_TJVENDORS_WRAPPAER_CLASS;?>">
<?php
if (Factory::getUser()->id)
{
	?>
	<h1>
		<?php
		if ($this->vendor_id)
		{
			echo Text::_('COM_TJVENDOR_UPDATE_VENDOR');
			echo ':&nbsp' . htmlspecialchars($this->vendor->vendor_title, ENT_COMPAT, 'UTF-8');
		}
		else
		{
			echo Text::_('COM_TJVENDOR_CREATE_VENDOR');
		}
		?>
	</h1>
	<form action="<?php echo Route::_('index.php?option=com_tjvendors&layout=edit&vendor_id=' . $this->input->get('vendor_id', '', 'INTEGER') . '&client=' . $this->input->get('client', '', 'STRING') . '&Itemid=' . $this->vendorFormItemId); ?>" method="post" enctype="multipart/form-data" name="adminForm" id="adminForm" class="form-validate">
		<div class="container">
		<div class="row">
			<div class="col-sm-12 vendorForm" id="tj-edit-form">
				<ul class="nav nav-tabs vendorForm__nav d-flex mb-15" id="myTab">
					<li class="nav-item"><a class="nav-link active" href="javascript:void(0);" data-bs-toggle="tab" data-bs-target="#tab1"><?php echo Text::_('COM_TJVENDORS_TITLE_PERSONAL'); ?></a> </li>
					<li class="nav-item"><a class="nav-link" href="javascript:void(0);" data-bs-toggle="tab" data-bs-target="#tab2"><?php echo Text::_('COM_TJVENDORS_VENDOR_PAYMENT_GATEWAY_DETAILS'); ?></a></li>
				</ul>
				<!----Tab Container Start----->
				<div class="tab-content">
					<!----Tab 1 Start----->
					<div id="tab1" class="tab-pane in active">
						<fieldset class="adminform">
							<input type="hidden" name="jform[vendor_id]" value="<?php echo $this->vendor_id; ?>" />
							<input type="hidden" name="jform[checked_out_time]" value="<?php echo $this->vendor->checked_out_time; ?>" />
							<input type="hidden" name="jform[checked_out]" value="<?php echo $this->vendor->checked_out; ?>" />
							<input type="hidden" name="jform[ordering]" value="<?php echo $this->vendor->ordering; ?>" />
							<input type="hidden" name="jform[state]" value="<?php echo $this->vendor->state; ?>" />
							<input type="hidden" name="jform[created_by]" value="<?php echo Factory::getUser()->id;?>" />
							<input type="hidden" name="jform[modified_by]"
							value="<?php echo (isset($this->vendor_id)) ? Factory::getUser()->id : '0';?>" />
							<input type="hidden" name="jform[created_time]" value="<?php echo $this->vendor->created_time; ?>" />
							<input type="hidden" name="jform[modified_time]" value="<?php echo $this->vendor->modified_time; ?>" />
							<?php
							$input = Factory::getApplication()->getInput();

							if (!empty($this->vendor->vendor_logo))
							{
								$this->vendorLogoProfileImg = $this->vendor->vendor_logo;
								$this->vendorLogoProfileImgPath = Uri::root() . $this->vendorLogoProfileImg;
							}
							?>

							<div class="row">
								<div class="col-sm-6">
									<div class="form-group row mt-2">
										<div class="col-sm-3 col-xs-12 form-label"><?php echo $this->form->getLabel('vendor_title'); ?></div>
										<div class="col-sm-9 col-xs-12"><?php echo $this->form->getInput('vendor_title'); ?></div>
									</div>
									<div class="form-group row mt-2">
										<div class="col-sm-3 col-xs-12 form-label"><?php echo $this->form->getLabel('alias'); ?></div>
										<div class="col-sm-9 col-xs-12"><?php echo $this->form->getInput('alias'); ?></div>
									</div>
									<div class="form-group row mt-2">
										<div class="col-sm-3 col-xs-12 form-label"><?php echo $this->form->getLabel('vendor_description'); ?></div>
										<div class="col-sm-9 col-xs-12"><?php echo $this->form->getInput('vendor_description'); ?></div>
									</div>
									<div class="form-group row mt-2">
										<div class="col-sm-3 col-xs-12"></div>
										<div class="col-sm-9 col-xs-12">
											<img src="<?php echo $this->vendorLogoProfileImgPath; ?>">
										</div>
										<div class="mt-10">
											<div class="form-group row mt-2">
												<div class="col-sm-3 col-xs-12 form-label"><?php echo $this->form->getLabel('vendor_logo'); ?></div>
												<div class="col-sm-9 col-xs-12"><?php echo $this->form->getInput('vendor_logo'); ?></div>
											</div>
										</div>
									</div>
									<div class="alert alert-info col-sm-8">
										<?php echo sprintf(Text::_("COM_TJVENDORS_MAXIMUM_LOGO_UPLOAD_SIZE_NOTE"), $this->params->get('image_size', '', 'STRING'));?>
									</div>
								</div>
								<div class="col-sm-6">
									<div class="form-group row mt-2">
										<div class="col-sm-3 col-xs-12 form-label"><?php echo $this->form->getLabel('phone_number'); ?></div>
										<div class="col-sm-9 col-xs-12"><?php echo $this->form->getInput('phone_number'); ?></div>
									</div>
									<div class="form-group row mt-2">
										<div class="col-sm-3 col-xs-12 form-label"><?php echo $this->form->getLabel('address'); ?></div>
										<div class="col-sm-9 col-xs-12"><?php echo $this->form->getInput('address'); ?></div>
									</div>
									<div class="form-group row mt-2" id="country_group">
										<div class="col-sm-3 col-xs-12 form-label">
											<label for="jform_country">
												<?php echo $this->form->getLabel('country'); ?>
											</label>
										</div>
										<div class="col-sm-9 col-xs-12">
											<?php echo $this->dropdown = HTMLHelper::_('select.genericlist', $this->options, 'jform[country]',
												'class="form-select" aria-invalid="false" size="1" onchange="CommonObj.generateStates(id,\'' .
												0 . '\',\'' . $this->vendor->region . '\',\'' . $this->vendor->city . '\')"', 'value', 'text', $this->default, 'jform_country');
											?>
										</div>
									</div>
									<div class="form-group row mt-2">
										<div class="col-sm-3 col-xs-12 form-label"><?php echo $this->form->getLabel('region'); ?></div>
										<div class="col-sm-9 col-xs-12"><?php echo $this->form->getInput('region'); ?></div>
									</div>
									<div class="form-group row mt-2">
										<div class="col-sm-3 col-xs-12 form-label"><?php echo $this->form->getLabel('city'); ?></div>
										<div class="col-sm-9 col-xs-12"><?php echo $this->form->getInput('city'); ?></div>
									</div>
									<div class="form-group row mt-2">
										<div class="col-sm-3 col-xs-12 form-label"><?php echo $this->form->getLabel('other_city'); ?></div>
										<div class="col-sm-9 col-xs-12"><?php echo $this->form->getInput('other_city'); ?></div>
									</div>
									<div class="form-group row mt-2">
										<div class="col-sm-3 col-xs-12 form-label"><?php echo $this->form->getLabel('zip'); ?></div>
										<div class="col-sm-9 col-xs-12"><?php echo $this->form->getInput('zip'); ?></div>
									</div>
									<div class="form-group row mt-2">
										<div class="col-sm-3 col-xs-12 form-label"><?php echo $this->form->getLabel('website_address'); ?></div>
										<div class="col-sm-9 col-xs-12"><?php echo $this->form->getInput('website_address'); ?></div>
									</div>
									<div class="form-group row mt-2">
										<div class="col-sm-3 col-xs-12 form-label"><?php echo $this->form->getLabel('vat_number'); ?></div>
										<div class="col-sm-9 col-xs-12"><?php echo $this->form->getInput('vat_number'); ?></div>
									</div>
								</div>
							</div>
						</fieldset>
					</div>
					<!----Tab 1 End----->
					<!----Tab 2 Start----->
					<div id="tab2" class="tab-pane">
						<div class="row">
							<?php
								$this->form->setFieldAttribute('payment_gateway', 'layout', '');
								$html = $this->form->getInput('payment_gateway');
								$html = str_replace('custom-select', 'form-select', $html);
								$html = str_replace('btn-mini', 'btn-sm', $html);
								$html = str_replace('text-right', 'text-right  float-end', $html);
								$html = str_replace('control-group', 'form-group row', $html);
								$html = str_replace('control-label', 'form-label col-md-4', $html);
								$html = str_replace('controls', 'col-md-8', $html);
								echo $html;
							?>
						</div>
					</div>
					<!----Tab 2 End----->
				</div>
				<!----Tab Container End----->
			</div>
		</div>
		<br>
		<div class="mt-10">
			<button type="button" class="btn btn-default btn-primary"  onclick="Joomla.submitbutton('vendor.save')">
				<span><?php echo Text::_('JSUBMIT'); ?></span>
			</button>
			<button class="btn  btn-default" onclick="Joomla.submitbutton('vendor.cancel')">
				<?php echo (!$this->isClientExist) ? Text::_('COM_TJVENDORS_CLIENT_REJECTION') : Text::_('JCANCEL'); ?>
			</button>
		</div>
		<input type="hidden" name="task" value="vendor.save"/>
		<?php echo HTMLHelper::_('form.token'); ?>
		</div>
	</form>
	<?php
}
else
{
	$link = Route::_('index.php?option=com_users&view=login');
	$app = Factory::getApplication();
	$app->redirect($link);
}
?>
</div>
