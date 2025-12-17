CREATE TABLE IF NOT EXISTS `#__tjvendors_passbook` (
`id` INT(11) UNSIGNED NOT NULL  AUTO_INCREMENT,
`vendor_id` INT(11)  NOT NULL DEFAULT 0,
`currency` VARCHAR(255)  NOT NULL DEFAULT '',
`total` DOUBLE(15,2)  NOT NULL ,
`credit` DOUBLE(15,2)  NOT NULL ,
`debit` DOUBLE(15,2)  NOT NULL ,
`reference_order_id` VARCHAR(255)  NOT NULL DEFAULT '',
`transaction_time` datetime DEFAULT NULL,
`client` VARCHAR(255)  NOT NULL DEFAULT '',
`transaction_id` VARCHAR(255)  NOT NULL DEFAULT '',
`status` TINYINT(1)  NOT NULL DEFAULT 1,
`params` text DEFAULT NULL,
PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__tjvendors_fee` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`vendor_id` INT(11)  NOT NULL DEFAULT 0,
`currency` VARCHAR(255)  NOT NULL DEFAULT '',
`client` VARCHAR(255)  NOT NULL DEFAULT '',
`percent_commission` DOUBLE(15,2)  NOT NULL ,
`flat_commission` DOUBLE(15,2)  NOT NULL ,
PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__vendor_client_xref` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`vendor_id` INT(11)  NOT NULL DEFAULT 0,
`client` VARCHAR(255)  NOT NULL DEFAULT '',
`approved` tinyint(1)  NOT NULL DEFAULT '1',
`state` tinyint(1)  NOT NULL DEFAULT '1',
`params` text DEFAULT NULL,
PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__tjvendors_vendors` (
  `vendor_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `vendor_title` varchar(255) NOT NULL DEFAULT '',
  `address` text DEFAULT NULL,
  `country` int(3) NOT NULL DEFAULT 0,
  `region` int(5) NOT NULL DEFAULT 0,
  `city` varchar(50) NOT NULL DEFAULT '',
  `other_city` varchar(50) NOT NULL DEFAULT '',
  `zip` varchar(50) NOT NULL DEFAULT '',
  `phone_number` varchar(50) NOT NULL DEFAULT '',
  `website_address` varchar(250) NOT NULL DEFAULT '',
  `vat_number` varchar(50) NOT NULL DEFAULT '',
  `alias` varchar(255) NOT NULL DEFAULT '',
  `vendor_description` text DEFAULT NULL,
  `vendor_logo` varchar(255) NOT NULL DEFAULT '',
  `state` tinyint(1) NOT NULL DEFAULT 1,
  `ordering` int(11) NOT NULL DEFAULT 0,
  `checked_out` int(11) NOT NULL DEFAULT 0,
  `checked_out_time` datetime DEFAULT NULL,
  `params` text DEFAULT NULL,
  `created_by` int(11) NOT NULL DEFAULT 0,
  `modified_by` int(11) NOT NULL DEFAULT 0,
  `created_time` datetime DEFAULT NULL,
  `modified_time` datetime DEFAULT NULL,
  PRIMARY KEY (`vendor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;
