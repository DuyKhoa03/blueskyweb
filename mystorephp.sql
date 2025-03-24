-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Mar 24, 2025 at 07:15 AM
-- Server version: 9.1.0
-- PHP Version: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `mystorephp`
--
CREATE DATABASE IF NOT EXISTS `mystorephp` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `mystorephp`;

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

DROP TABLE IF EXISTS `cart`;
CREATE TABLE IF NOT EXISTS `cart` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_cart_user` (`user_id`),
  KEY `idx_cart_product` (`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`id`, `user_id`, `product_id`, `quantity`, `created_at`) VALUES
(26, 2, 4, 1, '2025-03-22 11:24:25'),
(27, 1, 3, 1, '2025-03-23 11:23:14'),
(28, 3, 3, 1, '2025-03-23 15:23:39');

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

DROP TABLE IF EXISTS `category`;
CREATE TABLE IF NOT EXISTS `category` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`id`, `name`, `description`) VALUES
(1, 'Điện tử 2', 'Sản phẩm công nghệ và điện tử'),
(2, 'Thời trang', 'Quần áo và phụ kiện'),
(3, 'Điện tử 3', 'Mô tả điện tử 3'),
(4, 'Đồng hồ', 'Đồng hồ cao cấp');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
CREATE TABLE IF NOT EXISTS `orders` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` int DEFAULT NULL,
  `status` enum('pending','processing','completed','canceled') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `total_amount` decimal(10,2) DEFAULT '0.00',
  PRIMARY KEY (`id`),
  KEY `idx_orders_user` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `name`, `phone`, `address`, `created_at`, `user_id`, `status`, `total_amount`) VALUES
(16, '', '0134354656', 'a', '2025-03-19 14:52:44', 1, 'pending', 0.00),
(17, '', '0134354656', 'a', '2025-03-19 14:53:35', 1, 'pending', 0.00),
(18, '', '0134354656', 'as', '2025-03-19 14:56:56', 1, 'pending', 400000.00),
(19, '', '0134354656', 'fff', '2025-03-19 14:57:29', 1, 'pending', 60200000.00),
(20, '', '0134354656', 'aas', '2025-03-20 02:13:45', 1, 'pending', 15200000.00),
(21, '', '0988485765', 'a', '2025-03-21 02:30:25', 2, 'pending', 200000.00);

-- --------------------------------------------------------

--
-- Table structure for table `order_details`
--

DROP TABLE IF EXISTS `order_details`;
CREATE TABLE IF NOT EXISTS `order_details` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) GENERATED ALWAYS AS ((`quantity` * `price`)) STORED,
  PRIMARY KEY (`id`),
  KEY `idx_order_details_order` (`order_id`),
  KEY `idx_order_details_product` (`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_details`
--

INSERT INTO `order_details` (`id`, `order_id`, `product_id`, `quantity`, `price`) VALUES
(9, 16, 2, 99, 200000.00),
(11, 18, 2, 2, 200000.00),
(13, 19, 2, 1, 200000.00),
(15, 20, 2, 1, 200000.00),
(16, 21, 2, 1, 200000.00);

-- --------------------------------------------------------

--
-- Table structure for table `product`
--

DROP TABLE IF EXISTS `product`;
CREATE TABLE IF NOT EXISTS `product` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `price` decimal(10,2) NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `category_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_product_category` (`category_id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product`
--

INSERT INTO `product` (`id`, `name`, `description`, `price`, `image`, `category_id`) VALUES
(2, 'Áo thun ABC 11', 'Áo thun cotton cao cấp', 200000.00, 'uploads/1742457802_lap3.jpg', 4),
(3, 'Duy Khoa 12', 'aaa', 112.00, 'uploads/1742459300_lap2.jpg', 1),
(4, 'Áo thun ABC 112ss', 'aaa', 33112.00, 'uploads/1742524181_lap4.jpg', 1),
(5, 'Duy Khoa 12', 'saasss11', 112132.00, 'uploads/1742524201_lap2.jpg', 2),
(6, 'aaaa', 'sasss', 1224.00, 'uploads/1742524264_lap3.jpg', 3),
(7, 'Điện thoại HONOR X5b Plus 4GB/128GB', 'HONOR X5b Plus nổi bật với màn hình 6.56 inch HD+ 90 Hz, chip Helio G36, RAM 4 GB (mở rộng 4 GB), bộ nhớ 128 GB và pin 5200 mAh. Thiết kế hiện đại và camera AI 50MP giúp sản phẩm đáp ứng mọi nhu cầu từ làm việc đến giải trí.', 2490000.00, 'uploads/1742613553_honor-x5b-plus-blue-thumb-600x600.jpg', 1),
(8, 'Laptop HP 15s fq5147TU i7 1255U/8GB/512GB/Win11 (7C133PA)', 'Laptop HP 15s fq5147TU i7 1255U (7C133PA) được trang bị bộ vi xử lý Intel Core i7 thế hệ 12 cùng những tính năng hấp dẫn khác, giúp người dùng có thể thực hiện các công việc hàng ngày một cách dễ dàng, hiệu quả.', 15690000.00, 'uploads/1742613603_hp-15s-fq5147tu-i7-7c133pa-170225-102700-417-600x600.jpg', 1),
(9, 'Laptop Asus TUF Gaming A15 FA507NV', 'Laptop Asus TUF Gaming A15 FA507NV R7 7735HS (LP031W) sở hữu thông số cấu hình đầy hứa hẹn với con chip AMD Ryzen 7 7735HS, GPU RTX 40 series cùng các tính năng tân tiến, sự trang bị hoàn hảo để game thủ có thể toả sáng trong bất cứ trận chiến nào.', 1900000.00, 'uploads/1742613642_asus-tuf-gaming-a15-fa507nv-r7-lp031w-170225-103822-855-600x600.jpg', 1),
(10, 'Máy tính bảng iPad Air 6 M2 11 inch 5G 1TB', 'Máy tính bảng iPad Air 6 M2 11 inch 5G 1TB', 293949.00, 'uploads/1742613713_ipad-air-11-inch-m2-lte-grey-thumb-600x600.jpg', 1),
(19, 'ss', 's', 11.00, 'uploads/1742737394_Screenshot (11).png', 4),
(20, 'Laptop Dell XPS', 'Máy mỏng nhẹ cao cấp', 25000000.00, '', 1),
(21, 'Áo hoodie Nam', 'Áo hoodie nỉ dày ấm áp', 399000.00, '', 2),
(22, 'Giày thể thao', 'Giày chạy bộ thoải mái', 790000.00, '', 2),
(23, 'MacBook Air M1', 'Laptop Apple mượt mà', 29990000.00, '', 1);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fullname` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'user',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `phone` (`phone`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `fullname`, `address`, `email`, `phone`, `password`, `role`) VALUES
(1, 'DK03', 'Duy Khoa 122', NULL, 'tranduykhoa0303@gmail.com', '0134354656', '$2y$10$ha5hRhVIKwUVfCU6yZ/vZ.ptNaV036bKkBxezCRIQXL0ZGmRYox7e', 'user'),
(2, 'admin', 'Duy Khoa 1', 'HCMs', 'admin@gmail.com', '0988485765', '$2y$12$nLZS9GejipaavJWHzbAh..dKCyXHkt6Vv5p8DWcnUA9h2ux3mE2eC', 'admin'),
(3, 'ticau1111', 'Khoa Trần', NULL, 'ticau1111@gmail.com', '', '$2y$10$qhzKSGIiTxTMy9IHHgp/PeM4VRb6MtYQgMJ//hI3.te1Hcg43N33S', 'user');

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_orders_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `order_details`
--
ALTER TABLE `order_details`
  ADD CONSTRAINT `fk_order_details_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_order_details_product` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_details_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `order_details_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `product`
--
ALTER TABLE `product`
  ADD CONSTRAINT `fk_product_category` FOREIGN KEY (`category_id`) REFERENCES `category` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
