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
						<div class="af-col-xxs-12 col-xs-5 text-left af-py-5">
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
						<div class="af-col-xxs-6 col-xs-3 af-py-5">
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
						<div class="af-col-xxs-6 col-xs-4 text-right af-py-5">
							<strong> <span id="ticket_total_price<?php echo $ticketType->id;?>"
								class="text-left">
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
</div>
<!-- Ticket types end -->

