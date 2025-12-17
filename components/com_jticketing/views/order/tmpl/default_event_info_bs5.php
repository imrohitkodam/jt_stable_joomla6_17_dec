<?php
/**
 * @package     JTicketing
 * @subpackage  com_jticketing
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

/** @var $this JticketingViewOrder */

?>
<div id="jtwrap" class="event-info af-mb-5 tjBs5">
   <div>
		 <div class="bg-light-gray customized-row af-br-5 overflow-hidden af-d-flex">
			<div class="af-col-xxxs-5 af-col-xxs-4 col-xs-4 col-sm-4 pin__img1 af-p-0">
			   <a class="af-d-block af-bg-cover af-bg-repn pt-responsive" href="<?php echo $this->event->getUrl();?>" title="<?php echo $this->escape($this->event->getTitle());?>" style="background-image:url('<?php echo $this->event->getAvatar();?>');"></a>
			</div>
			<div class="af-col-xxxs-7 af-col-xxs-8 col-xs-8 col-sm-8 af-py-10">
				<a href="<?php echo $this->event->getUrl();?>" class="af-d-block af-ml-10 event-title af-font-600" title="
				 "><?php echo $this->event->getTitle(); ?>
				</a>
			   <div class="af-ml-10 event-date event-location">
				  <i class="fa fa-calendar" aria-hidden="true"></i>
				  <span class="af-ml-5"><?php echo $this->utilities->getFormatedDate($this->event->getStartDate()); ?></span>
			   </div>

				<div class="af-ml-10 event-location">
					<i class="fa fa-map-marker" aria-hidden="true"></i>
						<span class="af-ml-10"> <?php echo $this->event->getVenueDetails(); ?></span>
				</div>
			</div>
		 </div>
   </div>
</div>
