<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2017 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Component\ComponentHelper;

HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('bootstrap.renderModal', 'a.modal');
HTMLHelper::script('administrator/components/com_tjfields/assets/js/tjfields.js');

/** @var $this JTicketingViewEvent */

$existingUrl = $this->event->getOnlineEventId();

if (isset($this->item->gallery))
{
	$this->mediaGalleryObj = json_encode($this->item->gallery);
}

// Event detail view resized image setting
$this->eventMainImage = $this->com_params->get('admin_event_detail_view','media_s');

// Event detail view resized image setting
$this->eventGalleryImage = $this->com_params->get('admin_event_gallery_view','media_s');

if ($this->mainframe->isClient("administrator"))
{
	$this->isAdmin = 1;
}
?>
<div id="warning_message">
</div>
<form action="<?php echo Route::_('index.php?option=com_jticketing&layout=edit&id=' . (int) $this->item->id);?>"
	method="post" enctype="multipart/form-data" name="adminForm" id="adminForm" class="form-validate">
	<div class="form-horizontal">
		<div class="row">
			<div class="col-sm-12">
				<?php
				echo HTMLHelper::_('bootstrap.startTabSet', 'myTab', array('active' => 'details'));
					echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'details', Text::_('COM_JTICKETING_EVENT_TAB_DETAILS', true));
						echo HTMLHelper::_('bootstrap.startAccordion', 'myAccordian', array('active' => 'collapse1'));
							echo HTMLHelper::_('bootstrap.addSlide', 'myAccordian', Text::_('COM_JTICKETING_EVENT_TAB_BASIC'), 'collapse1', $class = '');
								echo $this->loadTemplate('details_bs5');
							echo HTMLHelper::_('bootstrap.endSlide');

							echo HTMLHelper::_('bootstrap.addSlide', 'myAccordian', Text::_('COM_JTICKETING_EVENT_TAB_LOCATION'), 'collapse2', $class='');
								echo $this->loadTemplate('location_bs5');
							echo HTMLHelper::_('bootstrap.endSlide');

							echo HTMLHelper::_('bootstrap.addSlide', 'myAccordian', Text::_('COM_JTICKETING_EVENT_TAB_TIME'), 'collapse3', $class='');
								echo $this->loadTemplate('booking');
								echo HTMLHelper::_('bootstrap.endSlide');
							echo HTMLHelper::_('bootstrap.endAccordion');
						echo HTMLHelper::_('bootstrap.endTab');
					// TJ-Field Additional fields for Event
					if ($this->fieldsIntegration == 'com_tjfields')
					{
						if (!empty($this->formExtraFields))
						{
							echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'extrafields', Text::_('COM_JTICKETING_EVENT_TAB_EXTRA_FIELDS', true));
							echo "<br>";
							if (empty($this->item->id))
							{ ?>
								<div class="alert alert-info">
									<?php echo Text::_('COM_JTICKETING_EVENT_EXTRA_DETAILS_SAVE_PROD_MSG');?>
								</div>
								<?php
							}
							else
							{
								// @TODO SNEHAL- Load layout for this
								echo $this->loadTemplate('extrafields');
							}

							echo HTMLHelper::_('bootstrap.endTab');
						}
					}

					// Tab start for ticket types.
					echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'tickettypes', Text::_('COM_JTICKETING_EVENT_TAB_TICKET_TYPES', true));
						echo "<br>";
						$entryNumbeAssignment = $this->com_params->get('entry_number_assignment', 0,'INT');

						if ($entryNumbeAssignment)
						{
							?>
								<div class="form-group row">

									<div class="form-label col-md-2">
										<?php echo $this->form->getLabel('start_number_for_event_level_sequence'); ?>
									</div>
									<div class="col-md-4">
										<?php echo $this->form->getInput('start_number_for_event_level_sequence'); ?>
									</div>
								</div>
							<?php
						}
						$this->form->setFieldAttribute('tickettypes', 'layout', 'JTsubformlayouts.layouts.bs5.subform.repeatable');
						echo $this->form->getInput('tickettypes');
						echo HTMLHelper::_('bootstrap.endTab');

					// Tab start for Attendee Fields.
					if ($this->collect_attendee_info_checkout)
					{
						echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'attendeefields', Text::_('COM_JTICKETING_EVENT_TAB_EXTRA_FIELDS_ATTENDEE', true));
							echo "<br>";
							echo $this->loadTemplate('attendee_core_fields');
							echo HTMLHelper::_('bootstrap.endTab');
					}

					echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'gallery', Text::_('COM_JTICKETING_EVENT_GALLERY', true));
					?>
					<br>
					<div class="col-sm-12 well">
						<div class="control-group">
							<div class="control-label"><?php echo $this->form->getLabel('gallery_file'); ?></div>
							<div class="controls">
								<?php echo $this->form->getInput('gallery_file'); ?>
							 </div>
						</div>

						<div class="control-group">
							<div class="af-d-inline-block">
								<?php echo $this->form->renderField('gallery_link'); ?>
							</div>
							<div class="af-ml-15 gallary__validateBtn af-d-inline-block">
								<input type="button" class="validate_video_link btn btn-secondary" onclick="tjMediaFile.validateFile(this,1, <?php echo $this->isAdmin;?>, 'event')"
							value='<?php echo Text::_('COM_TJMEDIA_ADD_VIDEO_LINK');?>'>
							</div>
						</div>
					</div>
					<div class="col-sm-12 subform-wrapper">
						  <ul class="gallary__media thumbnails row">
							 <li class="gallary__media--li hide col-sm-6 col-md-2">
								<a class="close" href="javascript:;" onclick="tjMediaFile.tjMediaGallery.deleteMedia(this, <?php echo $this->isAdmin;?>, <?php echo (!empty($this->item->id))? $this->item->id : 0;?>, 'event');return false;">×</a>
								<?php HTMLHelper::_('jquery.token');	?>
								<input type="hidden" name="jform[gallery_file][media][]" class="media_field_value" value="">
								<div class="thumbnail"></div>
							 </li>
						  </ul>
					</div>
					<?php echo  HTMLHelper::_('bootstrap.endTab');
						// Loading joomla's params layout to show the fields and field group added for the course.
						echo LayoutHelper::render('joomla.edit.params', $this);

					if (Factory::getUser()->authorise('core.edit.own','com_jticketing.event')) :
							echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'permissions', Text::_('JGLOBAL_ACTION_PERMISSIONS_LABEL', true));
							echo "<br>";
							echo $this->form->getInput('rules');
							echo HTMLHelper::_('bootstrap.endTab');
					endif;
					echo HTMLHelper::_('bootstrap.endTabSet'); ?>
			<div>
			<input type="hidden" name="task" value="" />
			<?php echo HTMLHelper::_('form.token'); ?>
		</div>
	</div>
