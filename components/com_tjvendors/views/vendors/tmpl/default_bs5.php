<?php
/**
 * @package     TJVendors
 * @subpackage  com_tjvendors
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access to this file
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

HTMLHelper::_('behavior.multiselect'); // only for list tables


$listOrder = $this->state->get('list.ordering');
$listDirn  = $this->state->get('list.direction');
?>

<script type="text/javascript">
	jQuery(document).ready(function () {
		jQuery('#clear-search-button').on('click', function () {
			jQuery('#filter_search').val('');
			jQuery('#adminForm').submit();
		});
	});

	jQuery(document).ready(function () {
		jQuery('#clear-calendar').on('click', function () {
			jQuery('#date').val('');
			jQuery('#dates').val('');
			jQuery('#adminForm').submit();
		});
	});

	tjVSite.vendors.initVendorsJs();
</script>
<style>
#tjv-wrapper th{
	white-space: inherit;
}
</style>
<div id="tjv-wrapper" class="<?php echo COM_TJVENDORS_WRAPPAER_CLASS;?>  tjBs5 container">
	<h1>
		<?php echo Text::_('COM_TJVENDOR_VENDOR_PAYOUT_REPORTS'); ?>
	</h1>

	<?php
	$user_id = Factory::getUser()->id;

	if (!empty($this->vendor_id))
	{
		?>
		<form action="<?php echo Route::_('index.php?option=com_tjvendors&view=vendors&client=' . $this->input->get('client', '', 'STRING') . '&Itemid=' . $this->vendorItemID);?>" method="post" id="adminForm" name="adminForm">

			<div id="j-main-container" class="vendor-report">
				<!-----"vendor-report" is a page cover class--->
				<div class="row">
					<div class="col-xs-12 col-md-12 date">
						<div class="row">
							<span class="col-md-3">
								<?php echo HTMLHelper::_('calendar', $this->state->get('filter.fromDate'), 'fromDates', 'dates', '%Y-%m-%d', array('class' => 'inputbox date__field', 'onchange' => 'document.adminForm.submit()', 'placeholder' => Text::_('COM_TJVENDORS_FROM'))); ?>
							</span>

							<span class="col-md-3">
								<?php echo HTMLHelper::_('calendar', $this->state->get('filter.toDate'), 'toDates', 'date', '%Y-%m-%d', array('class' => 'inputbox date__field', 'onchange' => 'document.adminForm.submit()', 'placeholder' => Text::_('COM_TJVENDORS_TO'))); ?>
							</span>

							<span class="col-md-2">
								<button class="btn btn-primary" id="clear-calendar" type="button" title="<?php echo Text::_('JSEARCH_CALENDAR_CLEAR'); ?>">
									<i class="icon-remove"></i>
								</button>
							</span>
						</div>
					</div>

					<div class="col-xs-12 col-md-12 btn-group">
						<ul class="input-group list-inline justify-content-md-end">
							<?php
							if (!empty($this->currencies))
							{
								?>
								<li>
									<div class="input-group-btn ms-2 mt-2 hidden-xs">
										<?php
										echo HTMLHelper::_('select.genericlist', $this->currencies, "currency", 'class="input-medium" size="1" onchange="document.adminForm.submit();"', "currency", "currency", $this->state->get('filter.currency'));
										$currency = $this->state->get('filter.currency');
										?>
									</div>
								</li>
								<?php
							}

							if ($this->vendorClient == '')
							{
								?>
								<li>
									<div class="input-group-btn ms-2 mt-2">
										<?php
										echo HTMLHelper::_('select.genericlist', $this->uniqueClients, "vendor_client", 'class="input-medium" size="1" onchange="document.adminForm.submit();"', "clientType", "clientValue", $this->state->get('filter.vendor_client'));
										$clientFilter = $this->state->get('filter.vendor_client');
										?>
									</div>
								</li>
								<?php
							}
							?>

							<li>
								<div class="input-group-btn hidden-xs ms-2 mt-2">
									<?php
									$transactionType[] = array("transactionType" => Text::_('COM_TJVENDORS_REPORTS_FILTER_ALL_TRANSACTIONS'), "transactionValue" => "0");
									$transactionType[] = array("transactionType" => Text::_('COM_TJVENDORS_REPORTS_FILTER_CREDIT'), "transactionValue" => Text::_('COM_TJVENDORS_REPORTS_FILTER_CREDIT'));
									$transactionType[] = array("transactionType" => Text::_('COM_TJVENDORS_REPORTS_FILTER_DEBIT'), "transactionValue" => Text::_('COM_TJVENDORS_REPORTS_FILTER_DEBIT'));

									echo HTMLHelper::_('select.genericlist', $transactionType, "transactionType", 'class="input-medium" size="1" onchange="document.adminForm.submit();"', "transactionValue", "transactionType", $this->state->get('filter.transactionType'));

									$transactionType = $this->state->get('filter.transactionType'); ?>
								</div>
							</li>

							<li class="pr-0">
								<div  class="input-group-btn hidden ms-2 mt-2">
									<label for="limit" >
										<?php echo Text::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC'); ?>
									</label>
									<?php echo $this->pagination->getLimitBox();?>
								</div>
							</li>
						</ul>
					</div>
				</div>

				<hr />

				<div class="row">
					<?php
					if (empty($this->items))
					{
						?>
						<div class="col-xs-12">
							<div class="alert alert-info">
								<?php echo Text::_('COM_TJVENDOR_NO_MATCHING_RESULTS'); ?>
							</div>
						</div>
						<?php
					}
					else
					{
						?>
						<div class="col-xs-12">
							<div class="alert alert-info">
								<?php echo Text::_('COM_TJVENDORS_REPORTS_CREDIT_NOTE'); ?>
								<?php echo Text::_('COM_TJVENDORS_REPORTS_DEBIT_NOTE'); ?>
							</div>
						</div>

						<div class="row">
							<div class="col-xs-12">
								<div id="no-more-tables">
									<table class="table table-striped table-bordered table-hover">
										<thead>
											<tr>
												<th width="1%">
													<?php echo Text::_('COM_TJVENDORS_VENDORS_SR_NO');?>
												</th>

												<th width="5%">
													<?php echo HTMLHelper::_('grid.sort', 'COM_TJVENDORS_REPORTS_TRANSACTION_ID', 'pass.`transaction_id`', $listDirn, $listOrder);?>
												</th>

												<?php
												if ($this->vendorClient == '' && $clientFilter == 'all')
												{
													?>
													<th width="5%">
														<?php echo Text::_('COM_TJVENDORS_REPORTS_CLIENT');?>
													</th>
													<?php
												}

												if ($currency == '0')
												{
													?>
													<th width="5%">
														<?php echo HTMLHelper::_('grid.sort', 'COM_TJVENDORS_REPORTS_CURRENCY', 'pass.`currency`', $listDirn, $listOrder); ?>
													</th>
													<?php
												}

												if ($transactionType == "Credit" || empty($transactionType))
												{
													?>
													<th class='left' width="10%">
														<?php echo HTMLHelper::_('grid.sort', 'COM_TJVENDORS_REPORTS_CREDIT_AMOUNT', 'pass.`credit`', $listDirn, $listOrder);?>
													</th>
													<?php
												}

												if ($transactionType == "Debit" || empty($transactionType))
												{
													?>
													<th class='left' width="10%">
														<?php echo HTMLHelper::_('grid.sort', 'COM_TJVENDORS_REPORTS_DEBIT_AMOUNT', 'pass.`debit`', $listDirn, $listOrder);?>
													</th>
													<?php
												}
												?>

												<th width="10%">
													<?php echo HTMLHelper::_('grid.sort', 'COM_TJVENDORS_REPORTS_REFERENCE_ORDER_ID', 'pass.`reference_order_id`', $listDirn, $listOrder);?>
												</th>

												<th width="15%">
													<?php echo HTMLHelper::_('grid.sort', 'COM_TJVENDORS_REPORTS_TRANSACTION_TIME', 'pass.`transaction_time`', $listDirn, $listOrder);?>
												</th>

												<th width="10%">
													<?php echo HTMLHelper::_('grid.sort', 'COM_TJVENDORS_REPORTS_PENDING_AMOUNT', 'pass.`total`', $listDirn, $listOrder);?>
												</th>

												<th>
													<?php echo Text::_('COM_TJVENDORS_REPORTS_ENTRY_STATUS'); ?>
												</th>

												<th>
													<?php echo Text::_('COM_TJVENDORS_REPORTS_CUSTOMER_NOTE'); ?>
												</th>
											</tr>
										</thead>

										<tfoot>
											<td colspan="5" class="hidden-xs">
												<?php if ($currency != '0'): ?>
													<div class="justify-content-md-end">
														<tr>
															<th colspan="12">
																<?php echo Text::_('COM_TJVENDORS_REPORTS_CREDIT_AMOUNT') . '&nbsp:&nbsp&nbsp ' . $this->totalDetails['creditAmount'] . '&nbsp' . $currency; ?>
															</th>
														</tr>

														<tr>
															<th colspan="12">
																<?php echo Text::_('COM_TJVENDORS_REPORTS_DEBIT_AMOUNT') . '&nbsp:&nbsp&nbsp ' . $this->totalDetails['debitAmount'] . '&nbsp' . $currency; ?>
															</th>
														</tr>

														<tr>
															<th colspan="12">
																<?php echo Text::_('COM_TJVENDORS_REPORTS_PENDING_AMOUNT') . '&nbsp:&nbsp&nbsp ' . $this->totalDetails['pendingAmount'] . '&nbsp' . $currency; ?>
															</th>
														</tr>
													</div>
												<?php endif;?>
											</td>

											<td colspan="7" class="hidden-xs">
												<?php echo $this->pagination->getListFooter();?>
											</td>
										</tfoot>

										<tbody>
											<?php
											foreach ($this->items as $i => $row)
											{
												?>
												<tr>
													<td>
														<?php echo $this->pagination->getRowOffset($i);?>
													</td>

													<td data-title="<?php echo Text::_('COM_TJVENDORS_REPORTS_TRANSACTION_ID'); ?>">
														<?php echo htmlspecialchars($row->transaction_id, ENT_COMPAT, 'UTF-8'); ?>
													</td>

													<?php
													if ($this->vendorClient == '' && $clientFilter == 'all')
													{
														?>
														<td data-title="<?php echo Text::_('COM_TJVENDORS_REPORTS_CLIENT');?>">
															<?php
															$tjvendorFrontHelper = new TjvendorFrontHelper;
															$client = $tjvendorFrontHelper->getClientName($row->client);
															echo htmlspecialchars($client, ENT_COMPAT, 'UTF-8');
															?>
														</td>
														<?php
													}

													if ($currency == '0')
													{
														?>
														<td data-title="<?php echo Text::_('COM_TJVENDORS_REPORTS_CURRENCY');?>">
															<?php echo htmlspecialchars($row->currency, ENT_COMPAT, 'UTF-8');?>
														</td>
														<?php
													}

													if ($transactionType == "Credit" || empty($transactionType))
													{
														?>
														<td data-title="<?php echo Text::_('COM_TJVENDORS_REPORTS_CREDIT_AMOUNT');?>">
															<?php
															if ($row->credit <='0')
															{
																echo "0";
															}
															else
															{
																echo htmlspecialchars($row->credit, ENT_COMPAT, 'UTF-8');
															}
															?>
														</td>
														<?php
													}

													if ($transactionType == "Debit" || empty($transactionType))
													{
														?>
														<td data-title="<?php echo Text::_('COM_TJVENDORS_REPORTS_DEBIT_AMOUNT');?>">
															<?php echo htmlspecialchars($row->debit, ENT_COMPAT, 'UTF-8');?>
														</td>
														<?php
													}
													?>

													<td data-title="<?php echo Text::_('COM_TJVENDORS_REPORTS_REFERENCE_ORDER_ID');?>">
														<?php echo htmlspecialchars($row->reference_order_id, ENT_COMPAT, 'UTF-8');?>
													</td>

													<td data-title="<?php echo Text::_('COM_TJVENDORS_REPORTS_TRANSACTION_TIME');?>">
														<?php echo htmlspecialchars($row->transaction_time, ENT_COMPAT, 'UTF-8');?>
													</td>

													<td data-title="<?php echo Text::_('COM_TJVENDORS_REPORTS_PENDING_AMOUNT');?>">
														<?php echo htmlspecialchars(abs($row->total), ENT_COMPAT, 'UTF-8');?>
													</td>

													<td data-title="<?php echo Text::_('COM_TJVENDORS_REPORTS_ENTRY_STATUS');?>">
														<?php
														$status = json_decode($row->params, true);

														if ($status['entry_status'] == "debit_payout")
														{
															if ($row->status == 1)
															{
																echo Text::_('COM_TJVENDOR_PAYOUT_DONE');
															}
															else
															{
																echo Text::_('COM_TJVENDOR_PAYOUT_PENDING');
															}
														}
														elseif ($status['entry_status'] == "credit_for_ticket_buy")
														{
															echo Text::_('COM_TJVENDOR_CREDIT_DONE');
														}
														?>
													</td>

													<td class="center" data-title="<?php echo Text::_('COM_TJVENDORS_REPORTS_CUSTOMER_NOTE');?>">
														<?php
														if (!empty($status['customer_note']))
														{
															echo $status['customer_note'];
														}
														else
														{
															echo "-";
														}
														?>
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
						<?php
					}
					?>
				</div>
			</div>

			<input type="hidden" name="task" value=""/>
			<input type="hidden" name="boxchecked" value="0"/>
			<input type="hidden" name="filter_order" value="<?php echo $listOrder;?>"/>
			<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>
			<?php echo HTMLHelper::_('form.token');?>
		</form>
		<?php
	}
	elseif (!$this->vendor_id && $user_id)
	{
		?>
		<div class="alert alert-info"><?php echo Text::_('COM_TJVENDOR_REPORTS_ERROR');?></div>
		<?php
	}
	else
	{
		$link = Route::_('index.php?option=com_users');
		$app = Factory::getApplication();
		$app->redirect($link);
	}
	?>
</div>
