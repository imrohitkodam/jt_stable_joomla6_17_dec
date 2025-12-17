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
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Component\ComponentHelper;

require_once JPATH_ADMINISTRATOR . '/components/com_jticketing'. '/controller.php';

/**
 * Makepayment controller class.
 *
 * @since  3.2
 */
class JticketingControllermasspayment extends jticketingController
{
	/**
	 *Function to perform mass pay
	 *
	 * @return  void
	 *
	 * @since  3.0
	 */
	public function performmasspay()
	{
		$com_params = ComponentHelper::getParams('com_jticketing');
		$siteadmin_comm_per = $com_params->get('siteadmin_comm_per');
		$private_key_cronjob = $com_params->get('private_key_cronjob');

		$input = Factory::getApplication()->getInput();
		$pkey = $input->get('pkey', '');

		if ($pkey != $private_key_cronjob)
		{
			echo Text::_('SECRET_KEY_ERROR');

			return false;
		}

		if ($siteadmin_comm_per == 0)
		{
			echo '<b>' . Text::_('COMMISSION_ZERO_ERROR') . '</b>';

			return false;
		}

		$model = $this->getModel('masspayment');

		$msg = $model->performmasspay();
		echo $msg;
	}
}
