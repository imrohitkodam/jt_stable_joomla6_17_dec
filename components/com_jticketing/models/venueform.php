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
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\Filesystem\File;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Http\Http;
use Joomla\Registry\Registry;
use Joomla\String\StringHelper;

if (file_exists(JPATH_LIBRARIES . "/techjoomla/media/storage/local.php")) { require_once JPATH_LIBRARIES . "/techjoomla/media/storage/local.php"; }
if (file_exists(JPATH_LIBRARIES . "/techjoomla/media/xref.php")) { require_once JPATH_LIBRARIES . "/techjoomla/media/xref.php"; }
if (file_exists(JPATH_ADMINISTRATOR . '/components/com_tjvendors/helpers/tjvendors.php')) { require_once JPATH_ADMINISTRATOR . '/components/com_tjvendors/helpers/tjvendors.php'; }
if (file_exists(JPATH_SITE . '/components/com_tjvendors/helpers/fronthelper.php')) { require_once JPATH_SITE . '/components/com_tjvendors/helpers/fronthelper.php'; }

/**
 * Jticketing model.
 *
 * @since  1.6
 */
class JticketingModelVenueForm extends AdminModel
{
	/**
	 * Method to get an object.
	 *
	 * @param   integer  $id  The id of the object to get.
	 *
	 * @return  mixed    Object on success, false on failure.
	 */
	public function getItem($id = null)
	{
		$jtParams = ComponentHelper::getParams('com_jticketing');
		$paramsImagePath = $jtParams->get('jticketing_venue_media_upload_path', 'media/com_jticketing/venues');
		$this->item = parent::getItem($id);

		if (!empty($this->item->id))
		{
			if ($this->item->id)
			{
				$mediaXrefLib = TJMediaXref::getInstance();
				$mediaGallery = $mediaXrefLib->retrive($data = array('clientId' => $this->item->id, 'client' => 'com_jticketing.venue','isGallery' => 1));

				if (!empty($mediaGallery))
				{
					$galleryFiles = array();
					$config = array();

					foreach ($mediaGallery as $mediaXref)
					{
						$config['id'] = $mediaXref->media_id;

						if (file_exists(JPATH_LIBRARIES . "/techjoomla/media/tables/files.php")) { require_once JPATH_LIBRARIES . "/techjoomla/media/tables/files.php"; }
						$filetable = Table::getInstance('Files', 'TJMediaTable');

						// Load the object based on the id or throw a warning.
						$filetable->load($mediaXref->media_id);

						$mediaType      = explode(".", $filetable->type);
						$venueImagePath = $paramsImagePath . '/' . $mediaType[0] . 's';

						$config['uploadPath'] = $venueImagePath;
						$galleryFiles[]       = TJMediaStorageLocal::getInstance($config);
					}

					$this->item->gallery = $galleryFiles;
				}
			}
		}

		return $this->item;
	}

