ALTER TABLE `#__jlike_content` DROP `type`;
ALTER TABLE `#__jlike_content` CHANGE `element` `element` VARCHAR(100);
ALTER TABLE `#__jlike_content` ADD UNIQUE `uk_element_pair` (`element_id`, `element`);
ALTER TABLE `#__jlike_todos` DROP `created`;
