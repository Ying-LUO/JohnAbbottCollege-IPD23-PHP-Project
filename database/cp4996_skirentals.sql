-- phpMyAdmin SQL Dump
-- version 5.0.4
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3333
-- Generation Time: Feb 24, 2021 at 05:09 AM
-- Server version: 10.4.17-MariaDB
-- PHP Version: 7.4.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `skirentalslocal`
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
(1, 'r7crvg4a379h3ds0v6duc9l9lt', 3, 1, 'month'),
(4, 'r7crvg4a379h3ds0v6duc9l9lt', 4, 1, 'month'),
(5, 'r7crvg4a379h3ds0v6duc9l9lt', 2, 2, 'month'),
(6, 'r7crvg4a379h3ds0v6duc9l9lt', 1, 4, 'month');

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
(1, 'Ski Boot-1', 'This is very good one!', '100.00', '200.00', '75f46cc9029c00ab.jpg', 6, 'skiBoots'),
(2, 'Ski Boot-1', 'This is very good one!', '100.00', '200.00', 'eef86cf60d639a6e.jpg', 6, 'snowboardBoots'),
(3, 'bsodvv', 'vsdbvsf sc', '20.00', '100.00', '088db60fc4c5196c.jpg', 43, 'helmets'),
(4, 'bsodvv', 'vsdbvsf sc', '100.00', '200.00', 'e7cabd9a7074ba8a.jpg', 43, 'helmets'),
(5, 'vsdvsdav', 'dwfwdf', '100.00', '200.00', '7f31aee510ef47b8.jpg', 13, 'helmets'),
(6, 'vsdvsdav', 'dwfwdf', '20.00', '100.00', NULL, 17, 'helmets'),
(7, 'Ski Boot-1', 'This is very good one!', '100.00', '200.00', NULL, 60, 'skiBoots'),
(10, 'vsdvsdav cxvsc', 'gsfgsfgvs', '345.00', '7934.00', 'c17240ca1dfc62d0.jpg', 34, 'skiBindings');

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
(4, 'dre', 'df', 'fd@jl.com', 'fred', '$2y$10$JzmzCuV4cWPEbK0NCpeNOehqldpacRts9.Ybrwg4XfPm8SSczqP4.', '123-123-1234', '2021-02-20 22:50:25', 'fhdwof;VH FHIOSDADCHDP CNAPSDCNDPIWFHE', 'csd', 'NT', 'CDS 214', 'user'),
(6, 'dre', 'df', 'test@gmail.com', 'another', '$2y$10$V2P1mF6d8oZzLyNln21qcOY9jRSBUdIpgwbypSJQXY7d1rWMH8Uxu', '514-123-1234', '2021-02-20 23:04:53', 'fdwesvd vdsvcsc cwdcvds', 'csd', 'NL', 'CDS 214', 'user'),
(7, 'ZHOU', 'WU', 'test@lgmai.com', 'new test', '$2y$10$O3mR8VAmC3PfbQDWfqvRQ.xP3l6EzTD031rj2z3ZdEaZGnT/b5G/m', '123-321-1234', '2021-02-20 23:05:47', '2284 Rue De L\'Equateur', 'Saint-Laurent', 'BC', 'H4R 3M4', 'user'),
(8, 'ZHOU', 'WU', 'payment@gmail.com', 'update User Name', '$2y$10$Bin52n.JR/z4oF1wVlKNX./0YE9LgYUR78wpPudjUCvhtIcUoA9VS', '123-234-1234', '2021-02-20 23:17:49', '2284 Rue De L\'Equateur', 'Saint-Laurent', 'BC', 'H4R 3M4', 'user'),
(10, 'ZHOU', 'WU', 'good@gmail.com', 'goodbye', '$2y$10$Ll65lXTWaHkjIKyFIm5bh.d5X6VojcGJ7N/3Gr8wORGh5SpeXARl6', '123-123-1234', '2021-02-21 00:45:36', '2284 Rue De L\'Equateur', 'Saint-Laurent', 'QC', 'H4R 3M4', 'user'),
(11, 'Admin', 'Admin', 'ying.luo.2019@gmail.com', 'Admin', '$2y$10$NVqE/pH89kw6LKqv/bWb/u6tm1Al9upPzplVdDYQbUieYR2a8Qzoa', '514-456-7890', '2021-02-21 21:09:48', 'Admin', 'Admin', 'AB', 'A12 C12', 'admin'),
(12, 'ZHOU', 'WU', 'test@another.com', 'anothony', '$2y$10$5b7YY3dMDsKxVJlZxnKo2.FACg5gZI.wW53.o0yjmd0LO3vbNP4d2', '123-123-1234', '2021-02-22 02:12:58', '2284 Rue De L\'Equateur', 'Saint-Laurent', 'NU', 'H4R 3M4', 'user');

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `equipments`
--
ALTER TABLE `equipments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

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
