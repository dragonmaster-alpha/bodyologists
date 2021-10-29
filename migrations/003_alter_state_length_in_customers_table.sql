ALTER TABLE `wde_customers`
	CHANGE COLUMN `state` `state` VARCHAR(100) NULL COLLATE 'utf8_general_ci' AFTER `city`;