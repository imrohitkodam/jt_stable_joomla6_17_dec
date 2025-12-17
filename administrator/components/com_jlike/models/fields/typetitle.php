<?php
/**
 * @package     JLike
 * @subpackage  com_jlike
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('JPATH_BASE') or die;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Form\Field\ListField;

/**
 * Supports an HTML select list of categories
 *
 * @since  1.6
 */
class JFormFieldTypeTitle extends ListField
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 *
	 * @since  1.6
	 */
	protected $type = 'typetitle';

	/**
	 * Method to get the field input markup.
	 *
	 * @return	array	The field input markup.
	 *
	 * @since	1.6
	 */
	public function getOptions()
	{
		// Initialize variables.
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('type_title, path_type_id')->from($db->quoteName('#__jlike_path_type'));

		$db->setQuery($query);
		$titles = $db->loadAssocList();

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $titles);
		$new = array();
		$new['0'] = Text::_('COM_JLIKE_PATH_TYPE_SELECT');

		foreach ($options as $key => $val)
		{
			$new[$val['path_type_id']] = $val['type_title'];
		}

		return $new;
	}
}
