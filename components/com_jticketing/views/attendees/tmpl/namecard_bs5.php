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
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Language\Text;

HTMLHelper::_('bootstrap.tooltip');
?>
<div id="jtwrap" class="tjBs5 container view-namecard">
	<form action="<?php echo Route::_('index.php?option=com_jticketing&view=attendees&layout=namecard' . $this->component); ?>" method="post" name="adminForm" id="adminForm">
		<div id="j-main-container" class="row">
			<div class="no-print col-xs-12 af-mt-10">
				<div class="row">
					<div class="col-md-12">
						<?php
							echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this, 'options'=> array('filterButton' => $this->filterHide)));
						?>
					</div>
				</div>
			</div>
			<?php
			// Do not rendar the unnessessary fields,rows,menus from a pop up view.
			if (empty($this->items)) : ?>
				<div class="alert alert-info mt-3">
					<?php echo Text::_('COM_JTICKETING_NO_ATTENDEES_FOUND'); ?>
				</div>
				<?php
				return; ?>
			<?php
			endif;  ?>
			<div class="col-xs-12">
				<input type="button" class="btn btn-success no-print pull-right" onclick="javascript:window.print()" value="<?php echo Text::_('COM_JTICKETING_ATTENDEE_VIEW_PRINT');?>">
			</div>
			<div class="col-xs-12 no-print">
				<hr>
			</div>
			<div class="clearfix"></div>
				<div class="col-xs-12 af-mt-10">
						<div class="row equal-height-col">
						<?php
						foreach($this->items as $item)
						{ ?>
							<div class="col-xs-6">
								<div class="thumbnail equal-items">
									<div class="stricker-name">
										<?php
										if (!empty($item->fname))
										{
											echo $this->escape(ucfirst($item->fname) . ' ' . ucfirst($item->lname));
										}
										elseif (!empty($item->firstname))
										{
											echo $this->escape(ucfirst($item->firstname) . ' ' . ucfirst($item->lastname));
										}
										else
										{
											echo $this->escape(ucfirst($item->name));
										} ?>
									</div>
									<div class="stricker-extra-info">
									<?php
									if(is_array($this->nameCardField) && count($this->nameCardField))
									{
										foreach ($this->nameCardField as $nameCardField)
										{
											if (!empty($item->$nameCardField))
											{
												echo $this->escape($item->$nameCardField) . '<br>';
											}
											else
											{
												echo '-';
											}
										}
									} ?>
									</div>
								</div>
							</div>
					<?php }	?>
					</div>
					</div>
					<div class="row">
						<div class="col-xs-12">
							<div class="pagination com_jticketing_align_center">
								<div class="pager">
									<?php echo $this->pagination->getPagesLinks(); ?>
								</div>
							</div>
						</div>
					</div>
			</div><!--jticketing-tbl-main-container ENDS-->
			<input type="hidden" name="task" id="task" value="" />
			<input type="hidden" name="boxchecked" value="0" />
			<input type="hidden" name="controller" id="controller" value="attendees" />
			<?php echo HTMLHelper::_('form.token'); ?>
		</div>
	</form>
</div>

<script>
	/** global: jticketing_baseurl */
	jQuery(document).ready(function() {
		if (jQuery('.view-namecard .js-stools .btn-toolbar:first').length)
		{
			jQuery('.view-namecard .js-stools .btn-toolbar:first') . addClass('float-end');
			jQuery('.view-namecard .js-stools .btn-toolbar:first') . parent() . addClass('row');
		}
	});
</script>
