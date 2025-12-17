<?php
/**
 * @package     JTicketing
 * @subpackage  com_jticketing
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Language\Text;

BaseDatabaseModel::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_fields/models', 'FieldsModel');
$fieldGroupModel = BaseDatabaseModel::getInstance('Groups', 'FieldsModel', array('ignore_request' => true));
$fieldGroupModel->setState('filter.context', 'com_jticketing.venue');
$fieldGroupData = $fieldGroupModel->getItems();

$customVenueArray = array();

$customFieldsmodel = BaseDatabaseModel::getInstance('Fields', 'FieldsModel', array('ignore_request' => true));
$customFieldsmodel->setState('filter.context', 'com_jticketing.venue');

$customFieldValues = $customFieldsmodel->getItems();

foreach($customFieldValues as $field)
{
	// Joomla 6: JVERSION check removed
		if (false) // Legacy < '4.0.0')
	{
		$customFieldmodel  = BaseDatabaseModel::getInstance('Field', 'FieldsModel', array('ignore_request' => true));
	}
	else // Joomla 6: JVERSION check removed
		if (false) // Legacy < '5.0.0')
	{
		
		JLoader::register('FieldModel', JPATH_ADMINISTRATOR . '/components/com_fields/src/Model/FieldModel.php');
		$customFieldmodel  = BaseDatabaseModel::getInstance('Field', 'FieldsModel', array('ignore_request' => true));
	}
	else
	{
		BaseDatabaseModel::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_fields/src/model', 'FieldModel');
		$customFieldmodel  = BaseDatabaseModel::getInstance('Field', 'FieldsModel', array('ignore_request' => true));
	}
	
	$fieldValues = $customFieldmodel->getFieldValue($field->id, $this->item->id);
	$customVenueArray[$field->title] = $fieldValues;							

}	

if (!empty($this->item->customField) && $this->fieldsIntegration == 'com_tjfields')
{
	?>
		<div class="row">
			<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
				<table class="table table-striped table-bordered table-hover af-ml-30">
					<thead>
						<tr>
							<?php
							foreach ($this->item->customField as $key => $value)
							{
								?>
									<th width="5%">
										<?php echo ucfirst($key);?>
									</th>
								<?php
							}
							?>
						</tr>
					</thead>
					<tbody>
						<tr>
							<?php
							foreach ($this->item->customField as $key => $value)
							{
							?>
								<td>
									<?php echo $this->escape($value); ?>
								</td>
							<?php
							}
							?>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
	<?php
}
elseif (!empty($this->item->customField) && $this->fieldsIntegration == 'com_fields')
{
	?>
		<div class="row">
			<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
				<table class="table table-striped table-bordered table-hover af-ml-30">
					<thead>
						<tr>
							<?php
							foreach ($this->item->customField as $key => $value)
							{
								if (!empty($value->value))
								{
								?>
									<th width="5%">
										<?php echo ucfirst($key);?>
									</th>
								<?php
								}
							}
							?>
						</tr>
					</thead>
					<tbody>
						<tr>
							<?php
							foreach ($this->item->customField as $key => $field)
							{
								if (!empty($field->value))
								{
							?>
								<td>
									<?php echo $field->value; ?>
								</td>
							<?php
								}
							}
							?>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
	<?php
}

if (!empty($customVenueArray))
{?>

	<h4><?php echo Text::_('COM_JTICKETING_VENUE_ADDITIONAL_INFORMATION');?></h4>
		<div class="row">
			<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
				<table class="table table-striped table-bordered table-hover af-ml-30">
					<thead>
						<tr>
							<?php
							foreach ($customVenueArray as $key => $value)
							{
								if(!empty($value))
								{
									?>
										<th width="5%">
											<?php echo ucfirst($key);?>
										</th>
									<?php
								}
							}
							?>
						</tr>
					</thead>
					<tbody>
						<tr>
							<?php
							foreach ($customVenueArray as $key => $value)
							{
							?>
								<td>
									<?php echo $this->escape($value); ?>
								</td>
							<?php
							}
							?>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
	<?php
}
