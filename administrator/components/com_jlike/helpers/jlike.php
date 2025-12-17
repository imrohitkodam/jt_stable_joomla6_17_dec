<?php
/**
 * @package     JLike
 * @subpackage  com_jlike
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access.
defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\Filesystem\Path;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Object\CMSObject;

/**
 * Class for JLike helper
 *
 * @since  1.6
 */
class JLikeHelper
{
	public static $extension = 'com_jlike';

	/**
	 * Configure the Linkbar.
	 *
	 * @param   string  $vName  The name of the active view.
	 *
	 * @return    CMSObject
	 *
	 * @since    1.6
	 */
	public static function addJLikeSubmenu($vName = '')
	{
		if (JVERSION < '4.0.0')
		{
			$dashboard = $element_config = $buttonset = $annotations = $about = false;

			$ratings = $ratingtypes = $reminders = $types = $paths = $contents = false;

			// $pathNodeGraphs = $categories = false;

			switch ($vName)
			{
				case 'dashboard':
					$dashboard = true;

				break;

				case 'element_config':
					$element_config = true;

				break;

				case 'buttonset':
					$buttonset = true;

				break;

				case 'annotations':
					$annotations = true;

				break;

				case 'reminders':
					$reminders = true;

				break;

				case 'about':
					$about = true;

				break;

				case 'types':
					$types = true;

				break;

				case 'paths':
					$paths = true;

				break;

				case 'contents':
					$contents = true;

				break;

				/*case 'pathnodegraphs':
					$pathNodeGraphs = true;
				break;*/

				case 'ratingtypes':
					$ratingtypes = true;

				break;

				case 'ratings':
					$ratings = true;

				break;

				/*case 'categories':
					$categories = true;
				break;*/

				default:
					$dashboard = true;

				break;
			}

			JHtmlSidebar::addEntry(
				Text::_('COM_JLIKE_DASHBOARD'),
				'index.php?option=com_jlike&view=dashboard',
				$dashboard
			);
			JHtmlSidebar::addEntry(
				Text::_('COM_JLIKE_ELEMENT_CONFIG'),
				'index.php?option=com_jlike&view=element_config',
				$element_config
			);
			JHtmlSidebar::addEntry(
				Text::_('COM_JLIKE_BUTTON_SETTINGS'),
				'index.php?option=com_jlike&view=buttonset',
				$buttonset
			);
			JHtmlSidebar::addEntry(
				Text::_('COM_JLIKE_TITLE_ANNOTATIONS'),
				'index.php?option=com_jlike&view=annotations',
				$annotations
			);
			JHtmlSidebar::addEntry(
				Text::_('COM_JLIKE_TITLE_ABOUT'),
				'index.php?option=com_jlike&view=about',
				$about
			);
			JHtmlSidebar::addEntry(
				Text::_('COM_JLIKE_TITLE_REMINDERS'),
				'index.php?option=com_jlike&view=reminders',
				$reminders
			);
			JHtmlSidebar::addEntry(
				Text::_('COM_JLIKE_TITLE_PATHS_TYPES'),
				'index.php?option=com_jlike&view=types',
				$types
			);
			JHtmlSidebar::addEntry(
				Text::_('COM_JLIKE_TITLE_PATHS'),
				'index.php?option=com_jlike&view=paths',
				$paths
			);

			JHtmlSidebar::addEntry(
				Text::_('COM_JLIKE_TITLE_CONTENTS'),
				'index.php?option=com_jlike&view=contents',
				$contents
			);

			JHtmlSidebar::addEntry(
				Text::_('COM_JLIKE_TITLE_RATING_TYPES'),
				'index.php?option=com_jlike&view=ratingtypes',
				$ratingtypes
			);

			JHtmlSidebar::addEntry(
				Text::_('COM_JLIKE_TITLE_VIEW_RATING'),
				'index.php?option=com_jlike&view=ratings',
				$ratings
			);

			/*JHtmlSidebar::addEntry(
				Text::_('COM_JLIKE_TITLE_PATH_NODE_GRAPH'),
				'index.php?option=com_jlike&view=pathnodegraphs',
				$pathNodeGraphs
			);*/

			/*JHtmlSidebar::addEntry(
				Text::_('COM_JLIKE_TITLE_PATH_CATEGORIES'),
				'index.php?option=com_categories&view=categories&extension=com_jlike.paths',
				$categories == 'categories'
			);*/
		}
	}

