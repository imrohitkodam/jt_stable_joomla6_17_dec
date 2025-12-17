<?php
/**
 * @package     Jlike
 * @subpackage  com_jlike
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2018 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Language\Text;

HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.multiselect');
HTMLHelper::_('behavior.multiselect'); // only for list tables


$listOrder  = $this->escape($this->state->get('list.ordering'));
$listDirn   = $this->escape($this->state->get('list.direction'));

$publish = '<i class="icon-publish"></i>';
$unpublish = '<i class="icon-unpublish"></i>';
?>
<form action="<?php echo Route::_('index.php?option=com_jlike&view=ratingtypes'); ?>" id="adminForm" method="post" name="adminForm">

	<?php if (!empty( $this->sidebar)) : ?>
		<div id="j-sidebar-container" class="span2">
	<?php echo $this->sidebar; ?>
		</div>
		<div id="j-main-container" class="span10">
	<?php else : ?>
		<div id="j-main-container">
	<?php endif;?>
	<?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>

	<?php if (empty($this->items))
	{ ?>
		<div class="alert alert-info">
			<?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
		</div>
	<?php
	}
	else
	{
	?>
	<table class="table table-striped table-hover">
		<thead>
			<tr>
				<th width="3%">
					<input type="checkbox" name="checkall-toggle" value=""
					title="<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)"/>
				</th>
				<?php if (isset($this->items[0]->state)): ?>
				<th width="1%" class="nowrap center">
					<?php echo HTMLHelper::_('grid.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
				</th>
				<?php endif; ?>
				<!-- <th width="7%">
					<?php // echo HTMLHelper::_('grid.sort', 'COM_JLIKE_RATING_TYPE_COMPONENT', 'a.client', $listDirn, $listOrder); ?>
				</th> -->
				<th width="7%">
					<?php echo HTMLHelper::_('grid.sort', 'COM_JLIKE_RATING_TYPE_RATING_TITLE', 'a.title', $listDirn, $listOrder); ?>
				</th>
				<th width="7%">
					<?php echo HTMLHelper::_('grid.sort', 'COM_JLIKE_RATING_TYPE_RATING_DEFAULT', 'a.is_default', $listDirn, $listOrder); ?>
				</th>
				<th width="7%">
					<?php echo HTMLHelper::_('grid.sort', 'COM_JLIKE_RATING_TYPE_CODE', 'a.code', $listDirn, $listOrder); ?>
				</th>
				<th width="7%">
					<?php echo HTMLHelper::_('grid.sort', 'COM_JLIKE_RATING_TYPE_SHOW_TITLE', 'a.show_title', $listDirn, $listOrder); ?>
				</th>
				<th width="7%">
					<?php echo HTMLHelper::_('grid.sort', 'COM_JLIKE_RATING_TYPE_REQUIRED_TITLE', 'a.title_required', $listDirn, $listOrder); ?>
				</th>
				<th width="7%">
					<?php echo HTMLHelper::_('grid.sort', 'COM_JLIKE_RATING_TYPE_SHOW_RATING', 'a.show_rating', $listDirn, $listOrder); ?>
				</th>
				<th width="7%">
					<?php echo HTMLHelper::_('grid.sort', 'COM_JLIKE_RATING_TYPE_REQUIRED_RATING', 'a.rating_required', $listDirn, $listOrder); ?>
				</th>
				<th width="7%">
					<?php echo HTMLHelper::_('grid.sort', 'COM_JLIKE_RATING_TYPE_RATING_SCALE', 'a.rating_scale', $listDirn, $listOrder); ?>
				</th>
				<th width="7%">
					<?php echo HTMLHelper::_('grid.sort', 'COM_JLIKE_RATING_TYPE_SHOW_REVIEW', 'a.show_review', $listDirn, $listOrder); ?>
				</th>
				<th width="7%">
					<?php echo HTMLHelper::_('grid.sort', 'COM_JLIKE_RATING_TYPE_REQUIRED_REVIEW', 'a.review_required', $listDirn, $listOrder); ?>
				</th>
				<th width="7%">
					<?php echo HTMLHelper::_('grid.sort', 'COM_JLIKE_RATING_TYPE_TJUCM_TYPE', 'a.tjucm_type_id', $listDirn, $listOrder); ?>
				</th>
				<th width="7%">
					<?php echo HTMLHelper::_('grid.sort', 'COM_JLIKE_RATING_TYPE_SHOW_ALL_RATING', 'a.show_all_rating', $listDirn, $listOrder); ?>
				</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($this->items as $i => $row) :
				$canChange  = $this->user->authorise('core.edit.state', 'com_jlike');
			?>
			<tr>
				<td>
					<?php echo HTMLHelper::_('grid.id', $i, $row->id);?>
				</td>
				<?php if (isset($this->items[0]->state)): ?>
				<td class="center">
					<?php echo HTMLHelper::_('jgrid.published', $row->state, $i, 'ratingtypes.', $canChange, 'cb'); ?>
				</td>
				<?php endif; ?>
				<!-- <td align="center">
					<a class="hasTooltip" href="<?php echo Route::_('index.php?option=com_jlike&task=ratingtype.edit&id=' . $row->id); ?>" title="<?php echo Text::_('JACTION_EDIT'); ?>">
						<?php // echo $this->escape($row->client); ?>
					</a>
				</td> -->
				<td align="center">
					<a class="hasTooltip" href="<?php echo Route::_('index.php?option=com_jlike&task=ratingtype.edit&id=' . $row->id); ?>" title="<?php echo Text::_('JACTION_EDIT'); ?>">
						<?php echo $this->escape($row->title); ?>
					</a>
				</td>
				<td align="center">
					<?php echo HTMLHelper::_('jgrid.isdefault', $row->is_default != '0', $i, 'ratingtypes.', $canChange && $row->is_default != '1'); ?>
				</td>
				<td align="center">
					<?php echo $this->escape($row->code); ?>
				</td>
				<td align="center">
					<?php echo $row->show_title ? $publish : $unpublish; ?>
				</td>
				<td align="center">
					<?php echo $row->title_required ? $publish : $unpublish; ?>
				</td>
				<td align="center">
					<?php echo $row->show_rating ? $publish : $unpublish; ?>
				</td>
				<td align="center">
					<?php echo $row->rating_required ? $publish : $unpublish; ?>
				</td>
				<td align="center">
					<?php echo $row->rating_scale; ?>
				</td>
				<td align="center">
					<?php echo $row->show_review ? $publish : $unpublish; ?>
				</td>
				<td align="center">
					<?php echo $row->review_required ? $publish : $unpublish; ?>
				</td>
				<td align="center">
					<?php echo $row->tjucm_type_id; ?>
				</td>
				<td align="center">
					<?php echo $row->show_all_rating ? $publish : $unpublish; ?>
				</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
		<tfoot>
			<tr>
				<td colspan="<?php echo isset($this->items[0]) ? count(get_object_vars($this->items[0])) : 10; ?>">
					<?php echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
		</tfoot>
	</table>
	<?php
	}
	?>
	<input type="hidden" name="task" value=""/>
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
