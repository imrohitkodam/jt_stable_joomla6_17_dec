<?php
declare(strict_types=1);

/**
 * @version    SVN: <svn_id>
 * @package    Com_Jticketing
 * @copyright  Copyright (C) 2005 - 2017. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * Shika is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */
defined( '_JEXEC' ) or die( ';)' );

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Component\ComponentHelper;
Use Joomla\String\StringHelper;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Table\Table;
use Joomla\Filesystem\File;
use Joomla\Database\DatabaseInterface;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Filter\OutputFilter;

/**
 * Script file of JTicketing component
 *
 * @since  1.0.0
 **/
class com_jticketingInstallerScript
{
	/** @var float The version number */
	private $version = 0.0;

	/** @var array The list of extra modules and plugins to install */
	private $uninstallation_queue = array(
		// @modules => { (folder) => { (module) => { (position), (published) } }* }*
		'modules' => array(
			'admin' => array(),
			'site' => array(
				'mod_jticketing_buy'       => 0,
				'mod_jticketing_event'     => 0,
				'mod_jticketing_menu'      => 0,
				'mod_jticketing_calendar'  => 0,
				'mod_jticketing_recommend' => 0
			)
		),

		// @plugins => { (folder) => { (element) => (published) }* }*
		'plugins' => array(
			'actionlog' => array(
				'jticketing' => 1
			),
			'api' => array(
				'jticket' => 1,
				'jlike'   => 1
			),
			'community' => array(
				'addfields' => 0
			),
			'content' => array(
				'jlike_events' => 1
			),
			'jevents' => array(
				'addfields' => 0
			),
			'jticketingtax' => array(
				'jticketing_tax_default' => 0
			),
			'privacy' => array(
				'jticketing' => 1
			),
			'search' => array(
				'jtevents' => 1
			),
			'system' => array(
				'jticketing_j3'        => 1,
				'plug_sys_jticketing'  => 1,
				'tjassetsloader'       => 1,
				'jticketingactivities' => 1,
				'tjupdates'            => 1
			),
			'tjevents' => array(
				'plug_tjevents_adobeconnect' => 1,
				'zoom' => 1,
				'jaas' => 1,
				'googlemeet' => 1,
			),
			'tjlmsdashboard' => array(
				'eventlist' => 1,
			),
			'tjsms' => array(
				'smshorizon' => 0,
				'clickatell' => 1
			),
			'tjreports' => array(
				'attendeereport'      => 1,
				'customerreport'      => 1,
				'eventcategoryreport' => 1,
				'eventreport'         => 1,
				'salesreport'         => 1
			),
			'jticketing' => array(
				'rsform' => 0
			),
			'finder' => array(
				'jticketingevents' => 0
			),
		),

		'applications' => array(
			'easysocial' => array(
				'jticketMyEvents'       => 0,
				'jticket_boughttickets' => 0,
				'jticketingtickettypes' => 1
			)
		),

		'dynamics' => array(
			'jticketing' => 1
		)
	);

	/**
	 * method to install the component
	 *
	 * @param   mixed  $parent  The parent object
	 *
	 * @return void|boolean  in case of success return void incase of failure return false
	 */
	public function install($parent): void
	{
		// $parent is the class calling this method
		$this->installSqlFiles($parent);

		$this->setjticketingdefaultBavior();

		// add acymailing integration
		$this->addAcyMalingIntegration();
	}

	/**
	 * method to uninstall the component
	 *
	 * @param   mixed  $parent  The parent object
	 *
	 * @return void|boolean  in case of success return void incase of failure return false
	 */
	public function uninstall($parent): void
	{
		// Uninstall subextensions
		$status = $this->_uninstallSubextensions($parent);
	}

