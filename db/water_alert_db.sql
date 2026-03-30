-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 30, 2026 at 04:55 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `water_alert_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `alerts`
--

CREATE TABLE `alerts` (
  `id` int(11) NOT NULL,
  `alert_type` varchar(50) DEFAULT 'LEAK',
  `message` text DEFAULT NULL,
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `alerts`
--

INSERT INTO `alerts` (`id`, `alert_type`, `message`, `sent_at`) VALUES
(1, 'LEAK', 'Leak Alert! Water level at 10%', '2026-03-28 03:25:17'),
(2, 'LEAK', 'Leak Alert! Water level at 10%', '2026-03-28 02:25:17'),
(3, 'LEAK', 'Leak Alert! Water level at 10%', '2026-03-27 22:55:17'),
(4, 'LEAK', 'Simulated Leak! Water level at 10%', '2026-03-28 04:10:27');

-- --------------------------------------------------------

--
-- Table structure for table `leak_logs`
--

CREATE TABLE `leak_logs` (
  `id` int(11) NOT NULL,
  `status` varchar(20) NOT NULL,
  `source` varchar(50) DEFAULT 'SYSTEM',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `leak_logs`
--

INSERT INTO `leak_logs` (`id`, `status`, `source`, `created_at`) VALUES
(1, 'SAFE', 'ESP32', '2026-03-28 03:45:17'),
(2, 'SAFE', 'ESP32', '2026-03-28 03:35:17'),
(3, 'LEAK', 'ESP32', '2026-03-28 03:25:17'),
(4, 'LEAK', 'WEB_SIMULATION', '2026-03-28 03:15:17'),
(5, 'SAFE', 'ESP32', '2026-03-28 03:05:17'),
(6, 'SAFE', 'ESP32', '2026-03-28 02:55:17'),
(7, 'LEAK', 'ESP32', '2026-03-28 02:25:17'),
(8, 'SAFE', 'WEB_SIMULATION', '2026-03-28 01:55:17'),
(9, 'SAFE', 'ESP32', '2026-03-28 00:55:17'),
(10, 'LEAK', 'ESP32', '2026-03-27 22:55:17'),
(11, 'LEAK', 'WEB_SIMULATION', '2026-03-28 04:10:27');

-- --------------------------------------------------------

--
-- Table structure for table `tank_status`
--

CREATE TABLE `tank_status` (
  `id` int(11) NOT NULL,
  `water_level` int(11) DEFAULT 75,
  `status` varchar(20) DEFAULT 'SAFE',
  `valve_state` varchar(20) DEFAULT 'OPEN',
  `battery` int(11) DEFAULT 100,
  `signal_strength` varchar(20) DEFAULT 'GOOD',
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tank_status`
--

INSERT INTO `tank_status` (`id`, `water_level`, `status`, `valve_state`, `battery`, `signal_strength`, `last_updated`) VALUES
(1, 10, 'LEAK', 'OPEN', 100, 'GOOD', '2026-03-28 04:10:27');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','manager','user') NOT NULL DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `created_at`) VALUES
(1, 'admin', 'admin123', 'admin', '2026-03-28 03:55:17'),
(2, 'manager', 'manager123', 'manager', '2026-03-28 03:55:17'),
(3, 'user1', 'user123', 'user', '2026-03-28 03:55:17');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `alerts`
--
ALTER TABLE `alerts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `leak_logs`
--
ALTER TABLE `leak_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tank_status`
--
ALTER TABLE `tank_status`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `alerts`
--
ALTER TABLE `alerts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `leak_logs`
--
ALTER TABLE `leak_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `tank_status`
--
ALTER TABLE `tank_status`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
