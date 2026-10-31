ALTER TABLE `auth_cookie`
  MODIFY `expires` BIGINT UNSIGNED NOT NULL;

UPDATE `auth_cookie`
SET `cookie_id` = SHA2(`cookie_id`, 256);

ALTER TABLE `auth_cookie`
  DROP INDEX `idx_cookie`,
  ADD UNIQUE KEY `idx_cookie` (`cookie_id`);
