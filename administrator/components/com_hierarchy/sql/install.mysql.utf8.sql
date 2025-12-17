-- --------------------------------------------------------------
--
-- Table structure for table `#__hierarchy_users`
--
CREATE TABLE IF NOT EXISTS `#__hierarchy_users` (
`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
`user_id` INT(11) NOT NULL COMMENT 'This field is to store user id of user' DEFAULT 0,
`reports_to` INT(11) NOT NULL COMMENT 'This field is to select to whom all current user reports to' DEFAULT 0,
`context` VARCHAR(255) NOT NULL DEFAULT '',
`context_id` INT(11) NOT NULL DEFAULT 0,
`created_by` INT(11) NOT NULL DEFAULT 0,
`modified_by` INT(11) NOT NULL DEFAULT 0,
`created_date` datetime DEFAULT NULL,
`modified_date` datetime DEFAULT NULL,
`state` INT(11) NOT NULL DEFAULT 0,
`note` TEXT DEFAULT NULL,
PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;
