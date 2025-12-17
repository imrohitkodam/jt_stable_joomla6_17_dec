<?php
/**
 * @package     TJCertificate
 * @subpackage  com_tjcertificate
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
use Joomla\Filesystem\File;
use Joomla\CMS\Uri\Uri;
use Joomla\Filesystem\Folder;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Router\Route;
use Joomla\Registry\Registry;
use Dompdf\Dompdf;
use Dompdf\Options;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Menu\SiteMenu;

/**
 * Certificate class.  Handles all application interaction with a Certificate
 *
 * @since  1.0.0
 */
class TjCertificateCertificate extends CMSObject
{
	public $id = null;

	public $unique_certificate_id = "";

	private $certificate_template_id = 0;

	public $generated_body = "";

	private $client = "";

	private $client_id = 0;

	private $client_issued_to = 0;

	private $client_issued_to_name = "";

	private $user_id = 0;

	public $state = 1;

	public $issued_on = null;

	private $expired_on = null;

	private $comment = null;

	public $defaultCertPrefix = "CERT";

	public $certImageDir = JPATH_SITE . '/media/com_tjcertificate/certificates/';

	public $certTmpDir = JPATH_SITE . '/media/com_tjcertificate/tmp/';

	protected static $certificateObj = array();

	public $is_external = 0;

	public $name = null;

	public $cert_url = "";

	public $cert_file = "";

	public $issuing_org = "";

	public $status = "";

	public $created_by = "";

	public $notify = 1;

	/**
	 * Constructor activating the default information of the Certificate
	 *
	 * @param   int  $id  The unique event key to load.
	 *
	 * @since   1.0.0
	 */
	public function __construct($id = 0)
	{
		if (!empty($id))
		{
			$this->load($id);
		}
	}

	/**
	 * Set certficate template id
	 *
	 * @param   integer  $value  Value to set certificate template.
	 *
	 * @return  void.
	 *
	 * @since   1.0.0
	 */
	public function setCertificateTemplate($value = 0)
	{
		$this->certificate_template_id = $value;
	}

	/**
	 * Get certficate template id
	 *
	 * @return  Integer Certificate template id.
	 *
	 * @since   1.0.0
	 */
	public function getCertificateTemplate()
	{
		return $this->certificate_template_id;
	}

	/**
	 * Set client
	 *
	 * @param   integer  $value  Value to set client.
	 *
	 * @return  void.
	 *
	 * @since   1.0.0
	 */
	public function setClient($value = '')
	{
		$this->client = $value;
	}

	/**
	 * Get client
	 *
	 * @return  string client
	 *
	 * @since   1.0.0
	 */
	public function getClient()
	{
		return $this->client;
	}

	/**
	 * Set client Id
	 *
	 * @param   integer  $value  Value to set client id.
	 *
	 * @return  void.
	 *
	 * @since   1.0.0
	 */
	public function setClientId($value = 0)
	{
		$this->client_id = $value;
	}

	/**
	 * Get client Id
	 *
	 * @return  string client id
	 *
	 * @since   1.0.0
	 */
	public function getClientId()
	{
		return $this->client_id;
	}

	/**
	 * Set client issued to
	 *
	 * @param   integer  $value  Value to set client issued to.
	 *
	 * @return  void.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setClientIssuedTo($value = 0)
	{
		$this->client_issued_to = $value;
	}

	/**
	 * Get client issued to
	 *
	 * @return  string  Client issued to.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getClientIssuedTo()
	{
		return $this->client_issued_to;
	}

	/**
	 * Set client issued to name
	 *
	 * @param   string  $value  Value to set client issued to name.
	 *
	 * @return  void.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setClientIssuedToName($value = "")
	{
		$this->client_issued_to_name = $value;
	}

	/**
	 * Get client issued to name
	 *
	 * @return   string  Client issued to name.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getClientIssuedToName()
	{
		return $this->client_issued_to_name;
	}

	/**
	 * Set User Id
	 *
	 * @param   integer  $value  Value to set user id.
	 *
	 * @return  void.
	 *
	 * @since   1.0.0
	 */
	public function setUserId($value = 0)
	{
		$this->user_id = $value;
	}

	/**
	 * Get user Id
	 *
	 * @return  string user id
	 *
	 * @since   1.0.0
	 */
	public function getUserId()
	{
		return $this->user_id;
	}

