<?php
/**
 * @package     JTicketing
 * @subpackage  com_jticketing
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

/** @var $this JticketingViewOrder */
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\HTML\HTMLHelper;

/** @var $this JticketingViewOrder */
HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('behavior.formvalidator');
// Joomla 6: formbehavior.chosen removed - using native select
$i = 0;

HTMLHelper::_('jquery.token');

$config              = JT::config();
$regexForAttendeeMob = $config->get('regexforAttendeeMob', '/^(\+\d{1,3}[- ]?)?\d{10}$/');
$document   = Factory::getDocument();
$document->addScriptDeclaration('var regexForAttendeeMob=new RegExp(' . $regexForAttendeeMob . ');');
?>
<div id="jtwrap" class="tjBs5">
<form name="attendee_field_form" action="" id="attendee_field_form" class="form-validate attendee_form container-fluid af-pl-0 af-pr-0">
	<div class="row af-mt-10">
		<div class="col-sm-8">
		 <?php echo $this->loadTemplate('event_info_' . JTICKETING_LOAD_BOOTSTRAP_VERSION);?>
			<div class="panel af-bg-white af-br-5 border-gray af-d-block d-sm-none">
				<?php echo $this->loadTemplate('cart_' . JTICKETING_LOAD_BOOTSTRAP_VERSION); ?>
			</div>
			<div>
				<?php if ($config->get('pdf_attach_in_mail') == 1)
				{?>
				<div class="custom-new-panel mt-20 card br-5 m-0">
					<div class="card-header br-t5">
						<h3 class="card-title"><strong><?php echo Text::_('COM_JTICKETING_SEND_TICKETS');?></strong>
						</h3>
					</div>
					<div class="card-body">
						<?php
							if (!$this->event->isOnline())
							{
							?>
							<input type="radio" id="ticketToBuyer" name="sendTicket" value="ticketToBuyer">
							<label for="ticketToBuyer"><?php echo Text::_('COM_JTICKETING_SEND_TICKETS_TO_EVENTBUYER');?></label><br>
							<?php
							}
						?>
						<input type="radio" id="ticketToAttendee" name="sendTicket" value="ticketToAttendee">
						<label for="ticketToAttendee"><?php echo Text::_('COM_JTICKETING_SEND_TICKETS_TO_ATTENDEE');?></label><br>
					</div>
				</div>
			<?php }?>
			</div>
		<?php
		foreach ($this->orderItems AS $key => $oitem)
		{
			$tickettype = JT::tickettype($oitem->type_id);
			$oitem->attendee_value = $this->attendeeFieldsModel->getAttendeeFields($this->order->event_details_id, $oitem->attendee_id, $this->event->getCategory());

			if ($oitem->attendee_value)
			{ ?>
				<div>
					<div class="card custom-new-panel af-mt-20 af-br-5 af-m-0">
						<div class="card-header af-br-t5">
							<h3 class="card-title"><strong><?php echo Text::sprintf('COM_JTICKETING_ATTENDEE_INFORMATION', $tickettype->title);?></strong></h3>
						</div>
						<div class="card-body">
							<input type="hidden" id="attendee_field[<?php echo $i;?>][order_items_id]" name="attendee_field[<?php echo $i;?>][order_items_id]" placeholder="" value="<?php if(isset($oitem->id)) echo $oitem->id;?>">
							<input type="hidden" id="attendee_field[<?php echo $i;?>][ticket_type]" name="attendee_field[<?php echo $i;?>][ticket_type]" placeholder="" value="<?php if (isset($oitem->id)) echo $oitem->type_id;?>">
							<input type="hidden" id="attendee_field[<?php echo $i;?>][attendee_id]" name="attendee_field[<?php echo $i;?>][attendee_id]" placeholder="" value="<?php if (isset($oitem->attendee_id)) echo $oitem->attendee_id;?>">
							<div class="row">
							<!--start of form-group-->
							<div class="form-group">
<!--
								<label class="">
									<?php echo Text::_('TICKET_TYPE');?>
								</label>
-->
								<span>
									<strong>
										<?php // echo htmlspecialchars($ticketTypeArr[$oitem->type_id], ENT_COMPAT, 'UTF-8');?>
									</strong>
								</span>
							</div>
							<?php
							foreach ($oitem->attendee_value AS $field)
							{
								// Get the fields value using attendee id.
								$fieldValue = isset($field->attendee_value[$oitem->attendee_id]) ? $field->attendee_value[$oitem->attendee_id] : '';

								// Important trick for universal fields, this is needed to save fields based on id(event specific) & name(universal)
								if (isset($field->is_universal) && $field->is_universal)
								{
									$field->id = $field->name;
								} ?>

								<div class="form-group col-sm-6 col-xs-12">
									<div class="input-field af-mb-20">
										<?php
										if(!empty($field->type))
										{
											// Adding validation class for email
											if ($field->name == 'email')
											{
												$field->validation_class = $field->validation_class . ' emailValidation';
											}

											// Call the layout depending on the type of the field.
											$layout = new FileLayout('com_jticketing.attendee.' . $field->type, JPATH_ROOT . '/components/com_jticketing');
											$data                   = array();
											$data['field']          = $field;
											$data['fieldValue']     = $fieldValue;
											$data['i']              = $i;
											$data['attendeeId']     = $oitem->attendee_id;

											echo $layout->render($data);
										}
										else
										{ ?>
											<input type="<?php echo $field->type;?>" id="attendee_field_<?php echo  $field->id; ?>_<?php echo  $i; ?>" <?php if ($field->js_function) echo $field->js_function; ?> class="<?php if ($field->required) echo "required"; echo $field->validation_class;?> form-control" name="attendee_field[<?php echo $i; ?>][<?php echo  $field->id; ?>]" placeholder="<?php if (isset($field->placehoder)) $field->placehoder; else  echo $field->label ?>" description="<?php echo $field->description;?>"
											value="">
									<?php }?>
									</div>
									<!--end of form-group-->
								</div>
						<?php } ?>
							</div>
						</div>
					</div>
				</div>
			<?php
			}
			$i++;
		}
		?>
		<div class="center generic-btn af-mt-20">
			<button id="attendeeCheckout" class="btn btn-primary af-border-0 attendeeCheckout" data-order-id="<?php echo $this->order->id; ?>" type="button">
				<?php echo Text::_('COM_JTICKETING_ATTENDEES_SAVE_DETAILS'); ?>
			</button>
		</div>
	</div>

		<div class="col-sm-4 af-mb-20 d-none d-sm-block">
			<div class="af-bg-white af-br-5 border-gray">
				<?php echo $this->loadTemplate('cart_' . JTICKETING_LOAD_BOOTSTRAP_VERSION); ?>
			</div>
		</div>
	</div>

</form>
</div>
<script>
// to validate attendee name
jtSite.order.validateAttendeeName();
jtSite.order.hideEmailField();
</script>