<?php
/**
 * @package     JTicketing
 * @subpackage  com_jticketing
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\User\User;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\LayoutHelper;

HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.multiselect');

$listOrder  = $this->state->get('list.ordering');
$listDirn   = $this->state->get('list.direction');
$saveOrder  = $listOrder == 'a.ordering';

if ($saveOrder)
{
	$saveOrderingUrl = 'index.php?option=com_jticketing&task=pdftemplates.saveOrderAjax&tmpl=component';
	HTMLHelper::_('sortablelist.sortable', 'couponList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}
?>
<div class="tjBs5" id="jtwrap">
<form action="<?php echo Route::_('index.php?option=com_jticketing&view=pdftemplates&Itemid='. $this->listingPageItemId); ?>" method="post" name="adminForm" id="adminForm">

	<?php
	if (!empty($this->sidebar))
	{
		?>
		<div id="j-sidebar-container" class="span2">
			<?php echo $this->sidebar;?>
		</div>
		<div id="j-main-container" class="span10">
		<?php
	}
	else
	{
		?>
		<div id="j-main-container">
		<?php
	}
	?>

		<!-- Filter Section -->
		<?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this));?>

		<div class="clearfix"></div>

		<?php
		if (empty($this->items))
		{
			?>
			<div class="clearfix">&nbsp;</div>

			<div class="alert alert-info">
				<?php echo Text::_('NODATA');?>
			</div>
			<?php
		}
		else
		{
			?>
			<table class="table table-striped" id="couponList">
				<thead>
					<tr>
						<th width="1%" class="nowrap center hidden-phone">
							<?php echo HTMLHelper::_('searchtools.sort', '', 'a.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2');?>
						</th>

						<th width="1%" class="hidden-phone">
							<?php echo HTMLHelper::_('grid.checkall');?>
						</th>

						<th width="1%" class="nowrap center">
							<?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder);?>
						</th>

						<th class='left'>
							<?php echo HTMLHelper::_('searchtools.sort',  'COM_JTICKETING_EVENT_NAME', 'a.title', $listDirn, $listOrder);?>
						</th>

						<th width="1%" class="nowrap center hidden-phone">
							<?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder);?>
						</th>
					</tr>
				</thead>

				<tbody>
					<?php
					foreach ($this->items as $i => $item)
					{
						$ordering = ($listOrder == 'a.ordering');
						?>

						<tr class="row<?php echo $i % 2; ?>">
							<td class="order nowrap center hidden-phone">
								<?php
								if ($this->canChange)
								{
									$disabledLabel    = (!$saveOrder) ? Text::_('JORDERINGDISABLED') : '';
									$disableClassName = (!$saveOrder) ? 'inactive tip-top' : '';
									?>
									<span
										class="sortable-handler hasTooltip <?php echo $disableClassName?>"
										title="<?php echo $disabledLabel?>">
											<i class="icon-menu"></i>
									</span>

									<input type="text"
										style="display:none"
										name="order[]" size="5"
										value="<?php echo $item->ordering;?>"
										class="width-20 text-area-order " />
									<?php
								}
								else
								{
									?>
									<span class="sortable-handler inactive"><i class="icon-menu"></i></span>
									<?php
								}
								?>
							</td>

							<td class="hidden-phone">
								<?php echo HTMLHelper::_('grid.id', $i, $item->id);?>
							</td>

							<td class="center">
								<?php echo HTMLHelper::_('jgrid.published', $item->state, $i, 'pdftemplates.', $this->canChange, 'cb');?>
							</td>

							<td>
								<?php
								if (isset($item->checked_out) && $item->checked_out)
								{
									echo HTMLHelper::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'pdftemplates.', $this->canCheckin);
								}
								?>

								<?php
								if ($this->canEdit)
								{
									?>
									<a href="<?php echo Route::_('index.php?option=com_jticketing&task=pdftemplate.edit&id=' . (int) $item->id); ?>">
										<?php echo $this->escape($item->title); ?>
									</a>
									<?php
								}
								else
								{
									echo $this->escape($item->title);
								}
								?>
							</td>

							<td class="center hidden-phone"><?php echo (int) $item->id;?></td>
						</tr>

						<?php
					}
					?>
					</tbody>
				</table>
				<?php echo $this->pagination->getListFooter(); ?>
			<?php
		}
		?>

		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<?php echo HTMLHelper::_('form.token'); ?>
	</div>
</form>
</div>