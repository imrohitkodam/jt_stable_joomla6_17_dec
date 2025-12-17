ALTER TABLE `#__jticketing_coupon` CHANGE `max_use` `limit` int(11) NOT NULL DEFAULT 0;
ALTER TABLE `#__jticketing_coupon` CHANGE `from_date` `valid_from` datetime DEFAULT NULL;
ALTER TABLE `#__jticketing_coupon` CHANGE `exp_date` `valid_to` datetime DEFAULT NULL;
ALTER TABLE `#__jticketing_coupon` CHANGE `coupon_params` `params` text DEFAULT NULL;
ALTER TABLE `#__jticketing_coupon` ADD UNIQUE KEY `code` (code);
ALTER TABLE `#__jticketing_coupon` ADD COLUMN `used` int(11) NOT NULL DEFAULT 0 COMMENT 'used coupon count';
ALTER TABLE `#__jticketing_coupon` ADD COLUMN `event_ids` varchar(255) NOT NULL DEFAULT '' COMMENT 'event id';
ALTER TABLE `#__jticketing_coupon` ADD COLUMN `vendor_id` int(11) NOT NULL DEFAULT 0;
