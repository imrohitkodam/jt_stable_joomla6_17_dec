<?php
/**
 * @package     Joomla.Site
 * @subpackage  Com_JLike
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (c) 2009-2017 TechJoomla, Tekdi Technologies Pvt. Ltd. All rights reserved.
 * @license     GPLv2 <http://www.gnu.org/licenses/old-licenses/gpl-2.0.html>.
 * @link        http://techjoomla.com.
 */

// No direct access
defined('_JEXEC') or die;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.formvalidator');
// Note: 'formbehavior.chosen' is deprecated in Joomla 4+, using native select styling instead

// Load admin language file
$lang = Factory::getLanguage();
$lang->load('com_jlike', JPATH_SITE);

$menu   = Factory::getApplication()->getMenu();
$menuItem = $menu->getItems('link', 'index.php?option=com_tjucm&view=itemform', true);
?>
<div>
	<form action="" method="post" name="adminForm" id="adminForm">
		<div class="row">
			<div class="col-xs-12">
				<div class="page-header">
					<h1><?php echo Text::_('COM_JLIKE_VIEW_TODOS');?></h1>
				</div>
			</div>
		</div>
		<?php
		if ($this->items)
		{
			?>
			<div class="col-xs-12 margint10">
				<div class="row">
					<div class ="col-xs-12 col-md-6">
						<strong><?php echo Text::_('COM_JLIKE_TODO_TITLE'); ?></strong>
					</div>
					<div class ="col-xs-12 col-md-6">
						<strong><?php echo Text::_('COM_JLIKE_TODO_STATUS'); ?></strong>
					</div>
				</div>
			</div>
			<?php
			foreach ($this->items as $item)
			{
				?>
				<div class="">
					<div class="col-xs-12 col-md-6">
						<a href="<?php echo $item->url. '&client=' . $item->element . '&Itemid=' . $menuItem->id; ?>" target="_blank">
							<?php echo htmlspecialchars($item->todo_title); ?>
						</a>
					</div>
					<div class="col-xs-12 col-md-6">
						<?php echo $item->status; ?>
					</div>
				</div>
			<?php
			}
		}
		else
		{
			?>
			<div class="alert alert-info">
				<?php echo Text::_('COM_JLIKE_NO_REC_TO_SHOW'); ?>
			</div>
			<?php
		}
		?>
		<input type="hidden" name="task" value=""/>
	</form>
</div>
