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

use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\LayoutHelper;

HTMLHelper::_('bootstrap.tooltip');
?>
<div id="jtwrap">
    <form action="<?php echo Route::_('index.php?option=com_jticketing&view=attendees&layout=namecard' . $this->component); ?>" method="post" name="adminForm" id="adminForm">
    	<div id="j-main-container">
			<div class="no-print">
				<?php
				echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this, 'options'=> array('filterButton' => $this->filterHide))); ?>
			</div>
			<?php
			if (empty($this->items)) : ?>
        		<div class="alert alert-info">
        			<?php echo Text::_('COM_JTICKETING_NO_ATTENDEES_FOUND'); ?>
        		</div>
        		<?php
        		return; ?>
        	<?php
			endif;  ?>
			<div class="row">
				<div class="col-md-12">
					<input type="button" class="btn btn-success no-print pull-right" onclick="javascript:window.print()" value="<?php echo Text::_('COM_JTICKETING_ATTENDEE_VIEW_PRINT');?>">
				</div>
			</div>
			<div class="row-fluid no-print">
				<hr>
			</div>
        			<div class="row equal-height-col">
        				<?php
        				foreach($this->items as $item)
        				{ ?>
							<div class="col-md-3 col-6 thumbnail equal-items border border-secondary m-2">
							<div class="af-p-10">
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
								if (is_array($this->nameCardField) || is_object($this->nameCardField))
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
        			<div>
    					<?php echo $this->pagination->getListFooter(); ?>
    				</div>
        	</div><!--jticketing-tbl-main-container ENDS-->
        	<input type="hidden" name="task" id="task" value="" />
        	<input type="hidden" name="boxchecked" value="0" />
        	<input type="hidden" name="controller" id="controller" value="attendees" />
        	<?php echo HTMLHelper::_('form.token'); ?>
    	</div>
    </form>
</div>
