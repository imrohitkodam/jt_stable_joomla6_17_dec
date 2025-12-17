<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Tjevents.zoom
 *
 * @copyright   Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license     http:/www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Plugin\CMSPlugin;

JLoader::discover("JTicketingEvent", JPATH_PLUGINS . '/tjevents/zoom/zoom');

/**
 * Class for Zoom Tjevents Plugin
 *
 * @since  1.0.0
 */
class PlgTjeventsZoom extends CMSPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 *
	 * @since  1.0.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * Return the type of the plugin
	 *
	 * @param   INT  $config  tevents pllugin
	 *
	 * @return  array
	 *
	 * @since  1.0.0
	 */
	public function onJtGetContentInfo($config = array('zoom'))
	{
		$obj = array();
		$obj['name'] = 'Zoom';
		$obj['id']   = $this->_name;

		return $obj;
	}
}
