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
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;

$googleEventUrl = Route::_("index.php?option=com_jticketing&task=event.addGoogleEvent()&id=" . $this->item->id, false);
$outlookEventUrl = Route::_("index.php?option=com_jticketing&view=event&format=ical&id=" . $this->item->id, false);
?>
<span>
	<i class="fa fa-calendar"></i>
</span>

<a href="<?php echo $googleEventUrl ?>" target="_blank">
	<?php echo   Text::_('COM_JTICKETING_EVENT_GOOGLE_CALENDER')?>
</a>
<input type="hidden" name="event" value="<?php echo $this->item->id; ?>"/>
<br/>
<br/>
<span>
	<i class="fa fa-file-text"></i>
</span>
<a href="<?php echo $outlookEventUrl;?>" download>
	<?php echo   Text::_('COM_JTICKETING_EVENT_OUTLOOK_CALENDER')?>
</a>
