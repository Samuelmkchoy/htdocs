-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Apr 12, 2024 at 01:46 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `secureappdev`
--

-- --------------------------------------------------------

--
-- Table structure for table `failedlogins`
--

CREATE TABLE `failedlogins` (
  `event_id` int(11) NOT NULL,
  `ip` varchar(128) NOT NULL,
  `timeStamp` datetime NOT NULL,
  `failedLoginCount` int(11) NOT NULL,
  `lockOutCount` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `failedlogins`
--

INSERT INTO `failedlogins` (`event_id`, `ip`, `timeStamp`, `failedLoginCount`, `lockOutCount`) VALUES
(1, '::1', '2024-04-12 01:31:16', 0, 1);

-- --------------------------------------------------------

--
-- Table structure for table `loginevents`
--

CREATE TABLE `loginevents` (
  `event_id` int(11) NOT NULL,
  `ip` varchar(128) NOT NULL,
  `timeStamp` datetime NOT NULL,
  `user_id` varchar(50) NOT NULL,
  `outcome` varchar(7) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `loginevents`
--

INSERT INTO `loginevents` (`event_id`, `ip`, `timeStamp`, `user_id`, `outcome`) VALUES
(1, '::1', '2024-04-12 01:03:28', 'abc', 'success'),
(2, '::1', '2024-04-12 01:13:33', 'abc', 'fail'),
(3, '::1', '2024-04-12 01:14:00', 'abc', 'fail'),
(4, '::1', '2024-04-12 01:14:46', 'abc', 'fail'),
(5, '::1', '2024-04-12 01:17:59', 'abc', 'fail'),
(6, '::1', '2024-04-12 01:18:29', 'admin', 'fail');

-- --------------------------------------------------------

--
-- Table structure for table `sapusers`
--

CREATE TABLE `sapusers` (
  `user_id` int(11) NOT NULL,
  `user_uid` varchar(256) NOT NULL,
  `user_pwd` varchar(256) NOT NULL,
  `user_admin` int(2) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sapusers`
--

INSERT INTO `sapusers` (`user_id`, `user_uid`, `user_pwd`, `user_admin`) VALUES
(1, 'admin', '$2y$10$U4SbUkphYILR4f2h90kGyuvSKCzrr4dyZJXHqpRztc7ldkHIJAuLi', 1),
(2, 'Tom', 'Password1!', 0),
(3, 'aaaa', '$2y$10$iqCarc1I5.imfIrIjd3Li.uknKy4WgyEviu7aIcmut27ockCTYAku', 0),
(4, 'ivy', '$2y$10$KxUcbEkF1KYAXWs9VnIwzuNmmQDq369E97ZETqiTnisQpbJpHlUGS', 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `failedlogins`
--
ALTER TABLE `failedlogins`
  ADD PRIMARY KEY (`event_id`);

--
-- Indexes for table `loginevents`
--
ALTER TABLE `loginevents`
  ADD PRIMARY KEY (`event_id`);

--
-- Indexes for table `sapusers`
--
ALTER TABLE `sapusers`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `failedlogins`
--
ALTER TABLE `failedlogins`
  MODIFY `event_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `loginevents`
--
ALTER TABLE `loginevents`
  MODIFY `event_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `sapusers`
--
ALTER TABLE `sapusers`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
