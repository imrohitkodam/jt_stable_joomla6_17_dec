<?php
/**
 * @version    SVN: <svn_id>
 * @package    Com_Jticekting
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2018 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.multiselect');
// Joomla 6: formbehavior.chosen removed - using native select
HTMLHelper::_('bootstrap.renderModal', 'a.modal');
HTMLHelper::_('jquery.token');

$listOrder  = $this->escape($this->state->get('list.ordering'));
$listDirn   = $this->escape($this->state->get('list.direction'));
$integration = JT::getIntegration(true);

// Modal pop up for mass enrollment
echo HTMLHelper::_('bootstrap.renderModal', 'myModal', $this->modal_params, $this->body);
?>
<div class="modal fade z-index-9999 move-attendee" id="move_attendee" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
	<div class="modal-content">
	  <div class="modal-header d-flex justify-content-between align-items-center p-0">
		<h5 class="modal-title af-d-inline-block af-font-500 ms-3"><?php echo Text::_('COM_JTICKETING_VIEW_ATTENDEES_MOVE_ATTENDEE_DESCRIPTION'); ?></h5>
			<button type="button" class="close btn btn-lg border-0 p-1 " data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close">
			  <span aria-hidden="true" class="fs-3">&times;</span>
			</button>
	  </div>
	  <div class="modal-body">
			<?php echo $this->attendeeBody; ?>
	  </div>
	</div>
  </div>
</div>

<div id="jtwrap" class="tjBs5 <?php echo ($this->tmpl == 'component') ? 'container' : ''; ?> view-attendees">
	<div class="row">
		<div class="col-sm-12 af-mr-10 af-mt-10">
			<div class="jtFilters">
			<?php
				echo $this->addTJtoolbar();
			?>
			</div>
		</div>
		<div class="col-sm-12 af-mr-10">
			<form action="<?php echo Route::_('index.php?option=com_jticketing&view=attendees&Itemid=' . $this->attendee_item_id . $this->component); ?>" method="post" name="adminForm" id="adminForm" class="jtFilters attendees">
				<div class="col-md-12">
					<hr class="hr-condensed"/>
					<div class="row">
						<div class="col-md-12">
							<?php
							echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this));
							?>
						</div>
					</div>
				</div>
				<?php

				if (empty($this->items))
				{?>
					<div class="col-xs-12 alert alert-info jtleft">
						<?php echo Text::_('COM_JTICKETING_NO_ATTENDEES_FOUND'); ?>
					</div>
				<?php
				}
				else
				{ 	?>
					<div class="jticketing-tbl" id="no-more-tables">
						<div class="table-responsive text-nowrap">
							<table class="table table-striped left_table table-bordered table-hover w-100" id="usersList">
								<thead class="table-primary text-light">
									<tr>
										<?php
										if ($this->tmpl !== 'component')
										{ ?>
										<th width="1%" class="hidden-phone">
											<input type="checkbox" name="checkall-toggle" value="" title="<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
										</th>
										<?php
										} 
										
										if (in_array('COM_JTICKETING_ORDER_ID', $this->attendeeListingFields))
										{ ?>
											<th class='left'>
												<?php echo  Text::_('COM_JTICKETING_ORDER_ID'); ?>
											</th>
											<?php 
										} ?>
										<th class='left'>
											<?php echo HTMLHelper::_('grid.sort',  'COM_JTICKETING_ENROLMENT_ID', 'attendee.id', $listDirn, $listOrder); ?>
										</th>

										<?php
										if (in_array('COM_JTICKEITNG_ATTENDEE_ENTRY_NUMBER', $this->attendeeListingFields))
										{ ?>
											<th class='left'>
												<?php echo HTMLHelper::_('grid.sort',  'COM_JTICKEITNG_ATTENDEE_ENTRY_NUMBER', 'oitem.entry_number', $listDirn, $listOrder); ?>
											</th>
										<?php } ?>

										<th class='left'>
											<?php echo  Text::_('COM_JTICKETING_ATTENDEE_USER_NAME'); ?>
										</th>

										<?php
										if (in_array('COM_JTICKETING_BUYER_NAME', $this->attendeeListingFields))
										{ ?>
											<th class='left'>
												<?php echo  Text::_('COM_JTICKETING_BUYER_NAME'); ?>
											</th>
											<?php 
										} 

										if (in_array('COM_JTICKETING_ENROLMENT_USER_USERNAME', $this->attendeeListingFields))
										{ ?>
											<th class='left'>
												<?php echo  Text::_('COM_JTICKETING_ENROLMENT_USER_USERNAME'); ?>
											</th>
										<?php } ?>

										<?php
										if (in_array('COM_JTICKEITNG_ATTENDEE_EMAIL', $this->attendeeListingFields))
										{ ?>
											<th class='left'>
												<?php echo Text::_('COM_JTICKEITNG_ATTENDEE_EMAIL'); ?>
											</th>
										<?php } ?>

										<th class='left'>
											<?php echo HTMLHelper::_('grid.sort',  'COM_JTICKETING_ENROLMENT_EVENT_NAME', 'events.title', $listDirn, $listOrder); ?>
										</th>

										<?php
										if (in_array('COM_JTICKEITNG_TICKET_TYPE_COLUMN', $this->attendeeListingFields))
										{ ?>
											<th class='left'>
												<?php echo Text::_('COM_JTICKEITNG_TICKET_TYPE_COLUMN'); ?>
											</th>
											<?php 
										} 

										if (in_array('TICKET_PRICE', $this->attendeeListingFields))
										{ ?>
											<th class='left'>
												<?php echo Text::_('TICKET_PRICE'); ?>
											</th>
											<?php 
										} 

										if ($this->tmpl !== 'component')
										{?><th class='left'>
											<?php
											if ($this->isEnrollmentEnabled && $this->isEnrollmentApproval)
											{
												echo HTMLHelper::_('grid.sort',  'COM_JTICKETING_ENROLMENT_APPROVAL', 'attendee.status', $listDirn, $listOrder);
											}
											else
											{
												echo HTMLHelper::_('grid.sort',  'COM_JTICKETING_ATTENDESS_VIEW_MOVE_ATTENDEE', 'attendee.status', $listDirn, $listOrder);
											}?>
										</th>
										<th align="left">
											<?php echo  Text::_('PREVIEW_TICKET'); ?>
										</th>
										<?php if ($integration == 2) { ?>
											<th align="left">
												<?php echo Text::_('COM_JTICKETING_VIEW_RECURRING_EVENTS'); ?>
											</th>
										<?php } 
										if (in_array('COM_JTICKETING_ENROLMENT_ACTION', $this->attendeeListingFields))
										{ ?>
											<th align="left">
												<?php echo  Text::_('COM_JTICKETING_ENROLMENT_ACTION'); ?>
											</th>
											<?php 
										}
										}

										if (in_array('COM_JTICKETING_CHECKIN_TIME', $this->attendeeListingFields))
										{ ?>
											<th class='left'>
												<?php echo HTMLHelper::_('grid.sort',  'COM_JTICKETING_CHECKIN_TIME', 'chck.checkintime', $listDirn, $listOrder); ?>
											</th>
											<?php 
										} ?>
										<?php
										if ($this->tmpl !== 'component')
										{ 
											if (in_array('COM_JTICKETING_CHECKIN', $this->attendeeListingFields))
											{ ?>					
												<th align="left">
													<?php echo  Text::_('COM_JTICKETING_CHECKIN'); ?>
												</th>
												<?php
											}?>

											<th class='left'>
												<?php echo Text::_('COM_JTICKETING_ENROLMENT_NOTIFY'); ?>
											</th>
									<?php } ?>
									</tr>
								</thead>
								<tbody>
								<?php
									$j = 0;
									$utilities    = JT::utilities();

									foreach ($this->items as $i => $item) :
										$ordering   = ($listOrder == 'b.ordering');
										?>
										<tr class="row<?php echo $i % 2; ?>" >
											<?php
											if ($this->tmpl !== 'component')
											{ ?>
											<td class="center hidden-phone">
												<?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
											</td>
											<?php
											if (isset($this->items[0]->state)): ?>
												<td class="center">
													<?php echo HTMLHelper::_('jgrid.published', $item->state, $i, 'enrollments.', $canChange, 'cb'); ?>
												</td>
											<?php
											endif;
											}

											if (in_array('COM_JTICKETING_ORDER_ID', $this->attendeeListingFields))
											{ ?>
												<td class="text-break" data-title="<?php echo Text::_('COM_JTICKETING_ORDER_ID');?>">
													<?php echo htmlspecialchars($item->order_id); ?>
												</td>
											<?php } ?>
											<td class="text-break" data-title="<?php echo Text::_('COM_JTICKETING_ENROLMENT_ID');?>">
												<?php echo htmlspecialchars($item->enrollment_id); ?>
											</td>

											<?php
											if (in_array('COM_JTICKEITNG_ATTENDEE_ENTRY_NUMBER', $this->attendeeListingFields))
											{ ?>
												<td class="text-break">
													<?php echo htmlspecialchars($item->entry_number); ?>
												</td>
											<?php } ?>

											<td data-title="<?php echo Text::_('COM_JTICKETING_ENROLMENT_USER_NAME');?>">
												<?php
												if (!empty($item->fname))
												{
													echo $this->escape(ucfirst($item->fname) . ' ' . ucfirst($item->lname));
												}
												elseif (!empty($item->firstname))
												{
													echo htmlspecialchars($item->firstname . ' ' . $item->lastname);
												}
												else
												{
													echo htmlspecialchars($item->name);
												} ?>
											</td>

											<?php
											if (in_array('COM_JTICKETING_BUYER_NAME', $this->attendeeListingFields))
											{ ?>
												<td class="text-break" data-title="<?php echo Text::_('COM_JTICKETING_BUYER_NAME');?>">
													<?php
														if (!empty($item->buyer_name))
														{
															echo htmlspecialchars($item->buyer_name);
														}
														else
														{
															echo htmlspecialchars('-');
														}
													?>
												</td>
											<?php
											}

											if (in_array('COM_JTICKETING_ENROLMENT_USER_USERNAME', $this->attendeeListingFields))
											{ ?>
												<td class="text-break" data-title="<?php echo Text::_('COM_JTICKETING_ENROLMENT_USER_USERNAME');?>">
													<?php
													if (!empty($item->username))
													{
														echo htmlspecialchars($item->username);
													}
													else
													{
														echo htmlspecialchars('-');
													}
													?>
												</td>
												<?php 
											} 
											
											if (in_array('COM_JTICKEITNG_ATTENDEE_EMAIL', $this->attendeeListingFields))
											{ ?>
												<td class="text-break" data-title="<?php echo Text::_('COM_JTICKEITNG_ATTENDEE_EMAIL');?>">
													<?php
													if (!empty($item->attendee_email))
													{
														echo $this->escape($item->attendee_email);
													}
													else
													{
														echo $this->escape($item->owner_email);
													}    ?>
												</td>
											<?php } ?>

											<td data-title="<?php echo Text::_('COM_JTICKETING_ENROLMENT_EVENT_NAME');?>">
												<?php echo htmlspecialchars($item->title); ?>
											</td>
											<?php

												if (in_array('COM_JTICKEITNG_TICKET_TYPE_COLUMN', $this->attendeeListingFields))
												{ ?>
													<td class="text-break" data-title="<?php echo Text::_('COM_JTICKEITNG_TICKET_TYPE_COLUMN');?>">
														<?php echo htmlspecialchars($item->ticket_type_title); ?>
													</td>
													<?php 
												} 

												if (in_array('TICKET_PRICE', $this->attendeeListingFields))
												{ ?>
													<td class="text-break" data-title="<?php echo Text::_('TICKET_PRICE');?>">
														<?php echo $utilities->getFormattedPrice($item->amount);?>
													</td>
													<?php 
												} 
											$app = Factory::getApplication();
											$isAdmin = 0;

											if ($app->isClient("administrator"))
											{
												$isAdmin = 1;
											}

											if ($this->tmpl !== 'component')
											{
												if (!$item->checkin)
												{
													if ($this->isEnrollmentEnabled === '1'  && $this->isEnrollmentApproval === '1')
													{
													?>
														<td data-title="<?php echo Text::_('COM_JTICKETING_ENROLMENT_APPROVAL');?>">
															<?php
																// Get the valid order status list options according to the current status of the enrollment.
																$validStatus = $this->attendeesModel->getValidAttendeeActions($item->status, $this->attendeeActions);
																$logedInUser  = Factory::getUser();
																$canEnrollOwn = $logedInUser->authorise('core.enrollown', 'com_jticketing');
																$enroll = 0;

																if (!$canEnrollOwn)
																{
																	$vendor = JT::event($item->event_id)->getVendorDetails();
																	$enroll = ($logedInUser->id == $vendor->user_id)?1:0;
																}

																if ($item->status == 'R' || $enroll == 1 )
																{

																	unset($validStatus['M']);
																}

																$oncahnge    = "jtCommon.enrollment.updateEnrollment(" . $i . "," . $item->id . ",'update'," . $isAdmin . ")";
																echo HTMLHelper::_('select.genericlist', $validStatus, "assign_" . $i, 'onchange=' . $oncahnge, "value", " text", $item->status);
															?>
														</td>
													<?php
													}
													elseif ($this->enableAttendeeMove && $item->status !== COM_JTICKETING_CONSTANT_ATTENDEE_STATUS_PENDING)
													{
														$onchange = "jtCommon.enrollment.updateEnrollment(" . $i . "," . $item->id . ",'moveAttendee'," . $isAdmin . ")";
														?>
														<td class="text-center">
															<input type="button" class="btn btn-primary btn-sm  py-1 w-100" id="assign_<?php echo $i;?>" onclick="<?php echo $onchange;?>" value="<?php echo Text::_('COM_JTICKETING_ATTENDESS_VIEW_MOVE_ATTENDEE');?>"/>
														</td>
													<?php 
													}
													elseif($this->enableAttendeeMove)
													{
													?>
														<td><?php echo '-';?></td>
													<?php
													}
													else
													{
													?>
														<td><?php echo '-';?></td>
													<?php
													}
												}
												else
												{?>
													<td>
														<?php echo Text::_('COM_JTICKETING_ATTENDESS_VIEW_CHECKED_IN');?>
													</td>
												<?php
												}
												?>
												<td data-title="<?php echo Text::_('PREVIEW_TICKET');?>">
												<?php
													if ($item->status == 'A') :
													?>
														<?php
															$href = Route::_(Uri::base(true) . '/index.php?option=com_jticketing&view=attendees&tmpl=component&layout=myticket&attendee_id='. $item->id);
															$modalConfig = array('width' => '800px', 'height' => '300px', 'modalWidth' => 80, 'bodyHeight' => 70);
															$modalConfig['url'] = $href;
															$modalConfig['title'] = Text::_('PREVIEW_DES');
															echo HTMLHelper::_('bootstrap.renderModal', 'attendeeTicketPreview' . $item->id, $modalConfig);
														?>
														<a data-bs-target="#attendeeTicketPreview<?php echo $item->id;?>" data-bs-toggle="modal" class="af-relative af-d-block">
															<span title="<?php echo Text::_('PREVIEW_DES');?>" ><?php echo Text::_('PREVIEW');?>
															</span>
														</a>
													<?php endif; ?>

													<!-- For Extra Attendee Fields -->
													<?php

													if ($this->collect_attendee_info_checkout) :
														?>
														<?php
															$attendeeHref = Route::_(Uri::base(true) . '/index.php?option=com_jticketing&view=attendees&tmpl=component&layout=attendee_details&eventid=' . $item->event_id . '&attendee_id=' . $item->id);
															$modalConfig = array('width' => '800px', 'height' => '300px', 'modalWidth' => 80, 'bodyHeight' => 70);
															$modalConfig['url'] = $attendeeHref;
															$modalConfig['title'] = Text::_('COM_JTICKETING_VIEW_ATTENDEE');
															echo HTMLHelper::_('bootstrap.renderModal', 'attendeeTicketView' . $item->id, $modalConfig);
														?>
														<a data-bs-target="#attendeeTicketView<?php echo $item->id;?>" data-bs-toggle="modal" class="af-relative af-d-block" href="javascript:;">
															<span title="<?php echo Text::_('COM_JTICKETING_VIEW_ATTENDEE');?>" ><?php echo Text::_('COM_JTICKETING_VIEW_ATTENDEE');?>
															</span>
														</a>

													<?php endif; ?>
												</td>
												<td>
													<?php if ($integration == 2 && $item->r_id) { ?>
														<a href="<?php echo Route::_('index.php?option=com_jticketing&view=recurringevents&attendee_id=' . $item->id); ?>">
															<?php echo Text::_('COM_JTICKETING_VIEW_RECURRING_EVENTS'); ?>
														</a>
													<?php } elseif ($integration == 2) { ?>
														<?php echo '-'; ?>
													<?php } ?>
												</td>
												<td>
													<?php
														$isOrderPresent = $item->order_id ? true : false;
														if (!$isOrderPresent)
														{
															?>
															<a href="javascript:void(0);" class="hasTooltip" data-original-title="<?php echo Text::_('JTOOLBAR_TRASH');?>" onclick="jtCommon.enrollment.deleteEnrollment('<?php echo $item->id; ?>')">

																<span class="fa fa-trash af-icon-red" area-hidden="true" ></span>
															</a>
															<?php
														}
														else 
														{
															echo '-';
														}
													?>
												</td>
									<?php 	}	 
										if (in_array('COM_JTICKETING_CHECKIN_TIME', $this->attendeeListingFields))
										{ ?>
											<td data-title="<?php echo Text::_('COM_JTICKETING_CHECKIN_TIME');?>">
												<?php
												if (!empty($item->checkintime) && !empty($item->checkin))
												{
													?>
													<?php echo $item->checkintime; ?>

													<?php
												}
												else
												{
													echo '-';
												}
												?>
											</td>
											<?php
										}
											if ($this->tmpl !== 'component')
											{ 
												if (in_array('COM_JTICKETING_CHECKIN', $this->attendeeListingFields))
												{ ?>
													<td data-title="<?php echo Text::_('COM_JTICKETING_CHECKIN');?>">
														<?php if ($item->status == 'A'){
														?>
														<a href="javascript:void(0);" class="hasTooltip" data-original-title="<?php echo ($item->checkin) ? Text::_('COM_JTICKETING_CHECKIN_FAIL') : Text::_('COM_JTICKETING_CHECKIN_SUCCESS');?>" onclick="Joomla.listItemTask('cb<?php echo $i;?>','<?php echo ($item->checkin) ? 'attendees.undochekin' : 'attendees.checkin';?>')">
															<img src="<?php echo Uri::root();?>administrator/components/com_jticketing/assets/images/<?php echo ($item->checkin) ? 'publish.png' : 'unpublish.png';?>" width="16" height="16" border="0" />
														</a>
														<?php
														}
														else
														{
															echo '-';
														}?>
													</td>
													<?php 
												} ?>
												
												<td data-title="<?php echo Text::_('COM_JTICKETING_ENROLMENT_NOTIFY');?>">
													<label>
														<input id="notify_user_<?php echo $item->id ?>" type="checkbox" name='notify_user_<?php echo $item->id ?>' checked>
													</label>
												</td>
											<?php
											} ?>
											<input type="hidden" id="eid_<?php echo $i ?>" name="eid" value="<?php echo $item->event_id; ?>" />
											<input type="hidden" id="owner_<?php echo $i ?>" name="ownerId" value="<?php echo $item->owner_id; ?>" />
										</tr>
										<?php $j++;
									endforeach;
								?>
								</tbody>
							</table>
						</div>
					</div><!--j-main-container ENDS-->
					<div class="row">
							<div class="col-xs-12">
								<div class="agination com_jticketing_align_center">
									<div class="pager">
										<div class="col-12 d-flex justify-content-end">
											<?php echo $this->pagination->getPagesLinks(); ?>
										</div>
									</div>
								</div>
							</div>
							<!-- col-lg-12 col-md-12 col-sm-12 col-xs-12-->
					</div>
				<?php
				}?>
				<input type="hidden" name="task" id="task" value="" />
				<input type="hidden" name="boxchecked" value="0" />
				<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
				<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
				<input type="hidden" name="controller" id="controller" value="attendees" />
				<?php echo HTMLHelper::_('form.token'); ?>
			</form>
		</div>
	</div>
</div>

<script>
	var jticketing_baseurl = "<?php echo Uri::root();?>";

	jQuery(".close").click(function() {
		parent.location.reload();
	});
	jQuery("#move_attendee").on('hidden.bs.modal', function () {
		parent.location.reload();
	})
	jQuery("#importCsvModal").on('hidden.bs.modal', function () {
		parent.location.reload();
	})

	/** global: jticketing_baseurl */
	jQuery(document).ready(function() {
		if (jQuery('.view-attendees .js-stools .btn-toolbar:first').length)
		{
			jQuery('.view-attendees .js-stools .btn-toolbar:first') . addClass('float-end');
			jQuery('.view-attendees .js-stools .btn-toolbar:first') . parent() . addClass('row');
		}
	});
</script>
