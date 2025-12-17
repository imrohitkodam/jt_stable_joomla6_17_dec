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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;

HTMLHelper::_('bootstrap.tooltip');
?>

<div id="jtwrap" class="tjBs3">
    <form action="<?php echo Route::_('index.php?option=com_jticketing&view=attendees&layout=signin' . $this->component); ?>" method="post" name="adminForm" id="adminForm">
    	<div id="j-main-container">
			<div class="no-print col-xs-12 jt-search-tools af-mt-10">
				<?php
				echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this, 'options'=> array('filterButton' => $this->filterHide)));
				?>
			</div>
			<?php
			// Check for empty items.
        	if (empty($this->items)) : ?>
				<div class="col-xs-12 col-md-12">
					<div class="alert alert-info">
						<?php echo Text::_('COM_JTICKETING_NO_ATTENDEES_FOUND'); ?>
					</div>
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
        		<div class="jticketing-tbl col-xs-12 af-mt-30">
        			<table class="table table-striped table-bordered left_table" id="usersList">
        				<thead>
        					<tr>
        						<th class='left'>
        							<?php echo  Text::_('COM_JTICKETING_ATTENDEE_USER_NAME'); ?>
        						</th>
        						<th class='left'>
        							<?php echo  Text::_('COM_JTICKETING_ENROLMENT_ATTENDEE_SIGN_IN'); ?>
        						</th>
        					</tr>
        				</thead>
        			<tbody>
        				<?php
        				foreach($this->items as $i => $item) :
        					?>
        					<tr class="row<?php echo $i % 2; ?>" >
        						<td>
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
        						</td>
        						<td></td>
        					</tr>
        					<?php
        				endforeach;
        				?>
        			</tbody>
        		</table>
        		<div class="row">
    				<div class="col-xs-12">
    					<div class="pagination com_jticketing_align_center">
    						<div class="pager">
    							<?php echo $this->pagination->getPagesLinks(); ?>
    						</div>
    					</div>
    				</div>
				</div>
        	</div><!--j-main-container ENDS-->
        	<input type="hidden" name="task" id="task" value="" />
        	<input type="hidden" name="boxchecked" value="0" />
        	<input type="hidden" name="controller" id="controller" value="attendees" />
        	<?php echo HTMLHelper::_('form.token'); ?>
    	</div>
    </form>
</div>
