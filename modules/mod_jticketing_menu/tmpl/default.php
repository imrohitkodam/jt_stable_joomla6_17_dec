<?php
/**
 * @package     JTicketing
 * @subpackage  mod_jticketing_menu
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\Module\Menu\Site\Helper\MenuHelper;

HTMLHelper::_('stylesheet', 'media/com_jticketing/css/jticketing.min.css');

$user = Factory::getUser();

if (empty($user->id))
{
	return;
}

// Load assets
JT::utilities()->loadjticketingAssetFiles();
$tjClass = 'JTICKETING_WRAPPER_CLASS ';
$input   = Factory::getApplication()->input;
$Itemid  = $input->get('Itemid', '', 'INT');
$payoutItemid  = $Itemid;

$app = Factory::getApplication();
$menu = $app->getMenu();
$user = Factory::getUser();
$groups = $user->getAuthorisedViewLevels();
$integration = JT::getIntegration();
$com_params = ComponentHelper::getParams('com_jticketing');
$activeMenu = $menu->getActive();
$activeId = $activeMenu->id;

$ordersMenuItem = $menu->getItems('link', 'index.php?option=com_jticketing&view=orders&layout=my');
$attendeesMenuItem = $menu->getItems('link', 'index.php?option=com_jticketing&view=attendees');
$allticketsalesMenuItem = $menu->getItems('link', 'index.php?option=com_jticketing&view=allticketsales');
$myticketsMenuItem = $menu->getItems('link', 'index.php?option=com_jticketing&view=mytickets');
$payoutMenuItem = $menu->getItems('link', 'index.php?option=com_tjvendors&view=vendors');
$myEventsMenuItem = $menu->getItems('link', 'index.php?option=com_jticketing&view=events&layout=my');
$myVenuesMenuItem = $menu->getItems('link', 'index.php?option=com_jticketing&view=venues');
$createNewEventMenuItem = $menu->getItems('link', 'index.php?option=com_jticketing&view=eventform');
$createNewVenueMenuItem = $menu->getItems('link', 'index.php?option=com_jticketing&view=venueform');
$allOrdersMenuItem = $menu->getItems('link', 'index.php?option=com_jticketing&view=orders&layout=default');
$createNewCouponMenuItem = $menu->getItems('link', 'index.php?option=com_jticketing&view=couponform');
$myCouponsMenuItem = $menu->getItems('link', 'index.php?option=com_jticketing&view=coupons');
$waitingListMenuItem = $menu->getItems('link', 'index.php?option=com_jticketing&view=waitinglist');
$pdfTemplateMenuItem = $menu->getItems('link', 'index.php?option=com_jticketing&view=pdftemplates');

if (!empty($Itemid))
{
	$Session = Factory::getSession();
	$Session->set("JT_Menu_Itemid", $Itemid);
}

if (!empty($eventid))
{
	$eventowner = JT::event($eventid)->getCreator();
}
?>
<div class="jticketing-menu-module <?php echo $tjClass . $params->get('moduleclass_sfx'); ?>">
	<div class="row-fluid">
		<div class="tj-list-group">
			<!-- Event Menu List -->
			<!--added for jticketing menu -->
			<ul class="">
			<?php
			$eventlink = (!empty($eventid)) ? '&event=' . $eventid : '';

			if ($integration == 'com_jticketing')
			{
				if (isset($createNewVenueMenuItem[0]) && ((!empty($createNewVenueMenuItem[0]->access) && in_array($createNewVenueMenuItem[0]->access, $groups)) || empty($createNewVenueMenuItem[0]->access)))
				{
					?>
						<li class="tj-list-group-item">
							<a <?php echo $activeId == $createNewVenueMenuItem[0]->id ? 'class="active"' : '';  ?> href="<?php echo Route::_('index.php?option=com_jticketing&view=venueform&Itemid=' . $createNewVenueMenuItem[0]->id . $eventlink);?>">
								<?php echo Text::_('CREATE_NEW_VENUE');?></a>
						</li>
					<?php
				}

				if (isset($myVenuesMenuItem[0]) && ((!empty($myVenuesMenuItem[0]->access) && in_array($myVenuesMenuItem[0]->access, $groups)) || empty($myVenuesMenuItem[0]->access)))
				{
					?>
						<li class="tj-list-group-item">
							<a <?php echo $activeId == $myVenuesMenuItem[0]->id ? 'class="active"' : '';  ?> href="<?php echo Route::_('index.php?option=com_jticketing&view=venues&Itemid=' . $myVenuesMenuItem[0]->id . $eventlink);?>">
								<?php echo Text::_('MY_VENUES');?></a>
						</li>
					<?php
				}

				if (isset($createNewEventMenuItem[0]) && ((!empty($createNewEventMenuItem[0]->access) && in_array($createNewEventMenuItem[0]->access, $groups)) || empty($createNewEventMenuItem[0]->access)))
				{
					?>
						<li class="tj-list-group-item">
							<a <?php echo $activeId == $createNewEventMenuItem[0]->id ? 'class="active"' : '';  ?> href="<?php echo Route::_('index.php?option=com_jticketing&view=eventform&Itemid=' . $createNewEventMenuItem[0]->id . $eventlink);?>">
								<?php echo Text::_('CREATE_NEW_EVENT');?></a>
						</li>
					<?php
				}
			}
			
			if (isset($myEventsMenuItem[0]) && ((!empty($myEventsMenuItem[0]->access) && in_array($myEventsMenuItem[0]->access, $groups)) || empty($myEventsMenuItem[0]->access)))
			{
				?>
					<li class="tj-list-group-item">
						<a <?php echo $activeId == $myEventsMenuItem[0]->id ? 'class="active"' : '';  ?> href="<?php echo Route::_('index.php?option=com_jticketing&view=events&layout=my&Itemid=' . $myEventsMenuItem[0]->id . $eventlink);?>">
							<?php echo Text::_('MY_EVENTS');?></a>
					</li>
				<?php
			}

			if (isset($allOrdersMenuItem[0]) && ((!empty($allOrdersMenuItem[0]->access) && in_array($allOrdersMenuItem[0]->access, $groups)) || empty($allOrdersMenuItem[0]->access)))
			{
				?>
					<li class="tj-list-group-item">
						<a <?php echo $activeId == $allOrdersMenuItem[0]->id ? 'class="active"' : '';  ?> href="<?php echo Route::_('index.php?option=com_jticketing&view=orders&layout=default&Itemid=' . $allOrdersMenuItem[0]->id . $eventlink);?>">
							<?php echo Text::_('ALL_ORDERS');?></a>
					</li>
				<?php
			}

			if (empty($eventid) || (($eventowner == $user->id) && !empty($eventid)))
			{
				if (isset($allticketsalesMenuItem[0]) && ((!empty($allticketsalesMenuItem[0]->access) && in_array($allticketsalesMenuItem[0]->access, $groups)) || empty($allticketsalesMenuItem[0]->access)))
				{
					?>
					<li class="tj-list-group-item">
						<a <?php echo $activeId == $allticketsalesMenuItem[0]->id ? 'class="active"' : '';  ?> href="<?php echo Route::_('index.php?option=com_jticketing&view=allticketsales&Itemid=' . $allticketsalesMenuItem[0]->id . $eventlink);?>">
							<?php echo Text::_('TICK_SALES'); ?></a>
					</li>
					<?php
				}
				
				if (isset($attendeesMenuItem[0]) && ((!empty($attendeesMenuItem[0]->access) && in_array($attendeesMenuItem[0]->access, $groups)) || empty($attendeesMenuItem[0]->access)))
				{
					?>
					<li class="tj-list-group-item">
						<a <?php echo $activeId == $attendeesMenuItem[0]->id ? 'class="active"' : '';  ?> href="<?php echo Route::_('index.php?option=com_jticketing&view=attendees&Itemid=' . $attendeesMenuItem[0]->id . $eventlink);?>">
							<?php echo Text::_('ATTENDEES'); ?></a>
					</li>
					<?php
				}

				if (isset($payoutMenuItem[0]) && ((!empty($payoutMenuItem[0]->access) && in_array($payoutMenuItem[0]->access, $groups)) || empty($payoutMenuItem[0]->access)))
				{
					?>
						<li class="tj-list-group-item">
							<a <?php echo $activeId == $payoutMenuItem[0]->id ? 'class="active"' : '';  ?> href="<?php echo Route::_('index.php?option=com_tjvendors&view=vendors&Itemid=' . $payoutMenuItem[0]->id);?>">
								<?php echo Text::_('MY_PAYOUT');?></a>
						</li>
					<?php
				}
			}

			if (isset($ordersMenuItem[0]) && ((!empty($ordersMenuItem[0]->access) && in_array($ordersMenuItem[0]->access, $groups)) || empty($ordersMenuItem[0]->access)))
			{
				?>
					<li class="tj-list-group-item">
						<a <?php echo $activeId == $ordersMenuItem[0]->id ? 'class="active"' : '';  ?> href="<?php echo Route::_('index.php?option=com_jticketing&view=orders&layout=my&Itemid=' . $ordersMenuItem[0]->id . $eventlink);?>">
							<?php echo Text::_('MY_ORDERS');?></a>
					</li>
				<?php
			}

			if (isset($myticketsMenuItem[0]) && ((!empty($myticketsMenuItem[0]->access) && in_array($myticketsMenuItem[0]->access, $groups)) || empty($myticketsMenuItem[0]->access)))
			{
				?>
					<li class="tj-list-group-item">
						<a <?php echo $activeId == $myticketsMenuItem[0]->id ? 'class="active"' : '';  ?> href="<?php echo Route::_('index.php?option=com_jticketing&view=mytickets&Itemid=' . $myticketsMenuItem[0]->id . $eventlink); ?>">
						<?php echo Text::_('MY_TICKET');?></a>
					</li>
				<?php
			}

			if (isset($createNewCouponMenuItem[0]) && ((!empty($createNewCouponMenuItem[0]->access) && in_array($createNewCouponMenuItem[0]->access, $groups)) || empty($createNewCouponMenuItem[0]->access)))
			{
				?>
					<li class="tj-list-group-item">
						<a <?php echo $activeId == $createNewCouponMenuItem[0]->id ? 'class="active"' : '';  ?> href="<?php echo Route::_('index.php?option=com_jticketing&view=couponform&Itemid=' . $createNewCouponMenuItem[0]->id . $eventlink);?>">
							<?php echo Text::_('CREATE_NEW_COUPON');?></a>
					</li>
				<?php
			}

			if (isset($myCouponsMenuItem[0]) && ((!empty($myCouponsMenuItem[0]->access) && in_array($myCouponsMenuItem[0]->access, $groups)) || empty($myCouponsMenuItem[0]->access)))
			{
				?>
					<li class="tj-list-group-item">
						<a <?php echo $activeId == $myCouponsMenuItem[0]->id ? 'class="active"' : '';  ?> href="<?php echo Route::_('index.php?option=com_jticketing&view=coupons&Itemid=' . $myCouponsMenuItem[0]->id . $eventlink);?>">
							<?php echo Text::_('MY_COUPONS');?></a>
					</li>
				<?php
			}

			if ($com_params->get('enable_waiting_list') != 'none')
			{
				if (isset($waitingListMenuItem[0]) && ((!empty($waitingListMenuItem[0]->access) && in_array($waitingListMenuItem[0]->access, $groups)) || empty($waitingListMenuItem[0]->access)))
				{
					?>
						<li class="tj-list-group-item">
							<a <?php echo $activeId == $waitingListMenuItem[0]->id ? 'class="active"' : '';  ?> href="<?php echo Route::_('index.php?option=com_jticketing&view=waitinglist&Itemid=' . $waitingListMenuItem[0]->id . $eventlink);?>">
								<?php echo Text::_('WAITING_LIST');?></a>
						</li>
					<?php
				}
			}

			if (isset($pdfTemplateMenuItem[0]) && ((!empty($pdfTemplateMenuItem[0]->access) && in_array($pdfTemplateMenuItem[0]->access, $groups)) || empty($pdfTemplateMenuItem[0]->access)))
			{
				?>
					<li class="tj-list-group-item">
						<a <?php echo $activeId == $pdfTemplateMenuItem[0]->id ? 'class="active"' : '';  ?> href="<?php echo Route::_('index.php?option=com_jticketing&view=pdftemplates&Itemid=' . $pdfTemplateMenuItem[0]->id . $eventlink);?>">
							<?php echo Text::_('PDF_TEMPLATES');?>
						</a>
					</li>
				<?php
			}
			?>
			</ul>
		</div>
	</div>
</div>
