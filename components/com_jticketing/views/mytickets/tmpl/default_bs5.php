<?php
/**
 * @version    SVN: <svn_id>
 * @package    Com_Jticekting
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2018 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Layout\LayoutHelper;

HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('bootstrap.renderModal', 'a.modal');
HTMLHelper::_('behavior.multiselect');
// Joomla 6: formbehavior.chosen removed - using native select

$listOrder  = $this->escape($this->state->get('list.ordering'));
$listDirn   = $this->escape($this->state->get('list.direction'));
?>
<div  class="floattext">
	<h1 class="componentheading"><?php	echo Text::_('MY_TICKET');?></h1>
</div>

<div id="jtwrap" class="row">
	<form action="<?php echo Route::_('index.php?option=com_jticketing&view=mytickets'); ?>"
		method="post" name="adminForm" id="adminForm" class="jtFilters">
		<div class="float-end">
		<?php
		echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this));
		?>
		</div>
		<div class="clearfix"></div>
		<?php
		if (empty($this->items))
		{
		?>
			<div class="col-xs-12 alert alert-info jtleft">
				<?php echo Text::_('COM_JTICKETING_NO_TICKETS_FOUND'); ?>
			</div>
		<?php
		}
		else
		{
		?>
		<div class="row">
			<div class="jticketing-tbl af-mt-10 overflow-x-scroll" id="no-more-tables">
				<table class="table table-striped left_table table-bordered table-hover table-light border " id="usersList">
					<thead class="table-primary text-light">
						<tr>
							<th width="1%" class="hidden-phone">
							<input type="checkbox" name="checkall-toggle" value="" title="<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
							</th>
							<th class='left'>
								<?php echo HTMLHelper::_('grid.sort',  'TICKET_ID', 'attendee.id', $listDirn, $listOrder); ?>
							</th>
							<th class='left'>
								<?php echo  HTMLHelper::_('grid.sort', 'EVENT_NAME', 'events.title', $listDirn, $listOrder); ?>
							</th>
							<th class='left'>
								<?php echo Text::_('EVENTDATE'); ?>
							</th>
							<th class='left'>
								<?php echo Text::_('TICKET_RATE'); ?>
							</th>
							<th class='left'>
								<?php echo Text::_('TOTAL_AMOUNT_BUY'); ?>
							</th>
							<th align="left">
								<?php echo  Text::_('PREVIEW_TICKET'); ?>
							</th>
						</tr>
					</thead>
				<tbody>
					<?php
					$j = 0;

					foreach ($this->items as $i => $item) :
						$order = JT::order()->loadByOrderId($item->order_id);
						$event = JT::event($item->event_id);
						$ordering   = ($listOrder == 'b.ordering');
						$datetoshow = '';
						$timezonestring = $this->jticketingmainhelper->getTimezoneString($item->event_id);
						$event = JT::event($item->event_id);

						if ($timezonestring['startdate'] == $timezonestring['enddate'])
						{
							$datetoshow = $timezonestring['startdate'];
						}
						else
						{
							$datetoshow = Text::_('COM_JTICKETING_FROM') . $timezonestring['startdate'] . Text::_('COM_JTICKETING_TO') . $timezonestring['enddate'];
						}

						if (!empty($timezonestring['eventshowtimezone']))
						{
							$datetoshow .= '<br/>' . $timezonestring['eventshowtimezone'];
						}
						else
						{
						}

						?>
						<tr class="row<?php echo $i % 2; ?>" >
							<td class="center hidden-phone">
								<?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
							</td>
							<?php
							if (isset($this->items[0]->state))
							:
							?>
							<td class="center">
								<?php echo HTMLHelper::_('jgrid.published', $item->state, $i, 'enrollments.', $canChange, 'cb'); ?>
							</td>
							<?php
							endif; ?>
							<td data-title="<?php echo Text::_('TICKET_ID');?>">
								<?php echo htmlspecialchars($item->ticket_id); ?>
							</td>
							<td data-title="<?php echo Text::_('EVENT_NAME');?>">
								<a href="<?php	echo $event->getUrl();?>">
							<?php echo htmlspecialchars($item->title);?></a>
							</td>
							<td align="center" data-title="<?php echo Text::_('EVENTDATE');?>"><?php
								echo $datetoshow;
							?></td>
							<td align="center" data-title="<?php echo Text::_('TICKET_RATE');?>">
								<?php echo $this->utilities->getFormattedPrice($item->amount); ?>
							</td>
							<td align="center" data-title="<?php echo Text::_('TOTAL_AMOUNT_BUY');?>">
								<?php echo $order->getAmount(true); ?>
							</td>

							<td data-title="<?php echo Text::_('PREVIEW_TICKET');?>">
							<?php
								if ($item->status == 'A')
								:
									$link = ('index.php?option=com_jticketing&view=mytickets&tmpl=component&layout=ticketprint&attendee_id=' .
										$item->id . '&Itemid=' . $this->Itemid);

									$link = $this->JTRouteHelper->JTRoute($link); ?>
								<?php
									$modalConfig = array('width' => '90%', 'height' => '300px', 'modalWidth' => 60, 'bodyHeight' => 70);
									$modalConfig['url'] = $link;
									$modalConfig['title'] = Text::_('PREVIEW_DES');
									echo HTMLHelper::_('bootstrap.renderModal', 'ticketPreview' . $item->id, $modalConfig);
								?>
								<a data-bs-target="#ticketPreview<?php echo $item->id;?>" data-bs-toggle="modal" class="af-relative af-d-block" href="javascript:;">
									<span class="editlinktip hasTip" title="<?php echo Text::_('PREVIEW_DES');?>" >
										<?php echo Text::_('PREVIEW');?>
									</span>
								</a>
							<?php
								else:
									echo '-';
								endif; ?>
							</td>

							<input type="hidden" id="eid_<?php echo $i ?>" name="eid" value="<?php echo $item->event_id; ?>" />
						</tr>
						<?php $j++;
					endforeach;
					?>
				</tbody>
			</table>

			<div class="row">
				<div class="pager col-xs-12">
					<?php    $class_pagination = 'pagination';?>
					<div class="<?php	echo $class_pagination;?> com_jticketing_align_center justify-content-end">
					<div class="<?php	echo $class_pagination;?> com_jticketing_align_center justify-content-end">
						<?php echo $this->pagination->getPagesLinks(); ?>
					</div>
				</div>
				<!-- col-lg-12 col-md-12 col-sm-12 col-xs-12-->
			</div>
		</div>
		</div><!--j-main-container ENDS-->
		<?php
		}?>
		<input type="hidden" name="task" id="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
		<input type="hidden" name="controller" id="controller" value="mytickets" />
		<input type="hidden" name="Itemid" value="<?php	echo $this->Itemid;?>" />
		<?php echo HTMLHelper::_('form.token'); ?>
	</form>
</div>
