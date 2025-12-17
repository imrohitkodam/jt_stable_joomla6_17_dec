<?php
/**
 * @version     1.5
 * @package     com_jticketing
 * @copyright   Copyright (C) 2014. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Techjoomla <extensions@techjoomla.com> - http://techjoomla.com
 */

defined('JPATH_BASE') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;

/**
 * Supports an HTML select list of categories
 */
class JFormFieldCreatedby extends FormField
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	1.6
	 */
	protected $type = 'createdby';

	/**
	 * Method to get the field input markup.
	 *
	 * @return	string	The field input markup.
	 * @since	1.6
	 */
	protected function getInput()
	{
		// Initialize variables.
		$html = array();

		//Load user
		$user_id = $this->value;
		if ($user_id) {
			$user = Factory::getUser($user_id);
		} else {
			$user = Factory::getUser();
			$html[] = '<input type="hidden" name="'.$this->name.'" value="'.$user->id.'" />';
		}
		$html[] = "<div>".$user->name." (".$user->username.")</div>";

		return implode($html);
	}
}