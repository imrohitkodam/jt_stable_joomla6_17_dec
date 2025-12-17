<?php
/**
 * @package    PlgSystemTjupdates
 *
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (C) 2009 - 2020 Techjoomla. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access.
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Component\ComponentHelper;

// Load language file for plugin
$lang = Factory::getLanguage();
$lang->load('plg_system_tjupdates', JPATH_ADMINISTRATOR);

/**
 * Techjoomla updates plugin
 *
 * This plugin will help setting download id to enable updates from Joomla backend
 *
 * @since  1.0
 */
class PlgSystemTjupdates extends CMSPlugin
{
	protected $app;

	protected $dbo;

	protected $extensions;

	protected $extensionsDetails;

	protected $freeExtensions;

	protected $input;

	/**
	 * Constructor
	 *
	 * @param   object  &$subject  The object to observe
	 * @param   array   $config    An optional associative array of configuration settings.
	 *                             Recognized key values include 'name', 'group', 'params', 'language'
	 *                             (this list is not meant to be comprehensive).
	 *
	 * @since   1.5
	 */
	public function __construct(&$subject, $config = array())
	{
		$this->dbo   = Factory::getDbo();
		$this->input = Factory::getApplication()->input;

		// List of free extensions
		$this->freeExtensions = array('althome', 'canva', 'pdf_embed', 'rtop');

		// List of all extensions
		$this->extensions = array(
			'althome',
			'canva',
			'com_activitystream',
			'com_api',
			'com_emailbeautifier',
			'com_hierarchy',
			'com_importer',
			'com_invitex',
			'com_jbolo',
			'com_jgive',
			'com_jlike',
			'com_jmailalerts',
			'com_jticketing',
			'com_psuggest',
			'com_quick2cart',
			'com_socialads',
			'com_subusers',
			'com_tc',
			'com_tjdashboard',
			'com_tjfields',
			'com_tjlms',
			'com_tjnotifications',
			'com_tjreports',
			'com_tjucm',
			'com_tjvendors',
			'pdf_embed',
			'rtop'
		);

		// Put in all extension details
		$this->extensionsDetails = array();

		// Logged in Home Page
		$this->extensionsDetails['althome']                   = new stdClass;
		$this->extensionsDetails['althome']->extension        = 'althome';
		$this->extensionsDetails['althome']->extensionElement = 'althome';
		$this->extensionsDetails['althome']->extensionType    = 'plugin';
		$this->extensionsDetails['althome']->updateStreamName = 'Logged in Home Page';
		$this->extensionsDetails['althome']->updateStreamType = 'extension';
		$this->extensionsDetails['althome']->updateStreamUrl  = 'https://techjoomla.com/updates/stream/althome.xml?format=xml';

		// Canva
		$this->extensionsDetails['canva']                   = new stdClass;
		$this->extensionsDetails['canva']->extension        = 'canva';
		$this->extensionsDetails['canva']->extensionElement = 'pkg_Canva';
		$this->extensionsDetails['canva']->extensionType    = 'package';
		$this->extensionsDetails['canva']->updateStreamName = 'Canva';
		$this->extensionsDetails['canva']->updateStreamType = 'extension';
		$this->extensionsDetails['canva']->updateStreamUrl  = 'https://techjoomla.com/updates/stream/canva.xml?format=xml';

		// Activitystream
		$this->extensionsDetails['com_activitystream']                   = new stdClass;
		$this->extensionsDetails['com_activitystream']->extension        = 'com_activitystream';
		$this->extensionsDetails['com_activitystream']->extensionElement = 'com_activitystream';
		$this->extensionsDetails['com_activitystream']->extensionType    = 'component';
		$this->extensionsDetails['com_activitystream']->updateStreamName = 'Activity Stream';
		$this->extensionsDetails['com_activitystream']->updateStreamType = 'extension';
		$this->extensionsDetails['com_activitystream']->updateStreamUrl  = 'https://techjoomla.com/updates/stream/activitystream.xml?format=xml';
		$this->extensionsDetails['com_activitystream']->downloadidParam  = 'downloadid';

		// COM API
		$this->extensionsDetails['com_api']                   = new stdClass;
		$this->extensionsDetails['com_api']->extension        = 'com_api';
		$this->extensionsDetails['com_api']->extensionElement = 'com_api';
		$this->extensionsDetails['com_api']->extensionType    = 'component';
		$this->extensionsDetails['com_api']->updateStreamName = 'Joomla REST API';
		$this->extensionsDetails['com_api']->updateStreamType = 'extension';
		$this->extensionsDetails['com_api']->updateStreamUrl  = 'https://techjoomla.com/updates/stream/api.xml?format=xml';
		$this->extensionsDetails['com_api']->downloadidParam  = 'downloadid';

		// EmailBeautifier
		$this->extensionsDetails['com_emailbeautifier']                   = new stdClass;
		$this->extensionsDetails['com_emailbeautifier']->extension        = 'com_emailbeautifier';
		$this->extensionsDetails['com_emailbeautifier']->extensionElement = 'com_emailbeautifier';
		$this->extensionsDetails['com_emailbeautifier']->extensionType    = 'component';
		$this->extensionsDetails['com_emailbeautifier']->updateStreamName = 'EmailBeautifier';
		$this->extensionsDetails['com_emailbeautifier']->updateStreamType = 'extension';
		$this->extensionsDetails['com_emailbeautifier']->updateStreamUrl  = 'https://techjoomla.com/updates/stream/emailbeautifier.xml?format=xml';
		$this->extensionsDetails['com_emailbeautifier']->downloadidParam  = 'downloadid';

		// Hierarchy
		$this->extensionsDetails['com_hierarchy']                   = new stdClass;
		$this->extensionsDetails['com_hierarchy']->extension        = 'com_hierarchy';
		$this->extensionsDetails['com_hierarchy']->extensionElement = 'com_hierarchy';
		$this->extensionsDetails['com_hierarchy']->extensionType    = 'component';
		$this->extensionsDetails['com_hierarchy']->updateStreamName = 'Hierarchy';
		$this->extensionsDetails['com_hierarchy']->updateStreamType = 'extension';
		$this->extensionsDetails['com_hierarchy']->updateStreamUrl  = 'https://techjoomla.com/updates/stream/hierarchy.xml?format=xml';
		$this->extensionsDetails['com_hierarchy']->downloadidParam  = 'downloadid';

		// Importer
		$this->extensionsDetails['com_importer']                   = new stdClass;
		$this->extensionsDetails['com_importer']->extension        = 'com_importer';
		$this->extensionsDetails['com_importer']->extensionElement = 'com_importer';
		$this->extensionsDetails['com_importer']->extensionType    = 'component';
		$this->extensionsDetails['com_importer']->updateStreamName = 'Importer';
		$this->extensionsDetails['com_importer']->updateStreamType = 'extension';
		$this->extensionsDetails['com_importer']->updateStreamUrl  = 'https://techjoomla.com/updates/stream/importer.xml?format=xml';
		$this->extensionsDetails['com_importer']->downloadidParam  = 'downloadid';

		// InviteX
		$this->extensionsDetails['com_invitex']                   = new stdClass;
		$this->extensionsDetails['com_invitex']->extension        = 'com_invitex';
		$this->extensionsDetails['com_invitex']->extensionElement = 'com_invitex';
		$this->extensionsDetails['com_invitex']->extensionType    = 'component';
		$this->extensionsDetails['com_invitex']->updateStreamName = 'InviteX';
		$this->extensionsDetails['com_invitex']->updateStreamType = 'extension';
		$this->extensionsDetails['com_invitex']->updateStreamUrl  = 'https://techjoomla.com/updates/stream/invitex.xml?format=xml';
		$this->extensionsDetails['com_invitex']->downloadidParam  = 'downloadid';

		// JBolo
		$this->extensionsDetails['com_jbolo']                   = new stdClass;
		$this->extensionsDetails['com_jbolo']->extension        = 'com_jbolo';
		$this->extensionsDetails['com_jbolo']->extensionElement = 'com_jbolo';
		$this->extensionsDetails['com_jbolo']->extensionType    = 'component';
		$this->extensionsDetails['com_jbolo']->updateStreamName = 'JBolo';
		$this->extensionsDetails['com_jbolo']->updateStreamType = 'extension';
		$this->extensionsDetails['com_jbolo']->updateStreamUrl  = 'https://techjoomla.com/updates/stream/jbolo.xml?format=xml';
		$this->extensionsDetails['com_jbolo']->downloadidParam  = 'downloadid';

		// JGive
		$this->extensionsDetails['com_jgive']                   = new stdClass;
		$this->extensionsDetails['com_jgive']->extension        = 'com_jgive';
		$this->extensionsDetails['com_jgive']->extensionElement = 'pkg_jgive';
		$this->extensionsDetails['com_jgive']->extensionType    = 'package';
		$this->extensionsDetails['com_jgive']->updateStreamName = 'JGive';
		$this->extensionsDetails['com_jgive']->updateStreamType = 'extension';
		$this->extensionsDetails['com_jgive']->updateStreamUrl  = 'https://techjoomla.com/updates/stream/jgive.xml?format=xml';
		$this->extensionsDetails['com_jgive']->downloadidParam  = 'downloadid';

		// JLike
		$this->extensionsDetails['com_jlike']                   = new stdClass;
		$this->extensionsDetails['com_jlike']->extension        = 'com_jlike';
		$this->extensionsDetails['com_jlike']->extensionElement = 'com_jlike';
		$this->extensionsDetails['com_jlike']->extensionType    = 'component';
		$this->extensionsDetails['com_jlike']->updateStreamName = 'JLike';
		$this->extensionsDetails['com_jlike']->updateStreamType = 'extension';
		$this->extensionsDetails['com_jlike']->updateStreamUrl  = 'https://techjoomla.com/updates/stream/jlike.xml?format=xml';
		$this->extensionsDetails['com_jlike']->downloadidParam  = 'downloadid';

		// JMailAlerts
		$this->extensionsDetails['com_jmailalerts']                   = new stdClass;
		$this->extensionsDetails['com_jmailalerts']->extension        = 'com_jmailalerts';
		$this->extensionsDetails['com_jmailalerts']->extensionElement = 'com_jmailalerts';
		$this->extensionsDetails['com_jmailalerts']->extensionType    = 'component';
		$this->extensionsDetails['com_jmailalerts']->updateStreamName = 'JMailAlerts';
		$this->extensionsDetails['com_jmailalerts']->updateStreamType = 'extension';
		$this->extensionsDetails['com_jmailalerts']->updateStreamUrl  = 'https://techjoomla.com/updates/stream/jmailalerts.xml?format=xml';
		$this->extensionsDetails['com_jmailalerts']->downloadidParam  = 'downloadid';

		// JTicketing
		$this->extensionsDetails['com_jticketing']                   = new stdClass;
		$this->extensionsDetails['com_jticketing']->extension        = 'com_jticketing';
		$this->extensionsDetails['com_jticketing']->extensionElement = 'pkg_jticketing';
		$this->extensionsDetails['com_jticketing']->extensionType    = 'package';
		$this->extensionsDetails['com_jticketing']->updateStreamName = 'JTicketing';
		$this->extensionsDetails['com_jticketing']->updateStreamType = 'extension';
		$this->extensionsDetails['com_jticketing']->updateStreamUrl  = 'https://techjoomla.com/updates/stream/jticketing.xml?format=xml';
		$this->extensionsDetails['com_jticketing']->downloadidParam  = 'downloadid';

		// PDF Embed
		$this->extensionsDetails['pdf_embed']                   = new stdClass;
		$this->extensionsDetails['pdf_embed']->extension        = 'pdf_embed';
		$this->extensionsDetails['pdf_embed']->extensionElement = 'pkg_pdf_embed';
		$this->extensionsDetails['pdf_embed']->extensionType    = 'package';
		$this->extensionsDetails['pdf_embed']->updateStreamName = 'PDF Embed';
		$this->extensionsDetails['pdf_embed']->updateStreamType = 'extension';
		$this->extensionsDetails['pdf_embed']->updateStreamUrl  = 'https://techjoomla.com/updates/stream/pdf_embed.xml?format=xml';

		// People Suggest
		$this->extensionsDetails['com_psuggest']                   = new stdClass;
		$this->extensionsDetails['com_psuggest']->extension        = 'com_psuggest';
		$this->extensionsDetails['com_psuggest']->extensionElement = 'com_psuggest';
		$this->extensionsDetails['com_psuggest']->extensionType    = 'component';
		$this->extensionsDetails['com_psuggest']->updateStreamName = 'People Suggest';
		$this->extensionsDetails['com_psuggest']->updateStreamType = 'extension';
		$this->extensionsDetails['com_psuggest']->updateStreamUrl  = 'https://techjoomla.com/updates/stream/psuggest.xml?format=xml';
		$this->extensionsDetails['com_psuggest']->downloadidParam  = 'downloadid';

		// Quick2cart
		$this->extensionsDetails['com_quick2cart']                   = new stdClass;
		$this->extensionsDetails['com_quick2cart']->extension        = 'com_quick2cart';
		$this->extensionsDetails['com_quick2cart']->extensionElement = 'pkg_quick2cart';
		$this->extensionsDetails['com_quick2cart']->extensionType    = 'package';
		$this->extensionsDetails['com_quick2cart']->updateStreamName = 'Quick2cart';
		$this->extensionsDetails['com_quick2cart']->updateStreamType = 'extension';
		$this->extensionsDetails['com_quick2cart']->updateStreamUrl  = 'https://techjoomla.com/updates/stream/quick2cart.xml?format=xml';
		$this->extensionsDetails['com_quick2cart']->downloadidParam  = 'downloadid';

		// Return to top
		$this->extensionsDetails['rtop']                   = new stdClass;
		$this->extensionsDetails['rtop']->extension        = 'rtop';
		$this->extensionsDetails['rtop']->extensionElement = 'rtop';
		$this->extensionsDetails['rtop']->extensionType    = 'plugin';
		$this->extensionsDetails['rtop']->updateStreamName = 'Return to Top';
		$this->extensionsDetails['rtop']->updateStreamType = 'extension';
		$this->extensionsDetails['rtop']->updateStreamUrl  = 'https://techjoomla.com/updates/stream/rtop.xml?format=xml';

		// Social ads
		$this->extensionsDetails['com_socialads']                   = new stdClass;
		$this->extensionsDetails['com_socialads']->extension        = 'com_socialads';
		$this->extensionsDetails['com_socialads']->extensionElement = 'com_socialads';
		$this->extensionsDetails['com_socialads']->extensionType    = 'component';
		$this->extensionsDetails['com_socialads']->updateStreamName = 'SocialAds';
		$this->extensionsDetails['com_socialads']->updateStreamType = 'extension';
		$this->extensionsDetails['com_socialads']->updateStreamUrl  = 'https://techjoomla.com/updates/stream/socialads.xml?format=xml';
		$this->extensionsDetails['com_socialads']->downloadidParam  = 'downloadid';

		// Subusers
		$this->extensionsDetails['com_subusers']                   = new stdClass;
		$this->extensionsDetails['com_subusers']->extension        = 'com_subusers';
		$this->extensionsDetails['com_subusers']->extensionElement = 'com_subusers';
		$this->extensionsDetails['com_subusers']->extensionType    = 'component';
		$this->extensionsDetails['com_subusers']->updateStreamName = 'Subusers';
		$this->extensionsDetails['com_subusers']->updateStreamType = 'extension';
		$this->extensionsDetails['com_subusers']->updateStreamUrl  = 'https://techjoomla.com/updates/stream/subusers.xml?format=xml';
		$this->extensionsDetails['com_subusers']->downloadidParam  = 'downloadid';

		// Terms and conditions manager
		$this->extensionsDetails['com_tc']                   = new stdClass;
		$this->extensionsDetails['com_tc']->extension        = 'com_tc';
		$this->extensionsDetails['com_tc']->extensionElement = 'com_tc';
		$this->extensionsDetails['com_tc']->extensionType    = 'component';
		$this->extensionsDetails['com_tc']->updateStreamName = 'Terms and conditions manager';
		$this->extensionsDetails['com_tc']->updateStreamType = 'extension';
		$this->extensionsDetails['com_tc']->updateStreamUrl  = 'https://techjoomla.com/updates/stream/tc.xml?format=xml';
		$this->extensionsDetails['com_tc']->downloadidParam  = 'downloadid';

		// TJ dashboard
		$this->extensionsDetails['com_tjdashboard']                   = new stdClass;
		$this->extensionsDetails['com_tjdashboard']->extension        = 'com_tjdashboard';
		$this->extensionsDetails['com_tjdashboard']->extensionElement = 'com_tjdashboard';
		$this->extensionsDetails['com_tjdashboard']->extensionType    = 'component';
		$this->extensionsDetails['com_tjdashboard']->updateStreamName = 'TJ dashboard';
		$this->extensionsDetails['com_tjdashboard']->updateStreamType = 'extension';
		$this->extensionsDetails['com_tjdashboard']->updateStreamUrl  = 'https://techjoomla.com/updates/stream/tjdashboard.xml?format=xml';
		$this->extensionsDetails['com_tjdashboard']->downloadidParam  = 'downloadid';

		// TJ fields
		$this->extensionsDetails['com_tjfields']                   = new stdClass;
		$this->extensionsDetails['com_tjfields']->extension        = 'com_tjfields';
		$this->extensionsDetails['com_tjfields']->extensionElement = 'com_tjfields';
		$this->extensionsDetails['com_tjfields']->extensionType    = 'component';
		$this->extensionsDetails['com_tjfields']->updateStreamName = 'TJ Fields';
		$this->extensionsDetails['com_tjfields']->updateStreamType = 'extension';
		$this->extensionsDetails['com_tjfields']->updateStreamUrl  = 'https://techjoomla.com/updates/stream/tjfields.xml?format=xml';
		$this->extensionsDetails['com_tjfields']->downloadidParam  = 'downloadid';

		// Shika
		$this->extensionsDetails['com_tjlms']                   = new stdClass;
		$this->extensionsDetails['com_tjlms']->extension        = 'com_tjlms';
		$this->extensionsDetails['com_tjlms']->extensionElement = 'pkg_shika';
		$this->extensionsDetails['com_tjlms']->extensionType    = 'package';
		$this->extensionsDetails['com_tjlms']->updateStreamName = 'Shika';
		$this->extensionsDetails['com_tjlms']->updateStreamType = 'extension';
		$this->extensionsDetails['com_tjlms']->updateStreamUrl  = 'https://techjoomla.com/updates/stream/tjlms.xml?format=xml';
		$this->extensionsDetails['com_tjlms']->downloadidParam  = 'downloadid';

		// Notifications
		$this->extensionsDetails['com_tjnotifications']                   = new stdClass;
		$this->extensionsDetails['com_tjnotifications']->extension        = 'com_tjnotifications';
		$this->extensionsDetails['com_tjnotifications']->extensionElement = 'com_tjnotifications';
		$this->extensionsDetails['com_tjnotifications']->extensionType    = 'component';
		$this->extensionsDetails['com_tjnotifications']->updateStreamName = 'TJ Notifications';
		$this->extensionsDetails['com_tjnotifications']->updateStreamType = 'extension';
		$this->extensionsDetails['com_tjnotifications']->updateStreamUrl  = 'https://techjoomla.com/updates/stream/tjnotifications.xml?format=xml';
		$this->extensionsDetails['com_tjnotifications']->downloadidParam  = 'downloadid';

		// TJ Reports
		$this->extensionsDetails['com_tjreports']                   = new stdClass;
		$this->extensionsDetails['com_tjreports']->extension        = 'com_tjreports';
		$this->extensionsDetails['com_tjreports']->extensionElement = 'com_tjreports';
		$this->extensionsDetails['com_tjreports']->extensionType    = 'component';
		$this->extensionsDetails['com_tjreports']->updateStreamName = 'TJ Reports';
		$this->extensionsDetails['com_tjreports']->updateStreamType = 'extension';
		$this->extensionsDetails['com_tjreports']->updateStreamUrl  = 'https://techjoomla.com/updates/stream/tjreports.xml?format=xml';
		$this->extensionsDetails['com_tjreports']->downloadidParam  = 'downloadid';

		// TJ UCM
		$this->extensionsDetails['com_tjucm']                   = new stdClass;
		$this->extensionsDetails['com_tjucm']->extension        = 'com_tjucm';
		$this->extensionsDetails['com_tjucm']->extensionElement = 'com_tjucm';
		$this->extensionsDetails['com_tjucm']->extensionType    = 'component';
		$this->extensionsDetails['com_tjucm']->updateStreamName = 'TJ UCM';
		$this->extensionsDetails['com_tjucm']->updateStreamType = 'extension';
		$this->extensionsDetails['com_tjucm']->updateStreamUrl  = 'https://techjoomla.com/updates/stream/tjucm.xml?format=xml';
		$this->extensionsDetails['com_tjucm']->downloadidParam  = 'downloadid';

		// TJ Vendors
		$this->extensionsDetails['com_tjvendors']                   = new stdClass;
		$this->extensionsDetails['com_tjvendors']->extension        = 'com_tjvendors';
		$this->extensionsDetails['com_tjvendors']->extensionElement = 'com_tjvendors';
		$this->extensionsDetails['com_tjvendors']->extensionType    = 'component';
		$this->extensionsDetails['com_tjvendors']->updateStreamName = 'TJ Vendors';
		$this->extensionsDetails['com_tjvendors']->updateStreamType = 'extension';
		$this->extensionsDetails['com_tjvendors']->updateStreamUrl  = 'https://techjoomla.com/updates/stream/tjvendors.xml?format=xml';
		$this->extensionsDetails['com_tjvendors']->downloadidParam  = 'downloadid';

		parent::__construct($subject, $config);
	}

