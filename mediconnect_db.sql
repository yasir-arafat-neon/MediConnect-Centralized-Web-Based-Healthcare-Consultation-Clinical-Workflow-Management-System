-- ==========================================================
-- DATABASE INITIALIZATION SCRIPT FOR XAMPP (phpMyAdmin / MySQL)
-- Project: MediConnect: Centralized Web-Based Healthcare Consultation & Clinical Workflow Management System
-- Target Engine: MySQL / MariaDB (XAMPP Default)
-- ==========================================================

CREATE DATABASE IF NOT EXISTS `mediconnect_db`
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE `mediconnect_db`;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `departments`;
DROP TABLE IF EXISTS `doctor_profiles`;
DROP TABLE IF EXISTS `doctor_schedules`;
DROP TABLE IF EXISTS `appointments`;
DROP TABLE IF EXISTS `prescriptions`;
DROP TABLE IF EXISTS `doctor_reviews`;
DROP TABLE IF EXISTS `system_audit_logs`;
SET FOREIGN_KEY_CHECKS = 1;

-- --------------------------------------------------------
-- Table: `users` (Central credentials, role definition, and account statuses)
-- --------------------------------------------------------
CREATE TABLE `users` (
  `user_id` INT AUTO_INCREMENT NOT NULL COMMENT 'Surrogate Primary Key',
  `email` VARCHAR(120) UNIQUE NOT NULL COMMENT 'User Login Identifier',
  `password_hash` VARCHAR(255) NOT NULL COMMENT 'Bcrypt Hashed Password',
  `role` ENUM('patient','doctor','admin') NOT NULL COMMENT 'RBAC Authorization',
  `full_name` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(20) NOT NULL,
  `status` ENUM('active','pending','suspended') NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: `departments` (Hospital medical specialties and consultation fees)
-- --------------------------------------------------------
CREATE TABLE `departments` (
  `dept_id` INT AUTO_INCREMENT NOT NULL,
  `dept_name` VARCHAR(80) NOT NULL,
  `dept_code` VARCHAR(15) UNIQUE NOT NULL,
  `description` TEXT NULL DEFAULT NULL,
  `consultation_fee` DECIMAL(8,2) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
  PRIMARY KEY (`dept_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: `doctor_profiles` (Doctor credentials, license numbers, and department link)
-- --------------------------------------------------------
CREATE TABLE `doctor_profiles` (
  `doctor_id` INT AUTO_INCREMENT NOT NULL,
  `user_id` INT NOT NULL,
  `dept_id` INT NOT NULL,
  `license_number` VARCHAR(50) UNIQUE NOT NULL,
  `qualification` VARCHAR(150) NOT NULL,
  `experience_years` INT NOT NULL,
  `is_verified` TINYINT(1) DEFAULT 0 NOT NULL,
  PRIMARY KEY (`doctor_id`),
  CONSTRAINT `fk_doctor_profiles_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_doctor_profiles_dept_id` FOREIGN KEY (`dept_id`) REFERENCES `departments` (`dept_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: `doctor_schedules` (Doctor working shifts, time windows, and patient quotas)
-- --------------------------------------------------------
CREATE TABLE `doctor_schedules` (
  `schedule_id` INT AUTO_INCREMENT NOT NULL,
  `doctor_id` INT NOT NULL,
  `day_of_week` ENUM('Mon','Tue','Wed','Thu','Fri','Sat','Sun') NOT NULL,
  `start_time` TIME NOT NULL,
  `end_time` TIME NOT NULL,
  `slot_duration_minutes` INT DEFAULT 20 NOT NULL,
  `max_quota` INT DEFAULT 15 NOT NULL,
  `is_active` TINYINT(1) DEFAULT 1 NOT NULL,
  PRIMARY KEY (`schedule_id`),
  CONSTRAINT `fk_doctor_schedules_doctor_id` FOREIGN KEY (`doctor_id`) REFERENCES `doctor_profiles` (`doctor_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: `appointments` (Consultation reservations between patients and doctors)
-- --------------------------------------------------------
CREATE TABLE `appointments` (
  `appointment_id` INT AUTO_INCREMENT NOT NULL,
  `token_number` VARCHAR(25) UNIQUE NOT NULL,
  `patient_user_id` INT NOT NULL,
  `doctor_id` INT NOT NULL,
  `appointment_date` DATE NOT NULL,
  `appointment_time` TIME NOT NULL,
  `status` ENUM('booked','completed','cancelled','no_show') NOT NULL,
  `symptoms` TEXT NULL DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
  PRIMARY KEY (`appointment_id`),
  CONSTRAINT `fk_appointments_patient_user_id` FOREIGN KEY (`patient_user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_appointments_doctor_id` FOREIGN KEY (`doctor_id`) REFERENCES `doctor_profiles` (`doctor_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: `prescriptions` (E-Prescriptions and clinical diagnostic directions issued by doctors)
-- --------------------------------------------------------
CREATE TABLE `prescriptions` (
  `prescription_id` INT AUTO_INCREMENT NOT NULL,
  `appointment_id` INT UNIQUE NOT NULL,
  `diagnosis` TEXT NOT NULL,
  `medications_json` LONGTEXT NOT NULL COMMENT 'Drug, dosage, frequency, days',
  `dietary_notes` TEXT NULL DEFAULT NULL,
  `lab_tests_ordered` TEXT NULL DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
  PRIMARY KEY (`prescription_id`),
  CONSTRAINT `fk_prescriptions_appointment_id` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`appointment_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: `doctor_reviews` (Patient feedback and ratings for completed appointments)
-- --------------------------------------------------------
CREATE TABLE `doctor_reviews` (
  `review_id` INT AUTO_INCREMENT NOT NULL,
  `appointment_id` INT UNIQUE NOT NULL,
  `patient_id` INT NOT NULL,
  `doctor_id` INT NOT NULL,
  `rating` TINYINT(1) NOT NULL COMMENT '1 to 5 Stars',
  `comment` TEXT NULL DEFAULT NULL,
  `submitted_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
  PRIMARY KEY (`review_id`),
  CONSTRAINT `fk_doctor_reviews_appointment_id` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`appointment_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_doctor_reviews_patient_id` FOREIGN KEY (`patient_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_doctor_reviews_doctor_id` FOREIGN KEY (`doctor_id`) REFERENCES `doctor_profiles` (`doctor_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: `system_audit_logs` (Security records, login attempts, and administrative modifications)
-- --------------------------------------------------------
CREATE TABLE `system_audit_logs` (
  `log_id` BIGINT AUTO_INCREMENT NOT NULL,
  `actor_user_id` INT NULL DEFAULT NULL,
  `event_type` VARCHAR(50) NOT NULL COMMENT 'AUTH_SUCCESS, PRESCRIPTION_ISSUED',
  `ip_address` VARCHAR(45) NOT NULL,
  `details` TEXT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
  PRIMARY KEY (`log_id`),
  CONSTRAINT `fk_system_audit_logs_actor_user_id` FOREIGN KEY (`actor_user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================================
-- SAMPLE SEED DATA FOR TESTING IN XAMPP
-- Default test password for all accounts: Password123#
-- ==========================================================

INSERT INTO `users` (`email`, `password_hash`, `role`, `full_name`, `phone`, `status`) VALUES
('sarah.patient@example.com', '$2y$10$eACCYoNOHEqgkT5Z2Qz3tOaK9k5qfR8m8U1u9Xm3jT5hF1yKz.G2S', 'patient', 'Sarah Jenkins', '+1-555-0192', 'active'),
('dr.robert.chen@hospital.org', '$2y$10$eACCYoNOHEqgkT5Z2Qz3tOaK9k5qfR8m8U1u9Xm3jT5hF1yKz.G2S', 'doctor', 'Dr. Robert Chen, MD', '+1-555-0144', 'active'),
('admin@mediconnect.local', '$2y$10$eACCYoNOHEqgkT5Z2Qz3tOaK9k5qfR8m8U1u9Xm3jT5hF1yKz.G2S', 'admin', 'System Administrator', '+1-555-0100', 'active');

INSERT INTO `departments` (`dept_name`, `dept_code`, `description`, `consultation_fee`) VALUES
('Cardiology & Vascular', 'CARD-01', 'Heart, cardiovascular diagnostics, and hypertension management', 75.00),
('Neurology & Brain Health', 'NEUR-02', 'Neurological disorders, migraines, and cognitive care', 85.00),
('Pediatrics & Child Care', 'PEDI-03', 'Infant, child, and adolescent healthcare', 50.00);

INSERT INTO `doctor_profiles` (`user_id`, `dept_id`, `license_number`, `qualification`, `experience_years`, `is_verified`) VALUES
(2, 1, 'MED-REG-884920', 'MBBS, MD (Cardiology), FACC', 12, 1);

INSERT INTO `doctor_schedules` (`doctor_id`, `day_of_week`, `start_time`, `end_time`, `slot_duration_minutes`, `max_quota`, `is_active`) VALUES
(1, 'Mon', '09:00:00', '13:00:00', 20, 12, 1),
(1, 'Wed', '14:00:00', '18:00:00', 20, 12, 1);

INSERT INTO `appointments` (`token_number`, `patient_user_id`, `doctor_id`, `appointment_date`, `appointment_time`, `status`, `symptoms`) VALUES
('TKN-2026-081', 1, 1, '2026-09-10', '10:20:00', 'booked', 'Mild recurrent chest tightness and elevated resting pulse rate.');

INSERT INTO `system_audit_logs` (`actor_user_id`, `event_type`, `ip_address`, `details`) VALUES
(3, 'SYSTEM_INIT', '127.0.0.1', 'Initial database migration and admin credential seeding in XAMPP environment.');
