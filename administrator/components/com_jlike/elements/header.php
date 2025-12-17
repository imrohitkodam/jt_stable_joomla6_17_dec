<?php
defined('JPATH_BASE') or die();
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
class JFormFieldHeader extends FormField
{
	var	$type='Header';
	function getInput()
	{
		$document=Factory::getDocument();
		$document->addStyleSheet(Uri::base().'components/com_jlike/assets/css/like.css');
		$return='
		<div class="jlike_div_outer">
			<div class="jlike_div_inner">
				'.Text::_($this->value).'
			</div>
		</div>';
		return $return;
	}
}
?>
