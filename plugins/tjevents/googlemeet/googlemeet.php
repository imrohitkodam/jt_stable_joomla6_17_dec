<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Tjevents.googlemeet
 *
 * @copyright   Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Plugin\CMSPlugin;

JLoader::discover("JTicketingEvent", JPATH_PLUGINS . '/tjevents/googlemeet/googlemeet');

/**
 * Class for Googlemeet Tjevents Plugin
 *
 * @since  1.0.0
 */
class PlgTjeventsGooglemeet extends CMSPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 *
	 * @since  3.2.11
	 */
	protected $autoloadLanguage = true;

	/**
	 * Constructor
	 *
	 * @param   string  $subject  subject
	 * @param   array   $config   config
	 *
	 * @since   1.0.0
	 */
	public function __construct($subject, $config)
	{
		parent::__construct($subject, $config);
	}

	public function GetContentInfo($config = array('googlemeet'))
	{
		$obj = array();
		$obj['name'] = 'Googlemeet';
		$obj['id']   = $this->_name;

		return $obj;
	}

	/**
	 * Example trigger
	 *
	 * @param   string   $param1  Example
	 * @param   string   $param2  Example
	 *
	 * @return  boolean
	 *
	 * @since   1.0.0
	 */
	public function onEntityAfterSave($param1, $param2)
	{
		return true;
	}
	
	/**
	 * Return the type of the plugin
	 *
	 * @return  array
	 *
	 * @since  4.1.3
	 */
	public function onJtGetContentInfo()
	{
		$obj = array();
		$obj['name'] = 'Googlemeet';
		$obj['id']   = $this->_name;

		return $obj;
	}
}
