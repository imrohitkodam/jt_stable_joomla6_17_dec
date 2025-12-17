UPDATE `#__tj_notification_templates` SET `replacement_tags` = CONCAT(LEFT(`replacement_tags`, LENGTH(`replacement_tags`) - 1), ',{"name":"ticket.entry_number","description":"Entry Number for the Ticket."}]') WHERE `key` = 'e-tickets' AND `client` = 'com_jticketing';
UPDATE `#__tj_notification_templates` SET `replacement_tags` = CONCAT(LEFT(`replacement_tags`, LENGTH(`replacement_tags`) - 1), ',{"name":"ticket.entry_number","description":"Entry Number for the Ticket."}]') WHERE `key` = 'm-tickets' AND `client` = 'com_jticketing';

ALTER TABLE `#__jticketing_types` CHANGE `access` `access` tinyint(1) NOT NULL DEFAULT 0;
ALTER TABLE `#__jticketing_coupon` ADD `group_discount` tinyint(1) NOT NULL DEFAULT 0;
ALTER TABLE `#__jticketing_coupon` ADD `group_discount_tickets` int(11) NOT NULL DEFAULT 0;
ALTER TABLE `#__jticketing_events` ADD COLUMN `recurring_type` VARCHAR(255) NOT NULL DEFAULT 'No_repeat' COMMENT 'No_repeat, Daily, Weekly, Monthly, Yearly';
ALTER TABLE `#__jticketing_events` ADD COLUMN `recurring_params` JSON DEFAULT NULL;

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

ALTER TABLE `#__jticketing_checkindetails` ADD COLUMN `r_id` int DEFAULT NULL;
