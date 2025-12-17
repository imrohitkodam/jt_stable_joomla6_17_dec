<?php
// no direct access
defined( '_JEXEC' ) or die( ';)' );
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
global $mainframe;
// Add the CSS and JS

$document =Factory::getDocument();

$input=Factory::getApplication()->getInput();
$eventid = $input->get('eventid','','INT');
HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('bootstrap.renderModal', 'a.modal');
$com_params=ComponentHelper::getParams('com_jticketing');
$integration = $com_params->get('integration');
$siteadmin_comm_per = $com_params->get('siteadmin_comm_per');
$currency = $com_params->get('currency');
$allow_buy_guestreg = $com_params->get('allow_buy_guestreg');
$tnc = $com_params->get('tnc');
$user = Factory::getUser();

if (empty($user->id))
{
	echo '<b>'.Text::_('USER_LOGOUT').'</b>';
	return;
}

$payment_statuses = array(
	'P' => Text::_('JT_PSTATUS_PENDING'),
	'C' => Text::_('JT_PSTATUS_COMPLETED'),
	'D' => Text::_('JT_PSTATUS_DECLINED'),
	'E' => Text::_('JT_PSTATUS_FAILED'),
	'UR' => Text::_('JT_PSTATUS_UNDERREVIW'),
	'RF' => Text::_('JT_PSTATUS_REFUNDED'),
	'CRV' => Text::_('JT_PSTATUS_CANCEL_REVERSED'),
	'RV' => Text::_('JT_PSTATUS_REVERSED'),
	'I' => Text::_('JT_PSTATUS_INITIATED')
);
?>

<?php

$integration=JT::getIntegration(true);
if($integration==1) //if Jomsocial show JS Toolbar Header
{
	$jspath=JPATH_ROOT . '/components/com_community';
	if(file_exists($jspath)){
	require_once($jspath . '/libraries/core.php');
}

	$header='';
	$header = JT::integration()->getJSheader();
	if(!empty($header))
	echo $header;
}

?>
<div  class="floattext">
	<h1 class="componentheading"><?php echo Text::_('MY_TICKET'); ?>	</h1>
</div>
<?php
$k=0;

