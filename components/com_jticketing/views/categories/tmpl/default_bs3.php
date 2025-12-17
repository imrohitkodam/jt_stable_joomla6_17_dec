<?php
/**
 * @package     JTicketing
 * @subpackage  com_jticketing
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license     GNU GPLv2 <http://www.gnu.org/licenses/old-licenses/gpl-2.0.html>
 */

// No direct access

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Layout\LayoutHelper;

// Joomla 6: formbehavior.chosen removed - using native select

// Load admin language file
$lang = Factory::getLanguage();
$lang->load('com_jticketing', JPATH_SITE);
$app  = Factory::getApplication();
$menuParams = $app->getParams('com_jticketing');
$show_child_categories = $menuParams->get('show_child_categories');
$show_categories_layout = $menuParams->get('show_categories_layout');

?>

<div class="container">
	<?php
	if ($app->getParams()->get('show_page_heading', 1))
	{
		?>
		<div class="page-header"><h1><?php echo $this->escape($app->getParams()->get('page_heading'));?></h1></div>
		<?php
	}
	?>
</div>

<div class="container-fluid categories-list">
	<form action="<?php echo Route::_('index.php?option=com_jticketing&view=categories&Itemid=' . $this->categoryItemId); ?>" method="post" name="adminForm" id="adminForm"
		class="jtFilters">
		<div class="clearfix"></div>
		<div class="float-end">
		<?php
		echo LayoutHelper::render('joomla.searchtools.default',
			array (
				'view' => $this
			)
		);
		?>
		</div>
		<div class="clearfix"></div>
		<hr class="hr-condensed"/>
		<?php
		if (empty($this->items))
		{
			?>
			<div class="alert alert-info" role="alert"><?php echo Text::_('NODATA');?></div>
			<?php
		}
		else
		{
			?>
			<div class="row af-mt-10">
			<?php 
				if ($show_categories_layout)
				{
					$layout = new FileLayout('categories_pin_view', JPATH_ROOT . '/components/com_jticketing/layouts/categories');
				}
				else 
				{
					$layout = new FileLayout('categories_list_view', JPATH_ROOT . '/components/com_jticketing/layouts/categories');
				}

				$displayData = array(
					'items' => $this->items,
					'utilities' => $this->utilities
				);

				$output = $layout->render($displayData);

				echo $output;
				?>
			</div>
			<div class="row af-mt-10">
			<div class="col-xs-12">
				<?php echo $this->pagination->getPagesLinks(); ?>
			</div>
			<?php
		}
		?>
		<input type="hidden" name="task" value=""/>
		<input type="hidden" name="boxchecked" value="0"/>
		<?php echo HTMLHelper::_('form.token');?>
	</form>
</div>
