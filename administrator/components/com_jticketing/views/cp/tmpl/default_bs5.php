<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2017 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

HTMLHelper::_('bootstrap.tooltip');
// Joomla 6: JVERSION check removed
		if (false) // Legacy < '4.0.0')
{
HTMLHelper::_('behavior.framework');
}
HTMLHelper::_('bootstrap.renderModal');

// Load style sheet
$document = Factory::getDocument();
HTMLHelper::_('stylesheet', 'media/techjoomla_strapper/bs3/css/bootstrap.min.css');
HTMLHelper::_('stylesheet', 'media/com_jticketing/css/jticketing-dashboard.css');
HTMLHelper::_('script', 'media/com_jticketing/vendors/js/morris.min.js');
HTMLHelper::_('script', 'media/com_jticketing/vendors/js/raphael.min.js');
HTMLHelper::_('script','libraries/techjoomla/assets/js/houseKeeping.js');

// Joomla 6: JVERSION check removed
		if (false) // Legacy < '5.0.0')
{
	HTMLHelper::stylesheet('media/techjoomla_strapper/vendors/font-awesome/css/font-awesome.min.css');
}
else 
{
	HTMLHelper::stylesheet('media/techjoomla_strapper/vendors/font-awesome/css/font-awesome-6-5-1.min.css');
}

$document->addScriptDeclaration("var tjHouseKeepingView='cp'");

$jticketingMainhelper = new Jticketingmainhelper;
$i = 0;

foreach ($this->allMonthName as $allMonthName)
{
	$allMonthNameFinal[$i] = $allMonthName['month'];
	$currMonth = $allMonthName['month'];
	$monthAmountVal[$currMonth] = 0;
	$i++;
}

$emptybarchart = 1;

foreach ($this->monthIncome as $monthIncome)
{
	$month_year = '';
	$month_year = $monthIncome->YEARNM;
	$month_name = $monthIncome->MONTHSNAME;

	$month_int = (int) $month_name;
	$timestamp = mktime(0, 0, 0, $month_int);
	$curr_month = date("F", $timestamp);

	foreach ($this->allMonthName as $allMonthName)
	{
		if (($curr_month == $allMonthName['month']) and ($monthIncome->amount) and ($month_year == $allMonthName['year']))
		{
			$monthAmountVal[$curr_month] = str_replace(",", '', $monthIncome->amount);
		}

		if ($monthIncome->amount)
		{
			$emptybarchart = 0;
		}
		else
		{
			$emptybarchart = 1;
		}
	}
}

$month_amt_str = implode(",", $monthAmountVal);
$month_name_str = implode("','", $allMonthNameFinal);
$month_name_str = "'" . $month_name_str . "'";
$month_array_name = array();
$js = "
Joomla.submitbutton = function (task)
	{
		if(task =='cp.migrate')
		{
			document.location='index.php?option=com_jticketing&task=migrateData';
		}
	}
";
$document->addScriptDeclaration($js);
?>
<script type="text/javascript" src="https://www.google.com/jsapi"></script>

