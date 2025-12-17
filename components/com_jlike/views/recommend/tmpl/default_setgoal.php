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
use Joomla\CMS\Uri\Uri;
use Joomla\Filesystem\File;
use Joomla\CMS\Language\Text;

HTMLHelper::addIncludePath(JPATH_COMPONENT . '/helpers/html');
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('bootstrap.tooltip');

$document =	Factory::getDocument();
$document->addStylesheet(Uri::root(true) . '/media/com_jlike/font-awesome/css/font-awesome.css');
$input  = Factory::getApplication()->getInput();
$type   = $input->get('type', 'reco');
$assignto   = $input->get('assignto', '');
$user = Factory::getUser();
$element = $input->get('element','');
$element_id   = $input->get('id', '', 'INT');

// Include helper file to get todoid and contentid
$path = JPATH_SITE . '/components/com_jlike/helper.php';
$ComjlikeHelper = "";

	if (File::exists($path))
	{
		if (!class_exists('ComjlikeHelper'))
		{
			JLoader::register('ComjlikeHelper', $path);
			JLoader::load('ComjlikeHelper');
		}

			$ComjlikeHelper = new ComjlikeHelper;
	}

	// Load jlike model to call api function for assigndetails and other
		$path = JPATH_SITE . '/components/com_jlike/models/recommendations.php';
		$this->JlikeModelRecommendations = "";

	if (File::exists($path))
	{
		if (!class_exists('JlikeModelRecommendations'))
		{
			JLoader::register('JlikeModelRecommendations', $path);
			JLoader::load('JlikeModelRecommendations');
		}

		$this->JlikeModelRecommendations = new JlikeModelRecommendations;
	}

$content_id      = $ComjlikeHelper->getContentId($element_id, $element);

if (!empty($content_id) && !empty($user->id))
{
	$this->JlikeModelRecommendations->setState("content_id", $content_id);
	$this->JlikeModelRecommendations->setState("assigned_by", $user->id);
	$this->JlikeModelRecommendations->setState("assigned_to", $user->id);
	$todos = $this->JlikeModelRecommendations->getItems();
}

$date   = Factory::getDate()->Format(Text::_('COM_JLIKE_DATE_FORMAT'));

?>
<div class="techjoomla-bootstrap">
	<div class="jlike-wrapper">
		<?php if ($type == 'assign' && $assignto == 'self'): ?>
		<div class="modal-header">
			<h3>
				<?php echo(empty($todos)?Text::sprintf("COM_JLIKE_FORM_TITLE_SET_GOAL", $this->element['title']):Text::sprintf("COM_JLIKE_FORM_TITLE_UPDATE_GOAL", $this->element['title'])); ?>
			</h3>
		</div>
		<form  method="post" name="adminForm" id="adminForm">
			<div class="modal-body">
			<div class="container-fluid">
			<div class="row">

						<div style="margin-top:18px;" class=" jlike-uber-padding pull-right" >
						
						<div class="row">
						<div class="col-md-6">
							<?php
							$start_date = '';

							if (!empty($todos[0]->start_date))
							{
								$start_date = $this->TechjoomlaCommon->getDateInLocal($todos[0]->start_date);
							}
							else
							{
								$start_date = HTMLHelper::date('now', 'D d M Y H:i');
							}
							?>
								<div class="form-group">
    								<label><?php echo Text::_('COM_JLIKE_START_DATE') ?></label>
    								<?php
										echo HTMLHelper::_('calendar',$start_date,'start_date','start_date','%Y-%m-%d', 'placeholder="' . Text::_("COM_JLIKE_START_DATE") . '" class="required"');
								?>
								</div>
							</div>

							<div class="col-md-6">
							<?php
							$due_date = '';

							if (!empty($todos[0]->due_date))
							{
								$due_date = $todos[0]->due_date;
							}
							?>
								<div class="form-group">
    								<label><?php echo Text::_('COM_JLIKE_DUE_DATE') ?></label>
    								<?php

										echo HTMLHelper::_('calendar',$due_date,'due_date','due_date','%Y-%m-%d', 'placeholder="' . Text::_("COM_JLIKE_DUE_DATE") . '" class="required"');

							 	?>
							 	</div>
							 </div>
							 </div> 
						</div>
						</div>
				</div>
			</div>
			</div>

			 <div class="modal-footer" style="margin-top:25px">
			 <div class="container-fluid ">
				<button
					onclick="closePopUp()"
					class="btn btn-small set-Goal-cancel">
					<i class="fa fa-times"></i>
					<?php echo Text::_('COM_JLIKE_CANCEL_BUTTON'); ?>
				</button>
				<button
					onclick="return recommendation('assignRecommendUsers')"
					name="recommend_friends_send"
					class="btn btn-small btn-primary"
					id="enrol">
						<i class="fa fa-check-square"></i>
						<?php echo (empty($todos)?Text::_('COM_JLIKE_SETGOAL_LABEL'):Text::_('COM_JLIKE_UPDATEGOAL_LABEL')); ?>
				</button>
				<?php endif; ?>
				</div>
			</div>
			<input type="hidden" id="recommend_task" name="task" value="assignRecommendUsers" />
			<input type="hidden"  name="option" value="com_jlike" />
			<input type="hidden" name="element" value="<?php echo $element; ?>" />
			<input type="hidden" name="element_id" value="<?php echo $input->get('id','','INT'); ?>" />
			<input type="hidden" name="plg_name" value="<?php echo $input->get('plg_name',''); ?>" />
			<input type="hidden" name="plg_type" value="<?php echo $input->get('plg_type',''); ?>" />
			<input type="hidden" id="task_type" name="type" value="<?php echo $type; ?>" />
			<input type="hidden" id="task_sub_type" name="sub_type" value="<?php echo $assignto; ?>" />
			<input type="hidden" id="todo_id" name="todo_id" value="<?php if(!empty($todos[0]->id)) echo $todos[0]->id; ?>" />

			<?php echo HTMLHelper::_('form.token'); ?>
		</form>
		</div>
	</div>
</div>

<script>
	jQuery(window).load(function() {
		jQuery(".input-append").addClass("jlike-calender-div");
});
</script>
<?php

$document = Factory::getDocument();
$document->addScript(Uri::root(true).'/components/com_jlike/assets/scripts/recommendation.js');
