-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 04, 2026 at 03:48 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `gym_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `members`
--

CREATE TABLE `members` (
  `member_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `gender` enum('Male','Female','Other') NOT NULL DEFAULT 'Male',
  `join_date` date NOT NULL,
  `package_id` int(11) DEFAULT NULL,
  `status` enum('active','expired') NOT NULL DEFAULT 'active',
  `expiry_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `membership_packages`
--

CREATE TABLE `membership_packages` (
  `package_id` int(11) NOT NULL,
  `package_name` varchar(100) NOT NULL,
  `duration` int(11) NOT NULL COMMENT 'Duration in months',
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `membership_packages`
--

INSERT INTO `membership_packages` (`package_id`, `package_name`, `duration`, `price`) VALUES
(1, 'Basic', 1, 100.00),
(2, 'Standard', 3, 250.00),
(3, 'Premium', 6, 450.00);

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `payment_date` date NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` enum('Cash','Card','Online') NOT NULL DEFAULT 'Cash',
  `payment_status` enum('Paid','Pending','Cancelled') NOT NULL DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `session_bookings`
--

CREATE TABLE `session_bookings` (
  `booking_id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `trainer_id` int(11) NOT NULL,
  `session_date` date NOT NULL,
  `session_time` varchar(20) NOT NULL,
  `session_type` enum('Strength','Cardio','Weight Loss','Rehab') NOT NULL DEFAULT 'Strength',
  `booking_status` enum('Pending','Approved','Rejected','Cancelled','Completed') NOT NULL DEFAULT 'Pending',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `trainers`
--

CREATE TABLE `trainers` (
  `trainer_id` int(11) NOT NULL,
  `trainer_name` varchar(100) NOT NULL,
  `specialization` varchar(100) NOT NULL,
  `available_days` varchar(200) NOT NULL,
  `available_time` varchar(100) NOT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `status` enum('Available','Busy') NOT NULL DEFAULT 'Available',
  `session_fee` decimal(10,2) NOT NULL DEFAULT 50.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `trainers`
--

INSERT INTO `trainers` (`trainer_id`, `trainer_name`, `specialization`, `available_days`, `available_time`, `contact_number`, `status`, `session_fee`) VALUES
(1, 'Ahmad Rizal', 'Strength Training', 'Mon, Tue, Wed, Thu, Fri', '8:00 AM - 12:00 PM', '012-3456789', 'Available', 80.00),
(2, 'Sarah Tan', 'Cardio & HIIT', 'Mon, Wed, Fri, Sat', '2:00 PM - 6:00 PM', '013-9876543', 'Available', 70.00),
(3, 'David Lee', 'Weight Loss', 'Tue, Thu, Sat', '9:00 AM - 1:00 PM', '011-2345678', 'Available', 75.00),
(4, 'Nurul Aisyah', 'Rehabilitation', 'Mon, Tue, Wed, Thu, Fri', '10:00 AM - 4:00 PM', '014-5678901', 'Available', 90.00),
(5, 'Ahmad Rizal', 'Strength Training', 'Mon, Tue, Wed, Thu, Fri', '8:00 AM - 12:00 PM', '012-3456789', 'Available', 80.00),
(6, 'Sarah Tan', 'Cardio & HIIT', 'Mon, Wed, Fri, Sat', '2:00 PM - 6:00 PM', '013-9876543', 'Available', 70.00),
(7, 'David Lee', 'Weight Loss', 'Tue, Thu, Sat', '9:00 AM - 1:00 PM', '011-2345678', 'Available', 75.00),
(8, 'Nurul Aisyah', 'Rehabilitation', 'Mon, Tue, Wed, Thu, Fri', '10:00 AM - 4:00 PM', '014-5678901', 'Available', 90.00);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','member') NOT NULL DEFAULT 'member',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'admin', 'admin@gym.com', '$2y$10$s4Lq2O88.yOwVj2JReqiXeBBs5Wr8kv6whXCJAjWCz8veCEJ.LZ9C', 'admin', '2026-05-02 13:21:19');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `members`
--
ALTER TABLE `members`
  ADD PRIMARY KEY (`member_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `package_id` (`package_id`);

--
-- Indexes for table `membership_packages`
--
ALTER TABLE `membership_packages`
  ADD PRIMARY KEY (`package_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `member_id` (`member_id`);

--
-- Indexes for table `session_bookings`
--
ALTER TABLE `session_bookings`
  ADD PRIMARY KEY (`booking_id`),
  ADD KEY `member_id` (`member_id`),
  ADD KEY `trainer_id` (`trainer_id`);

--
-- Indexes for table `trainers`
--
ALTER TABLE `trainers`
  ADD PRIMARY KEY (`trainer_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `members`
--
ALTER TABLE `members`
  MODIFY `member_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `membership_packages`
--
ALTER TABLE `membership_packages`
  MODIFY `package_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `session_bookings`
--
ALTER TABLE `session_bookings`
  MODIFY `booking_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `trainers`
--
ALTER TABLE `trainers`
  MODIFY `trainer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `members`
--
ALTER TABLE `members`
  ADD CONSTRAINT `members_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `members_ibfk_2` FOREIGN KEY (`package_id`) REFERENCES `membership_packages` (`package_id`) ON DELETE SET NULL;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`member_id`) ON DELETE CASCADE;

--
-- Constraints for table `session_bookings`
--
ALTER TABLE `session_bookings`
  ADD CONSTRAINT `session_bookings_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`member_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `session_bookings_ibfk_2` FOREIGN KEY (`trainer_id`) REFERENCES `trainers` (`trainer_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
