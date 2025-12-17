<?php

/**
 * @package    Com_Activitystream
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access.
defined('_JEXEC') or die();
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Form\Field\ListField;

/**
 * Supports an HTML select list of courses
 *
 * @since  1.0.1
 */
class ActivitytypeField extends ListField
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	1.0.1
	 */
	protected $type = 'activitytype';

	/**
	 * Fiedd to decide if options are being loaded externally and from xml
	 *
	 * @var		integer
	 * @since	1.0.1
	 */
	protected $loadExternally = 0;

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return	array		An array of HTMLHelper options.
	 *
	 * @since   1.0.1
	 */
	protected function getOptions()
	{
		$db     = Factory::getContainer()->get(\Joomla\Database\DatabaseInterface::class);
		$client = Factory::getApplication()->input->get('client', '', 'STRING');
		$query  = $db->getQuery(true);

		$query->select('distinct l.type')
				->from($db->quoteName('#__tj_activities', 'l'))
				->where($db->quoteName('client') . ' = ' . $db->quote($client));
		$query->order($db->escape('l.type') . ' ASC');
		$db->setQuery($query);

		// Send filter values
		$activityType = $db->loadObjectList();
		$options      = array();
		$options[]    = HTMLHelper::_('select.option', 'all', Text::_('COM_ACTIVITYSTREAM_SEARCH_FILTER'));

		foreach ($activityType as $type)
		{
			$temp        = implode("_", explode('.', $type->type));
			$filterValue = $client . '_activity_type_' . $temp;
			$filterText  = strtoupper($filterValue);
			$options[]   = HTMLHelper::_('select.option', $type->type, Text::_($filterText));
		}

		return $options;
	}
}
