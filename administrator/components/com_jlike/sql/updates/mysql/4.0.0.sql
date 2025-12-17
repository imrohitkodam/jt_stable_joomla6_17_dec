ALTER TABLE `#__jlike` CHANGE `title` `title` varchar(100) NOT NULL DEFAULT '' COMMENT 'Image name';
ALTER TABLE `#__jlike` CHANGE `published` `published` int(11) NOT NULL DEFAULT 0 COMMENT 'Published indicates which button default selected.';

ALTER TABLE `#__jlike_likes` CHANGE `content_id` `content_id` int(11) NOT NULL DEFAULT 0 COMMENT 'Foreign Key (#__jlike_content table)';
ALTER TABLE `#__jlike_likes` CHANGE `annotation_id` `annotation_id` int(11) NOT NULL DEFAULT 0 COMMENT 'comment id';
ALTER TABLE `#__jlike_likes` CHANGE `userid` `userid` int(11) NOT NULL DEFAULT 0 COMMENT 'Logged in user id';
ALTER TABLE `#__jlike_likes` CHANGE `like` `like` int(11) NOT NULL DEFAULT 0 COMMENT '1 for like';
ALTER TABLE `#__jlike_likes` CHANGE `dislike` `dislike` int(11) NOT NULL DEFAULT 0 COMMENT '1 for dislike';
ALTER TABLE `#__jlike_likes` CHANGE `date` `date` text;
ALTER TABLE `#__jlike_likes` CHANGE `created` `created` datetime DEFAULT NULL COMMENT 'Created date of like / dislike';
ALTER TABLE `#__jlike_likes` CHANGE `modified` `modified` datetime DEFAULT NULL COMMENT 'Modified date of like / dislike';

ALTER TABLE `#__jlike_recommend` CHANGE `content_id` `content_id` int(11) NOT NULL DEFAULT 0 COMMENT 'Foreign Key (#__jlike_content table)';
ALTER TABLE `#__jlike_recommend` CHANGE `recommend_to` `recommend_to` int(11) NOT NULL DEFAULT 0 COMMENT 'User id';
ALTER TABLE `#__jlike_recommend` CHANGE `recommend_by` `recommend_by` int(11) NOT NULL DEFAULT 0 COMMENT 'User id';
ALTER TABLE `#__jlike_recommend` CHANGE `params` `params` text DEFAULT NULL;

ALTER TABLE `#__jlike_annotations` CHANGE `ordering` `ordering` INT(11) NOT NULL DEFAULT 0;
ALTER TABLE `#__jlike_annotations` CHANGE `state` `state` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1: Publish, 0: Unpublish state';
ALTER TABLE `#__jlike_annotations` CHANGE `user_id` `user_id` int(11) NOT NULL DEFAULT 0 COMMENT 'User id who has added the annotation. Primary key of table #_users';
ALTER TABLE `#__jlike_annotations` CHANGE `content_id` `content_id` int(11) NOT NULL DEFAULT 0 COMMENT 'Foreign Key (#__jlike_content table)';
ALTER TABLE `#__jlike_annotations` CHANGE `annotation` `annotation` text DEFAULT NULL COMMENT 'Comment';
ALTER TABLE `#__jlike_annotations` CHANGE `privacy` `privacy` int(11) NOT NULL DEFAULT 0 COMMENT '1 - Keep comment private';
ALTER TABLE `#__jlike_annotations` CHANGE `parent_id` `parent_id` int(11) NOT NULL DEFAULT 0 COMMENT 'Parent comment id.';
ALTER TABLE `#__jlike_annotations` CHANGE `note` `note` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '0 - comment, 1 - note, 2- Review, 3 -Owner Reply for review';
UPDATE `#__jlike_annotations` SET `type` = '' WHERE `type` IS NULL;
UPDATE `#__jlike_annotations` SET `context` = '' WHERE `context` IS NULL;
ALTER TABLE `#__jlike_annotations` CHANGE `type` `type` varchar(255) NOT NULL DEFAULT '' COMMENT 'Can be comment, notes,private comment,collaborator comment, reviewer commentE.g collaborator, reviewer';
ALTER TABLE `#__jlike_annotations` CHANGE `context` `context` varchar(255) NOT NULL DEFAULT '';
ALTER TABLE `#__jlike_annotations` CHANGE `checked_out_time` `checked_out_time` datetime DEFAULT NULL;

