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
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;
$user = Factory::getUser();
$UriRoot = Uri::root();
?>
<div class="alert alert-info" role="alert">
	<?php echo Text::_('COM_HIERARCHY_SHOW_LIST');?><b><?php echo $user->name . '.'; ?></b>
</div>
<form action="<?php echo Route::_('index.php?option=com_hierarchy&view=hierarchys'); ?>" method="post" name="adminForm" id="adminForm">
	<div class=" col-lg-3 col-md-6 col-sm-6 col-xs-12">
		<div class="input-group">
			<input type="text" placeholder="<?php echo Text::_('COM_HIERARCHY_ENTER_USER_NAME'); ?>" name="filter_search" id="filter_search" value="<?php echo $srch = ($this->lists['search'])?$this->lists['search']:''; ?>" class="form-control" onchange="document.adminForm.submit();" />
			<span class="input-group-btn">
				<button type="button" onclick="this.form.submit();" class="btn btn-success tip hasTooltip" data-original-title="Search"><i class="fa fa-search"></i></button>
				<button type="button" onclick="document.getElementById('filter_search').value='';this.form.submit();" class="btn btn-primary tip hasTooltip" data-original-title="Clear"><i class="fa fa-remove"></i></button>
			</span>
		</div>
	</div>
	<div class=" col-lg-8 col-md-6 col-sm-6 col-xs-12">
		<div class="input-group pull-right">
			<?php //echo HTMLHelper::_('select.genericlist', $this->contextList, "filter_context", 'style="display:inline-block;" class="selectpicker" data-style="btn-primary" size="1" data-live-search="true"
				//onchange="document.adminForm.submit();" name="filter_context"',"value", "text", $this->lists['contextList']);
			?>
		</div>
	</div>
	<div class=" col-lg-1 col-md-6 col-sm-6 col-xs-12">
		<div class="btn-group pull-right hidden-xs">
			<label for="limit" class="element-invisible">
				<?php echo Text::_('JGLOBAL_DISPLAY_NUM'); ?>
			</label>
			<?php echo $this->pagination->getLimitBox(); ?>
		</div>
	</div>
	<div class="clearfix"> </div>
	<?php
	if (empty($this->items ))
	{
		?>
		<div class="alert alert-info" role="alert">
			<?php echo Text::_('COM_HIERARCHY_NO_USER'); ?>
		</div>
		<?php
	}
	else
	{
		?>
		<table class="table table-striped table-hover" id="hierarchyList">
			<thead>
				<tr>
					<th class='left'>
						<?php echo HTMLHelper::_('grid.sort',  'COM_HIERARCHY_HIERARCHYS_USER_NAME', 'a.name', $listDirn, $listOrder); ?>
					</th>
<!--
					<th class='left'>
						<?php //echo Text::_('COM_HIERARCHY_CONTEXT'); ?>
					</th>
					<th class='left'>
						<?php //echo Text::_('COM_HIERARCHY_CONTEXT_ID'); ?>
					</th>
-->
					<th class='left'>
						<?php echo Text::_('COM_HIERARCHY_HIERARCHYS_REPORT_TO'); ?>
					</th>
					<th class='right'>
						<?php echo HTMLHelper::_('grid.sort',  'COM_HIERARCHY_HIERARCHYS_USER_ID', 'a.id', $listDirn, $listOrder); ?>
					</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="<?php echo isset($this->items[0]) ? count(get_object_vars($this->items[0])) : 10; ?>">
						<?php $options = array("showLimitBox" => false);?>
						<div class="pager">
							<?php echo $this->pagination->getPaginationLinks(null, $options); ?>
						</div>
					</td>
				</tr>
			</tfoot>
			<tbody>
				<?php
				foreach ($this->items as $i => $item)
				{
				?>
				<tr class="row<?php echo $i % 2; ?> reports_to" id="row_<?php echo $item->user_id;?>">
					<td>
<!--
						<img src="<?php //echo $this->gravatar; ?>" class="img-rounded" alt="" width="30" height="30">
-->
						<a href="#" title="<?php echo $item->name;?>"><?php  echo $item->name; ?>
							<i class="fa fa-angle-down" id="click_off_<?php echo $item->user_id;?>" onclick="hierarchySite.hierarchys.drillUpDrillDownList('<?php echo $item->user_id;?>')"; aria-hidden="true"></i>
						</a>
					</td>
<!--
					<td>
					<?php
						//echo $item->context = !empty($item->context) ? $item->context : '-';
						?>
					</td>
					<td>
					<?php
						//echo $item->context_id = !empty($item->context_id) ? $item->context_id : '-';
						?>
					</td>
-->
					<td>
					<?php
						if ($item->user_id)
						{
							$name = array();
							$reportsTo = $this->hierarchysModel->getReportsTo($item->user_id);

							foreach($reportsTo as $reportTo)
							{
								$user = Factory::getUser($reportTo->user_id);
								$name[] = $user->name;
							}

							$userName = implode(', ', $name);
							?>
							<span id="popover_<?php echo $i; ?>" data-toggle="popover" data-trigger="hover" data-placement="right"  data-content="<?php echo $userName; ?>">
								<?php echo $userName = strlen($userName) > 20 ? substr($userName, 0, 20) . "..." : $userName; ?>
							</span>
							<?php
						}
						?>
					</td>
					<td>
						<?php echo $item->user_id; ?>
					</td>
				</tr>
				<?php
				}
				?>
			</tbody>
		</table>
		<?php
	}
	?>
	<input type="hidden" id="task" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
<script type="text/javascript">
	var UriRoot = "<?php echo $UriRoot; ?>";
	var gravatar = "<?php echo $this->gravatar; ?>";
	hierarchySite.hierarchys.showUserNames();
</script>