	/**
	 * Set expiry date
	 *
	 * @param   string  $value  expiry date.
	 *
	 * @return  void.
	 *
	 * @since   1.0.0
	 */
	public function setExpiry($value = null)
	{
		$this->expired_on = $value;
	}

	/**
	 * Get user Id
	 *
	 * @return  string user id
	 *
	 * @since   1.0.0
	 */
	public function getExpiry()
	{
		return $this->expired_on;
	}

	/**
	 * Set comment
	 *
	 * @param   string  $value  comment.
	 *
	 * @return  void.
	 *
	 * @since   1.0.0
	 */
	public function setComment($value = null)
	{
		$this->comment = $value;
	}

	/**
	 * Get comment
	 *
	 * @return  string comment
	 *
	 * @since   1.0.0
	 */
	public function getComment()
	{
		return $this->comment;
	}

	/**
	 * Set certificate issue date
	 *
	 * @param   string  $value  comment.
	 *
	 * @return  void.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setIssuedDate($value = null)
	{
		$this->issued_on = $value;
	}

	/**
	 * Get certificate issue date
	 *
	 * @return  string comment
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getIssuedDate()
	{
		return $this->issued_on;
	}

	/**
	 * Returns the global Certificate object
	 *
	 * @param   integer  $id  The primary key of the certificate to load (optional).
	 *
	 * @return  TjCertificateCertificate  The Certificate object.
	 *
	 * @since   1.0.0
	 */
	public static function getInstance($id = 0)
	{
		if (!$id)
		{
			return new TjCertificateCertificate;
		}

		if (empty(self::$certificateObj[$id]))
		{
			$certificate = new TjCertificateCertificate($id);
			self::$certificateObj[$id] = $certificate;
		}

		return self::$certificateObj[$id];
	}

	/**
	 * Method to load a certificate object by certificate id
	 *
	 * @param   int  $id  The certificate id
	 *
	 * @return  boolean  True on success
	 *
	 * @since 1.0.0
	 */
	public function load($id)
	{
		$table = TJCERT::table("certificates");

		if (!$table->load($id))
		{
			return false;
		}

		$getPrivateProperties = $this->_getPrivateProperties();

		$getPublicProperties  = $this->_getPublicProperties();

		$publicProperties = array ();

		foreach ($getPublicProperties as $key => $value)
		{
			$publicProperties[$value->name] = '';
		}

		$tableProperties = $table->getProperties();

		$setPublicProperties = array_intersect_key($tableProperties, $publicProperties);

		// Set public properties
		$this->setProperties($setPublicProperties);

		// Set private properties
		foreach ($getPrivateProperties as $key => $value)
		{
			$this->{$value->name} = $tableProperties[$value->name];
		}

		return true;
	}

	/**
	 * Get private properties
	 *
	 * @return  object Reflection class object
	 *
	 * @since   1.0.0
	 */
	private function _getPrivateProperties()
	{
		// Get reflection class object. This will give all the information about the class
		$reflection = new ReflectionClass($this);

		return $reflection->getProperties(ReflectionProperty::IS_PRIVATE);
	}

	/**
	 * Get public properties
	 *
	 * @return  object Reflection class object
	 *
	 * @since   1.0.0
	 */
	private function _getPublicProperties()
	{
		// Get reflection class object. This will give all the information about the class
		$reflection = new ReflectionClass($this);

		return $reflection->getProperties(ReflectionProperty::IS_PUBLIC);
	}

