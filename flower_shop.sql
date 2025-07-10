-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307
-- Generation Time: Jul 08, 2025 at 03:45 PM
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
-- Database: `flower_shop`
--

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cart_item`
--

CREATE TABLE `cart_item` (
  `id` int(11) NOT NULL,
  `cart_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `address` text NOT NULL,
  `phone` varchar(20) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `payment_proof` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `payment_method` varchar(50) DEFAULT 'GCash',
  `payment_status` varchar(50) DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `customer_name`, `address`, `phone`, `total`, `payment_proof`, `created_at`, `payment_method`, `payment_status`) VALUES
(1, 'fredelyn joy ouano', 'adada', '09923380079', 140.00, NULL, '2025-07-06 07:31:39', 'GCash', 'Pending'),
(2, 'Rica mae Java', 'uffehf', '09499055251', 199.00, NULL, '2025-07-06 07:31:48', 'GCash', 'Pending'),
(3, 'Rica Mae Java', 'dggfdsg', '09923380079', 140.00, NULL, '2025-07-06 08:01:37', 'GCash', 'Pending'),
(4, 'Rica mae Java', 'efeffesfd', '09499055251', 140.00, NULL, '2025-07-06 12:37:28', 'GCash', 'Pending'),
(5, 'Rica mae Java', 'Saint Jude St., Hipodromo , Cebu City , Cebu 6000', '09499055251', 140.00, NULL, '2025-07-06 12:46:58', 'GCash', 'Pending'),
(6, 'Rica mae Java', 'Saint Jude St., Hipodromo , Cebu City , Cebu, 7', '09499055251', 140.00, NULL, '2025-07-06 13:22:44', '0', 'Pending'),
(7, 'Rica mae Java', 'saint jude st', '09923380079', 140.00, 'uploads/686a7cb96d989_20f3e1a0-4f5b-4329-890f-3bce86146bd1.jpg', '2025-07-06 13:40:08', 'GCash', 'Pending'),
(8, 'Rica mae Java', 'ddsds', '09499055251', 140.00, NULL, '2025-07-06 13:49:02', 'GCash', 'Pending'),
(9, 'Rica mae Java', 'saint jude st', '09923380079', 140.00, 'uploads/686a7fd80f024_20f3e1a0-4f5b-4329-890f-3bce86146bd1.jpg', '2025-07-06 13:53:27', 'GCash', 'Pending'),
(10, 'Rica mae Java', 'saint jude st, Hipodromo , Cebu City, Cebu, Region XII', '09499055251', 140.00, NULL, '2025-07-06 14:12:00', '0', 'Pending'),
(11, 'Rica mae Java', 'saint jude st, Hipodromo , Davao City, Davao del Sur, Region XII', '09499055251', 737.00, NULL, '2025-07-06 14:17:27', '0', 'Pending'),
(12, 'Rica mae Java', 'saint jude st, Hipodromo , Cebu City, Cebu, Region VII', '09499055251', 597.00, NULL, '2025-07-06 14:19:16', '0', 'Pending'),
(13, 'Rica mae Java', 'saint jude st, Hipodromo , Cebu City, Cebu, Region VII', '09499055251', 199.00, NULL, '2025-07-08 13:43:45', '0', 'Pending');

-- --------------------------------------------------------

--
-- Table structure for table `order_item`
--

CREATE TABLE `order_item` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_item`
--

INSERT INTO `order_item` (`id`, `order_id`, `product_id`, `quantity`, `price`) VALUES
(1, 1, 1, 1, 140.00),
(2, 2, 2, 1, 199.00),
(3, 3, 1, 1, 140.00),
(4, 4, 1, 1, 140.00),
(5, 5, 1, 1, 140.00),
(6, 6, 1, 1, 140.00),
(7, 7, 1, 1, 140.00),
(8, 8, 1, 1, 140.00),
(9, 9, 1, 1, 140.00),
(10, 10, 1, 1, 140.00),
(11, 11, 1, 1, 140.00),
(12, 11, 2, 3, 199.00),
(13, 12, 2, 3, 199.00),
(14, 13, 2, 1, 199.00);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `stock` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `category`, `price`, `image`, `stock`) VALUES
(1, 'Fancy Mini Flower', 'mini', 140.00, 'images/flower1.jpg', 0),
(2, 'Giant Flower', 'giant', 199.00, 'images/flower2.png', 2),
(3, 'Flower Bouquet', 'bouquet', 200.00, 'images/flower3.jpg', 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cart_item`
--
ALTER TABLE `cart_item`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cart_id` (`cart_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `order_item`
--
ALTER TABLE `order_item`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cart_item`
--
ALTER TABLE `cart_item`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `order_item`
--
ALTER TABLE `order_item`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart_item`
--
ALTER TABLE `cart_item`
  ADD CONSTRAINT `cart_item_ibfk_1` FOREIGN KEY (`cart_id`) REFERENCES `cart` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_item_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_item`
--
ALTER TABLE `order_item`
  ADD CONSTRAINT `order_item_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_item_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
