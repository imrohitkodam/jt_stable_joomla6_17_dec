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
class JticketingModelPDFTemplate extends AdminModel
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
		$form = $this->loadForm('com_jticketing.pdftemplate', 'pdftemplate', array('control' => 'jform', 'load_data' => $loadData));

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
		if (!$data['event_id'])
		{
			$this->setError(Text::_('COM_JTICKETING_PDF_TEMPLATE_MSG_ERROR_SAVE_EVENT_ID'));
		}
		else 
		{
			$db = Factory::getDbo();
			$query = $db->getQuery(true);
			$query->select('COUNT(id) as cnt');
			$query->from($db->qn('#__jticketing_pdf_templates'));

			if ($data['id'])
			{
				$query->where($db->qn('id') . ' != ' . $db->quote($data['id']));
			}

			$query->where($db->qn('event_id') . ' 	= ' . $db->quote($data['event_id']));			
			$db->setQuery($query);
			$items_cnt_db = $db->loadResult();

			if ($items_cnt_db)
			{
				$this->setError(Text::_('COM_JTICKETING_PDF_TEMPLATE_MSG_ERROR_EVENT_ID_USED_ERROR'));
			}

		}

		if (!$data['body'])
		{
			$this->setError(Text::_('COM_JTICKETING_PDF_TEMPLATE_MSG_ERROR_SAVE_BODY'));
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
	public function getTable($type = 'pdftemplates', $prefix = 'JticketingTable', $config = array())
	{
		$this->addTablePath(JPATH_ADMINISTRATOR . '/components/com_jticketing/tables');

		return Table::getInstance($type, $prefix, $config);
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
		$table = $this->getTable();
		$table->load($id);
		$table->state = (int) $value;

		return $table->store();
	}

	/**
	 * Publish the element
	 *
	 * @param   Array  &$id  Array of templateId
	 *
	 * @return  boolean
	 */
	public function delete(&$id)
	{
		return parent::delete($id);
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return	mixed		The user id on success, false on failure.
	 *
	 * @since	1.6
	 */
	public function save($data)
	{
		$app   = Factory::getApplication();
		$table = $this->getTable('pdftemplates');

		$vendor    = JT::event()->loadByIntegration($data['event_id'])->getVendorDetails();
		$data['vendor_id'] = $vendor->vendor_id;

		if ($data['id'])
		{
			if ($app->isClient('site'))
			{
				$data['state'] = 0;
			}
		}

		if (!$table->bind($data))
		{
			$this->setError($table->getError());

			return false;
		}

		if (!$table->check())
		{
			$this->setError($table->getError());

			return false;
		}

		if (!$table->store())
		{
			$this->setError($table->getError());

			return false;
		}

		$app->getInput()->set('id', $table->id);

		return $table->id;
		
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
