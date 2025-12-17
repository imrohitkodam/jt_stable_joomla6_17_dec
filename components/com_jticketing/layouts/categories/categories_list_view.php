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
	<div class="jt_pin item_jt_pc_category qtc-prod-pin col-xs-12 col-md-12 jt-category-card"> 

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
		?>

		<div class="qtcWordWrap">
			<a class="tj-list-group-item " href="<?php echo $categoryUrl ;?>">
				<?php echo $category->title; ?>
			</a>
		</div>

	</div> 
	<?php
}
?>