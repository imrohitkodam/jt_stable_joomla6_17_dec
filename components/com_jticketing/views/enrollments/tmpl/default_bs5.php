<?php
/**
 * @version    SVN: <svn_id>
 * @package    Com_Jticekting
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2018 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Language\Text;

HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.multiselect');
// Joomla 6: formbehavior.chosen removed - using native select

$listOrder  = $this->escape($this->state->get('list.ordering'));
$listDirn   = $this->escape($this->state->get('list.direction'));
?>
<div class="<?php echo JTICKETING_WRAPPER_CLASS;?>">
<!--  Modal pop up for mass enrollment -->
	<div class="enrollModal">
		<?php
			echo HTMLHelper::_('bootstrap.renderModal', 'myModal', $this->modal_params, $this->body);
		?>
	</div>
	<form action="<?php echo Route::_('index.php?option=com_jticketing&view=enrollments'); ?>" method="post" name="adminForm" id="adminForm">

		<div>
			<?php echo $this->toolbarHTML;?>
		</div>

		<?php
		if (!empty($this->sidebar)): ?>
			<div id="j-sidebar-container" class="span2">
				<?php echo $this->sidebar; ?>
			</div>
			<div id="j-main-container" class="span10">
		<?php
		else : ?>
			<div id="j-main-container">
		<?php
		endif;
			echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this));

			if (empty($this->items))
			{ ?>
				<div class="alert alert-info">
					<?php echo Text::_('COM_JTICKETING_NO_ENROLLMENTS_FOUND'); ?>
				</div>
			<?php
			}
			else
			{?>

			<!-- <div class="jticketing-tbl"> -->
				<table class="table table-striped left_table" id="usersList">
					<thead>
						<tr>
							<th width="1%" class="hidden-phone">
							<input type="checkbox" name="checkall-toggle" value="" title="<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
							</th>
							<th class='left'>
								<?php echo HTMLHelper::_('grid.sort',  'COM_JTICKETING_ENROLMENT_ID', 'attendee.id', $listDirn, $listOrder); ?>
							</th>
							<th class='left'>
								<?php echo HTMLHelper::_('grid.sort',  'COM_JTICKETING_ENROLMENT_USER_NAME', 'users.name', $listDirn, $listOrder); ?>
							</th>
							<th class='left'>
								<?php echo HTMLHelper::_('grid.sort',  'COM_JTICKETING_ENROLMENT_EVENT_NAME', 'events.title', $listDirn, $listOrder); ?>
							</th>
							<th class='left'>
								<?php echo HTMLHelper::_('grid.sort',  'COM_JTICKETING_ENROLMENT_APPROVAL', 'attendee.status', $listDirn, $listOrder); ?>
							</th>
							<th class='left'>
								<?php echo Text::_('COM_JTICKETING_ENROLMENT_NOTIFY'); ?>
							</th>
						</tr>
					</thead>
					<tfoot>
						<?php
						if(isset($this->items[0]))
						{
							$colspan = count(get_object_vars($this->items[0]));
						}
						else
						{
							$colspan = 10;
						}
						?>
						<tr>
							<td colspan="<?php echo $colspan ?>">
								<div class="pager">
									<?php echo $this->pagination->getPagesLinks(); ?>
								</div>
							</td>
						</tr>
					</tfoot>
					<tbody>
						<?php
						foreach ($this->items as $i => $item) :
							$ordering   = ($listOrder == 'b.ordering');
							?>
							<tr class="row<?php echo $i % 2; ?>">
								<td class="center hidden-phone">
									<?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
								</td>
								<?php
								if (isset($this->items[0]->state)): ?>
									<td class="center">
										<?php echo HTMLHelper::_('jgrid.published', $item->state, $i, 'enrollments.', $canChange, 'cb'); ?>
									</td>
								<?php
								endif; ?>
								<td>
									<?php echo htmlspecialchars($item->enrollment_id); ?>
								</td>
								<td>
									<?php echo htmlspecialchars($item->name); ?>
								</td>
								<td>
									<?php echo htmlspecialchars($item->title); ?>
								</td>
								<td>
									<span class="float-start">
										<select id="assign_<?php echo $i ?>" name="assign_<?php echo $i ?>" onChange='jtCommon.enrollment.updateEnrollment(<?php echo $i; ?>, <?php echo $item->id; ?>, "update")'>
											<option value="P"><?php echo Text::_('COM_JTICKETING_ENROLMENT_STATUS_PENDING'); ?></option>
											<option value="R" <?php echo $item->status === 'r' || $item->status === 'R'?'selected':'' ?> ><?php echo Text::_('COM_JTICKETING_ENROLMENT_STATUS_REJECTED'); ?></option>
											<option value="A" <?php echo $item->status === 'A' || $item->status === 'a'?'selected':'' ?> ><?php echo Text::_('COM_JTICKETING_ENROLMENT_STATUS_APPROVED'); ?></option>
										</select>
									</span>
									<span id="ajax_loader"></span>
								</td>
								<td>
									<label>
										<input id="notify_user_<?php echo $i ?>" type="checkbox" name='notify_user_<?php echo $i ?>' checked>
									</label>
								</td>
								<input type="hidden" id="eid_<?php echo $i ?>" name="eid" value="<?php echo $item->event_id; ?>" />
							</tr>
							<?php
						endforeach; ?>
					</tbody>
				</table>
			<?php } ?><!-- else end here-->
		</div><!--j-main-container ENDS-->

		<?php echo HTMLHelper::_('form.token'); ?>
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
	</form>
</div>
<script>
	jQuery(".close").click(function() {
		parent.location.reload();
	});
</script>
