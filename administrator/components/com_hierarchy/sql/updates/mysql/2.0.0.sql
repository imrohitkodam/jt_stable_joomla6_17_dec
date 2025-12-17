-- Adding default value for all the columns

ALTER TABLE `#__hierarchy_users` CHANGE `context` `context` VARCHAR(255)  NOT NULL DEFAULT '';
ALTER TABLE `#__hierarchy_users` CHANGE `context_id` `context_id` INT(11)  NOT NULL DEFAULT 0;
ALTER TABLE `#__hierarchy_users` CHANGE `created_by` `created_by` INT(11)  NOT NULL DEFAULT 0;
ALTER TABLE `#__hierarchy_users` CHANGE `modified_by` `modified_by` INT(11)  NOT NULL DEFAULT 0;
ALTER TABLE `#__hierarchy_users` CHANGE `created_date` `created_date` datetime DEFAULT NULL;
ALTER TABLE `#__hierarchy_users` CHANGE `modified_date` `modified_date` datetime DEFAULT NULL;
ALTER TABLE `#__hierarchy_users` CHANGE `state` `state` INT(11) NOT NULL DEFAULT 0;
ALTER TABLE `#__hierarchy_users` CHANGE `note` `note` TEXT DEFAULT NULL;
