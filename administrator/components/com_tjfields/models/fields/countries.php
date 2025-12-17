<?php
/**
 * @version    SVN: <svn_id>
 * @package    Tjfields
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
 * Supports an HTML select list of categories
 */
class JFormFieldCountries extends ListField
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	1.6
	 */
	protected $type = 'countries';

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
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$client = Factory::getApplication()->getInput()->get('client', '', 'STRING');

		// Select the required fields from the table.
		$query->select('c.id, c.country, c.country_text');
		$query->from('`#__tj_country` AS c');

		if ($client)
		{
			$query->where('c.' . $client .' = 1');
		}

		$query->order($db->escape('c.ordering ASC'));

		$db->setQuery($query);

		// Get all countries.
		$countries = $db->loadObjectList();

		$options = array();

		// Load lang file for countries
		$lang = Factory::getLanguage();
		$lang->load('tjgeo.countries', JPATH_SITE, null, false, true);

		foreach ($countries as $c)
		{
			if ($lang->hasKey(strtoupper($c->country_text)))
			{
				$c->country = Text::_($c->country_text);
			}

			$options[] = HTMLHelper::_('select.option', $c->id, $c->country);
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
