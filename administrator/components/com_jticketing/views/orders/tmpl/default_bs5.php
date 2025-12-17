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
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Factory;


HTMLHelper::_('jquery.token');

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
Factory::getDocument()->addScriptDeclaration('
	jtAdmin.orders.initOrdersJs();
');
?>

<form action="" method="post" name="adminForm" id="adminForm">
	<?php
	if (!empty( $this->sidebar)):
		?>
		<div id="sidebar" >
			<div id="j-sidebar-container" class="span2">
				<?php echo $this->sidebar; ?>
			</div>
		</div>
		<div id="j-main-container" class="span10">
		<?php
	else :
		?>
			<div id="j-main-container">
		<?php
	endif;

		// Search tools bar
		echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this));

		if (empty($this->items))
		{ ?>
			<div class="alert alert-info "><?php echo Text::_('NODATA');?></div>
		<?php
			return;
		} ?>

		<div class="table-responsive">
			<table class="table table-striped table-hover" id="ordersList">
				<tr>
					<th width="1%" class="nowrap center">
						<input type="checkbox" name="checkall-toggle" value="" title="<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
					</th>
					<th width="5%" align="center">
						<?php echo HTMLHelper::_('searchtools.sort', 'ORDER_ID', 'id', $listDirn, $listOrder);?>
					</th>
					<th width="5%" align="center">
						<?php  echo Text::_( 'EVENT_NAME' ); ?>
					</th>
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
					<th width="5%" align="center">
						<?php  echo Text::_( 'COM_JTICKETING_BUYER_NAME' ); ?>
					</th>
					<th width="5%" align="center">
						<?php echo Text::_( 'TRANSACTION_ID' );?>
					</th>
					<?php
					if (in_array('PAY_METHOD', $this->ordersListingFields))
					{
						?>
						<th align="center">
							<?php echo HTMLHelper::_( 'searchtools.sort','PAY_METHOD','processor', $listDirn, $listOrder); ?>
						</th>
						<?php
					}
					?>
					<th align="center" class="removeWhiteSpace">
						<?php echo Text::_( 'NUMBEROFTICKETS_SOLD' );?>
					</th>
					<?php
					if ($this->jticketingparams->get('allow_taxation'))
					{
						?>
						<th align="center">
							<?php echo  Text::_( 'TAX_AMOUNT' ); ?>
						</th>
						<?php
					}
					?>
					<th align="center">
						<?php echo  Text::_( 'PAID_AMOUNT' ); ?>
					</th>
					<?php
					if (in_array('COM_JTICKETING_FEE', $this->ordersListingFields))
					{
						?>
						<th align="center">
							<?php echo  Text::_( 'COM_JTICKETING_FEE' ); ?>
						</th>
						<?php
					}
					if (in_array('COUPON_CODE_DIS', $this->ordersListingFields))
					{
						?>
						<th align="center">
							<?php echo  Text::_( 'COUPON_CODE_DIS' ); ?>
						</th>
						<?php
					}
					if (in_array('COM_JTICKETING_COLUMN_COUPON_CODE_DISCOUNT', $this->ordersListingFields))
					{
						?>
						<th align="center">
							<?php echo  Text::_( 'COM_JTICKETING_COLUMN_COUPON_CODE_DISCOUNT' ); ?>
						</th>
						<?php
					}
					?>
					<th  align="center">
						<?php echo HTMLHelper::_( 'searchtools.sort','PAYMENT_STATUS','status', $listDirn, $listOrder); ?>
					</th>
				</tr>
				<?php
				$i = $eventOriginalAmt = $eventPaidAmt = $eventFee = $eventDiscount = $eventTax = $eventTicketCount = 0;
				$comParams    = JT::config();
				$utilities    = JT::utilities();
				$pageSpan = 11;

				foreach ($this->items as $order)
				{
					$order 				= JT::order($order->id);
					$eventDetails		= JT::event()->loadByIntegration($order->event_details_id);
					$userInfo 			= $order->getbillingdata();
					$eventOriginalAmt 	+= $order->getOriginalAmount();
					$eventPaidAmt		+= $order->getAmount(false);
					$eventFee			+= $order->getFee(false);
					$eventDiscount		+= $order->getCouponDiscount(false);
					$eventTax			+= $order->getOrderTax();
					$eventTicketCount	+= $order->getTicketsCount();

					$passOrderId = ($order->order_id) ? $order->order_id : $order->id;

					$linkForInvoice = $this->linkForInvoice . '&event=' . $order->event_details_id .'&orderid='.$passOrderId;
					?>
					<tr class="">
						<td class="center">
							<?php echo HTMLHelper::_('grid.id', $i, $order->id); ?>
						</td>
						<?php
						$modalConfig = array('width' => '800px', 'height' => '300px', 'modalWidth' => 80, 'bodyHeight' => 70);
						$modalConfig['url'] = $linkForInvoice;
						$modalConfig['title'] = Text::sprintf('ORDER_DETAILS', $eventDetails->getTitle());
						echo HTMLHelper::_('bootstrap.renderModal', 'jtOrder' . $order->id, $modalConfig);
						?>
						<td  align="center">
							<a data-bs-target="#jtOrder<?php echo $order->id;?>" data-bs-toggle="modal"><?php if($order->order_id) echo $order->order_id; else echo $order->id;?></a>
						</td>
						<td>
						<?php
							 $eventTitle  = htmlspecialchars($eventDetails->getTitle());

							if($comParams->get('enable_eventstartdateinname'))
							{
								$startDate   = $utilities->getFormatedDate($eventDetails->getStartDate());
								$eventTitle  = $eventTitle . '(' . $startDate . ')';
							}

							echo $eventTitle;
						?>
						</td>
						<?php
						if (in_array('COM_JTICKETING_COLUMN_EVENT_START_DATE', $this->ordersListingFields))
						{
							?>
							<td align="center">
								<?php 
									$startDate   = $utilities->getFormatedDate($eventDetails->getStartDate());
									echo $startDate ? $startDate : '';
								?>
							</td>
							<?php
						}
						?>
						<td>
							<?php echo (!empty($order->id) && $userInfo) ? $this->escape($userInfo->firstname) . ' ' . $this->escape($userInfo->lastname) : '-'; ?>
						</td>
						<td>
							<?php echo (!empty($order->transaction_id)) ? $order->transaction_id : '-'; ?>
						</td>
						<?php
						if (in_array('PAY_METHOD', $this->ordersListingFields))
						{
							?>
							<td align="center">
								<?php echo (!empty($order->processor)) ? $order->processor : '-'; ?>
							</td>
							<?php
						}
						?>
						<td align="center">
							<?php echo $order->getTicketsCount(); ?>
						</td>
						<?php
							if ($this->jticketingparams->get('allow_taxation'))
							{
								$pageSpan = $pageSpan + 1;
								?>
								<td align="center">
									<?php  echo $this->utilities->getFormattedPrice($order->getOrderTax()); ?>
								</td>
								<?php
							}
						?>
						<td align="center" class="text-nowrap">
							<?php echo $order->getAmount();?>
						</td>
						<?php
						if (in_array('COM_JTICKETING_FEE', $this->ordersListingFields))
						{
							?>
							<td align="center" class="text-nowrap">
								<?php echo $order->getFee();?>
							</td>
							<?php
						}
						if (in_array('COUPON_CODE_DIS', $this->ordersListingFields))
						{
							?>
							<td align="center">
								<?php echo !empty($order->getCouponCode()) ? $order->getCouponCode() . ' ' : '-'; ?>
							</td>
							<?php
						}
						if (in_array('COM_JTICKETING_COLUMN_COUPON_CODE_DISCOUNT', $this->ordersListingFields))
						{
							?>
							<td align="center">
								<?php echo !empty($order->getCouponCode()) ? $order->getCouponDiscount() . ' ' : '-'; ?>
							</td>
							<?php
						}
						?>
						<td align="center">
						<?php
							// Get the valid order status list options
						$validStatus = $this->jticketingOrdersModel->getValidOrderStatus($order->getStatus(), $this->paymentStatuses);

							if (($order->getStatus()) && (!empty($order->processor)))
							{
								if ($order->getStatus()  === COM_JTICKETING_CONSTANT_ORDER_STATUS_INCOMPLETE)
								{
									echo Text::_('JT_PSTATUS_INITIATED');
								}
								else
								{
									$processor = "'" . $order->processor . "'";
									echo HTMLHelper::_('select.genericlist', $validStatus, "pstatus" . $i, 'onChange="jtAdmin.orders.selectStatusOrder(' . $order->id . ',' . $processor . ',this);"  class="form-sm-select form-select-sm payment-order-status" data-oldvalue="'. $order->getStatus() .'"', "value", " text", $order->getStatus());
								}
							}
							else
							{
								echo ($order->getStatus()  === COM_JTICKETING_CONSTANT_ORDER_STATUS_INCOMPLETE)
									? Text::_('JT_PSTATUS_INITIATED') : $validStatus[$order->getStatus()];
							} ?>
						</td>
					</tr>
					<?php $i++;
				}
				?>
				<tr>
					<td colspan="6" align="right">
						<div class="jtright">
							<b><?php echo Text::_('TOTAL');?></b>
						</div>
					</td>
					<td align="center">
						<b><?php echo $eventTicketCount;?></b>
					</td>
					<?php
					if ($this->jticketingparams->get('allow_taxation'))
					{
						?>
						<td align="center" class="text-nowrap">
							<b><?php echo $this->utilities->getFormattedPrice($eventTax);?></b>
						</td>
						<?php
					}
					?>
					<td align="center" class="text-nowrap">
						<b><?php echo $this->utilities->getFormattedPrice($eventPaidAmt); ?></b>
					</td>
					<td align="center" class="text-nowrap">
						<b><?php echo $this->utilities->getFormattedPrice($eventFee); ?></b>
					</td>
						<td align="center" colspan="3">
					</td>
				</tr>
				<tfoot>
					<tr>
						<td colspan="<?php echo $pageSpan;?>" align="center">
							<?php echo $this->pagination->getListFooter();  ?>
						</td>
					</tr>
				</tfoot>
			</table>
			<input type="hidden" name="task" value="" />
			<input type="hidden" name="boxchecked" value="0" />
			<input type="hidden" name="order_id" id='order_id' value="" />
			<input type="hidden" name="payment_status" id='payment_status'  value="" />
			<input type="hidden" name="processor" id='processor'  value="" />
			<input type="hidden" name="defaltevent" value="<?php echo !empty($this->lists['search_event']) ? $this->lists['search_event'] : '';?>" />
			<input type="hidden" name="filter_order" value="<?php echo !empty($this->lists['order']) ? $this->lists['order'] : ''; ?>" />
			<input type="hidden" name="filter_order_Dir" value="<?php echo !empty($this->lists['order_Dir']) ? $this->lists['order_Dir'] : ''; ?>" />
			<?php echo HTMLHelper::_('form.token'); ?>
			</div>
		</div><!--row-fluid-->
	</div>
</form>
