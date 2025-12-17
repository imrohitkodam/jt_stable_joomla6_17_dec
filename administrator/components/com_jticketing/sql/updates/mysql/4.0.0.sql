--
-- Update table structure for table `#__jticketing_attendees`
--

ALTER TABLE `#__jticketing_attendees` CHANGE `enrollment_id` `enrollment_id` varchar(100) NOT NULL DEFAULT '' COMMENT 'Enrollment id with prefix';
ALTER TABLE `#__jticketing_attendees` CHANGE `owner_id` `owner_id` int(11) NOT NULL DEFAULT 0 COMMENT 'user_id of jticketing_order table';
ALTER TABLE `#__jticketing_attendees` CHANGE `owner_email` `owner_email` varchar(100) NOT NULL DEFAULT '' COMMENT 'buyer email for guest checkout';
ALTER TABLE `#__jticketing_attendees` CHANGE `status` `status` varchar(2) NOT NULL DEFAULT '' COMMENT 'A = Appoved, R = Rejected and P = pending';
ALTER TABLE `#__jticketing_attendees` CHANGE `event_id` `event_id` int(255) NOT NULL DEFAULT 0 COMMENT 'event id is xref id of integration_xref table';
ALTER TABLE `#__jticketing_attendees` CHANGE `ticket_type_id` `ticket_type_id` int(255) NOT NULL DEFAULT 0 COMMENT 'Ticket type id';
ALTER TABLE `#__jticketing_attendees` CHANGE `params` `params` text DEFAULT NULL;

-- --------------------------------------------------------

--
-- Update table structure for table `#__jticketing_attendee_fields`
--

ALTER TABLE `#__jticketing_attendee_fields` CHANGE `eventid` `eventid` int(11) NOT NULL DEFAULT 0 COMMENT 'id of integration xref table';
ALTER TABLE `#__jticketing_attendee_fields` CHANGE `placeholder` `placeholder` text DEFAULT NULL;
ALTER TABLE `#__jticketing_attendee_fields` CHANGE `type` `type` varchar(255) NOT NULL DEFAULT '' COMMENT 'This is type of field like radio,selectbox,text,hidden';
ALTER TABLE `#__jticketing_attendee_fields` CHANGE `label` `label` varchar(255) NOT NULL DEFAULT '';
ALTER TABLE `#__jticketing_attendee_fields` CHANGE `required` `required` int(11) NOT NULL DEFAULT 0;
ALTER TABLE `#__jticketing_attendee_fields` CHANGE `validation_class` `validation_class` varchar(500) NOT NULL DEFAULT '';
ALTER TABLE `#__jticketing_attendee_fields` CHANGE `js_function` `js_function` varchar(255) NOT NULL DEFAULT '' COMMENT 'This is javascript function to call';
ALTER TABLE `#__jticketing_attendee_fields` CHANGE `state` `state` int(11) NOT NULL DEFAULT 0 COMMENT '1-published 0-not published';
ALTER TABLE `#__jticketing_attendee_fields` CHANGE `core` `core` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'There are some core fields like first name,last name,email,phone no';
ALTER TABLE `#__jticketing_attendee_fields` CHANGE `min` `min` int(10) NOT NULL DEFAULT 0;
ALTER TABLE `#__jticketing_attendee_fields` CHANGE `max` `max` int(10) NOT NULL DEFAULT 0;
ALTER TABLE `#__jticketing_attendee_fields` CHANGE `name` `name` varchar(255) NOT NULL DEFAULT '';
ALTER TABLE `#__jticketing_attendee_fields` CHANGE `tips` `tips` varchar(255) NOT NULL DEFAULT '';
ALTER TABLE `#__jticketing_attendee_fields` CHANGE `searchable` `searchable` int(3) NOT NULL DEFAULT 0;
ALTER TABLE `#__jticketing_attendee_fields` CHANGE `registration` `registration` tinyint(1) NOT NULL DEFAULT 0;
ALTER TABLE `#__jticketing_attendee_fields` CHANGE `options` `options` text DEFAULT NULL;
ALTER TABLE `#__jticketing_attendee_fields` CHANGE `default_selected_option` `default_selected_option` text DEFAULT NULL;
ALTER TABLE `#__jticketing_attendee_fields` CHANGE `field_code` `field_code` varchar(255) NOT NULL DEFAULT '';
ALTER TABLE `#__jticketing_attendee_fields` CHANGE `show_on_view` `show_on_view` int(11) NOT NULL DEFAULT 0 COMMENT 'This is name of option, view and layout name to be given';
ALTER TABLE `#__jticketing_attendee_fields` CHANGE `ordering` `ordering` int(11) NOT NULL DEFAULT 0;

-- --------------------------------------------------------

--
-- Update table structure for table `#__jticketing_attendee_field_values`
--

