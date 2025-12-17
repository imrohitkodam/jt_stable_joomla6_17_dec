<?php
/**
* @version		1.0.0 jLike $
* @package		jomgive
* @copyright	Copyright © 2012 - All rights reserved.
* @license		GNU/GPL
* @author		TechJoomla
* @author mail	extensions@techjoomla.com
* @website		http://techjoomla.com
*/
// no direct access
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

$params  = ComponentHelper::getParams('com_jlike');

?>

<div class="techjoomla-bootstrap">

	<?php
	if(!empty($this->guestMsg))
	{
		?>
		<div class="well" >
			<div class="alert alert-error">
				<span><?php echo Text::_('COM_JLIKE_LOGOUT_MSG'); ?></span>
			</div>
		</div>
	</div>
	<?php
		return false;
	}
	?>

	<!--page header-->
	<h2 class="componentheading">
		<?php echo Text::_('COM_JLIKE_ALLANNOTATIONS');?>
	</h2>
	<hr/>

		<form action="" method="post" name="adminForm" id="adminForm">
			<div class="input-append jlike-search-like btn-toolbar" id="filter-bar">
				<input type="text"
					placeholder="<?php echo Text::_('COM_JLIKE_SEARCH_IN_TITLE'); ?>"
					name="filter_search_likecontent"
					id="filter_search_likecontent"
					value="<?php if(!empty($this->filter_search_likecontent)) echo htmlspecialchars($this->filter_search_likecontent, ENT_COMPAT, 'UTF-8');?>"
					class="input-medium"
					onchange="document.adminForm.submit();" />

				<button type="button" onclick="this.form.submit();" class="btn btn-secondary tip hasTooltip" data-original-title="<?php echo Text::_('COM_JLIKE_ALLANNOTATIONS_SEARCH_BTN_TOOLTIP'); ?>">
					<i class="fa fa-search"></i>
				</button>

				<button onclick="document.getElementById('filter_search_likecontent').value='';this.form.submit();" type="button" class="btn btn-secondary tip hasTooltip" data-original-title="<?php echo Text::_('COM_JLIKE_ALLANNOTATIONS_CLEAR_BTN_TOOLTIP'); ?>">
					<i class="fa fa-times"></i>
				</button>
			</div>

			<div class="jlike-search-like float-end">
				<?php
				echo HTMLHelper::_('select.genericlist', $this->filter_likecontent_classification, "filter_likecontent_classification", ' size="1"
				onchange="this.form.submit();" class="" title="' . Text::_("COM_JLIKE_ALLANNOTATIONS_SELECT_ELEMENT_TOOLTIP") . '" name="filter_likecontent_classification"',"value", "text", $this->lists['filter_likecontent_classification']);
				?>&nbsp;
				<?php
				 echo HTMLHelper::_('select.genericlist', $this->filter_likecontent_list, "filter_likecontent_list", 'class="" title="' . Text::_("COM_JLIKE_ALLANNOTATIONS_SELECT_LIST_TOOLTIP") . '" size="1"
				onchange="this.form.submit();" name="filter_likecontent_list"', "value", "text",$this->lists['filter_likecontent_list']);
				?>&nbsp;
				<?php
				 echo HTMLHelper::_('select.genericlist', $this->filter_likecontent_user, "filter_likecontent_user", 'class="" title="' . Text::_("COM_JLIKE_ALLANNOTATIONS_SELECT_USER_TOOLTIP") . '" size="1"
				onchange="this.form.submit();" name="filter_likecontent_user"',"value", "text",$this->lists['filter_likecontent_user']);
				?>
			</div>
			<div class="clearfix">&nbsp;</div>
			<div class="float-end">
				<span class="hidden-phone">
					<?php echo $this->pagination->getLimitBox(); ?>
				</span>
			</div>
			<div class="clearfix">&nbsp;</div>
			<div class="clearfix">&nbsp;</div>
			<div id="no-more-tables">
				<table class="table table-striped table-bordered table-hover " width="100%">
					<thead>
					<tr>
						<th><?php echo HTMLHelper::_( 'grid.sort', 'COM_JLIKE_CONTENT_USERNAME','users.name', $this->lists['filter_order_Dir'], $this->lists['filter_order']); ?> </th>
						<th><?php echo HTMLHelper::_( 'grid.sort', 'COM_JLIKE_CONTENT_TITLE','title', $this->lists['filter_order_Dir'], $this->lists['filter_order']); ?></th>
						<th><?php echo HTMLHelper::_( 'grid.sort', 'COM_JLIKE_CONTENT_ANNOTATIONS','likeannotations.annotation', $this->lists['filter_order_Dir'], $this->lists['filter_order']); ?></th>
						<th><?php echo HTMLHelper::_( 'grid.sort', 'COM_JLIKE_CONTENT_CLASSIFICATION','element', $this->lists['filter_order_Dir'], $this->lists['filter_order']); ?></th>
						<th><?php echo HTMLHelper::_( 'grid.sort', 'COM_JLIKE_CONTENT_LIST','list_name', $this->lists['filter_order_Dir'], $this->lists['filter_order']); ?></th>
						<th><?php echo HTMLHelper::_( 'grid.sort', 'COM_JLIKE_CONTENT_LIKE','likecontent.like_cnt', $this->lists['filter_order_Dir'], $this->lists['filter_order']); ?> </th>

						<?php $allow_dislike = $params->get('allow_dislike'); ?>
						<?php if ($allow_dislike) { ?>
						<th><?php echo HTMLHelper::_( 'grid.sort', 'COM_JLIKE_CONTENT_DISLIKE','likecontent.dislike_cnt', $this->lists['filter_order_Dir'], $this->lists['filter_order']); ?> </th>
						<?php } ?>

					</tr>
					</thead>
					<tbody>
					<?php
					$i=1;

					foreach($this->data as $likedata)
					{
						$contentTypeKey = array_search($likedata->element, array_column($this->filter_likecontent_classification, 'value'));

						?>
						<tr>
							<td data-title="<?php echo Text::_("COM_JLIKE_CONTENT_USERNAME"); ?>"><?php echo $this->escape($likedata->username);?></td>
							<td data-title="<?php echo Text::_("COM_JLIKE_CONTENT_TITLE"); ?>">
								<div>

										<strong><a href="<?php echo $likedata->url;?>"><?php echo $this->escape($likedata->title);?></a></strong>

								</div>
								<div class="com_jlike_clear_both"></div>
							</td>

							<td data-title="<?php echo Text::_("COM_JLIKE_CONTENT_ANNOTATIONS"); ?>">
									<strong><?php echo $this->escape($likedata->annotation);?></strong>
							</td>

							<td data-title="<?php echo Text::_("COM_JLIKE_CONTENT_CLASSIFICATION"); ?>"><?php echo $this->escape($this->filter_likecontent_classification[$contentTypeKey]->text);?></td>

							<td data-title="<?php echo Text::_("COM_JLIKE_CONTENT_LIST"); ?>"><?php echo !empty($likedata->list_name) ? $this->escape($likedata->list_name) : '-';?></td>
							<td data-title="<?php echo Text::_("COM_JLIKE_CONTENT_LIKE"); ?>"><?php echo $this->escape($likedata->like_cnt);?></td>

							<?php if ($allow_dislike) { ?>
							<td data-title="<?php echo Text::_("COM_JLIKE_CONTENT_DISLIKE"); ?>"><?php echo $this->escape($likedata->dislike_cnt);?></td>
							<?php } ?>

						</tr>
						<?php
						$i++;
					}
					?>
				</tbody>
				</table>
			</div>
		<div class="pager com_jlike_align_center">
			<?php echo $this->pagination->getListFooter(); ?>
		</div>

		<input type="hidden" name="option" value="com_jlike" />
		<input type="hidden" name="view" value="annotations" />
		<input type="hidden" name="layout" value="all" />


		<input type="hidden" name="filter_order" value="<?php echo $this->lists['filter_order']; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['filter_order_Dir']; ?>" />
	</form>
</div>
