<?php 
/**
 * @package     Quick2cart
 * @subpackage  com_quick2cart
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Router\Route;

$app                 = Factory::getApplication();
$input               = $app->input;

$menu           = $app->getMenu();
$activeMenuItem = $menu->getActive();

// If product category not found in URL then assign product category according menu

$items   = isset($displayData['items']) ? $displayData['items'] : [];
$utilities   = isset($displayData['utilities']) ? $displayData['utilities'] : [];

$display_pin_page   = isset($displayData['display_pin_page']) ? $displayData['display_pin_page'] : 0;

foreach ($items as $key => $category)
{
	?>
	<div class="jt_pin jt_item_q2c_pc_category qtc-prod-pin col-xs-12 col-md-3 col-sm-6 mt-3 jt-category-card"> 

		<?php
		$defaultCategoryImage = 0;
		$menuParams = $app->getParams('com_jticketing');
		$show_child_categories = $menuParams->get('show_child_categories');

		if (!$show_child_categories)
		{
			$itemId  = $utilities->getItemId('index.php?option=com_jticketing&view=categories&layout=default', 0, $category->id);
			$categoryUrl = Route::_('index.php?option=com_jticketing&view=categories&layout=default&cat_id=' . $category->id . '&Itemid=' . $itemId, false);
		} else 
		{
			$itemId  = $utilities->getItemId('index.php?option=com_jticketing&view=events', 0, $category->id);
			$categoryUrl = Route::_('index.php?option=com_jticketing&view=events&layout=default&catid=' . $category->id . '&Itemid=' . $itemId, false);
		}

		$params = $category->params ? json_decode($category->params) : null;
		if ($params && $params->image)
		{
			$imagePath = $params->image;
		}
		else 
		{
			$imagePath = 'components/com_jticketing/assets/images/no-image-available.png';
			$defaultCategoryImage = 1;
		}
		?>

		<a class="jt-category--link " href="<?php echo $categoryUrl; ?>">
			<div class="jt-category--image br-15">
				<img class="af-br-5" src="<?php echo $imagePath; ?>">
				<?php
					if ($defaultCategoryImage)
					{
						?>
						<div class="category-image-text">
							<h3><?php echo $category->title; ?></h3>
						</div>
						<?php
					}
				?>
			</div>
			<div class="jt-category--title">
				
				<?php echo $category->title; ?>
			</div>
		</a>

	</div> 
	<?php
}
?>