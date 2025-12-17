<?php
/**
 * @package     JTicketing
 * @subpackage  com_jticketing
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Registry\Registry;

/** @var $this JticketingVieworders */

HTMLHelper::_('jquery.token');

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));

$mainframe  = Factory::getApplication();
$input      = $mainframe->input;
$class      =  'class="jt_selectbox" size="1" onchange="document.adminForm.submit();"';
$newClass       =  $class . 'name="search_paymentStatus"';

Factory::getDocument()->addScriptDeclaration('
	jtSite.orders.initOrdersJs();
');
?>
<div id="jtwrap">
	<?php
	if ($this->jticketingparams->get('show_page_heading', 1)):
		?>
		<div class="floattext container-fluid">
			<h1 class="componentheading"><?php echo $this->PageTitle;?></h1>
		</div>
	<?php
	endif;
	if (empty($this->state->get('search_event')))
	{

		$this->searchEvent = $input->get('event', '', 'INT');

	}


	if (empty($this->items))
	{ ?>
		<div class="<?php echo JTICKETING_WRAPPER_CLASS;?> container-fluid">
			<form action="" method="post" name="adminForm"  id="adminForm">
				<div id="all" class="row">
					<div class = "col-xs-12">
						<div class="af-mb-10">
							<span class="af-mr-5 af-mb-5 af-d-inline-block">
							<?php
							// If no events found dont show filter
							if (count($this->eventList))
							{
								echo HTMLHelper::_('select.genericlist', $this->status_event, "search_event", $class, "value", "text", $this->state->get('search_event'));?>
								</span>
								<span class="af-mr-5 af-mb-5 af-d-inline-block"> <?php
								echo HTMLHelper::_('select.genericlist', $this->searchPaymentStatuses, "search_paymentStatus", $newClass, "value", "text", strtoupper($this->state->get('search_paymentStatus')));
								?>
								</span>
								<span class="af-mr-5 af-mb-5 af-d-inline-block">
									<button type="button" class="btn hasTooltip js-stools-btn-clear" onclick="document.getElementById('search_event').value = '0'; document.getElementById('search_paymentStatus').value = '0'; document.adminForm.submit();">
										<?php echo Text::_('COM_JTICKETING_SEARCH_FILTER_CLEAR');?>
									</button>
								</span>
								<?php
							}?>
						</div>
						<div class="col-xs-12 alert alert-info jtleft"><?php echo Text::_('NODATA');?></div>
						<input type="hidden" name="option" value="com_jticketing" />
						<input type="hidden" name="task" value="" />
						<input type="hidden" name="boxchecked" value="0" />
						<input type="hidden" name="defaltevent" value="<?php echo $this->state->get('search_event');?>" />
						<input type="hidden" name="defaltpaymentStatus" value="<?php echo $this->state->get('search_paymentStatus', '');?>" />
						<input type="hidden" name="controller" value="orders" />
						<input type="hidden" name="view" value="orders" />
					</div>
				</div>
			</form>
		</div>
		<?php
			echo "</div>"; // If return need to close jtwrap div
			return;
	}
?>
<div class="<?php echo JTICKETING_WRAPPER_CLASS;?> container-fluid">
	<form action="" method="post" name="adminForm" id="adminForm">
		<div id="all" class="row">
			<div class="col-xs-12 col-sm-8 af-mb-10">
				<div class="af-d-flex">
					<span class="af-mr-5">
						<?php echo HTMLHelper::_('select.genericlist', $this->status_event, "search_event", $class, "value", "text", $this->state->get('search_event')); ?>
					</span>
					<span class="af-mr-5">
						<?php echo HTMLHelper::_('select.genericlist', $this->searchPaymentStatuses, "search_paymentStatus", $newClass, "value", "text", strtoupper($this->state->get('search_paymentStatus') ? $this->state->get('search_paymentStatus') : '')); ?>

					</span>
				<button type="button" class="btn btn-primary hasTooltip js-stools-btn-clear" onclick="document.getElementById('search_event').value = '0'; document.getElementById('search_paymentStatus').value = '0'; document.adminForm.submit();">
					<?php echo Text::_('COM_JTICKETING_SEARCH_FILTER_CLEAR');?>
				</button>
				</div>
			</div>
			<div class="col-xs-12 col-sm-4 af-mb-10">
				<div class="float-end">
					<label for="limit" class="element-invisible"><?php echo Text::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC');?></label>
					<?php echo $this->pagination->getLimitBox();?>
				</div>
			</div>
		</div>
		<div class="clearfix"></div>
		<div id='no-more-tables' class = "order">
			<table class="table table-striped table-bordered table-hover border mt-4">
			<thead class="text-break table-primary text-light">
				<tr>
					<th align="center">
						<?php echo HTMLHelper::_('grid.sort', 'ORDER_ID', 'id', $listDirn, $listOrder); ?>
					</th>
					<th align="center"><?php echo Text::_('EVENT_NAME');?></th>
					<?php
					if (in_array('COM_JTICKETING_COLUMN_EVENT_START_DATE', $this->ordersListingFields))
					{
						?>
						<th align="center">
							<?php echo  Text::_( 'COM_JTICKETING_COLUMN_EVENT_START_DATE' ); ?>
						</th>
						<?php
					}
					?>
					<?php
					if (in_array('PAY_METHOD', $this->ordersListingFields))
					{
						?>
						<th align="center">
							<?php echo HTMLHelper::_('grid.sort','PAY_METHOD','processor', $listDirn, $listOrder); ?>
						</th>
						<?php
					}
					?>
					<th align="center"><?php echo Text::_('NUMBEROFTICKETS_SOLD');?></th>
					<!-- <th align="center"><?php //echo Text::_('ORIGINAL_AMOUNT');?></th> -->
					<?php
					if (in_array('COM_JTICKETING_FEE', $this->ordersListingFields))
					{
						?>
							<th align="center" class="text-nowrap"><?php echo Text::_('COM_JTICKETING_FEE');?></th>
						<?php
					}
					?>
					<!-- <th align="center"><?php //echo Text::_('DISCOUNT_AMOUNT');?></th> -->
					<?php
						if ($this->jticketingparams->get('allow_taxation'))
						{
						?>
							<th align="center"><?php echo Text::_('TAX_AMOUNT');?></th>
				<?php   } ?>
					<th align="center"><?php echo Text::_('PAID_AMOUNT');?></th>
					<?php
					if (in_array('COUPON_CODE_DIS', $this->ordersListingFields))
					{
						?>
						<th align="center"><?php echo Text::_('COUPON_CODE_DIS');?></th>
						<?php
					}
					?>
					<th align="center"><?php echo HTMLHelper::_('grid.sort', 'PAYMENT_STATUS', 'status', $listDirn, $listOrder);?>
				</tr>
			</thead>
			<?php
				foreach ($this->items as $order)
				{
					$order              = JT::order($order->id);
					$eventDetails       = JT::event()->loadByIntegration($order->event_details_id);
					$passOrderId        = ($order->order_id) ? $order->order_id : $order->id;
					$orderUrl           = 'index.php?option=com_jticketing&view=orders&layout=order';
					$linkForOrders      = $this->jtRouteHelper->JTRoute($orderUrl . '&orderid=' . $passOrderId . '&tmpl=component', false);
					?>
					<tr class="">
						<td  class = "dis_modal" align="center" data-title="<?php echo Text::_('ORDER_ID');?>">
							<?php
							$modalConfig = array('width' => '100%', 'height' => '300px', 'modalWidth' => 80, 'bodyHeight' => 70);
							$modalConfig['url'] = $linkForOrders;
							$modalConfig['title'] = Text::_('ORDER_ID');
							echo HTMLHelper::_('bootstrap.renderModal', 'jtOrder' . $passOrderId, $modalConfig);
							?>
							<a data-bs-target="#jtOrder<?php echo $passOrderId;?>" data-bs-toggle="modal"
								class="af-relative af-d-block" href="javascript:;"><?php echo $passOrderId; ?>
							</a>
						</td>
						<td align="center" data-title="<?php echo Text::_('EVENT_NAME');?>">
							<?php
							if ($this->jticketingparams->get('enable_eventstartdateinname') && (property_exists($eventDetails, 'startdate')))
							{
								$startDate   = $this->utilities->getFormatedDate($eventDetails->getStartDate());
								echo $eventDetails->getTitle() . '(' . $startDate . ')';
							}
							else
							{
								echo  $eventDetails->getTitle();
							}
							?>
						</td>
						<?php
						if (in_array('COM_JTICKETING_COLUMN_EVENT_START_DATE', $this->ordersListingFields))
						{
							?>
							<td align="center" data-title="<?php echo Text::_('COM_JTICKETING_COLUMN_EVENT_START_DATE');?>">
								<?php 
									$startDate   = $this->utilities->getFormatedDate($eventDetails->getStartDate());
									echo $startDate ? $startDate : '';
								?>
							</td>
							<?php
						}
						if (in_array('PAY_METHOD', $this->ordersListingFields))
						{
						?>
							<td align="center" data-title="<?php echo Text::_('PAY_METHOD');?>">
								<?php
									if (!empty($order->processor) && $order->processor != 'Free_ticket')
									{
										$plugin       = PluginHelper::getPlugin('payment', $order->processor);
										$pluginParams = new Registry;
										$pluginParams->loadString($plugin->params);
										$param = $pluginParams->get('plugin_name', $order->processor);
										echo $param;
									}
									else
									{
										echo empty($order->processor) ? "-" : $order->processor;
									}
									?>
							</td>
							<?php
						}
						?>
						<td align="center" data-title="<?php echo Text::_('NUMBEROFTICKETS_SOLD');?>">
							<?php echo $order->getTicketsCount();?>
						</td>
						<!-- <td align="center" data-title="<?php //echo Text::_('ORIGINAL_AMOUNT');?>">
							<?php //echo $this->utilities->getFormattedPrice($order->getOriginalAmount()); ?>
						</td> -->
						<?php
						if (in_array('COM_JTICKETING_FEE', $this->ordersListingFields))
						{
							?>
							<td align="center" class="text-nowrap" data-title="<?php echo Text::_('COM_JTICKETING_FEE');?>">
								<?php echo $order->getFee();?>
							</td>
							<?php
						}
						?>
						<!-- <td align="center" data-title="<?php //echo Text::_('DISCOUNT_AMOUNT');?>">
							<?php //echo $order->getCouponDiscount(); ?>
						</td> -->
						<?php
						if ($this->jticketingparams->get('allow_taxation'))
						{
							?>
							<td align="center" class="text-nowrap" data-title="<?php echo Text::_('TAX_AMOUNT');?>">
								<?php echo $this->utilities->getFormattedPrice($order->getOrderTax()); ?>
							</td>
				<?php   } ?>
						<td align="center" class="text-nowrap" data-title="<?php echo Text::_('PAID_AMOUNT');?>">
							<?php echo $order->getAmount(); ?>
						</td>

						<?php
						if (in_array('COUPON_CODE_DIS', $this->ordersListingFields))
						{
							?>
							<td align="center" class="text-nowrap" data-title="<?php echo Text::_('COUPON_CODE_DIS');?>">
								<?php echo !empty($order->getCouponCode()) ? $order->getCouponCode() . ' ' : '-'; ?>
							</td>
							<?php
						}
						?>
						<td align="center" class="text-nowrap" data-title="<?php echo Text::_('PAYMENT_STATUS');?>">
							<?php echo !empty($this->payment_statuses[$order->getStatus()]) ? $this->payment_statuses[$order->getStatus()] . ' ' : '-'; ?>
						</td>
					</tr>
					<?php
				}
				?>
			</table>
		</div>
		<?php echo HTMLHelper::_('form.token'); ?>

		<div class="pagination justify-content-end">
			<?php echo $this->pagination->getListFooter();?>
		</div>
		<input type="hidden" name="filter_order" value="<?php echo $listOrder;?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn;?>" />
	</form>
</div>
</div>
