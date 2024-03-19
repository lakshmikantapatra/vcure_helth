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

--
-- RELATIONSHIPS FOR TABLE `clinics`:
--

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

--
-- RELATIONSHIPS FOR TABLE `doctors`:
--

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

--
-- RELATIONSHIPS FOR TABLE `doctor_practice`:
--

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

--
-- RELATIONSHIPS FOR TABLE `doctor_practice_details`:
--

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

--
-- RELATIONSHIPS FOR TABLE `expertise`:
--

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

--
-- RELATIONSHIPS FOR TABLE `password_temp_tbl`:
--

--
-- Indexes for dumped tables
--

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
-- AUTO_INCREMENT for dumped tables
--

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
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
