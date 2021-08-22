
CREATE TABLE `auth` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `password_hash` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `verified` tinyint(1) DEFAULT '0',
  `random` varchar(255) NOT NULL,
  `super` int(1) DEFAULT '0',
  `locked` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_email` (`email`),
  KEY `idx_random` (`random`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;