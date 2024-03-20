-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+05:30";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `vcure_health`
--
CREATE DATABASE IF NOT EXISTS `vcure_health` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `vcure_health`;

-- --------------------------------------------------------

--
-- Table structure for table `clinics`
--

CREATE TABLE `appointments` (
  `id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `pataint_id` int(11) NOT NULL,
  `clinic_id` int(11) DEFAULT NULL,
  `department` varchar(191) NOT NULL,
  `symptoms` varchar(191) NOT NULL,
  `booking_date` date NOT NULL,
  `booking_type` enum('clinic','video','chat','voice') NOT NULL DEFAULT 'clinic',
  `booking_time` varchar(20) NOT NULL,
  `clinic_name` varchar(191) DEFAULT NULL,
  `clinic_address` varchar(191) DEFAULT NULL,
  `fees` int(10) NOT NULL,
  `payment_mode` enum('online','offline') NOT NULL DEFAULT 'online',
  `status` enum('active','inactive','canceled') NOT NULL DEFAULT 'active',
  `remarks` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL,
  `deleted_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `appointments_filter`
--

CREATE TABLE `appointments_filter` (
  `id` int(11) NOT NULL,
  `filter_name` varchar(191) NOT NULL,
  `filter_icon` varchar(191) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `clinics`
--

CREATE TABLE `clinics` (
  `id` int(11) NOT NULL,
  `clinic_name` varchar(191) NOT NULL,
  `address` varchar(191) NOT NULL,
  `pin_code` int(10) NOT NULL,
  `mobile` varchar(20) NOT NULL,
  `payment_mode` enum('online','offline') NOT NULL DEFAULT 'online',
  `emergency_time` varchar(20) NOT NULL,
  `primary_care_unit` varchar(191) NOT NULL,
  `open_time` varchar(20) NOT NULL,
  `closeing_time` varchar(20) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `doctors`
--

CREATE TABLE `doctors` (
  `id` int(11) NOT NULL,
  `name` varchar(191) NOT NULL,
  `mobile` varchar(191) NOT NULL,
  `email` varchar(191) NOT NULL,
  `hashed_password` varchar(191) NOT NULL,
  `date_of_birth` date NOT NULL,
  `gender` varchar(20) NOT NULL,
  `profileimage` varchar(255) DEFAULT NULL,
  `kycimage` varchar(255) DEFAULT NULL,
  `license_no` varchar(191) DEFAULT NULL,
  `experience_year` int(11) DEFAULT NULL,
  `education` varchar(255) DEFAULT NULL,
  `expertise_id` int(11) DEFAULT NULL,
  `expertise_name` varchar(191) DEFAULT NULL,
  `professional_bio` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `doctor_appointments`
--

CREATE TABLE `doctor_appointments` (
  `id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `appintment_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `doctor_practice`
--

CREATE TABLE `doctor_practice` (
  `id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `type` enum('clinic','video','voice','chat') NOT NULL DEFAULT 'clinic',
  `is_consultation_on` tinyint(1) NOT NULL DEFAULT 1,
  `price` int(11) NOT NULL,
  `clinic_id` int(11) DEFAULT NULL,
  `clinic_name` varchar(191) DEFAULT NULL,
  `clinic_address` varchar(255) DEFAULT NULL,
  `clinic_mobile` varchar(191) DEFAULT NULL,
  `clinic_email` varchar(255) DEFAULT NULL,
  `clinic_pincode` int(10) DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `doctor_practice_details`
--

CREATE TABLE `doctor_practice_details` (
  `id` int(11) NOT NULL,
  `practice_id` int(11) NOT NULL,
  `days` varchar(191) NOT NULL,
  `start_time` varchar(11) NOT NULL,
  `end_time` varchar(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `expertise`
--

CREATE TABLE `expertise` (
  `id` int(11) NOT NULL,
  `expertise_name` varchar(191) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_temp_tbl`
--

CREATE TABLE `password_temp_tbl` (
  `id` int(11) NOT NULL,
  `email` varchar(191) DEFAULT NULL,
  `mobile` varchar(20) DEFAULT NULL,
  `token` text NOT NULL,
  `otp` int(11) DEFAULT NULL,
  `expiry` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pataint_medical_history`
--

CREATE TABLE `pataint_medical_history` (
  `id` int(11) NOT NULL,
  `height` varchar(11) NOT NULL,
  `bp` varchar(11) NOT NULL,
  `weight` varchar(11) NOT NULL,
  `SpO2` varchar(11) NOT NULL,
  `bmi` varchar(11) NOT NULL,
  `temp` varchar(11) NOT NULL,
  `waist` varchar(11) NOT NULL,
  `allergies` varchar(255) NOT NULL,
  `immunizations` varchar(255) NOT NULL,
  `procedures` varchar(255) NOT NULL,
  `current_condition` varchar(255) NOT NULL,
  `medications` varchar(255) NOT NULL,
  `last_doctor_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pataint_prescriptions`
--

CREATE TABLE `pataint_prescriptions` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `clinic_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `time` varchar(20) NOT NULL,
  `message` varchar(255) DEFAULT NULL,
  `clinic_name` varchar(255) NOT NULL,
  `is_complete` tinyint(4) NOT NULL DEFAULT 0,
  `document_name` varchar(191) NOT NULL,
  `document_path` varchar(191) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `patients`
--

CREATE TABLE `patients` (
  `id` int(11) NOT NULL,
  `member_id` int(11) DEFAULT NULL,
  `first_name` varchar(191) NOT NULL,
  `last_name` varchar(191) NOT NULL,
  `mobile` varchar(20) NOT NULL,
  `gender` varchar(20) NOT NULL,
  `email` varchar(191) NOT NULL,
  `date_of_birth` date NOT NULL,
  `hashed_password` varchar(191) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `city` varchar(191) DEFAULT NULL,
  `state` varchar(191) DEFAULT NULL,
  `pincode` int(11) DEFAULT NULL,
  `landmark` varchar(255) DEFAULT NULL,
  `insurance_file` varchar(191) DEFAULT NULL,
  `master_userid` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `symptoms`
--

CREATE TABLE `symptoms` (
  `id` int(11) NOT NULL,
  `symptom_name` varchar(191) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_reviews`
--

CREATE TABLE `user_reviews` (
  `id` int(11) NOT NULL,
  `user_name` varchar(191) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `appointment_id` int(11) NOT NULL,
  `average_starts` double(3,2) NOT NULL,
  `reviews` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `appointments_filter`
--
ALTER TABLE `appointments_filter`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `clinics`
--
ALTER TABLE `clinics`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `doctors`
--
ALTER TABLE `doctors`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `doctor_appointments`
--
ALTER TABLE `doctor_appointments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `doctor_practice`
--
ALTER TABLE `doctor_practice`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `doctor_practice_details`
--
ALTER TABLE `doctor_practice_details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `expertise`
--
ALTER TABLE `expertise`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_temp_tbl`
--
ALTER TABLE `password_temp_tbl`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pataint_medical_history`
--
ALTER TABLE `pataint_medical_history`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pataint_prescriptions`
--
ALTER TABLE `pataint_prescriptions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `patients`
--
ALTER TABLE `patients`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `symptoms`
--
ALTER TABLE `symptoms`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user_reviews`
--
ALTER TABLE `user_reviews`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `appointments_filter`
--
ALTER TABLE `appointments_filter`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `clinics`
--
ALTER TABLE `clinics`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `doctors`
--
ALTER TABLE `doctors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `doctor_appointments`
--
ALTER TABLE `doctor_appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `doctor_practice`
--
ALTER TABLE `doctor_practice`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `doctor_practice_details`
--
ALTER TABLE `doctor_practice_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `expertise`
--
ALTER TABLE `expertise`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `password_temp_tbl`
--
ALTER TABLE `password_temp_tbl`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pataint_medical_history`
--
ALTER TABLE `pataint_medical_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pataint_prescriptions`
--
ALTER TABLE `pataint_prescriptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `patients`
--
ALTER TABLE `patients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `symptoms`
--
ALTER TABLE `symptoms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_reviews`
--
ALTER TABLE `user_reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
