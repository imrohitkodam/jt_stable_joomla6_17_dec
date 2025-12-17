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
HTMLHelper::_('behavior.multiselect'); // only for list tables

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
	<form action="<?php echo Route::_('index.php?option=com_tjvendors&layout=edit&vendor_id=' . $this->input->get('vendor_id', '', 'INTEGER') . '&client=' . $this->input->get('client', '', 'STRING'). '&Itemid=' . $this->vendorFormItemId); ?>" method="post" enctype="multipart/form-data" name="adminForm" id="adminForm" class="form-validate">
		<div class="row">
			<div class="col-sm-12 vendorForm" id="tj-edit-form">
				<ul class="nav nav-tabs vendorForm__nav d-flex mb-15">
					<li class="active"><a data-toggle="tab" href="#tab1"><?php echo Text::_('COM_TJVENDORS_TITLE_PERSONAL'); ?></a> </li>
					<li><a data-toggle="tab" href="#tab2"><?php echo Text::_('COM_TJVENDORS_VENDOR_PAYMENT_GATEWAY_DETAILS'); ?></a></li>
				</ul>
				<!----Tab Container Start----->
				<div class="tab-content">
					<!----Tab 1 Start----->
					<div id="tab1" class="tab-pane fade in active">
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
									<?php
										echo $this->form->renderField('vendor_title');
										echo $this->form->renderField('alias');
										echo $this->form->renderField('vendor_description');
									?>
									<input type="hidden" name="jform[vendor_logo]" id="jform_vendor_logo_hidden" value="<?php echo $this->vendorLogoProfileImg ?>" />
									<div class="form-group">
										<div class="row">
											<div class="col-xs-12 col-sm-10 col-md-7">
												<img src="<?php echo $this->vendorLogoProfileImgPath; ?>">
											</div>
										</div>
										<div class="mt-10">
											<?php echo $this->form->renderField('vendor_logo'); ?>
										</div>
									</div>
									<div class="alert alert-info col-sm-8">
										<?php echo sprintf(Text::_("COM_TJVENDORS_MAXIMUM_LOGO_UPLOAD_SIZE_NOTE"), $this->params->get('image_size', '', 'STRING'));?>
									</div>
								</div>
								<div class="col-sm-6">
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
											<?php echo $this->dropdown = HTMLHelper::_('select.genericlist', $this->options, 'jform[country]',
												'aria-invalid="false" size="1" onchange="CommonObj.generateStates(id,\'' .
												0 . '\',\'' . $this->vendor->region . '\',\'' . $this->vendor->city . '\')"', 'value', 'text', $this->default, 'jform_country');
											?>
										</div>
									</div>
									<?php
										echo $this->form->renderField('region');
										echo $this->form->renderField('city');
										echo $this->form->renderField('other_city');
										echo $this->form->renderField('zip');
										echo $this->form->renderField('website_address');
										echo $this->form->renderField('vat_number');
									?>
								</div>
							</div>
						</fieldset>
					</div>
					<!----Tab 1 End----->
					<!----Tab 2 Start----->
					<div id="tab2" class="tab-pane fade">
						<div class="row">
							<?php echo $this->form->getInput('payment_gateway');?>
						</div>
					</div>
					<!----Tab 2 End----->
				</div>
				<!----Tab Container End----->
			</div>
		</div>

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