	/**
	 * Adds JS code for update check
	 *
	 * @return  void
	 *
	 * @since   1.0.1
	 */
	public function onAfterDispatch()
	{
		// Do not run on site
		if ($this->app->isClient('site'))
		{
			return;
		}

		$option = $this->input->get('option', '', 'string');

		// Chk if this component is in our extension list, and extension details are set
		if (!in_array($option, $this->extensions) || !isset($this->extensionsDetails[$option]))
		{
			return;
		}

		// Get current extension details
		$extension = $this->extensionsDetails[$option];

		// Try to get download id, current version
		$downloadId     = $this->getDownloadId($extension);
		$currentVersion = $this->getCurrentVersion($extension);

		// JS constant
		Text::script('PLG_SYSTEM_TJUPDATES_UPDATE_MSG');

		HTMLHelper::_('jquery.framework');
		HTMLHelper::_('script', 'plugins/system/tjupdates/tjupdates.js');

		$showUpdateNotice = $this->params->get('showUpdateNotice', 1);

		$document = Factory::getDocument();
		$document->addScriptDeclaration("
			jQuery(document).ready(function() {
				tjupdates.check('" . $option . "', '" . $currentVersion . "', '" . $downloadId . "', " . $showUpdateNotice . ");
			});"
		);
	}

