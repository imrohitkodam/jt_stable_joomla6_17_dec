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
class TjHouseKeepingUpdatePaymentTemplate extends TjModelHouseKeeping
{
	public $title       = "Update E-Mail template for invoice notification to event buyer";

	public $description = "Update E-mail template for invoice notification to event buyer. Display the fee, total ticket amount separately";

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
		$replacementTags = `[{"name":"event.title","description":"Name of event"},{"name":"event.organizer","description":"Event creator name"},{"name":"event.organizer_detail","description":"Event Creator details"},{"name":"order.orderid_with_prefix","description":"Order Id"},{"name":"order.newStatus","description":"Status of order"},{"name":"order.cdate","description":"Order date"},{"name":"order.firstname","description":"Buyer first name"},{"name":"order.lastname","description":"Buyer last name"},{"name":"order.phone","description":"Buyer phone number"},{"name":"order.user_email","description":"Buyer email id"},{"name":"order.address","description":"Buyer address"},{"name":"order.city","description":"Buyer city"},{"name":"order.zipcode","description":"Buyer zipcode"},{"name":"order.state","description":"Buyer state"},{"name":"order.country","description":"Buyer country"},{"name":"order.taxPercent","description":"Tax percent on order"},{"name":"order.amountwithFee","description":"Amount including Fee"},{"name":"order.subTotal","description":"Sub Total"},{"name":"order.platform_fee","description":"Total Fee"},{"name":"order.taxAmount","description":"Tax amount"},{"name":"order.coupon","description":"Coupon code"},{"name":"order.discount","description":"Coupon discount"},{"name":"order.total","description":"Order total price"},{"name":"order.business_name","description":"Buyer Business name"},{"name":"order.vat_number","description":"Order vat number"},{"name":"ticket.TICKET_INFO","description":"TICKET_INFO contain all ticket type information. TICKET_INFO is a language constant which have two langauge constant TICKET_HEAD and TICKET_TYPES. You can accordingly change your HTML from langauge file. This tag can only be used in email template"},{"name":"order.url","description":"Order Invoice Url"}]`;

		try
		{
			$flag = 0;
			// To update vendor in payout entries
			$client      = 'com_jticketing';
			$key      = 'payment';
			$title      = 'Payment invoice notification to Event Buyer';

			$db = Factory::getDbo();
			$query = $db->getQuery(true);
			$query->update($db->quoteName('#__tj_notification_templates'))
				->set($db->quoteName('replacement_tags') . ' = ' . $db->quote($replacementTags))
				->where($db->quoteName('client') . ' = ' . $db->quote($client))
				->where($db->quoteName('key') . ' = ' . $db->quote($key))
				->where($db->quoteName('title') . ' = ' . $db->quote($title));
			$db->setQuery($query);
			$db->execute();

			$result['status']  = true;
			$result['message'] = "Migration completed successfully.";
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