ALTER TABLE `#__jticketing_attendee_field_values` CHANGE `attendee_id` `attendee_id` int(11) NOT NULL DEFAULT 0 COMMENT 'primary key of Jticketing_attendees table';
ALTER TABLE `#__jticketing_attendee_field_values` CHANGE `field_id` `field_id` int(11) NOT NULL DEFAULT 0;
ALTER TABLE `#__jticketing_attendee_field_values` CHANGE `field_value` `field_value` text DEFAULT NULL;
ALTER TABLE `#__jticketing_attendee_field_values` CHANGE `field_source` `field_source` varchar(250) NOT NULL DEFAULT '' COMMENT 'We are using two types of field manager.  One source is jticketing_attendee_fields and  tjfields_fields  so values of this fields should be com_jticketing or com_tjfields.com_jticketig.ticket';

-- --------------------------------------------------------

--
-- Update table structure for table `#__jticketing_balance_order_items`
--

ALTER TABLE `#__jticketing_balance_order_items` CHANGE `order_id` `order_id` int(15) NOT NULL DEFAULT 0;
ALTER TABLE `#__jticketing_balance_order_items` CHANGE `type_id` `type_id` int(15) NOT NULL DEFAULT 0;
ALTER TABLE `#__jticketing_balance_order_items` CHANGE `ticketcount` `ticketcount` int(11) NOT NULL DEFAULT 0;
ALTER TABLE `#__jticketing_balance_order_items` CHANGE `ticket_price` `ticket_price` float(10,2) NOT NULL DEFAULT 0;
ALTER TABLE `#__jticketing_balance_order_items` CHANGE `amount_paid` `amount_paid` float(10,2) NOT NULL DEFAULT 0;
ALTER TABLE `#__jticketing_balance_order_items` CHANGE `attribute_amount` `attribute_amount` float(10,2) NOT NULL DEFAULT 0;
ALTER TABLE `#__jticketing_balance_order_items` CHANGE `coupon_discount` `coupon_discount` float(13,2) NOT NULL DEFAULT 0;
ALTER TABLE `#__jticketing_balance_order_items` CHANGE `payment_status` `payment_status` varchar(255) NOT NULL DEFAULT '';
ALTER TABLE `#__jticketing_balance_order_items` CHANGE `name` `name` text DEFAULT NULL;
ALTER TABLE `#__jticketing_balance_order_items` CHANGE `email` `email` varchar(700) NOT NULL DEFAULT '';

-- --------------------------------------------------------

--
-- Update table structure for table `#__jticketing_checkindetails`
--

ALTER TABLE `#__jticketing_checkindetails` CHANGE `ticketid` `ticketid` int(11) DEFAULT 0;
ALTER TABLE `#__jticketing_checkindetails` CHANGE `eventid` `eventid` int(11) DEFAULT 0;
ALTER TABLE `#__jticketing_checkindetails` CHANGE `attendee_id` `attendee_id` int(11) DEFAULT 0;
ALTER TABLE `#__jticketing_checkindetails` CHANGE `attendee_name` `attendee_name` text DEFAULT NULL;
ALTER TABLE `#__jticketing_checkindetails` CHANGE `attendee_email` `attendee_email` text DEFAULT NULL;
ALTER TABLE `#__jticketing_checkindetails` CHANGE `checkintime` `checkintime` datetime DEFAULT NULL;
ALTER TABLE `#__jticketing_checkindetails` CHANGE `checkouttime` `checkouttime` datetime DEFAULT NULL;
ALTER TABLE `#__jticketing_checkindetails` CHANGE `spent_time` `spent_time` time DEFAULT NULL;
ALTER TABLE `#__jticketing_checkindetails` CHANGE `checkin` `checkin` int(11) NOT NULL DEFAULT 0;

-- --------------------------------------------------------

--
-- Update table structure for table `#__jticketing_coupon`
--

ALTER TABLE `#__jticketing_coupon` CHANGE `state` `state` tinyint(1) NOT NULL DEFAULT 0;
ALTER TABLE `#__jticketing_coupon` CHANGE `ordering` `ordering` int(11) NOT NULL DEFAULT 0;
ALTER TABLE `#__jticketing_coupon` CHANGE `checked_out` `checked_out` int(11) NOT NULL DEFAULT 0;
ALTER TABLE `#__jticketing_coupon` CHANGE `checked_out_time` `checked_out_time` datetime DEFAULT NULL;
ALTER TABLE `#__jticketing_coupon` CHANGE `name` `name` varchar(100) NOT NULL DEFAULT '';
ALTER TABLE `#__jticketing_coupon` CHANGE `code` `code` varchar(100) NOT NULL DEFAULT '';
ALTER TABLE `#__jticketing_coupon` CHANGE `value` `value` FLOAT(13,2) NOT NULL DEFAULT 0;
ALTER TABLE `#__jticketing_coupon` CHANGE `val_type` `val_type` tinyint(4) NOT NULL DEFAULT 0;
ALTER TABLE `#__jticketing_coupon` CHANGE `limit` `limit` int(11) NOT NULL DEFAULT 0;
ALTER TABLE `#__jticketing_coupon` CHANGE `max_per_user` `max_per_user` int(11) NOT NULL DEFAULT 0;
ALTER TABLE `#__jticketing_coupon` CHANGE `description` `description` text DEFAULT NULL;
ALTER TABLE `#__jticketing_coupon` CHANGE `params` `params` text DEFAULT NULL;
ALTER TABLE `#__jticketing_coupon` CHANGE `valid_from` `valid_from` datetime DEFAULT NULL;
ALTER TABLE `#__jticketing_coupon` CHANGE `valid_to` `valid_to` datetime DEFAULT NULL;
ALTER TABLE `#__jticketing_coupon` CHANGE `created_by` `created_by` int(11) NOT NULL DEFAULT 0;
ALTER TABLE `#__jticketing_coupon` CHANGE `used` `used` int(11) NOT NULL DEFAULT 0;
ALTER TABLE `#__jticketing_coupon` CHANGE `event_ids` `event_ids` varchar(255) NOT NULL DEFAULT '';
ALTER TABLE `#__jticketing_coupon` CHANGE `vendor_id` `vendor_id` int(11) NOT NULL DEFAULT 0;