if(empty($this->Data))
{
	echo Text::_('NODATA');
$input=Factory::getApplication()->getInput();
$eventid = $input->get('event','','INT');
	 //if Jomsocial show JS Toolbar Header
if($integration==1)
{
	$footer = '';
	$footer = JT::integration()->getJSfooter();
	if(!empty($footer))
	echo $footer;

}
	return;
}
?>
<div class="<?php echo JTICKETING_WRAPPER_CLASS;?>">
<form action="" method="post" name="adminForm" id="adminForm">
		<div id="all" class="row">
			<div style="float:left">
			<?php echo HTMLHelper::_('select.genericlist', $this->status_order, "search_order", 'class="ad-status" size="1"
				onchange="document.adminForm.submit();" name="search_order"',"value", "text", $this->lists['search_order']);		 ?>
			</div>
			<?php if(JVERSION>'3.0') {?>
			<div class="btn-group float-end hidden-xm">
				<label for="limit" class="element-invisible"><?php echo Text::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC');?></label>
					<?php
					echo $this->pagination->getLimitBox();
					?>
			</div>
			<?php } ?>
			<div class="table-responsive">
				<table  class="table table-striped table-hover " >
					<tr>
						<th align="center"><?php echo HTMLHelper::_( 'grid.sort','EVENT_NAME','title', $this->lists['order_Dir'], $this->lists['order']); ?></th>
						<th align="center"><?php echo Text::_( 'EVENTDATE' ); ?></th>
						<th align="center"><?php echo Text::_('TIMING'); ?></th>
						<th align="center"><?php echo Text::_( 'TICKET_RATE' ).'('.$currency.')';?></th>
						<th align="center"><?php echo Text::_( 'NUMBEROFTICKETS_BOUGHT' );?></th>
						<th align="center"><?php echo  Text::_( 'TOTAL_AMOUNT_BUY' ).'('.$currency.')'; ?></th>
								<th align="center"><?php echo Text::_( 'PAYMENT_STATUS'); ?></th>
						<th align="center"><?php echo  Text::_( 'VIEW_TICKET' ); ?></th>
					</tr>
						<?php
						$totalnooftickets=0;
						$totalprice=0;
						$i=0;

								foreach($this->Data as $data) {

								$totalnooftickets=$totalnooftickets+$data->ticketscount;
								 $totalprice=$totalprice+$data->totalamount;
								if(JVERSION<'1.6.0')
								{
								$startdate = HTMLHelper::_('date', $data->startdate, '%Y/%m/%d');
								$enddate = HTMLHelper::_('date', $data->enddate, '%Y/%m/%d');
								}
								else
								{
								 $startdate = HTMLHelper::_('date', $data->startdate, 'Y-m-d');
								 $enddate = HTMLHelper::_('date', $data->enddate, 'Y-m-d');

								}
								if($startdate==$enddate)
								 $datetoshow=Text::sprintf('EVENTS_DURATION_ONE',$startdate);
								else
								$datetoshow=Text::sprintf('EVENTS_DURATION',$startdate,$enddate);

								if(JVERSION<'1.6.0')
								{
								 $starttime = HTMLHelper::_('date', $data->startdate, "%I:%M %p");
								$enddtime = HTMLHelper::_('date', $data->enddate, "%I:%M %p");
								}
								else
								{
									$starttime = HTMLHelper::_('date', $data->startdate, "g:i a");
									$enddtime = HTMLHelper::_('date', $data->enddate, "g:i a");
								}

								if($enddtime==$starttime)
								$timetoshow=Text::sprintf('EVENTS_DURATION_ONE',$starttime);
								else
									$timetoshow=Text::sprintf('EVENTS_DURATION',$starttime,$enddtime);

										if($integration==1 OR $integration==2)
										{	if($data->thumb)
												$avatar = $data->thumb;
											else
												$avatar = Uri::base().'components/com_community/assets/event_thumb.png';
										}
								?>
							<tr>

									<td >
									<img width="32" class="jticket_div_myticket_img" src="<?php echo $avatar;?>" />
									<?php if($integration==1){ ?>
									<a href="<?php echo CRoute::_('index.php?option=com_community&view=events&task=viewevent&eventid='.$data->eventid);?>"><?php echo $data->title;?></a>

									<?php }else if($integration==2){ ?>
									<a href="<?php echo Route::_('index.php?option=com_jticketing&view=event&eventid='.$data->eventid);?>"><?php echo $data->title;?></a>
									<?php } else if($integration==3){ ?>
											<a href="<?php echo Route::_('index.php?option=com_jevents&task=icalrepeat.detail&evid='.$data->eventid);?>"><?php echo $data->title;?></a>
									<?php }?>

									</td>
									<td align="center"> <?php echo $datetoshow ?></td>
									<td align="center"> <?php echo $timetoshow  ?></td>
									<td align="center"><?php echo $data->price .' ';?></td>
									<td align="center"><?php echo $data->ticketscount ?></td>
									<td align="center"><?php echo $data->totalamount;?></td>
									<td align="center"><?php echo $payment_statuses[$data->STATUS];?></td>
									<td	align="center">
										<?php
										if($data->STATUS=='C')
										{
											$link = Route::_('index.php?option=com_jticketing&view=mytickets&tmpl=component&layout=ticketprint&$jticketing_usesess=0&jticketing_eventid='.(int)$data->eventid.'&jticketing_userid='.(int)$data->user_id.'&jticketing_ticketid='.(int)$data->id.'&jticketing_order_items_id='.(int)$data->order_items_id);
											$modalConfig = array('width' => '800px', 'height' => '300px', 'modalWidth' => 80, 'bodyHeight' => 70);
											$modalConfig['url'] = $link;
											$modalConfig['title'] = Text::_('PREVIEW_DES');
											echo HTMLHelper::_('bootstrap.renderModal', 'jtEventsTicket' . $data->order_items_id, $modalConfig);
										?>
											<a data-bs-target="#jtEventsTicket<?php echo $data->order_items_id;?>" data-bs-toggle="modal">
												<span class="editlinktip hasTip" title="<?php echo Text::_('PREVIEW_DES');?>" ><?php echo Text::_('PREVIEW');?></span>
											</a>
										<?php
										}
										else
										 echo '-';
										?>
									</td>
					   </tr>
					<?php } ?>


						<tr>
						<td colspan="4" align="right"><?php echo Text::_('TOTAL');?></td>
						<td align="center"><b><?php echo $totalnooftickets;?></b></td>
						<td align="center"><b><?php echo $totalprice;?></b></td>
						<td ></td><td ></td>
						</tr>
					</table>
				</div>
			</div><!--row-->

					<div class="row">
						<div class="span12">
							<?php
								if(JVERSION<3.0)
									$class_pagination='pager';
								else
									$class_pagination='pagination';
							?>
							<div class="<?php echo $class_pagination; ?> com_jticketing_align_center">
								<div class="pager">
									<?php echo $this->pagination->getPagesLinks(); ?>
								</div>
							</div>
						</div><!--span12-->
					</div><!--row-->
			<input type="hidden" name="option" value="com_jticketing" />
			<input type="hidden" name="task" value="" />
			<input type="hidden" name="boxchecked" value="0" />
			<input type="hidden" name="controller" value="mytickets" />
			<input type="hidden" name="view" value="mytickets" />
			<input type="hidden" name="Itemid" value="<?php echo $this->Itemid; ?>" />
	</form>
</div><!--bootstrap-->
<?php

//if Jomsocial show JS Toolbar Footer
if($integration==1)
{
	$footer = '';
	$footer = JT::integration()->getJSfooter();

	if(!empty($footer))
	echo $footer;
}
//eoc for JS toolbar inclusion


