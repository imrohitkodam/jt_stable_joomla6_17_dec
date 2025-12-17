<?php
/**
 * @package     TJCertificate
 * @subpackage  com_tjcertificate
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

HTMLHelper::addIncludePath(JPATH_COMPONENT . '/helpers/html');
HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.multiselect');

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$saveOrder = $listOrder == 'ct.ordering';

if ($saveOrder)
{
	$saveOrderingUrl = 'index.php?option=com_tjcertificate&task=templates.saveOrderAjax';
	HTMLHelper::_('sortablelist.sortable', 'templateList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}

$client = $this->escape($this->state->get('filter.client'));

$clientUrlAppend = '';

if ($this->component)
{
	$clientUrlAppend = '&extension=' . $this->component;
}
elseif (!empty($client))
{
	$clientUrlAppend = '&client=' . $client;
}

if ($this->displayExtension)
{
	$link = 'index.php?option=com_tjcertificate&view=templates&extension=' . $this->displayExtension;
}
else
{
	$link = 'index.php?option=com_tjcertificate&view=templates';
}
?>

<div class="tj-page">
	<div class="row">
		<form action="<?php echo Route::_($link); ?>" method="post" name="adminForm" id="adminForm">
			<?php if (!empty( $this->sidebar))
			{
				?>
				<div id="j-sidebar-container" class="span2">
					<?php echo $this->sidebar; ?>
				</div>
				<div id="j-main-container" class="span10">
				<?php
			}
			else
			{
				?>
				<div id="j-main-container">
				<?php
			}
			// Search tools bar
			echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this));
			?>
			<?php
			if (empty($this->items))
			{
			?>
				<div class="alert alert-info">
					<?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
				</div>
			<?php
			}
			else
			{
				?>
					<table class="table table-striped" id="templateList">
						<thead>
							<tr>
								<th width="1%" class="nowrap center hidden-phone">
									<?php echo HTMLHelper::_('searchtools.sort', '', 'ct.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
								</th>

								<th width="1%" class="center">
									<?php echo HTMLHelper::_('grid.checkall'); ?>
								</th>

								<th width="1%" class="nowrap center">
									<?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'ct.state', $listDirn, $listOrder); ?>
								</th>

								<th>
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_TJCERTIFICATE_CERTIFICATE_TEMPLATE_LIST_VIEW_TITLE', 'ct.title', $listDirn, $listOrder); ?>
								</th>
								<th>
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_TJCERTIFICATE_CERTIFICATE_LIST_VIEW_TYPE', 'ct.client', $listDirn, $listOrder); ?>
								</th>
								<th>
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_TJCERTIFICATE_CERTIFICATE_TEMPLATE_LIST_VIEW_ACCESS', 'ct.is_public', $listDirn, $listOrder); ?>
								</th>
								<th>
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_TJCERTIFICATE_CERTIFICATE_TEMPLATE_LIST_VIEW_CREATED_BY', 'ct.created_by', $listDirn, $listOrder); ?>
								</th>
								<th>
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_TJCERTIFICATE_CERTIFICATE_TEMPLATE_LIST_VIEW_ID', 'ct.id', $listDirn, $listOrder); ?>
								</th>
							</tr>
						</thead>
						<tfoot>
							<tr>
								<td colspan="10">
									<?php echo $this->pagination->getListFooter(); ?>
								</td>
							</tr>
						</tfoot>
						<tbody>
							<?php
							foreach ($this->items as $i => $item)
							{
								$item->max_ordering = 0;
								$ordering   = ($listOrder == 'ct.ordering');

								$canEdit    = ($this->canDo->get('core.edit') && $this->canDo->get('template.edit'));

								$canEditOwn = ($this->canDo->get('template.edit.own') && ($item->created_by == Factory::getUser()->id));

								$canCheckin = ($this->canDo->get('core.edit.state') && $canEditOwn) || $this->canDo->get('template.edit');

								$canChange = ($this->canDo->get('core.edit.state') && $canEditOwn) || $this->canDo->get('template.edit');

								?>
								<tr class="<?php echo $i % 2; ?>" sortable-group-id="<?php echo $item->id; ?>">
								<td class="order nowrap center hidden-phone">
									<?php
									$iconClass = '';

									if (!$canChange)
									{
										$iconClass = ' inactive';
									}
									elseif (!$saveOrder)
									{
										$iconClass = ' inactive tip-top hasTooltip" title="' . HTMLHelper::_('tooltipText', 'JORDERINGDISABLED');
									}
									?>
									<span class="sortable-handler<?php echo $iconClass ?>">
										<span class="icon-menu" aria-hidden="true"></span>
									</span>
									<?php
									if ($canChange && $saveOrder)
									{
									?>
									<input type="text" style="display:none" name="order[]" size="5" value="<?php echo $item->ordering; ?>" class="width-20 text-area-order" />
									<?php
									}
									?>
								</td>
								<td class="center">
									<?php
										if ($canEditOwn || $canEdit)
										{
											echo HTMLHelper::_('grid.id', $i, $item->id);
										}
									?>
								</td>
								<td class="center">
									<?php echo HTMLHelper::_('jgrid.published', $item->state, $i, 'templates.', $canChange, 'cb'); ?>
								</td>
								<td class="has-context">
									<div class="pull-left break-word">
										<?php if ($item->checked_out)
										{
											?>
										<?php echo HTMLHelper::_('jgrid.checkedout', $i, $item->checked_out, $item->checked_out_time, 'templates.', $canCheckin); ?>
										<?php
										}
										?>
										<?php if ($canEdit || $canEditOwn)
										{
											?>
											<a class="hasTooltip" href="
											<?php echo Route::_('index.php?option=com_tjcertificate&task=template.edit&id=' . $item->id . $clientUrlAppend); ?>" title="
											<?php echo Text::_('JACTION_EDIT'); ?>">
											<?php echo $this->escape($item->title); ?></a>
											<?php
											}
											else
											{
												?>
											<span title="<?php echo Text::sprintf('JFIELD_ALIAS_LABEL', $this->escape($item->title)); ?>">
											<?php echo $this->escape($item->title); ?></span>
										<?php
										}?>

									</div>
								</td>
								<td>
									<?php
										$client = str_replace(".", "_", $item->client);
										$client = strtoupper("COM_TJCERTIFICATE_CLIENT_" . $client);
										echo TEXT::_($client);
									?>
								</td>
								<td><?php echo Text::_('COM_TJCERTIFICATE_CERTIFICATE_TEMPLATE_LIST_VIEW_ACCESS_' . $item->is_public); ?></td>
								<td><?php echo $this->escape($item->uname); ?></td>
								<td><?php echo (int) $item->id; ?></td>
							</tr>
							<?php
								}
							?>
						<tbody>
					</table>
					<?php
					}
					?>
					<input type="hidden" name="client" value="<?php echo $client; ?>" />
					<input type="hidden" name="extension" value="<?php echo $this->component; ?>" />
					<input type="hidden" name="task" value="" />
					<input type="hidden" name="boxchecked" value="0" />
					<?php echo HTMLHelper::_('form.token'); ?>
			</div>
		</form>
	</div>
</div>
