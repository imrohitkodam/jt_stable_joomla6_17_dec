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
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;

/**
 * Tjucm type field
 *
 * @since  3.0.0
 */
class JFormFieldTjucmtype extends ListField
{
	/**
	 * The form field type.
	 *
	 * @var   string
	 * @since  3.0.0
	 */
	protected $type = 'tjucmtype';

	/**
	 * Fiedd to decide if options are being loaded externally and from xml
	 *
	 * @var	integer
	 * @since	3.0.0
	 */
	protected $loadExternally = 0;

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return	array		An array of HTMLHelper options.
	 *
	 * @since  3.0.0
	 */
	protected function getOptions()
	{
		$options = array();
		$isCompInstalled = ComponentHelper::isEnabled('com_tjucm', true);

		if ($isCompInstalled)
		{
			// Get all the ucm types
			$db = Factory::getDbo();
			$query = $db->getQuery(true);

			// Select the required fields from the table.
			$query->select('id, title');
			$query->from('`#__tj_ucm_types` AS ut');
			$query->order($db->quoteName('ut.id') . ' ASC');

			$db->setQuery($query);

			// Get all Types.
			$ucmTypes = $db->loadObjectList();

			foreach ($ucmTypes as $ucmType)
			{
				$options[] = HTMLHelper::_('select.option', $ucmType->id, $ucmType->title);
			}

			if (!$this->loadExternally)
			{
				// Merge any additional options in the XML definition.
				$options = array_merge(parent::getOptions(), $options);
			}
		}

		return $options;
	}

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return	array		An array of HTMLHelper options.
	 *
	 * @since  3.0.0
	 */
	public function getOptionsExternally()
	{
		$this->loadExternally = 1;

		return $this->getOptions();
	}
}
