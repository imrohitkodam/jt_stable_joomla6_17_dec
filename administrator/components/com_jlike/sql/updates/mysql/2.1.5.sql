ALTER TABLE `#__jlike_paths` ADD COLUMN `subscribe_start_date` DATETIME DEFAULT NULL AFTER `modified_date`;
ALTER TABLE `#__jlike_paths` ADD COLUMN `subscribe_end_date` DATETIME DEFAULT NULL AFTER `subscribe_start_date`;
ALTER TABLE `#__jlike_paths` CHANGE `created_date` `created_date` date NULL DEFAULT NULL COMMENT 'Created date';
ALTER TABLE `#__jlike_paths` CHANGE `modified_date` `modified_date` date NULL DEFAULT NULL;
ALTER TABLE `#__jlike_paths` ADD COLUMN `access` INT(10) NOT NULL DEFAULT 0 AFTER `subscribe_end_date`;
ALTER TABLE `#__jlike_todos` ADD COLUMN `done_by` INT(11) UNSIGNED NOT NULL DEFAULT 0 AFTER `ideal_time`, ADD COLUMN `done_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `done_by`;
ALTER TABLE `#__jlike_todos` ADD KEY `unique_todo` (`content_id`,`assigned_to`);
ALTER TABLE `#__jlike_todos` ADD KEY `assigned_to` (`assigned_to`);
