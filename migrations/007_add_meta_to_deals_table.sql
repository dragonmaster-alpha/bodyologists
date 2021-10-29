ALTER TABLE `wde_deals`
    ADD COLUMN `meta` JSON NULL DEFAULT NULL AFTER `modified`;