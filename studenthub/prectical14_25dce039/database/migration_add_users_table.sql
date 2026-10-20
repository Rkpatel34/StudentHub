-- ============================================================
-- Practical 9 Migration: Add users table for authentication
-- Course: ITUE203 - Web Development Frameworks
-- Database: studenthub_db
-- ============================================================
-- Run this AFTER the main studenthub_db.sql from Practical 8
-- This adds the users table for secure authentication
-- Does NOT affect existing students, events, registrations tables
-- ============================================================

USE `studenthub_db`;

-- ============================================================
-- Table: users
-- Stores authentication credentials for login system
-- Separate from 'students' table which stores academic profiles
-- ============================================================
CREATE TABLE IF NOT EXISTS `users` (
    `user_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `username` VARCHAR(50) NOT NULL COMMENT 'Unique username for login',
    `email` VARCHAR(150) NOT NULL COMMENT 'Unique email for login/recovery',
    `password_hash` VARCHAR(255) NOT NULL COMMENT 'bcrypt hash from password_hash()',
    `full_name` VARCHAR(100) NOT NULL COMMENT 'Display name',
    `phone` VARCHAR(15) NULL COMMENT 'Optional phone number',
    `gender` ENUM('Male','Female','Other') NULL,
    `course` VARCHAR(100) NULL COMMENT 'Course preference',
    `year` TINYINT UNSIGNED NULL COMMENT 'Academic year 1-4',
    `role` ENUM('student','admin') NOT NULL DEFAULT 'student' COMMENT 'Future RBAC',
    `is_active` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Account status',
    `last_login` TIMESTAMP NULL DEFAULT NULL COMMENT 'Track last login',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`user_id`),
    UNIQUE KEY `uq_users_username` (`username`),
    UNIQUE KEY `uq_users_email` (`email`),
    INDEX `idx_users_role` (`role`),
    INDEX `idx_users_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Verification
-- ============================================================
-- DESCRIBE users;
-- SELECT * FROM users;

-- ============================================================
-- End of migration
-- ============================================================