	/**
	 * The check is triggered after the page has fully rendered
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function onAfterRender()
	{
		// Do not run on site
		if ($this->app->isClient('site'))
		{
			return;
		}

		// Get option, view
		$option = $this->input->get('option', '', 'string');
		$view   = $this->input->get('view', '', 'string');

		// Run only on updates view in installer component
		if ($option == 'com_installer' && $view == 'update')
		{
			// Process all extensions
			foreach ($this->extensions as $ext)
			{
				// Get current extension details
				$extension = $this->extensionsDetails[$ext];

				// Try to get download id
				$downloadId = $this->getDownloadId($extension);

				if ($downloadId)
				{
					// Set this download id and pass it on ahead
					$extension->downloadidParam = $downloadId;

					$this->onRefreshUpdateSite($extension);
				}
			}
		}
	}

	/**
	 * Get download id either form this plugin or from related extensions settings
	 *
	 * @param   object  $extension  An array of extension details
	 *
	 * @return  mixed  string or boolean
	 */
	private function getDownloadId($extension)
	{
		// For free extensions, get it from current plugin config
		if (in_array($extension->extension, $this->freeExtensions))
		{
			return $this->params->get('downloadid', false);
		}
		// @TODO - for now all extensions are assumed to be components
		else
		{
			$params = ComponentHelper::getParams($extension->extension);

			return $params->get($extension->downloadidParam, false);
		}
	}

