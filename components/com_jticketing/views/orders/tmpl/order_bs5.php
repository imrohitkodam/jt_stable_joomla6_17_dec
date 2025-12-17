<?php
/**
 * @package     JTicketing
 * @subpackage  com_jticketing
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die( ';)' );

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;

/* If user is on payment layout and log out at that time undefined order is found
 in such condition send to home page or provide error msg
 */

/** @var $this JticketingVieworders */

$rootUrl  	= Uri::root();
$freeticket	= '';
$target = "target='_blank'";

if (!empty($this->event))
{
	$freeticket = Text::_('ETICKET_PRINT_DETAILS_FREE');
	$freeticket = str_replace('[EVENTNAME]', $this->event->getTitle(), $freeticket);
	$freeticket = str_replace('[EMAIL]', isset($this->useInfo->user_email) ? $this->useInfo->user_email : '', $freeticket);
}
?>
<div class="container xs-p-0 tjBs5">
	<?php
		?>

	<?php
		?>
	<div class="card af-mt-30">
		<h4 class="overflow-hidden af-my-0">
			<input type="button" class="btn-w-50 btn btn-success no-print float-end" onclick="jtSite.orders.printDiv()"
				value="<?php echo Text::_('COM_JTICKEING_PRINT');?>">
				<?php if ($this->tmpl !== 'component')
				{
					$target = ''; ?>
					<a class="btn-w-50 btn btn-default btn-info no-print" href="<?php echo $this->event->getUrl(); ?>">
				<?php echo Text::_('COM_JTICKETING_BACK_EVENT'); ?></a>
			<?php } ?>
		</h4>
		<div class="card-body">
			<div id="printDiv">
				<div class="row">
					<div class="col-xs-12">
						<div class="invoice-title">
							<?php
								if (!empty($this->useInfo))
								{
									?>
							<div class="overflow-hidden af-mb-15 border-bottom">
								<h2 class="float-start af-mt-0"><?php echo Text::_('JT_ORDERS_REPORT'); ?></h2>
								<h5 class="float-end af-mt-10"><strong> <?php echo Text::_('COM_JTICKETING_ORDER_ID') , ':'; ?> <?php echo $this->orderinfo->order_id; ?></strong></h5>
							</div>
							<?php
								if ($this->orderinfo->getAmount(false) <= 0)
								{
									echo $freeticket;
								}
								elseif ($this->orderinfo->getStatus() == COM_JTICKETING_CONSTANT_ORDER_STATUS_PENDING)
								{
									echo Text::sprintf('ETICKET_PRINT_DETAILS', $this->useInfo->user_email);
								}
								}?>
							<h3 class="text-center"><?php echo $this->event->getTitle(); ?></h3>
						</div>
						<div class="row">
							<div class="bill-to-details col-6">
								<address class="af-mb-10">
									<strong><?php echo Text::_('COM_JTICKETING_BILLED_TO'); ?></strong><br>
									<?php
										if (!empty($this->useInfo))
										{
											if (!empty($this->useInfo->firstname) && empty($this->useInfo->business_name))
											{
												$this->useInfo->firstname 	= $this->escape($this->useInfo->firstname);
												$this->useInfo->lastname  	= $this->escape($this->useInfo->lastname);
												echo $this->escape($this->useInfo->firstname) . ' ' . $this->escape($this->useInfo->lastname);
											}
											else
											{
												echo '<span>' . (!empty($this->useInfo->business_name) ? $this->escape($this->useInfo->business_name) : '') . '</span>' ;
											}

											echo '<span>' . (!empty($this->useInfo->phone) ? $this->useInfo->phone : '') . '</span>' ;

											echo '<span>' . (!empty($this->useInfo->user_email) ? $this->escape($this->useInfo->user_email) : '') . '</span>' ;
											echo "<span>";

											if (!empty($this->useInfo->city))
											{
												$this->useInfo->address = !empty($this->useInfo->address) ? $this->useInfo->address : '';

												echo $this->useInfo->address . ',';
											}

											echo '</span>';

											if (!empty($this->useInfo->zipcode))
											{

												echo $this->escape($this->useInfo->city) . '-' . $this->escape($this->useInfo->zipcode);

												if (!empty($this->useInfo->state_code))
												{
													echo ", " . $this->TjGeoHelper->getRegionNameFromId($this->useInfo->state_code);
												}

												if (!empty($this->useInfo->country_code))
												{
													echo ", " . $this->TjGeoHelper->getCountryNameFromId($this->useInfo->country_code);
												}
											}

											if (!empty($this->useInfo->vat_number))
											{
												echo '<span>' . Text::_('COM_JTICKETING_ORDER_VAT_NUMBER');

												echo ' - ' . $this->escape($this->useInfo->vat_number) . '</span>';
											}
										}
										?>
								</address>
							</div>
							<div class="bill-to-details col-6 ">
								<address class="af-mb-10 text-end">
									<?php
									$vendorDetails = '';

									if (JT::config()->get('display_vendor_details'))
									{
										$vendor = $this->event->getVendorDetails();

										$vendorDetails .= (!empty($vendor->vendor_title) ? '<span>' . $this->escape($vendor->vendor_title) . '</span>' : '');
									    $vendorDetails .= (!empty($vendor->address) ?  '<span> ' . $this->escape($vendor->address) . '</span>' : '');
									    $vendorDetails .= (!empty($vendor->vat_number) ?  '<span> ' . $this->escape($vendor->vat_number) . '</span>' : '');
									}
									else
									{
								 		$vendorDetails .= (!empty($this->company_name) ? '<span> ' . $this->escape($this->company_name) .'</span>': '');
										$vendorDetails .= (!empty($this->company_address) ? '<span> ' . $this->escape($this->company_address). '</span>' : '');
										$vendorDetails .= (!empty($this->company_vat_no) ? '<span> ' . $this->escape($this->company_vat_no) . '</span>' : '');
									}

									if (!empty(trim($vendorDetails)))
									{
									?>
										<strong><?php echo Text::_('COM_JTICKETING_VENDOR_DETAILS'); ?></strong><br>
									<?php
										echo $vendorDetails;
									}
									?>
								</address>
							</div>
						</div>
						<div class="row border-bottom">
							<div class="col-6">
								<h5><strong><?php echo Text::_('COM_JTICKETING_ORDER_PAYMENT_STATUS'); ?></strong></h5>
								<?php echo $this->payment_statuses[$this->orderinfo->getStatus()]; ?><br>
							</div>
							<div class="col-6 text-end">
									<h5>
									<strong><?php echo Text::_('COM_JTICKETING_ORDER_DATE');?></strong>
									<h5>
									<?php echo $this->utilities->getFormatedDate($this->orderinfo->cdate);?>
							</div>
						</div>
					</div>
					<div class="">
						<?php
							if ($this->orderinfo->processor=="bycheck" || $this->orderinfo->processor=="byorder")
							{
								?>
						<div class="col-xs-12 af-mt-20">
							<address>
								<h5><strong><?php echo Text::_('COM_JTICKETING_PAYMENT_INFO'); ?></strong></h5>
								<?php echo $this->jticketingparams->get('plugin_mail','');?>
							</address>
						</div>
						<?php
							}
							?>
					</div>
				</div>
				<?php
				$ExclusiveFee = (!$this->jticketingparams->get('admin_fee_mode') && $this->jticketingparams->get('admin_fee_level') != 'order');
				$exclusiveOrderFee = (!$this->jticketingparams->get("admin_fee_mode") && $this->jticketingparams->get('admin_fee_level') == 'order');
				?>
				<div class="row">
					<div class="col-md-12">
						<div class="card">
							<div class="card-header">
								<h3 class="card-title"><strong><?php echo Text::_('COM_JTICKETING_TICKET_INFO'); ?></strong></h3>
							</div>
							<div class="card-body">
								<div class="table-responsive">
									<table class="table table-borderd table-condensed">
										<thead>
											<tr>
												<td><strong><?php echo Text::_('COM_JTICKETING_NO'); ?></strong></td>
												<td class="text-start"><strong><?php echo  Text::_('COM_JTICKETING_PRODUCT_NAM'); ?></strong></td>
												<td class="text-start"><strong><?php echo Text::_('COM_JTICKETING_PRODUCT_QTY'); ?></strong></td>
												<td class="text-start"><strong><?php echo Text::_('COM_JTICKETING_PRODUCT_PRICE'); ?></strong></td>
												<?php

												$emptyTableaHead = '</td><td class="no-line"></td><td class="no-line"></td><td class="no-line"></td>';

												if ($ExclusiveFee)
													{
														$emptyTableaHead = '<td class="no-line"></td><td class="no-line"></td><td class="no-line"></td><td class="no-line"></td>';
														?>
												<td class="text-start"><strong><?php echo Text::_('COM_JTICKETING_PRODUCT_FEE'); ?></strong></td>
												<?php }?>
												<td class="text-start"><strong><?php echo Text::_('COM_JTICKETING_PRODUCT_TPRICE'); ?></strong></td>
											</tr>
										</thead>
										<tbody>
											<?php
												$i = 1;

												foreach ($this->ticketTypes as $type)
												{
													$item = $this->orderinfo->getItemsByType($type->id);

													if (!isset($type->price))
													{
														$type->price = 0;
													}
													?>
											<tr>
												<td><?php echo $i++;?></td>
												<td class="text-start"><?php echo $this->escape($type->title);?></td>
												<td class="text-start"><?php echo $item->count;?></td>
												<td class="text-start"><?php echo $this->utilities->getFormattedPrice($type->price);?></td>
												<?php if ($ExclusiveFee)
													{ ?>
												<td class="text-start"><?php echo $this->utilities->getFormattedPrice($item->totalFee);?>
													<?php } ?>
												<td class="text-start">
													<?php
													if (JT::config()->get('admin_fee_mode'))
													{
														echo $this->utilities->getFormattedPrice($item->totalPrize);
													}
													else
													{
														echo $this->utilities->getFormattedPrice($item->totalPrize + $item->totalFee);
													}
													?>
												</td>
											</tr>
											<?php
												}
												?>
											<tr>
												<?php echo $emptyTableaHead;?>
												<td class="thick-line text-start">
													<strong><?php echo Text::_('COM_JTICKETING_PRODUCT_TOTAL'); ?></strong>
												</td>
												<td class="thick-line text-lfet">
													<span><?php echo $this->utilities->getFormattedPrice($this->orderinfo->getNetAmount());?> </span>
												</td>
											</tr>
											<?php
											if ($exclusiveOrderFee)
												{
												?>
											<tr>
												<?php echo $emptyTableaHead;?>
												<td class="thick-line text-start">
													<strong><?php echo Text::_('COM_JTICKETING_ORDER_TOTAL_FEE'); ?></strong>
												</td>
												<td class="thick-line text-lfet">
													<span><?php echo $this->orderinfo->getFee();?></span>
												</td>
											</tr>
											<?php
												}

												if ($this->orderinfo->get('coupon_discount') > 0)
												{
													?>
											<tr>
												<?php echo $emptyTableaHead;?>
												<td class="thick-line text-start">
													<strong><?php echo sprintf(Text::_('COM_JTICKETING_PRODUCT_DISCOUNT'), $this->orderinfo->getCouponCode()); ?>
													</strong>
												</td>
												<td class="no-line text-start">
													<span id= "coupon_discount" >
													<?php echo $this->orderinfo->getCouponDiscount();?>
													</span>
												</td>
											</tr>
											<tr class="dis_tr">
												<?php echo $emptyTableaHead;?>
												<td class="thick-line text-start">
													<strong><?php echo Text::_('COM_JTICKETING_NET_AMT_PAY');?></strong>
												</td>
												<td class="no-line text-start">
													<span id= "total_dis_cop" >
													<?php
														echo $this->utilities->getFormattedPrice($this->orderinfo->getAmount(false));
														?>
													</span>
												</td>
											</tr>
											<?php
												}
													if ($this->orderinfo->getOrderTax() > 0)
													{
														$taxDetails = new Registry($this->orderinfo->getTaxDetails());

														foreach ($taxDetails as $taxDetail)
														{
															foreach ($taxDetail->breakup as $breakup)
															{
														?>
															<tr>
														<?php echo $emptyTableaHead;?>
																<td class="thick-line text-lfet">
																	<strong>
																	<?php echo Text::sprintf('TAX_AMOOUNT', $breakup->percentage) . "%"; ?>
																	</strong>
																</td>
																<td class="no-line text-start">
																	<span id= "tax_amt">
																	<?php echo $this->utilities->getFormattedPrice($breakup->value); ?>
																	</span>
																</td>
															</tr>
														<?php
														}
													}
												}

												?>
											<tr>
												<?php echo $emptyTableaHead;?>
												<td class="thick-line text-start">
													<strong><?php echo Text::_('COM_JTICKETING_ORDER_TOTAL'); ?></strong>
												</td>
												<td class="no-line text-start">
													<strong>
													<span id="final_amt_pay"><?php echo $this->utilities->getFormattedPrice($this->orderinfo->getAmount(false)); ?>
													</span>
													</strong>
												</td>
											</tr>
											<?php
											if ($this->orderinfo->getStatus() != COM_JTICKETING_CONSTANT_ORDER_STATUS_COMPLETED && $this->orderinfo->getStatus() != COM_JTICKETING_CONSTANT_ORDER_STATUS_INCOMPLETE)
												{
													?>
											<tr>
												<?php echo $emptyTableaHead;?>
												<td></td>
												<td class="thick-line text-start">

													<?php
														$jtRouteHelper = new JTRouteHelper;
														$paymentLink = $jtRouteHelper->JTRoute('index.php?option=com_jticketing&view=order&layout=payment&orderId=' . $this->orderinfo->id, false);
														if (in_array($this->orderinfo->processor, ['byorder', 'bycheck']))
														{
															?>
																<div class="alert alert-info" role="alert">
																	<?php echo Text::_('COM_JTICKETING_ORDER_PENDING_WITH_OFFLINE_WARNING_MESSAGE'); ?>
																</div>
															<?php
														}
													?>
													<a href="<?php echo $paymentLink; ?>" <?php echo $target; ?> class="btn btn-sm btn-primary no-print"><?php echo Text::_('COM_JTICKETING_RETRY_PAYMENT'); ?>
													</a>
												</td>
											</tr>
											<?php
												}
												?>
										</tbody>
									</table>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<div style="clear:both;"></div>
<div class="col-xs-12" id="html-container"></div>
<?php 
Factory::getDocument()->addScriptDeclaration('
	var rootUrl = "' . $rootUrl . '";
	var orderID = "' . $this->orderinfo->id . '";
	jtSite.orders.initOrdersJs();
');
