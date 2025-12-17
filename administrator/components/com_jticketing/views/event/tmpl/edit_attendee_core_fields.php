<?php
use Joomla\CMS\Language\Text;
?>
<table class="table table-bordered">
<thead>
	<tr>
		<th width="5%">
			<?php echo Text::_('COM_JTICKETING_ATTENDEE_TITLE'); ?>
		</th>
		<th width="5%">
			<?php echo Text::_('COM_JTICKETING_ATTENDEE_FIELD_NAME'); ?>
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
							<?php echo $this->escape(Text::_($item->label), ENT_COMPAT, 'UTF-8'); ?>
						</td>
						<td >
							<?php echo $this->escape($item->name); ?>
						</td>
						<td >
							<?php echo $item->type; ?>
						</td>
					</tr>
			<?php
			}?>
</tbody>
</table>
<?php
$bsVersion = (JVERSION < '4.0.0') ? 'bs3' : 'bs5';
$this->form->setFieldAttribute('attendeefields', 'layout', 'JTsubformlayouts.layouts.' . $bsVersion . '.subform.repeatable');
echo $this->form->getInput('attendeefields');