	/**
	 * Refreshes the Joomla! update sites for this extension as needed
	 *
	 * @param   object  $extension  An array of extension details
	 *
	 * @return  void
	 */
	public function onRefreshUpdateSite($extension)
	{
		// Extra query for Joomla 3.0 onwards
		$extra_query = null;

		/*if (preg_match('/^([0-9]{1,}:)?[0-9a-f]{32}$/i', $extension->downloadidParam))
		{
			$extra_query = 'dlid=' . $extension->downloadidParam;
		}*/

		$extra_query = 'key=' . $extension->downloadidParam;

		// Setup update site array for storing in database
		$update_site = array(
			'name'                 => $extension->updateStreamName,
			'type'                 => $extension->updateStreamType,
			'location'             => $extension->updateStreamUrl,
			'enabled'              => 1,
			'last_check_timestamp' => 0,
			'extra_query'          => $extra_query
		);

		$db = $this->dbo;

		// Get current extension ID
		$extension_id = $this->getExtensionId($extension);

		if (!$extension_id)
		{
			return;
		}

		// Get the update sites for current extension
		$query = $db->getQuery(true)
			->select($db->qn('update_site_id'))
			->from($db->qn('#__update_sites_extensions'))
			->where($db->qn('extension_id') . ' = ' . $db->q($extension_id));
		$db->setQuery($query);

		$updateSiteIDs = $db->loadColumn(0);

		if (!count($updateSiteIDs))
		{
			// No update sites defined. Create a new one.
			$newSite = (object) $update_site;
			$db->insertObject('#__update_sites', $newSite);

			$id = $db->insertid();

			$updateSiteExtension = (object) array(
				'update_site_id' => $id,
				'extension_id'   => $extension_id,
			);

			$db->insertObject('#__update_sites_extensions', $updateSiteExtension);
		}
		else
		{
			// Loop through all update sites
			foreach ($updateSiteIDs as $id)
			{
				$query = $db->getQuery(true)
					->select('*')
					->from($db->qn('#__update_sites'))
					->where($db->qn('update_site_id') . ' = ' . $db->q($id));
				$db->setQuery($query);
				$aSite = $db->loadObject();

				// Does the name and location match?
				if (($aSite->name == $update_site['name']) && ($aSite->location == $update_site['location']))
				{
					// Do we have the extra_query property (J 3.2+) and does it match?
					if (property_exists($aSite, 'extra_query'))
					{
						if ($aSite->extra_query == $update_site['extra_query'])
						{
							continue;
						}
					}
					else
					{
						// Joomla! 3.1 or earlier. Updates may or may not work.
						continue;
					}
				}

				$update_site['update_site_id'] = $id;
				$newSite = (object) $update_site;
				$db->updateObject('#__update_sites', $newSite, 'update_site_id', true);
			}
		}

		// If latest version is found
		if ($this->onGetLatestVersion($extension))
		{
			// Also update updates table to add download id as extra_query
			$query      = $db->getQuery(true);
			$fields     = array($db->quoteName('extra_query') . ' = ' . $db->quote($extra_query));
			$conditions = array($db->quoteName('extension_id') . ' = ' . $extension_id);
			$query->update($db->quoteName('#__updates'))->set($fields)->where($conditions);
			$db->setQuery($query);
			$db->execute();
		}
	}

