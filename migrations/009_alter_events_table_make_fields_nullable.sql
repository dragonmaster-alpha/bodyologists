ALTER TABLE `wde_events`
    CHANGE COLUMN `url` `url` VARCHAR(255) NULL COLLATE 'utf8_general_ci' AFTER `title`,
    CHANGE COLUMN `price` `price` DECIMAL(10,2) NULL AFTER `url`,
    CHANGE COLUMN `purchase_link` `purchase_link` TEXT(65535) NULL COLLATE 'utf8_general_ci' AFTER `price`,
    CHANGE COLUMN `start_date` `start_date` DATE NOT NULL AFTER `seller`,
    CHANGE COLUMN `end_date` `end_date` DATE NOT NULL AFTER `start_date`,
    CHANGE COLUMN `start_time` `start_time` TIME NOT NULL AFTER `end_date`,
    CHANGE COLUMN `end_time` `end_time` TIME NOT NULL AFTER `start_time`,
    CHANGE COLUMN `address` `address` VARCHAR(255) NULL COLLATE 'utf8_general_ci' AFTER `end_time`,
    CHANGE COLUMN `city` `city` VARCHAR(150) NULL COLLATE 'utf8_general_ci' AFTER `address`,
    CHANGE COLUMN `state` `state` VARCHAR(150) NULL COLLATE 'utf8_general_ci' AFTER `city`,
    CHANGE COLUMN `zipcode` `zipcode` VARCHAR(150) NULL COLLATE 'utf8_general_ci' AFTER `state`,
    CHANGE COLUMN `text` `text` TEXT(65535) NULL COLLATE 'utf8_general_ci' AFTER `zipcode`,
    CHANGE COLUMN `notes` `notes` TEXT(65535) NULL COLLATE 'utf8_general_ci' AFTER `text`;