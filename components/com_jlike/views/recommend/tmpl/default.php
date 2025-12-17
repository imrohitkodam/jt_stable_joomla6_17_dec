<?php
/**
 * @version    SVN: <svn_id>
 * @package    JLike
 * @copyright  Copyright (C) 2005 - 2015. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * other free or open source software licenses.
 */

// No direct access
defined('_JEXEC') or die;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Uri\Uri;

HTMLHelper::addIncludePath(JPATH_COMPONENT . '/helpers/html');
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('bootstrap.tooltip');

if (JVERSION >= '3.0')
{
	HTMLHelper::_('bootstrap.tooltip');
	HTMLHelper::_('behavior.multiselect'); // only for list tables

}

$input  = Factory::getApplication()->getInput();
$type   = $input->get('type', 'reco');

// Pass below value as hidden
$listOrder = $this->state->get('list.ordering');
$listDirn = $this->state->get('list.direction');

$date   = Factory::getDate()->Format(Text::_('COM_JLIKE_DATE_FORMAT'));
?>
<div class="techjoomla-bootstrap recommend-popup-div">
	<div class="jlike-wrapper">
		<form  method="post" name="adminForm" id="adminForm">
			<div class="modal-header">
				<h3>
					<?php echo ($type == 'reco') ? Text::sprintf("COM_JLIKE_FORM_TITLE_RECOMMENDATIONS", $this->element['title']) : Text::sprintf("COM_JLIKE_FORM_TITLE_ASSIGN_CONTENT", $this->element['title']); ?>
				</h3>
			</div>
			<div class="modal-body">
				<?php
				$app = Factory::getApplication();
				$messages = $app->getMessageQueue();
				if (!empty($messages))
				{
					$messageQueue = array('msgList');

					foreach ($messages as $msg){
						$messageQueue['msgList'][$msg['type']] = isset($messageQueue['msgList'][$msg['type']]) ? $messageQueue['msgList'][$msg['type']] : array();
						$messageQueue['msgList'][$msg['type']][] = $msg['message'];
					}
					echo LayoutHelper::render('joomla.system.message', $messageQueue);
				}
				?>
				<div class="container">
					<div class="row-fluid row">
						<div id="filter-bar" class="btn-toolbar assignUser">
							<div class="filter-search btn-group pull-left">
								<input type="text"
									name="filter_search"
									id="filter_search"
									placeholder="<?php echo Text::_('COM_JLIKE_SEARCH_FILTER'); ?>"
									value="<?php echo $this->escape($this->state->get('filter.search')); ?>"
									data-placement="bottom"
									title="<?php echo Text::_('COM_JLIKE_SEARCH_FILTER'); ?>" />
							</div>



							<div class="btn-group pull-left">
								<button
									class="btn hasTooltip"
									type="submit"
									title="<?php echo Text::_('JSEARCH_FILTER_SUBMIT'); ?>">
										<i class="fa fa-search"></i>
								</button>
							</div>

							<div class="btn-group pull-left">
								<button
									class="btn hasTooltip"
									type="button"
									title="<?php echo Text::_('JSEARCH_FILTER_CLEAR'); ?>"
									onclick="document.getElementById('filter_search').value='';this.form.submit();">
										<i class="fa fa-times"></i>
								</button>
							</div>

							<?php if (JVERSION >= '3.0'): ?>
								<div class="btn-group pull-right hidden-phone">
									<label
										for="limit"
										class="element-invisible">
											<?php echo Text::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC');?>
									</label>
									<?php echo $this->pagination->getLimitBox(); ?>
								</div>

							<?php endif; ?>

						</div>
					</div>
					<div class="row-fluid row">
						<div class="span6 col-sm-6 jlike-uber-padding say-something-for-recomendation">
							<textarea name="sender_msg" rows="3" class="jlike-commentbox-height" placeholder="<?php echo Text::_("COM_JLIKE_SAY_SOMETHING");?>"></textarea>
						</div>

						<?php if ($type == 'assign'): ?>
							<div class="span6 col-sm-6 custom-calendar-bx pull-right" >
								<?php
								$calendar_start = HTMLHelper::_('calendar', '','start_date','start_date',Text::_('COM_JLIKE_DATE_FORMAT_PER'), 'placeholder="' . Text::_("COM_JLIKE_START_DATE") . '" class="required"');

								$calendar_due = HTMLHelper::_('calendar', '','due_date','due_date',Text::_('COM_JLIKE_DATE_FORMAT_PER'), 'placeholder="' . Text::_("COM_JLIKE_DUE_DATE") . '" class="required"');
								echo $calendar_start = str_replace("icon-calendar","fa fa-calendar",$calendar_start);
								echo $calendar_due = str_replace("icon-calendar","fa fa-calendar",$calendar_due);
								 ?>
							</div>
						<?php endif; ?>
					</div>
					<div class="row-fluid row">
						<?php

						if (empty($this->peopleToRecommend))
						{ ?>
								<div class="alert alert-warning">
									<?php
										echo Text::_('COM_JLIKE_NO_FRIENDS_FOUND');
									?>
								</div>
							<?php
						}
						else
						{ ?>
						<div class="usersContainer">
							<ul id="jlike-users-list" class="jlike-users-list">
								<?php foreach ($this->peopleToRecommend as $i => $item): ?>
									<li id="rocommenToUser<?php echo $item->friendid; ?>"
										class="allUserAvaiable span3 col-sm-3 jlike-uber-padding" >

										<div class="thumbnail clearfix jlike-thumbnail-margin">

											<img src="<?php echo $item->avatar;?>"
												alt="<?php echo $item->name;?>"
												class="pull-left user_avatar span5 clearfix" >

											<input type="checkbox"
												id="recommend_friends-<?php echo $item->friendid; ?>"
												name="recommend_friends[]"
												value="<?php echo $item->friendid?>"
												onclick="<?php if(!empty($onclick)) echo $onclick;?>"
												class="thCheckbox contacts_check " />

											<div class="recousername">
												<strong>
													<em><?php echo $item->name;?></em>
												</strong>
											</div>

										</div>
									</li>
								<?php endforeach; ?>
								<li id="jlike_pagination" class="span12">
										<?php if (JVERSION < 3.0) : ?>
											<div class="clearfix">&nbsp;</div>
										<?php endif;?>
										<div class="pager">
									<?php echo $this->pagination->getListFooter(); ?>
									</div>
								</li>

							</ul>

						</div>
						<?php } ?>
					</div>
				</div>
			</div>
			<?php
			if (!empty($this->peopleToRecommend))
			{ ?>
			<div class="modal-footer">
				<button
					onclick="closePopUp()"
					class="btn btn-small">
					<span class="icon-cancel"></span>
					<?php echo Text::_('COM_JLIKE_CANCEL_BUTTON'); ?>
				</button>

				<button
					onclick="return recommendation('assignRecommendUsers')"
					name="recommend_friends_send"
					class="btn btn-small btn-success"
					id="enrol">
						<span class="icon-apply icon-white"></span>
						<?php echo ($type == 'reco') ? Text::_('COM_JLIKE_RECOMMEND_USERS') : Text::_('COM_JLIKE_ASSIGN_LABEL'); ?>
				</button>
			</div>
			<input type="hidden" id="recommend_task" name="task" value="assignRecommendUsers" />
			<input type="hidden"  name="option" value="com_jlike" />
			<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
			<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
			<input type="hidden" name="element" value="<?php echo $input->get('element',''); ?>" />
			<input type="hidden" name="element_id" value="<?php echo $input->get('id','','INT'); ?>" />
			<input type="hidden" name="plg_name" value="<?php echo $input->get('plg_name',''); ?>" />
			<input type="hidden" name="plg_type" value="<?php echo $input->get('plg_type',''); ?>" />
			<input type="hidden" id="task_type" name="type" value="<?php echo $type; ?>" />
			<?php echo HTMLHelper::_('form.token'); ?>
			<?php } ?>
		</form>
	</div>
</div>

<?php

$document = Factory::getDocument();
$document->addScript(Uri::root(true).'/components/com_jlike/assets/scripts/recommendation.js');