	/**
	 * Get extension id for this extension
	 *
	 * @param   object  $extension  An array of extension details
	 *
	 * @return  mixed  boolean or string
	 *
	 * @since   1.0
	 */
	private function getExtensionId($extension)
	{
		$db = $this->dbo;

		// Get current extension ID
		$query = $db->getQuery(true)
			->select($db->qn('extension_id'))
			->from($db->qn('#__extensions'))
			->where($db->qn('type') . ' = ' . $db->q($extension->extensionType))
			->where($db->qn('element') . ' = ' . $db->q($extension->extensionElement));
		$db->setQuery($query);

		$extension_id = $db->loadResult();

		if (empty($extension_id))
		{
			return false;
		}
		else
		{
			return $extension_id;
		}
	}

	/**
	 * Get latest version fetched by joomla updater
	 *
	 * @param   object  $extension  An array of extension details
	 *
	 * @return  mixed  boolean or string
	 *
	 * @since   1.0
	 */
	public function onGetLatestVersion($extension)
	{
		// Get current extension ID
		$extension_id = $this->getExtensionId($extension);

		if (!$extension_id)
		{
			return false;
		}

		$db = $this->dbo;

		// Get current extension ID
		$query = $db->getQuery(true)
			->select($db->qn(array('version', 'infourl')))
			->from($db->qn('#__updates'))
			->where($db->qn('extension_id') . ' = ' . $db->q($extension_id));
		$db->setQuery($query);

		$latestVersion = $db->loadObject();

		if (empty($latestVersion))
		{
			return false;
		}
		else
		{
			return $latestVersion;
		}
	}

	/**
	 * Get current version of extension from extensions table
	 *
	 * @param   object  $extension  An array of extension details
	 *
	 * @return  mixed  boolean or string
	 *
	 * @since   1.0.1
	 */
	private function getCurrentVersion($extension)
	{
		// Get current extension ID
		$extension_id = $this->getExtensionId($extension);

		if (!$extension_id)
		{
			return false;
		}

		$db = $this->dbo;

		// Get current extension ID
		$query = $db->getQuery(true)
			->select($db->qn(array('extension_id', 'manifest_cache')))
			->from($db->qn('#__extensions'))
			->where($db->qn('extension_id') . ' = ' . $db->q($extension_id));
		$db->setQuery($query);

		$latestVersion = $db->loadObject();

		if (empty($latestVersion))
		{
			return false;
		}
		else
		{
			$manifest = json_decode($latestVersion->manifest_cache);

			return $manifest->version;
		}
	}
}
