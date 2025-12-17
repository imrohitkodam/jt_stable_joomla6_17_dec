<?php
/**
 * @package     JTicketing
 * @subpackage  com_jticketing
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;
use Joomla\String\StringHelper;
use Joomla\CMS\Helper\TagsHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Form\Form;

if (file_exists(JPATH_LIBRARIES . "/techjoomla/media/storage/local.php")) { require_once JPATH_LIBRARIES . "/techjoomla/media/storage/local.php"; }
if (file_exists(JPATH_LIBRARIES . "/techjoomla/media/xref.php")) { require_once JPATH_LIBRARIES . "/techjoomla/media/xref.php"; }
if (file_exists(JPATH_SITE . '/components/com_jticketing/events/event.php')) { require_once JPATH_SITE . '/components/com_jticketing/events/event.php'; }
if (file_exists(JPATH_SITE . '/components/com_tjfields/filterFields.php')) { require_once JPATH_SITE . '/components/com_tjfields/filterFields.php'; }
if (file_exists(JPATH_SITE . '/components/com_jticketing/models/attendeefields.php')) { require_once JPATH_SITE . '/components/com_jticketing/models/attendeefields.php'; }
if (file_exists(JPATH_SITE . '/components/com_jticketing/models/tickettype.php')) { require_once JPATH_SITE . '/components/com_jticketing/models/tickettype.php'; }
if (file_exists(JPATH_SITE . '/components/com_jticketing/models/integrationxref.php')) { require_once JPATH_SITE . '/components/com_jticketing/models/integrationxref.php'; }
if (file_exists(JPATH_SITE . '/components/com_jticketing/helpers/route.php')) { require_once JPATH_SITE . '/components/com_jticketing/helpers/route.php'; }
if (file_exists(JPATH_SITE . '/components/com_jticketing/helpers/frontendhelper.php')) { require_once JPATH_SITE . '/components/com_jticketing/helpers/frontendhelper.php'; }
JLoader::register('JticketingMailHelper', JPATH_SITE . '/components/com_jticketing/helpers/mail.php');

/**
 * model for showing order
 *
 * @package     JTicketing
 * @subpackage  component
 * @since       1.0
 */

