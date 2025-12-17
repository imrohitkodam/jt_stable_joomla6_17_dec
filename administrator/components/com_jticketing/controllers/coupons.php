<?php
/**
 * @package     JTicketing
 * @subpackage  com_jticketing
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;
// Joomla 6: JLoader removed - use autoloading
use Joomla\CMS\Factory;

// Joomla 6: JLoader removed - use require_once
$jticketlistPath = JPATH_ADMINISTRATOR . '/components/com_jticketing/controllers/jticketlist.php';
if (file_exists($jticketlistPath))
{
	require_once $jticketlistPath;
}

/**
 * Coupons list controller class.
 *
 * @since  1.6
 */
class JticketingControllerCoupons extends JTicketingControllerJticketlist
{
	/**
	 * Proxy for getModel.
	 *
	 * @param   boolean  $name    If true, the view output will be cached
	 * @param   boolean  $prefix  If true, the view output will be cached
	 * @param   array    $config  An array of safe url parameters and their variable types, for valid values see {@link
	 *
	 * @return  object|boolean The model
	 *
	 * @since  1.6
	 */
	public function getModel($name = 'coupon', $prefix = 'JticketingModel', $config = array())
	{
		return parent::getModel($name, $prefix, array('ignore_request' => true));
	}

	/**
	 * Method to fetch vendor specific events
	 *
	 * @return  string
	 *
	 * @since   2.4.0
	 */
	public function getVendorSpecificEvents()
	{
		$input    = Factory::getApplication()->getInput();
		$vendorId = $input->get('vendorId');
		$eventsList = array();

		if ($vendorId)
		{
			$eventsModel = JT::model('events', array('ignore_request' => true));
			$eventsList = $eventsModel->getVendorSpecificEvents($vendorId);
		}
		else
		{
			$eventsList = array("error" => 1);
		}

		echo json_encode($eventsList);
		jexit();
	}
}
