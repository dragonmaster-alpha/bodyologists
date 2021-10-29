CREATE TABLE `wde_events_flags` (
    `id` BIGINT NOT NULL AUTO_INCREMENT,
    `event_id` BIGINT NOT NULL,
    `user_id` BIGINT NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`) USING BTREE
)
COMMENT='Here we track the reports as inappropriate ("flag") for events.'
COLLATE='utf8mb4_general_ci'
ENGINE=InnoDB