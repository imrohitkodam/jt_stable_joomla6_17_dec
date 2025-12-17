<?php
/**
 * @package     Jlike
 * @subpackage  com_jlike
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2018 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.multiselect'); // only for list tables

HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('behavior.formvalidator');

$comjlikeHelper = new ComjlikeHelper;
$comjlikeHelper->getLanguageConstant();
?>

<script>
	var ratings = document.getElementsByClassName('rating');
</script>

<form action="" enctype="multipart/form-data" method="post" name="adminForm" id="adminForm" class="form-validate">
	<?php if (!$this->isRatingSubmitted) { ?>
		<div class="row ratingForm">
			<div class="col-md-12">
				<div class="row">
					<div class="col-md-12">
						<h4><?php echo $this->ratingType->title?></h4>
					</div>
					<div class="col-md-12 mb-20">
						<img class="img-avatar img-circle jlike-img-border mr-10" src="<?php echo $this->user->avtar; ?>" alt="<?php echo Text::_('COM_JLIKE_RATING_ALTERNATE_TEXT_IMAGE'); ?>">
						<span><?php echo $this->user->name?></span>
					</div>
					<?php if ($this->ratingType->show_rating) {?>
					<div class="col-md-12">
						<?php echo Text::_('COM_JLIKE_RATING_OVERALL_RATING'); ?>
					</div>
					<div class="col-md-12">
						<div class="jlike" >
							<ul class="rate-area pd-0">
								<?php for($i=$this->ratingType->rating_scale; $i>0; $i--) {
								?>
									<input type="radio" id="<?php echo $i; ?>-star" name="rating" value="<?php echo $i; ?>"/><label for="<?php echo $i; ?>-star" title=""><?php echo $i; ?><?php Text::_('COM_JLIKE_RATING_STAR_LABEL'); ?></label>
								<?php } ?>
							</ul>
						</div>
					</div>
					<?php } ?>
				</div>
				<div class="row">
					<div class="col-md-12 control-label">
						<?php
						if ($this->form)
						{
							// Iterate through the normal form fieldsets and display each one
							$fieldSets = $this->form->getFieldsets();
							foreach ($fieldSets as $fieldName => $fieldset){
								foreach($this->form->getFieldset($fieldset->name) as $field){
									echo $field->label;
									echo $field->input;
								}
							}
						}
					?>
					</div>
				</div>
				<input type="hidden" name="jform[id]" id="jform_id "value="<?php echo $this->item->id; ?>" />
				<input type="hidden" name="jform[ucmType]" id="jform_ucmType" value="<?php echo $this->ucmType; ?>" />
				<input type="hidden" name="jform[submitted_by]" id="jform_submitted_by" value="<?php echo $this->user->id; ?>" />
				<input type="hidden" name="jform[rating_scale]" id="jform_rating_scale" value="<?php echo $this->ratingType->rating_scale; ?>" />
				<input type="hidden" name="jform[content_id]" id="jform_content_id"
				value="<?php echo $this->content_id; ?>" />
				<input type="hidden" name="jform[rating_type_id]" id="jform_rating_type_id"	value="<?php echo $this->rating_type_id; ?>" />
				<input type="hidden" name="task" value="rating.save"/>
				<input type="hidden" name="form_status" id="form_status" value=""/>

				<div class="row">
					<br>
					<div class="col-md-12">
						<button type='button' id="saveRating" class='btn btn-success btn-small reviewButton validate' onclick='jlikeRatingUI.saveRatingForm(this.form.id);'><?php echo Text::_('COM_JLIKE_RATING_SUBMIT') ?></button>
					</div>
				</div>
				<?php echo HTMLHelper::_('bootstrap.endTab'); ?>
				<?php echo HTMLHelper::_('form.token');
				HTMLHelper::_('jquery.token');  ?>
			</div>
		</div>
	<?php } else {?>
		<div>
			<span class="alert alert-success col-md-12"><?php echo Text::_('COM_JLIKE_RATING_FORM_SUBMITTED'); ?></span>
		</div>
	<?php } ?>
	<div>
		<div class="message"></div>
	</div>
</form>
<?php echo $this->loadTemplate('detail'); ?>
	<div class="col-xs-12">
		<div id="ratingDetailview"></div>
	</div>



