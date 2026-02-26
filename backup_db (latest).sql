-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 27, 2026 at 12:22 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.3.29

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `backup_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `id_number` varchar(50) NOT NULL,
  `time_in` datetime DEFAULT NULL,
  `time_out` datetime DEFAULT NULL,
  `work_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `synced` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`id`, `user_id`, `id_number`, `time_in`, `time_out`, `work_date`, `created_at`, `synced`) VALUES
(1, 1, '12', '2026-02-26 14:10:11', '2026-02-26 14:10:25', '2026-02-26', '2026-02-26 06:10:11', 1),
(2, 1, 'admin1', '2026-02-26 07:32:21', '2026-02-26 07:35:02', '2026-02-26', '2026-02-25 23:32:21', 1),
(3, 12, '111', '2026-02-27 07:20:09', '2026-02-27 07:20:16', '2026-02-27', '2026-02-26 23:20:09', 0);

-- --------------------------------------------------------

--
-- Table structure for table `booking`
--

CREATE TABLE `booking` (
  `id` int(11) NOT NULL,
  `membership_id` varchar(100) DEFAULT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `contact_number` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `court_type` int(11) NOT NULL,
  `date` date NOT NULL,
  `start_time` varchar(50) NOT NULL,
  `end_time` varchar(50) NOT NULL,
  `total_amount` varchar(50) NOT NULL,
  `is_paid` tinyint(4) NOT NULL DEFAULT 0,
  `booked_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `gcash_receipt` varchar(255) DEFAULT NULL,
  `status` int(11) NOT NULL,
  `synced` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `booking`
--

INSERT INTO `booking` (`id`, `membership_id`, `first_name`, `last_name`, `contact_number`, `email`, `court_type`, `date`, `start_time`, `end_time`, `total_amount`, `is_paid`, `booked_at`, `gcash_receipt`, `status`, `synced`) VALUES
(1, 'CBMS-2026-0001', 'Elizabeth', 'Rebonza', '09092853634', 'rebonzaelizabeth@gmail.com', 3, '2026-02-24', '09:00', '11:00', '180.00', 1, '2026-02-23 12:06:52', 'gcash_696763061b0c6.jpg', 1, 1),
(2, 'CBMS-2026-0001', 'Elizabeth', 'Rebonza', '09092853634', 'rebonzaelizabeth@gmail.com', 1, '2026-02-25', '08:00', '09:00', '945.00', 1, '2026-02-23 12:11:24', 'gcash_69676421914b4.jpg', 1, 1),
(3, 'CBMS-2026-0001', 'Elizabeth', 'Rebonza', '09092853634', 'rebonzaelizabeth@gmail.com', 1, '2026-01-30', '13:00', '15:00', '236.25', 0, '2026-01-30 11:50:04', 'gcash_69676421914b4.jpg', 1, 1),
(6, '', 'ADRIAN', 'PELIGRINO', '09654013233', 'adrianpolpeligrino27@gmail.com', 1, '2026-02-24', '07:00', '09:00', '700', 0, '2026-02-22 21:37:33', 'gcash_699b213fd46d16.89508470.png', 2, 1),
(7, '', 'ADRIAN', 'PELIGRINO', '09654013233', 'adrianpolpeligrino27@gmail.com', 1, '2026-02-23', '07:00', '09:00', '350.00', 1, '2026-02-22 21:40:10', 'gcash_699b22f0969ce1.60855719.png', 1, 1),
(8, '', 'ADRIAN', 'PELIGRINO', '09654013233', 'adrianpolpeligrino27@gmail.com', 1, '2026-02-24', '09:00', '10:00', '175.00', 1, '2026-02-22 21:34:08', 'gcash_699c0b8cb069a3.61791593.jpg', 1, 1),
(9, '', 'Nona', 'Reyes', '09190841745', 'nona.reyes@gmail.com', 1, '2026-02-27', '10:00', '12:00', '350.00', 1, '2026-02-24 16:26:41', 'gcash_699e9f56a35b17.69058125.jpeg', 1, 1),
(10, 'CBMS-2026-0001', 'Antonette Gabrielle', 'Alguzar', '09076559766', 'antonettebalguzar@gmail.com', 1, '2026-02-28', '07:00', '09:00', '630', 0, '2026-02-24 17:17:58', 'gcash_699eb03647ef32.72535676.jpg', 0, 1),
(11, '', 'Rhea', 'Ranises', '09177939535', 'tso_gc@yahoo.com', 1, '2026-03-02', '07:00', '08:00', '350', 0, '2026-02-24 18:12:03', 'gcash_699ebce3a394a9.14536729.jpeg', 0, 1);

-- --------------------------------------------------------

--
-- Table structure for table `cashier_shifts`
--

CREATE TABLE `cashier_shifts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime DEFAULT NULL,
  `total_sales` decimal(10,2) DEFAULT 0.00,
  `status` enum('open','closed') DEFAULT 'open',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cashier_shifts`
--

INSERT INTO `cashier_shifts` (`id`, `user_id`, `start_time`, `end_time`, `total_sales`, `status`, `created_at`) VALUES
(1, 1, '2026-02-17 18:28:22', '2026-02-17 20:38:38', 230.00, 'closed', '2026-02-17 10:28:22'),
(2, 12, '2026-02-16 18:47:49', '2026-02-16 20:40:43', 100.00, 'closed', '2026-02-17 10:47:49'),
(3, 12, '2026-02-17 20:58:26', '2026-02-17 21:04:24', 180.00, 'closed', '2026-02-17 12:58:26'),
(5, 1, '2026-02-24 21:39:15', NULL, 0.00, 'open', '2026-02-24 13:39:15'),
(6, 12, '2026-02-25 22:20:07', NULL, 0.00, 'open', '2026-02-25 14:20:07'),
(7, 14, '2026-02-25 22:23:15', '2026-02-26 14:13:22', 90.00, 'closed', '2026-02-25 14:23:15'),
(8, 14, '2026-02-26 14:14:38', NULL, 0.00, 'open', '2026-02-26 06:14:38');

-- --------------------------------------------------------

--
-- Table structure for table `courts`
--

CREATE TABLE `courts` (
  `id` int(11) NOT NULL,
  `court_type` varchar(50) NOT NULL,
  `capacity` varchar(20) NOT NULL,
  `amount` int(11) NOT NULL,
  `is_deleted` int(11) NOT NULL,
  `deleted_by` int(11) NOT NULL,
  `created_at` varchar(255) DEFAULT NULL,
  `synced` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `courts`
--

INSERT INTO `courts` (`id`, `court_type`, `capacity`, `amount`, `is_deleted`, `deleted_by`, `created_at`, `synced`) VALUES
(1, 'Pickleball Court', '30', 350, 0, 0, '2026-01-11 12:23:11', 1),
(2, 'Table Tennis Court', '20', 300, 0, 0, '2026-01-11 12:23:11', 1),
(3, 'Badminton Court', '10', 400, 0, 0, '2026-01-11 12:23:11', 1);

-- --------------------------------------------------------

--
-- Table structure for table `email_verifications`
--

CREATE TABLE `email_verifications` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `code` varchar(10) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `synced` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `members`
--

CREATE TABLE `members` (
  `id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `address` varchar(100) NOT NULL,
  `birth_date` date NOT NULL,
  `contact_number` varchar(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `membership_id` varchar(50) NOT NULL,
  `card_number` varchar(10) NOT NULL,
  `pin` varchar(100) NOT NULL,
  `wallet` varchar(11) NOT NULL,
  `joined_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `synced` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `members`
--

INSERT INTO `members` (`id`, `first_name`, `last_name`, `address`, `birth_date`, `contact_number`, `email`, `membership_id`, `card_number`, `pin`, `wallet`, `joined_at`, `synced`) VALUES
(6, 'Elizabeth', 'Rebonza', 'Barangay Bakid-Bakid, Gingoog City', '2005-11-22', '09092853634', 'rebonzaelizabeth@gmail.com', 'CBMS-2026-0001', '123456789', '$2y$10$sjarMnLwYW7UnvXlJQ5XiOJ1sUiZPwdNnADjE1CPoNgQO8/kfNf/e', '550', '2026-02-26 14:45:29', 1);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `product_category` varchar(50) NOT NULL,
  `product_number` varchar(50) NOT NULL,
  `product_name` varchar(50) NOT NULL,
  `price` varchar(20) NOT NULL,
  `qty` int(11) NOT NULL,
  `product_image` varchar(100) NOT NULL,
  `status` varchar(50) NOT NULL,
  `is_deleted` int(11) NOT NULL,
  `deleted_by` int(11) NOT NULL,
  `created_at` varchar(255) DEFAULT current_timestamp(),
  `synced` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `product_category`, `product_number`, `product_name`, `price`, `qty`, `product_image`, `status`, `is_deleted`, `deleted_by`, `created_at`, `synced`) VALUES
(1, 'Foods', 'TEST-FOODS-0001', 'Burgers', '100', 5, '1771747385_burger.jpg', '', 0, 0, '2026-01-11 14:07:53', 1),
(2, 'Merch', 'TEST-MERCH-0001', 'Plain Shirt', '1500', 4, '1771748826_plain_shirt.jpg', '', 0, 0, '2026-01-14 03:06:25', 1),
(3, 'Foods', 'LS20250003', 'Fries', '80', 40, '1771747476_fires.jpg', '', 0, 0, '2026-01-14 03:08:03', 1),
(7, 'Merch', '34543', 'Baseball Cap', '90', 9, '1771748769_cap.jpg', '', 0, 0, '2026-01-21 07:06:16', 1),
(9, 'Foods', 'TEST-FOOD-123', 'Pizza', '180', 19, '1771747606_pizza.jpg', '', 0, 0, '2026-02-22 16:06:46', 1);

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `id` int(11) NOT NULL,
  `transaction_no` varchar(50) DEFAULT NULL,
  `sub_total` decimal(10,2) DEFAULT NULL,
  `discount` decimal(10,2) DEFAULT 0.00,
  `membership_card` varchar(20) NOT NULL,
  `final_total` decimal(10,2) DEFAULT NULL,
  `cash_amount` varchar(255) DEFAULT NULL,
  `cash_change` varchar(255) DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `is_deleted` varchar(255) DEFAULT '0',
  `user_id` int(11) DEFAULT NULL,
  `synced` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sales`
--

INSERT INTO `sales` (`id`, `transaction_no`, `sub_total`, `discount`, `membership_card`, `final_total`, `cash_amount`, `cash_change`, `payment_method`, `created_at`, `is_deleted`, `user_id`, `synced`) VALUES
(30, 'TXN-6991280426C97', 100.00, 0.00, 'N/A', 100.00, '200.00', '100.00', 'Cash', '2026-02-15 10:19:04', '0', 1, 1),
(31, 'TXN-69912D5CC5465', 100.00, 0.00, 'N/A', 100.00, '100.00', '0.00', 'Cash', '2026-02-15 10:20:26', '0', 12, 1),
(32, 'TXN-69944755523C5', 100.00, 0.00, 'N/A', 100.00, '200.00', '100.00', 'Cash', '2026-02-16 18:48:15', '0', 12, 1),
(33, 'TXN-699454E9D6BCF', 80.00, 0.00, 'N/A', 80.00, '100.00', '20.00', 'Cash', '2026-02-17 19:46:19', '0', 1, 1),
(34, 'TXN-6994610326902', 150.00, 0.00, 'N/A', 150.00, '200.00', '50.00', 'Cash', '2026-02-17 20:37:39', '0', 1, 1),
(35, 'TXN-699466FC624EA', 180.00, 0.00, 'N/A', 180.00, '200.00', '20.00', 'Cash', '2026-02-17 21:03:09', '0', 12, 1),
(36, 'TXN-699EDB090FAEA', 180.00, 0.00, 'N/A', 180.00, '200.00', '20.00', 'Cash', '2026-02-25 19:20:57', '0', 1, 1),
(37, 'TXN-699F0B595938A', 90.00, 0.00, '123456789', 90.00, '0.00', '0.00', 'Card', '2026-02-25 22:50:05', '0', 14, 1),
(38, 'TXN-69A043A11EC9C', 360.00, 0.00, '123456789', 360.00, '0.00', '0.00', 'Card', '2026-02-26 20:59:38', '0', 14, 1);

-- --------------------------------------------------------

--
-- Table structure for table `sales_items`
--

CREATE TABLE `sales_items` (
  `id` int(11) NOT NULL,
  `sale_id` int(11) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `qty` int(11) NOT NULL DEFAULT 1,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` datetime DEFAULT current_timestamp(),
  `synced` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sales_items`
--

INSERT INTO `sales_items` (`id`, `sale_id`, `item_name`, `qty`, `price`, `total`, `created_at`, `synced`) VALUES
(2, 30, 'Burgers', 1, 100.00, 100.00, '2026-02-15 10:19:04', 1),
(3, 31, 'Burgers', 1, 100.00, 100.00, '2026-02-15 10:20:26', 1),
(4, 32, 'Burgers', 1, 100.00, 100.00, '2026-02-16 18:48:15', 1),
(5, 33, 'Fries', 1, 80.00, 80.00, '2026-02-17 19:46:19', 1),
(6, 34, 'Plain Shirt', 1, 150.00, 150.00, '2026-02-17 20:37:39', 1),
(7, 35, 'Burgers', 1, 100.00, 100.00, '2026-02-17 21:03:09', 1),
(8, 35, 'Fries', 1, 80.00, 80.00, '2026-02-17 21:03:09', 1),
(9, 36, 'Burgers', 1, 100.00, 100.00, '2026-02-25 19:20:57', 1),
(10, 36, 'Fries', 1, 80.00, 80.00, '2026-02-25 19:20:57', 1),
(11, 37, 'Baseball Cap', 1, 90.00, 90.00, '2026-02-25 22:50:05', 1),
(12, 38, 'Burgers', 1, 100.00, 100.00, '2026-02-26 20:59:38', 1),
(13, 38, 'Fries', 1, 80.00, 80.00, '2026-02-26 20:59:38', 1),
(14, 38, 'Pizza', 1, 180.00, 180.00, '2026-02-26 20:59:38', 1);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `user_type` tinyint(4) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` text NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `id_number` varchar(20) NOT NULL,
  `is_deleted` int(11) NOT NULL,
  `deleted_by` int(11) NOT NULL,
  `synced` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `user_type`, `username`, `password`, `first_name`, `last_name`, `id_number`, `is_deleted`, `deleted_by`, `synced`) VALUES
(1, 1, 'super', '$2y$10$V//UOLLGRb22sd241tgMcu7/U4GvoMy6kRMTCESmMAEWwK/mWDX/q', 'super', 'admin', 'admin1', 0, 0, 1),
(12, 2, 'test111', '$2y$10$eNQEI9GXHrDIC8GSNm2IpuIyu9mDbEdF56Pchy9n8HvbbYWRrLEYe', 'test', 'test', '111', 0, 0, 1),
(14, 2, 'test222', '$2y$10$6UsHEL/GQtpuJxxDNXNgO.3Ra3OAZmO7KDuCF1R48MO1O6xdVRE8W', 'test2', 'test', '222', 0, 0, 1),
(21, 2, 'test', '$2y$10$vc0Ir9e.kUDXQTIL8YX5JejupXTnkClAVFPNGUbf2jjIpwSRvzZ/u', 'test', 'test', '1112', 0, 0, 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `booking`
--
ALTER TABLE `booking`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cashier_shifts`
--
ALTER TABLE `cashier_shifts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `courts`
--
ALTER TABLE `courts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `email_verifications`
--
ALTER TABLE `email_verifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `members`
--
ALTER TABLE `members`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sales_items`
--
ALTER TABLE `sales_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sale_id` (`sale_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `booking`
--
ALTER TABLE `booking`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `cashier_shifts`
--
ALTER TABLE `cashier_shifts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `courts`
--
ALTER TABLE `courts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `email_verifications`
--
ALTER TABLE `email_verifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT for table `members`
--
ALTER TABLE `members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `sales_items`
--
ALTER TABLE `sales_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `sales_items`
--
ALTER TABLE `sales_items`
  ADD CONSTRAINT `sales_items_ibfk_1` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
