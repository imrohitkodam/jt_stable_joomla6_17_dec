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

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;

HTMLHelper::_('bootstrap.renderModal', 'a.modal');

$document = Factory::getDocument();
HTMLHelper::_('stylesheet', 'media/com_jticketing/vendors/css/magnific-popup.css');
HTMLHelper::_('script', 'media/com_jticketing/vendors/js/jquery.magnific-popup.min.js');

if (count($this->item->venueVideoData) > 0)
{
	?>
	<div class="row af-my-15">
		<div class="col-xs-12 col-sm-9 videosText">
			<h5><?php echo Text::_('COM_JTICKETING_VENUE_VIDEOS');?></h5>
		</div>
	</div>
	<div class="row af-my-15">
		<div class="col-xs-12 col-sm-9 videosText">
			<h5><?php echo Text::_('COM_JTICKETING_VENUE_VIDEOS');?></h5>
		</div>
		<?php
		if (count($this->item->venueVideoData) > 0 && count($this->item->venueImageData) > 0)
		{
			?>
			<div class="col-xs-12 col-sm-3 gallary-filters">
				<select id="venue_gallary_filter">
					<option value="0"><?php echo Text::_('COM_JTICKETING_VENUE_TYPE');?></option>
					<option value="1"><?php echo Text::_('COM_JTICKETING_VENUE_VIDEOS');?></option>
					<option value="2"><?php echo Text::_('COM_JTICKETING_VENUE_IMAGES');?></option>
				</select>
			</div>
			<?php
		}?>
	</div>
	<div id="venue_videos">
		<?php
		if (!empty($this->item->venueVideoData))
		{
			?>
			<div id="venueVideo" class="row jtVideo">
				<div class="media" id="jt_video_gallery">
					<?php
					foreach ($this->item->venueVideoData as $venueVideo)
					{
						$venueVideoType = substr($venueVideo->type, 6);
						$videoId  = JticketingMediaHelper::videoId($venueVideoType, $venueVideo->media);
						$srclink = "https://www.youtube.com/embed/" . $videoId;
						$thumbSrc = JticketingMediaHelper::videoThumbnail($venueVideoType, $videoId);

						$modalConfig = array('width' => '800px', 'height' => '300px', 'modalWidth' => 80, 'bodyHeight' => 70);
						$modalConfig['url'] = $srclink;
						$modalConfig['title'] = Text::_('COM_JTICKETING_VENUE_VIDEOS');
						echo HTMLHelper::_('bootstrap.renderModal', 'venueGalleryVideo' . $venueVideo->id, $modalConfig);
						?>
						<div class="col-md-3 col-sm-4 col-xs-6 jt_gallery_image_item bg-center af-bg-faded text-center af-bg-cover af-bg-repn">
							<a data-target="#venueGalleryVideo<?php echo $venueVideo->id;?>" data-toggle="modal" class="af-d-block af-relative ">
								<img src="<?php echo Uri::root(true) . '/media/com_jticketing/images/play_icon.png';?>"class="play_icon center-xy af-absolute"/>
								<img src="<?php echo $thumbSrc; ?>" width="100%"/>
							</a>
						</div>
						<?php
					}
					?>
				</div>
			</div>
			<?php
		}
		?>
	</div>
	<?php
}

if (count($this->item->venueImageData) > 0)
{
	?>
	<div id="venue_images" class="af-mt-20">
		<div class="row">
			<div class="col-xs-12 imagesText">
				<h5><?php echo Text::_('COM_JTICKETING_VENUE_IMAGES');?></h5>
			</div>
		</div>

		<?php
		if (!empty($this->item->venueImageData))
		{
			?>
			<div id="venueImages" class="row">
				<div class="media" id="jt_image_gallery">
					<div class="popup-gallery-venue">
						<?php
						foreach ($this->item->venueImageData as $venueImage)
						{
							$img_path = $venueImage->media;
							?>
							<div class="col-md-3 col-sm-4 col-xs-6 af-mb-15 jt_image_item">
								<a href="<?php echo $img_path;?>" title="" class="" >
									<div class="jt-image-gallery-inner bg-center af-bg-faded text-center af-bg-cover af-bg-repn" style="background-image: url('<?php echo $img_path;?>'); background-size: contain; background-position: center;">
									</div>
								</a>
							</div>
							<?php
						}
						?>
					</div>
				</div>
			</div>
			<?php
		}
		?>
	</div>
	<?php
}

Factory::getDocument()->addScriptDeclaration("
	jQuery(document).ready(function()
	{
	    jtSite.event.eventImgPopup('popup-gallery-venue');
		jtSite.venue.onChangefun();
	});"
);
