-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Nov 27, 2025 at 02:32 PM
-- Server version: 11.8.3-MariaDB-log
-- PHP Version: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u972712031_studysmart`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_logs`
--

CREATE TABLE `admin_logs` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `action` varchar(100) NOT NULL,
  `target_type` varchar(50) DEFAULT NULL,
  `target_id` int(11) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `admin_logs`
--

INSERT INTO `admin_logs` (`id`, `admin_id`, `action`, `target_type`, `target_id`, `details`, `created_at`) VALUES
(1, 1, 'User logged in', 'login', NULL, '{\"ip\":\"::1\",\"device\":\"Desktop\"}', '2025-07-29 16:36:34'),
(2, 1, 'User logged in', 'login', NULL, '{\"ip\":\"unknown\",\"device\":\"Desktop\"}', '2025-08-16 08:12:48'),
(3, 2, 'User logged in', 'login', NULL, '{\"ip\":\"unknown\",\"device\":\"Desktop\"}', '2025-08-16 08:12:48'),
(4, 5, 'User logged in', 'login', NULL, '{\"ip\":\"unknown\",\"device\":\"Desktop\"}', '2025-08-16 08:12:49'),
(5, 1, 'User logged in', 'login', NULL, '{\"ip\":\"unknown\",\"device\":\"Desktop\"}', '2025-08-16 08:17:50'),
(6, 2, 'User logged in', 'login', NULL, '{\"ip\":\"unknown\",\"device\":\"Desktop\"}', '2025-08-16 08:17:51'),
(7, 5, 'User logged in', 'login', NULL, '{\"ip\":\"unknown\",\"device\":\"Desktop\"}', '2025-08-16 08:17:51'),
(8, 1, 'User logged in', 'login', NULL, '{\"ip\":\"::1\",\"device\":\"Desktop\"}', '2025-08-16 08:21:56'),
(9, 1, 'Multiple session detected', 'login', NULL, '{\"session_count\":1}', '2025-08-16 08:22:44'),
(10, 1, 'User logged in', 'login', NULL, '{\"ip\":\"::1\",\"device\":\"Desktop\"}', '2025-08-16 08:22:44'),
(11, 1, 'Multiple session detected', 'login', NULL, '{\"session_count\":1}', '2025-08-16 08:27:36'),
(12, 1, 'User logged in', 'login', NULL, '{\"ip\":\"::1\",\"device\":\"Desktop\"}', '2025-08-16 08:27:36'),
(13, 1, 'Multiple session detected', 'login', NULL, '{\"session_count\":2}', '2025-08-16 08:30:21'),
(14, 1, 'User logged in', 'login', NULL, '{\"ip\":\"unknown\",\"device\":\"Desktop\"}', '2025-08-16 08:30:21'),
(15, 1, 'Multiple session detected', 'login', NULL, '{\"session_count\":1}', '2025-08-16 09:07:53'),
(16, 1, 'User logged in', 'login', NULL, '{\"ip\":\"::1\",\"device\":\"Desktop\"}', '2025-08-16 09:07:53'),
(17, 20, 'User logged in', 'login', NULL, '{\"ip\":\"::1\",\"device\":\"Desktop\"}', '2025-08-16 10:16:19'),
(18, 5, 'User logged in', 'login', NULL, '{\"ip\":\"::1\",\"device\":\"Desktop\"}', '2025-08-16 12:10:25'),
(19, 5, 'User logged in', 'login', NULL, '{\"ip\":\"::1\",\"device\":\"Desktop\"}', '2025-08-16 13:43:27'),
(20, 5, 'User logged in', 'login', NULL, '{\"ip\":\"::1\",\"device\":\"Desktop\"}', '2025-08-16 14:38:10'),
(21, 20, 'User logged in', 'login', NULL, '{\"ip\":\"45.215.254.172\",\"device\":\"Desktop\"}', '2025-08-16 17:33:36'),
(22, 1, 'Multiple session detected', 'login', NULL, '{\"session_count\":1}', '2025-08-16 17:34:08'),
(23, 1, 'User logged in', 'login', NULL, '{\"ip\":\"45.215.254.172\",\"device\":\"Desktop\"}', '2025-08-16 17:34:08'),
(24, 1, 'Multiple session detected', 'login', NULL, '{\"session_count\":1}', '2025-08-16 17:38:44'),
(25, 1, 'User logged in', 'login', NULL, '{\"ip\":\"45.215.254.172\",\"device\":\"Desktop\"}', '2025-08-16 17:38:44'),
(26, 1, 'Multiple session detected', 'login', NULL, '{\"session_count\":2}', '2025-08-16 18:49:57'),
(27, 1, 'User logged in', 'login', NULL, '{\"ip\":\"102.208.220.205\",\"device\":\"Desktop\"}', '2025-08-16 18:49:57'),
(28, 5, 'Multiple session detected', 'login', NULL, '{\"session_count\":1}', '2025-08-16 19:36:17'),
(29, 5, 'User logged in', 'login', NULL, '{\"ip\":\"2605:59c0:5d0c:4008:315d:5a29:42f0:36ff\",\"device\":\"Desktop\"}', '2025-08-16 19:36:17'),
(30, 5, 'Multiple session detected', 'login', NULL, '{\"session_count\":1}', '2025-08-16 20:43:11'),
(31, 5, 'User logged in', 'login', NULL, '{\"ip\":\"2605:59c0:5d0c:4008:745d:4d1f:7efe:f4c8\",\"device\":\"Desktop\"}', '2025-08-16 20:43:11'),
(32, 2, 'User logged in', 'login', NULL, '{\"ip\":\"2605:59c0:5d0c:4008:745d:4d1f:7efe:f4c8\",\"device\":\"Desktop\"}', '2025-08-16 20:54:23'),
(33, 5, 'User logged in', 'login', NULL, '{\"ip\":\"102.208.220.204\",\"device\":\"Desktop\"}', '2025-08-18 15:40:51'),
(34, 5, 'Multiple session detected', 'login', NULL, '{\"session_count\":1}', '2025-08-19 15:30:37'),
(35, 5, 'User logged in', 'login', NULL, '{\"ip\":\"41.63.2.190\",\"device\":\"Desktop\"}', '2025-08-19 15:30:37'),
(36, 1, 'Multiple session detected', 'login', NULL, '{\"session_count\":1}', '2025-08-21 17:32:14'),
(37, 1, 'User logged in', 'login', NULL, '{\"ip\":\"102.208.220.201\",\"device\":\"Desktop\"}', '2025-08-21 17:32:14'),
(38, 1, 'Multiple session detected', 'login', NULL, '{\"session_count\":2}', '2025-08-21 21:26:14'),
(39, 1, 'User logged in', 'login', NULL, '{\"ip\":\"41.60.183.124\",\"device\":\"Desktop\"}', '2025-08-21 21:26:14'),
(40, 1, 'Multiple session detected', 'login', NULL, '{\"session_count\":3}', '2025-08-22 08:45:27'),
(41, 1, 'User logged in', 'login', NULL, '{\"ip\":\"45.214.73.150\",\"device\":\"Desktop\"}', '2025-08-22 08:45:27'),
(42, 1, 'Multiple session detected', 'login', NULL, '{\"session_count\":3}', '2025-08-22 20:41:28'),
(43, 1, 'User logged in', 'login', NULL, '{\"ip\":\"102.208.220.201\",\"device\":\"Mobile Device\"}', '2025-08-22 20:41:28'),
(44, 5, 'User logged in', 'login', NULL, '{\"ip\":\"102.208.220.203\",\"device\":\"Mobile Device\"}', '2025-08-22 20:45:01'),
(45, 5, 'Multiple session detected', 'login', NULL, '{\"session_count\":1}', '2025-08-22 20:48:28'),
(46, 5, 'User logged in', 'login', NULL, '{\"ip\":\"102.208.220.201\",\"device\":\"Desktop\"}', '2025-08-22 20:48:28'),
(47, 5, 'Multiple session detected', 'login', NULL, '{\"session_count\":2}', '2025-08-22 21:02:10'),
(48, 5, 'User logged in', 'login', NULL, '{\"ip\":\"102.208.220.201\",\"device\":\"Mobile Device\"}', '2025-08-22 21:02:10'),
(49, 2, 'User logged in', 'login', NULL, '{\"ip\":\"102.208.220.201\",\"device\":\"Desktop\"}', '2025-08-22 21:27:10'),
(50, 5, 'Multiple session detected', 'login', NULL, '{\"session_count\":2}', '2025-08-22 21:49:05'),
(51, 5, 'User logged in', 'login', NULL, '{\"ip\":\"102.208.220.201\",\"device\":\"Mobile Device\"}', '2025-08-22 21:49:05'),
(52, 5, 'Multiple session detected', 'login', NULL, '{\"session_count\":3}', '2025-08-22 22:44:01'),
(53, 5, 'User logged in', 'login', NULL, '{\"ip\":\"102.208.220.201\",\"device\":\"Desktop\"}', '2025-08-22 22:44:01'),
(54, 1, 'Multiple session detected', 'login', NULL, '{\"session_count\":1}', '2025-08-23 16:24:34'),
(55, 1, 'User logged in', 'login', NULL, '{\"ip\":\"45.212.141.22\",\"device\":\"Mobile Device\"}', '2025-08-23 16:24:34'),
(56, 1, 'Multiple session detected', 'login', NULL, '{\"session_count\":2}', '2025-08-23 16:27:03'),
(57, 1, 'User logged in', 'login', NULL, '{\"ip\":\"102.208.220.201\",\"device\":\"Desktop\"}', '2025-08-23 16:27:03'),
(58, 2, 'User logged in', 'login', NULL, '{\"ip\":\"102.208.220.201\",\"device\":\"Desktop\"}', '2025-08-23 17:17:53'),
(59, 2, 'Multiple session detected', 'login', NULL, '{\"session_count\":1}', '2025-08-23 17:18:16'),
(60, 2, 'User logged in', 'login', NULL, '{\"ip\":\"45.212.141.22\",\"device\":\"Desktop\"}', '2025-08-23 17:18:16'),
(61, 5, 'Multiple session detected', 'login', NULL, '{\"session_count\":4}', '2025-08-23 17:25:47'),
(62, 5, 'User logged in', 'login', NULL, '{\"ip\":\"102.208.220.201\",\"device\":\"Desktop\"}', '2025-08-23 17:25:47'),
(63, 5, 'Multiple session detected', 'login', NULL, '{\"session_count\":5}', '2025-08-23 17:25:52'),
(64, 5, 'User logged in', 'login', NULL, '{\"ip\":\"45.212.141.22\",\"device\":\"Desktop\"}', '2025-08-23 17:25:52'),
(65, 5, 'User logged in', 'login', NULL, '{\"ip\":\"45.215.255.218\",\"device\":\"Mobile Device\"}', '2025-08-25 06:55:40'),
(66, 1, 'Multiple session detected', 'login', NULL, '{\"session_count\":1}', '2025-08-25 08:07:04'),
(67, 1, 'User logged in', 'login', NULL, '{\"ip\":\"45.215.255.218\",\"device\":\"Desktop\"}', '2025-08-25 08:07:04'),
(68, 5, 'User logged in', 'login', NULL, '{\"ip\":\"41.63.2.190\",\"device\":\"Mobile Device\"}', '2025-08-25 09:03:52'),
(69, 5, 'Multiple session detected', 'login', NULL, '{\"session_count\":1}', '2025-08-26 06:03:54'),
(70, 5, 'User logged in', 'login', NULL, '{\"ip\":\"2605:59c0:5d0c:4008:ac03:83dc:d88:d56c\",\"device\":\"Mobile Device\"}', '2025-08-26 06:03:54'),
(71, 1, 'Multiple session detected', 'login', NULL, '{\"session_count\":1}', '2025-08-26 08:23:08'),
(72, 1, 'User logged in', 'login', NULL, '{\"ip\":\"2605:59c0:5d0c:4008:8c36:d6e8:bd9e:e3dc\",\"device\":\"Mobile Device\"}', '2025-08-26 08:23:08'),
(73, 5, 'Multiple session detected', 'login', NULL, '{\"session_count\":2}', '2025-08-26 08:34:05'),
(74, 5, 'User logged in', 'login', NULL, '{\"ip\":\"2605:59c0:5d0c:4008:8c36:d6e8:bd9e:e3dc\",\"device\":\"Mobile Device\"}', '2025-08-26 08:34:05'),
(75, 5, 'Multiple session detected', 'login', NULL, '{\"session_count\":2}', '2025-08-26 10:03:50'),
(76, 5, 'User logged in', 'login', NULL, '{\"ip\":\"2605:59c0:5d0c:4008:8c36:d6e8:bd9e:e3dc\",\"device\":\"Mobile Device\"}', '2025-08-26 10:03:50'),
(77, 2, 'User logged in', 'login', NULL, '{\"ip\":\"2605:59c0:5d0c:4008:8c36:d6e8:bd9e:e3dc\",\"device\":\"Mobile Device\"}', '2025-08-26 10:05:12'),
(78, 5, 'Multiple session detected', 'login', NULL, '{\"session_count\":2}', '2025-08-26 23:19:13'),
(79, 5, 'User logged in', 'login', NULL, '{\"ip\":\"102.208.220.201\",\"device\":\"Desktop\"}', '2025-08-26 23:19:13'),
(80, 1, 'Multiple session detected', 'login', NULL, '{\"session_count\":1}', '2025-08-27 07:24:35'),
(81, 1, 'User logged in', 'login', NULL, '{\"ip\":\"102.208.220.201\",\"device\":\"Mobile Device\"}', '2025-08-27 07:24:35'),
(82, 5, 'User logged in', 'login', NULL, '{\"ip\":\"102.208.220.207\",\"device\":\"Desktop\"}', '2025-08-28 05:44:41'),
(83, 1, 'Multiple session detected', 'login', NULL, '{\"session_count\":1}', '2025-08-28 14:44:51'),
(84, 1, 'User logged in', 'login', NULL, '{\"ip\":\"41.216.87.10\",\"device\":\"Desktop\"}', '2025-08-28 14:44:51'),
(85, 3, 'User logged in', 'login', NULL, '{\"ip\":\"41.216.87.10\",\"device\":\"Desktop\"}', '2025-08-28 15:03:23'),
(86, 6, 'User logged in', 'login', NULL, '{\"ip\":\"41.216.87.10\",\"device\":\"Desktop\"}', '2025-08-28 15:09:13'),
(87, 5, 'User logged in', 'login', NULL, '{\"ip\":\"2605:59c0:5d0c:4008:f472:4b29:59fd:fa64\",\"device\":\"Desktop\"}', '2025-08-30 10:28:08'),
(88, 1, 'Multiple session detected', 'login', NULL, '{\"session_count\":1}', '2025-08-30 13:40:04'),
(89, 1, 'User logged in', 'login', NULL, '{\"ip\":\"2605:59c0:5d0c:4008:54dd:bc53:c25b:ce1e\",\"device\":\"Desktop\"}', '2025-08-30 13:40:04'),
(90, 5, 'User logged in', 'login', NULL, '{\"ip\":\"45.215.249.130\",\"device\":\"Mobile Device\"}', '2025-09-01 06:33:05'),
(91, 5, 'User logged in', 'login', NULL, '{\"ip\":\"45.215.249.130\",\"device\":\"Mobile Device\"}', '2025-09-01 07:14:27'),
(92, 5, 'User logged in', 'login', NULL, '{\"ip\":\"102.208.220.204\",\"device\":\"Mobile Device\"}', '2025-09-03 00:19:33'),
(93, 5, 'User logged in', 'login', NULL, '{\"ip\":\"102.208.220.201\",\"device\":\"Desktop\"}', '2025-09-05 06:47:21'),
(94, 5, 'Multiple session detected', 'login', NULL, '{\"session_count\":1}', '2025-09-05 15:19:32'),
(95, 5, 'User logged in', 'login', NULL, '{\"ip\":\"102.208.220.201\",\"device\":\"Desktop\"}', '2025-09-05 15:19:32'),
(96, 5, 'Multiple session detected', 'login', NULL, '{\"session_count\":1}', '2025-09-06 10:08:23'),
(97, 5, 'User logged in', 'login', NULL, '{\"ip\":\"102.208.220.204\",\"device\":\"Mobile Device\"}', '2025-09-06 10:08:23'),
(98, 5, 'Multiple session detected', 'login', NULL, '{\"session_count\":1}', '2025-09-06 20:22:03'),
(99, 5, 'User logged in', 'login', NULL, '{\"ip\":\"216.234.213.253\",\"device\":\"Desktop\"}', '2025-09-06 20:22:03'),
(100, 5, 'User logged in', 'login', NULL, '{\"ip\":\"2605:59c0:5d0c:4008:2480:b348:6833:7a67\",\"device\":\"Desktop\"}', '2025-09-08 22:57:18'),
(101, 5, 'User logged in', 'login', NULL, '{\"ip\":\"102.208.220.201\",\"device\":\"Desktop\"}', '2025-09-10 12:13:01'),
(102, 2, 'User logged in', 'login', NULL, '{\"ip\":\"102.208.220.201\",\"device\":\"Desktop\"}', '2025-09-10 12:19:22'),
(103, 1, 'Multiple session detected', 'login', NULL, '{\"session_count\":1}', '2025-09-10 12:23:10'),
(104, 1, 'User logged in', 'login', NULL, '{\"ip\":\"102.208.220.201\",\"device\":\"Desktop\"}', '2025-09-10 12:23:10'),
(105, 5, 'User logged in', 'login', NULL, '{\"ip\":\"2605:59c0:5d0c:4008:bc7e:f4cb:8e9a:76b3\",\"device\":\"Mobile Device\"}', '2025-09-11 20:35:42'),
(106, 5, 'User logged in', 'login', NULL, '{\"ip\":\"2605:59c0:5d0c:4008:804e:8dbe:376f:db65\",\"device\":\"Mobile Device\"}', '2025-09-13 09:21:16'),
(107, 5, 'User logged in', 'login', NULL, '{\"ip\":\"2605:59c0:5d0c:4008:a138:f596:7b46:84b1\",\"device\":\"Mobile Device\"}', '2025-09-14 11:34:54'),
(108, 1, 'Multiple session detected', 'login', NULL, '{\"session_count\":1}', '2025-09-14 11:37:43'),
(109, 1, 'User logged in', 'login', NULL, '{\"ip\":\"2605:59c0:5d0c:4008:a138:f596:7b46:84b1\",\"device\":\"Mobile Device\"}', '2025-09-14 11:37:43'),
(110, 3, 'User logged in', 'login', NULL, '{\"ip\":\"2605:59c0:5d0c:4008:55f6:eea6:3fe6:3174\",\"device\":\"Mobile Device\"}', '2025-09-14 13:45:02'),
(111, 5, 'User logged in', 'login', NULL, '{\"ip\":\"45.215.249.82\",\"device\":\"Mobile Device\"}', '2025-09-16 14:54:21'),
(112, 5, 'Multiple session detected', 'login', NULL, '{\"session_count\":1}', '2025-09-17 10:05:54'),
(113, 5, 'User logged in', 'login', NULL, '{\"ip\":\"102.208.220.204\",\"device\":\"Desktop\"}', '2025-09-17 10:05:54'),
(114, 5, 'User logged in', 'login', NULL, '{\"ip\":\"2605:59c0:5d0c:4008:80a6:2cb3:b32f:19d2\",\"device\":\"Mobile Device\"}', '2025-09-20 04:03:16'),
(115, 5, 'Multiple session detected', 'login', NULL, '{\"session_count\":1}', '2025-09-20 14:06:51'),
(116, 5, 'User logged in', 'login', NULL, '{\"ip\":\"2605:59c0:5d0c:4008:35fa:e2e9:5da9:9458\",\"device\":\"Desktop\"}', '2025-09-20 14:06:51'),
(117, 5, 'User logged in', 'login', NULL, '{\"ip\":\"41.60.177.79\",\"device\":\"Desktop\"}', '2025-09-26 03:20:07'),
(118, 5, 'User logged in', 'login', NULL, '{\"ip\":\"216.234.213.135\",\"device\":\"Mobile Device\"}', '2025-10-05 02:30:14'),
(119, 5, 'User logged in', 'login', NULL, '{\"ip\":\"102.208.221.215\",\"device\":\"Mobile Device\"}', '2025-10-06 06:10:57'),
(120, 5, 'User logged in', 'login', NULL, '{\"ip\":\"41.223.116.244\",\"device\":\"Mobile Device\"}', '2025-10-08 16:33:46'),
(121, 5, 'User logged in', 'login', NULL, '{\"ip\":\"2605:59c0:5d0c:4008:50da:7af4:b9b3:674d\",\"device\":\"Desktop\"}', '2025-10-14 18:26:49'),
(122, 1, 'User logged in', 'login', NULL, '{\"ip\":\"2605:59c0:5d0c:4008:50da:7af4:b9b3:674d\",\"device\":\"Desktop\"}', '2025-10-14 19:47:03'),
(123, 1, 'User logged in', 'login', NULL, '{\"ip\":\"2605:59c0:5d0c:4008:8ce4:d85c:ffdf:ad87\",\"device\":\"Desktop\"}', '2025-10-21 09:54:49'),
(124, 1, 'User logged in', 'login', NULL, '{\"ip\":\"2605:59c0:5d0c:4008:859a:a409:7878:1c7d\",\"device\":\"Desktop\"}', '2025-10-25 20:27:21'),
(125, 1, 'User logged in', 'login', NULL, '{\"ip\":\"2605:59c0:5d0c:4008:859a:a409:7878:1c7d\",\"device\":\"Desktop\"}', '2025-10-25 20:35:13'),
(126, 5, 'User logged in', 'login', NULL, '{\"ip\":\"45.215.249.205\",\"device\":\"Mobile Device\"}', '2025-10-27 08:35:35'),
(127, 5, 'Multiple session detected', 'login', NULL, '{\"session_count\":1}', '2025-10-27 09:52:51'),
(128, 5, 'User logged in', 'login', NULL, '{\"ip\":\"102.208.220.204\",\"device\":\"Desktop\"}', '2025-10-27 09:52:51'),
(129, 5, 'User logged in', 'login', NULL, '{\"ip\":\"2605:59c0:5d0c:4008:342c:2c3f:5505:6acf\",\"device\":\"Desktop\"}', '2025-10-28 20:00:55'),
(130, 5, 'Multiple session detected', 'login', NULL, '{\"session_count\":1}', '2025-10-29 10:13:35'),
(131, 5, 'User logged in', 'login', NULL, '{\"ip\":\"2605:59c0:5d0c:4008:98ee:40f1:6e46:4331\",\"device\":\"Desktop\"}', '2025-10-29 10:13:35'),
(132, 5, 'User logged in', 'login', NULL, '{\"ip\":\"2605:59c0:5d0c:4008:edd3:223d:3541:2188\",\"device\":\"Desktop\"}', '2025-10-31 01:49:45'),
(133, 5, 'User logged in', 'login', NULL, '{\"ip\":\"2605:59c0:5d0c:4008:b096:d266:4034:b4db\",\"device\":\"Desktop\"}', '2025-11-01 22:24:27'),
(134, 5, 'User logged in', 'login', NULL, '{\"ip\":\"102.148.18.103\",\"device\":\"Desktop\"}', '2025-11-03 11:20:49'),
(135, 5, 'User logged in', 'login', NULL, '{\"ip\":\"2605:59c0:5d0c:4008:5171:4954:3b5c:7536\",\"device\":\"Desktop\"}', '2025-11-09 11:05:13'),
(136, 5, 'User logged in', 'login', NULL, '{\"ip\":\"2605:59c0:5d0c:4008:795d:b613:c1ba:e634\",\"device\":\"Desktop\"}', '2025-11-10 22:11:33'),
(137, 21, 'User logged in', 'login', NULL, '{\"ip\":\"45.215.255.44\",\"device\":\"Mobile Device\"}', '2025-11-11 19:38:56'),
(138, 5, 'Multiple session detected', 'login', NULL, '{\"session_count\":1}', '2025-11-11 19:41:35'),
(139, 5, 'User logged in', 'login', NULL, '{\"ip\":\"45.215.255.44\",\"device\":\"Desktop\"}', '2025-11-11 19:41:35'),
(140, 1, 'User logged in', 'login', NULL, '{\"ip\":\"45.215.255.44\",\"device\":\"Desktop\"}', '2025-11-11 19:43:08'),
(141, 5, 'User logged in', 'login', NULL, '{\"ip\":\"2605:59c0:5d0c:4008:3dee:83e5:554d:7333\",\"device\":\"Desktop\"}', '2025-11-14 02:27:18'),
(142, 5, 'User logged in', 'login', NULL, '{\"ip\":\"2605:59c0:5d0c:4008:44d4:ec5e:d0d6:f48d\",\"device\":\"Desktop\"}', '2025-11-15 16:06:06'),
(143, 5, 'User logged in', 'login', NULL, '{\"ip\":\"216.234.213.107\",\"device\":\"Desktop\"}', '2025-11-17 02:15:47'),
(144, 5, 'Multiple session detected', 'login', NULL, '{\"session_count\":1}', '2025-11-17 11:11:21'),
(145, 5, 'User logged in', 'login', NULL, '{\"ip\":\"41.223.118.39\",\"device\":\"Desktop\"}', '2025-11-17 11:11:21'),
(146, 5, 'Multiple session detected', 'login', NULL, '{\"session_count\":2}', '2025-11-17 14:47:57'),
(147, 5, 'User logged in', 'login', NULL, '{\"ip\":\"41.63.2.190\",\"device\":\"Desktop\"}', '2025-11-17 14:47:57'),
(148, 5, 'Multiple session detected', 'login', NULL, '{\"session_count\":3}', '2025-11-17 19:09:34'),
(149, 5, 'User logged in', 'login', NULL, '{\"ip\":\"2605:59c0:5d0c:4008:1960:8e9b:fb4b:3d9f\",\"device\":\"Desktop\"}', '2025-11-17 19:09:34'),
(150, 5, 'Multiple session detected', 'login', NULL, '{\"session_count\":3}', '2025-11-18 10:20:16'),
(151, 5, 'User logged in', 'login', NULL, '{\"ip\":\"102.212.181.85\",\"device\":\"Mobile Device\"}', '2025-11-18 10:20:16'),
(152, 5, 'User logged in', 'login', NULL, '{\"ip\":\"41.63.2.190\",\"device\":\"Desktop\"}', '2025-11-20 02:02:23'),
(153, 5, 'User logged in', 'login', NULL, '{\"ip\":\"41.63.2.190\",\"device\":\"Desktop\"}', '2025-11-23 22:57:46'),
(154, 5, 'User logged in', 'login', NULL, '{\"ip\":\"41.63.2.190\",\"device\":\"Desktop\"}', '2025-11-24 23:39:52'),
(155, 5, 'Multiple session detected', 'login', NULL, '{\"session_count\":1}', '2025-11-26 00:23:01'),
(156, 5, 'User logged in', 'login', NULL, '{\"ip\":\"41.63.2.190\",\"device\":\"Desktop\"}', '2025-11-26 00:23:01'),
(157, 5, 'Multiple session detected', 'login', NULL, '{\"session_count\":2}', '2025-11-26 22:00:51'),
(158, 5, 'User logged in', 'login', NULL, '{\"ip\":\"2605:59c0:5d0c:4008:7d79:23e7:9616:a597\",\"device\":\"Desktop\"}', '2025-11-26 22:00:51');

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` int(11) NOT NULL,
  `course_code` varchar(20) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `lecturer_id` int(11) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `credits` int(11) DEFAULT 3
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `course_code`, `title`, `description`, `lecturer_id`, `is_active`, `created_at`, `updated_at`, `credits`) VALUES
(1, 'CS101', 'Introduction to Comp', 'Basic concepts of programming and computer science fundamentals', 21, 1, '2025-07-25 19:18:26', '2025-08-23 16:45:18', 3),
(2, 'MATH201', 'Calculus I', 'Differential and integral calculus with applications', 3, 1, '2025-07-25 19:18:26', '2025-07-25 19:18:26', 3),
(3, 'ENG102', 'Academic Writing', 'Advanced writing skills for academic and professional contexts', 4, 1, '2025-07-25 19:18:26', '2025-07-25 19:18:26', 3),
(4, 'PHY101', 'General Physics', 'Mechanics, thermodynamics, and wave phenomena', 3, 1, '2025-07-25 19:18:26', '2025-07-25 19:18:26', 3),
(5, 'BUS301', 'Business Management', 'Principles of management and organizational behavior', 2, 1, '2025-07-25 19:18:26', '2025-07-25 19:18:26', 3);

-- --------------------------------------------------------

--
-- Table structure for table `course_ratings`
--

CREATE TABLE `course_ratings` (
  `id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `lecturer_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `feedback` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `course_ratings`
--

INSERT INTO `course_ratings` (`id`, `course_id`, `student_id`, `lecturer_id`, `rating`, `feedback`, `created_at`) VALUES
(1, 1, 5, 2, 5, 'Excellent course! The programming concepts are explained very clearly.', '2024-12-15 18:30:00'),
(2, 1, 6, 2, 4, 'Good introduction to computer science. Would like more practical examples.', '2024-12-16 20:15:00'),
(3, 2, 5, 3, 5, 'Professor Smith explains calculus concepts brilliantly. Highly recommended!', '2024-12-17 19:45:00'),
(4, 2, 7, 3, 4, 'Great course content, but could use more practice problems.', '2024-12-18 16:20:00'),
(5, 3, 7, 4, 5, 'Fantastic writing course! My academic writing has improved significantly.', '2024-12-19 21:10:00'),
(6, 4, 6, 3, 4, 'Physics lab sessions are very informative and well-organized.', '2024-12-20 17:35:00'),
(7, 5, 8, 2, 5, 'Business management principles are taught with real-world examples.', '2024-12-21 19:50:00');

-- --------------------------------------------------------

--
-- Table structure for table `enrollments`
--

CREATE TABLE `enrollments` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `enrolled_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1,
  `enrollment_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `enrollments`
--

INSERT INTO `enrollments` (`id`, `student_id`, `course_id`, `enrolled_at`, `is_active`, `enrollment_date`, `created_at`) VALUES
(1, 5, 1, '2024-01-15 10:00:00', 1, '2025-08-16 11:13:34', '2025-08-16 11:13:34'),
(2, 5, 2, '2024-01-16 14:30:00', 1, '2025-08-16 11:13:34', '2025-08-16 11:13:34'),
(3, 5, 3, '2024-01-17 09:15:00', 1, '2025-08-16 11:13:34', '2025-08-16 11:13:34'),
(4, 6, 1, '2024-01-18 11:00:00', 1, '2025-08-16 11:13:34', '2025-08-16 11:13:34'),
(5, 6, 4, '2024-01-19 13:00:00', 1, '2025-08-16 11:13:34', '2025-08-16 11:13:34'),
(6, 7, 2, '2024-01-20 10:30:00', 1, '2025-08-16 11:13:34', '2025-08-16 11:13:34'),
(7, 7, 3, '2024-01-21 15:00:00', 1, '2025-08-16 11:13:34', '2025-08-16 11:13:34'),
(8, 7, 5, '2024-01-22 12:00:00', 1, '2025-08-16 11:13:34', '2025-08-16 11:13:34'),
(9, 8, 1, '2024-01-23 09:00:00', 1, '2025-08-16 11:13:34', '2025-08-16 11:13:34'),
(10, 8, 4, '2024-01-24 14:00:00', 1, '2025-08-16 11:13:34', '2025-08-16 11:13:34'),
(11, 8, 5, '2024-01-25 11:30:00', 1, '2025-08-16 11:13:34', '2025-08-16 11:13:34'),
(12, 9, 2, '2024-01-26 10:00:00', 1, '2025-08-16 11:13:34', '2025-08-16 11:13:34'),
(13, 9, 3, '2024-01-27 13:30:00', 1, '2025-08-16 11:13:34', '2025-08-16 11:13:34');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `type` enum('info','warning','success','error') DEFAULT 'info',
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `is_read`, `created_at`) VALUES
(1, 5, 'Welcome to StudySmart!', 'Your account has been activated. Start exploring your enrolled courses.', 'success', 1, '2024-01-15 10:05:00'),
(2, 5, 'New Resource Available', 'A new resource \"Python Basics Tutorial\" has been uploaded to CS101', 'info', 0, '2024-12-20 09:30:00'),
(3, 5, 'Upcoming Session', 'Python Programming Fundamentals session starts in 1 hour', 'warning', 0, '2024-12-25 13:00:00'),
(4, 6, 'Course Enrollment Confirmed', 'You have been successfully enrolled in Introduction to Computer Science', 'success', 1, '2024-01-18 11:05:00'),
(5, 6, 'New Resource Available', 'Programming Exercise Set 1 is now available for download', 'info', 0, '2024-12-21 14:15:00'),
(6, 7, 'Welcome to Academic Writing!', 'You have been enrolled in ENG102 - Academic Writing', 'success', 1, '2024-01-21 15:05:00'),
(7, 7, 'Session Reminder', 'Essay Structure and Organization session tomorrow at 15:30', 'info', 0, '2024-12-26 20:00:00'),
(8, 2, 'New Student Enrollment', 'Alice Williams has enrolled in your CS101 course', 'info', 1, '2024-01-15 10:10:00'),
(9, 2, 'Course Assignment', 'You have been assigned to teach BUS301 - Business Management', 'info', 1, '2024-01-10 09:00:00'),
(10, 3, 'Student Enrollment', 'Multiple students have enrolled in your MATH201 course', 'info', 1, '2024-01-20 10:35:00'),
(11, 3, 'Resource Upload Successful', 'Your Physics Laboratory Manual has been uploaded successfully', 'success', 1, '2024-12-18 16:20:00'),
(12, 4, 'Course Assignment', 'You have been assigned to teach ENG102 - Academic Writing', 'info', 1, '2024-01-08 14:30:00');

-- --------------------------------------------------------

--
-- Table structure for table `ratings`
--

CREATE TABLE `ratings` (
  `id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `resources`
