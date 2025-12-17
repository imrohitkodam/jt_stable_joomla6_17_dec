<?php
/**
 * @package     JTicketing
 * @subpackage  com_jticketing
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;

HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('bootstrap.renderModal', 'a.modal');

// NATIVE EVENT MANAGER
if ($this->integration != 2)
{
	?>
		<div class="alert alert-info alert-help-inline">
			<?php echo Text::_('COMJTICKETING_INTEGRATION_NATIVE_NOTICE');	?>
		</div>
	<?php

	return false;
}
else
{
}

$utilities = JT::utilities();
?>

<div class="<?php echo JTICKETING_WRAPPER_CLASS;?>">

	<div class="event-form<?php echo $this->pageclass_sfx?>">
	<?php if ($this->params->get('show_page_heading', 1))
	{
	?>
		<div class="page-header">
			<h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
		</div>
	<?php
} ?>
	</div>

	<div id="jtwrap">
		<div class="row mt-2">
			<div class="col-xs-12">
				<form method="post" name="adminForm" id="adminForm" class="form-inline">
				<div class="container">
					<div class="list-inline events row">
						<div class="col-md-12">
							<div>
								<?php echo $this->toolbarHTML;?>
							</div>
							<div class="clearfix"> </div>
							<hr class="hr-condensed" />
						</div>

						<span class="col-sm-12 col-md-4 mt-2 mb-2">
							<span class="event__separation">
								<span class="events__search input-group" id="searchFilterInputBox">
									<input
										type="text"
										placeholder="<?php echo Text::_('COM_JTICKETING_ENTER_EVENTS_NAME'); ?>"
										name="search"
										id="search"
										value="<?php echo htmlspecialchars($srch = ($this->lists['search'])?$this->lists['search']:''); ?>"
										class="form-control af-bg-faded"
										onchange="this.form.submit();"/>

										<span class="input-group-text">
										<a id="searchEventBtn"  href="javascript:void(0)" onclick="jtSite.events.toggleDiv('searchFilterInputBox');" title="<?php echo Text::_('COM_JTICKETING_SEARCH_EVENT')?>">
												<i class="fa fa-search"></i>
											</a>
										</span>
										<span class="clear-sepration">
											<button
											type="reset"
											href="javascript:void(0)"
											onclick="document.getElementById('search').value='';this.form.submit();"
											class="btn btn-info"
											title="<?php echo Text::_('COM_JTICKETING_CLEAR_SEARCH')?>">
											<i class="fa fa-remove"></i>
											</button>
										</span>

<!--
									<button
										type="button"
										onclick="document.getElementById('search').value='';this.form.submit();"
										class="btn events__search--clear hasTooltip af-absolute"
										data-original-title="Clear">
										<i class="fa fa-remove"></i>
									</button>
-->
								</span>
							</span>
						</span>
						<div class="col-sm-12 col-md-8 mt-2 mb-2">
							<span class="float-end">
								<?php echo $this->pagination->getLimitBox(); ?>
							</span>
						</div>
					</div>
				</div>
							<input type="hidden" name="filter_order" value="<?php echo $this->filter_order; ?>" />
							<input type="hidden" name="filter_order_Dir" value="<?php echo $this->filter_order_Dir; ?>" />
							<input type="hidden" name="option" value="com_jticketing" />
							<input type="hidden" name="view" value="events" />
							<input type="hidden" name="controller" value="" />
							<input type="hidden" name="task" value="" />
							<input type="hidden" name="boxchecked" value="0" />
							<?php echo HTMLHelper::_( 'form.token' ); ?>

					<div class="row">
						<div class="col-xs-12">
							<?php
							if (empty($this->items))
							{
							?>
								<div class="clearfix">&nbsp;</div>
									<div class="col-xs-12 alert alert-info"><?php echo Text::_('COM_JTICKETING_EVENTS_NO_EVENTS_FOUND');?></div>
							<?php
							}
							else
							{
							?>
								<div id='no-more-tables'>
									<table class="table table-striped table-bordered table-hover table-light border mt-4">
										<thead class="table-primary text-light">
											<tr>
												<th width="1%" class="nowrap center hidden-phone">
													<input type="checkbox" name="checkall-toggle" value="" title="<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
												</th>
												<th class="center">
													<?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'a.state');?>
												</th>
												<th>
													<?php echo HTMLHelper::_('grid.sort', 'COM_JTICKETING_EVENTS_TITLE', 'title', $this->filter_order_Dir, $this->filter_order ); ?>
												</th>

												<th class="center nowrap com_jticketing_width10">
													<?php echo HTMLHelper::_('grid.sort', 'COM_JTICKETING_EVENTS_CREATED', 'created', $this->filter_order_Dir, $this->filter_order ); ?>
												</th>

												<!--
												<th class="center hidden-xm com_jticketing_width5">
													<?php echo HTMLHelper::_('grid.sort', 'COM_JTICKETING_EVENTS_PUBLISHED', 'state', $this->filter_order_Dir, $this->filter_order ); ?>
												</th>
												-->

												<th class="com_jticketing_width15">
													<?php echo HTMLHelper::_('grid.sort', 'COM_JTICKETING_EVENTS_CATEGORY', 'category', $this->filter_order_Dir, $this->filter_order ); ?>
												</th>


												<th class="center nowrap com_jticketing_width10">
													<?php echo HTMLHelper::_('grid.sort', 'COM_JTICKETING_EVENTS_STARTDATE', 'startdate', $this->filter_order_Dir, $this->filter_order ); ?>
												</th>

												<th class="center nowrap com_jticketing_width10">
													<?php echo HTMLHelper::_('grid.sort', 'COM_JTICKETING_EVENTS_ENDDATE', 'enddate', $this->filter_order_Dir, $this->filter_order ); ?>
												</th>

												<th class="com_jticketing_width15">
													<?php echo HTMLHelper::_('grid.sort', 'COM_JTICKETING_EVENTS_LOCATION', 'location', $this->filter_order_Dir, $this->filter_order ); ?>
												</th>
												<th class="com_jticketing_width15">
													<?php echo Text::_('COM_JTICKETING_EVENTS_ATTENDEE_DETAILS'); ?>
												</th>
											</tr>
										</thead>
										<tbody>
											<?php
											$n = count($this->items);

											for ($i = 0; $i < $n; $i++)
											{
												$row  = $this->items[$i];
												$link = Route::_('index.php?option=com_jticketing&view=eventform&id=' . (int) $row->id . '&Itemid=' .
													$this->create_event_itemid, false
												);

												$event = JT::event($row->id);
												$eventDetailUrl = $event->getUrl();
												?>

												<tr>
													<td class="center">
														<?php echo HTMLHelper::_('grid.id', $i, $row->id); ?>
													</td>
													<td class="center <?php echo ($row->state) ? '' : 'af-relative';?>">
														<?php
														echo HTMLHelper::_('jgrid.published', $row->state, $i, 'events.', $this->canChange, 'cb');
														?>
													</td>
													<td data-title="<?php echo Text::_('COM_JTICKETING_EVENTS_TITLE'); ?>">
														<a href="<?php echo $eventDetailUrl; ?>"
															title="<?php echo $this->escape($row->title); ?>">
															<?php echo $this->escape($row->title); ?>

														</a>
														<a href="<?php echo $link; ?>"
															title="<?php echo Text::_('COM_JTICKETING_EDIT_EVENT'); ?>">
															<i class="fa fa-pencil-square-o float-end" aria-hidden="true"></i>
														</a>
													</td>

													<td class="center small nowrap hidden-xm com_jticketing_width10" data-title="<?php echo Text::_('COM_JTICKETING_EVENTS_CREATED');?>">
														<span class="bg">
															<?php echo $utilities->getFormatedDate($row->created); ?>
														</span>
													</td>

													<!--
													<td class="small center hidden-xm com_jticketing_width5">
														<a class="btn  btn-default  btn-micro active hasTooltip"
														href="javascript:void(0);"
														title="<?php echo ( $row->state ) ? Text::_('COM_JTICKETING_UNPUBLISH') : Text::_('COM_JTICKETING_PUBLISH');?>"
														onclick="document.adminForm.boxchecked.value=1; Joomla.submitbutton('
														<?php echo ( $row->state ) ? 'tests.unpublish' : 'tests.publish';?>');">
															<i class=<?php echo ( $row->state ) ? "icon-publish" : "icon-unpublish";?> > </i>
														<a/>
													</td>
													-->

													<td class="small com_jticketing_width15"  data-title="<?php echo Text::_('COM_JTICKETING_EVENTS_CATEGORY');?>">
													<?php echo ($row->category == null) ? "-" : $row->category; ?>
													</td>

													<td class="center small nowrap com_jticketing_width10" data-title="<?php echo Text::_('COM_JTICKETING_EVENTS_STARTDATE');?>">
														<?php
														echo $this->utilities->getFormatedDate($row->startdate);
														?>
													</td>

													<td class="center small nowrap com_jticketing_width10" data-title="<?php echo Text::_('COM_JTICKETING_EVENTS_ENDDATE');?>">
													<?php
														echo $this->utilities->getFormatedDate($row->enddate);
													?>
													</td>

													<td class="small com_jticketing_width15" data-title="<?php echo Text::_('COM_JTICKETING_EVENTS_LOCATION');?>">
														<?php echo $this->escape($row->location); ?>
													</td>
													<td class="small com_jticketing_width15" >
														<div class="btn-group myevents">
															<?php
															$url = "index.php?option=com_jticketing&view=attendees&filter[events]=" . $row->xref_id . "&tmpl=component";
															$modalConfig = array('width' => '100%', 'height' => '300px', 'modalWidth' => 80, 'bodyHeight' => 70);
															$modalConfig['url'] = Route::_($url, false);
															$modalConfig['title'] = Text::_('COM_JTICKETING_ATTENDEE_EXPORT');
															echo HTMLHelper::_('bootstrap.renderModal', 'jtEvents' . $row->xref_id, $modalConfig);
															?>
															<a class="btn" data-bs-target="#jtEvents<?php echo $row->xref_id;?>" data-bs-toggle="modal"
															class="modal_jform_"
															title="<?php echo Text::_('COM_JTICKETING_ATTENDEE_EXPORT'); ?>"
															class="btn" href="#">
																<i class="fa fa-download" ></i>
															</a>

															<?php
															if($this->params->get('signin_export'))
															{
																$signinUrl = "index.php?option=com_jticketing&view=attendees&layout=signin&filter[events]=" . $row->xref_id . "&tmpl=component";
																$modalConfig = array('width' => '100%', 'height' => '300px', 'modalWidth' => 80, 'bodyHeight' => 70);
																$modalConfig['url'] = Route::_($signinUrl, false);
																$modalConfig['title'] = Text::_('COM_JTICKETING_EVENTS_ATTENDEE_SIGN_IN_SHEET');
																echo HTMLHelper::_('bootstrap.renderModal', 'jtEventsExport' . $row->xref_id, $modalConfig);
																?>
																<a class="btn" data-bs-target="#jtEventsExport<?php echo $row->xref_id;?>" data-bs-toggle="modal"
																class="modal_jform_"
																title="<?php echo Text::_('COM_JTICKETING_EVENTS_ATTENDEE_SIGN_IN_SHEET'); ?>"
																class="btn" href="#">
																	<i class="fa fa-file-text"></i>
																</a>
															<?php
															}
															if($this->params->get('namecard_export'))
															{
																$namecard = "index.php?option=com_jticketing&view=attendees&layout=namecard&filter[events]=" . $row->xref_id . "&tmpl=component";
																$modalConfig = array('width' => '100%', 'height' => '300px', 'modalWidth' => 80, 'bodyHeight' => 70);
																$modalConfig['url'] = Route::_($namecard, false);
																$modalConfig['title'] = Text::_('COM_JTICKETING_EVENTS_ATTENDEE_NAME_CARD_SHEET');
																echo HTMLHelper::_('bootstrap.renderModal', 'jtEventsNameCard' . $row->xref_id, $modalConfig);
																?>
																<a class="btn" data-bs-target="#jtEventsNameCard<?php echo $row->xref_id;?>" data-bs-toggle="modal"
																class="modal_jform_"
																title="<?php echo Text::_('COM_JTICKETING_EVENTS_ATTENDEE_NAME_CARD_SHEET'); ?>"
																class="btn" href="#">
																	<i class="fa fa-bar-chart"></i>
																</a>
															<?php
															}
															?>
														</div>
													</td>
												</tr>
											<?php
											}
											?>
										</tbody>
									</table>
								</div>
							<?php
							}?>
						</div><!--col-xs-12-->
					</div><!--row-->
					<div class="row">
						<div class="pager col-xs-12">
							<div class="col-12 d-flex justify-content-end">
								<?php echo $this->pagination->getPagesLinks(); ?>
							</div>
						</div>
					</div>
				</form>
			</div><!--col-xs-12-->
		</div><!--row-->
	</div><!--row-->
</div>

<script>
	Joomla.submitbutton = function(task) {
		jtSite.myEvents.myEventsSubmitData(task);
	}

	jQuery(document).ready(function() {
		jtSite.myEventsSubmitData.submit();
	});
</script>
