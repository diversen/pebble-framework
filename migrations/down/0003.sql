ALTER TABLE `auth_cookie`
  MODIFY `expires` INT(10) DEFAULT '0';

ALTER TABLE `auth_cookie`
  DROP INDEX `idx_cookie`,
  ADD KEY `idx_cookie` (`cookie_id`);
