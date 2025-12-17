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
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

HTMLHelper::addIncludePath(JPATH_COMPONENT . '/helpers/html');

HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.multiselect');

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$saveOrder = $listOrder == 'a.ordering';

if (!empty($this->extra_sidebar))
{
    $this->sidebar .= $this->extra_sidebar;
}
?>

<div class="tj-page">
	<div class="row-fluid">
		<form action="index.php?option=com_jlike&view=contents" id="adminForm" method="post" name="adminForm" class="form-validate">
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
		<table class="table table-striped" id="contentsList">
			<thead>
				<tr>
					<th width="1%" class="nowrap center hidden-phone">
						<?php echo HTMLHelper::_('searchtools.sort', '', 'a.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
					</th>

					<th width="1%" class="center">
						<?php echo HTMLHelper::_('grid.checkall'); ?>
					</th>

					<th class='left'>
					<?php echo HTMLHelper::_('grid.sort',  'COM_JLIKE_CONTENT_ID', 'a.id', $listDirn, $listOrder); ?>
					</th>

					<th class='left'>
					<?php echo HTMLHelper::_('grid.sort',  'COM_JLIKE_CONTENT_ELEMENTID', 'a.element_id', $listDirn, $listOrder); ?>
					</th>

					<th class='left'>
					<?php echo HTMLHelper::_('grid.sort',  'COM_JLIKE_CONTENT_URL', 'a.url', $listDirn, $listOrder); ?>
					</th>

					<th class='left'>
					<?php echo HTMLHelper::_('grid.sort',  'COM_JLIKE_CONTENT_ELEMENT', 'a.element', $listDirn, $listOrder); ?>
					</th>

					<th class='left'>
					<?php echo HTMLHelper::_('grid.sort',  'COM_JLIKE_CONTENT_TITLE', 'a.title', $listDirn, $listOrder); ?>
					</th>

					<th class='left'>
					<?php echo HTMLHelper::_('grid.sort',  'COM_JLIKE_CONTENT_LIKECOUNT', 'a.like_cnt', $listDirn, $listOrder); ?>
					</th>

					<th class='left'>
					<?php echo HTMLHelper::_('grid.sort',  'COM_JLIKE_CONTENT_DISLIKECOUNT', 'a.dislike_cnt', $listDirn, $listOrder); ?>
					</th>
				</tr>
			</thead>

			<tbody>
			<?php
			foreach ($this->items as $i => $item)
			{
				$link = Route::_('index.php?option=com_jlike&task=content.edit&id=' . $item->id);
				?>

				<tr class="row<?php echo $i % 2; ?>">
					<td class="order nowrap center hidden-phone">
						<?php
						$iconClass = '';
						if (!$saveOrder)
						{
							$iconClass = ' inactive tip-top hasTooltip" title="' . HTMLHelper::_('tooltipText', 'JORDERINGDISABLED');
						}
						?>


					</td>

					<td class="center">
						<?php echo HTMLHelper::_('grid.id', $i,$item->id); ?>

					</td>



					<td class="has-context">

						<a href="<?php echo $link; ?>" title="<?php echo Text::_('COM_JLIKE_PATHS_LIST_VIEW_DESC');?>">
                    <?php echo ($item->id); ?></a>



						</div>
					</td>

					<td><?php echo ($item->element_id); ?></td>
					<td><?php echo ($item->url); ?></td>
					<td><?php echo ($item->element); ?></td>
					<td><?php echo ($item->title); ?></td>
					<td><?php echo ($item->like_cnt); ?></td>
					<td><?php echo ($item->dislike_cnt); ?></td>

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
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
        <input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
		<?php echo HTMLHelper::_('form.token'); ?>
		</div>
		</form>
	</div>
</div>
