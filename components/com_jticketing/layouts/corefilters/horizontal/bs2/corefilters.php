<?php
/**
 * @package    JTicketing
 * @author     TechJoomla <extensions@techjoomla.com>
 * @website    http://techjoomla.com*
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Component\ComponentHelper;

$app = Factory::getApplication();
$menuParams = $app->getParams('com_jticketing');

$jticketingParams = ComponentHelper::getParams('com_jticketing');

// Get the online_events value if it's enable then display event filter
$online_events_enable = $jticketingParams->get('enable_online_events', '', 'INT');

require_once JPATH_SITE . '/components/com_jticketing/helpers/event.php';
require_once JPATH_SITE . '/components/com_jticketing/models/events.php';

$jteventHelper         = new jteventHelper;
$JticketingModelEvents = new JticketingModelEvents;

$ordering_options           = $JticketingModelEvents->getOrderingOptions();
$ordering_direction_options = $JticketingModelEvents->getOrderingDirectionOptions();
$creator                    = $JticketingModelEvents->getCreator();
$locations                  = $JticketingModelEvents->getLocation();
$cat_options                = JT::model('events')->getEventCategories();
$url = 'index.php?option=com_jticketing&view=events&layout=default';

// Array of events to show
$events_to_show = array();
$events_to_show[] = HTMLHelper::_('select.option', 'featured', Text::_('COM_JTK_FEATURED_CAMP'));
$events_to_show[] = HTMLHelper::_('select.option', '0', Text::_('COM_JTK_FILTER_ONGOING'));
$events_to_show[] = HTMLHelper::_('select.option', '-1', Text::_('COM_JTK_FILTER_PAST_EVNTS'));

// Event type options array.
$event_types   = array();
$event_types[] = HTMLHelper::_('select.option', '', Text::_('COM_JTK_FILTER_SELECT_EVENT_DEFAULT'));
$event_types[] = HTMLHelper::_('select.option', '0', Text::_('COM_JTK_FILTER_SELECT_EVENT_OFFLINE'));
$event_types[] = HTMLHelper::_('select.option', '1', Text::_('COM_JTK_FILTER_SELECT_EVENT_ONLINE'));

// Price filter
$filterPrice = array(
	HTMLHelper::_('select.option', '', Text::_('COM_JTICKETING_SELECT_PRICE')),
	HTMLHelper::_('select.option', 'free', Text::_('COM_JTICKETING_FREE_EVENTS')),
	HTMLHelper::_('select.option', 'paid', Text::_('COM_JTICKETING_PAID_EVENTS')),
);

// Get itemid
$singleEventItemid = JT::utilities()->getItemId($url);

if (empty($singleEventItemid))
{
	$singleEventItemid = Factory::getApplication()->getInput()->get('Itemid');
}

// Get filter value and set list
$defualtCatid               = $menuParams->get('defualtCatid');
$filter_event_cat           = $app->getUserStateFromRequest('com_jticketing.filter_events_cat', 'filter_events_cat', $defualtCatid, 'INT');
$lists['filter_events_cat'] = $filter_event_cat;

// Ordering option
$default_sort_by_option = $menuParams->get('default_sort_by_option');
$filter_order_Dir       = $menuParams->get('filter_order_Dir');
$filter_order           = $app->getUserStateFromRequest('com_jticketing.filter_order', 'filter_order', $default_sort_by_option, 'string');
$filter_order_Dir       = $app->getUserStateFromRequest('com_jticketing.filter_order_Dir', 'filter_order_Dir', $filter_order_Dir, 'string');

// Get creator and location filter
$filter_creator  = $app->getUserStateFromRequest('com_jticketing' . 'filter_creator', 'filter_creator');
$filter_location = $app->getUserStateFromRequest('com_jticketing' . 'filter_location', 'filter_location');
$online_event = $app->getUserStateFromRequest('com_jticketing' . 'online_events', 'online_events');
$filter_tags = $app->getUserStateFromRequest('com_jticketing' . 'filter_tags', 'filter_tags');
$filter_price = $app->getUserStateFromRequest('com_jticketing' . 'filter_tags', 'filter_price');

// Set all filters in list
$lists['filter_order']     = $filter_order;
$lists['filter_order_Dir'] = $filter_order_Dir;
$lists['filter_creator']   = $filter_creator;
$lists['filter_location']  = $filter_location;
$lists['online_events']    = $online_event;
$lists['filter_tags']      = $filter_tags;
$lists['filter_price']     = $filter_price;
$lists                     = $lists;

// Search and filter
$filter_state            = $app->getUserStateFromRequest('com_jticketing' . 'search', 'search', '', 'string');
$filter_events_to_show   = $app->getUserStateFromRequest('com_jticketing' . 'events_to_show', 'events_to_show');
$lists['search']         = $filter_state;
$lists['events_to_show'] = $filter_events_to_show;
?>

<!--Quick Search-->
<div class="tj-filterhrizontal pull-left span3" >
	<div>
		<h5 class="af-font-bold">
			<?php echo Text::_('COM_JTICKETING_EVENTS_TO_SHOW');?>
		</h5>
	</div>
	<?php
	$selected = $app->get('events_to_show', '', 'string');
	$quick_search_url='index.php?option=com_jticketing&view=events&layout=default&events_to_show=&Itemid='.$singleEventItemid;
	$quick_search_url=Uri::root().substr(Route::_($quick_search_url),strlen(Uri::base(true))+1);
	?>
	<div class="<?php echo empty($selected) ? 'active': ''; ?>">
		<label>
			<input type="radio" class="" name="<?php echo "quick_search[]";?>"
				id="quicksearch" value="<?php echo Text::_('COM_JTICKETING_RESET_FILTER_TO_ALL'); ?>"
				<?php echo empty($selected) ? 'checked': ''; ?>
				onclick='window.location.assign("<?php echo $quick_search_url;?>")'/>
			<?php echo Text::_('COM_JTICKETING_RESET_FILTER_TO_ALL'); ?>
		</label>
	</div>
	<?php
	for ($i = 1; $i < count($events_to_show); $i ++)
	{
		$check = "";
		$selected = $events_to_show[$i]->value;

		$quick_search_url = 'index.php?option=com_jticketing&view=events&layout=default&events_to_show=' . $selected . '&Itemid='.$singleEventItemid;
		$quick_search_url=Uri::root().substr(Route::_($quick_search_url),strlen(Uri::base(true))+1);

		if ($lists['events_to_show'] == $selected)
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
			<label>
				<input type="radio" class=""
				name="<?php echo 'quick_search[]';?>"
				id="quicksearchfields" <?php echo $check;?>
				value="<?php echo $events_to_show[$i]->text; ?>"
				onclick='window.location.assign("<?php echo $quick_search_url;?>")'/>
				<?php echo $events_to_show[$i]->text; ?>
			</label>
		</div>
	<?php
	}
	?>
</div>
<!--Quick Search end here-->

<form action="" method="post" name="adminForm" id="adminForm">
	<!--Event Tpye filter-->
	<?php
	if ($online_events_enable == '1')
	{
		if ($menuParams->get('show_event_filter'))
		{
			?>
			<div class="tj-filterhrizontal pull-left span3" >
				<div>
					<h5 class="af-font-bold"><?php echo Text::_('COM_JTICKETING_EVENT_TYPE');?></h5>
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

	<?php
	if ($menuParams->get('show_creator_filter') || $menuParams->get('show_location_filter'))
	{
		?>
		<div class="tj-filterhrizontal pull-left span3" >
			<div class="tj-filterhrizontal span12">
				<div><h5 class="af-font-bold"><?php echo Text::_('COM_JTICKETING_EVENT_LOCATION');?></h5></div>
				<div>
					<?php echo HTMLHelper::_('select.genericlist', $locations,
					"filter_location", 'class="form-control" size="1" onchange="this.form.submit();" name="filter_location"',
					"value", "text", $lists['filter_location']
					);?>
				</div>
			</div>

			<div class="tj-filterhrizontal span12">
				<div>
					<h5 class="af-font-bold"><?php echo Text::_('COM_JTICKETING_EVENT_CREATOR');?>
					</h5>
				</div>
				<div>
					<?php
						$creator_filter_on = 0;

						if ($menuParams->get('show_creator_filter'))
						{
							$creator_filter_on = 1;

							echo HTMLHelper::_('select.genericlist', $creator, "filter_creator", ' size="1"
								onchange="this.form.submit();" class="form-control" name="filter_creator"',
								"value", "text", $lists['filter_creator']
								);
						}
						else
						{
							$input = Factory::getApplication()->getInput();
							$filter_creator = $input->get('filter_creator', '', 'INT');

							if (!empty($filter_user))
							{
								$creator_filter_on = 0;
							}
						}
					?>
				</div>
			</div>
			<div class="tj-filterhrizontal span12">
				<?php
				if ($menuParams->get('show_tags_filter') == 'advanced')
				{
					?>
					<h5 class="af-font-bold">
						<?php echo Text::_('COM_JTICKETING_EVENT_TAGS');?>
					</h5>
					<div>
						<select name="filter_tags" id="filter_tags" onchange="this.form.submit();" class="form-control">
							<option value=""><?php echo Text::_('JOPTION_SELECT_TAG'); ?></option>
							<?php echo HTMLHelper::_('select.options', HTMLHelper::_('tag.options', true, true), 'value', 'text', $lists['filter_tags']); ?>
						</select>
					</div>
					<?php
				}
					?>
			</div>

			<div class="tj-filterhrizontal span12">
				<?php
				if ($menuParams->get('show_price_filter') == 'advanced')
				{
					?>
					<h5 class="af-font-bold">
						<?php echo Text::_('COM_JTICKETING_EVENT_PRICE');?>
					</h5>
					<div>
						<select name="filter_price" id="filter_price" onchange="this.form.submit();" class="form-control">
							<?php echo HTMLHelper::_('select.options', $filterPrice, 'value', 'text', $lists['filter_price']); ?>
						</select>
					</div>
					<?php
				}
					?>
			</div>
		</div>
		<div class="clearfix"></div>
	<?php
	}
	?>

	<input type="hidden" name="option" value="com_jticketing" />
	<input type="hidden" name="view" value="events" />
	<input type="hidden" name="layout" value="default" />
</form>
