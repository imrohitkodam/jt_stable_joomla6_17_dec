<?php
/**
 * @package		jLike
 * @author 		Techjoomla http://www.techjoomla.com
 * @copyright 	Copyright (C) 2011-2012 Techjoomla. All rights reserved.
 * @license 	GNU/GPL v2 http://www.gnu.org/licenses/gpl-2.0.html
 *
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\Filesystem\File;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;

if (!defined('DS'))
{
	define('DS', DIRECTORY_SEPARATOR);
}

// TO use lanugage cont in javascript
Text::script('COM_JLIKE_REMOVE_FROM_PLAN_ALERT', true);
Text::script('COM_JLIKE_MUSTLOGIN', true);
$document = Factory::getDocument();

HTMLHelper::_('jquery.framework');

if (JVERSION < '4.0.0')
{
	HTMLHelper::_('behavior.framework', true);
}

HTMLHelper::_('jquery.token');

// Loading JS
HTMLHelper::script('media/com_jlike/vendors/jquery-loading-overlay/loadingoverlay.min.js');

// Essential for Recommend / Assign popup
HTMLHelper::_('bootstrap.renderModal', 'a.modal');

$tjStrapperPath = JPATH_SITE . '/media/techjoomla_strapper/tjstrapper.php';

if (File::exists($tjStrapperPath))
{
	require_once $tjStrapperPath;
	TjStrapper::loadTjAssets('com_jlike');
}

//~ HTMLHelper::stylesheet(Uri::root(true). '/components/com_jlike/assets/css/jlike_review_rating.css' );
$document->addScript(Uri::root(true) . '/components/com_jlike/assets/scripts/jlike.js');
$document->addScript(Uri::root(true) . '/components/com_jlike/assets/scripts/jlike_comments.js');
$params = $this->params;
$data   = $this->data;

$load_bootstrap = $params->get('load_bootstrap');

// Check config
if ($load_bootstrap)
{
	// Load bootstrap CSS.
	HTMLHelper::_('bootstrap.loadcss');
}

//comments
//get looged user details
$userobject = Factory::getUser();
$loged_user                 = Factory::getUser()->id;
$userInfo                   = new StdClass();
$userInfo->id               = $loged_user;
$userInfo->email            = Factory::getUser()->email;
$userInfo->name             = Factory::getUser()->name;
//get user profile url & profile pic

$helperPath = JPATH_SITE . '/components/com_jlike/helpers/main.php';

if (!class_exists('ComjlikeMainHelper'))
{
	// Require_once $path;
	JLoader::register('ComjlikeMainHelper', $helperPath);
	JLoader::load('ComjlikeMainHelper');
}

$ComjlikeMainHelper = new ComjlikeMainHelper;


$link = '';

$sLibObj  = $ComjlikeMainHelper->getSocialLibraryObject('', array("plg_type" => $this->urldata->plg_type, "plg_name" => $this->urldata->plg_name));
$link = $profileUrl = $sLibObj->getProfileUrl($userobject);

if ($profileUrl)
{
	if (!parse_url($profileUrl, PHP_URL_HOST))
	{
		$link = Uri::root() . substr(Route::_($profileUrl), strlen(Uri::base(true)) + 1);
	}
}

$userInfo->user_profile_url = $link;
$userInfo->avtar   = $sLibObj->getAvatar($userobject, 50);

// Array of annotation ids for view more
$annotaion_ids              = array();

$comment_limit           = $params->get('no_of_commets_to_show');
$limit_on_comment_lenght = $params->get('limit_on_comment_lenght');

$comment_length = 0;
$maxlength      = '';

if ($limit_on_comment_lenght)
{
	$comment_length = $params->get('comment_length');
	if ($comment_length)
		$maxlength = $comment_length;
}
//end comments

$item   = $this->buttonset;
$type = !empty($this->urldata->type) ? $this->urldata->type : '';

$for_id_type = str_replace('.', '-', $type);

// $likecontainerid  = str_replace('.', '-', $this->urldata->element) . "-" . $for_id_type;
$likecontainerid  = "like-" . str_replace('.', '-', $this->urldata->element) . "-" . $this->urldata->cont_id;
$likecontainerid = trim($likecontainerid);

$likeContainerIdPart2 = '';
$type = empty($this->urldata->type) ? '' : $this->urldata->type;

if ($type)
{
	$likeContainerIdPart2  = "-" . $this->urldata->type;
}

$likecontenttitle = html_entity_decode(urldecode($this->urldata->title));
$style            = "
			.native-jlike #" . $likecontainerid . " #jlike-container a.melike,
			.native-jlike #" . $likecontainerid . " #jlike-container a.medislike,
			.native-jlike #" . $likecontainerid . " #jlike-container a.meunlike,
			.native-jlike #" . $likecontainerid . " #jlike-container a.meundislike{
				background: url('" . Uri::base() . "components/com_jlike/assets/images/buttonset/" . $item->title . "') repeat-x scroll 0 0 #FFFFFF;
			}
		";
$document->addStyleDeclaration($style);


$oluser = Factory::getUser();

$show_annotation_snippet = $display_dislike = $display_pwltcb = 0;

$dislike_onload_style = "style='display:none'";
if ($params->get('allow_user_lables') || $params->get('allow_annotation'))
{
	$show_annotation_snippet = 1;
}

if ($params->get('allow_dislike'))
{
	$display_dislike      = 1;
	$dislike_onload_style = "style='display:inline-block'";
}
/*
if (1 == $oluser->guest)
{
	if ($params->get('show_users') && $params->get('which_users_to_show') == '0')
	{
		$display_pwltcb = 1;
	}
}
else
{
	if ($params->get('show_users'))
	{
		$display_pwltcb = 1;
	}
}*/