--

CREATE TABLE `resources` (
  `id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `lecturer_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `type` enum('pdf','video','link','document') NOT NULL,
  `file_path` varchar(500) DEFAULT NULL,
  `file_size` bigint(20) DEFAULT NULL,
  `external_url` text DEFAULT NULL,
  `views_count` int(11) DEFAULT 0,
  `downloads_count` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `resource_type` varchar(50) DEFAULT 'document',
  `file_url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `resources`
--

INSERT INTO `resources` (`id`, `course_id`, `lecturer_id`, `title`, `description`, `type`, `file_path`, `file_size`, `external_url`, `views_count`, `downloads_count`, `is_active`, `created_at`, `updated_at`, `resource_type`, `file_url`) VALUES
(1, 1, 2, 'Introduction to Programming Concepts', 'Basic programming concepts and syntax overview', 'link', NULL, NULL, 'https://docs.google.com/document/d/sample-programming-intro', 2, 0, 1, '2025-07-25 19:18:27', '2025-07-25 19:18:27', 'document', NULL),
(2, 1, 2, 'Python Basics Tutorial', 'Step-by-step Python programming tutorial for beginners', 'link', NULL, NULL, 'https://www.youtube.com/watch?v=sample-python-tutorial', 2, 0, 1, '2025-07-25 19:18:27', '2025-07-25 19:18:27', 'document', NULL),
(3, 1, 2, 'Programming Exercise Set 1', 'Practice problems for basic programming concepts', 'link', NULL, NULL, 'https://github.com/sample/programming-exercises', 1, 0, 1, '2025-07-25 19:18:27', '2025-07-25 19:18:27', 'document', NULL),
(4, 2, 3, 'Calculus Fundamentals', 'Introduction to limits, derivatives, and integrals', 'link', NULL, NULL, 'https://drive.google.com/file/d/sample-calculus-notes', 3, 0, 1, '2025-07-25 19:18:27', '2025-07-25 19:18:27', 'document', NULL),
(5, 2, 3, 'Derivative Rules Reference', 'Quick reference guide for differentiation rules', 'link', NULL, NULL, 'https://docs.google.com/document/d/sample-derivatives', 1, 0, 1, '2025-07-25 19:18:27', '2025-07-25 19:18:27', 'document', NULL),
(6, 3, 4, 'Academic Writing Guidelines', 'Comprehensive guide to academic writing standards', 'link', NULL, NULL, 'https://docs.google.com/document/d/sample-writing-guide', 3, 0, 1, '2025-07-25 19:18:27', '2025-07-25 19:18:27', 'document', NULL),
(7, 3, 4, 'Citation and Referencing', 'APA and MLA citation styles with examples', 'link', NULL, NULL, 'https://drive.google.com/file/d/sample-citations', 2, 0, 1, '2025-07-25 19:18:27', '2025-07-25 19:18:27', 'document', NULL),
(8, 4, 3, 'Physics Laboratory Manual', 'Complete lab procedures and safety guidelines', 'link', NULL, NULL, 'https://docs.google.com/document/d/sample-lab-manual', 2, 0, 1, '2025-07-25 19:18:27', '2025-07-25 19:18:27', 'document', NULL),
(9, 4, 3, 'Newton\'s Laws Explained', 'Interactive demonstration of Newton\'s three laws', 'link', NULL, NULL, 'https://www.youtube.com/watch?v=sample-newtons-laws', 2, 0, 1, '2025-07-25 19:18:27', '2025-07-25 19:18:27', 'document', NULL),
(10, 5, 2, 'Management Principles Overview', 'Key concepts in modern business management', 'link', NULL, NULL, 'https://docs.google.com/presentation/d/sample-management', 2, 0, 1, '2025-07-25 19:18:27', '2025-07-25 19:18:27', 'document', NULL),
(11, 5, 2, 'Case Study: Successful Organizations', 'Analysis of successful business management strategies', 'link', NULL, NULL, 'https://drive.google.com/file/d/sample-case-study', 2, 0, 1, '2025-07-25 19:18:27', '2025-07-25 19:18:27', 'document', NULL),
(12, 5, 2, 'Exam', 'Ihjgk', 'pdf', '/home/u972712031/domains/mindeverest.site/public_html/chosen/config/../uploads/1755969703_68a9f8a7e92f6.pdf', 294428, NULL, 0, 0, 1, '2025-08-23 17:21:43', '2025-08-23 17:21:43', 'document', NULL),
(13, 1, 21, 'Bj', '', 'document', '/home/u972712031/domains/mindeverest.site/public_html/chosen/config/../uploads/1762890004_691391146f6be.docx', 83296, NULL, 0, 0, 1, '2025-11-11 19:40:04', '2025-11-11 19:40:04', 'document', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `resource_access`
--

CREATE TABLE `resource_access` (
  `id` int(11) NOT NULL,
  `resource_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `access_type` enum('view','download') NOT NULL,
  `accessed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `resource_access`
--

INSERT INTO `resource_access` (`id`, `resource_id`, `student_id`, `access_type`, `accessed_at`) VALUES
(1, 1, 5, 'view', '2024-12-20 10:15:00'),
(2, 1, 6, 'view', '2024-12-20 11:30:00'),
(3, 2, 5, 'view', '2024-12-21 09:45:00'),
(4, 2, 5, 'view', '2024-12-22 14:20:00'),
(5, 3, 6, 'view', '2024-12-21 16:10:00'),
(6, 4, 5, 'view', '2024-12-19 13:25:00'),
(7, 4, 7, 'view', '2024-12-20 15:40:00'),
(8, 4, 9, 'view', '2024-12-21 10:30:00'),
(9, 5, 7, 'view', '2024-12-22 11:15:00'),
(10, 6, 5, 'view', '2024-12-18 14:50:00'),
(11, 6, 7, 'view', '2024-12-19 16:25:00'),
(12, 6, 9, 'view', '2024-12-20 12:40:00'),
(13, 7, 5, 'view', '2024-12-21 13:20:00'),
(14, 7, 7, 'view', '2024-12-22 09:35:00'),
(15, 8, 6, 'view', '2024-12-20 14:15:00'),
(16, 8, 8, 'view', '2024-12-21 10:45:00'),
(17, 9, 6, 'view', '2024-12-22 16:30:00'),
(18, 9, 8, 'view', '2024-12-23 11:20:00'),
(19, 10, 7, 'view', '2024-12-19 15:10:00'),
(20, 10, 8, 'view', '2024-12-20 13:25:00'),
(21, 11, 7, 'view', '2024-12-21 14:40:00'),
(22, 11, 8, 'view', '2024-12-22 12:15:00');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `lecturer_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `session_date` datetime NOT NULL,
  `duration_minutes` int(11) DEFAULT 60,
  `meeting_link` varchar(500) DEFAULT NULL,
  `meeting_platform` enum('zoom','google_meet','other') DEFAULT 'zoom',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `session_type` varchar(50) DEFAULT 'tutoring',
  `max_students` int(11) DEFAULT 10,
  `status` enum('scheduled','ongoing','completed','cancelled') DEFAULT 'scheduled',
  `location` varchar(255) DEFAULT 'Online',
  `start_time` datetime GENERATED ALWAYS AS (`session_date`) STORED
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `course_id`, `lecturer_id`, `title`, `description`, `session_date`, `duration_minutes`, `meeting_link`, `meeting_platform`, `is_active`, `created_at`, `session_type`, `max_students`, `status`, `location`) VALUES
(1, 1, 2, 'Python Programming Fundamentals', 'Introduction to Python syntax and basic programming concepts', '2024-12-25 14:00:00', 90, 'https://zoom.us/j/1234567890', 'zoom', 1, '2025-07-25 19:18:27', 'tutoring', 10, 'scheduled', 'Online'),
(2, 2, 3, 'Limits and Continuity', 'Understanding limits and their applications in calculus', '2024-12-26 10:00:00', 75, 'https://meet.google.com/abc-defg-hij', 'google_meet', 1, '2025-07-25 19:18:27', 'tutoring', 10, 'scheduled', 'Online'),
(3, 3, 4, 'Essay Structure and Organization', 'How to structure academic essays effectively', '2024-12-27 15:30:00', 60, 'https://zoom.us/j/0987654321', 'zoom', 1, '2025-07-25 19:18:27', 'tutoring', 10, 'scheduled', 'Online'),
(4, 4, 3, 'Motion and Forces Lab', 'Hands-on experiments with Newton\'s laws', '2024-12-28 13:00:00', 120, 'https://meet.google.com/xyz-uvwx-rst', 'google_meet', 1, '2025-07-25 19:18:27', 'tutoring', 10, 'scheduled', 'Online'),
(5, 5, 2, 'Team Management Strategies', 'Effective techniques for managing diverse teams', '2024-12-30 11:00:00', 90, 'https://zoom.us/j/5555555555', 'zoom', 1, '2025-07-25 19:18:27', 'tutoring', 10, 'scheduled', 'Online'),
(6, 1, 2, 'Course Introduction', 'Welcome and overview of Computer Science fundamentals', '2024-12-15 14:00:00', 60, 'https://zoom.us/j/1111111111', 'zoom', 1, '2025-07-25 19:18:27', 'tutoring', 10, 'scheduled', 'Online'),
(7, 2, 3, 'Introduction to Calculus', 'Overview of calculus concepts and applications', '2024-12-16 10:00:00', 75, 'https://meet.google.com/intro-calc-session', 'google_meet', 1, '2025-07-25 19:18:27', 'tutoring', 10, 'scheduled', 'Online'),
(8, 1, 21, 'Isj', '', '2025-11-11 09:40:00', 60, '', 'other', 1, '2025-11-11 19:40:49', 'lecture', 10, 'scheduled', 'Online');

