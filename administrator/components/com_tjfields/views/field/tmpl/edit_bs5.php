<?php
/**
 * @version    SVN: <svn_id>
 * @package    Tjfields
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2016 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */
// no direct access
defined('_JEXEC') or die;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;

HTMLHelper::addIncludePath(JPATH_COMPONENT . '/helpers/html');
HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');

$input = Factory::getApplication()->getInput();
$fullClient = $input->get('client', '', 'STRING');
$fullClient =  explode('.', $fullClient);

$client = $fullClient[0];
$clientType = $fullClient[1];

$link = Route::_('index.php?option=com_tjfields&view=field&layout=edit&id=0&client=' . $input->get('client', '', 'STRING'), false);

// Import helper for declaring language constant
JLoader::import('TjfieldsHelper', Uri::root() . 'administrator/components/com_tjfields/helpers/tjfields.php');
// Call helper function
TjfieldsHelper::getLanguageConstant();

// Import CSS - Updated for Joomla 6
$wa = Factory::getApplication()->getDocument()->getWebAssetManager();

// CRITICAL: Load jQuery and core FIRST with proper dependencies
$wa->useScript('core');
$wa->useScript('jquery');
$wa->useScript('jquery-noconflict'); // Required for Joomla

// Register and use CSS with proper path
$wa->registerAndUseStyle(
	'com_tjfields.field.edit',
	'administrator/components/com_tjfields/assets/css/tjfields.css',
	[],
	[],
	[]
);

// Import JS - Updated for Joomla 6 - Load with jQuery dependency
$wa->registerAndUseScript(
	'com_tjfields.field.edit',
	'administrator/components/com_tjfields/assets/js/field.js',
	['core', 'jquery', 'jquery-noconflict'], // Dependencies
	[],
	[]
);

// Inline script using addScriptDeclaration - ensures proper script order
$doc = Factory::getApplication()->getDocument();
$doc->addScriptOptions('com_tjfields.field', [
	'invalidFormErrorMsg' => Text::_('COM_TJFIELDS_INVALID_FORM'),
	'editFormlink' => $link
]);

