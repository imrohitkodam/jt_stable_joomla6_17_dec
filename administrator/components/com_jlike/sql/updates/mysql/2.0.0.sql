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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

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
  `created_by` int(11) NOT NULL DEFAULT 0 COMMENT 'Created By user id',
  `created_date` date DEFAULT NULL COMMENT 'Created date',
  `modified_by` int(11) NOT NULL DEFAULT 0,
  `modified_date` date DEFAULT NULL,
  PRIMARY KEY (`path_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__jlike_path_type` (
  `path_type_id` int(11) NOT NULL AUTO_INCREMENT,
  `type_title` varchar(255) NOT NULL DEFAULT '' COMMENT 'eg. general, learning',
  `identifier` varchar(100) NOT NULL DEFAULT '' COMMENT 'eg. com_jlike.learning',
  `params` text DEFAULT NULL COMMENT 'Used to Define specific fields needed for this path type, specific actions on various events etc',
  PRIMARY KEY (`path_type_id`),
  UNIQUE KEY `identifier` (`identifier`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__jlike_path_user` (
  `path_user_id` int(11) NOT NULL AUTO_INCREMENT,
  `path_id` int(11) DEFAULT 0 COMMENT 'jlike_paths PK',
  `user_id` int(11) NOT NULL DEFAULT 0,
  `status` varchar(50) NOT NULL DEFAULT 'I' COMMENT 'path completion status If C= Completed, I= Incompleted' DEFAULT '',
  `subscribed_date` datetime DEFAULT NULL,
  `completed_date` datetime DEFAULT NULL,
  PRIMARY KEY (`path_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;
