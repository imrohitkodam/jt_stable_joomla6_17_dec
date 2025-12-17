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
use Joomla\CMS\Component\ComponentHelper;

/**
 * Class to display cron
 *
 * @package     JTicketing
 * @subpackage  component
 * @since       2.2
 */
class JFormFieldCron extends FormField
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 * @since  1.0
	 */
	public $type = 'cron';

	/**
	 * Method to get the field input markup. @TODO: Add access check.
	 *
	 * @since  2.2.1
	 *
	 * @return   string  The field input markup
	 */
	protected function getInput()
	{
		switch ($this->name)
		{
			case 'jform[private_key_cronjob]' :
			case 'jform[attendancecron_key]' :
			case 'jform[pkey_for_pending_email]':
			case 'jform[waitlistcron_key]':
			case 'jform[neweventnotify_key]':
			case 'jform[feedbackmail_key]':

				return $this->getCronKey(
					$this->name,
					$this->value,
					$this->element,
					isset($this->options['control']) ? $this->options['control'] : ''
				);

				break;

			case 'jform[cronjoburl]' :
			case 'jform[waitinglistclear]' :
			case 'jform[onlineattendance]':
			case 'jform[cronjoburl_pending_emails]':
			case 'jform[neweventnotify_cron]':
			case 'jform[feedbackmail_cron_url]':

				return $this->getCronUrl(
					$this->name,
					$this->value,
					$this->element,
					isset($this->options['control']) ? $this->options['control'] : ''
				);

				break;
		}
	}

	/**
	 * Return cron key
	 *
	 * @param   string  $name          name of field
	 * @param   mixed   $value         value of field
	 * @param   string  $node          node of field
	 * @param   string  $control_name  controller name
	 *
	 * @since  2.2.1
	 *
	 * @return  string                 return html
	 */
	protected function getCronKey($name, $value, $node, $control_name)
	{
		// Generate randome string

		if (empty($value))
		{
			$length       = 10;
			$characters   = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
			$randomString = '';

			for ($i = 0; $i < $length; $i++)
			{
				$randomString .= $characters[rand(0, strlen($characters) - 1)];
			}

			return "<input type='text' class='form-control' name='$name' value=" . $randomString . ">";
		}

		return "<input type='text' class='form-control' name='$name' value=" . $value . "></label>";
	}

	/**
	 * Return cron url
	 *
	 * @param   string  $name          name of field
	 * @param   mixed   $value         value of field
	 * @param   string  $node          node of field
	 * @param   string  $control_name  controller name
	 *
	 * @since  2.2.1
	 *
	 * @return  string                 return html
	 */
	protected function getCronUrl($name, $value, $node, $control_name)
	{
		$params = ComponentHelper::getParams('com_jticketing');

		switch ($name)
		{
			case 'jform[waitinglistclear]':
				$private_key_cronjob = $params->get('waitlistcron_key');

				$cron_masspayment = Route::_(
							Uri::root() . 'index.php?option=com_jticketing&task=waitinglist.processWaitlistQueue&pkey='
							. $private_key_cronjob
						);
				$return	= '<label class="text-break">' . $cron_masspayment . '</label>';

				break;

			case 'jform[cronjoburl]':
				$private_key_cronjob = $params->get('private_key_cronjob');

				$cron_masspayment = Route::_(
								Uri::root() . 'index.php?option=com_jticketing&task=masspayment.performmasspay&pkey='
								. $private_key_cronjob
							);
				$return	= '<label class="text-break">' . $cron_masspayment . '</label>';

				break;

			case 'jform[onlineattendance]':
				$private_key_cronjob = $params->get('attendancecron_key');

				$cron_masspayment = Route::_(
								Uri::root() . 'index.php?option=com_jticketing&task=attendees.MarkAttendance&pkey='
								. $private_key_cronjob
							);
				$return	= '<label class="text-break">' . $cron_masspayment . '</label>';

				break;

			case 'jform[cronjoburl_pending_emails]':
				$private_key_cronjob = $params->get('pkey_for_pending_email');

				$cron_masspayment = Route::_(
								Uri::root() . 'index.php?option=com_jticketing&task=orders.sendPendingTicketEmails&pkey='
								. $private_key_cronjob
							);
				$return	= '<label class="text-break">' . $cron_masspayment . '</label>';

				break;
			case 'jform[neweventnotify_cron]':
				$private_key_cronjob = $params->get('neweventnotify_key');

				$cron_url = Route::_(
					Uri::root() . 'index.php?option=com_jticketing&task=eventform.sendEventEmails&pkey='
					. $private_key_cronjob
				);

				$return = '<label class="text-break">' . $cron_url . '</label>';
				break;
			case 'jform[feedbackmail_cron_url]':
				$private_key_cronjob = $params->get('feedbackmail_key');
				$cron_url = Route::_(
					Uri::root() . 'index.php?option=com_jticketing&task=eventform.sendFeedbackEmails&pkey='
					. $private_key_cronjob
				);
				$return = '<label class="text-break">' . $cron_url . '</label>';
				break;
		}

		return $return;
	}
}
