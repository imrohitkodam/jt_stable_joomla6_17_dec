<?php
/**
 * @version     1.0.0
 * @package     com_hierarchy
 * @copyright   Copyright (C) 2015. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Parth Lawate <contact@techjoomla.com> - http://techjoomla.com
 */

// no direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

HTMLHelper::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jticketing/helpers/html');
HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.multiselect');
//~ // Joomla 6: formbehavior.chosen removed - using native select
HTMLHelper::_('bootstrap.renderModal', 'a.modal');
HTMLHelper::_('behavior.keepalive');

$user	= Factory::getUser();
$userId	= $user->get('id');
Factory::getDocument()->addScriptDeclaration("
	jQuery(document).ready(function () {
		jQuery('#export-submit').on('click', function () {
			document.getElementById('task').value = 'catimpexp.csvexport';
			document.adminForm.submit();
			document.getElementById('task').value = '';
		});
	});");

//Joomla Component Creator code to allow adding non select list filters
if (!empty($this->extra_sidebar)) {
    $this->sidebar .= $this->extra_sidebar;
}
?>
<div class="container">
	<form action="<?php echo Uri::base(); ?>index.php?option=com_jticketing&view=catimpexp" id="adminForm" class="form-inline"  name="adminForm" method="post">
	<?php if(!empty($this->sidebar)): ?>
		<div id="j-sidebar-container" class="span2">
			<?php echo $this->sidebar; ?>
		</div>
		<div id="j-main-container" class="span10">
	<?php else : ?>
		<div id="j-main-container">
	<?php endif;?>

			<!--div id="filter-bar" class="btn-toolbar">
				<div class="btn-group pull-left">
					<button type="button" class="btn btn-success" id="export-submit"><i class="icon-download icon-white"></i> <?php echo Text::_('COM_JTICKETING_CSV_IMPORT_EXPORT_HELP_TEXT'); ?></button>
				</div>
			</div-->
			<div class="clearfix">&nbsp;</div>
			<div class="bs-callout bs-callout-info" id="callout-xref-input-group">
			<p><?php echo Text::_('COM_JTICKETING_CSV_IMPORT_EXPORT_HELP_TEXT'); ?></p>
			<a href="<?php echo Uri::base().'index.php?option=com_categories&view=categories&extension=com_jticketing'; ?>"><?php echo Text::_('COM_JTICKETING_CATEGORY_LINK'); ?></a>
			</div>
			<div class="clearfix">&nbsp;</div>
			<input type="hidden" id="task" name="task" value="" />
			<?php echo HTMLHelper::_('form.token'); ?>
		</div>
	</form>
</div>
<?php
$link = '<a href="' . Uri::root() . 'media/com_jticketing/samplecsv/categoryImport.csv' . '">' . Text::_("COM_JTICKETING_CSV_SAMPLE") . '</a>';
$body = '
<div class="container">
	<div id="import_category" class="col-sm-12">
		<div class="clearfix">&nbsp;</div>
		<form action="' . Uri::base() . 'index.php?option=com_jticketing&task=catimpexp.csvImport&tmpl=component&format=html" id="uploadForm" class="form-inline center"  name="uploadForm" method="post" enctype="multipart/form-data">
			<table>
				<tr>
					<div id="uploadform_cat">
						<fieldset id="upload-noflash">
							<label for="upload-file" class="control-label">' . Text::_('COMJTICKETING_UPLOADE_FILE') . '</label>
							<input type="file" id="upload-file" name="csvfile" id="csvfile" required="true" />
							<button class="btn btn-primary" id="upload-submit">
								<i class="icon-upload icon-white"></i>
								' . Text::_('COMJTICKETING_EVENT_IMPORT_CSV') . '
							</button>
							<hr class="hr hr-condensed">
							<div class="alert alert-warning" role="alert"><i class="icon-info"></i>
								' . Text::sprintf('COM_JTICKETING_CSVHELP_CATEGORY', $link) . '
							</div>
						</fieldset>
					</div>
				</tr>
			</table>
		</form>
	</div>
</div>';

echo HTMLHelper::_(
	'bootstrap.renderModal', 'import_categorywrap',
	array(
		'title' => Text::_('COMJTICKETING_EVENT_IMPORT_CSV'),
		'height' => '600px', 'width' => '600px',
		'bodyHeight' => '70', 'modalWidth'=> '80',
		'closeButton' => true, 'backdrop' => 'static',
		'keyboard' => false,
	), $body
);
