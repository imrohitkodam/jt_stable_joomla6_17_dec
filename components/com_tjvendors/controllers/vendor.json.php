<?php
/**
 * @package     TJVendor
 * @subpackage  com_tjvendors
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2020 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access to this file
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Response\JsonResponse;

/**
 * Vendor Json controller class
 *
 * @since  1.4.0
 */
class TjvendorsControllerVendor extends TjvendorsController
{
	/**
	 * This method loads regions according to selected country
	 * called via jquery ajax
	 *
	 * @return  void
	 */
	public function getRegion()
	{
		$app           = Factory::getApplication();
		$input         = $app->getInput();
		$country       = $input->get('country', 0, 'INT');
		$defaultRegion = array(
			"id"           => '',
			"region"       => Text::_('COM_TJVENDORS_FORM_LIST_SELECT_OPTION'),
			"region_text" => Text::_('COM_TJVENDORS_FORM_LIST_SELECT_OPTION')
		);
		
		$utilitiesObj  = TJVendors::utilities();
		$regions       = $utilitiesObj->getRegions($country);

		if (!empty($regions))
		{
			array_unshift($regions, $defaultRegion);
		}
		else
		{
			$regions[] = $defaultRegion;
		}

		echo new JsonResponse($regions);
		$app->close();
	}

	/**
	 * Loads city according to selected country
	 * called via jquery ajax
	 *
	 * @return  void
	 */
	public function getCity()
	{
		$app         = Factory::getApplication();
		$input       = $app->getInput();
		$country     = $input->get('country', 0, 'INT');
		$defaultCity = array(
			"id"         => '',
			"city"       => Text::_('COM_TJVENDORS_FORM_LIST_SELECT_OPTION'),
			"city_text" => Text::_('COM_TJVENDORS_FORM_LIST_SELECT_OPTION')
		);

		// Use helper file function
		$utilitiesObj  = TJVendors::utilities();
		$city          = $utilitiesObj->getCities($country);

		if (!empty($city))
		{
			array_unshift($city, $defaultCity);
			$otherCity = array("id" => 'other', "city" => Text::_('COM_TJVENDORS_VENDOR_OTHER_CITY_OPTION'), "city_text" => 'other');
			array_push($city, $otherCity);
		}
		else
		{
			$city[] = $defaultCity;
		}

		echo new JsonResponse($city);
		$app->close();
	}
}
