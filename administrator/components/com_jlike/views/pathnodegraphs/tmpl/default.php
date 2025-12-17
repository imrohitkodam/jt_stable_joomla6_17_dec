<?php
/**
 * @version    SVN: <svn_id>
 * @package    School
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2017 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access to this file

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Language\Text;

HTMLHelper::addIncludePath(JPATH_COMPONENT . '/helpers/html');

HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.multiselect');
HTMLHelper::_('behavior.multiselect'); // only for list tables


$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$saveOrder = $listOrder == 'a.ordering';
?>

<div class="tj-page">
	<div class="row-fluid">
		<form action="<?php echo Route::_('index.php?option=com_jlike&view=pathnodegraphs'); ?>" method="post" name="adminForm" id="adminForm">
			<?php if (!empty( $this->sidebar)) : ?>
				<div id="j-sidebar-container" class="span2">
					<?php echo $this->sidebar; ?>
				</div>
				<div id="j-main-container" class="span10">
			<?php else : ?>
				<div id="j-main-container">
			<?php endif; ?>

				<?php
					// Search tools bar
				 echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this));
				?>
				<div class="clearfix"></div>
				<?php if (empty($this->items)) : ?>
				<div class="alert alert-info">
					<?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
				</div>
				<?php else : ?>
				<table class="table table-striped" id="pathnodegraphsList">
					<thead>
						<tr>
							<th width="1%" class="nowrap center hidden-phone">
								<?php echo HTMLHelper::_('searchtools.sort', '', 'a.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
							</th>

							<th width="1%" class="center">
								<?php echo HTMLHelper::_('grid.checkall'); ?>
							</th>
							<th class='left'>
							<?php echo HTMLHelper::_('grid.sort',  'COM_JLIKE_PATH_NODE_GRAPH_PATH_NODE_GRAPH_ID', 'a.pathnode_graph_id', $listDirn, $listOrder); ?>
							</th>

							<th class='left'>
							<?php echo HTMLHelper::_('grid.sort',  'COM_JLIKE_PATH_NODE_GRAPH_PATH_ID', 'a.path_id', $listDirn, $listOrder); ?>
							</th>

							<th class='left'>
							<?php echo HTMLHelper::_('grid.sort',  'COM_JLIKE_PATH_NODE_GRAPH_LFT', 'a.lft', $listDirn, $listOrder); ?>
							</th>

							<th class='left'>
							<?php echo HTMLHelper::_('grid.sort',  'COM_JLIKE_PATH_NODE_GRAPH_NODE', 'a.node', $listDirn, $listOrder); ?>
							</th>

							<th class='left'>
							<?php echo HTMLHelper::_('grid.sort',  'COM_JLIKE_PATH_NODE_GRAPH_RGT', 'a.rgt', $listDirn, $listOrder); ?>
							</th>

							<th class='left'>
							<?php echo HTMLHelper::_('grid.sort',  'COM_JLIKE_PATH_NODE_GRAPH_PATH_ORDER', 'a.order', $listDirn, $listOrder); ?>
							</th>


							<th class='left'>
							<?php echo HTMLHelper::_('grid.sort',  'COM_JLIKE_PATH_NODE_GRAPH_PATH_ISPATH', 'a.isPath', $listDirn, $listOrder); ?>
							</th>

							<th class='left'>
							<?php echo HTMLHelper::_('grid.sort',  'COM_JLIKE_PATH_NODE_GRAPH_THIS_COMPULSORY', 'a.this_compulsory', $listDirn, $listOrder); ?>
							</th>


							<th class='left'>
							<?php echo HTMLHelper::_('grid.sort',  'COM_JLIKE_PATH_NODE_GRAPH_PATH_DELAY', 'a.delay', $listDirn, $listOrder); ?>
							</th>

							<th class='left'>
							<?php echo HTMLHelper::_('grid.sort',  'COM_JLIKE_PATH_NODE_GRAPH_PATH_DURATION', 'a.duration', $listDirn, $listOrder); ?>
							</th>

							<th class='left'>
							<?php echo HTMLHelper::_('grid.sort',  'COM_JLIKE_PATH_NODE_GRAPH_PATH_VISIBILITY', 'a.visibility', $listDirn, $listOrder); ?>
							</th>
						</tr>
						</thead>

						<tbody>
							<?php
							foreach ($this->items as $i => $item)
							{
								$link = Route::_('index.php?option=com_jlike&task=pathnodegraph.edit&pathnode_graph_id=' . $item->pathnode_graph_id);
								$item->max_ordering = 0;
								$ordering   = ($listOrder == 'a.ordering');
								$canCreate  = $this->canCreate;
								$canEdit    = $this->canEdit;

								$canCheckin = $this->canCheckin;
								$canChange  = $this->canChangeStatus;
								?>

								<tr class="row<?php echo $i % 2; ?>" sortable-group-id="<?php echo $item->class; ?>">
									<td class="order nowrap center hidden-phone">
										<?php
										$iconClass = '';

										if (!$canChange)
										{
											$iconClass = ' inactive';
										}
										elseif (!$saveOrder)
										{
											$iconClass = ' inactive tip-top hasTooltip" title="' . HTMLHelper::_('tooltipText', 'JORDERINGDISABLED');
										}
										?>
										<span class="sortable-handler<?php echo $iconClass ?>">
											<span class="icon-menu" aria-hidden="true"></span>
										</span>
										<?php if ($canChange && $saveOrder) : ?>
											<input type="text" style="display:none" name="order[]" size="5" value="<?php echo $item->ordering; ?>" class="width-20 text-area-order" />
										<?php endif; ?>
									</td>

									<td class="center">
										<?php echo HTMLHelper::_('grid.id', $i,$item->pathnode_graph_id); ?>

									</td>

									<td class="has-context">
										<a href="<?php echo $link; ?>" title="<?php echo Text::_('COM_JLIKE_PATHS_LIST_VIEW_DESC');?>">
                                    <?php echo ($item->pathnode_graph_id); ?></a>


										</div>
									</td>

									<td><?php echo ($item->path_id); ?></td>
									<td><?php echo ($item->lft); ?></td>
									<td><?php echo ($item->node); ?></td>
									<td><?php echo ($item->rgt); ?></td>
									<td><?php echo ($item->order); ?></td>
									<td><?php echo ($item->isPath); ?></td>
									<td><?php echo ($item->this_compulsory); ?></td>
									<td><?php echo ($item->delay); ?></td>
									<td><?php echo ($item->duration); ?></td>
									<td><?php echo ($item->visibility); ?></td>

								</tr>

								<?php
							}
							?>
						<tbody>
					</table>
					<?php endif; ?>
					<?php echo $this->pagination->getListFooter(); ?>

					<input type="hidden" name="task" value="" />
					<input type="hidden" name="boxchecked" value="0" />
				<input type="hidden" name="filter_order" value="<?php echo $this->sortColumn; ?>" />
	            <input type="hidden" name="filter_order_Dir" value="<?php echo $this->sortDirection; ?>" />
					<?php echo HTMLHelper::_('form.token'); ?>
			</div>
		</form>
	</div>
</div>
