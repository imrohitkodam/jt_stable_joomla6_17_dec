<?php
/**
 * @package     JTicketing
 * @subpackage  com_jticketing
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Form\Field\ListField;

require_once JPATH_SITE . '/components/com_jticketing/includes/jticketing.php';

FormHelper::loadFieldClass('list');

/**
 * Attendee fields list field
 *
 * @since  2.7.0
 */
class JFormFieldAttendeefields extends ListField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  2.7.0
	 */
	protected $type = 'attendeefields';

	public $layout = "joomla.form.field.list-fancy-select";

		/**
	 * Method to get a list of options for a list input.
	 *
	 * @return array An array of JHtml options.
	 *
	 * @since   2.1
	 */
	protected function getOptions()
	{
		$attendeeFieldsModel = JT::model('attendeefields');

		// Get the core field and tjfields of attendee.
		$attendeeFieldsData = $attendeeFieldsModel->extraFieldslabel();

		$options = array();

		// Check if attendee fields are not empty.
		if (!empty($attendeeFieldsData))
		{
			// Iterate through all the results
			foreach ($attendeeFieldsData as $field)
			{
				$options[] = HTMLHelper::_('select.option', Text::_($field->label), Text::_($field->label));
			}

			// Joomla 6: JVERSION check removed
		if (false) // Legacy >= '4.0.0')
			{
				$this->class = 'form-select required';
			}
			else
			{
				$this->class = 'inputbox required';
			}

			return $options;
		}
	}
}
