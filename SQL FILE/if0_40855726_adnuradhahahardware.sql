-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: sql100.infinityfree.com
-- Generation Time: Jan 08, 2026 at 05:58 AM
-- Server version: 10.6.22-MariaDB
-- PHP Version: 7.2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `if0_40855726_adnuradhahahardware`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`, `created_at`) VALUES
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2025-09-21 10:00:00');

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
(17, 5, '505.60', 'Pending', '2025-08-28 23:10:03');

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
(16, 17, 38, 1, '5.00');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(100) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(36, 'DDA', 2, 'jj', '100.00', 11, 'uploads/1756136689_The Basic Set of Tools Every Woman Should Own—and….jpeg', 1),
(37, 'Bolts & Nuts Set', 4, 'l', '2.00', 1, 'uploads/1756136707_12.jpeg', 0),
(38, 'Dimalsha', 2, ';', '5.00', 4, 'uploads/1756136840_SKIL PWR CORE 20 Brushless 20V 1_2 Inch Drill….jpeg', 1),
(39, 'll', 1, 'l', '8.00', 8, 'uploads/1756136878_1756136707_12.jpeg', 1),
(40, 'Sanchana', 5, 'lll', '45.00', 5, 'uploads/1756137357_THE CHALLENGE_After replacing one of the leading….jpeg', 1);

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
(5, 'Sanchana', 'Perera', 'anura@mail.com', '$2y$10$5wdN.8EI/dJoYNFk/9abVe2MvdlcGWmoyuBvJTF.1VHutpRJtOqVy', '+07 660 5548', 'individual', '', '', '151 KEPUNGODA PAMUNUGAMA', 'Negombo', '11370', 'Sri Lanka', '2025-08-25 17:08:47', '2025-08-25 17:08:47');

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
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

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
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

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
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

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
