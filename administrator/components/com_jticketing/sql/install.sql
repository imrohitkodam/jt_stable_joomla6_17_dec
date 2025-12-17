-- phpMyAdmin SQL Dump
-- version 3.4.10.1deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jan 07, 2014 at 11:23 AM
-- Server version: 5.5.29
-- PHP Version: 5.3.10-1ubuntu3.6




/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `test_merge_20dec`
--

-- --------------------------------------------------------

-- --------------------------------------------------------

--
-- Table structure for table `#__jticketing_attendees`
--

CREATE TABLE IF NOT EXISTS `#__jticketing_attendees` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `enrollment_id` varchar(100) NOT NULL DEFAULT '' COMMENT 'Enrollment id with prefix',
  `owner_id` int(11) NOT NULL DEFAULT 0 COMMENT 'user_id of jticketing_order table',
  `owner_email` varchar(100) NOT NULL DEFAULT '' COMMENT 'buyer email for guest checkout',
  `status` varchar(2) NOT NULL DEFAULT '' COMMENT 'A = Appoved, R = Rejected and P = pending',
  `event_id` int(255) NOT NULL DEFAULT 0 COMMENT 'event id is xref id of integration_xref table',
  `ticket_type_id` int(255) NOT NULL DEFAULT 0 COMMENT 'Ticket type id',
  `params` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `event_id_idx` (`event_id`),
  KEY `enrollment_id_idx` (`enrollment_id`),
  KEY `multicolumn_idx` (`enrollment_id`,`event_id`,`ticket_type_id`,`status`,`owner_id`,`owner_email`),
  KEY `ticket_type_id_idx` (`ticket_type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1;

-- --------------------------------------------------------

--
-- Table structure for table `#__jticketing_attendee_fields`
--

CREATE TABLE IF NOT EXISTS `#__jticketing_attendee_fields` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `eventid` int(11) NOT NULL DEFAULT 0 COMMENT 'id of integration xref table',
  `placeholder` text DEFAULT NULL,
  `type` varchar(255) NOT NULL DEFAULT '' COMMENT 'This is type of field like radio,selectbox,text,hidden',
  `label` varchar(255) NOT NULL DEFAULT '',
  `required` int(11) NOT NULL DEFAULT 0,
  `validation_class` varchar(500) NOT NULL DEFAULT '',
  `js_function` varchar(255) NOT NULL DEFAULT '' COMMENT 'This is javascript function to call',
  `state` int(11) NOT NULL DEFAULT 0 COMMENT '1-published 0-not published',
  `core` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'There are some core fields like first name,last name,email,phone no',
  `min` int(10) NOT NULL DEFAULT 0,
  `max` int(10) NOT NULL DEFAULT 0,
  `name` varchar(255) NOT NULL DEFAULT '',
  `tips` varchar(255) NOT NULL DEFAULT '',
  `searchable` int(3) NOT NULL DEFAULT 0,
  `registration` tinyint(1) NOT NULL DEFAULT 0,
  `options` text DEFAULT NULL,
  `default_selected_option` text DEFAULT NULL,
  `field_code` varchar(255) NOT NULL DEFAULT '',
  `show_on_view` int(11) NOT NULL DEFAULT 0 COMMENT 'This is name of option, view and layout name to be given',
  `ordering` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `eventid_idx` (`eventid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1;

-- --------------------------------------------------------

--
-- Table structure for table `#__jticketing_attendee_field_values`
--

CREATE TABLE IF NOT EXISTS `#__jticketing_attendee_field_values` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `attendee_id` int(11) NOT NULL DEFAULT 0 COMMENT 'primary key of Jticketing_attendees table',
  `field_id` int(11) NOT NULL DEFAULT 0,
  `field_value` text DEFAULT NULL,
  `field_source` varchar(250) NOT NULL DEFAULT '' COMMENT 'We are using two types of field manager.  One source is jticketing_attendee_fields and  tjfields_fields  so values of this fields should be com_jticketing or com_tjfields.com_jticketig.ticket  ',
  PRIMARY KEY (`id`),
  KEY `attendee_id_idx` (`attendee_id`),
  KEY `field_source_idx` (`field_source`),
  KEY `field_id_idx` (`field_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1;

-- --------------------------------------------------------

--
-- Table structure for table `#__jticketing_balance_order_items`
--

CREATE TABLE IF NOT EXISTS `#__jticketing_balance_order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(15) NOT NULL DEFAULT 0,
  `type_id` int(15) NOT NULL DEFAULT 0,
  `ticketcount` int(11) NOT NULL DEFAULT 0,
  `ticket_price` float(10,2) NOT NULL DEFAULT 0,
  `amount_paid` float(10,2) NOT NULL DEFAULT 0,
  `attribute_amount` float(10,2) NOT NULL DEFAULT 0,
  `coupon_discount` float(13,2) NOT NULL DEFAULT 0,
  `payment_status` varchar(255) NOT NULL DEFAULT '',
  `name` text DEFAULT NULL,
  `email` varchar(700) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1;

-- --------------------------------------------------------

--
-- Table structure for table `#__jticketing_checkindetails`
--
CREATE TABLE IF NOT EXISTS `#__jticketing_checkindetails` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ticketid` int(11) DEFAULT 0,
  `eventid` int(11) DEFAULT 0,
  `attendee_id` int(11) DEFAULT 0,
  `attendee_name` text DEFAULT NULL,
  `attendee_email` text DEFAULT NULL,
  `checkintime` datetime DEFAULT NULL,
  `checkouttime` datetime DEFAULT NULL,
  `spent_time` time DEFAULT NULL,
  `checkin` int(11) NOT NULL DEFAULT 0,
  `r_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `eventid_idx` (`eventid`),
  KEY `attendee_id_idx` (`attendee_id`),
  KEY `checkin_idx` (`checkin`),
  KEY `ticketid_idx` (`ticketid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1;

-- --------------------------------------------------------

--
-- Table structure for table `#__jticketing_coupon`
--

CREATE TABLE IF NOT EXISTS `#__jticketing_coupon` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `state` tinyint(1) NOT NULL DEFAULT 0,
  `ordering` int(11) NOT NULL DEFAULT 0,
  `checked_out` int(11) NOT NULL DEFAULT 0,
  `checked_out_time` datetime DEFAULT NULL,
  `name` varchar(100) NOT NULL DEFAULT '',
  `code` varchar(100) NOT NULL DEFAULT '',
  `value` FLOAT(13,2) NOT NULL DEFAULT 0,
  `val_type` tinyint(4) NOT NULL DEFAULT 0,
  `limit` int(11) NOT NULL DEFAULT 0,
  `max_per_user` int(11) NOT NULL DEFAULT 0,
  `description` text DEFAULT NULL,
  `params` text DEFAULT NULL,
  `valid_from` datetime DEFAULT NULL,
  `valid_to` datetime DEFAULT NULL,
  `created_by` int(11) NOT NULL DEFAULT 0,
  `used` int(11) NOT NULL DEFAULT 0,
  `event_ids` varchar(255) NOT NULL DEFAULT '',
  `vendor_id` int(11) NOT NULL DEFAULT 0,
  `group_discount` tinyint(1) NOT NULL DEFAULT 0,
  `group_discount_tickets` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1;

-- --------------------------------------------------------

--
-- Table structure for table `#__jticketing_events`
--

CREATE TABLE IF NOT EXISTS `#__jticketing_events` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `created_by` int(11) NOT NULL DEFAULT 0,
  `title` varchar(255) NOT NULL DEFAULT '',
  `alias` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `catid` int(11) NOT NULL DEFAULT 0,
  `ideal_time` int(11) NOT NULL DEFAULT 0,
  `venue` int(11) NOT NULL DEFAULT 0,
  `short_description` text DEFAULT NULL,
  `long_description` text DEFAULT NULL,
  `startdate` datetime DEFAULT NULL,
  `enddate` datetime DEFAULT NULL,
  `booking_start_date` datetime DEFAULT NULL,
  `booking_end_date` datetime DEFAULT NULL,
  `location` text DEFAULT NULL,
  `latitude` float NOT NULL DEFAULT 0,
  `longitude` float NOT NULL DEFAULT 0,
  `permission` tinyint(4) unsigned NOT NULL DEFAULT 0 COMMENT '0 - Open (Anyone can mark attendence), 1 - Private (Only invited can mark attendence)',
  `image` text DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  `state` tinyint(3) NOT NULL DEFAULT 0,
  `allow_view_attendee` tinyint(3) NOT NULL DEFAULT 0,
   `access` int(11) NOT NULL DEFAULT 0,
  `featured` tinyint(1) NOT NULL DEFAULT 0,
  `online_events` tinyint(4) NOT NULL DEFAULT 0,
  `ordering` int(11) NOT NULL DEFAULT 0,
  `checked_out` int(11) NOT NULL DEFAULT 0,
  `checked_out_time` datetime DEFAULT NULL,
  `jt_params` text DEFAULT NULL,
  `params` text DEFAULT NULL,
  `meta_data` text DEFAULT NULL,
  `meta_desc` text DEFAULT NULL,
  `recurring_type` VARCHAR(255) NOT NULL DEFAULT 'No_repeat' COMMENT 'No_repeat, Daily, Weekly, Monthly, Yearly',
  `recurring_params` JSON DEFAULT NULL,
  `email_sent` int(1) NOT NULL DEFAULT 0,
  `feedback_mail_sent` int(1) NOT NULL DEFAULT 0,
  `start_number_for_event_level_sequence` varchar(50) NOT NULL DEFAULT '',
   PRIMARY KEY (`id`),
  KEY `title_idx` (`title`),
  KEY `catid_idx` (`catid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1;

-- --------------------------------------------------------



-- --------------------------------------------------------

--
-- Table structure for table `#__jticketing_integration_xref`
--

CREATE TABLE IF NOT EXISTS `#__jticketing_integration_xref` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vendor_id` int(11) NOT NULL DEFAULT 0,
  `eventid` int(11) NOT NULL DEFAULT 0,
  `source` varchar(100) NOT NULL DEFAULT '',
  `paypal_email` varchar(100) NOT NULL DEFAULT '',
  `checkin` int(11) NOT NULL DEFAULT 0,
  `userid` int(11) NOT NULL DEFAULT 0,
  `enable_ticket` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1=Yes 0=No',
  `cron_status` int(11) NOT NULL DEFAULT 0,
  `cron_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1;



-- --------------------------------------------------------

--
-- Table structure for table `#__jticketing_order`
--

CREATE TABLE IF NOT EXISTS `#__jticketing_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` varchar(23) NOT NULL DEFAULT '',
  `parent_order_id` int(11) NOT NULL DEFAULT 0,
  `event_details_id` int(11) NOT NULL DEFAULT 0,
  `name` varchar(255) NOT NULL DEFAULT '',
  `email` varchar(100) NOT NULL DEFAULT '',
  `user_id` int(11) NOT NULL DEFAULT 0,
  `cdate` datetime DEFAULT NULL,
  `mdate` datetime DEFAULT NULL,
  `transaction_id` varchar(100) NOT NULL DEFAULT '',
  `payee_id` varchar(100) NOT NULL DEFAULT '',
  `order_amount` float(10,2) NOT NULL DEFAULT 0,
  `original_amount` float(10,2) NOT NULL DEFAULT 0 COMMENT 'original amount with no fee applied',
  `amount` float(10,2) NOT NULL DEFAULT 0 COMMENT 'amount after applying fee',
  `coupon_code` varchar(100) NOT NULL DEFAULT '',
  `fee` float(10,2) NOT NULL DEFAULT 0 COMMENT 'site admin commision(processing fee)',
  `status` varchar(100) NOT NULL DEFAULT '',
  `processor` varchar(100) NOT NULL DEFAULT '',
  `ip_address` varchar(50) NOT NULL DEFAULT '',
  `ticketscount` int(11) NOT NULL DEFAULT 0,
  `extra` text DEFAULT NULL,
  `order_tax` float(10,2) NOT NULL DEFAULT 0,
  `order_tax_details` text DEFAULT NULL,
  `coupon_discount` float(10,2) NOT NULL DEFAULT 0,
  `coupon_discount_details` text DEFAULT NULL,
  `ticket_email_sent` tinyint(2) NOT NULL DEFAULT 0,
  `customer_note` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `name_idx` (`name`),
  KEY `order_id_idx` (`order_id`),
  KEY `event_details_id_idx` (`event_details_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1;

-- --------------------------------------------------------

--
-- Table structure for table `#__jticketing_order_items`
--

CREATE TABLE IF NOT EXISTS `#__jticketing_order_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(15) NOT NULL DEFAULT 0,
  `type_id` int(15) NOT NULL DEFAULT 0,
  `attendee_id` int(11) NOT NULL DEFAULT 0 COMMENT 'id of #__jticketing_attendees table',
  `ticketcount` int(11) NOT NULL DEFAULT 0,
  `ticket_price` float(10,2) NOT NULL DEFAULT 0,
  `amount_paid` float(10,2) NOT NULL DEFAULT 0,
  `fee_amt` float(10,2) NOT NULL DEFAULT 0,
  `fee_params` text DEFAULT NULL,
  `attribute_amount` float(10,2) NOT NULL DEFAULT 0,
  `coupon_discount` float(10,2) NOT NULL DEFAULT 0,
  `payment_status` varchar(255) NOT NULL DEFAULT 0,
  `name` text DEFAULT NULL,
  `email` varchar(700) NOT NULL DEFAULT '',
  `comment` text DEFAULT NULL,
  `entry_number` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `order_id_idx` (`order_id`),
  KEY `order_attendee_idx` (`order_id`, `attendee_id`),
  KEY `type_id_idx` (`type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1;

-- --------------------------------------------------------


-- --------------------------------------------------------

--
-- Table structure for table `#__jticketing_ticket_payouts`
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

-- --------------------------------------------------------

--
-- Table structure for table `#__jticketing_types`
--

CREATE TABLE IF NOT EXISTS `#__jticketing_types` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `title` varchar(500) NOT NULL DEFAULT '',
  `desc` varchar(500) NOT NULL DEFAULT '',
  `ticket_startdate` datetime DEFAULT NULL,
  `ticket_enddate` datetime DEFAULT NULL,
  `price` float(10,2) NOT NULL DEFAULT 0,
  `deposit_fee` float(10,2) NOT NULL DEFAULT 0,
  `available` int(10) NOT NULL DEFAULT 0,
  `count` int(10) NOT NULL DEFAULT 0,
  `unlimited_seats` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1=unlimited 0=limited',
  `eventid` int(10) NOT NULL DEFAULT 0,
  `max_limit_ticket` INT(11) NOT NULL DEFAULT 0,
  `access` int(10) NOT NULL DEFAULT 0,
  `state` tinyint(4) NOT NULL DEFAULT 0,
  `allow_ticket_level_sequence` tinyint(2) NOT NULL DEFAULT 0,
  `start_number_for_sequence` varchar(50) NOT NULL DEFAULT '',
  `max_ticket_per_order` INT(11) NOT NULL DEFAULT 0 COMMENT '0=unlimited OR max_ticket_per_order as specified',
PRIMARY KEY (`id`),
   KEY `eventid_idx` (`eventid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1;

-- --------------------------------------------------------

--
-- Table structure for table `#__jticketing_users`
--

CREATE TABLE IF NOT EXISTS `#__jticketing_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `order_id` int(11) NOT NULL DEFAULT 0,
  `user_email` varchar(255) NOT NULL DEFAULT '',
  `address_type` varchar(11) NOT NULL DEFAULT '',
  `firstname` varchar(250) NOT NULL DEFAULT '',
  `lastname` varchar(250) NOT NULL DEFAULT '',
  `registration_type` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1=business 0=company',
  `business_name` varchar(100) NOT NULL DEFAULT '',
  `vat_number` varchar(250) NOT NULL DEFAULT '',
  `tax_exempt` tinyint(4) NOT NULL DEFAULT 0,
  `country_code` varchar(250) NOT NULL DEFAULT '',
  `address` varchar(255) NOT NULL DEFAULT '',
  `city` varchar(250) NOT NULL DEFAULT '',
  `state_code` varchar(250) NOT NULL DEFAULT '',
  `zipcode` varchar(255) NOT NULL DEFAULT '',
  `phone` varchar(50) NOT NULL DEFAULT '',
  `approved` tinyint(1) NOT NULL DEFAULT 0,
  `country_mobile_code` int(11) NOT NULL DEFAULT 0,
  `comment` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `order_id_idx` (`order_id`)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1;


--
-- Table structure for table `#__jticketing_reminder_types`
--

CREATE TABLE IF NOT EXISTS `#__jticketing_reminder_types` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `asset_id` int(10) unsigned NOT NULL DEFAULT 0,
  `ordering` int(11) NOT NULL DEFAULT 0,
  `state` tinyint(1) NOT NULL DEFAULT 0,
  `checked_out` int(11) NOT NULL DEFAULT 0,
  `checked_out_time` datetime DEFAULT NULL,
  `created_by` int(11) NOT NULL DEFAULT 0,
  `title` varchar(500) NOT NULL DEFAULT '',
  `description` text DEFAULT NULL,
  `days` int(11) NOT NULL DEFAULT 0,
  `hours` int(11) NOT NULL DEFAULT 0,
  `minute` int(11) NOT NULL DEFAULT 0,
  `subject` varchar(600) NOT NULL DEFAULT '',
  `sms` varchar(255) NOT NULL DEFAULT '',
  `email` varchar(255) NOT NULL DEFAULT '',
  `css` text DEFAULT NULL,
  `email_template` text DEFAULT NULL,
  `sms_template` text DEFAULT NULL,
  `event_id` int(11) NOT NULL DEFAULT 0,
  `replytoemail` varchar(255) NOT NULL DEFAULT '',
  `reminder_params` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1;

--
-- Table structure for table `#__jticketing_queue`
--

CREATE TABLE IF NOT EXISTS `#__jticketing_queue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL DEFAULT 0 COMMENT 'id of #__jticketing_order table',
  `subject` text DEFAULT NULL,
  `content` text DEFAULT NULL,
  `reminder_type_id` int(11) NOT NULL DEFAULT 0,
  `reminder_type` varchar(500) NOT NULL DEFAULT '',
  `date_to_sent` datetime DEFAULT NULL,
  `email` text DEFAULT NULL,
  `mobile_no` bigint(20) NOT NULL DEFAULT 0,
  `sent` int(11) NOT NULL DEFAULT 0 COMMENT '0=not sent 1=sent 2=expired 3=delayed so it can be sent when cron runs later',
  `sent_date` datetime DEFAULT NULL,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `event_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1;

--
-- Table structure for table `#__Stripe_xref`
--
CREATE TABLE IF NOT EXISTS `#__Stripe_xref` (
 `id` int(11) NOT NULL auto_increment,
 `user_id` int(11) NOT NULL DEFAULT 0,
 `client_id` int(11) NOT NULL DEFAULT 0,
 `client` varchar(20) NOT NULL DEFAULT '',
 `params` text DEFAULT NULL,
 PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__tjlms_user_xref` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `join_date` date DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1;

--
-- Table structure for table `rhdq7_jticketing_venues`
--

CREATE TABLE IF NOT EXISTS `#__jticketing_venues` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `vendor_id` int(11) NOT NULL DEFAULT 0,
  `asset_id` int(10) unsigned NOT NULL DEFAULT 0,
  `ordering` int(11) NOT NULL DEFAULT 0,
  `state` tinyint(1) NOT NULL DEFAULT 0,
  `checked_out` int(11) NOT NULL DEFAULT 0,
  `checked_out_time` datetime DEFAULT NULL,
  `created_by` int(11) NOT NULL DEFAULT 0,
  `modified_by` int(11) NOT NULL DEFAULT 0,
  `name` varchar(255) NOT NULL DEFAULT '',
  `alias` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `venue_category` int(11) NOT NULL DEFAULT 0,
  `online` int(3) NOT NULL DEFAULT 0,
  `online_provider` varchar(255) NOT NULL DEFAULT '' COLLATE utf8_bin,
  `country` int(11) NOT NULL DEFAULT 0,
  `state_id` int(1) NOT NULL DEFAULT 0,
  `city` varchar(255) NOT NULL DEFAULT '' COLLATE utf8_bin,
  `zipcode` varchar(255) NOT NULL DEFAULT '' COLLATE utf8_bin,
  `address` varchar(255) NOT NULL DEFAULT '' COLLATE utf8_bin,
  `seats_capacity` tinyint(1) NOT NULL DEFAULT 1 COLLATE utf8_bin,
  `capacity_count` int(11) NOT NULL DEFAULT 0,
  `description` text DEFAULT NULL,
  `longitude` float NOT NULL DEFAULT 0,
  `latitude` float NOT NULL DEFAULT 0,
  `privacy` int(11) NOT NULL DEFAULT 0,
  `params` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1;

--
-- Table structure for table `#__techjoomlaAPI_users`
--
CREATE TABLE IF NOT EXISTS `#__techjoomlaAPI_users` (
  `id` int(11) NOT NULL auto_increment,
  `api` varchar(200) NOT NULL DEFAULT '',
  `token` text DEFAULT NULL,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `client` varchar(200) NOT NULL DEFAULT '',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1;

--
-- Table structure for table `#__tj_media_files`
--

CREATE TABLE IF NOT EXISTS `#__tj_media_files` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(250) NOT NULL DEFAULT '' COLLATE utf8_bin,
  `type` varchar(250) NOT NULL DEFAULT '' COLLATE utf8_bin,
  `path` varchar(250) NOT NULL DEFAULT '' COLLATE utf8_bin,
  `state` tinyint(1) NOT NULL DEFAULT 0,
  `source` varchar(250) NOT NULL DEFAULT '' COLLATE utf8_bin,
  `original_filename` varchar(250) NOT NULL DEFAULT '' COLLATE utf8_bin,
  `size` int(11) NOT NULL DEFAULT 0,
  `storage` varchar(250) NOT NULL DEFAULT '' COLLATE utf8_bin,
  `created_by` int(11) NOT NULL DEFAULT 0,
  `access` tinyint(1) NOT NULL DEFAULT 0,
  `created_date` datetime DEFAULT NULL,
  `params` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1;

--
-- Table structure for table `#__tj_media_files_xref`
--

CREATE TABLE IF NOT EXISTS `#__tj_media_files_xref` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `media_id` int(11) NOT NULL DEFAULT 0,
  `client_id` int(11) NOT NULL DEFAULT 0,
  `client` varchar(250) NOT NULL DEFAULT '' COLLATE utf8_bin,
  `is_gallery` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1;

-- --------------------------------------------------------

--
-- Table structure for table `#__jticketing_waiting_list`
--

CREATE TABLE IF NOT EXISTS `#__jticketing_waiting_list` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `event_id` int(11) NOT NULL DEFAULT 0 COMMENT 'event id is xref id of integration_xref table',
  `behaviour` varchar(100) NOT NULL DEFAULT '' COMMENT 'Classroom Training and E-commerce',
  `status` varchar(2) NOT NULL DEFAULT '' COMMENT 'WL = Waitlist, C = Clear and  CA = Canceled',
  `created_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
   KEY `user_id_idx` (`user_id`),
   KEY `event_id_idx` (`event_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__jticketing_pdf_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_id` int(11) NOT NULL DEFAULT 0,
  `vendor_id` int(11) NOT NULL DEFAULT 0,
  `body` text DEFAULT NULL,
  `css` text DEFAULT NULL,
  `state` int(11) NOT NULL DEFAULT 0,
  `created_on` datetime NULL DEFAULT NULL,
  `updated_on` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
   KEY `event_id` (`event_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

CREATE TABLE IF NOT EXISTS `#__jticketing_recurring_events` (
  `r_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `event_id` int(11) unsigned NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  PRIMARY KEY (`r_id`),
  CONSTRAINT `fk_event_id` FOREIGN KEY (`event_id`) REFERENCES `#__jticketing_events` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__jticketing_recurring_event_attendees` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `attendee_id` INT(11) NOT NULL,
    `r_id` INT UNSIGNED NOT NULL,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`attendee_id`)
        REFERENCES `#__jticketing_attendees` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (`r_id`)
    REFERENCES `#__jticketing_recurring_events` (`r_id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;