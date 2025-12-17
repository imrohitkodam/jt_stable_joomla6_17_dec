<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2017 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

HTMLHelper::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jticketing'. '/helpers/html');
HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.multiselect');
// Joomla 6: formbehavior.chosen removed - using native select

$user       = Factory::getUser();
$userId     = $user->get('id');
$listOrder  = $this->state->get('list.ordering');
$listDirn   = $this->state->get('list.direction');
$canCreate  = $user->authorise('core.create', 'com_jticketing');
$canEdit    = $user->authorise('core.edit', 'com_jticketing');
$canCheckin = $user->authorise('core.manage', 'com_jticketing');
$canChange  = $user->authorise('core.edit.state', 'com_jticketing');
$canDelete  = $user->authorise('core.delete', 'com_jticketing');
$saveOrder  = $listOrder == 'a.ordering';

if ($saveOrder)
{
	$saveOrderingUrl = 'index.php?option=com_tjlms&task=venues.saveOrderAjax&tmpl=component';
	HTMLHelper::_('sortablelist.sortable', 'venueList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}
?>
<div class="container-fluid">
	<?php
	if ($this->params->get('show_page_heading', 1)):
		?>
		<div class="page-header">
			<h1><?php echo $this->PageTitle;?></h1>
		</div>
		<?php
	endif;
	?>
</div>
</br>
<div id="jtwrap" class="tjBs3">
<form action="<?php echo Route::_('index.php?option=com_jticketing&view=venues'); ?>" method="post" name="adminForm" id="adminForm">
	<div>
		<?php echo $this->toolbarHTML;?>
	</div>
	<div class="clearfix"> </div>
	<hr class="hr-condensed" />
	<div class="col-xs-12 col-sm-4 mb-2">
		<ul class="list-inline">
			<li class="input-group event__separation">
				<span class="pull-left events__search " id="searchVenueInputBox">
					<input
						type="text"
						placeholder="<?php echo Text::_('COM_JTICKETING_SEARCH_VENUES_NAME'); ?>"
						name="filter_search"
						id="filter_search"
						value="<?php echo $this->state->get('filter.search'); ?>"
						class="form-control events__search--input"
						onchange="document.adminForm.submit();" />
				</span>
				<a id="searchVenueBtn"  class="btn btn-primary" href="javascript:void(0)" title="<?php echo Text::_('COM_JTICKETING_SEARCH_VENUE')?>">
					<i class="fa fa-search"></i>
				</a>
			</li>
			<li class="clear-sepration">
				<button
					type="reset"
					id="clear-search"
					onclick="document.getElementById('filter_search').value='';document.getElementById('venue_type').value='';
					document.getElementById('venue_privacy').value='';this.form.submit();"
					class="clear-search"
					title="<?php echo Text::_('COM_JTICKETING_CLEAR_SEARCH')?>">
					<i class="fa fa-remove"></i>
				</button>
			</li>
		</ul>
	</div>
	<div class="col-xs-12 col-sm-8 mb-2">
		<ul class="list-inline af-float-sm-right">
			<li class="input-group">
				<?php echo HTMLHelper::_('select.genericlist', $this->venueTypeList, "venue_type", 'style="display:inline-block;" class="selectpicker" data-style="btn-primary" size="1" data-live-search="true"
					onchange="document.adminForm.submit();" name="venue_type"',"value", "text", $this->lists['venueTypeList']);
				?>
			</li>
			<li class="input-group">
				<?php echo HTMLHelper::_('select.genericlist', $this->venuePrivacyList, "venue_privacy", 'style="display:inline-block;" class="inputbox" size="1"
					onchange="document.adminForm.submit();" name="venue_privacy"',"value", "text", $this->lists['venuePrivacyList']);
				?>
			</li>
			<li>
				<label for="limit" class="element-invisible"><?php echo Text::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC'); ?></label>
				<?php echo $this->pagination->getLimitBox(); ?>
			</li>
		</ul>
	</div>
	<div class="clearfix"> </div>
	<?php
	if (empty($this->items ))
	{
		?>
		<div class="alert alert-info" role="alert">
		<?php echo Text::_('NODATA'); ?>
		</div>
		<?php
	}
	else
	{
		?>
		<div class="col-xs-12" id="no-more-tables">
			<table class="table table-striped table-bordered table-hover mt-4" id="venueList">
				<thead class="text-break table-primary text-light">
					<tr>
						<th class="hidden-phone center">
							<input type="checkbox" name="checkall-toggle" value="" title="<?php echo Text::_('JGLOBAL_CHECK_ALL');?>" onclick="Joomla.checkAll(this)" />
						</th>
						<th class="center">
							<?php echo HTMLHelper::_('grid.sort',  'COM_JTICKETING_VENUES_ID', 'a.id', $listDirn, $listOrder); ?>
						</th>
						<?php
						if (isset($this->items[0]->state)):
							?>
							<th width="1%" class="nowrap center">
								<?php
								echo HTMLHelper::_('grid.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder);
								?>
							</th>
							<?php
						endif;
							?>
						<th>
							<?php echo HTMLHelper::_('grid.sort',  'COM_JTICKETING_VENUES_NAME', 'a.name', $listDirn, $listOrder); ?>
						</th>
						<th>
							<?php echo HTMLHelper::_('grid.sort',  'COM_JTICKETING_VENUES_CATEGORY', 'a.venue_category', $listDirn, $listOrder); ?>
						</th>
						<th>
							<?php echo HTMLHelper::_('grid.sort',  'COM_JTICKETING_VENUES_TYPE', 'a.online', $listDirn, $listOrder); ?>
						</th>
						<th>
							<?php echo HTMLHelper::_('grid.sort',  'COM_JTICKETING_VENUES_PRIVACY', 'a.privacy', $listDirn, $listOrder); ?>
						</th>
					</tr>
				</thead>
				<tbody>
				<?php
				foreach ($this->items as $i => $item):

				$canEdit = $user->authorise('core.edit', 'com_jticketing');

				if (!$canEdit && $user->authorise('core.edit.own', 'com_jticketing')):
					$canEdit = Factory::getUser()->id == $item->created_by;
				endif;
					?>
					<tr class="row<?php echo $i % 2; ?>">
						<td class="center">
							<?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
						</td>
						<td class="center hidden-xs  hidden-sm">
						<?php echo $item->id; ?>
						</td>

						<td class="center" data-title="<?php echo Text::_('JSTATUS');?>">
							<div>
								<a class="btn btn-micro hasTooltip" href="<?php if ($canEdit):?>javascript:void(0);<?php else: ?><?php echo Uri::root();?>index.php?option=com_users<?php endif;?>" title="<?php echo ($item->state) ? Text::_('TJTOOLBAR_UNPUBLISH') : Text::_('TJTOOLBAR_PUBLISH');?>"
								onclick="document.adminForm.cb<?php echo $i; ?>.checked=1; document.adminForm.boxchecked.value=1; Joomla.submitbutton('<?php echo ($item->state) ? 'venues.unpublish' : 'venues.publish';?>');">
								<?php if ($item->state == 1)
								{?>
									<span>
										<i class="fa fa-check-circle" aria-hidden="true"></i>
									</span>
								<?php }
								elseif ($item->state == 0)
								{?>
									<span>
										<i class="fa fa-times-circle" aria-hidden="true"></i>
									</span>
								<?php } ?>
								</a>
							</div>
						</td>

						<td data-title="<?php echo Text::_('COM_JTICKETING_VENUES_NAME');?>">
						<?php
						if (isset($item->checked_out) && $item->checked_out):
							echo HTMLHelper::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'venues.', $canCheckin);
						endif;
							?>
							<a href="<?php echo Route::_(Uri::root(). 'index.php?option=com_jticketing&view=venueform&layout=default&id='.(int) $item->id . '&Itemid=' . $this->venuesMenuItemId); ?>">
							<?php echo $this->escape($item->name); ?>
							</a>
						</td>
						<td data-title="<?php echo Text::_('COM_JTICKETING_VENUES_CATEGORY');?>">
							<?php echo $item->venue_category; ?>
						</td>
						<td data-title="<?php echo Text::_('COM_JTICKETING_VENUES_TYPE');?>">
							<?php echo ($item->online) ? Text::_("COM_JTICKETING_VENUE_TYPEONLINE"):Text::_("COM_JTICKETING_VENUE_TYPEOFFLINE") ?>
						</td>
						<td data-title="<?php echo Text::_('COM_JTICKETING_VENUES_PRIVACY');?>">
							<?php echo ($item->privacy) ? Text::_("COM_JTICKETING_VENUE_PRIVACY_PUBLIC"):Text::_("COM_JTICKETING_VENUE_PRIVACY_PRIVATE") ?>
						</td>
					</tr>
					<?php
				endforeach;
				?>
				</tbody>
			</table>
			<div class="col-xs-12">
				<div <?php echo isset($this->items[0]) ? count(get_object_vars($this->items[0])) : 10; ?>>
					<div class="pager">
						<?php echo $this->pagination->getPagesLinks(); ?>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
	?>
		<input type="hidden" name="task" value=""/>
		<input type="hidden" name="boxchecked" value="0"/>
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>
		<?php echo HTMLHelper::_('form.token'); ?>
	</form>
</div>