class JticketingModelEventForm extends AdminModel
{
	use TjfieldsFilterField;
	/**
	 * Method to get a table object, load it if necessary.
	 *
	 * @param   string  $type    The table name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  JTable  A JTable object
	 *
	 * @since   1.6
	 */
	public function getTable($type = 'Event', $prefix = 'JTicketingTable', $config = array())
	{
		$this->addTablePath(JPATH_ADMINISTRATOR . '/components/com_jticketing/tables');

		return Table::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to get the record form.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  mixed    A JForm object on success, false on failure
	 *
	 * @since   1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_jticketing.event', 'event', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

		$comParams = ComponentHelper::getParams('com_jticketing');
		$dateFormat = $comParams->get('date_format_show');

		if ($dateFormat == "custom")
		{
			$dateFormat = $comParams->get('custom_format');
		}

		// If format is 24 hour then display dates as per format
		if (strpos($dateFormat, "H") !== false) {
			$form->setFieldAttribute('startdate', 'format', '%Y-%m-%d %H:%M');
			$form->setFieldAttribute('startdate', 'timeformat', '24');
			$form->setFieldAttribute('enddate', 'format', '%Y-%m-%d %H:%M');
			$form->setFieldAttribute('enddate', 'timeformat', '24');
			$form->setFieldAttribute('booking_start_date', 'format', '%Y-%m-%d %H:%M');
			$form->setFieldAttribute('booking_start_date', 'timeformat', '24');
			$form->setFieldAttribute('booking_end_date', 'format', '%Y-%m-%d %H:%M');
			$form->setFieldAttribute('booking_end_date', 'timeformat', '24');
		}
		else
		{
			$form->setFieldAttribute('startdate', 'format', '%Y-%m-%d %I:%M %P');
			$form->setFieldAttribute('startdate', 'timeformat', '12');
			$form->setFieldAttribute('enddate', 'format', '%Y-%m-%d %I:%M %P');
			$form->setFieldAttribute('enddate', 'timeformat', '12');
			$form->setFieldAttribute('booking_start_date', 'format', '%Y-%m-%d %I:%M %P');
			$form->setFieldAttribute('booking_start_date', 'timeformat', '12');
			$form->setFieldAttribute('booking_end_date', 'format', '%Y-%m-%d %I:%M %P');
			$form->setFieldAttribute('booking_end_date', 'timeformat', '12');
		}

		if (!empty($form->getValue('id')))
		{
			$form->setFieldAttribute('online_events', 'readonly', 'true');

			if ($form->getValue('online_events') == 1)
			{
				$form->setFieldAttribute('venuechoice', 'readonly', 'true');
				$form->setFieldAttribute('venue', 'readonly', 'true');
				$form->setFieldAttribute('existing_event', 'readonly', 'true');
				$form->setFieldAttribute('online_provider', 'readonly', 'true');
			}
		}

		$entryNumbeAssignment = $comParams->get('entry_number_assignment', 0,'INT');

		// Add the entry number fields based on configuration
		if (!$entryNumbeAssignment)
		{
			$subformPath = JPATH_SITE . $form->getFieldAttribute('tickettypes', 'formsource');

			// Load the subform as a separate instance
			$subform = Form::getInstance('tickettypes', $subformPath);

			$subform->removeField('start_number_for_sequence');
			$subform->removeField('allow_ticket_level_sequence');
			$form->removeField('start_number_for_event_level_sequence');
			$form->setFieldAttribute('tickettypes', 'formsource', $subform->getXml()->asXML());
		}
		else
		{
			if (!empty($form->getValue('id')))
			{
				$subformPath = JPATH_SITE . $form->getFieldAttribute('tickettypes', 'formsource');

				// Retrieve the ticket types from the form data (assuming it's part of the form data submission)
				$ticketTypes = $form->getData()->get('tickettypes', []);
				$alreadyBookedTickets = $this->getAllBookedTicketIds($form->getValue('id'));

				// Load the subform as a separate instance
				$subform = Form::getInstance('tickettypes', $subformPath);

				if (count($alreadyBookedTickets))
				{
					$form->setFieldAttribute('start_number_for_event_level_sequence', 'readonly', 'true');
					$subform->setFieldAttribute('start_number_for_sequence', 'readonly', 'true');
					$subform->setFieldAttribute('allow_ticket_level_sequence', 'readonly', 'true');

					// Currently this funcyionlaity not working we will check this at the end
					// if any oone event ticket is sold then disable entry number for all event
					// We will check in future disable only respective ticket numbering
					// Loop through the ticket types and conditionally set attributes
					// foreach ($ticketTypes as $index => $ticketType) 
					// {
					// 	if (isset($ticketType->allow_ticket_level_sequence) && !$ticketType->allow_ticket_level_sequence) 
					// 	{
					// 		$groupPath = 'tickettypes.' . $index;

					// 		// Set readonly attributes for fields within the subform
					// 		$subform->setFieldAttribute('start_number_for_sequence', 'readonly', 'true', $groupPath);
					// 		$subform->setFieldAttribute('allow_ticket_level_sequence', 'readonly', 'true', $groupPath);
					// 	}
					// 	else
					// 	{
					// 		if (isset($ticketType->id) && $ticketType->id && in_array($ticketType->id, $alreadyBookedTickets))
					// 		{
					// 			$subform->setFieldAttribute('allow_ticket_level_sequence', 'readonly', 'true', 'tickettypes.' . $index);
					// 			$subform->setFieldAttribute('start_number_for_sequence', 'readonly', 'true', 'tickettypes.' . $index);
					// 		}
					// 	}
					// }
				}
				
				$form->setFieldAttribute('tickettypes', 'formsource', $subform->getXml()->asXML());
			}
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  mixed  The data for the form.
	 *
	 * @since   1.6
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = Factory::getApplication()->getUserState('com_jticketing.edit.eventform.data', array());
		if (empty($data))
		{
			$data = $this->getItem();
			if (!empty($data->startdate)) {
				$startDateTime = explode(' ', $data->startdate);
				$data->eventstart_date = date('Y-m-d', strtotime($startDateTime[0]));
				$data->start_time = isset($startDateTime[1]) ? $startDateTime[1] : '';
			}
			if (!empty($data->enddate)) {
				$endDateTime = explode(' ', $data->enddate);
				$data->eventend_date = date('Y-m-d', strtotime($endDateTime[0]));
				$data->end_time = isset($endDateTime[1]) ? $endDateTime[1] : '';
			}
		}

		return $data;
	}

	/**
	 * Method to get a single record.
	 *
	 * @param   integer  $pk  The id of the primary key.
	 *
	 * @return  mixed    Object on success, false on failure.
	 *
	 * @since    1.6
	 */
	public function getItem($pk = null)
	{
		$jticketingfrontendhelper = new Jticketingfrontendhelper;
		$com_params    = ComponentHelper::getParams('com_jticketing');
		$enable_tags = $com_params->get('show_tags', '0', 'INT');
		$this->collect_attendee_info_checkout = $com_params->get('collect_attendee_info_checkout');

		if ($item = parent::getItem($pk))
		{
			if (isset($item->recurring_params) && !empty($item->recurring_params)) {
				$params = json_decode($item->recurring_params, true);
				$item->repeat_interval = $params['repeat_interval'] ?? 1;
				$item->repeat_count    = $params['repeat_count'] ?? 0;
				$item->repeat_until    = $params['repeat_until'] ?? '';
				$item->repeat_via = (empty($item->repeat_until) || !strtotime($item->repeat_until)) ? 'rep_count' : 'rep_until';
			}
			if (!empty($item->id))
			{
				$xrefId = JT::event($item->id, 'com_jticketing');
				BaseDatabaseModel::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jticketing/models', 'integrationxref');
				$JTIcketingModelIntegrationXref = BaseDatabaseModel::getInstance('Integrationxref', 'JTicketingModel');

				if (!empty($xrefId))
				{
					$integrationData = $JTIcketingModelIntegrationXref->getItem($xrefId->integrationId);

					if (!empty($integrationData->vendor_id))
					{
						$item->vendor_id = $integrationData->vendor_id;
					}
				}

				$ticketTypes = array();
				$attendeeFields = array();
				$db = Factory::getDbo();

				// Load ticket types data
				$query = $db->getQuery(true);
				$query->select('*');
				$query->from($db->quoteName('#__jticketing_types'));

				if (!empty($xrefId))
				{
					$query->where($db->quoteName('eventid') . " = " . $db->quote($xrefId->integrationId));
				}

				$db->setQuery($query);
				$ticketTypes = $db->loadObjectList();
				$item->tickettypes = $ticketTypes;

				if ($this->collect_attendee_info_checkout == 1)
				{
					// Load attendee fields data
					$query1 = $db->getQuery(true);
					$query1->select('*');
					$query1->from($db->quoteName('#__jticketing_attendee_fields'));

					if (!empty($xrefId))
					{
						$query1->where($db->quoteName('eventid') . " = " . $db->quote($xrefId->integrationId));
					}

					$query1->where($db->quoteName('core') . " != " . $db->quote('1'));

					$db->setQuery($query1);
					$item->attendeefields = $db->loadObjectList();
				}

				// Getting User privacy data value for campaign
				Table::addIncludePath(JPATH_ROOT . '/administrator/components/com_tjprivacy/tables');
				$userPrivacyTable = Table::getInstance('tj_consent', 'TjprivacyTable', array());
				$userPrivacyData = $userPrivacyTable->load(
												array(
														'client' => 'com_jticketing.eventform',
														'client_id' => $item->id ,
														'user_id' => $item->created_by
													)
											);

				$item->privacy_terms_condition = $userPrivacyData;

				if ($item->id)
				{
					$modelMedia     = BaseDatabaseModel::getInstance('Media', 'JticketingModel');
					$jtParams       = ComponentHelper::getParams('com_jticketing');
					$eventImagePath = $jtParams->get('jticketing_media_upload_path', 'media/com_jticketing/events');

					if (file_exists(JPATH_LIBRARIES . "/techjoomla/media/tables/files.php")) { require_once JPATH_LIBRARIES . "/techjoomla/media/tables/files.php"; }

					$mediaXrefLib  = TJMediaXref::getInstance();
					$xrefMediaData = array('clientId' => $item->id, 'client' => 'com_jticketing.event','isGallery' => 1);
					$mediaGallery  = $mediaXrefLib->retrive($xrefMediaData);

					if ($mediaGallery)
					{
						$galleryFiles = array();
						$config = array();

						foreach ($mediaGallery as $mediaXref)
						{
							$config['id'] = $mediaXref->media_id;

							$filetable = Table::getInstance('Files', 'TJMediaTable');

							// Load the object based on the id or throw a warning.
							$filetable->load($mediaXref->media_id);

							$mediaType    = explode(".", $filetable->type);
							$eventImgPath = $eventImagePath . '/' . $mediaType[0] . 's';

							$config['uploadPath'] = $eventImgPath;
							$galleryFiles[]       = TJMediaStorageLocal::getInstance($config);
						}

						$item->gallery = $galleryFiles;
					}

					$eventMainImage = $modelMedia->getEventMedia($item->id, 'com_jticketing.event', 0);

					if (!empty($eventMainImage))
					{
						$filetable = Table::getInstance('Files', 'TJMediaTable');

						// Load the object based on the id or throw a warning.
						// Load the object based on the id or throw a warning.
						foreach ($eventMainImage as $eventImage)
						{
							$filetable->load($eventImage->media_id);

							$mediaType   = explode(".", $filetable->type);
							$imgPath     = $eventImagePath . '/' . $mediaType[0] . 's';
							$mediaConfig = array('id' => $eventImage->media_id, 'uploadPath' => $imgPath);
							$image = TJMediaStorageLocal::getInstance($mediaConfig);
							$imgparams = json_decode($image->params);

							if (empty($imgparams->detail))
							{
								$item->image = $image;
							}
							else
							{
								$item->coverImage = $image;
							}
						}
					}

					if ($enable_tags == 1)
					{
						$item->tags = new TagsHelper;
						$item->tags->getTagIds($item->id, 'com_jticketing.event');
					}
				}

				// Set Certificate Template & Certificate Expiry fields
				if (!empty($item->params) && !empty($item->params['certificate_template']))
				{
					$item->certificate_template = $item->params['certificate_template'];
					$item->certificate_expiry   = $item->params['certificate_expiry'];
				}
			}
		}

		return $item;
	}

	/**
	 * Method to save an event data.
	 *
	 * @param   array  $data  data
	 *
	 * @return  mixed  Id on success and false on failure
	 *
	 * @since    1.6
	 */
	public function save($data)
	{
		$app                      = Factory::getApplication();
		$user                     = Factory::getUser();
		$config                   = JT::config();
		$jticketingfrontendhelper = new jticketingfrontendhelper;
		$collectAttendeeInfo      = $config->get('collect_attendee_info_checkout');
		$xrefData                 = array();
		$filter                   = InputFilter::getInstance();
		$tjvendorFrontHelper      = new TjvendorFrontHelper;
		$tjvendorsHelper          = new TjvendorsHelper;
		$creatorId                = $user->id;
		$adminApproval            = JT::config()->get('event_approval');

		// Create event in Backend for another user (Need to select vendor id based on event creator)
		if (!$data['id'] && isset($data['created_by']) && $data['created_by'])
		{
			$creatorId = $data['created_by'];
		}

		if (!empty($data['id']))
		{
			$creatorId = $data['created_by'];
			$data['isAuthorizedToEdit'] = $this->checkOwnership($data['vendor_id'], $data['created_by'], $data['id'], 'save');
		}

		// If user is authorise to create or edit event.
		if (isset($data['isAuthorizedToEdit']) || $user->authorise('core.create', 'com_jticketing'))
		{
			// Checked if the user is a vendor
			$getVendorId = $tjvendorFrontHelper->checkVendor($creatorId, 'com_jticketing');

			// Collecting vendor data
			$vendorData                  = array();
			$vendorData['vendor_client'] = "com_jticketing";
			$vendorData['user_id']       = $creatorId;

			$userName                   = Factory::getUser($vendorData['user_id'])->name;
			$vendorData['vendor_title'] = $userName;
			$vendorData['state']        = "1";
			$eventLevelStartNumber = $data['start_number_for_event_level_sequence'];

			$paymentDetails                    = array();
			$paymentDetails['payment_gateway'] = '';
			$vendorData['paymentDetails']      = json_encode($paymentDetails);

			Table::addIncludePath(JPATH_ROOT . '/administrator/components/com_tjvendors/tables');
			$table = Table::getInstance('vendor', 'TJVendorsTable', array());
			$table->load(array('user_id' => $creatorId));

			$xrefData['vendor_id'] = $getVendorId;

			// Check for vendor's id if not adds a vendor
			if (empty($table->vendor_id))
			{
				$xrefData['vendor_id'] = $tjvendorsHelper->addVendor($vendorData);
			}
			elseif (empty($getVendorId))
			{
				$vendorData['vendor_id'] = $table->vendor_id;
				$xrefData['vendor_id']       = $tjvendorsHelper->addVendor($vendorData);
			}

			$date = Factory::getDate();
			$recurringParams = [
				'repeat_interval' => $data['repeat_interval'] ?? 1,  // Default to 1 if not set
			];
			if ($data['repeat_via'] === 'rep_count') {
				$recurringParams['repeat_count'] = $data['repeat_count'] ?? 0;
				$recurringParams['repeat_until'] = '';
			} elseif ($data['repeat_via'] === 'rep_until') {
				$recurringParams['repeat_count'] = 0;
				$recurringParams['repeat_until'] = $data['repeat_until'] ?? '';
			}
			// Encode it into JSON
			$data['recurring_params'] = json_encode($recurringParams);
			if ($data['id'])
			{
				$data['modified'] = $date->toSql();
			}
			else
			{
				$data['created'] = $date->toSql();
			}

			// Save certificate data in params
			if (!empty($data['certificate_template']))
			{
				if (JT::utilities()->isJSON($data['params']))
				{
					$data['params'] = $eventData;
				}

				$data['params']['certificate_template'] = $data['certificate_template'];
				$data['params']['certificate_expiry']   = $data['certificate_expiry'];
				$data['params']                         = json_encode($data['params']);
			}

			// Save certificate data in params
			if (!empty($data['certificate_template']))
			{
				if (JT::utilities()->isJSON($data['params']))
				{
					$data['params'] = json_decode($data['params'], true);
				}

				$data['params']['certificate_template'] = $data['certificate_template'];
				$data['params']['certificate_expiry']   = $data['certificate_expiry'];
				$data['params']                         = json_encode($data['params']);
			}

			// TRIGGER Before Event Create
			PluginHelper::importPlugin('system');
			Factory::getApplication()->triggerEvent('onBeforeJtEventCreate', array($data));
			$table = $this->getTable();

			if ($data['id'] != 0)
			{
				$oldEventData = $this->getItem($data['id']);
			}

			// Bind data
			if (!$table->bind($data))
			{
				$this->setError($table->getError());

				return false;
			}

			$this->name = "event";
			$onlineEvent = false;

			// Trigger online event method to create online event
			if (empty($data['id']))
			{
				// Check for the online_event parameter
				$onlineEvent = $data['online_events'] == 1;
			}
			else
			{
				$event = JT::event($data['id']);
				$onlineEvent = $event->isOnline();
			}

			if ($onlineEvent)
			{
				// Trigger online event method to create online event
				if (!empty($data['id']))
				{
					$event = JT::event($data['id']);
				}
				else
				{
					$event = JT::onlineEvent(JT::venue($data['venue']));
				}

				if ($app->isClient('site'))
				{
					$data['beginDate'] = $data['startdate'];
					$data['onlineEndDate'] = $data['enddate'];
				}

				if (!$event->save($data))
				{
					$this->setError($event->getError());

					return false;
				}

				$oldParams = new Registry($data['params']);
				$newParams = new Registry($event->params);
				$oldParams->merge($newParams);
				$data['params'] = $oldParams->toArray();
			}

			$event = JT::event($data['id']);

			$oldParams = new Registry(!empty($data['params']) ? $data['params'] : array());
			$newParams = new Registry($event->params);
			$newParams->merge($oldParams);
			$data['params'] = $newParams->toArray();

			// For editing event and empty end date as we do not set end date empty by default
			if ($data['booking_start_date'] == '')
			{
				$data['booking_start_date'] = '0000-00-00 00:00:00';
			}

			if ($data['booking_end_date'] == '')
			{
				$data['booking_end_date'] = '0000-00-00 00:00:00';
			}

			if (!empty($data['venue']))
			{
				$data['latitude'] = 0.0;
				$data['longitude'] = 0.0;
			}

			if (parent::save($data))
			{
				$id = (int) $this->getState($this->getName() . '.id');

				if (isset($data['tags']) && $data['tags'])
				{
					$eventTable = $this->getTable();
					$eventTable->load(array('id' => $id));
					$eventTable->newTags = $data['tags'];
					$eventTable->store();
				}

				if ($config->get('tnc_for_create_event') == '1')
				{
					// Save User Privacy Terms and conditions Data
					Table::addIncludePath(JPATH_ROOT . '/administrator/components/com_tjprivacy/tables');
					$userPrivacyTable = Table::getInstance('tj_consent', 'TjprivacyTable', array());
					$userPrivacyData = $userPrivacyTable->load(
													array(
															'client' => 'com_jticketing.eventform',
															'client_id' => $id ,
															'user_id' => $data['created_by']
														)
												);

					if ($userPrivacyData == false)
					{
						$input		= Factory::getApplication()->getInput();
						$task 		= $input->post->get('task', '');

						if ($data['privacy_consent'] == 'on' || $task == 'events.duplicate')
						{
							$userPrivacyData = array();
							$userPrivacyData['client'] = 'com_jticketing.eventform';
							$userPrivacyData['purpose'] = Text::_('COM_JTICKETING_USER_PRIVACY_TERMS_PURPOSE_FOR_EVENT');

							/* later pass privacy_terms_condition value 1*/
							$userPrivacyData['accepted'] = 1;
							$userPrivacyData['user_id'] = $data['created_by'];
							$date = Factory::getDate();

							$userPrivacyData['date'] = $date->toSql(true);
							$userPrivacyData['client_id'] = $id;

							if (file_exists(JPATH_SITE . '/components/com_tjprivacy/models/tjprivacy.php')) { require_once JPATH_SITE . '/components/com_tjprivacy/models/tjprivacy.php'; }
							$tjprivacyModel = BaseDatabaseModel::getInstance('Tjprivacy', 'TjprivacyModel');

							$tjprivacyModel->save($userPrivacyData);
						}
						else
						{
							return false;
						}
					}
				}

				if ($id)
				{
					// Check xref ID is exist in intergration xref table or not.
					$existingId = JT::event($id, 'com_jticketing');
					$xrefData['id'] = !empty($existingId->integrationId) ? $existingId->integrationId : '';
					$xrefData['eventid'] = $id;
					$xrefData['source'] = "com_jticketing";
					$xrefData['userid'] = $data['created_by'];
					$xrefData['enable_ticket'] = 1;

					BaseDatabaseModel::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jticketing/models', 'integrationxref');
					$JTIcketingModelIntegrationXref = BaseDatabaseModel::getInstance('Integrationxref', 'JTicketingModel', array('ignore_request' => true));
					$JTIcketingModelIntegrationXref->save($xrefData);

					$ticketTypes = $data['tickettypes'];
					BaseDatabaseModel::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jticketing/models', 'tickettype');
					$ticketTypeModel = BaseDatabaseModel::getInstance('Tickettype', 'JticketingModel');

					$xrefId = new stdClass;
					$xrefId->integrationId = empty($existingId->integrationId) ?
					(int) $JTIcketingModelIntegrationXref->getState($JTIcketingModelIntegrationXref->getName() . '.id') :
					$existingId->integrationId;
					$existingTicketTypes = $ticketTypeModel->getTicketTypes($xrefId->integrationId);

					$tickets = array();
					$newTicketTypes = array();
					$existingId = array();
					$validCount = 0;
					$existingCount = 0;

					foreach ($ticketTypes as $ticketType)
					{
						if (!empty($ticketType['id']))
						{
							$tickets['id'] = $ticketType['id'];

						// Ticket count should be remaining tickets.
							if (!empty($ticketType['count']))
							{
								$tickets['count'] = $ticketType['count'];
							}
						}
						else
						{
							$tickets['id'] = '';
							$tickets['count'] = $ticketType['available'];

							if (!empty($ticketType['count']))
							{
								$tickets['count'] = $ticketType['count'];
							}
						}

						$tickets['title'] = $filter->clean($ticketType['title'], 'string');
						$tickets['desc'] = $ticketType['desc'];

						if (!empty($ticketType['ticket_enddate']))
						{
							$tickets['ticket_enddate'] = $ticketType['ticket_enddate'];

							// Due to the Joomla subform valiadtion rule
							if (version_compare(JVERSION, '3.9.7', '<'))
							{
								$config = Factory::getConfig();
								$user = Factory::getUser($creatorId);
								$ticketEndDt = Factory::getDate($ticketType['ticket_enddate'], $user->getParam('timezone', $config->get('offset')));
								$ticketEndDt->setTimezone(new DateTimeZone('UTC'));
								$ticketEndDt = $ticketEndDt->toSql(true);
								$tickets['ticket_enddate'] = $ticketEndDt;
							}
						}
						else
						{
							$tickets['ticket_enddate'] = '';
						}

						$tickets['unlimited_seats'] = $ticketType['unlimited_seats'];
						$tickets['available'] = $ticketType['available'];

						// If ticket count is 0 while editing ticket type
						if (!empty($ticketType['id']) && empty($ticketType['count']) && $ticketType['unlimited_seats'] == 0)
						{
							$ticketDetails = $ticketTypeModel->getItem($ticketType['id']);

							if ($ticketDetails->available < $ticketType['available'])
							{
								$tickets['count'] = $ticketType['available'] - $ticketDetails->available;
							}
							elseif($ticketDetails->available != $ticketType['available'])
							{
								$ticketType['available'] = $ticketDetails->available;

								$this->setError(Text::_('COM_JTICKETING_TICKET_REMAINING_SEATS_COUNT_ERROR'));

								return false;
							}
						}

						// If remaining ticket count available while editing ticket type
						if (!empty($ticketType['id']) && !empty($ticketType['count']) && $ticketType['unlimited_seats'] == 0)
						{
							$ticketDetails = $ticketTypeModel->getItem($ticketType['id']);

							if ($ticketDetails->available < $ticketType['available'])
							{
								$tickets['count'] = ($ticketType['available'] - $ticketDetails->available) + $ticketDetails->count;
							}
							elseif($ticketDetails->available > $ticketType['available'] && $ticketDetails->count < $ticketType['available'])
							{
								$tickets['count'] = $ticketDetails->count - ($ticketDetails->available - $ticketType['available']);
							}
							elseif ($ticketDetails->available > $ticketType['available'] && $ticketDetails->count > $ticketType['available'])
							{
								$tickets['count'] = $ticketDetails->count - ($ticketDetails->available - $ticketType['available']);
							}
						}

						$tickets['state'] = $ticketType['state'];
						$tickets['price'] = $ticketType['price'];

						if ($config->get('entry_number_assignment', 0,'INT'))
						{
							if (isset($ticketType['allow_ticket_level_sequence']) && $ticketType['allow_ticket_level_sequence'])
							{
								$tickets['allow_ticket_level_sequence'] = 1;
								$tickets['start_number_for_sequence'] = $ticketType['start_number_for_sequence'] ? $ticketType['start_number_for_sequence'] : '1';
							}
							else
							{
								$tickets['allow_ticket_level_sequence'] = 0;
								$tickets['start_number_for_sequence'] = $eventLevelStartNumber ? $eventLevelStartNumber : '1';
							}
						}
						else
						{
							$tickets['start_number_for_sequence'] = 0;
							$tickets['allow_ticket_level_sequence'] = 0;
						}

						$tickets['max_ticket_per_order'] = $ticketType['max_ticket_per_order'];

						if ((int) $config->get('show_access_level') === 0)
						{
							$tickets['access'] = $config->get('default_accesslevels', '1');
						}
						else
						{
							$tickets['access'] = $ticketType['access'];
						}

						$tickets['eventid'] = $xrefId->integrationId;

						if ($ticketType['unlimited_seats'] != 1)
						{
							$attendeeModel = JT::model('attendees');
							$attendeesResult  = $attendeeModel->getItems();
							$bookedTicketCount = 0;

							foreach ($attendeesResult as $attendee)
							{
								if ($attendee->ticket_type_title == $ticketType['title'] && $attendee->event_id == $id)
								{
									$bookedTicketCount++;
								}
							}

							if ($bookedTicketCount > $tickets['available'] )
							{
								if ($app->isClient('site'))
								{
									$error = Text::sprintf('COM_JTICKETING_TICKET_REMAINING_SEATS_COUNT_ERROR_MULTIPLE', $bookedTicketCount, $ticketType['title']);
									$app->enqueueMessage($error, 'error');
								}
								else
								{
									$error = Text::sprintf('COM_JTICKETING_TICKET_REMAINING_SEATS_COUNT_ERROR_MULTIPLE', $bookedTicketCount, $ticketType['title']);
									$this->setError($error);
								}

								return false;
							}
							else
							{
								$tickets['count'] = $tickets['available'] - $bookedTicketCount;
							}
						}

						if (!$ticketTypeModel->save($tickets))
						{
							return false;
						}
					}

					foreach ($existingTicketTypes as $existingTicketType)
					{
						$existingId[$existingCount] = $existingTicketType['id'];
						$existingCount++;

						foreach ($ticketTypes as $ticketType)
						{
							if ($ticketType['id'] == $existingTicketType['id'])
							{
								$newTicketTypes[$validCount] = $ticketType['id'];
								$validCount++;
							}
						}
					}

					$invalidTicketTypeIds = array_diff($existingId, $newTicketTypes);

					foreach ($invalidTicketTypeIds as $invalidId)
					{
						// Make a check if this particular ticket type has any orders against it.
						$ticketOrder = $ticketTypeModel->checkOrderExistsTicketType($invalidId);

						if (empty($ticketOrder))
						{
							$ticketTypeModel->delete($invalidId);
						}
						else
						{
							$app->enqueueMessage(Text::_('COM_JTICKETING_EVENT_TICKET_TYPES_DELETE_ERROR'), 'warning');

							return false;
						}
					}

					if ($collectAttendeeInfo == 1)
					{
						// Save Attendee fields
						$attendeeFields = $data['attendeefields'];
						$attendeeFieldsModel = BaseDatabaseModel::getInstance('Attendeefields', 'JticketingModel');
						$attendeeCoreFieldsModel = BaseDatabaseModel::getInstance('AttendeeCoreFields', 'JticketingModel');
						$existingAttendeeFields = $attendeeCoreFieldsModel->getAttendeeFields($xrefId->integrationId);
						$attendeeFieldsArray = array();
						$validAttendeeField = array();
						$existingId = array();
						$validCount = 0;
						$existingCount = 0;

						foreach ($attendeeFields as $attendeeField)
						{
							if (!empty($attendeeField['id']))
							{
								$attendeeFieldsArray['id'] = $attendeeField['id'];
							}
							else
							{
								$attendeeFieldsArray['id'] = '';
							}

							$attendeeFieldsArray['label'] = $attendeeField['label'];
							$attendeeFieldsArray['type'] = $attendeeField['type'];
							$attendeeFieldsArray['core'] = 0;
							$attendeeFieldsArray['default_selected_option'] = $attendeeField['default_selected_option'];
							$attendeeFieldsArray['required'] = $attendeeField['required'];
							$attendeeFieldsArray['eventid'] = $xrefId->integrationId;
							$attendeeFieldsArray['state'] = 1;

							if (!empty($attendeeFieldsArray['label']))
							{
								$string = strtolower($attendeeField['label']);
								$string = str_replace(' ', '_', $string);
								$attendeeFieldsArray['name'] = preg_replace('/[^A-Za-z0-9\-]/', '', $string);
								$return = $attendeeFieldsModel->save($attendeeFieldsArray);
							}

							if ($attendeeFieldsArray['type'] == "single_select"
								|| $attendeeFieldsArray['type'] == "multi_select" || $attendeeFieldsArray['type'] == "radio")
							{
								if ($attendeeFieldsArray['default_selected_option'] == "" && $attendeeFieldsArray['label'] != "")
								{
									$error1 = Text::_('COM_JTICKETING_INVALID_FIELD') . Text::_('COM_JTICKETING_TICKET_FIELD_DEFAULT_OPTION_LABEL');
									$error2 = Text::_('COM_JTICKETING_EVENT_ATTENDEE_FIELD');
									$app->enqueueMessage($error1 . $error2 . $attendeeFieldsArray['label'], 'error');

									return false;
								}

								if ($attendeeFieldsArray['label'] == "" && $attendeeFieldsArray['default_selected_option'] != "" )
								{
									$app->enqueueMessage(Text::_('COM_JTICKETING_INVALID_ATTENDEE_TITLE'), 'error');

									return false;
								}
							}
						}

						foreach ($existingAttendeeFields as $existingAttendeeField)
						{
							$existingId[$existingCount] = $existingAttendeeField['id'];
							$existingCount++;

							foreach ($attendeeFields as $attendeeField)
							{
								if ($attendeeField['id'] == $existingAttendeeField['id'])
								{
									$validAttendeeField[$validCount] = $attendeeField['id'];
									$validCount++;
								}
							}
						}

						$invalidAttendeeFieldIds = array_diff($existingId, $validAttendeeField);

						foreach ($invalidAttendeeFieldIds as $invalidId)
						{
							$attendeeFieldCheck = $attendeeFieldsModel->checkAttendeeFieldValue($invalidId);

							if (empty($attendeeFieldCheck))
							{
								$attendeeFieldsModel->delete($invalidId);
							}
							else
							{
								$app->enqueueMessage(Text::_('COM_JTICKETING_EVENT_ATENDEE_FIELDS_DELETE_ERROR'), 'warning');

								return false;
							}
						}
					}

					$eventImagePath = $config->get('jticketing_media_upload_path', 'media/com_jticketing/events');

					if (file_exists(JPATH_LIBRARIES . "/techjoomla/media/tables/xref.php")) { require_once JPATH_LIBRARIES . "/techjoomla/media/tables/xref.php"; }
					$tableXref = Table::getInstance('Xref', 'TJMediaTable');

					if (!empty($data['image']['new_image']))
					{
						if ($this->saveMedia($data['image']['new_image'], 0, $id))
						{
							// Store params of Media for detail image
							$mediaParams            = array();

							// Cover Image is not present so detail in params will be 0
							$mediaParams["detail"]  = 0;
							$table                  = $this->getTable('media');
							$table->params          = json_encode($mediaParams);
							$table->id              = $data['image']['new_image'];
							$table->store();

							$xrefData = array('client_id' => $id, 'client' => 'com_jticketing.event', 'media_id' =>
								$data['image']['old_image']);
							$tableXref->load($xrefData);

							if ($tableXref->id)
							{
								$xrefConfig = array('id' => $tableXref->id);
								$modelMediaXref = TJMediaXref::getInstance($xrefConfig);

								if ($modelMediaXref->delete())
								{
									$configtoStoreImage = array();
									$configtoStoreImage['id'] = $data['image']['old_image'];
									$configtoStoreImage['uploadPath'] = $eventImagePath;

									$mediaData = TJMediaStorageLocal::getInstance($configtoStoreImage);

									// To check whether  old media id is present in media_xref table
									$oldXrefData = array('media_id' => $data['image']['old_image']);
									$resultOldXrefData = $tableXref->load($oldXrefData);

									if (empty($resultOldXrefData))
									{
										$mediaData->delete();
									}
								}
							}
						}
					}

					if (!empty($data['coverImage']['new_image']))
					{
						if ($this->saveMedia($data['coverImage']['new_image'], 0, $id))
						{
							// Store params of Media for detail image
							$mediaParams            = array();

							// Cover Image is present so detail in params will be 1
							$mediaParams["detail"]  = 1;
							$table                  = $this->getTable('media');
							$table->params          = json_encode($mediaParams);
							$table->id              = $data['coverImage']['new_image'];
							$table->store();

							$xrefData = array('client_id' => $id, 'client' => 'com_jticketing.event', 'media_id' =>
								$data['coverImage']['old_image']);
							$tableXref->load($xrefData);

							if ($tableXref->id)
							{
								$xrefConfig = array('id' => $tableXref->id);
								$modelMediaXref = TJMediaXref::getInstance($xrefConfig);

								if ($modelMediaXref->delete())
								{
									$configtoStoreImage = array();
									$configtoStoreImage['id'] = $data['coverImage']['old_image'];
									$configtoStoreImage['uploadPath'] = $eventImagePath;
									$mediaData = TJMediaStorageLocal::getInstance($configtoStoreImage);

									// To check whether  old media id is present in media_xref table
									$oldXrefData = array('media_id' => $data['coverImage']['old_image']);
									$resultOldXrefData = $tableXref->load($oldXrefData);

									if (empty($resultOldXrefData))
									{
										$mediaData->delete();
									}
								}
							}
						}
					}

					if (isset($data['gallery_file']['media']))
					{
						$this->saveMedia($data['gallery_file']['media'], 1, $id);
					}
				}

				$socialintegration = $config->get('integrate_with', 'none');
				$streamAddEvent    = $config->get('streamAddEvent', 0);

				// Add event create activity in EasySocial/JomSocial activity
				if ($socialintegration != 'none')
				{
					$user       = Factory::getUser();
					$orderModel = JT::model('order');
					$libclass   = $orderModel->getJticketSocialLibObj();

					// Add in activity.
					if ($streamAddEvent)
					{
						$eventObj = JT::event($id);
						$linkMode    = Factory::getConfig()->get('force_ssl', 0) == 2 ? Route::TLS_FORCE : Route::TLS_IGNORE;
						$link = Route::link('site', $eventObj->getUrl(false), false, $linkMode, true);
						$eventLink   = '<a class="" href="' . $link . '">' . $data['title'] . '</a>';
						$originalMsg = ($eventObj->getCreator() != $user->id || !empty($data['id'])) ? Text::sprintf('COM_JTICKETING_ACTIVITY_UPDATED_EVENT', $eventLink) : Text::sprintf('COM_JTICKETING_ACTIVITY_ADD_EVENT', $eventLink);

						$libclass->pushActivity($user->id, $act_type = '', $act_subtype = '', $originalMsg, $act_link = '', $title = '', $act_access = 0);
					}
				}
				$data['eventId'] = $id;
				// Prepare recurring event data
				$recurringData = [
					'event_id'   => $id,
					'recurring_type'=>$data['recurring_type'],
					'repeat_interval'=>$data['repeat_interval'],
					'repeat_count'=>$data['repeat_count'],
					'repeat_until'=>$data['repeat_until'],
					'startdate'  => $data['startdate'],
					'enddate'    => $data['enddate'],
					'repeat_via' => $data['repeat_via']
				];
				// Load the RecurringEvent model and save the data
				$recurringModel = $this->getInstance('RecurringEvents', 'JticketingModel');
				$saveResult = $recurringModel->save($recurringData);

				if ($saveResult === false) {
					$this->setError($recurringModel->getError());
					return false;
				}
				// Make obj of event class
				$jtTriggerEvent = new JticketingTriggerEvent;

				// Edit event case
				if ($data['id'])
				{
					$data['eventOldData'] = $oldEventData;

					// JT event class trigger
					$jtTriggerEvent->onAfterEventSave($data, false);

					// New plugin trigger
					PluginHelper::importPlugin('jticketing');
					Factory::getApplication()->triggerEvent('onAfterJtEventSave', array($data, false));
				}
				// New event case
				else
				{
					// JT event class trigger
					$jtTriggerEvent->onAfterEventSave($data, true);

					// New plugin trigger
					PluginHelper::importPlugin('jticketing');
					Factory::getApplication()->triggerEvent('onAfterJtEventSave', array($data, true));
				}

				// Old plugin trigger
				PluginHelper::importPlugin('system');
				Factory::getApplication()->triggerEvent('onAfterJtEventCreate', array($data));

				if ($adminApproval != 0 && $data['state'] == 0)
				{
					// Send admin approval notification
					JticketingMailHelper::sendAdminApproval($id);
				}

				return $id;
			}
			else
			{
				return false;
			}
		}
	}

	/**
	 * Method to get book venue
	 *
	 * @param   array  $event  An array to get the item values
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function getvenuehtml($event)
	{
		$array_venue = array();
		$db = Factory::getDbo();
		$selectedvenue = $event->venue;

		$array_venue['venue'] = $selectedvenue;
		$array_venue['start_dt_timestamp'] = $event->startdate;
		$array_venue['end_dt_timestamp'] = $event->enddate;
		$array_venue['event_online'] = $event->online_events;
		$array_venue['created_by'] = $event->created_by;

		$getvenue = $this->getAvailableVenue($array_venue);
		$options = array();

		if (!empty($getvenue))
		{
			foreach ($getvenue as $u)
			{
				$options[] = HTMLHelper::_('select.option', $u->id, $u->name);
			}
		}
		else
		{
			$u = new stdClass;
			$u->name = '';
		}

		return HTMLHelper::_('select.genericlist', $options, $u->name, 'class="inputbox"  size="5"', 'value', 'text', $selectedvenue);
	}

	/**
	 * Method to get book venue
	 *
	 * @param   array  $array_venue  An array to get the booked values
	 *
	 * @return  array|array<mixed,mixed>
	 *
	 * @since   1.6
	 */
	public function getAvailableVenue($array_venue)
	{
		$db = Factory::getDbo();
		$jinput = Factory::getApplication()->getInput();
		$eventid = $jinput->get('id', '', 'STRING');

		$venue = $array_venue['venue'];
		$array_venue['start_dt_timestamp'];
		$array_venue['end_dt_timestamp'];
		$array_venue['event_online'];
		$created_by = $array_venue['created_by'];
		$query = $db->getQuery(true);

		if (!empty($eventid))
		{
		$query = "SELECT  v.id,v.name,v.created_by,v.privacy,v.state FROM   #__jticketing_venues AS v
			WHERE NOT EXISTS(SELECT NULL FROM #__jticketing_events AS ed WHERE ed.venue = v.id AND(('"
		. $array_venue["start_dt_timestamp"] . "' BETWEEN UNIX_TIMESTAMP(ed.startdate) AND UNIX_TIMESTAMP(ed.enddate)) OR ('"
		. $array_venue["end_dt_timestamp"] . "' BETWEEN UNIX_TIMESTAMP(ed.startdate) AND UNIX_TIMESTAMP(ed.enddate)) OR ('"
		. $array_venue["start_dt_timestamp"] . "' <= UNIX_TIMESTAMP(ed.startdate) AND '"
		. $array_venue["end_dt_timestamp"] . "' >= UNIX_TIMESTAMP(ed.enddate))
		 )) AND v.online = "
		. $array_venue['event_online'] . " AND v.state = 1 AND (v.created_by ='"
		. $created_by . "' OR v.privacy = 1) AND v.id != '" . $eventid . "'";
		}
		else
		{
			$query = "SELECT  v.id,v.name,v.created_by,v.privacy,v.state FROM   #__jticketing_venues AS v
			WHERE NOT EXISTS(SELECT NULL FROM #__jticketing_events AS ed WHERE ed.venue = v.id AND(('"
		. $array_venue["start_dt_timestamp"] . "' BETWEEN UNIX_TIMESTAMP(ed.startdate) AND UNIX_TIMESTAMP(ed.enddate)) OR ('"
		. $array_venue["end_dt_timestamp"] . "' BETWEEN UNIX_TIMESTAMP(ed.startdate) AND UNIX_TIMESTAMP(ed.enddate)) OR ('"
		. $array_venue["start_dt_timestamp"] . "' <= UNIX_TIMESTAMP(ed.startdate) AND '"
		. $array_venue["end_dt_timestamp"] . "' >= UNIX_TIMESTAMP(ed.enddate))
		 )) AND v.online = " . $array_venue['event_online'] . " AND v.state = 1 AND (v.created_by ='" . $created_by . "' OR v.privacy = 1)";
		}

