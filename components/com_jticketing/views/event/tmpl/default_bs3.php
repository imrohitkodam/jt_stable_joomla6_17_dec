<?php
/**
 * @package     JTicketing
 * @subpackage  com_jticketing
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Component\ComponentHelper;

HTMLHelper::_('bootstrap.renderModal', 'a.modal');
HTMLHelper::_('jquery.token');
if (file_exists(JPATH_LIBRARIES . '/techjoomla/common.php')) { require_once JPATH_LIBRARIES . '/techjoomla/common.php'; }
$this->techjoomlacommon = new TechjoomlaCommon;
JticketingCommonHelper::getLanguageConstant();
$eventUrl          = 'index.php?option=com_jticketing&view=event&id=' . (int) $this->item->id;
$eventUrl          = Uri::root() . substr(Route::_($eventUrl), strlen(Uri::base(true)) + 1);
$document          = Factory::getDocument();

// Load Chart Javascript Files.
HTMLHelper::_('script', 'media/com_jticketing/vendors/js/Chart.min.js');
HTMLHelper::_('script', 'media/com_jticketing/vendors/js/jquery.countdown.min.js');

if (!empty($this->item->coverImage->media))
{
	$imagePath = $this->item->coverImage->media;
}
elseif (!empty($this->item->image->media))
{
	$imagePath = $this->item->image->media;
}
else
{
	$imagePath = Route::_(Uri::base() . 'media/com_jticketing/images/default-event-image.png');
}

//Plugin trigger to add the schema for events
Factory::getApplication()->triggerEvent("onJTEventSchema", array($this->item));

?>
<div class="col-xs-12 tjBs3" id="jtwrap">
	<div class="eventDetails">
		<?php
		if ($this->item->created_by == $this->userid)
		{
			?>
			<!--Event performance map-->
			<div class="eventDetails-chart">
				<div class="clearfix">
					<div class="pull-right">
						<select id='event-graph-period' class="form-select form-select-sm w-auto">
							<option value ='0'><?php echo Text::_('COM_JTICKETING_FILTER_LATEST');?></option>
							<option value ='1'><?php echo Text::_('COM_JTICKETING_FILTER_LAST_MONTH');?></option>
							<option value ='2'><?php echo Text::_('COM_JTICKETING_FILTER_LAST_YEAR');?></option>
						</select>
					</div>
				</div>
				<div class="mt-2">
					<canvas id="myevent_graph"></canvas>
				</div>
			</div>
			<!--Event performance map end-->
			<?php
		}
		else
		{
			?>
			<!--Event image-->
			<div class="eventDetails-banner" style="background-image:url('<?php echo $imagePath;?>');"></div>
			<!--Event image end-->
			<?php
		}
		?>

		<!--Event date, name, organizer, book button-->
		<div class="eventDetails-meta">
			<div class="eventDetails-time hidden-xs">
				<h4 class="af-m-0">
					<strong class="af-d-block af-mb-5">
						<?php echo HTMLHelper::date($this->item->startdate, Text::_("COM_JTICKETING_DATE_FORMAT_EVENT_DETAIL_DATE"), true);?>
					</strong>
					<?php echo HTMLHelper::date($this->item->startdate, Text::_("COM_JTICKETING_DATE_FORMAT_EVENT_DETAIL_MONTH"), true);?>
				</h4>
			</div>

			<!--Event Title start-->
			<div class="eventDetails-title af-mt-15">
				<h1 class="af-mt-0 af-font-bold h2 text-left">
					<?php
					if ($this->item->created_by == $this->userid)
					{
						$editEventlink = Route::_('index.php?option=com_jticketing&view=eventform&id=' . $this->item->id . '&Itemid=' . $this->createEventItemid, false);
						?>

							<a id="eventEdit" href="<?php echo $editEventlink?>" title="<?php echo Text::_('COM_JTICKETING_EDIT_EVENT_LINK')?>">
									<?php echo $this->escape($this->item->title);?>
							<small>
								<i class="fa fa-pencil-square-o" aria-hidden="true"></i>
							</small>
							</a>
						<?php
					}
					else
					{
						echo $this->escape($this->item->title);
					}
					?>
				</h1>
			</div>
			<!--Event Title end-->

			<!--Event Organizer start-->
			<div class="eventDetails-organizer text-muted">
				<?php
				$userinfo = Factory::getUser($this->item->created_by);
				echo Text::_('COM_JTICKETING_EVENT_DETAIL_ORGANIZER_BY') . ' ' . $userinfo->name;

				if ($this->item->organizerProfileUrl)
				{
					$socialIntegration = $this->params->get('social_integration');

					echo '<br/>';

					if ($socialIntegration != 'joomla')
					{
						?>
						<a href="<?php echo $this->item->organizerProfileUrl;?>">
							<?php echo ucfirst(Text::_('COM_JTICKETING_EVENT_ORGANIZER_DETAILS'));?>
						</a>
						<?php
					}
				}
				?>
			</div>
			<!--Event Organizer end-->

			<!----Price---->
			<div class="eventDetails-price h4">
				<?php
				if (($this->item->eventPriceMaxValue == $this->item->eventPriceMinValue) AND (($this->item->eventPriceMaxValue == 0)
							AND ($this->item->eventPriceMinValue == 0)))
				{
						?>
					<strong>
						<?php echo strtoupper(Text::_('COM_JTICKETING_ONLY_FREE_TICKET_TYPE'));?>
					</strong>
					<?php
				}
				elseif (($this->item->eventPriceMaxValue == $this->item->eventPriceMinValue) AND
							(($this->item->eventPriceMaxValue != 0) AND ($this->item->eventPriceMinValue != 0)))
				{
					?>
					<strong>
						<?php echo $this->utilities->getFormattedPrice($this->item->eventPriceMaxValue);?>
					</strong>
					<?php
				}
				elseif (($this->item->eventPriceMaxValue == 1) AND ($this->item->eventPriceMinValue == -1))
				{
					?>
					<strong>
						<?php echo strtoupper(Text::_('COM_JTICKETING_HOUSEFULL_TICKET_TYPE'));?>
					</strong>
					<?php
				}
				else
				{
					?>
					<strong>
						<?php
						echo $this->utilities->getFormattedPrice($this->item->eventPriceMinValue);
						echo ' - ';
						echo $this->utilities->getFormattedPrice($this->item->eventPriceMaxValue);
								?>
					</strong>
					<?php
				}
				?>
			</div>
			<!----Price end---->

			<!-- Action Button code Start-->
			<div class="row af-mt-auto">
				<div class="col-xs-12">
					<div class="ticketBookBtn">
						<?php $this->event->meetingView = $this->item ? $this->item->venuedatails : null;
						      $this->event->itemId = $this->item; 
						      $this->event->allData = $this; 
						      echo LayoutHelper::render('event.actions', $this->event); ?>
					</div>
				</div>
				<div class="hidden-xs af-mt-5 col-xs-12">
					<?php
					$this->currentTime = Factory::getDate()->toSql();

					$booking_start_date = ($this->item->booking_start_date != '0000-00-00 00:00:00') ?
					$this->item->booking_start_date : $this->item->created;

					$booking_end_date = ($this->item->booking_end_date != '0000-00-00 00:00:00') ?
					$this->item->booking_end_date : $this->item->enddate;

					if ($booking_start_date > $this->currentTime)
					{
						echo Text::_('COM_JTICKETING_EVENTS_BOOKING_WILL_START');
						echo $bookingStartDate = $this->utilities->getFormatedDate($booking_start_date);
					}
					elseif ($booking_end_date < $this->currentTime)
					{
						echo Text::_('COM_JTICKETING_EVENTS_BOOKING_IS_CLOSED');
					}
					else
					{
						echo Text::_('COM_JTICKETING_EVENTS_BOOKING_WILL_CLOSED');
						echo $bookingEndDate = $this->utilities->getFormatedDate($booking_end_date);
					}
					?>
				</div>
			</div>
			<!--Action Button code end here-->
			<div class="clearfix"></div>
		</div>
	</div>
	<!--Event date, name, organizer, book button end -->

	<!--Zoom meeting in page view load-->
		<?php 
			$this->item->meetingView = $this->venuedatails;
			if ($this->item->venuedatails && $this->onlineZoomEvent)
			{
			    echo LayoutHelper::render(
					'zoomInPage', $this->item, JPATH_SITE . '/plugins/tjevents/zoom/activity/'
				);
			}
		?>
	<!--Zoom meeting in page view load end -->
	<div class="row eventDetails-body">
		<div class="col-sm-4 col-sm-push-8 eventDetails-venue">

			<!--Event location and time-->
			<?php echo $this->loadTemplate("eventdatetime"); ?>
			<!--Event location and time-->

			<!--Sharing Option start-->
			<div class="row">
				<div class="col-xs-12">
					<div class="socialSharing eventDetails-socialSharing">
						<?php
						if ($this->params->get('social_sharing'))
						{
							echo '<div id="fb-root"></div>';
							$fbLikeTweet = Uri::root() . 'media/com_jticketing/js/fblike.js';
							echo "<script type='text/javascript' src='" . $fbLikeTweet . "'></script>";

							// Set metadata
							$config = Factory::getConfig();
							$siteName = $config->get('sitename');
							$document->addCustomTag('<meta property="og:title" content="' . $this->escape($this->item->title) . '" />');
							$document->addCustomTag('<meta property="og:image" content="' . $imagePath . '" />');
							$document->addCustomTag('<meta property="og:url" content="' . $eventUrl . '" />');
							$document->addCustomTag('<meta property="og:description" content="' . nl2br($this->escape($this->item->short_description)) . '" />');
							$document->addCustomTag('<meta property="og:site_name" content="' . $this->siteName . '" />');
							$document->addCustomTag('<meta property="og:type" content="event" />');

							if ($this->params->get('social_shring_type') == 'addtoany')
							{
								$addToAnyShare = '<div class="a2a_kit a2a_kit_size_32 a2a_default_style">';

								if($this->params->get('addtoany_universal_button') == 'before')
								{
									$addToAnyShare .= '<a class="a2a_dd" href="https://www.addtoany.com/share"></a>';
								}

								$addToAnyShare .= $this->params->get('addtoany_share_buttons');

								if($this->params->get('addtoany_universal_button') == 'after')
								{
									$addToAnyShare .= '<a class="a2a_dd" href="https://www.addtoany.com/share"></a>';
								}

								$addToAnyShare .= '</div>';

								$addToAnyShare .= '<script async src="https://static.addtoany.com/menu/page.js"></script>';

								/*output all social sharing buttons*/
								echo' <div id="rr" style="">
									<div class="social_share_container">
									<div class="social_share_container_inner">' .
										$addToAnyShare .
									'</div>
								</div>
								</div>
								';
							}
							else
							{
								echo '<div class="com_jticketing_horizontal_social_buttons af-mr-10 af-mt-5">';
								echo '<div class="af-float-left">
										<div class="fb-like" style=" display: inline-grid" data-href="' . $eventUrl . '" data-send="true" data-layout="button_count" data-width="450" data-show-faces="true">
										</div>
									  </div>';

								echo '<div class="af-float-left">
										&nbsp; <div class="g-plus" data-action="share" data-annotation="bubble" data-href="' . $eventUrl . '"></div>
									  </div>';
								echo '<div class="af-float-left">
											&nbsp; <a href="https://twitter.com/share" class="twitter-share-button" data-url="' . $eventUrl . '" data-counturl="' . $eventUrl . '"  data-lang="en">Tweet</a>
										  </div>';
								echo '</div>
									<div class="com_jticketing_clear_both"></div>';
							}
						}
						?>
						<div class="clearfix"></div>

						<!--Like Dislike Option-->
						<?php
						if (file_exists(JPATH_SITE . '/' . 'components/com_jlike/helper.php'))
						{
							$this->showComments = -1;
							$this->showLikeButtons = 1;
							$jLikeUrl = 'index.php?option=com_jticketing&view=event&id=' . (int) $this->item->id;

							// echo LayoutHelper::render('event.like_buttons', $this->item, JPATH_SITE . '/' . 'components/com_jticketing/layouts',  array('eventUrl' => $eventUrl, 'show_comments' => $this->showComments, 'show_like_buttons' => $this->showLikeButtons));
						}
						?>
					</div>
				</div>
			</div>

			<!--Sharing Option End-->
			<!--Event tags if no description-->
			<div class="row">
				<div class="col-xs-12">
					<div class="af-mt-15">
						<?php
						if (empty($this->item->long_description) && $this->enable_tags == 1
							&& $this->redirect_tags_to_events == 0 && !empty($this->item->tags->itemTags))
						{
							?>
							<?php $this->item->tagLayout = new FileLayout('joomla.content.tags');?>
							<?php echo ($this->item->tagLayout->render($this->item->tags->itemTags));?>
							<?php
						}

						if (empty($this->item->long_description) && $this->enable_tags == 1
							&& $this->redirect_tags_to_events == 1 && !empty($this->item->tags->itemTags))
						{
							?>
							<div class="tags">
								<i class="fa fa-tags"></i>
								<?php
								foreach ($this->item->tags->itemTags as $key => $tag)
								{
									$url = 'index.php?option=com_jticketing&view=events&layout=default';

									// Get itemid
									$itemId = $this->utilities->getItemId($url);
									$href = Route::_(Uri::base(true) . '/index.php?option=com_jticketing&view=events&layout=default&filter[tags]=' . $tag->tag_id . '&Itemid=' . $itemId);
									?>
									<span class="tag-2 tag-list<?php echo $key; ?>" itemprop="keywords">
										<a href="<?php echo $href; ?>" class="label label-info">
											<?php echo $tag->title;?>
										</a>
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
			</div>
			<!--Event tags if no description-->
		</div>

		<!--Event information tabs - Description, activity, attendees, media-->
		<div class="col-sm-8 col-sm-pull-4">
			<div class="af-py-15">
				<?php
					$activeTab = '';
					PluginHelper::importPlugin( 'jticketing' );
					$results = Factory::getApplication()->triggerEvent( 'onEventContentAfterDisplay', array($this->item));

					if ($this->item->long_description)
					{
						$activeTab = 'tab1';
					}
					elseif ($this->params->get('activity'))
					{
						$activeTab = 'tab2';
					}
					elseif ($this->item->allow_view_attendee == 1 && count($this->item->eventAttendeeInfo) > 0)
					{
						$activeTab = 'tab3';
					}
					elseif (isset($this->item->gallery))
					{
						$activeTab = 'tab4';
					}
					elseif (count($this->extraData) && $this->fieldsIntegration == 'com_tjfields')
					{
						$activeTab = 'tab5';
					}
					elseif ($this->fieldsIntegration == 'com_fields' && property_exists($this->item, "customField"))
					{
						$activeTab = 'tab5';
					}
					elseif (isset($this->venueCustomFields) && count($this->venueCustomFields))
					{
						$activeTab = 'venueAddInfo';
					}
					elseif (!empty($this->item->venueGallery))
					{
						$activeTab = 'tab6';
					}
					elseif (!empty($results[0]['html']))
					{
						$activeTab = 'tab7';
					}

					echo HTMLHelper::_('bootstrap.startTabSet', 'JTEventTab', array('active' => $activeTab));

					// #tab1 description start
					if ($this->item->long_description)
					{
						echo HTMLHelper::_('bootstrap.addTab', 'JTEventTab', 'tab1', strtoupper(Text::_('COM_JTICKETING_EVENT_TAB_DETAILS'))); ?>
						<div class="row af-ml-5"><?php
							$cleanHtmlLongDescription = InputFilter::getInstance(array(), array(), 1, 1)->clean($this->item->long_description, 'html');?>
							<?php $long_desc_char = $this->params->get('desc_length', '1', 'INT'); ?>
							<div class="description"></div>
							<div class="long_desc af-text-break">
								<div class="readMore displayFullText" style="-webkit-line-clamp: <?php echo $long_desc_char; ?>;">
									<?php
										echo $cleanHtmlLongDescription;
									?>
								</div>
								<div class="r-more-less-div d-none">
									<?php
										echo '<a href="javascript:" class="r-more-des">' . Text::_("COM_JTICKETING_DESCRIPTION_READ_MORE") . '</a>';
										echo '<a href="javascript:" class="r-less-des d-none">' . Text::_("COM_JTICKETING_DESCRIPTION_READ_LESS") . '</a>';
									
									?>
								</div>
							</div>
							<div class="long_desc_extend no-margin" style="display:none;">
								<?php echo $cleanHtmlLongDescription . ' <a href="javascript:" class="r-less">' . Text::_("COM_JTICKETING_DESCRIPTION_READ_LESS") . '</a>';?>
							</div>
							<!--Event tags-->
							<div class="row">
								<div class="col-xs-12">
									<div class="af-mt-15">
										<?php
										if ($this->enable_tags == 1 && $this->redirect_tags_to_events == 0 && !empty($this->item->tags->itemTags))
										{
											?>
											<?php $this->item->tagLayout = new FileLayout('joomla.content.tags');?>
											<?php echo ($this->item->tagLayout->render($this->item->tags->itemTags));?>
											<?php
										}

										if ($this->enable_tags == 1 && $this->redirect_tags_to_events == 1 && !empty($this->item->tags->itemTags))
										{?>
											<div class="tags">
												<i class="fa fa-tags"></i>
												<?php
												foreach ($this->item->tags->itemTags as $key => $tag)
												{
													$url = 'index.php?option=com_jticketing&view=events&layout=default';

													// Get itemid
													$itemId = JT::utilities()->getItemId($url);
													$href = Route::_(Uri::base(true) . '/index.php?option=com_jticketing&view=events&layout=default&filter[tags]=' . $tag->tag_id . '&Itemid=' . $itemId);
													?>
													<span class="tag-2 tag-list<?php echo $key; ?>" itemprop="keywords">
														<a href="<?php echo $href; ?>" class="label label-info">
															<?php echo $tag->title;?>
														</a>
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
							</div>
						</div>
						<?php
						echo HTMLHelper::_('bootstrap.endTab');
					}
					// #tab1 description end

					// #tab2 activity start
					$activity = $this->params->get('activity');

					if ($activity)
					{
						echo HTMLHelper::_('bootstrap.addTab', 'JTEventTab', 'tab2', strtoupper(Text::_('COM_JTICKETING_EVENT_ACTIVITY')));?> 
						<div class="row af-ml-5">
							<?php echo $this->loadTemplate("activity"); ?>
						</div>
						<?php echo HTMLHelper::_('bootstrap.endTab');
					}
					// #tab2 activity end

					// #tab3 attendee start
					if ($this->item->allow_view_attendee == 1 && count($this->item->eventAttendeeInfo) > 0)
					{
						echo HTMLHelper::_('bootstrap.addTab', 'JTEventTab', 'tab3', strtoupper(Text::_('COM_JTICKETING_EVENT_ATTENDEE')));?> 
						<div class="row af-ml-5">
							<?php echo $this->loadTemplate("attendee"); ?>
						</div>
						<?php echo HTMLHelper::_('bootstrap.endTab');
					}
					// #tab3 attendee end

					// #tab4 event gallery start
					if (isset($this->item->gallery))
					{
						echo HTMLHelper::_('bootstrap.addTab', 'JTEventTab', 'tab4', strtoupper(Text::_('COM_JTICKETING_EVENT_GALLERY')));?>
						<div class="row af-ml-5">
							<?php echo $this->loadTemplate("gallery_" . JTICKETING_LOAD_BOOTSTRAP_VERSION); ?>
						</div>
						<?php echo HTMLHelper::_('bootstrap.endTab');
					}
					// #tab4 event gallery end

					// #tab5 extrafields start
					if (count($this->extraData) && $this->fieldsIntegration == 'com_tjfields')
					{
						echo HTMLHelper::_('bootstrap.addTab', 'JTEventTab', 'tab5', strtoupper(Text::_('COM_JTICKETING_EVENT_ADDITIONAL_INFO')));?>
						<div class="row">
							<div class="col-sm-12">
								<?php
									if ($this->form_extra)
									{
										$count = 0;
										$xmlFieldSets = array();

										foreach ($this->form_extra->getFieldsets() as $k => $xmlFieldSet)
										{
											$xmlFieldSets[$count] = $xmlFieldSet;
											$count++;
										}

										$itemData         = new stdClass();
										$itemData->id     = $this->item->id;
										$itemData->client = 'com_jticketing.event';
										$itemData->created_by = $this->item->created_by;

										// Call the JLayout to render the fields in the details view
										$layout = new FileLayout('event.extrafields', JPATH_ROOT . '/components/com_jticketing');
										echo $layout->render(array('xmlFormObject' => $xmlFieldSets, 'formObject' => $this->form_extra, 'itemData' => $itemData));
									}
								?>
							</div>
						</div>
						<?php echo HTMLHelper::_('bootstrap.endTab');
					}
					elseif ($this->fieldsIntegration == 'com_fields' && property_exists($this->item, "customField"))
					{
						echo HTMLHelper::_('bootstrap.addTab', 'JTEventTab', 'tab5', strtoupper(Text::_('COM_JTICKETING_EVENT_ADDITIONAL_INFO')));?>
						<div class="row af-ml-5">
							<?php echo $this->loadTemplate("extrafields"); ?>
						</div>
						<?php echo HTMLHelper::_('bootstrap.endTab');
					}
					// #tab5 extrafields end

					// #venueAddInfo venue addition info start
					if (isset($this->venueCustomFields) && count($this->venueCustomFields))
					{
						echo HTMLHelper::_('bootstrap.addTab', 'JTEventTab', 'venueAddInfo', strtoupper(Text::_('COM_JTICKETING_EVENT_VENUE_INFO')));?>
							<div class="row">
								<div class="col-md-12 m-2">
									<b><?php echo strtoupper(Text::_('COM_JTICKETING_EVENT_VENUE_INFO')); ?>: </b>
								</div>
								<div class="col-sm-12">
									<table class="table table-striped">
										<tbody>
											<?php
												foreach ($this->venueCustomFields as $field)
												{ ?>
													<tr>
														<td scope="row"><?php echo $field->title; ?></td>
														<td><?php echo $field->value; ?></td>
													<tr>
													<?php
												} ?>
										</tbody>
									</table>
								</div>
							</div>
						<?php echo HTMLHelper::_('bootstrap.endTab');
					}
					// #venueAddInfo venue addition info start

					// #tab6 venuegallery start
					if (!empty($this->item->venueGallery))
					{
						echo HTMLHelper::_('bootstrap.addTab', 'JTEventTab', 'tab6', strtoupper(Text::_('COM_JTICKETING_VENUE_GALLERY')));?>
						<div class="row af-ml-5">
							<?php echo $this->loadTemplate("venuegallery_" . JTICKETING_LOAD_BOOTSTRAP_VERSION); ?>
						</div>
						<?php echo HTMLHelper::_('bootstrap.endTab');
					}
					// #tab6 venuegallery start

					// #tab7 start
					if (!empty($results[0]['html']))
					{
						echo HTMLHelper::_('bootstrap.addTab', 'JTEventTab', 'tab7', strtoupper($results[0]['tabName']));?>
						<div class="row af-ml-5">
							<?php echo $results[0]['html']; ?>
						</div>
						<?php echo HTMLHelper::_('bootstrap.endTab');
					}
					// #tab7 start
					echo HTMLHelper::_('bootstrap.endTabSet');?>
			</div>
		</div>

		<div class="col-xs-12 af-mt-30">
			<!---Map-->
			<?php
				if ($this->item->online_events == '0' && !empty(JT::config()->get('google_map_api_key')))
				{
					$address = $this->item->venue > 0 ? $this->venueAddress : $this->item->location;
				?>
				<div id="jticketing-event-map" class="af-responsive-embed af-responsive-embed-21by9">
					<div id="evnetGoogleMapLocation">
						<iframe width="100%" src="https://www.google.com/maps/embed/v1/place?key=<?php echo $this->params->get('google_map_api_key'); ?>&q=<?php echo $this->escape($address); ?>" allowfullscreen>
						</iframe>
					</div>
				</div>
				<?php
				}
				?>
			<!---Map end-->

			<!--Comment start -->
			<div class="comment af-mt-20">
			<?php
				// Integration with Jlike
				if (file_exists(JPATH_SITE . '/components/com_jlike/helper.php'))
				{
					$this->showComments = 1;
					$this->showLikeButtons = 0;
					$jLikeUrl = 'index.php?option=com_jticketing&view=event&id=' . (int) $this->item->id;

					echo LayoutHelper::render('event.like_buttons', $this->item, JPATH_SITE . '/' . 'components/com_jticketing/layouts',  array('eventUrl' => $jLikeUrl, 'show_comments' => $this->showComments, 'show_like_buttons' => $this->showLikeButtons));
				}
				?>
			</div>
			<!--Comment end -->
		</div>
		<!--Event information tabs - Description, activity, attendees, media End-->
	</div>
</div><!---tjWrap End-->
<input type="hidden" id="event_id" name="event_id" value="<?php echo $this->item->id;?>"/>
<?php
	$date = Factory::getDate();
	$currentDate = HTMLHelper::date($date, 'Y-m-d H:i:s');
	$startDate = HTMLHelper::date($this->item->startdate, 'Y-m-d H:i:s');
	$endDate = HTMLHelper::date($this->item->enddate, 'Y-m-d H:i:s');
	$guest = Factory::getUser()->id ? 0 : 1;

Factory::getDocument()->addScriptDeclaration('
	var event_id = "' . $this->item->id . '";
	var currentDate ="' . $currentDate . '";
	var startDate = "' . $startDate . '";
	var endDate = "' . $endDate . '";
	var startDateUTC = "' . $this->item->startdate . '";
	var endDateUTC = "' . $this->item->enddate . '";
	var guest = "' . $guest . '";
	var onlineEvent = "' . $this->item->online_events . '";
	jtSite.event.initEventDetailJs();
');
?>

<script>
	/** global: jticketing_baseurl */
	jQuery(document).ready(function() {
		jQuery('.long_desc .displayFullText').click(function() {
			jQuery(this) . toggleClass('readMore');
			jQuery('.long_desc .r-more-des'). toggleClass('hide');
			jQuery('.long_desc .r-less-des'). toggleClass('hide');
		});

		if (jQuery('.long_desc .displayFullText').length)
		{
			if (jQuery('.long_desc .displayFullText').get(0).scrollHeight > jQuery('.long_desc .displayFullText').get(0).clientHeight)
			{
				jQuery('.r-more-less-div').removeClass('d-none');
			}
		}
	});
</script>
