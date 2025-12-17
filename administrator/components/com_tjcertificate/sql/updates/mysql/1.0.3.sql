ALTER TABLE `#__tj_certificate_issue` ADD COLUMN `is_external` tinyint(1) NOT NULL DEFAULT 0 AFTER `expired_on`;
ALTER TABLE `#__tj_certificate_issue` ADD COLUMN `name` varchar(255) NOT NULL DEFAULT '' AFTER `is_external`;
ALTER TABLE `#__tj_certificate_issue` ADD COLUMN `issuing_org` varchar(255) NOT NULL DEFAULT '' AFTER `name`;
ALTER TABLE `#__tj_certificate_issue` ADD COLUMN `cert_url` text DEFAULT NULL AFTER `issuing_org`;
ALTER TABLE `#__tj_certificate_issue` ADD COLUMN `cert_file` varchar(255) NOT NULL DEFAULT '' AFTER `cert_url`;
ALTER TABLE `#__tj_certificate_issue` ADD COLUMN `access` tinyint(1) NOT NULL DEFAULT 0 AFTER `cert_file`;
ALTER TABLE `#__tj_certificate_issue` ADD COLUMN `created_by` int(11) NOT NULL DEFAULT 0 AFTER `access`;
ALTER TABLE `#__tj_certificate_issue` ADD COLUMN `status` varchar(64) NOT NULL DEFAULT '' AFTER `created_by`;

--
-- Table structure for table `#__tj_media_files`
--

CREATE TABLE IF NOT EXISTS `#__tj_media_files` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(250) NOT NULL DEFAULT '',
  `type` varchar(250) NOT NULL DEFAULT '',
  `path` varchar(250) COLLATE utf8mb4_bin NOT NULL DEFAULT '',
  `state` tinyint(1) NOT NULL DEFAULT 0,
  `source` varchar(250) NOT NULL DEFAULT '',
  `original_filename` varchar(250) COLLATE utf8mb4_bin NOT NULL DEFAULT '',
  `size` int(11) NOT NULL DEFAULT 0,
  `storage` varchar(250) NOT NULL DEFAULT '',
  `created_by` int(11) NOT NULL DEFAULT 0,
  `access` tinyint(1) NOT NULL DEFAULT 0,
  `created_date` datetime DEFAULT NULL,
  `params` varchar(500) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1;

--
-- Table structure for table `#__tj_media_files_xref`
--

CREATE TABLE IF NOT EXISTS `#__tj_media_files_xref` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `media_id` int(11) NOT NULL DEFAULT 0,
  `client_id` int(11) NOT NULL DEFAULT 0,
  `client` varchar(250) NOT NULL DEFAULT '',
  `is_gallery` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1;
