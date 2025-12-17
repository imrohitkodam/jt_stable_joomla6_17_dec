<?php
/**
 * @package    Com_Tjcertificate
 * @copyright  Copyright (C) 2009 -2020 Techjoomla, Tekdi Web Solutions . All rights reserved.
 * @license    GNU GPLv2 <http://www.gnu.org/licenses/old-licenses/gpl-2.0.html>
 * @link       http://www.techjoomla.com
 */

defined('JPATH_BASE') or die;

use Joomla\CMS\Form\FormField;
use Joomla\CMS\Factory;

/**
 * Supports an HTML select list of categories
 *
 * @since  __DEPLOY_VERSION__
 */
class JFormFieldCreatedby extends FormField
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	__DEPLOY_VERSION__
	 */
	protected $type = 'createdby';

	/**
	 * Method to get the field input markup.
	 *
	 * @return	string	The field input markup.
	 * 
	 * @since	1.6
	 */
	protected function getInput()
	{
		// Initialize variables.
		$html = array();

		// Load user
		$user_id = $this->value;

		if ($user_id)
		{
			$user = Factory::getUser($user_id);
		}
		else
		{
			$user = Factory::getUser();
			$html[] = '<input type="hidden" name="' . $this->name . '" value="' . $user->id . '" />';
		}

		$html[] = "<div>{$user->name} ({$user->username})</div>";

		return implode($html);
	}
}
