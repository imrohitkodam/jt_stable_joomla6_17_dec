ALTER TABLE `#__tj_certificate_issue` ADD `client_issued_to` int(11) NOT NULL DEFAULT 0 AFTER `user_id`;
ALTER TABLE `#__tj_certificate_issue` ADD `client_issued_to_name` varchar(400) COLLATE 'utf8mb4_unicode_ci' NOT NULL DEFAULT '' AFTER `client_issued_to`;
