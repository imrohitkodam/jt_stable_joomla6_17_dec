<?php
/**
 * @package     TJCertificate
 * @subpackage  com_tjcertificate
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2020 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
$document = Factory::getDocument();

$sharingOptions = $this->params->get('sharing_option');
?>
<!-- Social Sharing button start-->
<?php if ($this->params->get('social_sharing'))
{
	if ($this->params->get('social_sharing_type') === 'addthis')
	{
		$pid = $this->params->get('addthis_publishid');

		if (!empty($pid))
		{
			$add_this_js = 'http://s7.addthis.com/js/300/addthis_widget.js';
			$document->addScript($add_this_js);
			$add_this_share = '
			<!-- AddThis Button BEGIN -->
			<div class="addthis_toolbox addthis_default_style">
			<a class="addthis_button_facebook_like" fb:like:layout="button_count" class="addthis_button" addthis:url="' . $this->certificateUrl . '"></a>
			<a class="addthis_button_google_plusone" g:plusone:size="medium" class="addthis_button" addthis:url="' . $this->certificateUrl . '"></a>
			<a class="addthis_button_tweet" class="addthis_button" addthis:url="' . $this->certificateUrl . '"></a>
			<a class="addthis_button_pinterest_pinit" class="addthis_button" addthis:url="' . $this->certificateUrl . '"></a>
			<a class="addthis_counter addthis_pill_style" class="addthis_button" addthis:url="' . $this->certificateUrl . '"></a>
			</div>
			<script type="text/javascript">
				var addthis_config ={ pubid: "' . $pid . '"};
			</script>
			<script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js#pubid="' . $pid . '"></script>
			<!-- AddThis Button END -->';

			// Output all social sharing buttons
			echo' <div id="rr" style="">
				<div class="social_share_container">
				<div class="social_share_container_inner">'
				. $add_this_share .
				'</div>
			</div>
			</div>
			';
		}
	}
	else
	{
	?>
		<div class="share" id="share-btn-grp">
			<ul class="list-unstyled">
			<?php 
			if (in_array("facebook", $sharingOptions))
			{
			?>
				 <li>
					<a id="fb" href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode($this->certificateUrl);?> "target="_blank" >
						<i class="fa fa-facebook-square fa-2x" aria-hidden="true"></i>
					</a>
				</li>
			<?php 
			} ?>
			<?php 
			if (in_array("linkedin", $sharingOptions))
			{
			?>
				<li>
					<a href="https://www.linkedin.com/shareArticle?mini=true&url=<?php echo urlencode($this->certificateUrl);?>&title=<?php echo urlencode($this->item->title);?>" target="_blank" ><i class="fa fa-linkedin-square fa-2x" aria-hidden="true"></i></a>
				</li>
			<?php 
			} ?>
			<?php
			if (in_array("twitter", $sharingOptions))
			{
			?>
				<li>
					<a href="https://twitter.com/intent/tweet?url=<?php echo urlencode($this->certificateUrl);?>&text=<?php echo urlencode($this->item->title);?>" target="_blank">
						<i class="fa fa-twitter-square fa-2x" aria-hidden="true"></i>
					</a>
				</li>
			<?php
			}
			?>
			<?php
			if (in_array("whatsapp", $sharingOptions))
			{
				$deviceUrl = "";

				// Check Mobile devices
				if (preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]))
				{
					$deviceUrl = "https://wa.me/?text=" . urlencode($this->certificateUrl);
				}
				else
				{
					$deviceUrl = "https://web.whatsapp.com/send?text=" . urlencode($this->certificateUrl);
				}
				?>
					<li>
						<a href=<?php echo $deviceUrl; ?> target="_blank" >
							<i class="fa fa-whatsapp fa-2x" aria-hidden="true" title="Share on Whatsapp"></i>
						</a>
					</li>
				<?php
			}
			?>
				<li>
					<a id="copyurl" data-toggle="popover" data-placement="bottom" data-alt-url="<?php echo Uri::getInstance()->toString();?>" data-content="Copied!" onclick="certificateImage.copyUrl('copyurl');" title="<?php echo Text::_('COM_TJCERTIFICATE_CERTIFICATE_URL_COPY');?>">
					<i class="fa fa-clipboard fa-2x" aria-hidden="true"></i>
					</a>
				</li>
			</ul>
		</div>
<?php 
	}
} ?>
<!-- Social Sharing button end-->
