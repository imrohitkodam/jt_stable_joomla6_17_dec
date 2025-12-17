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
	if ($this->jticketingparams->get('show_page_heading', 1)) :
	?>
		<div class="floattext container-fluid">
			<h1 class="componentheading"><?php echo $this->PageTitle; ?></h1>
		</div>
	<?php
	endif;

	if (empty($this->state->get('search_event'))) {
		$this->searchEvent = $input->get('event', '', 'INT');
	}

	if (empty($this->items) || !count($this->eventList)) { ?>
		<div class="<?php echo JTICKETING_WRAPPER_CLASS; ?> container-fluid">
			<form action="" method="post" name="adminForm" id="adminForm">
				<div id="all" class="row">
					<div class="col-xs-12">
						<?php
						if (count($this->eventList)) {
						?>
							<div class="af-d-flex af-mb-10">
								<span class="af-mr-5">
									<?php
									// If no events found dont show filter
									echo HTMLHelper::_('select.genericlist', $this->status_event, "search_event", $class, "value", "text", $this->state->get('search_event')); ?>
								</span>
								<span>
									<?php
									echo HTMLHelper::_('select.genericlist', $this->searchPaymentStatuses, "search_paymentStatus", $newClass, "value", "text", $this->state->get('search_paymentStatus') ? strtoupper($this->state->get('search_paymentStatus')) : ''); ?>
								</span>
								<br>
								<span>
									<button type="button" class="btn hasTooltip js-stools-btn-clear" onclick="document.getElementById('search_event').value = '0'; document.getElementById('search_paymentStatus').value = '0'; document.adminForm.submit();">
										<?php echo Text::_('COM_JTICKETING_SEARCH_FILTER_CLEAR'); ?>
									</button>
								</span>
							</div>
						<?php
						}
						?>
						<div class="col-xs-12 alert alert-info jtleft"><?php echo Text::_('NODATA'); ?></div>
						<input type="hidden" name="option" value="com_jticketing" />
						<input type="hidden" name="task" value="" />
						<input type="hidden" name="boxchecked" value="0" />
						<input type="hidden" name="defaltevent" value="<?php echo $this->state->get('search_event'); ?>" />
						<input type="hidden" name="defaltpaymentStatus" value="<?php echo $this->state->get('search_paymentStatus', ''); ?>" />
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
	<div class="<?php echo JTICKETING_WRAPPER_CLASS; ?> container-fluid">
		<?php if ($this->checkGatewayDetails && ($this->jticketingparams->get('handle_transactions') == 1 || in_array('adaptive_paypal', $this->jticketingparams->get('gateways')))) {
		?>
			<div class="alert alert-warning">
				<?php
				$vendorId = $this->vendorCheck;
				$link = 'index.php?option=com_tjvendors&view=vendor&layout=profile&client=com_jticketing';
				echo Text::_('COM_JTICKETING_PAYMENT_DETAILS_ERROR_MSG1');
				$itemid = $this->utilities->getItemId($link); ?>
				<a href="<?php echo Route::_($link . '&itemId=' . $itemid . '&vendor_id=' . $vendorId, false); ?>">
					<?php echo Text::_('COM_JTICKETING_VENDOR_FORM_LINK'); ?></a>
				<?php echo " <br> " . Text::_('COM_JTICKETING_PAYMENT_DETAILS_ERROR_MSG2'); ?>
			</div>
		<?php
		} ?>
		<form action="" method="post" name="adminForm" id="adminForm">
			<div id="all" class="row">
				<div class="col-xs-12 col-sm-8 af-mb-10">
					<div class="af-d-flex">
						<span class="af-mr-5">
							<?php echo HTMLHelper::_('select.genericlist', $this->status_event, "search_event", $class, "value", "text", $this->state->get('search_event')); ?>
						</span>
						<span>
							<?php echo HTMLHelper::_('select.genericlist', $this->searchPaymentStatuses, "search_paymentStatus", $newClass, "value", "text", $this->state->get('search_paymentStatus') ? strtoupper($this->state->get('search_paymentStatus')) : ''); ?>
						</span>
						<button type="button" class="btn hasTooltip js-stools-btn-clear bg-primary bg-gradient text-light ms-1" onclick="document.getElementById('search_event').value = '0'; document.getElementById('search_paymentStatus').value = '0'; document.adminForm.submit();">
							<?php echo Text::_('COM_JTICKETING_SEARCH_FILTER_CLEAR'); ?>
						</button>
					</div>
				</div>
				<div class="btn-group col-xs-12 col-sm-4 af-mb-10">
					<div class="pull-right">
						<label for="limit" class="element-invisible"><?php echo Text::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC'); ?></label>
						<?php echo $this->pagination->getLimitBox(); ?>
					</div>
				</div>
			</div>
			<div class="clearfix"></div>
			<div id='no-more-tables' class="order mt-4">
				<table class="table table-striped table-bordered table-hover border">
					<thead class="text-break table-primary text-light"">
						<tr>
							<th align="center">
								<?php echo HTMLHelper::_('grid.sort', 'ORDER_ID', 'id', $listDirn, $listOrder); ?>
							</th>
							<th align="center"><?php echo Text::_('EVENT_NAME'); ?></th>
							<th align="center">
								<?php echo HTMLHelper::_('grid.sort', 'PAY_METHOD', 'processor', $listDirn, $listOrder); ?>
							</th>
							<th align="center"><?php echo Text::_('NUMBEROFTICKETS_SOLD'); ?></th>
							<th align="center"><?php echo Text::_('ORIGINAL_AMOUNT'); ?></th>
							<th align="center" class="removeWhiteSpace"><?php echo Text::_('COM_JTICKETING_FEE'); ?></th>
							<th align="center"><?php echo Text::_('DISCOUNT_AMOUNT'); ?></th>
							<?php
							if ($this->jticketingparams->get('allow_taxation')) {
							?>
								<th align="center"><?php echo Text::_('TAX_AMOUNT'); ?></th>
							<?php   } ?>
							<th align="center"><?php echo Text::_('PAID_AMOUNT'); ?></th>
							<th align="center"><?php echo Text::_('COUPON_CODE_DIS'); ?></th>
							<th align="center"><?php echo HTMLHelper::_('grid.sort', 'PAYMENT_STATUS', 'status', $listDirn, $listOrder); ?>
							</th>
						</tr>
					</thead>
					<?php
					$i = $eventOriginalAmt  = $eventPaidAmt = $eventFee = $eventDiscount =  $eventTicketCount = 0;
					$eventTax = 0.0;

					foreach ($this->items as $order) {
						$order              = JT::order($order->id);
						$eventDetails       = JT::event()->loadByIntegration($order->event_details_id);

						$eventOriginalAmt   += $order->getOriginalAmount();
						$eventPaidAmt       += $order->getAmount(false);
						$eventFee           += $order->getFee(false);
						$eventDiscount      += $order->getCouponDiscount(false);
						$eventTax           += $order->getOrderTax();
						$eventTicketCount   += $order->getTicketsCount();
						$passOrderId        = ($order->order_id) ? $order->order_id : $order->id;

						$orderUrl           = 'index.php?option=com_jticketing&view=orders&layout=order';
						$linkForOrders      = $this->jtRouteHelper->JTRoute($orderUrl . '&orderid=' . $passOrderId . '&tmpl=component', false);
					?>
						<tr class="">
							<td class="dis_modal" align="center" data-title="<?php echo Text::_('ORDER_ID'); ?>">
								<?php
								$modalConfig = array('width' => '600px', 'height' => '600px', 'modalWidth' => 80, 'bodyHeight' => 70);
								$modalConfig['url'] = $linkForOrders;
								$modalConfig['title'] = Text::_('ORDER_ID');
								echo HTMLHelper::_('bootstrap.renderModal', 'jtOrder' . $passOrderId, $modalConfig);
								?>
								<a data-target="#jtOrder<?php echo $passOrderId; ?>" data-toggle="modal" class="af-relative af-d-block"><?php echo $passOrderId; ?>
								</a>
							</td>
							<td align="center" data-title="<?php echo Text::_('EVENT_NAME'); ?>">
								<?php
								if ($this->jticketingparams->get('enable_eventstartdateinname') && (property_exists($eventDetails, 'startdate'))) {
									$startDate   = $this->utilities->getFormatedDate($eventDetails->getStartDate());
									echo $eventDetails->getTitle() . '(' . $startDate . ')';
								} else {
									echo  $eventDetails->getTitle();
								}
								?>
							</td>
							<td align="center" data-title="<?php echo Text::_('PAY_METHOD'); ?>">
								<?php
								if (!empty($order->processor) && $order->processor != 'Free_ticket') {
									$plugin       = PluginHelper::getPlugin('payment', $order->processor);
									$pluginParams = new Registry;
									$pluginParams->loadString($plugin->params);
									$param = $pluginParams->get('plugin_name', $order->processor);
									echo $param;
								} else {
									echo empty($order->processor) ? "-" : $order->processor;
								}
								?>
							</td>
							<td align="center" class="af-text-nowrap" data-title="<?php echo Text::_('NUMBEROFTICKETS_SOLD'); ?>">
								<?php echo $order->getTicketsCount(); ?>
							</td>
							<td align="center" class="af-text-nowrap" data-title="<?php echo Text::_('ORIGINAL_AMOUNT'); ?>">
								<?php echo $this->utilities->getFormattedPrice($order->getOriginalAmount()); ?>
							</td>
							<td align="center" class="af-text-nowrap" data-title="<?php echo Text::_('COM_JTICKETING_FEE'); ?>">
								<?php echo $order->getFee(); ?>
							</td>
							<td align="center" class="af-text-nowrap" data-title="<?php echo Text::_('DISCOUNT_AMOUNT'); ?>">
								<?php echo $order->getCouponDiscount(); ?>
							</td>
							<?php
							if ($this->jticketingparams->get('allow_taxation')) {
							?>
								<td align="center" class="af-text-nowrap" data-title="<?php echo Text::_('TAX_AMOUNT'); ?>">
									<?php echo $this->utilities->getFormattedPrice($order->getOrderTax()); ?>
								</td>
							<?php   } ?>
							<td align="center" class="af-text-nowrap" data-title="<?php echo Text::_('PAID_AMOUNT'); ?>">
								<?php echo $order->getAmount(); ?>
							</td>
							<td align="center" data-title="<?php echo Text::_('COUPON_CODE_DIS'); ?>">
								<?php echo !empty($order->getCouponCode()) ? $order->getCouponCode() . ' ' : '-'; ?>
							</td>
							<td align="center" data-title="<?php echo Text::_('PAYMENT_STATUS'); ?>">
								<?php
								// Get the valid order status list options
								$validStatus = $this->jticketingOrdersModel->getValidOrderStatus($order->getStatus(), $this->payment_statuses);

								if (($order->getStatus()) && (!empty($order->processor))) {
									$processor = "'" . $order->processor . "'";
									echo HTMLHelper::_('select.genericlist', $validStatus, "pstatus" . $i, 'onChange="jtAdmin.orders.selectStatusOrder(' . $order->id . ',' . $processor . ',this);" data-oldvalue="' . $order->getStatus() . '"', "value", " text", $order->getStatus());
								} else {
									echo ($order->getStatus()  === COM_JTICKETING_CONSTANT_ORDER_STATUS_INCOMPLETE) ? Text::_('JT_PSTATUS_INITIATED') : $validStatus[$order->getStatus()];
								}
								?>
							</td>
						</tr>
					<?php
						$i++;
					}
					?>
					<tr class="jticket_row_head">
						<td colspan="3" align="right" class="hidden-xs hidden-sm">
							<div class="jtright"><b><?php echo Text::_('TOTAL'); ?></b></div>
						</td>
						<td align="center" data-title="<?php echo Text::_('TOTAL_NUMBEROFTICKETS_SOLD'); ?>">
							<b><?php echo $eventTicketCount; ?></b>
						</td>
						<td align="center" class="af-text-nowrap" data-title="<?php echo Text::_('TOTAL_ORIGINAL_AMOUNT'); ?>">
							<b><?php echo $this->utilities->getFormattedPrice($eventOriginalAmt); ?></b>
						</td>
						<td align="center" class="af-text-nowrap" data-title="<?php echo Text::_('TOTAL_COM_JTICKETING_FEE'); ?>">
							<b><?php echo $this->utilities->getFormattedPrice($eventFee); ?></b>
						</td>
						<td align="center" class="af-text-nowrap" data-title="<?php echo Text::_('TOTAL_DISCOUNT_AMOUNT'); ?>">
							<b><?php echo $this->utilities->getFormattedPrice($eventDiscount); ?></b>
						</td>
						<?php
						if ($this->jticketingparams->get('allow_taxation')) { ?>
							<td align="center" class="af-text-nowrap" data-title="<?php echo Text::_('TOTAL_TAX_AMOUNT'); ?>">
								<b><?php echo $this->utilities->getFormattedPrice($eventTax); ?></b>
							</td>
						<?php   } ?>
						<td align="center" class="af-text-nowrap" data-title="<?php echo Text::_('TOTAL_PAID_AMOUNT'); ?>">
							<b><?php echo $this->utilities->getFormattedPrice($eventPaidAmt); ?></b>
						</td>
						<td align="center" class="hidden-xs hidden-sm"></td>
						<td align="center" class="hidden-xs hidden-sm"></td>
					</tr>
				</table>
			</div>
			<input type="hidden" name="option" value="com_jticketing" />
			<input type="hidden" id='order_id' name="order_id" value="" />
			<input type="hidden" id='payment_status' name="payment_status" value="" />
			<input type="hidden" id='processor' name="processor" value="" />
			<input type="hidden" name="task" value="" />
			<input type="hidden" name="boxchecked" value="0" />
			<input type="hidden" name="defaltevent" value="<?php echo $this->state->get('search_event'); ?>" />
			<input type="hidden" name="controller" value="orders" />
			<input type="hidden" name="view" value="orders" />
			<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
			<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
			<?php echo HTMLHelper::_('form.token'); ?>

			<div class="col-12 d-flex justify-content-end">
				<div class="pagination">
					<?php echo $this->pagination->getListFooter(); ?>
				</div>
			</div>
		</form>
	</div>
</div>