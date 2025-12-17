ALTER TABLE `#__jticketing_integration_xref` ADD COLUMN `enable_ticket` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1=Yes 0=No' AFTER `userid`;
