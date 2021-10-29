CREATE TABLE `wde_deals_flags` (
    `id` BIGINT NOT NULL AUTO_INCREMENT,
    `deal_id` BIGINT NOT NULL,
    `user_id` BIGINT NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT NOW(),
    PRIMARY KEY (`id`)
)
    COMMENT='Here we track the reports as inappropriate ("flag") for deals.'
    COLLATE='utf8mb4_general_ci'
;