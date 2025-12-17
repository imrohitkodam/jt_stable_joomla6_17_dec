CREATE TABLE IF NOT EXISTS `#__jlike_ratings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rating_type_id` int(11) NOT NULL DEFAULT 0,
  `title` varchar(500) DEFAULT NULL DEFAULT '',
  `review` text DEFAULT NULL,
  `submitted_by` int(11) NOT NULL DEFAULT 0,
  `content_id` int(11) NOT NULL DEFAULT 0,
  `rating_scale` tinyint(3) NOT NULL DEFAULT 0,
  `rating` tinyint(3) NOT NULL DEFAULT 0,
  `created_date` datetime DEFAULT NULL,
  `created_by` int(11) NOT NULL DEFAULT 0,
  `modified_date` datetime DEFAULT NULL,
  `tjucm_content_id` int(11) NOT NULL DEFAULT 0,
  `state` tinyint(3) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `rating_type_id` (`rating_type_id`),
  KEY `content_id` (`content_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__jlike_rating_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '',
  `code` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '',
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '',
  `show_title` tinyint(1) NOT NULL DEFAULT 0,
  `title_required` tinyint(1) NOT NULL DEFAULT 0,
  `show_rating` tinyint(1) NOT NULL DEFAULT 0,
  `rating_required` tinyint(1) NOT NULL DEFAULT 0,
  `rating_scale` tinyint(3) NOT NULL DEFAULT '5',
  `show_review` tinyint(1) NOT NULL DEFAULT 0,
  `review_required` tinyint(1) NOT NULL DEFAULT 0,
  `tjucm_type_id` int(11) NOT NULL DEFAULT 0,
  `state` tinyint(3) NOT NULL DEFAULT 1,
  `show_all_rating` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `#__jlike_reminders` ADD COLUMN `mailfrom` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'Email id using which reminder mail should be send' AFTER `cc`;

ALTER TABLE `#__jlike_reminders` ADD COLUMN `fromname` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'From name in reminder mail' AFTER `mailfrom`;

ALTER TABLE `#__jlike_reminders` ADD COLUMN `replyto` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'Email id to which mail reply should be sent' AFTER `fromname`;

ALTER TABLE `#__jlike_reminders` ADD COLUMN `replytoname` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'Reply to name used in reminder mail' AFTER `replyto`;

ALTER TABLE `#__jlike_content` ADD COLUMN `params` TEXT DEFAULT NULL COMMENT 'Used for storing extra data' AFTER `dislike_cnt`;

INSERT INTO `#__jlike_rating_types` (`client`, `code`, `is_default`, `title`, `show_title`, `title_required`, `show_rating`, `rating_required`, `rating_scale`, `show_review`, `review_required`, `tjucm_type_id`, `state`, `show_all_rating`)  VALUES ('com_content', 'DEFAULT_RATING', '1', 'Default Rating', '1', '1', '1', '1', '5', '1', '1', '0', '1', '1') ON DUPLICATE KEY UPDATE `code` = `code`;

-- Decrease column length from 255 to 100 to accommodate it into Unique key constraint
ALTER TABLE `#__jlike_path_type` MODIFY `identifier` VARCHAR(100);
ALTER TABLE `#__jlike_content` MODIFY `element` VARCHAR(100);

--
-- Change default table engine to InnoDB;
-- Necessary for indexes to work
--
ALTER TABLE `#__jlike` ENGINE = InnoDB;
ALTER TABLE `#__jlike_likes` ENGINE = InnoDB;
ALTER TABLE `#__jlike_recommend` ENGINE = InnoDB;
ALTER TABLE `#__jlike_annotations` ENGINE = InnoDB;
ALTER TABLE `#__jlike_config` ENGINE = InnoDB;
ALTER TABLE `#__jlike_likes_lists_xref` ENGINE = InnoDB;
ALTER TABLE `#__jlike_like_lists` ENGINE = InnoDB;
ALTER TABLE `#__jlike_content_inviteX_xref` ENGINE = InnoDB;
ALTER TABLE `#__jlike_todos` ENGINE = InnoDB;
ALTER TABLE `#__jlike_likeStatusXref` ENGINE = InnoDB;
ALTER TABLE `#__jlike_rating` ENGINE = InnoDB;
ALTER TABLE `#__jlike_reminders` ENGINE = InnoDB;
ALTER TABLE `#__jlike_reminder_contentids` ENGINE = InnoDB;
ALTER TABLE `#__jlike_reminder_sent` ENGINE = InnoDB;
ALTER TABLE `#__jlike_types` ENGINE = InnoDB;
ALTER TABLE `#__jlike_pathnode_graph` ENGINE = InnoDB;
ALTER TABLE `#__jlike_paths` ENGINE = InnoDB;
ALTER TABLE `#__jlike_path_user` ENGINE = InnoDB;
ALTER TABLE `#__jlike_path_type` ENGINE = InnoDB;
ALTER TABLE `#__jlike_content` ENGINE = InnoDB;

ALTER TABLE `#__jlike` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `#__jlike_likes` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `#__jlike_recommend` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `#__jlike_annotations` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `#__jlike_config` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `#__jlike_likes_lists_xref` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `#__jlike_like_lists` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `#__jlike_content_inviteX_xref` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `#__jlike_todos` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `#__jlike_likeStatusXref` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `#__jlike_rating` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `#__jlike_reminders` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `#__jlike_reminder_contentids` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `#__jlike_reminder_sent` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `#__jlike_types` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `#__jlike_pathnode_graph` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `#__jlike_paths` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `#__jlike_path_user` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `#__jlike_content` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `#__jlike_path_type` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

ALTER TABLE `#__jlike_todos` ADD PRIMARY KEY `pk_id` (`id`);
ALTER TABLE `#__jlike_todos` DROP INDEX `id`;

-- Delete previous path type entries
DELETE FROM `#__jlike_path_type`;

ALTER TABLE `#__jlike_path_type` ADD UNIQUE(`identifier`);
