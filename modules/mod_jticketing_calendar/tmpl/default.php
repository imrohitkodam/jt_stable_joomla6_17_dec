<?php

/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.multiselect');
HTMLHelper::_('formbehavior.chosen', 'select');
HTMLHelper::_('bootstrap.renderModal', 'a.modal');

$document = Factory::getDocument();
$root_url = Uri::base();
HTMLHelper::_('stylesheet', 'modules/mod_jticketing_calendar/assets/css/calendar.css');
HTMLHelper::_('stylesheet', 'components/com_jticketing/assets/font-awesome-4.1.0/css/font-awesome.min.css');
$jticketingfrontendhelper = JPATH_ROOT . '/components/com_jticketing/helpers/frontendhelper.php';

if (!class_exists('jticketingfrontendhelper')) {
	JLoader::register('jticketingfrontendhelper', $jticketingfrontendhelper);
	JLoader::load('jticketingfrontendhelper');
}

// Load assets
JT::utilities()->loadjticketingAssetFiles();
$tjClass = 'JTICKETING_WRAPPER_CLASS ';
?>

<div class="<?php echo $tjClass . $params->get('moduleclass_sfx'); ?>">

	<form method="post" name="jtcalendarForm" id="jtcalendarForm">

		<div class="jtcalendarForm">
			<div class="row">
				<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
					<div class="jtick-calendar-header center">
						<div class="form-inline"></div>
						<div class="btn" id="month_button" data-calendar-nav=""><span id="month_text"></span>
						</div>
						<div class="jtick-calendar-body">
							<div class="btn-group">
								<span class="input-group-btn btn-default pull-left float-start input-group">
									<button class="btn btn-info  btn-sm-jt" id="pre_year_button" data-calendar-nav="prev-year"><i class="fa fa-backward"></i></button>
									<button class="btn btn-info btn-sm-jt" id="pre_button" data-calendar-nav="prev">
										<i class="fa fa-chevron-left"></i></button>
									<button class="btn" data-calendar-nav="today">Today</button>
									<button class="btn btn-info btn-sm-jt" id="nex_button" data-calendar-nav="next">
										<i class="fa fa-chevron-right"></i></button>
									<button class="btn btn-info btn-sm-jt" id="nex_year_button" data-calendar-nav="next-year">
										<i class="fa fa-forward"></i></button>
								</span>
							</div>
						</div>
						<div class="clearfix"></div>
					</div>
				</div>

				<div class="">
					<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
						<div id="jt-calendar-module"></div>
					</div>
				</div>



				<input type="hidden" name="template_path_calendar" id="template_path_calendar" value="<?php echo $root_url; ?>modules/mod_jticketing_calendar/assets/tmpls/">
				<input type="hidden" name="jt_root_url" id="jt_root_url" value="<?php echo $root_url; ?>">
			</div>
		</div>
	</form>
</div>

<script>
	jQuery(document).ready(function() {
		jQuery(document).on("submit", "#jtcalendarForm",function(e) {
			e.preventDefault();
			return false;
		});
	});
</script>

<script type="text/javascript" src="<?php echo $root_url . 'components/com_jticketing/assets/calendars/components/underscore/underscore-min.js'; ?>">
</script>
<script type="text/javascript" src="<?php echo $root_url . 'components/com_jticketing/assets/calendars/components/jstimezonedetect/jstz.min.js'; ?>">
</script>
<script type="text/javascript" src="<?php echo $root_url . 'components/com_jticketing/assets/calendars/js/calendar.js'; ?>">
</script>
<script type="text/javascript" src="<?php echo $root_url . 'modules/mod_jticketing_calendar/assets/js/app.js'; ?>">
</script>