</form>
<?php
if(!$this->accessLevel)
{?>
	<style>
		#tickettypes .subform-repeatable-group .form-group:nth-last-child(2):not(#attendeefields #tickettypes .subform-repeatable-group .form-group:nth-last-child(2)){
			display: none;
		}

		#tickettypes .subform-repeatable-group .form-group:last-child:not(#attendeefields #tickettypes .subform-repeatable-group .inputboxdescmultiselect) {
			display: none;
		}
	</style>
<?php
}
Text::script('COM_JTICKETING_TICKET_ACCESS_EXCLUDE');
Text::script('COM_JTICKETING_TICKET_ACCESS_INCLUDE');
$ticketAccess = JT::config()->get('ticket_access');
$document = Factory::getDocument();
$document->addScriptDeclaration('var ticketAccessState = "' . $ticketAccess . '";');

Factory::getDocument()->addScriptDeclaration("
	var existing_url = '" . $existingUrl . "';
	var silentVendor = '" . $this->silentVendor . "';
	var eventId = '" . $this->item->id . "';
	var venueName = '" . htmlspecialchars($this->venueName) . "';
	var root_url = '" . Uri::root() . "';
	var venueId = '" . $this->venueId . "' ;
	var mediaSize = '". $this->mediaSize . "';
	var mediaGallery = " . $this->mediaGalleryObj . ";
	var galleryImage = '" . $this->eventGalleryImage . "';
	var eventMainImage = '" . $this->eventMainImage . "';
	var enableOnlineVenues = '" . $this->enableOnlineVenues . "';
	var selectedVenue = '" . $this->item->venue . "';
	var jticketing_baseurl = '" . Uri::root() . "';
	var handle_transactions = '" . $this->com_params->get('handle_transactions') . "';
	var array_check = '" . $this->arra_check . "';
	var tncForCreateEvent = '" . $this->tncForCreateEvent . "';
	var oldEventStartDate = '" . HTMLHelper::date($this->item->startdate ? $this->item->startdate : '', 'Y-m-d H:i', true) . "';
	var oldEventEndDate = '" . HTMLHelper::date($this->item->enddate ? $this->item->enddate : '', 'Y-m-d H:i', true) . "';
	jtAdmin.event.initEventJs();
	validation.positiveNumber();
");
?>
