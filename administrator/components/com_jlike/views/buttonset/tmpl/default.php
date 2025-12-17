<?php
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

HTMLHelper::_('behavior.formvalidator');
/**
 * @package		jomLike
 * @author 		Techjoomla http://www.techjoomla.com
 * @copyright 	Copyright (C) 2011-2012 Techjoomla. All rights reserved.
 * @license 	GNU/GPL v2 http://www.gnu.org/licenses/gpl-2.0.html
 */
?>

<style type="text/css">
span.latestbutton{
color:#0B55C4;
cursor: pointer;
}
span.latestbutton:hover{
text-decoration:underline;
}
</style>

<script>
/* Override joomla.javascript, as form-validation not work with Toolbar */
Joomla.submitbutton = function (task) {
	if (task == 'add')
	{
		Joomla.submitform(task, document.getElementById('newItemForm'));
	}

	if (task == 'apply')
	{
		Joomla.submitform(task, document.getElementById('adminForm'));
	}
}
</script>
<form method="POST" name="adminForm" action="" id="adminForm">
<?php if (!empty($this->sidebar)) { ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
<?php }
else
{ ?>
	<div id="j-main-container">
<?php }?>

<table class="table">
	<tbody>
		<tr>
		<?php
			$itemcnt = 0;
			if ($this->list)
			{
				foreach ($this->list as $item)
				{
					?>
					<td>
						<input type="radio" name="buttonset" <?php echo $item->published ? 'checked="checked"' : ''; ?> value="<?php echo $item->id; ?>" />
						<img src="<?php echo Uri::root() . 'components/com_jlike/assets/images/buttonset/' . $item->title; ?>" alt=""/>
					</td>
					<?php
						$itemcnt++;
					if ($itemcnt % 3 == 0)
					{
						echo "<tr></tr>";
					} ?>
					<?php
				}
			}
			else
			{ ?>
				<tr><td><?php echo Text::_('COM_JLIKE_NO_BUTTONSET'); ?></td></tr>
			<?php
			} ?>
	</tbody>
</table>

<input type="hidden" name="view" value="buttonset"/>
<input type="hidden" name="task" value=""/>
<input type="hidden" name="option" value="com_jlike"/>
<?php echo HTMLHelper::_('form.token'); ?>
</form>

<div>
	<legend><?php echo Text::_('COM_JLIKE_ADD_BUTTONSET'); ?></legend>
	<form action="" id="newItemForm" name="newItemForm" method="post" enctype="multipart/form-data" class="form-validate">
		<table>
			<tbody>
				<tr>
					<td>
						<label for="file" ><?php echo Text::_("COM_JLIKE_ADD_NEW_BUTTON_SET"); ?> </label>
						<input id="file" type="file" class="required" required="required" name="file" accept="image/*"/>
					</td>
					<td>
						<button type="submit"  class="validate btn btn-success" onclick="submitbutton('add');">
						<?php echo Text::_('Submit'); ?></button>
					</td>
				</tr>
				<tr colspan=2>
					<div class="alert alert-info">
						<?php echo Text::_('COM_JLIKE_ADD_BUTTONSET_MSG'); ?>
					</div>
				</tr>
			</tbody>
		</table>
		<input type="hidden" name="view" value="buttonset"/>
		<input type="hidden" name="task" value="add"/>
		<input type="hidden" name="option" value="com_jlike"/>
		<?php echo HTMLHelper::_('form.token'); ?>
	</form>
</div>