	/**
	 * Configure the Linkbar.
	 *
	 * @param   string  $view  string
	 *
	 * @return void
	 */
	public static function addSubmenu($view = '')
	{
		$extension   = Factory::getApplication()->getInput()->get('extension', '', 'STRING');
		$full_client = $extension;

		// Set ordering.
		$full_client = explode('.', $full_client);

		// Eg com_jgive
		$component = $full_client[0];
		$eName     = str_replace('com_', '', $component);
		$file      = Path::clean(JPATH_ADMINISTRATOR . '/components/' . $component . '/helpers/' . $eName . '.php');

		if (file_exists($file))
		{
			require_once $file;
			$prefix = ucfirst($eName);
			$cName  = $prefix . 'Helper';

			if (class_exists($cName))
			{
				if (is_callable(array($cName, 'addJLikeSubmenu')))
				{
					// Loading language file
					$lang = Factory::getLanguage();
					$lang->load($component, JPATH_BASE, null, false, false)
					 || $lang->load($component, Path::clean(JPATH_ADMINISTRATOR . '/components/' . $component), null, false, false)
					 || $lang->load($component, JPATH_BASE, $lang->getDefault(), false, false)
					 || $lang->load($component, Path::clean(JPATH_ADMINISTRATOR . '/components/' . $component), $lang->getDefault(), false, false);

					call_user_func(array($cName, 'addJLikeSubmenu'), $view . (isset($section) ? '.' . $section : ''));
				}
			}
		}
		else
		{
			self::addJLikeSubmenu($view);
		}
	}

	/**
	 * Creates the component footer
	 *
	 * @return  void
	 *
	 * @since    1.6
	 */
	public static function addFooter()
	{
		echo '<br style="clear:both;height:1px"/>';
		echo '<div style="margin:2em 0 0 0;border-top:2px solid #DDD;">';
		echo '<p style="text-align:center">&copy;' . date('Y') . ' \'corePHP\' All Rights Reserved</p>';
		echo '<p>Product Page: <a href="http://www.corephp.com/joomla-products/jlike.html" target="_blank">jLike</a></p>';
		echo '<p>Website: <a href="http://www.corephp.com" target="_blank">www.corePHP.com</a></p>';
		echo '<p>Problems/Questions: <a href="https://www.corephp.com/members/submitticket.php" target="_blank">Submit a ticket.</a></p>';
		echo '<table summary=""><tbody>';
		echo "<tr><td>Your Server:</td><td>" . gmdate('D, d M Y H:i:s T') . "</td></tr>";
		echo "<tr><td>Your Computer:</td><td><script type=\"text/javascript\">document.write(new Date().toUTCString())</script></td></tr>";
		echo '</tbody></table>';
		echo '</div>';
	}

	/**
	 * Gets a list of the actions that can be performed.
	 *
	 * @return  result.
	 *
	 * @since    1.6
	 */
	public static function getActions()
	{
		$user   = Factory::getUser();
		$result = new CMSObject;

		$assetName = 'com_jlike';

		$actions = array(
			'core.admin',
			'core.manage',
			'core.create',
			'core.edit',
			'core.edit.own',
			'core.edit.state',
			'core.delete',
		);

		foreach ($actions as $action)
		{
			$result->set($action, $user->authorise($action, $assetName));
		}

		return $result;
	}

	/**
	 * Get count of the required categories count.
	 *
	 * @param   string  $extension  category name
	 *
	 * @return  array
	 *
	 * @since    1.6
	 */
	public static function getCount($extension)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('COUNT(*)');
		$query->from($db->quoteName('#__categories'));
		$query->where($db->quoteName('extension') . ' LIKE ' . $db->quote($extension));
		$db->setQuery($query);

		return $db->loadResult();
	}

	/**
	 * Method to get the category .
	 *
	 * @param   int  $extension  id or extension for genric purpose
	 *
	 * @return  array  data id or category name
	 *
	 * @since   12.2
	 */
	public function getCategory($extension)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName(array('id', 'path')));
		$query->from($db->quoteName('#__categories'));
		$query->where($db->quoteName('extension') . " = " . $db->quote($extension) . 'OR' . $db->quoteName('id') . " = " . $db->quote($extension));

		$db->setQuery($query);

		return $db->loadAssocList();
	}

	/**
	 * Check if given string in JSON
	 *
	 * @param   object  $string  string
	 *
	 * @return boolean
	 */
	public function isJSON($string)
	{
		return is_string($string) && is_array(json_decode($string, true)) && (json_last_error() == JSON_ERROR_NONE) ? true : false;
	}
}
