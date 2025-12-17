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

if (isset($this->item->gallery))
{
	$eventVideoData = array();
	$eventImageData = array();

	for ($i = 0; $i <= count($this->item->gallery); $i++)
	{
		if (isset($this->item->gallery[$i]->type))
		{
			$eventContentType = substr($this->item->gallery[$i]->type, 0, 5);

			if ($eventContentType == 'image')
			{
				$eventImageData[$i] = $this->item->gallery[$i];
			}
			elseif ($eventContentType == 'video')
			{
				$eventVideoData[$i] = $this->item->gallery[$i];
			}
		}
	}

	if (count($eventVideoData) > 0)
	{
		?>
		<div class="row af-my-15">
			<div class="col-xs-12 col-sm-9 videosText">
				<h5><?php echo Text::_('COM_JTICKETING_EVENT_VIDEOS');?></h5>
			</div>
			<?php
			if (count($eventVideoData) > 0 && count($eventImageData) > 0)
			{
				?>
				<div class="col-xs-12 col-sm-3 gallary-filters">
					<select id="gallary_filter" class="form-select">
						<option value="0"><?php echo Text::_('COM_JTICKETING_EVENT_TYPE');?></option>
						<option value="1"><?php echo Text::_('COM_JTICKETING_EVENT_GALLERY_VIDEOS');?></option>
						<option value="2"><?php echo Text::_('COM_JTICKETING_EVENT_GALLERY_IMAGES');?></option>
					</select>
				</div>
				<?php
			}
			?>
		</div>
		<div id="videos">
			<?php
			if (!empty($eventVideoData))
			{
				?>
				<div id="eventVideo">
					<div class="jtVideo">
						<div class="media row" id="jt_video_gallery">
							<?php
							foreach ($eventVideoData as $eventVideo)
							{
								$eventVideoType = substr($eventVideo->type, 6);
								$videoId  = JticketingMediaHelper::videoId($eventVideoType, $eventVideo->media);
								$srclink = "https://www.youtube.com/embed/" . $videoId;
								$thumbSrc = JticketingMediaHelper::videoThumbnail($eventVideoType, $videoId);

								$modalConfig = array('width' => '800px', 'height' => '300px', 'modalWidth' => 80, 'bodyHeight' => 70);
								$modalConfig['url'] = $srclink;
								$modalConfig['title'] = Text::_('COM_JTICKETING_VENUE_VIDEOS');
								echo HTMLHelper::_('bootstrap.renderModal', 'mediaGalleryVideo' . $eventVideo->id, $modalConfig);
								?>
								<div class="col-md-3 col-sm-4 col-xs-6 jt_gallery_image_item bg-center af-bg-faded text-center af-bg-cover af-bg-repn">
									<a data-bs-target="#mediaGalleryVideo<?php echo $eventVideo->id;?>" data-bs-toggle="modal" class="af-d-block af-relative ">
										<img src="<?php echo Uri::root(true) . '/media/com_jticketing/images/play_icon.png';?>"class="play_icon center-xy af-absolute"/>
										<img src="<?php echo $thumbSrc; ?>" width="100%"/>
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

	if (count($eventImageData) > 0)
	{
		?>
		<div id="images" class="af-mt-20">
			<div class="row">
				<div class="col-xs-12 imagesText">
					<h5><?php echo Text::_('COM_JTICKETING_EVENT_IMAGES');?></h5>
				</div>
			</div>
			<?php
			if (!empty($eventImageData))
			{
				?>
				<div id="eventImages">
					<div class=>
						<div class="media" id="jt_image_gallery">
							<div class="row popup-gallery-media">
								<?php
								foreach ($eventImageData as $eventImage)
								{
									$img_path = $eventImage->{$this->params->get('front_event_gallery_view')};
									?>
									<div class="col-md-3 col-sm-4 col-xs-6 af-mb-15 jt_image_item">
										<a href="<?php echo $img_path;?>" title="" class="" >
											<div class="jt-image-gallery-inner bg-center af-bg-faded text-center af-bg-cover af-bg-repn"
											style="background-image: url('<?php echo $img_path;?>');
											background-size: contain; background-position: center;">
											</div>
										</a>
									</div>
								<?php
								}?>
							</div>
						</div>
					</div>
				</div>
				<?php
			}
			?>
		</div>
		<?php
	}
}

Factory::getDocument()->addScriptDeclaration("
	jQuery(document).ready(function()
	{
		jtSite.event.eventImgPopup('popup-gallery-media');
		jtSite.event.onChangefun();
	});"
);
