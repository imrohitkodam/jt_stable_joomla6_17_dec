 <?php


 use Joomla\CMS\Factory;
 use Joomla\CMS\HTML\HTMLHelper;
 use Joomla\CMS\Language\Text;
 use Joomla\CMS\Layout\LayoutHelper;
 use Joomla\CMS\Router\Route;
 use Joomla\CMS\Session\Session;
 use Joomla\CMS\Access\Access;

 require_once JPATH_SITE . "/components/com_jticketing/includes/jticketing.php";

 $item = $displayData;
 $user       = Factory::getUser();
 $userName   = $user->get('name');
 $userEmail  = $user->get('email');
 ?>

 <nav id="nav-tool" class="navbar navbar-inverse navbar-fixed-top">
	<div class="container">
		<div class="navbar-header">
		</div>
		<div id="navbar" class="websdktest">
			<form class="navbar-form navbar-right" id="meeting_form">
				<div class="form-group">
					<input type="hidden" name="display_name" id="display_name" value="<?php echo $userName;?>" maxLength="100"
					placeholder="Name" class="form-control" required>
				</div>
				<div class="form-group">
					<input type="hidden" name="meeting_number" id="meeting_number" value="<?php echo $item->params['zoom']['id']; ?>" maxLength="200"
					style="width:150px" placeholder="Meeting Number" class="form-control" required>
				</div>
				<div class="form-group">
					<input type="hidden" name="meeting_pwd" id="meeting_pwd" value="<?php echo $item->params['zoom']['password']; ?>" style="width:150px"
					maxLength="32" placeholder="Meeting Password" class="form-control">
				</div>
				<div class="form-group">
					<input type="hidden" name="meeting_email" id="meeting_email" value="<?php echo $userEmail; ?>" style="width:150px"
					maxLength="32" placeholder="Email option" class="form-control">
				</div>

				<div class="form-group" style="display:none">
					<select id="meeting_role" class="sdk-select">
						<option value=0>Attendee</option>
						<option value=1>Host</option>
					</select>
				</div>
				<div class="form-group"style="display:none">
					<select id="meeting_china" class="sdk-select">
						<option value=0>Global</option>
						<option value=1>China</option>
					</select>
				</div>
				<div class="form-group" style="display: none;">
					<select id="meeting_lang" class="sdk-select">
						<option value="en-US">English</option>
						<option value="de-DE">German Deutsch</option>
						<option value="es-ES">Spanish Español</option>
						<option value="fr-FR">French Français</option>
						<option value="jp-JP">Japanese 日本語</option>
						<option value="pt-PT">Portuguese Portuguese</option>
						<option value="ru-RU">Russian Русский</option>
						<option value="zh-CN">Chinese 简体中文</option>
						<option value="zh-TW">Chinese 繁体中文</option>
						<option value="ko-KO">Korean 한국어</option>
						<option value="vi-VN">Vietnamese Tiếng Việt</option>
						<option value="it-IT">Italian italiano</option>
					</select>
				</div>

				<input type="hidden" value="" id="copy_link_value" />
				<button type="submit" class="btn btn-primary" id="join_meeting" style="display: none;"><?php echo Text::_('PLG_TJEVENTS_ZOOM_MEETING_JOIN'); ?></button>
				<button type="hidden" style="display:none" class="btn btn-primary" id="clear_all">Clear</button>
				<button type="hidden" style="display:none" link="" onclick="window.copyJoinLink('#copy_join_link')"
				class="btn btn-primary" id="copy_join_link">Copy Direct join link</button>
				<input type="hidden" id="eventId" value="<?php echo $item->id;?>">
				<input type="hidden" id="sdk_key" value="<?php echo $item->meetingView['sdk_key']?>">
				<input type="hidden" id="sdk_secret" value="<?php echo $item->meetingView['sdk_secret']?>">


			</form>
		</div>
		<!--/.navbar-collapse -->
	</div>
</nav>
<div id="zoomframeDiv">
   <iframe id="zoomframe" src width="100%" height="800px"allow="camera; microphone" style="display:none;">
   </iframe>
</div>

<script>
	// document.getElementById('show-test-tool-btn').addEventListener("click", function (e) {
	// 	var textContent = e.target.textContent;
	// 	if (textContent === 'Show') {
	// 		document.getElementById('nav-tool').style.display = 'block';
	// 		document.getElementById('show-test-tool-btn').textContent = 'Hide';
	// 	} else {
	// 		document.getElementById('nav-tool').style.display = 'none';
	// 		document.getElementById('show-test-tool-btn').textContent = 'Show';
	// 	}
	// })
</script>

<script src="plugins/tjevents/zoom/activity/zoom-meetingsdk-web/CDN/js/react.min.js"></script>
<!--https://source.zoom.us/2.11.0/lib/vendor/react-dom.min.js-->
<script src="plugins/tjevents/zoom/activity/zoom-meetingsdk-web/CDN/js/react-dom.min.js"></script>
<!--https://source.zoom.us/2.11.0/lib/vendor/redux.min.js-->
<script src="plugins/tjevents/zoom/activity/zoom-meetingsdk-web/CDN/js/redux.min.js"></script>
<!--https://source.zoom.us/2.11.0/lib/vendor/redux-thunk.min.js-->
<script src="plugins/tjevents/zoom/activity/zoom-meetingsdk-web/CDN/js/redux-thunk.min.js"></script>
<!--https://source.zoom.us/2.11.0/lib/vendor/lodash.min.js-->
<script src="plugins/tjevents/zoom/activity/zoom-meetingsdk-web/CDN/js/lodash.min.js"></script>
<!--https://source.zoom.us/zoom-meeting-2.11.0.min.js-->
<script src="plugins/tjevents/zoom/activity/zoom-meetingsdk-web/CDN/js/zoommeeting.js"></script>
<script src="plugins/tjevents/zoom/activity/zoom-meetingsdk-web/CDN/js/tool.js"></script>
<script src="plugins/tjevents/zoom/activity/zoom-meetingsdk-web/CDN/js/vconsole.min.js"></script>
<script src="plugins/tjevents/zoom/activity/zoom-meetingsdk-web/CDN/js/index.js"></script>

<script>
	// Hide meeting view after meeting over
	jQuery("#zoomframe").on("load", function () {
	  if(jQuery("#zoomframe").contents().find("body").length) {

		if (!jQuery("#zoomframe").contents().find(".root-inner").length) {
			jQuery("#zoomframe").css('display','none');
		}
	}
})
	// Scroll to meeting
	jQuery("#join_meeting").click(function() {
		setTimeout(function(){
		jQuery('html,body').animate({
		scrollTop: jQuery("#zoomframeDiv").offset().top},
		'slow');
	   
		},400)
	});
</script>