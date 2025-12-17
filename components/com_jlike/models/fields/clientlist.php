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
use Joomla\CMS\Factory;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Form\Field\ListField;

/**
 * getting html list of categories
 *
 * @package     Jlike
 * @subpackage  component
 * @since       1.0
 */
class JFormFieldClientlist extends ListField
{
	/**
	 * The form field type.
	 *
	 * @var   string
	 * @since 1.6
	 */
	protected $type = 'clientlist';

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
	 * @return	array		An array of HTMLHelper options.
	 *
	 * @since   11.4
	 */
	protected function getOptions()
	{
		// Get all the content type from the classification file in the array
		$jlikeContentArray = parse_ini_file(JPATH_SITE . "/components/com_jlike/classification.ini");
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$filter = InputFilter::getInstance();

		// Select the required fields from the table.
		$query->select('distinct(jc.element)');
		$query->from('`#__jlike_content` AS jc');

		$db->setQuery($query);

		// Get all countries.
		$allClient = $db->loadObjectList();
		$options = array();

		foreach ($allClient as $client)
		{
			$element      = $filter->clean($client->element, 'string');
			$options[] = HTMLHelper::_('select.option', $client->element, $jlikeContentArray[$element]);
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
	 * @return	array		An array of HTMLHelper options.
	 *
	 * @since   2.2
	 */
	public function getOptionsExternally()
	{
		$this->loadExternally = 1;

		return $this->getOptions();
	}
}
