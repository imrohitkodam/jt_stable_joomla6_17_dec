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
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Language\Text;

use Joomla\Registry\Registry;

$params     = new Registry;

JLoader::import('components.com_jlike.models.todos', JPATH_SITE);

$menu   = $this->getApplication()->getMenu();
$menuItem = $menu->getItems('link', 'index.php?option=com_jlike&view=pathusers', true);

// Create path description based on path status
Factory::getApplication()->triggerEvent('onContentBeforeDisplay', array('com_jlike.path', &$this->item, &$params));

?>
<form action="" method="post" name="adminForm" id="adminForm">
	<div>
		<div>
			<div class="row">
				<div class="col-xs-12 col-md-3">
					<div class="page-header">
						<h1><?php echo $this->escape($this->item->path_title); ?></h1>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-xs-12 col-md-3">
					<?php echo $this->escape($this->item->path_description); ?>
				</div>
			</div>
		</div>
		<hr/>
		<div>
		<?php
			if (!empty($this->item->info))
			{
				foreach ($this->item->info as $item)
				{
					if ($item['visibility'] == '1')
					{
						// Create path description based on path status
						Factory::getApplication()->triggerEvent('onContentBeforeDisplay', array('com_jlike.path', &$item, &$params));
					?>
					<div class="row">
						<div class="col-xs-12 col-md-3">
							<?php echo $this->escape($item['node_path_title']); ?>
						</div>
						<div class="col-xs-12 col-md-4">
							<?php echo $this->escape($item['node_path_description']); ?>
						</div>
						<div class="col-xs-12 col-md-2">
							<?php
							if (empty($item['pathuserid']))
							{
								$class = "noclickon btn btn-secondary";
								$buttonName = 'COM_JLIKE_PATHWAY_SUBPATH_SUBSCRIBE_BUTTON';
							}
							else
							{
								$buttonName = 'COM_JLIKE_PATHWAY_SUBPATH_LAUNCH';
								$class = "btn btn-primary subcriberule";
							}

							// Find all assigned todos count
							$allTodosCountModel = BaseDatabaseModel::getInstance('Todos', 'JLikeModel');
							$allTodosCountModel->setState('filter.path_id', $item['node_path_id']);
							$allTodosCount       = count($allTodosCountModel->getItems());

							// Find completed todos count
							$completeTodoCountModel = BaseDatabaseModel::getInstance('Todos', 'JLikeModel');
							$completeTodoCountModel->setState('filter.path_id', $item['node_path_id']);
							$completeTodoCountModel->setState('filter.todo_status', 'C');
							$completedTodosCount       = count($completeTodoCountModel->getItems());

							// If the completed todo count is exactly equal to all assigned todo in given path then Check the Path is completed
							if (($allTodosCount != 0 ) && ($allTodosCount == $completedTodosCount && !empty($item['pathuserid']) && $item['isSubscribedPath'] == 'I'))
							{
								?>
								<a href="index.php?option=com_jlike&task=pathuser.selfConfirmPath&path_id=<?php echo $item['node_path_id'];?>" class= "<?php echo $class;?>" name="subcribe_path_<?php echo $item->id;?>" id="subcribe_path_<?php echo $item->id;?>">
									<?php echo Text::_('CONFIRM')?>
								</a>
								<?php
							}
							?>
						</div>
						<div class="col-xs-12 col-md-2">
							<a href="index.php?option=com_jlike&view=todos&path_id=<?php echo $item['node_path_id'];?>&Itemid=<?php echo $menuItem->id;?>" class= "<?php echo $class;?>" name="subcribe_path_<?php echo $item->id;?>" id="subcribe_path_<?php echo $item->id;?>">
								<?php echo Text::_($buttonName)?>
							</a>
						</div>
					</div>
					<?php
					}
				}
			}
			else
			{
				?>
				<div>
					<?php echo Text::_('COM_JLIKE_NO_REC_TO_SHOW'); ?>
				</div>
				<?php
			}
			?>
		</div>
		<input type="hidden" name="task" value=""/>
	</div>
</form>
