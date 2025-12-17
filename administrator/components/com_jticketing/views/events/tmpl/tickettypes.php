<?php
/**
 * @version    SVN: <svn_id>
 * @package    Com_Jticekting
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2018 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */
// No direct access to this file
defined('_JEXEC') or die;
use Joomla\CMS\Language\Text;
?>

<div class="table-responsive">
	<?php if(empty($this->allTicketTypes)): ?>
		<div class="clearfix">&nbsp;</div>
		<div class="alert alert-info">
			<?php echo Text::_('NODATA'); ?>
		</div>

	<?php else : ?>
			<table class="table table-striped" width="80%" id="eventList">
					<tr>
						<th  class="">
							<?php echo Text::_('COM_JTICKETING_TICKET_TYPE_ID'); ?>
						</th>
						<th  class="">
							<?php echo Text::_('JT_TICKET_TYPE_TITLE'); ?>
						</th>
						<th  class="">
							<?php echo Text::_('JT_TICKET_TYPE_DESC'); ?>
						</th>
						<th  class="">
							<?php echo Text::_('JT_TICKET_TYPE_PRICE'); ?>
						</th>
						<th  class="">
							<?php echo  Text::_('JT_TICKET_TYPE_AVAILABLE');?>
						</th>
						<th  class="">
							<?php echo  Text::_('COM_JTICKETING_TICKET_TYPE_END_DATETIME');?>
						</th>
					</tr>

					<tbody>
					<?php
					$i = 0;
					foreach ($this->allTicketTypes as $i => $item) :
					?>
					<tr class="row<?php echo $i % 2; ?>">
						<td>
							<?php  echo $item->id?>
						</td>
						<td>
							<?php  echo $this->escape($item->title);?>
						</td>
						<td>
							<?php  echo $this->escape($item->desc);?>
						</td>
						<td>
							<?php  echo $item->price?>
						</td>
						<td>
							<?php
								if ($item->unlimited_seats == 1)
								{
									echo Text::_('COM_JTICKETING_UNLIMITED_SEATS_YES');
								}
								else
								{
									echo $item->count;
								}
							?>
						</td>
						<td>
							<?php
								if ($item->ticket_enddate)
								{
									echo $this->utilities->getFormatedDate($item->ticket_enddate);
								}
								else
								{
									echo "-";
								}
							?>
						</td>
					</tr>
			<?php
					$i++;
					endforeach;
			?>
					</tbody>
			</table>
	<?php endif;