<form name="adminForm" id="adminForm" class="form-validate" method="post">
<?php
if (!empty($this->sidebar))
{
?>
<div id="sidebar">
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
</div>

<div id="j-main-container" class="span10">
<?php
}
else
{
?>
<div id="j-main-container">
<?php
}?>
	<div class="tjBs3 tjBs5 jt-admin-dashboard">
		<div class="tjDB">
			<div class="row">
			<?php
				echo $this->loadTemplate("version_bs5");
			?>
			</div>
			<div id="wrapper">
				<div id="page-wrapper">
					<div class="clearfix">&nbsp;</div>
					<div class="row">
						<!--Total Events(Native)-->
						<div class="col-lg-4 col-md-6">
							<div class="panel panel-yellow">
								<?php
								if ($this->dashboardData['integrationSource'] == 'com_jticketing')
								{
									$this->eventUrl = 'index.php?option=com_jticketing&view=events&filter[featured]=""&filter[startdate]=""&filter[enddate]=""&filter[state]=1';
								}?>
								<a href="<?php echo Route::_($this->eventUrl, false)?>">
									<div class="panel-heading">
										<div class="row">
											<div class="col-xs-4 ">
												<i class="fa fa-calendar fa-4x"></i>
											</div>
											<div class="col-xs-8 text-right">
												<div class="huge">
													<span><?php echo $this->dashboardData['totalEvents'];?></span>
												</div>
												<div class="panel-text-min-height-50"><?php echo Text::_('COM_JTICKETING_DASHBOARD_EVENTS');?></div>
											</div>
										</div>
									</div>
									<div class="panel-footer">
										<span class="float-start"><?php echo Text::_('COM_JTICKETING_VIEW_DETAILS');?></span>
										<span class="float-end">
											<i class="fa fa-arrow-circle-right"></i>
										</span>
										<div class="clearfix"></div>
									</div>
								</a>
							</div>
						</div>
						<!--Total Attendees(Native)-->
						<div class="col-lg-4 col-md-6">
							<a href="<?php echo Route::_('index.php?option=com_jticketing&view=attendees', false)?>">
								<div class="panel panel-primary">
									<div class="panel-heading">
										<div class="row">
											<div class="col-xs-4 ">
												<i class="fa fa-users fa-4x"></i>
											</div>
											<div class="col-xs-8 text-right">
												<div class="huge">
													<span><?php echo $this->dashboardData['totalAttendees'];?></span>
												</div>
												<div class="panel-text-min-height-50"><?php echo Text::_('COM_JTICKETING_DASHBOARD_TOTAL_ATTENDEE');?></div>
											</div>
										</div>
									</div>
									<div class="panel-footer">
										<span class="float-start"><?php echo Text::_('COM_JTICKETING_VIEW_DETAILS');?></span>
										<span class="float-end">
											<i class="fa fa-arrow-circle-right"></i>
										</span>
										<div class="clearfix"></div>
									</div>
								</div>
							</a>
						</div>
						<!--Total Orders(Native)-->
						<div class="col-lg-4 col-md-6">
							<div class="panel panel-green">
								<a href="<?php echo Route::_('index.php?option=com_jticketing&view=orders', false)?>">
									<div class="panel-heading">
										<div class="row">
											<div class="col-xs-4 ">
												<i class="fa fa-shopping-cart fa-4x"></i>
											</div>
											<div class="col-xs-8 text-right">
												<div class="huge">
													<span><?php echo $this->dashboardData['totalOrders'];?></span>
												</div>
												<div class="panel-text-min-height-50"><?php echo Text::_('COM_JTICKETING_DASHBOARD_ORDERS');?></div>
											</div>
										</div>
									</div>
									<div class="panel-footer">
										<span class="float-start"><?php echo Text::_('COM_JTICKETING_VIEW_DETAILS');?></span>
										<span class="float-end">
											<i class="fa fa-arrow-circle-right"></i>
										</span>
										<div class="clearfix"></div>
									</div>
								</a>
							</div>
						</div>
						<!--Total Commision(Native)-->
						<div class="col-lg-3 col-md-6">
							<div class="panel panel-red">
								<a href="<?php echo Route::_('index.php?option=com_jticketing&view=allticketsales', false)?>">
									<div class="panel-heading">
										<div class="row">
											<div class="col-xs-4 ">
												<i class="fa fa-money fa-4x"></i>
											</div>
											<div class="col-xs-8 text-right">
												<div class="huge">
													<span><?php echo $this->utilities->getFormattedPrice($this->dashboardData['commissionAmount']);?></span>
												</div>
												<div class="panel-text-min-height-50"><?php echo Text::_('COM_JTICKETING_DASHBOARD_COMMISSION_AMOUNT');?></div>
											</div>
										</div>
									</div>
									<div class="panel-footer">
										<span class="float-start"><?php echo Text::_('COM_JTICKETING_VIEW_DETAILS');?></span>
										<span class="float-end">
											<i class="fa fa-arrow-circle-right"></i>
										</span>
										<div class="clearfix"></div>
									</div>
								</a>
							</div>
						</div>
						<!--Total Ongoing Event(Native)-->
						<div class="col-lg-3 col-md-6">
							<div class="panel panel-purple-blue-marguerita">
								<?php
								if ($this->dashboardData['integrationSource'] == 'com_jticketing')
								{
									$this->eventUrl = 'index.php?option=com_jticketing&view=events&filter[featured]=1&filter[state]=1';
								}?>
								<a href="<?php echo Route::_($this->eventUrl, false)?>">
									<div class="panel-heading">
										<div class="row">
											<div class="col-xs-4 ">
												<i class="fa fa-calendar fa-4x"></i>
											</div>
											<div class="col-xs-8 text-right">
												<div class="huge">
													<span><?php echo $this->dashboardData['ongoingEvents'];?></span>
												</div>
												<div class="panel-text-min-height-50"><?php echo Text::_('COM_JTICKETING_DASHBOARD_FEATURED_EVENTS');?></div>
											</div>
										</div>
									</div>
									<div class="panel-footer">
										<span class="float-start"><?php echo Text::_('COM_JTICKETING_VIEW_DETAILS');?></span>
										<span class="float-end">
											<i class="fa fa-arrow-circle-right"></i>
										</span>
										<div class="clearfix"></div>
									</div>
								</a>
							</div>
						</div>
						<!--Total Past Events(Native)-->
						<div class="col-lg-3 col-md-6">
							<div class="panel panel-spring-green">
								<?php
								if ($this->dashboardData['integrationSource'] == 'com_jticketing')
								{
									$this->eventUrl = 'index.php?option=com_jticketing&view=events&filter[state]=1&filter[enddate]=1';
								}?>
								<a href="<?php echo Route::_($this->eventUrl, false)?>">
									<div class="panel-heading">
										<div class="row">
											<div class="col-xs-4 ">
												<i class="fa fa-calendar-check-o fa-4x"></i>
											</div>
											<div class="col-xs-8 text-right">
												<div class="huge">
													<span><?php echo $this->dashboardData['pastEvents'];?></span>
												</div>
												<div class="panel-text-min-height-50"><?php echo Text::_('COM_JTICKETING_DASHBOARD_PAST_EVENTS');?></div>
											</div>
										</div>
									</div>
									<div class="panel-footer">
										<span class="float-start"><?php echo Text::_('COM_JTICKETING_VIEW_DETAILS');?></span>
										<span class="float-end">
											<i class="fa fa-arrow-circle-right"></i>
										</span>
										<div class="clearfix"></div>
									</div>
								</a>
							</div>
						</div>
						<!--Total Upcoming Events(Native)-->
						<div class="col-lg-3 col-md-6">
							<div class="panel panel-burnt-orange">
								<?php
								if ($this->dashboardData['integrationSource'] == 'com_jticketing')
								{
									$this->eventUrl = 'index.php?option=com_jticketing&view=events&filter[state]=1&filter[startdate]=1';
								}?>
								<a href="<?php echo Route::_($this->eventUrl, false);?>">
									<div class="panel-heading">
										<div class="row">
											<div class="col-xs-4 ">
												<i class="fa fa-calendar fa-4x"></i>
											</div>
											<div class="col-xs-8 text-right">
												<div class="huge">
													<span><?php echo $this->dashboardData['upcomingEvents'];?></span>
												</div>
												<div class="panel-text-min-height-50"><?php echo Text::_('COM_JTICKETING_DASHBOARD_UPCOMING_EVENTS');?></div>
											</div>
										</div>
									</div>
									<div class="panel-footer">
										<span class="float-start"><?php echo Text::_('COM_JTICKETING_VIEW_DETAILS');?></span>
										<span class="float-end">
											<i class="fa fa-arrow-circle-right"></i>
										</span>
										<div class="clearfix"></div>
									</div>
								</a>
							</div>
						</div>
					</div>

					<div class="row">
						<div class="col-lg-8">
							<!-- Start - Bar Chart for Monthly Donations for past 12 months -->
							<div class="panel card">
								<div class="panel-heading">
									<i class="fa fa-bar-chart-o fa-fw"></i>
									<?php echo Text::_('MONTHLY_INCOME_MONTH');?>
								</div>
								<div class="panel-body">
									<?php
									if ($this->allincome)
									{
									?>
										<div id="monthin"></div>
									<?php
									}
									else
									{
										echo Text::_('COM_JTICKETING_NO_DATA');
									}
									?>

								</div>
							</div>
							<!-- End - Bar Chart for Monthly Income for past 12 months -->

							<!-- Start - Weekly Ticket sales graph -->
							<div class="panel card">
								<div class="panel-heading">
									<i class="fa fa-line-chart" aria-hidden="true"></i>
									<?php echo Text::_('COM_JTICKETING_TICKET_SALES_LAST_WEEK_PER_DAY');?>
								</div>
								<div class="panel-body">
									<?php
									if ($this->ticketSalesLastweek)
									{
									?>
										<div id="lastWeekTicketSale" style="width:auto;height:350px;"></div>
									<?php
									}
									else
									{
									?>
										<?php echo Text::_('COM_JTICKETING_NO_DATA');?>
									<?php
									}
									?>
								</div>
							</div>
							<!-- End - Weekly Ticket sales graph -->

							<div class="row">
								<div class="col-lg-7">
									<?php
									// Joomla 6: JVERSION check removed
		if (false) // Legacy < '4.0.0')
									{
										echo $this->loadTemplate("periodicgraph_bs2");
									}
									else
									{
										echo $this->loadTemplate("periodicgraph_bs5");
									}
									?>
								</div>

								<!--Top 3 best events performance-->
								<div class="col-lg-5">
									<div class="panel card">
										<div class="panel-heading">
											<i class="fa fa-list fa-fw"></i>
											<?php echo Text::_('COM_JTICKETING_TOP_FIVE_EVENTS');?>
										</div>
										<div class="panel-body">
											<?php
											if ($this->topFiveEvents)
											{
											?>
												<table class="table table-striped table-hover">
													<thead>
														<th><?php echo Text::_('COM_JTICKETING_DASHBOARD_EVENT_TITLE')?></th>
														<th><?php echo Text::_('COM_JTICKETING_DASHBOARD_SALES_AMOUNT')?></th>
														<th><?php echo Text::_('COM_JTICKETING_DASHBOARD_ORDERS_COUNT')?></th>
													</thead>
													<tbody>
														<?php
														foreach ($this->topFiveEvents as $topFiveEvents)
														{
														?>
															<tr>
																<td><?php echo wordwrap($this->escape($topFiveEvents->title),15,"<br>\n");?></td>
																<td>
																	<?php echo $this->utilities->getFormattedPrice($topFiveEvents->salesAmount);?>
																</td>
																<td>
																	<?php echo $topFiveEvents->orderCount;?>
																</td>
															</tr>
														<?php
														}
														?>
													</tbody>
												</table>
												<a title="<?php echo Text::_('COM_JTICKETING_ORDERS_SHOW_ALL_DESC');?>"
													class="btn btn-primary btn-small float-end"
													href="<?php echo Route::_('index.php?option=com_jticketing&view=orders');?>"
													target="_blank" >
														<?php echo Text::_('COM_JTICKETING_ORDERS_SHOW_ALL'); ?>
												</a>
											<?php
											}
											else
											{
												echo Text::_('COM_JTICKETING_NO_DATA');
											}?>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="col-lg-4">
							<?php
							if (!$this->downloadid)
							{
							?>
							<div class="clearfix float-end">
								<div class="alert alert-warning">
									<?php echo Text::sprintf('COM_JTICKETING_LIVE_UPDATE_DOWNLOAD_ID_MSG', '<a href="https://techjoomla.com/add-on-download-ids/" target="_blank">' . Text::_('COM_JTICKETING_LIVE_UPDATE_DOWNLOAD_ID_MSG2') . '</a>'); ?>

									<?php
										//echo Text::sprintf('COM_SA_LIVE_UPDATE_DOWNLOAD_ID_MSG', '<a href="https://techjoomla.com/my-account/add-on-download-ids" target="_blank">' . Text::_('COM_SA_LIVE_UPDATE_DOWNLOAD_ID_MSG2') . '</a>');
									?>
								</div>
							</div>

							<div class="clearfix"></div>
							<?php
							}
							?>


							<div class = "panel card">
								<div class = "panel-heading">
									<i class="fa fa-ticket" aria-hidden="true"></i>
									<?php echo Text::_('COM_JTICKETING'); ?>
								</div>
								<div class="panel-body">
									<div class = "">
										<blockquote class="blockquote-reverse">
											<p><?php echo Text::_('COM_JTICKETING_INTRO');?></p>
										</blockquote>
									</div>

									<div class="row">
										<div class = "col-lg-12 col-md-12 col-sm-12">
											<p class = "float-end"><span class="label label-info"><?php echo Text::_('COM_JTICKETING_LINKS'); ?></span></p>
										</div>
									</div>

									<div class = "list-group">
										<a href = "https://techjoomla.com/table/extension-documentation/documentation-for-jticketing/" class="list-group-item" target = "_blank">
											<i class="fa fa-file fa-fw i-document"></i>
											<?php echo Text::_('COM_JTICKETING_DOCS');?>
										</a>

										<a href="https://techjoomla.com/documentation-for-jticketing/jticketing-faqs.html" class = "list-group-item" target="_blank">
											<i class = "fa fa-question fa-fw i-question"></i> <?php echo Text::_('COM_JTICKETING_FAQS');?>
										</a>
										<a href = "http://techjoomla.com/support/support-tickets" class = "list-group-item" target = "_blank">
											<i class = "fa fa-support fa-fw i-support"></i> <?php echo Text::_('COM_JTICKETING_TECHJOOMLA_SUPPORT_CENTER');?>
										</a>

										<a href = "http://extensions.joomla.org/extensions/extension-specific/jomsocial-extensions/21064" class = "list-group-item" target = "_blank">
											<i class = "fa fa-bullhorn fa-fw i-horn"></i> <?php echo Text::_('COM_JTICKETING_LEAVE_JED_FEEDBACK');?>
										</a>
									</div>

									<div class = "row">
										<div class = "col-lg-12 col-md-12 col-sm-12">
											<p class = "float-end">
												<span class = "label label-info"><?php echo Text::_('COM_JTICKETING_STAY_TUNNED'); ?></span>
											</p>
										</div>
									</div>

									<div class = "list-group">
										<div class="list-group-item">
											<div class="float-start">
												<i class="fa fa-twitter fa-fw i-twitter"></i>
												<?php echo Text::_('COM_JTICKETING_FACEBOOK'); ?>
											</div>
											<div class = "float-end">
												<div id = "fb-root"></div>
												<script>
													(function(d, s, id)
														{
															var js, fjs = d.getElementsByTagName(s)[0];

															if (d.getElementById(id)) return;
															js = d.createElement(s); js.id = id;
															js.src = "//connect.facebook.net/en_US/all.js#xfbml=1";
															fjs.parentNode.insertBefore(js, fjs);
														}
														(document, 'script', 'facebook-jssdk')
													);
												</script>
												<div class = "fb-like"
													data-href = "https://www.facebook.com/techjoomla"
													data-send = "true" data-layout = "button_count"
													data-width = "250" data-show-faces = "false"
													data-font = "verdana">
												</div>
											</div>
											<div class = "clearfix">&nbsp;</div>
										</div>
										<div class="list-group-item">
											<div class="float-start">
												<i class="fa fa-twitter fa-fw i-twitter"></i>
												<?php echo Text::_('COM_JTICKETING_TWITTER'); ?>
											</div>
											<div class = "float-end">
												<!-- twitter button code -->
												<a href = "https://twitter.com/techjoomla" class = "twitter-follow-button" data-show-count = "false">Follow @techjoomla</a>
												<script>
													!function(d,s,id)
													{
														var js,fjs = d.getElementsByTagName(s)[0];
														if(!d.getElementById(id))
														{
															js = d.createElement(s);
															js.id = id;
															js.src = "//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);
														}
													}
													(document,"script","twitter-wjs");
												</script>
											</div>
											<div class = "clearfix">&nbsp;</div>
										</div>
										<div class = "list-group-item">
											<div class = "float-start">
												<i class = "fa fa-google fa-fw i-google"></i>
												<?php echo Text::_('COM_JTICKETING_GPLUS'); ?>
											</div>
											<div class = "float-end">
												<!-- Place this tag where you want the +1 button to render. -->
												<div class = "g-plusone" data-annotation = "inline" data-width = "120" data-href = "https://plus.google.com/102908017252609853905"></div>
												<!-- Place this tag after the last +1 button tag. -->
												<script type = "text/javascript">
												(function() {
												var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
												po.src = 'https://apis.google.com/js/plusone.js';
												var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
												})();
												</script>
											</div>
											<div class = "clearfix">&nbsp;</div>
										</div>
									</div>
									<div class = "clearfix">&nbsp;</div>
									<div class = "row">
										<div class = "col-lg-12 col-md-12 col-sm-12 center">
											<?php
											$logo = '<img src = "' . Uri::root(true) . '/media/com_jticketing/images/techjoomla.png" alt = "TechJoomla" class = ""/>';?>
											<span class = "center thumbnail">
												<a href = 'http://techjoomla.com/' target = '_blank'>
													<?php echo $logo;?>
												</a>
											</span>
											<p><?php echo Text::_('COM_JTICKETING_COPYRIGHT'); ?></p>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
