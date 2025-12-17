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
use Joomla\CMS\Factory;

HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.formvalidator');

// Load admin language file
$listOrder  = $this->escape($this->state->get('list.ordering'));
$listDirn   = $this->escape($this->state->get('list.direction'));
?>
<script>
jQuery(document).ready(function() {

	jQuery(".show-more").click(function(){
		jQuery(this).addClass("hide");
		jQuery(this).siblings(".maxStr").removeClass("hide");
		jQuery(this).siblings(".show-less").removeClass("hide");
	});
	jQuery(".show-less").click(function(){
		jQuery(this).addClass("hide");
		jQuery(this).siblings(".show-more").removeClass("hide");
		jQuery(this).siblings(".maxStr").addClass("hide");
	});
});
</script>
<form action="<?php echo Route::_('index.php?option=com_jlike&view=ratings'); ?>" id="adminForm" method="post" name="adminForm" class="">
	<?php if (!empty($this->sidebar)) : ?>
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
	<table class="table table-striped">
		<thead>
			<tr>
				<th width="3%">
					<input type="checkbox" name="checkall-toggle" value=""
					title="<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)"/>
				</th>
				<th width="7%">
					<?php echo HTMLHelper::_('grid.sort', 'COM_JLIKE_VIEW_RATING_STATE', 'a.state', $listDirn, $listOrder); ?>
				</th>
				<th width="7%">
					<?php echo HTMLHelper::_('grid.sort', 'COM_JLIKE_VIEW_RATING_NAME', 'u.name', $listDirn, $listOrder); ?>
				</th>
				<th width="7%">
					<?php echo Text::_('COM_JLIKE_VIEW_RATING_RATING_TYPE_TITLE'); ?>
				</th>
				<th width="7%">
					<?php echo HTMLHelper::_('grid.sort', 'COM_JLIKE_VIEW_RATING_CONTENT_TITLE', 'c.title', $listDirn, $listOrder); ?>
				</th>
				<th width="7%">
					<?php echo HTMLHelper::_('grid.sort', 'COM_JLIKE_VIEW_RATING_RATING', 'a.rating', $listDirn, $listOrder); ?>
				</th>
				<th width="7%">
					<?php echo HTMLHelper::_('grid.sort', 'COM_JLIKE_VIEW_RATING_TITLE', 'a.title', $listDirn, $listOrder); ?>
				</th>
				<th width="7%">
					<?php echo HTMLHelper::_('grid.sort', 'COM_JLIKE_VIEW_RATING_REVIEW', 'a.review', $listDirn, $listOrder); ?>
				</th>
				<th width="7%">
					<?php echo Text::_('COM_JLIKE_VIEW_RATING_UCM_ID'); ?>
				</th>
				<th width="7%">
					<?php echo HTMLHelper::_('grid.sort', 'COM_JLIKE_VIEW_RATING_CREATED_DATE', 'a.created_date', $listDirn, $listOrder); ?>
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
					<td align="center">
						<?php echo HTMLHelper::_('jgrid.published', $row->state, $i, 'ratings.', $canChange, 'cb'); ?>
					</td>
					<td align="center">
						<?php echo Factory::getUser($row->submitted_by)->name; ?> | <?php echo Factory::getUser($row->submitted_by)->email; ?>
					</td>
					<td align="center">
						<?php echo $this->escape(isset($this->ratingTypes[$row->rating_type_id]) ? $this->ratingTypes[$row->rating_type_id] : '-'); ?>
					</td>
					<td align="center">
						<?php echo $this->escape($row->content_title); ?>
					</td>
					<td align="center">
						<?php echo $row->rating; ?>/<?php echo $row->rating_scale; ?>
					</td>
					<td align="center">
						<?php echo $this->escape($row->title); ?>
					</td>
					<td align="center">
						<div class="more">
						</div><?php
						if (strlen($row->review) > 50)
						{
							$minStr = substr($row->review, 0, 49);
							$maxStr = substr($row->review, 50);
							echo "<span class='minStr'>" . $this->escape($minStr) . "</span>";
							echo "<a  class='show-more'> " . Text::_('COM_JLIKE_VIEW_RATING_READ_MORE') . "</a>";
							echo "<span class='hide maxStr'>" . $this->escape($maxStr) . "</span>";
							echo "<a class='show-less hide'> " . Text::_('COM_JLIKE_VIEW_RATING_SHOW_LESS') . "</a>";
						}
						else
						{
							echo $this->escape($row->review);
						}
						?>
					</td>
					<td align="center">
						<?php echo $row->tjucm_content_id; ?>
					</td>
					<td align="center">
						<?php echo $row->created_date; ?>
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
