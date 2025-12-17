<?php

/**
 * @version    SVN: <svn_id>
 * @package    Com_Jticekting
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2018 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */
// No direct access
defined('_JEXEC') or die(';)');

use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;

$bootstrapclass = "";
$tableclass = "table table-striped  table-hover";
$document = Factory::getDocument();
$jticketingmainhelper = new jticketingmainhelper;
$com_params = ComponentHelper::getParams('com_jticketing');
$integration = $com_params->get('integration');
$siteadmin_comm_per = $com_params->get('siteadmin_comm_per');
$show_js_toolbar = $com_params->get('show_js_toolbar');
$currency = $com_params->get('currency');
$user = Factory::getUser();
$input = Factory::getApplication()->getInput();

if (empty($user->id)) {
	echo '<div class="alert alert-warning">' . Text::_('USER_LOGOUT') . '</div>';

	return;
}

$js_key = "Joomla.submitbutton = function(task)
{
	function submitbutton( task )
	{
		document.adminForm.action.value=task;
		if (task =='cancel')
		{	Joomla.submitform(task);
			document.adminForm.submit();
		}
	}
}
";
$document->addScriptDeclaration($js_key);

if ($integration == 1) // If Jomsocial show JS Toolbar Header
{
	$jspath = JPATH_ROOT . '/components/com_community';

	if (file_exists($jspath)) {
		require_once($jspath . '/libraries/core.php');
	}

	$header = '';
	$header = JT::integration()->getJSheader();

	if (!empty($header)) {
		echo $header;
	}
}
?>

<div class="floattext col-xs-12 col-sm-8">
	<?php
	if ($this->params->get('show_page_heading', 1)) {
	?>
		<div class="floattext container-fluid">
			<h1 class="componentheading"><?php echo $this->PageTitle; ?></h1>
		</div>
	<?php
	} ?>
</div>
<?php
$eventid = $this->lists['search_event'];
if (!$eventid) {
	$eventid = $input->get('event', '', 'INT');
}

$linkbackbutton = '';

