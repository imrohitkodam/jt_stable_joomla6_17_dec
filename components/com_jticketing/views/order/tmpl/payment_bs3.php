<?php
/**
 * @package     JTicketing
 * @subpackage  com_jticketing
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
HTMLHelper::_('jquery.token');

/** @var $this JticketingViewOrder */

$document   = Factory::getDocument();
$document->addScriptDeclaration('var orderId = "' . $this->order->id . '";');
$document->addScriptDeclaration('var count = "' . count($this->gateways) . '";');
$document->addScriptDeclaration('var gateWayName = "' . $this->gateways[0]->id . '";');
$jtRouteHelper = new JTRouteHelper;
$billingLink   = $jtRouteHelper->JTRoute('index.php?option=com_jticketing&view=order&layout=billing&orderId=' . $this->order->id, false);
?>
<div class="row af-mt-10 tjBs3" id="jtwrap">
	<div class="col-sm-8">
		<?php echo $this->loadTemplate('event_info_' . JTICKETING_LOAD_BOOTSTRAP_VERSION); ?>
		<div class="panel af-bg-white af-br-5 border-gray visible-xs-block">
				<?php echo $this->loadTemplate('cart_' . JTICKETING_LOAD_BOOTSTRAP_VERSION); ?>
		</div>
		<div class="jticketing-checkout-content" id="payment-info-tab">
			<div id="payment-info" class="jticketing-checkout-steps form-horizontal">
				<div class="paymentHTMLWrapper af-mt-20">
					<h3 class="af-d-inline-block font-16 af-pl-15 af-m-0">
						<strong>
						<?php
						echo Text::_('COM_JTICKETING_SEL_GATEWAY');?>
						</strong>
					</h3>
					<a href="<?php echo $billingLink;?>" class="text-muted af-font-bold pull-right">
						<i class="fa fa-angle-left af-pr-5 af-font-bold" aria-hidden="true"></i>
						<span><?php echo Text::_('COM_JTICKETING_PREVIOUS');?></span>
					</a>
					<div class="panel-body af-p-0">
					<?php
					$default = "";
					$gatewayDivStyle = 1;

					if (!empty($this->gateways) && count($this->gateways) == 1)
					{
						$default = $this->gateways[0]->id;
					}
					?>
						<div class="container-fluid custom-radio-style" style="<?php echo ($gatewayDivStyle == 1)?"" : "display:none;" ?>">
							<?php
							if (empty($this->gateways))
							{
								echo Text::_('COM_JTICKETING_NO_PAYMENT_GATEWAY');
							}
							else
							{
								$orderID = $this->order->id;
								$addFun = "onChange = jtSite.order.gatewayHtml(this.value,$orderID)";
								echo HTMLHelper::_('select.radiolist', $this->gateways,
								'gateways', 'class="radio gatewaylabel required" ' . $addFun . '  ', 'id','name', $default, false);
							}
							?>
						</div>
						<?php
						if (empty($gatewayDivStyle))
						{
						?>
							<div class="col-md-10 col-sm-9 col-xs-12 qtc_left_top">
								<?php echo $this->escape($this->gateways[0]->name);?>
							</div>
						<?php
						}
						?>
						<hr class="hr hr-condensed"/>
							<div id="jticketing-payHtmlDiv"> </div>
						</div>
				</div>
			</div>
		</div>
	</div>
	<div class="col-sm-4 af-mb-20 hidden-xs">
		<div class="af-bg-white af-br-5 border-gray">
			<?php echo $this->loadTemplate('cart_' . JTICKETING_LOAD_BOOTSTRAP_VERSION); ?>
		</div>
	</div>
</div>
<script>
	jtSite.order.paymentInit();
</script>
