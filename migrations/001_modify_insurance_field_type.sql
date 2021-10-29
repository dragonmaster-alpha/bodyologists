ALTER TABLE `wde_customers`
	CHANGE COLUMN `birthday` `birthday` DATE NULL DEFAULT NULL AFTER `gender`;

ALTER TABLE `wde_customers`
	CHANGE COLUMN `insurance` `insurance` TEXT NULL COLLATE 'utf8_general_ci' AFTER `extra`;