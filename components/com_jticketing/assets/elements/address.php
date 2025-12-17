<?php
/**
 * @package     JTicketing
 * @subpackage  com_jticketing
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die();
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

if (file_exists(JPATH_ROOT . '/media/techjoomla_strapper/tjstrapper.php'))
{
	require_once JPATH_ROOT . '/media/techjoomla_strapper/tjstrapper.php';
	TjStrapper::loadTjAssets('com_jticketing');
}

/**
 * Field class to render the address field
 *
 * @package     JTicketing
 * @subpackage  com_jticketing
 * @since       1.0.0
 */
class JFormFieldAddress extends FormField
{
	/**
	 * The form field type.
	 *
	 * @var String
	 * @since 1.0.0
	 */
	public $type = 'address';

	/**
	 * The name of the form field.
	 *
	 * @var    string
	 * @since  1.0.0
	 */
	protected $name = 'address';

	/**
	 * Get html of the element
	 *
	 * @return  string
	 *
	 * @since  1.0.0
	 */
	public function getInput()
	{
		$class        = (JVERSION < '4.0.0') ? 'inputbox' : 'form-control';
		$html = '';
		$html .= '<div>';
		$html .= '<input id="jform_address" name="jform[address]" type="text" size="26" label="'
				. Text::_('COM_JTICKETING_FORM_LBL_VENUE_ADDRESS')
				. '" class="' . $class
				. '" description="' . Text::_('COM_JTICKETING_FORM_DESC_VENUE_ADDRESS')
				. '" filter="safehtml" onchange="jtSite.venueForm.getLongitudeLatitude();" value="' . htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '" autocomplete="off">';

		if(Uri::getInstance()->isSsl())
		{
			$html .= '<input id="getlocation"class=" btn btn-small btn-primary mt-3" type="button" onclick="jtSite.venueForm.getCurrentLocation();" value="'
					. Text::_('COM_JTICKETING_CURR_LOCATION') . '" title="' . Text::_('COM_JTICKETING_CURR_LOCATION_TOOLTIP') . '">';
		}

		$html .= '</div>';

		return $html;
	}
}
