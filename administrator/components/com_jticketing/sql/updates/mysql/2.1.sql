ALTER TABLE `#__jticketing_attendees` add column `enrollment_id` varchar(100) NOT NULL DEFAULT '' COMMENT 'Enrollment id with prefix';
ALTER TABLE `#__jticketing_attendees` add column `status` varchar(2) NOT NULL DEFAULT '' COMMENT 'A = Appoved, R = Rejected and P = pending';
ALTER TABLE `#__jticketing_attendees` add column `event_id` int(255) NOT NULL DEFAULT 0 COMMENT 'event id is xref id of integration_xref table';
ALTER TABLE `#__jticketing_attendees` add column `ticket_type_id` int(255) NOT NULL DEFAULT 0 COMMENT 'Ticket type id';

--
-- Update Menu Item attendee list
--

UPDATE `#__menu` SET link = 'index.php?option=com_jticketing&view=attendees' where link = 'index.php?option=com_jticketing&view=attendee_list';
