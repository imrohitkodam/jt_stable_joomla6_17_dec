<?php
/**
 * @version    SVN: <svn_id>
 * @package    Com_Hierarchy
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2017 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Object\CMSObject;

/**
 * Hierarchys helper.
 *
 * @since  1.6
 */
class HierarchyHelper
{
	/**
	 * Configure the Submenu links.
	 *
	 * @param   string  $vName  The extension being used for the categories.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public static function addSubmenu($vName = '')
	{
		if (JVERSION < '4.0.0')
		{
			JHtmlSidebar::addEntry(Text::_('COM_HIERARCHY_TITLE_HIERARCHYS'), 'index.php?option=com_hierarchy&view=hierarchys', $vName == 'hierarchys');
		}
	}
	
	/**
	 * Gets a list of the actions that can be performed.
	 *
	 * @return  JObject
	 *
	 * @since   1.6
	 * @deprecated  3.2  Use JHelperContent::getActions() instead
	 */
	public static function getActions()
	{
		$user = Factory::getUser();
		$result = new CMSObject;

		$assetName = 'com_hierarchy';

		$actions = array('core.admin', 'core.manage', 'core.create', 'core.edit',
		'core.edit.own', 'core.edit.state', 'core.delete', 'core.csv.export', 'core.csv.import');

		foreach ($actions as $action)
		{
			$result->set($action, $user->authorise($action, $assetName));
		}

		return $result;
	}

	/** Get all jtext for javascript
	 *
	 * @return   void
	 *
	 * @since   1.0
	 */
	public static function getLanguageConstant()
	{
		Text::script('JGLOBAL_VALIDATION_FORM_FAILED');
		Text::script('COM_HIERARCHY_HIERARCHY_DELETE_CONF');
		Text::script('COM_HIERARCHY_USERNAMES_DESC');
	}
}
