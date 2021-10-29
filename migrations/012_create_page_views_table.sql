CREATE TABLE `wde_page_views` (
  `date` date NOT NULL,
  `time` time NOT NULL,
  `object_type` tinyint NOT NULL COMMENT 'Uses constants value in app',
  `object_id` bigint unsigned NOT NULL,
  `object_owner` bigint unsigned NOT NULL,
  `url` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `referer` varchar(1000) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `visitor_ip` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  INDEX `wde_page_views_object_owner_IDX` (`object_owner`,`date`,`object_type`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci