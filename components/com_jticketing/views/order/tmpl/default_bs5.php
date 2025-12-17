<?php
/**
 * @package     JTicketing
 * @subpackage  com_jticketing
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Registry\Registry;

HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('bootstrap.renderModal', 'a.modal');
// Joomla 6: formbehavior.chosen removed - using native select
HTMLHelper::_('jquery.token');

/** @var $this JticketingViewOrder */
?>
<div class="js-jt-ticket-listing">
	<div class="col-xs-12 af-mb-15">
		<a href="<?php echo $this->event->getUrl();?>" class="text-muted af-font-bold">
			<i class="fa fa-angle-left af-pr-5 af-font-bold" aria-hidden="true"></i>
			<span><?php echo Text::_('BACK_TO_EVENT');?></span>
		</a>
	</div>
	<?php
	// Event Detail on every page
	echo $this->loadTemplate('event_info_' . JTICKETING_LOAD_BOOTSTRAP_VERSION);
	?>
	<div class="<?php echo JTICKETING_WRAPPER_CLASS;?>" id="jtwrap">
		<div class="com_jt_book com_jticketing_button w-100 booking-btn">
			<form action="" method="post" name="ticketform" id="ticketform" class="book-tickets-form">
				<div>
					<h5 class="af-font-600 af-mt-30 af-mb-15 md-mx-0"><?php echo Text::_('COM_JTICKETING_SELECT_SEATS');?></h5>
					<?php
						if (($this->integration == 'com_jticketing' && $this->event->online_events == 1) || ! empty($this->jtParams->get('single_ticket_per_user')) ||
							$this->jtParams->get('max_noticket_peruserperpurchase') == 1)
						{
							echo $this->loadTemplate('single_ticket_' . JTICKETING_LOAD_BOOTSTRAP_VERSION);
						}
						else
						{
							echo $this->loadTemplate('ticket_' . JTICKETING_LOAD_BOOTSTRAP_VERSION);
						}
						?>
				</div>
				<div class="row af-mb-30 af-mt-30">
				<div class="af-bg-white af-py-10 af-px-15 overflow-auto">
					<h5 class="af-font-600 af-mt-30 af-mb-15 md-mx-0">
						<?php echo Text::_('COM_JTICKETING_BILL_DETAIL');?>
					</h5>
					<div class="row af-py-5 grey-text">
						<?php
						if (!$this->jtParams->get("admin_fee_mode") && $this->jtParams->get('admin_fee_level') == 'order')
						{?>
							<div class="col-7 af-my-5 af-font-600">
								<?php echo Text::_('COM_JTICKETING_SEATS') . " : "; ?>
							</div>
							<div class="col-5 float-end grey-text">
								<span id="totalTickets" class="float-end"><?php echo $this->order->get('ticketscount'); ?></span>
							</div>
							<div class="col-7 af-py-5 af-font-600 grey-text">
								<?php echo Text::_('COM_JTICKETING_ORDER_TOTAL_FEE');?>
							</div>
							<div class="col-5 float-end grey-text">
								<span id="total_fee_amt" class="float-end">
								<?php echo $this->order->getFee();?>
								</span>
							</div>
						<?php
						}?>
						<div class="col-7 af-py-5 af-font-600 grey-text">
							<?php echo Text::_('COM_JTICKETING_ORDER_TOTAL_TICKET');?>
						</div>
						<div class="col-5 float-end grey-text">
							<span id="total_order_amt" class="float-end">
								<?php echo $this->utilities->getFormattedPrice($this->order->order_amount);?>
							</span>
						</div>
						<div class="col-7 af-py-5 af-font-600 grey-text">
							<?php echo Text::_('COM_JTICKETING_ORDER_GRANT_TOTAL');?>
						</div>
						<div class="col-5 float-end grey-text">
							<span id="total_order_amt" class="float-end">
								<?php echo $this->utilities->getFormattedPrice($this->order->getNetAmount());?>
							</span>
						</div>
					</div>
					<div class="row justify-content-end">
							<?php
								if ($this->jtParams->get('enable_coupon')
									&& ($this->ticketPriceNotFree > 0
									&& ($this->order->getAmount(false) || $this->order->get('coupon_discount') > 0)
									))
								{
								?>
								<div id="coupon_troption" class="af-bg-white col-sm-10 col-md-4">
									<div class="input-group af-p-10">
									<?php if (empty($this->order->get('coupon_code')))
											{?>
										<input id="coupon_code" class="focused af-px-5 form-control w-150 float-end af-mr-5" placeholder="<?php echo Text::_('CUPCODE');?>" name="coupon_code" value="<?php echo $this->order->get('coupon_code', '');?>" >
										<?php } else {?>
										<input id="coupon_code" class="focused af-px-5 form-control w-150 float-end af-mr-5" name="coupon_code" value="<?php echo $this->order->get('coupon_code', '');?>" readonly>
										<?php }?>
										<span class="input-group-btn">
										<?php
										if (empty($this->order->get('coupon_code')))
											{?>
										<input type="button" name="coup_button" id="coup_button" data-order-id="<?php echo $this->order->get('id');?>" class="applyCoupon btn btn-default" value="<?php echo Text::_('APPLY');?>">
										<?php    } else{?>
										<input type="button" name="coup_button" id="coup_button" data-order-id="<?php echo $this->order->get('id');?>" class="removeCoupon btn btn-default" value="<?php echo Text::_('COM_JTICKETING_COUPON_REMOVE');?>">
										<?php    }?>
										</span>
									</div>
								</div>
								<?php if (!empty($this->order->get('coupon_code', '')))
								{?>
								<div class="container">
									<div id="dis_copon" class="af-mb-10 row">
										<div class="col-7 af-font-600">
											<?php echo Text::_('COP_DISCOUNT');?>
										</div>
										<div class="col-5 float-end">
											<span id="dis_copon_amt" class="float-end">
											<?php
												if (!empty($this->order->get('coupon_discount')))
												{
														echo $this->utilities->getFormattedPrice($this->order->get('coupon_discount'));
												} ?>
											</span>
										</div>
									</div>
								</div>
								<?php
								}
								}
								?>
						</div>
						<!--Coupon description end -->
						<?php
							if ($this->jtParams->get('allow_taxation') && $this->order->get('order_tax'))
							{
								$taxDetails = new Registry($this->order->get('order_tax_details'));

								foreach ($taxDetails as $taxDetail)
								{
									foreach ($taxDetail->breakup as $breakup)
									{
									?>
						<div class="af-mb-10 row">
							<div class="col-7 af-font-600">
								<?php echo Text::sprintf('TAX_AMOOUNT', $breakup->percentage) . "%"; ?>
							</div>
							<div class="col-5 float-end">
								<span id="tax_to_pay" class="float-end">
								<?php echo $this->utilities->getFormattedPrice($breakup->value); ?>
								</span>
							</div>
						</div>
						<?php
							}
							}?>
						<div class="af-mb-10 row">
							<div class="col-7 af-font-600">
							   <?php echo Text::_('TOTALPRICE_PAY_AFTER_TAX'); ?>
							</div>
							<div class="col-5 float-end">
								<span id="net_amt_after_tax" class="float-end">
								<?php echo $this->order->getAmount(); ?>
								</span>
							</div>
						</div>
						<?php
							}
							?>
						<div class="row border-top af-py-15 d-none d-sm-flex">
							<div class="col-7">
								<strong>
								<?php echo Text::_('COM_JTICKETING_TOTAL_PAY_AFTER_TAX');?>
								</strong>
							</div>
							<div class="col-5 float-end">
								<span id="total_amt" class="float-end">
								<?php echo $this->order->getAmount();?>
								</span>
							</div>
						</div>
					</div>
					<div class="clearfix">&nbsp;</div>
					<div class="row af-bg-white md-mx-0 af-ml-0 af-mr-0">
						<div class="col-12">
							<div class="af-font-600 float-end af-mb-10">
								<?php echo Text::_('COM_JTICKETING_TOTAL_PAY_AFTER_TAX') . ":&nbsp"; ?>
								<span id="total_cost" class="af-pr-5">
									<?php echo $this->order->getAmount(); ?>
								</span>
							</div>
						</div>
						<div class="col-12">
							<button id="proceedCheckout" type="button" data-order-id="<?php echo $this->order->get('id'); ?>" class="proceedCheckout btn btn-primary r-btn float-end">
								<?php echo Text::_('COM_JTICKETING_ORDER_PROCEED_CHECKOUT'); ?>
							</button>
						</div>
					</div>
				</div>
				<?php echo HTMLHelper::_('form.token'); ?>
			</form>
		</div>
	</div>
</div>

