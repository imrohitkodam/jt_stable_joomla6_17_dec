<?php
/**
 * @package     TJVendors
 * @subpackage  com_tjvendors
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access
defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
?>
<div class="container">
<h1><?php echo Text::_('COM_TJVENDOR_VENDOR_PROFILE');?></h1>
<input type="hidden" name="client" value="<?php echo $this->input->get('client', '', 'STRING'); ?>" />
<div class="profile row <?php echo COM_TJVENDORS_WRAPPAER_CLASS;?>" id="tjv-wrapper">
	<div class="row">
		<h3 class="mt-0">
			<?php echo htmlspecialchars($this->VendorDetail->vendor_title, ENT_COMPAT, 'UTF-8');?>
			<span class="pull-right">
            <small>
                <?php if (empty($this->vendor_id)) : ?>
                    <!-- Display "Create" link when vendor_id is not present -->
                    <a  
                        href="<?php echo Route::_(
                            'index.php?option=com_tjvendors&view=vendor&layout=create&client=' . $this->input->get('client', '', 'STRING') . '&Itemid=' . $this->vendorFormItemId
                        ); ?>">
                        <i class="fa fa-plus" aria-hidden="true"></i> <?php echo Text::_('COM_TJVENDOR_CREATE_VENDOR'); ?>
                    </a>
                <?php else : ?>
                    <!-- Display "Update" link when vendor_id is present -->
                    <a  
                        href="<?php echo Route::_(
                            'index.php?option=com_tjvendors&view=vendor&layout=editinfo&vendor_id=' . $this->vendor_id . '&client=' . $this->input->get('client', '', 'STRING') . '&Itemid=' . $this->vendorFormItemId
                        ); ?>">
                        <i class="fa fa-wrench" aria-hidden="true"></i> <?php echo Text::_('COM_TJVENDORS_VENDOR_UPDATE'); ?>
                    </a>
                <?php endif; ?>
            </small>
        </span>
		</h3>
	</div>
	<?php
	$profileImage = Uri::root() . "media/com_tjvendor/images/default.png";

	if (!empty($this->VendorDetail->vendor_logo))
	{
		$profileImage = Uri::root() . $this->VendorDetail->vendor_logo;
	}
	?>
	<div class="row">
		<div class="controls col-sm-3 center">
			<img src="<?php echo $profileImage; ?>" width="100%">
		</div>
		<div class="col-sm-9">
			<div>
				<div class='profile__content text-muted'>
				<?php echo $this->escape($this->VendorDetail->vendor_description);?>
				</div>
			</div>
		</div>
	</div>
	<div>&nbsp;</div>
	<div class="row">
		<?php
		if (!empty($this->VendorDetail->address) || !empty($this->VendorDetail->city_name))
		{
		?>
		<div class="col-sm-3"><?php echo Text::_('COM_TJVENDORS_VENDOR_ADDRESS')?></div>
		<div class="col-sm-9">
			<?php
				if (!empty($this->VendorDetail->address))
				{
					echo $this->escape($this->VendorDetail->address);
				}

				if (!empty($this->VendorDetail->country_name))
				{
					echo ', ' . $this->escape($this->VendorDetail->country_name);
				}

				if (!empty($this->VendorDetail->region_name))
				{
					echo ', ' . $this->escape($this->VendorDetail->region_name);
				}

				if (!empty($this->VendorDetail->city_name))
				{
					echo ', ' . $this->escape($this->VendorDetail->city_name);
				}
			?>
		</div>
		<?php
		}
		if (!empty($this->VendorDetail->zip))
		{
		?>
			<div class="col-sm-3"><?php echo Text::_('COM_TJVENDORS_VENDOR_ZIP')?></div>
			<div class="col-sm-9">
				<?php echo htmlspecialchars($this->VendorDetail->zip, ENT_COMPAT, 'UTF-8')?>
			</div>
		<?php 
		}
		if (!empty($this->VendorDetail->phone_number))
		{
		?>
			<div class="col-sm-3"><?php echo Text::_('COM_TJVENDORS_VENDOR_PHONE_NUMBER')?></div>
			<div class="col-sm-9">
				<?php echo htmlspecialchars($this->VendorDetail->phone_number, ENT_COMPAT, 'UTF-8')?>
			</div>
		<?php 
		}

		if (!empty($this->VendorDetail->website_address))
		{
		?>
			<div class="col-sm-3"><?php echo Text::_('COM_TJVENDORS_VENDOR_WEBSITE_ADDRESS')?></div>
			<div class="col-sm-9">
				<?php echo htmlspecialchars($this->VendorDetail->website_address, ENT_COMPAT, 'UTF-8')?>
			</div>
		<?php 
		}

		if (!empty($this->VendorDetail->vat_number))
		{
		?>
			<div class="col-sm-3"><?php echo Text::_('COM_TJVENDORS_VENDOR_VAT_NUMBER')?></div>
			<div class="col-sm-9">
				<?php echo htmlspecialchars($this->VendorDetail->vat_number, ENT_COMPAT, 'UTF-8')?>
			</div>
		<?php
		}?>	
	</div>
</div>
</div>
<script>
	tjVAdmin.vendor.readMore();
</script>