		$db->setQuery($query);
		$events = $db->loadObjectList();

		return $events;
	}

	/**
	 * Method to get venue list depending on the vendor chosen.
	 *
	 * @param   string  $eventData  event data
	 *
	 * @return   array result
	 *
	 * @since    1.6
	 */
	public function getVenueList($eventData)
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$filter = InputFilter::getInstance();
		// Select the required fields from the table.
		$startdate = $eventData['eventstart_date'] . ' ' . $eventData['start_time'];
		$enddate = $eventData['eventend_date'] . ' ' . $eventData['end_time'];
		$query->select('venue');
		$query->from('#__jticketing_events');
		$query->where($db->quoteName('startdate') . ' BETWEEN ' . $db->quote($eventData['eventStartDate']));
		$query->where($db->quote($eventData['eventEndDate']) . 'OR' . $db->quoteName('enddate') . ' BETWEEN ' . $db->quote($eventData['eventStartDate']));
		$query->where($db->quote($eventData['eventEndDate']));
		$db->setQuery($query);
		$eventOnDate = $db->loadAssocList();
		$query = $db->getQuery(true);
			$query->select('id,name');
			$query->from('#__jticketing_venues');

		foreach ($eventOnDate as $event)
		{
			$query->where($db->quoteName('id') . ' != ' . $db->quote($event['venue']));
		}

		$query->where($db->quoteName('online') . ' = ' . $db->quote($eventData['radioValue']));
		$query->where($db->quoteName('privacy') . ' != 0');
		$query->where($db->quoteName('state') . ' = 1');
		$query->order($db->quoteName('name'));
		$db->setQuery($query);
		$venuesAvailable = $db->loadAssocList();

		if ($eventData['silentVendor'] != 1)
		{
			$privateVenues = $this->getPrivateVenues($eventData['vendor_id'], $eventData['radioValue']);
		}
		else
		{
			$vendorId = $this->getVendorId($eventData['created_by'], $eventData['radioValue']);
			$privateVenues = $this->getPrivateVenues($vendorId, $eventData['radioValue']);
		}

		$Available = $privateVenues + $privateVenues;

		foreach ($privateVenues as $venue)
		{
			$venuesAvailable[] = array("id" => $venue['id'], "name" => $venue['name']);
		}

		/* Add Custom location option in 2 cases
		 * 1. For new event
		 * 2. User should be able to add Custom location in case of edit event
		 */
		if (($eventData['radioValue'] == 0 && empty($eventData['eventId']))
			||($eventData['radioValue'] == 0 && $eventData['eventId'] && $eventData['venueId'] != 0))
		{
			$venuesAvailable[] = array("id" => "0", "name" => Text::_('COM_JTICKETING_CUSTOM_LOCATION'));
		}

		$options = array();

		foreach ($venuesAvailable as $venue)
		{
			$name      = $filter->clean($venue['name'], 'string');
			$options[] = HTMLHelper::_('select.option', $venue['id'], $name);
		}

		return $options;
	}

	/**
	 * Method to get private venues be it online or offline of the vendor.
	 *
	 * @param   integer  $vendor_id  vendor id
	 *
	 * @param   integer  $venueType  type of venue online or offline
	 *
	 * @return   array result
	 *
	 * @since    1.6
	 */
	public function getPrivateVenues($vendor_id, $venueType)
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('id,name');
		$query->from('#__jticketing_venues');
		$query->where($db->quoteName('vendor_id') . ' = ' . $db->quote($vendor_id));
		$query->where($db->quoteName('online') . ' = ' . $db->quote($venueType));
		$query->where($db->quoteName('privacy') . ' = 0 ');
		$query->where($db->quoteName('state') . ' = 1 ');
		$query->order($db->quoteName('name'));
		$db->setQuery($query);
		$venues = $db->loadAssocList();

		return $venues;
	}

	/**
	 * Method to get Vendor id based on the venue typ[e chosen
	 *
	 * @param   integer  $created_by  user's id
	 *
	 * @param   integer  $venueType   type of venue online or offline
	 *
	 * @return   array result
	 *
	 * @since    1.6
	 */
	public function getVendorId($created_by, $venueType)
	{
			$db = Factory::getDbo();
			$query = $db->getQuery(true);
			$query->select($db->quoteName('vendor_id'));
			$query->from('#__tjvendors_vendors');
			$query->where($db->quoteName('user_id') . ' = ' . $db->quote($created_by));
			$db->setQuery($query);
			$vendor_id = $db->loadResult();

			return $vendor_id;
	}

	/**
	 * Method to get edit venue
	 *
	 * Method to create online event
	 *
	 * @param   array  $data  data
	 *
	 * @deprecated 3.0.0 the online event creation is handled in the event save method itself
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function createOnlineEvent($data)
	{
		$ticket = $data['tickettypes'];

		if ($ticket['tickettypes0']['unlimited_seats'] == 1)
		{
			$ticketCount = 'unlimited';
		}
		else
		{
			$ticketCount = array_sum($ticket['tickettypes0']['available']);
		}

		$timezone = new DateTimeZone(Factory::getConfig()->get('offset'));

		$beginDateTime = preg_split('/\s+/', $data['beginDate']);
		$startdate = new DateTime($beginDateTime[0] . 'T' . $beginDateTime[1], $timezone);

		$beginDate = $startdate->format(DateTime::ISO8601);

		$endDateTime = preg_split('/\s+/', $data['onlineEndDate']);
		$endDate = new DateTime($endDateTime[0] . 'T' . $endDateTime[1], $timezone);

		$endDate = $endDate->format(DateTime::ISO8601);

		$venueId = $data['venue'];
		$Name = $data['title'];

		// Load AnnotationForm Model
		$model = BaseDatabaseModel::getInstance('Venue', 'JticketingModel');
		$licenceContent = $model->getItem($venueId);
		$licence = (object) $licenceContent->params;
		$utilities = JT::utilities();
		$password  = $utilities->generateRandomString(8);
		$userid = $data['created_by'];
		$online_provider = ltrim($licenceContent->online_provider, "plug_tjevents_");
		$online_provider = ucfirst($online_provider);

		if (empty($userid))
		{
			$userDetail = Factory::getUser();
		}
		else
		{
			$userDetail = Factory::getUser($userid);
		}

		// TRIGGER After create event
		if (!empty($licence))
		{
			PluginHelper::importPlugin('tjevents');

			if ($data['id'] == 0 && $data['venuechoice'] == "new")
			{
				if ($licence->event_type == 'meeting')
				{
					$result = Factory::getApplication()->triggerEvent('onCreate' . $online_provider . 'Meeting', array
					($licence, $Name, $userDetail, $beginDate, $endDate, $ticketCount, $password)
					);
				}
				elseif ($licence->event_type == 'seminar')
				{
					$result = Factory::getApplication()->triggerEvent('onCreate' . $online_provider . 'Seminar', array
					($licence, $Name, $userDetail, $beginDate, $endDate, $ticketCount, $password)
					);
					$res = $result['0'];
					$data['params']['event_source_sco_id'] = $res['source_sco_id'];
				}

				$res = $result['0'];

				if ($res['error_message'])
				{
					$this->setError($res['error_message']);

					return false;
				}
				else
				{
					$data['params']['event_url'] = $res['meeting_url'];
					$data['params']['event_sco_id'] = $res['sco_id'];
					$data['params'] = json_encode($data['params']);

					return $data['params'];
				}
			}
			else
			{
				$eventData = $this->getItem($data['id']);
				$event_sco_id = $eventData->params['event_sco_id'];
				$event_url = $eventData->params['event_url'];

				if ($licence->event_type == 'meeting')
				{
					$result = Factory::getApplication()->triggerEvent('onUpdate' . $online_provider . 'Meeting', array
						($licence, $Name, $event_sco_id, $beginDate, $endDate, $event_url,$userDetail)
					);
				}
				elseif ($licence->event_type == 'seminar')
				{
					$result = Factory::getApplication()->triggerEvent('onUpdate' . $online_provider . 'Seminar', array
						($licence, $Name, $params, $beginDate, $endDate, $ticketCount, $userDetail)
					);
				}

				$res = $result['0'];

				if ($data['id'] == 0 && $data['venuechoice'] = "existing")
				{
					$data['params']['event_url'] = $data['existing_event'];
					$data['params']['event_sco_id'] = $data['onlineScoId'];
					$data['params'] = json_encode($data['params']);

					return $data['params'];
				}
				elseif ($res['error_message'])
				{
					$this->setError($res['error_message']);

					return false;
				}
				else
				{
					return $res;
				}
			}
		}

		return false;
	}

	/**
	 * Method toget required data
	 *
	 * @param   integer  $column         table volumn
	 *
	 * @param   integer  $tableName      table name
	 *
	 * @param   integer  $condition      conditions
	 *
	 * @param   integer  $integrationId  integration id
	 *
	 * @return  array|array<mixed,mixed>
	 *
	 * @since   1.6
	 */
	public function getRequiredData($column, $tableName, $condition, $integrationId)
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName($column));
		$query->from($db->quoteName($tableName));
		$query->where($db->quoteName($condition) . ' = ' . $db->quote($integrationId));
		$db->setQuery($query);

		return $db->loadObjectList();
	}

	/**
	 * Method toget integration Id
	 *
	 * @param   integer  $event_id  event id
	 *
	 * @return  object
	 *
	 * @since   1.6
	 */
	public function getIntegrationId($event_id)
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName('id'));
		$query->from($db->quoteName('#__jticketing_integration_xref'));
		$query->where($db->quoteName('eventid') . ' = ' . $db->quote($event_id));
		$db->setQuery($query);

		return $db->loadResult();
	}

	/**
	 * Method toget category name
	 *
	 * @param   integer  &$id  category id
	 *
	 * @return  boolean
	 *
	 * @since   1.6
	 */
	public function delete(&$id)
	{
		$data = $this->getItem($id);
		$eventObj = JT::event($id);

		if ($eventObj->isOnline())
		{
			// Delete the zoom meeting
			$deleteMeeting = $eventObj->delete();

			if ($deleteMeeting)
			{
				return parent::delete($id);
			}
		}
		else
		{
			$integrationId = $this->getIntegrationId($data->id);
			$ticketIds     = $this->getRequiredData('id', '#__jticketing_types', 'eventid', $integrationId);

			BaseDatabaseModel::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jticketing/models', 'Tickettype');
			$JTicketingModelTickettype = BaseDatabaseModel::getInstance('Tickettype', 'JTicketingModel');

			foreach ($ticketIds as $key => $ticketId)
			{
				$JTicketingModelTickettype->delete($ticketId->id);
			}

			$attendeeIds = $this->getRequiredData('id', '#__jticketing_attendee_fields', 'eventid', $integrationId);
			BaseDatabaseModel::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jticketing/models', 'attendeefields');
			$JTicketingModelAttendeefields = BaseDatabaseModel::getInstance('Attendeefields', 'JTicketingModel');

			foreach ($attendeeIds as $key => $attendeeId)
			{
				$JTicketingModelAttendeefields->delete($attendeeId->id);
			}

			$orderIds = $this->getRequiredData('id', '#__jticketing_order', 'event_details_id', $integrationId);
			BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_jticketing/models', 'order');
			$JticketingModelOrder = BaseDatabaseModel::getInstance('order', 'JticketingModel');
			BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_jticketing/models', 'orderItem');
			$JticketingModelOrderItem = BaseDatabaseModel::getInstance('orderItem', 'JticketingModel');

			foreach ($orderIds as $key => $orderId)
			{
				$orderItems = $JticketingModelOrderItem->getOrderItems($orderId->id);

				foreach ($orderItems as $orderItem)
				{
					$JticketingModelOrderItem->delete($orderItem->id);
				}

				$JticketingModelOrder->delete($orderId->id);
			}

			$mediaIds = $this->getRequiredData('media_id', '#__tj_media_files_xref', 'client_id', $integrationId);
			BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_jticketing/models', 'media');
			$JticketingModelMedia = BaseDatabaseModel::getInstance('media', 'JticketingModel');

			foreach ($mediaIds as $key => $mediaId)
			{
				$JticketingModelMedia->delete($mediaId->media_id);
			}

			BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_jticketing/models', 'integrationxref');
			$JticketingModelIntegrationxref = BaseDatabaseModel::getInstance('integrationxref', 'JticketingModel');
			$JticketingModelIntegrationxref->delete($integrationId);

			$jtTriggerEvent = new JticketingTriggerEvent;
			$jtTriggerEvent->onAfterEventDelete(array($data));
		}

		if (parent::delete($id))
		{
			PluginHelper::importPlugin('jticketing');
			Factory::getApplication()->triggerEvent('onAfterJtEventDelete', array($data));
		}
		else
		{
			return false;
		}

		return true;
	}

	/**
	 * Method to call media save function
	 *
	 * @param   INT  $mediaGallery  mediaGallery
	 *
	 * @param   INT  $isGallery     isGallery
	 *
	 * @param   INT  $eventId       eventId
	 *
	 * @return   array result
	 *
	 * @since    2.0
	 */
	public function saveMedia($mediaGallery, $isGallery, $eventId)
	{
		if (!is_array($mediaGallery))
		{
			$mediaGallery = (array) $mediaGallery;
		}

		foreach ($mediaGallery as $mediaId)
		{
			if ($mediaId)
			{
				$mediaXref = array();
				$mediaXref['id'] = '';
				$mediaXref['client_id'] = $eventId;
				$mediaXref['media_id'] = $mediaId;
				$mediaXref['is_gallery'] = $isGallery;
				$mediaXref['client'] = 'com_jticketing.event';
				$mediaModelXref = TJMediaXref::getInstance($mediaXref['id']);

				$mediaModelXref->bind($mediaXref);

				$mediaModelXref->save();
			}
		}

		return true;
	}

	/**
	 * Check authorization for particular activity
	 *
	 * @param   data     $action        like core.edit.own, core.delete, core.edit.state
	 * @param   INTEGER  $formVendorId  vendor id
	 * @param   INTEGER  $created_by    creator
	 * @param   INTEGER  $id            creator
	 *
	 * @return boolean
	 *
	 * @since    2.0.13
	 */
	public function checkAuthorization($action, $formVendorId, $created_by, $id)
	{
		$existingData = $this->getItem($id);
		$app  = Factory::getApplication();

		if ($existingData->created_by != $created_by && $app->isClient('site'))
		{
			$app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'error');
		}

		$TjvendorFrontHelper = new TjvendorFrontHelper;
		$user = Factory::getUser();
		$vendor_id = $TjvendorFrontHelper->checkVendor('', 'com_jticketing');
		$authorise = ($user->authorise($action, 'com_jticketing') == 1 ? true : false);

		if ($authorise === true)
		{
			// If form vendor id is same as vendor id and login user is creator of event
			if (($vendor_id == $formVendorId && $user->id == $created_by) || $user->authorise('core.edit', 'com_jticketing'))
			{
				return true;
			}
			else
			{
				$app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'error');
			}
		}

		return false;
	}

	/**
	 * Check ownership
	 *
	 * @param   data     $formVendorId  vendor id
	 * @param   data     $created_by    creator
	 * @param   INTEGER  $id            like save, delete, publish
	 * @param   data     $task          like save, delete, publish
	 *
	 * @return boolean
	 *
	 * @since    2.0.13
	 */
	public function checkOwnership($formVendorId, $created_by, $id, $task)
	{
		$user = Factory::getUser();

		if (!$user->authorise('core.admin'))
		{
			switch ($task)
			{
				case 'save':
					return $this->checkAuthorization('core.edit.own', $formVendorId, $created_by, $id);
					break;

				case 'delete':
					return $this->checkAuthorization('core.delete', $formVendorId, $created_by, $id);
					break;

				case 'publish':
					return $this->checkAuthorization('core.edit.state', $formVendorId, $created_by, $id);
					break;

				default:
				$app = Factory::getApplication();
				$app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'error');
			}
		}
		else
		{
			return true;
		}
	}

	/**
	 * Method to change the published state of one or more records.
	 *
	 * @param   array    &$pks   A list of the primary keys to change.
	 * @param   integer  $value  The value of the published state.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   2.5
	 */
	public function publish(&$pks, $value = 1)
	{
		$eventId       = $pks;
		$adminApproval = JT::config()->get('event_approval');

		if (parent::publish($pks, $value))
		{
			$extension  = Factory::getApplication()->getInput()->get('option');

			// Include the content plugins for the change of category state event.
			PluginHelper::importPlugin('jticketing');

			// Trigger the onCategoryChangeState event.
			Factory::getApplication()->triggerEvent('onAfterJtEventChangeState', array($extension, $eventId, $value));

			$eventDetails = $this->getItem($eventId);

			if ($adminApproval == '1' && $eventDetails->state == '1')
			{
				// Send notification on event is approved.
				JticketingMailHelper::eventApproved($eventDetails->id);
			}

			$result = 1;

			return $result;
		}
	}

	/**
	 * Method to save event/venue latitude longitude
	 *
	 * @param   INTEGER  $venue    Venue id
	 * @param   STRING   $address  address
	 * @param   INTEGER  $eventID  Event ID
	 *
	 * @return boolean|array Incase of failure rewturn false or array
	 *
	 * @since    2.3.4
	 */
	public function addLatLongForEvent($venue, $address, $eventID)
	{
		$data = new stdClass;
		$address = $address ? str_replace(" ", "+", $address) : '';
		$com_params    = ComponentHelper::getParams('com_jticketing');
		$googleApiKey = $com_params->get('google_map_api_key');
		$url = "https://maps.google.com/maps/api/geocode/json?address=" . $address . "&key=" . $googleApiKey;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		$response = curl_exec($ch);
		curl_close($ch);
		$response = json_decode($response);

		if (empty($response->results[0]->geometry))
		{
			return;
		}
		else
		{
			$data->longitude = $response->results[0]->geometry->location->lng;
			$data->latitude = $response->results[0]->geometry->location->lat;

			if (!empty($venue))
			{
				$data->id = $venue;
				$this->_db->updateObject('#__jticketing_venues', $data, 'id');
			}
			else
			{
				$data->id = $eventID;
				$this->_db->updateObject('#__jticketing_events', $data, 'id');
			}
		}

		return $data;
	}

	/**
	 * Method to validate the form data.
	 *
	 * @param   \JForm  $form   The form to validate against.
	 * @param   array   $data   The data to validate.
	 * @param   string  $group  The name of the field group to validate.
	 *
	 * @return  array|boolean  Array of filtered data if valid, false otherwise.
	 *
	 * @since   2.3.5
	 */

	public function validate($form, $data, $group = null)
	{
		$return = true;
		$eventStartDate = Factory::getDate($data['startdate'], 'UTC')->toUnix();
		$eventEndDate = Factory::getDate($data['enddate'], 'UTC')->toUnix();
		$bookingStartDate = Factory::getDate($data['booking_start_date'], 'UTC')->toUnix();
		$bookingEndDate  = Factory::getDate($data['booking_end_date'], 'UTC')->toUnix();
		$start_date=$data['startdate'];
		$end_date=$data['enddate'];

		foreach ($data['tickettypes'] as $ticketType)
		{
			if ($ticketType['unlimited_seats'] == 0 && $ticketType['available'] < 0)
			{
				$this->setError(Text::_('COM_JTICKETING_NEGATIVE_SEAT_COUNT') . $ticketType['title']);
				$return = false;
			}

			if (!empty($ticketType['ticket_enddate']) && $ticketType['ticket_enddate'])
			{
				$ticketEndDate = Factory::getDate($ticketType['ticket_enddate'], 'UTC')->toUnix();

				if ($data['booking_end_date'] && $ticketEndDate > $bookingEndDate)
				{
					$this->setError(Text::_('COM_JTICKETING_TICKET_END_DATE_GREATER_BOOKING_END_DATE_ERROR'));
					$return = false;
				}

				// Validate if ticket end-date >= booking end-date.
				if ($data['booking_start_date'] && $ticketEndDate < $bookingStartDate)
				{
					$this->setError(Text::_('COM_JTICKETING_TICKET_END_DATE_LESS_BOOKING_START_DATE_ERROR'));
					$return = false;
				}
			}

			if ($data['startdate'] && $data['booking_start_date'])
			{
				if ($bookingStartDate > $eventStartDate)
				{
					$this->setError(Text::_('COM_JTICKETING_BOOKING_START_DATE_WITH_EVENT_DATE_ERROR'));
					$return = false;
				}
			}
		}

		$params = ComponentHelper::getParams('com_jticketing');

		$input		= Factory::getApplication()->getInput();
		$task 		= $input->post->get('task', '');

		if ($task == 'events.duplicate')
		{
			$data['privacy_consent'] = 'on';
		}

		if ($data['privacy_consent'] != 'on' && $params->get('tnc_for_create_event') == '1')
		{
			$this->setError(Text::_('COM_JTICKETING_PRIVACY_TERMS_AND_CONDITIONS_ERROR'));
			$return = false;
		}

		$data['title'] = StringHelper::trim($data['title']);

		if (($data['recurring_type']==="No_repeat")&&($eventStartDate > $eventEndDate))
		{
			$this->setError(Text::_('COM_JTICKETING_EVENT_START_DATE_LESS_EVENT_END_DATE_ERROR'));
			$return = false;
		}

		if ($data['booking_start_date'] != '' && $data['booking_end_date'] != ''
			&& $bookingStartDate > $bookingEndDate)
		{
			$this->setError(Text::_('COM_JTICKETING_EVENT_BOOKING_START_DATE_LEES_EVENT_START_DATE_ERROR'));

			$return = false;
		}

		// Certificate validation - start
		if (!empty($data['certificate_expiry']) && $data['certificate_expiry'] > 0)
		{
			$certificateExpiryDate = Factory::getDate($data['startdate'], 'UTC');
			$certificateExpiryDate->modify("+" . $data['certificate_expiry'] . " days");
			$certificateExpiryDate = $certificateExpiryDate->toUnix();

			// Validate certificate expiry date with event end date
			if (!empty($certificateExpiryDate))
			{
				if ($certificateExpiryDate < $eventEndDate)
				{
					$this->setError(Text::_('COM_JTICKETING_EVENT_CERTIFICATE_EXPIRY_DATE_LESS_THAN_EVENT_END_DATE_ERROR'));
					$return = false;
				}
			}
		}
		elseif (!empty($data['certificate_expiry']) && $data['certificate_expiry'] < 0)
		{
			$this->setError(Text::_('COM_JTICKETING_EVENT_CERTIFICATE_EXPIRY_DAYS_NEGATIVE_ERROR'));
			$return = false;
		}
		// Validate certificate expiry date with certificate template
		elseif (empty($data['certificate_template']) && !empty($data['certificate_expiry']))
		{
			$this->setError(Text::_('COM_JTICKETING_EVENT_CERTIFICATE_TEPMLATE_EMPTY_ERROR'));
			$return = false;
		}

		// Venue validation
		if ($data['venue'] < 0)
		{
			$this->setError(Text::_('COM_JTICKETING_EVENT_VENUE_EMPTY_ERROR'));
			$return = false;
		}

		// Certificate validation - end
		$data = parent::validate($form, $data, $group);
		$data['startdate']=$start_date;
		$data['enddate']=$end_date;
		return (!$return) ? false : $data;
	}
	/**
	 * Method to duplicate an Event
	 *
	 * @param   array  &$pks  An array of primary key IDs.
	 *
	 * @return  boolean|JException  Boolean true on success, JException instance on error
	 *
	 * @since   2.6.0
	 *
	 * @throws  Exception
	 */
	public function duplicate(&$pks)
	{
		$user = Factory::getUser();

		// Added this by require once because, site and admin model files have same name. We need site model files
		require_once JPATH_SITE . '/components/com_jticketing/models/tickettypes.php';
		require_once JPATH_SITE . '/components/com_jticketing/models/attendeefields.php';

		// Access checks.
		if (!$user->authorise('core.create', 'com_jticketing'))
		{
			throw new Exception(Text::_('JERROR_CORE_CREATE_NOT_PERMITTED'));
		}

		$table = $this->getTable();

		foreach ($pks as $pk)
		{
			if ($table->load($pk, true))
			{
				// Load Event Data
				$eventData = JT::event($table->id, 'com_jticketing');

				// Reset the id to create a new record.
				$table->id = 0;
				$data = (array) $table->getProperties();

				// Copy recurring event parameters
				if (isset($eventData->recurring_params))
				{
					$recurringParams = json_decode($eventData->recurring_params, true);
					if (is_array($recurringParams))
					{
						// Copy the repeat mode (count or until)
						$data['repeat_via'] = isset($recurringParams['repeat_until']) && !empty($recurringParams['repeat_until']) ? 'rep_until' : 'rep_count';

						// Copy the repeat count and interval
						$data['repeat_count'] = $recurringParams['repeat_count'] ?? 0;
						$data['repeat_interval'] = $recurringParams['repeat_interval'] ?? 1;
						$data['repeat_until'] = $recurringParams['repeat_until'] ?? '';

						// Copy the original recurring parameters
						$data['recurring_params'] = $eventData->recurring_params;

						// Copy recurring type if it exists
						if (isset($eventData->recurring_type))
						{
							$data['recurring_type'] = $eventData->recurring_type;
						}
					}
				}

				// Alter the title.
				$m = null;

				if (preg_match('#\((\d+)\)$#', $table->title, $m))
				{
					$table->title = preg_replace('#\(\d+\)$#', '(' . ($m[1] + 1) . ')', $table->title);
				}

				$title = $this->generateNewTitle(0, $table->alias, $table->title);
				$data['title'] = $title[0];
				$data['alias'] = '';

				// Unpublish duplicate event
				$data['state'] = 0;

				// Add ticket data, reset sold count
				$ticketTypesModel = new JticketingModelTickettypes;

				$ticketTypesModel->setState('filter.eventid', $eventData->integrationId);
				$ticketTypes = $ticketTypesModel->getItems();
				$data['tickettypes'] = array();

				foreach ($ticketTypes as $ticketKey => $ticketType)
				{
					$ticketType->id = 0;

					if ($ticketType->unlimited_seats == 0)
					{
						$ticketType->count = $ticketType->available;
					}

					unset($ticketType->eventid);
					$data['tickettypes']['tickettypes' . $ticketKey] = (array) $ticketType;
				}

				// Add event specific attendee data for copied event
				$attendeeFieldsModel = new JTicketingModelAttendeefields;
				$attendeeOptions = array("eventid" => $eventData->integrationId);
				$attendeeFields = $attendeeFieldsModel->getFields($attendeeOptions);
				$data['attendeefields'] = array();

				foreach ($attendeeFields as $attendeeKey => $attendeeField)
				{
					$attendeeField->id = 0;
					unset($attendeeField->eventid);
					$data['attendeefields']['attendeefields' . $attendeeKey] = (array) $attendeeField;
				}

				// Add event image and gallery for copied event
				$modelMedia = JT::model('media');
				$data['image'] = array();
				$eventMainImage = $modelMedia->getEventMedia($eventData->id, 'com_jticketing.event', 0);

				foreach ($eventMainImage as $img)
				{
					$image = TJMediaStorageLocal::getInstance(array('id' => $img->media_id));
					$imgparams = json_decode($image->params);

					if ($imgparams->detail == 0)
					{
						$data['image']['new_image'] = $img->media_id;
						$data['image']['old_image'] = '';
					}
					else
					{
						$data['coverImage']['new_image'] = $img->media_id;
						$data['coverImage']['old_image'] = '';
					}
				}

				$galleryData = $modelMedia->getEventMedia($eventData->id, 'com_jticketing.event', 1);
				$data['gallery_file'] = array();
				$galleryFiles = array();

				foreach ($galleryData as $gallery)
				{
					$galleryFiles[] = $gallery->media_id;
				}

				$data['gallery_file']['media'] = $galleryFiles;

				$form = $this->getForm($data, false);

				if (!$form)
				{
					throw new Exception($this->getError());
				}

				$validData = $this->validate($form, $data);

				// Check for errors.
				if ($validData === false)
				{
					throw new Exception($this->getError());
				}

				if (!$this->save($data))
				{
					throw new Exception($this->getError());
				}
			}
			else
			{
				throw new Exception($table->getError());
			}
		}

		// Clean cache
		$this->cleanCache();

		return true;
	}

	/**
	 * Method to change the title & alias.
	 *
	 * @param   integer  $category_id  The id of the category.
	 * @param   string   $alias        The alias.
	 * @param   string   $title        The title.
	 *
	 * @return  array  Contains the modified title and alias.
	 *
	 * @since   2.6.0
	 */
	protected function generateNewTitle($category_id, $alias, $title)
	{
		// Alter the title & alias
		$table = $this->getTable();

		while ($table->load(array('alias' => $alias, 'catid' => $category_id)))
		{
			$title = StringHelper::increment($title);
			$alias = StringHelper::increment($alias, 'dash');
		}

		return array($title, $alias);
	}

	/**
	 * Function to insert or update todo status
	 *
	 * @param   array  $eventData  It contains data data required for todo
	 *
	 * @return  boolean
	 *
	 * @since   3.2.0
	 */
	public function saveTodo($eventData)
	{
		if (file_exists(JPATH_SITE . '/components/com_jlike/models/contentform.php')) { require_once JPATH_SITE . '/components/com_jlike/models/contentform.php'; }
		$contentFormModel = BaseDatabaseModel::getInstance('contentForm', 'JlikeModel');

		extract($eventData);

		// TODO Insertion and updation operations will go here
		$date 	= Factory::getDate();
		$user 	= Factory::getUser();
		$data	= array();
		$link 	= "index.php?option=com_jticketing&view=event&id=" . $eventId;

		$contentData = array(
			'url' => $link,
			'element' => "com_jticketing.event",
			'element_id' => $eventId
		);

		if ($content_id = $contentFormModel->getConentId($contentData))
		{
			// Check for duplicate TODO, Call get todo api

			$data['content_id'] = $content_id;

			if (isset($assigned_to))
			{
				$data['assigned_to'] = $assigned_to;
			}

			if (isset($assigned_by))
			{
				$data['assigned_by'] = $assigned_by;
			}

			if (isset($user_id))
			{
				$data['user_id'] = $user_id;
			}

			$todos = $this->checkDuplicateTodo($data);

			// Setting up TODO data to insert or update

			$todoData = array();
			$todoData['content_id']  = $content_id;

			if (isset($assigned_to) && $assigned_to == $user->id)
			{
				$todoData['type'] = 'self';
			}
			else
			{
				$todoData['type'] = 'assign';
			}

			$todoData['context'] = '';
			$todoData['client'] = 'com_jticketing.event';

			list($plg_type, $plg_name) = explode(".", $todoData['client']);

			$todoData['plg_type'] = $plg_type;
			$todoData['plg_name'] = $plg_name;

			if ($eventTitle)
			{
				$todoData['title'] = $eventTitle;
			}

			$todoData['sender_msg'] = '';

			$todoData['assigned_by'] = isset($assigned_by) ? $assigned_by : $user->id;

			$todoData['assigned_to'] = isset($assigned_to) ? $assigned_to : $user->id;

			$todoData['status'] = isset($status) ? $status : 'I';

			$todoData['created_date'] = $date->toSql(true);

			if ($startDate)
			{
				$todoData['start_date'] = $startDate;
			}

			if ($endDate)
			{
				$todoData['due_date'] = $endDate;
			}

			$todoData['parent_id'] = '0';
			$todoData['state'] = 1;

			if (count($todos))
			{
				// Update TODO if already exist
				$todoData['id'] = $todos[0]->id;
			}
			else
			{
				// Insert TODO
				$todoData['id']          = '';
				$todoData['status']      = 'I';
			}

			// Avoid php cs warning.
			if (!empty($notify) && $notify === true)
			{
				$todoData['notify'] = 1;
			}
			else
			{
				$todoData['notify'] = 0;
			}

			if (file_exists(JPATH_SITE . '/components/com_jlike/models/recommendationform.php')) { require_once JPATH_SITE . '/components/com_jlike/models/recommendationform.php'; }
			$recommendationFormModel = BaseDatabaseModel::getInstance('RecommendationForm', 'JlikeModel');

			$todos_id = $recommendationFormModel->save($todoData);

			if (empty($todos_id))
			{
				return false;
			}

			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Function to delete todo
	 *
	 * @param   array  $eventData  It contains data data required for todo
	 *
	 * @return  boolean
	 *
	 * @since   3.2.0
	 */
	public function deleteTodo($eventData)
	{
		extract($eventData);

		if (!isset($isEnrollment) && (int) $isEnrollment !== 1)
		{
			//  TODO deletion will go here for orders
			$orderData = JT::order($orderId);
			$event     = JT::event()->loadByIntegration($orderData->event_details_id);
			$eventId   = $event->getId();
		}

		$link = "index.php?option=com_jticketing&view=event&id=" . $eventId;
		$contentData = array(
			'url' => $link,
			'element' => "com_jticketing.event",
			'element_id' => $eventId
		);

		if (file_exists(JPATH_SITE . '/components/com_jlike/models/contentform.php')) { require_once JPATH_SITE . '/components/com_jlike/models/contentform.php'; }
		$contentFormModel = BaseDatabaseModel::getInstance('contentForm', 'JlikeModel');

		if ($contentId = $contentFormModel->getConentId($contentData))
		{
			$data                = array();
			$data['content_id']  = $contentId;
			$data['assigned_to'] = $assigned_to;

			if (isset($assigned_by))
			{
				$data['assigned_by'] = (int) $assigned_by;
			}

			if (isset($user_id))
			{
				$data['user_id'] = $user_id;
			}

			$todos = $this->checkDuplicateTodo($data);

			if (file_exists(JPATH_SITE . '/components/com_jlike/models/recommendationform.php')) { require_once JPATH_SITE . '/components/com_jlike/models/recommendationform.php'; }
			$recommendationFormModel = BaseDatabaseModel::getInstance('RecommendationForm', 'JlikeModel');

			if ($todos['0']->id)
			{
				$TodoDeletionData       = array();
				$TodoDeletionData['id'] = $todos['0']->id;
				$deletedTodoId          = $recommendationFormModel->delete($TodoDeletionData);

				if ($deletedTodoId)
				{
					return true;
				}
			}
			else
			{
				return false;
			}
		}
	}

	/**
	 * Function to check duplicate todo
	 *
	 * @param   array  $eventData  It contains data data required for todo
	 *
	 * @return  boolean
	 *
	 * @since   3.2.0
	 */
	public function checkDuplicateTodo($eventData)
	{
		extract($eventData);
		$user = Factory::getUser();

		if (!isset($content_id))
		{
			return false;
		}

		if (file_exists(JPATH_SITE . '/components/com_jlike/models/recommendation.php')) { require_once JPATH_SITE . '/components/com_jlike/models/recommendation.php'; }
		if (file_exists(JPATH_SITE . '/components/com_jlike/models/recommendations.php')) { require_once JPATH_SITE . '/components/com_jlike/models/recommendations.php'; }
		$recommendationsModel = BaseDatabaseModel::getInstance('Recommendations', 'JlikeModel');
		$recommendationModel = BaseDatabaseModel::getInstance('Recommendation', 'JlikeModel');

		$todos_id = $recommendationModel->save($eventData);

		$data['content_id'] = $content_id;
		$recommendationsModel->setState("content_id", $data['content_id']);

		$data['type'] = isset($type)? $type : 'todos';
		$recommendationsModel->setState("type", $data['type']);

		$data['context'] = isset($context)? $context : '';
		$recommendationsModel->setState("context", $data['context']);

		$data['client'] = isset($client) ? $client : 'com_jticketing.event';
		list($plg_type, $plg_name) = explode(".", $data['client']);

		$data['plg_type'] = $plg_type;
		$data['plg_name'] = $plg_name;

		$data['supress_data'] = isset($supress_data) ? $supress_data : '';

		$data['status'] = isset($status) ? $status : '';
		$recommendationsModel->setState("status", $data['status']);

		$data['assigned_to'] = isset($assigned_to) ? $assigned_to : $user->id;
		$recommendationsModel->setState("assigned_to", $data['assigned_to']);

		$data['user_id'] = isset($user_id) ? $user_id : $user->id;
		$recommendationsModel->setState("user_id", $data['user_id']);

		$data['state'] = isset($state) ? $state : 1;
		$recommendationsModel->setState("state", $data['state']);

		$todos = $recommendationsModel->getTodos($data);

		return $todos;
	}

	/**
	 * Function to create content
	 *
	 * @param   array  $contentData  It contains data data required for content
	 *
	 * @return  boolean
	 *
	 * @since   3.2.0
	 */
	public function createContent($contentData)
	{
		if (file_exists(JPATH_SITE . '/components/com_jlike/models/contentform.php')) { require_once JPATH_SITE . '/components/com_jlike/models/contentform.php'; }
		$contentFormModel = BaseDatabaseModel::getInstance('contentForm', 'JlikeModel');

		return $contentFormModel->getContentID($contentData);
	}

	/**
	 * Function to update content
	 *
	 * @param   array  $contentData  It contains data data required for content
	 *
	 * @return  boolean
	 *
	 * @since   3.2.0
	 */
	public function updateContent($contentData)
	{
		if (file_exists(JPATH_SITE . '/components/com_jlike/models/contentform.php')) { require_once JPATH_SITE . '/components/com_jlike/models/contentform.php'; }
		$contentFormModel = BaseDatabaseModel::getInstance('contentForm', 'JlikeModel');

		$contentId =  $contentFormModel->getContentID($contentData);

		if ($contentId)
		{
			$contentData['id'] = $contentId;
			$contentFormModel->save($contentData);
		}
	}

	/**
	 * Send emails to past attendees of events in the same category.
	 *
	 * @param   int     $eventId     Current event ID.
	 * @param   string  $eventTitle  Event title.
	 * @param   int     $categoryId  Category ID of the event.
	 *
	 * @return  array  List of email addresses the mail was sent to.
	 * @since   5.1.0
	 */
	public function getEmailsOfPastAttendees($eventId, $eventTitle, $categoryId)
	{
		$db = Factory::getDbo();

		$query = $db->getQuery(true)
			->select('email_sent')
			->from('#__jticketing_events')
			->where('id = ' . (int) $eventId);
		$db->setQuery($query);
		$emailSent = $db->loadResult();

		if ((int) $emailSent === 1)
		{
			return [];
		}

		$query = $db->getQuery(true)
			->select('DISTINCT u.email')
			->from('#__jticketing_attendees AS a')
			->join('INNER', '#__users AS u ON a.owner_id = u.id')
			->join('INNER', '#__jticketing_events AS e ON a.event_id = e.id')
			->where('e.catid = ' . (int) $categoryId);
		$db->setQuery($query);
		$emails = $db->loadColumn();

		return $emails;
	}

	/**
	 * Get a list of pending email events that haven't had notifications sent yet.
	 *
	 * @return  array  List of event data (id, title, catid).
	 * @since   5.1.0
	 */
	public function getPendingEmailEvents()
	{
		$db = Factory::getDbo();

		$subQuery = $db->getQuery(true)
			->select('DISTINCT(event_id)')
			->from('#__jticketing_attendees');

		$query = $db->getQuery(true)
			->select('id, title, catid')
			->from('#__jticketing_events')
			->where('email_sent = 0')
			->where('state = 1')
			->where('id NOT IN (' . $subQuery . ')');

		$db->setQuery($query);

		return $db->loadAssocList();
	}

	/**
	 * Method to Get a list of past events (ended yesterday) for which feedback emails haven't been sent,
	 * along with the list of attendee email addresses for each event.
	 *
	 * @return  array  List of events with associated attendee emails.
	 * @since   5.1.0
	*/
	public function fetchPendingFeedbackEventsWithEmails()
	{
		$db = Factory::getDbo();
		$yesterday = (new DateTime())->modify('-1 day')->format('Y-m-d');

		$query = $db->getQuery(true)
			->select('e.id, e.title, e.enddate, e.catid')
			->from('#__jticketing_events AS e')
			->where('e.feedback_mail_sent = 0')
			->where('DATE(e.enddate) = ' . $db->quote($yesterday));
		$db->setQuery($query);
		$events = $db->loadAssocList();

		if (empty($events))
		{
			return [];
		}

		foreach ($events as &$event)
		{
			$query = $db->getQuery(true)
				->select('DISTINCT u.email')
				->from('#__jticketing_attendees AS a')
				->join('INNER', '#__users AS u ON a.owner_id = u.id')
				->where('a.event_id = ' . (int) $event['id']);
			$db->setQuery($query);
			$emails = $db->loadColumn();

			$event['emails'] = $emails;
		}

		return $events;
	}

	/**
	 * Function to get All booked Ticket Ids
	 *
	 * @param   array  $eventId  It contains id of event
	 *
	 * @return  array
	 *
	 * @since   5.0.4
	 */
	public function getAllBookedTicketIds($eventId, $source = 'com_jticketing')
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('DISTINCT ' . $db->quoteName('oitem.type_id'));
		$query->from($db->quoteName('#__jticketing_order_items', 'oitem'));
		$query->join('LEFT', $db->qn('#__jticketing_order', 'order') . 'ON (' . $db->qn('oitem.order_id')
			. ' = ' . $db->qn('order.id') . ')');
		$query->join('LEFT', $db->qn('#__jticketing_integration_xref', 'xref') . 'ON (' . $db->qn('order.event_details_id')
			. ' = ' . $db->qn('xref.id') . ')');
		$query->where($db->quoteName('xref.eventid') . ' = ' . $db->quote($eventId));
		$query->where($db->quoteName('xref.source') . ' = ' . $db->quote($source));
		$query->where($db->quoteName('order.status') . ' = ' . $db->quote('C'));
		$db->setQuery($query);
		$ticketTypesList = $db->loadColumn();

		return $ticketTypesList;
	}
}
