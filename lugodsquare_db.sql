-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 05, 2025 at 10:31 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `lugodsquare_db`
--

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
  `booked_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `booking`
--

INSERT INTO `booking` (`id`, `membership_id`, `first_name`, `last_name`, `contact_number`, `email`, `court_type`, `date`, `start_time`, `end_time`, `total_amount`, `booked_at`, `status`) VALUES
(1, 'CBMS-2025-0001', 'Elizabeth', 'Rebonza', '09092853634', 'rebonzaelizabeth@gmail.com', 1, '2025-11-02', '07:00', '08:00', '0.00', '2025-10-30 23:51:20', 0),
(2, 'CBMS-2025-0001', 'Elizabeth', 'Rebonza', '09092853634', 'rebonzaelizabeth@gmail.com', 1, '2025-11-02', '08:00', '09:00', '0.00', '2025-10-30 23:52:06', 0),
(3, 'CBMS-2025-0001', 'Elizabeth', 'Rebonza', '09092853634', 'rebonzaelizabeth@gmail.com', 1, '2025-11-02', '09:00', '12:00', '0.00', '2025-10-30 23:56:56', 0),
(4, 'CBMS-2025-0001', 'Elizabeth', 'Rebonza', '09092853634', 'rebonzaelizabeth@gmail.com', 1, '2025-11-02', '12:00', '17:00', '535.00', '2025-10-30 23:57:33', 0),
(5, 'CBMS-2025-0001', 'Elizabeth', 'Rebonza', '09092853634', 'rebonzaelizabeth@gmail.com', 1, '2025-11-01', '07:00', '08:00', '0.00', '2025-10-31 00:35:50', 0),
(6, 'CBMS-2025-0001', 'Elizabeth', 'Rebonza', '09092853634', 'rebonzaelizabeth@gmail.com', 1, '2025-11-01', '08:00', '09:00', '0.00', '2025-10-31 01:58:49', 0),
(7, 'CBMS-2025-0001', 'Elizabeth', 'Rebonza', '09092853634', 'rebonzaelizabeth@gmail.com', 1, '2025-11-01', '09:00', '11:00', '220.00', '2025-10-31 02:04:09', 0),
(8, 'CBMS-2025-0001', 'Elizabeth', 'Rebonza', '09092853634', 'rebonzaelizabeth@gmail.com', 1, '2025-11-03', '07:00', '09:00', '630.00', '2025-10-31 02:05:20', 0),
(9, 'CBMS-2025-0001', 'Elizabeth', 'Rebonza', '09092853634', 'rebonzaelizabeth@gmail.com', 1, '2025-11-03', '09:00', '10:00', '0.00', '2025-10-31 02:10:30', 0);

-- --------------------------------------------------------

--
-- Table structure for table `courts_tbl`
--

CREATE TABLE `courts_tbl` (
  `id` int(11) NOT NULL,
  `court_type` varchar(50) NOT NULL,
  `capacity` varchar(20) NOT NULL,
  `amount` int(11) NOT NULL,
  `is_deleted` int(11) NOT NULL,
  `deleted_by` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `courts_tbl`
--

INSERT INTO `courts_tbl` (`id`, `court_type`, `capacity`, `amount`, `is_deleted`, `deleted_by`) VALUES
(1, 'Basketball Court', '30', 350, 0, 0),
(2, 'Tennis Court', '20', 300, 0, 0),
(3, 'Badminton Court', '10', 400, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `email_verifications`
--

CREATE TABLE `email_verifications` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `code` varchar(10) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `email_verifications`
--

INSERT INTO `email_verifications` (`id`, `email`, `code`, `created_at`) VALUES
(51, 'rebonzaelizabeth@gmail.com', '714208', '2025-10-30 12:14:27'),
(53, 'admin@ckcgingoog.edu.ph', '975877', '2025-10-30 13:00:29');

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
  `joined_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `members`
--

INSERT INTO `members` (`id`, `first_name`, `last_name`, `address`, `birth_date`, `contact_number`, `email`, `membership_id`, `card_number`, `pin`, `wallet`, `joined_at`) VALUES
(1, 'Elizabeth', 'Rebonza', 'Japan', '2005-11-22', '09092853634', 'rebonzaelizabeth@gmail.com', 'CBMS-2025-0001', '0085224513', '$2y$10$LfjNwyfHqinImqkcHoUSB.7PzSTQxsxayF7LLUQvCi2HeXm6yUmKS', '5198', '2025-11-05 07:33:34');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `product_number` varchar(50) NOT NULL,
  `product_name` varchar(50) NOT NULL,
  `price` varchar(20) NOT NULL,
  `qty` int(11) NOT NULL,
  `status` varchar(50) NOT NULL,
  `is_deleted` int(11) NOT NULL,
  `deleted_by` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `product_number`, `product_name`, `price`, `qty`, `status`, `is_deleted`, `deleted_by`) VALUES
