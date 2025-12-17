<?php
/**
 * @package     JTicketing
 * @subpackage  com_jticketing
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die();
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Form\FormField;

/**
 * Class for cron reminder
 *
 * @package     JTicketing
 * @subpackage  component
 * @since       1.0
 */
class JFormFieldCronreminder extends FormField
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	1.6
	 */
	public $type = 'CronReminder';

	/**
	 * Get cron job url
	 *
	 * @return  html select box
	 *
	 * @since   1.0
	 */
	protected function getInput()
	{
		$cron_masspayment        = '';
		$cron_masspayment        = Route::_(Uri::root() . 'index.php?option=com_jlike&task=remindersCron&tmpl=component');
		$return                  = '<label class="text-break">' . $cron_masspayment . '</label>';

		return $return;
	}
}
