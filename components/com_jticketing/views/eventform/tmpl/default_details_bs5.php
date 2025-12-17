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

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\HTML\HTMLHelper;

/** @var $this JticketingViewEventform */
?>
	<div class="row event_details_form af-mt-10">
		<!--Col-md-6 start-->
		<div class="col-md-8 col-xs-12">
			<div class="row mt-2">
				<div class="col-sm-6 col-xs-12 af-mb-10">
					<?php echo $this->form->getLabel('title');?>
					<?php echo $this->form->getInput('title'); ?>
				</div>

				<div class="col-sm-6 col-xs-12 af-mb-10">
					<?php echo $this->form->getLabel('alias');?>
					<?php echo $this->form->getInput('alias'); ?>
				</div>
			</div>
			<?php
				$integration = JT::getIntegration(true);
				if ($integration == 2): ?>
					<div class="row mt-2">
						<div>
							<?php echo $this->form->getLabel('recurring_type');?>
							<?php echo $this->form->getInput('recurring_type');?>
						</div>
					</div>
					<div class="row">
						<div class="col-sm-6 col-xs-12 mb-0">
							<?php
								$repeatInterval = $this->form->renderField('repeat_interval');
								$repeatInterval = str_replace('class="control-group"', '', $repeatInterval);
								echo $repeatInterval;
							?>
						</div>
						<div class="col-sm-6 col-xs-12 mb-0">
							<?php
								$repeatVia = $this->form->renderField('repeat_via');
								$repeatVia = str_replace('class="control-group"', '', $repeatVia);
								echo $repeatVia;
							?>
						</div>
					</div>

					<div class="row">
						<div class="col-sm-6 col-xs-12 af-mb-10">
							<?php
								$repeatCount = $this->form->renderField('repeat_count');
								$repeatCount = str_replace('class="control-group"', '', $repeatCount);
								echo $repeatCount;
							?>
							<?php
								$repeatUntil = $this->form->renderField('repeat_until');
								$repeatUntil = str_replace('class="control-group"', '', $repeatUntil);
								echo $repeatUntil;
							?>
						</div>
					</div>

					<div class="row mt-2">
						<div class="col-sm-6 col-xs-12 af-mb-10">
							<?php echo $this->form->getLabel('eventstart_date');?>
							<?php echo $this->form->getInput('eventstart_date');?>
						</div>
						<div class="col-sm-6 col-xs-12 af-mb-10">
							<?php
								$field = $this->form->renderField('eventend_date');
								$field = str_replace('class="control-group"', '', $field);
								echo $field;
							?>
						</div>
					</div>
					<div class="row mt-3">
						<div class="col-sm-6 col-xs-12 af-mb-10">
							<?php echo $this->form->getLabel('start_time');?>
							<?php echo $this->form->getInput('start_time');?>
						</div>
						<div class="col-sm-6 col-xs-12 af-mb-10">
							<?php echo $this->form->getLabel('end_time');?>
							<?php echo $this->form->getInput('end_time');?>
						</div>
					</div>
			<?php endif; ?>
			<div class="row mt-3">
				<div class="col-sm-6 col-xs-12 af-mb-10">
					<?php echo $this->form->getLabel('catid');?>
					<?php echo $this->form->getInput('catid'); ?>
				</div>

				<div class="col-sm-6 col-xs-12 af-mb-10">
					<?php
					$canState = false;
					$canState = Factory::getUser()->authorise('core.edit.own', 'com_jticketing');

					if ($this->adminApproval == 0)
					{
						if (!$canState)
						{
							?>
							<?php echo $this->form->getLabel('state'); ?>
							<?php
							$stateString = Text::_('JUNPUBLISHED');
							$stateValue = 0;

							if ($this->item->state == 1):
							$stateString = Text::_('JPUBLISHED');
							$stateValue = 1;
							endif;
							?>
							<div>
								<?php echo $stateString;?>
							</div>
							<input type="hidden" name="jform[state]" value="<?php echo $stateValue;?>"/>
							<?php
						}
						else
						{
							?>
							<?php echo $this->form->getLabel('state'); ?>
							<div class="mt-1 mx-2">
								<?php
								$state = $this->form->getValue('state');
								$jtPublish = "checked='checked'";
								$jtUnpublish = "";

								if (empty($state))
								{
									$jtPublish = "";
									$jtUnpublish = "checked='checked'";
								}
								?>
								<label class="radio-inline mt-1" >
									<input type="radio" <?php echo $jtPublish;?> value="1" id="jform_state1" name="jform[state]" >
									<?php echo Text::_('JPUBLISHED');?>
								</label>
								<label class="radio-inline mt-1 mx-3">
									<input type="radio" <?php echo $jtUnpublish;?> value="0" id="jform_state0" name="jform[state]" >
									<?php echo Text::_('JUNPUBLISHED');?>
								</label>
							</div>
						<?php
						}
					}
					else
					{
					?>
						<input type="hidden" name="jform[state]" id="jform_state" value="0" />
				<?php
					}
					?>
				</div>
			</div>

			<div class="row mt-3">
				<div class="col-sm-6 col-xs-12 af-mb-10">
					<?php echo $this->form->getLabel('allow_view_attendee'); ?>
					<div class="mt-1 mx-2">
						<?php
						$allowEventOrderToSee = intval($this->form->getValue('allow_view_attendee'));

						if ($allowEventOrderToSee == 0)
						{
							$jtAllowNo = " checked='checked' ";
							$jtAllowYes = "";
						}
						elseif ($allowEventOrderToSee == 1)
						{
							$jtAllowNo = "";
							$jtAllowYes = " checked='checked' ";
						}
						?>
						<label class="radio-inline">
							<input type="radio" value="1" name="jform[allow_view_attendee]" class="" <?php echo $jtAllowYes;?> >
							<?php echo Text::_('JYES');?>
						</label>
						<label class="radio-inline mx-3">
							<input type="radio" value="0" name="jform[allow_view_attendee]" class="" <?php echo $jtAllowNo;?> >
							<?php echo Text::_('JNO');?>
						</label>
					</div>
				</div>
				<div class="col-sm-6 col-xs-12 af-mb-10">
					<div><?php echo $this->form->getLabel('access'); ?></div>
					<div><?php echo $this->form->getInput('access'); ?></div>
				</div>

				<?php if ($this->show_tags == 1): ?>
					<div class="col-sm-6 col-xs-12 af-mb-10">
						<?php echo $this->form->getLabel('tags');?>
						<?php echo $this->form->getInput('tags'); ?>
					</div>
				<?php endif; ?>
			</div>
		</div>
		<!--Col-md-6 End-->
		<!--Col-md-6 start-->
		<div class="col-md-4 col-xs-12">
			<?php
			if ($this->enableOnlineVenues == 1)
			{
			?>
				<div class="row mt-2">
					<div class="col-sm-6 col-xs-12 af-mb-10">
						<?php echo $this->form->getLabel('online_events'); ?>
						<div class="mt-1">
							<?php
								$readOnly = "";
								if (!$this->event->isOnline())
								{
									$jtOnlineNo = " checked='checked' ";
									$jtOnlineYes = "";
								}
								else
								{
									$readOnly = "readonly disabled";
									$jtOnlineNo = "";
									$jtOnlineYes = " checked='checked' ";
								}
								?>

							<label class="radio-inline">
								<input type="radio" value="1" name="jform[online_events]" class="" <?php echo $jtOnlineYes;  echo $readOnly; ?>  >
								<?php echo Text::_('COM_JTICKETING_YES');?>
							</label>
							<label class="radio-inline mx-3">
								<input type="radio" value="0" name="jform[online_events]" class="" <?php echo $jtOnlineNo; echo $readOnly;?> >
								<?php echo Text::_('COM_JTICKETING_NO');?>
							</label>
						</div>
					</div>
				</div>
			<?php
			}

			if ($this->item->venue == 0)
			{
			?>
				<div class="row mt-4" id="note_id">
					<div class="col-sm-10 col-xs-12 af-mb-10">
						<?php echo Text::sprintf('COM_VENUE_LOCATION_NOTE');?>
					</div>
				</div>
			<?php
			}
			?>
			<div class="row mt-5">
				<div class="col-sm-12 col-xs-12 eventDateTime list-width" id="venue_id">
					<?php echo $this->form->getLabel('venue'); ?>
					<?php echo $this->form->getInput('venue'); ?>
					<div id="ajax_loader"></div>
				</div>
			</div>

			<?php
			if ($this->enableOnlineVenues == 1)
			{
			?>
				<div class="row">
					<div class="col-sm-12 col-xs-12 eventDateTime list-width" id="venuechoice_id">
						<?php echo $this->form->getLabel('venuechoice'); ?>
						<?php echo $this->form->getInput('venuechoice'); ?>

						<input type="hidden" name="event_url" class="event_url" id="event_url" value=""/>
						<input type="hidden" name="event_sco_id" class="event_sco_id" id="event_sco_id" value=""/>
					</div>
				</div>

				<div class="row">
					<div class="col-sm-12 col-xs-12 eventDateTime list-width" id="existingEvent">

						<?php
						$exitingField = $this->form->getField('existing_event');

						if ($this->event->getId())
						{
							$exitingField->__set('value', $this->event->getOnlineEventId());
							$exitingField->addOption($this->event->getTitle(), ['value' => $this->event->getOnlineEventId(), 'selected' => 'selected']);
						}
						?>

						<?php echo $this->form->getLabel('existing_event'); ?>
						<span class="star">&nbsp;*</span>
						<?php echo $exitingField->__get('input'); ?>
					</div>
				</div>
			<?php
			}?>

			<div class="row mt-4" id="event-location">
				<div class="col-12 af-mb-10">
					<?php
					echo $this->form->getLabel('location');
					echo $this->form->getInput('location');
					echo $this->form->renderField('longitude');
					echo $this->form->renderField('latitude');
					?>
				</div>
			</div>
		</div>
		<!--Col-md-6 End-->
	</div>
