<?php
/**
 * @package     JTicketing
 * @subpackage  com_jticketing
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Categories\Categories;

/**
 * JTicketing Categories helper.
 *
 * @since  1.0.0
 */
class JticketingCategories extends Categories
{
	/**
	 * Method acts as a consturctor
	 *
	 * @param   ARRAY  $options  categories with parent
	 *
	 * @since   1.0.0
	 */
	public function __construct($options = array())
	{
		$options['table'] = '#__categories';
		$options['extension'] = 'com_jticketing';
		parent::__construct($options);
	}
}
