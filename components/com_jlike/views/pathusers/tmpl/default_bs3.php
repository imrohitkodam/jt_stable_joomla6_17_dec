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
use Joomla\CMS\Router\Route;

HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.multiselect'); // only for list tables


use Joomla\Registry\Registry;

$params     = new Registry;

// Load language file
$lang = Factory::getLanguage();
$lang->load('com_jlike', JPATH_SITE);

$menu   = $this->getApplication()->getMenu();
$menuItem = $menu->getItems('link', 'index.php?option=com_jlike&view=pathusers', true);

if (count($this->items) == 0)
{
	?>
	<div class="alert alert-info">
		<?php echo Text::_("COM_JLIKE_NO_REC_TO_SHOW"); ?>
	</div>
	<?php
	return;
}
?>
<div>
	<form action="" method="post" name="adminForm" id="adminForm">
		<div class="col-xs-12 margint10">
			<div class="row">
				<div class="col-xs-12">
					<div class="page-header">
						<h1><?php echo Text::_('COM_JLIKE_VIEW_PATHS');?></h1>
					</div>
				</div>
				<div class ="col-xs-12 col-md-3">
					<strong><?php echo Text::_('COM_JLIKE_PATHWAY_PATH_TITLE'); ?></strong>
				</div>
				<div class ="col-xs-12 col-md-6">
					<strong><?php echo Text::_('COM_JLIKE_PATHWAY_PATH_DESCRIPTION'); ?></strong>
				</div>
				<div class ="col-xs-12 col-md-3">
					<strong><?php echo Text::_(''); ?></strong>
				</div>
			</div>
		</div>
		<?php
		foreach ($this->items as $item)
		{
			// Create path description based on path status
			Factory::getApplication()->triggerEvent('onContentBeforeDisplay', array('com_jlike.path', &$item, &$params));

			?>
			<div class="row">
				<div class="col-xs-12 col-md-3">
					<?php echo $this->escape($item->path_title); ?>
				</div>
				<div class="col-xs-12 col-md-6">
					<?php echo $this->escape($item->path_description); ?>
				</div>
				<?php
				if ($item->isSubscribedPath)
				{
					?>
					<div class="col-xs-12 col-md-3">
						<a href="<?php echo Route::_("index.php?option=com_jlike&view=pathdetail&path_id=".$item->path_id."&Itemid=".$menuItem->id); ?>" name="launch_path_<?php echo $item->path_id;?>" id="launch_path_<?php echo $item->path_id;?>" class="btn btn-primary">
							<?php echo Text::_('COM_JLIKE_PATHWAY_PATH_LAUNCH')?>
						</a>
					</div>
					<?php
				}
				else
				{
					?>
					<div class="col-xs-12 col-md-3">
						<?php
						if ($item->isPathOpenToSubscribe->allowedToSubscribe)
						{
					?>
						<a href="index.php?option=com_jlike&task=pathuser.save&path_id=<?php echo $item->path_id;?>" name="subcribe_path_<?php echo $item->path_id;?>" id="subcribe_path_<?php echo $item->path_id;?>" class="btn btn-primary" >
							<?php echo Text::_('COM_JLIKE_PATHWAY_PATH_SUBSCRIBE_BUTTON')?>
						</a>
					<?php
						}
						else
						{
							?>
							<a href="javascript:void(0);" id="subcribe_path_<?php echo $item->path_id;?>" class="btn btn-secondary disabled" >
								<?php
								if ($item->isPathOpenToSubscribe->pathClosed)
								{
									echo Text::_('COM_JLIKE_PATHWAY_PATH_SUBSCRIBE_CLOSED_BUTTON');
								}
								elseif ($item->isPathOpenToSubscribe->pathOpeningSoon)
								{
									echo Text::sprintf('COM_JLIKE_PATHWAY_PATH_SUBSCRIBE_OPENING_SOON_BUTTON', HTMLHelper::_('date', $item->subscribe_start_date, Text::_('DATE_FORMAT_LC2')));
								}
								?>
							</a>
							<?php
						}
						?>
					</div>
					<?php
				}
				?>
			</div>
			<?php
		}
		?>
		<input type="hidden" name="task" value="pathuser.save"/>
	</form>
</div>
