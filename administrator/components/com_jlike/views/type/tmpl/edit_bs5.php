<?php
/**
 * @version    CVS: 1.2.1
 * @package    Com_Jlike
 * @author     Sudhir Sapkal <contact@techjoomla.com>
 * @copyright  2016 Sudhir Sapkal
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
HTMLHelper::_('jquery.token');
HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.multiselect'); // only for list tables

HTMLHelper::_('behavior.keepalive');

$options['relative'] = true;
HTMLHelper::_('script', 'com_jlike/pathService.min.js', $options);
HTMLHelper::_('script', 'com_jlike/path.min.js', $options);

if (!empty($this->extra_sidebar))
{
	$this->sidebar .= $this->extra_sidebar;
}
?>

<form action="<?php echo Route::_('index.php?option=com_jlike&layout=edit&path_type_id=' . (int) $this->item->path_type_id); ?>"
 method="post" name="adminForm" id="adminForm" >
<?php if(!empty($this->sidebar)): ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
<?php else : ?>
	<div id="j-main-container">
<?php endif;?>
	<div class="form-horizontal">
		<fieldset class="adminform">
				<div class="row-fluid">
				<div class="span6">
					<?php echo $this->form->renderField('type_title'); ?>
					<?php echo $this->form->renderField('identifier'); ?>
					<div class="path-params">
						<a class="btn btn-info pull-right" href="javascript:void(0);" data-toggle="popover" title="<?php echo Text::_('COM_JLIKE_PATH_PARAMS'); ?>" data-content="<?php echo Text::_('COM_JLIKE_PATH_PARAMS_EXAMPLE_DESC'); ?>"><?php echo Text::_('COM_JLIKE_PATH_INFO_EXAMPLE_TITLE'); ?></a>
						<?php echo $this->form->renderField('params'); ?>
					</div>
				<div>
				<div>
		</fieldset>
	</div>

   <input type="hidden" name="task" value=""/>
<?php echo HTMLHelper::_('form.token'); ?>
</form>
<script type="text/javascript">
	jQuery(document).ready(function(){
		jQuery('[data-toggle="popover"]').popover();

		<?php
		if (empty($this->item->path_type_id))
		{
		?>
			path.getPathParams(1);

		<?php
		}
		?>
	});
</script>