ALTER TABLE `#__jlike_config` CHANGE `namekey` `namekey` text DEFAULT NULL;
ALTER TABLE `#__jlike_config` CHANGE `value` `value` text DEFAULT NULL;

ALTER TABLE `#__jlike_content` CHANGE `element_id` `element_id` int(11) NOT NULL DEFAULT 0 COMMENT 'Content item id';
ALTER TABLE `#__jlike_content` CHANGE `url` `url` text DEFAULT NULL COMMENT 'Content url';
ALTER TABLE `#__jlike_content` CHANGE `element` `element` VARCHAR(100) DEFAULT '' NOT NULL COMMENT 'Content element name. For eg. com_quick2cart.productpage';
ALTER TABLE `#__jlike_content` CHANGE `title` `title` text DEFAULT NULL COMMENT 'Content title. For eg. Q2C product name';
ALTER TABLE `#__jlike_content` CHANGE `img` `img` text DEFAULT NULL;
ALTER TABLE `#__jlike_content` CHANGE `like_cnt` `like_cnt` int(11) NOT NULL DEFAULT 0 COMMENT 'Like count';
ALTER TABLE `#__jlike_content` CHANGE `dislike_cnt` `dislike_cnt` int(11) NOT NULL DEFAULT 0 COMMENT 'Dilike count';
ALTER TABLE `#__jlike_content` CHANGE `params` `params` text DEFAULT NULL COMMENT 'Used for storing extra data';

ALTER TABLE `#__jlike_likes_lists_xref` CHANGE `content_id` `content_id` int(11) NOT NULL DEFAULT 0 COMMENT 'Foreign Key (#__jlike_content table)';
ALTER TABLE `#__jlike_likes_lists_xref` CHANGE `list_id` `list_id` int(11) NOT NULL DEFAULT 0 COMMENT 'Selected label id';

ALTER TABLE `#__jlike_like_lists` CHANGE `user_id` `user_id` int(11) NOT NULL DEFAULT 0 COMMENT 'User Id';
ALTER TABLE `#__jlike_like_lists` CHANGE `title` `title` varchar(40) NOT NULL DEFAULT '' COMMENT 'Label name';
ALTER TABLE `#__jlike_like_lists` CHANGE `privacy` `privacy` int(11) NOT NULL DEFAULT 0 COMMENT '1 - Keep label private';

ALTER TABLE `#__jlike_content_inviteX_xref` CHANGE `content_id` `content_id` int(15) NOT NULL DEFAULT 0 COMMENT 'Foreign Key (#__jlike_content table)';
ALTER TABLE `#__jlike_content_inviteX_xref` CHANGE `importEmailId` `importEmailId` int(15) NOT NULL DEFAULT 0;

