<?php
/**
 * @version    SVN: <svn_id>
 * @package    Quick2cart
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access.
defined('_JEXEC') or die();
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Component\ComponentHelper;

if (isset($random_container))
{
	HTMLHelper::_('bootstrap.tooltip');
	HTMLHelper::_('behavior.framework');
	HTMLHelper::_('bootstrap.renderModal');

	$document = Factory::getDocument();
	HTMLHelper::_('script', 'media/com_jticketing/vendors/js/masonry.pkgd.min.js');

	$this->params = ComponentHelper::getParams('com_jticketing');

	// Get pin width
	$pin_width = $this->params->get('pin_width');

	if (empty($pin_width))
	{
		$pin_width = 170;
	}

	// Get pin padding
	$pin_padding = $this->params->get('pin_padding');

	if (empty($pin_padding))
	{
		$pin_padding = 7;
	}

	// Calulate columnWidth (columnWidth = pin_width+pin_padding)
	$columnWidth = $pin_width + $pin_padding;
	?>

	<?php if (!isset($pin_width_defined)): ?>
		<style type="text/css">
			.jticket_pin_item_<?php echo $random_container;?>{ width: <?php echo $pin_width . 'px';?> !important; margin-bottom: <?php echo $pin_padding . 'px'; ?> !important; }
		</style>
	<?php endif; 
	Factory::getDocument()->addScriptDeclaration('
	var pin_container_' . $random_container . ' = "jticket_pc_es_app_my_products"

		var container_' . $random_container . ' = document.getElementById(pin_container_' . $random_container . ');
		var msnry = new Masonry( container_' . $random_container . ', {
			columnWidth: ' . $columnWidth . ',
			itemSelector: ".jticket_pin_item_' . $random_container . '",
			gutter: ' . $pin_padding . '});


		function jticketPinArrange()
		{

			var container_' . $random_container . ' = document.getElementById(pin_container_' . $random_container . ');
			var msnry = new Masonry( container_' . $random_container . ', {
				columnWidth: ' . $columnWidth . ',
				itemSelector: ".jticket_pin_item_' . $random_container . '",
				gutter: ' . $pin_padding . '});
		}
		techjoomla.jQuery(document).ready(function()
		{
			/*var container_' . $random_container . ' = document.getElementById(pin_container_' . $random_container . ');
			var msnry = new Masonry( container_' . $random_container . ', {
				columnWidth: ' . $columnWidth . ',
				itemSelector: ".jticket_pin_item_' . $random_container . '",
				gutter: ' . $pin_padding . '});
			*/
			setTimeout(function() { jticketPinArrange(); }, 1000);
			setTimeout(function() { jticketPinArrange(); }, 2000);
			setTimeout(function() { jticketPinArrange(); }, 3000);
			setTimeout(function() { jticketPinArrange(); }, 4000);
			setTimeout(function() { jticketPinArrange(); }, 5000);
			setTimeout(function() { jticketPinArrange(); }, 6000);
			setTimeout(function() { jticketPinArrange(); }, 7000);
			setTimeout(function() { jticketPinArrange(); }, 8000);
			setTimeout(function() { jticketPinArrange(); }, 9000);
			setTimeout(function() { jticketPinArrange(); }, 10000);
			setTimeout(function() { jticketPinArrange(); }, 11000);
		});
	');
}
?>
