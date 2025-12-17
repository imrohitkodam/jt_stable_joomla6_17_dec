<?php

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;

?>

<div class="row-fluid form-horizontal-desktop">
	<div class="span6">
		<div class="control-group" style="display:none">
			<div class="control-label"><?php echo $this->form->getLabel('id'); ?></div>
			<div class="controls"><?php echo $this->form->getInput('id'); ?></div>
		</div>
		<div class="control-group">
			<div class="control-label"><?php echo $this->form->getLabel('title'); ?></div>
			<div class="controls"><?php echo $this->form->getInput('title'); ?></div>
		</div>
		<div class="control-group">
			<div class="control-label"><?php echo $this->form->getLabel('alias'); ?></div>
			<div class="controls"><?php echo $this->form->getInput('alias'); ?></div>
		</div>

		<div class="control-group">
			<div class="control-label"><?php echo $this->form->getLabel('created_by'); ?></div>
			<div class="controls"><?php echo $this->form->getInput('created_by'); ?></div>
		</div>

		<div class="control-group">
			<div class="control-label"><?php echo $this->form->getLabel('catid'); ?></div>
			<div class="controls"><?php echo $this->form->getInput('catid'); ?></div>
		</div>
		<?php if ($this->show_tags == 1): ?>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('tags'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('tags'); ?></div>
			</div>
		<?php endif; ?>
		<div class="control-group">
			<?php
			$canState = false;
			$canState = $canState = Factory::getUser()->authorise('core.edit.state','com_jticketing');

			if(!$canState): ?>
				<div class="control-label">
					<?php echo $this->form->getLabel('state'); ?>
				</div>
				<?php
				$state_string = Text::_('COM_JTICKETING_UNPUBLISH');
				$state_value = 0;

				if ($this->item->state == 1):
					$state_string = Text::_('COM_JTICKETING_PUBLISH');
					$state_value = 1;
				endif;
				?>
				<div class="controls"><?php echo $state_string; ?></div>
				<input type="hidden" name="jform[state]" value="<?php echo $state_value; ?>" />
				<?php
			else: ?>
				<div class="control-label">
					<?php echo $this->form->getLabel('state'); ?>
				</div>
				<div class="controls"><?php echo $this->form->getInput('state'); ?></div>
				<?php
			endif; ?>
		</div>
		<div class="control-group">
			<div class="control-label"><?php echo $this->form->getLabel('allow_view_attendee'); ?></div>
			<div class="controls"><?php echo $this->form->getInput('allow_view_attendee'); ?></div>
		</div>

		<div class="control-group">
			<div class="control-label"><?php echo $this->form->getLabel('access'); ?></div>
			<div class="controls"><?php echo $this->form->getInput('access'); ?></div>
		</div>
	</div>
	<div class="span6">
		<?php
		$integration = JT::getIntegration(true);  
		// Check if integration is 2 for com_jticketing
		if ($integration == 2): ?>
		<?php
			echo $this->form->renderField('recurring_type');
			echo $this->form->renderField('repeat_interval');
			echo $this->form->renderField('repeat_via');
			echo $this->form->renderField('repeat_count');
			echo $this->form->renderField('repeat_until');
		?>
		<div class="control-group">
			<div class="control-group d-flex align-items-center gap-3">
				<?php echo $this->form->renderField('eventstart_date'); ?>
			</div>
			<div>
				<?php echo $this->form->renderField('start_time'); ?>
			</div>
		</div>
		<div class="control-group">
			<div class="control-group d-flex align-items-center gap-3">
				<?php echo $this->form->renderField('eventend_date'); ?>
			</div>
			<div>
				<?php echo $this->form->renderField('end_time'); ?>
			</div>
		</div>
		<?php endif; ?>
		<?php

			$OnlineEvents = $this->params->get('enable_online_events');

			if ($OnlineEvents == 1)
			{	?>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('online_events'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('online_events'); ?></div>
				</div>
			<?php
			}

			if($this->item->venue == 0)
			{
		?>
			<div class="alert alert-info" id="note_id">
				<?php echo Text::sprintf('COM_VENUE_LOCATION_NOTE');?>
			</div>
		<?php
			}
		?>
		<div class="control-group" id="venue_id">

			<div class="control-label"><?php echo $this->form->getLabel('venue'); ?></div>
			<div class="controls"><?php echo $this->form->getInput('venue'); ?>
			<div id="ajax_loader"></div>

			</div>
		</div>

		<?php
			if ($OnlineEvents == 1)
			{	?>
				<div class="control-group" id="venuechoice_id">
					<div class="control-label"><?php echo $this->form->getLabel('venuechoice'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('venuechoice'); ?></div>
					<input type="hidden" name="event_url" class="event_url" id="event_url" value=""/>
					<input type="hidden" name="event_sco_id" class="event_sco_id" id="event_sco_id" value=""/>
				</div>
				<div class="control-group" id="existingEvent">
				<?php

				$exitingField = $this->form->getField('existing_event');

				if ($this->event->getId())
				{
					$exitingField->__set('value', $this->event->getOnlineEventId());
					$exitingField->addOption($this->event->getTitle(), ['value' => $this->event->getOnlineEventId(), 'selected' => 'selected']);
				}

				?>
					<div class="control-label">
						<?php echo $this->form->getLabel('existing_event'); ?>
						<span class="star">&nbsp;*</span>
					</div>
					<div class="controls"><?php echo $exitingField->__get('input'); ?></div>
				</div>
		<?php
			}?>
		<div class="control-group" id="event-location">

			<div class="control-label"><?php echo $this->form->getLabel('location'); ?></div>
			<div class="controls">
				<?php echo $this->form->getInput('location');
					echo $this->form->renderField('longitude');
					echo $this->form->renderField('latitude');
				?>

			</div>

		</div>

		<?php
		if ($this->tncForCreateEvent == 1)
		{
			?>
			<div class="control-group">
				<div class="checkbox af-d-flex af-ml-15">
					<?php

					$checked = '';

					if (!empty($this->item->privacy_terms_condition))
					{
						$checked = 'checked';
					}

					$link = Route::_(Uri::root() . "index.php?option=com_content&view=article&id=" . $this->eventArticle . "&tmpl=component");
						?>
					<label for="accept_privacy_term">
						<input class="af-mb-10" type="checkbox" name="accept_privacy_term" id="accept_privacy_term" size="30" <?php echo $checked ?> />
						<?php
						$modalConfig = array('width' => '800px', 'height' => '300px', 'modalWidth' => 80, 'bodyHeight' => 70);
						$modalConfig['url'] = $link;
						$modalConfig['title'] = Text::_('COM_JTICKETING_TERMS_CONDITION_EVENT');
						echo HTMLHelper::_('bootstrap.renderModal', 'jtTnC', $modalConfig);
						?>
						<a data-bs-target="#jtTnC" data-bs-toggle="modal" class="af-relative"><?php echo Text::_('COM_JTICKETING_TERMS_CONDITION_EVENT');?>
						</a>
						<span class="star">&nbsp;*</span>
					</label>
				</div>
			</div>
		<?php
		}
		?>
	</div>
</div>

<?php
$this->googleMapLink = 'https://maps.googleapis.com/maps/api/js?libraries=places&key=' . $this->googleMapApiKey;
Factory::getDocument()->addScript(!empty($this->googleMapLink) ? $this->googleMapLink : '');

Factory::getDocument()->addScriptDeclaration("
	// Google Map autosuggest  for location
	var googleMapApiKey = '" . $this->googleMapApiKey . "';
	var googleMapLink = '" . $this->googleMapLink . "';
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