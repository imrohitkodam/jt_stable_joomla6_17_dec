<?php
/**
 * @package	Jticketing
 * @copyright Copyright (C) 2009 -2010 Techjoomla, Tekdi Web Solutions . All rights reserved.
 * @license GNU GPLv2 <http://www.gnu.org/licenses/old-licenses/gpl-2.0.html>
 * @link     http://www.techjoomla.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Editor\Editor;

require_once JPATH_ADMINISTRATOR . "/components/com_jticketing/config.php";

require_once JPATH_LIBRARIES . '/techjoomla/emogrifier/tjemogrifier.php';
InitEmogrifier::initTjEmogrifier();

$app = Factory::getApplication();
$document =Factory::getDocument();
HTMLHelper::_('stylesheet', 'components/com_jticketing/assets/css/jticketing.css');
?>
<?php

if(JVERSION>=3.0):

	if(!empty( $this->sidebar)): ?>
	<div id="sidebar">
		<div id="j-sidebar-container" class="span2">
			<?php echo $this->sidebar; ?>
		</div>

	</div>

		<div id="j-main-container" class="span10">

	<?php else : ?>
		<div id="j-main-container">
	<?php endif;
endif;
?>

<form method="POST" name="adminForm" action="" id="adminForm">
<div  class="techjoomla-bootstrap">
	<div class="adminlist row">
		<div class="col-sm12"><?php
		//Code to Read CSS File
		if(!function_exists('mb_convert_encoding'))		// condition to check if mbstring is enabled
		{
			echo Text::_("MB_EXT");
			$emorgdata=$emails_config['message_body'];
		}
		else
		{
			$cssfile = JPATH_SITE . "/components/com_jticketing/assets/css/email.css";
			$cssdata = file_get_contents($cssfile);
			//End Code to Read CSS File

			$emogr=new TjEmogrifier($emails_config['message_body'],$cssdata);
			$emorgdata=$emogr->emogrify();
		}
		echo "<label>" . Text::_("COM_JTICKETING_EMAIL_CONFIG") . "</label>";
		$getEditor  = Factory::getConfig()->get('editor');
		$editor     = Editor::getInstance($getEditor);
		echo $editor->display("data[message_body]",stripslashes($emorgdata),670,600,60,20,true);
		?>
		</div>
		<div class="col-sm-12">
			<table width="100%">
				<tr>
					<td colspan="2"><div class="alert alert-info"><?php echo Text::_('EB_CSS_EDITOR_MSG') ?> <br/></div>
						<textarea name="data[template_css]" rows="10" cols="90"><?php echo trim($cssdata); ?></textarea>
					</td>
				</tr>
				<tr>
					<td colspan="2"><div class="alert alert-info"><?php echo Text::_('COM_JTICKETING_EB_TAGS_DESC') ?> <br/></div>
									</tr>

				<tr>
					<td width="30%"><b>&nbsp;&nbsp;[NAME] </b> </td>
					<td><?php echo Text::_('TAGS_NAME'); ?></td>

				</tr>

				<tr>
					<td><b>&nbsp;&nbsp;[BOOKING_DATE]</b></td>
					<td><?php echo Text::_('TAGS_BOOKING_DATE'); ?></td>

				</tr>
				<tr>
					<td><b>&nbsp;&nbsp;[BUYER_NAME]</b></td>
					<td><?php echo Text::_('TAGS_BUYER_NAME'); ?></td>
				</tr>
				<tr>
					<td><b>&nbsp;&nbsp;[EVENT_IMAGE]</b></td>
					<td><?php echo Text::_('TAGS_EVENT_IMAGE'); ?></td>

				</tr>
				<tr>
					<td><b>&nbsp;&nbsp;[EVENT_NAME]</b></td>
					<td><?php echo Text::_('TAGS_EVENT_NAME'); ?></td>

				</tr>
				<tr>
					<td><b>&nbsp;&nbsp;[EVENT_URL]</b></td>
					<td><?php echo Text::_('TAGS_EVENT_LINK'); ?></td>

				</tr>
				<tr>
					<td><b>&nbsp;&nbsp;[ST_DATE]</b> </td>
					<td><?php echo Text::_('TAGS_EVENT_START_DATE'); ?></td>

				</tr>
				<tr>
					<td><b>&nbsp;&nbsp;[EN_DATE]</b></td>
					<td><?php echo Text::_('TAGS_EVENT_END_DATE'); ?></td>

				</tr>

				<tr>
					<td><b>&nbsp;&nbsp;[EVENT_LOCATION]</b> </td>
					<td><?php echo Text::_('TAGS_EVENT_LOCATION'); ?></td>
				</tr>
				<tr>
					<td><b>&nbsp;&nbsp;[TICKET_ID]</b></td>
					<td><?php echo Text::_('TAGS_TICKET_ID'); ?></td>

				</tr>
				<tr>
					<td><b>&nbsp;&nbsp;[TICKET_TYPE]</b></td>
					<td><?php echo Text::_('TAGS_TICKET_TYPE'); ?></td>

				</tr>
				<tr>
					<td><b>&nbsp;&nbsp;[ENTRY_NUMBER]</b></td>
					<td><?php echo Text::_('COM_JTICKEITNG_TAGS_ATTENDEE_ENTRY_NUMBER'); ?></td>

				</tr>
				<tr>
					<td><b>&nbsp;&nbsp;[TICKET_PRICE]</b> </td>
					<td><?php echo Text::_('TAGS_TICKET_PRICE'); ?></td>
				</tr>
				<tr>
					<td><b>&nbsp;&nbsp;[TOTAL_PRICE]</b></b></td>
					<td><?php echo Text::_('TAGS_TOTAL_PRICE'); ?></td>

				</tr>
				<tr>
					<td><b>&nbsp;&nbsp;[EVENT_DESCRIPTION] </b></td>
					<td><?php echo Text::_('TAGS_EVENT_DESCRIPTION'); ?></td>

				</tr>
				<tr>
					<td><b>&nbsp;&nbsp;[QR_CODE]</b></td>
					<td><?php echo Text::_('TAGS_QC_CODE'); ?></td>
				</tr>
				<tr>
					<td><b>&nbsp;&nbsp;[BAR_CODE]</b></td>
					<td><?php echo Text::_('TAGS_BAR_CODE'); ?></td>
				</tr>
			</table>
		</div>
	</div>
	<input type="hidden" name="option" value="com_jticketing" />
	<input	type="hidden" name="task" value="save" />
	<input type="hidden"	name="controller" value="email_config" />
	<input type="hidden"	name="view" value="email_config" />
	<?php echo HTMLHelper::_( 'form.token' ); ?>
</div>

</form>
</div>
