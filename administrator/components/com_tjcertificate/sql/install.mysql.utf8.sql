CREATE TABLE IF NOT EXISTS `#__tj_certificate_templates` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(150) NOT NULL DEFAULT '',
  `unique_code` varchar(100) NOT NULL DEFAULT '',
  `body` text DEFAULT NULL,
  `template_css` text DEFAULT NULL,
  `client` varchar(100) NOT NULL DEFAULT '' COMMENT 'e.g. com_jticketing.event, com_tjlms.course',
  `ordering` int(11) NOT NULL DEFAULT 0,
  `state` tinyint(3) NOT NULL DEFAULT 0,
  `checked_out` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `checked_out_time` datetime DEFAULT NULL,
  `created_by` int(11) NOT NULL DEFAULT 0,
  `created_on` datetime DEFAULT NULL,
  `modified_by` int(11) NOT NULL DEFAULT 0,
  `modified_on` datetime DEFAULT NULL,
  `is_public` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1-Private, 2-Public',
  `params` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY unqk_code (`unique_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__tj_certificate_issue` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `unique_certificate_id` varchar(150) NOT NULL DEFAULT '',
  `certificate_template_id` int(11) NOT NULL DEFAULT 0,
  `generated_body` text DEFAULT NULL,
  `client` varchar(100) NOT NULL DEFAULT '',
  `client_id` int(11) NOT NULL DEFAULT 0,
  `client_issued_to` int(11) NOT NULL DEFAULT 0,
  `client_issued_to_name` varchar(400) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `user_id` int(11) NOT NULL DEFAULT 0,
  `comment` text DEFAULT NULL,
  `state` tinyint(3) NOT NULL DEFAULT 0,
  `issued_on` datetime DEFAULT NULL,
  `expired_on` datetime DEFAULT NULL,
  `is_external` tinyint(1) NOT NULL DEFAULT 0,
  `name` varchar(255) NOT NULL DEFAULT '',
  `issuing_org` varchar(255) NOT NULL DEFAULT '',
  `cert_url` text DEFAULT NULL,
  `cert_file` varchar(255) NOT NULL DEFAULT '',
  `created_by` int(11) NOT NULL DEFAULT 0,
  `access` tinyint(1) NOT NULL DEFAULT 0,
  `status` varchar(64) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY unqk_certificate_id (`unique_certificate_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

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
  `params` text DEFAULT NULL,
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
