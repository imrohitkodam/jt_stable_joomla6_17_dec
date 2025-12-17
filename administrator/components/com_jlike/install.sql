CREATE TABLE IF NOT EXISTS `#__jlike` (
  `id` int(11) NOT NULL auto_increment COMMENT 'Primary key',
  `title` varchar(100) NOT NULL DEFAULT '' COMMENT 'Image name',
  `published` int(11) NOT NULL DEFAULT 0 COMMENT 'Published indicates which button default selected.',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__jlike_likes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `content_id` int(11) NOT NULL DEFAULT 0 COMMENT 'Foreign Key (#__jlike_content table)',
  `annotation_id` int(11) NOT NULL DEFAULT 0 COMMENT 'comment id',
  `userid` int(11) NOT NULL DEFAULT 0 COMMENT 'Logged in user id',
  `like` int(11) NOT NULL DEFAULT 0 COMMENT '1 for like',
  `dislike` int(11) NOT NULL DEFAULT 0 COMMENT '1 for dislike',
  `date` text DEFAULT NULL,
  `created` datetime DEFAULT NULL COMMENT 'Created date of like / dislike',
  `modified` datetime DEFAULT NULL COMMENT 'Modified date of like / dislike',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__jlike_recommend` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `content_id` int(11) NOT NULL DEFAULT 0 COMMENT 'Foreign Key (#__jlike_content table)',
  `recommend_to` int(11) NOT NULL DEFAULT 0 COMMENT 'User id',
  `recommend_by` int(11) NOT NULL DEFAULT 0 COMMENT 'User id',
  `params` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__jlike_annotations` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Primary key',
  `ordering` INT(11) NOT NULL DEFAULT 0,
  `state` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1: Publish, 0: Unpublish state',
  `user_id` int(11) NOT NULL DEFAULT 0 COMMENT 'User id who has added the annotation. Primary key of table #_users',
  `content_id` int(11) NOT NULL DEFAULT 0 COMMENT 'Foreign Key (#__jlike_content table)',
  `annotation` text DEFAULT NULL COMMENT 'Comment',
  `privacy` int(11) NOT NULL DEFAULT 0 COMMENT '1 - Keep comment private',
  `annotation_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Comment creation date',
  `parent_id` int(11) NOT NULL DEFAULT 0 COMMENT 'Parent comment id.',
  `note` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '0 - comment, 1 - note, 2- Review, 3 -Owner Reply for review',
  `type` varchar(100) NOT NULL DEFAULT '' COMMENT 'Can be comment, notes,private comment,collaborator comment, reviewer commentE.g collaborator, reviewer',
  `context` varchar(255) NOT NULL DEFAULT '',
  `checked_out_time` datetime DEFAULT NULL,
  `images` text DEFAULT NULL COMMENT 'Review images',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__jlike_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `namekey` text DEFAULT NULL,
  `value` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__jlike_content` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `element_id` int(11) NOT NULL DEFAULT 0 COMMENT 'Content item id',
  `url` text DEFAULT NULL COMMENT 'Content url',
  `element` VARCHAR(100) NOT NULL DEFAULT '' COMMENT 'Content element name. For eg. com_quick2cart.productpage',
  `title` text DEFAULT NULL COMMENT 'Content title. For eg. Q2C product name',
  `img` text DEFAULT NULL,
  `like_cnt` int(11) NOT NULL DEFAULT 0 COMMENT 'Like count',
  `dislike_cnt` int(11) NOT NULL DEFAULT 0 COMMENT 'Dilike count',
  `params` text DEFAULT NULL COMMENT 'Used for storing extra data',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_element_pair` (`element_id`, `element`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__jlike_likes_lists_xref` (
  `content_id` int(11) NOT NULL DEFAULT 0 COMMENT 'Foreign Key (#__jlike_content table)',
  `list_id` int(11) NOT NULL DEFAULT 0 COMMENT 'Selected label id'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__jlike_like_lists` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT 0 COMMENT 'User Id',
  `title` varchar(40) NOT NULL DEFAULT '' COMMENT 'Label name',
  `privacy` int(11) NOT NULL DEFAULT 0 COMMENT '1 - Keep label private',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

REPLACE INTO `#__jlike` VALUES (1,'1.png',0),(2,'2.png',0),(3,'3.png',0),(4,'4.png',1),(5,'5.png',0),(6,'6.png',0);

CREATE TABLE IF NOT EXISTS `#__jlike_content_inviteX_xref` (
  `id` int(15) NOT NULL AUTO_INCREMENT,
  `content_id` int(15) NOT NULL DEFAULT 0 COMMENT 'Foreign Key (#__jlike_content table)',
  `importEmailId` int(15) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__jlike_todos` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Primary key, auto increment',
  `asset_id` int(10) unsigned NOT NULL DEFAULT 0,
  `ordering` int(11) NOT NULL DEFAULT 0,
  `state` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Publish / Unpublish state',
  `checked_out` int(11) NOT NULL DEFAULT 0,
  `checked_out_time` datetime DEFAULT NULL,
  `created_by` int(11) NOT NULL DEFAULT 0 COMMENT 'User id',
  `sender_msg` text DEFAULT NULL COMMENT 'Message given by sender while recommending/assignment',
  `content_id` int(11) NOT NULL DEFAULT 0 COMMENT 'FK to jlike_content',
  `assigned_by` int(11) NOT NULL DEFAULT 0 COMMENT 'user id',
  `assigned_to` int(11) NOT NULL DEFAULT 0 COMMENT 'user id',
  `created_date` datetime DEFAULT NULL COMMENT 'Created date',
  `start_date` datetime DEFAULT NULL COMMENT 'todo start date',
  `due_date` datetime DEFAULT NULL COMMENT 'todo end date',
  `status` varchar(100) NOT NULL DEFAULT '' COMMENT 'I- Incomplete , C- Completed, S- Started',
  `title` varchar(255) NOT NULL DEFAULT '' COMMENT 'Content tile',
  `type` varchar(100) NOT NULL DEFAULT '' COMMENT 'Type of the todo (self, reco, assign)',
  `context` varchar(255) NOT NULL DEFAULT '',
  `system_generated` tinyint(4) NOT NULL DEFAULT 1,
  `parent_id` int(11) NOT NULL DEFAULT 0 COMMENT 'Shika lesson prerequisites id',
  `list_id` int(11) NOT NULL DEFAULT 0 COMMENT 'jlike list id',
  `modified_date` datetime DEFAULT NULL COMMENT 'modified date',
  `modified_by` int(11) NOT NULL DEFAULT 0,
  `can_override` tinyint(4) NOT NULL DEFAULT 0,
  `overriden` tinyint(4) NOT NULL DEFAULT 0,
  `params` text DEFAULT NULL,
  `todo_list_id` int(11) NOT NULL DEFAULT 0,
  `ideal_time` int(11) NOT NULL DEFAULT 0,
  `done_by` int(11) unsigned NOT NULL DEFAULT 0,
  `done_date` datetime DEFAULT NULL,
  PRIMARY KEY `id` (`id`),
  KEY `unique_todo` (`content_id`,`assigned_to`),
  KEY `assigned_to` (`assigned_to`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__jlike_likeStatusXref` (
  `id` int(15) NOT NULL AUTO_INCREMENT,
  `content_id` int(10) NOT NULL DEFAULT 0 COMMENT 'Foreign Key (#__jlike_content table)',
  `status_id` int(11) NOT NULL DEFAULT 0,
  `user_id` int(15) NOT NULL DEFAULT 0,
  `cdate` datetime DEFAULT NULL,
  `mdate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS  `#__jlike_rating` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT 0 COMMENT 'User id',
  `content_id` int(11) NOT NULL DEFAULT 0 COMMENT 'Foreign Key (#__jlike_content table)',
  `rating_upto` int(11) NOT NULL DEFAULT 0 COMMENT 'Total rating',
  `user_rating` int(11) NOT NULL DEFAULT 0 COMMENT 'User given rating',
  `created_date` datetime DEFAULT NULL COMMENT 'Created date and time of rating',
  `modified_date` datetime DEFAULT NULL COMMENT 'Modified date and time of rating',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__jlike_reminders` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Primary Key',
  `ordering` INT(11) NOT NULL DEFAULT 0,
  `state` TINYINT(1) NOT NULL DEFAULT 0,
  `checked_out` INT(11) NOT NULL DEFAULT 0,
  `checked_out_time` DATETIME DEFAULT NULL,
  `created_by` INT(11) NOT NULL DEFAULT 0,
  `modified_by` INT(11) NOT NULL DEFAULT 0,
  `title` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'Title of the reminder',
  `days_before` INT(11) NOT NULL DEFAULT 0 COMMENT 'Number of days before the due date, when the reminder is sent',
  `email_template` TEXT DEFAULT NULL COMMENT 'The body of the email that will be sent',
  `subject` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'Subject of email',
  `content_type` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'distinct jlike_content.element table',
  `cc` VARCHAR(255)  NOT NULL DEFAULT '' COMMENT 'Add emails to sent,mulptiple emails seprated by comma',
  `mailfrom` VARCHAR(255)  NOT NULL DEFAULT '' COMMENT 'Email id using which reminder mail should be send',
  `fromname` VARCHAR(255)  NOT NULL DEFAULT '' COMMENT 'Fron name in reminder mail',
  `replyto` VARCHAR(255)  NOT NULL DEFAULT '' COMMENT 'Email id to which mail reply should be sent',
  `replytoname` VARCHAR(255)  NOT NULL DEFAULT '' COMMENT 'Reply to name used in reminder mail',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__jlike_reminder_contentids` (
  `reminder_id` int(11) NOT NULL DEFAULT 0 COMMENT 'FK to jlike_reminders.id',
  `content_id` int(11) NOT NULL DEFAULT 0 COMMENT 'Foreign Key (#__jlike_content table)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__jlike_reminder_sent` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Primary Key',
  `todo_id` int(11) NOT NULL DEFAULT 0 COMMENT 'FK to jlike_todos.id',
  `reminder_id` int(11) NOT NULL DEFAULT 0 COMMENT 'FK to jlike_reminders.id',
  `sent_on` datetime DEFAULT NULL COMMENT 'Date and time when the reminder was sent',
  `r_id` INT UNSIGNED NULL COMMENT 'Refers to r_id of recurring events',
  `attendee_id` INT UNSIGNED NULL COMMENT 'Refers to attendees table',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__jlike_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(150) NOT NULL DEFAULT '',
  `subtype` varchar(50) NOT NULL DEFAULT '',
  `client` varchar(255) NOT NULL DEFAULT '',
  `params` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__jlike_pathnode_graph` (
  `pathnode_graph_id` int(11) NOT NULL AUTO_INCREMENT,
  `path_id` int(11) NOT NULL DEFAULT 0 COMMENT 'context / path ',
  `lft` int(11) NOT NULL DEFAULT 0,
  `node` int(11) NOT NULL DEFAULT 0 COMMENT 'is content_id of jlike_content OR path_id of jlike_path table',
  `rgt` int(11) NOT NULL DEFAULT 0,
  `order` int(11) NOT NULL DEFAULT 0 COMMENT 'Display order',
  `isPath` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'if 1 = path and 0 = node',
  `this_compulsory` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Current/This is compulsory or not, 1 is compulsory 0 is optional',
  `delay` int(11) NOT NULL DEFAULT 0,
  `duration` int(11) NOT NULL DEFAULT 0,
  `visibility` tinyint(4) NOT NULL DEFAULT 1,
  PRIMARY KEY (`pathnode_graph_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__jlike_paths` (
  `path_id` int(11) NOT NULL AUTO_INCREMENT,
  `path_title` varchar(255) CHARACTER SET latin1 NOT NULL DEFAULT '' COMMENT 'eg. Student, Coach',
  `alias` varchar(255) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `path_description` text CHARACTER SET latin1 DEFAULT NULL,
  `path_image` text CHARACTER SET latin1 DEFAULT NULL,
  `category_id` int(11) NOT NULL DEFAULT 0 COMMENT 'This will be Joomla Category',
  `order` int(11) NOT NULL DEFAULT 0 COMMENT 'Display order',
  `path_type` varchar(255) CHARACTER SET latin1 NOT NULL DEFAULT '' COMMENT 'identifier of path_type table',
  `depth` tinyint(4) NOT NULL DEFAULT 0,
  `params` text CHARACTER SET latin1 DEFAULT NULL,
  `state` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Published/Unpublished',
  `access` INT(10) NOT NULL DEFAULT 0,
  `created_by` int(11) NOT NULL DEFAULT 0 COMMENT 'Created By user id',
  `created_date` date DEFAULT NULL COMMENT 'Created date',
  `modified_by` int(11) NOT NULL DEFAULT 0,
  `modified_date` date DEFAULT NULL,
  `subscribe_start_date` DATETIME DEFAULT NULL,
  `subscribe_end_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`path_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__jlike_path_type` (
  `path_type_id` int(11) NOT NULL AUTO_INCREMENT,
  `type_title` varchar(255) NOT NULL DEFAULT '' COMMENT 'eg. general, learning',
  `identifier` varchar(100) NOT NULL DEFAULT '' COMMENT 'eg. com_jlike.learning',
  `params` text DEFAULT NULL COMMENT 'Used to Define specific fields needed for this path type, specific actions on various events etc',
  PRIMARY KEY (`path_type_id`),
  UNIQUE KEY `identifier` (`identifier`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `#__jlike_path_type` (`type_title`, `identifier`, `params`) VALUES
('General', 'com_jlike.general', '{\r\n \"core\": {\r\n \"approval\": \"admin\"\r\n },\r\n \"onaftersubscribe\": {\r\n \"advanced\": {\r\n \"esgroup\": \"\",\r\n \"jugroup\": \"\",\r\n \"course\": \"\",\r\n \"notify\": \"\",\r\n \"activity\": \"\"\r\n },\r\n \"custom\": {\r\n }\r\n },\r\n \"onaftercompletion\": {\r\n \"advanced\": {\r\n \"esgroup\": \"\",\r\n \"jugroup\": \"\",\r\n \"course\": \"\",\r\n \"notify\": \"\",\r\n \"activity\": \"\"\r\n },\r\n \"custom\": {\r\n }\r\n }\r\n}') ON DUPLICATE KEY UPDATE `identifier` = `identifier`;

CREATE TABLE IF NOT EXISTS `#__jlike_path_user` (
  `path_user_id` int(11) NOT NULL AUTO_INCREMENT,
  `path_id` int(11) DEFAULT 0 COMMENT 'jlike_paths PK',
  `user_id` int(11) NOT NULL DEFAULT 0,
  `status` varchar(50) NOT NULL DEFAULT 'I' COMMENT 'path completion status If C= Completed, I= Incompleted',
  `subscribed_date` datetime DEFAULT NULL,
  `completed_date` datetime DEFAULT NULL,
  PRIMARY KEY (`path_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__jlike_ratings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rating_type_id` int(11) NOT NULL DEFAULT 0,
  `title` varchar(500) NOT NULL DEFAULT '',
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `#__jlike_rating_types` (`client`, `code`, `is_default`, `title`, `show_title`, `title_required`, `show_rating`, `rating_required`, `rating_scale`, `show_review`, `review_required`, `tjucm_type_id`, `state`, `show_all_rating`) VALUES ('com_content', 'DEFAULT_RATING', 1, 'Default Rating', 1, 1, 1, 1, '5', 1, 1, 0, 1, 1) ON DUPLICATE KEY UPDATE `code` = `code`;