	/**
	 * Method to save the Certificate object to the database
	 *
	 * @return  boolean  True on success
	 *
	 * @since   1.0.0
	 * @throws  \RuntimeException
	 */
	public function save()
	{
		// Create the widget table object
		$table = TJCERT::table("certificates");

		// Get public properties with data
		$properties = $this->getProperties();

		// Add private properties as getProperties() function only fetches public properties
		$getPrivateProperties = $this->_getPrivateProperties();

		// Set private properties with data
		foreach ($getPrivateProperties as $key => $value)
		{
			$properties[$value->name] = $this->{$value->name};
		}

		$table->bind($properties);

		// Allow an exception to be thrown.
		try
		{
			// Check and store the object.
			if (!$table->check())
			{
				$this->setError($table->getError());

				return false;
			}

			// Check if new record
			$isNew = empty($this->id);

			// Set current date if issued_on date is not set
			if ($isNew && !$table->issued_on)
			{
				$table->issued_on = Factory::getDate()->toSql();
			}

			$table->created_by = !empty($this->created_by) ? $this->created_by : Factory::getUser()->id;

			// If certificate id is not added from the form then add
			if (empty($this->unique_certificate_id))
			{
				$options = new Registry;
				$table->unique_certificate_id = $this->generateUniqueCertId($options);
			}

			// Store the user data in the database
			if (!($table->store()))
			{
				$this->setError($table->getError());

				return false;
			}

			$this->id = $table->id;


			if ($table->is_external && $isNew)
			{
				/* Send mail on record creation */
				require_once JPATH_SITE . '/components/com_tjcertificate/events/record.php';
				$tjCertificateTriggerRecord = new TjCertificateTriggerRecord;
				$tjCertificateTriggerRecord->onAfterRecordSave($this, true);
				Factory::getApplication()->triggerEvent('onTrainingRecordAfterAdded', array($isNew, $this));
			}

			// Fire the onTjCertificateAfterSave event.

			Factory::getApplication()->triggerEvent('onTjCertificateAfterSave', array($isNew, $this));
		}
		catch (\Exception $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		return true;
	}

	/**
	 * Method to bind an associative array of data to a certificate object
	 *
	 * @param   array  &$array  The associative array to bind to the object
	 *
	 * @return  boolean  True on success
	 *
	 * @since 1.0.0
	 */
	public function bind(&$array)
	{
		if (empty ($array))
		{
			$this->setError(Text::_('COM_TJCERTIFICATE_EMPTY_DATA'));

			return false;
		}

		$getPrivateProperties = $this->_getPrivateProperties();

		$getPublicProperties  = $this->_getPublicProperties();

		$publicProperties = array();

		foreach ($getPublicProperties as $key => $value)
		{
			$publicProperties[$value->name] = '';
		}

		$setPublicProperties = array_intersect_key($array, $publicProperties);

		// Set public properties
		if (!$this->setProperties($setPublicProperties))
		{
			$this->setError(Text::_('COM_TJCERTIFICATE_BINDING_ERROR'));

			return false;
		}

		// Set private properties
		foreach ($getPrivateProperties as $key => $value)
		{
			if (!empty($array[$value->name]))
			{
				$this->{$value->name} = $array[$value->name];
			}
		}

		// Make sure its an integer
		$this->id = (int) $this->id;

		return true;
	}

	/**
	 * Method to get issued certificate list
	 *
	 * @param   string   $client          Client e.g. com_tjlms.course
	 *
	 * @param   integer  $clientId        Specific client id
	 *
	 * @param   integer  $userId          User Id
	 *
	 * @param   boolean  $expired         Get expired certificates
	 *
	 * @param   boolean  $clientIssuedTo  Client issued to
	 *
	 * @return  boolean|array Issued certificate array
	 *
	 * @since 1.0
	 */
	public static function getIssued($client, $clientId, $userId = 0, $expired = false, $clientIssuedTo = 0)
	{
		if (empty($client) || empty($clientId))
		{
			return false;
		}

		if (empty($userId) && empty($clientIssuedTo))
		{
			return false;
		}

		$model = TJCERT::model('Certificates', array('ignore_request' => true));

		$model->setState('filter.client', $client);
		$model->setState('filter.client_id', $clientId);

		if (!empty($userId))
		{
			$model->setState('filter.user_id', $userId);
		}

		if ($clientIssuedTo)
		{
			$model->setState('filter.client_issued_to', $clientIssuedTo);
		}

		if ($expired)
		{
			$model->setState('filter.expired', $expired);
		}

		return $model->getItems();
	}

	/**
	 * Method to get certificate url.
	 *
	 * @param   array    $options        Url options
	 *
	 * @param   boolean  $showSearchBox  Show search box
	 *
	 * @param   boolean  $isExternal     Check record is external
	 *
	 * @return  string Certificate url.
	 *
	 * @since 1.0
	 */
	public function getUrl($options, $showSearchBox = true, $isExternal = false)
	{
		$app  = Factory::getApplication();
		$link = 'index.php?option=com_tjcertificate&view=certificates&layout=my';
	
		if ($isExternal)
		{
			$url = 'index.php?option=com_tjcertificate&view=trainingrecord&id=' . $this->id;
		}
		else
		{
			$url = 'index.php?option=com_tjcertificate&view=certificate&certificate=' . $this->unique_certificate_id;
		}

		// If search box is true then only show search box param in URL
		if ($showSearchBox)
		{
			$url .= '&show_search=' . $showSearchBox;
		}

		if (isset($options['popup']))
		{
			$url .= '&tmpl=component';
		}
		
		if ($app->isClient('site'))
		{
			// Get the menu instance
			$menu = new SiteMenu();

			// Get all menu items
			$menuItems = $menu->getItems('','');

			foreach ($menuItems as $menuItem) 
			{
				$menuItemLink = $menuItem->link;

				if (stripos($menuItemLink, $link) !== false) {
					$menuItemId = $menuItem->id;
					break;
				}
			}

			if (!empty($menuItemId))
			{
				$url .= '&Itemid=' . $menuItemId;
			}
		}

		if (isset($options['absolute']))
		{
			return Route::link('site', $url, false, 0, true);
		}

		return Route::_($url);
	}

	/**
	 * Method to get certificate download url.
	 *
	 * @param   array  $options  Url options
	 *
	 * @return  boolean|string Certificate download url.
	 *
	 * @since 1.0
	 */
	public function getDownloadUrl($options = array())
	{
		if (File::exists(JPATH_SITE . '/libraries/techjoomla/dompdf/autoload.inc.php'))
		{
			$url = 'index.php?option=com_tjcertificate&task=certificate.download&certificate=' . $this->unique_certificate_id;

			if (isset($options['store']))
			{
				$url .= '&store=store';
			}

			if (isset($options['absolute']))
			{
				return Uri::root() . substr(Route::_($url), strlen(Uri::base(true)) + 1);
			}

			return Route::_($url);
		}

		return false;
	}

	/**
	 * Method to get certificate download url.
	 *
	 * @param   integer  $store  Store as attachment for emails
	 *
	 * @return  boolean|string Certificate pdf url.
	 *
	 * @since 1.0
	 */
	public function pdfDownload($store = 0)
	{
		$app  = Factory::getApplication();

		if (File::exists(JPATH_SITE . '/libraries/techjoomla/dompdf/autoload.inc.php'))
		{

			$html = $this->generated_body;

			// Get template details
			$template = TJCERT::Template($this->certificate_template_id);

			$templateParams = new Registry($template->params);
			$pageSize       = $templateParams->get('certifcate_page_size', 'A4');
			$orientation    = $templateParams->get('orientation', 'portrait');
			$font           = $templateParams->get('certificate_font', 'DeJaVu Sans');
			$style          = '';

			// If the pagesize is custom then get the correct size and width.
			if ($pageSize === 'custom')
			{
				$height   = $templateParams->get('certificate_pdf_width', '80') * 28.3465;
				$width    = $templateParams->get('certificate_pdf_height', '80') * 28.3465;
				$pageSize = array(0, 0, $width, $height);
			}

			// If the font is custom then get the custmized font.
			if ($font === 'custom')
			{
				$font      = $templateParams->get('certificate_custom_font', 'DeJaVu Sans');
				$fontArray = explode(',', $font);

				// Apply multiple google fonts.

				foreach ($fontArray as $fontName)
				{
					$fontName = str_replace(' ', '', ucfirst($fontName));
					$link = '<link href="https://fonts.googleapis.com/css?family=' . $fontName . '" rel="stylesheet" type="text/css">';
					$style .= $link;
				}
			}

			require_once JPATH_SITE . "/libraries/techjoomla/dompdf/autoload.inc.php";

			$html = '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>' . $style . '</head><body>' . $html . '</body></html>';

			$html = stripslashes($html);

			// Set font for the pdf download.
			$options = new Options;
			$options->setDefaultFont($font);

			$domPDF = new DOMPDF($options);
			$domPDF->loadHTML($html);

			// Set the page size and oriendtation.
			$domPDF->setPaper($pageSize, $orientation);

			// Render PDF
			$domPDF->render();

			// Certificate name
			$certificatePdfName = File::makeSafe(Text::sprintf("COM_TJCERTIFICATE_CERTIFICATE_DOWNLOAD_FILE_NAME", $this->unique_certificate_id) . ".pdf");

			// Download as attachment for emails
			if ($store == 1)
			{
				file_put_contents($certificatePdfName, $domPDF->output());

				header('Content-Description: File Transfer');
				header('Cache-Control: public');
				header('Content-Type: application/pdf');
				header("Content-Transfer-Encoding: binary");
				header('Content-Disposition: attachment; filename="' . basename($certificatePdfName) . '"');
				header('Content-Length: ' . filesize($certificatePdfName));

				ob_clean();
				flush();
				readfile($certificatePdfName);
				jexit();
			}
			elseif ($store == 2)
			{
				return $domPDF->output();
			}

			$domPDF->stream($certificatePdfName, array("Attachment" => 1));

			jexit();
		}

		return false;
	}

	/**
	 * Method to validate issued certificate.
	 *
	 * @param   String  $uniqueCertificateId  Unique certicate Id.
	 *
	 * @return  boolean|object Certificate Table Object.
	 *
	 * @since 1.0
	 */
	public static function validateCertificate($uniqueCertificateId)
	{
		$table = TJCERT::table("certificates");

		$table->load(array('unique_certificate_id' => $uniqueCertificateId));

		if (empty($table->id) || $table->state != 1)
		{
			return false;
		}

		// Check if certificate expired
		if ($table->expired_on != '0000-00-00 00:00:00')
		{
			$now                   = new DateTime(Factory::getDate('now', 'UTC'));
			$certificateExpiryDate = new DateTime(Factory::getDate($table->expired_on, 'UTC'));

			if ($now > $certificateExpiryDate)
			{
				return false;
			}
		}

		return self::getInstance($table->id);
	}

	/**
	 * Method to issue certificate.
	 *
	 * @param   Array       $replacements  Array contains replacement.
	 * @param   JParameter  $options       Object contains Jparameters like prefix.
	 *
	 * @return  boolean|object Certificate Object.
	 *
	 * @since 1.0
	 */
	public function issueCertificate($replacements, $options)
	{
		try
		{
			// Check user_id or issued Id or certificate_template_id (this is needed to generate certificate body) is empty
			if ((empty($this->user_id) && empty($this->client_issued_to)) || empty($this->certificate_template_id))
			{
				throw new Exception(Text::_('COM_TJCERTIFICATE_CERTIFICATE_EMPTY_DATA'));
			}

			// Get template details
			$template = TJCERT::Template($this->certificate_template_id);

			if (empty($template->id))
			{
				throw new Exception(Text::_('COM_TJCERTIFICATE_TEMPLATE_INVALID'));
			}

			if (empty($this->unique_certificate_id))
			{
				// Generate unique certificate id
				$this->unique_certificate_id = $this->generateUniqueCertId($options);
			}

			// Generate unique certificate id replacement
			$replacements->certificate->cert_id = $this->unique_certificate_id;

			$params = ComponentHelper::getParams('com_tjcertificate');

			// Generate QR code of certificate.
			$qrCodeWidth  = $params->get('qr_code_width', 130);
			$qrCodeHeight = $params->get('qr_code_height', 130);

			$qrString = 'index.php?option=com_tjcertificate&view=certificate&certificate=' . $this->unique_certificate_id;
			$qrString = Uri::root() . substr(Route::_($qrString, false), strlen(Uri::base(true)) + 1);
			$qrString = urlencode($qrString);
			$qrUrl    = "https://chart.apis.google.com/chart?cht=qr&chs=";
			$qrImage  = $qrUrl . $qrCodeWidth . "x" . $qrCodeHeight . "&chl=" . $qrString . "&chld=H|0";
			$replacements->certificate->qr_code = '<img src="data:image/png;base64,' . base64_encode(file_get_contents($qrImage)) . '" class="qrimg">';

			// Generate certificate body
			$this->generated_body = $this->generateCertificateBody($template->body, $replacements);

			// Emogrify generated body with template css is available
			$emogrData = $template->getEmogrify($this->generated_body, $template->template_css);

			if (!empty($emogrData))
			{
				$this->generated_body = $emogrData;
			}

			// Get expiry date option if available
			$db = Factory::getDbo();

			if (!empty($this->expired_on))
			{
				// Check if only date is provided e.g. Y-m-d, then add 23:59:59
				if (!(DateTime::createFromFormat('Y-m-d H:i:s', $this->expired_on) !== false)
					&& (DateTime::createFromFormat('Y-m-d', $this->expired_on) !== false))
				{
					$this->expired_on = $this->expired_on . ' 23:59:59';
				}
				elseif (!(DateTime::createFromFormat('Y-m-d H:i:s', $this->expired_on) !== false))
				{
					throw new Exception(Text::_('COM_TJCERTIFICATE_TEMPLATE_INVALID_DATE'));
				}
			}
			else
			{
				$this->expired_on = $db->getNullDate();
			}

			// Save certificate
			if ($this->save())
			{
				// Remove old certificate image after re-generating the certificate
				$path = JPATH_SITE . '/media/com_tjcertificate/certificates/';
				$fileName = $this->unique_certificate_id . '.png';

				if (File::exists($path . $fileName))
				{
					File::delete($path . $fileName);
				}

				// Generate Certificate Image
				if ($params->get('cert_image_gen_type') == 'imagick')
				{
					// Generate image from PDF
					$this->generateImageFromPDF($this->pdfDownload(2));
				}

				return self::getInstance($this->id);
			}
		}
		catch (\Exception $e)
		{
			return $e->getMessage();
		}
	}

	/**
	 * Method to generate certificate body.
	 *
	 * @param   String  $templateBody  Template Body.
	 * @param   Array   $replacements  Array contains replacement.
	 *
	 * @return  string Certificate body.
	 *
	 * @since 1.0
	 */
	protected function generateCertificateBody($templateBody, $replacements)
	{
		$templateBody = stripslashes($templateBody);
		$matches      = $this->getTags($templateBody);

		$replacamentTags = $matches[0];
		$tags            = $matches[1];
		$index           = 0;

		if (isset($replacements))
		{
			foreach ($replacamentTags as $ind => $replacamentTag)
			{
				// Explode e.g course.name with "." so $data[0]=course and $data[1]=name
				$data = explode(".", $tags[$ind]);

				if (isset($data))
				{
					$key   = $data[0];
					$value = $data[1];

					if (!empty($replacements->$key->$value) || $replacements->$key->$value == 0)
					{
						$replaceWith = $replacements->$key->$value;
					}
					else
					{
						$replaceWith = "";
					}

					if (isset ($replaceWith))
					{
						$templateBody = str_replace($replacamentTag, $replaceWith, $templateBody);
						$index++;
					}
				}
			}
		}

		return $templateBody;
	}

	/**
	 * Method to get Tags.
	 *
	 * @param   String  $templateBody  Template Body.
	 *
	 * @return  array   $matches
	 *
	 * @since 1.0
	 */
	public static function getTags($templateBody)
	{
		//  Pattern for {text};
		$pattern = "/{([^}]*)}/";

		preg_match_all($pattern, $templateBody, $matches);

		//  $matches[0] will store array like {course.name} and $matches[1] will store array like course.name.
		//  Explode it and make it course->name
		return $matches;
	}

	/**
	 * Method to generate unique certificate Id.
	 *
	 * @param   JParameter  $options  Object contains Jparameters like prefix.
	 *
	 * @return   string
	 *
	 * @since    1.0.0
	 */
	protected function generateUniqueCertId($options)
	{
		// Get prefix option if available
		$params = ComponentHelper::getParams('com_tjcertificate');
		$this->defaultCertPrefix = $options->get('prefix', $params->get('certificate_prefix', $this->defaultCertPrefix));

		// Get certificate random string config
		$randomStringLength = $options->get('certificate_random_string_length', $params->get('certificate_random_string_length', 30));
		$fixedLength  = $options->get('certificate_fixed_random_string_length', $params->get('certificate_fixed_random_string_length', true));

		if (empty($randomStringLength) || $randomStringLength > 30 || $randomStringLength < 0)
		{
			$randomStringLength = rand(5, 30);
		}
		else
		{
			$randomStringLength = $fixedLength ? $randomStringLength : rand(5, $randomStringLength);
		}

		$characters = '0123456789';
		$charactersLength = strlen($characters);
		$certificateString = '';

		for ($i = 0; $i < $randomStringLength; $i++)
		{
			$certificateString .= $characters[rand(0, $charactersLength - 1)];
		}

		// Check if random string exists
		$certificateString = $this->defaultCertPrefix . '-' . $certificateString;
		$table = TJCERT::table("certificates");

		$table->load(array('unique_certificate_id' => $certificateString));

		if (!empty($table->unique_certificate_id))
		{
			$this->generateUniqueCertId($options);
		}

		return $certificateString;
	}

	/**
	 * This function checks the certificate download permission
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function canDownload()
	{
		$user = Factory::getUser();

		if ($user->authorise('certificate.download.all', 'com_tjcertificate'))
		{
			return true;
		}

		if ($user->authorise('certificate.download.own', 'com_tjcertificate'))
		{
			if ($user->get('id') == $this->user_id)
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Method to get linkedIn add to profile url.
	 *
	 * @return  STRING
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function getAddToLinkedInProfileUrl()
	{
		$params   = ComponentHelper::getParams('com_tjcertificate');
		$config   = Factory::getConfig();
		$siteName = $config->get('sitename');

		$issuedMonth = HTMLHelper::_('date', $this->issued_on, 'm');
		$issuedYear  = HTMLHelper::_('date', $this->issued_on, 'Y');

		$expirationDetails = null;

		if ($this->expired_on != '0000-00-00 00:00:00')
		{
			$expirationMonth   = HTMLHelper::_('date', $this->expired_on, 'm');
			$expirationYear    = HTMLHelper::_('date', $this->expired_on, 'Y');
			$expirationDetails = '&expirationYear=' . $expirationYear . '&expirationMonth=' . $expirationMonth;
		}

		$orgParam = '&' . $params->get('organization_info') . '=' . $params->get('organization_id_name');

		// Get client data
		PluginHelper::importPlugin('content');
		$result = Factory::getApplication()->triggerEvent('onGetCertificateClientData', array($this->client_id, $this->client));
		$clientData = $result[0];

		$urlOptions             = array();
		$urlOptions['absolute'] = true;
		$certificateUrl         = $this->getURL($urlOptions, false);

		$certificateTitle   = $clientData->title ? $clientData->title : $siteName . ' ' . Text::_('COM_TJCERTIFICATE_CERTIFICATE_DETAIL_VIEW_HEAD');
		$linkedInprofileUrl = 'https://www.linkedin.com/profile/add?startTask=CERTIFICATION_NAME&name=' . $certificateTitle . $orgParam
		. '&issueYear=' . $issuedYear . '&issueMonth=' . $issuedMonth . $expirationDetails
		. '&certUrl=' . urlencode($certificateUrl) . '&certId=' . $this->unique_certificate_id;

		return $linkedInprofileUrl;
	}

	/**
	 * Method to generate certificate image from PDF.
	 *
	 * @param   string  $domPDFOutput  DomPDF output.
	 *
	 * @return  void
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function generateImageFromPDF($domPDFOutput)
	{
		if (extension_loaded('imagick'))
		{
			if (!Folder::exists($this->certImageDir))
			{
				Folder::create($this->certImageDir);
			}

			if (!Folder::exists($this->certTmpDir))
			{
				Folder::create($this->certTmpDir);
			}

			$tmpPDF = $this->certTmpDir . $this->unique_certificate_id . '.pdf';

			file_put_contents($tmpPDF, $domPDFOutput);

			$im = new Imagick;
			$im->setResolution(72, 72);
			$im->readimage($tmpPDF);
			$im->setImageBackgroundColor('white');
			$im->setImageAlphaChannel(imagick::ALPHACHANNEL_REMOVE);
			$im->mergeImageLayers(imagick::LAYERMETHOD_FLATTEN);
			$im->writeImage($this->certImageDir . $this->unique_certificate_id . '.png');
			$im->clear();
			$im->destroy();

			if (File::exists($tmpPDF))
			{
				File::delete($tmpPDF);
			}
		}
	}

	/**
	 * Get formated date
	 *
	 * @param   string  $datetime  The current offset
	 *
	 * @return  string  formatted datetime
	 *
	 * @since  __DEPLOY_VERSION__
	 **/
	public function getFormatedDate($datetime)
	{
		$params   = ComponentHelper::getParams('com_tjcertificate');
		$dateFormat = $params->get('date_format_show');

		if ($dateFormat == "custom")
		{
			$dateFormat = $params->get('custom_format');
		}

		return HTMLHelper::_('date', $datetime, $dateFormat, true);
	}
}
