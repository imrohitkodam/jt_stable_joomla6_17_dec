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

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/** @var $this JticketingViewOrder */

JticketingCommonHelper::getLanguageConstant();

?>
<div class="af-mb-15 ticket-box">
	<div>
		<div class="af-mt-15 text-left single-ticket-detail">
			<?php
			$totalCount = 0;
			$options = array();
			$orderItem = current($this->orderItems);
			$selectedType = '';
			$ticketCnt = 0;

			if (! empty($orderItem->type_id))
			{
				$selectedType = JT::tickettype($orderItem->type_id);
				$ticketCnt = $selectedType->getAvailable();
			}

			if (count($this->ticketTypeData) <= 1)
			{
				$defaultTicket = $this->ticketTypeData[0]->id;
			}

			$remainingSeatCount = 0;
			foreach ($this->ticketTypeData as $key => $ticketType)
			{
				if (! empty($orderItem) && $orderItem->type_id === $ticketType->id)
				{
					$defaultTicket = $ticketType->id;
					$remainingSeatCount = $ticketType->count;
				}

				if (isset($ticketType->count) && $ticketType->count > 0 && empty($ticketType->unlimited_seats))
				{
					$totalCount = $ticketType->count + $totalCount;
				}

				if (((isset($ticketType->count) && $ticketType->count > 0 && $ticketType->unlimited_seats == 0) || $ticketType->unlimited_seats == 1))
		    	{
					$title = $ticketType->title . ' - ' . $this->utilities->getFormattedPrice($ticketType->price);
					$options[] = HTMLHelper::_('select.option', $ticketType->id, $title);
				}

				if ($ticketType->price > 0)
				{
					$this->ticketPriceNotFree = (float) $ticketType->price;
				}

				// Add extra attributes for the select list.
				$attr = array();
				$attr['class'] = 'inputbox ticketsTypes';
				$attr['size'] = '5';
				$attr['data-order-id'] = $this->order->id;
				$attr['data-checkout-limit'] = $this->jtParams->get('max_noticket_peruserperpurchase');
				$attr['data-order-item-id'] = ! empty($orderItem->id) ? $orderItem->id : 0;
				$attr['data-previous-type-id'] = ! empty($defaultTicket) ? $defaultTicket : 0;
			}

			echo HTMLHelper::_('select.genericlist', $options, 'ticketsTypes', $attr, 'value', 'text', isset($defaultTicket) ? $defaultTicket : '');

			// Check if remaining count is less than config count and there are only limited seats.
			if (! empty($selectedType) && ($this->jtParams->get('display_remaining_count') >= $ticketCnt) &&
					($ticketCnt != COM_JTICKETING_CONSTANT_TICKET_TYPE_UNLIMITED))
			{
				?>
			<div class="label label-info af-br-10">
				<?php echo Text::_('COM_JTICKETING_TICKET_TYPE_REMAINING_SEATS') . " : "; ?>
				<span class="remainingSeatCount"><?php echo $remainingSeatCount ? $remainingSeatCount : $ticketType->count; ?></span>
			</div>
			<?php } ?>
		</div>
		<!-- <?php
		if ($ticketType->desc)
		{
			?>
			<div class="col-xs-3">
			<?php
				echo $this->escape($ticketType->desc);
			?>
			</div>
			<?php
		}
		?> -->
	</div>
</div>

