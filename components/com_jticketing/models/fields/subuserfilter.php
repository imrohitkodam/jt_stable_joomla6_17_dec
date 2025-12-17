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
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

/**
 * JFormFieldSubuserfilter helper.
 *
 * @since  2.3.3
 */
class JFormFieldSubuserfilter extends ListField
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	2.3.3
	 */
	protected $type = 'subuserfilter';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return	string   An array of JHtml options.
	 *
	 * @since   2.3.3
	 */
	protected function getInput()
	{
		$hasUsers = $this->getOptions();

		if (empty($hasUsers))
		{
			return null;
		}

		$input = parent::getInput();

		// Only 1 option then hide dropdown by jlike and JTicketing classes
		if (count($hasUsers) == 1)
		{
			$input = '<div class="jlike_display_none jticketig_display_none">' . $input . '</div>';
		}

		return $input;
	}

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   2.3.3
	 */
	protected function getOptions()
	{
		if (file_exists(JPATH_ADMINISTRATOR . '/components/com_jticketing/helpers/jticketing.php')) { require_once JPATH_ADMINISTRATOR . '/components/com_jticketing/helpers/jticketing.php'; }
		$hasUsers = JticketingHelper::getSubusers();

		// If not manager, we do not need to show dropdown
		if (!$hasUsers)
		{
			return null;
		}

		$options 	= array();
		$options[] 	= HTMLHelper::_('select.option', '', Text::_('COM_JTICKETING_FILTER_SUBUSERFILTER'));
		$options[] 	= HTMLHelper::_('select.option', 1, Text::_('COM_JTICKETING_FILTER_SUBUSERFILTER_UNDER_ME'));

		return $options;
	}
}