</form>
<script type = 'text/javascript'>
<?php
if (!empty($this->allincome))
{
?>
	google.charts.load('49', {packages: ['corechart']});
	google.setOnLoadCallback(drawChart);

	function drawChart()
	{
		var data = new google.visualization.DataTable();
		var raw_dt1=[<?php echo $month_amt_str;?>];
		var raw_data = [raw_dt1];
		var Months = [<?php echo $month_name_str;?>];
		data.addColumn("string", "<?php echo Text::_('BAR_CHART_HAXIS_TITLE');?>");
		data.addColumn("number","<?php echo Text::_('BAR_CHART_VAXIS_TITLE') . ' (' . $this->currency . ')';?>");
		data.addRows(Months.length);

		for (var j = 0; j < Months.length; ++j)
		{
			data.setValue(j, 0, Months[j].toString());
		}

		for (var i = 0; i  < raw_data.length; ++i)
		{
			for (var j = 1; j  <=(raw_data[i].length); ++j)
			{
				data.setValue(j-1, i+1, raw_data[i][j-1]);
			}
		}

		// Create and draw the visualization.
		new google.visualization.ColumnChart(document.getElementById("monthin")).draw(data, {
			title:'<?php //echo Text::_("MONTHLY_INCOME_MONTH");?>',
			width:'48%', height:300,
			fontSize:'12px',
			hAxis: {title: "<?php echo Text::_('BAR_CHART_HAXIS_TITLE').'('.date('Y').'-'.(date('y')-1).')';?>"},
			vAxis: {title: "<?php echo Text::_('BAR_CHART_VAXIS_TITLE') . ' (' . $this->currency . ')';?>"}
		});
	}
<?php
}
?>