-- --------------------------------------------------------

--
-- Update table structure for table `#__jticketing_events`
--

ALTER TABLE `#__jticketing_events` CHANGE `created_by` `created_by` int(11) NOT NULL DEFAULT 0;
ALTER TABLE `#__jticketing_events` CHANGE `title` `title` varchar(255) NOT NULL DEFAULT '';
ALTER TABLE `#__jticketing_events` CHANGE `alias` `alias` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '';
ALTER TABLE `#__jticketing_events` CHANGE `catid` `catid` int(11) NOT NULL DEFAULT 0;
ALTER TABLE `#__jticketing_events` CHANGE `ideal_time` `ideal_time` int(11) NOT NULL DEFAULT 0;
ALTER TABLE `#__jticketing_events` CHANGE `venue` `venue` int(11) NOT NULL DEFAULT 0;
ALTER TABLE `#__jticketing_events` CHANGE `short_description` `short_description` text DEFAULT NULL;
ALTER TABLE `#__jticketing_events` CHANGE `long_description` `long_description` text DEFAULT NULL;
ALTER TABLE `#__jticketing_events` CHANGE `startdate` `startdate` datetime DEFAULT NULL;
ALTER TABLE `#__jticketing_events` CHANGE `enddate` `enddate` datetime DEFAULT NULL;
ALTER TABLE `#__jticketing_events` CHANGE `booking_start_date` `booking_start_date` datetime DEFAULT NULL;
ALTER TABLE `#__jticketing_events` CHANGE `booking_end_date` `booking_end_date` datetime DEFAULT NULL;
ALTER TABLE `#__jticketing_events` CHANGE `location` `location` text DEFAULT NULL;
ALTER TABLE `#__jticketing_events` CHANGE `latitude` `latitude` float NOT NULL DEFAULT 0;
ALTER TABLE `#__jticketing_events` CHANGE `longitude` `longitude` float NOT NULL DEFAULT 0;
ALTER TABLE `#__jticketing_events` CHANGE `permission` `permission` tinyint(4) unsigned NOT NULL DEFAULT 0 COMMENT '0 - Open (Anyone can mark attendence), 1 - Private (Only invited can mark attendence)';
ALTER TABLE `#__jticketing_events` CHANGE `image` `image` text DEFAULT NULL;
ALTER TABLE `#__jticketing_events` CHANGE `created` `created` datetime DEFAULT NULL;
ALTER TABLE `#__jticketing_events` CHANGE `modified` `modified` datetime DEFAULT NULL;
ALTER TABLE `#__jticketing_events` CHANGE `state` `state` tinyint(3) NOT NULL DEFAULT 0;
ALTER TABLE `#__jticketing_events` CHANGE `allow_view_attendee` `allow_view_attendee` tinyint(3) NOT NULL DEFAULT 0;
ALTER TABLE `#__jticketing_events` CHANGE `access` `access` int(11) NOT NULL DEFAULT 0;
ALTER TABLE `#__jticketing_events` CHANGE `featured` `featured` tinyint(1) NOT NULL DEFAULT 0;
ALTER TABLE `#__jticketing_events` CHANGE `online_events` `online_events` tinyint(4) NOT NULL DEFAULT 0;
ALTER TABLE `#__jticketing_events` CHANGE `ordering` `ordering` int(11) NOT NULL DEFAULT 0;
ALTER TABLE `#__jticketing_events` CHANGE `checked_out` `checked_out` int(11) NOT NULL DEFAULT 0;
ALTER TABLE `#__jticketing_events` CHANGE `checked_out_time` `checked_out_time` datetime DEFAULT NULL;
ALTER TABLE `#__jticketing_events` CHANGE `params` `params` text DEFAULT NULL;
ALTER TABLE `#__jticketing_events` CHANGE `meta_data` `meta_data` text DEFAULT NULL;
ALTER TABLE `#__jticketing_events` CHANGE `meta_desc` `meta_desc` text DEFAULT NULL;

-- --------------------------------------------------------



-- --------------------------------------------------------

--
-- Update table structure for table `#__jticketing_integration_xref`
--

