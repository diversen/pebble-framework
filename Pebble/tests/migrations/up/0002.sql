DROP TABLE IF EXISTS `auth_cookie`;

CREATE TABLE `auth_cookie` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `cookie_id` varchar(100) NOT NULL,
  `auth_id` int(10) NOT NULL,
  `last_login` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `expires` int(10) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_cookie` (`cookie_id`),
  KEY `idx_auth_id` (`auth_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `cache_system`;

CREATE TABLE `cache_system` (
  `id` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `json_key` varchar(1024) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `data` mediumtext COLLATE utf8mb4_unicode_ci,
  `unix_ts` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_system_cache` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;