ALTER TABLE `#__jlike_todos` CHANGE `asset_id` `asset_id` int(10) unsigned NOT NULL DEFAULT 0;
ALTER TABLE `#__jlike_todos` CHANGE `ordering` `ordering` int(11) NOT NULL DEFAULT 0;
ALTER TABLE `#__jlike_todos` CHANGE `state` `state` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Publish / Unpublish state';
ALTER TABLE `#__jlike_todos` CHANGE `checked_out` `checked_out` int(11) NOT NULL DEFAULT 0;
ALTER TABLE `#__jlike_todos` CHANGE `checked_out_time` `checked_out_time` datetime DEFAULT NULL;
ALTER TABLE `#__jlike_todos` CHANGE `created_by` `created_by` int(11) NOT NULL DEFAULT 0 COMMENT 'User id';
ALTER TABLE `#__jlike_todos` CHANGE `sender_msg` `sender_msg` text DEFAULT NULL COMMENT 'Message given by sender while recommending/assignment';
ALTER TABLE `#__jlike_todos` CHANGE `content_id` `content_id` int(11) NOT NULL DEFAULT 0 COMMENT 'FK to jlike_content';
ALTER TABLE `#__jlike_todos` CHANGE `assigned_by` `assigned_by` int(11) NOT NULL DEFAULT 0 COMMENT 'user id';
ALTER TABLE `#__jlike_todos` CHANGE `assigned_to` `assigned_to` int(11) NOT NULL DEFAULT 0 COMMENT 'user id';
ALTER TABLE `#__jlike_todos` CHANGE `created_date` `created_date` datetime DEFAULT NULL COMMENT 'Created date';
ALTER TABLE `#__jlike_todos` CHANGE `start_date` `start_date` datetime DEFAULT NULL COMMENT 'todo start date';
ALTER TABLE `#__jlike_todos` CHANGE `due_date` `due_date` datetime DEFAULT NULL COMMENT 'todo end date';
ALTER TABLE `#__jlike_todos` CHANGE `status` `status` varchar(100) NOT NULL DEFAULT '' COMMENT 'I- Incomplete , C- Completed, S- Started';
ALTER TABLE `#__jlike_todos` CHANGE `title` `title` varchar(255) NOT NULL DEFAULT '' COMMENT 'Content tile';
ALTER TABLE `#__jlike_todos` CHANGE `type` `type` varchar(255) NOT NULL DEFAULT '' COMMENT 'Type of the todo (self, reco, assign)';
ALTER TABLE `#__jlike_todos` CHANGE `context` `context` varchar(255) NOT NULL DEFAULT '';
ALTER TABLE `#__jlike_todos` CHANGE `system_generated` `system_generated` tinyint(4) NOT NULL DEFAULT 1;
ALTER TABLE `#__jlike_todos` CHANGE `parent_id` `parent_id` int(11) NOT NULL DEFAULT 0 COMMENT 'Shika lesson prerequisites id';
ALTER TABLE `#__jlike_todos` CHANGE `list_id` `list_id` int(11) NOT NULL DEFAULT 0 COMMENT 'jlike list id';
ALTER TABLE `#__jlike_todos` CHANGE `modified_date` `modified_date` datetime DEFAULT NULL COMMENT 'modified date';
ALTER TABLE `#__jlike_todos` CHANGE `modified_by` `modified_by` int(11) NOT NULL DEFAULT 0;
ALTER TABLE `#__jlike_todos` CHANGE `can_override` `can_override` tinyint(4) NOT NULL DEFAULT 0;
ALTER TABLE `#__jlike_todos` CHANGE `overriden` `overriden` tinyint(4) NOT NULL DEFAULT 0;
ALTER TABLE `#__jlike_todos` CHANGE `params` `params` text DEFAULT NULL;
ALTER TABLE `#__jlike_todos` CHANGE `todo_list_id` `todo_list_id` int(11) NOT NULL DEFAULT 0;
ALTER TABLE `#__jlike_todos` CHANGE `ideal_time` `ideal_time` int(11) NOT NULL DEFAULT 0;
ALTER TABLE `#__jlike_todos` CHANGE `done_by` `done_by` int(11) unsigned NOT NULL DEFAULT 0;
ALTER TABLE `#__jlike_todos` CHANGE `done_date` `done_date` datetime DEFAULT NULL;

ALTER TABLE `#__jlike_likeStatusXref` CHANGE `content_id` `content_id` int(10) NOT NULL COMMENT 'Foreign Key (#__jlike_content table)';
ALTER TABLE `#__jlike_likeStatusXref` CHANGE `status_id` `status_id` int(11) NOT NULL DEFAULT 0;
ALTER TABLE `#__jlike_likeStatusXref` CHANGE `user_id` `user_id` int(15) NOT NULL DEFAULT 0;
ALTER TABLE `#__jlike_likeStatusXref` CHANGE `cdate` `cdate` datetime DEFAULT NULL;
ALTER TABLE `#__jlike_likeStatusXref` CHANGE `mdate` `mdate` datetime DEFAULT NULL;

ALTER TABLE `#__jlike_rating` CHANGE `user_id` `user_id` int(11) NOT NULL DEFAULT 0 COMMENT 'User id';
ALTER TABLE `#__jlike_rating` CHANGE `content_id` `content_id` int(11) NOT NULL DEFAULT 0 COMMENT 'Foreign Key (#__jlike_content table)';
ALTER TABLE `#__jlike_rating` CHANGE `rating_upto` `rating_upto` int(11) NOT NULL DEFAULT 0 COMMENT 'Total rating';
ALTER TABLE `#__jlike_rating` CHANGE `user_rating` `user_rating` int(11) NOT NULL DEFAULT 0 COMMENT 'User given rating';
ALTER TABLE `#__jlike_rating` CHANGE `created_date` `created_date` datetime DEFAULT NULL COMMENT 'Created date and time of rating';
ALTER TABLE `#__jlike_rating` CHANGE `modified_date` `modified_date` datetime DEFAULT NULL COMMENT 'Modified date and time of rating';

