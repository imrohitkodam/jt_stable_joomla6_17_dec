<?php
/**
 * @package     JTicketing
 * @subpackage  com_jticketing
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;

/**
 * JTicketing Check Payout Entry Migration
 *
 * @since  3.3.4
 */
class TjHouseKeepingCorrectPayoutEntry extends TjModelHouseKeeping
{
	public $title       = "Check Payout Entry";

	public $description = "This checks the payout entries. If any invalid entry found, this will ask user to contact for the correction of those entries.";

	/**
	 * This function will check the payout entry
	 *
	 * @return  array $result
	 *
	 * @since  3.3.5
	 */
	public function migrate()
	{
		$result = array();

		try
		{
			$flag = 0;
			// To update vendor in payout entries
			$db = Factory::getDbo();
			$query = $db->getQuery(true);
			$query->select('*');
			$query->from($db->quoteName('#__tjvendors_passbook'));
			$query->where($db->quoteName('client') . ' = ' . $db->quote('com_jticketing'));
			$db->setQuery($query);

			$resultPayout = $db->loadAssocList();

			foreach ($resultPayout as $payout)
			{
				// Use reference order ID to update the vendor
				$order = JT::order($payout['reference_order_id']);

				if (empty($order->id))
				{
					$order = JT::order()->loadByOrderId($payout['reference_order_id']);
				}

				if ($order->id)
				{
					$event = JT::event()->loadByIntegration($order->event_details_id);
					$vendor_id = $event->vendor_id;
					$eventCreator = $event->getCreator();
					$tjvendorFrontHelper = new tjvendorFrontHelper;
					$getVendorIdByCreator = $tjvendorFrontHelper->checkVendor($eventCreator, 'com_jticketing');

					if ($vendor_id !=  $getVendorIdByCreator)
					{
						http_response_code(500);
						$flag = 1;
						$result['status']  = false;
						$result['message'] = "There are some incorrect Payout Entries found. Please contact Support to correct it.";
						break;
					}
					else
					{
						continue;
					}
				}
			}

			if ($flag == 0)
			{
				$result['status']  = true;
				$result['message'] = "Migration completed successfully.";
			}

		}
		catch (Exception $e)
		{
			http_response_code(500);
			$result['err_code'] = '';
			$result['status']   = false;
			$result['message']  = $e->getMessage();
		}

		return $result;
	}
}
