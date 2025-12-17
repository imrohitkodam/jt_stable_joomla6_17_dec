<?php
/**
 * @package    JTicketing
 * @author     TechJoomla | <extensions@techjoomla.com>
 * @copyright  Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Form\FormHelper;

FormHelper::loadFieldClass('list');
if (JVERSION < '4.0.0')
{
	HTMLHelper::_('formbehavior.chosen', 'select');
}
/**
 * Supports an HTML select list
 *
 * @since  2.6.1
 */
class JFormFieldRsform extends FormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var   string
	 * @since 2.6.1
	 */
	protected $type = 'rsform';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return	array		An array of JHtml options.
	 *
	 * @since   2.6.1
	 */
	protected function getOptions()
	{
		$input   = Factory::getApplication()->input;
		$eventId = $input->get('id');

		$eventInfo  = JT::event($eventId);
		$rsform     = '';

		if (!empty($eventInfo->params['rsform']->onAfterJtAttendeeCheckin))
		{
			$rsform = $eventInfo->params['rsform']->onAfterJtAttendeeCheckin;
		}

		if (empty($this->value) && !empty($eventId))
		{
			$this->value = $rsform;
		}

		JLoader::register('RSFormProHelper', JPATH_ADMINISTRATOR . '/components/com_rsform/helpers/rsform.php');

		BaseDatabaseModel::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_rsform/models');
		$rsmodel = BaseDatabaseModel::getInstance('Submissions', 'RSFormModel');
		$forms   = $rsmodel->getForms();

		$options = array();

		$options[] = HTMLHelper::_('select.option', '', Text::_("PLG_ONAFTEREVENTCHECKIN_RSFORM_SELECT_FORM"));

		if (!empty($forms))
		{
			foreach ($forms as $form)
			{
				$options[] = HTMLHelper::_('select.option', $form->value, $form->text);
			}
		}

		return $options;
	}

	/**
	 * Method to get a list of options for a list input externally and not from xml.
	 *
	 * @return	array		An array of JHtml options.
	 *
	 * @since   2.6.1
	 */
	public function getOptionsExternally()
	{
		return $this->getOptions();
	}
}