<?php
$googleMapLink = "https://maps.googleapis.com/maps/api/js?libraries=places&key=" . $this->googleMapApiKey;

HTMLHelper::_('script', $googleMapLink);

Factory::getDocument()->addScriptDeclaration("
	var googleMapApiKey = '" . $this->googleMapApiKey . "';
	var googleMapLink = '" . $googleMapLink . "';

	// Google Map autosuggest  for location
	function initializeGoogleMap()
	{
		if (googleMapApiKey)
		{
			input = document.getElementById('jform_location');
			var autocomplete = new google.maps.places.Autocomplete(input);
		}
	}

	google.maps.event.addDomListener(window, 'load', initializeGoogleMap);
");
?>
<script type="text/javascript">
 document.addEventListener('DOMContentLoaded', function () {
    const repeatViaRadios = document.querySelectorAll('input[name="jform[repeat_via]"]');

    repeatViaRadios.forEach(function (radio) {
        radio.addEventListener('change', function () {
            const repeatCount = document.getElementById('jform_repeat_count');
            const repeatUntil = document.getElementById('jform_repeat_until');

            if (this.value === 'rep_until') {
                if (repeatCount) repeatCount.value = 0;
                if (repeatUntil) repeatUntil.value = '';
            } else if (this.value === 'rep_count') {
                if (repeatUntil) repeatUntil.value = '';
            }
        });
    });
});
</script>