ALTER TABLE `#__jticketing_integration_xref` CHANGE `vendor_id` `vendor_id` int(11) NOT NULL DEFAULT 0;
ALTER TABLE `#__jticketing_integration_xref` CHANGE `eventid` `eventid` int(11) NOT NULL DEFAULT 0;
ALTER TABLE `#__jticketing_integration_xref` CHANGE `source` `source` varchar(100) NOT NULL DEFAULT '';
ALTER TABLE `#__jticketing_integration_xref` CHANGE `paypal_email` `paypal_email` varchar(100) NOT NULL DEFAULT '';
ALTER TABLE `#__jticketing_integration_xref` CHANGE `checkin` `checkin` int(11) NOT NULL DEFAULT 0;
ALTER TABLE `#__jticketing_integration_xref` CHANGE `userid` `userid` int(11) NOT NULL DEFAULT 0;
ALTER TABLE `#__jticketing_integration_xref` CHANGE `cron_status` `cron_status` int(11) NOT NULL DEFAULT 0;
ALTER TABLE `#__jticketing_integration_xref` CHANGE `cron_date` `cron_date` datetime DEFAULT NULL;

-- --------------------------------------------------------

--
-- Update table structure for table `#__jticketing_order`
--

ALTER TABLE `#__jticketing_order` CHANGE `order_id` `order_id` varchar(23) NOT NULL DEFAULT '';
ALTER TABLE `#__jticketing_order` CHANGE `parent_order_id` `parent_order_id` int(11) NOT NULL DEFAULT 0;
ALTER TABLE `#__jticketing_order` CHANGE `event_details_id` `event_details_id` int(11) NOT NULL DEFAULT 0;
ALTER TABLE `#__jticketing_order` CHANGE `name` `name` varchar(255) NOT NULL DEFAULT '';
ALTER TABLE `#__jticketing_order` CHANGE `email` `email` varchar(100) NOT NULL DEFAULT '';
ALTER TABLE `#__jticketing_order` CHANGE `user_id` `user_id` int(11) NOT NULL DEFAULT 0;
ALTER TABLE `#__jticketing_order` CHANGE `cdate` `cdate` datetime DEFAULT NULL;
ALTER TABLE `#__jticketing_order` CHANGE `mdate` `mdate` datetime DEFAULT NULL;
ALTER TABLE `#__jticketing_order` CHANGE `transaction_id` `transaction_id` varchar(100) NOT NULL DEFAULT '';
ALTER TABLE `#__jticketing_order` CHANGE `payee_id` `payee_id` varchar(100) NOT NULL DEFAULT '';
ALTER TABLE `#__jticketing_order` CHANGE `order_amount` `order_amount` float(10,2) NOT NULL DEFAULT 0;
ALTER TABLE `#__jticketing_order` CHANGE `original_amount` `original_amount` float(10,2) NOT NULL DEFAULT 0 COMMENT 'original amount with no fee applied';
ALTER TABLE `#__jticketing_order` CHANGE `amount` `amount` float(10,2) NOT NULL DEFAULT 0 COMMENT 'amount after applying fee';
ALTER TABLE `#__jticketing_order` CHANGE `coupon_code` `coupon_code` varchar(100) NOT NULL DEFAULT '';
ALTER TABLE `#__jticketing_order` CHANGE `fee` `fee` float(10,2) NOT NULL DEFAULT 0 COMMENT 'site admin commision(processing fee)';
ALTER TABLE `#__jticketing_order` CHANGE `status` `status` varchar(100) NOT NULL DEFAULT '';
ALTER TABLE `#__jticketing_order` CHANGE `processor` `processor` varchar(100) NOT NULL DEFAULT '';
ALTER TABLE `#__jticketing_order` CHANGE `ip_address` `ip_address` varchar(50) NOT NULL DEFAULT '';
ALTER TABLE `#__jticketing_order` CHANGE `ticketscount` `ticketscount` int(11) NOT NULL DEFAULT 0;
ALTER TABLE `#__jticketing_order` CHANGE `extra` `extra` text DEFAULT NULL;
ALTER TABLE `#__jticketing_order` CHANGE `order_tax` `order_tax` float(10,2) NOT NULL DEFAULT 0;
ALTER TABLE `#__jticketing_order` CHANGE `order_tax_details` `order_tax_details` text DEFAULT NULL;
ALTER TABLE `#__jticketing_order` CHANGE `coupon_discount` `coupon_discount` float(10,2) NOT NULL DEFAULT 0;
ALTER TABLE `#__jticketing_order` CHANGE `coupon_discount_details` `coupon_discount_details` text DEFAULT NULL;
ALTER TABLE `#__jticketing_order` CHANGE `ticket_email_sent` `ticket_email_sent` tinyint(2) NOT NULL DEFAULT 0;
ALTER TABLE `#__jticketing_order` CHANGE `customer_note` `customer_note` text DEFAULT NULL;

-- --------------------------------------------------------

--
-- Update table structure for table `#__jticketing_order_items`
--

