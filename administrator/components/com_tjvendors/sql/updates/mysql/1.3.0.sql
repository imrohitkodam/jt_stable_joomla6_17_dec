--
-- Columns drop
--

ALTER TABLE  `#__vendor_client_xref` DROP  `payment_gateway`;
ALTER TABLE `#__vendor_client_xref` CHANGE `params` `params` text;

