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

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Layout\LayoutHelper;

HTMLHelper::_('jquery.token');

$document           = Factory::getDocument();
$integration        = $config->get('integration');
$eventOwnerBuy      = $config->get('eventowner_buy');
$user               = Factory::getUser();
$session            = Factory::getSession();
$backlink           = Uri::current();
$session->set('backlink', $backlink);
$enableWaitingList  = $config->get('enable_waiting_list');
$utilities = JT::utilities();

/** @VAR $event JTicketingEventJevents */
$event         = JT::event($eventId);
$eventTickets  = $event->getTicketTypes();
$userId        = Factory::getUser()->id;
$isboughtEvent = $event->isBought($userId);

if ((empty($event->getTicketTypes()) && $enableWaitingList == 'none' && empty($event->isOver())) ||
	empty($event->isTicketingEnabled()))
{
	echo "<button type='button' class='btn btn-info disabled w-100 booking-btn'>" . Text::_('COM_JTICKETING_EVENTS_UNAUTHORISED') . "</button>";

	return;
}

$url            = new Uri;
$pageURL        = $url->toString();
$redirectionUrl = base64_encode($pageURL);
$isPaidEvent        = $event->isPaid();

if (!$event->isOver() || $event->isrepeat())
{
	$isEventbought = $event->isBought();
	$showBuyButton = $event->isAllowedToBuy();
	$tickets       = $event->getTicketTypes();

	if (($integration == COM_JTICKETING_CONSTANT_INTEGRATION_JOMSOCIAL || $integration == COM_JTICKETING_CONSTANT_INTEGRATION_EASYSOCIAL) && $showBuyButton && empty($isEventbought))
	{
			$jsKey = '';

			if ($integration == COM_JTICKETING_CONSTANT_INTEGRATION_EASYSOCIAL)
			{
				$jsKey = 'jQuery(document).ready(function(){   jQuery(".media-meta").html("' . Text::_('TICKET_RSVP_BUY') . '")   });';
			}
			else
			{
				$jsKey = 'jQuery(document).ready(function() {
    						jQuery(".cEvent-Rsvp").html("' . Text::_('TICKET_RSVP_BUY') . '");
    						jQuery(".joms-focus__actions--desktop a:first").remove();
    						jQuery(".joms-focus__actions--desktop :last").before("' . Text::_('TICKET_RSVP_BUY') . '")
							});';
			}

			$document->addScriptDeclaration($jsKey);
	}

	if (!$integration == COM_JTICKETING_CONSTANT_INTEGRATION_JEVENTS && ($event->isCreator() && ($eventOwnerBuy == 0) && !empty($user->id)))
	{
		echo '<div  class="cModule  app-box-content"><b>' . Text::_('MOD_JTICKETING_BUY_EVENT_OWNER_CANT_BUY') . '</b></div>';

		return;
	}


	if ((($integration == COM_JTICKETING_CONSTANT_INTEGRATION_JOMSOCIAL) || ($integration == COM_JTICKETING_CONSTANT_INTEGRATION_JEVENTS))
		&& (!$showBuyButton && $isPaidEvent && empty($event->isBought()))
		&& $enableWaitingList == 'none')
	{
		echo '<div  class="cModule  app-box-content">
		<img class="soldout" src="' . Uri::base() . 'modules/mod_jticketing_buy/images/sold.png" />
		<br/>
		<b>' . Text::_('MOD_JTICKETING_BUY_TICKET_UNAVAILABLE') . '</b>
		</div>';

		return;
	}

	// If Event with unlimited seats then ignore if all event type avalaible count is  0 otherwise hide module
	if ($showBuyButton || !empty($event->isBought()) || $enableWaitingList != 'none')
	{?>
		<div  class="cModule cEvent-Extra app-box tjBs3 $params->get('moduleclass_sfx');?>">
			<div class="">
				<div class="app-box-content">
					<?php
					if ($eventTickets)
					{
					?>
						<div class="row small">
							<div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
								<strong>
									<?php echo Text::_('MOD_JTICKETING_BUY_TICKET_TYPE_TITLE');?>
								</strong>
							</div>
							<div class="col-lg-4 col-md-4 col-sm-4 col-xs-12 center">
								<strong>
									<?php echo Text::_('MOD_JTICKETING_BUY_TICKET_TYPE_PRICE');?>
								</strong>
							</div>
							<?php
							if (!empty($params->get('show_available')))
							{
								?>
								<div class="col-lg-4 col-md-4 col-sm-4 col-xs-12 center">
									<strong>
										<?php echo Text::_('MOD_JTICKETING_BUY_TICKET_TYPE_AVAILABLE');?>
									</strong>
								</div>
								<?php
							}
							?>
						</div>
						<hr class="hr hr-condensed"/>
						<?php
						foreach ($eventTickets as $ticket)
						{
								?>
								<div class="row small">
									<div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
										<?php echo htmlspecialchars($ticket->title, ENT_COMPAT, 'UTF-8');?>
									</div>
									<div class="col-lg-4 col-md-4 col-sm-4 col-xs-12 center">
										<?php
										if ($ticket->price == 0)
										{
											echo Text::_('MOD_JTICKETING_BUY_FREE_TICKET');
										}
										else
										{
											echo $utilities->getFormattedPrice($ticket->price);
										}
										?>
									</div>

									<?php
									if (!empty($params->get('show_available')))
									{
										?>
										<div class="col-lg-4 col-md-4 col-sm-4 col-xs-12 center">
											<?php
											if ($ticket->unlimited_seats)
											{
												echo Text::_('MOD_JTICKETING_BUY_UNLIM_SEATS');
											}
											else
											{
												echo $ticket->count . '/' . $ticket->available;
											}
											?>
										</div>
										<?php
									}
									?>
								</div>
								<hr class="hr hr-condensed"/>
								<?php
						}
					}
					elseif (empty($tickets) && JT::config('ticket_access') == 'exclude')
					{
						echo Text::_('COM_JTICKETING_NOT_AVAILABLE');
					}
					elseif (!$eventTickets && $enableWaitingList != 'none')
					{
						echo Text::_('COM_JTICKETING_WAITINGLIST_MESSAGE');
					}

					// Buy button html start here
					?>
					<div class="center app-box-footer">
						<?php
						echo LayoutHelper::render('event.actions', $event, JPATH_SITE . '/components/com_jticketing/layouts');
						?>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
}
elseif ($isPaidEvent)
{?>
	<div  class="cModule    app-box-content">
			<b> <?php  echo Text::_('MOD_JTICKETING_BUY_EVENT_CANT_BUY') ?> </b>
		</div>

<?php }
