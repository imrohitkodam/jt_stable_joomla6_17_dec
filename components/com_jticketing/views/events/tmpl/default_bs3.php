<?php
/**
 * @package    JTicketing
 * @author     TechJoomla <extensions@techjoomla.com>
 * @website    http://techjoomla.com*
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Factory;

// Joomla 6: formbehavior.chosen removed - using native select
HTMLHelper::_('bootstrap.tooltip');

require_once JPATH_ADMINISTRATOR . '/components/com_jticketing/helpers/jticketing.php';
$canDo = JticketingHelper::getActions();

// Import helper for declaring language constant
if (file_exists(JPATH_SITE . '/components/com_jticketing/helpers/main.php')) { require_once JPATH_SITE . '/components/com_jticketing/helpers/main.php'; }

// Call helper function
JticketingCommonHelper::getLanguageConstant();

// Native Event Manager
if ($this->integration != 2 and $this->integration != 4)
{
    ?>
	<div class="alert alert-info alert-help-inline">
		<?php	echo Text::_('COMJTICKETING_INTEGRATION_NATIVE_NOTICE');?>
	</div>
	<?php
	return false;
}

echo '<div id="fb-root"></div>';
$fblike_tweet = Uri::root() . 'media/com_jticketing/js/fblike.js';
echo "<script type='text/javascript' src='" . $fblike_tweet . "'></script>";

$document = Factory::getDocument();
HTMLHelper::_('script', 'media/com_jticketing/vendors/js/moment.min.js');
HTMLHelper::_('script', 'media/com_jticketing/vendors/js/daterangepicker.min.js');
HTMLHelper::_('stylesheet', 'media/com_jticketing/vendors/css/daterangepicker.min.css');

$launch_event_url = Route::_('index.php?option=com_jticketing&view=eventform&Itemid=' . $this->create_event_itemid, false);
?>

<div  id="jtwrap" class="tjBs3">
	<?php if ($this->params->get('show_page_heading', 1)) : ?>
			<h1><?php echo $this->PageTitle;?></h1>
	<?php endif; ?>
	<div class="custom-form-elements mt-5">
	<form action="" method="post" name="adminForm" id="adminForm" class="">
			<div class="row align-items-center">
				<?php
					if ($this->params->get('show_search_filter'))
					{ ?>
					<span class="col-sm-12 col-md-4 mt-2">
								<span class="event__separation">
									<span class="events__search input-group" id="searchFilterInputBox">
										<input
											type="text"
											placeholder="<?php echo Text::_('COM_JTICKETING_ENTER_EVENTS_NAME'); ?>"
											name="search"
											id="search"
											value="<?php echo htmlspecialchars($srch = ($this->lists['search'])?$this->lists['search']:''); ?>"
											class="form-control af-bg-faded"
											onchange="this.form.submit();"/>

											<span class="input-group-text">
											<a id="searchEventBtn"  href="javascript:void(0)" onclick="jtSite.events.toggleDiv('searchFilterInputBox');" title="<?php echo Text::_('COM_JTICKETING_SEARCH_EVENT')?>">
													<i class="fa fa-search"></i>
												</a>
											</span>
											<span class="clear-sepration">
												<button
												type="reset"
												href="javascript:void(0)"
												onclick="document.getElementById('search').value='';this.form.submit();"
												class="btn btn-info"
												title="<?php echo Text::_('COM_JTICKETING_CLEAR_SEARCH')?>">
												<i class="fa fa-remove"></i>
												</button>
											</span>
									</span>
								</span>
							</span>
					<div class="col-3 visible-xs af-p-0 events__mobilefilter--buttons">
						<button class="" onclick="jtSite.events.displayMobileFilter();" title="<?php echo Text::_('COM_JTICKETING_FILTER_EVENT')?>">
							<i class="fa fa-filter" aria-hidden="true"></i>
						</button>
						<button type="button" class="btn btn-transparent" onclick="jQuery('.form-control').val(''); this.form.submit();">
								<i class="fa fa-close" aria-hidden="true"></i>
						</button>
					</div>
					<?php
					} ?>
					
					<div class="col-12 col-sm-12 col-md-8">
					<ul class="pull-right list-unstyled events af-d-flex align-items-center events__options">
					<?php
						if ($canDo->{'core.create'} && count($this->items)): ?>
							<li class="events__create event__separation ">
								<a href="<?php echo $launch_event_url;?>" title="<?php echo Text::_('COM_JTICKETING_EVENTS_CREATE_NEW_EVENT')?>" class="btn btn-info">
									<div class="input-group mb-1 d-flex align-items-center">
										<i class="fa fa-paper-plane" aria-hidden="true"></i>
										<span class="ms-2 d-none d-sm-inline"> <?php echo Text::_('COM_JTICKETING_EVENTS_CREATE_EVENT');?></span>
									</div>
								</a>
							</li>
						<?php
						endif;
						?>
						<?php
							if ($this->params->get('show_sorting_options'))
							{
							?>
							<li class="sort-result event__separation">
								<?php
									echo HTMLHelper::_('select.genericlist', $this->ordering_options, "filter_order", 'size="1" onchange="this.form.submit();" class="form-control" name="filter_order"',"value", "text", $this->lists['filter_order']
									);
								?>
							</li>
						<?php
							}
						?>
						<?php if ($this->params->get('show_filters') === 'advanced' || $this->params->get('show_filters') === 'both') : ?>
							<li class="hidden-xs">
								<a class="" id="displayFilter" href="javascript:void(0)" onclick="jtSite.events.toggleDiv('displayFilterText');" title="<?php echo Text::_('COM_JTICKETING_FILTER_EVENT')?>">
									<i class="fa fa-filter" aria-hidden="true"></i>
								</a>
							</li>
						<?php endif;?>
						<?php
						if ($this->params->get('show_search_filter') || $this->params->get('show_sorting_options') || 
						$this->params->get('show_filters') != 'none')
						{?>
							<li class="">
								<button type="button" class="btn btn-transparent af-p-0 height-auto hidden-xs" onclick="jQuery('.form-control').val(''); this.form.submit();">
									<i class="fa fa-close" aria-hidden="true"></i>
								</button>
							</li>
						<?php 
						}?>
						<li class="hidden-xs">
							<?php echo $this->pagination->getLimitBox(); ?>
						</li>
					</ul>
				</div>
			</div>
			<div class="row mt-2" id="mobileFilter">
			<?php
			if ($this->params->get('show_filters') === 'both' || $this->params->get('show_filters') === 'basic')
    		{
				// Location
				if ($this->params->get('show_location_filter') == 'basic')
    			{
    				?>
    				<div class="col-xs-12 col-sm-3 col-md-2 af-mb-10">
    				<?php
    					require_once JPATH_SITE . '/components/com_jticketing/models/events.php';

    					$jticketingModelEvents = new JticketingModelEvents;
    					$location = $jticketingModelEvents->getLocation();
    					echo HTMLHelper::_('select.genericlist', $location, "filter_location", 'class="form-control" size="1"
    						onchange="this.form.submit();"
    						name="filter_location"', "value", "text",
    						$this->lists['filter_location']
    					);
    				?>
    				</div>
    				<?php
				}
				// Date
    			if ($this->params->get('show_date_filter') == 'basic')
    			{
    				// In case of a custom date is selected format the Date as startDate - endDate
    				$pickDate = explode("-", $this->lists['filter_day'] ? $this->lists['filter_day'] : '');

    				if (count($pickDate) === 2)
    				{
    					$pickFormatDate = HTMLHelper::date($pickDate [0], 'M d') . ' - ' . $endDate   = HTMLHelper::date($pickDate[1], 'M d');

    					foreach ($this->days_options as $each)
    					{
    						if ($each->value === 'custom_date')
    						{
    							$each->text = $pickFormatDate;
    							$each->value = $this->lists['filter_day'];
    						}
    					}
    				}
    				?>
    				<div class="col-xs-12 col-sm-3 col-md-2 af-mb-10">
						<?php
							echo HTMLHelper::_('select.genericlist', $this->days_options, "filter_day", 'size="1" onchange="jtSite.events.calendarSubmit(this.value, this);"
								class="form-control calendar" name="filter_day"', "value", "text", $this->lists['filter_day']
							);
						?>

						<?php if (count($pickDate) === 2)
						{ ?>
							<button type="button" class="btn btn-transparent jt-btn-clear" onclick="jtSite.events.resetCalendar(this);">
								<i class="fa fa-times" aria-hidden="true"></i>
							</button>
						<?php
						} ?>
					</div>
				<?php
				}
				// Creator
    			if ($this->params->get('show_creator_filter') === 'basic')
    			{ ?>
    				<div class="tj-filterhrizontal col-xs-12 col-sm-3 col-md-2 af-mb-10">
    					<?php
    					echo HTMLHelper::_('select.genericlist', $this->creator, "filter_creator", ' size="1"
    						onchange="this.form.submit();" class="form-control" name="filter_creator"', "value",
    						"text", $this->lists['filter_creator']
    					);?>
    				</div>
    			<?php
				}
				// Price
    			if ($this->params->get('show_price_filter') == 'basic')
    			{
    				?>
    				<div class="col-xs-12 col-sm-3 col-md-2 af-mb-10">
    					<select name="filter_price" id="filter_price" onchange="this.form.submit();" class="form-control">
    						<?php echo HTMLHelper::_('select.options', $this->filterPrice, 'value', 'text', $this->state->get('filter.price')); ?>
    					</select>
    				</div>
    				<?php
				}
				// Category
    			if ($this->params->get('show_category_filter') == 'basic')
    			{
    				?>
    				<div class="col-xs-12 col-sm-3 col-md-2 af-mb-10">
    					<select name="filter_events_cat" id="filter_events_cat" onchange="this.form.submit();" class="form-control">
    						<?php echo HTMLHelper::_('select.options', $this->cat_options, 'value', 'text', $this->lists['filter_events_cat']); ?>
    					</select>
    				</div>
    				<?php
    			}
				// Tags
    			if ($this->params->get('show_tags_filter') == 'basic')
    			{
    				?>
    				<div class="col-xs-12 col-sm-3 col-md-2 af-mb-10">
    					<select name="filter_tags" id="filter_tags" onchange="this.form.submit();" class="form-control">
    						<option value=""><?php echo Text::_('JOPTION_SELECT_TAG'); ?></option>
    						<?php echo HTMLHelper::_('select.options', HTMLHelper::_('tag.options', true, true), 'value', 'text', $this->state->get('filter.tags')); ?>
    					</select>
    				</div>
    				<?php
    			} ?>
				<?php
				// Event
    			if ($this->params->get('show_event_filter') === 'basic' && $this->onlineEventsEnabled)
    			{
    				?>
					<div class="tj-filterhrizontal pull-left col-xs-12 col-sm-3 col-md-2 eventFilter__ht af-mb-10 w-150" >
					<?php echo HTMLHelper::_('select.genericlist', $this->eventTypes, "online_events", 'class="form-control" size="1" onchange="this.form.submit();"
						name="online_events"', "value", "text", $this->lists['online_events']
						);
						?>
					</div>
					<?php
    			}
    			?>
			<?php
    		}
			?>
				<div class="visible-xs text-center col-xs-12 af-font-bold af-mb-10">
					<?php if ($this->params->get('show_filters') === 'advanced' || $this->params->get('show_filters') === 'both') : ?>
						<a class="" id="mobiledisplayFilter" href="javascript:void(0)" onclick="jtSite.events.toggleDiv('displayFilterText');" title="<?php echo Text::_('COM_JTICKETING_FILTER_EVENT')?>">
							<i class="fa af-mr-5 fa-plus" aria-hidden="true"></i>
							<?php echo Text::_('COM_JTICKETING_FILTERS_MORE_LBL')?>
						</a>
					<?php endif;?>
				</div>
			</div>
			<div class="clearfix"></div>
			<div class="events__filter hide" id="displayFilterText">
				<div class="row">
					<?php
						echo $this->loadTemplate("filters_" . JTICKETING_LOAD_BOOTSTRAP_VERSION);
					?>
				</div>
			</div>
			<input type="hidden" name="option" value="com_jticketing" />
			<input type="hidden" name="view" value="events" />
			<input type="hidden" name="layout" value="default" />
		</form>
		</div>
		<hr class="af-mt-10 af-mb-0 af-pb-20">
		<?php
		if (empty($this->items))
		{
			?>
			<div class="row">
				<div class="col-xs-12">
					<div class="well">
					    <strong><?php echo Text::_('COM_JT_NOT_FOUND_EVENT');?></strong>
					    <p>
					        <a href="<?php echo $launch_event_url;?>" title="<?php echo Text::_('COM_JTICKETING_EVENTS_CREATE_NEW_EVENT')?>">
								<i class="fa fa-paper-plane-o" aria-hidden="true"></i>
								<span class="hidden-xs"><?php echo Text::_('COM_JTICKETING_EVENTS_CREATE_EVENT');?></span>
							</a>
					    </p>
					</div>
				</div>
			</div>
			<?php
		}
		else
		{
			?>
			<div class="row justify-content-center">
				<?php echo $this->loadTemplate("pin_". JTICKETING_LOAD_BOOTSTRAP_VERSION);?>
			</div>
			<div class="pager pull-right">
				<?php echo $this->pagination->getPagesLinks(); ?>
			</div>
			<?php
		}?>
</div>
<script>
	jtSite.events.init();
</script>
