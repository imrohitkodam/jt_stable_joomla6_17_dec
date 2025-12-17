ALTER TABLE `#__jticketing_users` ADD COLUMN `registration_type` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1=business 0=company' AFTER `lastname`;
ALTER TABLE `#__jticketing_users` ADD COLUMN `business_name` varchar(100) NOT NULL DEFAULT '' AFTER `registration_type`;
