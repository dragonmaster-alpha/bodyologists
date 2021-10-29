CREATE TABLE `wde_customer_services` (
    `id` BIGINT NOT NULL AUTO_INCREMENT,
    `user_id` BIGINT NOT NULL,
    `name` varchar(160) NOT NULL,
    `fee` BIGINT NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`) USING BTREE
)
COMMENT='Here we enter user services.'
COLLATE='utf8mb4_general_ci'
ENGINE=InnoDB