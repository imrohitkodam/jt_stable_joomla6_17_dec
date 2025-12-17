<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2025 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

//no direct access
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;

$document = Factory::getDocument();

$app = Factory::getApplication();
$input = $app->input;

// Check if the user is on the event detail page
$option = $input->getCmd('option', '');
$view = $input->getCmd('view', '');
$layout = $input->getCmd('layout', '');
$eventId = 0;
// Check if it's the event detail page
if ($option === 'com_jticketing' && $view === 'event') {
    // Get the Event ID from the URL
    $eventId = $input->getInt('id', 0);
}

HTMLHelper::_('stylesheet', 'modules/mod_jticketing_event/css/jticketing_event.css');
JLoader::import('frontendhelper', JPATH_SITE . '/components/com_jticketing/helpers');
JLoader::import('route', JPATH_SITE . '/components/com_jticketing/helpers');
$jTRouteHelper = new JTRouteHelper;
JT::utilities()->loadjticketingAssetFiles();
$utilities = JT::utilities();

$tjClass        = 'tjBs3 ';
$allEventsItemId = JT::utilities()->getItemId('index.php?option=com_jticketing&view=events&layout=default');

// @model helper object
$modJTicketingHelper = new ModJTicketingEventHelper;
if($params->get('distance_limit') == 0 && $params->get('personalized_event_suggestion') == 0) {
	$data                = $modJTicketingHelper->getData($params, $latitude,$longitude, $eventId);
}
$pin_width           = $params->get('mod_pin_width', '230', 'INT');
$pin_padding         = $params->get('mod_pin_padding', '10', 'INT');
$arraycnt = $data ? count($data) : 0;

/**
 * Get user Location: 
 * if distance_limit is greater than 0 in config param, then
 * get users latitude and longitude from browser and send the details to function via ajax.
 */


