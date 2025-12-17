<?php
/**
 * @package     JTicketing
 * @subpackage  com_jticketing
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license     GNU GPLv2 <http://www.gnu.org/licenses/old-licenses/gpl-2.0.html>
 */

// No direct access
defined('_JEXEC') or die;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Layout\LayoutHelper;

HTMLHelper::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jticketing'. '/helpers/html');
HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');

$path = JPATH_SITE . '/components/com_jticketing/helpers/main.php';

if (!class_exists('Jticketingmainhelper'))
{
	JLoader::register('Jticketingmainhelper', $path);
	JLoader::load('Jticketingmainhelper');
}

// Call helper function
JticketingHelper::getLanguageConstant();

$editId         = $this->item->id;
$existingScoUrl = '';
?>

<form action="<?php echo Route::_('index.php?option=com_jticketing&view=pdftemplate&layout=edit&id=' . (int) $this->item->id); ?>" method="post" enctype="multipart/form-data" name="adminForm" id="adminForm" class="form-validate">

	<div class="form-horizontal" id="jtwrap">
		<div class="row form-horizontal-desktop">
			<div class="col-md-8">
				<div class="control-group" style="display:none">
					<div class="control-label"><?php echo $this->form->getLabel('id'); ?></div>
					<div class="col-md-9"><?php echo $this->form->getInput('id'); ?></div>
				</div>
				<div class="control-group mt-2">
					<div class="control-label"><?php echo $this->form->getLabel('event_id'); ?></div>
					<div class="col-md-9"><?php echo $this->form->getInput('event_id'); ?></div>
				</div>
				<div class="control-group mt-2">
					<div class="control-label"><?php echo $this->form->getLabel('body'); ?></div>
					<div class="col-md-9"><?php echo $this->form->getInput('body'); ?></div>
				</div>
				<div class="control-group mt-2">
					<div class="col-md-12 mb-2">
						<div class="alert alert-info"><?php echo Text::_('EB_CSS_EDITOR_MSG') ?></div>
					</div>
					<div class="control-label"><?php echo $this->form->getLabel('css'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('css'); ?></div>
				</div>
			</div>

			<div class="col-sm-4">
				<table width="100%">
					<tr>
						<td colspan="2"><div class="alert alert-info"><?php echo Text::_('COM_JTICKETING_EB_TAGS_DESC') ?> <br/></div>
										</tr>

					<tr>
						<td width="30%"><b>&nbsp;&nbsp;[NAME] </b> </td>
						<td><?php echo Text::_('TAGS_NAME'); ?></td>

					</tr>
					<tr>
						<td><b>&nbsp;&nbsp;[BOOKING_DATE]</b></td>
						<td><?php echo Text::_('TAGS_BOOKING_DATE'); ?></td>

					</tr>
					<tr>
						<td><b>&nbsp;&nbsp;[BUYER_NAME]</b></td>
						<td><?php echo Text::_('TAGS_BUYER_NAME'); ?></td>
					</tr>
					<tr>
						<td><b>&nbsp;&nbsp;[EVENT_IMAGE]</b></td>
						<td><?php echo Text::_('TAGS_EVENT_IMAGE'); ?></td>

					</tr>
					<tr>
						<td><b>&nbsp;&nbsp;[EVENT_NAME]</b></td>
						<td><?php echo Text::_('TAGS_EVENT_NAME'); ?></td>

					</tr>
					<tr>
						<td><b>&nbsp;&nbsp;[EVENT_URL]</b></td>
						<td><?php echo Text::_('TAGS_EVENT_LINK'); ?></td>

					</tr>
					<tr>
						<td><b>&nbsp;&nbsp;[ST_DATE]</b> </td>
						<td><?php echo Text::_('TAGS_EVENT_START_DATE'); ?></td>

					</tr>
					<tr>
						<td><b>&nbsp;&nbsp;[EN_DATE]</b></td>
						<td><?php echo Text::_('TAGS_EVENT_END_DATE'); ?></td>

					</tr>

					<tr>
						<td><b>&nbsp;&nbsp;[EVENT_LOCATION]</b> </td>
						<td><?php echo Text::_('TAGS_EVENT_LOCATION'); ?></td>
					</tr>
					<tr>
						<td><b>&nbsp;&nbsp;[TICKET_ID]</b></td>
						<td><?php echo Text::_('TAGS_TICKET_ID'); ?></td>

					</tr>
					<tr>
						<td><b>&nbsp;&nbsp;[TICKET_TYPE]</b></td>
						<td><?php echo Text::_('TAGS_TICKET_TYPE'); ?></td>

					</tr>
					<tr>
						<td><b>&nbsp;&nbsp;[TICKET_PRICE]</b> </td>
						<td><?php echo Text::_('TAGS_TICKET_PRICE'); ?></td>
					</tr>
					<tr>
						<td><b>&nbsp;&nbsp;[TOTAL_PRICE]</b></b></td>
						<td><?php echo Text::_('TAGS_TOTAL_PRICE'); ?></td>

					</tr>
					<tr>
						<td><b>&nbsp;&nbsp;[EVENT_DESCRIPTION] </b></td>
						<td><?php echo Text::_('TAGS_EVENT_DESCRIPTION'); ?></td>

					</tr>
					<tr>
						<td><b>&nbsp;&nbsp;[QR_CODE]</b></td>
						<td><?php echo Text::_('TAGS_QC_CODE'); ?></td>
					</tr>
					<tr>
						<td><b>&nbsp;&nbsp;[BAR_CODE]</b></td>
						<td><?php echo Text::_('TAGS_BAR_CODE'); ?></td>
					</tr>
				</table>
			</div>
		</div>
	</div>
	<input type="hidden" name="task" value="" />
	<?php echo HTMLHelper::_('form.token'); ?>
</form>