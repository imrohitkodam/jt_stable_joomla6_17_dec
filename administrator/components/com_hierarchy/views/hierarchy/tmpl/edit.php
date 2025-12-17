<?php
/**
 * @version    SVN: <svn_id>
 * @package    Com_Hierarchy
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2017 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;

HTMLHelper::addIncludePath(JPATH_COMPONENT . '/helpers/html');
HTMLHelper::_('behavior.keepalive');
$app = Factory::getApplication();
$input = $app->getInput();
$userId = $input->get('user_id', 0);
$UriRoot = Uri::root(true) . '/administrator/';

// Tokenize
$document = Factory::getDocument();
HTMLHelper::_('script', '/media/com_hierarchy/vendors/tokenize/jquery.tokenize.js');
HTMLHelper::_('stylesheet','/media/com_hierarchy/vendors/tokenize/jquery.tokenize.css');

// Call helper function
HierarchyHelper::getLanguageConstant();
?>
<form action="<?php echo Route::_('index.php?option=com_hierarchy&layout=edit&id=' . (int) $this->item->id); ?>" method="post" enctype="multipart/form-data" name="adminForm" id="hierarchy-form" class="form-validate">
	<div class="form-horizontal">
		<div class="row-fluid">
			<div class="span10 form-horizontal">
				<fieldset class="adminform">
					<input type="hidden" name="jform[id]" value="<?php echo $this->item->id; ?>" />
					<?php
						echo $this->form->renderField('reports_to');
						echo $this->form->renderField('context');
						echo $this->form->renderField('context_id');
					?>
				</fieldset>
			</div>
		</div>
		<?php echo HTMLHelper::_('bootstrap.endTab');?>
		<input type="hidden" name="task" value="" />
		<input type="hidden" id="user_id" name="user_id" value="<?php  echo $userId; ?>">
		<input type="hidden" id="created_by" name="created_by" value="<?php  echo $userId; ?>">
		<input type="hidden" id="modified_by" name="modified_by" value="<?php  echo $userId; ?>">
		<?php echo HTMLHelper::_('form.token'); ?>
	</div>
</form>
<script type="text/javascript">
	var UriRoot = "<?php echo $UriRoot; ?>";
	var client = "<?php echo $this->client; ?>";
	var userID = "<?php echo $userId; ?>";
	var clientID = "<?php echo $this->clientID; ?>";
	hierarchyAdmin.hierarchy.initHierarchyJs();
	hierarchyAdmin.hierarchy.getAutoSuggestUsers();
</script>
