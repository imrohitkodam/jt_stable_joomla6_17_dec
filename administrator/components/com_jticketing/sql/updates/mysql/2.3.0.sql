ALTER TABLE `#__jticketing_events` add column `meta_data` text DEFAULT NULL COMMENT 'meta keywords';
ALTER TABLE `#__jticketing_events` add column `meta_desc` text DEFAULT NULL COMMENT 'meta description';
ALTER TABLE `#__jticketing_types` add column `ticket_enddate` DATETIME DEFAULT NULL;
ALTER TABLE `#__jticketing_events` add column `ideal_time` int(11) NOT NULL DEFAULT '0';
ALTER TABLE `#__jticketing_venues` MODIFY `name` VARCHAR(255) NOT NULL DEFAULT '' CHARACTER SET utf8 COLLATE utf8_general_ci;
