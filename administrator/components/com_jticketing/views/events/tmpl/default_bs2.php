<?php
/**
 * @package     JTicketing
 * @subpackage  com_jticketing
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\String\StringHelper;
use Joomla\CMS\Layout\LayoutHelper;

HTMLHelper::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jticketing/helpers/html');

if(JVERSION >= '3.0')
{
	HTMLHelper::_('bootstrap.tooltip');
	// Joomla 6: formbehavior.chosen removed - using native select
	HTMLHelper::_('behavior.multiselect');
	HTMLHelper::_('bootstrap.renderModal', 'a.modal');
}
// Import CSS.
$document = Factory::getDocument();
HTMLHelper::_('stylesheet', 'components/com_jticketing/assets/css/jticketing.css');
$user       = Factory::getUser();
$listOrder  = $this->state->get('list.ordering');
$listDirn   = $this->state->get('list.direction');
$saveOrder  = $listOrder == 'a.ordering';

if ($saveOrder)
{
	$saveOrderingUrl = 'index.php?option=com_jticketing&task=events.saveOrderAjax&tmpl=component';
	HTMLHelper::_('sortablelist.sortable', 'eventList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}
Factory::getDocument()->addScriptDeclaration('
	Joomla.orderTable = function()
	{
		table = document.getElementById("sortTable");
		direction = document.getElementById("directionTable");
		order = table.options[table.selectedIndex].value;

		if (order != "'. $listOrder.  '")
		{
			dirn = "asc";
		}
		else
		{
			dirn = direction.options[direction.selectedIndex].value;
		}

		Joomla.tableOrdering(order, dirn, "");
	}
	techjoomla.jQuery(document).ready(function () {
		techjoomla.jQuery("#export-submit").on("click", function () {
			document.getElementById("task").value = "events.csvexport";
			document.adminForm.submit();
			document.getElementById("task").value = "";
		});
	});');

if (!empty($this->extra_sidebar))
{
    $this->sidebar .= $this->extra_sidebar;
}
?>

	<form action="<?php echo Route::_('index.php?option=com_jticketing&view=events'); ?>" method="post" name="adminForm" id="adminForm">
		<?php if(!empty($this->sidebar)): ?>
		<div id="j-sidebar-container" class="span2">
			<?php echo $this->sidebar; ?>
		</div>
		<div id="j-main-container" class="span10">
		<?php else : ?>
		<div id="j-main-container">
		<?php endif;?>

			<?php
			// Search tools bar
			echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this));

			// Native Event Manager.
			if($this->integration != 2)
			{
			?>
			<div class="alert alert-info alert-help-inline">
				<?php echo Text::_('COMJTICKETING_INTEGRATION_NATIVE_NOTICE');?>
			</div>
			<?php
			return false;
			}
			?>

			<div class="clearfix"> </div>
			<div class="table-responsive">
				<?php if (empty($this->items)) : ?>
				<div class="clearfix">&nbsp;</div>
				<div class="alert alert-info">
					<?php echo Text::_('NODATA'); ?>
				</div>
			<?php
			else : ?>
				<table class="table table-striped" id="eventList">
					<thead>
						<tr>

						<?php if (isset($this->items[0]->ordering)): ?>
							<th width="1%" class="nowrap center hidden-phone">
								<?php echo HTMLHelper::_('grid.sort', '<i class="icon-menu-2"></i>', 'a.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING'); ?>
							</th>
						<?php endif; ?>
							<th width="1%">
								<input type="checkbox" name="checkall-toggle" value="" title="<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
							</th>

						<?php if (isset($this->items[0]->state)): ?>
							<th width="1%" class="nowrap center">
								<?php echo HTMLHelper::_('grid.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
							</th>
						<?php endif; ?>

						<th class=''>
							<?php echo HTMLHelper::_('grid.sort',  'COM_JTICKETING_EVENTS_TITLE', 'a.title', $listDirn, $listOrder); ?>
						</th>
<!--
						<th class='hidden-phone'>
							<?php // echo HTMLHelper::_('grid.sort',  'COM_JTICKETING_EVENTS_DESC', 'a.short_description', $listDirn, $listOrder); ?>
						</th>
-->
						<th class='hidden-phone'>
							<?php echo Text::_('COM_JTICKETING_EVENTS_TICKET_TYPES'); ?>
						</th>
						<th class='hidden-phone'>
							<?php echo HTMLHelper::_('grid.sort',  'COM_JTICKETING_EVENTS_CATEGORY', 'a.catid', $listDirn, $listOrder); ?>
						</th>

						<th class='hidden-phone hidden-tablet'>
							<?php echo HTMLHelper::_('grid.sort',  'COM_JTICKETING_EVENTS_CREATOR', 'a.created_by', $listDirn, $listOrder); ?>
						</th>

						<th class='hidden-phone'>
							<?php echo HTMLHelper::_('grid.sort',  'COM_JTICKETING_EVENTS_STARTDATE', 'a.startdate', $listDirn, $listOrder); ?>
						</th>

						<th class='hidden-phone'>
							<?php echo HTMLHelper::_('grid.sort',  'COM_JTICKETING_EVENTS_ENDDATE', 'a.enddate', $listDirn, $listOrder); ?>
						</th>

						<th class='hidden-phone'>
							<?php echo Text::_('COM_JTICKETING_EVENTS_LOCATION'); ?>
						</th>

						<th class='center'>
							<?php echo Text::_('COM_JTICKETING_EVENTS_ATTENDEE_DETAILS'); ?>
						</th>

						<th class='center'>
							<?php echo HTMLHelper::_('grid.sort',  'COM_JTICKETING_EVENTS_FEATURED', 'a.featured', $listDirn, $listOrder); ?>
						</th>

						<th class='center'>
							<?php echo Text::_( 'COM_JTICKETING_EVENTS_ATTENDED_USERS' );?>
						</th>

						<?php if (isset($this->items[0]->id)): ?>
							<th width="1%" class="nowrap center hidden-phone hidden-tablet">
								<?php echo HTMLHelper::_('grid.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
							</th>
						<?php endif; ?>
						</tr>
					</thead>

					<tfoot>
						<?php
						if(isset($this->items[0]))
						{
							$colspan = count(get_object_vars($this->items[0]));
						}
						else
						{
							$colspan = 10;
						}
						?>
						<tr>
							<td colspan="<?php echo $colspan ?>">
								<?php echo $this->pagination->getListFooter(); ?>
							</td>
						</tr>
					</tfoot>

					<tbody>
					<?php
					foreach ($this->items as $i => $item) :
						$ordering   = ($listOrder == 'a.ordering');
						$canCreate	= $user->authorise('core.create',		'com_jticketing');
						$canEdit	= $user->authorise('core.edit',			'com_jticketing');
						$canCheckin	= $user->authorise('core.manage',		'com_jticketing');
						$canChange	= $user->authorise('core.edit.state',	'com_jticketing');

						$ticketTypesLink = Route::_("index.php?option=com_jticketing&view=events&layout=tickettypes&tmpl=component&id=".$item->id);
						$eventAttendeedLink =Route::_("index.php?option=com_jticketing&view=attendees&filter[events]=".$item->integrationId ."&filter[attended_status]=1");
						$eventNotAttendeedLink =Route::_("index.php?option=com_jticketing&view=attendees&filter[events]=".$item->integrationId ."&filter[status]=A");

						$integration = JT::getIntegration();
						$event       = JT::event($item->id, $integration);
						$xrefId      = $event->integrationId;
						$buyersCount = $event->soldTicketCount();
						?>

						<tr class="row<?php echo $i % 2; ?>">

						<?php if (isset($this->items[0]->ordering)): ?>
							<td class="order nowrap center hidden-phone">
							<?php if ($canChange) :
								$disableClassName = '';
								$disabledLabel	  = '';
								if (!$saveOrder) :
									$disabledLabel    = Text::_('JORDERINGDISABLED');
									$disableClassName = 'inactive tip-top';
								endif; ?>
								<span class="sortable-handler hasTooltip <?php echo $disableClassName?>" title="<?php echo $disabledLabel?>">
									<i class="icon-menu"></i>
								</span>
								<input type="text" style="display:none" name="order[]" size="5" value="<?php echo $item->ordering;?>" class="width-20 text-area-order " />
							<?php else : ?>
								<span class="sortable-handler inactive" >
									<i class="icon-menu"></i>
								</span>
							<?php endif; ?>
							</td>
						<?php endif; ?>
							<td class="center">
								<?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
							</td>

						<?php if (isset($this->items[0]->state)): ?>
							<td class="center">
								<?php echo HTMLHelper::_('jgrid.published', $item->state, $i, 'events.', $canChange, 'cb'); ?>
							</td>
						<?php endif; ?>

						<td class="">
							<?php if (isset($item->checked_out) && $item->checked_out) : ?>
								<?php echo HTMLHelper::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'events.', $canCheckin); ?>
							<?php endif; ?>
							<?php if ($canEdit) : ?>
								<a href="<?php echo Route::_('index.php?option=com_jticketing&task=event.edit&id='.(int) $item->id); ?>">
								<?php echo $this->escape($item->title); ?></a>
							<?php else : ?>
								<?php echo $this->escape($item->title); ?>
							<?php endif; ?>
						</td>
<!--
						<td class="hidden-phone">
							<?php
							//if (strlen($item->short_description)>50)
							//echo substr($item->short_description, 0, 50) . " ...";
							//else
							//echo $item->short_description;
							?>
						</td>
-->
						<td class="hidden-phone">

						<?php
						$link = Uri::root();
						echo HTMLHelper::_(
						  'bootstrap.renderModal', 'previewModal' . $item->id,
						  array(
							'url' => $ticketTypesLink, 'title' => Text::_('Preview'),
							'height' => '600px', 'width' => '600px',
							'bodyHeight' => '70', 'modalWidth'=> '80',
							'closeButton' => true, 'backdrop' => 'static',
							'keyboard' => false
						  )
						);
						?>

						<a data-target="#previewModal<?php echo $item->id;?>" data-toggle="modal">
							<?php echo Text::_('COM_JTICKETING_VIEW_TICKET_TYPES'); ?>
						</a>
						</td>
						<td class="hidden-phone">
							<?php echo $item->catid; ?>
						</td>

						<td class="hidden-phone hidden-tablet">
							<?php echo $item->creator; ?>
						</td>

						<td class="hidden-phone">
							<?php
								echo $this->utilities->getFormatedDate($item->startdate);
							?>
						</td>

						<td class="hidden-phone">
							<?php
								echo $this->utilities->getFormatedDate($item->enddate);
							?>
						</td>

						<td class="hidden-phone">
							<?php
							if ($item->online_events == "1")
							{
								if ($item->online_provider == "plug_tjevents_adobeconnect")
								{
									echo "Adobe - " . $item->name;
								}
								else
								{
									echo StringHelper::ucfirst($item->online_provider) . " - " . $item->name;
								}
							}
							elseif($item->online_events == "0")
							{
								if ($item->venue != "0")
								{
									echo $item->name . "- " . $item->address;
								}
								else
								{
									echo $item->location;
								}
							}
							else
							{
								echo "-";
							} ?>
						</td>
						<td>
							<div class="btn-group">
								<?php
								$url = "index.php?option=com_jticketing&view=attendees&filter[events]=" . $xrefId . "&tmpl=component";
								echo HTMLHelper::_(
								'bootstrap.renderModal', 'attendeeexport' . $xrefId,
								array(
									'url' => $url, 'title' => Text::_('COM_JTICKETING_EVENTS_ATTENDEE_EXPORT'),
									'height' => '700px', 'width' => '600px',
									'bodyHeight' => '70', 'modalWidth'=> '80',
									'closeButton' => true, 'backdrop' => 'static',
									'keyboard' => false,
									)
								);
								?>
								<a class="btn"
								data-target="#attendeeexport<?php echo $xrefId;?>" data-toggle="modal"
								title="<?php echo Text::_('COM_JTICKETING_EVENTS_ATTENDEE_EXPORT'); ?>">
								<i class="fa fa-download" ></i>
								</a>
								<?php
								if($this->params->get('signin_export'))
								{
									$signinUrl = "index.php?option=com_jticketing&view=attendees&layout=signin&filter[events]=" . $xrefId . "&tmpl=component";
									echo HTMLHelper::_(
									'bootstrap.renderModal', 'signinsheet' . $xrefId,
									array(
									'url' => $signinUrl, 'title' => Text::_('COM_JTICKETING_EVENTS_ATTENDEE_SIGN_IN_SHEET'),
									'height' => '700px', 'width' => '600px',
									'bodyHeight' => '70', 'modalWidth'=> '80',
									'closeButton' => true, 'backdrop' => 'static',
									'keyboard' => false,
									)
								);
								?>
								<a class="btn"
								data-target="#signinsheet<?php echo $xrefId;?>" data-toggle="modal"
								title="<?php echo Text::_('COM_JTICKETING_EVENTS_ATTENDEE_SIGN_IN_SHEET'); ?>">
								<i class="fa fa-file-text"></i>
								</a>
								<?php
								}
								if($this->params->get('namecard_export'))
								{
									$namecard = "index.php?option=com_jticketing&view=attendees&layout=namecard&filter[events]=" . $xrefId . "&tmpl=component";
									echo HTMLHelper::_(
								  	'bootstrap.renderModal', 'namecard' . $xrefId,
								  	array(
									'url' => $namecard, 'title' => Text::_('COM_JTICKETING_EVENTS_ATTENDEE_NAME_CARD_SHEET'),
									'height' => '700px', 'width' => '600px',
									'bodyHeight' => '70', 'modalWidth'=> '80',
									'closeButton' => true, 'backdrop' => 'static',
									'keyboard' => false,
									)
								);
								?>
								<a class="btn"
								data-target="#namecard<?php echo $xrefId;?>" data-toggle="modal"
								title="<?php echo Text::_('COM_JTICKETING_EVENTS_ATTENDEE_NAME_CARD_SHEET'); ?>">
								<i class="fa fa-bar-chart"></i>
								</a>
								<?php
								}
								?>
							</div>
						</td>
						<td class="center">
							<a href="javascript:void(0);"
							class="btn btn-micro active hasTooltip"
							onclick="Joomla.listItemTask('cb<?php echo $i;?>','<?php echo ($item->featured) ? 'events.unfeature' : 'events.feature';?>')"
							title="<?php echo ( $item->featured ) ? Text::_('COM_JTICKETING_UNFEATURE_ITEM') : Text::_('COM_JTICKETING_FEATURE_ITEM'); ;?>">
								<?php
								if(JVERSION > '3.0')
								{
									$featuredClass = ($item->featured) ? 'featured' : 'unfeatured';
								}
								else
								{
									$featuredClass = ($item->featured) ? 'star' : 'star-empty';
								}
								?>
								<i class="icon-<?php echo $featuredClass;?>"></i>
							</a>
						</td>

						<td class="hidden-phone">
							<a href="<?php echo $eventAttendeedLink; ?>">
								<span class="editlinktip hasTip" title="<?php echo Text::_('COM_JTICKETING_EVENT_ATTENDEED_LIST');?>" > <?php echo $item->attended_count;?> </span>
							</a>
							<?php echo " / ";?>
							<a href="<?php echo $eventNotAttendeedLink; ?>">
								<span class="editlinktip hasTip" title="<?php echo Text::_('COM_JTICKETING_EVENT_ENROLLED_LIST');?>" > <?php echo $buyersCount; ?> </span>
							</a>
						</td>

						<?php if (isset($this->items[0]->id)): ?>
							<td class="center hidden-phone hidden-tablet">
								<?php echo (int) $item->id; ?>
							</td>
						<?php endif; ?>
					</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
				<?php endif; ?>
			</div>
			<div class="bs-callout bs-callout-info" id="callout-xref-input-group">
				<p><?php echo Text::_('COMJTICKETING_EVENT_CSV_HELP_TEXT'); ?></p>
				<p><?php echo Text::_('COMJTICKETING_EVENT_CSV_EXPORT_HELP_TEXT'); ?></p>
				<p><?php echo Text::_('COMJTICKETING_EVENT_CSV_IMPORT_HELP_TEXT'); ?></p>
			</div>
			<input type="hidden" id="task" name="task" value="" />
			<input type="hidden" name="boxchecked" value="0" />
			<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
			<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
			<?php echo HTMLHelper::_('form.token'); ?>
		</div>
	</form>
</div>

<?php
$link = '<a href="' . Uri::root() . 'media/com_jticketing/samplecsv/EventImport.csv' . '">' . Text::_("COM_JTICKETING_CSV_SAMPLE") . '</a>';
$body = '<div id="import_events">
	<form action="' . Uri::base() . 'index.php?option=com_jticketing&task=events.csvImport&tmpl=component&format=html" id="uploadForm" class="form-inline center"  name="uploadForm" method="post" enctype="multipart/form-data">
		<table>
			<tr>&nbsp;</tr>
			<tr>
				<div id="uploadform">
					<fieldset id="upload-noflash" class="actions">
						<label for="upload-file" class="control-label">' . Text::_('COMJTICKETING_UPLOADE_FILE') . '</label>
						<input type="file" id="upload-file" name="csvfile" id="csvfile" required accept=".csv"/>
						<button class="btn btn-primary" id="upload-submit">
							<i class="icon-upload icon-white"></i>
							' . Text::_('COMJTICKETING_EVENT_IMPORT_CSV') . '
						</button>
						<hr class="hr hr-condensed">
						<div class="alert alert-warning" role="alert"><i class="icon-info"></i>
							' . Text::sprintf('COM_JTICKETING_CSVHELP', $link) . '
						</div>
					</fieldset>
				</div>
			</tr>
		</table>
	</form>
</div>';

echo HTMLHelper::_(
	'bootstrap.renderModal', 'import_eventswrap',
	array(
		'title' => Text::_('COMJTICKETING_EVENT_IMPORT_CSV'),
		'height' => '600px', 'width' => '600px',
		'bodyHeight' => '70', 'modalWidth'=> '80',
		'closeButton' => true, 'backdrop' => 'static',
		'keyboard' => false,
	), $body
);
