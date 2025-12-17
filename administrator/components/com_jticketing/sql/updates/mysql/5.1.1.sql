ALTER TABLE `#__jticketing_types` add column  `max_ticket_per_order` INT(11) NOT NULL DEFAULT 0 COMMENT '0=unlimited OR max_ticket_per_order as specified';
ALTER TABLE `#__jticketing_types` add column `allow_ticket_level_sequence` tinyint(2) NOT NULL DEFAULT 0;
ALTER TABLE `#__jticketing_types` add column `start_number_for_sequence` varchar(50) NOT NULL DEFAULT '';
ALTER TABLE `#__jticketing_order_items` add column `entry_number` varchar(50) NOT NULL DEFAULT '';
ALTER TABLE `#__jticketing_events` add column `start_number_for_event_level_sequence` varchar(50) NOT NULL DEFAULT '';