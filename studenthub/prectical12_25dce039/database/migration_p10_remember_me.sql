-- ============================================================
-- Practical 10 Migration: Add remember-me token & last_login
-- Course: ITUE203 - Web Development Frameworks
-- Database: studenthub_db
-- ============================================================
-- Run this AFTER Practical 9 migration
-- Adds secure remember-me token support and last_login tracking
-- ============================================================

USE `studenthub_db`;

-- ============================================================
-- 1. Add remember-me token columns to users table
-- ============================================================
ALTER TABLE `users`
    ADD COLUMN `remember_token_hash` VARCHAR(255) NULL COMMENT 'SHA-256 hash of remember-me token' AFTER `last_login`,
    ADD COLUMN `remember_token_expires` TIMESTAMP NULL DEFAULT NULL COMMENT 'Token expiry timestamp' AFTER `remember_token_hash`,
    ADD INDEX `idx_users_remember_token` (`remember_token_hash`);

-- ============================================================
-- 2. Optional: Update last_login for existing users (set to created_at)
-- ============================================================
-- UPDATE `users` SET `last_login` = `created_at` WHERE `last_login` IS NULL;

-- ============================================================
-- Verification
-- ============================================================
-- DESCRIBE users;
-- SELECT user_id, username, email, role, remember_token_hash, remember_token_expires, last_login FROM users;

-- ============================================================
-- End of migration
-- ============================================================