<?php
/**
 * @package     TJVendors
 * @subpackage  com_tjvendors
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2024 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die();

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;

HTMLHelper::addIncludePath(JPATH_COMPONENT . '/helpers/html');
HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('formbehavior.chosen', 'select');
HTMLHelper::_('behavior.keepalive');

?>
<script type="text/javascript">
	tjVAdmin.fee.initFeeJs();
</script>

<form action="
<?php echo Route::_('index.php?option=com_tjvendors&view=vendorfee&layout=edit&id=' . (int) $this->id . '&vendor_id=' . (int) $this->item->vendor_id);?>"
method="post" enctype="multipart/form-data" name="adminform" id="vendorfee-form" class="form-validate">

	<div class="form-horizontal">
		<div class="row-fluid">
			<div class="span10 form-horizontal">
				<fieldset class="adminform">

					<?php
						echo $this->form->renderField('vendor_title');

						if($this->item->vendor_id == 0)
						{
							echo $this->form->renderField('currency');
						}
						else
						{
							$this->form->setFieldAttribute('currency', 'readonly', 'true');
							echo $this->form->renderField('currency');
						?>
						<input type="hidden" name="jform[vendor_id]" value="<?php echo $this->vendor_id; ?>" />
						<?php
						}
						?>
						<?php echo $this->form->renderField('percent_commission'); ?>
						<?php echo $this->form->renderField('flat_commission'); ?>
						<input type="hidden" name="jform[client]" value="<?php echo $this->input->get('client', '', 'STRING');?>" />

				</fieldset>
			</div>
		</div>
		<input type="hidden" name="task" value=""/>
		<input type="hidden" name="vendor_id" value="<?php echo $this->vendor_id;?>" />
		<input type="hidden" name="client" value="<?php echo $this->input->get('client', '', 'STRING');?>" />
		<input type="hidden" name="jform[id]" value="<?php echo $this->id; ?>" />
		<?php echo HTMLHelper::_('form.token'); ?>
	</div>
</form>

