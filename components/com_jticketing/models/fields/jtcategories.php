<?php
/**
 * @package     JTicketing
 * @subpackage  com_jticketing
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/**
 * getting html list of categories
 *
 * @package     JTicketing
 * @subpackage  component
 * @since       1.0
 */
class JFormFieldJtcategories extends ListField
{
	/**
	 * The form field type.
	 *
	 * @var   string
	 * @since 1.6
	 */
	protected $type = 'jtcategories';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return array  An array of JHtml options.
	 *
	 * @since   11.4
	 */
	protected function getOptions()
	{
		// Merge any additional options in the XML definition.
		$extension = 'com_jticketing';

		if ($this->name == 'jform[venue_category]' || $this->name == 'filter[categoryfilter]')
		{
			$extension = 'com_jticketing.venues';
		}

		$jt_options = HTMLHelper::_('category.options', $extension, array('filter.published' => array(1)));
		$options = array();
		$options[] = HTMLHelper::_('select.option', "", Text::_('COM_JTICKETING_SELECT_CATEGORY'));

		$options = array_merge($options, parent::getOptions(), $jt_options);

		return $options;
	}
}
