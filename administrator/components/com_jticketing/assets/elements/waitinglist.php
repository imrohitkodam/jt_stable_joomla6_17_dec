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

use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Component\ComponentHelper;

if (file_exists(JPATH_LIBRARIES . '/techjoomla/tjmoney/tjmoney.php')) { require_once JPATH_LIBRARIES . '/techjoomla/tjmoney/tjmoney.php'; }

FormHelper::loadFieldClass('list');

/**
 * JFormFieldWaitinglist class
 *
 * @package     JTicketing
 * @subpackage  component
 * @since       2.2
 */

class JFormFieldWaitinglist extends ListField
{
	/**
	 * The form field type.
	 *
	 * @var   string
	 * @since 2.2
	 */
	protected $type = 'waitinglist';

	/**
	 * Fiedd to decide if options are being loaded externally and from xml
	 *
	 * @var   integer
	 * @since 2.2
	 */
	protected $loadExternally = 0;

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return array An array of JHtml options.
	 *
	 * @since   2.2
	 */
	protected function getOptions()
	{
		$params = ComponentHelper::getParams('com_jticketing');

		$classroomTraining = $params->get('enable_self_enrollment', '', 'INT');

		$options   = array();
		$options[] = HTMLHelper::_('select.option', 'E-commerce', Text::_('COM_JTICKETING_E-COMMERCE'));

		if ($classroomTraining == 1)
		{
			$options[] = HTMLHelper::_('select.option', 'classroom_training', Text::_('COM_JTICKETING_CLASSROOMTRAINING'));

			$options[] = HTMLHelper::_('select.option', 'both', Text::_('COM_JTICKETING_BOTH_ENABLE'));
		}

		$options[] = HTMLHelper::_('select.option', 'none', Text::_('COM_JTICKETING_NONE'));

		return $options;
	}
}
