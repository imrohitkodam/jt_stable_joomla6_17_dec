<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2018 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die( ';)' );
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

if (isset($this->html))
{
	?>
	<div class="">
		<div class="">
		<table width="100%">
			<tr>
				<td class="text-right" align='right'>
					<input type="button" class="btn btn-success no-print af-mb-5 af-mt-5" onclick="javascript:window.print()" value="<?php echo Text::_('PRINT');?>">
					<a class="btn btn-success btn-medium no-print af-mb-5 af-mt-5"
						href="<?php echo Route::_(
						Uri::root() . '?option=com_jticketing&task=attendees.downloadPDF&ticketid='
						. $this->attendeeId . '&eventid=' . $this->eventId, false
						);?>"><?php echo Text::_('COM_JTICKETING_PRINT_PDF');?></a>
				</td>
			</tr>
		</table>
		</div>
	<div>
	<?php
	echo $this->html;
}
