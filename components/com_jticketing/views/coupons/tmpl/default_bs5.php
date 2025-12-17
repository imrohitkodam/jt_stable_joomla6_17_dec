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
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.multiselect');

/** @var $this JticketingViewCoupons */

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
?>

<div class="container">
	<?php
	if ($this->params->get('show_page_heading', 1))
	{
		?>
		<div class="page-header"><h1><?php echo $this->escape($this->params->get('page_heading'));?></h1></div>
		<?php
	}
	?>
</div>

<div id="jtwrap" class="tjBs5 jticketing-wrapper couponsListing">
	<form action="<?php echo Route::_('index.php?option=com_jticketing&view=coupons'); ?>" method="post" name="adminForm" id="adminForm"
	class="jtFilters">
		<div class="mt-2"><?php echo $this->toolbarHTML;?></div>
		<div class="clearfix"></div>
		<hr class="hr-condensed mt-0 mb-3"/>

		<div class="col-md-12">
			<div class="clearfix"></div>
			<div class="row">
				<div class="col-md-12">
					<?php
					echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this));
					?>
				</div>
			</div>
		</div>
		<div class="clearfix"></div>
		<?php
		if (empty($this->items))
		{
			?>
			<div class="alert alert-info" role="alert"><?php echo Text::_('NODATA');?></div>
			<?php
		}
		else
		{
			?>
			<div class="row">
				<div class="col-xs-12" id="no-more-tables">
					<table class="table table-striped table-bordered table-hover " id="couponList">
						<thead class="text-break table-primary text-light">
							<tr>
								<th class="hidden-phone center">
									<?php echo HTMLHelper::_('grid.checkall'); ?>
								</th>
								<th class="center">
									<?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder);?>
								</th>
								<th class="center">
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_JTICKETING_COUPONS_NAME', 'a.name', $listDirn, $listOrder);?>
								</th>
								<th class="center">
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_JTICKETING_COUPONS_CODE', 'a.code', $listDirn, $listOrder);?>
								</th>
								<th class="center">
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_JTICKETING_COUPONS_VALUE', 'a.value', $listDirn, $listOrder);?>
								</th>
								<th class="center">
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_JTICKETING_COUPONS_MAX_USE', 'a.limit', $listDirn, $listOrder);?>
								</th>

								<th class="center">
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_JTICKETING_COUPONS_MAX_USE_PER_USER', 'a.max_per_user', $listDirn, $listOrder);?>
								</th>

								<th class="center">
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_JTICKETING_COUPONS_USED', 'a.used', $listDirn, $listOrder);?>
								</th>
								<th class="center">
									<?php echo Text::_('COM_JTICKETING_COUPONS_EVENT_IDS'); ?>
								</th>

								<th class="center">
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_JTICKETING_COUPONS_FROM_DATE', 'a.valid_from', $listDirn, $listOrder);?>
								</th>
								<th class="center">
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_JTICKETING_COUPONS_EXP_DATE', 'a.valid_to', $listDirn, $listOrder);?>
								</th>

								<?php
								if ($this->user->authorise('core.admin'))
								{
									?>
									<th class="center">
										<?php echo HTMLHelper::_('searchtools.sort', 'COM_JTICKETING_COUPONS_CREATED_BY', 'a.created_by', $listDirn, $listOrder);?>
									</th>
									<?php
								}
								?>
								<th class="center">
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_JTICKETING_COUPONS_ID', 'a.id', $listDirn, $listOrder);?>
								</th>
							</tr>
						</thead>
						<tbody>
							<?php
							foreach ($this->items as $i => $item)
							{
								?>
								<tr class="row<?php echo $i % 2; ?>">
									<td class="center hidden-xs  hidden-sm">
										<?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
									</td>
									<td class="center">
										<?php
										echo HTMLHelper::_('jgrid.published', $item->state, $i, 'coupons.', $this->canChange, 'cb');
										?>
									</td>
									<td data-title="<?php echo Text::_('COM_JTICKETING_COUPONS_NAME');?>">
										<a href="<?php echo Route::_('index.php?option=com_jticketing&view=couponform&layout=default&id=' . (int) $item->id .'&Itemid=' . $this->couponsMenuItemId); ?>">
											<?php echo $this->escape($item->name); ?>
										</a>
									</td>
									<td data-title="<?php echo Text::_('COM_JTICKETING_COUPONS_CODE');?>">
										<?php echo $this->escape($item->code);?>
									</td>
									<td data-title="<?php echo Text::_('COM_JTICKETING_COUPONS_VALUE');?>">
										<?php echo (int) $item->value;?>
									</td>
									<td data-title="<?php echo Text::_('COM_JTICKETING_COUPONS_MAX_USE');?>">
										<?php echo (int) $item->limit;?>
									</td>
									<td data-title="<?php echo Text::_('COM_JTICKETING_COUPONS_MAX_USE_PER_USER');?>">
										<?php echo (int) $item->max_per_user;?>
									</td>

									<td data-title="<?php echo Text::_('COM_JTICKETING_COUPONS_USED');?>">
										<?php echo (int) $item->used;?>
									</td>
									<td data-title="<?php echo Text::_('COM_JTICKETING_COUPONS_EVENT_IDS');?>">
									<?php
										if ($item->event_ids)
										{
											$eventIds  = $tempEvents = array();
											$eventIds = explode(",", $item->event_ids);

											foreach ($eventIds as $xrefEventId)
											{
												$event = JT::event()->loadByIntegration((int) $xrefEventId);

												if (!empty($event->integrationId))
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
									<td data-title="<?php echo Text::_('COM_JTICKETING_COUPONS_FROM_DATE');?>">
										<?php echo ($item->valid_from && $item->valid_from != '0000-00-00 00:00:00') ? HTMLHelper::_('date', $item->valid_from, Text::_('DATE_FORMAT_LC4')) : '-';?>
									</td>
									<td data-title="<?php echo Text::_('COM_JTICKETING_COUPONS_EXP_DATE');?>">
										<?php echo ($item->valid_to && $item->valid_to != '0000-00-00 00:00:00') ? HTMLHelper::_('date', $item->valid_to, Text::_('DATE_FORMAT_LC4')) : '-';?>
									</td>

									<?php
									if ($this->user->authorise('core.admin'))
									{
										?>
										<td data-title="<?php echo Text::_('COM_JTICKETING_COUPONS_CREATED_BY');?>">
											<?php
											$this->tjvendorTable->load(array('user_id' => $item->created_by));
											echo $this->tjvendorTable->vendor_title ? $this->tjvendorTable->vendor_title : ' - ';?>
										</td>
										<?php
									}
									?>
									<td data-title="<?php echo Text::_('COM_JTICKETING_COUPONS_ID');?>">
										<?php echo (int) $item->id;?>
									</td>
								</tr>
								<?php
							}
							?>
						</tbody>
					</table>

					<div class="col-xs-12">
						<?php echo $this->pagination->getPagesLinks(); ?>
					</div>
				</div>
			</div>
			<?php
		}
		?>
		<input type="hidden" name="task" value=""/>
		<input type="hidden" name="boxchecked" value="0"/>
		<?php echo HTMLHelper::_('form.token');?>
	</form>
</div>
<?php
Factory::getDocument()->addScriptDeclaration('
	Joomla.submitbutton = function(task){jtSite.coupons.couponsSubmitData(task);}
');
?>
<script>
	jQuery(document).ready(function() {
		if (jQuery('.couponsListing .js-stools .btn-toolbar:first').length)
		{
			jQuery('.couponsListing .js-stools .btn-toolbar:first') . addClass('float-end');
			jQuery('.couponsListing .js-stools .btn-toolbar:first') . parent() . addClass('row');
		}
	});
</script>