-- --------------------------------------------------------

--
-- Table structure for table `session_enrollments`
--

CREATE TABLE `session_enrollments` (
  `id` int(11) NOT NULL,
  `session_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `enrollment_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('enrolled','attended','absent') DEFAULT 'enrolled',
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `setting_description`, `created_at`, `updated_at`) VALUES
(1, 'site_name', 'StudySmart', 'Website name', '2025-08-16 10:11:19', '2025-08-16 10:11:19'),
(2, 'site_description', 'Online Tutoring Platform', 'Website description', '2025-08-16 10:11:19', '2025-08-16 10:11:19'),
(3, 'max_file_size', '10485760', 'Maximum file upload size in bytes', '2025-08-16 10:11:19', '2025-08-16 10:11:19'),
(4, 'allowed_file_types', 'pdf,doc,docx,ppt,pptx,jpg,jpeg,png', 'Allowed file types for uploads', '2025-08-16 10:11:19', '2025-08-16 10:11:19'),
(5, 'session_timeout', '3600', 'Session timeout in seconds', '2025-08-16 10:11:19', '2025-08-16 10:11:19'),
(6, 'maintenance_mode', '0', 'Maintenance mode (0=off, 1=on)', '2025-08-16 10:11:19', '2025-08-16 10:11:19');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `role` enum('admin','lecturer','student') NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `whatsapp_number` varchar(20) DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `two_factor_enabled` tinyint(1) DEFAULT 0,
  `two_factor_secret` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `first_name`, `last_name`, `role`, `phone`, `whatsapp_number`, `profile_image`, `is_active`, `two_factor_enabled`, `two_factor_secret`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin@studysmart.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System', 'Administrator', 'admin', NULL, NULL, NULL, 1, 0, NULL, '2025-07-25 19:08:55', '2025-07-25 19:08:55'),
