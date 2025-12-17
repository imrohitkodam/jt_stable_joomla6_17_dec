<?php
/**
 * @package     TJVendors
 * @subpackage  com_tjvendors
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Session\Session;

HTMLHelper::addIncludePath(JPATH_COMPONENT . '/helpers/');
HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.multiselect');

Factory::getApplication()->getDocument()->getWebAssetManager()->useStyle('searchtools')->useScript('searchtools');

// Import CSS
HTMLHelper::stylesheet('media/com_tjvendor/css/admintjvendors.css');
HTMLHelper::_('script', Uri::root(true) . '/libraries/techjoomla/assets/js/houseKeeping.js');
$document = Factory::getDocument();
$document->addScriptDeclaration("var tjHouseKeepingView='vendors';");

$user      = Factory::getUser();
$userId    = $user->id;
$listOrder = $this->state->get('list.ordering');
$listDirn  = $this->state->get('list.direction');
$canOrder  = $user->authorise('core.edit.state', 'com_tjvendors');
$saveOrder = $listOrder == 'v.ordering';

if ($saveOrder)
{
	$saveOrderingUrl = 'index.php?option=com_tjvendors&task=vendors.saveOrderAjax&tmpl=component' . Session::getFormToken() . '=1';
	HTMLHelper::_('draggablelist.draggable');
}
?>
<script type="text/javascript">
	Joomla.orderTable = function ()
	{
		table = document.getElementById("sortTable");
		direction = document.getElementById("directionTable");
		order = table.options[table.selectedIndex].value;
		if (order != '<?php echo $listOrder; ?>')
		{
			dirn = 'asc';
		}
		else
		{
			dirn = direction.options[direction.selectedIndex].value;
		}

		Joomla.tableOrdering(order, dirn, '');
	};

	jQuery(document).ready(function ()
	{
		jQuery('#clear-search-button').on('click', function ()
		{
			jQuery('#filter_search').val('');
			jQuery('#adminForm').submit();
		});
	});

	window.toggleField = function (id, task, field)
	{
		var f = document.adminForm,
			i = 0, cbx,
			cb = f[ id ];

		if (!cb) return false;

		while (true)
		{
			cbx = f[ 'cb' + i ];

			if (!cbx) break;

			cbx.checked = false;
			i++;
		}

		var inputField   = document.createElement('input');
		inputField.type  = 'hidden';
		inputField.name  = 'field';
		inputField.value = field;
		f.appendChild(inputField);

		cb.checked = true;
		f.boxchecked.value = 1;
		window.submitform(task);

		return false;
	};

	Joomla.submitbutton = function (task)
	{
		if(task == 'vendors.delete')
		{
			var msg = "<?php echo Text::_('COM_TJVENDORS_CONFIRM_TO_DELETE_RECORD'); ?>";

			if (confirm(msg) == true)
			{
				Joomla.submitform(task, document.getElementById('adminForm'));
			}
			else
			{
				return false;
			}
		}
		else
		{
			Joomla.submitform(task, document.getElementById('adminForm'));
		}
	}

	var client = "<?php echo $this->input->get('client', '', 'STRING'); ?>";
</script>
<?php

// Joomla Component Creator code to allow adding non select list filters
if (!empty($this->extra_sidebar))
{
	$this->sidebar .= $this->extra_sidebar;
}

?>
<form action="<?php echo Route::_('index.php?option=com_tjvendors&view=vendors&client=' . $this->input->get('client', '', 'STRING')); ?>" method="post" name="adminForm" id="adminForm">
	<div class="row tjvendor-wrapper">
		<div class="col-md-12 tjvendor-vendors">
			<div id="j-main-container" class="j-main-container">
				<div class="js-stools" role="search">
					<div class="js-stools-container-bar">
						<div class="btn-toolbar">
							<div class="filter-search-bar btn-group">
								<label for="filter_search" class="element-invisible">
									<?php echo Text::_('JSEARCH_FILTER'); ?>
								</label>
								<input
									type="text"
									name="filter_search"
									id="filter_search"
									placeholder="<?php echo Text::_('COM_TJVENDOR_SEARCH_BY_USERNAME'); ?>"
									value="<?php echo $this->escape($this->state->get('filter.search')); ?>"
									title="<?php echo Text::_('JSEARCH_FILTER'); ?>"/>
								<button
									class="btn btn-primary hasTooltip"
									type="submit"
									title="<?php echo Text::_('JSEARCH_FILTER_SUBMIT'); ?>">
									<i class="icon-search"></i>
								</button>
								<button
									class="btn btn-primary hasTooltip"
									id="clear-search-button"
									type="button" title="<?php echo Text::_('JSEARCH_FILTER_CLEAR'); ?>">
									<i class="icon-remove"></i>
								</button>
							</div>
							<div class="ordering-select">
								<div class="js-stools-field-list">
									<label for="limit" class="element-invisible">
										<?php echo Text::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC'); ?>
									</label>
									<?php echo $this->pagination->getLimitBox(); ?>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div>&nbsp;</div>
				<?php
				if(empty($this->items))
				{?>
					<div class="clearfix">&nbsp;</div>
					<div class="alert alert-no-items">
						<?php echo Text::_('COM_TJVENDOR_NO_MATCHING_RESULTS'); ?>
					</div>
				<?php
				}
				else
				{
					?>
					<table class="table" id="vendorList">
						<thead>
							<tr>
								<?php
								if (isset($this->items[0]->ordering))
								{
									?>
									<th width="1%" class="nowrap center hidden-phone">
										<?php echo HTMLHelper::_('grid.sort', '<span class="icon-menu-2"></span>', 'v.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING'); ?>
									</th>
								<?php
								}
								?>
								<th width="1%" class="hidden-phone">
									<?php echo HTMLHelper::_('grid.checkall'); ?>
								</th>

								<?php
								if (isset($this->items[0]->state))
								{
								?>
									<th width="1%" ><?php echo Text::_('JSTATUS');?></th>
								<?php
								}
								?>
								<th width="5%"><?php echo HTMLHelper::_('grid.sort', 'COM_TJVENDORS_VENDORS_VENDOR_TITLE', 'v.`vendor_title`', $listDirn, $listOrder); ?></th>
								<?php
								if ($this->vendorApproval)
								{
								?>
									<th width="2%"><?php echo Text::_('COM_TJVENDORS_VENDORS_VENDOR_APPROVE'); ?></th>
								<?php
								}?>
								<th width="5%"><?php echo Text::_('COM_TJVENDORS_VENDORS_ACTION_MENU'); ?></th>
								<th width="5%" ><?php echo HTMLHelper::_('grid.sort', 'COM_TJVENDORS_VENDORS_ID', 'v.`vendor_id`', $listDirn, $listOrder); ?></th>
							</tr>
						</thead>
						<tbody <?php if ($saveOrder) : ?> class="js-draggable" data-url="<?php echo $saveOrderingUrl; ?>" data-direction="<?php echo strtolower($listDirn); ?>" data-nested="false"<?php endif; ?>>
							<?php
							$options[] = array("type" => Text::_('COM_TJVENDORS_VENDORS_VENDOR_APPROVE'), "value" => "1");
							$options[] = array("type" => Text::_('COM_TJVENDORS_VENDORS_VENDOR_UNAPPROVED'), "value" => "0");

							foreach ($this->items as $i => $item)
							{
								$ordering   = ($listOrder == 'v.ordering');
								$canCreate  = $user->authorise('core.create', 'com_tjvendors');
								$canEdit    = $user->authorise('core.edit', 'com_tjvendors');
								$canCheckin = $user->authorise('core.manage', 'com_tjvendors');
								$canChange  = $user->authorise('core.edit.state', 'com_tjvendors');
								?>
								<tr class="row<?php echo $i % 2; ?>" data-draggable-group="<?php echo $item->vendor_id ?: 'none'; ?>">
								<?php
									if (isset($this->items[0]->ordering))
									{
									?>
										<td class="order nowrap center hidden-phone">
											<?php
											if ($canChange)
											{
												$disabledLabel    = (!$saveOrder) ? Text::_('JORDERINGDISABLED') : '';
												$disableClassName = (!$saveOrder) ? 'inactive tip-top' : '';
											?>
												<span class="sortable-handler hasTooltip <?php echo $disableClassName ?>" title="<?php echo $disabledLabel ?>">
													<i class="icon-ellipsis-v"></i>
												</span>
												<input type="text" style="display:none" name="order[]" size="5" value="<?php echo $item->ordering; ?>" class="width-20 text-area-order "/>
											<?php
											}
											else
											{
											?>
												<span class="sortable-handler inactive">
													<i class="icon-ellipsis-v"></i>
												</span>
											<?php
											}?>
										</td>
									<?php
									}?>

									<td class="hidden-phone">
										<?php echo HTMLHelper::_('grid.id', $i, $item->vendor_id); ?>
									</td>
									<?php
									if (isset($this->items[0]->state))
									{
									?>
										<?php $class = ($canChange) ? 'active' : 'disabled'; ?>
										<td >
											<?php echo HTMLHelper::_('jgrid.published', $item->state, $i, 'vendors.', $canChange, 'cb'); ?>
										</td>
									<?php
									} ?>
									<td>
										<a href="<?php echo Route::_(
											'index.php?option=com_tjvendors&view=vendor&layout=edit&client=' .
											$this->input->get('client', '', 'STRING') . '&vendor_id=' . (int) $item->vendor_id
										);?>">
											<?php echo $this->escape($item->vendor_title); ?>
										</a>
									</td>
									<?php
									if ($this->vendorApproval)
									{
									?>
										<td>
											<?php echo HTMLHELPER::_('select.genericlist', $options, "vendorApprove", 'class="input-medium" size="1" onChange="tjVAdmin.vendors.vendorApprove(' .
												$item->vendor_id . ',this);"', 'value', 'type', $item->approved);
											?>
										</td>
									<?php
									}?>
									<td>
										<a href="<?php echo Route::_('index.php?option=com_tjvendors&view=vendorfees&vendor_id=' . (int) $item->vendor_id) .
										'&client=' . $this->input->get('client', '', 'STRING'); ?>">
											<?php echo Text::_('COM_TJVENDORS_VENDORS_FEE'); ?>
										</a> |
										<a href="<?php echo Route::_('index.php?option=com_tjvendors&view=payouts&vendor_id=' . (int) $item->vendor_id) .
										'&client=' . $this->input->get('client', '', 'STRING'); ?>">
											<?php echo Text::_('COM_TJVENDORS_VENDORS_PAYOUTS'); ?>
										</a> |
										<a href="<?php echo Route::_('index.php?option=com_tjvendors&view=reports&vendor_id=' . (int) $item->vendor_id) .
										'&client=' . $this->input->get('client', '', 'STRING'); ?>">
											<?php echo Text::_('COM_TJVENDORS_VENDORS_REPORTS'); ?>
										</a>
									</td>
									<td>
										<?php echo $item->vendor_id; ?>
									</td>
								</tr>
							<?php
							}?>
						</tbody>
					</table>
				<?php echo $this->pagination->getListFooter(); ?>
				<?php
				}?>
				<input type="hidden" name="task" value=""/>
				<input type="hidden" name="boxchecked" value="0"/>
				<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
				<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>
				<?php echo HTMLHelper::_('form.token'); ?>
			</div>
		</div>
	</div>
</form>
