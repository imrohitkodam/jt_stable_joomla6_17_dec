-- Change engine
ALTER TABLE `#__hierarchy_users` ENGINE = InnoDB;

-- Change charset, collation
ALTER TABLE `#__hierarchy_users` CHANGE `note` `note` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL;
ALTER TABLE `#__hierarchy_users` CHANGE `context` `context` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '';