// Eoc for JS toolbar inclusion
if (empty($this->Data)) {
?>
	<div class="<?php echo JTICKETING_WRAPPER_CLASS; ?>">
		<form action="" method="post" name="adminForm" id="adminForm">
			<div id="all" class="row">
				<div class="col-xs-12 div-mt-3">
					<div class="row">
						<div class="col-sm-12 col-md-4">
							<div class="pull-left float-start af-mb-10">
								<?php echo HTMLHelper::_('select.genericlist', $this->status_event, "search_event", 'class="ad-status form-select" size="1"
							onchange="document.adminForm.submit();" name="search_event"', "value", "text", $this->lists['search_event']); ?>
							</div>
							<button type="button" class="af-ml-10 btn btn-primary hasTooltip js-stools-btn-clear" onclick="document.getElementById('search_event').value = '';
							document.adminForm.submit();">
								<?php echo Text::_('COM_JTICKETING_SEARCH_FILTER_CLEAR'); ?>
							</button>
						</div>
						<div class="col-sm-12 col-md-8">
							<div class="btn-group pull-right float-end af-mb-10">
								<?php
								echo $this->pagination->getLimitBox();
								?>
							</div>
						</div>
					</div>
					<div class="clearfix">&nbsp;</div>
					<div class="col-xs-12 alert alert-info jtleft"><?php echo Text::_('NODATA'); ?></div>
				</div>
			</div>
			<input type="hidden" name="option" value="com_jticketing" />
			<input type="hidden" name="task" value="" />
			<input type="hidden" name="boxchecked" value="0" />
			<input type="hidden" name="defaltevent" value="<?php echo $this->lists['search_event']; ?>" />
			<input type="hidden" name="controller" value="allticketsales" />
			<input type="hidden" name="view" value="allticketsales" />
			<input type="hidden" name="Itemid" value="<?php echo $this->Itemid; ?>" />
		</form>
	</div>

<?php
	if ($integration == 1) {
		$footer = '';
		$footer = JT::integration()->getJSfooter();

		if (!empty($footer))
			echo $footer;
	}
	return;
}
?>
<div class="<?php echo JTICKETING_WRAPPER_CLASS; ?>">
	<form action="" method="post" name="adminForm" id="adminForm" class="container-fluid">
		<div id="all" class="row">
			<div class="col-xs-12 div-mt-3">
				<div class="pull-left float-start af-mb-10">
					<?php echo HTMLHelper::_('select.genericlist', $this->status_event, "search_event", 'class="ad-status form-select" size="1"
				   onchange="document.adminForm.submit();" name="search_event"', "value", "text", $this->lists['search_event']); ?>
				</div>
				<button type="button" class="af-ml-10 btn btn-primary hasTooltip js-stools-btn-clear" onclick="document.getElementById('search_event').value = '';
			document.adminForm.submit();">
					<?php echo Text::_('COM_JTICKETING_SEARCH_FILTER_CLEAR'); ?>
				</button>

				<?php // Joomla 6: JVERSION check removed
		if (false) { // Legacy >= '3.0'
				?>
					<div class="btn-group pull-right float-end af-mb-10">
						<?php
						echo $this->pagination->getLimitBox();
						?>
					</div>
					<div class="clearfix"><br>&nbsp;</div>
				<?php } ?>
				<div id='no-more-tables'>
					<table class="table table-striped table-bordered table-hover table-light border ">
						<thead class="table-primary text-light">
							<tr>
								<th><?php echo HTMLHelper::_('grid.sort', 'EVENT_NAME', 'title', $this->lists['order_Dir'], $this->lists['order']); ?></th>
								<th align="right"><?php echo  Text::_('COM_JTICKEITNG_TICKET_SALES_EVENT_START_DATE'); ?></th>
								<!--<th ><?php echo HTMLHelper::_('grid.sort', 'BOUGHTON', 'cdate', $this->lists['order_Dir'], $this->lists['order']); ?></th>-->
								<th><?php echo HTMLHelper::_('grid.sort', 'NUMBEROFTICKETS_SOLD', 'eticketscount', $this->lists['order_Dir'], $this->lists['order']); ?></th>
								<th align="right"><?php echo  Text::_('EARNINGTOTAL_AMOUNT'); ?></th>
								<th align="right"><?php echo  Text::_('COMMISSION'); ?></th>
								<th align="right"><?php echo  Text::_('TOTAL_AMOUNT'); ?></th>
							</tr>
						</thead>
						<?php
						$i = $subtotalamount = 0;
						$sclass = '';

						foreach ($this->Data as $data) 
						{
							if (empty($data->thumb))
								$data->thumb = Uri::root() . 'components/com_community/assets/event_thumb.png';
							else
								$data->thumb = Uri::root() . $data->thumb;
							require_once JPATH_SITE . "/components/com_jticketing/helpers/route.php";
							$JTRouteHelper = new JTRouteHelper;
							$attendeesLink = 'index.php?option=com_jticketing&view=attendees&filter[events]=' . $data->evid . '&filter[status]=A';
							$link = $JTRouteHelper->JTRoute($attendeesLink);
						?>
							<tr>
								<td data-title="<?php echo Text::_('EVENT_NAME'); ?>">
									<a href="<?php echo $link; ?>"><?php echo ucfirst($data->title); ?></a>
								</td>
								<td align="center" data-title="<?php echo Text::_('COM_JTICKEITNG_TICKET_SALES_EVENT_START_DATE'); ?>"><?php echo $this->utilities->getFormatedDate($data->startdate);; ?></td>
								<td align="center" data-title="<?php echo Text::_('NUMBEROFTICKETS_SOLD'); ?>"><?php echo $data->eticketscount ?></td>
								<td align="center" data-title="<?php echo Text::_('EARNINGTOTAL_AMOUNT'); ?>"><?php echo $this->utilities->getFormattedPrice($data->eamount); ?></td>
								<td align="center" data-title="<?php echo Text::_('COMMISSION'); ?>"><?php echo $this->utilities->getFormattedPrice($data->ecommission); ?></td>
								<td align="center" data-title="<?php echo Text::_('TOTAL_AMOUNT'); ?>"><?php $subtotalearn = $data->eamount - $data->ecommission;
																										echo $this->utilities->getFormattedPrice($subtotalearn); ?>
								</td>
							</tr>
						<?php $i++;
						} ?>
						<tr>
							<td>
								<div class="jtright hidden-xs hidden-sm"><b><?php echo Text::_('COM_JTICKETING_TOTAL_OVERALL_STATISTICS'); ?></b></div>
							</td>
							<td align="center" data-title="<?php echo Text::_('COM_JTICKEITNG_TICKET_SALES_EVENT_START_DATE'); ?>">-</td>
							<td align="center" data-title="<?php echo Text::_('TOTAL_NUMBEROFTICKETS_SOLD'); ?>"><b><?php echo number_format($this->totalnooftickets, 0, '', ''); ?></b></td>
							<td align="center" data-title="<?php echo Text::_('EARNINGTOTAL_AMOUNT'); ?>"><b><?php echo $this->utilities->getFormattedPrice($this->totalprice); ?></b></td>
							<td align="center" data-title="<?php echo Text::_('COMMISSION'); ?>"><b><?php echo $this->utilities->getFormattedPrice($this->totalcommission); ?></b></td>
							<td align="center" data-title="<?php echo Text::_('TOTAL_AMOUNT'); ?>"><b><?php echo $this->utilities->getFormattedPrice($this->totalearn); ?></b></td>
						</tr>
					</table>
				</div>
				<input type="hidden" name="option" value="com_jticketing" />
				<input type="hidden" name="task" value="" />
				<input type="hidden" name="boxchecked" value="0" />
				<input type="hidden" name="defaltevent" value="<?php echo $this->lists['search_event']; ?>" />
				<input type="hidden" name="controller" value="allticketsales" />
				<input type="hidden" name="view" value="allticketsales" />
				<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
				<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
			</div>
			<!--row fluid -->
			<div class="row">
				<div class="col-xs-12">
					<?php
					// Joomla 6: JVERSION check removed
		if (false) // Legacy < 3.0)
						$class_pagination = 'pager';
					else
						$class_pagination = 'pagination';
					?>
					<div class="<?php echo $class_pagination; ?> com_jticketing_align_center">
						<div class="pager">
							<?php echo $this->pagination->getPagesLinks(); ?>
						</div>
					</div>
				</div>
				<!-- col-lg-12 col-md-12 col-sm-12 col-xs-12-->
			</div>
			<!--row-->
		</div>
	</form>
</div>
<!--bootstrap-->
<!-- newly added for JS toolbar inclusion  -->
<?php
if ($integration == 1) //if Jomsocial show JS Toolbar Footer
{
	$footer = '';
	$footer = JT::integration()->getJSfooter();

	if (!empty($footer))
		echo $footer;
}
?>
<!-- eoc for JS toolbar inclusion	 -->