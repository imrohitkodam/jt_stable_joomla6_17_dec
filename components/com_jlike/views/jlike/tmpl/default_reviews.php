<?php

// No direct access.
defined('_JEXEC') or die();

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

$loginuserflag= $this->reviews_count_loginuser[0] ? $this->reviews_count_loginuser[0] : 0;
HTMLHelper::_('jquery.token');
 ?>
<style>
.drop-area {
    width: 100%;
    padding: 20px;
    border: 2px dashed #ccc;
    text-align: center;
    cursor: pointer;
}
.drag-over {
    background-color: #f3f3f3;
}
.upload-label {
    color: blue;
    cursor: pointer;
}
.image-container {
	position: relative;
	display: inline-block;
	margin: 10px;
}
.delete-icon {
	position: absolute;
	top: 5px;
	right: 5px;
	background: rgba(255, 0, 0, 0.7);
	color: white;
	border: none;
	border-radius: 50%;
	width: 25px;
	height: 25px;
	display: flex;
	justify-content: center;
	align-items: center;
	cursor: pointer;
}
.showEditDeleteButton {
	cursor: pointer;

}
.loader {
	border: 4px solid #f3f3f3;
	border-top: 4px solid #3498db;
	border-radius: 50%;
	width: 40px;
	height: 40px;
	animation: spin 1s linear infinite;
	display: none;
	margin: auto;
}
@keyframes spin {
	0% { transform: rotate(0deg); }
	100% { transform: rotate(360deg); }
}
</style>

