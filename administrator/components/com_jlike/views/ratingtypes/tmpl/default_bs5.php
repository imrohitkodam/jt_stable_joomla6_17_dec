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
				<!-- <td>
					<a class="hasTooltip" href="<?php echo Route::_('index.php?option=com_jlike&task=ratingtype.edit&id=' . $row->id); ?>" title="<?php echo Text::_('JACTION_EDIT'); ?>">
						<?php // echo $this->escape($row->client); ?>
					</a>
				</td> -->
				<td>
					<a class="hasTooltip" href="<?php echo Route::_('index.php?option=com_jlike&task=ratingtype.edit&id=' . $row->id); ?>" title="<?php echo Text::_('JACTION_EDIT'); ?>">
						<?php echo $this->escape($row->title); ?>
					</a>
				</td>
				<td>
					<?php echo HTMLHelper::_('jgrid.isdefault', $row->is_default != '0', $i, 'ratingtypes.', $canChange && $row->is_default != '1'); ?>
				</td>
				<td>
					<?php echo $this->escape($row->code); ?>
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
