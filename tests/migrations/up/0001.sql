DROP TABLE IF EXISTS `table_1_a`;

CREATE TABLE `table_1_a` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `entity` varchar(100) NOT NULL DEFAULT '',
  `right` varchar(64) NOT NULL DEFAULT '',
  `entity_id` int(11) unsigned NOT NULL,
  `auth_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `table_1_b`;

CREATE TABLE `table_1_b` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `password_hash` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `verified` tinyint(1) DEFAULT '0',
  `random` varchar(100) NOT NULL,
  `super` int(1) DEFAULT '0',
  `locked` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_email` (`email`),
  KEY `idx_random` (`random`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;