<form action="<?php echo $this->urldata->url; ?>" method="post" id="jlike_reviews" name="jlike_reviews" enctype="multipart/form-data">
	<div class="row-fluid jlike_comments_container jlike_text_decoration">
		<?php
		if($this->urldata->show_reviews== 1)
		{
			$userReviewed = array_filter($this->reviews, function($obj) use ($loged_user) {
				return $obj->user_id == $loged_user;
			}, 0);

			$isUserReviewed = count($userReviewed) > 0 ? true: false;
		?>
		<div class="jlike-comment-header">
			<div class="jlike_commentBox ">
				<div class="pull-left">
					<div class="jlike_comment_sort">
						<a data-toggle="dropdown" href="#" class="jlike_comment_msg">
							<i class="jlike_icon_comment_pos icon-comments"></i>
							<span id="total_comments" class="count_reviews"><?php echo $flag= $this->reviews_count ? $this->reviews_count : 0;   ?></span>
							<span ><?php  echo $this->urldata->show_reviews ?  Text::_('COM_JLIKE_REVIEWS'): '<a href="'.$this->urldata->url.'" title="Click here to add Reviews"> '.Text::_('COM_JLIKE_REVIEWS').'</a>' ?></span>
							<?php if($flag): ?>
							<span class="caret jlike_caret_margin"></span>
							<?php endif; ?>
						</a>

						<?php if($flag): ?>
						<ul class="dropdown-menu jlike_list_style_type">
							<li id="lioldest" tabindex="-1">
								<a id="alatest" class="showEditDeleteButton" onclick="setAscending('<?php echo $likecontainerid;?>')"><?php echo Text::_('COM_JLIKE_SET_OLDEST'); ?></a>
							</li>
							<li id="lilatest" tabindex="-1">
								<a id="aoldest" class="showEditDeleteButton" onclick="setDecending('<?php echo $likecontainerid;?>')"><?php echo Text::_('COM_JLIKE_SET_LATEST'); ?></a>
							</li>
						</ul>
						<?php endif; ?>
					</div>
				</div>

				<?php if($loginuserflag): ?>
					<div id="divaddcomment" class="pointer pull-right jlike_review_display" style="display:none;" >
				<?php else :?>
					<div id="divaddcomment" class="pointer pull-right jlike_review_display" >
				<?php endif; ?>
						<div class="jlike_loadbar"> <span id="loadingCommentsProgressBar"></span></div>
						<?php //if($this->urldata->show_reviews==1):
						if ($this->urldata->jlike_allow_rating >= 1 && !$isUserReviewed):?>
							<a class="jlike_comment_msg jlike_comment_padding_right " onclick="addCommentArea('<?php echo $likecontainerid;?>','0','0','97.5',0,0,0)"> <?php echo Text::_('COM_JLIKE_ADD_REVIEWS'); ?> </a>
						<?php endif; ?>
					</div>
					<div style="clear:both"></div>
				</div>
			</div>
		<!-- ***************************** Rating ********************** --> 
			<div class="jlike_comments ">
			<?php
				$i                    = 1;
				$user_comment_present = 0;
				$annotaionIds         = array();

				foreach ($this->reviews as $reviews)
				{
					$annotaionIds[] = $reviews->annotation_id;

					if ($reviews->annotation) {
					?>
					
					<div id="jlcomment<?php echo $reviews->annotation_id; ?>" class="media jlike_commentingArea jlike_renderedComment jlike_no_radius jlike_text_decoration" >

						<!-- <hr class="jlike_hr_margin" /> -->

						<div class="review-item border rounded p-3 mb-3 fw-bold">
							<a class="pull-left" href="<?php echo $reviews->user_profile_url; ?>">
								<img class="jlike_tp_margin img-circle jlike-img-border" src="<?php echo $reviews->avtar; ?>" alt="Smiley face" width="36px" height="auto">
							</a>

							<div class="media-body jlike_media_body " >
								<span>
									<a class="ps-2 pull-left" href="<?php echo $reviews->user_profile_url; ?>">
										<?php echo ucwords($reviews->name); ?>
									</a>
									<?php
										if ($loged_user == $reviews->user_id)
										{
											if ($params->get('jlike_allow_rating_edit') == 1)
											{
												?>
												<!-- ***************************** Rating ********************** -->
												<div id="<?php echo 'jlike_show_rating' . $reviews->annotation_id; ?>" class="" >
												<div class="basic_readonly" data-rating="<?php echo $reviews->rating_upto;?>" data-average="<?php echo $reviews->user_rating;?>" data-id="1"></div>
												</div>
												<?php
											} else { ?>
												<!-- ***************************** Rating ********************** -->
												<div id="<?php echo 'jlike_show_rating' . $reviews->annotation_id; ?>" class="" >
												<div class="basic_readonly"  data-rating="<?php echo $reviews->rating_upto;?>"  data-average="<?php echo $reviews->user_rating;?>" data-id="1"></div>
												</div>
											<?php
											}
										}
										else
										{
										?>
										<!-- ***************************** Rating ********************** -->

											<div id="<?php echo 'jlike_show_rating' . $reviews->annotation_id; ?>" class="" >
												<div class="basic_readonly"  data-rating="<?php echo $reviews->rating_upto;?>"  data-average="<?php echo $reviews->user_rating;?>" data-id="1"></div>
											</div>
										<?php } 
										
										
									if ($loged_user == $reviews->user_id)
									{
										if ($params->get('jlike_allow_rating_edit') == 1)
										{
										?><div class="d-flex justify-content-end ">
											<!-- <a data-toggle="dropdown" class="pull-left" onclick="EditComment(this)" href="javascript:void(0)">
												<i class="icon-pencil" ></i>
											</a> -->
											<div id="showEditDeleteButton<?php echo $reviews->annotation_id; ?>" tabindex="-1" class="p-2">
											<a class="showEditDeleteButton" onclick="EditComment(this)" href="javascript:void(0);" >
													<?php echo Text::_('COM_JLIKE_EDIT'); ?>
													<i class="icon-pencil" ></i>
												</a>
											</div>
											<div id="DeleteButton<?php echo $reviews->annotation_id; ?>" tabindex="-1" class="p-2">
											<a class="showEditDeleteButton" onclick="DeleteComment(this)"  class="p-2">
														<?php echo Text::_('COM_JLIKE_DELETE'); ?>
														<i class="icon-trash" ></i>

													</a>
												</div>
										</div><?php
										}
									}
								?></span>

								<div class="jlike_comment_padding_top">
									<div id="showlimited<?php echo $reviews->annotation_id; ?>" class="showlimited_review">
										<?php
										if (strlen(strip_tags($reviews->smileyannotation)) >= 165)
										{
											echo nl2br(trim($this->jlikehelperObj->getsubstrwithHTML($reviews->smileyannotation, 165, '...', true)));
										}
										else
										{
											echo nl2br(trim($reviews->smileyannotation));
										}
										?>

										<a class="jlike_pointer"  onclick="showFullComment(<?php echo $reviews->annotation_id; ?>)">
											<?php
											if (strlen(strip_tags($reviews->smileyannotation)) >= 165)
											{
												echo Text::_('COM_JLIKE_SEE_MORE');
											}
											?>
										</a>
									</div>

									<div id="showlFullComment<?php echo $reviews->annotation_id; ?>" class="jlike_display_none " >
										<?php echo nl2br(trim($reviews->smileyannotation));?>&nbsp;
										<a class="jlike_pointer" onclick="showLimitedComment(<?php echo $reviews->annotation_id; ?>)">
										<?php
											echo Text::_('COM_JLIKE_SEE_LESS');
										?>
										</a>
									</div>

								<!--comment added user & logged in user are the same then show edit comment-->
								<?php
								if ($loged_user == $reviews->user_id)
								{
									?>
									
									<div id="<?php echo 'edit_jlike_show_rating' . $reviews->annotation_id; ?>" class="d-none py-2" >
										<div id="<?php echo 'editRating' . $reviews->annotation_id; ?>" class="basic" data-rating="<?php echo $reviews->rating_upto;?>" data-average="<?php echo $reviews->user_rating;?>" data-id="1"></div>
									</div>
												
									<div id="EditComment<?php echo $reviews->annotation_id; ?>" class="jlike_display_none" >
										<div class="jlike_textarea taggable" id="CommentText<?php echo $reviews->annotation_id; ?>" contenteditable="true" <?php echo $maxlength; ?> required="required" onkeyup="characterLimit(id, <?php echo $maxlength; ?>)">
											<?php
												echo nl2br(trim($reviews->annotation));
												$user_comment_present = 1;
											?>
										</div>

										<div class="jlike_smiley_container">
											<div id="<?php echo $reviews->annotation_id; ?>" class="jlike_smiley jlike_display_inline_blk jlike_btn_container" >
												<button id="jlike_smiley" class="jlike_smiley " type="button" onClick="javascript:jLikeshowSmiley(this,<?php echo $reviews->annotation_id; ?>);">
												</button>
											</div>
										</div>
									</div><?php
								}
								else
								{ ?>
									<div class="jlike_textarea jlike_display_none taggable"  <?php echo $maxlength; ?> id="CommentText<?php echo $reviews->annotation_id; ?>" contenteditable="true" <?php echo $maxlength; ?>  required="required"><?php
										echo nl2br(trim($reviews->smileyannotation));
										$user_comment_present = 1;
									?></div>
									<div id="displaytagsfor_CommentText<?php echo $reviews->annotation_id; ?>" class="displayme_CommentText"></div><?php
								}
								?>

								<div id="prodReviewImage<?php echo $reviews->annotation_id; ?>" class="productReviewImages">
								<?php 
								$images = json_decode($reviews->images);
								// echo "<Pre>"; print_r($images);exit;
								if( ! empty($reviews->images) && count($images) > 0):
									for($i=0; $i < count($images); $i++): 
								?>
								<div class='col-md-3 image-container'>
									<a href="<?php echo Uri::root(). 'images/reviews/' .$images[$i]?>" target="_blank">
										<img class='img-fluid rounded' alt='Image' src="<?php echo Uri::root(). 'images/reviews/' .$images[$i]?>" />
									</a>
								</div>
								<?php 
									endfor;
								endif; ?>
								</div>

								<div id="drop-area<?php echo $reviews->annotation_id; ?>" class="drop-area d-none mt-3 p-2">
									<p class="mb-0">Drag & Drop an image or <label for="image-upload<?php echo $reviews->annotation_id; ?>" class="upload-label">click to select</label></p>
									<input type="file" id="image-upload<?php echo $reviews->annotation_id; ?>" name="image" accept="image/*" style="display:none;">
									<div class="loader" id="loader"></div>
								</div>

								<div id="addedProductReviewImages<?php echo $reviews->annotation_id; ?>" class="col-sm-12 col-md-12 d-none mt-2 productReviewImages row">
								<?php 
								$images = json_decode($reviews->images);
								if( ! empty($reviews->images) && count($images) > 0):
									for($i=0; $i < count($images); $i++): 
								?>
									<div class='col-md-3 image-container' id='img<?=$i?>'>
										<input type='hidden' name='reviewImages[]' value='<?php echo $images[$i]?>'/>
										<button class='delete-icon' type='button' name='deleteReviewImages' onclick='deleteReviewImage(<?php echo "\"img".$i."\", \"".$images[$i]."\"";?> )'>&times;</button>
										<img class='img-fluid rounded' alt='Image' src="<?php echo Uri::root(). 'images/reviews/' .$images[$i]?>" />
									</div>
								<?php 
									endfor;
								endif; ?>
								</div>

								<div class="row-fluid jlike_comment_padding_top" >
									<span class="small">
										<?php
										if ($loged_user == $reviews->user_id)
										{
											?>
											<span class="d-flex justify-content-end">
												<span id="jlike_cancel_comment_btn<?php echo $reviews->annotation_id; ?>" class="jlike_display_none" >
													<button type="button" class='btn btn-small jlike_cancelbtn' onclick="Cancel(<?php echo $reviews->annotation_id; ?>)">
														<?php echo Text::_('COM_JLIKE_REVIEW_CANCEL_BTN_LABEL'); ?>
													</button>
													<button type="button" class='btn btn-success btn-small jlike_commentbtn' onclick="SaveEditedComment(<?php echo $reviews->annotation_id; ?>,<?php echo $reviews->annotation_id; ?>)">
														<?php echo Text::_('COM_JLIKE_REVIEW_SUBMIT_BTN_LABEL'); ?>
													</button>
												</span>
											</span>
											<?php
										}

										if($params->get('allow_threaded_comment'))
										{ ?>

											<a id="parentid<?php echo $reviews->annotation_id; ?>"
												class="jlike_pointer"
												onclick="jlike_reply(this,'7','<?php echo '80%';?>',37,1)" >
													<?php echo Text::_('COM_JLIKE_REPLY_BTN'); ?>
											</a>
										

										<span id="parentid_show_reply<?php echo $reviews->annotation_id; ?>"
											class="jlike_pointer"
											onclick='show_reply(this,<?php echo (json_encode($reviews->children));?>,8,92,1,0, "<?php echo $likecontainerid; ?>")'>
											<div class="jlike_count_box">
												<?php echo $reviews->replycount; ?>
											</div>
										</span>
										<?php
										} ?>
										<!--Show rating time -->
										<span id="<?php echo 'jlike_comment_time' . $reviews->annotation_id; ?>" class="jlike_comment_time pull-right" >
											<?php
											echo $reviews->date;
											echo $reviews->time;
											?>
										</span>
									</span>
									<!-- this one-->
								</div>
							</div>
							</div>
						</div>
					</div> <!-- end of row-fluid--class -->
					<?php
					$i++;
				}
			} //print_r($annotaionIds); //end of foreach for reviews
			?>
			</div> <!-- End of Main comment div-->
			<div style="clear:both"></div>
			<div class="row-fluid">
				<span id="progessBar"></span>
				<!-- @S View More button-->
				<?php
				// Show only when comments on content available more that loaded at page load
				if ($comment_limit < $this->reviews_count)
				{ ?>
					<div class="jlike_viewReviewsMsg">
						<div id="viewReviewsMsg" class=" span12 center btn pointer" onclick="showAllReviews(0,0)">
							<span  class="reviews_count"><?php
								echo Text::_('COM_JLIKE_VIEW_MORE') . '  ' . Text::_('COM_JLIKE_VIEW_MORE1');
							?></span>
							<span id="caret" class="caret jlike_caret_margin_top"></span>
						</div>
					</div>
				<?php
				}
				?>
				<div class="clearfix"></div>
				<!-- @E View More button -->

				<!-- show user name in popup who like the comment !-->

				<a id="user_info_modal" href="#like_dislike_users" role="button" class="btn jlike_display_none" data-toggle="modal"></a>

				<!-- Modal -->
				<div id="like_dislike_users" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
					<div class="modal-dialog modal-lg">
      					<div class="modal-content">
							<div class="modal-header">
								<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
								<h3 id="myModalLabel" class="modal_header"></h3>
							</div>
							<div class="modal-body">
								<div id="modalconent" class="modal-body"></div>
							</div>
							<div class="modal-footer">
								<button class="btn" data-dismiss="modal" aria-hidden="true"><?php echo Text::_('COM_JLIKE_CLOSE'); ?></button>
							</div>
						</div>
					</div>
				</div>
				<div class="clearfix"></div>
			</div>
			<input type="hidden" id="sorting" name="sorting" value="1" /><?php
		}
	?>
	</div>
</form>
<?php
if ($this->urldata->show_reviews == 1)
{
	// Require the rating scripting methods file
	require_once(JPATH_SITE . DS . 'components' . DS . 'com_jlike' . DS . 'views' . DS . 'jlike' . DS . 'tmpl' . DS . 'reviews.php');
}
?>