(1, 'LS20250001', 'Cheese Burger', '130', 45, '', 0, 0),
(2, 'LS20250002', 'Pepperoni Pizza', '350', 49, '', 0, 0),
(3, 'LS20250003', 'Spaghetti Bolognese', '180', 44, '', 0, 0),
(4, 'LS20250004', 'Iced Coffee', '90', 47, '', 0, 0),
(5, 'LS20250005', 'Chicken Sandwich', '150', 49, '', 0, 0),
(6, 'LS20250006', 'French Fries', '70', 49, '', 0, 0);

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
  `payment_method` varchar(50) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sales`
--

INSERT INTO `sales` (`id`, `transaction_no`, `sub_total`, `discount`, `membership_card`, `final_total`, `payment_method`, `created_at`, `user_id`) VALUES
(3, 'TXN-6909A5B335A1A', 1080.00, 108.00, '0085224513', 972.00, 'Card', '2025-11-04 15:06:02', 1),
(8, 'TXN-690ADC089DC30', 650.00, 65.00, '0085224513', 585.00, 'Card', '2025-11-05 13:10:30', 1),
(9, 'TXN-690AE6053B503', 90.00, 0.00, '0085224513', 90.00, 'Card', '2025-11-05 13:52:30', 3),
(10, 'TXN-690AE71328A12', 440.00, 44.00, '0085224513', 396.00, 'Card', '2025-11-05 13:58:18', 3),
(22, 'TXN-690AFD9402743', 350.00, 35.00, '0085224513', 315.00, 'Card', '2025-11-05 15:33:36', 1),
(23, 'TXN-690AFE1F5D1BD', 90.00, 0.00, 'non-member', 90.00, 'Cash', '2025-11-05 15:35:04', 1);

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
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sales_items`
--

INSERT INTO `sales_items` (`id`, `sale_id`, `item_name`, `qty`, `price`, `total`, `created_at`) VALUES
(3, 3, 'Spaghetti Bolognese', 6, 180.00, 1080.00, '2025-11-04 15:06:02'),
(12, 8, 'Cheese Burger', 5, 130.00, 650.00, '2025-11-05 13:10:30'),
(13, 9, 'Iced Coffee', 1, 90.00, 90.00, '2025-11-05 13:52:30'),
(14, 10, 'Iced Coffee', 1, 90.00, 90.00, '2025-11-05 13:58:18'),
(15, 10, 'French Fries', 1, 70.00, 70.00, '2025-11-05 13:58:18'),
(16, 10, 'Chicken Sandwich', 1, 150.00, 150.00, '2025-11-05 13:58:18'),
(17, 10, 'Cheese Burger', 1, 130.00, 130.00, '2025-11-05 13:58:18'),
(32, 22, 'Pepperoni Pizza', 1, 350.00, 350.00, '2025-11-05 15:33:36'),
(33, 23, 'Iced Coffee', 1, 90.00, 90.00, '2025-11-05 15:35:04');

-- --------------------------------------------------------

--
-- Table structure for table `users_tbl`
--

CREATE TABLE `users_tbl` (
  `id` int(11) NOT NULL,
  `user_type` tinyint(4) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` text NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `is_deleted` int(11) NOT NULL,
  `deleted_by` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users_tbl`
--

INSERT INTO `users_tbl` (`id`, `user_type`, `username`, `password`, `first_name`, `last_name`, `is_deleted`, `deleted_by`) VALUES
(1, 1, 'super', '$2y$10$V//UOLLGRb22sd241tgMcu7/U4GvoMy6kRMTCESmMAEWwK/mWDX/q', 'Super', 'Admin', 0, 0),
(3, 2, 'user', '$2y$10$NQ30AxzPVxkddDYeGEE3buRn6Mqsz3UrlJeYT0dgkAez/k.BKJNdm', 'Elizabeth', 'Rebonza', 0, 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `booking`
--
ALTER TABLE `booking`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `courts_tbl`
--
ALTER TABLE `courts_tbl`
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
-- Indexes for table `users_tbl`
--
ALTER TABLE `users_tbl`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `booking`
--
ALTER TABLE `booking`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `courts_tbl`
--
ALTER TABLE `courts_tbl`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `email_verifications`
--
ALTER TABLE `email_verifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT for table `members`
--
ALTER TABLE `members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `sales_items`
--
ALTER TABLE `sales_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `users_tbl`
--
ALTER TABLE `users_tbl`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

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
