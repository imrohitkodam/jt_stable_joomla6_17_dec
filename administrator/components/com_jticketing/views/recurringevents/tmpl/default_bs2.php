<?php
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;

HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.multiselect');
HTMLHelper::_('bootstrap.modal');
HTMLHelper::_('jquery.token');

$recurringEvents = $this->recurringEvents;

if (empty($recurringEvents)) :
?>
    <p><?php echo Text::_('COM_JTICKETING_NO_RECURRING_EVENTS_FOUND'); ?></p>
<?php else : ?>
    <h1>
    <?php
        if (!empty($recurringEvents)) {
            $firstName = $recurringEvents[0]->first_name ?? 'N/A';
            $lastName = $recurringEvents[0]->last_name ?? 'N/A';
            $fullName = htmlspecialchars($firstName) . ' ' . htmlspecialchars($lastName);
        } else {
            $fullName = Text::_('COM_JTICKETING_UNKNOWN');
        }
        echo sprintf(Text::_('COM_JTICKETING_RECURRING_EVENTS'), $fullName);
    ?>
</h1>
<br/>
    <form action="<?php echo Route::_('index.php?option=com_jticketing&view=recurringevents&attendee_id=' . (int) $this->attendeeId); ?>" method="post" name="adminForm" id="adminForm">
    <input type="hidden" name="task" id="task" value="" />
    <input type="hidden" name="boxchecked" value="0" />
    <input type="hidden" name="controller" id="controller" value="recurringevents" />
    <input type="hidden" name="attendee_id" value="<?php echo $this->attendeeId; ?>" />

    <?php echo HTMLHelper::_('form.token'); ?>
    <table class="table table-striped">
        <thead>
            <tr>
            <?php
            if ($this->tmpl !== 'default') { ?>
                <th width="1%" class="hidden-phone">
                    <input type="checkbox" name="checkall-toggle" value="" title="<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
                </th>
            <?php } ?>
                <th><?php echo Text::_('COM_JTICKETING_SERIAL_NO'); ?></th>
                <th><?php echo Text::_('COM_JTICKETING_DATE'); ?></th>
                <th><?php echo Text::_('COM_JTICKETING_START_TIME'); ?></th>
                <th><?php echo Text::_('COM_JTICKETING_END_TIME'); ?></th>
                <th class='text-left'><?php echo Text::_('COM_JTICKETING_CHECKIN_TIME'); ?></th>
                <th class="text-center"><?php echo Text::_('COM_JTICKETING_CHECKIN'); ?></th>
                <th class='text-left'><?php echo Text::_('COM_JTICKETING_ENROLMENT_NOTIFY'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($recurringEvents as $index => $event) : ?>
                <tr>
                <?php
                if ($this->tmpl !== 'default') { ?>
                    <td class="text-center hidden-phone">
                        <?php echo HTMLHelper::_('grid.id', $index, $event->r_id); ?>
                    </td>
                <?php } ?>
                    <td><?php echo $index + 1; ?></td>
                    <td><?php echo HTMLHelper::_('date', $event->start_date, 'Y-m-d'); ?></td>
                    <td><?php echo HTMLHelper::_('date', $event->start_time, 'H:i'); ?></td>
                    <td><?php echo HTMLHelper::_('date', $event->end_time, 'H:i'); ?></td>
                    <td>
                        <?php
                        if (!empty($event->checkintime) && !empty($event->checkin)) {
                            echo ($event->checkintime);
                        } else {
                            echo '-';
                        }
                        ?>
                    </td>
                    <td class="text-center">
                        <?php if ($event->status == 'A') : ?>
                            <a href="javascript:void(0);"
                               class="btn btn-link" 
                               onclick="
                                    const taskField = document.getElementById('task');
                                    const taskValue = '<?php echo $event->checkin ? 'recurringevents.undocheckin' : 'recurringevents.checkin'; ?>';
                                    taskField.value = taskValue;

                                    Joomla.listItemTask('cb<?php echo $index; ?>', taskValue);
                                ">
                                <img src="<?php echo Uri::root(); ?>administrator/components/com_jticketing/assets/images/<?php echo $event->checkin ? 'publish.png' : 'unpublish.png'; ?>" 
                                    class="img-fluid" width="16" height="16" alt="Checkin" />
                            </a>
                        <?php else : ?>
                            -
                        <?php endif; ?>
                    </td>
                    <td>
                        <label>
                            <input id="notify_user_<?php echo $event->r_id ?>" type="checkbox" name='notify_user_<?php echo $event->r_id ?>' checked>
                        </label>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>            
    </form>
<?php endif; ?>
