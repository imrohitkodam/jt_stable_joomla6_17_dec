<?php
// no direct access
defined( '_JEXEC' ) or die( ';)' );

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Layout\LayoutHelper;

// Joomla 6: formbehavior.chosen removed - using native select

$bootstrapclass="";
$tableclass="table table-striped  table-hover";
$document=Factory::getDocument();
$mainframe = Factory::getApplication();
$com_params=ComponentHelper::getParams('com_jticketing');
$integration = $com_params->get('integration');
$siteadmin_comm_per = $com_params->get('siteadmin_comm_per');
$show_js_toolbar = $com_params->get('show_js_toolbar');
$currency = $com_params->get('currency');
$jticketingmainhelper = new jticketingmainhelper();
$user =Factory::getUser();
$input=Factory::getApplication()->getInput();

if(empty($user->id))
{

	echo '<b>'.Text::_('USER_LOGOUT').'</b>';
	return;

}

$js_key = "
jQuery(document).ready(function (){
	Joomla.submitbutton = function(task){ ";

$js_key .= "
	document.adminForm.action.value=task;
	if (task =='cancel')
	{";
		$js_key .= "Joomla.submitform(task);";
		$js_key .= "
	}
}
});
";

$document->addScriptDeclaration($js_key);
$eventid = $this->lists['search_event'];

if(!$eventid)
$eventid = $input->get('event','','INT');
$linkbackbutton = '';

//eoc for JS toolbar inclusion
?>
<form action="" method="post" name="adminForm" id="adminForm">
	<div class="techjoomla-bootstrap">

		<div id="all" class="row-fluid">

			<?php
				if(JVERSION>=3.0):
					if(!empty( $this->sidebar)): ?>
					<div id="sidebar" >
						<div id="j-sidebar-container" class="span2">
							<?php echo $this->sidebar; ?>
						</div>

					</div>
					<?php if(JVERSION>'3.0') {?>

					<?php } ?>
				<div id="j-main-container" class="span10">
			<?php else : ?>
				<div id="j-main-container">
			<?php endif;
		endif;

		// Search tools bar
		?>
		<div class="row-fluid">
			<div class="span12">
				<div class="pull-left">
					<?php
					$search_event = $mainframe->getUserStateFromRequest( 'com_jticketingsearch_event', 'search_event','', 'string' );
					echo HTMLHelper::_('select.genericlist', $this->status_event, "search_event", 'class="ad-status" size="1" onchange="document.adminForm.submit();" name="search_event"',"value", "text", $search_event);
					?>
				</div>
				<div class="pull-right">
					<?php echo $this->pagination->getLimitBox(); ?>
				</div>
			</div>
		</div>
		<?php

		if(empty($this->Data))
		{
			?>
			<div class="alert alert-info af-mt-10"><?php echo Text::_('NODATA');?></div>
			<?php
			return;
		}
		?>
		<div class="clearfix">&nbsp;</div>
		<div class="table-responsive af-mt-10">
			<table 	class="table table-striped  table-hover">
				<tr>
					<th ><?php echo HTMLHelper::_( 'grid.sort','EVENT_NAME','title', $this->lists['order_Dir'], $this->lists['order']); ?></th>
					<th ><?php echo HTMLHelper::_( 'grid.sort','NUMBEROFTICKETS_SOLD','eticketscount', $this->lists['order_Dir'], $this->lists['order']); ?></th>
					<th align="center"><?php echo  Text::_( 'COM_JTICKETING_ORIGINAL_AMT' ); ?></th>
					<th align="center"><?php echo  Text::_( 'COM_JTICKETING_COUPON_DISCOUNT' ); ?></th>
					<th align="center"><?php echo  Text::_( 'COM_JTICKETING_AMOUNT_AFTER_DISCOUNT' ); ?></th>
					<th align="center"><?php echo  Text::_( 'COM_JTICKETING_ORDER_TAX' ); ?></th>
					<th align="center"><?php echo  Text::_( 'COM_JTICKETING_TOTAL_PAID' ); ?></th>
					<th align="center"><?php echo  Text::_( 'COM_JTICKETING_COMMISSION' ); ?></th>
					<th align="center"><?php echo  Text::_( 'COM_JTICKETING_NETAMTTOPAY_EVENT' ); ?></th>

				</tr>



				<?php
				$i = 0;

				foreach($this->Data as $data)
				{
					 if(empty($data->thumb))
						$data->thumb = Uri::root().'components/com_community/assets/event_thumb.png';
					 else
							$data->thumb = Uri::root().$data->thumb;
					$link = Route::_(Uri::base().'index.php?option=com_jticketing&view=attendees&filter[events]='.$data->event_details_id . '&filter[status]=A');
				?>
					<tr>
						<td>
								<a href="<?php echo $link;?>"><?php echo ucfirst($data->title);?></a>
						</td>
						<td align="center">
							<?php echo $data->eticketscount ?>
						</td>
						<td align="center">
							<?php echo $this->utilities->getFormattedPrice($data->eoriginal_amount);?>
						</td>
						<td align="center">
							<?php echo $this->utilities->getFormattedPrice($data->ecoupon_discount);?>
						</td>
						<td align="center">
							<?php echo $this->utilities->getFormattedPrice($data->eoriginal_amount - $data->ecoupon_discount);?>
						</td>
						<td align="center">
							<?php echo $this->utilities->getFormattedPrice($data->eorder_tax);?>
						</td>
						<td align="center">
							<?php echo $this->utilities->getFormattedPrice($data->eamount);?>
						</td>
						<td align="center">
							<?php echo $this->utilities->getFormattedPrice($data->ecommission);?>
						</td>
						<td align="center">
							<?php echo $this->utilities->getFormattedPrice($data->eamount - $data->ecommission);?>
						</td>

					</tr>
			<?php
					$i++;
				}
			?>
				<tr>
					<td align="center">
						<div class="">
							<b><?php echo Text::_('COM_JTICKETING_TOTAL_OVERALL_STATISTICS');?></b>
						</div>
					</td>
				
					<td align="center">
						<b><?php echo number_format($this->totalnooftickets, 0, '', '');?></b>
					</td>
					<td align="center">
						<b><?php echo $this->utilities->getFormattedPrice($this->totaloriginalamt);?></b>
					</td>
					<td align="center">
						<b><?php echo $this->utilities->getFormattedPrice($this->totaldiscount);?></b>
					</td>
					<td align="center">
						<b><?php echo $this->utilities->getFormattedPrice($this->amtafterDisc);?></b>
					</td>
					<td align="center">
						<b><?php echo $this->utilities->getFormattedPrice($this->totalordertax);?></b>
					</td>

					<td align="center">
						<b><?php echo $this->utilities->getFormattedPrice($this->totalamount);?></b>
					</td>
					<td align="center">
						<b><?php echo $this->utilities->getFormattedPrice($this->totalcommission);?></b>
					</td>
					<td align="center">
						<b><?php echo $this->utilities->getFormattedPrice($this->amtToBePaidEventowner);?></b>
					</td>
				</tr>
				<tfoot>
					<tr>
					<td colspan="9" align="center">
						<?php echo $this->pagination->getListFooter(); ?>
					</td>
					</tr>
				</tfoot>
			</table>
		</div>
		<input type="hidden" name="option" value="com_jticketing" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="defaltevent" value="<?php echo $this->lists['search_event'];?>" />
		<input type="hidden" name="controller" value="allticketsales" />
		<input type="hidden" name="view" value="allticketsales" />
		<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
	</div><!--row fluid -->
	</div><!--bootstrap-->
</form>
