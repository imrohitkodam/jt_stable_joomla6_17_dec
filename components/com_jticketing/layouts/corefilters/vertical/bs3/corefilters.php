<?php
/**
 * @package    JTicketing
 * @author     TechJoomla <extensions@techjoomla.com>
 * @website    http://techjoomla.com
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

// No direct access
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;

$app    = Factory::getApplication();
$params = $app->getParams('com_jticketing');

// Get the online_events value if it's enable then display event filter
$onlineEventsEnable = $params->get('enable_online_events', '', 'INT');

$jticketingModelEvents = JT::model('events');;
$days                  = $jticketingModelEvents->getDayOptions();
$creator               = $jticketingModelEvents->getCreator();
$location              = $jticketingModelEvents->getLocation();

$online_events   = array();
$online_events[] = HTMLHelper::_('select.option', '', Text::_('COM_JTK_FILTER_SELECT_EVENT_DEFAULT'));
$online_events[] = HTMLHelper::_('select.option', '0', Text::_('COM_JTK_FILTER_SELECT_EVENT_OFFLINE'));
$online_events[] = HTMLHelper::_('select.option', '1', Text::_('COM_JTK_FILTER_SELECT_EVENT_ONLINE'));

foreach ($online_events as $value) {
	$online_event = $value->text;
}

$eventsToShow   = array();
$eventsToShow[] = HTMLHelper::_('select.option', 'featured', Text::_('COM_JTK_FEATURED_CAMP'));
$eventsToShow[] = HTMLHelper::_('select.option', 'ongoing', Text::_('COM_JTK_FILTER_ONGOING'));
$eventsToShow[] = HTMLHelper::_('select.option', 'past', Text::_('COM_JTK_FILTER_PAST_EVNTS'));

// Get itemid
$url               = 'index.php?option=com_jticketing&view=events&layout=default';
$singleEventItemid = JT::utilities()->getItemId($url);

if (empty($singleEventItemid)) {
	$singleEventItemid = Factory::getApplication()->getInput()->get('Itemid');
}

// Get filter value and set list
$defualtCatid               = $app->getInput()->get('catid');
$filter_event_cat           = $app->getUserStateFromRequest('com_jticketing.filter_events_cat', 'filter_events_cat', $defualtCatid, 'INT');
$lists['filter_events_cat'] = $filter_event_cat;

// Ordering option
$default_sort_by_option = $params->get('default_sort_by_option');
$filter_order_Dir       = $params->get('filter_order_Dir');
$filter_order           = $app->getUserStateFromRequest('com_jticketing.filter_order', 'filter_order', $default_sort_by_option, 'string');
$filter_order_Dir       = $app->getUserStateFromRequest('com_jticketing.filter_order_Dir', 'filter_order_Dir', $filter_order_Dir, 'string');

// Get creator and location filter
$filter_creator  = $app->getUserStateFromRequest('com_jticketing' . 'filter_creator', 'filter_creator');
$filter_location = $app->getUserStateFromRequest('com_jticketing' . 'filter_location', 'filter_location');
$online_event    = $app->getUserStateFromRequest('com_jticketing' . 'online_events', 'online_events');
$filter_day      = $app->getUserStateFromRequest('com_jticketing' . 'filter_day', 'filter_day');
$filter_tags     = $app->getUserStateFromRequest('com_jticketing' . 'filter_tags', 'filter_tags');
$filter_price    = $app->getUserStateFromRequest('com_jticketing' . 'filter_tags', 'filter_price');

// Set all filters in list
$lists['filter_order']     = $filter_order;
$lists['filter_order_Dir'] = $filter_order_Dir;
$lists['filter_creator']   = $filter_creator;
$lists['filter_location']  = $filter_location;
$lists['online_events']    = $online_event;
$lists['filter_day']       = $filter_day;
$lists['filter_tags']      = $filter_tags;
$lists['filter_price']     = $filter_price;
$lists                     = $lists;

// Search and filter
$filter_state            = $app->getUserStateFromRequest('com_jticketing' . 'search', 'search', '', 'string');
$filter_events_to_show   = $app->getUserStateFromRequest('com_jticketing' . 'filter_quicksearchfields', 'filter_quicksearchfields');
$lists['search']         = $filter_state;
$lists['events_to_show'] = $filter_events_to_show;
?>

<div class="jticketing_filters">
	<div class="panel-group" id="accordion">
		<div><b><?php echo Text::_('COM_JTICKETING_FILTER_EVENTS');?></b></div>
		<div class="form-group">
			<?php
			if ($onlineEventsEnable == '1')
			{
			    if ($params->get('show_event_filter') !== 'none')
			    {
			        echo HTMLHelper::_('select.genericlist', $online_events, "online_events", ' size="1"
						onchange="this.form.submit();" class="form-control" name="online_events"',"value", "text", $lists['online_events']);
			    }
			}

			if ($params->get('show_location_filter') !== 'none')
			{
			    echo HTMLHelper::_('select.genericlist', $location, "filter_location", 'class="form-control" size="1"
					onchange="this.form.submit();" name="filter_location"',"value", "text",$lists['filter_location']);
			}

			if ($params->get('show_creator_filter') !== 'none')
			{
				echo HTMLHelper::_('select.genericlist', $creator, "filter_creator", ' size="1"
					onchange="this.form.submit();" class="form-control" name="filter_creator"',"value", "text", $lists['filter_creator']);
			}
			else
			{
				$input          = Factory::getApplication()->getInput();
				$filter_creator = $input->get('filter_creator','','INT');
			}

			if ($params->get('show_date_filter') !== 'none')
			{
			    // In case of a custom date is selected format the Date as startDate - endDate
			    $pickDate = $lists['filter_day'] ? explode("-", $lists['filter_day']) : [];

			    if (count($pickDate) === 2)
			    {
			        $pickFormatDate = HTMLHelper::date($pickDate [0], 'M d') . ' - ' . HTMLHelper::date($pickDate[1], 'M d');

			        foreach ($days as $each)
			        {
			            if($each->value === 'custom_date')
			            {
			                $each->text = $pickFormatDate;
			                $each->value = $lists['filter_day'];
			            }
			        }
			    }
			    ?>
    			<div>
    				<?php
    				echo HTMLHelper::_('select.genericlist', $days,
    				"filter_day", 'class="form-control" size="1" onchange="jtSite.events.calendarSubmit(this.value, this);"
    				name="filter_day"', "value", "text", $lists['filter_day']
    				);
    				?>
    			</div>
				<?php if (count($pickDate) === 2)
                { ?>
        			<div class="tj-filterhrizontal col-xs-2 col-sm-1 col-md-1">
        				<button type="button" class="btn btn-primary" onclick="jtSite.events.resetCalendar();">
        					<i class="fa fa-times" aria-hidden="true"></i>
        				</button>
        			</div>
        		<?php
    			}
    		}

			if ($params->get('show_tags_filter') !== 'none')
    		{
    		?>
			<div>
				<select name="filter_tags" id="filter_tags" onchange="this.form.submit();" class="form-control">
					<option value=""><?php echo Text::_('JOPTION_SELECT_TAG'); ?></option>
					<?php echo HTMLHelper::_('select.options', HTMLHelper::_('tag.options', true, true), 'value', 'text', $lists['filter_tags']); ?>
				</select>
			</div>
    		<?php
    		}

            if ($params->get('show_price_filter') !== 'none')
            {
                $options = array(
                    HTMLHelper::_('select.option', '', Text::_('COM_JTICKETING_SELECT_PRICE')),
                    HTMLHelper::_('select.option', 'free', Text::_('COM_JTICKETING_FREE_EVENTS')),
                    HTMLHelper::_('select.option', 'paid', Text::_('COM_JTICKETING_PAID_EVENTS')),
                );
                ?>
    			<div>
    				<select name="filter_price" id="filter_price" onchange="this.form.submit();" class="form-control">
    					<?php echo HTMLHelper::_('select.options', $options, 'value', 'text', $lists['filter_price']); ?>
    				</select>
    			</div>
    		<?php
            }
            ?>
		</div>

		<?php
		if ($params->get('show_search_filter'))
		{?>
    		<div><b><?php echo Text::_('COM_JTICKETING_EVENTS_TO_SHOW'); ?></b></div>
    		<div>
			<?php
				for($i=0; $i < count($eventsToShow); $i++)
				{
					$check = '';

					if ($lists['events_to_show'] == $eventsToShow[$i]->value)
					{
						$class = "active";
						$check = "checked";
					}
					else
					{
						$class = "";
					}
					?>
					<div class="<?php echo $class; ?>">
						<label><input type="radio" class=""
							name="filter_quicksearchfields"
							id="quicksearchfields" <?php echo $check;?>
							value="<?php echo $eventsToShow[$i]->value; ?>"
							onclick='this.form.submit()'/>
						<?php echo $eventsToShow[$i]->text; ?></label>
					</div>
			<?php
				}?>
		</div>
		<?php
        } ?>
	</div>
</div>
