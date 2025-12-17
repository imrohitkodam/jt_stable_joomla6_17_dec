<?php
/**
 * @version    CVS: 1.0.0
 * @package    Com_Pip
 * @author     Sudhir Sapkal <contact@techjoomla.com>
 * @copyright  2016 Sudhir Sapkal
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Language\Text;

HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.formvalidator');

// Load admin language file
$lang = Factory::getLanguage();
$lang->load('com_jlike', JPATH_SITE);

if (!empty($this->extra_sidebar))
{
    $this->sidebar .= $this->extra_sidebar;
}
?>
<form action="<?php echo Route::_('index.php?option=com_jlike&view=paths'); ?>" id="adminForm" method="post" name="adminForm" class="form-validate">

<?php if(!empty($this->sidebar)): ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
<?php else : ?>
	<div id="j-main-container">
<?php endif;?>
 <?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
 	<div class="clearfix"></div>
	<?php if (empty($this->items)) : ?>
	<div class="alert alert-info">
		<?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
	</div>
	<?php else : ?>
	<table class="table table-striped table-hover">
		<tbody>
			<th width="3%">
				<input type="checkbox" name="checkall-toggle" value=""
				title="<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)"/>
			</th>
			<th width="7%">
				<?php echo HTMLHelper::_('grid.sort', 'COM_JLIKE_PATHWAY_PATH_TITLE', 'path_title', $this->sortDirection, $this->sortColumn); ?>
			</th>
			<th width="10%">
				<?php echo HTMLHelper::_('grid.sort', 'COM_JLIKE_PATHWAY_PATH_PATH_TYPE', 'path_type', $this->sortDirection, $this->sortColumn); ?>
			</th>
			<th width="10%">
				<?php echo HTMLHelper::_('grid.sort', 'COM_JLIKE_PATHWAY_PATH_CREATED_BY', 'created_by', $this->sortDirection, $this->sortColumn); ?>
			</th>
			<th width="7%">
				<?php echo HTMLHelper::_('grid.sort', 'COM_JLIKE_PATHWAY_PATH_ID', 'path_id', $this->sortDirection, $this->sortColumn); ?>
			</th>
			<th width="7%">
				<?php echo Text::_('COM_JLIKE_PATH_CONTENTS'); ?>
			</th>
		</tr>
			<?php foreach ($this->items as $i => $row) :
				$link = Route::_('index.php?option=com_jlike&task=path.edit&path_id=' . $row->path_id);
			?>
				<tr>
					<td>
						<?php echo HTMLHelper::_('grid.id', $i, $row->path_id);?>
					</td>

					<td>
						<a href="<?php echo $link; ?>" title="<?php echo Text::_('COM_JLIKE_PATHS_LIST_VIEW_DESC');?>">
						<?php echo $row->path_title; ?></a>
					</td>

					<td>
						<?php echo $row->type_title; ?>
					</td>

					<td>
						<?php echo Factory::getUser($row->created_by)->name; ?>
					</td>
					<td>
						<?php echo $row->path_id; ?>
					</td>
					<td>
						<a class="btn btn-primary" href="<?php echo Route::_('index.php?option=com_jlike&view=pathnodegraphs&path_id=' . $row->path_id); ?>"><?php echo Text::_('COM_JLIKE_PATH_CONTENTS');?></a>
					</td>
				</tr>

			<?php endforeach; ?>
	</tbody>
</table>
<?php endif; ?>
	<tfoot>
		<tr>
			<td colspan="<?php echo isset($this->items[0]) ? count(get_object_vars($this->items[0])) : 10; ?>">
				<?php echo $this->pagination->getListFooter(); ?>
			</td>
		</tr>
	</tfoot>
	<input type="hidden" name="task" value=""/>
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $this->sortColumn; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->sortDirection; ?>" />
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
