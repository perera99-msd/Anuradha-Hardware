-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 20, 2025 at 05:29 PM
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
-- Database: `anuradha_hardware`
--

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cart_items`
--

CREATE TABLE `cart_items` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `session_id` varchar(255) DEFAULT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart_items`
--

INSERT INTO `cart_items` (`id`, `user_id`, `session_id`, `product_id`, `quantity`, `created_at`, `updated_at`) VALUES
(14, 6, NULL, 39, 1, '2025-08-28 08:19:56', '2025-08-28 08:19:56');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `created_at`) VALUES
(1, 'Power Tools', 'Electric and battery-powered tools for construction and DIY projects', '2025-08-21 22:40:18'),
(2, 'Hand Tools', 'Manual tools for various construction and repair tasks', '2025-08-21 22:40:18'),
(3, 'Building Materials', 'Essential materials for construction projects', '2025-08-21 22:40:18'),
(4, 'Plumbing Supplies', 'Pipes, fittings, and accessories for plumbing systems', '2025-08-21 22:40:18'),
(5, 'Electrical Supplies', 'Wires, switches, and electrical components', '2025-08-21 22:40:18'),
(6, 'Paint & Decorating', 'Paints, brushes, and decorating supplies', '2025-08-21 22:40:18'),
(7, 'Safety Equipment', 'Protective gear and safety equipment', '2025-08-21 22:40:18'),
(8, 'Hardware & Fasteners', 'Nuts, bolts, screws, and other fasteners', '2025-08-21 22:40:18');

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `name`, `email`, `phone`, `address`, `created_at`) VALUES
(1, 'Anura Perera', 'anura@mail.com', '0771234567', 'Colombo', '2025-07-23 17:28:03'),
(2, 'Saman Silva', 'saman@mail.com', '0772345678', 'Kandy', '2025-07-23 17:28:03'),
(3, 'Nimal Fernando', 'nimal@mail.com', '0773456789', 'Galle', '2025-07-23 17:28:03'),
(4, 'Sunil Rajapakse', 'sunil@mail.com', '0774567890', 'Kurunegala', '2025-07-23 17:28:03'),
(5, 'Kamal Gunasekara', 'kamal@mail.com', '0775678901', 'Negombo', '2025-07-23 17:28:03'),
(6, 'Nirosha De Silva', 'nirosha@mail.com', '0776789012', 'Matara', '2025-07-23 17:28:03'),
(7, 'Dilani Jayasinghe', 'dilani@mail.com', '0777890123', 'Anuradhapura', '2025-07-23 17:28:03'),
(8, 'Tharindu Mendis', 'tharindu@mail.com', '0778901234', 'Rathnapura', '2025-07-23 17:28:03'),
(9, 'Sanduni Herath', 'sanduni@mail.com', '0779012345', 'Nuwara Eliya', '2025-07-23 17:28:03'),
(10, 'Hasitha Karunaratne', 'hasitha@mail.com', '0770123456', 'Polonnaruwa', '2025-07-23 17:28:03'),
(11, 'Chamara Wickramasinghe', 'chamara@mail.com', '0771111111', 'Badulla', '2025-07-23 17:28:03'),
(12, 'Ruwan Pathirana', 'ruwan@mail.com', '0772222222', 'Hambantota', '2025-07-23 17:28:03'),
(13, 'Madhavi Seneviratne', 'madhavi@mail.com', '0773333333', 'Trincomalee', '2025-07-23 17:28:03'),
(14, 'Kasun Jayalath', 'kasun@mail.com', '0774444444', 'Ampara', '2025-07-23 17:28:03'),
(15, 'Isuru Bandara', 'isuru@mail.com', '0775555555', 'Jaffna', '2025-07-23 17:28:03');

-- --------------------------------------------------------

--
-- Table structure for table `home_page_content`
--

