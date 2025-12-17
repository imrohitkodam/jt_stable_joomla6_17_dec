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

<form action="" enctype="multipart/form-data" method="post" name="jlikeRating" id="jlikeRating" class="">
	<input type="hidden" name="jform[content_id]" id="jform_content_id"	value="<?php echo $this->content_id; ?>" />
	<input type="hidden" name="task" value="rating.getLoggedInUserRating"/>
	<div class="col-xs-12">
		<div id="ratingDetailview"></div>
	</div>
	<?php echo HTMLHelper::_('form.token');
	HTMLHelper::_('jquery.token');
	  ?>
</form>

<script id="entry-template" type="text/x-handlebars-template">
<div>
	<div class="col-xs-12">
		<hr>
	</div>
	<div class="col-xs-12">
		<img class="img-circle jlike-img-border" src={{info.avtar}} alt="Smiley face" width="36px" height="auto"/>
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
</div>
</script>