	/**
	 * Method to get the record form.
	 *
	 * @param   string  $data      An optional array of data for the form to interogate.
	 * @param   string  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return	JForm	A JForm object on success, false on failure
	 *
	 * @since   1.0
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_jticketing.venue', 'venue', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Method to get the plugin form.
	 *
	 * @param   string  $data      An optional array of data for the form to interogate.
	 * @param   string  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return	JForm	A JForm object on success, false on failure
	 *
	 * @since   1.0
	 */
	public function getPluginForm($data = array(), $loadData = true)
	{
		// Initialise variables.
		$app = Factory::getApplication();

		$filePath = JPATH_SITE . '/plugins/tjevents/' . $data['online_provider'] . '/' . $data['online_provider']
					. '/form/' . $data['online_provider'] . '.xml';

		if (!File::exists($filePath))
		{
			return false;
		}

		// Get the form.
		$form = $this->loadForm('com_jticketing.plugin_extra', $filePath, array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Method to validate the extraform data.
	 *
	 * @param   JForm   $form   The form to validate against.
	 * @param   array   $data   The data to validate.
	 * @param   string  $group  The name of the field group to validate.
	 *
	 * @return  mixed  Array of filtered data if valid, false otherwise.
	 *
	 * @see     JFormRule
	 * @see     JFilterInput
	 * @since   12.2
	 */
	public function validate($form, $data, $group = null)
	{
		if ($data['online'] == 1)
		{
			$licence = (object) $data['plugin'];
			$venue = JT::venue();
			$registery = new Registry($licence);
			$venue->params = $registery->toString();
			$venue->online_provider = $data['online_provider'];
			$event = JT::onlineEvent($venue);
			$result = $event->isValidCredentials();

			if (!$result)
			{
				$this->setError($event->getError());
			}
		}

		if ($data['seats_capacity'] == 0 && !$data['capacity_count'])
		{
			$this->setError(Text::_('COM_JTICKETING_MSG_ERROR_SAVE_VENUE_CAPACITY'));
		}

		return parent::validate($form, $data);
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return    mixed    The data for the form.
	 *
	 * @since    1.6
	 */
	protected function loadFormData()
	{
		// Initialise variables.
		$app = Factory::getApplication();

		// Check admin and load admin form in case of admin venue form

		if ($app->isClient('administrator'))
		{
			// Check the session for previously entered form data.
			$data = Factory::getApplication()->getUserState('com_jticketing.edit.venue.data', array());
		}
		else
		{
			$data = Factory::getApplication()->getUserState('com_jticketing.edit.venueform.data', array());
		}

		if (empty($data))
		{
			$data = $this->getItem();
		}

		return $data;
	}

	/**
	 * Get an instance of JTable class
	 *
	 * @param   string  $type    Name of the JTable class to get an instance of.
	 * @param   string  $prefix  Prefix for the table class name. Optional.
	 * @param   array   $config  Array of configuration values for the JTable object. Optional.
	 *
	 * @return  JTable|bool JTable if success, false on failure.
	 */
	public function getTable($type = 'Venue', $prefix = 'JticketingTable', $config = array())
	{
		$this->addTablePath(JPATH_ADMINISTRATOR . '/components/com_jticketing/tables');

		return Table::getInstance($type, $prefix, $config);
	}

	/**
	 * Get the id of an item by alias
	 *
	 * @param   string  $alias  Item alias
	 *
	 * @return  mixed
	 */
	public function getItemIdByAlias($alias)
	{
		$table = $this->getTable();

		$table->load(array('alias' => $alias));

		return $table->id;
	}

	/**
	 * Method to check out an item for editing.
	 *
	 * @param   integer  $id  The id of the row to check out.
	 *
	 * @return  boolean True on success, false on failure.
	 *
	 * @since    1.6
	 */
	public function checkout($id = null)
	{
		// Get the user id.
		$id = (!empty($id)) ? $id : (int) $this->getState('venue.id');

		if ($id)
		{
			// Initialise the table
			$table = $this->getTable();

			// Get the current user object.
			$user = Factory::getUser();

			// Attempt to check the row out.
			if (method_exists($table, 'checkout'))
			{
				if (!$table->checkout($user->get('id'), $id))
				{
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Get the name of a category by id
	 *
	 * @param   int  $id  Category id
	 *
	 * @return  Object|null	Object if success, null in case of failure
	 */
	public function getCategoryName($id)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query
			->select('title')
			->from('#__categories')
			->where('id = ' . $id);
		$db->setQuery($query);

		return $db->loadObject();
	}

	/**
	 * Publish the element
	 *
	 * @param   int  &$id    Item id
	 * @param   int  $value  Publish/Unpublish state
	 *
	 * @return  boolean  true or false
	 */
	public function publish(&$id, $value = 1)
	{
		$venueData = $this->getItem($id);

		if ($this->checkOwnership($venueData->vendor_id, $venueData->created_by, 'publish', $id))
		{
			$table = $this->getTable();
			$table->load($id);
			$table->state = (int) $value;

			$extension = Factory::getApplication()->getInput()->get('option');
			PluginHelper::importPlugin('jticketing');
			Factory::getApplication()->triggerEvent('onAfterJtVenueChangeState', array($extension, $id, $value));

			return $table->store();
		}
	}

	/**
	 * Publish the element
	 *
	 * @param   Array  &$id  Array of Venue id
	 *
	 * @return  boolean
	 */
	public function delete(&$id)
	{
		PluginHelper::importPlugin('jticketing');

		foreach ($id as $venue)
		{
			$venueData = $this->getItem($venue);

			if ($this->checkOwnership($venueData->vendor_id, $venueData->created_by, 'delete', $venueData->id))
			{
				if (parent::delete($id))
				{
					Factory::getApplication()->triggerEvent('onAfterJtDeleteVenue', array($venueData));

					return true;
				}

				return false;
			}
		}
	}

	/**
	 * Prepare and sanitise the table prior to saving.
	 *
	 * @param   data  $action        TO  ADD
	 *
	 * @param   data  $formVendorId  TO  ADD
	 *
	 * @param   data  $created_by    TO  ADD
	 *
	 * @return boolean
	 *
	 * @since    2.0.13
	 */
	public function checkAuthorization($action, $formVendorId, $created_by)
	{
		$tjvendorFrontHelper = new TjvendorFrontHelper;
		$user = Factory::getUser();
		$vendor_id = $tjvendorFrontHelper->checkVendor($user->id, 'com_jticketing');

		$authorise = ($user->authorise($action, 'com_jticketing') == 1 ? true : false);

		if ($authorise == 1)
		{
			if ($vendor_id == $formVendorId && $user->id == $created_by)
			{
				return true;
			}
			else
			{
				$app = Factory::getApplication();
				$app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'error');
			}
		}

		return false;
	}

	/**
	 * Prepare and sanitise the table prior to saving.
	 *
	 * @param   INTEGER  $formVendorId  VendorID
	 * @param   INTEGER  $created_by    Created By
	 * @param   STRING   $task          Task
	 * @param   INTEGER  $id            Venue ID
	 *
	 * @return boolean
	 *
	 * @since    2.0.13
	 */
	public function checkOwnership($formVendorId, $created_by, $task, $id)
	{
		$user = Factory::getUser();
		$app  = Factory::getApplication();
		$venueData = $this->getItem($id);

		if ($venueData->created_by != $created_by && $app->isClient('site'))
		{
			$app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'error');
		}

		if (!$user->authorise('core.admin') && $app->isClient('site'))
		{
			switch ($task)
			{
				case 'save':
					return $this->checkAuthorization('core.edit.own', $formVendorId, $created_by);
					break;

				case 'delete':
					return $this->checkAuthorization('core.delete', $formVendorId, $created_by);
					break;

				case 'publish':
					return $this->checkAuthorization('core.edit.state', $formVendorId, $created_by);
					break;

				default:
				$app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'error');
			}
		}
		else
		{
			return true;
		}
	}

	/**
	 * Prepare and sanitise the table prior to saving.
	 *
	 * @param   data  $data  TO  ADD
	 *
	 * @return void|boolean|integer
	 *
	 * @since    1.6
	 */
	public function save($data)
	{
		if (empty($data['created_by']))
		{
			$data['created_by'] = Factory::getUser()->id;
		}

		$user = Factory::getUser();
		$app = Factory::getApplication();
		$licence = (object) !empty($data['plugin']) ? $data['plugin'] : array();
		$data['params'] = json_encode(!empty($data['plugin']) ? $data['plugin'] : array());
		$online_provider = ltrim($data['online_provider'], "plug_tjevents_");
		$online_provider = ucfirst($online_provider);

		// Generating vendor
		$tjvendorFrontHelper = new TjvendorFrontHelper;
		$tjvendorsHelper     = new TjvendorsHelper;
		$creatorId           = $data['created_by'];

		// Checked if the user is a vendor
		$getVendorId = $tjvendorFrontHelper->checkVendor($creatorId, 'com_jticketing');

		if (!empty($data['id']))
		{
			$data['passed'] = $this->checkOwnership($getVendorId, $data['created_by'], 'save', $data['id']);
		}

		if (isset($data['passed']) || $user->authorise('core.create', 'com_jticketing'))
		{
			// Collecting vendor data
			$vendorData                  = array();
			$vendorData['vendor_client'] = "com_jticketing";
			$vendorData['user_id']       = $creatorId;

			$userName                   = Factory::getUser($vendorData['user_id'])->name;
			$vendorData['vendor_title'] = $userName;
			$vendorData['state']        = "1";

			// Collecting payment gateway details
			$paymentDetails                    = array();
			$paymentDetails['payment_gateway'] = '';
			$vendorData['paymentDetails']      = json_encode($paymentDetails);

			Table::addIncludePath(JPATH_ROOT . '/administrator/components/com_tjvendors/tables');
			$table = Table::getInstance('vendor', 'TJVendorsTable', array());
			$table->load(
				array(
					'user_id' => $creatorId
				)
			);

			// Check for vendor's id if not adds a vendor
			if (empty($table->vendor_id))
			{
				$data['vendor_id'] = $tjvendorsHelper->addVendor($vendorData);
			}
			elseif (empty($getVendorId))
			{
				$vendorData['vendor_id'] = $table->vendor_id;
				$data['vendor_id']       = $tjvendorsHelper->addVendor($vendorData);
			}
			else
			{
				$data['vendor_id'] = $getVendorId;
			}

			if (parent::save($data))
			{
				$id = (int) $this->getState($this->getName() . '.id');

				if ($id)
				{
					if (isset($data['gallery_file']['media']))
					{
						$this->saveMedia($data['gallery_file']['media'], 1, $id);
					}
				}

				$data['venueId'] = $id;

				// Trigger - OnAfterSavingVenue
				PluginHelper::importPlugin('jticketing');

				// Edit existed venue
				if (!empty($data['id']))
				{
					Factory::getApplication()->triggerEvent('onAfterJtVenueSave', array($data, false));
				}
				// Create new venue
				else
				{
					Factory::getApplication()->triggerEvent('onAfterJtVenueSave', array($data, true));
				}
			}

			return $id;
		}
	}

	/**
	 * Method to Get User Current Location.
	 *
	 * @param   Array  $post  Array of data
	 *
	 * @return Array
	 *
	 * @since   1.0
	 */
	public function getCurrentLocation($post)
	{
		$longitude = $post->get('longitude');
		$latitude  = $post->get('latitude');

		$googleApiKey = JT::config()->get('google_map_api_key');
		$url = 'https://maps.googleapis.com/maps/api/geocode/json?latlng=' . trim($latitude) . ',' .
				trim($longitude) . '&sensor=false&key=' . $googleApiKey;

		$transport = new Http;
		$response = $transport->get($url);
		$responseBody = new Registry($response->body);

		$data = array();
		$data['location'] = '';
		$data['latitude'] = '';
		$data['longitude'] = '';

		if ($response->code == '200' && $responseBody->get('status') == "OK")
		{
			$results = $responseBody->get('results');
			$locationLogLat = $results[0]->geometry;
			$longitude = $locationLogLat->location->lng;
			$latitude = $locationLogLat->location->lat;

			// Get address from json data
			$data['location'] = $results[0]->formatted_address;
			$data['latitude'] = $latitude;
			$data['longitude'] = $longitude;

			return $data;
		}

		$data['error'] = $responseBody->get('error_message');

		return $data;
	}

	/**
	 * To return a Used Venues
	 *
	 * @param   integer  $venueCodes  Venue Codes
	 *
	 * @return  integer on success
	 *
	 * @since  1.6
	 */
	public function usedVenues($venueCodes)
	{
		$venueCode = implode(", ", $venueCodes);

		$db = Factory::getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select('venue');
		$query->from('`#__jticketing_events`');
		$query->where('`venue` IN (' . $venueCode . ')');

		$db->setQuery($query);
		$used = $db->loadColumn();

		if ($used)
		{
			return $used;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Check if data can be saved
	 *
	 * @return bool
	 */
	public function getCanSave()
	{
		$table = $this->getTable();

		return $table !== false;
	}

	/**
	 * Method to call media save function
	 *
	 * @param   Array  $mediaGallery  mediaGallery
	 *
	 * @param   INT    $isGallery     isGallery
	 *
	 * @param   INT    $venueId       eventId
	 *
	 * @return   mixed result
	 *
	 * @since    2.4.0
	 */
	public function saveMedia($mediaGallery, $isGallery, $venueId)
	{
		if (!is_array($mediaGallery))
		{
			$mediaGallery = (array) $mediaGallery;
		}

		foreach ($mediaGallery as $mediaId)
		{
			if ($mediaId)
			{
				$mediaXref               = array();
				$mediaXref['id']         = '';
				$mediaXref['client_id']  = $venueId;
				$mediaXref['media_id']   = $mediaId;
				$mediaXref['is_gallery'] = $isGallery;
				$mediaXref['client']     = 'com_jticketing.venue';

				$mediaModelXref          = TJMediaXref::getInstance($mediaXref['id']);

				$mediaModelXref->bind($mediaXref);
				$mediaModelXref->save();
			}
		}

		return true;
	}
}
