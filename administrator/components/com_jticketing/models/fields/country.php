<?php
/**
 * @version    SVN: <svn_id>
 * @package    JGive
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;

/**
 * render plugin selection of type online event
 *
 * @since  1.0
 */
class JFormFieldCountry extends FormField
{
	protected $type = 'Country';

	/**
	 * Method to get the field input markup.
	 *
	 * @return	string	The field input markup.
	 *
	 * @since	1.6
	 */
	protected function getInput()
	{
		return self::fetchElement($this->name, $this->value, $this->element, $this->options['control']);
	}

	/**
	 * Returns html element select plugin
	 *
	 * @param   string  $name          Name of control
	 * @param   string  $value         Value of control
	 * @param   string  &$node         Node name
	 * @param   array   $control_name  Control Name
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	protected function fetchElement($name, $value, &$node, $control_name)
	{
		$country = $this->getCountry();
		$default_country = '';
		$default_country = ((isset($this->userbill->country_code)) ? $this->userbill->country_code : '');

		$options = array();
		$options[] = HTMLHelper::_('select.option', "", Text::_('COM_JTICKETING_COUNTRY_TOOLTIP'));

		foreach ($country as $key => $value)
		{
			$options[] = HTMLHelper::_('select.option', $value['id'], $value['country']);
		}

		$script = "
			function generateState(countryId)
			{
				var country = countryId;

				if(countryId == undefined)
				{
					var country = techjoomla.jQuery('#country').val();
				}

				if(country == undefined || country == '')
				{
					return false;
				}

				techjoomla.jQuery.ajax({
					url: 'index.php?option=com_jticketing&task=venue.getRegionListFromCountryID&country='+country+'&tmpl=component',
					type: 'GET',
					dataType: 'json',
					success: function(data)
					{
						generateoption(data);
					}
				});
			}

			function generateoption(data)
			{
				var country=techjoomla.jQuery('#country').val();
				var options, index, select, option;

				select = techjoomla.jQuery('#state_id');
				default_opt = 'Select State';

				select.find('option').remove().end();

				selected='selected=\"selected\"';
				var op='<option '+selected+' value=\">'  +default_opt+   '</option>'     ;
				techjoomla.jQuery('#state_id').append(op);

				if(data)
				{
					options = data.options;
					for (index = 0; index < data.length; ++index)
					{
						var name=data[index]['id'];
						selected=\"\";
						";
						
						if ($this->form->getValue('state_id'))
						{ 
							$script .= "
						if(name== " . $this->form->getValue('state_id') . ")
							selected='selected=\"selected\"';";
						}
						
						$script .=  
						"var op='<option '+selected+' value=\"'+data[index]['id']+'\">'  +data[index]['region']+   '</option>';

						//if(countryId=='country')
						{
							techjoomla.jQuery('#state_id').append(op);
						}

					}	 // end of for

					jQuery('#state_id').trigger('liszt:updated');
					jQuery('#state_id').trigger('liszt:updated');
				}
			}";

			if ($this->value)
			{
				$script .= "generateState(" . $this->value . ");";
			}

		Factory::getDocument()->addScriptDeclaration($script);
			
		return HTMLHelper::_('select.genericlist',
		$options, 'jform[country]',
		'class="input-style lms_select bill"  required="required" aria-invalid="false" size="1" onchange=\'generateState()\' ',
		'value', 'text', $this->value, 'country');
	}

	/**
	 * To Fetch country list from Db
	 *
	 * @return  list of countries
	 *
	 * @since  1.0.0
	 */
	public function getCountry()
	{
		$TjGeoHelper = JPATH_ROOT . '/components/com_tjfields/helpers/geo.php';

		if (!class_exists('TjGeoHelper'))
		{
			JLoader::register('TjGeoHelper', $TjGeoHelper);
			JLoader::load('TjGeoHelper');
		}

		$tjGeoHelper = new TjGeoHelper;

		return $tjGeoHelper->getCountryList();
	}
}