$doc->addScriptDeclaration('
	(function() {
		var options = Joomla.getOptions("com_tjfields.field") || {};
		var invalidFormErrorMsg = options.invalidFormErrorMsg || "";
		var editFormlink = options.editFormlink || "";
		
		function initFieldTypeHandler() {
			// Check if jQuery and show_option_div function are available
			if (typeof jQuery !== "undefined" && typeof show_option_div !== "undefined") {
				jQuery(document).ready(function($){
					$("#field-form #jform_type").on("change", function(){
						show_option_div(this.value);
					});
				});
			} else {
				// Retry if not loaded yet (max 50 attempts = 5 seconds)
				if (typeof initFieldTypeHandler.attempts === "undefined") {
					initFieldTypeHandler.attempts = 0;
				}
				initFieldTypeHandler.attempts++;
				if (initFieldTypeHandler.attempts < 50) {
					setTimeout(initFieldTypeHandler, 100);
				} else {
					console.error("Failed to load jQuery or show_option_div. jQuery: " + (typeof jQuery !== "undefined" ? "OK" : "MISSING") + ", show_option_div: " + (typeof show_option_div !== "undefined" ? "OK" : "MISSING"));
				}
			}
		}
		
		// Start initialization - wait for scripts to load
		if (document.readyState === "loading") {
			document.addEventListener("DOMContentLoaded", function() {
				setTimeout(initFieldTypeHandler, 200);
			});
		} else {
			setTimeout(initFieldTypeHandler, 200);
		}
	})();
');
?>
<div>
	<form action="<?php echo Route::_('index.php?option=com_tjfields&view=field&layout=edit&id='.(int) $this->item->id).'&client='.$input->get('client','','STRING'); ?>" method="post" enctype="multipart/form-data" name="adminForm" id="field-form" class="form-validate">
		<div class="form-horizontal">
			<?php echo HTMLHelper::_('bootstrap.startTabSet', 'myTab', array('active' => 'general')); ?>
			<?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'general', Text::_('COM_TJFIELDS_TITLE_FIELD', true)); ?>
			<div>&nbsp;</div>
			<div class="row">
				<div class="col-md-6">
					<div class="adminform">
						<legend>
							<?php
								echo Text::_('COM_TJFIELDS_BASIC_FIELDS_VALUES');
							?>
						</legend>
						<div class="control-group">
							<div class="control-label"><?php echo $this->form->getLabel('id'); ?></div>
							<div class="controls"><?php echo $this->form->getInput('id'); ?></div>
						</div>
						<?php echo $this->form->getInput('title');?>
						<div class="control-group">
							<div class="control-label"><?php echo $this->form->getLabel('label'); ?></div>
							<div class="controls"><?php echo $this->form->getInput('label'); ?></div>
							<span class="control-label">&nbsp;</span>
							<span class="controls alert alert-info alert-help-inline alert_no_margin">
							<?php echo Text::_('COM_TJFIELDS_LABEL_LANG_CONSTRAINT_ONE'); ?>
							<span class="alert-text-change">
							<?php echo Text::sprintf('COM_TJFIELDS_LABEL_LANG_CONSTRAINT_TWO', $client); ?>
							</span>
							</span>
						</div>
						<div class="control-group">
							<div class="control-label"><?php echo $this->form->getLabel('name'); ?></div>
							<div class="controls"><?php echo $this->form->getInput('name'); ?></div>
						</div>
						<div class="control-group">
							<div class="control-label"><?php echo $this->form->getLabel('type'); ?></div>
							<?php
								if (!empty($this->item->id))
								{
									?>
									<div class="controls">
										<input type="text" name="jform[type]" id="jform_type" value="<?php echo $this->item->type;?>" class="required" required="required" aria-required="true" aria-invalid="false" readonly="true"/>
									</div>
									<?php
								}
								else
								{
									?>
									<div class="controls"><?php echo $this->form->getInput('type'); ?></div>
									<?php
								}
							?>
						</div>
						<div class="control-group">
							<?php
								foreach ($this->form->getFieldsets('params') as $name => $fieldSet)
								{
									foreach ($this->form->getFieldset($name) as $field)
									{
										echo $field->renderField();
									}
								}
								echo $this->form->getInput('options');
								?>
						</div>
						<?php
						$type = $this->form->getValue('type');

						if ($type == 'radio' || $type == 'single_select' || $type == 'multi_select' || $type == 'tjlist')
						{?>
							<div class="control-label"><?php echo $this->form->getLabel('fieldoption'); ?></div>
							<div class="controls"><?php echo $this->form->getInput('fieldoption'); ?></div><?php
						} ?> 
						
					</div>
					<div class="fileUploadAlert d-none">
						<p class="alert alert-info text-break">
							<?php
								echo Text::_('COM_TJFIELDS_FORM_LBL_FILE_UPLOAD_PATH_NOTICE');
							?>
						</p>
					</div>
					<input type="hidden" name="jform[client]" value="<?php echo $input->get('client','','STRING'); ?>" />
				</div>
				<div class="col-md-5 form-horizontal">
					<div class="adminform form-horizontal">
						<legend>
							<?php
								echo Text::_('COM_TJFIELDS_EXTRA_FIELDS_VALUES');
								?>
						</legend>
						<div class="control-group">
							<div class="control-label"><?php echo $this->form->getLabel('group_id'); ?></div>
							<div class="controls"><?php echo $this->form->getInput('group_id'); ?></div>
						</div>
						<div class="control-group" >
							<div class="control-label"><?php echo $this->form->getLabel('state'); ?></div>
							<div class="controls"><?php echo $this->form->getInput('state'); ?></div>
						</div>
						<div class="control-group">
							<div class="control-label"><?php echo $this->form->getLabel('required'); ?></div>
							<div class="controls"><?php echo $this->form->getInput('required'); ?></div>
						</div>
						<div class="control-group">
							<div class="control-label"><?php echo $this->form->getLabel('readonly'); ?></div>
							<div class="controls"><?php echo $this->form->getInput('readonly'); ?></div>
						</div>
						<div class="control-group">
							<div class="control-label"><?php echo $this->form->getLabel('showonlist'); ?></div>
							<div class="controls"><?php echo $this->form->getInput('showonlist'); ?></div>
						</div>
						<div class="control-group">
							<div class="control-label"><?php echo $this->form->getLabel('created_by'); ?></div>
							<div class="controls"><?php echo $this->form->getInput('created_by'); ?></div>
						</div>
						<div class="control-group">
							<div class="control-label"><?php echo $this->form->getLabel('category') ; ?></div>
							<div class="controls">
								<?php echo $this->form->getInput('category');?>
							</div>
							<span class="control-label">&nbsp;</span>
							<span class="controls alert alert-warning alert-help-inline col-md-9 alert_no_margin">
								<?php echo Text::_('COM_TJFIELDS_CATEGORY_NOTE'); ?>
							</span>
						</div>
						<div class="control-group">
							<div class="control-label"><?php echo $this->form->getLabel('filterable'); ?></div>
							<div class="controls">
								<?php echo $this->form->getInput('filterable'); ?>
							</div>
							<span class="control-label">&nbsp;</span>
							<span class="controls alert alert-info alert-help-inline col-md-9 alert_no_margin">
							<?php echo Text::_('COM_TJFIELDS_FILTERABLE_NOTE'); ?>
							</span>
						</div>
						<div class="control-group">
							<div class="control-label"><?php echo $this->form->getLabel('description'); ?></div>
							<div class="controls"><?php echo $this->form->getInput('description'); ?></div>
						</div>
						<div class="control-group">
							<div class="control-label"><?php echo $this->form->getLabel('js_function'); ?></div>
							<div class="controls">
								<?php echo $this->form->getInput('js_function'); ?>
							</div>
						</div>
						<div class="control-group">
							<div class="control-label"><?php echo $this->form->getLabel('validation_class'); ?></div>
							<div class="controls">
								<?php echo $this->form->getInput('validation_class'); ?>
							</div>
							<span class="control-label"></span>
							<span class="controls alert alert-info alert-help-inline col-md-9 alert_no_margin">
							<?php echo Text::_('COM_TJFIELDS_VALIDATION_CLASS_NOTE'); ?>
							</span>
						</div>
					</div>
				</div>
				<!--</div>-->
			</div>
			<?php echo HTMLHelper::_('bootstrap.endTab'); ?>
			<?php if (Factory::getUser()->authorise('core.admin','com_tjfields')) : ?>
				<?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'permissions', Text::_('JGLOBAL_ACTION_PERMISSIONS_LABEL', true)); ?>
				<?php echo $this->form->getInput('rules'); ?>
				<?php echo HTMLHelper::_('bootstrap.endTab'); ?>
			<?php endif; ?>
		<?php echo HTMLHelper::_('bootstrap.endTabSet'); ?>
			<input type="hidden" name="client_type" value="<?php echo $clientType;?>" />
			<input type="hidden" name="task" value="" />
			<?php echo HTMLHelper::_('form.token'); ?>
		</div>
		<!--row fuild ends-->
</div>
<!--techjoomla ends-->
</form>
</div>