CREATE TABLE `home_page_content` (
  `id` int(11) NOT NULL,
  `section` varchar(50) NOT NULL,
  `content_key` varchar(50) NOT NULL,
  `content_value` text DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `order_num` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `home_page_content`
--

INSERT INTO `home_page_content` (`id`, `section`, `content_key`, `content_value`, `image_path`, `order_num`, `is_active`, `created_at`, `updated_at`) VALUES
(4, 'slider', 'slide_1', '{\"title\": \"Quality Hardware Solutions\", \"description\": \"Everything you need for your construction and home improvement projects\", \"button_text\": \"Shop Now\", \"button_link\": \"products.php\"}', 'images/slider/1.jpeg', 1, 1, '2025-08-14 16:13:20', '2025-08-14 16:17:10'),
(5, 'slider', 'slide_2', '{\"title\": \"Seasonal Special Offers\", \"description\": \"Up to 30% off on selected items this month\", \"button_text\": \"View Offers\", \"button_link\": \"offers.html\"}', 'images/slider/2.jpeg', 2, 1, '2025-08-14 16:13:20', '2025-08-14 16:15:12'),
(6, 'slider', 'slide_3', '{\"title\": \"Professional Grade Tools\", \"description\": \"For contractors and serious DIY enthusiasts\", \"button_text\": \"Browse Tools\", \"button_link\": \"products.php?category=power-tools\"}', 'images/slider/3.jpeg', 3, 1, '2025-08-14 16:13:20', '2025-08-14 16:15:12'),
(8, 'offers', 'power_tools_offer', '{\"title\": \"Power Tools Sale\", \"description\": \"Get premium power tools at discounted prices. Limited stock available.\", \"discount\": \"25% OFF\", \"current_price\": \"From Rs. 8,500.00\", \"original_price\": \"Rs. 11,300.00\", \"button_text\": \"Shop Now\", \"button_link\": \"products.php?category=power-tools\"}', 'images/offers/1.jpeg', 1, 1, '2025-08-20 13:03:05', '2025-08-20 13:03:05'),
(9, 'offers', 'paint_offer', '{\"title\": \"Paint Special\", \"description\": \"Premium quality paints with extended warranty. All colors available.\", \"discount\": \"15% OFF\", \"current_price\": \"From Rs. 3,570.00\", \"original_price\": \"Rs. 4,200.00\", \"button_text\": \"Shop Now\", \"button_link\": \"products.php?category=paints\"}', 'images/offers/2.jpeg', 2, 1, '2025-08-20 13:03:05', '2025-08-20 13:03:05'),
(10, 'offers', 'plumbing_offer', '{\"title\": \"Plumbing Essentials\", \"description\": \"Buy selected pipes and get fittings free. Perfect for your renovation projects.\", \"discount\": \"BUY 1 GET 1\", \"current_price\": \"Special Package Deals\", \"original_price\": \"\", \"button_text\": \"View Details\", \"button_link\": \"products.php?category=plumbing\"}', 'images/offers/3.jpeg', 3, 1, '2025-08-20 13:03:05', '2025-08-20 13:03:05');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `status` enum('Pending','Processing','Completed','Cancelled') DEFAULT 'Pending',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `customer_id`, `total_amount`, `status`, `created_at`) VALUES
(1, 1, 1500.00, 'Completed', '2025-01-15 00:00:00'),
(2, 2, 1800.00, 'Completed', '2025-01-20 00:00:00'),
(3, 3, 2000.00, 'Completed', '2025-02-10 00:00:00'),
(4, 4, 1200.00, 'Pending', '2025-02-18 00:00:00'),
(5, 5, 2500.00, 'Pending', '2025-03-05 00:00:00'),
(6, 6, 1750.00, 'Completed', '2025-03-22 00:00:00'),
(7, 7, 3000.00, 'Processing', '2025-04-07 00:00:00'),
(8, 8, 2200.00, 'Completed', '2025-04-18 00:00:00'),
(9, 9, 2700.00, 'Completed', '2025-05-02 00:00:00'),
(10, 10, 2100.00, 'Completed', '2025-05-19 00:00:00'),
(11, 11, 2600.00, 'Completed', '2025-06-08 00:00:00'),
(12, 12, 3100.00, 'Completed', '2025-06-24 00:00:00'),
(13, 13, 1900.00, 'Completed', '2025-07-03 00:00:00'),
(14, 14, 2300.00, 'Processing', '2025-07-14 00:00:00'),
(15, 15, 2500.00, 'Completed', '2025-07-21 00:00:00'),
(16, 1, 1800.00, 'Completed', '2025-07-23 21:08:34'),
(17, 5, 505.60, 'Pending', '2025-08-28 23:10:03');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price`) VALUES
(1, 1, NULL, 2, 750.00),
(2, 2, NULL, 3, 600.00),
(3, 3, NULL, 2, 1000.00),
(4, 4, NULL, 4, 300.00),
(5, 5, NULL, 5, 500.00),
(6, 6, NULL, 2, 875.00),
(7, 7, NULL, 6, 500.00),
(8, 8, NULL, 4, 550.00),
(9, 9, NULL, 3, 900.00),
(10, 10, NULL, 6, 350.00),
(11, 11, NULL, 4, 90.00),
(12, 12, NULL, 2, 650.00),
(13, 13, NULL, 3, 480.00),
(14, 14, NULL, 1, 7500.00),
(15, 15, NULL, 2, 4200.00),
(16, 17, 38, 1, 5.00);

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(100) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`email`, `token`, `created_at`) VALUES
('msdperera99@gmail.com', '1c2f63bbf2714333412a0ea9aebf1bf2bb0cdcea94ad5ecd41ae11146464d234', '2025-07-31 20:35:34');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `stock` int(11) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `is_featured` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `category_id`, `description`, `price`, `stock`, `image`, `is_featured`) VALUES
(36, 'DDA', 2, 'jj', 100.00, 11, 'uploads/1756136689_The Basic Set of Tools Every Woman Should Own—and….jpeg', 1),
(37, 'Bolts & Nuts Set', 4, 'l', 2.00, 1, 'uploads/1756136707_12.jpeg', 0),
(38, 'Dimalsha', 2, ';', 5.00, 4, 'uploads/1756136840_SKIL PWR CORE 20 Brushless 20V 1_2 Inch Drill….jpeg', 1),
(39, 'll', 1, 'l', 8.00, 8, 'uploads/1756136878_1756136707_12.jpeg', 1),
(40, 'Sanchana', 5, 'lll', 45.00, 5, 'uploads/1756137357_THE CHALLENGE_After replacing one of the leading….jpeg', 1);

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `session_id` varchar(128) NOT NULL,
  `user_id` int(11) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` text NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `testimonials`
