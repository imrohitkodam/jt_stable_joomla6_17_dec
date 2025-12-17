ALTER TABLE `#__jticketing_types` add column `ticket_startdate` datetime DEFAULT NULL after `desc`;
SET sql_mode = '';
UPDATE `#__jticketing_types` SET `ticket_enddate` = NULL WHERE `ticket_enddate` = '0000-00-00 00:00:00';

ALTER TABLE `#__jticketing_events`
ADD COLUMN `recurring_type` VARCHAR(255) NOT NULL DEFAULT 'No_repeat' COMMENT 'No_repeat, Daily, Weekly, Monthly, Yearly',
ADD COLUMN `recurring_params` JSON DEFAULT NULL;

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

ALTER TABLE `#__jticketing_checkindetails`
ADD COLUMN `r_id` INT DEFAULT NULL COMMENT 'Refers to r_id of recurring events';