<?php
if ($this->ticketSalesLastweek)
{
?>
	google.charts.load('49', {packages: ['corechart']});
	google.setOnLoadCallback(lastWeekDrawChart);
	function lastWeekDrawChart()
	{
		var data = google.visualization.arrayToDataTable([
			['<?php echo Text::_("COM_JTICKETING_DATE");?>', '<?php echo Text::_("COM_JTICKETING_TICKET_SALESLASTWEEK_PER_DAY_CNT");?>'],
			<?php
				foreach ($this->ticketSalesLastweek as $mpd)
				{
					echo "['" . $mpd->date . "'," . $mpd->count . "],";
				}
			?>
		]);
		var options = {
			title: '<?php //echo Text::_("COM_JTICKETING_TICKET_SALESLASTWEEK_PER_DAY");?>',
			vAxis: {title:'<?php echo Text::_("COM_JTICKETING_TICKET_SALESLASTWEEK_PER_DAY_CNT");?>'},
			hAxis: {title:'<?php echo Text::_("COM_JTICKETING_DATE");?>'},
			backgroundColor:'transparent',
			colors: ['#3EA99F']

		};
		var chart = new google.visualization.LineChart(document.getElementById('lastWeekTicketSale'));
		chart.draw(data, options);
	}
<?php
}
?>
</script>