	/**
	 * method to setjticketingdefaultBavior
	 *
	 * @return void|boolean  in case of success return void incase of failure return false
	 */
	public function setjticketingdefaultBavior()
	{
		$user = Factory::getUser();
		$db   = Factory::getContainer()->get(DatabaseInterface::class);

		// Check if tag exists
		$sql = $db->getQuery(true)->select($db->quoteName('type_id'))
			->from($db->quoteName('#__content_types'))
			->where($db->quoteName('type_title') . ' = ' . $db->quote('Event'))
			->where($db->quoteName('type_alias') . ' = ' . $db->quote('com_jticketing.event'));
		$db->setQuery($sql);
		$type_id = $db->loadColumn()[0] ?? null;

		// Create tag
		$db                                 = Factory::getContainer()->get(DatabaseInterface::class);
		$tagobject                          = new stdclass;
		$tagobject->type_id                 = '';
		$tagobject->type_title              = 'Event';
		$tagobject->type_alias              = 'com_jticketing.event';
		$tagobject->table                   = '{"special":{"dbtable":"#__jticketing_events","key":"id","type":"Jticketingevent",'
		. '"prefix":"JTable","config":"array()"},'
		. '"common":{"dbtable":"#__ucm_content","key":"ucm_id","type":"Corecontent","prefix":"JTable","config":"array()"}}';
		$tagobject->rules                   = '';

		$field_mappings_arr = [
		'common' => [
					"core_content_item_id" => "id",
					"core_title" => "title",
					"core_state" => "state",
					"core_alias" => "alias",
					"core_created_time" => "created",
					"core_modified_time" => "modified",
					"core_body_short" => "short_description",
					"core_body_long" => "long_description",
					"core_hits" => "null",
					"core_publish_up" => "null",
					"core_publish_down" => "enddate",
					"core_access" => "access",
					"core_params" => "jt_params",
					"core_featured" => "featured",
					"core_metadata" => "null",
					"core_language" => "null",
					"core_images" => "image",
					"core_urls" => "null",
					"core_version" => "null",
					"core_ordering" => "ordering",
					"core_metakey" => "null",
					"core_metadesc" => "null",
					"core_catid" => "catid",
					"core_xreference" => "null",
					"asset_id" => "null"
				],
		'special' => [
					"parent_id" => "parent_id",
					"lft" => "lft",
					"rgt" => "rgt",
					"level" => "level",
					"path" => "path",
					"path" => "path",
					"extension" => "extension",
					"extension" => "extension",
					"note" => "note"
					]
		];
		$tagobject->field_mappings          = json_encode($field_mappings_arr);

		$tagobject->router                  = 'JTRouteHelper::getEventRoute';

		$content_history_options_arr = [
		'formFile' => "administrator\/components\/com_jticketing\/models\/forms\/event.xml",
		'hideFields' => '["asset_id","checked_out","checked_out_time"],"ignoreChanges":["checked_out", "checked_out_time"]',
		'convertToInt' => '["ordering"]',
		'displayLookup' => '[{"sourceColumn":"cat_id",
		"targetTable":"#__categories","targetColumn":"id","displayColumn":"title"},
		{"sourceColumn":"created_by","targetTable":"#__users","targetColumn":"id","displayColumn":"name"}]'
		];

		$tagobject->content_history_options = json_encode($content_history_options_arr);

		if (!$type_id)
		{
			if (!$db->insertObject('#__content_types', $tagobject, 'type_id'))
			{
				$app = Factory::getApplication();
				$app->enqueueMessage($db->getErrorMsg(), 'error');

				return false;
			}
		}
		else
		{
			$tagobject->type_id = $type_id;

			if (!$db->updateObject('#__content_types', $tagobject, 'type_id'))
			{
				$app = Factory::getApplication();
				$app->enqueueMessage($db->getErrorMsg(), 'error');

				return false;
			}
		}

		// Check if content type exists
		$query = $db->getQuery(true);
		$query->select($db->quoteName('type_id'));
		$query->from($db->quoteName('#__content_types'));
		$query->where($db->quoteName('type_alias') . ' = ' . $db->quote('com_jticketing.category'));
		$db->setQuery($query);
		$typeId = $db->loadColumn()[0] ?? null;

		if (!$typeId)
		{
			$contentType = new stdClass;
			$contentType->type_title = 'Event Category';
			$contentType->type_alias = 'com_jticketing.category';
			$contentType->table = '{"special":{"dbtable":"#__categories","key":"id","type":"Category","prefix":"JTable","config":"array()"},'
				. '"common":{"dbtable":"#__ucm_content","key":"ucm_id","type":"Corecontent","prefix":"JTable","config":"array()"}}';
			$contentType->rules = '';
			$contentType->field_mappings = '
				{"common":{
				"core_content_item_id":"id",
				"core_title":"title",
				"core_state":"published",
				"core_alias":"alias",
				"core_created_time":"created_time",
				"core_modified_time":"modified_time",
				"core_body":"description",
				"core_hits":"hits",
				"core_publish_up":"null",
				"core_publish_down":"null",
				"core_access":"access",
				"core_params":"params", "core_featured":"null",
				"core_metadata":"metadata", "core_language":"language",
				"core_images":"null", "core_urls":"null", "core_version":"version", "core_ordering":"null", "core_metakey":"metakey",
				"core_metadesc":"metadesc", "core_catid":"parent_id",
				"core_xreference":"null", "asset_id":"asset_id"},
				"special": {
				"parent_id":"parent_id",
				"lft":"lft",
				"rgt":"rgt",
				"level":"level",
				"path":"path",
				"extension":"extension",
				"note":"note"
				}
				}';
			$contentType->content_history_options = '{"formFile":"administrator\/components\/com_categories\/models\/forms\/category.xml",
				"hideFields":["asset_id","checked_out","checked_out_time","version","lft","rgt","level","path","extension"],

				"ignoreChanges":["modified_user_id", "modified_time", "checked_out", "checked_out_time", "version", "hits", "path"],

				"convertToInt":["publish_up", "publish_down"],
				"displayLookup":[{"sourceColumn":"created_user_id","targetTable":"#__users","targetColumn":"id",
				"displayColumn":"name"},
				{"sourceColumn":"access","targetTable":"#__viewlevels","targetColumn":"id","displayColumn":"title"},
				{"sourceColumn":"modified_user_id","targetTable":"#__users","targetColumn":"id","displayColumn":"name"},
				{"sourceColumn":"parent_id","targetTable":"#__categories","targetColumn":"id","displayColumn":"title"}]}';

			if (!$db->insertObject('#__content_types', $contentType, 'type_id'))
			{
				$app = Factory::getApplication();
				$app->enqueueMessage($db->getErrorMsg(), 'error');
				return false;
			}
		}

		//  Tags for venue category field
		$query = $db->getQuery(true);
		$query->select($db->quoteName('type_id'));
		$query->from($db->quoteName('#__content_types'));
		$query->where($db->quoteName('type_alias') . ' = ' . $db->quote('com_jticketing.venues.category'));
		$db->setQuery($query);
		$typeId = $db->loadColumn()[0] ?? null;

		if (!$typeId)
		{
			$contentType = new stdClass;
			$contentType->type_title = 'Venue Category';
			$contentType->type_alias = 'com_jticketing.venues.category';
			$contentType->table = '
				{"special":{"dbtable":"#__categories","key":"id","type":"CategoryTable",
					"prefix":"Joomla\\Component\\Categories\\Administrator\\Table\\","config":"array()"},
					"common":{"dbtable":"#__ucm_content","key":"ucm_id","type":"Corecontent",
					"prefix":"Joomla\\CMS\\Table\\","config":"array()"}}';
			$contentType->rules = '';
			$contentType->field_mappings = '
				{"common":{"core_content_item_id":"id","core_title":"title","core_state":"published","core_alias":"alias",
					"core_created_time":"created_time","core_modified_time":"modified_time","core_body":"description", 
					"core_hits":"hits","core_publish_up":"null","core_publish_down":"null","core_access":"access", "core_params":"params", 
					"core_featured":"null", "core_metadata":"metadata", "core_language":"language", "core_images":"null", 
					"core_urls":"null", "core_version":"version", "core_ordering":"null", "core_metakey":"metakey", 
					"core_metadesc":"metadesc", "core_catid":"parent_id", "asset_id":"asset_id"}, 
					"special":{"parent_id":"parent_id","lft":"lft","rgt":"rgt","level":"level","path":"path","extension":"extension","note":"note"}}';
			$contentType->content_history_options = '{"formFile":"administrator\/components\/com_categories\/forms\/category.xml", 
				"hideFields":["checked_out","checked_out_time","version","lft","rgt","level","path","extension"], 
				"ignoreChanges":["modified_user_id", "modified_time", "checked_out", "checked_out_time", "version", "hits", "path"], 
				"convertToInt":["publish_up", "publish_down"], 
				"displayLookup":[{"sourceColumn":"created_user_id",
				"targetTable":"#__users","targetColumn":"id","displayColumn":"name"}, 
				{"sourceColumn":"access","targetTable":"#__viewlevels","targetColumn":"id","displayColumn":"title"},
				{"sourceColumn":"modified_user_id","targetTable":"#__users","targetColumn":"id","displayColumn":"name"},
				{"sourceColumn":"parent_id","targetTable":"#__categories","targetColumn":"id","displayColumn":"title"}]}';

			if (!$db->insertObject('#__content_types', $contentType, 'type_id'))
			{
				$app = Factory::getApplication();
				$app->enqueueMessage($db->getErrorMsg(), 'error');
				return false;
			}
		}

		// Create default category on installation if not exists
		$sql = $db->getQuery(true)->select($db->quoteName('id'))
			->from($db->quoteName('#__categories'))
			->where($db->quoteName('extension') . ' = ' . $db->quote('com_jticketing'));

		$db->setQuery($sql);
		$cat_id = $db->loadColumn()[0] ?? null;

		if (empty($cat_id))
		{
			$catobj        = new stdClass;
			$catobj->title = 'Uncategorised';
			$catobj->alias = 'uncategorised';

			$catobj->extension = "com_jticketing";
			$catobj->path      = "uncategorised";
			$catobj->parent_id = 1;
			$catobj->level     = 1;
			$catobj->created_user_id = $user->id;
			$catobj->language        = "*";
			$catobj->description     = '<p>This is a default Event category</p>';
			$createdDateTime = Factory::getDate('now');
			$catobj->created_time = $createdDateTime->toSQL();
			$catobj->modified_time = $createdDateTime->toSQL();
			$catobj->published = 1;
			$catobj->access    = 1;

			if (!$db->insertObject('#__categories', $catobj, 'id'))
			{
				$app = Factory::getApplication();
				$app->enqueueMessage($db->getErrorMsg(), 'error');

				return false;
			}
		}
	}

	public function row2text($row,$dvars=[])
	{
		reset($dvars);
		foreach ($dvars as $idx => $var)
		{
			unset($row[$var]);
		}

		$text='';
		reset($row);
		$flag=0;
		$i=0;

		foreach ($row as $var => $val)
		{
			if($flag==1)
			$text.=",\n";
			elseif($flag==2)
			$text.=",\n";
			$flag=1;

			if(is_numeric($var))
			if($var[0]=='0')
			$text.="'$var'=>";
			else
			{
				if($var!==$i)
				$text.="$var=>";
				$i=$var;
			}
			else
			$text.="'$var'=>";
			$i++;

			if(is_array($val))
			{
				$text.="array(".$this->row2text($val,$dvars).")";
				$flag=2;
			}
			else
			$text.="\"".addslashes($val)."\"";
		}

		return($text);
	}

	/**
	 * method to update the component
	 *
	 * @param   mixed  $parent  The parent object
	 *
	 * @return void
	 */
	public function update($parent): void
	{
		// Install SQL FIles
		$this->installSqlFiles($parent);
		$this->fix_db_on_update($parent);

		// To remove short description
		$this->removeShortDescription($parent);

		// To add event alias
		$this->addEventAlias($parent);

		// To add venue alias
		$this->addVenueAlias($parent);

		$this->deleteCategoryReport();
		$this->setjticketingdefaultBavior();

		// add acymailing integration
		$this->addAcyMalingIntegration();
	}

	/**
	 * Delete category report
	 *
	 * @return Boolean
	 *
	 * @since 2.1
	 */
	public function deleteCategoryReport()
	{
		// Delete the category report entry from report table.

		$db = Factory::getContainer()->get(DatabaseInterface::class);
		
		// Check if tj_reports table exists before trying to use it
		$tableName = $db->replacePrefix('#__tj_reports');
		$query = "SHOW TABLES LIKE " . $db->quote($tableName);
		$db->setQuery($query);
		$tableExists = $db->loadResult();
		
		if (!$tableExists)
		{
			// Table doesn't exist, skip deletion
			return true;
		}
		
		$selectReport = $db->getQuery(true);

		$conditions = [
		$db->quoteName('client') . ' = ' . $db->quote('com_jticketing'),
		$db->quoteName('plugin') . ' = ' . $db->quote('categoryreport')
		];

		$selectReport->select('*');
		$selectReport->from($db->quoteName('#__tj_reports'));
		$selectReport->where($conditions);
		$db->setQuery($selectReport);
		$confrim = $db->loadObjectList();

		if (!empty($confrim))
		{
			$deleteReport = $db->getQuery(true);
			$deleteReport->delete($db->quoteName('#__tj_reports'));
			$deleteReport->where($conditions);
			$db->setQuery($deleteReport);
			$result = $db->execute();
		}
		
		return true;
	}

	/**
	 * method to run before an install/update/uninstall method
	 *
	 * @param   string  $type    The action being performed
	 * @param   mixed   $parent  The parent object
	 *
	 * @return void
	 */
	public function preflight(string $type, $parent): void
	{
	}

	/**
	 * method to run after an install/update/uninstall method
	 *
	 * @param   string  $type    The action being performed
	 * @param   mixed   $parent  The parent object
	 *
	 * @return void
	 */
	public function postflight(string $type, $parent): void
	{
		// Delete existing log file for security purpose
		$this->deleteLog();

		// Add sample field manager data.
		$fieldsManagerDataStatus = $this->_installSampleFieldsManagerData();

		if ($fieldsManagerDataStatus)
		{
			echo '<br/><strong style="color:green">' . Text::_('COM_JTICKETING_FIELDS_SAMPLE_DATA_INSTALLED') . '</strong>';
		}

		// Add core fields manager .
		$fieldsManagerDataStatus = $this->_installSampleCoreFieldsJTicketing();

		if ($fieldsManagerDataStatus)
		{
			echo '<br/><strong style="color:green">' . Text::_('COM_JTICKETING_FIELDS_SAMPLE_CORE_FIELDS_INSTALLED') . '</strong>';
		}

		// Add Categories for native events
		$basicCategories = $this->_installbasicCategories();

		if ($basicCategories)
		{
			echo '<br/><strong style="color:green">' . Text::_('COM_JTICKETING_FIELDS_SAMPLE_CATEGORY_INSTALLED') . '</strong>';
		}

		// Add Categories for native events
		$basicCategoriesforVenues = $this->_installbasicCategoriesforVenues();

		if ($basicCategoriesforVenues)
		{
			echo '<br/><strong style="color:green">' . Text::_('COM_JTICKETING_FIELDS_SAMPLE_CATEGORY_INSTALLED') . '</strong>';
		}

		// Add default permissions
		$this->deFaultPermissionsFix();

		// Write template file for email and pdf template
		$this->_writeTemplate();

		if ($type=='install')
		{
			echo '<p><strong style="color:green">' . Text::_('COM_JTICKETING_' . $type . '_TEXT') . '</p></strong>';
		}
		else
		{
			echo '<p><strong style="color:green">' . Text::_('COM_JTICKETING_' . $type . '_TEXT') . '</p></strong>';
		}

		// Set default layouts to load
		$this->setDefaultLayout($type);

		// Add All Events menu in the main menu
		$menuMsg = $this->_addMenuItems();

		if ($menuMsg)
		{
			echo '<br/><strong style="color:green">' . Text::_('COM_JTICKETING_ALL_EVENTS_MENU_CREATED') . '</strong>';
		}

		// Add my events menu in the main menu
		$myEventsMenuMsg = $this->_addMyEventsMenu();

		if ($myEventsMenuMsg)
		{
			echo '<br/><strong style="color:green">' . Text::_('COM_JTICKETING_MY_EVENTS_MENU_CREATED') . '</strong>';
		}
		
		// Need to verify We have written that code removes duplicate menu entry from table
		$this->menuItemMigration();

	}

	/**
	 * method to install default email templates
	 *
	 * @return void
	 */
	private function _writeTemplate()
	{
		// Write default config template for PDF(Which is attached to Ticket email)
		$this->_writeTicketTemplate();

		// Insert the required data for notifications template
		$this->_installNotificationsTemplates();

		// Insert Certificate templates
		$this->installCertificateTemplates();
	}

	/**
	 * method to install default ticket templates
	 *
	 * @return void
	 */
	private function _writeTicketTemplate()
	{
		// Code for email template
		$filename = JPATH_ADMINISTRATOR . '/components/com_jticketing/config.php';
		$filename_default = JPATH_ADMINISTRATOR . '/components/com_jticketing/config_default.php';

		// If config file does not exists
		if (!is_file($filename))
		{
			File::move($filename_default,$filename);
			include($filename);

			$emails_config_new = [];
			$emails_config_new['message_body'] = $emails_config["message_body"];
			$emails_config_file_contents = "<?php \n\n";
			$emails_config_file_contents .= "\$emails_config=array(\n" . $this->row2text($emails_config_new) . "\n);\n";
			$emails_config_file_contents .= "\n?>";

			File::delete($filename);
			File::write($filename, $emails_config_file_contents);
		}
		elseif (is_file($filename_default))
		{
			// If config file exists
			File::delete($filename_default);
		}
	}

	/**
	 * TODO - Check its use and remove the function
	 * Add default ACL permissions if already set by administrator
	 *
	 * @return  void
	 */
	public function deFaultPermissionsFix()
	{
		$db = Factory::getContainer()->get(DatabaseInterface::class);
		$query = "SELECT id, rules FROM `#__assets` WHERE `name` = 'com_jticketing' ";
		$db->setQuery($query);
		$result = $db->loadobject();

		if (strlen(trim($result->rules))<=3)
		{
			$obj = new Stdclass();
			$obj->id = $result->id;
			$obj->rules = '{"core.admin":[],"core.manage":[],"core.create":{"2":1},"core.delete":{"2":1},"core.edit":{"2":1},"core.edit.state":{"2":1},"core.edit.own":{"2":1},"core.enrollown":{"7":0}, "core.enrollall":{"7":0},"core.enroll":{"7":0},"coupon.create":{"2":1},"coupon.view":{"2":1},"coupon.edit.own":{"2":1},"coupon.edit":{"2":1},"coupon.edit.state":{"2":1},"coupon.delete":{"2":1}}';

			if (!$db->updateObject('#__assets', $obj, 'id'))
			{
				$app = Factory::getApplication();
				$app->enqueueMessage($db->stderr(), 'error');
			}
		}
	}

	/**
	 * installSqlFiles
	 *
	 * @param   JInstaller  $parent  parent
	 *
	 * @return  void
	 */
	public function installSqlFiles($parent)
	{
		$db = Factory::getContainer()->get(DatabaseInterface::class);

		// Obviously you may have to change the path and name if your installation SQL file
		if (method_exists($parent, 'extension_root'))
		{
			$sqlfile = $parent->getPath('extension_root') . '/admin/sql/install.sql';
		}
		elseif (method_exists($parent, 'getParent') && method_exists($parent->getParent(), 'getPath'))
		{
			$sqlfile = $parent->getParent()->getPath('source') . '/admin/sql/install.sql';
		}
		else
		{
			$sqlfile = $parent->getParent()->getPath('extension_root') . '/admin/sql/install.sql';
		}

		// Don't modify below this line
		if (!file_exists($sqlfile))
		{
			// Try alternative path
			if (method_exists($parent, 'getParent') && method_exists($parent->getParent(), 'getPath'))
			{
				$sqlfile = $parent->getParent()->getPath('source') . '/admin/sql/install.sql';
			}
		}
		
		$buffer = file_get_contents($sqlfile);

		if ($buffer !== false)
		{

			$queries = $db->splitSql($buffer);

			if (count($queries) != 0)
			{
				foreach ($queries as $query)
				{
					$query = trim($query);

					if ($query != '' && $query[0] != '#')
					{
						$db->setQuery($query);

						if (!$db->execute())
						{
							$this->setMessage(Text::sprintf('JLIB_INSTALLER_ERROR_SQL_ERROR', $db->stderr(true)), 'error');

							return false;
						}
					}
				}
			}
		}

		$config = Factory::getConfig();
		$configdb = $config->get('db');

		// Get dbprefix
		$dbprefix = $config->get('dbprefix');
	}

	/**
	 * install core attendee fields in JTicketing
	 *
	 * @param   JInstaller  $parent  parent
	 *
	 * @return  void
	 */
	private function _installSampleCoreFieldsJTicketing()
	{
		// Check if file is present.
		$db   = Factory::getContainer()->get(DatabaseInterface::class);

		// Check if any core ateende fields  exists.
		$query = $db->getQuery(true);
		$query->select('COUNT(aflds.id) AS count');
		$query->from('`#__jticketing_attendee_fields` AS aflds');
		$query->where('aflds.core=1');
		$db->setQuery($query);
		$attendee_fields = $db->loadColumn()[0] ?? 0;

		if (empty($attendee_fields))
		{
			// If no core ateende fields, add a sample one.First name,Last name is required
			$queries[] = "INSERT INTO `#__jticketing_attendee_fields` (`eventid`, `placeholder`, `type`, `label`, `name`,`core`,`state`,`required`, `options`, `default_selected_option`) VALUES(0, 'COM_JTICKETING_AF_FNAME','text','COM_JTICKETING_AF_FNAME','first_name',1,1,1, '', '');";
			$queries[] = "INSERT INTO `#__jticketing_attendee_fields` (`eventid`, `placeholder`, `type`, `label`, `name`,`core`,`state`,`required`, `options`, `default_selected_option`) VALUES(0, 'COM_JTICKETING_AF_LNAME','text','COM_JTICKETING_AF_LNAME','last_name',1,1,1, '', '');";
			$queries[] = "INSERT INTO `#__jticketing_attendee_fields` (`eventid`, `placeholder`, `type`, `label`, `name`,`core`,`state`,`required`, `options`, `default_selected_option`) VALUES(0, 'COM_JTICKETING_AF_PHONE','text','COM_JTICKETING_AF_PHONE','phone',1,1,1, '', '');";
			$queries[] = "INSERT INTO `#__jticketing_attendee_fields` (`eventid`, `placeholder`, `type`, `label`, `name`,`core`,`state`,`required`, `options`, `default_selected_option`) VALUES(0, 'COM_JTICKETING_AF_EMAIL','text','COM_JTICKETING_AF_EMAIL','email',1,1,1, '', '');";

			// Execute sql queries.
			if (count($queries) != 0)
			{
				foreach ($queries as $query)
				{
					$query = trim($query);

					if ($query != '')
					{
						$db->setQuery($query);

						if (!$db->execute())
						{
							$this->setMessage(Text::sprintf('JLIB_INSTALLER_ERROR_SQL_ERROR', $db->stderr(true)), 'error');

							return false;
						}
					}
				}
			}
		}

		return true;
	}

	/**
	 * install event categories in JTicketing
	 *
	 * @param   JInstaller  $parent  parent
	 *
	 * @return  void
	 */
	private function _installbasicCategories()
	{
		$db   = Factory::getContainer()->get(DatabaseInterface::class);
		$user = Factory::getUser();

		// Check if any categories present for jticketing.
		$query = $db->getQuery(true);
		$query->select('COUNT(cat.id) AS count');
		$query->from('`#__categories` AS cat');
		$search = $db->quote('com_jticketing');
		$query->where('cat.extension=' . $search);
		$db->setQuery($query);
		$CategoryCount = $db->loadColumn()[0] ?? 0;

		// If no category found, add a sample one.
		if (!$CategoryCount)
		{
				$catobj=new stdClass;
				$catobj->title = 'General';
				$catobj->alias = 'General';;
				$catobj->extension="com_jticketing";
				$catobj->path=" General";
				$catobj->parent_id=1;
				$catobj->level=1;

				$paramdata=array();
				$paramdata['category_layout']='';
				$paramdata['image']='';
				$catobj->params=json_encode($paramdata);

				$catobj->created_user_id=$user->id;
				$catobj->language="*";
				//$catobj->description = $category->description;
				$createdDateTime = Factory::getDate('now');
				$catobj->created_time = $createdDateTime->toSQL();
				$catobj->modified_time = $createdDateTime->toSQL();
				$catobj->published = 1;
				$catobj->access = 1;
				if(!$db->insertObject('#__categories',$catobj,'id'))
				{
					$app = Factory::getApplication();
					$app->enqueueMessage($db->getErrorMsg(), 'error');
					return false;
				}
			return true;
		}
	}

	/**
	 * install venue categories in JTicketing
	 *
	 * @param   JInstaller  $parent  parent
	 *
	 * @return  void
	 */
	private function _installbasicCategoriesforVenues()
	{
		$db   = Factory::getContainer()->get(DatabaseInterface::class);
		$user = Factory::getUser();


		// Check if any categories present for jticketing.
		$query = $db->getQuery(true);
		$query->select('COUNT(cat.id) AS count');
		$query->from('`#__categories` AS cat');
		$search = $db->quote('com_jticketing.venues');
		$query->where('cat.extension=' . $search);
		$db->setQuery($query);
		$CategoryCount = $db->loadColumn()[0] ?? 0;

		// If no category found, add a sample one.
		if (!$CategoryCount)
		{
				$catobj=new stdClass;
				$catobj->title = 'General';
				$catobj->alias = 'General';;
				$catobj->extension="com_jticketing.venues";
				$catobj->path=" General";
				$catobj->parent_id=1;
				$catobj->level=1;

				$paramdata=array();
				$paramdata['category_layout']='';
				$paramdata['image']='';
				$catobj->params=json_encode($paramdata);

				$catobj->created_user_id=$user->id;
				$catobj->language="*";
				//$catobj->description = $category->description;
				$createdDateTime = Factory::getDate('now');
				$catobj->created_time = $createdDateTime->toSQL();

				// Need to add date as in com_content to install
				$catobj->modified_time = $createdDateTime->toSQL();
				$catobj->published = 1;
				$catobj->access = 1;
				if(!$db->insertObject('#__categories',$catobj,'id'))
				{
					$app = Factory::getApplication();
					$app->enqueueMessage($db->getErrorMsg(), 'error');
					return false;
				}
			return true;
		}
	}

	/**
	 * install event categories in JTicketing
	 *
	 * @param   JInstaller  $parent  parent
	 *
	 * @return  void
	 */
	private function _installSampleFieldsManagerData()
	{
		// Check if file is present.
		$filePath = JPATH_SITE . '/components/com_tjfields/tjfields.php';

		if (!is_file($filePath))
		{
			return false;
		}

		$db   = Factory::getContainer()->get(DatabaseInterface::class);
		$user = Factory::getUser();

		// Check if any eventform fields groups exists.
		$query = $db->getQuery(true);
		$query->select('COUNT(tfg.id) AS count');
		$query->from('`#__tjfields_groups` AS tfg');
		$search = $db->quote('com_jticketing.event');
		$query->where('tfg.client=' . $search);
		$db->setQuery($query);
		$eventFieldsGroupsCount = $db->loadColumn()[0] ?? 0;

		// Check if any ticket fields groups exists.
		$query = $db->getQuery(true);
		$query->select('COUNT(tfg.id) AS count');
		$query->from('`#__tjfields_groups` AS tfg');
		$search = $db->quote('com_jticketing.ticket');
		$query->where('tfg.client=' . $search);
		$db->setQuery($query);
		$ticketFieldsGroupsCount = $db->loadColumn()[0] ?? 0;

		$queries = [];

		// If no eventform fields groups found, add a sample one.
		if (!$eventFieldsGroupsCount)
		{
			$queries[] = "INSERT INTO `#__tjfields_groups` (`ordering`, `state`, `created_by`, `name`, `title`, `client`) VALUES(1, 1, ".$user->id.", 'Event - Additional Details', 'Event Fields', 'com_jticketing.event');";
		}

		// If no ticketform fields groups found, add a sample one.
		if (!$ticketFieldsGroupsCount)
		{
			$queries[] = "INSERT INTO `#__tjfields_groups` (`ordering`, `state`, `created_by`, `name`, `title`, `client`) VALUES(2, 1,".$user->id.", 'Ticket - Additional Details', 'Ticket Fields', 'com_jticketing.ticket');";
		}

		// Execute sql queries.
		if (count($queries) != 0)
		{
			foreach ($queries as $query)
			{
				$query = trim($query);

				if ($query != '')
				{
					$db->setQuery($query);

					if (!$db->execute())
					{
						$this->setMessage(Text::sprintf('JLIB_INSTALLER_ERROR_SQL_ERROR', $db->stderr(true)), 'error');

						return false;
					}
				}
			}
		}

		return true;
	}

	//since version 1.5
	//check if column - paypal_email exists
	function fixIntegrationXrefTable($db,$dbprefix,$config)
	{
		// since version 1.8
		// check if column - paypal_email exists
		$query="SHOW COLUMNS FROM #__jticketing_integration_xref WHERE `Field` = 'cron_status'";
		$db->setQuery($query);
		$check=$db->loadResult();
		if(!$check)
		{
			$query="ALTER TABLE  `#__jticketing_integration_xref` ADD  `cron_status` INT( 11 ) NOT NULL DEFAULT 0 AFTER  `userid`";
			$db->setQuery($query);
			if ( !$db->execute() ) {
				$app = Factory::getApplication();
				$app->enqueueMessage($db->stderr(), 'error');
			}
		}

		//since version 1.8
		//check if column - paypal_email exists
		$query="SHOW COLUMNS FROM #__jticketing_integration_xref WHERE `Field` = 'cron_date'";
		$db->setQuery($query);
		$check=$db->loadResult();
		if(!$check)
		{
			$query="ALTER TABLE  `#__jticketing_integration_xref` ADD  `cron_date` datetime DEFAULT NULL AFTER  `cron_status`";
			$db->setQuery($query);
			if ( !$db->execute() ) {
				$app = Factory::getApplication();
				$app->enqueueMessage($db->stderr(), 'error');
			}
		}

		//since version 2.0
		//check if column - vendor_id exists
		$query="SHOW COLUMNS FROM #__jticketing_integration_xref WHERE `Field` = 'vendor_id'";
		$db->setQuery($query);
		$check=$db->loadResult();
		if(!$check)
		{
			$query="ALTER TABLE  `#__jticketing_integration_xref` ADD  `vendor_id` INTEGER NOT NULL DEFAULT 0 AFTER  `eventid`";
			$db->setQuery($query);
			if ( !$db->execute() ) {
				$app = Factory::getApplication();
				$app->enqueueMessage($db->stderr(), 'error');
			}
		}
	}


		//since version 1.5
		//check if column - attendee_id exists
	function fixEventsTable($db, $dbprefix, $config)
	{
		$query="SHOW COLUMNS FROM #__jticketing_events WHERE `Field` = 'access'";
		$db->setQuery($query);
		$check=$db->loadResult();

		if(!$check)
		{
			$query=" ALTER TABLE  `#__jticketing_events` ADD  `access` int(11) NOT NULL DEFAULT 0 AFTER  `state`";
			$db->setQuery($query);

			if ( !$db->execute() ) {
				$app = Factory::getApplication();
				$app->enqueueMessage($db->stderr(), 'error');
			}

			// Set each ticket type as public
			/*$query=" UPDATE   `#__jticketing_events` SET  `access`=1";
			$db->setQuery($query);
			$db->execute();*/

		}

		//since version 1.5
		//check if column - attendee_id exists
		$query="SHOW COLUMNS FROM #__jticketing_events WHERE `Field` = 'asset_id'";
		$db->setQuery($query);
		$check=$db->loadResult();

		if(!$check)
		{
			$query=" ALTER TABLE  `#__jticketing_events` ADD  `asset_id` int(11) NOT NULL DEFAULT 0 AFTER `id`";
			$db->setQuery($query);

			if ( !$db->execute() ) {
				$app = Factory::getApplication();
				$app->enqueueMessage($db->stderr(), 'error');
			}
		}

		//since version 2.0
		//check if column - attendee_id exists
		$query="SHOW COLUMNS FROM #__jticketing_events WHERE `Field` = 'allow_view_attendee'";
		$db->setQuery($query);
		$check=$db->loadResult();

		if(!$check)
		{
			$query=" ALTER TABLE  `#__jticketing_events` ADD  `allow_view_attendee` tinyint(3) NOT NULL DEFAULT 0 AFTER `state`";
			$db->setQuery($query);

			if ( !$db->execute() ) {
				$app = Factory::getApplication();
				$app->enqueueMessage($db->stderr(), 'error');
			}
		}

		//since version 1.8
		//check if column - venue exists
		$query="SHOW COLUMNS FROM #__jticketing_events WHERE `Field` = 'venue'";
		$db->setQuery($query);
		$check=$db->loadResult();
		if(!$check)
		{
			$query="ALTER TABLE  `#__jticketing_events` ADD  `venue` INT(11) NOT NULL DEFAULT 0 AFTER  `catid`";
			$db->setQuery($query);
			if ( !$db->execute() ) {
				$app = Factory::getApplication();
				$app->enqueueMessage($db->stderr(), 'error');
			}
		}

		// Since version 1.8
		$query="SHOW COLUMNS FROM #__jticketing_events WHERE `Field` = 'online_events'";
		$db->setQuery($query);
		$check=$db->loadResult();
		if(!$check)
		{
			$query="ALTER TABLE  `#__jticketing_events` ADD  `online_events` TINYINT(4) NOT NULL DEFAULT 0 AFTER  `featured`";
			$db->setQuery($query);
			if ( !$db->execute() ) {
				$app = Factory::getApplication();
				$app->enqueueMessage($db->stderr(), 'error');
			}
		}

		// Since version 1.8
		$query="SHOW COLUMNS FROM #__jticketing_events WHERE `Field` = 'jt_params'";
		$db->setQuery($query);
		$check=$db->loadResult();
		if(!$check)
		{
			$query="ALTER TABLE  `#__jticketing_events` ADD  `jt_params` TEXT DEFAULT NULL AFTER  `checked_out_time`";
			$db->setQuery($query);
			if ( !$db->execute() ) {
				$app = Factory::getApplication();
				$app->enqueueMessage($db->stderr(), 'error');
			}
		}
		else 
		{
			$query       = "ALTER TABLE `#__jticketing_events` CHANGE `jt_params` `jt_params` text DEFAULT NULL";
			$db->setQuery($query);
			if ( !$db->execute() ) {
				$app = Factory::getApplication();
				$app->enqueueMessage($db->stderr(), 'error');
			}
		}
	}

	//since version 2.0
	function fixCheckindetailsTable($db, $dbprefix, $config)
	{
		$query="SHOW COLUMNS FROM #__jticketing_checkindetails WHERE `Field` = 'id'";
		$db->setQuery($query);
		$check=$db->loadResult();
		if(!$check)
		{
			$query="ALTER TABLE  `#__jticketing_checkindetails` ADD  `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST ";
			$db->setQuery($query);
			if ( !$db->execute() ) {
				$app = Factory::getApplication();
				$app->enqueueMessage($db->stderr(), 'error');
			}
		}

		//since version 2.0
		$query="SHOW COLUMNS FROM #__jticketing_checkindetails WHERE `Field` = 'checkouttime'";
		$db->setQuery($query);
		$check=$db->loadResult();
		if(!$check)
		{
			$query="ALTER TABLE  `#__jticketing_checkindetails` ADD  `checkouttime` DATETIME DEFAULT NULL AFTER  `checkintime`";
			$db->setQuery($query);
			if ( !$db->execute() ) {
				$app = Factory::getApplication();
				$app->enqueueMessage($db->stderr(), 'error');
			}
		}
	}

	function fixJtUsersTable($db, $dbprefix, $config)
	{
		$query = "ALTER TABLE `#__jticketing_users` CHANGE `country_code` `country_code` VARCHAR( 250 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
		CHANGE `city` `city` VARCHAR( 250 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
		CHANGE `state_code` `state_code` VARCHAR( 250 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''";
		$db->setQuery($query);

		if (!$db->execute())
		{
			$app = Factory::getApplication();
				$app->enqueueMessage($db->stderr(), 'error');
		}

		//since version 1.7.2
		//check if column - attendee_id exists
		$query="SHOW COLUMNS FROM #__jticketing_users WHERE `Field` = 'country_mobile_code'";
		$db->setQuery($query);
		$check=$db->loadResult();

		if(!$check)
		{
			$query=" ALTER TABLE  `#__jticketing_users` ADD  `country_mobile_code` int(11) NOT NULL DEFAULT 0 AFTER  `zipcode`";
			$db->setQuery($query);
			if ( !$db->execute() ) {
				$app = Factory::getApplication();
				$app->enqueueMessage($db->stderr(), 'error');
			}
		}
	}

	//since version 1.8
	//check if column - venue exists
	function fixVenuesTable($db, $dbprefix, $config)
	{
		$query="SHOW COLUMNS FROM #__jticketing_venues WHERE `Field` = 'address'";
		$db->setQuery($query);
		$check=$db->loadResult();
		if(!$check)
		{
			$query="ALTER TABLE  `#__jticketing_venues` ADD  `address` VARCHAR(255) NOT NULL DEFAULT '' AFTER  `zipcode`";
			$db->setQuery($query);
			if ( !$db->execute() ) {
				$app = Factory::getApplication();
				$app->enqueueMessage($db->stderr(), 'error');
			}
		}

		// Check if column - venue alias exists
		$query = "SHOW COLUMNS FROM #__jticketing_venues WHERE `Field` = 'alias'";
		$db->setQuery($query);
		$check = $db->loadResult();

		if (!$check)
		{
			$query = "ALTER TABLE `#__jticketing_venues` ADD `alias` VARCHAR(255) NOT NULL DEFAULT '' AFTER `name`";
			$db->setQuery($query);
			if ( !$db->execute() ) {
				$app = Factory::getApplication();
				$app->enqueueMessage($db->stderr(), 'error');
			}
		}

		// Check if column - vendor_id exists
		$query = "SHOW COLUMNS FROM #__jticketing_venues WHERE `Field` = 'vendor_id'";
		$db->setQuery($query);
		$check = $db->loadResult();

		if (!$check)
		{
			$query = "ALTER TABLE `#__jticketing_venues` ADD `vendor_id` INTEGER(11) NOT NULL DEFAULT 0 AFTER `id`";
			$db->setQuery($query);
			if ( !$db->execute() ) {
				$app = Factory::getApplication();
				$app->enqueueMessage($db->stderr(), 'error');
			}
		}
	}

	/**
	 * Function to update JLike data
	 *
	 * @return  void
	 *
	 * @since   2.1.3
	 */
	function jLikeContentMigration()
	{
		$db = Factory::getContainer()->get(DatabaseInterface::class);
		
		// Check if jlike_content table exists before trying to use it
		$tableName = $db->replacePrefix('#__jlike_content');
		$query = "SHOW TABLES LIKE " . $db->quote($tableName);
		$db->setQuery($query);
		$tableExists = $db->loadResult();
		
		if (!$tableExists)
		{
			// Table doesn't exist, skip migration
			return;
		}
		
		$query = $db->getQuery(true);

		// Load JLike data
		$query->select('a.*');
		$query->from($db->quoteName('#__jlike_content', 'a'));
		$query->where($db->quoteName('a.element') . ' = ' . $db->quote('com_jticketing.event'));
		$db->setQuery($query);
		$results = $db->loadObjectList();

		foreach ($results as $result)
		{
			// Delete itemID from URL
			$this->updateURL($result);

			// Delete Duplicate entries of JLike
			$query1 = $db->getQuery(true);
			$query1->select('b.*');
			$query1->from($db->quoteName('#__jlike_content', 'b'));
			$query1->where($db->quoteName('b.element_id')." = ".$db->quote($result->element_id));
			$query1->where($db->quoteName('b.element')." = ".$db->quote($result->element));
			$db->setQuery($query1);
			$contents = $db->loadObjectList();

			if (count($contents) >= 2)
			{
				array_pop($contents);

				for ($i = 0; $i < count($contents); $i++)
				{
					$query3 = $db->getQuery(true);
					$conditions = array(
						$db->quoteName('id') . ' = ' . $db->quote($contents[$i]->id),
						$db->quoteName('element') . ' = ' . $db->quote('com_jticketing.event')
					);

					$query3->delete($db->quoteName('#__jlike_content'));
					$query3->where($conditions);

					$db->setQuery($query3);
					$result = $db->execute();
				}
			}
		}
	}

	/**
	 * Function to remove itemID from URL
	 *
	 * @return  void
	 *
	 * @since   2.1.3
	 */
	function updateURL($result)
	{
		if($result->url == '')
		{
			return false;
		}

		$urlExplode = explode('&', $result->url);

		if (sizeof($urlExplode) == 4)
		{
			array_pop($urlExplode);
		}

		$urlAfterItemID = implode('&', $urlExplode);
		$db = Factory::getContainer()->get(DatabaseInterface::class);
		
		// Check if jlike_content table exists before trying to update it
		$tableName = $db->replacePrefix('#__jlike_content');
		$checkQuery = "SHOW TABLES LIKE " . $db->quote($tableName);
		$db->setQuery($checkQuery);
		$tableExists = $db->loadResult();
		
		if (!$tableExists)
		{
			// Table doesn't exist, skip update
			return false;
		}
		
		$query = $db->getQuery(true);
		$fields = array(
			$db->quoteName('url') . ' = ' . $db->quote($urlAfterItemID),
		);

		$conditions = array(
			$db->quoteName('id') . ' = ' . $db->quote($result->id)
		);

		$query->update($db->quoteName('#__jlike_content'))->set($fields)->where($conditions);
		$db->setQuery($query);
		$result = $db->execute();

	}


	//Since jticketing version 1.5
	function fix_db_on_update($parent = null)
	{
		$db = Factory::getContainer()->get(DatabaseInterface::class);
		$config = Factory::getConfig();
		$dbprefix = $config->get( 'dbprefix' );

		// Try to get XML from installed location first, then from source
		$xmlPath = JPATH_ADMINISTRATOR . '/components/com_jticketing/jticketing.xml';
		
		// If not found in installed location and parent is available, try source path
		if (!file_exists($xmlPath) && $parent !== null)
		{
			if (method_exists($parent, 'getPath'))
			{
				$xmlPath = $parent->getPath('source') . '/jticketing.xml';
			}
			elseif (method_exists($parent, 'getParent') && method_exists($parent->getParent(), 'getPath'))
			{
				$xmlPath = $parent->getParent()->getPath('source') . '/jticketing.xml';
			}
		}

		$xml = @simplexml_load_file($xmlPath);
		
		if ($xml !== false && isset($xml->version))
		{
			$version = (string)$xml->version;
			$this->version = (float)($version);
		}
		else
		{
			// Fallback: try to get version from extensions table
			$query = $db->getQuery(true)
				->select($db->quoteName('manifest_cache'))
				->from($db->quoteName('#__extensions'))
				->where($db->quoteName('element') . ' = ' . $db->quote('com_jticketing'))
				->where($db->quoteName('type') . ' = ' . $db->quote('component'));
			$db->setQuery($query);
			$manifestCache = $db->loadResult();
			
			if ($manifestCache)
			{
				$manifest = json_decode($manifestCache, true);
				if (isset($manifest['version']))
				{
					$this->version = (float)$manifest['version'];
				}
			}
		}
		$this->fixIntegrationXrefTable($db, $dbprefix, $config);
		$this->jLikeContentMigration();
		$this->fixEventsTable($db, $dbprefix, $config);
		$this->fixCheckindetailsTable($db, $dbprefix, $config);
		$this->fixJtUsersTable($db, $dbprefix, $config);
		$this->fixVenuesTable($db, $dbprefix, $config);
		$this->updateCoreAttendeeFields();
	}


	/**
	 * Renders the post-installation message
	 */
	private function _renderPostInstallation($status, $parent, $msgBox=array())
	{
		$document = Factory::getDocument();

		?>

		<?php $rows = 1;?>

		<link rel="stylesheet" type="text/css" href=""/>
		<div class="techjoomla-bootstrap" >
		<table class="table-condensed table">
			<thead>
				<tr>
					<th class="title" colspan="2">Extension</th>
					<th width="30%">Status</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="3"></td>
				</tr>
			</tfoot>
			<tbody>
				<tr class="row0">
					<td class="key" colspan="2">JTicketing component</td>
					<td><strong style="color: green">Installed</strong></td>
				</tr>




				<?php if (count($status->modules)) : ?>
				<tr>
					<th>Module</th>
					<th>Client</th>
					<th></th>
				</tr>
				<?php foreach ($status->modules as $module) : ?>
				<tr class="row<?php echo ($rows++ % 2); ?>">
					<td class="key"><?php echo $module['name']; ?></td>
					<td class="key"><?php echo ucfirst($module['client']); ?></td>
					<td><strong style="color: <?php echo ($module['result'])? "green" : "red"?>"><?php echo ($module['result'])?'Installed':'Not installed'; ?></strong></td>
				</tr>
				<?php endforeach;?>
				<?php endif;?>
				<?php if (count($status->plugins)) : ?>
				<tr>
					<th>Plugin</th>
					<th>Group</th>
					<th></th>
				</tr>
				<?php foreach ($status->plugins as $plugin) : ?>
				<tr class="row<?php echo ($rows++ % 2); ?>">
					<td class="key"><?php echo ucfirst($plugin['name']); ?></td>
					<td class="key"><?php echo ucfirst($plugin['group']); ?></td>
					<td><strong style="color: <?php echo ($plugin['result'])? "green" : "red"?>"><?php echo ($plugin['result'])?'Installed':'Not installed'; ?></strong></td>
				</tr>
				<?php endforeach; ?>
				<?php endif; ?>
				<?php if (!empty($status->libraries) and count($status->libraries)) : ?>
				<tr class="row1">
					<th>Library</th>
					<th></th>
					<th></th>
					</tr>
				<?php foreach ($status->libraries as $libraries) : ?>
				<tr class="row2 <?php //echo ($rows++ % 2); ?>">
					<td class="key"><?php echo ucfirst($libraries['name']); ?></td>
					<td class="key"></td>
					<td><strong style="color: <?php echo ($libraries['result'])? "green" : "red"?>"><?php echo ($libraries['result'])?'Installed':'Not installed'; ?></strong>
					<?php
						if(!empty($libraries['result'])) // if installed then only show msg
						{
						echo $mstat=($libraries['status']? "<span class=\"label label-success\">Enabled</span>" : "<span class=\"label label-important\">Disabled</span>");

						}
					?>

					</td>
				</tr>
				<?php endforeach;?>
				<?php endif;?>

				<?php if (!empty($status->applications) and count($status->applications)) :
				 ?>
				<tr class="row1">
					<th>EasySocial App</th>
					<th></th>
					<th></th>
					</tr>
				<?php foreach ($status->applications as $app_install) : ?>
				<tr class="row2 <?php  ?>">
					<td class="key"><?php echo ucfirst($app_install['name']); ?></td>
					<td class="key"></td>
					<td><strong style="color: <?php echo ($app_install['result'])? "green" : "red"?>"><?php echo ($app_install['result'])?'Installed':'Not installed'; ?></strong>
					<?php
						if(!empty($app_install['result'])) // if installed then only show msg
						{
							echo $mstat=($app_install['status']? "<span class=\"label label-success\">Enabled</span>" : "<span class=\"label label-important\">Disabled</span>");

						}
					?>

					</td>
				</tr>
				<?php endforeach;?>
				<?php endif;?>

			</tbody>
		</table>
	</div>
		<?php
	}

	function removeShortDescription()
	{
		// Short description removed from current version
		$db    = Factory::getContainer()->get(DatabaseInterface::class);
		$query = $db->getQuery(true);
		$query->select($db->quoteName(array('id', 'short_description', 'long_description')));
		$query->from($db->quoteName('#__jticketing_events'));

		$db->setQuery($query);
		$short_description = $db->loadObjectList();

		if (!empty($short_description))
		{
			foreach ($short_description as $desc)
			{
				$obj = new stdclass;
				$obj->id = $desc->id;
				$obj->long_description = $desc->short_description . ' ' . $desc->long_description;
				$obj->short_description = '';

				if (!$db->updateObject('#__jticketing_events', $obj, 'id'))
				{
					return false;
				}
			}
		}
	}

	/**
	 * update event alias field
	 *
	 * @return  void
	 *
	 * @since  1.0
	 */
	public function addEventAlias()
	{
		$db    = Factory::getContainer()->get(DatabaseInterface::class);
		$query = $db->getQuery(true);
		$query->select($db->quoteName(array('id', 'title', 'alias')));
		$query->from($db->quoteName('#__jticketing_events'));
		$db->setQuery($query);
		$events = $db->loadObjectList();

		foreach ($events as $event)
		{
			$event->alias = trim($event->alias);

			if (empty($event->alias))
			{
				$event->alias = trim($event->title);
			}

			$obj = new stdclass;
			$obj->id = $event->id;
			$obj->alias = $event->alias;

			if ($obj->alias)
			{
				if (Factory::getConfig()->get('unicodeslugs') == 1)
				{
					$obj->alias = OutputFilter::stringURLUnicodeSlug($obj->alias);
				}
				else
				{
					$obj->alias = OutputFilter::stringURLSafe($obj->alias);
				}
			}

			// Check if event with same alias is present
			Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jticketing/tables');
			$table = Table::getInstance('Event', 'JticketingTable', array('dbo', $db));

			if ($table->load(array('alias' => $obj->alias)) && ($table->id != $obj->id || $obj->id == 0))
			{
				$msg = Text::_('COM_JTICKETING_SAVE_ALIAS_WARNING');

				while ($table->load(array('alias' => $obj->alias)))
				{
					$obj->alias = StringHelper::increment($obj->alias, 'dash');
				}

				Factory::getApplication()->enqueueMessage($msg, 'warning');
			}

			if (!$db->updateObject('#__jticketing_events', $obj, 'id'))
			{
				return false;
			}
		}
	}

	/**
	 * Installed Notifications Templates
	 *
	 * @return  void
	 */
	public function _installNotificationsTemplates()
	{
		// Check if com_tjnotifications component is installed and enabled
		$notificationModelPath = JPATH_ADMINISTRATOR . '/components/com_tjnotifications/models/notification.php';
		
		if (!is_file($notificationModelPath) || !ComponentHelper::isEnabled('com_tjnotifications'))
		{
			// Component not installed or not enabled, skip notification template installation
			return;
		}

		Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_tjnotifications/tables');
		
		if (!is_file($notificationModelPath))
		{
			return;
		}
		
		require_once $notificationModelPath;
		
		$notificationsModel = \Joomla\CMS\MVC\Model\BaseDatabaseModel::getInstance('Notification', 'TJNotificationsModel');

		if (!$notificationsModel)
		{
			return;
		}

		$existingKeys = [];
		$filePath = JPATH_ADMINISTRATOR . '/components/com_jticketing/jticketingTemplate.json';
		
		if (!is_file($filePath))
		{
			return;
		}
		
		$str = file_get_contents($filePath);
		$json = json_decode($str, true);
		$client = 'com_jticketing';
		
		if (empty($json))
		{
			return;
		}
		
		$existingKeys = $notificationsModel->getKeys($client);

		foreach ($json as $template => $array)
		{
			if (!isset($array['key']))
			{
				continue;
			}
			
			$replacementTagCount = $notificationsModel->getReplacementTagsCount($array['key'], $client);

			// If template doesn't exist then we add notification template.
			if (!in_array($array['key'], $existingKeys))
			{
				$notificationsModel->createTemplates($array);
			}
			else
			{
				$notificationsModel->updateTemplates($array, $client);
			}

			$this->installEasysocialAlert($client, $array['key']);

			// If replacement tags are changed update those
			if (in_array($array['key'], $existingKeys) && isset($array['replacement_tags']) && count($array['replacement_tags']) != $replacementTagCount)
			{
				$notificationsModel->updateReplacementTags($array);
			}
		}
	}
	
	/**
	 * Installed Easysocial alert
	 *
	 * @return  void
	 */
	public function installEasysocialAlert($client, $key)
	{
		$esFilePath = JPATH_ROOT . '/administrator/components/com_easysocial/includes/easysocial.php';

		if (File::exists($esFilePath) && ComponentHelper::isEnabled('com_easysocial'))
		{
			Table::addIncludePath(JPATH_ROOT . '/administrator/components/com_easysocial/tables');
			$db    = Factory::getContainer()->get(DatabaseInterface::class);
		
			$socialAlertTable = Table::getInstance('Alert', 'SocialTable', array('dbo', $db));
			$socialAlertTable->element = $client;
			$socialAlertTable->rule = $key;
			
			return $socialAlertTable->save($socialAlertTable);
		}
	}

	/**
	 * update venue alias field
	 *
	 * @return  false
	 *
	 * @since  1.0
	 */
	public function addVenueAlias()
	{
		$db    = Factory::getContainer()->get(DatabaseInterface::class);
		$query = $db->getQuery(true);
		$query->select($db->quoteName(array('id', 'name', 'alias')));
		$query->from($db->quoteName('#__jticketing_venues'));
		$db->setQuery($query);
		$venues = $db->loadObjectList();

		foreach ($venues as $venue)
		{
			$venue->alias = trim($venue->alias);

			if (empty($venue->alias))
			{
				$venue->alias = trim($venue->name);
			}

			$obj = new stdclass;
			$obj->id = $venue->id;
			$obj->alias = $venue->alias;

			if ($obj->alias)
			{
				if (Factory::getConfig()->get('unicodeslugs') == 1)
				{
					$obj->alias = OutputFilter::stringURLUnicodeSlug($obj->alias);
				}
				else
				{
					$obj->alias = OutputFilter::stringURLSafe($obj->alias);
				}
			}

			// Check if venue with same alias is present
			Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jticketing/tables');
			$table = Table::getInstance('Venue', 'JticketingTable', array('dbo', $db));

			if ($table->load(array('alias' => $obj->alias)) && ($table->id != $obj->id || $obj->id == 0))
			{
				$msg = Text::_('COM_JTICKETING_VENUE_SAVE_ALIAS_WARNING');

				while ($table->load(array('alias' => $obj->alias)))
				{
					$obj->alias = StringHelper::increment($obj->alias, 'dash');
				}

				Factory::getApplication()->enqueueMessage($msg, 'warning');
			}

			if (!$db->updateObject('#__jticketing_venues', $obj, 'id'))
			{
				return false;
			}
		}
	}

	/**
	 * Update core attendee fields values
	 *
	 * @return  void
	 *
	 * @since  1.0
	 */
	public function updateCoreAttendeeFields()
	{
		$db    = Factory::getContainer()->get(DatabaseInterface::class);
		$query = $db->getQuery(true);
		$query->select($db->quoteName(array('id', 'placeholder', 'label')));
		$query->from($db->quoteName('#__jticketing_attendee_fields'));
		$db->setQuery($query);
		$attendeeFields = $db->loadObjectList();

		if (!empty($attendeeFields))
		{
			foreach ($attendeeFields as $field)
			{
				$obj = new stdclass;
				$obj->id = $field->id;

				if ($field->id == '1' && $field->placeholder != 'COM_JTICKETING_AF_FNAME' && $field->label != 'COM_JTICKETING_AF_FNAME')
				{
					$obj->placeholder = 'COM_JTICKETING_AF_FNAME';
					$obj->label = 'COM_JTICKETING_AF_FNAME';
				}
				elseif ($field->id == '2' && $field->placeholder != 'COM_JTICKETING_AF_LNAME' && $field->label != 'COM_JTICKETING_AF_LNAME')
				{
					$obj->placeholder = 'COM_JTICKETING_AF_LNAME';
					$obj->label = 'COM_JTICKETING_AF_LNAME';
				}
				elseif ($field->id == '3' && $field->placeholder != 'COM_JTICKETING_AF_PHONE' && $field->label != 'COM_JTICKETING_AF_PHONE')
				{
					$obj->placeholder = 'COM_JTICKETING_AF_PHONE';
					$obj->label = 'COM_JTICKETING_AF_PHONE';
				}
				elseif ($field->id == '4' && $field->placeholder != 'COM_JTICKETING_AF_EMAIL' && $field->label != 'COM_JTICKETING_AF_EMAIL')
				{
					$obj->placeholder = 'COM_JTICKETING_AF_EMAIL';
					$obj->label = 'COM_JTICKETING_AF_EMAIL';
				}

				if (!$db->updateObject('#__jticketing_attendee_fields', $obj, 'id'))
				{
					return false;
				}
			}
		}
	}

	/**
	 * Delete Payment plugin logs on installation
	 *
	 * @since 2.0.5
	 *
	 * @return void
	 */
	public function deleteLog()
	{
		// Get log config path
		$config = Factory::getConfig();
		$logPath = $config->get('log_path');

		if (empty($logPath) || !is_dir($logPath))
		{
			return;
		}

		$logsPath = [
		"cpg__paypal.log", "com_jticketing_paypal.log",
		"com_jticketing_authorizenet.log", "com_jticketing_2checkout.log",
		"com_jticketing_adaptive_paypal.log", "com_jticketing_alphauserpoints.log",
		"com_jticketing_bycheck.log", "com_jticketing_byorder.log",
		"com_jticketing_jomsocialpoints.log", "com_jticketing_linkpoint.log",
		"com_jticketing_payu.log", "com_jticketing_ccavenue.log",
		"com_jticketing_easysocialpoints.log", "com_jticketing_cod.log",
		"com_jticketing_epaydk.log", "com_jticketing_epaydk.log",
		"com_jticketing_ewallet.log", "com_jticketing_eway.log",
		"com_jticketing_ewayrapid3.log", "com_jticketing_ogone.log",
		"com_jticketing_pagseguro.log", "com_jticketing_payfast.log",
		"com_jticketing_paymill.log", "com_jticketing_paypalpro.log",
		"com_jticketing_payumoney.log", "com_jticketing_transfirst.log",
		"com_jticketing_razorpay.log"];

		foreach ($logsPath as $path)
		{
			$filePath = $logPath . '/' . $path;
			
			// Check if file exists before trying to delete
			if (!is_file($filePath))
			{
				continue;
			}

			try
			{
				// 1. Try to delete log file using native PHP (more reliable)
				if (@unlink($filePath))
				{
					continue;
				}
				
				// 2. If unlink fails, try File::delete()
				if (!@File::delete($filePath))
				{
					// 3. If delete fails, try to clear content
					@file_put_contents($filePath, "");
					
					// 4. Ensure .htaccess exists to protect directory
					$htaccessPath = $logPath . '/.htaccess';
					if (!is_file($htaccessPath))
					{
						@file_put_contents($htaccessPath, "Deny from all");
					}
				}
			}
			catch (\Exception $e)
			{
				// Silently continue - log deletion is not critical for installation
				continue;
			}
			catch (\Throwable $e)
			{
				// Silently continue - log deletion is not critical for installation
				continue;
			}
		}
	}

	/**
	 * Uninstalls subextensions (modules, plugins) bundled with the main extension
	 *
	 * @param   JInstaller  $parent  parent
	 *
	 * @return  JObject The subextension uninstallation status
	 */
	private function _uninstallSubextensions($parent)
	{

		$db = Factory::getContainer()->get(DatabaseInterface::class);

		$status          = new stdClass;
		$status->modules = [];
		$status->plugins = [];

		$src = $parent->getParent()->getPath('source');

		// Modules uninstallation
		if (count($this->uninstallation_queue['modules']))
		{
			foreach ($this->uninstallation_queue['modules'] as $folder => $modules)
			{
				if (count($modules))
				{
					foreach ($modules as $module => $modulePreferences)
					{
						// Find the module ID
						$sql = $db->getQuery(true)->select($db->quoteName('extension_id'))
						->from($db->quoteName('#__extensions'))
						->where($db->quoteName('element') . ' = ' . $db->quote($module))
						->where($db->quoteName('type') . ' = ' . $db->quote('module'));
						$db->setQuery($sql);
						$id = $db->loadColumn()[0] ?? null;

						// Uninstall the module
						if ($id)
						{
							$installer         = Factory::getContainer()->get(Installer::class);
							$result            = $installer->uninstall('module', $id, 1);
							$status->modules[] = [
								'name' => $module,
								'client' => $folder,
								'result' => $result
							];
						}
					}
				}
			}
		}

		// Plugins uninstallation
		if (count($this->uninstallation_queue['plugins']))
		{
			foreach ($this->uninstallation_queue['plugins'] as $folder => $plugins)
			{
				if (count($plugins))
				{
					foreach ($plugins as $plugin => $published)
					{
						$sql = $db->getQuery(true)->select($db->quoteName('extension_id'))
						->from($db->quoteName('#__extensions'))
						->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
						->where($db->quoteName('element') . ' = ' . $db->quote($plugin))
						->where($db->quoteName('folder') . ' = ' . $db->quote($folder));
						$db->setQuery($sql);

						$id = $db->loadColumn()[0] ?? null;

						if ($id)
						{
							$installer         = Factory::getContainer()->get(Installer::class);
							$result            = $installer->uninstall('plugin', $id);
							$status->plugins[] = [
								'name' => 'plg_' . $plugin,
								'group' => $folder,
								'result' => $result
							];
						}
					}
				}
			}
		}

		return $status;
	}

	/**
	 * Install Certificate Templates
	 *
	 * @return  void
	 */
	private function installCertificateTemplates()
	{
		if (ComponentHelper::isEnabled('com_tjcertificate'))
		{
			$tjcertificatePath = JPATH_ADMINISTRATOR . '/components/com_tjcertificate/includes/tjcertificate.php';
			
			if (!is_file($tjcertificatePath))
			{
				return;
			}
			
			require_once $tjcertificatePath;

			$filePath = JPATH_ADMINISTRATOR . '/components/com_jticketing/certificateTemplate.json';
			
			if (!is_file($filePath))
			{
				return;
			}
			
			$str = file_get_contents($filePath);
			$json = json_decode($str, true);

			if (empty($json) || !class_exists('TJCERT'))
			{
				return;
			}

			$table = TJCERT::table("templates");
			$model = TJCERT::model('Template', ['ignore_request' => true]);

			foreach ($json as $template => $tmplArr)
			{
				if (!isset($tmplArr['unique_code']))
				{
					continue;
				}
				
				$table->load(['unique_code' => $tmplArr['unique_code']]);

				if (empty($table->id))
				{
					$model->save($tmplArr);
				}
			}
		}
	}

	/**
	 * Set default bootstrap layouts to load
	 *
	 * @param   string  $type  install, update or discover_update
	 * 
	 * @return void
	 *
	 * @since 4.0.0
	 */
	 public function setDefaultLayout($type)
	 {
		 if ($type == 'install' && JVERSION >= '4.0.0')
		 {
			 $db = Factory::getContainer()->get(DatabaseInterface::class);
			 $query = $db->getQuery(true);
			 $query->select('*');
			 $query->from($db->quoteName('#__extensions'));
			 $query->where($db->quoteName('type') . ' = ' . $db->quote('component'));
			 $query->where($db->quoteName('element') . ' = ' . $db->quote('com_jticketing'));
			 $db->setQuery($query);
			 $data = $db->loadObject();
 
			 $params = json_decode($data->params);
 
			 if (!empty($params) && isset($params->bootstrap_version))
			 {
				 $query = $db->getQuery(true);
				 $params->bootstrap_version = 'bs5';
				 $fields = [$db->quoteName('params') . ' = ' . $db->quote(json_encode($params))];
				 $conditions = [$db->quoteName('extension_id') . ' = ' . $data->extension_id];
				 $query->update($db->quoteName('#__extensions'))->set($fields)->where($conditions);
				 $db->setQuery($query);
				 $db->execute();
			 }
		 }
	 }

	 /**
	  * This function add all events menu entry in mainmainu
	  *
	  * @return  boolean
	  *
	  * @since  4.0.2
	  */
	 private function _addMenuItems()
	 {
		$lang = Factory::getLanguage();
		$lang->load('com_jticketing', JPATH_ADMINISTRATOR);
 
		 // Get new component id.
		$db = Factory::getContainer()->get(DatabaseInterface::class);
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from($db->quoteName('#__extensions'));
		$query->where($db->quoteName('type') . ' = ' . $db->quote('component'));
		$query->where($db->quoteName('element') . ' = ' . $db->quote('com_jticketing'));
		$db->setQuery($query);
		$data = $db->loadObject();

		$componentId = 0;

		if (isset($data->extension_id))
		{
			$componentId = $data->extension_id;
		}
 
		 // Get the default menu type from database (safer during installation)
		$db = Factory::getContainer()->get(DatabaseInterface::class);
		$query = $db->getQuery(true);
		$query->select($db->quoteName('menutype'));
		$query->from($db->quoteName('#__menu_types'));
		$query->where($db->quoteName('client_id') . ' = 0');
		$query->order($db->quoteName('id') . ' ASC');
		$db->setQuery($query, 0, 1);
		$defaultMenuType = $db->loadResult();
		
		// Fallback to 'mainmenu' if no menu type found
		if (empty($defaultMenuType))
		{
			$defaultMenuType = 'mainmenu';
		}
 
		 // Update the existing menu items.
		 $row               = Table::getInstance('menu');
		 $row->menutype     = $defaultMenuType;
		 $row->title        = Text::_('COM_JTICKETING_ALL_EVENTS_MENU');
		 $row->alias        = 'all-events';
		 $row->path         = 'all-events';
		 $row->access       = 1;
		 $row->link         = 'index.php?option=com_jticketing&view=events&layout=default';
		 $row->type         = 'component';
		 $row->published    = '1';
		 $row->params       = '{"show_filters":1,"show_search_filter":1,"show_category_filter":1}';
		 $row->component_id = $componentId;
		 $row->id           = null;
		 $row->language     = '*';
		 $row->check();
 
		 $isPresent = $this->isMenuItemPresent($row->link);
 
		 if (empty($isPresent))
		 {
			 // Add menu
			 $var = $row->store();
 
			 // Update menu
			 $query = $db->getQuery(true);
			 $query->update($db->quoteName('#__menu'));
			 $query->set($db->quoteName('parent_id') . " = " . $db->quote(1));
			 $query->set($db->quoteName('level') . " = " . $db->quote(1));
			 $query->where($db->quoteName('id') . " = " . $db->quote($row->id));
			 $db->setQuery( $query );
 
			 try
			 {
				 $db->execute();
			 }
			 catch (\RuntimeException $e)
			 {
				 $this->setError($e->getMessage());
 
				 return false;
			 }
 
			 // As add all events menu. Use it for display
			 return true;
		 }
	 }

	 /**
	  * check whether menu for link is resent or not
	  *
	  * @return  void
	  *
	  * @since  4.0.2
	  */
	 public function isMenuItemPresent($link, $menutype='mainmenu')
	 {
		 $db    = Factory::getContainer()->get(DatabaseInterface::class);
		 $query = $db->getQuery(true);
		 $query->select($db->quoteName(array('id')));
		 $query->from($db->quoteName('#__menu'));
 
		 if (!empty($link))
		 {
			 $link = $db->quote('%' . $db->escape($link, true) . '%');
			 $query->where('(link LIKE ' . $link . ')');
		 }
 
		 $db->setQuery($query);
 
			return $db->loadColumn()[0] ?? null;
	 }

	 /**
	  * This function will create my events menu entry in mainmainu
	  *
	  * @return  boolean
	  *
	  * @since  4.0.2
	  */
	 private function _addMyEventsMenu()
	 {
		 $lang = Factory::getLanguage();
		 $lang->load('com_jticketing', JPATH_ADMINISTRATOR);
 
		 // Get new component id.
		$db = Factory::getContainer()->get(DatabaseInterface::class);
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from($db->quoteName('#__extensions'));
		$query->where($db->quoteName('type') . ' = ' . $db->quote('component'));
		$query->where($db->quoteName('element') . ' = ' . $db->quote('com_jticketing'));
		$db->setQuery($query);
		$data = $db->loadObject();

		$componentId = 0;

		if (isset($data->extension_id))
		{
			$componentId = $data->extension_id;
		}

		 // Get the default menu type from database (safer during installation)
		$db = Factory::getContainer()->get(DatabaseInterface::class);
		$query = $db->getQuery(true);
		$query->select($db->quoteName('menutype'));
		$query->from($db->quoteName('#__menu_types'));
		$query->where($db->quoteName('client_id') . ' = 0');
		$query->order($db->quoteName('id') . ' ASC');
		$db->setQuery($query, 0, 1);
		$defaultMenuType = $db->loadResult();
		
		// Fallback to 'mainmenu' if no menu type found
		if (empty($defaultMenuType))
		{
			$defaultMenuType = 'mainmenu';
		}
 
		 // Update the existing menu items.
		 $row               = Table::getInstance ('menu');
		 $row->menutype     = $defaultMenuType;
		 $row->title        = Text::_('COM_JTICKETING_MY_EVENTS_MENU');
		 $row->alias        = 'my-events';
		 $row->path         = 'my-events';
		 $row->access       = 1;
		 $row->link         = 'index.php?option=com_jticketing&view=events&layout=my';
		 $row->type         = 'component';
		 $row->published    = '1';
		 $row->component_id = $componentId;
		 $row->id           = null;
		 $row->language     = '*';
		 $row->check();
 
		 $isPresent = $this->isMenuItemPresent($row->link);
 
		 if (empty($isPresent))
		 {
			 // Add menu
			 $var = $row->store();
 
			 // Update menu
			 $query = $db->getQuery(true);
			 $query->update($db->quoteName('#__menu'));
			 $query->set($db->quoteName('parent_id') . " = " . $db->quote(1));
			 $query->set($db->quoteName('level') . " = " . $db->quote(1));
			 $query->where($db->quoteName('id') . " = " . $db->quote($row->id));
			 $db->setQuery( $query );
			 
			 try
			 {
				 $db->execute();
			 }
			 catch (\RuntimeException $e)
			 {
				 $this->setError($e->getMessage());
 
				 return false;
			 }
			 
			 return true;
		 }
	 }	 

	/**
	 * Remove duplicate menu item
	 *
	 * @return boolean
	 *
	 * @since 2.4.0
	 */
	public function menuItemMigration()
	{
		$db = Factory::getContainer()->get(DatabaseInterface::class);
		$query = $db->getQuery(true);

		// Joomla 6: JVERSION check removed
		if (false) // Legacy >= '4.0.0')
		{
			$table   = new \Joomla\Component\Menus\Administrator\Table\MenuTable($db);
		} 
		else 
		{
			Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_menus/tables');
			$table = Table::getInstance('menu', 'MenusTable', ['dbo', $db]);
		}

		$db = Factory::getContainer()->get(DatabaseInterface::class);
		$query = $db->getQuery(true);
		$query->select('id');
		$query->from($db->quoteName('#__menu'));
		$query->where($db->quoteName('menutype') . ' = ' . $db->quote('main'));
		$query->where($db->quoteName('path') . ' IN ("com-jticketing-title-tjreport", "com-jticketing-regions", "com-jticketing-countries",
					"com-jticketing-vendors", "com-jticketing-title-email-template", "com-jticketing-notifications-subscriptions",
					"com-jticketing-certificate-template", "com-jticketing-certificate-issued", "com-jticketing-title-reminders",
					"com-jticketing-event-tjfield-menu", "com-jticketing-event-tjgroup-menu",
					"com-jticketing-attendee-field-menu", "com-jticketing-attendee-fields-group",
					"com_jticketing_title_tjreport", "com_jticketing_regions", "com_jticketing_countries",
					"com_jticketing_vendors", "com_jticketing_title_email_template", "com_jticketing_notifications_subscriptions",
					"com_jticketing_certificate_template", "com_jticketing_certificate_issued", "com_jticketing_title_reminders",
					"com_jticketing_event_tjfield_menu", "com_jticketing_event_tjgroup_menu",
					"com_jticketing_attendee_field_menu", "com_jticketing_attendee_fields_group")');
		$db->setQuery($query);
		$data = $db->loadObjectList();

		foreach ($data as $key => $menuItem) 
		{
			$table->delete($menuItem->id);
		}

		return true;
	}

	/**
	 * Add acymailing integration to work with jticketing
	 *
	 * @return boolean
	 *
	 * @since 2.4.0
	 */
	public function addAcyMalingIntegration()
	{
		// check acymailing is installed or not
		if (is_dir(JPATH_ADMINISTRATOR . "/components/com_acym/dynamics"))
		{
			// Get the database object
			$db = Factory::getContainer()->get(DatabaseInterface::class);
			$query = "SHOW TABLES LIKE " . $db->quote($db->replacePrefix('#__acym_plugin'));

			// Set the query and execute
			$db->setQuery($query);
			$result = $db->loadColumn()[0] ?? null;

			// check database table is exist or not
			if ($result)
			{
				// Get new component id.
				$db = Factory::getContainer()->get(DatabaseInterface::class);
				$query = $db->getQuery(true);
				$query->select('*');
				$query->from($db->quoteName('#__acym_plugin'));
				$query->where($db->quoteName('folder_name') . ' = ' . $db->quote('jticketing'));
				$db->setQuery($query);
				$data = $db->loadObject();

				if (!($data && $data->id))
				{
					$db = Factory::getContainer()->get(DatabaseInterface::class);
					$acymInteObject = new stdclass;
					$acymInteObject->title = 'Jticketing';
					$acymInteObject->folder_name = 'jticketing';
					$acymInteObject->version = '1.0.0';
					$acymInteObject->active = '1';
					$acymInteObject->category = 'Event management';
					$acymInteObject->level = '';
					$acymInteObject->uptodate = '1';
					$acymInteObject->latest_version = '1.0.0';
					$acymInteObject->description = '<a href=\"https://techjoomla.com/jticketing\" target=\"_blank\"> https://techjoomla.com/jticketing<i class=\"fa fa-external-link ms-2\" aria-hidden=\"true\"></i> </a>';
					$acymInteObject->type = 'ADDON';

					if (!$db->insertObject('#__acym_plugin', $acymInteObject, 'id'))
					{
						$app = Factory::getApplication();
						$app->enqueueMessage($db->getErrorMsg(), 'error');

						return false;
					}
				}

			}
		}
	}
}