if($params->get('distance_limit') > 0):
	$base_url = Uri::root();
	$document->addScriptDeclaration('
		if (navigator.geolocation) {
			navigator.geolocation.getCurrentPosition(
				function(position) {
					const latitude = position.coords.latitude;
					const longitude = position.coords.longitude;
	
					jQuery.ajax({
					type: "POST",
					url: "'.$base_url.'index.php?option=com_ajax&module=jticketing_event&method=eventsNearMe&format=raw",
					data: "latitude=" + latitude + "&longitude=" + longitude + "&modid='.$module->id.'&eventid='.$eventId.'",
					dataType: "json",
						success: function(data) {
							const eventscontainer = "#mod_jticketing_container"+data.mod_jticketing_container;
							jQuery(eventscontainer).html(data.data);
						},
						error: function(err) {
							console.log("error", err);
						}
					});
				},
				function(error) {
					console.error("Error fetching location:", error);
				}
			);
		}
		else{
			console.error("location not supported");
		}
	');
	
	endif;

?>
<div id="mod_jticketing_container<?php echo $module->id;?>" class="<?php echo $tjClass.$params->get('moduleclass_sfx'); ?> container-fluid" >
<?php
if ($arraycnt <= 0)
{?>
	<div class="alert alert-warning">
		<?php echo Text::_('MOD_JTICKETING_EVENT_NO_DATA_FOUND');?>
	</div>
<?php
}
else
{
	foreach ($data as $modEventData)
	{
	?>
		<div class="col-sm-3 col-xs-12 jticketing_pin_item" id="jtwrap">
			<?php
			$eventLink = Uri::root() . substr(
			Route::_('index.php?option=com_jticketing&view=event&id=' . $modEventData['event']->id . '&Itemid=' . $allEventsItemId),
			strlen(Uri::base(true)) + 1
			);
			?>
			<div class="jticketing_pin_img">
				<?php

				if(!empty($modEventData['image']))
				{
					$imagePath = $modEventData['image'];
				}
				else
				{
					$imagePath = Route::_(Uri::base() . 'media/com_jticketing/images/default-event-image.png');
				}
				?>
				<a href="<?php echo $eventLink;?>" class="af-d-block bg-center af-bg-cover af-bg-repn embed-responsive responsive-embed-16by9" title="<?php echo $modEventData['event']->title;?>" style="background-image:url('<?php echo $imagePath;?>'); height:200px;">
				</a>
			</div>
			<div class="jt-heading">
				<span class="jt-event-ticket-price-text pin__ticket af-mr-5 af-px-5 af-absolute af-bg-faded">
					<a href="<?php echo $eventLink;?>" title="<?php echo $modEventData['event']->title;?>">
						<?php
						if (($modEventData['event_max_ticket'] == $modEventData['event_min_ticket']) AND (($modEventData['event_max_ticket'] == 0) AND ($modEventData['event_min_ticket'] == 0)))
						{
						?>
							<strong><?php echo strtoupper(Text::_('MOD_JTICKETING_ONLY_FREE_TICKET_TYPE'));?></strong>
						<?php
						}
						elseif (($modEventData['event_max_ticket'] == $modEventData['event_min_ticket']) AND  (($modEventData['event_max_ticket'] != 0) AND ($modEventData['event_min_ticket'] != 0)))
						{
						?>
							<strong><?php echo $utilities->getFormattedPrice($modEventData['event_max_ticket']);?></strong>
						<?php
						}
						else
						{
						?>
							<strong>
								<?php
									echo $utilities->getFormattedPrice($modEventData['event_min_ticket']);
									echo ' - ';
									echo $utilities->getFormattedPrice($modEventData['event_max_ticket']);
								?>
							</strong>
						<?php
						}
						?>
					</a>
				</span>
			</div>
			<div class="thumbnail">
				<div class="caption">
					<ul class="list-unstyled">
						<li>
							<div>
								<i class="fa fa-calendar" aria-hidden="true"></i>
								<?php
									echo $utilities->getFormatedDate($modEventData['event']->startdate);
								?>
							</div>
						</li>
						<li>
							<?php
							$online = Uri::base() . 'media/com_jticketing/images/online.png';

							if ($modEventData['event']->online_events)
							{?>
								<img src="<?php echo $online; ?>"
								class="img-circle" alt="<?php echo Text::_('MOD_JTICKETING_EVENT_ONLINE')?>"
								title="<?php echo Text::sprintf('MOD_JTICKETING_ONLINE_EVENT', $modEventData['event']->title);?>">
							<?php
							}
							?>
							<b>
								<a href="<?php echo $eventLink;?>" title="<?php echo $modEventData['event']->title;?>">
								<?php
								if (strlen($modEventData['event']->title) > 20)
								{
									echo substr($modEventData['event']->title, 0, 20) . '...';
								}
								else
								{
									 echo $modEventData['event']->title;
								}

								if ($modEventData['event']->featured == 1)
								{
								?>
									<span>
										<i class="fa fa-star pull-right" aria-hidden="true" title="<?php echo Text::sprintf('MOD_JTICKETING_FEATURED_EVENT', $modEventData['event']->title);?>"></i>
									</span>
								<?php
								}
								?>
								</a>
							</b>
						</li>
						<li class="events-pin-location">
							<i class="fa fa-map-marker" aria-hidden="true"></i>
							<?php
							$location = (isset($modEventData['event']->location) && !empty($modEventData['event']->location)) ? $modEventData['event']->location : $modEventData['location'];

							if ($location)
							{
								echo substr($location, 0, 20) . '...';
							}
							?>
						</li>
					</ul>
					<div class="clearfix"></div>
				</div>
			</div>
		</div>
	<?php
	}
}
?>
</div>
<style>
	@media (min-width: 480px){
		#mod_jticketing_container<?php echo $module->id;?> .jticketing_pin_item { width: <?php echo $pin_width . 'px'; ?> !important; }
	}
</style>
