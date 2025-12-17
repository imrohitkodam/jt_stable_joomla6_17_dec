<?php
/**
 * @package    Jlike
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('bootstrap.renderModal');

define('JLIKE_DASHBORD_ICON_COMMENT', "icon-comment");
define('JLIKE_DASHBORD_ICON_THUMBS_UP', "icon-thumbs-up");
define('JLIKE_DASHBORD_ICON_THUMBS_DOWN', "icon-thumbs-down");

HTMLHelper::_('script', 'administrator/components/com_jlike/assets/js/raphael.min.js');
HTMLHelper::_('script', 'administrator/components/com_jlike/assets/js/morris.min.js');
HTMLHelper::_('script', 'libraries/techjoomla/assets/js/houseKeeping.js');
HTMLHelper::_('script', 'https://www.google.com/jsapi');
HTMLHelper::_('stylesheet', 'administrator/components/com_jlike/assets/css/morris.css');
HTMLHelper::_('stylesheet', 'administrator/components/com_jlike/assets/css/dashboard.css');

$document = Factory::getDocument();
$document->addScriptDeclaration("var tjHouseKeepingView='dashboard';");

// Global icon constants.
define('JLIKE_DASHBORD_ICON_USERS', "icon-users");

// Load jQuery.
HTMLHelper::_('jquery.framework');

$iconclass     = ' statbox-icons-j30 ';
$strapperClass = '';
?>

<!--@S Migration code -->
<?php
if (!$this->checkMigrate)
{
	Factory::getDocument()->addScriptDeclaration('
		function migratelikes(success_msg, error_msg) {
			jQuery.ajax({
				url: "index.php?option=com_jlike&tmpl=component&task=migrateLikes",
				type: "POST",
				dataType: "json",
				timeout: 3500,
				error: function() {
					jQuery("#migrate_msg").css("display", "block");
					jQuery("#migrate_msg").addClass("alert alert-error");
					jQuery("#migrate_msg").text(error_msg);
				},
				beforeSend: function() {
					jQuery("#jlike-loading-image").show();
				},
				complete: function() {
					jQuery("#jlike-loading-image").hide();
				},
				success: function(response) {
					jQuery("#migrate_msg").css("display", "block");
					jQuery("#migrate_msg").addClass("alert alert-success");
					jQuery("#migrate_msg").text(success_msg);
					jQuery("#migrate_button").css("display", "none");
				}
			});
		}
	');
?>
<div class="well well-large center">
	<?php
		$limit_populate_link = Route::_(Uri::base() . 'index.php?option=com_jlike&tmpl=component&task=migrateLikes'); ?>
	<div class="alert" id="migrate_msg" style='display:none'></div>
	<div>
		<div class='jlike-loading-image' style="background: url('<?php echo Uri::root() . '/' . 'components' . '/' . 'com_jlike/assets/images/ajax-loading.gif'; ?>')
				no-repeat scroll 0 0 transparent;display:none;">
		</div>
		<button class="btn btn-success" style="margin-top:20px;" id="migrate_button" onclick="migratelikes('<?php echo Text::_('COM_JLIKE_MIGRATE_SUCCESS'); ?>',
				'<?php echo Text::_('COM_JLIKE_MIGRATE_ERROR'); ?>')">
			<?php echo Text::_('Migrate Old Likes data to Jlike'); ?>

		</button>
	</div>
</div>
<?php
} ?>
<!--@E Migration code -->


<form action="" id="adminForm" name="adminForm" method="post">

	<!--@S tj-dashboard div-->
	<div class="tj-dashboard">
		<?php if (!empty($this->sidebar)) { ?>
		<div id="j-sidebar-container">
			<?php echo $this->sidebar; ?>
		</div>
		<div id="j-main-container">
			<?php }
		else
		{ ?>
			<div id="j-main-container">
				<?php }?>
				<div class="row tjBs3">
					<div class="page-header row">
						<div class="col-sm-12 col-md-6">
							<!--DASHBOARD HEADING-->
							<h3><?php echo Text::_('COM_JLIKE_DASHBOARD_TITLE'); ?></h3>
						</div>
						<!--COMPANY LOGO-->
						<div class="col-sm-12 col-md-6">
							<a href="http://techjoomla.com/" class="float-end" taget="_blank">
								<img src="<?php echo Uri::base(); ?>components/com_jlike/images/techjoomla.png" alt="TechJoomla">
							</a>
						</div>
					</div>
					<div class="col-sm-12 col-md-8">
						<!--Periodic-Quick-stats-->
						<div class="row">

							<div class="statbox col-sm-6 col-md-3">
								<div class="statbox-overlay statbox-darkGreen">
									<div class="inline-block-class">
										<i class="<?php echo JLIKE_DASHBORD_ICON_COMMENT; echo $iconclass; ?>"></i>
									</div>
									<div class="inline-block-class parent-statbox-value">
										<div class="statbox-value">
											<?php echo !empty($this->data->comment_count) ? $this->data->comment_count : "0"; ?>
										</div>
									</div>
									<div>
										<span class="statbox-title"><?php echo Text::_('COM_JLIKE_TOTAL_COMMENTS'); ?></span>
									</div>
								</div>
							</div>

							<div class="statbox col-sm-6 col-md-3">
								<div class="statbox-overlay statbox-green">
									<div class="inline-block-class">
										<i class="<?php echo JLIKE_DASHBORD_ICON_THUMBS_UP; echo $iconclass; ?> "></i>
									</div>
									<div class="inline-block-class parent-statbox-value">
										<div class="statbox-value">
											<?php echo !empty($this->data->like_count) ? $this->data->like_count : "0"; ?>
										</div>
									</div>
									<div>
										<span class="statbox-title"><?php echo Text::_('COM_JLIKE_LIKES_COUNT'); ?></span>
									</div>
								</div>
							</div>

							<div class="statbox col-sm-6 col-md-3">
								<div class="statbox-overlay statbox-lightOrange">
									<div class="inline-block-class">
										<i class="<?php echo JLIKE_DASHBORD_ICON_THUMBS_DOWN; echo $iconclass; ?>"></i>
									</div>
									<div class="inline-block-class parent-statbox-value">
										<div class="statbox-value">
											<?php echo !empty($this->data->dislike_count) ? $this->data->dislike_count : "0"; ?>
										</div>
									</div>
									<div>
										<span class="statbox-title"><?php echo Text::_('COM_JLIKE_DISLIKE_COUNT'); ?></span>
									</div>
								</div>
							</div>

							<div class="statbox col-sm-6 col-md-3">
								<div class="statbox-overlay statbox-lightCyan">
									<div class="inline-block-class">
										<i class="<?php echo JLIKE_DASHBORD_ICON_USERS; echo $iconclass; ?>"></i>
									</div>
									<div class="inline-block-class parent-statbox-value">
										<div class="statbox-value">
											<?php echo !empty($this->data->users_count) ? $this->data->users_count : "0"; ?>
										</div>
									</div>
									<div>
										<span class="statbox-title"><?php echo Text::_('COM_JLIKE_USERS_COUNT'); ?></span>
									</div>
								</div>
							</div>
						</div>

						<div class="row">
							<div class="jlike-grath">
								<div class="pull-right">
									<div class="row">
										<div class="col-md-5">
										<span class="help-inline"><?php echo Text::_('COM_JLIKE_FROM_DATE'); ?></span>
										<?php echo HTMLHelper::_('calendar', $this->fromdate, "fromdate", "fromdate", '%Y-%m-%d', "class='form-control'"); ?>
										</div>
										<div class="col-md-5">
										<span class="help-inline"><?php echo Text::_('COM_JLIKE_TO_DATE'); ?></span>
										<?php echo HTMLHelper::_('calendar', $this->todate, "todate", "todate", '%Y-%m-%d', "class='input-small'"); ?>
										</div>
										<div class="col-md-2">
										<div class="help-inline">&nbsp;</div>
										<input type="button" class="btn btn-primary" value="Go" onclick="submitbutton();">
										</div>
									</div>
								</div>
								<div class="clearfix">&nbsp;</div>

								<hr class="hr hr-condensed">
								<div style="clear:both"></div>
								<div class="row">
									<div class="col-md-12 ">
										<div id="chart_div" style="height: 300px;"></div>
									</div>
								</div>
							</div>
						</div>
					</div>

					<div class="col-sm-6 col-md-4">
					<div class="row">
						<div class="col-sm-12">
						<?php
							$versionHTML = '<span class="label label-info">' .
								Text::_('COM_JLIKE_HAVE_INSTALLED_VER') . ': ' . $this->version .
							'</span>';

							if ($this->latestVersion)
							{
								if ($this->latestVersion->version > $this->version)
								{
									$versionHTML = '<div class="alert alert-error">' .
										'<i class="icon-puzzle install"></i>' .
										Text::_('COM_JLIKE_HAVE_INSTALLED_VER') . ': ' . $this->version .
										'<br/>' .
										'<i class="icon icon-info"></i>' .
										Text::_("COM_JLIKE_NEW_VER_AVAIL") . ': ' .
										'<span class="jlike_latest_version_number">' .
											$this->latestVersion->version .
										'</span>
										<br/>' .
										'<i class="icon icon-warning"></i>' .
										'<span class="small">' .
											Text::_("COM_JLIKE_LIVE_UPDATE_BACKUP_WARNING") . '
										</span>' . '
									</div>

									<div class="jlike-info-buttons">
										<a href="index.php?option=com_installer&view=update" class="jlike-btn-wrapper btn btn-small btn-primary">' .
											Text::sprintf('COM_JLIKE_LIVE_UPDATE_TEXT', $this->latestVersion->version) . '
										</a>
										<a href="' . $this->latestVersion->infourl . '/?utm_source=clientinstallation&utm_medium=dashboard&utm_term=jlike&utm_content=updatedetailslink&utm_campaign=jlike_ci' . '" target="_blank" class="jlike-btn-wrapper btn btn-small btn-info">' .
											Text::_('COM_JLIKE_LIVE_UPDATE_KNOW_MORE') . '
										</a>
									</div> <hr/>';
								}
							}
							?>

						<div class="row">
							<?php if (!$this->downloadid) { ?>
							<div class="">
								<div class="clearfix pull-right">
									<div class="alert alert-warning">
										<?php echo Text::sprintf('COM_JLIKE_LIVE_UPDATE_DOWNLOAD_ID_MSG', '<a href="https://techjoomla.com/about-tj/faqs/#how-to-get-your-download-id" target="_blank">' . Text::_('COM_JLIKE_LIVE_UPDATE_DOWNLOAD_ID_MSG2') . '</a>'); ?>
									</div>
								</div>
							</div>
							<?php } ?>

							<div class="">
								<div class="clearfix pull-right">
									<?php echo $versionHTML; ?>
								</div>
							</div>
						</div>
						<div class="clearfix">&nbsp;</div>
					</div>

					<div class="col-sm-12">
						<div class="panel-heading">
							<i class="icon-info-sign"></i> <?php echo Text::_('COM_JLIKE_MOST_LIKED_CONTENT'); ?>
						</div>

						<div class="widget-box transparent ">

							<div class="widget-body">
								<div class="widget-body-inner" style="display: block;">
									<div class="widget-main no-padding">
										<table class="table table-bordered table-striped">
											<thead class="thin-border-bottom">
												<tr>
													<th>
														<i class="ace-icon fa fa-caret-right blue"></i><?php echo Text::_('COM_JLIKE_TILE'); ?>
													</th>

													<th>
														<i class="ace-icon fa fa-caret-right blue"></i><?php echo Text::_('COM_JLIKE_LIKES'); ?>
													</th>

													<th class="hidden-480">
														<i class="ace-icon fa fa-caret-right blue"></i><?php echo Text::_('COM_JLIKE_DISLIKES'); ?>
													</th>
												</tr>
											</thead>

											<tbody>
												<?php foreach ($this->mostLikedData as $object)
			{
				if (!empty($object->title))
									{ ?>
												<tr>
													<td><?php echo $object->title; ?> </td>

													<td>
														<span class="label label-info arrowed-right arrowed-in">
															<?php echo $object->like_cnt; ?>
														</span>
													</td>

													<td class="hidden-480">
														<span class="label label-warning arrowed-right arrowed-in">
															<?php echo $object->dislike_cnt; ?>
														</span>
													</td>
												</tr>
												<?php
									}
			} ?>
											</tbody>
										</table>
									</div><!-- /.widget-main -->
								</div>
							</div><!-- /.widget-body -->
						</div>

					</div>
				</div>
				</div>
				</div>



				<input type="hidden" name="task" value="" />
				<input type="hidden" name="option" value="com_jlike" />
				<?php echo HTMLHelper::_('form.token'); ?>
			</div>
</form>
</div>
</div>

</div>
<!--@E tj-dashboard-->
<?php
Factory::getDocument()->addScriptDeclaration('
jQuery(document).ready(function() {
Morris.Area({
	element: "chart_div",
	data: ' . json_encode($this->linechart) . ' ,
	xkey : "ondate",
	ykeys: ["like_cnt", "dislike_cnt", "comment_cnt"],
	labels: ["Likes", "Dislikes", "Comments"],
	xLabels: "day",
	barRatio: 0.4,
	xLabelMargin: 10,
	hideHover: "auto",
	barColors: ["#3d88ba"]
});

function submitbutton() {
	var daterangefrom = techjoomla.jQuery("#fromdate").val();
	var daterangeto = techjoomla.jQuery("#todate").val();

	var res = checkDateFormat(daterangefrom);

	if (res == false) {
		alert("' . Text::_("COM_JLIKE_INVALID_DATE_FORMAT") . '" + daterangefrom);
		return false;
	}

	var res = checkDateFormat(daterangeto);

	if (res == false) {
		alert("' . Text::_("COM_JLIKE_INVALID_DATE_FORMAT") . '" + daterangeto);
		return false;
	}


	if ((daterangefrom) < (daterangeto)) {
		submitform();
	} else {
		alert("' . Text::_("COM_JLIKE_DATE_RANGE_VALID_MSG") . '");
		return false;
	}
}

function checkDateFormat(datevalue) {
	// regular expression to match required date format
	regExp = /^\d{4}\-\d{1,2}\-\d{1,2}$/;

	if (datevalue != "" && !datevalue.match(regExp)) {
		return false;
	}

	return true;
}
});
');
