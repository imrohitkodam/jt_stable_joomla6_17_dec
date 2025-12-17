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
	$saveOrderingUrl = 'index.php?option=com_jticketing&task=coupons.saveOrderAjax&tmpl=component';
	HTMLHelper::_('sortablelist.sortable', 'couponList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}
?>
<div class="tjBs5">
<form action="<?php echo Route::_('index.php?option=com_jticketing&view=coupons'); ?>"
	method="post" name="adminForm" id="adminForm">

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
							<?php echo HTMLHelper::_('searchtools.sort',  'COM_JTICKETING_COUPONS_NAME', 'a.name', $listDirn, $listOrder);?>
						</th>

						<th class='left'>
							<?php echo HTMLHelper::_('searchtools.sort',  'COM_JTICKETING_COUPONS_CODE', 'a.code', $listDirn, $listOrder);?>
						</th>

						<th class='left'>
							<?php echo HTMLHelper::_('searchtools.sort',  'COM_JTICKETING_COUPONS_VALUE', 'a.value', $listDirn, $listOrder);?>
						</th>

						<!-- <th class='left'>
							<?php //echo HTMLHelper::_('searchtools.sort',  'COM_JTICKETING_COUPONS_MAX_USE', 'a.limit', $listDirn, $listOrder);?>
						</th> -->

						<!-- <th class='left'>
							<?php //echo HTMLHelper::_('searchtools.sort',  'COM_JTICKETING_COUPONS_MAX_USE_PER_USER', 'a.max_per_user', $listDirn, $listOrder);?>
						</th> -->

						<th class='left'>
							<?php echo HTMLHelper::_('searchtools.sort',  'COM_JTICKETING_COUPONS_USED', 'a.used', $listDirn, $listOrder);?>
						</th>

						<th class='left'>
							<?php echo Text::_('COM_JTICKETING_COUPONS_EVENT_IDS'); ?>
						</th>
						<th class='left'>
							<?php echo HTMLHelper::_('searchtools.sort',  'COM_JTICKETING_COUPONS_VENDORS', 'a.vendor_id', $listDirn, $listOrder);?>
						</th>

						<th class='left'>
							<?php echo HTMLHelper::_('searchtools.sort',  'COM_JTICKETING_COUPONS_FROM_DATE', 'a.valid_from', $listDirn, $listOrder);?>
						</th>

						<th class='left'>
							<?php echo HTMLHelper::_('searchtools.sort',  'COM_JTICKETING_COUPONS_EXP_DATE', 'a.valid_to', $listDirn, $listOrder);?>
						</th>

						<!-- <th class='left'>
							<?php //echo HTMLHelper::_('searchtools.sort',  'COM_JTICKETING_COUPONS_CREATED_BY', 'a.created_by', $listDirn, $listOrder);?>
						</th> -->

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
								<?php echo HTMLHelper::_('jgrid.published', $item->state, $i, 'coupons.', $this->canChange, 'cb');?>
							</td>

							<td>
								<?php
								if (isset($item->checked_out) && $item->checked_out)
								{
									echo HTMLHelper::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'coupons.', $this->canCheckin);
								}
								?>

								<?php
								if ($this->canEdit)
								{
									?>
									<a href="<?php echo Route::_('index.php?option=com_jticketing&task=coupon.edit&id=' . (int) $item->id); ?>">
										<?php echo $this->escape($item->name); ?>
									</a>
									<?php
								}
								else
								{
									echo $this->escape($item->name);
								}
								?>
							</td>

							<td><?php echo $this->escape($item->code);?></td>

							<td><?php echo (int) $item->value;?></td>

							<!-- <td><?php //echo (int) $item->limit;?></td> -->

							<!-- <td><?php //echo (int) $item->max_per_user;?></td> -->

							<td><?php echo (int) $item->used;?></td>

							<td>
								<?php
									if ($item->event_ids)
									{
										$eventIds = $tempEvents = array();
										$eventIds = explode(",", $item->event_ids);

										foreach ($eventIds as $xrefEventId)
										{
											$event = JT::event()->loadByIntegration($xrefEventId);

											if (!empty($event->getId()))
											{
												if ($this->params->get('enable_eventstartdateinname'))
												{
													$tempEvents[] = $event->getTitle() . ' ( '. $this->utilities->getFormatedDate($event->getStartDate()) . ' )';
												}
												else
												{
													$tempEvents[] = $event->getTitle();
												}
											}
										}

										echo $this->escape(implode(', ', $tempEvents));
									}
								?>
							</td>
							<td>
							<?php
								$this->tjvendorTable->load(array('vendor_id' => $item->vendor_id));
								echo $this->escape($this->tjvendorTable->vendor_title ? $this->tjvendorTable->vendor_title : ' - ');
							?>
							</td>
							<td>
								<?php echo ($item->valid_from && $item->valid_from != '0000-00-00 00:00:00') ? $this->utilities->getFormatedDate($item->valid_from) : '-';?>
							</td>

							<td>
								<?php echo ($item->valid_to && $item->valid_to != '0000-00-00 00:00:00') ? $this->utilities->getFormatedDate($item->valid_to) : '-';?>
							</td>

							<!-- <td>
								<?php
									//$user = Factory::getUser($item->created_by);
									//echo $user->name;
								?>
							</td> -->


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