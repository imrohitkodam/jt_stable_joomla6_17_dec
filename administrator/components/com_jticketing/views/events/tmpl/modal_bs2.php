<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2017 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\String\StringHelper;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Toolbar\ToolbarHelper;

$document = Factory::getDocument();
ToolbarHelper::preferences( 'com_jticketing' );
HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.multiselect');
// Joomla 6: formbehavior.chosen removed - using native select

$core_js = Uri::root() . 'media/system/js/core.js';

$flg = 0;

foreach ($document->_scripts as $name => $ar)
{
	if ($name == $core_js)
	{
		$flg = 1;
	}
}

if ($flg == 0)
{
	echo "<script type='text/javascript' src='" . $core_js . "'></script>";
}

$app = Factory::getApplication();

if ($app->isClient("site"))
{
	Session::checkToken('get') or die(Text::_('JINVALID_TOKEN'));
}

$function  = $app->getInput()->getCmd('function', 'jSelectBook_');
$listOrder = $this->state->get('list.ordering');
$listDirn = $this->state->get('list.direction');

$fieldView = $app->getInput()->getInt('fieldView');

// Special case for the search field tooltip.
$searchFilterDesc = $this->filterForm->getFieldAttribute('search', 'description', null, 'filter');
HTMLHelper::_('bootstrap.tooltip', '#filter_search', array('title' => Text::_($searchFilterDesc), 'placement' => 'bottom'));

if($this->issite)
{
?>
	<div class="well" >
		<div class="alert alert-error">
			<span ><?php echo Text::_('COM_JTICKETING_NO_ACCESS_MSG'); ?> </span>
		</div>
	</div>
<?php
		return false;
}?>
<div>
	<form action="index.php?fieldView=<?php echo $fieldView; ?>" method="post" name="adminForm" id="adminForm">
		<?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
		<?php
		if(empty($this->items))
		{?>
			<div class="alert alert-info">
				<span ><?php echo Text::_('COM_JTICKETING_NO_MATCHING_RESULTS'); ?> </span>
			</div>
		<?php
		}
		else
		{
		?>
		<!-- <div class="row">
			<div class="well">
				<strong><?php echo Text::_("COM_JTICKETING_ALL_EVENTS_MENU");?></strong>
			</div>
		</div> -->
		<table class="table table-striped">
			<thead>
				<tr>
					<th>
						<?php echo HTMLHelper::_('grid.sort',  'COM_JTICKETING_EVENTS_TITLE', 'a.title', $listDirn, $listOrder); ?>
					</th>
					<th>
						<?php echo HTMLHelper::_('grid.sort',  'COM_JTICKETING_EVENTS_CATEGORY', 'a.catid', $listDirn, $listOrder); ?>
					</th>
					<th>
						<?php echo HTMLHelper::_('grid.sort',  'COM_JTICKETING_EVENTS_STARTDATE', 'a.startdate', $listDirn, $listOrder); ?>
					</th>
					<th>
						<?php echo HTMLHelper::_('grid.sort',  'COM_JTICKETING_EVENTS_ENDDATE', 'a.enddate', $listDirn, $listOrder); ?>
					</th>
					<th>
						<?php echo HTMLHelper::_('grid.sort',  'COM_JTICKETING_EVENTS_LOCATION', 'a.location', $listDirn, $listOrder); ?>
					</th>
					<?php
					if (isset($this->items[0]->id))
					{
					?>
						<th>
							<?php echo HTMLHelper::_('grid.sort', 'C_ID', 'a.id', $listDirn, $listOrder); ?>
						</th>
					<?php
					}?>
				</tr>
			</thead>
			<tbody>
				<?php
				foreach ($this->items as $eventData)
				{
				?>
					<tr>
						<td>
							<a <?php if ($fieldView) { ?> class="pointer button-select" data-user-value="<?php echo $eventData->id; ?>" data-user-name="<?php echo $this->escape($eventData->title); ?>" <?php } else { ?> class="pointer" <?php } ?>  onclick="if (window.parent) window.parent.<?php echo $this->escape($function);?>('<?php echo $eventData->id; ?>', '<?php echo $this->escape(addslashes($eventData->title)); ?>','<?php echo Uri::root().substr(Route::_('index.php?option=com_jticketing&view=event&layout=default&id='.$eventData->id.'&Itemid='.$this->singleEventItemid),strlen(Uri::base(true))+1);?>');">
								<?php echo $this->escape($eventData->title);?>
							</a>
						</td>
						<td>
							<?php echo $eventData->catid; ?>
						</td>
						<td>
							<?php
								$statrtDate = Factory::getDate($eventData->startdate)->Format(Text::_('COM_JTICKETING_DATE_FORMAT_SHOW_AMPM'));
								$startDate =  HTMLHelper::date($eventData->startdate, Text::_('COM_JTICKETING_DATE_FORMAT_SHOW_AMPM'), true);
								echo $startDate;
							?>
						</td>
						<td>
							<?php $endDate =  Factory::getDate($eventData->enddate)->Format(Text::_('COM_JTICKETING_DATE_FORMAT_SHOW_AMPM'));
								$endDate =  HTMLHelper::date($eventData->enddate, Text::_('COM_JTICKETING_DATE_FORMAT_SHOW_AMPM'), true);
								echo $endDate;
							?>
						</td>
						<td>
							<?php
							if ($eventData->online_events == "1")
							{
								if ($eventData->online_provider == "plug_tjevents_adobeconnect")
								{
									echo Text::_('COM_JTICKETING_ADOBECONNECT_PLG_NAME') . " - " . $eventData->name;
								}
								else
								{
									echo StringHelper::ucfirst($eventData->online_provider) . " - " . $eventData->name;
								}
							}
							elseif($eventData->online_events == "0")
							{
								if ($eventData->venue != "0")
								{
									echo $eventData->name . " - " . $eventData->address;
								}
								else
								{
									echo $eventData->location;
								}
							}
							else
							{
								echo "-";
							}?>

						</td>
						<td><?php echo (int) $eventData->id; ?></td>
					</tr>
				<?php
				}
				?>
			</tbody>
		</table>
		<?php
		}
		?>
		<input type="hidden" name="option" value="com_jticketing" />
		<input type="hidden" name="view" value="events" />
		<input type="hidden" name="layout" value="modal" />
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="tmpl" value="component" />
		<input type="hidden" id="controller" name="controller" value="events" />
	</form>
</div>
