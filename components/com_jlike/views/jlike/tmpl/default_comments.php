<?php 

// No direct access.
defined('_JEXEC') or die();

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

HTMLHelper::_('jquery.token');
?>

<form action="<?php echo $this->urldata->url;?>" method="post" id="jlike_comments" name="jlike_comments" enctype="multipart/form-data">
<div class="row-fluid jlike_comments_container jlike_text_decoration">

			<?php
			if($this->urldata->show_comments!=-1)
			{ ?>
				<div class="jlike-comment-header">
					<div class="jlike_commentBox ">

						<div class="<?php echo $this->urldata->show_comments ? 'pull-left':'pull-right'; ?>">
							<div class="jlike_comment_sort">
								<?php
								$temp_class = '';
								if (!empty($this->comments_count[0]))
								{
									$temp_class = "avoid-clicks";
								}
								?>

								<a data-toggle="dropdown" href="javascript:void(0)" class="jlike_comment_msg <?php echo $temp_class;?>">
									<i class="jlike_icon_comment_pos icon-comments"></i>
									<span id="total_comments<?php echo $for_id_type; ?>">
										<?php echo $flag = (!empty($this->comments_count[0])) ? $this->comments_count[0] : 0;   ?>
									</span>
									<span>
										<?php  echo $this->urldata->show_comments ?  Text::_('COM_JLIKE_COMMENTS'): '<a href="'.$this->urldata->url.'" title="Click here to add comment"> '.Text::_('COM_JLIKE_COMMENTS').'</a>' ?>
									</span>

									<?php if($flag AND $this->urldata->show_comments): ?>
										<span class="caret jlike_caret_margin"></span>
									<?php endif; ?>
								</a>

								<?php if($flag && $this->urldata->show_comments==1): ?>
									<ul class="dropdown-menu jlike_list_style_type">
										<li id="asc-<?php echo $likecontainerid; ?>" tabindex="-1">
											<a href="javascript:void(0);" onclick="commentSorting(this,'asc','<?php echo $likecontainerid;?>');">
												<?php echo Text::_('COM_JLIKE_SET_OLDEST'); ?>
											</a>
										</li>
										<li id="desc-<?php echo $likecontainerid; ?>" tabindex="-1">
											<a href="javascript:void(0);" onclick="commentSorting(this,'desc','<?php echo $likecontainerid;?>')">
												<?php echo Text::_('COM_JLIKE_SET_LATEST'); ?>
											</a>
										</li>
									</ul>
								<?php endif; ?>

							</div>
						</div>

						<div id="divaddcomment<?php echo $for_id_type; ?>" class="pointer pull-right" >
							<div class="jlike_loadbar">
								<span id="loadingCommentsProgressBar<?php echo $for_id_type; ?>"></span>
							</div>
							<?php if($this->urldata->show_comments==1):?>
							<a class="jlike_comment_msg jlike_comment_padding_right" onclick="addCommentArea('<?php echo $likecontainerid;?>','0','0','97.5',0,0)"> <?php echo Text::_('COM_JLIKE_ADD_COMMENTS'); ?> </a>
							<?php endif; ?>
						</div>
						<div style="clear:both"></div>
					</div>
				</div>
			<?php
			} ?>

		<?php if($this->urldata->show_comments==1): ?>
		<div class="jlike_comments">
			<?php

			$i=1;
			$user_comment_present=0;
			$annotaionIds=array();

			foreach($this->comments as $comment)
			{
				$annotaionIds[]=$comment->annotation_id;

				if($comment->annotation)
				{
				 ?>

				<div id="jlcomment<?php echo $comment->annotation_id; ?>" class="media jlike_commentingArea jlike_renderedComment jlike_no_radius jlike_text_decoration" >

					<?php if (!empty($comment->user_profile_url)) : ?>
						<a class="pull-left" target="_blank" href="<?php echo $comment->user_profile_url?>" >
					<?php else:	?>
						<div class="pull-left">
					<?php endif; ?>
							<img class="jlike_tp_margin img-rounded" src="<?php echo $comment->avtar; ?>" alt="Smiley face" width="36px" height="auto">
					<?php if (!empty($comment->user_profile_url)) : ?>
						</a>
					<?php else:	?>
						</div>
					<?php endif; ?>

					<div class="media-body jlike_media_body" >
						<span>
							<?php if (!empty($comment->user_profile_url)) : ?>
								<a href="<?php echo $comment->user_profile_url; ?>">
							<?php endif; ?>
									<strong class="jlike-comment-author"><?php echo ucwords($comment->name); ?></strong>
							<?php if (!empty($comment->user_profile_url)) : ?>
								</a>
							<?php endif; ?>

							<?php if($loged_user==$comment->commenter_id){ ?>
							<div class="jlike_position_relative pull-right">
								<a data-toggle="dropdown" class="pull-left" href="#">
									<i class="icon-pencil" ></i>
								</a>
								<ul class="dropdown-menu jlike_edit_dropdown jlike_list_style_type" >
									<li id="showEditDeleteButton<?php echo $comment->annotation_id; ?>" tabindex="-1">
										<a href="javascript:void(0)" onclick="EditComment(this)"><?php echo Text::_('COM_JLIKE_EDIT'); ?></a>
									</li>
									<li id="DeleteButton<?php echo $comment->annotation_id; ?>" tabindex="-1">
										<a href="javascript:void(0)" id="#DeleteButton<?php echo $comment->annotation_id; ?>" onclick="DeleteComment(this, '<?php echo $likecontainerid;?>')"><?php echo Text::_('COM_JLIKE_DELETE'); ?></a>
									</li>
								</ul>
							</div>
							<?php } ?>
						</span>

						<div class="jlike_comment_padding_top">

							<div id="showlimited<?php echo $comment->annotation_id; ?>" >
								<?php
									if (strlen(strip_tags($comment->smileyannotation))>=165)
									{
										echo $this->jlikehelperObj->getsubstrwithHTML($comment->smileyannotation, 165, '...', true);
									}
									else
									{
										echo $comment->smileyannotation;
									} ?>

								<a class="jlike_pointer"  onclick="showFullComment(<?php echo $comment->annotation_id; ?>)"><?php
									if(strlen(strip_tags($comment->smileyannotation))>=165)
									{
										 echo Text::_('COM_JLIKE_SEE_MORE');
									} ?>
								</a>
							</div>

							<div id="showlFullComment<?php echo $comment->annotation_id; ?>" class="jlike_display_none" >
								<?php echo $comment->smileyannotation; ?>&nbsp;
								<a class="jlike_pointer" onclick="showLimitedComment(<?php echo $comment->annotation_id; ?>)">
									<?php echo Text::_('COM_JLIKE_SEE_LESS'); ?>
								</a>
							</div>

							<!-- Comment added user & logged in user are the same then show edit comment-->
							<?php if ($loged_user == $comment->user_id): ?>
								<div id="EditComment<?php echo $comment->annotation_id;?>" class="jlike_display_none" >
									<div id="CommentText<?php echo $comment->annotation_id;?>"
										class="jlike_textarea taggable jlike-mention_<?php echo $likecontainerid; ?>"
										required="required"
										contenteditable="true"
										onkeyup="characterLimit(id, <?php echo $maxlength; ?>)"><?php echo nl2br(trim($comment->annotation));$user_comment_present=1;?></div>

									<div class="jlike_smiley_container">
										<div id="<?php echo $comment->annotation_id; ?>" class="jlike_smiley jlike_display_inline_blk jlike_btn_container" >
											<button id="jlike_smiley" class="jlike_smiley " type="button" onClick="javascript:jLikeshowSmiley(this,<?php echo $comment->annotation_id; ?>);">
											</button>
										</div>
									</div>
								</div>

							<?php else: ?>
								<div class="jlike_textarea jlike_display_none taggable" id="CommentText<?php echo $comment->annotation_id;?>" contenteditable="true" required="required"><?php echo nl2br(trim($comment->smileyannotation));$user_comment_present=1;?></div>
								<div id="displaytagsfor_CommentText<?php echo $comment->annotation_id;?>" class="displayme_CommentText"></div>
							<?php endif; ?>

							<div class="row-fluid jlike_comment_padding_top" >
								<span class="small">

								<!--@ threaded REPLY button-->
								<?php if($params->get('allow_threaded_comment'))
								{ ?>
									<a id="parentid<?php echo $comment->annotation_id; ?>" class="jlike_pointer" onclick="jlike_reply(this,'<?php echo $margin_left;?>','<?php echo $width;?>',37,1, '<?php echo $likecontainerid;?>')" ><?php echo Text::_('COM_JLIKE_REPLY_BTN'); ?>
									</a>
									<?php
								} ?>

								<!--@ threaded REPLIES COUNT -->
								<?php if($params->get('allow_threaded_comment')): ?>
									<span id="parentid_show_reply<?php echo $comment->annotation_id; ?>" class="jlike_pointer" onclick='show_reply(this,<?php echo (json_encode($comment->children));?>,8,92,1,0, "<?php echo $likecontainerid; ?>")' >
										<span class="jlike_count_box"><?php echo $comment->replycount; ?></span>
									</span>

									<span id="nbspId<?php echo $comment->annotation_id; ?>" > &nbsp;&nbsp; </span>
								<?php endif; ?>
								<!-- LIKE button
								$comment->userLikeDislike =0  => user not like or dislike on this comment
								$comment->userLikeDislike =1  => user not like this comment
								$comment->userLikeDislike =2  => user not dislike this comment
								-->

									<?php if($params->get('like_dislike_comments'))
									{

										if(!$comment->likeCount)
										{
											$comment->likeCount=0;
										 } ?>

										 <span class="">

											<!--LIKE BUTTON -->

											<a id="like_annotationid<?php echo $comment->annotation_id; ?>" class="jlike_like_btn jlike_pointer " onclick="increaseLikeCount(this,'<?php echo $likecontainerid; ?>')" >
												<span id="like_unlike<?php echo $comment->annotation_id; ?>">
												<?php
													if($comment->userLikeDislike==1)
													{
														echo Text::_('COM_JLIKE_UNLIKE_BTN');
													}
													else
													{
														echo Text::_('COM_JLIKE_LIKE_BTN');
													}
												?>
												</span>
											</a>
											<span id="jlike_like_count_area<?php echo $comment->annotation_id; ?>" >

												<!--@ threaded LIKE COUNT -->
												<span id="commentid<?php echo $comment->annotation_id; ?>" class="user_liked jlike_pointer" onClick="getUsersByCommentId('<?php echo $comment->annotation_id; ?>', '1');">
													<div id="like_count<?php echo $comment->annotation_id; ?>" class="jlike_count_box">
														<?php echo $comment->likeCount; ?>
													</div>
												</span>
											</span>
										</span>

										<!-- DISLIKE BUTTON -->

										<?php if($params->get('show_comment_dislike_button'))
										{

											if(!$comment->dislikeCount){
												$comment->dislikeCount=0;
											 } ?>
											 &nbsp;&nbsp;
											<span class="">
												<a id="dislike_annotationid<?php echo $comment->annotation_id; ?>" class=" jlike_dislike_btn jlike_pointer " onclick="increaseDislikeCount(this,'<?php echo $likecontainerid; ?>')" >
													<span id="dislike_undislike<?php echo $comment->annotation_id; ?>">
													<?php
														if($comment->userLikeDislike==2)
															echo Text::_('COM_JLIKE_UNDISLIKE_BTN');
														else
															echo Text::_('COM_JLIKE_DISLIKE_BTN');
													?>
												</span>
												</a>

												<!--@ threaded DISLIKE COUNT -->
												<span id="jlike_dislike_count_area<?php echo $comment->annotation_id; ?>">
													<span id="commentDislike<?php echo $comment->annotation_id; ?>" class="user_disliked jlike_pointer" onClick="getUsersByCommentId('<?php echo $comment->annotation_id; ?>', '0');">
														<div id="dislike_count<?php echo $comment->annotation_id; ?>" class="jlike_count_box"><?php echo $comment->dislikeCount; ?></div>
													</span>
												</span>
											</span><?php
										}
									} ?>
									<?php if($loged_user==$comment->user_id)
									{ ?>
										<span >
											<span id="jlike_cancel_comment_btn<?php echo $comment->annotation_id;?>" class=" jlike_cancel_comment_btn jlike_display_none" >
												<button type="button" class='btn btn-small jlike_cancelbtn' onclick="Cancel(<?php echo $comment->annotation_id; ?>)"> <?php echo Text::_('COM_JLIKE_CACEL'); ?></button>
												<button type="button" class='btn btn-success btn-small jlike_commentbtn' onclick="SaveEditedComment(<?php echo $comment->annotation_id; ?>,<?php echo $comment->annotation_id; ?>, '<?php echo $likecontainerid;?>')"> <?php echo Text::_('COM_JLIKE_COMMENT'); ?></button>
											</span>
										</span>
									<?php
									} ?>

									<!--end threaded reply button-->
									<!--Show comment time -->
									<span id="<?php echo 'jlike_comment_time'.$comment->annotation_id; ?>" class="jlike_comment_time pull-right" >
										<!--<label class="timeago" title="<?php //echo $comment->date; ?>" style=" font-weight:bold"/> -->
									<?php
										echo $comment->date;
										echo $comment->time;?>
									</span>
								</span>
								<!-- this one-->
							</div>


						</div>
					</div>

				</div> <!-- end of row-fluid--class -->

					<?php
				$i++;
				}
			}  //print_r($annotaionIds); //end of foreach for comments ?>

		</div> <!-- End of Main comment div-->
		<div style="clear:both"></div>
		<div class="row-fluid">
			<span id="progessBar<?php echo $for_id_type; ?>"></span>

			<!-- @S View More button
			-->

			<?php
			// Show only when comments on content available more that loaded at page load
			if($comment_limit < (!empty($this->comments_count[0])) ? $this->comments_count[0] : 0)
			{ ?>
				<div class="jlike_viewCommentsMsg">
					<div id="viewCommentsMsg<?php echo $likecontainerid; ?>" class=" span12 center btn pointer" onclick="showAllComments(0,0, '<?php echo $likecontainerid;?>')">
						<span  class="comments_count"><?php echo  Text::_('COM_JLIKE_VIEW_MORE').'  '. Text::_('COM_JLIKE_VIEW_MORE1') ?></span>
						<span id="caret<?php echo $likecontainerid; ?>" class="caret jlike_caret_margin_top"></span>
					</div>
				</div>
				<?php
			} ?>
			<div class="clearfix"></div>
			<!-- @E View More button -->

			<!-- show user name in popup who like the comment !-->
			<!-- Button to trigger modal -->
			<a id="user_info_modal<?php echo $for_id_type; ?>" href="#like_dislike_users" role="button" class="btn jlike_display_none" data-toggle="modal"></a>

			<!-- Modal -->
			<div id="like_dislike_users<?php echo $for_id_type; ?>" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
				<div class="modal-dialog modal-lg">
      				<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
							<h3 id="myModalLabel<?php echo $for_id_type; ?>" class="modal_header"></h3>
						</div>
						<div class="modal-body">
							<div id="modalconent<?php echo $for_id_type; ?>" class="modal-body"></div>
						</div>
						<div class="modal-footer">
							<button class="btn" data-dismiss="modal" aria-hidden="true"><?php echo Text::_('COM_JLIKE_CLOSE'); ?></button>
						</div>
					</div>
				</div>
			</div>

			<div class="clearfix"></div>
		</div>

			<input type="hidden" id="sorting<?php echo $for_id_type; ?>" name="sorting" value="1" />
		<?php endif; ?>
	</div>
</form>

<?php
if ($this->urldata->show_comments == 1)
{
	// Require the comment scripting methods file
	require JPATH_SITE . '/components/com_jlike/views/jlike/tmpl/comment.php';
}

// @TODO - commented to avoid conflict with bootstrap.js
/*
$document = Factory::getDocument();
$document->addScript(Uri::root(true) . '/media/sourcecoast/js/jquery-ui.js');
$document->addStyleSheet(Uri::root(true) . '/media/sourcecoast/css/jquery-ui/jquery-ui.css');
$document->addScript(Uri::root(true) . '/components/com_jlike/assets/scripts/jquery.mentions.js');
$document->addScript(Uri::root(true) . '/components/com_jlike/assets/scripts/comment.mention.js');
*/
?>

<script>
	jQuery(document).ready(function(){
		var className = ".jlike-mention_<?php echo $likecontainerid; ?>";
//		init_mention(className, <?php echo json_encode($this->userslist); ?>);
	});

	window.onload = function (){
		getTextareaVal = function (inputId) {
			return jQuery('#'+inputId).mentionsInput('getValue');
		}
	}
</script>
