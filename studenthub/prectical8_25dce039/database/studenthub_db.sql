-- ============================================================
-- StudentHub Database Schema (Practical 8)
-- Course: ITUE203 - Web Development Frameworks
-- Database: studenthub_db
-- ============================================================
-- This script creates the database and tables for StudentHub.
-- Safe to import into a fresh MySQL database via phpMyAdmin.
-- Does NOT drop any existing database automatically.
-- ============================================================

-- 1. Create database if not exists
CREATE DATABASE IF NOT EXISTS `studenthub_db`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `studenthub_db`;

-- ============================================================
-- 2. Table: students
-- Stores basic student information
-- Matches existing student data from students.json and register.php
-- ============================================================
CREATE TABLE IF NOT EXISTS `students` (
    `student_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `full_name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(150) NOT NULL,
    `mobile` VARCHAR(15) NULL,
    `course` VARCHAR(100) NOT NULL,
    `year` TINYINT UNSIGNED NOT NULL COMMENT 'Academic year: 1,2,3,4',
    `roll_no` VARCHAR(20) NULL COMMENT 'College roll number (e.g., 25DCE039)',
    `department` VARCHAR(50) NULL COMMENT 'Department: Computer, IT, Civil, AI, etc.',
    `cgpa` DECIMAL(3,2) NULL COMMENT 'CGPA 0.00-10.00',
    `city` VARCHAR(50) NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`student_id`),
    UNIQUE KEY `uq_students_email` (`email`),
    UNIQUE KEY `uq_students_roll_no` (`roll_no`),
    INDEX `idx_students_course` (`course`),
    INDEX `idx_students_department` (`department`),
    INDEX `idx_students_year` (`year`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 3. Table: events
-- Stores StudentHub event information
-- ============================================================
CREATE TABLE IF NOT EXISTS `events` (
    `event_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `title` VARCHAR(200) NOT NULL,
    `description` TEXT NULL,
    `event_date` DATE NOT NULL,
    `location` VARCHAR(200) NOT NULL,
    `category` VARCHAR(50) NULL COMMENT 'e.g., Technical, Cultural, Sports, Workshop',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`event_id`),
    INDEX `idx_events_date` (`event_date`),
    INDEX `idx_events_category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 4. Table: registrations
-- Junction table for many-to-many relationship between students and events
-- ============================================================
CREATE TABLE IF NOT EXISTS `registrations` (
    `registration_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `student_id` INT UNSIGNED NOT NULL,
    `event_id` INT UNSIGNED NOT NULL,
    `registration_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `status` ENUM('registered','attended','cancelled') NOT NULL DEFAULT 'registered',
    PRIMARY KEY (`registration_id`),
    UNIQUE KEY `uq_student_event` (`student_id`, `event_id`),
    FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    INDEX `idx_registrations_student` (`student_id`),
    INDEX `idx_registrations_event` (`event_id`),
    INDEX `idx_registrations_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 5. Sample Data: Students
-- Based on existing students.json data + registration form fields
-- ============================================================
INSERT INTO `students` (`full_name`, `email`, `mobile`, `course`, `year`, `roll_no`, `department`, `cgpa`, `city`) VALUES
('Aarav Patel', 'aarav.patel@student.charusat.edu.in', '9876543210', 'Web Development', 1, '25DCE001', 'Computer', 9.10, 'Ahmedabad'),
('Bhavya Sharma', 'bhavya.sharma@student.charusat.edu.in', '9876543211', 'Data Science', 2, '25DCE015', 'IT', 8.70, 'Surat'),
('Chirag Mehta', 'chirag.mehta@student.charusat.edu.in', '9876543212', 'Programming', 3, '25DCE024', 'Civil', 7.80, 'Rajkot'),
('Diya Joshi', 'diya.joshi@student.charusat.edu.in', '9876543213', 'Cyber Security', 2, '25DCE032', 'AI', 9.30, 'Vadodara'),
('Radhe Kalariya', 'radhe.kalariya@student.charusat.edu.in', '9876543214', 'Web Development', 4, '25DCE039', 'Computer', 9.60, 'Ahmedabad'),
('Priya Shah', 'priya.shah@student.charusat.edu.in', '9876543215', 'Data Science', 1, '25DCE045', 'IT', 8.90, 'Anand'),
('Karan Desai', 'karan.desai@student.charusat.edu.in', '9876543216', 'Programming', 3, '25DCE052', 'Computer', 8.20, 'Nadiad'),
('Meera Patel', 'meera.patel@student.charusat.edu.in', '9876543217', 'Web Development', 2, '25DCE061', 'Civil', 9.00, 'Vadodara');

-- ============================================================
-- 6. Sample Data: Events
-- Typical college events for StudentHub
-- ============================================================
INSERT INTO `events` (`title`, `description`, `event_date`, `location`, `category`) VALUES
('TechFest 2026', 'Annual technical festival with coding competitions, hackathons, and tech talks.', '2026-02-15', 'Main Auditorium, CHARUSAT', 'Technical'),
('Web Development Workshop', 'Hands-on workshop on modern web technologies: HTML5, CSS3, JavaScript, PHP.', '2026-03-10', 'Computer Lab 1, DEPSTAR', 'Workshop'),
('Data Science Symposium', 'Expert talks on Machine Learning, AI, and Data Analytics.', '2026-04-05', 'Seminar Hall, DEPSTAR', 'Technical'),
('Cultural Night', 'Annual cultural program with music, dance, and drama performances.', '2026-02-28', 'Open Air Theatre, CHARUSAT', 'Cultural'),
('Sports Week', 'Inter-department sports competitions: Cricket, Football, Badminton, Table Tennis.', '2026-01-20', 'Sports Complex, CHARUSAT', 'Sports'),
('Cyber Security Awareness', 'Seminar on cyber security best practices and ethical hacking basics.', '2026-03-25', 'Conference Room, DEPSTAR', 'Workshop'),
('Alumni Meet 2026', 'Networking event with CHARUSAT alumni from various batches.', '2026-05-10', 'Banquet Hall, CHARUSAT', 'Cultural'),
('Final Year Project Expo', 'Showcase of final year student projects from all departments.', '2026-04-20', 'Project Lab, DEPSTAR', 'Technical');

-- ============================================================
-- 7. Sample Data: Registrations
-- Linking students to events (many-to-many)
-- ============================================================
INSERT INTO `registrations` (`student_id`, `event_id`, `status`) VALUES
-- Aarav Patel (student_id=1) registers for 3 events
(1, 1, 'registered'),
(1, 2, 'registered'),
(1, 4, 'attended'),

-- Bhavya Sharma (student_id=2) registers for 2 events
(2, 1, 'registered'),
(2, 3, 'registered'),

-- Chirag Mehta (student_id=3) registers for 2 events
(3, 5, 'attended'),
(3, 8, 'registered'),

-- Diya Joshi (student_id=4) registers for 3 events
(4, 1, 'registered'),
(4, 3, 'registered'),
(4, 6, 'registered'),

-- Radhe Kalariya (student_id=5) registers for 4 events
(5, 1, 'registered'),
(5, 2, 'attended'),
(5, 7, 'registered'),
(5, 8, 'registered'),

-- Priya Shah (student_id=6) registers for 2 events
(6, 2, 'registered'),
(6, 3, 'registered'),

-- Karan Desai (student_id=7) registers for 2 events
(7, 1, 'registered'),
(7, 5, 'attended'),

-- Meera Patel (student_id=8) registers for 3 events
(8, 4, 'registered'),
(8, 7, 'registered'),
(8, 8, 'registered');

-- ============================================================
-- 8. Verification Queries (run after import to confirm)
-- ============================================================
-- SELECT 'Students count:' AS info, COUNT(*) AS count FROM students;
-- SELECT 'Events count:' AS info, COUNT(*) AS count FROM events;
-- SELECT 'Registrations count:' AS info, COUNT(*) AS count FROM registrations;
-- 
-- -- Check foreign keys work
-- SELECT s.full_name, e.title, r.status
-- FROM registrations r
-- JOIN students s ON r.student_id = s.student_id
-- JOIN events e ON r.event_id = e.event_id
-- ORDER BY s.full_name, e.event_date;

-- ============================================================
-- End of studenthub_db.sql
-- ============================================================