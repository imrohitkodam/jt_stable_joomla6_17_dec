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
defined('_JEXEC') or die( ';)' );

use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;
use Joomla\String\StringHelper;
use Joomla\CMS\Factory;

/** @var $this JticketingVieworders */
/* If user is on payment layout and log out at that time undefined order is found
 in such condition send to home page or provide error msg
 */

$freeticket	= '';

if (!empty($this->userInfo))
{
	$this->userInfo->user_email = $this->escape($this->userInfo->user_email);
}

if (!empty($this->eventdetails))
{
	$freeticket = Text::_('ETICKET_PRINT_DETAILS_FREE');
	$freeticket = StringHelper::str_ireplace('[EVENTNAME]', $this->eventdetails->getTitle(), $freeticket);
	$freeticket = StringHelper::str_ireplace('[EMAIL]', isset($this->userInfo->user_email) ? $this->userInfo->user_email : '', $freeticket);
}
?>

<div class="container-fluid invoice">
	<h4 class="overflow-hidden">
		<input type="button" class="btn-w-50 btn btn-default btn-success no-print pull-right" onclick="jtSite.orders.printDiv()"
		value="<?php echo Text::_('COM_JTICKEING_PRINT');?>">
	</h4>
<div id="printDiv">
	<div class="panel panel-default">
		<div class="panel-body">
				<div class="row">
					<div class="col-xs-12">
						<div class="invoice-title">
							<?php
							if (!empty($this->userInfo))
							{
								?>
								<div class="overflow-hidden af-mb-15 border-bottom">
								<h2 class="pull-left af-mt-0"><?php echo Text::_('JT_ORDERS_REPORT'); ?></h2>
								<h5 class="pull-right af-mt-10"><strong> <?php echo Text::_('COM_JTICKETING_ORDER_ID') , ':'; ?> <?php echo $this->orderinfo->order_id; ?></strong></h5>
								</div>
								<?php
									if ($this->orderinfo->getAmount(false) <= 0)
									{
										echo $freeticket;
									}
									elseif ($this->orderinfo->getStatus() == COM_JTICKETING_CONSTANT_ORDER_STATUS_PENDING)
									{
										echo Text::sprintf('ETICKET_PRINT_DETAILS', $this->userInfo->user_email);
									}
							}?>

							<h3 class="text-center"><?php echo $this->eventdetails->getTitle(); ?></h3>
						</div>
						</div>
					</div>
						<div class="row">
							<div class="bill-to-details col-md-6 float-start">
								<address class="af-mb-10">
								<strong><?php echo Text::_('COM_JTICKETING_BILLED_TO'); ?></strong><br>
								<?php
								if (!empty($this->userInfo))
								{
									if (!empty($this->userInfo->firstname) && $this->userInfo->registration_type == 0)
									{
										echo $this->escape($this->userInfo->firstname) . ' ' . $this->escape($this->userInfo->lastname);
									}
									else
									{
										echo (!empty($this->userInfo->business_name) ? '<span>' . $this->escape($this->userInfo->business_name) . '</span>' : '');
									}

									echo (isset($this->userInfo->phone) ? '<span>' . $this->userInfo->phone . '</span>' : '') ;

									echo (isset($this->userInfo->user_email) ? '<span>' . $this->escape($this->userInfo->user_email) . '</span>' : '') ;

									echo "<span>";
									if (!empty($this->userInfo->city))
									{
										$this->userInfo->address = !empty($this->userInfo->address) ? $this->userInfo->address : '';
										$this->userInfo->address = $this->escape($this->userInfo->address);
										$this->userInfo->city    = $this->escape($this->userInfo->city);
										echo $this->userInfo->address . ',' . $this->userInfo->city;
									}

									if (!empty($this->userInfo->zipcode))
									{
										echo '-' . $this->escape($this->userInfo->zipcode);

										if (!empty($this->userInfo->state_code))
										{
											echo ", " . $this->TjGeoHelper->getRegionNameFromId($this->userInfo->state_code);
										}

										if (!empty($this->userInfo->country_code))
										{
											echo ", " . $this->TjGeoHelper->getCountryNameFromId($this->userInfo->country_code);
										}
									}

									echo "</span>";

									if (!empty($this->userInfo->vat_number))
									{
										echo '<span>' . Text::_('COM_JTICKETING_ORDER_VAT_NUMBER');

										echo ' - ' . $this->escape($this->userInfo->vat_number) . '</span>';
									}
								}
								?>
								</address>
							</div>
							<div class="bill-to-details col-md-6 float-end">
								<address class="af-mb-10 text-end">
									<?php
									$vendorDetails = '';

									if (JT::config()->get('display_vendor_details'))
									{
										$vendor = $this->eventdetails->getVendorDetails();
										$vendorDetails .= !empty($vendor->vendor_title) ? '<span>' . $this->escape($vendor->vendor_title) . '</span>': '';
									    $vendorDetails .=  !empty($vendor->address) ?  '<span>' . $this->escape($vendor->address) . '/<span>': '';
									    $vendorDetails .=  !empty($vendor->vat_number) ?  '<span>' . $this->escape($vendor->vat_number) . '</span>': '';
									}
									else
									{
								 		$vendorDetails .= !empty($this->companyName) ? '<span>' . $this->escape($this->companyName) . '</span>': '';
										$vendorDetails .= !empty($this->companyAddress) ? '<span>' . $this->escape($this->companyAddress) . '</span>': '';
										$vendorDetails .= !empty($this->companyVatNo) ? '<span>' . $this->escape($this->companyVatNo) . '</span>' : '';
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
								<address class="">
									<div class="col-xs-6 pull-left">
										<h5><strong><?php echo Text::_('COM_JTICKETING_ORDER_PAYMENT_STATUS'); ?></strong></h5>  <?php echo $this->paymentStatuses[$this->orderinfo->getStatus()]; ?><br>
									</div>
									<div class="col-xs-6">
										<div class=" pull-right">
											<h5><strong><?php echo Text::_('COM_JTICKETING_ORDER_DATE');?></strong><h5>
											<?php echo $this->utilities->getFormatedDate($this->orderinfo->cdate);?>
										</div>
									</div>
								</address>
							</div>
						</div>
						<div class="">
							<?php
								if ($this->orderinfo->processor=="bycheck" || $this->orderinfo->processor=="byorder")
								{ ?>
									<div class="col-xs-12 af-mt-20">
										<address>
											<h5><strong><?php echo Text::_('COM_JTICKETING_PAYMENT_INFO'); ?></strong></h5>
											<?php echo $this->jticketingparams->get('plugin_mail','');?>
										</address>
									</div>
									<?php
								} ?>
						</div>
					</div>
				<?php if (!empty($this->ticketTypes)) : ?>
				<div class="row">
					<div class="col-md-12">
						<div class="panel panel-default">
							<div class="panel-heading">
								<h3 class="panel-title"><strong><?php echo Text::_('COM_JTICKETING_TICKET_INFO'); ?></strong></h3>
							</div>
							<div class="panel-body">
								<div class="table-responsive">
									<table class="table table-borderd table-condensed">
										<thead>
											<tr>

												<td><strong><?php echo Text::_('COM_JTICKETING_NO'); ?></strong></td>
												<td class="text-left"><strong><?php echo  Text::_('COM_JTICKETING_PRODUCT_NAM'); ?></strong></td>
												<td class="text-left"><strong><?php echo Text::_('COM_JTICKETING_PRODUCT_QTY'); ?></strong></td>
												<td class="text-left"><strong><?php echo Text::_('COM_JTICKETING_PRODUCT_PRICE'); ?></strong></td>
												<?php
												// Set flag if fee is applied to the order
												$isFeePerOrder = $isFeePerTicket = false;
												if (!$this->jticketingparams->get('admin_fee_mode') && $this->jticketingparams->get('admin_fee_level') == 'order')
												{
													$isFeePerOrder = true;
												}
												elseif (!$this->jticketingparams->get('admin_fee_mode') && $this->jticketingparams->get('admin_fee_level') == 'orderitem')
												{
													$isFeePerTicket = true;
												}

												// Make a column html for aligning the columns.
												if ($isFeePerTicket)
												{
													$tdHtml = "<td class='thick-line'> </td>
																<td class='thick-line'> </td>
																<td class='thick-line'> </td>
																<td class='thick-line'> </td>";
													?>
													<td class="text-left"><strong><?php echo Text::_('COM_JTICKETING_PRODUCT_FEE'); ?></strong></td>
												<?php }
												else
												{
													$tdHtml = "<td class='thick-line'> </td>
																<td class='thick-line'> </td>
																<td class='thick-line'> </td>";
												}
												?>
												<td class="text-left"><strong><?php echo Text::_('COM_JTICKETING_PRODUCT_TPRICE'); ?></strong></td>
											</tr>
										</thead>
										<tbody>
											<?php
												$i = 1;

												foreach ($this->ticketTypes as $type)
												{
													$item 		= $this->orderinfo->getItemsByType($type->id);

													if (!isset($item->price))
													{
														$item->price = 0;
													}
													?>
													<tr>
														<td><?php echo $i++;?></td>
														<td class="text-left"><?php echo $this->escape($type->title);?></td>
														<td class="text-left"><?php echo $item->count;?></td>
														<td class="text-left"><?php echo $this->utilities->getFormattedPrice($item->price);?></td>
													<?php if ($isFeePerTicket)
														{ ?>
															<td class="text-left"><?php echo $this->utilities->getFormattedPrice($item->totalFee);?>

													<?php } ?>
														<td class="text-left">
															<?php echo $this->utilities->getFormattedPrice($item->totalPrize);?>
														</td>
													</tr>
													<?php
												}
												?>
											<tr>
												<?php echo $tdHtml; ?>
												<td class="thick-line text-left">
													<strong><?php echo Text::_('COM_JTICKETING_PRODUCT_TOTAL'); ?></strong>
												</td>
												<td class="thick-line text-lfet">
													<?php
													// Check if fee is per order or regular/none.
													if ($isFeePerOrder || $this->jticketingparams->get('admin_fee_mode'))
													{ ?>
														<span id= "cop_discount" ><?php echo $this->utilities->getFormattedPrice($this->orderinfo->getOriginalAmount());?> </span>
												<?php }
													elseif($isFeePerTicket)
													{ ?>
														<span id= "cop_discount" ><?php echo $this->utilities->getFormattedPrice($this->orderinfo->getOriginalAmount() + $this->orderinfo->getFee(false));?> </span>
												<?php } ?>
												</td>
											</tr>
											<?php

											if ($isFeePerOrder)
											{
											?>
											<tr>
												<?php echo $tdHtml; ?>
												<td class="thick-line text-left">
													<strong><?php echo Text::_('COM_JTICKETING_ADMIN_ORDERS_VIEW_TOTAL_FEE'); ?></strong>
												</td>
												<td class="thick-line text-lfet">
													<span id= "cop_discount" ><?php echo $this->orderinfo->getFee();
													?>
													</span>
												</td>
											</tr>
											<?php
											}

											if ($this->orderinfo->get('coupon_discount') > 0)
											{
												?>
												<tr>
													<?php echo $tdHtml; ?>
													<td class="thick-line text-left">
														<strong><?php echo sprintf(Text::_('COM_JTICKETING_PRODUCT_DISCOUNT'),$this->orderinfo->getCouponCode()); ?>
														</strong>
													</td>
													<td class="no-line text-left">
														<span id= "coupon_discount" >
														<?php echo $this->orderinfo->getCouponDiscount();
														?>
														</span>
													</td>
												</tr>
												<tr class="dis_tr">
													<?php echo $tdHtml; ?>
													<td class="thick-line text-left">
														<strong><?php echo Text::_('COM_JTICKETING_NET_AMT_PAY');?></strong>
													</td>
													<td class="no-line text-left">
														<span id= "total_dis_cop" >
														<?php
														echo $this->utilities->getFormattedPrice($this->orderinfo->getAmount(false));
														?>
														</span>
													</td>
												</tr>
												<?php
											}

											if ($this->orderinfo->getOrderTax() && $this->orderinfo->getOrderTax() > 0)
												{
													$taxDetails = new Registry($this->orderinfo->getTaxDetails());

													foreach ($taxDetails as $taxDetail)
													{
														foreach ($taxDetail->breakup as $breakup)
														{
													?>
													<tr>
														<?php echo $tdHtml; ?>
														<td class="thick-line text-lfet">
															<strong>
																	<?php echo Text::sprintf('TAX_AMOOUNT', $breakup->percentage) . "%"; ?>
															</strong>
														</td>
														<td class="no-line text-left">
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
												<?php echo $tdHtml; ?>
												<td class="thick-line text-left">
													<strong><?php echo Text::_('COM_JTICKETING_ORDER_TOTAL'); ?></strong>
												</td>
												<td class="no-line text-left">
													<strong>
														<span id="final_amt_pay" name="final_amt_pay"><?php echo $this->utilities->getFormattedPrice($this->orderinfo->getAmount(false)); ?>
														</span>
													</strong>
												</td>
											</tr>
										</tbody>
									</table>
								</div>
							</div>
						</div>
					</div>
				</div>
				<?php endif; ?>
		</div>
		</div>
	</div>
</div>
<?php
Factory::getDocument()->addScriptDeclaration("
	var rootUrl = '" . Uri::root() . "';
	var orderID = '" . $this->orderinfo->order_id . "';
	jtSite.orders.initOrdersJs();");
