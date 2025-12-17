<?php
/**
 * @package     JTicketing
 * @subpackage  com_jticketing
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;

/** @var $this JticketingViewEventform */
?>

<table class="table table-bordered">
	<thead>
		<tr>
			<th width="5%">
				<?php echo Text::_('COM_JTICKETING_ATTENDEE_TITLE'); ?>
			</th>
			<th width="5%">
				<?php echo Text::_('COM_JTICKETING_ATTENDEE_TYPE'); ?>
			</th>
		</tr>
	</thead>
	<tbody>
		<?php
				foreach ($this->attendeeList as $i => $item)
				{
					?>
					<tr class="row<?php echo $i % 2; ?>">
							<td >
								<?php echo htmlspecialchars(Text::_($item->label), ENT_COMPAT, 'UTF-8'); ?>
							</td>
							<td >
								<?php echo $item->type; ?>
							</td>
						</tr>
				<?php
				}?>
	</tbody>
</table>
<div class="jticketing_params_container">
	<div>
		<?php
			$this->form->setFieldAttribute('attendeefields', 'layout', 'JTsubformlayouts.layouts.bs5.subform.repeatable');
			echo $this->form->getInput('attendeefields');
		?>
	</div>
</div>