ALTER TABLE `#__jlike_reminders` CHANGE `ordering` `ordering` INT(11) NOT NULL DEFAULT 0;
ALTER TABLE `#__jlike_reminders` CHANGE `state` `state` TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE `#__jlike_reminders` CHANGE `checked_out` `checked_out` INT(11) NOT NULL DEFAULT 0;
ALTER TABLE `#__jlike_reminders` CHANGE `checked_out_time` `checked_out_time` DATETIME DEFAULT NULL;
ALTER TABLE `#__jlike_reminders` CHANGE `created_by` `created_by` INT(11) NOT NULL DEFAULT 0;
ALTER TABLE `#__jlike_reminders` CHANGE `modified_by` `modified_by` INT(11) NOT NULL DEFAULT 0;
ALTER TABLE `#__jlike_reminders` CHANGE `title` `title` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'Title of the reminder';
ALTER TABLE `#__jlike_reminders` CHANGE `days_before` `days_before` INT(11) NOT NULL DEFAULT 0 COMMENT 'Number of days before the due date, when the reminder is sent';
ALTER TABLE `#__jlike_reminders` CHANGE `email_template` `email_template` TEXT DEFAULT NULL COMMENT 'The body of the email that will be sent';
ALTER TABLE `#__jlike_reminders` CHANGE `subject` `subject` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'Subject of email';
ALTER TABLE `#__jlike_reminders` CHANGE `content_type` `content_type` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'distinct jlike_content.element table';
ALTER TABLE `#__jlike_reminders` CHANGE `cc` `cc` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'Add emails to sent,mulptiple emails seprated by comma';
ALTER TABLE `#__jlike_reminders` CHANGE `mailfrom` `mailfrom` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'Email id using which reminder mail should be send';
ALTER TABLE `#__jlike_reminders` CHANGE `fromname` `fromname` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'Fron name in reminder mail';
ALTER TABLE `#__jlike_reminders` CHANGE `replyto` `replyto` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'Email id to which mail reply should be sent';
ALTER TABLE `#__jlike_reminders` CHANGE `replytoname` `replytoname` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'Reply to name used in reminder mail';

ALTER TABLE `#__jlike_reminder_contentids` CHANGE `reminder_id` `reminder_id` int(11) NOT NULL DEFAULT 0 COMMENT 'FK to jlike_reminders.id';
ALTER TABLE `#__jlike_reminder_contentids` CHANGE `content_id` `content_id` int(11) NOT NULL DEFAULT 0 COMMENT 'Foreign Key (#__jlike_content table)';

ALTER TABLE `#__jlike_reminder_sent` CHANGE `todo_id` `todo_id` int(11) NOT NULL DEFAULT 0 COMMENT 'FK to jlike_todos.id';
ALTER TABLE `#__jlike_reminder_sent` CHANGE `reminder_id` `reminder_id` int(11) NOT NULL DEFAULT 0 COMMENT 'FK to jlike_reminders.id';
ALTER TABLE `#__jlike_reminder_sent` CHANGE `sent_on` `sent_on` datetime DEFAULT NULL COMMENT 'Date and time when the reminder was sent';

ALTER TABLE `#__jlike_types` CHANGE `type` `type` varchar(255) NOT NULL DEFAULT '';
ALTER TABLE `#__jlike_types` CHANGE `subtype` `subtype` varchar(50) NOT NULL DEFAULT '';
ALTER TABLE `#__jlike_types` CHANGE `client` `client` varchar(255) NOT NULL DEFAULT '';
ALTER TABLE `#__jlike_types` CHANGE `params` `params` text DEFAULT NULL;

