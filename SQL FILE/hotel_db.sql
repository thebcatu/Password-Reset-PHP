-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Feb 14, 2025 at 03:18 PM
-- Server version: 8.0.30
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `hotel_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int NOT NULL,
  `email` varchar(255) NOT NULL,
  `otp` varchar(6) NOT NULL,
  `token` varchar(64) NOT NULL,
  `expiry` datetime NOT NULL,
  `used` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`id`, `email`, `otp`, `token`, `expiry`, `used`, `created_at`) VALUES
(22, 'user@gmail.com', '974183', '4b0cafee8e24138ab5eda320bbd0542106d07571c272b42e68101311b0c2518b', '2025-02-14 20:48:54', 1, '2025-02-14 15:02:54'),
(23, 'user@gmail.com', '683320', 'a32215454b3723f073fce7bad17a5eec18253a53eb8c4fdb63dcbff9870c5fea', '2025-02-14 20:49:58', 0, '2025-02-14 15:03:58'),
(24, 'user@gmail.com', '140634', 'bb53fe9823770322ffea47108418ba2e68d1a1883af2e8ee5356c0a902c64fd6', '2025-02-14 20:51:13', 0, '2025-02-14 15:05:13'),
(25, 'user@gmail.com', '135708', 'c94eeb9ad2f285e241fb97457a5663a050a6accec3c37bf0cf43fe0f04ac1ef7', '2025-02-14 20:55:48', 0, '2025-02-14 15:09:48'),
(26, 'user@gmail.com', '136022', 'bd617532fc672b1d529b3d99fd22b95a0efb5f127ec6dffccae860dcaa8914d3', '2025-02-14 21:01:26', 0, '2025-02-14 15:15:26');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `password`, `created_at`) VALUES
(2, 'ram.sharma@mahendrasingh.com.np', '81dc9bdb52d04dc20036dbd8313ed055', '2025-02-14 15:02:13'),
(3, 'sita.khadka@mahendrasingh.com.np', 'd93591bdf7860e1e4ee2fca799911215', '2025-02-14 15:02:13'),
(4, 'gopal.bhattarai@mahendrasingh.com.np', '674f3c2c1a8a6f90461e8a66fb5550ba', '2025-02-14 15:02:13'),
(5, 'ramesh.ghimire@mahendrasingh.com.np', 'f38fef4c0e4988792723c29a0bd3ca98', '2025-02-14 15:02:13'),
(6, 'sushila.acharya@mahendrasingh.com.np', '81b073de9370ea873f548e31b8adc081', '2025-02-14 15:02:13'),
(7, 'hari.karki@mahendrasingh.com.np', '2e92962c0b6996add9517e4242ea9bdc', '2025-02-14 15:02:13'),
(8, 'pratikshya.magar@mahendrasingh.com.np', '46d045ff5190f6ea93739da6c0aa19bc', '2025-02-14 15:02:13'),
(9, 'bishal.rai@mahendrasingh.com.np', '912e79cd13c64069d91da65d62fbb78c', '2025-02-14 15:02:13'),
(10, 'sunita.gurung@mahendrasingh.com.np', 'c26820b8a4c1b3c2aa868d6d57e14a79', '2025-02-14 15:02:13'),
(11, 'deepak.tamang@mahendrasingh.com.np', '021f6dd88a11ca489936ae770e4634ad', '2025-02-14 15:02:13'),
(12, 'user@gmail.com', '81dc9bdb52d04dc20036dbd8313ed055', '2025-02-14 15:02:13'),
(13, 'exmple@gmail.com', 'd93591bdf7860e1e4ee2fca799911215', '2025-02-14 15:02:13'),
(14, 'test@gmail.com', '674f3c2c1a8a6f90461e8a66fb5550ba', '2025-02-14 15:02:13');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_token` (`token`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
