CREATE TABLE `acl` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `entity` varchar(255) NOT NULL DEFAULT '',
  `right` varchar(64) NOT NULL DEFAULT '',
  `entity_id` int(11) unsigned NOT NULL,
  `auth_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;