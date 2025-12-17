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

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;

$doc = Factory::getDocument();

$style     = "
.table th {
	white-space: inherit !important;
}";
$doc->addStyleDeclaration($style);

$options = array();
$options['relative'] = true;

HTMLHelper::addIncludePath(JPATH_COMPONENT . '/helpers/html');
HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.multiselect');
HTMLHelper::_('bootstrap.renderModal', 'a.modal');
HTMLHelper::script('media/vendor/jquery/js/jquery.min.js');
HTMLHelper::script('com_tjcertificate/certificateImage.min.js', $options);
HTMLHelper::StyleSheet('media/com_tjcertificate/css/tjCertificate.css');

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));

$app = Factory::getApplication();
PluginHelper::importPlugin('content');
?>

<div class="tj-page">
	<div class="row">
		<form action="<?php echo Route::_('index.php?option=com_tjcertificate&view=certificates'); ?>" method="post" name="adminForm" id="adminForm">

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
					<table class="table table-striped" id="certificateList">
						<thead>
							<tr>
								<th width="1%" class="center">
									<?php echo HTMLHelper::_('grid.checkall'); ?>
								</th>

								<th width="1%" class="nowrap center">
									<?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'ci.state', $listDirn, $listOrder); ?>
								</th>
								<th>
									<?php echo Text::_('COM_TJCERTIFICATE_CERTIFICATE_LIST_VIEW_CERTIFICATE_ID'); ?>
								</th>
								<th>
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_TJCERTIFICATE_CERTIFICATE_LIST_VIEW_USER_NAME', 'ci.user_id', $listDirn, $listOrder); ?>
								</th>

								<?php if ($this->isAgencyEnabled) { ?>
									<th>
										<?php echo Text::_('COM_TJCERTIFICATE_CERTIFICATE_LIST_VIEW_ORG_NAME'); ?>
									</th>
								<?php } ?>

								<th>
									<?php echo Text::_('COM_TJCERTIFICATE_CERTIFICATE_TYPE_NAME'); ?>
								</th>
								<th>
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_TJCERTIFICATE_CERTIFICATE_LIST_VIEW_ISSUED_DATE', 'ci.issued_on', $listDirn, $listOrder); ?>
								</th>
								<th>
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_TJCERTIFICATE_CERTIFICATE_LIST_VIEW_EXPIRY_DATE', 'ci.expired_on', $listDirn, $listOrder); ?>
								</th>
								<th>
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_TJCERTIFICATE_CERTIFICATE_LIST_VIEW_TYPE', 'ci.client', $listDirn, $listOrder); ?>
								</th>
								<th>
									<?php echo Text::_('COM_TJCERTIFICATE_CERTIFICATE_URL'); ?>
								</th>
								<th>
									<?php echo Text::_('JGLOBAL_PREVIEW');?>
								</th>
								<th>
								</th>
								<th>
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_TJCERTIFICATE_CERTIFICATE_LIST_VIEW_ID', 'ci.id', $listDirn, $listOrder); ?>
								</th>
							</tr>
						</thead>
						<tbody>
							<?php
							foreach ($this->items as $i => $item)
							{
								$certificateObj = TJCERT::Certificate($item->id);
								$data = $app->triggerEvent('onGetCertificateClientData', array($item->client_id, $item->client));
								$item->max_ordering = 0;

								$canEdit    = $this->canDo->get('core.edit');

								$canCheckin = $this->canDo->get('core.edit.state');

								$canChange  = $this->canDo->get('core.edit.state');

								$canEditOwn = $this->canDo->get('core.edit.own');
								?>
								<tr class="<?php echo $i % 2; ?>" sortable-group-id="<?php echo $item->id; ?>">
								<td class="center">
									<?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
								</td>
								<td class="center">
									<?php echo HTMLHelper::_('jgrid.published', $item->state, $i, 'certificates.', $canChange, 'cb'); ?>
								</td>
								<td class="has-context">
									<div class="pull-left break-word">
										<?php if ($canEdit || $canEditOwn)
										{
										?>
											<a href="
											<?php echo Route::_('index.php?option=com_tjcertificate&view=certificate&layout=edit&id=' . (int) $item->id . '&extension=' . $this->component, false);?>">
											<?php echo $this->escape($item->unique_certificate_id); ?></a>
											<?php
										}
										else
										{
										?>
											<span title="<?php echo Text::sprintf('JFIELD_ALIAS_LABEL', $this->escape($item->unique_certificate_id)); ?>">
											<?php echo $this->escape($item->unique_certificate_id); ?></span>
										<?php
										}?>

									</div>
								</td>
								<td>
									<?php
									$userName = '-';

										if (!empty($item->client_issued_to_name))
										{
											$userName = $this->escape($item->client_issued_to_name);
										}
										elseif (!empty($item->uname))
										{
											$userName = $this->escape($item->uname);
										}

										echo $userName;
										?>
								</td>
								<?php 
								if ($this->isAgencyEnabled)
								{ ?>
									<td><?php echo $item->title; ?></td>
								<?php 
								} ?>
								<td>
									<?php
										if ($item->is_external)
										{
											echo $item->name;									
										}
										else
										{
											echo ((isset($data[0]) && isset($data[0]->title)) ? $data[0]->title : "-");
										}
									?>
								</td>
								<td><?php echo $certificateObj->getFormatedDate($item->issued_on); ?></td>
								<td><?php
									if (!empty($item->expired_on) && $item->expired_on != '0000-00-00 00:00:00')
									{
										echo $certificateObj->getFormatedDate($item->expired_on);
									}
									else
									{
										echo '-';
									}
									?>
								</td>
								<td>
									<?php
										$client = str_replace(".", "_", $item->client);
										$client = strtoupper("COM_TJCERTIFICATE_CLIENT_" . $client);
										echo TEXT::_($client);
									?>
								</td>
								<td>
									<?php
									$utcNow = Factory::getDate()->toSql();
									$link = "";

									if ($item->expired_on > $utcNow || $item->expired_on == '0000-00-00 00:00:00')
									{
										// Get TJcertificate url for display certificate
										$urlOpts = array ('absolute' => true);

										if ($item->is_external)
										{
											$link = $certificateObj->getUrl($urlOpts, false, true);
										}
										else
										{	
											$link = $certificateObj->getUrl($urlOpts, false);
										}
									?>
									<div class="btn-group">
									<a id="copyurl<?php echo $item->id;?>" data-bs-toggle="popover"
										data-bs-placement="bottom" data-bs-content="Copied!"
										data-alt-url="<?php echo $link;?>" class="btn border-btn" type="button"
										onclick="certificateImage.copyUrl('copyurl<?php echo $item->id;?>');">
										<?php echo Text::_('COM_TJCERTIFICATE_CERTIFICATE_URL_COPY');?>
									</a>
									</div>
									<?php
									}
									else
									{
										echo Text::_('COM_TJCERTIFICATE_CERTIFICATE_EXPIRED');
									}
									?>
								</td>
								<td>
									<div class="btn-group">
									<?php if (!$item->is_external)
									{
										$certLink = Route::_('index.php?option=com_tjcertificate&view=certificate&layout=preview&tmpl=component&id=' . (int) $item->id, false);
									}
									else
									{

										$certLink = Route::_('index.php?option=com_tjcertificate&view=trainingrecord&layout=preview&tmpl=component&id=' . (int) $item->id, false);
									} ?>

										<a id =""
											onclick="document.getElementById('previewModal' + <?php echo $item->id; ?>).open();"
											href="javascript:void(0);" >
											<?php echo Text::_('JGLOBAL_PREVIEW'); ?>
										</a>

									<?php

										echo HTMLHelper::_('bootstrap.renderModal', 'previewModal' . $item->id,
											array(
												'url' => $certLink,
												'width' => '800px',
												'height' => '300px',
												'modalWidth' => '80',
												'bodyHeight' => '70'
											)
										);
									?>

									</div>
								</td>
								<td>
									<?php if ($link) { ?>
									<a href="<?php echo $link;?>" target="_blank" title="<?php echo Text::_('COM_TJCERTIFICATE_CERTIFICATE_FRONTEND_PREVIEW');?>"></a>
									<?php } ?>
								</td>
								<td><?php echo (int) $item->id; ?></td>
							</tr>
							<?php
								}
							?>
						<tbody>
					</table>
					<?php echo $this->pagination->getListFooter(); ?>
					<?php
					}
					?>
					<input type="hidden" name="task" value="" />
					<input type="hidden" name="extension" value="<?php echo $this->component; ?>" />
					<input type="hidden" name="boxchecked" value="0" />
					<?php echo HTMLHelper::_('form.token'); ?>
			</div>
		</form>
	</div>
</div>