ALTER TABLE `#__jticketing_order_items` CHANGE `order_id` `order_id` int(15) NOT NULL DEFAULT 0;
ALTER TABLE `#__jticketing_order_items` CHANGE `type_id` `type_id` int(15) NOT NULL DEFAULT 0;
ALTER TABLE `#__jticketing_order_items` CHANGE `attendee_id` `attendee_id` int(11) NOT NULL DEFAULT 0 COMMENT 'id of #__jticketing_attendees table';
ALTER TABLE `#__jticketing_order_items` CHANGE `ticketcount` `ticketcount` int(11) NOT NULL DEFAULT 0;
ALTER TABLE `#__jticketing_order_items` CHANGE `ticket_price` `ticket_price` float(10,2) NOT NULL DEFAULT 0;
ALTER TABLE `#__jticketing_order_items` CHANGE `amount_paid` `amount_paid` float(10,2) NOT NULL DEFAULT 0;
ALTER TABLE `#__jticketing_order_items` CHANGE `fee_amt` `fee_amt` float(10,2) NOT NULL DEFAULT 0;
ALTER TABLE `#__jticketing_order_items` CHANGE `fee_params` `fee_params` text DEFAULT NULL;
ALTER TABLE `#__jticketing_order_items` CHANGE `attribute_amount` `attribute_amount` float(10,2) NOT NULL DEFAULT 0;
ALTER TABLE `#__jticketing_order_items` CHANGE `coupon_discount` `coupon_discount` float(10,2) NOT NULL DEFAULT 0;
ALTER TABLE `#__jticketing_order_items` CHANGE `payment_status` `payment_status` varchar(255) NOT NULL DEFAULT 0;
ALTER TABLE `#__jticketing_order_items` CHANGE `name` `name` text DEFAULT NULL;
ALTER TABLE `#__jticketing_order_items` CHANGE `email` `email` varchar(700) NOT NULL DEFAULT '';
ALTER TABLE `#__jticketing_order_items` CHANGE `comment` `comment` text DEFAULT NULL;

-- --------------------------------------------------------


-- --------------------------------------------------------

--
-- Update table structure for table `#__jticketing_ticket_payouts`
--



