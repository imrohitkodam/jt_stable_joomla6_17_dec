
CREATE TABLE IF NOT EXISTS `#__jitsi_attendee` (
  `email` varchar(100) NOT NULL DEFAULT '0',
  `eventid` int(11) NOT NULL,
  `intime` datetime NOT NULL,
  `outtime` datetime NOT NULL,
  `timespent` int(11) NOT NULL COMMENT 'total timespent in minutes'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1;


ALTER TABLE `#__jitsi_attendee`
  ADD KEY `eventid` (`eventid`),
  ADD KEY `email` (`email`),
  ADD KEY `event_email` (`email`,`eventid`);
COMMIT;