if ($data['likeaction'] == 'like')
{
	if ($data['likecount'])
		$like_text = $data['likecount'];
	else
		$like_text = $data['liketext'];

	$like_tooltip = $data['liketext'];
}
else
{
	$like_text    = $data['likecount'];
	$like_tooltip = $data['unliketext'];
}
if ($data['dislikeaction'] == 'dislike')
{
	if ($data['dislikecount'])
		$dislike_text = $data['dislikecount'];
	else
		$dislike_text = $data['disliketext'];

	$dislike_tooltip = $data['disliketext'];
}
else
{
	$dislike_text    = $data['dislikecount'];
	$dislike_tooltip = $data['undisliketext'];
}

?>
<div class="techjoomla-bootstrap native-jlike">

	<div class="row-fluid">
		<div class="likes" id="<?php echo $likecontainerid;?>">
			<?php
			// This is for guest user
			$show_user_pic = 0;

			if (1 == $oluser->guest && $this->urldata->show_like_buttons == 1)
			{
				$currUri       = Uri::getInstance(); 
				$return        = $currUri->toString();

				$url  = Uri::root() . 'index.php?option=com_users&view=login';
				$url .= '&return=' . base64_encode($return);

				//$mainframe->redirect($url, Text::_('You must login first') );
				?>
				<span id="jlike-container">
					<span class="like-snippet" >

						<a href="javascript:void(0);" class="<?php echo 'melike'; ?>" onclick='jlike_loginRedirect("<?php echo $url; ?>")';>
							<span class="like-snippet-text" id="likecount"><?php 	echo ($data['likecount']) ? $data['likecount'] : 0; ?>
							</span>
						</a>

					<?php if ($params->get('allow_dislike')){ ?>

						<a href="javascript:void(0);" <?php echo $dislike_onload_style; ?> class="<?php  echo 'medislike'; ?>" onclick='jlike_loginRedirect("<?php echo $url; ?>")';>
							<span class="like-snippet-text" id="dislikecount">
								<?php echo ($data['dislikecount']) ? $data['dislikecount'] : 0; ?>
							</span>
						</a>

					<?php } ?>

					</span>
				</span>
			<?php
			}
			elseif ($this->urldata->show_like_buttons == 1)
			{
				// This is for logged in plugin
				?>
				<span id="jlike-container">
						<span id="" class="like-snippet">
							<a href="javascript:void(0);" title="<?php echo $like_tooltip ?>" class="<?php echo 'me'.$data['likeaction'];?> " style="display:<?php echo ($data['likeaction'])? 'inline-block': 'none' ?>" onclick="jLike.<?php echo ($data['likeaction'])?>(this,'<?php echo $likecontainerid ;?>','<?php echo $show_annotation_snippet;?>','<?php echo $display_dislike;?>','<?php echo $display_pwltcb;?>');">
								<span class="like-snippet-text" id="likecount"><?php echo $like_text ?></span>
							</a>

					<?php if ($params->get('allow_dislike')){ ?>

							<a href="javascript:void(0);" title="<?php echo $dislike_tooltip ?>"  class="<?php echo 'me'.$data['dislikeaction'];?>" <?php echo $dislike_onload_style;?> onclick="jLike.<?php echo ($data['dislikeaction'])?>(this,'<?php echo $likecontainerid ;?>','<?php echo $show_annotation_snippet;?>','<?php echo $display_dislike;?>','<?php echo $display_pwltcb;?>');">
								<span class="like-snippet-text" id="dislikecount"><?php echo $dislike_text ?></span>
							</a>

					<?php } ?>
						</span><!-- like-snippet -->

				</span><!-- jlike-container -->

				<!--- > Code Moved to down--->
				<?php
			} ?>

			<!--- Recommendations & assignment ans set goal code -->
			<?php if ($this->urldata->showrecommendbtn == '1' || $this->urldata->showassignbtn == '1' || $this->urldata->showsetgoalbtn == '1'): ?>
				<div class="jlike-recommend">
					<?php
						$comjlikeHelper = new comjlikeHelper;
						$recommendFile = $comjlikeHelper->getjLikeViewpath('jlike','default_recommend');

						ob_start();
							include($recommendFile);
							$html = ob_get_contents();
						ob_end_clean();

						echo $html;
					?>
				</div>
			<?php endif; ?>
			<!--- Recommendations & assignment code -->

		<?php if ($this->urldata->show_like_buttons == 1) { ?>
				<div style="clear:both"></div>
				<?php
				// Status mgt is enabled and have statuses
				if (!empty($this->statusMgt) && !empty($this->Allstatuses))
				{
				?>
					<?php
						$default = (isset($this->userStatusId)) ? $this->userStatusId : 0;
						$options = array();
						// $options[] = HTMLHelper::_('select.option', "", Text::_('QTC_BILLIN_SELECT_COUNTRY'));

						foreach ($this->Allstatuses as $key=>$value)
						{
							$options[] = HTMLHelper::_('select.option', $value->id, Text::_($value->status_code, true) );
						}

					$fieldName = 'likeStatus_' . $likecontainerid;
					$fparam = '"' . $this->urldata->element . '",' . $this->urldata->cont_id . ',this.id';
					echo $this->dropdown = HTMLHelper::_('select.genericlist', $options, $fieldName,'class="chzn-done" data-chosen="jlike"  aria-invalid="false" size="1" onchange=\'jlUpdateStatus(' . $fparam . ')\' ','value','text', $default, $fieldName);
					?>
					<span id="jLload_<?php echo $fieldName?>" style="display:none;">
						<img class="" src="<?php echo Uri::root() ?>components/com_jlike/assets/images/ajax-loading.gif" height="15" width="15">
					</span>
				<?php
				}
				?>

				<?php if ($params->get('show_users')) { ?>
					<span class="pwltcb" id="pwltcb" style="display:<?php echo ($display_pwltcb)? 'block': 'none' ?>">
						<ul class="pwltcb_ul">
							<?php
							$pwltcb_cnt=0;
							foreach($this->data['pwltcb'] as $ind=>$obj)
							{
								if ($pwltcb_cnt==5)
								{
									break;
								}
								?>
								<li class="pwltcb_li">
									<a title="<?php echo $obj->name ?>" target="_blank"  <?php echo ($obj->link_url)?'href="'.$obj->link_url.'"':''; ?>>
										<img class="pwltcb_img img-circle" src="<?php echo $obj->img_url ?>" alt="" data-jsid="img">
									</a>
								</li>
								<?php
								$pwltcb_cnt++;
							}
							?>
						</ul>
							<?php
							$more_pwltcb=count($this->data['pwltcb'])-$pwltcb_cnt ;
							if ($more_pwltcb	>0 )
								echo "<span class='pwltcb_more'> ".Text::sprintf( 'COM_JLIKE_MORE_LIKE_MSG',  $more_pwltcb )."	</span>";
							 ?>
					</span>
				<?php }?>

				<?php if ($params->get('allow_annotation') || $params->get('allow_user_lables')) { ?>
					<span id="annotation-snippet" class="annotation-snippet">
						<div class="innerdiv">
							<div class="annotationNub">
									<div class="nibtip"></div>
							</div>

							<div>
								<div class="annotation">
									<div class="pam">
										<form class="form-horizontal" id="annotationform" name="annotationform">
											<div class="alert alert-success like-success-msg">
												<strong><?php echo Text::sprintf('LIKE_SUCCESS', $likecontenttitle);?></strong>
											</div>

											<?php
											if ($params->get('allow_annotation'))
											{ ?>
											<div class="control-group">
												<div class="well">
													<div class="media">
														<a href="#" class="pull-left">
																<img class="userimage" data-jsid="img" alt="" src="<?php echo $this->userdetails['img_url'];?>">
														</a>
														<div class="media-body mt-2">
															<textarea placeholder="<?php echo Text::_('ANNOTATE_PLACE_HOLDER')?>" name="annotation" id="annotation" title="Add a comment" class="annotationplace mt-2 ms-0"><?php echo $this->userNote; ?></textarea>
														</div>
														<!-- media-body -->
													</div>
													<!-- media-->
												</div>
												<!-- well-->
											</div>
											<!-- control-group-->
										<div class="control-group">
											<label class="checkbox pull-right">
												<input type="checkbox" id="privacy" name="privacy"  value="1">&nbsp;<?php echo Text::_('PRIVACY_CHECKBOX_LABEL')?>
											</label>
										</div>
										<!-- control-group-->
										<?php
										} ?>

										<?php
										if ($params->get('allow_user_lables'))
											{ ?>
											<div class="labels-space btn-group mb-0">

												<a href="#" class="btn dropdown-toggle mt-1 " data-toggle="dropdown" onclick="return openlabels('add','<?php echo $likecontainerid;?>');">
													<?php echo Text::_('FILE_IN')?>
														<span class="caret">
													</span>
												</a>

												<ul class="user-labels dropdown-menu">
													<?php
													// Use language constant in javascript
													Text::script('COM_JLIKE_DELETE_LIST_CONFIRMATION', true);


													if(!empty($this->userlables))
													{
														foreach($this->userlables as $ind=>$obj)
														{	?>
														<li>
															<div class="row-fluid" id="lableRow_<?php echo $obj->id; ?>">
																<span class="span10">
																<label class="checkbox" class="">
																	<input type="checkbox" class='label-check' value="<?php echo $obj->id;?>" name="label-check[]" onclick="oncheck('<?php echo $likecontainerid;?>')">
																		<?php echo $obj->title;  ?>
																</label>
																</span>


																<span class="span2" id="lable_<?php echo $obj->id; ?>" title="" onClick="jlike_deleteList(<?php echo $obj->id; ?>)">
																	<i class="icon-trash icon-white"></i>
																</span>
															</div>

														</li>

														<?php
														}
													}

													?>
													<li class="divider"></li>
													<li id="jlike-add-label mt-1">
														<div class="input-group">
															<input class="form-control border-0 jlike-tag-append-text jlike-btn-35" id="appendedInputButton" type="text" placeholder="<?php echo Text::_('NEW_TAG_ADD_PLACEHOLDER')?>">
															<button class="btn btn-primary border-0 d-flex justify-content-center align-items-center jlike-tag-append-button jlike-btn-35" type="button" title="<?php echo Text::_('CLICK_TO_CREATE_NEW')?>" onclick="addlables('appendedInputButton','<?php echo Text::_('NO_BLANK_LABLES')?>','<?php echo $likecontainerid;?>');"><i class="fa-solid fa-plus"></i></button>
														</div>
													</li>
													<li id="jlike-apply-label" style="display:none">
														<button class="btn btn-success" type="button" onclick="return openlabels('remove','<?php echo $likecontainerid;?>')"><?php echo Text::_('COM_JLIKE_ADD_TO_LIST_APPLY');?></button>
													</li>

												</ul>
												<!--ul-->
											</div>
											<!-- labels-space -->
											<?php
											} ?>
											<div class="jlike-form-actions row-fluid bg-transparent p-0">
												<div id="jlike-loading-image" class="spinner-border text-primary d-none pull-left jlike-loading-image" style="display:none;">&nbsp;</div>

												<button type="button" class="btn btn-success jlike-btn-35" onclick="savedata('<?php echo $likecontainerid;?>','<?php echo Text::_('COM_JLIKE_SAVE_SUCCESS_MSG');?>')"><?php echo Text::_('COM_JLIKE_SAVE');?>
												</button>
												<button type="button" class="btn btn-secondary jlike-btn-35" onclick="close_comment_snippet('<?php echo $likecontainerid;?>')"><?php echo Text::_('COM_JLIKE_CANCEL');?></button>
											</div>

											<input type="hidden" id="content_id" name="content_id" value="">
										</form>
										<!--form -->
									</div>
									<!-- pam -->
								</div>
								<!-- annotation -->
							</div>
							<!-- div -->
						</div>
						<!-- innerdiv -->
					</span>
					<!-- annotation-snippet -->
				<?php } ?>

			<?php } ?>

			<?php
			// Rating & Reviews
			if ($this->urldata->show_reviews == 1)
			{
				$comjlikeHelper = new comjlikeHelper();
				$commentFilex = $comjlikeHelper->getjLikeViewpath('jlike','default_reviews');
				ob_start();
					include($commentFilex);
					$htmlx = ob_get_contents();
				ob_end_clean();

				echo $htmlx;
			}
			else
			{
				// Comments
				$allow_to_add_comments = $params->get('allow_comments');
				$style = 'margin-left:8%; width:92%;';
				$margin_left = 8;
				$width       = 92;

				if ($allow_to_add_comments)
				{
					$comjlikeHelper = new comjlikeHelper;
					$commentFile = $comjlikeHelper->getjLikeViewpath('jlike','default_comments');

					ob_start();
						include($commentFile);
						$html = ob_get_contents();
					ob_end_clean();

					echo $html;
				}
			}

			if (1 != $oluser->guest)
			{ ?>
				<script type="text/javascript">
					root_url = "<?php echo Uri::root(); ?>";
					jLikeVal['<?php echo $likecontainerid; ?>']=[];
					jLikeVal['<?php echo $likecontainerid; ?>']['likeaction'] = "<?php echo $data['likeaction'];?>";
					jLikeVal['<?php echo $likecontainerid; ?>']['dislikeaction'] = "<?php echo $data['dislikeaction'];?>";
					jLikeVal['<?php echo $likecontainerid; ?>']['cont_id'] = "<?php echo $this->urldata->cont_id ;?>";
					jLikeVal['<?php echo $likecontainerid; ?>']['element'] = "<?php echo $this->urldata->element; ?>";
					var title = "<?php echo addslashes($likecontenttitle);?>";
					jLikeVal['<?php echo $likecontainerid; ?>']['title']	=	techjoomla.jQuery('<div/>').html(title).text();
					jLikeVal['<?php echo $likecontainerid; ?>']['url'] = "<?php echo $this->urldata->url; ?>";
					jLikeVal['<?php echo $likecontainerid; ?>']['likecount'] = "<?php echo $data['likecount'];?>";
					jLikeVal['<?php echo $likecontainerid; ?>']['dislikecount'] = "<?php echo $data['dislikecount'];?>";
					jLikeVal['<?php echo $likecontainerid; ?>']['liketext'] = "<?php echo Text::_('LIKE') ;?>";
					jLikeVal['<?php echo $likecontainerid; ?>']['disliketext'] = "<?php echo Text::_('DISLIKE') ;?>";
					jLikeVal['<?php echo $likecontainerid; ?>']['unliketext'] = "<?php echo Text::_('UNLIKE') ;?>";
					jLikeVal['<?php echo $likecontainerid; ?>']['undisliketext'] = "<?php echo Text::_('UNDISLIKE') ;?>";
					jLikeVal['<?php echo $likecontainerid; ?>']['no_of_pwltc_users'] = "<?php echo $params->get('no_of_users_to_show','5','INT'); ;?>";
					jLikeVal['<?php echo $likecontainerid; ?>']['like_icon_class'] = "<?php echo $params->get('like_icon_class') ;?>";
					jLikeVal['<?php echo $likecontainerid; ?>']['dislike_icon_class'] = "<?php echo $params->get('dislike_icon_class') ;?>";
					jLikeVal['<?php echo $likecontainerid; ?>']['statusMgt'] = "<?php //echo $params->get('statusMgt', 0) ;?>";
					jLikeVal['<?php echo $likecontainerid; ?>']['plg_name'] = "<?php echo !empty($this->urldata->plg_name) ? $this->urldata->plg_name : '';?>";
					jLikeVal['<?php echo $likecontainerid; ?>']['plg_type'] = "<?php echo !empty($this->urldata->plg_type) ? $this->urldata->plg_type : '';?>";
					jLikeVal['<?php echo $likecontainerid; ?>']['type'] = "<?php echo !empty($this->urldata->type) ? $this->urldata->type : '';?>";
					jLikeVal['<?php echo $likecontainerid; ?>']['userslist'] = <?php echo json_encode($this->userslist); ?>;
			</script>
	<?php	} ?>
		 </div>
		 <!-- likes -->
	</div>
</div>
<!-- techjoomala-bootstrap -->