CREATE TABLE IF NOT EXISTS `#__jticketing_ticket_payouts` (
	`id` int(15) NOT NULL AUTO_INCREMENT,
	`user_id` int(11) NOT NULL DEFAULT 0,
	`payee_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
	`date` datetime DEFAULT NULL,
	`transction_id` varchar(15) NOT NULL DEFAULT '',
	`payee_id` varchar(55) NOT NULL DEFAULT '',
	`amount` float(10,2) NOT NULL DEFAULT 0,
	`status` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
	`ip_address` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
	`type` text DEFAULT NULL,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1;


--
ALTER TABLE `#__jticketing_ticket_payouts` CHANGE `user_id` `user_id` int(11) NOT NULL DEFAULT 0;
ALTER TABLE `#__jticketing_ticket_payouts` CHANGE `payee_name` `payee_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '';
ALTER TABLE `#__jticketing_ticket_payouts` CHANGE `date` `date` datetime DEFAULT NULL;
ALTER TABLE `#__jticketing_ticket_payouts` CHANGE `transction_id` `transction_id` varchar(15) NOT NULL DEFAULT '';
ALTER TABLE `#__jticketing_ticket_payouts` CHANGE `payee_id` `payee_id` varchar(55) NOT NULL DEFAULT '';
ALTER TABLE `#__jticketing_ticket_payouts` CHANGE `amount` `amount` float(10,2) NOT NULL DEFAULT 0;
ALTER TABLE `#__jticketing_ticket_payouts` CHANGE `status` `status` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '';
ALTER TABLE `#__jticketing_ticket_payouts` CHANGE `ip_address` `ip_address` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '';
ALTER TABLE `#__jticketing_ticket_payouts` CHANGE `type` `type` text DEFAULT NULL;

-- --------------------------------------------------------

--
-- Update table structure for table `#__jticketing_types`
--

ALTER TABLE `#__jticketing_types` CHANGE `title` `title` varchar(500) NOT NULL DEFAULT '';
ALTER TABLE `#__jticketing_types` CHANGE `desc` `desc` varchar(500) NOT NULL DEFAULT '';
ALTER TABLE `#__jticketing_types` CHANGE `ticket_enddate` `ticket_enddate` datetime DEFAULT NULL;
ALTER TABLE `#__jticketing_types` CHANGE `price` `price` float(10,2) NOT NULL DEFAULT 0;
ALTER TABLE `#__jticketing_types` CHANGE `deposit_fee` `deposit_fee` float(10,2) NOT NULL DEFAULT 0;
ALTER TABLE `#__jticketing_types` CHANGE `available` `available` int(10) NOT NULL DEFAULT 0;
ALTER TABLE `#__jticketing_types` CHANGE `count` `count` int(10) NOT NULL DEFAULT 0;
ALTER TABLE `#__jticketing_types` CHANGE `unlimited_seats` `unlimited_seats` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1=unlimited 0=limited';
ALTER TABLE `#__jticketing_types` CHANGE `eventid` `eventid` int(10) NOT NULL DEFAULT 0;
ALTER TABLE `#__jticketing_types` CHANGE `max_limit_ticket` `max_limit_ticket` INT(11) NOT NULL DEFAULT 0;
ALTER TABLE `#__jticketing_types` CHANGE `access` `access` tinyint(1) NOT NULL DEFAULT 0;
ALTER TABLE `#__jticketing_types` CHANGE `state` `state` tinyint(4) NOT NULL DEFAULT 0;

-- --------------------------------------------------------

--
-- Update table structure for table `#__jticketing_users`
--

ALTER TABLE `#__jticketing_users` CHANGE `user_id` `user_id` int(11) NOT NULL DEFAULT 0;
ALTER TABLE `#__jticketing_users` CHANGE `order_id` `order_id` int(11) NOT NULL DEFAULT 0;
ALTER TABLE `#__jticketing_users` CHANGE `user_email` `user_email` varchar(255) NOT NULL DEFAULT '';
ALTER TABLE `#__jticketing_users` CHANGE `address_type` `address_type` varchar(11) NOT NULL DEFAULT '';
ALTER TABLE `#__jticketing_users` CHANGE `firstname` `firstname` varchar(250) NOT NULL DEFAULT '';
ALTER TABLE `#__jticketing_users` CHANGE `lastname` `lastname` varchar(250) NOT NULL DEFAULT '';
ALTER TABLE `#__jticketing_users` CHANGE `registration_type` `registration_type` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1=business 0=company';
ALTER TABLE `#__jticketing_users` CHANGE `business_name` `business_name` varchar(100) NOT NULL DEFAULT '';
ALTER TABLE `#__jticketing_users` CHANGE `vat_number` `vat_number` varchar(250) NOT NULL DEFAULT '';
ALTER TABLE `#__jticketing_users` CHANGE `tax_exempt` `tax_exempt` tinyint(4) NOT NULL DEFAULT 0;
ALTER TABLE `#__jticketing_users` CHANGE `country_code` `country_code` varchar(250) NOT NULL DEFAULT '';
ALTER TABLE `#__jticketing_users` CHANGE `address` `address` varchar(255) NOT NULL DEFAULT '';
ALTER TABLE `#__jticketing_users` CHANGE `city` `city` varchar(250) NOT NULL DEFAULT '';
ALTER TABLE `#__jticketing_users` CHANGE `state_code` `state_code` varchar(250) NOT NULL DEFAULT '';
ALTER TABLE `#__jticketing_users` CHANGE `zipcode` `zipcode` varchar(255) NOT NULL DEFAULT '';
ALTER TABLE `#__jticketing_users` CHANGE `phone` `phone` varchar(50) NOT NULL DEFAULT '';
ALTER TABLE `#__jticketing_users` CHANGE `approved` `approved` tinyint(1) NOT NULL DEFAULT 0;
ALTER TABLE `#__jticketing_users` CHANGE `country_mobile_code` `country_mobile_code` int(11) NOT NULL DEFAULT 0;

--
-- Update table structure for table `#__jticketing_reminder_types`
--

ALTER TABLE `#__jticketing_reminder_types` CHANGE `asset_id` `asset_id` int(10) unsigned NOT NULL DEFAULT 0;
ALTER TABLE `#__jticketing_reminder_types` CHANGE `ordering` `ordering` int(11) NOT NULL DEFAULT 0;
ALTER TABLE `#__jticketing_reminder_types` CHANGE `state` `state` tinyint(1) NOT NULL DEFAULT 0;
ALTER TABLE `#__jticketing_reminder_types` CHANGE `checked_out` `checked_out` int(11) NOT NULL DEFAULT 0;
ALTER TABLE `#__jticketing_reminder_types` CHANGE `checked_out_time` `checked_out_time` datetime DEFAULT NULL;
ALTER TABLE `#__jticketing_reminder_types` CHANGE `created_by` `created_by` int(11) NOT NULL DEFAULT 0;
ALTER TABLE `#__jticketing_reminder_types` CHANGE `title` `title` varchar(500) NOT NULL DEFAULT '';
ALTER TABLE `#__jticketing_reminder_types` CHANGE `description` `description` text DEFAULT NULL;
ALTER TABLE `#__jticketing_reminder_types` CHANGE `days` `days` int(11) NOT NULL DEFAULT 0;
ALTER TABLE `#__jticketing_reminder_types` CHANGE `hours` `hours` int(11) NOT NULL DEFAULT 0;
ALTER TABLE `#__jticketing_reminder_types` CHANGE `minute` `minute` int(11) NOT NULL DEFAULT 0;
ALTER TABLE `#__jticketing_reminder_types` CHANGE `subject` `subject` varchar(600) NOT NULL DEFAULT '';
ALTER TABLE `#__jticketing_reminder_types` CHANGE `sms` `sms` varchar(255) NOT NULL DEFAULT '';
ALTER TABLE `#__jticketing_reminder_types` CHANGE `email` `email` varchar(255) NOT NULL DEFAULT '';
ALTER TABLE `#__jticketing_reminder_types` CHANGE `css` `css` text DEFAULT NULL;
ALTER TABLE `#__jticketing_reminder_types` CHANGE `email_template` `email_template` text DEFAULT NULL;
ALTER TABLE `#__jticketing_reminder_types` CHANGE `sms_template` `sms_template` text DEFAULT NULL;
ALTER TABLE `#__jticketing_reminder_types` CHANGE `event_id` `event_id` int(11) NOT NULL DEFAULT 0;
ALTER TABLE `#__jticketing_reminder_types` CHANGE `replytoemail` `replytoemail` varchar(255) NOT NULL DEFAULT '';
ALTER TABLE `#__jticketing_reminder_types` CHANGE `reminder_params` `reminder_params` text DEFAULT NULL;

--
-- Update table structure for table `#__jticketing_queue`
--

ALTER TABLE `#__jticketing_queue` CHANGE `order_id` `order_id` int(11) NOT NULL DEFAULT 0 COMMENT 'id of #__jticketing_order table';
ALTER TABLE `#__jticketing_queue` CHANGE `subject` `subject` text DEFAULT NULL;
ALTER TABLE `#__jticketing_queue` CHANGE `content` `content` text DEFAULT NULL;
ALTER TABLE `#__jticketing_queue` CHANGE `reminder_type_id` `reminder_type_id` int(11) NOT NULL DEFAULT 0;
ALTER TABLE `#__jticketing_queue` CHANGE `reminder_type` `reminder_type` varchar(500) NOT NULL DEFAULT '';
ALTER TABLE `#__jticketing_queue` CHANGE `date_to_sent` `date_to_sent` datetime DEFAULT NULL;
ALTER TABLE `#__jticketing_queue` CHANGE `email` `email` text DEFAULT NULL;
ALTER TABLE `#__jticketing_queue` CHANGE `mobile_no` `mobile_no` bigint(20) NOT NULL DEFAULT 0;
ALTER TABLE `#__jticketing_queue` CHANGE `sent` `sent` int(11) NOT NULL DEFAULT 0 COMMENT '0=not sent 1=sent 2=expired 3=delayed so it can be sent when cron runs later';
ALTER TABLE `#__jticketing_queue` CHANGE `sent_date` `sent_date` datetime DEFAULT NULL;
ALTER TABLE `#__jticketing_queue` CHANGE `user_id` `user_id` int(11) NOT NULL DEFAULT 0;
ALTER TABLE `#__jticketing_queue` CHANGE `event_id` `event_id` int(11) NOT NULL DEFAULT 0;

--
-- Update table structure for table `#__Stripe_xref`
--

ALTER TABLE `#__Stripe_xref` CHANGE `user_id` `user_id` int(11) NOT NULL DEFAULT 0;
ALTER TABLE `#__Stripe_xref` CHANGE `client_id` `client_id` int(11) NOT NULL DEFAULT 0;
ALTER TABLE `#__Stripe_xref` CHANGE `client` `client` varchar(20) NOT NULL DEFAULT '';
ALTER TABLE `#__Stripe_xref` CHANGE `params` `params` text DEFAULT NULL;

ALTER TABLE `#__tjlms_user_xref` CHANGE `user_id` `user_id` int(11) NOT NULL DEFAULT 0;
ALTER TABLE `#__tjlms_user_xref` CHANGE `join_date` `join_date` date DEFAULT NULL;

--
-- Update table structure for table `rhdq7_jticketing_venues`
--

ALTER TABLE `#__jticketing_venues` CHANGE `vendor_id` `vendor_id` int(11) NOT NULL DEFAULT 0;
ALTER TABLE `#__jticketing_venues` CHANGE `asset_id` `asset_id` int(10) unsigned NOT NULL DEFAULT 0;
ALTER TABLE `#__jticketing_venues` CHANGE `ordering` `ordering` int(11) NOT NULL DEFAULT 0;
ALTER TABLE `#__jticketing_venues` CHANGE `state` `state` tinyint(1) NOT NULL DEFAULT 0;
ALTER TABLE `#__jticketing_venues` CHANGE `checked_out` `checked_out` int(11) NOT NULL DEFAULT 0;
ALTER TABLE `#__jticketing_venues` CHANGE `checked_out_time` `checked_out_time` datetime DEFAULT NULL;
ALTER TABLE `#__jticketing_venues` CHANGE `created_by` `created_by` int(11) NOT NULL DEFAULT 0;
ALTER TABLE `#__jticketing_venues` CHANGE `modified_by` `modified_by` int(11) NOT NULL DEFAULT 0;
ALTER TABLE `#__jticketing_venues` CHANGE `name` `name` varchar(255) NOT NULL DEFAULT '';
ALTER TABLE `#__jticketing_venues` CHANGE `alias` `alias` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '';
ALTER TABLE `#__jticketing_venues` CHANGE `venue_category` `venue_category` int(11) NOT NULL DEFAULT 0;
ALTER TABLE `#__jticketing_venues` CHANGE `online` `online` int(3) NOT NULL DEFAULT 0;
ALTER TABLE `#__jticketing_venues` CHANGE `online_provider` `online_provider` varchar(255) NOT NULL DEFAULT '' COLLATE utf8_bin;
ALTER TABLE `#__jticketing_venues` CHANGE `country` `country` int(11) NOT NULL DEFAULT 0;
ALTER TABLE `#__jticketing_venues` CHANGE `state_id` `state_id` int(1) NOT NULL DEFAULT 0;
ALTER TABLE `#__jticketing_venues` CHANGE `city` `city` varchar(255) NOT NULL DEFAULT '' COLLATE utf8_bin;
ALTER TABLE `#__jticketing_venues` CHANGE `zipcode` `zipcode` varchar(255) NOT NULL DEFAULT '' COLLATE utf8_bin;
ALTER TABLE `#__jticketing_venues` CHANGE `address` `address` varchar(255) NOT NULL DEFAULT '' COLLATE utf8_bin;
ALTER TABLE `#__jticketing_venues` CHANGE `description` `description` text DEFAULT NULL;
ALTER TABLE `#__jticketing_venues` CHANGE `longitude` `longitude` float NOT NULL DEFAULT 0;
ALTER TABLE `#__jticketing_venues` CHANGE `latitude` `latitude` float NOT NULL DEFAULT 0;
ALTER TABLE `#__jticketing_venues` CHANGE `privacy` `privacy` int(11) NOT NULL DEFAULT 0;
ALTER TABLE `#__jticketing_venues` CHANGE `params` `params` text DEFAULT NULL;

--
-- Update table structure for table `#__techjoomlaAPI_users`
--

ALTER TABLE `#__techjoomlaAPI_users` CHANGE `api` `api` varchar(200) NOT NULL DEFAULT '';
ALTER TABLE `#__techjoomlaAPI_users` CHANGE `token` `token` text DEFAULT NULL;
ALTER TABLE `#__techjoomlaAPI_users` CHANGE `user_id` `user_id` int(11) NOT NULL DEFAULT 0;
ALTER TABLE `#__techjoomlaAPI_users` CHANGE `client` `client` varchar(200) NOT NULL DEFAULT '';

--
-- Update table structure for table `#__tj_media_files`
--

ALTER TABLE `#__tj_media_files` CHANGE `title` `title` varchar(250) NOT NULL DEFAULT '' COLLATE utf8_bin;
ALTER TABLE `#__tj_media_files` CHANGE `type` `type` varchar(250) NOT NULL DEFAULT '' COLLATE utf8_bin;
ALTER TABLE `#__tj_media_files` CHANGE `path` `path` varchar(250) NOT NULL DEFAULT '' COLLATE utf8_bin;
ALTER TABLE `#__tj_media_files` CHANGE `state` `state` tinyint(1) NOT NULL DEFAULT 0;
ALTER TABLE `#__tj_media_files` CHANGE `source` `source` varchar(250) NOT NULL DEFAULT '' COLLATE utf8_bin;
ALTER TABLE `#__tj_media_files` CHANGE `original_filename` `original_filename` varchar(250) NOT NULL DEFAULT '' COLLATE utf8_bin;
ALTER TABLE `#__tj_media_files` CHANGE `size` `size` int(11) NOT NULL DEFAULT 0;
ALTER TABLE `#__tj_media_files` CHANGE `storage` `storage` varchar(250) NOT NULL DEFAULT '' COLLATE utf8_bin;
ALTER TABLE `#__tj_media_files` CHANGE `created_by` `created_by` int(11) NOT NULL DEFAULT 0;
ALTER TABLE `#__tj_media_files` CHANGE `access` `access` tinyint(1) NOT NULL DEFAULT 0;
ALTER TABLE `#__tj_media_files` CHANGE `created_date` `created_date` datetime DEFAULT NULL;
ALTER TABLE `#__tj_media_files` CHANGE `params` `params` text DEFAULT NULL;

--
-- Update table structure for table `#__tj_media_files_xref`
--

ALTER TABLE `#__tj_media_files_xref` CHANGE `media_id` `media_id` int(11) NOT NULL DEFAULT 0;
ALTER TABLE `#__tj_media_files_xref` CHANGE `client_id` `client_id` int(11) NOT NULL DEFAULT 0;
ALTER TABLE `#__tj_media_files_xref` CHANGE `client` `client` varchar(250) NOT NULL DEFAULT '' COLLATE utf8_bin;
ALTER TABLE `#__tj_media_files_xref` CHANGE `is_gallery` `is_gallery` tinyint(1) NOT NULL DEFAULT 0;

-- --------------------------------------------------------

--
-- Update table structure for table `#__jticketing_waiting_list`
--

ALTER TABLE `#__jticketing_waiting_list` CHANGE `user_id` `user_id` int(11) NOT NULL DEFAULT 0;
ALTER TABLE `#__jticketing_waiting_list` CHANGE `event_id` `event_id` int(11) NOT NULL DEFAULT 0 COMMENT 'event id is xref id of integration_xref table';
ALTER TABLE `#__jticketing_waiting_list` CHANGE `behaviour` `behaviour` varchar(100) NOT NULL DEFAULT '' COMMENT 'Classroom Training and E-commerce';
ALTER TABLE `#__jticketing_waiting_list` CHANGE `status` `status` varchar(2) NOT NULL DEFAULT '' COMMENT 'WL = Waitlist; C = Clear and  CA = Canceled';
ALTER TABLE `#__jticketing_waiting_list` CHANGE `created_date` `created_date` datetime DEFAULT NULL;
