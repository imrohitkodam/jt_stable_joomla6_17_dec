<?php
/**
 * @package     JTicketing
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Filter\InputFilter;

HTMLHelper::_('bootstrap.tooltip');

$data     = $displayData;
$app      = Factory::getApplication();
$utilities = JT::utilities();
$eventLink = $data->eventUrl;
$long_desc_char = $data->descLength;
?>

<div class="eventlist hidden-xs tjBs3">
<div class="thumbnail af-br-0 af-p-0">
<div class="row">
	<div class="col-sm-3 af-pr-0">
		<a href="<?php echo $eventLink;?>" class="text-center pull-left w-100">
		<div class="eventlist__image" title="<?php echo $data->title; ?>" style="background:url('<?php echo $data->eventImage;?>'); background-position: center center; background-size: cover; background-repeat: no-repeat;min-height: 135px;">

		<div class="eventlist__price">
				<strong>
					<?php
						echo $data->eventPrice;
					?>
				</strong>
		</div>
		</div>
		</a>
	</div>
	<div class="col-sm-9">
		<div class="af-pr-10">
		<h4>
			<a title="<?php echo $data->title; ?>" href="<?php echo $eventLink;?>"><?php echo $data->title; ?></a>
		</h4>
		<div class="event-date af-my-10">
			<i class="fa fa-calendar" aria-hidden="true"></i>
			<?php
				echo $startDate = $utilities->getFormatedDate($data->startdate);
			?>
		</div>
		<div class="event-location af-mb-10 af-text-truncate">
			<i class="fa fa-map-marker" aria-hidden="true"></i>
			<?php
				echo $data->location;
			?>
		</div>
		<small class="long_desc eventlist__desc af-font-500">
			<?php 
				$cleanHtmlLongDescription = InputFilter::getInstance(array(), array(), 1, 1)->clean($data->long_description, 'html');

				if (strlen($cleanHtmlLongDescription) > $long_desc_char )
				{
					echo substr($cleanHtmlLongDescription, 0, $long_desc_char) . '...';
				}
				else
				{
					echo $cleanHtmlLongDescription;
				}
			?>
		</small>
		</div>
	</div>
</div>
</div>
</div>

<!--eventpin-->
<div class="col-xs-12 eventpin mobile-view visible-xs tjBs3">
	<div class="thumbnail af-p-0 eventpin__thumbnail pull-left">
		<div class="eventpin_image">
			<a href="<?php echo $eventLink;?>" class="text-center pull-left w-100">
			<div class="eventlist__image" title="<?php echo $data->title; ?>" style="background:url('<?php echo $eventImage;?>'); background-position: center center; background-size: cover; background-repeat: no-repeat;min-height: 160px;"></div>
			</a>
		</div>
		<div class="af-p-10 eventpin_content pull-left w-100">
			<div class="eventpin__title">
				<h4 class="af-mt-0">
					<a title="<?php echo $data->title; ?>" href="<?php echo $eventLink;?>"><?php echo $data->title; ?></a>
				</h4>
			</div>
			<div class="event-date af-my-10">
				<i class="fa fa-calendar" aria-hidden="true"></i>
				<?php
					echo $startDate = $utilities->getFormatedDate($data->startdate);
				?>
			</div>
			<div class="event-location af-mb-10 af-text-truncate">
				<i class="fa fa-map-marker" aria-hidden="true"></i>
				<?php
					echo $data->location;
				?>
			</div>
			<small class="eventlist__desc af-font-500">
			<?php 
				$cleanHtmlLongDescription = InputFilter::getInstance(array(), array(), 1, 1)->clean($data->long_description, 'html');

				if (strlen($cleanHtmlLongDescription) > $long_desc_char )
				{
					echo substr($cleanHtmlLongDescription, 0, $long_desc_char) . '...';
				}
				else
				{
					echo $cleanHtmlLongDescription;
				}
			?>
			</small>
		</div>
	</div>
</div>
