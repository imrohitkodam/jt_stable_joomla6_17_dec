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

/** @var $this JticketingViewOrder */
?>
<div class="ticket-list">
	<?php
		foreach ($this->ticketTypeData as $key => $ticketType)
		{
			$item = $this->order->getItemsByType($ticketType->id);
			$totalItemAmount = 0;
			$totalItemFee = 0;
			$totalItemCount = 0;

			if ($item)
			{
				$totalItemAmount = $item->totalPrize;
				$totalItemFee = $item->totalFee;
				$totalItemCount = $item->count;
			}
			if (((isset($ticketType->count) && $ticketType->count > 0 && $ticketType->unlimited_seats == 0) || $ticketType->unlimited_seats == 1))
		    {
			?>
				<div class="af-mb-15 box-shadow af-bg-white af-p-15">
					<div class="row">
						<div class="af-col-xxs-12 col-5 float-start af-py-5">
							<input class="inputbox input-mini"
								id="type_id[<?php echo $ticketType->id?>]"
								name="type_id[<?php echo $ticketType->id?>]" type="hidden"
								value="<?php echo $ticketType->id?>">
							<?php echo $ticketType->title ?> <strong><?php echo $this->utilities->getFormattedPrice($ticketType->price);?></strong>
							<?php
								if ($ticketType->desc && $this->jtParams->get('show_ticket_type_description'))
								{
									?>
									<div>
										<?php echo $this->escape($ticketType->desc);?>
									</div>
									<?php
								}

								// Check if remaining count is less than config count and there are only limited seats.
								if (($this->jtParams->get('display_remaining_count') >= $ticketType->count) && ! ($ticketType->unlimited_seats))
								{
									?>
							<div class="label label-info af-br-10">
								<?php echo Text::sprintf('COM_JTICKETING_TICKET_TYPE_REMAINING_SEATS_COUNT', $ticketType->count); ?>
							</div>
							<?php } ?>
						</div>
						<div class="af-col-xxs-6 col-3 af-py-5">
							<div
								class="super_number<?php echo $ticketType->id; ?> jt-ticket-buttons af-float-left">
								<button id="decr<?php echo $ticketType->id; ?>"
									class="removeItem ui attached button af-float-left af-pt-5"
									data-order-id="<?php echo $this->order->id; ?>"
									data-ticket-type-id="<?php echo $ticketType->id;?>" type="button">–</button>
								<input id="type_ticketcount[<?php echo $ticketType->id?>]"
									name="type_ticketcount[<?php echo $ticketType->id?>]"
									class="ticketCount type_ticketcounts ticketInput<?php echo $ticketType->id; ?>"
									type="text" value="<?php echo $totalItemCount; ?>"  readonly="readonly">
								<button id="incr<?php echo $ticketType->id; ?>"
									class="addItem ui attached button af-float-right af-pt-15 af-pl-20"
									data-order-id="<?php echo $this->order->id;?>"
									data-checkout-limit="<?php echo $ticketType->count;?>"
									data-unlimited-ticket="<?php echo $ticketType->unlimited_seats;?>"
									data-ticket-type-id="<?php echo $ticketType->id;?>" type="button">+</button>
							</div>
						</div>
						<div class="af-col-xxs-6 col-4 text-end af-py-5">
							<strong> <span id="ticket_total_price<?php echo $ticketType->id;?>"
								class="text-end">
							<?php echo  $this->utilities->getFormattedPrice($totalItemAmount); ?>
							</span>
							</strong>
							<?php
							if (!$this->jtParams->get('admin_fee_mode') && $this->jtParams->get('admin_fee_level') != 'order')
								{
									echo '(' . Text::_('COM_JTICKETING_FEE') . ' ';
									?>
							<span class="text-secondary"
								id="ticket_fee_price<?php echo $ticketType->id;?>">
							<?php echo $this->utilities->getFormattedPrice($totalItemFee);?>
							</span>
							<?php
								echo ')';
								}
								?>
						</div>
					</div>
				</div>
				<?php
				if ($ticketType->price > 0)
				{
					$this->ticketPriceNotFree = (float) $ticketType->price;
				}
			}
		}
		?>

	<?php
		$params = JT::config();

		// Check if $params is not null
		if ($params)
		{
			// Default value 0 if parameter is not present
			$enableCoupon = $params->get('enable_coupon', 0);
			$eventId = $this->event->integrationId;

			// Proceed only if coupons are enabled
			if ($enableCoupon == 1)
			{
				$ticketTypeCounts = [];
				$totalTicketCount = 0;

				foreach ($this->ticketTypeData as $ticketType)
				{
					$item = $this->order->getItemsByType($ticketType->id);

					if ($item)
					{
						$ticketTypeCounts[$ticketType->title] = $item->count;
						$totalTicketCount += $item->count;
					}
				}

				$model = JT::model('Coupons', array("ignore_request" => true));
				// Fetch coupons for the event
				$coupons = $model->getApplicableCoupons($eventId, $totalTicketCount);

				// Filter coupons based on group_discount_tickets
				$validCoupons = [];
				foreach ($coupons as $coupon)
				{
					if ($totalTicketCount >= $coupon->group_discount_tickets)
					{
						$validCoupons[$coupon->code] = $coupon;
					}
				}

				// Display coupons only if valid coupons are available
				if (!empty($validCoupons))
				{
				?>
					<h5 class="af-font-600 af-mt-30 af-mb-15 md-mx-0"><?php echo Text::_('COM_JTICKETING_COUPON'); ?></h5>
					<div class="coupon-details-container mt-4 p-3 box-shadow af-bg-light">
						<table class="table table-bordered table-striped">
							<h5 class="af-font-600 af-mt-30 af-mb-15 md-mx-0"><?php echo Text::_('COM_JTICKETING_COUPON_SUGGESTION'); ?></h5>
							<thead>
								<tr>
									<th><?php echo Text::_('COM_JTICKETING_COUPONS_NAME'); ?></th>
									<th><?php echo Text::_('COM_JTICKETING_COUPONS_CODE'); ?></th>
									<th><?php echo Text::_('COM_JTICKETING_COUPONS_VALUE'); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($validCoupons as $coupon)
								{
									$finalValue = $coupon->val_type == 1 ? $coupon->value . ' %' : $this->utilities->getFormattedPrice($coupon->value, false);
									$couponNameWithNote = $coupon->name . ' [' . Text::sprintf('COM_JTICKETING_COUPON_USE_NOTE', (int) $coupon->group_discount_tickets) . ']';
								?>
									<tr>
										<td><?php echo htmlspecialchars($couponNameWithNote, ENT_QUOTES, 'UTF-8'); ?></td>
										<td><?php echo htmlspecialchars($coupon->code, ENT_QUOTES, 'UTF-8'); ?></td>
										<td><?php echo $finalValue; ?></td>
									</tr>
								<?php } ?>
							</tbody>
						</table>
					</div>
	<?php
				}
			}
		} ?>

</div>
<!-- Ticket types end -->

