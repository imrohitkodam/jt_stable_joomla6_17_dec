<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2017 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

$jticketingMainHelper = new jticketingmainhelper;
$jticketingTimeHelper = new jticketingTimeHelper;
$integration = $this->params['integration'];
$pin_width = $this->params['pin_width']? $this->params['pin_width'] : 300;
$pin_padding = $this->params['pin_padding'] ? $this->params['pin_padding'] :10;

?>
<style type="text/css">
	@media (min-width: 480px){
		#jtwrap .pin {
			width: <?php echo $pin_width . 'px'; ?> ;
			padding: <?php echo $pin_padding . 'px'; ?> ;
		}
	}
</style>
<?php
foreach ($this->items as $eventData)
{
?>
<div class="col-sm-3 col-xs-12 pin af-mb-15">
	<div class="pin__cover border-gray">
		<div class="pin__img">
		<?php
			$event = JT::event($eventData->id);
			$eventDetailUrl = $event->getUrl();

			if ($integration == 4)
			{
				$imagePath = '/media/com_easysocial/avatars/event/' . $eventData->id . '/';
			}

			if ($eventData->image)
			{
				$imagePath = $eventData->image->media;
			}
			else
			{
				$imagePath = Route::_(Uri::base() . 'media/com_jticketing/images/default-event-image.png');
			}
		?>
		<a class="af-d-block bg-center af-bg-cover af-bg-repn af-responsive-embed af-responsive-embed-16by9" href="<?php echo $eventDetailUrl; ?>"
			title="<?php echo $this->escape($eventData->title);?>"
		style="background-image:url('<?php echo $imagePath; ?>');">
		</a>
	  </div>

	  <div class="pin__ticket af-mr-5 af-px-5 af-absolute af-bg-faded">

			<a href="<?php echo $eventDetailUrl;?>"
				title="<?php echo $this->escape($eventData->title);?>">
			<?php
				if (($eventData->eventPriceMaxValue == $eventData->eventPriceMinValue)
					AND (($eventData->eventPriceMaxValue == 0) AND ($eventData->eventPriceMinValue == 0)))
				{
				?>
					<strong><?php echo strtoupper(Text::_('COM_JTICKETING_ONLY_FREE_TICKET_TYPE'));?></strong>
				<?php
				}
				elseif (($eventData->eventPriceMaxValue == $eventData->eventPriceMinValue)
					AND (($eventData->eventPriceMaxValue != 0) AND ($eventData->eventPriceMinValue != 0)))
				{
				?>
					<strong><?php echo $this->utilities->getFormattedPrice($eventData->eventPriceMaxValue);?></strong>
				<?php
				}
				elseif (($eventData->eventPriceMaxValue == 1) AND ($eventData->eventPriceMinValue == -1))
				{
				?>
					<strong>
						<?php echo '';?>
					</strong>
				<?php
				}
				else
				{
				?>
					<strong>
						<?php
							echo $this->utilities->getFormattedPrice($eventData->eventPriceMinValue);
							echo ' - ';
							echo $this->utilities->getFormattedPrice($eventData->eventPriceMaxValue);
						?>
					</strong>
				<?php
				}
			?>
			</a>
		</div>

		<div class="pin__info af-p-10 af-bg-faded">
			<ul class="list-unstyled">
				<li class="af-pb-5">
					<div>
						<i class="fa fa-calendar af-mr-5" aria-hidden="true"></i>
					<?php
						echo $this->utilities->getFormatedDate($eventData->startdate);
					?>
					</div>
				</li>
				<li>
					<?php
					if ($this->params->get('display_timezone'))
					{?>
						<span class="time<?php echo $event->id;?>"></span>
					<?php
					}
					?>
				</li>
				<li class="af-pb-5 af-text-truncate">
					<?php
					$online = Uri::base() . 'media/com_jticketing/images/online.png';

					if ($eventData->online_events)
					{
					?>
						<img src="<?php echo $online; ?>"
						class="img-circle af-d-inline-block" alt="<?php echo Text::_('COM_JTK_FILTER_SELECT_EVENT_ONLINE')?>"
						title="<?php echo Text::sprintf('COM_JTICKETING_ONLINE_EVENT', $this->escape($eventData->title));?>">
					<?php
					}?>
					<b>
						<a href="<?php echo $eventDetailUrl;?>"
							title="<?php echo $this->escape($eventData->title);?>">
							<?php echo $this->escape($eventData->title);?>
							<?php
							if ($eventData->featured == 1)
							{
							?>
								<span>
								<i class="fa fa-star pull-right" aria-hidden="true"
								title="<?php echo Text::sprintf('COM_JTICKETING_FEATURED_EVENT', $this->escape($eventData->title));?>"></i>
								</span>
							<?php
							}
							?>
						</a>
					</b>
				</li>
				<li>
					<i class="fa fa-map-marker af-mr-5" aria-hidden="true"></i>
					<?php
					if (strlen($eventData->location) > 20)
					{
						echo substr($this->escape($eventData->location), 0, 20) . '...';
					}
					else
					{
						echo $this->escape($eventData->location);
					}
					?>
				</li>
			</ul>
		</div>
   </div>
</div>
<?php
	$date = Factory::getDate();
	$currentDate = HTMLHelper::date($date, 'Y-m-d H:i:s');
	$startDate = HTMLHelper::date($event->startdate, 'Y-m-d H:i:s');
	$endDate = HTMLHelper::date($event->enddate, 'Y-m-d H:i:s');
	$guest = Factory::getUser()->id ? 0 : 1;
	
	Factory::getDocument()->addScriptDeclaration('
		var event_id = "' . $event->id . '";
		var currentDate = "' . $currentDate . '";
		var startDate = "' . $startDate . '";
		var endDate = "' . $endDate . '";
		var startDateUTC = "' . $event->startdate . '";
		var endDateUTC = "' . $event->enddate . '";
		var guest = "' . $guest . '";
		var spanName = ".time' . $event->id . '";

		if (guest == 1)
	    {
	       	jtSite.events.localTimeEvents(startDateUTC, endDateUTC, event_id);
	    }
	    else
	    {
			jtSite.events.localTimeEvents(startDate, endDate, event_id);
	    }
	');
$currTime = Factory::getDate()->toSql();
}?>
<div></div>
