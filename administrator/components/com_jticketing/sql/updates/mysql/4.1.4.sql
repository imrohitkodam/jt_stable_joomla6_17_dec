CREATE TABLE IF NOT EXISTS `#__jticketing_pdf_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_id` int(11) NOT NULL DEFAULT 0,
  `vendor_id` int(11) NOT NULL DEFAULT 0,
  `body` text DEFAULT NULL,
  `css` text DEFAULT NULL,
  `state` int(11) NOT NULL DEFAULT 0,
  `created_on` datetime NULL DEFAULT NULL,
  `updated_on` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
   KEY `event_id` (`event_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1;