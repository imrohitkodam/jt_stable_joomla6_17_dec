RENAME TABLE `#__media_files_xref` TO `#__tj_media_files_xref`;
RENAME TABLE `#__jticketing_media_files` TO `#__tj_media_files`;
ALTER TABLE `#__jticketing_venues` add column `description` text DEFAULT NULL CHARACTER SET utf8;
