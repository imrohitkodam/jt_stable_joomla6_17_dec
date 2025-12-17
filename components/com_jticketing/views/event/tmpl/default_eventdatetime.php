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

use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
?>

<div class="row">
	<?php
	if ($this->item->online_events == '0')
	{
		if ($this->item->venue != '0')
		{
			?>
			<div class="col-xs-12 eventDetails-metaBlocks">
				<i class="fa fa-map-marker af-mr-5" aria-hidden="true"></i>
				<strong><?php echo Text::_('COM_JTICKETING_EVENT_LOCATION');?></strong><br>

				<?php
				if ($this->item->online_events != '0')
				{
					echo $this->venueName;
				}
				else
				{
					echo $this->venueName . ', ' . $this->venueAddress;
				}
				?>

				<br/>
				<?php
				if (!empty(JT::config()->get('google_map_api_key')))
				{?>
					<div class="af-py-10">
					<a id="googleMap" href="#evnetGoogleMapLocation" title="">
						<?php echo Text::_('COM_JTICKETING_VIEW_MAP_LINK')?>
					</a>
					</div>
				<?php
				}
				?>
			</div>
			<?php
		}
		else
		{
			?>
			<div class="col-xs-12 eventDetails-metaBlocks">
				<i class="fa fa-map-marker af-mr-5" aria-hidden="true"></i>
				<strong><?php echo Text::_('COM_JTICKETING_EVENT_LOCATION');?></strong>
				<br/>
				<?php echo $this->item->location;?>
				<br/>
				<?php
				if (!empty(JT::config()->get('google_map_api_key')))
				{?>
					<div class="af-py-10">
					<a id="googleMap" href="#evnetGoogleMapLocation" title="">
						<?php echo Text::_('COM_JTICKETING_VIEW_MAP_LINK')?>
					</a>
					</div>
				<?php
				}
				?>
			</div>
			<?php
		}
	}
	?>

	<div class="col-xs-12 eventDetails-metaBlocks">
		<i class="fa fa-calendar af-mr-5" aria-hidden="true"></i>
		<strong><?php echo Text::_('COM_JTICKETING_EVENT_DATE_AND_TIME');?></strong>
		<br/>
		<?php
		$startDate = HTMLHelper::date($this->item->startdate, Text::_('COM_JTICKETING_DATE_FORMAT_SHOW_FJY_DATE'), true);
		$endDate   = HTMLHelper::date($this->item->enddate, Text::_('COM_JTICKETING_DATE_FORMAT_SHOW_FJY_DATE'), true);

		if ($startDate == $endDate)
		{
			$endDateTime = HTMLHelper::date($this->item->enddate, Text::_('COM_JTICKETING_TIME_FORMAT_SHOW_AMPM_SMALL_CAPS'), true);

			echo $this->utilities->getFormatedDate($this->item->startdate);
			echo " - " . $endDateTime;
		}
		else
		{
			echo $this->utilities->getFormatedDate($this->item->startdate);
			echo " - ";
			echo $this->utilities->getFormatedDate($this->item->enddate);
		}

		if($this->params->get('display_timezone'))
		{?>
			<span class="time" ></span>
		<?php
		}?>

		<div class="af-py-10">
			<?php
			if ($this->item->enddate > $this->currentTime)
			{
				$link = Route::_('index.php?option=com_jticketing&view=event&tmpl=component&layout=add_to_calendar&id=' . $this->item->id);
				?>
				<a href="#addToGoogleModal" data-keyboard="true" data-toggle="modal" data-bs-toggle="modal"  data-target="#addToGoogleModal" data-bs-target="#addToGoogleModal"> 
					<?php echo Text::_('COM_JTICKETING_EVENT_ADD_TO_CALENDER');?>
				</a>
				<?php
			}
			?>
		</div>

		<div class="modal center fade" id="addToGoogleModal" role="dialog" tabindex='-1'>
			<div class="modal-dialog">
				<!-- Modal content-->
				<div class="modal-content">
					<div class="modal-body">
						<p><?php echo $this->loadTemplate("add_to_calendar");?></p>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-default" data-bs-dismiss="modal" data-dismiss="modal">
							<?php echo Text::_('COM_JTICKETING_MODAL_CLOSE'); ?>
						</button>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
