<?php
/**
 * @package     TJVendors
 * @subpackage  com_tjvendors
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */
defined('_JEXEC') or die();

use Joomla\CMS\Factory;

/**
 * Version information class for the TJVendors.
 *
 * @since  1.4.0
 */
class TjvendorsVersion
{
	/**
	 * Product name.
	 *
	 * @var    string
	 * @since  1.4.0
	 */
	const PRODUCT = 'TJVendors';

	/**
	 * Major release version.
	 *
	 * @var    integer
	 * @since  1.4.0
	 */
	const MAJOR_VERSION = 1;

	/**
	 * Minor release version.
	 *
	 * @var    integer
	 * @since  1.4.0
	 */
	const MINOR_VERSION = 4;

	/**
	 * Patch release version.
	 *
	 * @var    integer
	 * @since  1.4.0
	 */
	const PATCH_VERSION = 0;

	/**
	 * Release date.
	 *
	 * @var    string
	 * @since  1.4.0
	 */
	const RELDATE = '20-August-2019';

	/**
	 * Gets a "PHP standardized" version string for the current TJVendors.
	 *
	 * @return  string  Version string.
	 *
	 * @since   1.4.0
	 */
	public function getShortVersion()
	{
		return self::MAJOR_VERSION . '.' . self::MINOR_VERSION . '.' . self::PATCH_VERSION;
	}

	/**
	 * Gets a version string for the current TJVendors with all release information.
	 *
	 * @return  string  Complete version string.
	 *
	 * @since   1.4.0
	 */
	public function getLongVersion()
	{
		return self::PRODUCT . ' ' . $this->getShortVersion() . ' ' . self::RELDATE;
	}

	/**
	 * Generate a media version string for assets
	 * Public to allow third party developers to use it
	 *
	 * @return  string
	 *
	 * @since   1.4.0
	 */
	public function generateMediaVersion()
	{
		return md5($this->getLongVersion() . Factory::getConfig()->get('secret'));
	}

	/**
	 * Gets a media version which is used to append to TJVendors core media files.
	 *
	 * This media version is used to append to TJVendors core media in order to trick browsers into
	 * reloading the CSS and JavaScript, because they think the files are renewed.
	 * The media version is renewed after TJVendors core update, install, discover_install and uninstallation.
	 *
	 * @return  string  The media version.
	 *
	 * @since   1.4.0
	 */
	public function getMediaVersion()
	{
		return $this->generateMediaVersion();
	}
}
