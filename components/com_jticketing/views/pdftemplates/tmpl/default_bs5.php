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
	<div class="row">
		<div class="col-md-12">
			<?php echo $this->toolbarHTML;?>
		</div>
		<hr>
	</div>

	<div class="col-md-12">

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
			<table class="table table-striped left_table table-bordered table-hover mt-4" id="pdfTemplateList">
				<thead class="table-primary text-light">
					<tr>
						<th width="1%" class="hidden-phone">
							<?php echo HTMLHelper::_('grid.checkall');?>
						</th>

						<th width="1%" class="nowrap center">
							<?php echo Text::_('JSTATUS');?>
						</th>

						<th class='left'>
							<?php echo Text::_('COM_JTICKETING_EVENT_NAME');?>
						</th>

						<th width="1%" class="nowrap center hidden-phone">
							<?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'title', $listDirn, $listOrder);?>
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
							<td class="hidden-phone">
								<?php echo HTMLHelper::_('grid.id', $i, $item->id);?>
							</td>

							<td class="center">
								<?php echo HTMLHelper::_('jgrid.published', $item->state, $i, 'pdftemplates.', false, 'cb');?>
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

<script>
	jQuery(document).ready(function() {
		// Remove unwanted tooltip
		if (jQuery('#jtwrap #pdfTemplateList [role="tooltip"]').length)
		{
			jQuery('#jtwrap #pdfTemplateList [role="tooltip"]').addClass('d-none')
		}
	});
</script>