(2, 'lecturer1', 'lecturer1@studysmart.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John', 'Smith', 'lecturer', '+260971234567', '+260971234567', NULL, 1, 0, NULL, '2025-07-25 19:18:26', '2025-07-25 19:18:26'),
(3, 'lecturer2', 'lecturer2@studysmart.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Sarah', 'Johnson', 'lecturer', '+260971234568', '+260971234568', NULL, 1, 0, NULL, '2025-07-25 19:18:26', '2025-07-25 19:18:26'),
(4, 'lecturer3', 'lecturer3@studysmart.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Michael', 'Brown', 'lecturer', '+260971234569', '+260971234569', NULL, 1, 0, NULL, '2025-07-25 19:18:26', '2025-07-25 19:18:26'),
(5, 'student1', 'student1@studysmart.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Alice', 'Williams', 'student', '+260971234570', '+260971234570', NULL, 1, 0, NULL, '2025-07-25 19:18:26', '2025-07-25 19:18:26'),
(6, 'student2', 'student2@studysmart.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Bob', 'Davis', 'student', '+260971234571', '+260971234571', NULL, 1, 0, NULL, '2025-07-25 19:18:26', '2025-07-25 19:18:26'),
(7, 'student3', 'student3@studysmart.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Carol', 'Miller', 'student', '+260971234572', '+260971234572', NULL, 1, 0, NULL, '2025-07-25 19:18:26', '2025-07-25 19:18:26'),
(8, 'student4', 'student4@studysmart.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'David', 'Wilson', 'student', '+260971234573', '+260971234573', NULL, 1, 0, NULL, '2025-07-25 19:18:26', '2025-07-25 19:18:26'),
(9, 'student5', 'student5@studysmart.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Eva', 'Moore', 'student', '+260971234574', '+260971234574', NULL, 1, 0, NULL, '2025-07-25 19:18:26', '2025-07-25 19:18:26'),
(20, 'choolwe', 'choolwedm3@gmail.com', '$2y$10$eyyYGmDBHzkcjhHCJC6FHubT2eK62FRLhwoGBSSggVBIF4GY.8vZ6', 'Choolwe', 'Muloongo', 'lecturer', '0970063248', '0970063248', NULL, 1, 0, NULL, '2025-08-16 09:23:46', '2025-08-16 09:23:46'),
(21, 'Jphiri', 'jp@studysmart.com', '$2y$10$ipcLXgMHhWB7HsmBhzKy8.yUnfhG2AYXGPHcJtG9s9xn.dz.W3Lfi', 'Jayson', 'Phiri', 'lecturer', '1231567890', '1234567890', NULL, 1, 0, NULL, '2025-08-23 16:34:56', '2025-08-23 16:34:56');

-- --------------------------------------------------------

--
-- Table structure for table `user_notifications`
--

CREATE TABLE `user_notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `notification_id` int(11) NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `user_notifications`
--

INSERT INTO `user_notifications` (`id`, `user_id`, `notification_id`, `is_read`, `read_at`, `created_at`) VALUES
(1, 1, 1, 0, NULL, '2025-08-16 10:14:54'),
(2, 1, 2, 0, NULL, '2025-08-16 10:14:54');

-- --------------------------------------------------------

--
-- Table structure for table `user_sessions`
--

CREATE TABLE `user_sessions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `session_token` varchar(255) NOT NULL,
  `device_info` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `last_activity` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `user_sessions`
--

INSERT INTO `user_sessions` (`id`, `user_id`, `session_token`, `device_info`, `ip_address`, `user_agent`, `last_activity`, `created_at`, `expires_at`, `is_active`) VALUES
(1, 1, 'a6c0c31080330a864dd3fcaf7710ffe5d77b35c63502cfced777ffad7ddc2243', 'Desktop', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-29 16:40:36', '2025-07-29 16:36:33', '2025-07-30 17:36:33', 1),
(2, 1, 'bd407d661a3de1b82a99fe82acf4ad6a45fe8a50e0cd416c10ea7a5216421525', 'Desktop', 'unknown', 'unknown', '2025-08-16 08:12:48', '2025-08-16 08:12:47', '2025-08-17 09:12:47', 0),
(3, 2, '5aac7eb6f125ce3a42eaf357e998234b1b8dcd41ea674cb575fc24d8910ecf8c', 'Desktop', 'unknown', 'unknown', '2025-08-16 08:12:48', '2025-08-16 08:12:48', '2025-08-17 09:12:48', 0),
(4, 5, '4ecb54918aca30775b16594ea8d35f5ee1cb68d19cd9f5e434516d9bfce0356e', 'Desktop', 'unknown', 'unknown', '2025-08-16 08:12:49', '2025-08-16 08:12:48', '2025-08-17 09:12:48', 0),
(5, 1, '84005f806948bee316a0e716addec8a750ec04d939d1a41196e7128a6557403a', 'Desktop', 'unknown', 'unknown', '2025-08-16 08:17:51', '2025-08-16 08:17:50', '2025-08-17 09:17:50', 0),
(6, 2, '55bc4c34e7ba58be2c3015778242fb18dd5e87fb5befd9e61892a78c8c9e8ad9', 'Desktop', 'unknown', 'unknown', '2025-08-16 08:17:51', '2025-08-16 08:17:51', '2025-08-17 09:17:51', 0),
(7, 5, '218195ccf3a59247f33ea04cd5dca41a66f1264c12c959c661fd8e12a46dd193', 'Desktop', 'unknown', 'unknown', '2025-08-16 08:17:51', '2025-08-16 08:17:51', '2025-08-17 09:17:51', 0),
(8, 1, '6c1a8b3e46f044c656bd3b1661d2bcc62859da9b54a5874e8112925ab63b46b6', 'Desktop', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', '2025-08-16 08:21:56', '2025-08-16 08:21:56', '2025-09-15 09:21:56', 1),
(9, 1, '287aec0fb01121ac01038670405301346399b58a454c88b0da7aeba7b46dc4fd', 'Desktop', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36 Edg/139.0.0.0', '2025-08-16 08:25:38', '2025-08-16 08:22:44', '2025-08-17 09:22:44', 0),
(10, 1, 'e1574a183cf3f93ca149539de085757474fa596f087222d2826eee0234361624', 'Desktop', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36 Edg/139.0.0.0', '2025-08-16 09:07:50', '2025-08-16 08:27:36', '2025-08-17 09:27:36', 0),
(11, 1, '21c27303a5f25ca98697b89bccdfb8fc0d53a7e376991f297014bcfcefb5f055', 'Desktop', 'unknown', 'unknown', '2025-08-16 08:30:21', '2025-08-16 08:30:21', '2025-08-17 09:30:21', 0),
(12, 1, 'ca36841d7b5069690c75d6da30920369f1cb657c72ee9aeb230adaabade91d67', 'Desktop', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36 Edg/139.0.0.0', '2025-08-16 10:16:14', '2025-08-16 09:07:53', '2025-08-17 10:07:53', 0),
(13, 20, '584ef3206b1db68d11e15d15c77e9e1ea09c3370d04c315904a702c80cd7bedd', 'Desktop', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36 Edg/139.0.0.0', '2025-08-16 12:10:10', '2025-08-16 10:16:19', '2025-08-17 11:16:19', 0),
(14, 5, '8c99c2b67ce7f3518b0c5bdc2489fbde8df917858e2cec67d3784e03372d8501', 'Desktop', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36 Edg/139.0.0.0', '2025-08-16 13:42:37', '2025-08-16 12:10:25', '2025-08-17 13:10:25', 0),
(15, 5, 'c5c803bdf310c6180fa6b80797a8375494196c22a41dd750922cc6827d1237ed', 'Desktop', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36 Edg/139.0.0.0', '2025-08-16 13:45:00', '2025-08-16 13:43:26', '2025-08-17 14:43:26', 0),
(16, 5, '92902da7697f6ebea67dfe75084901b8da52b2e54f3a810bf090165a2888474c', 'Desktop', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36 Edg/139.0.0.0', '2025-08-16 16:20:57', '2025-08-16 14:38:10', '2025-08-17 15:38:10', 1),
(17, 20, 'fec05ba0217f4b9808b1083b8001047da201810b5a1ebc2dc0f0fec3fe65fbdf', 'Desktop', '45.215.254.172', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36 Edg/139.0.0.0', '2025-08-16 17:33:57', '2025-08-16 17:33:36', '2025-08-17 17:33:36', 0),
(18, 1, '16e19bb8374cee31cc2c9c15765711c94917b79d83e3af5ceff75d2b3c77356e', 'Desktop', '45.215.254.172', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36 Edg/139.0.0.0', '2025-08-16 17:34:35', '2025-08-16 17:34:08', '2025-08-17 17:34:08', 0),
(19, 1, 'd25d1ff4947d994000caa353a9cbaee6fd78b5136a0279c494de4fc035b86925', 'Desktop', '45.215.254.172', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36 Edg/139.0.0.0', '2025-08-16 17:42:36', '2025-08-16 17:38:44', '2025-08-17 17:38:44', 1),
(20, 1, '9a76164b622397ae07930806963229e6f6f552c22081977eb97fbb3432941a0f', 'Desktop', '102.208.220.205', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-16 18:57:40', '2025-08-16 18:49:57', '2025-08-17 18:49:57', 0),
(21, 5, '740955f0d41e43e9ff06dc76fdc0462f54f040dc15e7914dbd0339c46f7686f8', 'Desktop', '2605:59c0:5d0c:4008:315d:5a29:42f0:36ff', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0', '2025-08-16 20:02:08', '2025-08-16 19:36:17', '2025-08-17 19:36:17', 0),
(22, 5, '6fc656a4561b4f6f3f3e0ca2373090918dcea2de20fb26d6ed154399ab3bb9f2', 'Desktop', '2605:59c0:5d0c:4008:745d:4d1f:7efe:f4c8', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0', '2025-08-16 20:53:21', '2025-08-16 20:43:11', '2025-08-17 20:43:11', 0),
(23, 2, 'ef4805b8c21b0213534071531e460c45aa07633891eefa7450afc92aba9e8736', 'Desktop', '2605:59c0:5d0c:4008:745d:4d1f:7efe:f4c8', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0', '2025-08-17 19:49:44', '2025-08-16 20:54:23', '2025-08-17 20:54:23', 1),
(24, 5, 'ca4043f5ff79a953d2824e09925aa261925c6ba2739f943193244975f471e3d2', 'Desktop', '102.208.220.204', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0', '2025-08-18 15:59:18', '2025-08-18 15:40:51', '2025-08-19 15:40:51', 1),
(25, 5, '04593194aa50bf8d99b8971d080d285340efd6711e66a5c16cac6bf4fd6a7fb0', 'Desktop', '41.63.2.190', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0', '2025-08-19 15:33:17', '2025-08-19 15:30:37', '2025-08-20 15:30:37', 1),
(26, 1, 'f1b835d46b0927fbf78f851c652b341639410a966ec02254e055e6efcfbefe97', 'Desktop', '102.208.220.201', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36 Edg/139.0.0.0', '2025-08-21 17:43:30', '2025-08-21 17:32:14', '2025-08-22 17:32:14', 1),
(27, 1, '208e61e6d47bb5974f597612b61f557434a400337721c854bc696852ebce15b5', 'Desktop', '41.60.183.124', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0', '2025-08-21 21:35:50', '2025-08-21 21:26:14', '2025-08-22 21:26:14', 1),
(28, 1, '01b783c8ebc22e66d93e7e520621f06592edc8c09b4f2fa435d2b7161dc808ca', 'Desktop', '45.214.73.150', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36 Edg/139.0.0.0', '2025-08-22 08:45:27', '2025-08-22 08:45:27', '2025-08-23 08:45:27', 1),
(29, 1, '864a73b50d1176f808560606f47d2be2928665010d9ab5244a50fbb093863220', 'Mobile Device', '102.208.220.201', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/28.0 Chrome/130.0.0.0 Mobile Safari/537.36', '2025-08-22 20:44:16', '2025-08-22 20:41:28', '2025-08-23 20:41:28', 0),
(30, 5, '710f5b281a215505013f600c6b1fa803ccd11ec561298339dc817bb0bd942978', 'Mobile Device', '102.208.220.203', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/28.0 Chrome/130.0.0.0 Mobile Safari/537.36', '2025-08-22 20:47:59', '2025-08-22 20:45:01', '2025-08-23 20:45:01', 1),
(31, 5, 'b68a35e6a192be257261162db253954fedae9ead0da536d1af8f9e67d188573e', 'Desktop', '102.208.220.201', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-08-22 21:25:51', '2025-08-22 20:48:28', '2025-08-23 20:48:28', 0),
(32, 5, '0c4e81fb43f76060f6b6552ad97725b37a211f2408957331f4ffb0e45fda7725', 'Mobile Device', '102.208.220.201', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/28.0 Chrome/130.0.0.0 Mobile Safari/537.36', '2025-08-22 21:02:37', '2025-08-22 21:02:10', '2025-08-23 21:02:10', 1),
(33, 2, 'd05e847af361a4365f7df4c0ecfb7ad1d64914aafb4371ab0a92f5402e4b27e2', 'Desktop', '102.208.220.201', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-08-23 16:26:40', '2025-08-22 21:27:10', '2025-08-23 21:27:10', 0),
(34, 5, 'df8d79a2b4d244149a70f21ad24cd870303f88f1cb76f65c54ce8539a7390743', 'Mobile Device', '102.208.220.201', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/28.0 Chrome/130.0.0.0 Mobile Safari/537.36', '2025-08-22 21:49:06', '2025-08-22 21:49:05', '2025-08-23 21:49:05', 1),
(35, 5, '562e8fe2e930b0a5fa39851d214f65911999a9415f1aeea0952f2a24939ccd80', 'Desktop', '102.208.220.201', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36 Edg/139.0.0.0', '2025-08-22 22:44:26', '2025-08-22 22:44:01', '2025-08-23 22:44:01', 1),
(36, 1, '21dacb86c54237a5dc66a6760e3a32021ba94dc5efbe882b7ad0c0d456afb321', 'Mobile Device', '45.212.141.22', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Mobile Safari/537.36', '2025-08-23 17:17:20', '2025-08-23 16:24:34', '2025-08-24 16:24:34', 0),
(37, 1, 'f5a434d234d4e9cb4d0409ce80ea58e2728e1e3f4812de87c1bfba9fcd38d61d', 'Desktop', '102.208.220.201', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-08-23 17:17:38', '2025-08-23 16:27:03', '2025-08-24 16:27:03', 0),
(38, 2, '8c70cd00f6d03c9a68edcdfc54113248a9417f98c5f3effd55024fabf7923b09', 'Desktop', '102.208.220.201', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-08-23 17:25:42', '2025-08-23 17:17:53', '2025-08-24 17:17:53', 0),
(39, 2, '50938c977f4a704c5c2b8fab2a21120c99b5c98cc42e83b195c7c9625a19503c', 'Desktop', '45.212.141.22', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-23 17:25:31', '2025-08-23 17:18:16', '2025-08-24 17:18:16', 0),
(40, 5, '2e3e14bc06a4f535ea6aa83fd1bc6492dbe1bae34eb771c7ffdde6f5c483e842', 'Desktop', '102.208.220.201', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-08-23 17:35:48', '2025-08-23 17:25:47', '2025-08-24 17:25:47', 1),
(41, 5, '0144d278bc47d9c84a684849a4fdb939b329e561fe276665dda0b6ee08ed4beb', 'Desktop', '45.212.141.22', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-23 17:35:18', '2025-08-23 17:25:52', '2025-08-24 17:25:52', 0),
(42, 5, 'b1853f2650ea328a63e0bbe44cc87059550b25d3867e8f28a9525de798304c56', 'Mobile Device', '45.215.255.218', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/25.0 Chrome/121.0.0.0 Mobile Safari/537.36', '2025-08-25 08:06:44', '2025-08-25 06:55:40', '2025-08-26 06:55:40', 0),
(43, 1, 'ccd2d2ab2b8725912004050a0ffe4d63e0c7d7e82137bbb534ad0b40688f1f8c', 'Desktop', '45.215.255.218', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/25.0 Chrome/121.0.0.0 Safari/537.36', '2025-08-25 09:02:34', '2025-08-25 08:07:04', '2025-08-26 08:07:04', 1),
(44, 5, '5622be8e379a1e9fcadf04a1da428241790eb5f007bd189ca427e596253d3801', 'Mobile Device', '41.63.2.190', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/25.0 Chrome/121.0.0.0 Mobile Safari/537.36', '2025-08-25 09:15:07', '2025-08-25 09:03:52', '2025-08-26 09:03:52', 1),
(45, 5, '4c550126dde5ea7b073e5201b43328e1896966cc880cabbed2136ca48dc59f4f', 'Mobile Device', '2605:59c0:5d0c:4008:ac03:83dc:d88:d56c', 'Mozilla/5.0 (iPad; CPU OS 9_3_5 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Version/9.0 Mobile/13G36 Safari/601.1', '2025-08-26 06:38:49', '2025-08-26 06:03:54', '2025-08-27 06:03:54', 1),
(46, 1, '7e5407137ef8974d1c5b402711685951bf6628a3b9a4d1c7f5296dce44a3afe7', 'Mobile Device', '2605:59c0:5d0c:4008:8c36:d6e8:bd9e:e3dc', 'Mozilla/5.0 (iPad; CPU OS 9_3_5 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Version/9.0 Mobile/13G36 Safari/601.1', '2025-08-26 08:28:50', '2025-08-26 08:23:08', '2025-08-27 08:23:08', 0),
(47, 5, 'c009a61ffbda0d06915b8afe994c4c5e72a879ffcdaab18053d3a107037946c1', 'Mobile Device', '2605:59c0:5d0c:4008:8c36:d6e8:bd9e:e3dc', 'Mozilla/5.0 (iPad; CPU OS 9_3_5 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Version/9.0 Mobile/13G36 Safari/601.1', '2025-08-26 08:46:05', '2025-08-26 08:34:05', '2025-08-27 08:34:05', 1),
(48, 5, '94583a211a6abaae79a24d349c6c9766f248f9ca51cba45f2e2c15253bd0ec75', 'Mobile Device', '2605:59c0:5d0c:4008:8c36:d6e8:bd9e:e3dc', 'Mozilla/5.0 (iPad; CPU OS 9_3_5 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Version/9.0 Mobile/13G36 Safari/601.1', '2025-08-26 10:04:51', '2025-08-26 10:03:50', '2025-08-27 10:03:50', 0),
(49, 2, '89ac7629b3ed4a04cc2f2a2310cbdf515ea16a2fe4a322f112329d3a26c9e0f6', 'Mobile Device', '2605:59c0:5d0c:4008:8c36:d6e8:bd9e:e3dc', 'Mozilla/5.0 (iPad; CPU OS 9_3_5 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Version/9.0 Mobile/13G36 Safari/601.1', '2025-08-26 10:08:42', '2025-08-26 10:05:12', '2025-08-27 10:05:12', 1),
(50, 5, 'd670c8e6eb3812f606662ba6910d0bad987a008092d464734bc5e49d221829d7', 'Desktop', '102.208.220.201', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36 Edg/139.0.0.0', '2025-08-26 23:41:03', '2025-08-26 23:19:13', '2025-08-27 23:19:13', 1),
(51, 1, 'e7e8d824d4dfe6ab716d820dbd1480cfa8464e0445304dcc5baf5e85458c9a39', 'Mobile Device', '102.208.220.201', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Mobile Safari/537.36', '2025-08-27 07:24:35', '2025-08-27 07:24:35', '2025-08-28 07:24:35', 1),
(52, 5, '3e0f01a14183347834951107918fce54be7a645b07b3e3e98921c119fd03ef2f', 'Desktop', '102.208.220.207', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36 Edg/139.0.0.0', '2025-08-28 05:59:02', '2025-08-28 05:44:41', '2025-08-29 05:44:41', 1),
(53, 1, '561a020793746f1da0939e860aadc9756d335cd513724b1fce2ecbb424a61f67', 'Desktop', '41.216.87.10', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36 Edg/139.0.0.0', '2025-08-28 15:03:04', '2025-08-28 14:44:51', '2025-08-29 14:44:51', 0),
(54, 3, 'fa25ab903e26c1f65f0d7b65be19ef863918da1cb6eff968ce3a812684fdaadf', 'Desktop', '41.216.87.10', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36 Edg/139.0.0.0', '2025-08-28 15:08:53', '2025-08-28 15:03:23', '2025-08-29 15:03:23', 0),
(55, 6, '8b5a90bd2648a7460e04972e2a5e5fbdc953edfcb5c6f146ebad072980ad7ec6', 'Desktop', '41.216.87.10', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36 Edg/139.0.0.0', '2025-08-28 15:12:42', '2025-08-28 15:09:13', '2025-08-29 15:09:13', 1),
(56, 5, '7ece4edeeb13ad2b6ae687d744a2c7de6f11a580f68f32fd613a8953cab1ffd5', 'Desktop', '2605:59c0:5d0c:4008:f472:4b29:59fd:fa64', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36 Edg/139.0.0.0', '2025-08-30 13:23:36', '2025-08-30 10:28:08', '2025-08-31 10:28:08', 0),
(57, 1, '56bfce0f35e20e5974a0cffdb73ecd20d0b96808f1e6c62a95811d0f89410944', 'Desktop', '2605:59c0:5d0c:4008:54dd:bc53:c25b:ce1e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36 Edg/139.0.0.0', '2025-08-30 13:40:04', '2025-08-30 13:40:04', '2025-08-31 13:40:04', 1),
(58, 5, 'd4c3b765db71b8920868051c95f6071e38a8d8aa75a8fedb1f6625fa7006ff00', 'Mobile Device', '45.215.249.130', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Mobile Safari/537.36', '2025-09-01 06:33:40', '2025-09-01 06:33:05', '2025-09-02 06:33:05', 0),
(59, 5, '287d3622c84ed2aae88d0a01772f3e872dc872885b3f48b19e67556774aa2a1e', 'Mobile Device', '45.215.249.130', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Mobile Safari/537.36', '2025-09-01 07:17:12', '2025-09-01 07:14:27', '2025-09-02 07:14:27', 1),
(60, 5, '73ecfa5accf6275e174333deff15fb2e3f38e605eb0c96fd294871f53f8a3bcd', 'Mobile Device', '102.208.220.204', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Mobile Safari/537.36', '2025-09-03 00:19:59', '2025-09-03 00:19:33', '2025-09-04 00:19:33', 1),
(61, 5, '99d80a5e3fdab30ebd6a439aee7a44f64ebbe51f857c495d99e51c6a1d0b8c20', 'Desktop', '102.208.220.201', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36 Edg/139.0.0.0', '2025-09-05 06:49:55', '2025-09-05 06:47:21', '2025-09-06 06:47:21', 1),
(62, 5, 'b5c847f0fea60f3bfe11fcdc10c1cee76faf6ad1454da23b7cafb2a367a9c42f', 'Desktop', '102.208.220.201', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36 Edg/139.0.0.0', '2025-09-05 15:19:56', '2025-09-05 15:19:32', '2025-09-06 15:19:32', 1),
(63, 5, '17b945d7f0a0aa17a047676791642bdac7817575aa4e2e74efac43dfe977d77e', 'Mobile Device', '102.208.220.204', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Mobile Safari/537.36', '2025-09-06 10:08:24', '2025-09-06 10:08:23', '2025-09-07 10:08:23', 1),
(64, 5, 'a4bb07f9f6ea26b77ecaa7a62fbfc4c50bd82d80eac50a37246e1a8e5d0c61e4', 'Desktop', '216.234.213.253', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36 Edg/139.0.0.0', '2025-09-06 23:10:09', '2025-09-06 20:22:03', '2025-09-07 20:22:03', 1),
(65, 5, 'af9389ed034aaea2198bbea955d9063bfe05d4c58471efb1c953761049e158f9', 'Desktop', '2605:59c0:5d0c:4008:2480:b348:6833:7a67', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0', '2025-09-08 23:01:57', '2025-09-08 22:57:18', '2025-09-09 22:57:18', 1),
(66, 5, '1e5364333a0aaf1a27466c13ffc218bede1cf92369757a0b32bd20316da4c095', 'Desktop', '102.208.220.201', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36 Edg/139.0.0.0', '2025-09-10 12:18:40', '2025-09-10 12:13:01', '2025-09-11 12:13:01', 0),
(67, 2, '7258391f7f21e64cb01c71069f16b1115a710d6804565b27281495fd6f76fa43', 'Desktop', '102.208.220.201', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36 Edg/139.0.0.0', '2025-09-10 12:22:39', '2025-09-10 12:19:22', '2025-09-11 12:19:22', 0),
(68, 1, '7940e3c42fa5fedab0e1d43edac96a9e3b233ec5bd5afb4ed4dce119289e678f', 'Desktop', '102.208.220.201', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36 Edg/139.0.0.0', '2025-09-10 12:24:52', '2025-09-10 12:23:10', '2025-09-11 12:23:10', 0),
(69, 5, '3f3e7520b1fa5345067a55bef1f3ae4954861dbb25af72ca9c07be1918b7e075', 'Mobile Device', '2605:59c0:5d0c:4008:bc7e:f4cb:8e9a:76b3', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Mobile Safari/537.36', '2025-09-11 20:35:42', '2025-09-11 20:35:42', '2025-09-12 20:35:42', 1),
(70, 5, 'b92e63bc26e3d882cf38c42df72ab5d077be9b821e8c66f60e4a59f43f8f7cd7', 'Mobile Device', '2605:59c0:5d0c:4008:804e:8dbe:376f:db65', 'Mozilla/5.0 (iPad; CPU OS 9_3_5 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Version/9.0 Mobile/13G36 Safari/601.1', '2025-09-14 13:41:45', '2025-09-13 09:21:16', '2025-09-14 09:21:16', 0),
(71, 5, '27d0187af5e12e675c3fd8de2888354b1f83b996e39b00b621669d0d83994f09', 'Mobile Device', '2605:59c0:5d0c:4008:a138:f596:7b46:84b1', 'Mozilla/5.0 (iPad; CPU OS 9_3_5 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Version/9.0 Mobile/13G36 Safari/601.1', '2025-09-14 11:37:30', '2025-09-14 11:34:54', '2025-09-15 11:34:54', 0),
(72, 1, '6a9c39c784366544caf6b017a75c423c68221923c7fc8e877873c71cf30cc10c', 'Mobile Device', '2605:59c0:5d0c:4008:a138:f596:7b46:84b1', 'Mozilla/5.0 (iPad; CPU OS 9_3_5 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Version/9.0 Mobile/13G36 Safari/601.1', '2025-09-14 11:40:07', '2025-09-14 11:37:43', '2025-09-15 11:37:43', 1),
(73, 3, '67dc4d7de96850abede67b27c4621ba8ca5c1b9a62f7e553e0fb0b67309f7bf4', 'Mobile Device', '2605:59c0:5d0c:4008:55f6:eea6:3fe6:3174', 'Mozilla/5.0 (iPad; CPU OS 9_3_5 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Version/9.0 Mobile/13G36 Safari/601.1', '2025-09-14 13:45:15', '2025-09-14 13:45:02', '2025-09-15 13:45:02', 1),
(74, 5, 'ed77720005960401fa008b7995dc3fa18996c505c0c0f568c40e1b808b7911bc', 'Mobile Device', '45.215.249.82', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Mobile Safari/537.36', '2025-09-16 14:54:47', '2025-09-16 14:54:21', '2025-09-17 14:54:21', 1),
(75, 5, 'b167319f58644b4fc0036c323537c246ef96abd1747a5b8eaca55612f3feba42', 'Desktop', '102.208.220.204', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0', '2025-09-17 10:07:25', '2025-09-17 10:05:54', '2025-09-18 10:05:54', 1),
(76, 5, 'fd1e3b2bf6d494b1a1d63a4c37a0bd3126ccccb7725675ccd3737fd64d54d8d0', 'Mobile Device', '2605:59c0:5d0c:4008:80a6:2cb3:b32f:19d2', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/28.0 Chrome/130.0.0.0 Mobile Safari/537.36', '2025-09-20 04:04:15', '2025-09-20 04:03:16', '2025-09-21 04:03:16', 1),
(77, 5, '05513100468f2b2dc391fed9e70e6a87ea2d881d8486d610b40b8f010e659789', 'Desktop', '2605:59c0:5d0c:4008:35fa:e2e9:5da9:9458', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0', '2025-09-20 21:23:46', '2025-09-20 14:06:51', '2025-09-21 14:06:51', 1),
(78, 5, 'a1c0abe787ad3e88db5b4dd63751f73e277158f7b4faa9eb84eda3962a264740', 'Desktop', '41.60.177.79', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0', '2025-09-26 03:22:39', '2025-09-26 03:20:07', '2025-09-27 03:20:07', 1),
(79, 5, '382eccc399c30765964cc7d4132ce86354216732dfc2bbc323d56e12e21957fe', 'Mobile Device', '216.234.213.135', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Mobile Safari/537.36', '2025-10-05 02:30:21', '2025-10-05 02:30:14', '2025-10-06 02:30:14', 1),
(80, 5, 'd0282646a7b861a1efa446e0c53e637f7121612e8ffde03cc3820177a50209e4', 'Mobile Device', '102.208.221.215', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Mobile Safari/537.36', '2025-10-06 06:11:41', '2025-10-06 06:10:57', '2025-10-07 06:10:57', 1),
(81, 5, '3d8288e728bdbc85298d21e10bbc23257047295c5ac6788ff1c5d0ff0811bc86', 'Mobile Device', '41.223.116.244', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Mobile Safari/537.36', '2025-10-08 16:34:10', '2025-10-08 16:33:46', '2025-10-09 16:33:46', 1),
(82, 5, 'a1f577a98f60f54dc4a508882da1d6ffb035b27826973bc81ec549559bf4f005', 'Desktop', '2605:59c0:5d0c:4008:50da:7af4:b9b3:674d', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36 Edg/139.0.0.0', '2025-10-14 19:06:55', '2025-10-14 18:26:49', '2025-10-15 18:26:49', 1),
(83, 1, '08cc24374a8f6954c2e1f3cfcf929396e7f957ac1598a455f304c910826a15fb', 'Desktop', '2605:59c0:5d0c:4008:50da:7af4:b9b3:674d', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36 Edg/139.0.0.0', '2025-10-14 19:47:42', '2025-10-14 19:47:03', '2025-10-15 19:47:03', 1),
(84, 1, 'b93e2e5178decc2e6799f2617cb8026a9fa9826775dc8920e2d3ee84dac5bd95', 'Desktop', '2605:59c0:5d0c:4008:8ce4:d85c:ffdf:ad87', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-10-21 09:57:35', '2025-10-21 09:54:49', '2025-10-22 09:54:49', 1),
(85, 1, '93fe38252f460c2e470ba1d62f6bfc7bb56bdad8adde65fbadd9a4e89ef4e0e0', 'Desktop', '2605:59c0:5d0c:4008:859a:a409:7878:1c7d', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-25 20:33:54', '2025-10-25 20:27:21', '2025-10-26 20:27:21', 0),
(86, 1, '996478e200690a108909b7b39f4afa72c9cd52c140f509aaba87dbf74ed30d17', 'Desktop', '2605:59c0:5d0c:4008:859a:a409:7878:1c7d', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-25 20:54:21', '2025-10-25 20:35:13', '2025-10-26 20:35:13', 0),
(87, 5, '66bba8f5df87f622ff645b5c632ca23901e4b8b7db4d06403501b4ff8c495a83', 'Mobile Device', '45.215.249.205', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Mobile Safari/537.36', '2025-10-27 08:38:06', '2025-10-27 08:35:35', '2025-10-28 08:35:35', 1),
(88, 5, '7e34f86dbdfbf91065bd2fd0a519a165a6094c8fcd9748e543e30e001acadff9', 'Desktop', '102.208.220.204', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-27 10:03:48', '2025-10-27 09:52:51', '2025-10-28 09:52:51', 1),
(89, 5, '940d487a3f3233e51a78dc51bea447892b93ecec6d6a9518c473cc5382f8bfd4', 'Desktop', '2605:59c0:5d0c:4008:342c:2c3f:5505:6acf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-28 20:01:19', '2025-10-28 20:00:55', '2025-10-29 20:00:55', 1),
(90, 5, 'a3527fa06654524639ebb672780ee1a39551e5d7c404a0141ca75b7d43912d90', 'Desktop', '2605:59c0:5d0c:4008:98ee:40f1:6e46:4331', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-29 21:06:51', '2025-10-29 10:13:35', '2025-10-30 10:13:35', 0),
(91, 5, '91a7782e2719b2adce81b7dbb79b443066d3096c773d1db6cd35d1b38e3a412b', 'Desktop', '2605:59c0:5d0c:4008:edd3:223d:3541:2188', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 02:37:47', '2025-10-31 01:49:45', '2025-11-01 01:49:45', 1),
(92, 5, '9411b66c26b232c30eb0efd882a9800a932dbd39fec214502b92a7398532009a', 'Desktop', '2605:59c0:5d0c:4008:b096:d266:4034:b4db', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-11-01 22:26:07', '2025-11-01 22:24:27', '2025-11-02 22:24:27', 1),
(93, 5, 'd995e26e325f9b827d181be82617b6f67ea6cf9d588c6270e044f5b970b52f9c', 'Desktop', '102.148.18.103', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-03 11:20:50', '2025-11-03 11:20:49', '2025-11-04 11:20:49', 1),
(94, 5, 'dfe93097da40c7b418658fb7428a7a07168c1fce9e4745f7986ab32795903e32', 'Desktop', '2605:59c0:5d0c:4008:5171:4954:3b5c:7536', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-10 01:45:13', '2025-11-09 11:05:13', '2025-11-10 11:05:13', 1),
(95, 5, '74734b6630c4607514816757153a234dd98492b19610dcd1db93aacd024bc9d2', 'Desktop', '2605:59c0:5d0c:4008:795d:b613:c1ba:e634', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-10 22:25:14', '2025-11-10 22:11:33', '2025-11-11 22:11:33', 1),
(96, 21, '254ac3bc1a577359ff91cc5048549803ffdf6a98e1f929eab7be712a32fb0bec', 'Mobile Device', '45.215.255.44', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Mobile Safari/537.36', '2025-11-11 19:41:13', '2025-11-11 19:38:56', '2025-11-12 19:38:56', 0),
(97, 5, '3ed8a9f319c300413d0162720dc2305b1ebcf9968c0a68e9a3784e3afb13f62e', 'Desktop', '45.215.255.44', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-11-11 19:43:03', '2025-11-11 19:41:35', '2025-11-12 19:41:35', 0),
(98, 1, 'f1c3d7ddaf6bfc7e0c345bf3e92a74d7052c07e2a2ff7337122c33467ef05c37', 'Desktop', '45.215.255.44', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-11-11 19:44:31', '2025-11-11 19:43:08', '2025-11-12 19:43:08', 0),
(99, 5, '9c46205636d9ebcb8c9f945f1732ba87f4f9f9279c8d8e3b83daba7bf387d4fe', 'Desktop', '2605:59c0:5d0c:4008:3dee:83e5:554d:7333', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-14 12:20:03', '2025-11-14 02:27:18', '2025-11-15 02:27:18', 1),
(100, 5, '63bf2e1a0763214d4c458fab33cb51780880ca55af2863b4a5744091bc0f57fc', 'Desktop', '2605:59c0:5d0c:4008:44d4:ec5e:d0d6:f48d', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-15 16:09:40', '2025-11-15 16:06:06', '2025-11-16 16:06:06', 1),
(101, 5, 'becc228d4028aa17358a2ab9ed605c7e124870664117bbd7d03ca8189ed95732', 'Desktop', '216.234.213.107', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-17 02:16:55', '2025-11-17 02:15:47', '2025-11-18 02:15:47', 1),
(102, 5, 'c95fee83c9b54a5cd69989e78c997d334ad5d25a38b7a122bc16344a4aeda27b', 'Desktop', '41.223.118.39', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-17 11:11:56', '2025-11-17 11:11:21', '2025-11-18 11:11:21', 1),
(103, 5, '7d77252400762575c7469e2e29d4de5862270dba0ace50870da32c5a8129cfa3', 'Desktop', '41.63.2.190', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-17 15:01:18', '2025-11-17 14:47:57', '2025-11-18 14:47:57', 1),
(104, 5, 'd162cf3be3f97130b38efb863ff1771ab9a58ece8fbcf1e6f67c70bb24c65839', 'Desktop', '2605:59c0:5d0c:4008:1960:8e9b:fb4b:3d9f', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-17 20:05:37', '2025-11-17 19:09:34', '2025-11-18 19:09:34', 1),
(105, 5, 'f453036c92f6e690f758c8082a3db12f56c2a78807aefc4a4998f5b026c55816', 'Mobile Device', '102.212.181.85', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0.1 Mobile/15E148 Safari/604.1', '2025-11-18 10:20:16', '2025-11-18 10:20:16', '2025-11-19 10:20:16', 1),
(106, 5, 'b9c16adebd8f83c7092b74b342dab30e4809c1356eb5bc757b1a613a9153a761', 'Desktop', '41.63.2.190', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-20 02:34:27', '2025-11-20 02:02:23', '2025-11-21 02:02:23', 1),
(107, 5, 'ae59429348b3409aa29fe91a78366e25f8a951153cc1297d6458ac7bfbe07569', 'Desktop', '41.63.2.190', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-24 01:20:10', '2025-11-23 22:57:46', '2025-11-24 22:57:46', 1),
(108, 5, 'e078aff9dea62796b8add426f5f68d6ed6d39d2e173ede48fb6a39c0681ee740', 'Desktop', '41.63.2.190', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-24 23:46:16', '2025-11-24 23:39:52', '2025-12-24 23:39:52', 1),
(109, 5, '234e25420826de4ce7bf409452b2a863d212e5a9ac92d4e60649df749b25dafc', 'Desktop', '41.63.2.190', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-26 00:25:14', '2025-11-26 00:23:01', '2025-11-27 00:23:01', 1),
(110, 5, '09f3a2660a09efe049078cd6791bf6aab2d8d277df0d4527ca96dfda10a1a740', 'Desktop', '2605:59c0:5d0c:4008:7d79:23e7:9616:a597', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-26 22:26:23', '2025-11-26 22:00:51', '2025-11-27 22:00:51', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_logs`
--
ALTER TABLE `admin_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `course_code` (`course_code`),
  ADD KEY `idx_courses_lecturer` (`lecturer_id`);

--
-- Indexes for table `course_ratings`
--
ALTER TABLE `course_ratings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_rating` (`course_id`,`student_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `lecturer_id` (`lecturer_id`);

--
-- Indexes for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_enrollment` (`student_id`,`course_id`),
  ADD KEY `idx_enrollments_student` (`student_id`),
  ADD KEY `idx_enrollments_course` (`course_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_notifications_user` (`user_id`);

--
-- Indexes for table `ratings`
--
ALTER TABLE `ratings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_id` (`course_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `resources`
--
ALTER TABLE `resources`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lecturer_id` (`lecturer_id`),
  ADD KEY `idx_resources_course` (`course_id`);

--
-- Indexes for table `resource_access`
--
ALTER TABLE `resource_access`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_resource_access_resource` (`resource_id`),
  ADD KEY `idx_resource_access_student` (`student_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lecturer_id` (`lecturer_id`),
  ADD KEY `idx_sessions_course` (`course_id`);

--
-- Indexes for table `session_enrollments`
--
ALTER TABLE `session_enrollments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `session_id` (`session_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_users_role` (`role`),
  ADD KEY `idx_users_email` (`email`);

--
-- Indexes for table `user_notifications`
--
ALTER TABLE `user_notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `notification_id` (`notification_id`);

--
-- Indexes for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `session_token` (`session_token`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_user_sessions_token` (`session_token`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_logs`
--
ALTER TABLE `admin_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=159;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `course_ratings`
--
ALTER TABLE `course_ratings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `enrollments`
--
ALTER TABLE `enrollments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `ratings`
--
ALTER TABLE `ratings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `resources`
--
ALTER TABLE `resources`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `resource_access`
--
ALTER TABLE `resource_access`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `sessions`
--
ALTER TABLE `sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `session_enrollments`
--
ALTER TABLE `session_enrollments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `user_notifications`
--
ALTER TABLE `user_notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `user_sessions`
--
ALTER TABLE `user_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=111;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin_logs`
--
ALTER TABLE `admin_logs`
  ADD CONSTRAINT `admin_logs_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `courses`
--
ALTER TABLE `courses`
  ADD CONSTRAINT `courses_ibfk_1` FOREIGN KEY (`lecturer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `course_ratings`
--
ALTER TABLE `course_ratings`
  ADD CONSTRAINT `course_ratings_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `course_ratings_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `course_ratings_ibfk_3` FOREIGN KEY (`lecturer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD CONSTRAINT `enrollments_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `enrollments_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `ratings`
--
ALTER TABLE `ratings`
  ADD CONSTRAINT `ratings_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`),
  ADD CONSTRAINT `ratings_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `resources`
--
ALTER TABLE `resources`
  ADD CONSTRAINT `resources_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `resources_ibfk_2` FOREIGN KEY (`lecturer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `resource_access`
--
ALTER TABLE `resource_access`
  ADD CONSTRAINT `resource_access_ibfk_1` FOREIGN KEY (`resource_id`) REFERENCES `resources` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `resource_access_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sessions`
--
ALTER TABLE `sessions`
  ADD CONSTRAINT `sessions_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sessions_ibfk_2` FOREIGN KEY (`lecturer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `session_enrollments`
--
ALTER TABLE `session_enrollments`
  ADD CONSTRAINT `session_enrollments_ibfk_1` FOREIGN KEY (`session_id`) REFERENCES `sessions` (`id`),
  ADD CONSTRAINT `session_enrollments_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `user_notifications`
--
ALTER TABLE `user_notifications`
  ADD CONSTRAINT `user_notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_notifications_ibfk_2` FOREIGN KEY (`notification_id`) REFERENCES `notifications` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD CONSTRAINT `user_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