--

CREATE TABLE `testimonials` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `role` varchar(100) NOT NULL,
  `comment` text NOT NULL,
  `rating` int(11) NOT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `order_num` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `account_type` enum('individual','business') NOT NULL,
  `business_name` varchar(100) DEFAULT NULL,
  `vat_number` varchar(50) DEFAULT NULL,
  `address` text NOT NULL,
  `city` varchar(50) NOT NULL,
  `postal_code` varchar(20) NOT NULL,
  `country` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `password`, `phone`, `account_type`, `business_name`, `vat_number`, `address`, `city`, `postal_code`, `country`, `created_at`, `updated_at`) VALUES
(1, 'Dimalsha', 'Perera', 'dimalsha@gmail.com', '$2y$10$DoZnXV37juhFMnfF2AAx5e8JYvi/HwKeFlepO084KZ/ykgP/rgmba', '0766055480', 'individual', '', '', '151 KEPUNGODA PAMUNUGAMA', 'Negombo', '11370', 'Sri Lanka', '2025-07-31 22:14:19', '2025-07-31 22:42:52'),
(2, 'Dunil', 'Gunathilake', 'dunilg@gmail.com', '$2y$10$7/tM79TKzZUqC6FAvDy0wOcGm8fsGLEWtUxZJ23l4P6vKIiZxDAPS', '0123456789', 'individual', '', '', '120 Kaluthara', 'Kaluthara', '13524', 'Sri Lanka', '2025-07-31 23:02:04', '2025-07-31 23:02:04'),
(3, 'Sanchana', 'Perera', 'msdperera99@gmail.com', '$2y$10$s0mrVK4O/ZwzURU9zYMKeu9F3E9AmWjNXyC2.xmet3OQZWFefagFi', '0766055480', 'individual', '', '', '151 KEPUNGODA PAMUNUGAMA', 'Negombo', '11370', 'Sri Lanka', '2025-07-31 23:05:07', '2025-07-31 23:05:07'),
(4, 'Pramod', 'Buddkhika', 'lakshan@gmail.com', '$2y$10$B2zhPvZ6LYwZWm29YLAkFOGwMw.XXMMiPBx5Yln6uBKZzPxbo6Dri', '0766055480', 'individual', '', '', '161 / Walasmulla ,Mathara', 'Mathara', '52460', 'Sri Lanka', '2025-08-01 04:34:06', '2025-08-01 04:34:06'),
(5, 'Sanchana', 'Perera', 'anura@mail.com', '$2y$10$5wdN.8EI/dJoYNFk/9abVe2MvdlcGWmoyuBvJTF.1VHutpRJtOqVy', '+07 660 5548', 'individual', '', '', '151 KEPUNGODA PAMUNUGAMA', 'Negombo', '11370', 'Sri Lanka', '2025-08-25 17:08:47', '2025-08-25 17:08:47'),
(6, 'dd', 'dd', 'cimola1158@sgatra.com', '$2y$10$1AN5KjQikYZnxhKVkcOCi.qrhADT4ik2WrvstPzIT0S.jpxZKKzpW', '+07 660 5548', 'individual', '', '', 'dd', 'ssaasd', 'd11', 'Sri Lanka', '2025-08-28 05:41:01', '2025-08-28 05:41:01');

-- --------------------------------------------------------

--
-- Table structure for table `wishlist_items`
--

CREATE TABLE `wishlist_items` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `session_id` varchar(255) DEFAULT NULL,
  `product_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_product` (`user_id`,`product_id`);

--
-- Indexes for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `session_id` (`session_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `home_page_content`
--
ALTER TABLE `home_page_content`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `section_key` (`section`,`content_key`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`session_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `testimonials`
--
ALTER TABLE `testimonials`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `wishlist_items`
--
ALTER TABLE `wishlist_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `session_id` (`session_id`),
  ADD KEY `product_id` (`product_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `home_page_content`
--
ALTER TABLE `home_page_content`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `testimonials`
--
ALTER TABLE `testimonials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `wishlist_items`
--
ALTER TABLE `wishlist_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `cart_items_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `sessions`
--
ALTER TABLE `sessions`
  ADD CONSTRAINT `sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `wishlist_items`
--
ALTER TABLE `wishlist_items`
  ADD CONSTRAINT `wishlist_items_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `wishlist_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
