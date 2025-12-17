<?php
/**
 * @package    JTicketing
 * @author     TechJoomla <extensions@techjoomla.com>
 * @website	http://techjoomla.com*
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Component\ComponentHelper;

$app        = Factory::getApplication();
$menuParams = $app->getParams('com_jticketing');

$jticketingParams = ComponentHelper::getParams('com_jticketing');

// Get the online_events value if it's enable then display event filter
$onlineEventsEnable         = $jticketingParams->get('enable_online_events', '', 'INT');
$JticketingModelEvents      = JT::model('events');
$creator                    = $JticketingModelEvents->getCreator();
$locations                  = $JticketingModelEvents->getLocation();
$days                       = $JticketingModelEvents->getDayOptions();

// Event type options array.
$event_types   = array();
$event_types[] = HTMLHelper::_('select.option', '', Text::_('COM_JTK_FILTER_SELECT_EVENT_DEFAULT'));
$event_types[] = HTMLHelper::_('select.option', '0', Text::_('COM_JTK_FILTER_SELECT_EVENT_OFFLINE'));
$event_types[] = HTMLHelper::_('select.option', '1', Text::_('COM_JTK_FILTER_SELECT_EVENT_ONLINE'));

// Get itemid
$url               = 'index.php?option=com_jticketing&view=events&layout=default';
$singleEventItemid = JT::utilities()->getItemId($url);

if (empty($singleEventItemid))
{
	$singleEventItemid = Factory::getApplication()->getInput()->get('Itemid');
}

// Get filter value and set list
$defualtCatid			   = $app->getInput()->get('catid');
$filter_event_cat		   = $app->getUserStateFromRequest('com_jticketing.filter_events_cat', 'filter_events_cat', $defualtCatid, 'INT');
$lists['filter_events_cat'] = $filter_event_cat;

// Ordering option
$default_sort_by_option = $menuParams->get('default_sort_by_option');
$filter_order_Dir	   = $menuParams->get('filter_order_Dir');
$filter_order		   = $app->getUserStateFromRequest('com_jticketing.filter_order', 'filter_order', $default_sort_by_option, 'string');
$filter_order_Dir	   = $app->getUserStateFromRequest('com_jticketing.filter_order_Dir', 'filter_order_Dir', $filter_order_Dir, 'string');

// Get creator and location filter
$filter_creator  = $app->getUserStateFromRequest('com_jticketing' . 'filter_creator', 'filter_creator');
$filter_location = $app->getUserStateFromRequest('com_jticketing' . 'filter_location', 'filter_location');
$online_event    = $app->getUserStateFromRequest('com_jticketing' . 'online_events', 'online_events');
$filter_day      = $app->getUserStateFromRequest('com_jticketing' . 'filter_day', 'filter_day');
$filter_tags     = $app->getUserStateFromRequest('com_jticketing' . 'filter_tags', 'filter_tags');
$filter_price    = $app->getUserStateFromRequest('com_jticketing' . 'filter_tags', 'filter_price');

// Set all filters in list
$lists['filter_order']	 = $filter_order;
$lists['filter_order_Dir'] = $filter_order_Dir;
$lists['filter_creator']   = $filter_creator;
$lists['filter_location']  = $filter_location;
$lists['online_events']	= $online_event;
$lists['filter_day']	   = $filter_day;
$lists['filter_tags']	  = $filter_tags;
$lists['filter_price']	 = $filter_price;
$lists					 = $lists;

// Search and filter
$filter_state			= $app->getUserStateFromRequest('com_jticketing' . 'search', 'search', '', 'string');
$filter_events_to_show   = $app->getUserStateFromRequest('com_jticketing' . 'events_to_show', 'events_to_show');
$lists['search']		 = $filter_state;
$lists['events_to_show'] = $filter_events_to_show;
?>
	<!--Event Tpye filter-->
	<?php
	if ($onlineEventsEnable == '1')
	{
	    if ($menuParams->get('show_event_filter') == 'advanced')
		{
			?>
			<div class="tj-filterhrizontal pull-left col-xs-12 col-sm-3 col-md-2 eventFilter__ht af-mb-5" >
				<div>
					<div>
						<?php echo HTMLHelper::_('select.genericlist', $event_types, "online_events", 'class="form-control" size="1" onchange="this.form.submit();"
							name="online_events"', "value", "text", $lists['online_events']
							);
						?>
					</div>
				</div>
			</div>
		<?php
		}
	}

	?>
	<!--Event Tpye filter end-->
	<div class="tj-filterhrizontal" >
		<?php
		// Location
		if ($menuParams->get('show_location_filter') == 'advanced')
		{ ?>
			<div class="tj-filterhrizontal col-xs-12 col-sm-3 col-md-2 af-mb-10">
				<div>
					<?php echo HTMLHelper::_('select.genericlist', $locations, "filter_location",
						'class="form-control" size="1" onchange="this.form.submit();" name="filter_location"',
						"value", "text", $lists['filter_location']
					);?>
				</div>
			</div>
		<?php
		}
		?>
		<?php
		if ($menuParams->get('show_date_filter') == 'advanced')
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
		?><div class="col-xs-12 col-sm-3 col-md-2 af-mb-5">
		<?php
		// Date
		echo HTMLHelper::_('select.genericlist', $days,
		"filter_day", 'class="form-control" size="1" onchange="jtSite.events.calendarSubmit(this.value, this);"
		name="filter_day"', "value", "text", $lists['filter_day']
		);

		?>

		<?php if (count($pickDate) === 2)
			{ ?>

		<button type="button" class="btn btn-transparent jt-btn-clear" onclick="jtSite.events.resetCalendar(this);">
			<i class="fa fa-times" aria-hidden="true"></i>
		</button>
		<?php
		}
		?>
		</div>
		<?php
		}
		?>

		<?php
		// Creator
		if ($menuParams->get('show_creator_filter') == 'advanced')
		{ ?>
    		<div class="tj-filterhrizontal col-xs-12 col-sm-3 col-md-2 af-mb-10">
    			<?php
    			echo HTMLHelper::_('select.genericlist', $creator, "filter_creator", ' size="1"
    				onchange="this.form.submit();" class="form-control" name="filter_creator"', "value",
    				"text", $lists['filter_creator']
    			);?>
			</div>
		<?php
		}
		else
		{
			$input = Factory::getApplication()->getInput();
			$filter_creator = $input->get('filter_creator', '', 'INT');
		}
		?>
		<?php
		// Price
		if ($menuParams->get('show_price_filter') == 'advanced')
		{
			$options = array(
			HTMLHelper::_('select.option', '', Text::_('COM_JTICKETING_SELECT_PRICE')),
			HTMLHelper::_('select.option', 'free', Text::_('COM_JTICKETING_FREE_EVENTS')),
			HTMLHelper::_('select.option', 'paid', Text::_('COM_JTICKETING_PAID_EVENTS')),
		);
		?>
		<div class="tj-filterhrizontal col-xs-12 col-sm-3 col-md-2 af-mb-10">
			<div>
				<select name="filter_price" id="filter_price" onchange="this.form.submit();" class="form-control">
					<?php echo HTMLHelper::_('select.options', $options, 'value', 'text', $lists['filter_price']); ?>
				</select>
			</div>
		</div>
		<div class="visible-xs col-xs-12 af-mt-5">
			<a class="btn btn-primary pull-right" onclick="jQuery('.form-control').val(''); this.form.submit();">
			<i class="fa fa-repeat af-mr-5" aria-hidden="true"></i><?php echo Text::_('COM_JTICKETING_CLEAR_SEARCH');?>
			</a>
		</div>
		<?php
		// Tags
		if ($menuParams->get('show_tags_filter') == 'advanced')
		{
		?>
		<div class="tj-filterhrizontal col-xs-12 col-sm-3 col-md-2 af-mb-10">
			<div>
				<select name="filter_tags" id="filter_tags" onchange="this.form.submit();" class="form-control">
					<option value=""><?php echo Text::_('JOPTION_SELECT_TAG'); ?></option>
					<?php echo HTMLHelper::_('select.options', HTMLHelper::_('tag.options', array('filter.published' => array(1)), true), 'value', 'text', $lists['filter_tags']); ?>

				</select>
			</div>
		</div>
		<?php
		}
		?>

		<div class="visible-xs col-xs-12 af-mt-5">
			<a class="btn btn-primary pull-right" onclick="jQuery('.form-control').val(''); this.form.submit();">
			<i class="fa fa-repeat af-mr-5" aria-hidden="true"></i><?php echo Text::_('COM_JTICKETING_CLEAR_SEARCH');?>
			</a>
		</div>

		<?php
		}
			?>
	</div>
