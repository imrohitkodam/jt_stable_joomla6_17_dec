<?php
/**
 * @package     JTicketing
 * @subpackage  com_jticketing
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
?>

<div class="row form-horizontal-desktop">
	<div class="col-sm-12">
		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('image');?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('image'); ?>
				<p class="alert alert-info">
					<?php echo Text::sprintf('COM_JTICKETING_MAIN_IMAGE_SIZE', $this->params->get('large_width'), $this->params->get('large_height')); ?>
				</p>
			</div>
		</div>
		<div class="control-group">
			<div>
				<?php
					$mediaId = '';
					$this->eventImage = Route::_(Uri::root() . 'media/com_jticketing/images/default-event-image.png');

					if (isset($this->item->image->id))
					{
						$hideDiv = '';
						$this->eventImage = !empty($this->item->image->media) ? $this->item->image->media : '';
						$mediaId = $this->item->image->id;
					}
				?>
				<ul class="list-unstyled container">
					<li class="event_media row">
						<input type="hidden" name="jform[image][new_image]" id="jform_event_image" value="<?php echo $mediaId;?>" />
						<input type="hidden" name="jform[image][old_image]" id="jform_event_old_image" value="" />
						<img src="<?php echo $this->eventImage ?>" id="uploaded_media" class="img-responsive media_image_width">
					</li>
				</ul>
			</div>
		</div>

		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('coverImage');?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('coverImage'); ?>
				<p class="alert alert-info">
					<?php echo Text::sprintf('COM_JTICKETING_MAIN_IMAGE_SIZE', $this->params->get('large_width'), $this->params->get('large_height')); ?>
				</p>
			</div>
		</div>
		<div class="control-group">
			<div>
				<?php
					$mediaId = '';
					$this->eventImage = Route::_(Uri::root() . 'media/com_jticketing/images/default-event-image.png');

					if (isset($this->item->coverImage->id))
					{
						$hideDiv = '';
						$this->eventImage = $this->item->coverImage->media;
						$mediaId = $this->item->coverImage->id;
					}
				?>
				<ul class="list-unstyled container">
					<li class="event_media row">
						<input type="hidden" name="jform[coverImage][new_image]" id="jform_event_coverImage" value="<?php echo $mediaId;?>" />
						<input type="hidden" name="jform[coverImage][old_image]" id="jform_event_cover_old_image" value="" />
						<img src="<?php echo $this->eventImage ?>" id="uploaded_media_cover" class="img-responsive media_image_width">
					</li>
				</ul>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label"><?php echo $this->form->getLabel('meta_data'); ?></div>
			<div class="controls"><?php echo $this->form->getInput('meta_data'); ?></div>
		</div>

		<div class="control-group">
			<div class="control-label"><?php echo $this->form->getLabel('meta_desc'); ?></div>
			<div class="controls"><?php echo $this->form->getInput('meta_desc'); ?></div>
		</div>
	</div>
	<div class="col-sm-12">
		<div class="control-group basicDetails__desc">
			<div class="control-label">
				<?php echo $this->form->getLabel('long_description'); ?>
			</div>
			<div class="controls pull-right">
				<?php echo $this->form->getInput('long_description'); ?>
			</div>
		</div>
		<?php
		if ($this->enableCertification)
		{
			?>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('certificate_template'); ?></div>
				<div class="controls">
					<?php echo $this->form->getInput('certificate_template'); ?>
					<p class="alert alert-info"><?php echo Text::_('COM_JTICKETING_FORM_LBL_EVENT_CERTIFICATE_INFO'); ?></p>
				</div>
			</div>

			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('certificate_expiry'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('certificate_expiry'); ?></div>
			</div>
			<?php
		}
		?>
	</div>
</div>

