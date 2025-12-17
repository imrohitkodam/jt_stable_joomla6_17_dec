<?php
/**
 * @package     TJCertificate
 * @subpackage  com_tjcertificate
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2020 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;

?>
<!-- Modal -->
<style>
	.modal-body {
	    overflow-y: auto;
	}
</style>
<div id="templatePreview" class="modal fade hide" role="dialog">
	<div class="modal-dialog">
		<button type="button" class="close" data-dismiss="modal" style="width: 40px;opacity: 0.7;">&times;</button>
		<!-- Modal content-->
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title"><?php echo Text::_('COM_TJCERTIFICATE_CERTIFICATE_TEMPLATE_MODAL_HEADER'); ?></h4>
				<p class="alert alert-info hide" id="show-info"><?php echo Text::_('COM_TJCERTIFICATE_CERTIFICATE_TEMPLATE_MODAL_HEADER_INFO'); ?></p>
			</div>
			<div class="modal-body" id="previewTempl">
			</div>
			<div class="modal-footer">
			<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
    			<?php echo Text::_('JCLOSE'); ?>
			</button>
			</div>
		</div>
	</div>
</div>
