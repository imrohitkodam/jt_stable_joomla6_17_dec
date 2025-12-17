--
-- Table structure for table `#__jticketing_waiting_list`
--

CREATE TABLE IF NOT EXISTS `#__jticketing_waiting_list` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `event_id` int(11) NOT NULL DEFAULT 0 COMMENT 'event id is xref id of integration_xref table',
  `behaviour` varchar(100) NOT NULL DEFAULT '' COMMENT 'Classroom Training and E-commerce',
  `status` varchar(2) NOT NULL DEFAULT '' COMMENT 'WL = Waitlist, C = Clear and  CA = Canceled',
  `created_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
   KEY `user_id_idx` (`user_id`),
   KEY `event_id_idx` (`event_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1;