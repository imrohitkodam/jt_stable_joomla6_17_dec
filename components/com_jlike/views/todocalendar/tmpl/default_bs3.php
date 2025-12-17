<?php
/**
 * @version    SVN: <svn_id>
 * @package    Jlike
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2018 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
$site_root = Uri::root();
HTMLHelper::_('jquery.framework');
HTMLHelper::_('bootstrap.framework');

$document = Factory::getDocument();
$document->addStyleSheet($site_root . 'media/com_jlike/vendors/fullcalendar/css/fullcalendar.min.css');
HTMLHelper::script($site_root . 'media/com_jlike/vendors/fullcalendar/js/moment.min.js');
HTMLHelper::script($site_root . 'media/com_jlike/vendors/fullcalendar/js/fullcalendar.min.js');
HTMLHelper::script($site_root . 'media/com_jlike/vendors/fullcalendar/js/app.js');
HTMLHelper::script($site_root . 'components/com_jlike/assets/scripts/calendartodo.jQuery.js');
HTMLHelper::script($site_root . 'components/com_jlike/assets/scripts/todo.calendar.js');
$client = $this->params->get('client_filter');
$user = Factory::getUser();
if ($client == '')
{?>
<div class="pull-right">
	<span>
	<?php
	$cls = 'class="selectbox" size="1" onchange="setdata.setClient(this);"';
	echo HTMLHelper::_('select.genericlist', $this->options, "search_client", $cls, "value", "text");?>
	</span>
	<span id="ajax_loader"></span>
</div>
<?php
} ?>
<div data-jlike-todos="todo" id="todoCalendar"
data-jlike-url=""
data-jlike-type="todos"
data-jlike-context=""
data-jlike-assigned_by=""
data-jlike-assigned_to="<?php echo $user->id; ?>"
data-jlike-start_date=""
data-jlike-due_date=""
data-jlike-parent_id=""
data-jlike-status=""
data-jlike-state=""
data-jlike-client="<?php echo $client; ?>"
data-jlike-content_id="">
</div>

<script type="text/javascript">
/** const: site_root */
const jlike_site_root = '<?php echo $site_root ?>';
</script>

<div id='jlike-calendar'></div>
