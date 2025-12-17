<?php
/**
 * @package     JTicketing
 * @subpackage  com_jticketing
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license     GNU GPLv2 <http://www.gnu.org/licenses/old-licenses/gpl-2.0.html>
 */

// No direct access
defined('_JEXEC') or die;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Layout\LayoutHelper;

HTMLHelper::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jticketing'. '/helpers/html');
HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');

$path = JPATH_SITE . '/components/com_jticketing/helpers/main.php';

if (!class_exists('Jticketingmainhelper'))
{
	JLoader::register('Jticketingmainhelper', $path);
	JLoader::load('Jticketingmainhelper');
}

// Call helper function
JticketingHelper::getLanguageConstant();

$editId         = $this->item->id;
$existingParams = $this->item->params;
$existingScoUrl = '';
?>

<form action="<?php echo Route::_('index.php?option=com_jticketing&layout=edit&id=' . (int) $this->item->id); ?>" method="post" enctype="multipart/form-data" name="adminForm" id="venue-form" class="form-validate">

	<div class="form-horizontal">
		<?php
		echo HTMLHelper::_('bootstrap.startTabSet', 'myTab', array('active' => 'details'));

			echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'details', Text::_('COM_JTICKETING_VENUE_TAB_DETAILS', true));

			if ($this->googleMapApiKey == null)
			{
				?>
				<div class="alert alert-info">
					<?php echo Text::_('COM_JTICKETING_CONFIGURE_API_KEY');?>
				</div>
				<?php
			}
			?>
			<br>
			<div class="form-horizontal">
				<fieldset class="adminform bs5Loaded">
					<div class="col-sm-12">
						<input type="hidden" name="jform[id]" id="venue_id" value="<?php echo $this->item->id; ?>" />
						<input type="hidden" name="jform[ordering]" value="<?php echo $this->item->ordering; ?>" />
						<input type="hidden" name="jform[state]" value="<?php echo $this->item->state; ?>" />
						<input type="hidden" id="venue_params" name="params" value=""/>
						<?php
						if (empty($existingSco))
						{
							?>
							<input type="hidden" id="jform_seminar_room" name="jform[seminar_room]" value=""/>
							<input type="hidden" id="jform_seminar_room_id" name="jform[seminar_room_id]" value=""/>
							<?php
						}
						else
						{
							?>
							<input type="hidden" id="jform_seminar_room" name="jform[existingScoUrl]"
								value="<?php echo $existingSco; ?>"/>
							<input type="hidden" id="jform_seminar_room" name="jform[seminar_room]"
								value="<?php echo $existingScoUrl; ?>"/>
							<?php
						}?>
						<?php
						echo $this->form->renderField('created_by');
						echo $this->form->renderField('name');
						echo $this->form->renderField('alias');
						echo $this->form->renderField('state');
						echo $this->form->renderField('venue_category');

						if ($this->EnableOnlineEvents == 1)
						{
							echo $this->form->renderField('online');
							echo $this->form->renderField('online_provider');
						}
						?>
						<?php echo $this->form->renderField('privacy'); ?>
						<div id="provider_html"></div>
							<div id="jform_offline_provider">
								<?php
									echo $this->form->renderField('address');
									echo $this->form->renderField('longitude');
									echo $this->form->renderField('latitude');
								?>
							</div>
						<?php
							echo $this->form->renderField('seats_capacity');
							echo $this->form->renderField('capacity_count');
						?>
					</div>

					<div class="col-sm-12">
						<div class="control-group basicDetails__desc">
							<div class="control-label">
								<?php echo $this->form->getLabel('description'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('description'); ?>
							</div>
						</div>
					</div>
				</fieldset>
			</div>

			<?php
			echo HTMLHelper::_('bootstrap.endTab');

			if ($this->showVenueGallery)
			{
				echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'gallery', Text::_('COM_JTICKETING_VENUE_GALLERY', true));
				?>
					<br>
					<div class="well">
						<div class="control-group">
							<div class="control-label"><?php echo $this->form->getLabel('gallery_file'); ?></div>
							<div class="controls">
								<?php echo $this->form->getInput('gallery_file'); ?>
								<?php HTMLHelper::_('jquery.token'); ?>
							</div>
						</div>

						<div class="control-group">
							<div class="af-d-inline-block">
								<?php echo $this->form->renderField('gallery_link'); ?>
							</div>
							<div class="af-ml-15 gallary__validateBtn af-d-inline-block">
								<input type="button" class="validate_video_link btn btn-secondary"
									onclick="tjMediaFile.validateFile(this,1, <?php echo $this->isAdmin;?>, 'venue')"
									value="<?php echo Text::_('COM_TJMEDIA_ADD_VIDEO_LINK');?>">
									<?php HTMLHelper::_('jquery.token'); ?>
							</div>
						</div>
					</div>

					<div class="subform-wrapper">
						<ul class="gallary__media thumbnails row" style="list-style-type: none;">
							<li class="gallary__media--li hide col-md-3">
								<i class="close" href="javascript:;"
									onclick="tjMediaFile.tjMediaGallery.deleteMedia(this, <?php echo $this->isAdmin;?>, <?php if (!empty($this->item->id)) { echo $this->item->id; } else { echo "0";}?>, 'venue'); return false;">×</i>
								<?php HTMLHelper::_('jquery.token'); ?>
								<input type="hidden" name="jform[gallery_file][media][]"
									class="media_field_value" value="">
								<div class="thumbnail"></div>
							</li>
						 </ul>
					</div>
				<?php
				echo HTMLHelper::_('bootstrap.endTab');
			}

		echo LayoutHelper::render('joomla.edit.params', $this);
		echo HTMLHelper::_('bootstrap.endTabSet');
		?>

		<input type="hidden" name="task" value="" />
		<?php echo HTMLHelper::_('form.token'); ?>
	</div>
</form>

<?php
Factory::getDocument()->addScript(!empty($this->googleMapLink) ? $this->googleMapLink : '');

Factory::getDocument()->addScriptDeclaration('
	var googleMapApiKey = "' .  $this->googleMapApiKey . '";
	var googleMapLink = "' .  $this->googleMapLink . '";
	var mediaSize = "' .  $this->mediaSize . '";
	var galleryImage = "' .  $this->venueGalleryImage . '";
	var mediaGallery = ' .  $this->mediaGalleryObj . ';
	jtAdmin.venue.initVenueJs();
	var editId = "' .  $editId . '";
	var getValue = "' . $this->form->getValue("online_provider") . '";
	Joomla.submitbutton = function(task) {jtAdmin.venue.venueSubmitButton(task);}
');
?>