ALTER TABLE `#__jlike_pathnode_graph` CHANGE `path_id` `path_id` int(11) NOT NULL DEFAULT 0 COMMENT 'context / path ';
ALTER TABLE `#__jlike_pathnode_graph` CHANGE `lft` `lft` int(11) NOT NULL DEFAULT 0;
ALTER TABLE `#__jlike_pathnode_graph` CHANGE `node` `node` int(11) NOT NULL DEFAULT 0 COMMENT 'is content_id of jlike_content OR path_id of jlike_path table';
ALTER TABLE `#__jlike_pathnode_graph` CHANGE `rgt` `rgt` int(11) NOT NULL DEFAULT 0;
ALTER TABLE `#__jlike_pathnode_graph` CHANGE `order` `order` int(11) NOT NULL DEFAULT 0 COMMENT 'Display order';
ALTER TABLE `#__jlike_pathnode_graph` CHANGE `isPath` `isPath` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'if 1 = path and 0 = node';
ALTER TABLE `#__jlike_pathnode_graph` CHANGE `this_compulsory` `this_compulsory` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Current/This is compulsory or not, 1 is compulsory 0 is optional';
ALTER TABLE `#__jlike_pathnode_graph` CHANGE `delay` `delay` int(11) NOT NULL DEFAULT 0;
ALTER TABLE `#__jlike_pathnode_graph` CHANGE `duration` `duration` int(11) NOT NULL DEFAULT 0;
ALTER TABLE `#__jlike_pathnode_graph` CHANGE `visibility` `visibility` tinyint(4) NOT NULL DEFAULT 1;

ALTER TABLE `#__jlike_paths` CHANGE `path_title` `path_title` varchar(255) CHARACTER SET latin1 NOT NULL DEFAULT '' COMMENT 'eg. Student, Coach';
ALTER TABLE `#__jlike_paths` CHANGE `alias` `alias` varchar(255) CHARACTER SET latin1 NOT NULL DEFAULT '';
ALTER TABLE `#__jlike_paths` CHANGE `path_description` `path_description` text CHARACTER SET latin1 DEFAULT NULL;
ALTER TABLE `#__jlike_paths` CHANGE `path_image` `path_image` text CHARACTER SET latin1 DEFAULT NULL;
ALTER TABLE `#__jlike_paths` CHANGE `category_id` `category_id` int(11) NOT NULL DEFAULT 0 COMMENT 'This will be Joomla Category';
ALTER TABLE `#__jlike_paths` CHANGE `order` `order` int(11) NOT NULL DEFAULT 0 COMMENT 'Display order';
ALTER TABLE `#__jlike_paths` CHANGE `path_type` `path_type` varchar(255) CHARACTER SET latin1 NOT NULL DEFAULT '' COMMENT 'identifier of path_type table';
ALTER TABLE `#__jlike_paths` CHANGE `depth` `depth` tinyint(4) NOT NULL DEFAULT 0;
ALTER TABLE `#__jlike_paths` CHANGE `params` `params` text CHARACTER SET latin1 DEFAULT NULL;
ALTER TABLE `#__jlike_paths` CHANGE `state` `state` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Published/Unpublished';
ALTER TABLE `#__jlike_paths` CHANGE `created_by` `created_by` int(11) NOT NULL DEFAULT 0 COMMENT 'Created By user id';
ALTER TABLE `#__jlike_paths` CHANGE `created_date` `created_date` date DEFAULT NULL COMMENT 'Created date';
ALTER TABLE `#__jlike_paths` CHANGE `modified_by` `modified_by` int(11) NOT NULL DEFAULT 0;
ALTER TABLE `#__jlike_paths` CHANGE `modified_date` `modified_date` date DEFAULT NULL;

ALTER TABLE `#__jlike_path_type` CHANGE `type_title` `type_title` varchar(255) NOT NULL DEFAULT '' COMMENT 'eg. general, learning';
ALTER TABLE `#__jlike_path_type` CHANGE `identifier` `identifier` varchar(100) NOT NULL DEFAULT '' COMMENT 'eg. com_jlike.learning';
ALTER TABLE `#__jlike_path_type` CHANGE `params` `params` text DEFAULT NULL COMMENT 'Used to Define specific fields needed for this path type, specific actions on various events etc';

