<?php
/**
 * @package     TJVendors
 * @subpackage  com_tjvendors
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

/**
 * Tjvendors Listhelper.
 *
 * @since  1.6
 */
abstract class HTMLHelperListhelper
{
	// Change by Deepa
	/* public static function toggle($value = 0, $view, $field, $i)*/

	/**
	 * Methods toggle.
	 *
	 * @param   String   $view   Value
	 * @param   String   $field  Value
	 * @param   Integer  $i      Value
	 * @param   Integer  $value  Value
	 *
	 * @return void
	 */
	public static function toggle($view, $field, $i, $value = 0)
	{
		$states = array(
			0 => array('icon-remove', Text::_('Toggle'), 'inactive btn-danger'),
			1 => array('icon-checkmark', Text::_('Toggle'), 'active btn-success'),
		);

		$state  = \Joomla\Utilities\ArrayHelper::getValue($states, (int) $value, $states[0]);
		$text   = '<span aria-hidden="true" class="' . $state[0] . '"></span>';
		$html   = '<a href="#" class="btn btn-micro ' . $state[2] . '"';
		$html  .= 'onclick="return toggleField(\'cb' . $i . '\',\'' . $view . '.toggle\',\'' .
		$field . '\')" title="' . Text::_($state[1]) . '">' . $text . '</a>';

		return $html;
	}
}
