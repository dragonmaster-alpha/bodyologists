ALTER TABLE `wde_customers`
    ADD COLUMN `approved` TINYINT(1) NOT NULL DEFAULT '0' AFTER `alive`;