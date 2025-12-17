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
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
?>
<tr>
	<td class="col-xs-12 col-md-2">
		<?php
		if (!empty($this->eventAttendeeInfo->owner_id) && !empty($this->eventAttendeeInfo->avatar))
		{
			$profileImg = $this->eventAttendeeInfo->avatar;
		}
		else
		{
			$profileImg = Uri::root(true) . '/media/com_jticketing/images/default_avatar.png';
		}
		?>
		<div class="col-xs-12 organizer-profile af-p-5 center col-md-4">
			<img src="<?php echo $profileImg; ?>" class="img-circle img-responsive" alt="<?php echo Text::_('COM_JTICKETING_EVENT_OWNER_AVATAR')?>">
		</div>
	</td>
	<td class="col-xs-12 col-md-10" data-title="<?php echo Text::_("COM_JTICKETING_ATTENDER_NAME"); ?>">
		<?php echo $this->eventAttendeeInfo->name ? ucfirst($this->eventAttendeeInfo->name) : Text::_('COM_JTICKETING_GUEST');?>
	</td>
</tr>
