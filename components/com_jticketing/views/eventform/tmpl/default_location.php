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

/** @var $this JticketingViewEventform */

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
?>

<div class="row af-mt-10">
	<!--Col-md-6 start-->
	<div class="col-md-6 col-xs-12">
		<div class="row">
			<div class="col-xs-12">
				<?php echo $this->form->getLabel('image'); ?>
				<?php echo $this->form->getInput('image');?>
					<div class="alert alert-info">
					<?php echo Text::sprintf('COM_JTICKETING_MAIN_IMAGE_SIZE', $this->params->get('large_width'), $this->params->get('large_height')); ?>
				</div>
			</div>
			<div class="col-sm-6 col-xs-12 af-mb-10">
				<?php
				$mediaId = '';
				$hideDiv = '';
				$this->eventImage = Route::_(Uri::base() . 'media/com_jticketing/images/default-event-image.png');

				if (isset($this->item->image->id))
				{
					$hideDiv = '';
					$this->eventImage = $this->item->image->media_m;
					$mediaId = $this->item->image->id;
				}
				?>
				<ul class="list-unstyled">
					<li class="event_media">
						<input type="hidden" name="jform[image][new_image]" id="jform_event_image" value="<?php echo $mediaId;?>" />
						<input type="hidden" name="jform[image][old_image]" id="jform_event_old_image" value="" />
						<img src="<?php echo $this->eventImage ?>" id="uploaded_media" class="img-responsive">
					</li>
				</ul>
			</div>
		</div>
		<div class="row">
			<div class="col-xs-12">
				<?php echo $this->form->getLabel('coverImage'); ?>
				<?php echo $this->form->getInput('coverImage');?>
					<div class="alert alert-info">
					<?php echo Text::sprintf('COM_JTICKETING_MAIN_IMAGE_SIZE', $this->params->get('large_width'), $this->params->get('large_height')); ?>
				</div>
			</div>
			<div class="col-sm-6 col-xs-12 af-mb-10">
				<?php
				$mediaId = '';
				$hideDiv = '';
				$this->eventImage = Route::_(Uri::base() . 'media/com_jticketing/images/default-event-image.png');

				if (isset($this->item->coverImage->id))
				{
					$hideDiv = '';
					$this->eventImage = !empty($this->item->coverImage->media_m) ? $this->item->coverImage->media_m : '';
					$mediaId = $this->item->coverImage->id;
				}
				?>
				<ul class="list-unstyled">
					<li class="event_media">
						<input type="hidden" name="jform[coverImage][new_image]" id="jform_event_coverImage" value="<?php echo
						$mediaId;?>" />
						<input type="hidden" name="jform[coverImage][old_image]" id="jform_event_cover_old_image" value="" />
						<img src="<?php echo $this->eventImage ?>" id="uploaded_media_cover" class="img-responsive media_image_width">
					</li>
				</ul>
			</div>
		</div>

		<div class="row">
			<div class="col-xs-12 af-mb-10">
					<?php echo $this->form->getLabel('meta_data');?>
					<?php echo $this->form->getInput('meta_data'); ?>
			</div>

			<div class="col-xs-12 af-mb-10">
					<?php echo $this->form->getLabel('meta_desc');?>
					<?php echo $this->form->getInput('meta_desc'); ?>
			</div>
		</div>
	</div>
	<!--Col-md-6 End-->
	<!--Col-md-6 start-->
	<div class="col-md-6 col-xs-12">
		<div class="basicDetails__desc">
			<div class="af-mb-10">
				<?php echo $this->form->getLabel('long_description');?>
				<?php echo $this->form->getInput('long_description');?>
			</div>
		</div>
	</div>
	<!--Col-md-6 End-->

	<?php
	if ($this->enableCertification)
	{
		?>
		<!--Col-md-6 start-->
		<div class="col-md-6 col-xs-12">
			<div class="af-mb-10 eventDateTime">
				<p class="alert alert-info"><?php echo Text::_('COM_JTICKETING_FORM_LBL_EVENT_CERTIFICATE_INFO'); ?></p>
				<?php echo $this->form->getLabel('certificate_template');?>
				<?php echo $this->form->getInput('certificate_template');?>
			</div>

			<div class="af-mb-10 eventDateTime">
				<?php echo $this->form->getLabel('certificate_expiry');?>
				<?php echo $this->form->getInput('certificate_expiry');?>
			</div>
		</div>
		<!--Col-md-6 End-->
		<?php
	}
	?>
</div>
