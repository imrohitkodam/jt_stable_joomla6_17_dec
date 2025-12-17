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
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;

HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.multiselect'); // only for list tables


$listOrder  = $this->escape($this->state->get('list.ordering'));
$listDirn   = $this->escape($this->state->get('list.direction'));
$sortFields = $this->getSortFields();

?>
<form action="<?php echo Route::_('index.php?option=com_jlike&view=ratings'); ?>" method="post" name="subForm" id="subForm">
	<div class="row">
		<div class="col-xs-12">
			<hr>
		</div>
		<?php if (count($this->items)) { ?>
			<div class="col-xs-12 margint10">
				<div class="row">
					<div class="col-xs-12">
						<strong><?php echo Text::_('COM_JLIKE_RATINGS_CUSTOMER_REVIEWS_TITLE'); ?></strong>
					</div>
				</div>
			</div>
			<div class="btn-group pull-right">
				<label for="sortTable" class="element-invisible"><?php // echo Text::_('JGLOBAL_SORT_BY'); ?></label>
				<select name="sortTable" id="sortTable" class="input-medium" onchange="jlikeRatingUI.getRatings();">
					<?php echo HTMLHelper::_('select.options', $sortFields, 'value', 'text', $listOrder); ?>
				</select>
			</div>
			<div>
				<div id="ratingList" class="ratinglist"></div>
				<div class="load-more center">
					<a id='load-more-rating-button' class="btn btn-primary btn-md" onclick="jlikeRatingUI.loadMoreRating()">Load More</a>
				</div>
			</div>
			<input type="hidden" id="content_id" name="content_id" value="<?php echo $this->contentId; ?>"/>
			<input type="hidden" id="limit" name="limit" value="2"/>
			<input type="hidden" id="start" name="start" value=""/>
			<input type="hidden" name="task" value="ratings.getRatings"/>
			<input type="hidden" id = "filter_order" name="filter_order" value="<?php echo $listOrder; ?>"/>
			<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>
		<?php } else { ?>
			<div class="col-xs-12 margint10">
				<div class="row">
					<div class ="col-xs-12">
						<strong><?php echo Text::_('COM_JLIKE_RATINGS_NO_CUSTOMER_REVIEWS'); ?></strong>
					</div>
				</div>
			</div>
		<?php } ?>

	</div>
</form>
<script id="entry-template" type="text/x-handlebars-template">
<div>
	<div class="col-xs-12">
		<img class="img-circle jlike-img-border" src={{info.avtar}} alt="Smiley face" width="36px" height="auto">
			{{info.userName}}
	</div>
	<div class="col-xs-12">
		<div class="jlike" >
			<ul class="rate-areas">
				{{#times info}}
					<input type="radio" id="{{this.key}}-star-{{this.id}}" disabled name="rating{{this.id}}" value="{{this.key}}" {{{this.checked}}} /><label for="{{this.key}}-star-{{this.id}}" title="Amazing">{{this.key}}</label>

				{{/times}}
			</ul>
		</div>
	</div>
	<div class="col-xs-12">
		{{info.created_day}} {{info.created_date_month}}
	</div>
	<div class="col-xs-12">
		{{info.review}}
	</div>
	<div class="col-xs-12">
		<hr>
	</div>
</div>
</script>
