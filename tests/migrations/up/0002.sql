DROP TABLE IF EXISTS `table_2`;

CREATE TABLE `table_2` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `cookie_id` varchar(100) NOT NULL,
  `auth_id` int(10) NOT NULL,
  `last_login` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `expires` int(10) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_cookie` (`cookie_id`),
  KEY `idx_auth_id` (`auth_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;