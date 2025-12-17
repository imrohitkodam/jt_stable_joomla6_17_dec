--
-- Columns Deault value changed to null
--

ALTER TABLE `#__vendor_client_xref` ALTER COLUMN `payment_gateway` SET DEFAULT 'NULL';
ALTER TABLE `#__vendor_client_xref` ALTER COLUMN `params` SET DEFAULT 'NULL';

--
-- Deleting old vendors table
--

DROP TABLE IF EXISTS `#__tj_vendors`;
