<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_hierarchy
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\Filesystem\File;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

/**
 * Hierarchys helper.
 *
 * @since  1.6
 */
class HierarchyFrontendHelper
{
	/**
	 * HierarchyFrontendHelper constructor
	 *
	 * @since   1.0
	 */
	public function __construct()
	{
		$this->params      = ComponentHelper::getParams('com_hierarchy');
		$this->integration = $this->params->get('integration', 2);

		// Note: jimport calls for Techjoomla JSocial classes
		// These may need to be updated based on Techjoomla JSocial library compatibility with Joomla 6
		if ($this->integration != 'none')
		{
			if ($this->integration == '2')
			{
				// jimport('techjoomla.jsocial.joomla');
			}
			elseif ($this->integration == '1')
			{
				// jimport('techjoomla.jsocial.jomsocial');
			}
			elseif ($this->integration == '4')
			{
				// jimport('techjoomla.jsocial.easysocial');
			}
			elseif($this->integration == '6')
			{
				// jimport('techjoomla.jsocial.cb');
			}
		}
	}

	/**
	 * Get layout html
	 *
	 * @param   string  $viewName       name of view
	 * @param   string  $layout         layout of view
	 * @param   string  $searchTmpPath  site/admin template
	 * @param   string  $useViewpath    site/admin view
	 *
	 * @return  [type]                  description
	 */
	public function getViewPath($viewName, $layout = "", $searchTmpPath = 'SITE', $useViewpath = 'SITE')
	{
		$searchTmpPath = ($searchTmpPath == 'SITE') ? JPATH_SITE : JPATH_ADMINISTRATOR;
		$useViewpath   = ($useViewpath == 'SITE') ? JPATH_SITE : JPATH_ADMINISTRATOR;

		if (!empty($layout))
		{
			$layoutName = $layout . '.php';
		}
		else
		{
			$layoutName = "default.php";
		}

		// Get templates from override folder
		if ($searchTmpPath == JPATH_SITE)
		{
			$defTemplate = $this->getSiteDefaultTemplate(0);
		}
		else
		{
			$defTemplate = $this->getSiteDefaultTemplate(0);
		}

		$override = $searchTmpPath . '/templates/' . $defTemplate . '/html/com_hierarchy/' . $viewName . '/' . $layoutName;

		if (File::exists($override))
		{
			return $view = $override;
		}
		else
		{
			return $view = $useViewpath . '/components/com_hierarchy/views/' . $viewName . '/tmpl/' . $layoutName;
		}
	}

	/**
	 * Get sites/administrator default template
	 *
	 * @param   mixed  $client  0 for site and 1 for admin template
	 *
	 * @return  json
	 *
	 * @since   1.5
	 */
	public function getSiteDefaultTemplate($client = 0)
	{
		try
		{
			$db = Factory::getDbo();

			// Get current status for Unset previous template from being default
			// For front end => client_id=0
			$query = $db->getQuery()->select('template')->from($db->quoteName('#__template_styles'))->where('client_id=' . $client)->where('home=1');
			$db->setQuery($query);

			return $db->loadResult();
		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());

			return '';
		}
	}

	/**
	 * Get user Avatar
	 *
	 * @param   integer  $userid    userid
	 * @param   integer  $relative  relative
	 *
	 * @return  string  profile url
	 *
	 * @since   1.0
	 */
	public function getUserAvatar($userid, $relative = false)
	{
		$user        = Factory::getUser($userid);
		$gravatar    = $this->params->get('gravatar');
		$uimage      = '';

		if ($this->integration == '2')
		{
			if ($gravatar)
			{
				$user     = Factory::getUser($userid);
				$usermail = $user->get('email');

				// Refer https://en.gravatar.com/site/implement/images/php/
				$hash     = md5(strtolower(trim($usermail)));
				$uimage   = 'http://www.gravatar.com/avatar/' . $hash . '?s=32';

				return $uimage;
			}
			else
			{
				if ($relative)
				{
					$uimage = 'media/com_hierarchy/images/default_avatar.png';
				}
				else
				{
					$uimage = Uri::root() . 'media/com_hierarchy/images/default_avatar.png';
				}
			}
		}
		else
		{
			if ($this->integration == '6')
			{
				$this->integration = new JSocialCB;
			}
			elseif ($this->integration == '1')
			{
				$this->integration = new JSocialJomsocial;
			}
			elseif ($this->integration == '4')
			{
				$this->integration = new JSocialEasysocial;
			}

			$uimage = $this->integration->getAvatar($user, '', $relative);
		}

		return $uimage;
	}
}
