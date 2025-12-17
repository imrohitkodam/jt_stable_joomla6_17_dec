<?php
/**
 * @package     JTicketing
 * @subpackage  com_jticketing
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die();

use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\Factory;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

/**
 * Supports an HTML select list of courses
 *
 * @since  1.0.0
 */
class JFormFieldVenuelist extends ListField
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	1.6
	 */
	protected $type = 'venuelist';

	/**
	 * Fiedd to decide if options are being loaded externally and from xml
	 *
	 * @var		integer
	 * @since	2.2
	 */
	protected $loadExternally = 0;

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return	array		An array of JHtml options.
	 *
	 * @since   11.4
	 */
	protected function getOptions()
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$filter = InputFilter::getInstance();

		// Select the required fields from the table.
		$query->select('l.id, l.name');
		$query->from('`#__jticketing_venues` AS l');
		$query->order($db->escape('l.name ASC'));

		$db->setQuery($query);

		// Get all countries.
		$allUsers = $db->loadObjectList();

		$options = array();
		$options[] = HTMLHelper::_('select.option', '0', Text::_('COM_JTICKETING_FORM_VENUE_DEFAULT_OPTION'));

		foreach ($allUsers as $u)
		{
			$name      = $filter->clean($u->name, 'string');
			$options[] = HTMLHelper::_('select.option', $u->id, $name);
		}

		if (!$this->loadExternally)
		{
			// Merge any additional options in the XML definition.
			$options = array_merge(parent::getOptions(), $options);
		}

		return $options;
	}

	/**
	 * Method to get a list of options for a list input externally and not from xml.
	 *
	 * @return	array		An array of JHtml options.
	 *
	 * @since   2.2
	 */
	public function getOptionsExternally()
	{
		$this->loadExternally = 1;

		return $this->getOptions();
	}
}
