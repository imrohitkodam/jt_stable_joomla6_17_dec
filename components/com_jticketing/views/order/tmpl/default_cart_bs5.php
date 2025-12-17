<?php
/**
 * @package     JTicketing
 * @subpackage  com_jticketing
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/** @var $this JticketingViewOrder */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;
?>
	<div class="header-background af-br-t5 bg-gray af-pt-10 af-pb-10 border-bottom">
		<h3 class="af-d-inline-block font-16 af-pl-15 af-m-0 "><strong><?php echo Text::_('COM_JTICKETING_ORDER_YOUR_BOOKED_TICKETS');?></strong></h3>
			<i class="fa fa-chevron-circle-down float-end af-pt-5 af-mr-10 af-d-block d-sm-none" data-bs-toggle="collapse" href="#collapseExample" role="button" aria-expanded="false" aria-controls="collapseExample"></i>
	</div>
	<div class="collapse dont-collapse-sm show" id="collapseExample">
		<div class="card card-body">

		<?php if (JT::config()->get('collect_attendee_info_checkout') == 1 && $this->attendeeEdit === true)
		{
		?>
			<div class="af-mt-15 af-px-15">
				<div class="row">
					<div class="text-start">
						<a title="<?php echo Text::_("COM_JTICKETING_ATTENDEE_REDIRECTION_DESC");?>" href="<?php echo $this->attendeeRedirectLink; ?>"><?php echo Text::_("COM_JTICKETING_ATTENDEE_REDIRECTION_LABEL");?></a>
					</div>
				</div>
			</div>
	<?php } ?>
		<?php $ticketTypes  = $this->order->getItemTypes();

			foreach ($ticketTypes as $ticketType)
			{
				$item = $this->order->getItemsByType($ticketType->id);

				if ($item->count)
				{ ?>
					<div class="af-mt-15 af-pt-0 ">
						<div class="row">
							<div class="col-5 af-col-xxs-5 text-start">
								<i class="fa fa-ticket af-mr-5"></i>
								<?php echo $this->escape($ticketType->title);?>
								<span class="af-mr-5 af-ml-5"><strong>x</strong></span><strong><?php echo $this->escape($item->count);?></strong>
							</div>
<!--
							<div class="col-3 af-col-xxs-7">
								<div class="super_number<?php echo $ticketType->id; ?> menu af-float-left">
									<?php echo $this->escape($item->count);?>
								</div>
							</div>
-->
							<div class="col-7 af-col-xxs-7 text-end af-pl-0 af-pr-10">
								<strong class="af-mr-5">
									<span id="ticket_total_price<?php echo $ticketType->id;?>" class="text-start">
										<?php echo $this->escape($this->utilities->getFormattedPrice($item->totalPrize)); ?>
									</span>
								</strong>
								<?php
								if (!$this->jtParams->get('admin_fee_mode') && $this->jtParams->get('admin_fee_level') != 'order')
								{
									echo '(' . Text::_('COM_JTICKETING_FEE') . ' ';
								?>
									<span class="text-secondary" id="ticket_fee_price<?php echo $ticketType->id;?>">
										<?php echo $this->escape($this->utilities->getFormattedPrice($item->totalFee));?>
									</span>
								<?php
										echo ')';
								}
								?>
							</div>
						</div>
					</div>
					<?php
				}
			}
			?>

		<div class="row border-top af-p-5 af-pt-15 af-mt-20">
			<?php
			if (!$this->jtParams->get("admin_fee_mode") && $this->jtParams->get('admin_fee_level') == 'order')
			{?>
				<div class="col-sm-7 col-xs-7">
				<strong>
					<?php echo Text::_('COM_JTICKETING_ORDER_TOTAL_FEE');?>
				</strong>
			</div>
			<div class="col-sm-5 col-xs-5 text-end">
				<span id="total_fee_amt" class="text-end">
					<strong><?php echo $this->order->getFee();?></strong>
				</span>
			</div>
			<?php }?>

			<div class="col-sm-7 col-xs-7">
				<strong>
					<?php echo Text::_('COM_JTICKETING_ORDER_TOTAL_COST');?>
				</strong>
			</div>
			<div class="col-sm-5 col-xs-5 text-end af-px-10">
				<span id="total_order_amt" class="text-end">
					<strong><?php echo $this->escape($this->utilities->getFormattedPrice($this->order->getNetAmount())); ?></strong>
				</span>
			</div>

			<?php
			if ($this->order->getCouponCode())
			{
				?>
				<div id="coupon_troption mt-20" class="af-px-10 af-py-5">
					<div class="float-start af-my-5">
						<strong>
							<?php echo Text::_('COM_JTICKETING_COUPON_APPLIED');?>
						</strong>
					</div>
					<div class="float-end af-my-5">
						<?php echo $this->order->getCouponCode(); ?>
					</div>
					<div class="clearfix"></div>
				</div>

				<div id="dis_copon" class="af-px-10 af-mb-10 overflow-hidden">
					<div class="float-start af-my-5">
						<strong>
							<?php echo Text::_('COP_DISCOUNT');?>
						</strong>
					</div>
					<div class="float-end af-my-5">
						<span id="dis_copon_amt" class="text-end">
							<?php echo  $this->escape($this->order->getCouponDiscount());?>
						</span>
					</div>
				</div>
				<?php
			}
			?>

			<?php
				if ($this->jtParams->get('allow_taxation') && $this->order->get('order_tax'))
					{
						$taxDetails = new Registry($this->order->order_tax_details);

						foreach ($taxDetails as $taxDetail)
						{
							foreach ($taxDetail->breakup as $breakup)
							{
							?>
								<div class="container">
									<div class="row tax_tr af-mb-10">
										<div class="col-sm-7 col-xs-7">
											<strong>
											<?php echo Text::sprintf('TAX_AMOOUNT', $breakup->percentage) . "%"; ?>
											</strong>
										</div>
										<div class="col-sm-5 col-xs-5 text-end">
											<span id="tax_to_pay" class="text-end">
											<strong>
											<?php echo $this->utilities->getFormattedPrice($breakup->value); ?>
											</strong>
											</span>
										</div>
									</div>
								</div>
						<?php
							}
						}?>
						<div class="container">
							<div class="row tax_tr af-mb-10">
								<div class="col-sm-7 col-xs-7">
									<strong><?php echo Text::_('TOTALPRICE_PAY_AFTER_TAX'); ?></strong>
								</div>
								<div class="col-sm-5 col-xs-5 text-end">
									<span id="net_amt_after_tax" class="text-end">
									<strong>
									<?php echo $this->order->getAmount(); ?>
									</strong>
									</span>
								</div>
							</div>
						</div>
			<?php	}	?>
						<div class="clearfix"></div>
						<div class="container">
							<div class="row af-mb-10 border-top af-pt-10 af-mt-10">
								<div class="col-7 af-px-10">
									<strong>
										<?php echo Text::_('COM_JTICKETING_TOTAL_PAY_AFTER_TAX');?>
									</strong>
								</div>
								<div class="col-5 text-end af-px-10">
									<span id="total_amt" class="text-end">
										<strong><?php echo  $this->escape($this->order->getAmount());?></strong>
									</span>
								</div>
							</div>
						</div>
			</div>
		</div>
	</div>
