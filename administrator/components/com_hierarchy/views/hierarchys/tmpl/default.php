<?php
/**
 * @version    SVN: <svn_id>
 * @package    Com_Hierarchy
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2017 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

HTMLHelper::addIncludePath(JPATH_COMPONENT.'/helpers/html');
HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.multiselect');
HTMLHelper::_('behavior.keepalive');

if (JVERSION < '4.0.0')
{
	HTMLHelper::_('behavior.modal', 'a.modal');
}
else
{
	HTMLHelper::_('bootstrap.renderModal', 'a.modal');
}

// Import CSS
HTMLHelper::_('script', 'jui/fielduser.min.js', array('version' => 'auto', 'relative' => true));

$user      = Factory::getUser();
$userId    = $user->get('id');
$listOrder = $this->state->get('list.ordering');
$listDirn  = $this->state->get('list.direction');
$canOrder  = $user->authorise('core.edit.state', 'com_hierarchy');
$saveOrder = $listOrder == 'a.ordering';

if ($saveOrder)
{
	$saveOrderingUrl = 'index.php?option=com_hierarchy&task=hierarchys.saveOrderAjax&tmpl=component';
	HTMLHelper::_('sortablelist.sortable', 'hierarchyList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}

$sortFields = $this->getSortFields();

// Call helper function
HierarchyHelper::getLanguageConstant();
?>
<form action="<?php echo Route::_('index.php?option=com_hierarchy&view=hierarchys'); ?>" method="post" name="adminForm" id="adminForm">
<?php
	if(!empty($this->sidebar)):
		?>
		<div id="j-sidebar-container" class="span2">
			<?php echo $this->sidebar; ?>
		</div>
		<div id="j-main-container" class="span10">
		<?php
	else :
		?>
		<div id="j-main-container">
		<?php
	endif;

	// Search tools bar
	echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this));
	?>
	<div class="clearfix"> </div>
	<?php
		if (empty($this->items)) :
			?>
			<div class="alert alert-no-items">
				<?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
			</div>
			<?php
		else :
		?>
		<table class="table table-striped" id="hierarchyList">
			<thead>
				<tr>
					<th width="1%">
						<input type="checkbox" name="checkall-toggle" value="" title="<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
					</th>
					<th class='left'>
						<?php echo HTMLHelper::_('grid.sort',  'COM_HIERARCHY_HIERARCHYS_USER_NAME', 'a.name', $listDirn, $listOrder); ?>
					</th>
					<th class='left'>
						<?php echo Text::_('COM_HIERARCHY_CONTEXT'); ?>
					</th>
					<th class='left'>
						<?php echo Text::_('COM_HIERARCHY_CONTEXT_ID'); ?>
					</th>
					<th class='left'>
						<?php echo Text::_('COM_HIERARCHY_HIERARCHYS_REPORT_TO'); ?>
					</th>
					<th class='right'>
						<?php echo HTMLHelper::_('grid.sort',  'COM_HIERARCHY_HIERARCHYS_USER_ID', 'a.id', $listDirn, $listOrder); ?>
					</th>
				</tr>
			</thead>
			<tfoot>
			<?php
				if (isset($this->items[0]))
				{
					$colspan = count(get_object_vars($this->items[0]));
				}
				else
				{
					$colspan = 10;
				}
				?>
				<tr>
					<td colspan="<?php echo $colspan ?>"><?php echo $this->pagination->getListFooter(); ?></td>
				</tr>
			</tfoot>
			<tbody>
			<?php

				foreach ($this->items as $i => $item) :
					$ordering = ($listOrder == 'a.+ordering');
					$userName = $item->ReportsToUserNameStr;

					?>
					<tr class="row<?php echo $i % 2; ?> reports_to">
						<td class='center'><?php echo HTMLHelper::_('grid.id', $i, $item->subuserId); ?></td>
						<td><?php  echo $item->name; ?></td>
						<td>
						<?php
							echo $item->context = !empty($item->context) ? $item->context : '-';
							?>
						</td>
						<td>
						<?php
							echo $item->context_id = !empty($item->context_id) ? $item->context_id : '-';
							?>
						</td>
						<td>
							<?php
								$clientUrl = '';

								// Client and client_id is passed to the form URL
								if ($this->client && $this->clientId)
								{
									$clientUrl = '&client=' . $this->client . '&client_id=' . $this->clientId;
								}

								if ($this->canEdit) :
									$url = Route::_('index.php?option=com_hierarchy&view=hierarchy&layout=edit&id='.(int) $item->id . '&user_id=' .(int) $item->subuserId . $clientUrl);
									$text = Text::_('COM_HIERARCHY_SET_MANAGER');
								else :
									$url = Route::_('index.php?option=com_hierarchy&view=hierarchy&layout=edit&user_id=' . (int) $item->subuserId . $clientUrl);
									$text = Text::_('COM_HIERARCHY_SET_MANAGER');
								endif;
							?>
							<a href="<?php echo $url;?>" class="btn button btn-success modal" rel="{handler: 'iframe', size: {x: 800, y: 500}}">
								<span class="icon icon-users"></span>
								<?php echo $text;?>
							</a>
							<?php
								if ($this->canEdit)
								{
									?>
									<span id="popover_<?php echo $i; ?>" data-toggle="popover" data-trigger="hover" data-placement="right"  data-content="<?php echo $userName; ?>"><?php echo $userName = strlen($userName) > 20 ? substr($userName, 0, 20) . "..." : $userName; ?></span>
									<?php
								}
								?>
						</td>
						<td class='right'><?php echo $item->subuserId; ?></td>
					</tr>
					<?php
				endforeach;
				?>
			</tbody>
		</table>
		<?php
		endif;
		?>
		<input type="hidden" id="task" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
		<?php echo HTMLHelper::_('form.token'); ?>
	</div>
</form>
<div style="display:none">
	<div id="import_append">
		<form action="<?php echo Uri::base(); ?>index.php?option=com_hierarchy&task=hierarchys.csvImport&tmpl=component&format=html" id="uploadForm" class="form-inline center"  name="uploadForm" method="post" enctype="multipart/form-data">
			<table>
				</br>
				<tr>
					<div id="uploadform">
						<fieldset id="upload-noflash" class="actions">
							<label for="upload-file" class="control-label">
								<?php echo Text::_('COM_HIERARCHY_UPLOADE_FILE'); ?>
							</label>
							<input type="file" id="upload-file" name="csvfile" id="csvfile" />
							<button class="btn btn-primary" id="upload-submit">
								<i class="icon-upload icon-white"></i>
								<?php echo Text::_('COM_HIERARCHY_IMPORT_CSV'); ?>
							</button>
							<hr class="hr hr-condensed">
							<div class="alert alert-warning" role="alert">
								<i class="icon-info"></i>
								<?php
									$link = '<a href="' . Uri::root() . 'media/com_hierarchy/samplecsv/userImport.csv' . '" download>' . Text::_("COM_HIERARCHY_CSV_SAMPLE") . '</a>';
									echo Text::sprintf('COM_HIERARCHY_CSVHELP', $link);
								?>
							</div>
						</fieldset>
					</div>
				</tr>
			</table>
		</form>
	</div>
</div>
<script type="text/javascript">
	hierarchyAdmin.hierarchys.initHierarchysJs();
	hierarchyAdmin.hierarchys.showUserNames();
</script>
