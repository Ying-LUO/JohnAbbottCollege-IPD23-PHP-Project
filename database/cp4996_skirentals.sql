-- phpMyAdmin SQL Dump
-- version 4.9.5
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Feb 24, 2021 at 11:14 AM
-- Server version: 10.3.28-MariaDB-log
-- PHP Version: 7.3.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `cp4996_skirentals`
--

-- --------------------------------------------------------

--
-- Table structure for table `cartitems`
--

CREATE TABLE `cartitems` (
  `id` int(11) NOT NULL,
  `session_id` varchar(100) NOT NULL,
  `equipId` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `rentalType` enum('month','season') NOT NULL DEFAULT 'month'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `cartitems`
--

INSERT INTO `cartitems` (`id`, `session_id`, `equipId`, `quantity`, `rentalType`) VALUES
(11, '4d8a5530b611f87f61e511fbfba175e3', 11, 2, 'month'),
(13, 'fefb8b4c986b562fb7fbf3b69263f675', 13, 1, 'month'),
(16, 'fefb8b4c986b562fb7fbf3b69263f675', 11, 2, 'month'),
(18, 'fefb8b4c986b562fb7fbf3b69263f675', 14, 1, 'month'),
(20, '4d8a5530b611f87f61e511fbfba175e3', 19, 2, 'season');

-- --------------------------------------------------------

--
-- Table structure for table `equipments`
--

CREATE TABLE `equipments` (
  `id` int(11) NOT NULL,
  `equipName` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `rateByMonth` decimal(10,2) NOT NULL DEFAULT 20.00,
  `rateBySeason` decimal(10,2) NOT NULL DEFAULT 100.00,
  `photo` varchar(320) DEFAULT NULL,
  `inStock` int(11) NOT NULL,
  `category` enum('skiBoots','skiBindings','goggles','helmets','snowboardBoots','snowboardBindings') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `equipments`
--

INSERT INTO `equipments` (`id`, `equipName`, `description`, `rateByMonth`, `rateBySeason`, `photo`, `inStock`, `category`) VALUES
(11, 'Ski Boot-1', 'Nice one', 100.00, 200.00, 'a38aea1edec01af2.jpg', 50, 'skiBoots'),
(13, 'Ski Boot-3', 'good one', 40.00, 100.00, '65f81e1d228bc2ba.jpg', 1, 'skiBoots'),
(14, 'Ski Boot-4', 'super', 100.00, 200.00, '43502f2f7136f7df.jpg', 5, 'skiBoots'),
(16, 'Ski Boot-6', 'good for kids', 45.00, 100.00, 'ac3d6f80b8446902.jpg', 3, 'skiBoots'),
(17, 'Ski Boot-7', 'good', 100.00, 200.00, 'a097a77393c8ab65.jpg', 1, 'skiBoots'),
(18, 'Helmet-1', 'blue one', 25.00, 60.00, '515e2821f61a4f35.png', 3, 'helmets'),
(19, 'Helmet-2', 'good deal', 15.00, 40.00, '77c35f67c47cd9af.png', 2, 'helmets'),
(20, 'Goggles-1', 'WOW', 20.00, 45.00, 'e218ff089c0e15d8.jpg', 2, 'goggles'),
(21, 'Goggles-2', 'good deal', 10.00, 25.00, '05e9f3ac07418011.jpg', 2, 'goggles'),
(23, 'Test binding', 'test', 50.00, 200.00, '9c8cdb493f06a767.jpg', 12, 'skiBindings'),
(24, 'Test boots cx cz', 'ncdpsfdsp', 12.00, 24.00, '1c04b48b27468f1c.jpg', 5, 'skiBindings');

-- --------------------------------------------------------

--
-- Table structure for table `orderitems`
--

CREATE TABLE `orderitems` (
  `id` int(11) NOT NULL,
  `orderId` int(11) NOT NULL,
  `equipId` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `rentalPrice` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `orderTS` timestamp NOT NULL DEFAULT current_timestamp(),
  `totalPrice` decimal(10,2) NOT NULL,
  `paymentType` enum('credit_card','debit_card','payPal') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `passwordresets`
--

CREATE TABLE `passwordresets` (
  `id` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `secret` varchar(60) NOT NULL,
  `creationDateTime` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `passwordresets`
--

INSERT INTO `passwordresets` (`id`, `userId`, `secret`, `creationDateTime`) VALUES
(35, 11, 'udjflecxhHwePMVXyxDVP1rRQ7zsubb99cuZYFebx9bh7HhpuMNk2SafAZMm', '2021-02-24 17:23:01'),
(37, 21, 'AQxPo5QZX2BdNxpTQ2VziRXPTHCpayIgKCMQawMyl0SQaQh8RAK7nHcuLSRw', '2021-02-24 08:24:18');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `firstName` varchar(50) NOT NULL,
  `lastName` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `username` varchar(30) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(12) NOT NULL,
  `registerTS` timestamp NOT NULL DEFAULT current_timestamp(),
  `street` varchar(100) NOT NULL,
  `city` varchar(100) NOT NULL,
  `province` varchar(30) NOT NULL,
  `postalCode` varchar(7) NOT NULL,
  `role` enum('user','admin') NOT NULL DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `firstName`, `lastName`, `email`, `username`, `password`, `phone`, `registerTS`, `street`, `city`, `province`, `postalCode`, `role`) VALUES
(6, 'dre', 'df', 'test@gmail.com', 'another', '$2y$10$V2P1mF6d8oZzLyNln21qcOY9jRSBUdIpgwbypSJQXY7d1rWMH8Uxu', '514-123-1234', '2021-02-20 23:04:53', 'fdwesvd vdsvcsc cwdcvds', 'csd', 'NL', 'CDS 214', 'user'),
(7, 'ZHOU', 'WU', 'test@lgmai.com', 'new test', '$2y$10$O3mR8VAmC3PfbQDWfqvRQ.xP3l6EzTD031rj2z3ZdEaZGnT/b5G/m', '123-321-1234', '2021-02-20 23:05:47', '2284 Rue De L\'Equateur', 'Saint-Laurent', 'BC', 'H4R 3M4', 'user'),
(8, 'ZHOU', 'WU', 'payment@gmail.com', 'update User Name', '$2y$10$Bin52n.JR/z4oF1wVlKNX./0YE9LgYUR78wpPudjUCvhtIcUoA9VS', '123-234-1234', '2021-02-20 23:17:49', '2284 Rue De L\'Equateur', 'Saint-Laurent', 'BC', 'H4R 3M4', 'user'),
(10, 'ZHOU', 'WU', 'good@gmail.com', 'goodbye', '$2y$10$Ll65lXTWaHkjIKyFIm5bh.d5X6VojcGJ7N/3Gr8wORGh5SpeXARl6', '123-123-1234', '2021-02-21 00:45:36', '2284 Rue De L\'Equateur', 'Saint-Laurent', 'QC', 'H4R 3M4', 'user'),
(11, 'Admin', 'Admin', 'ying.luo@johnabbottcollege.net', 'Admin', '$2y$10$Ymrq8qD3hdSCTaS2PZ.f8u74jkERtZM1448KoB42lThfotccGGhBK', '514-456-7890', '2021-02-21 21:09:48', 'Admin', 'Admin', 'AB', 'A12 C12', 'admin'),
(12, 'ZHOU', 'WU', 'test@another.com', 'anothony', '$2y$10$5b7YY3dMDsKxVJlZxnKo2.FACg5gZI.wW53.o0yjmd0LO3vbNP4d2', '123-123-1234', '2021-02-22 02:12:58', '2284 Rue De L\'Equateur', 'Saint-Laurent', 'NU', 'H4R 3M4', 'user'),
(21, 'Khalil', 'Hanna', 'khalilhanna@gmail.com', 'khalilhanna', '$2y$10$ug7o14kcfjJEnDmU6A5PXek/PGa9Fx9zg1JSrJ7.loXBVM/0jQboa', '514-813-2880', '2021-02-24 06:35:25', '4561 Boul AAA', 'Montreal', 'QC', 'H7R4S5', 'admin'),
(22, 'zhou', 'WU', 'testpp@gmail.com', 'testUser pp', '$2y$10$202YL5UlRQ1H0dRY2RKKleqBCtmv4N4hBpqTZfK2HbrsgBSfOcPa6', '123-123-1234', '2021-02-24 15:35:58', 'dsvds', 'Montreal', 'QC', 'H4L 3R5', 'user');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cartitems`
--
ALTER TABLE `cartitems`
  ADD PRIMARY KEY (`id`),
  ADD KEY `equipId` (`equipId`);

--
-- Indexes for table `equipments`
--
ALTER TABLE `equipments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orderitems`
--
ALTER TABLE `orderitems`
  ADD PRIMARY KEY (`id`),
  ADD KEY `equipId` (`equipId`),
  ADD KEY `orderId` (`orderId`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `userId` (`userId`);

--
-- Indexes for table `passwordresets`
--
ALTER TABLE `passwordresets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `userId` (`userId`) USING BTREE;

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cartitems`
--
ALTER TABLE `cartitems`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `equipments`
--
ALTER TABLE `equipments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `orderitems`
--
ALTER TABLE `orderitems`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `passwordresets`
--
ALTER TABLE `passwordresets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cartitems`
--
ALTER TABLE `cartitems`
  ADD CONSTRAINT `cartitems_ibfk_1` FOREIGN KEY (`equipId`) REFERENCES `equipments` (`id`);

--
-- Constraints for table `orderitems`
--
ALTER TABLE `orderitems`
  ADD CONSTRAINT `orderitems_ibfk_1` FOREIGN KEY (`equipId`) REFERENCES `equipments` (`id`),
  ADD CONSTRAINT `orderitems_ibfk_2` FOREIGN KEY (`orderId`) REFERENCES `orders` (`id`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`userId`) REFERENCES `users` (`id`);

--
-- Constraints for table `passwordresets`
--
ALTER TABLE `passwordresets`
  ADD CONSTRAINT `passwordresets_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