ALTER TABLE `#__jlike_path_user` CHANGE `path_id` `path_id` int(11) DEFAULT 0 COMMENT 'jlike_paths PK';
ALTER TABLE `#__jlike_path_user` CHANGE `user_id` `user_id` int(11) NOT NULL DEFAULT 0;
ALTER TABLE `#__jlike_path_user` CHANGE `subscribed_date` `subscribed_date` datetime DEFAULT NULL;
ALTER TABLE `#__jlike_path_user` CHANGE `completed_date` `completed_date` datetime DEFAULT NULL;

ALTER TABLE `#__jlike_ratings` CHANGE `rating_type_id` `rating_type_id` int(11) NOT NULL DEFAULT 0;
ALTER TABLE `#__jlike_ratings` CHANGE `title` `title` varchar(500) NOT NULL DEFAULT '';
ALTER TABLE `#__jlike_ratings` CHANGE `review` `review` text DEFAULT NULL;
ALTER TABLE `#__jlike_ratings` CHANGE `submitted_by` `submitted_by` int(11) NOT NULL DEFAULT 0;
ALTER TABLE `#__jlike_ratings` CHANGE `content_id` `content_id` int(11) NOT NULL DEFAULT 0;
ALTER TABLE `#__jlike_ratings` CHANGE `rating_scale` `rating_scale` tinyint(3) NOT NULL DEFAULT 0;
ALTER TABLE `#__jlike_ratings` CHANGE `rating` `rating` tinyint(3) NOT NULL DEFAULT 0;
ALTER TABLE `#__jlike_ratings` CHANGE `created_date` `created_date` datetime DEFAULT NULL;
ALTER TABLE `#__jlike_ratings` CHANGE `created_by` `created_by` int(11) NOT NULL DEFAULT 0;
ALTER TABLE `#__jlike_ratings` CHANGE `modified_date` `modified_date` datetime DEFAULT NULL;
ALTER TABLE `#__jlike_ratings` CHANGE `tjucm_content_id` `tjucm_content_id` int(11) NOT NULL DEFAULT 0;
ALTER TABLE `#__jlike_ratings` CHANGE `state` `state` tinyint(3) NOT NULL DEFAULT 1;

ALTER TABLE `#__jlike_rating_types` CHANGE `client` `client` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '';
ALTER TABLE `#__jlike_rating_types` CHANGE `code` `code` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '';
ALTER TABLE `#__jlike_rating_types` CHANGE `is_default` `is_default` tinyint(1) NOT NULL DEFAULT 0;
ALTER TABLE `#__jlike_rating_types` CHANGE `title` `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '';
ALTER TABLE `#__jlike_rating_types` CHANGE `show_title` `show_title` tinyint(1) NOT NULL DEFAULT 0;
ALTER TABLE `#__jlike_rating_types` CHANGE `title_required` `title_required` tinyint(1) NOT NULL DEFAULT 0;
ALTER TABLE `#__jlike_rating_types` CHANGE `show_rating` `show_rating` tinyint(1) NOT NULL DEFAULT 0;
ALTER TABLE `#__jlike_rating_types` CHANGE `rating_required` `rating_required` tinyint(1) NOT NULL DEFAULT 0;
ALTER TABLE `#__jlike_rating_types` CHANGE `rating_scale` `rating_scale` tinyint(3) NOT NULL DEFAULT '5';
ALTER TABLE `#__jlike_rating_types` CHANGE `show_review` `show_review` tinyint(1) NOT NULL DEFAULT 0;
ALTER TABLE `#__jlike_rating_types` CHANGE `review_required` `review_required` tinyint(1) NOT NULL DEFAULT 0;
ALTER TABLE `#__jlike_rating_types` CHANGE `tjucm_type_id` `tjucm_type_id` int(11) NOT NULL DEFAULT 0;
ALTER TABLE `#__jlike_rating_types` CHANGE `state` `state` tinyint(3) NOT NULL DEFAULT 1;
ALTER TABLE `#__jlike_rating_types` CHANGE `show_all_rating` `show_all_rating` tinyint(1) NOT NULL DEFAULT 0;

ALTER TABLE `#__jlike_reminder_sent`
ADD COLUMN `r_id` INT UNSIGNED NULL COMMENT 'Refers to r_id of recurring events',
ADD COLUMN `attendee_id` INT UNSIGNED NULL COMMENT 'Refers to attendees table';