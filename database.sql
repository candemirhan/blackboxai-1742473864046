-- phpMyAdmin SQL
-- version 5.0.0
-- https://www.phpmyadmin.net/

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Veritabanı: `cloud_storage`
--
CREATE DATABASE IF NOT EXISTS `cloud_storage` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `cloud_storage`;

-- --------------------------------------------------------

--
-- Tablo yapısı: `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(32) NOT NULL,
  `is_admin` tinyint(1) DEFAULT 0,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Tablo için örnek veriler `users`
--

INSERT INTO `users` (`username`, `password`, `is_admin`) VALUES
('can', 'e10adc3949ba59abbe56e057f20f883e', 1); -- Şifre: 123456

-- --------------------------------------------------------

--
-- Tablo yapısı: `files`
--

CREATE TABLE `files` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `filename` varchar(255) NOT NULL,
  `filepath` varchar(255) NOT NULL,
  `filesize` bigint(20) NOT NULL,
  `filetype` varchar(100) DEFAULT NULL,
  `is_common` tinyint(1) DEFAULT 0,
  `user_id` int(11) NOT NULL,
  `upload_date` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;