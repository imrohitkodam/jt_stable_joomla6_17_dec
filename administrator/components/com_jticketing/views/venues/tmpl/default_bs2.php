<?php
/**
 * @version    CVS: 1.0.0
 * @package    Com_Jticketing
 * @author     Techjoomla <kiran_l@techjoomla.com>
 * @copyright  2016 techjoomla
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\LayoutHelper;

HTMLHelper::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jticketing'. '/helpers/html');
HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.multiselect');
// Joomla 6: formbehavior.chosen removed - using native select

$user       = Factory::getUser();
$userId     = $user->get('id');
$listOrder  = $this->state->get('list.ordering');
$listDirn   = $this->state->get('list.direction');
$canCreate  = $user->authorise('core.create', 'com_jticketing');
$canEdit    = $user->authorise('core.edit', 'com_jticketing');
$canCheckin = $user->authorise('core.manage', 'com_jticketing');
$canChange  = $user->authorise('core.edit.state', 'com_jticketing');
$canDelete  = $user->authorise('core.delete', 'com_jticketing');
$saveOrder  = $listOrder == 'a.ordering';

if ($saveOrder)
{
	$saveOrderingUrl = 'index.php?option=com_tjlms&task=venues.saveOrderAjax&tmpl=component';
	HTMLHelper::_('sortablelist.sortable', 'venueList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}
?>

<form action="<?php echo Route::_('index.php?option=com_jticketing&view=venues'); ?>" method="post" name="adminForm" id="adminForm">
	<?php
	if (!empty($this->sidebar)):
		?>
		<div id="j-sidebar-container" class="span2">
			<?php echo $this->sidebar; ?>
		</div>
		<div id="j-main-container" class="span10">
		<?php
	else :
		?>
		<div id="j-main-container">
		<?php
	endif;

	echo  LayoutHelper::render('joomla.searchtools.default', array('view' => $this));

	if (empty($this->items ))
	{
		?>
		<div class="alert alert-info">
		<?php echo Text::_('NODATA'); ?>
		</div>
		<?php
	}
	else
	{
		?>
		<table class="table table-striped" id="venueList">
			<thead>
				<tr>
				<?php
				if (isset($this->items[0]->ordering)):
					?>
					<th width="1%" class="nowrap center hidden-phone">
						<?php
						echo HTMLHelper::_('grid.sort', '<i class="icon-menu-2"></i>', 'a.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING');
						?>
					</th>
					<?php
				endif;
					?>
					<th width="1%" class="hidden-phone">
						<input type="checkbox" name="checkall-toggle" value="" title="<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
					</th>
					<?php
				if (isset($this->items[0]->state)):
					?>
					<th width="1%" class="nowrap center">
						<?php
						echo HTMLHelper::_('grid.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder);
						?>
					</th>
					<?php
				endif;
					?>
					<th class=''>
						<?php
						echo HTMLHelper::_('grid.sort',  'COM_JTICKETING_VENUES_NAME', 'a.name', $listDirn, $listOrder);
						?>
					</th>
					<th class=''>
						<?php
						echo Text::_('COM_JTICKETING_VENUES_CREATER_NAME');
						?>
					</th>
					<th class=''>
						<?php
						echo HTMLHelper::_('grid.sort',  'COM_JTICKETING_VENUES_CATEGORY', 'a.venue_category', $listDirn, $listOrder);
						?>
					</th>
					<th class=''>
						<?php
						echo HTMLHelper::_('grid.sort',  'COM_JTICKETING_VENUES_TYPE', 'a.online', $listDirn, $listOrder);
						?>
					</th>
					<th class=''>
						<?php
						echo HTMLHelper::_('grid.sort',  'COM_JTICKETING_VENUES_PRIVACY', 'a.privacy', $listDirn, $listOrder);
						?>
					</th>
					<th class=''>
						<?php
						echo HTMLHelper::_('grid.sort',  'COM_JTICKETING_VENUES_ID', 'a.id', $listDirn, $listOrder);
						?>
					</th>
				</tr>
			</thead>
			<tfoot>
			<tr>
				<td colspan="<?php echo isset($this->items[0]) ? count(get_object_vars($this->items[0])) : 10; ?>">
					<?php echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
			</tfoot>
			<tbody>
			<?php
			foreach ($this->items as $i => $item) :

				$canEdit = $user->authorise('core.edit', 'com_jticketing');

				if (!$canEdit && $user->authorise('core.edit.own', 'com_jticketing')):
				$canEdit = Factory::getUser()->id == $item->created_by;
				endif;
				?>
				<tr class="row<?php echo $i % 2; ?>">
				<?php
				if (isset($this->items[0]->ordering)):
					?>
					<td class="order nowrap center hidden-phone">
						<?php
						if ($canChange) :
						$disableClassName = '';
						$disabledLabel    = '';

							if (!$saveOrder) :
							$disabledLabel    = Text::_('JORDERINGDISABLED');
							$disableClassName = 'inactive tip-top';
							endif;
							?>
							<span class="sortable-handler hasTooltip <?php echo $disableClassName?>" title="<?php echo $disabledLabel?>">
								<i class="icon-menu"></i>
							</span>
							<input type="text" style="display:none" name="order[]" size="5" value="<?php echo $item->ordering;?>" class="width-20 text-area-order " />
							<?php
						else :
							?>
							<span class="sortable-handler inactive" >
								<i class="icon-menu"></i>
							</span>
							<?php
						endif;
						?>
					</td>
					<?php
				endif;
					?>
					<td class="center hidden-phone">
						<?php
						echo HTMLHelper::_('grid.id', $i, $item->id);
						?>
					</td>
					<?php
					if (isset($this->items[0]->state)) :
					$class = ($canChange) ? 'active' : 'disabled';
						?>
						<td class="center">
							<?php
							echo HTMLHelper::_('jgrid.published', $item->state, $i, 'venues.', $canChange, 'cb');
							?>
						</td>
						<?php
					endif;
					?>
					<td>
						<?php
						if (isset($item->checked_out) && $item->checked_out) :
							echo HTMLHelper::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'venues.', $canCheckin);
						endif;
						if ($canEdit): ?>
							<a href="<?php echo Route::_('index.php?option=com_jticketing&view=venue&layout=edit&id='.(int) $item->id); ?>">
								<?php
								echo $this->escape($item->name);
								?>
							</a>
						<?php
						else: 
							echo $this->escape($item->name);
						endif; ?>
					</td>
					<td>
						<?php 
						$user = Factory::getUser($item->created_by);
						echo $user->name;?>
					</td>
					<td>
						<?php
						echo $item->venue_category;
						?>
					</td>
					<td>
						<?php
						echo ($item->online) ? Text::_("COM_JTICKETING_VENUE_TYPEONLINE"):Text::_("COM_JTICKETING_VENUE_TYPEOFFLINE")
						?>
					</td>
					<td>
						<?php
						echo ($item->privacy) ? Text::_("COM_JTICKETING_VENUE_PRIVACY_PUBLIC"):Text::_("COM_JTICKETING_VENUE_PRIVACY_PRIVATE")
						?>
					</td>
					<td>
						<?php
						echo $item->id;
						?>
					</td>
				</tr>
				<?php
			endforeach;
			?>
			</tbody>
		</table>
		<?php
	}
	?>
	<input type="hidden" name="task" value=""/>
	<input type="hidden" name="boxchecked" value="0"/>
	<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
	<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>
	<?php echo HTMLHelper::_('form.token'); ?>
	</div>
</form>
