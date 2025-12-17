<?php
/**
 * @package     JTicketing
 * @subpackage  com_jticketing
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;

HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('formbehavior.chosen', '#jform_venue');
HTMLHelper::_('behavior.multiselect');
HTMLHelper::_('bootstrap.renderModal', 'a.modal');
HTMLHelper::script('administrator/components/com_tjfields/assets/js/tjfields.js');

/** @var $this JticketingViewEventform */

if ($this->allowedToCreate == 1)
{
	if ($this->checkVendorApproval || (JT::config()->get('silent_vendor') == 1))
	{
		?>
		<div id="jtwrap" class="tjBs5 custom-create-event">
		<?php
			if ($this->integration != 2)
			{
				?>
				<div class="alert alert-info alert-help-inline">
					<?php echo Text::_('COMJTICKETING_INTEGRATION_NATIVE_NOTICE');?>
				</div>
					<?php
					return false;
			}
			else
			{
				?>
				<div id="eventform1" class="row">
					<div class="col-xs-12">
						<div class="page-header">
							<h2>
								<?php
								if ($this->item->id)
								{
									echo Text::_('COM_JTICKETING_EDIT_EVENT');
									echo ':&nbsp' . $this->item->title;
								}
								else
								{
									echo Text::_('COM_JTICKETING_CREATE_NEW_EVENT');
								}
								?>
							</h2>
						</div>

						<?php

						if (($this->checkGatewayDetails == "true" && ($this->handle_transactions == 1 || in_array('adaptive_paypal', $this->adaptivePayment))) && $this->vendorCheck)
						{
							?>
							<div class="alert alert-warning">
								<?php
								$vendor_id = $this->vendorCheck;
								$link = 'index.php?option=com_tjvendors&view=vendor&layout=profile&client=com_jticketing';
								echo Text::_('COM_JTICKETING_PAYMENT_DETAILS_ERROR_MSG1');

								$itemid = JT::utilities()->getItemId($link);?>

								<a href="<?php echo Route::_($link . '&itemId=' . $itemid . '&vendor_id=' . $vendor_id, false); ?>" target="_blank">
									<?php echo Text::_('COM_JTICKETING_VENDOR_FORM_LINK'); ?>
								</a>

								<?php echo Text::_('COM_JTICKETING_PAYMENT_DETAILS_ERROR_MSG2'); ?>
							</div>
						<?php
						}
						?>

						<form id="adminForm" name="adminForm" action="" method="post" class="form-validate form-horizontal" enctype="multipart/form-data">
							<!--Tab Container start-->
							<div class="form-horizontal">
								<div class="row">
									<div class="col-xs-12  jt-event-frontend-edit">
									<!--tab 1 start-->
									<?php
										echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'details'));
									?>
										<?php
											echo HTMLHelper::_('uitab.addTab', 'myTab', 'details', Text::_('COM_JTICKETING_EVENT_TAB_DETAILS', true));
												echo HTMLHelper::_('bootstrap.startAccordion', 'myAccordian', array('active' => 'collapse1'));
												echo HTMLHelper::_('bootstrap.addSlide', 'myAccordian', Text::_('COM_JTICKETING_EVENT_TAB_BASIC'), 'collapse1', $class = 'af-mb-10');
													echo $this->loadTemplate('details_bs5');
												echo HTMLHelper::_('bootstrap.endSlide');

												echo HTMLHelper::_('bootstrap.addSlide', 'myAccordian', Text::_('COM_JTICKETING_EVENT_TAB_LOCATION'), 'collapse2', $class='af-mb-10');
													echo $this->loadTemplate('location');
												echo HTMLHelper::_('bootstrap.endSlide');

												echo HTMLHelper::_('bootstrap.addSlide', 'myAccordian', Text::_('COM_JTICKETING_EVENT_TAB_TIME'), 'collapse3', $class='af-mb-10');
													echo $this->loadTemplate('booking');
													echo HTMLHelper::_('bootstrap.endSlide');
												echo HTMLHelper::_('bootstrap.endAccordion');
											echo HTMLHelper::_('uitab.endTab');
										?>
									<!--tab 1 end-->

									<!--tab 2 start-->
										<?php
										echo HTMLHelper::_('uitab.addTab', 'myTab', 'tickettypes', Text::_('COM_JTICKETING_EVENT_TAB_TICKET_TYPES', true));
										$this->form->setFieldAttribute('tickettypes', 'layout', 'JTsubformlayouts.layouts.bs5.subform.repeatable');

										$entryNumbeAssignment = $this->params->get('entry_number_assignment', 0,'INT');

										if ($entryNumbeAssignment)
										{
											?>
												<div class="form-group row">

													<div class="form-label col-md-4">
														<?php echo $this->form->getLabel('start_number_for_event_level_sequence'); ?>
													</div>
													<div class="col-md-8">
														<?php echo $this->form->getInput('start_number_for_event_level_sequence'); ?>
													</div>
												</div>
											<?php
										}
										
										echo $this->form->getInput('tickettypes');
										?>
											<div class="row">
												<div class="col-sm-12">
													<?php
													if ( $this->params->get('siteadmin_comm_per') > 0 || $this->params->get('siteadmin_comm_flat') > 0)
													{
														?>
														<div id="commission">
															<span class="help-inline">
																<strong>
																	<?php echo Text::sprintf('COMMISSION_DEDUCTED_NOT_PERCENT', $this->params->get('siteadmin_comm_per'), '%');?>
																</strong>
															</span>

															<?php
															if ($this->params->get('siteadmin_comm_flat') > 0)
															{
																?>
																<span class="help-inline">
																	<strong>
																		<?php echo Text::sprintf('COMMISSION_DEDUCTED_NOT_FLAT', $this->params->get('siteadmin_comm_flat'), $this->params->get('currency'));?>
																	</strong>
																</span>
																<?php
															}
															?>
														</div>
													<?php
													}
													?>
												</div>
											</div>
										<?php
											echo HTMLHelper::_('uitab.endTab');
										?>
									<!--tab 2 end-->

									<!--tab 3 start-->
										<?php
										// Tab start for Attendee Fields.
											if ($this->collect_attendee_info_checkout)
											{
												echo HTMLHelper::_('uitab.addTab', 'myTab', 'attendeefields', Text::_('COM_JTICKETING_EVENT_TAB_EXTRA_FIELDS_ATTENDEE', true));
													echo $this->loadTemplate('attendee_core_fields_' . JTICKETING_LOAD_BOOTSTRAP_VERSION);
													echo HTMLHelper::_('uitab.endTab');
											}
										?>
									<!--tab 3 end-->

									<!--tab 4 start-->
										<?php
										// TJ-Field Additional fields for Event
											if ($this->fieldsIntegration == 'com_tjfields')
											{
												echo HTMLHelper::_('uitab.addTab', 'myTab', 'extrafields', Text::_('COM_JTICKETING_EVENT_TAB_EXTRA_FIELDS', true));

												if ($this->formExtraFields && (!empty($this->item->id)))
												{
													// @TODO SNEHAL- Load layout for this
													echo $this->loadTemplate('extrafields');
												}
												else
												{
													if (empty($this->item->id))
													{
													?>
														<div class="alert alert-info">
															<?php echo Text::_('COM_JTICKETING_EVENT_EXTRA_DETAILS_SAVE_PROD_MSG');?>
														</div>
													<?php
													}
												}

												echo HTMLHelper::_('uitab.endTab');
											}
										?>
									<!--tab 4 end-->

									<!--tab 5 start-->
										<?php
										echo HTMLHelper::_('uitab.addTab', 'myTab', 'gallery', Text::_('COM_JTICKETING_EVENT_GALLERY', true));
										?>
											<div class="col-sm well">
												<div class="af-mb-10">
													<div><?php echo $this->form->getLabel('gallery_file'); ?>				</div>
													<div>
														<?php echo $this->form->getInput('gallery_file'); ?>
													</div>
												</div>

												<div class="af-mb-10">
													<div class="af-d-inline-block">
														<div><?php echo $this->form->getLabel('gallery_link'); ?></div>
														<div><?php echo $this->form->getInput('gallery_link'); ?></div>
													</div>
													<div class="gallary__validateBtn af-d-inline-block">
														<input type="button" class="validate_video_link btn btn-primary" onclick="tjMediaFile.validateFile(this,1, <?php echo $this->isAdmin;?>, 'eventform')"
													value="<?php echo Text::_('COM_TJMEDIA_ADD_VIDEO_LINK');?>">
													</div>
												</div>
											</div>
											<div class="col-sm subform-wrapper">
												  <ul class="gallary__media thumbnails row">
													 <li class="gallary__media--li hide col-sm-4 col-md-2 af-mr-20">
														<a class="close" onclick="tjMediaFile.tjMediaGallery.deleteMedia(this, <?php echo $this->isAdmin;?>, <?php echo (!empty($this->item->id))? $this->item->id : 0;?>, 'eventform');return false;">×</a>
														<?php HTMLHelper::_('jquery.token');	?>
														<input type="hidden" name="jform[gallery_file][media][]" class="media_field_value" value="">
														<div class="thumbnail"></div>
													 </li>
												  </ul>
											</div>
										<?php
											echo  HTMLHelper::_('uitab.endTab');
										?>
									<!--tab 5 end-->

									<!--tab 6 start-->
										<?php
											// Loading joomla's params layout to show the fields and field group added for the event.
											echo LayoutHelper::render('joomla.edit.params', $this);
										?>
									<!-- tab 6 end-->
									<?php echo HTMLHelper::_('uitab.endTabSet'); ?>
								</div>

								<!--Tab Container closed-->
								<div class="clearfix">&nbsp;</div>
								<?php
								if ($this->tncForCreateEvent == 1)
								{
									?>
									<div>
										<div class="col-sm-12 col-xs-12 af-mb-10 form-check">
											<?php

											$checked = '';

											if (!empty($this->item->privacy_terms_condition))
											{
												$checked = 'checked';
											}

											$link = Route::_(Uri::root() . "index.php?option=com_content&view=article&id=" . $this->eventArticle . "&tmpl=component");
											?>
											<input class="form-check-input" type="checkbox" name="accept_privacy_term" id="accept_privacy_term" size="30" <?php echo $checked ?> />
											<label for="accept_privacy_term" class="form-check-label d-flex">
												<?php
												$modalConfig = array('width' => '600px', 'height' => '600px', 'modalWidth' => 80, 'bodyHeight' => 70);
												$modalConfig['url'] = $link;
												$modalConfig['title'] = Text::_('COM_JTICKETING_TERMS_CONDITION_EVENT');
												echo HTMLHelper::_('bootstrap.renderModal', 'jtEventTermsNConditions', $modalConfig);
												?>
												<a data-bs-target="#jtEventTermsNConditions" data-bs-toggle="modal" class="af-relative af-d-block ">
													<?php echo Text::_('COM_JTICKETING_TERMS_CONDITION_EVENT');?>
												</a>
												<span class="star">&nbsp;*</span>
											</label>
										</div>
									</div>
									<?php
								}
								?>
								<div class="af-mt-10">
									<button type="button" id="eventform-save" class="btn btn-primary com_jticketing_margin validate"
									onclick="Joomla.submitbutton('eventform.save')">
										<span><?php echo Text::_('JSUBMIT'); ?></span>
									</button>

									<button type="button" class="btn btn-default com_jticketing_margin"
									onclick="Joomla.submitbutton('eventform.cancel')">
										<span><?php echo Text::_('JCANCEL'); ?></span>
									</button>
								</div>

								<input type="hidden" name="option" value="com_jticketing" />
								<input type="hidden" name="task" id="task" value="eventform.save" />

								<input type="hidden" name="id"
									value="<?php if (!empty($this->item->id)) echo $this->item->id; ?>"/>

								<input type="hidden" name="jform[id]"
									value="<?php if (!empty($this->item->id)) echo $this->item->id;?>"/>

								<input type="hidden" name="jform[created_by]"
									value="<?php echo $this->item->created_by ? $this->item->created_by:Factory::getUser()->id;?>" />

								<?php echo HTMLHelper::_('form.token'); ?>
								</div>
							</div>
						</form>
					</div>
				</div>
			</div>
			<?php
		}
	}
	else
	{
		?>
		<div class="alert alert-info">
			<?php echo Text::_('COM_JTICKETING_VENDOR_NOT_APPROVED_MESSAGE');?>
		</div>
		<?php
	}
}
else
{
	?>
	<div class="alert alert-info alert-help-inline">
		<?php echo Text::_('COM_JTICKETING_VENDOR_ENFORCEMENT_ERROR');?>
		<?php echo Text::_('COM_JTICKETING_VENDOR_ENFORCEMENT_EVENT_REDIRECT_MESSAGE');?>
	</div>

	<div>
		<a href="<?php echo Route::_('index.php?option=com_tjvendors&view=vendor&layout=edit&client=com_jticketing');?>" target="_blank" >
			<button class="btn btn-primary">
				<?php echo Text::_('COM_JTICKETING_VENDOR_ENFORCEMENT_EVENT_REDIRECT_LINK'); ?>
			</button>
		</a>
	</div>
	<?php
}


$mediaGalleryObj = 0;

if (!empty($this->item->gallery))
{
	$mediaGalleryObj = json_encode($this->item->gallery);
}

$script = "
	var existing_url = '" . $this->event->getOnlineEventId() . "';
	var silentVendor = '" . $this->silentVendor . "';
	var eventId = '" . $this->item->id . "';";

	if ($this->item->venue != 0)
	{
		$script .= "var venueName = '" . htmlspecialchars($this->venueName) . "';";
	}
	
	$script .= "
	var root_url = '" . Uri::root() . "';
	var venueId = '" . $this->venueId . "';
	var mediaSize = '" . $this->mediaSize . "';
	var mediaGallery = " . $mediaGalleryObj . ";
	var galleryImage = '" . $this->eventGalleryImage . "';
	var eventMainImage = '" . $this->eventMainImage . "';
	var enableOnlineEvents = '" . $this->onlineEvents . "';
	var jticketing_baseurl = '" . Uri::root() . "';
	var enableOnlineVenues = '" . $this->enableOnlineVenues . "';
	var descriptionError = '" . Text::_('COM_JTICKETING_EMPTY_DESCRIPTION_ERROR') . "';
	var vendor_id = '" . $this->vendorCheck . "';
	var selectedVenue = '" . $this->item->venue . "';
	var tncForCreateEvent = '" . $this->tncForCreateEvent . "';
	var oldEventStartDate = '" . HTMLHelper::date($this->item->startdate ? $this->item->startdate : '','Y-m-d H:i', true) . "';
	var oldEventEndDate = '" . HTMLHelper::date($this->item->enddate ? $this->item->enddate : '','Y-m-d H:i', true) . "';
	jtSite.eventform.initEventJs();
	validation.positiveNumber();
";
Factory::getDocument()->addScriptDeclaration($script);

if (!$this->accessLevel)
{
	?>
	<style>
		.subform-repeatable-group .form-group:nth-last-child(2):not(#attendeefields .subform-repeatable-group .form-group:nth-last-child(2)){
			display: none;
		}

		.subform-repeatable-group .form-group:last-child:not(#attendeefields .subform-repeatable-group .inputboxdescmultiselect) {
			display: none;
		}
	</style>
	<?php
}
$ticketAccess = JT::config()->get('ticket_access');
$document = Factory::getDocument();
$document->addScriptDeclaration('var ticketAccessState = "' . $ticketAccess . '";');?>
